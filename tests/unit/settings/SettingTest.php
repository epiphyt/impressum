<?php

declare(strict_types=1);

namespace Tests\Unit\blocks;

use epiphyt\Impressum\Helper;
use epiphyt\Impressum\settings\Registry;
use epiphyt\Impressum\settings\Setting;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use PHPUnit\Framework\Attributes\CoversClass;

use function Brain\Monkey\Functions\expect;
use function Brain\Monkey\Functions\stubEscapeFunctions;
use function Brain\Monkey\Functions\stubs;
use function Brain\Monkey\Functions\stubTranslationFunctions;
use function Brain\Monkey\setUp;
use function Brain\Monkey\tearDown;

#[CoversClass(Setting::class)]
final class SettingTest extends MockeryTestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        setUp();
        stubEscapeFunctions();
        stubTranslationFunctions();
        stubs([
            'add_shortcode' => '__return_null',
            'is_network_admin' => false,
            'register_activation_hook' => '__return_null',
            'register_deactivation_hook' => '__return_null',
        ]);
    }

    public function testGetData(): void
    {
        $helper = \Mockery::mock('alias:' . Helper::class);
        $helper
            ->shouldReceive('get_option')
            ->andReturn([]);
            /** @disregard P1006 */
        $setting = new Setting(
            'page',
            [
                'api' => [
                    'description' => \esc_html__('The imprint page ID.', 'impressum'),
                    'type' => 'integer',
                ],
                'args' => [
                    'class' => 'impressum_row',
                    'label_for' => 'page',
                    //'setting' => 'impressum_imprint_options',
                ],
                'callback' => 'page',
                'no_output' => true,
                'page' => 'impressum_imprint',
                'section' => 'impressum_section_imprint',
                'title' => \__('Imprint Page', 'impressum'),
            ],
            $helper
        );
        $this->assertNull($setting->get_data('custom_title'));
        $this->assertEqualsCanonicalizing(
            [
                'type' => 'integer',
            ],
            $setting->get_data('data_type')
        );
        $this->assertSame('The imprint page ID.', $setting->get_data('description'));
        $this->assertTrue($setting->get_data('hide_output'));
        $this->assertEqualsCanonicalizing(
            [
                'class' => 'impressum_row',
                'label_for' => 'page',
                //'setting' => 'impressum_imprint_options',
            ],
            $setting->get_data('setting_attributes')
        );
        $this->assertSame('page', $setting->get_data('setting_callback'));
        $this->assertSame('impressum_imprint', $setting->get_data('setting_page'));
        $this->assertSame('impressum_section_imprint', $setting->get_data('setting_section'));
        $this->assertSame('Imprint Page', $setting->get_data('title'));
        $this->assertSame('impressum_imprint_options', $setting->get_data('type'));
    }

    public function testGetTitle(): void
    {
        $helper = \Mockery::mock('alias:' . Helper::class);
        $helper
            ->shouldReceive('get_option')
            ->andReturn(['page' => ['name' => 'Custom Title']], []);
        /** @disregard P1006 */
        $setting = new Setting(
            'page',
            [
                'api' => [
                    'description' => \esc_html__('The imprint page ID.', 'impressum'),
                    'type' => 'integer',
                ],
                'args' => [
                    'class' => 'impressum_row',
                    'label_for' => 'page',
                    //'setting' => 'impressum_imprint_options',
                ],
                'callback' => 'page',
                'no_output' => true,
                'page' => 'impressum_imprint',
                'section' => 'impressum_section_imprint',
                'title' => \__('Imprint Page', 'impressum'),
            ],
            $helper
        );
        $this->assertSame('Custom Title', $setting->get_title());
        /** @disregard P1006 */
        $setting = new Setting(
            'page',
            [
                'api' => [
                    'description' => \esc_html__('The imprint page ID.', 'impressum'),
                    'type' => 'integer',
                ],
                'args' => [
                    'class' => 'impressum_row',
                    'label_for' => 'page',
                    //'setting' => 'impressum_imprint_options',
                ],
                'callback' => 'page',
                'no_output' => true,
                'page' => 'impressum_imprint',
                'section' => 'impressum_section_imprint',
                'title' => \__('Imprint Page', 'impressum'),
            ],
            $helper
        );
        $this->assertSame('Imprint Page', $setting->get_title());
    }

    public function testGetValue(): void
    {
        $helper = \Mockery::mock('alias:' . Helper::class);
        $helper
            ->shouldReceive('get_option')
            ->andReturn(
                [], // custom titles
                // first run
                [],
                // second run
                ['page' => 'value']
            );
        /** @disregard P1006 */
        $setting = new Setting(
            'page',
            [
                'api' => [
                    'description' => \esc_html__('The imprint page ID.', 'impressum'),
                    'type' => 'integer',
                ],
                'args' => [
                    'class' => 'impressum_row',
                    'label_for' => 'page',
                    //'setting' => 'impressum_imprint_options',
                ],
                'callback' => 'page',
                'no_output' => true,
                'page' => 'impressum_imprint',
                'section' => 'impressum_section_imprint',
                'title' => \__('Imprint Page', 'impressum'),
            ],
            $helper
        );
        $this->assertEmpty($setting->get_value());
        $this->assertSame('value', $setting->get_value());
    }

    public function testSetValue(): void
    {
        $helper = \Mockery::mock('alias:' . Helper::class);
        $helper
            ->shouldReceive('get_option')
            ->andReturn([]);
        /** @disregard P1006 */
        $setting = new Setting(
            'page',
            [
                'api' => [
                    'description' => \esc_html__('The imprint page ID.', 'impressum'),
                    'type' => 'integer',
                ],
                'args' => [
                    'class' => 'impressum_row',
                    'label_for' => 'page',
                    //'setting' => 'impressum_imprint_options',
                ],
                'callback' => 'page',
                'no_output' => true,
                'page' => 'impressum_imprint',
                'section' => 'impressum_section_imprint',
                'title' => \__('Imprint Page', 'impressum'),
            ],
            $helper
        );
        expect('update_option')->andReturn(true);
        $this->assertTrue($setting->set_value('new value'));
        // get new value from cache
        $this->assertSame('new value', $setting->get_value());
    }

    protected function tearDown(): void
    {
        tearDown();
        parent::tearDown();
    }
}
