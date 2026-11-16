<?php

declare(strict_types=1);

namespace Tests\Unit\settings;

use epiphyt\Impressum\settings\Legacy_Renderer;
use epiphyt\Impressum\settings\Registry;
use epiphyt\Impressum\settings\Setting;
use epiphyt\Impressum\settings\Tabs;
use Mockery;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\PreserveGlobalState;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;

use function Brain\Monkey\Filters\expectApplied;
use function Brain\Monkey\Functions\stubEscapeFunctions;
use function Brain\Monkey\Functions\stubTranslationFunctions;
use function Brain\Monkey\setUp;
use function Brain\Monkey\tearDown;

#[CoversClass(Tabs::class)]
#[RunTestsInSeparateProcesses]
#[PreserveGlobalState(false)]
final class TabsTest extends MockeryTestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        setUp();
        stubEscapeFunctions();
        stubTranslationFunctions();
    }

    /**
     * @param array<string, string> $settings Section ID keyed by setting name
     * @param array                 $legacy   Tabs the legacy renderer returns
     */
    private function buildTabs(array $settings = [], array $legacy = []): Tabs
    {
        $base = Mockery::mock('alias:' . Setting::class);
        $registered = [];

        foreach ($settings as $name => $section) {
            $setting = clone $base;
            $setting->name = $name;
            $setting->type = 'impressum_imprint_options';
            $setting
                ->shouldReceive('get_data')
                ->with('setting_page')
                ->andReturn('impressum_imprint');
            $setting
                ->shouldReceive('get_data')
                ->with('setting_section')
                ->andReturn($section);
            $registered['impressum_imprint_options_' . $name] = $setting;
        }

        $registry = Mockery::mock('alias:' . Registry::class);
        $registry->shouldReceive('get_settings')->andReturn($registered);

        $legacy_renderer = Mockery::mock('alias:' . Legacy_Renderer::class);
        $legacy_renderer->shouldReceive('get_tabs')->andReturn($legacy);

        /** @disregard P1006 */
        return new Tabs($registry, $legacy_renderer);
    }

    public function testImprintTabIsNative(): void
    {
        $tabs = $this->buildTabs()->get_tabs();
        $this->assertSame('imprint', $tabs[0]['slug']);
        $this->assertSame('native', $tabs[0]['type']);
        $this->assertSame('', $tabs[0]['html']);
    }

    public function testSectionsAreDerivedFromTheRegistry(): void
    {
        // the settings API is unavailable outside of an admin request
        $tabs = $this->buildTabs([
            'address' => 'impressum_section_imprint',
            'name' => 'impressum_section_imprint',
            'vat_id' => 'impressum_section_tax',
        ])->get_tabs();
        $sections = $tabs[0]['sections'];
        $this->assertCount(2, $sections);
        $this->assertSame('impressum_section_imprint', $sections[0]['id']);
        $this->assertSame(
            ['impressum_imprint_options_address', 'impressum_imprint_options_name'],
            $sections[0]['fields']
        );
        $this->assertSame('impressum_section_tax', $sections[1]['id']);
    }

    public function testTabsCanBeRegisteredThroughTheFilter(): void
    {
        expectApplied('impressum_settings_tabs')->once()->andReturnUsing(
            static function (array $tabs): array {
                $tabs[] = [
                    'priority' => 20,
                    'slug' => 'privacy',
                    'title' => 'Privacy',
                ];

                return $tabs;
            }
        );
        $tabs = $this->buildTabs()->get_tabs();
        $this->assertCount(2, $tabs);
        $this->assertSame('privacy', $tabs[1]['slug']);
        $this->assertSame('native', $tabs[1]['type']);
    }

    public function testTabsAreOrderedByPriority(): void
    {
        expectApplied('impressum_settings_tabs')->once()->andReturnUsing(
            static function (array $tabs): array {
                $tabs[] = ['priority' => 5, 'slug' => 'first', 'title' => 'First'];

                return $tabs;
            }
        );
        $this->assertSame(
            ['first', 'imprint'],
            array_column($this->buildTabs()->get_tabs(), 'slug')
        );
    }

    public function testMarkupTabsAreAppendedAsLegacy(): void
    {
        $tabs = $this->buildTabs([], [
            ['content' => '<p>Get Plus</p>', 'slug' => 'get_plus', 'title' => 'Get Plus'],
        ])->get_tabs();
        $this->assertCount(2, $tabs);
        $this->assertSame('get_plus', $tabs[1]['slug']);
        $this->assertSame('legacy', $tabs[1]['type']);
        $this->assertSame('<p>Get Plus</p>', $tabs[1]['html']);
    }

    public function testMarkupTabDoesNotOverrideAStructuredTab(): void
    {
        // a tab registered both ways keeps its structured registration
        $tabs = $this->buildTabs([], [
            ['content' => '<p>Old</p>', 'slug' => 'imprint', 'title' => 'Imprint'],
        ])->get_tabs();
        $this->assertCount(1, $tabs);
        $this->assertSame('native', $tabs[0]['type']);
    }

    protected function tearDown(): void
    {
        tearDown();
        parent::tearDown();
    }
}
