<?php

declare(strict_types=1);

namespace Tests\Unit\blocks;

use epiphyt\Impressum\settings\Data;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use PHPUnit\Framework\Attributes\CoversClass;

use function Brain\Monkey\Functions\stubEscapeFunctions;
use function Brain\Monkey\Functions\stubs;
use function Brain\Monkey\Functions\stubTranslationFunctions;
use function Brain\Monkey\setUp;
use function Brain\Monkey\tearDown;

#[CoversClass(Data::class)]
final class DataTest extends MockeryTestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        setUp();
        stubEscapeFunctions();
        stubTranslationFunctions();
        stubs([
            'add_shortcode' => '__return_null',
            'register_activation_hook' => '__return_null',
            'register_deactivation_hook' => '__return_null',
        ]);
    }

    public function testInit(): void
    {
        $settings_registry = include __DIR__ . '/../helpers/SettingsRegistryMock.php';
        $settings_data = new Data($settings_registry);
        $settings_data->init();
        $this->assertSame(
            15,
            \has_action('init', '\epiphyt\Impressum\settings\Data->register_filters()')
        );
    }

    public function testRegisterFilters(): void
    {
        $settings_registry = include __DIR__ . '/../helpers/SettingsRegistryMock.php';
        $settings_data = new Data($settings_registry);
        $settings_data->register_filters();
        $this->assertSame(
            10,
            \has_filter(
                'pre_update_option_impressum_imprint_options',
                '\epiphyt\Impressum\settings\Data->sanitize_update_option()'
            )
        );
    }

    public function testSanitizeUpdateOption(): void
    {
        $settings_registry = include __DIR__ . '/../helpers/SettingsRegistryMock.php';
        $settings_data = new Data($settings_registry);
        $value_all_good = [
            'page' => '3',
            'country' => 'deu',
        ];
        $value_unknown_option = [
            'page' => '3',
            'country' => 'deu',
            'unknown' => 'This should be removed',
        ];
        $this->assertEqualsCanonicalizing(
            $value_all_good,
            $settings_data->sanitize_update_option($value_all_good, [], 'impressum_imprint_options')
        );
        $this->assertEqualsCanonicalizing(
            $value_all_good,
            $settings_data->sanitize_update_option($value_unknown_option, [], 'impressum_imprint_options')
        );
    }

    protected function tearDown(): void
    {
        tearDown();
        parent::tearDown();
    }
}
