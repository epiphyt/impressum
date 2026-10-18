<?php

declare(strict_types=1);

namespace Tests\Unit\settings {

    use epiphyt\Impressum\settings\Legacy_Renderer;
    use epiphyt\Impressum\settings\Registry;
    use epiphyt\Impressum\settings\Setting;
    use Mockery;
    use Mockery\Adapter\Phpunit\MockeryTestCase;
    use PHPUnit\Framework\Attributes\CoversClass;
    use PHPUnit\Framework\Attributes\PreserveGlobalState;
    use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;

    use function Brain\Monkey\Actions\expectDone;
    use function Brain\Monkey\Filters\expectApplied;
    use function Brain\Monkey\Functions\stubEscapeFunctions;
    use function Brain\Monkey\Functions\stubTranslationFunctions;
    use function Brain\Monkey\setUp;
    use function Brain\Monkey\tearDown;

    #[CoversClass(Legacy_Renderer::class)]
    #[RunTestsInSeparateProcesses]
    #[PreserveGlobalState(false)]
    final class LegacyRendererTest extends MockeryTestCase
    {
        protected function setUp(): void
        {
            parent::setUp();
            setUp();
            stubEscapeFunctions();
            stubTranslationFunctions();
        }

        private function buildSetting(?string $callback, array $attributes = []): Setting
        {
            $setting = Mockery::mock('alias:' . Setting::class);
            $setting->name = 'custom_field';
            $setting->type = 'impressum_imprint_options';
            $setting
                ->shouldReceive('get_data')
                ->with('setting_callback')
                ->andReturn($callback);
            $setting
                ->shouldReceive('get_data')
                ->with('setting_attributes')
                ->andReturn($attributes);

            /** @disregard P1006 */
            return $setting;
        }

        private function buildRenderer(array $settings = []): Legacy_Renderer
        {
            $registry = Mockery::mock('alias:' . Registry::class);
            $registry
                ->shouldReceive('get_settings')
                ->andReturn($settings);

            /** @disregard P1006 */
            return new Legacy_Renderer($registry);
        }

        public function testRenderFieldAppliesCallbackInstanceFilter(): void
        {
            expectApplied('impressum_admin_fields_callback_instance')
                ->once()
                ->andReturn(new FakeCallbackInstance());
            $markup = $this->buildRenderer()->render_field(
                $this->buildSetting('custom_type', ['label_for' => 'custom_field'])
            );
            $this->assertSame('<span>custom_field</span>', $markup);
        }

        public function testRenderFieldReturnsEmptyStringForUnknownCallback(): void
        {
            expectApplied('impressum_admin_fields_callback_instance')
                ->once()
                ->andReturn(new FakeCallbackInstance());
            $this->assertSame(
                '',
                $this->buildRenderer()->render_field($this->buildSetting('does_not_exist'))
            );
        }

        public function testRenderFieldReturnsEmptyStringWithoutCallback(): void
        {
            expectApplied('impressum_admin_fields_callback_instance')
                ->once()
                ->andReturn(new FakeCallbackInstance());
            $this->assertSame('', $this->buildRenderer()->render_field($this->buildSetting(null)));
        }

        public function testGetTabsAppliesAdminTabFilterWithThreeArguments(): void
        {
            expectApplied('impressum_admin_tab')
                ->once()
                ->with(
                    Mockery::type('array'),
                    'https://example.test/wp-admin/options.php',
                    'imprint'
                )
                ->andReturnFirstArg();
            $tabs = $this->buildRenderer()->get_tabs('https://example.test/wp-admin/options.php', 'imprint');
            $this->assertSame('imprint', $tabs[0]['slug']);
            $this->assertSame('', $tabs[0]['content']);
        }

        public function testGetTabsKeepsFilterAddedTabs(): void
        {
            expectApplied('impressum_admin_tab')
                ->once()
                ->andReturnUsing(static function (array $tabs): array {
                    $tabs[] = [
                        'content' => '<p>Privacy</p>',
                        'slug' => 'privacy',
                        'title' => 'Privacy',
                    ];

                    return $tabs;
                });
            $tabs = $this->buildRenderer()->get_tabs('', 'imprint');
            $this->assertCount(2, $tabs);
            $this->assertSame('privacy', $tabs[1]['slug']);
        }

        public function testGetFormHtmlCapturesBothActions(): void
        {
            expectDone('impressum_settings_form_before')->once();
            expectDone('impressum_settings_form_after')->once();
            $this->assertSame(
                ['after' => '', 'before' => ''],
                $this->buildRenderer()->get_form_html('', 'imprint', 'imprint')
            );
        }

        public function testGetLegacyPayloadDoesNotRenderFields(): void
        {
            // field markup is attached to the field itself, not duplicated here
            expectApplied('impressum_admin_tab')->once()->andReturnFirstArg();
            $payload = $this->buildRenderer([
                'impressum_imprint_options_custom_field' => $this->buildSetting('custom_type'),
            ])->get_legacy_payload();
            $this->assertArrayNotHasKey('fields', $payload);
            $this->assertArrayHasKey('form', $payload);
            $this->assertArrayHasKey('tabs', $payload);
        }

        protected function tearDown(): void
        {
            tearDown();
            parent::tearDown();
        }
    }

    final class FakeCallbackInstance
    {
        public function custom_type(array $args): void
        {
            echo '<span>' . ($args['label_for'] ?? '') . '</span>';
        }
    }
}
