<?php

declare(strict_types=1);

namespace Tests\Unit;

use epiphyt\Impressum\Frontend;
use epiphyt\Impressum\Helper;
use epiphyt\Impressum\settings\Registry;
use epiphyt\Impressum\settings\Setting;
use epiphyt\Impressum\settings\Validator;
use Mockery;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\PreserveGlobalState;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;

use function Brain\Monkey\Functions\stubEscapeFunctions;
use function Brain\Monkey\Functions\stubs;
use function Brain\Monkey\Functions\stubTranslationFunctions;
use function Brain\Monkey\setUp;
use function Brain\Monkey\tearDown;

#[CoversClass(Frontend::class)]
#[RunTestsInSeparateProcesses]
#[PreserveGlobalState(false)]
final class FrontendTest extends MockeryTestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        setUp();
        stubEscapeFunctions();
        stubTranslationFunctions();
        stubs([
            'add_shortcode' => '__return_null',
        ]);
    }

    /**
     * Set up a container handing out the given imprint options and settings.
     *
     * @param array<string, mixed> $options Imprint options
     * @param array<string, array<string, mixed>> $definitions Setting data keyed by setting name
     */
    private function setUpContainer(array $options, array $definitions): void
    {
        $helper = Mockery::mock('alias:' . Helper::class);
        $helper
            ->shouldReceive('get_option')
            ->with('impressum_imprint_options', true)
            ->andReturn($options);
        $helper
            ->shouldReceive('get_option')
            ->with('impressum_field_data', true)
            ->andReturn([]);

        $plugin = Mockery::mock();
        $plugin
            ->shouldReceive('get_block_fields')
            ->andReturn([]);

        $base = Mockery::mock('alias:' . Setting::class);
        $settings = [];

        foreach ($definitions as $name => $definition) {
            $setting = clone $base;
            $setting->name = $name;
            $setting
                ->shouldReceive('get_data')
                ->with()
                ->andReturn(['hide_output' => $definition['hide_output'] ?? false]);
            $setting
                ->shouldReceive('get_data')
                ->with('conditions')
                ->andReturn($definition['conditions'] ?? []);
            $setting
                ->shouldReceive('get_title')
                ->andReturn($name);
            $settings['impressum_imprint_options_' . $name] = $setting;
        }

        $registry = Mockery::mock('alias:' . Registry::class);
        $registry
            ->shouldReceive('get_setting')
            ->andReturnUsing(static fn (string $name) => $settings[$name] ?? null);

        $container = Mockery::mock('Container');
        $container->shouldReceive('get')->with('helper')->andReturn($helper);
        $container->shouldReceive('get')->with('plugin')->andReturn($plugin);
        $container->shouldReceive('get')->with('settings-registry')->andReturn($registry);
        /** @disregard P1006 */
        $container->shouldReceive('get')->with('settings-validator')->andReturn(new Validator($registry));
        $GLOBALS['impressum_test_container'] = $container;
    }

    public function testRenderSkipsFieldsHiddenByTheirConditions(): void
    {
        $is_company = [
            'visible' => [
                'field' => 'legal_entity',
                'operator' => 'not_in',
                'value' => ['individual', 'self'],
            ],
        ];
        $this->setUpContainer(
            [
                'legal_entity' => 'individual',
                'name' => 'Jane Doe',
                'register' => 'HRB 12345',
            ],
            [
                'legal_entity' => ['hide_output' => true],
                'name' => [],
                'register' => ['conditions' => $is_company],
            ]
        );

        $this->assertSame(
            '<div class="impressum__imprint-container"><dl><dt>name</dt><dd>Jane Doe</dd></dl></div>',
            Frontend::get_instance()->render([])
        );
    }

    public function testRenderOutputsFieldsVisibleByTheirConditions(): void
    {
        $is_company = [
            'visible' => [
                'field' => 'legal_entity',
                'operator' => 'not_in',
                'value' => ['individual', 'self'],
            ],
        ];
        $this->setUpContainer(
            [
                'legal_entity' => 'gmbh',
                'name' => 'Example GmbH',
                'register' => 'HRB 12345',
            ],
            [
                'legal_entity' => ['hide_output' => true],
                'name' => [],
                'register' => ['conditions' => $is_company],
            ]
        );

        $this->assertSame(
            '<div class="impressum__imprint-container"><dl><dt>name</dt><dd>Example GmbH</dd>'
            . '<dt>register</dt><dd>HRB 12345</dd></dl></div>',
            Frontend::get_instance()->render([])
        );
    }

    public function testRenderSkipsThePressLawPersonOutsideOfGermany(): void
    {
        $definitions = [
            'country' => ['hide_output' => true],
            'press_law_checkbox' => ['hide_output' => true],
            'press_law_person' => [
                'conditions' => [
                    'visible' => [
                        'all' => [
                            [
                                'field' => 'country',
                                'operator' => 'in',
                                'value' => ['de-de', 'deu'],
                            ],
                            [
                                'field' => 'press_law_checkbox',
                                'operator' => 'truthy',
                            ],
                        ],
                    ],
                ],
            ],
        ];
        $this->setUpContainer(
            [
                'country' => 'aut',
                'press_law_checkbox' => '1',
                'press_law_person' => 'Jane Doe',
            ],
            $definitions
        );

        $this->assertSame('', Frontend::get_instance()->render(['markup' => 'false']));
    }

    protected function tearDown(): void
    {
        tearDown();
        parent::tearDown();
    }
}
