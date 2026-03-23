<?php

declare(strict_types=1);

namespace Tests\Unit\blocks;

use epiphyt\Impressum\Helper;
use epiphyt\Impressum\settings\Registry;
use epiphyt\Impressum\settings\Setting;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use PHPUnit\Framework\Attributes\CoversClass;

use function Brain\Monkey\Functions\stubEscapeFunctions;
use function Brain\Monkey\Functions\stubs;
use function Brain\Monkey\Functions\stubTranslationFunctions;
use function Brain\Monkey\setUp;
use function Brain\Monkey\tearDown;

#[CoversClass(Registry::class)]
final class RegistryTest extends MockeryTestCase
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

    public function testGetSetting(): void
    {
        $helper = \Mockery::mock('alias:' . Helper::class);
        $setting = \Mockery::mock('overload:' . Setting::class);
        $setting
            ->shouldReceive('__construct')
            ->andSet('name', 'page')
            ->andSet('type', 'impressum_imprint_options');
        $setting->name = 'page';
        $setting->type = 'impressum_imprint_options';
        /** @disregard P1006 */
        $settings_registry = new Registry($helper);
        $this->assertNull($settings_registry->get_setting('impressum_imprint_options_page'));
        $settings_registry->register(
            'page',
            [
                'api' => [
                    'description' => \esc_html__('The imprint page ID.', 'impressum'),
                    'type' => 'integer',
                ],
                'args' => [
                    'class' => 'impressum_row',
                    'label_for' => 'page',
                    'setting' => 'impressum_imprint_options',
                ],
                'callback' => 'page',
                'no_output' => true,
                'page' => 'impressum_imprint',
                'section' => 'impressum_section_imprint',
                'title' => \__('Imprint Page', 'impressum'),
            ]
        );
        $actualSetting = $settings_registry->get_setting('impressum_imprint_options_page');
        $this->assertSame('page', $actualSetting->name);
        $this->assertSame('impressum_imprint_options', $actualSetting->type);
    }

    public function testGetSettingTypes(): void
    {
        $helper = \Mockery::mock('alias:' . Helper::class);
        $setting = \Mockery::mock('overload:' . Setting::class);
        $setting
            ->shouldReceive('__construct')
            ->andSet('name', 'page')
            ->andSet('type', 'impressum_imprint_options');
        $setting->name = 'page';
        $setting->type = 'impressum_imprint_options';
        /** @disregard P1006 */
        $settings_registry = new Registry($helper);
        $settings_registry->register(
            'page',
            [
                'api' => [
                    'description' => \esc_html__('The imprint page ID.', 'impressum'),
                    'type' => 'integer',
                ],
                'args' => [
                    'class' => 'impressum_row',
                    'label_for' => 'page',
                    'setting' => 'impressum_imprint_options',
                ],
                'callback' => 'page',
                'no_output' => true,
                'page' => 'impressum_imprint',
                'section' => 'impressum_section_imprint',
                'title' => \__('Imprint Page', 'impressum'),
            ]
        );
        $this->assertEqualsCanonicalizing(
            ['impressum_imprint_options'],
            $settings_registry->get_setting_types()
        );
    }

    public function testGetSettings(): void
    {
        $helper = \Mockery::mock('alias:' . Helper::class);
        $setting = \Mockery::mock('overload:' . Setting::class);
        $setting->name = 'page';
        $setting->type = 'impressum_imprint_options';
        $setting
            ->shouldReceive('__construct')
            ->andSet('name', 'page')
            ->andSet('type', 'impressum_imprint_options');
        $setting
            ->shouldReceive('get_data')
            ->with('type')
            ->andReturn($setting->type);
        /** @disregard P1006 */
        $settings_registry = new Registry($helper);
        $this->assertNull($settings_registry->get_setting('impressum_imprint_options_page'));
        $settings_registry->register(
            'page',
            [
                'api' => [
                    'description' => \esc_html__('The imprint page ID.', 'impressum'),
                    'type' => 'integer',
                ],
                'args' => [
                    'class' => 'impressum_row',
                    'label_for' => 'page',
                    'setting' => 'impressum_imprint_options',
                ],
                'callback' => 'page',
                'no_output' => true,
                'page' => 'impressum_imprint',
                'section' => 'impressum_section_imprint',
                'title' => \__('Imprint Page', 'impressum'),
            ]
        );
        $this->assertEmpty($settings_registry->get_settings('impressum_invalid_options'));
        $settings = $settings_registry->get_settings('impressum_imprint_options');

        foreach ($settings as $setting) {
            $this->assertSame('impressum_imprint_options', $setting->type);
        }
    }

    protected function tearDown(): void
    {
        tearDown();
        parent::tearDown();
    }
}
