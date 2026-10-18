<?php

declare(strict_types=1);

namespace Tests\Unit\api;

use epiphyt\Impressum\Helper;
use epiphyt\Impressum\api\Settings_Controller;
use epiphyt\Impressum\settings\Registry;
use epiphyt\Impressum\settings\Schema;
use epiphyt\Impressum\settings\Setting;
use epiphyt\Impressum\settings\Validator;
use Mockery;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\PreserveGlobalState;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use WP_REST_Request;

use function Brain\Monkey\Functions\expect;
use function Brain\Monkey\Functions\stubEscapeFunctions;
use function Brain\Monkey\Functions\stubs;
use function Brain\Monkey\Functions\stubTranslationFunctions;
use function Brain\Monkey\Functions\when;
use function Brain\Monkey\setUp;
use function Brain\Monkey\tearDown;

#[CoversClass(Settings_Controller::class)]
#[RunTestsInSeparateProcesses]
#[PreserveGlobalState(false)]
final class SettingsControllerTest extends MockeryTestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        setUp();
        stubEscapeFunctions();
        stubTranslationFunctions();
        stubs([
            'current_user_can' => true,
            'is_multisite' => false,
            'rest_authorization_required_code' => 403,
            'rest_ensure_response' => static fn ($value) => new \WP_REST_Response($value),
        ]);
    }

    private function buildController(
        array $setting_names = ['name'],
        bool $field_data_writable = false,
        array $invalid = []
    ): Settings_Controller {
        $base = Mockery::mock('alias:' . Setting::class);
        $settings = [];

        foreach ($setting_names as $name) {
            $setting = clone $base;
            $setting->name = $name;
            $setting->type = 'impressum_imprint_options';
            $settings['impressum_imprint_options_' . $name] = $setting;
        }

        $registry = Mockery::mock('alias:' . Registry::class);
        $registry->shouldReceive('get_settings')->andReturn($settings);
        $registry
            ->shouldReceive('get_setting_types')
            ->andReturn(['impressum_imprint_options']);

        $schema = Mockery::mock('alias:' . Schema::class);
        $schema->shouldReceive('get_json_schema')->andReturn([]);
        $schema->shouldReceive('get_invalid_values')->andReturn($invalid);
        $schema->shouldReceive('get_payload')->andReturn(['version' => '3.1.0']);
        $schema->shouldReceive('get_values_payload')->andReturn(['values' => []]);
        $schema->shouldReceive('is_field_data_writable')->andReturn($field_data_writable);

        $validator = Mockery::mock('alias:' . Validator::class);
        $validator->shouldReceive('get_errors')->andReturn([]);
        $validator->shouldReceive('get_notices')->andReturn([]);
        $validator->shouldReceive('get_required_fields')->andReturn([]);

        $helper = Mockery::mock('alias:' . Helper::class);
        $helper
            ->shouldReceive('with_scope')
            ->andReturnUsing(static fn (string $scope, callable $callback) => $callback());
        $helper->shouldReceive('clear_option_cache');

        /** @disregard P1006 */
        return new Settings_Controller($registry, $schema, $validator);
    }

    public function testRegisterRoutesRegistersBothRoutes(): void
    {
        expect('register_rest_route')
            ->once()
            ->with('impressum/v1', '/settings', Mockery::type('array'));
        expect('register_rest_route')
            ->once()
            ->with('impressum/v1', '/settings/validate', Mockery::type('array'));
        $this->buildController()->register_routes();
    }

    public function testGroupIsLimitedToRegisteredSettingsGroups(): void
    {
        $routes = [];
        expect('register_rest_route')->andReturnUsing(
            static function (string $namespace, string $route, array $args) use (&$routes): bool {
                $routes[$route] = $args;

                return true;
            }
        );
        $this->buildController()->register_routes();
        $this->assertSame(
            ['', 'impressum_imprint_options'],
            $routes['/settings'][0]['args']['group']['enum']
        );
    }

    public function testPermissionCheckRequiresManageOptions(): void
    {
        when('current_user_can')->alias(static fn (string $capability) => $capability === 'manage_options');
        $this->assertTrue(
            $this->buildController()->get_item_permissions_check(new WP_REST_Request())
        );
    }

    public function testPermissionCheckRequiresManageNetworkOptionsForNetworkScope(): void
    {
        when('is_multisite')->justReturn(true);
        when('current_user_can')->alias(static fn (string $capability) => $capability === 'manage_options');
        $result = $this->buildController()
            ->get_item_permissions_check(new WP_REST_Request(['network' => true]));
        $this->assertInstanceOf(\WP_Error::class, $result);
        $this->assertSame('rest_forbidden', $result->get_error_code());
    }

    public function testPermissionCheckRejectsNetworkScopeOnSingleSite(): void
    {
        $result = $this->buildController()
            ->get_item_permissions_check(new WP_REST_Request(['network' => true]));
        $this->assertInstanceOf(\WP_Error::class, $result);
        $this->assertSame('impressum_rest_no_multisite', $result->get_error_code());
    }

    public function testUpdateItemWritesThroughUpdateOption(): void
    {
        // sanitizing happens in pre_update_option_*, never in the controller
        when('get_option')->justReturn([]);
        expect('update_option')
            ->once()
            ->with('impressum_imprint_options', ['name' => 'Example']);
        expect('sanitize_text_field')->never();
        $this->buildController()->update_item(new WP_REST_Request([
            'values' => ['impressum_imprint_options' => ['name' => 'Example']],
        ]));
    }

    public function testUpdateItemMergesWithStoredValues(): void
    {
        when('get_option')->justReturn(['email' => 'mail@example.com', 'name' => 'Old']);
        expect('update_option')
            ->once()
            ->with('impressum_imprint_options', ['email' => 'mail@example.com', 'name' => 'New']);
        $this->buildController()->update_item(new WP_REST_Request([
            'values' => ['impressum_imprint_options' => ['name' => 'New']],
        ]));
    }

    public function testUpdateItemStripsTheMergedNetworkValues(): void
    {
        // the default key is a read time artifact and must never be stored
        when('get_option')->justReturn(['default' => ['name' => 'Network'], 'name' => 'Local']);
        expect('update_option')
            ->once()
            ->with('impressum_imprint_options', ['name' => 'Changed']);
        $this->buildController()->update_item(new WP_REST_Request([
            'values' => ['impressum_imprint_options' => ['name' => 'Changed']],
        ]));
    }

    public function testUpdateItemIgnoresUnregisteredGroups(): void
    {
        expect('update_option')->never();
        expect('get_option')->never();
        $this->buildController()->update_item(new WP_REST_Request([
            'values' => ['some_other_option' => ['name' => 'Example']],
        ]));
    }

    public function testUpdateItemUsesSiteOptionsForNetworkScope(): void
    {
        when('is_multisite')->justReturn(true);
        when('get_site_option')->justReturn([]);
        expect('update_site_option')
            ->once()
            ->with('impressum_imprint_options', ['name' => 'Example']);
        expect('update_option')->never();
        $this->buildController()->update_item(new WP_REST_Request([
            'network' => true,
            'values' => ['impressum_imprint_options' => ['name' => 'Example']],
        ]));
    }

    public function testFieldDataIsIgnoredWhenNotWritable(): void
    {
        when('get_option')->justReturn([]);
        expect('update_option')
            ->once()
            ->with('impressum_imprint_options', Mockery::type('array'));
        $this->buildController(['name'], false)->update_item(new WP_REST_Request([
            'field_data' => ['name' => ['name' => 'Custom']],
            'values' => ['impressum_imprint_options' => ['name' => 'Example']],
        ]));
    }

    public function testFieldDataIsFilteredToRegisteredFields(): void
    {
        when('get_option')->justReturn([]);
        expect('update_option')
            ->once()
            ->with('impressum_imprint_options', Mockery::type('array'));
        expect('update_option')
            ->once()
            ->with('impressum_field_data', ['name' => ['name' => 'Custom']]);
        $this->buildController(['name'], true)->update_item(new WP_REST_Request([
            'field_data' => [
                'name' => ['name' => 'Custom'],
                'unregistered' => ['name' => 'Dropped'],
            ],
            'values' => ['impressum_imprint_options' => ['name' => 'Example']],
        ]));
    }

    public function testUpdateItemRejectsAnInvalidValueWithoutWriting(): void
    {
        $controller = $this->buildController(['page'], false, [
            'page' => [
                'code' => 'impressum_invalid_page',
                'message' => 'Select a page from the list.',
            ],
        ]);
        expect('update_option')->never();
        expect('get_option')->never();

        $result = $controller->update_item(new WP_REST_Request([
            'values' => ['impressum_imprint_options' => ['page' => '999']],
        ]));

        $this->assertInstanceOf(\WP_Error::class, $result);
        $this->assertSame('impressum_invalid_settings', $result->get_error_code());

        $fields = $result->get_error_data()['fields'];
        $this->assertArrayHasKey('impressum_imprint_options.page', $fields);
        $this->assertSame(
            'page',
            $fields['impressum_imprint_options.page']['name']
        );
    }

    public function testValidateItemDoesNotWrite(): void
    {
        expect('update_option')->never();
        expect('update_site_option')->never();
        $response = $this->buildController()->validate_item(new WP_REST_Request([
            'values' => ['impressum_imprint_options' => []],
        ]));
        $this->assertSame(
            ['errors', 'notices', 'required'],
            array_keys($response->get_data())
        );
    }

    protected function tearDown(): void
    {
        tearDown();
        parent::tearDown();
    }
}
