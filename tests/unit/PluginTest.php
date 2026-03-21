<?php

declare(strict_types=1);

namespace Tests\Unit;

use epiphyt\Impressum\Admin;
use epiphyt\Impressum\blocks\Block_Registry;
use epiphyt\Impressum\Frontend;
use epiphyt\Impressum\Helper;
use epiphyt\Impressum\Plugin;
use epiphyt\Impressum\settings\Registry;
use Mockery;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use PHPUnit\Framework\Attributes\CoversClass;

use function Brain\Monkey\Filters\applied;
use function Brain\Monkey\Filters\expectApplied;
use function Brain\Monkey\Functions\stubEscapeFunctions;
use function Brain\Monkey\Functions\stubs;
use function Brain\Monkey\Functions\stubTranslationFunctions;
use function Brain\Monkey\Functions\when;
use function Brain\Monkey\setUp;
use function Brain\Monkey\tearDown;

#[CoversClass(Plugin::class)]
final class PluginTest extends MockeryTestCase
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
        $plugin = $this->getInstantiatedClass();
        $plugin->init();
        $this->assertSame(10, \has_action('init', '\epiphyt\Impressum\Plugin->load_settings()'));
        $this->assertSame(5, \has_action('init', '\epiphyt\Impressum\Plugin->load_textdomain()'));
        $this->assertSame(10, \has_action(
            'pre_update_option_impressum_imprint_options',
            '\epiphyt\Impressum\Plugin->activate()'
        ));
    }

    public function testActivate(): void
    {
        $plugin = $this->getInstantiatedClass();
        when('wp_next_scheduled')->justReturn(true);
        $this->assertEqualsCanonicalizing(
            [],
            $plugin->activate()
        );
        when('wp_next_scheduled')->justReturn(false);
        when('wp_schedule_event')->justReturn();
        $this->assertEqualsCanonicalizing(
            ['test'],
            $plugin->activate(['test'])
        );
    }

    public function testGetBlockFields(): void
    {
        $helper = Mockery::mock('alias:' . Helper::class);
        $helper->shouldReceive('get_option')->andReturn(
            [],
            [
                'legal_entity' => 'individual',
                'contact_form_page' => 1,
            ]
        );
        $plugin = $this->getInstantiatedClass();
        $this->assertEqualsCanonicalizing(
            [
                'page' => [
                    'custom_title' => 'Imprint Page',
                    'hide_output' => true,
                    'title' => 'Imprint Page',
                    'value' => '',
                ],
                'country' => [
                    'custom_title' => 'Country',
                    'hide_output' => true,
                    'title' => 'Country',
                    'value' => '',
                ],
                'legal_entity' => [
                    'custom_title' => 'Legal Entity',
                    'hide_output' => true,
                    'title' => 'Legal Entity',
                    'value' => '',
                ],
                'name' => [
                    'custom_title' => 'Name',
                    'hide_output' => false,
                    'title' => 'Name',
                    'value' => '',
                ],
                'address' => [
                    'custom_title' => 'Address',
                    'hide_output' => false,
                    'title' => 'Address',
                    'value' => '',
                ],
                'address_alternative' => [
                    'custom_title' => 'Alternative Address',
                    'hide_output' => false,
                    'title' => 'Address',
                    'value' => '',
                ],
                'email' => [
                    'custom_title' => 'Email Address',
                    'hide_output' => false,
                    'title' => 'Email Address',
                    'value' => '',
                ],
                'phone' => [
                    'custom_title' => 'Phone',
                    'hide_output' => false,
                    'title' => 'Phone',
                    'value' => '',
                ],
                'contact_form_page' => [
                    'custom_title' => 'Contact Form Page',
                    'hide_output' => false,
                    'title' => 'Contact Form Page',
                    'value' => '',
                ],
                'fax' => [
                    'custom_title' => 'Fax',
                    'hide_output' => false,
                    'title' => 'Fax',
                    'value' => '',
                ],
                'press_law_checkbox' => [
                    'custom_title' => 'Journalistic/Editorial Content',
                    'hide_output' => true,
                    'title' => 'Journalistic/Editorial Content',
                    'value' => '',
                ],
                'press_law_person' => [
                    'custom_title' => 'Responsible for content',
                    'hide_output' => true,
                    'title' => 'Responsible for content',
                    'value' => '',
                ],
                'vat_id' => [
                    'custom_title' => 'VAT ID',
                    'hide_output' => false,
                    'title' => 'VAT ID',
                    'value' => '',
                ],
                'business_id' => [
                    'custom_title' => 'Business ID',
                    'hide_output' => false,
                    'title' => 'Business ID',
                    'value' => '',
                ],
            ],
            $plugin->get_block_fields('impressum_imprint_options')
        );
    }

    public function testGetCountries(): void
    {
        $plugin = $this->getInstantiatedClass();
        $plugin->load_textdomain();
        $this->assertEqualsCanonicalizing(
            [
                'arg' => [
                    'locale' => 'es-ar',
                    'title' => \__('Argentina', 'impressum'),
                ],
                'aus' => [
                    'locale' => 'en-au',
                    'title' => \__('Australia', 'impressum'),
                ],
                'aut' => [
                    'locale' => 'de-at',
                    'title' => \__('Austria', 'impressum'),
                ],
                'bel' => [
                    'locale' => 'fr-be',
                    'title' => \__('Belgium', 'impressum'),
                ],
                'bgr' => [
                    'locale' => 'bg',
                    'title' => \__('Bulgaria', 'impressum'),
                ],
                'bra' => [
                    'locale' => 'pt-br',
                    'title' => \__('Brazil', 'impressum'),
                ],
                'can' => [
                    'locale' => 'en-ca',
                    'title' => \__('Canada', 'impressum'),
                ],
                'che' => [
                    'locale' => 'de-ch',
                    'title' => \__('Switzerland', 'impressum'),
                ],
                'chl' => [
                    'locale' => 'es-cl',
                    'title' => \__('Chile', 'impressum'),
                ],
                'chn' => [
                    'locale' => 'zh',
                    'title' => \__('China', 'impressum'),
                ],
                'col' => [
                    'locale' => 'es-co',
                    'title' => \__('Columbia', 'impressum'),
                ],
                'cyp' => [
                    'locale' => 'el',
                    'title' => \__('Cyprus', 'impressum'),
                ],
                'cze' => [
                    'locale' => 'cs',
                    'title' => \__('Czech Republic', 'impressum'),
                ],
                'deu' => [
                    'locale' => 'de-de',
                    'locale_primary' => 'de',
                    'title' => \__('Germany', 'impressum'),
                ],
                'dnk' => [
                    'locale' => 'da',
                    'title' => \__('Denmark', 'impressum'),
                ],
                'dza' => [
                    'locale' => 'ar-dz',
                    'title' => \__('Algeria', 'impressum'),
                ],
                'esp' => [
                    'locale' => 'es',
                    'locale_primary' => 'es',
                    'title' => \__('Spain', 'impressum'),
                ],
                'est' => [
                    'locale' => 'et',
                    'title' => \__('Estonia', 'impressum'),
                ],
                'fin' => [
                    'locale' => 'fi',
                    'title' => \__('Finland', 'impressum'),
                ],
                'fra' => [
                    'locale' => 'fr-fr',
                    'locale_primary' => 'fr',
                    'title' => \__('France', 'impressum'),
                ],
                'gbr' => [
                    'locale' => 'en-gb',
                    'title' => \__('United Kingdom', 'impressum'),
                ],
                'grc' => [
                    'locale' => 'gr',
                    'title' => \__('Greece', 'impressum'),
                ],
                'hkg' => [
                    'locale' => 'zh-hans-hk',
                    'title' => \__('Hong Kong', 'impressum'),
                ],
                'hrv' => [
                    'locale' => 'hr',
                    'title' => \__('Croatia', 'impressum'),
                ],
                'hun' => [
                    'locale' => 'hu',
                    'title' => \__('Hungary', 'impressum'),
                ],
                'idn' => [
                    'locale' => 'id',
                    'title' => \__('Indonesia', 'impressum'),
                ],
                'irl' => [
                    'locale' => 'en-ie',
                    'title' => \__('Ireland', 'impressum'),
                ],
                'isr' => [
                    'locale' => 'ar-il',
                    'title' => \__('Israel', 'impressum'),
                ],
                'ita' => [
                    'locale' => 'it',
                    'title' => \__('Italy', 'impressum'),
                ],
                'jpn' => [
                    'locale' => 'ja',
                    'title' => \__('Japan', 'impressum'),
                ],
                'kor' => [
                    'locale' => 'ko-kr',
                    'locale_primary' => 'ko',
                    'title' => \__('South Korea', 'impressum'),
                ],
                'ltu' => [
                    'locale' => 'lt',
                    'title' => \__('Lithuania', 'impressum'),
                ],
                'lux' => [
                    'locale' => 'lb',
                    'title' => \__('Luxembourg', 'impressum'),
                ],
                'lva' => [
                    'locale' => 'lv',
                    'title' => \__('Latvia', 'impressum'),
                ],
                'mlt' => [
                    'locale' => 'mt',
                    'title' => \__('Malta', 'impressum'),
                ],
                'nld' => [
                    'locale' => 'nl',
                    'title' => \__('Netherlands', 'impressum'),
                ],
                'nor' => [
                    'locale' => 'nn',
                    'locale_primary' => 'nb',
                    'title' => \__('Norway', 'impressum'),
                ],
                'nzl' => [
                    'locale' => 'en-nz',
                    'title' => \__('New Zealand', 'impressum'),
                ],
                'other' => [
                    'locale' => 'none',
                    'title' => \__('other', 'impressum'),
                ],
                'pol' => [
                    'locale' => 'pl',
                    'title' => \__('Poland', 'impressum'),
                ],
                'prt' => [
                    'locale' => 'pt-pt',
                    'locale_primary' => 'pt',
                    'title' => \__('Portugal', 'impressum'),
                ],
                'rou' => [
                    'locale' => 'ro',
                    'title' => \__('Romania', 'impressum'),
                ],
                'rus' => [
                    'locale' => 'ru',
                    'title' => \__('Russia', 'impressum'),
                ],
                'srb' => [
                    'locale' => 'sr',
                    'title' => \__('Serbia', 'impressum'),
                ],
                'svk' => [
                    'locale' => 'sk',
                    'title' => \__('Slovakia', 'impressum'),
                ],
                'svn' => [
                    'locale' => 'sl',
                    'title' => \__('Slovenia', 'impressum'),
                ],
                'swe' => [
                    'locale' => 'sv',
                    'title' => \__('Sweden', 'impressum'),
                ],
                'tha' => [
                    'locale' => 'th',
                    'title' => \__('Thailand', 'impressum'),
                ],
                'tur' => [
                    'locale' => 'tr',
                    'title' => \__('Turkey', 'impressum'),
                ],
                'twn' => [
                    'locale' => 'zh-hant-tw',
                    'title' => \__('Taiwan', 'impressum'),
                ],
                'usa' => [
                    'locale' => 'en-us',
                    'locale_primary' => 'en',
                    'title' => \__('United States', 'impressum'),
                ],
                'ven' => [
                    'locale' => 'es-ve',
                    'title' => \__('Venezuela', 'impressum'),
                ],
                'vnm' => [
                    'locale' => 'vi',
                    'title' => \__('Vietnam', 'impressum'),
                ],
                'zaf' => [
                    'locale' => 'en-za',
                    'title' => \__('South Africa', 'impressum'),
                ],
            ],
            $plugin->get_countries()
        );
        $this->assertTrue(applied('impressum_country_pre_sort') === 1);
        $this->assertTrue(applied('impressum_country_after_sort') === 1);
    }

    public function testGetLegalEntities(): void
    {
        $plugin = $this->getInstantiatedClass();
        $plugin->load_textdomain();
        $this->assertEqualsCanonicalizing(
            [
                'ag' => \__('AG', 'impressum'),
                'eg' => \__('eG', 'impressum'),
                'einzelkaufmann' => \__('Einzelkaufmann', 'impressum'),
                'ek' => \__('e.K.', 'impressum'),
                'ev' => \__('e.V.', 'impressum'),
                'freelancer' => \__('Freelancer', 'impressum'),
                'gbr' => \__('GbR', 'impressum'),
                'gesbr' => \__('GesbR', 'impressum'),
                'ggmbh' => \__('gGmbH', 'impressum'),
                'gmbh' => \__('GmbH', 'impressum'),
                'gmbh_co_kg' => \__('GmbH & Co. KG', 'impressum'),
                'individual' => \__('Individual', 'impressum'),
                'kg' => \__('KG', 'impressum'),
                'kgag' => \__('KGaA', 'impressum'),
                'ohg' => \__('OHG', 'impressum'),
                'partnership' => \__('Partnership', 'impressum'),
                'self' => \__('Self-employed', 'impressum'),
                'ug' => \__('UG (haftungsbeschränkt)', 'impressum'),
                'ug_co_kg' => \__('UG (haftungsbeschränkt) & Co. KG', 'impressum'),
            ],
            $plugin->get_legal_entities()
        );
        $this->assertTrue(applied('impressum_legal_entity_pre_sort') === 1);
        $this->assertTrue(applied('impressum_legal_entity_after_sort') === 1);
    }

    public function testLoadSettings(): void
    {
        $plugin = $this->getInstantiatedClass();
        expectApplied('impressum_settings_fields')->once()->andReturnFirstArg();
        $plugin->load_settings();
        $this->assertTrue(applied('impressum_settings_fields') === 1);
    }

    public function testLoadTextdomain(): void
    {
        $plugin = $this->getInstantiatedClass();
        expectApplied('impressum_country_after_sort')->once()->andReturnFirstArg();
        expectApplied('impressum_legal_entity_after_sort')->once()->andReturnFirstArg();
        $plugin->load_textdomain();
        $this->assertTrue(applied('impressum_country_after_sort') === 1);
        $this->assertTrue(applied('impressum_country_pre_sort') === 1);
        $this->assertTrue(applied('impressum_legal_entity_after_sort') === 1);
        $this->assertTrue(applied('impressum_legal_entity_pre_sort') === 1);
    }

    protected function tearDown(): void
    {
        tearDown();
        parent::tearDown();
    }

    private function getInstantiatedClass(): Plugin
    {
        $admin = Mockery::mock('alias:' . Admin::class);
        $admin->shouldReceive('init');
        $block_registry = Mockery::mock('alias:' . Block_Registry::class);
        $block_registry->shouldReceive('init');
        $frontend = Mockery::mock('alias:' . Frontend::class);
        $frontend->shouldReceive('init');
        $settings_registry = include __DIR__ . '/helpers/SettingsRegistryMock.php';
        /** @disregard P1006 */
        $plugin = new Plugin(
            $admin,
            $frontend,
            $block_registry,
            $settings_registry
        );

        return $plugin;
    }
}
