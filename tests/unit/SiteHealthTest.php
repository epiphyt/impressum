<?php

declare(strict_types=1);

namespace Tests\Unit;

use epiphyt\Impressum\Admin;
use epiphyt\Impressum\Helper;
use epiphyt\Impressum\settings\Registry;
use epiphyt\Impressum\settings\Validator;
use epiphyt\Impressum\Site_Health;
use Mockery;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\PreserveGlobalState;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;

use function Brain\Monkey\Filters\expectApplied;
use function Brain\Monkey\Functions\stubEscapeFunctions;
use function Brain\Monkey\Functions\stubs;
use function Brain\Monkey\Functions\stubTranslationFunctions;
use function Brain\Monkey\setUp;
use function Brain\Monkey\tearDown;

#[CoversClass(Site_Health::class)]
#[RunTestsInSeparateProcesses]
#[PreserveGlobalState(false)]
final class SiteHealthTest extends MockeryTestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        setUp();
        stubEscapeFunctions();
        stubTranslationFunctions();
        stubs([
            'add_query_arg' => static fn (array $args, string $url): string => $url . '?' . \http_build_query($args),
            'admin_url' => static fn (string $path = ''): string => 'https://example.com/wp-admin/' . $path,
        ]);
    }

    public function testInit(): void
    {
        $settings_registry = Mockery::mock('alias:' . Registry::class);
        /** @disregard P1006 */
        $site_health = new Site_Health(new Admin($settings_registry, new Validator($settings_registry)));
        $site_health->init();
        $this->assertSame(10, \has_filter('site_status_tests', '\epiphyt\Impressum\Site_Health->register_tests()'));
    }

    public function testRegisterTests(): void
    {
        $settings_registry = Mockery::mock('alias:' . Registry::class);
        /** @disregard P1006 */
        $site_health = new Site_Health(new Admin($settings_registry, new Validator($settings_registry)));
        $tests = $site_health->register_tests([
            'async' => [
                'foo' => [
                    'label' => 'Foo',
                    'test' => 'foo',
                ],
            ],
        ]);

        $this->assertSame(
            [
                'label' => 'Foo',
                'test' => 'foo',
            ],
            $tests['async']['foo']
        );
        $this->assertSame(
            [
                'label' => 'Required imprint fields',
                'test' => [$site_health, 'test_imprint_required_fields'],
            ],
            $tests['direct']['impressum_imprint_required_fields']
        );
    }

    public function testImprintRequiredFields(): void
    {
        $helper = Mockery::mock('alias:' . Helper::class);
        $container = Mockery::mock('Container');
        $container->shouldReceive('get')->with('helper')->andReturn($helper);
        $GLOBALS['impressum_test_container'] = $container;
        $helper->shouldReceive('get_option')->andReturn(
            [],
            [
                'address' => 'Address',
                'contact_form_page' => 1,
                'email' => 'mail@example.com',
                'legal_entity' => 'individual',
                'name' => 'Name',
            ]
        );
        expectApplied('impressum_required_fields')->times(2)->andReturnFirstArg();
        $settings_registry = include __DIR__ . '/helpers/SettingsRegistryMock.php';
        /** @disregard P1006 */
        $site_health = new Site_Health(new Admin($settings_registry, new Validator($settings_registry)));

        $result = $site_health->test_imprint_required_fields();
        $this->assertSame('critical', $result['status']);
        $this->assertSame('red', $result['badge']['color']);
        $this->assertSame('Your imprint is missing required information', $result['label']);
        $this->assertStringContainsString(
            '<ul><li>Address</li><li>Email Address</li><li>Name</li><li>Phone or Contact Form Page</li></ul>',
            $result['description']
        );
        $this->assertStringContainsString(
            'https://example.com/wp-admin/options-general.php?imprint_tab=imprint&page=impressum',
            $result['actions']
        );
        $this->assertSame('impressum_imprint_required_fields', $result['test']);

        $result = $site_health->test_imprint_required_fields();
        $this->assertSame('good', $result['status']);
        $this->assertSame('blue', $result['badge']['color']);
        $this->assertSame('Your imprint contains all required information', $result['label']);
        $this->assertSame('', $result['actions']);
        $this->assertSame('impressum_imprint_required_fields', $result['test']);
    }

    protected function tearDown(): void
    {
        tearDown();
        parent::tearDown();
    }
}
