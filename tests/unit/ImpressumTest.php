<?php

declare(strict_types=1);

namespace Tests\Unit;

use epiphyt\Impressum\Helper;
use epiphyt\Impressum\Impressum;
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

#[CoversClass(Impressum::class)]
final class ImpressumTest extends MockeryTestCase
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
        Impressum::get_instance()->init();
        $this->assertSame(10, \has_action('init', '\epiphyt\Impressum\Impressum->load_settings()'));
        $this->assertSame(5, \has_action('init', '\epiphyt\Impressum\Impressum->load_textdomain()'));
        $this->assertSame(10, \has_action(
            'pre_update_option_impressum_imprint_options',
            '\epiphyt\Impressum\Impressum->twice_daily_cron_activation()'
        ));
        // frontend
        $this->assertSame(10, \has_action('init', '\epiphyt\Impressum\Frontend->register_blocks()'));
        // admin
        $this->assertSame(10, \has_action('admin_enqueue_scripts', '\epiphyt\Impressum\Admin->enqueue_assets()'));
        $this->assertSame(10, \has_action('admin_init', '\epiphyt\Impressum\Admin->init_settings()'));
        $this->assertSame(10, \has_action('admin_menu', '\epiphyt\Impressum\Admin->options_page()'));
        $this->assertSame(10, \has_action('admin_notices', '\epiphyt\Impressum\Admin->invalid_notice()'));
        $this->assertSame(10, \has_action('admin_notices', '\epiphyt\Impressum\Admin->welcome_notice()'));
        $this->assertSame(10, \has_action('enqueue_block_editor_assets', '\epiphyt\Impressum\Admin->block_assets()'));
        $this->assertSame(10, \has_action(
            'update_option_impressum_imprint_options',
            '\epiphyt\Impressum\Admin->reset_invalid_notice()'
        ));
        $this->assertSame(10, \has_action(
            'wp_ajax_impressum_dismissed_notice_handler',
            '\epiphyt\Impressum\Admin->ajax_notice_handler()'
        ));
        $this->assertSame(10, \has_filter('impressum_admin_tab', '\epiphyt\Impressum\Admin->register_plus_tab()'));
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
        when('get_permalink')->justReturn('https://www.example.com/permalink');
        Impressum::get_instance()->settings_fields = [
            'legal_entity' => [
                'api' => [
                    'description' => \esc_html__('The legal entity.', 'impressum'),
                    'enum' => [
                        'ag' => \__('AG', 'impressum'),
                        'eg' => \__('eG', 'impressum'),
                        'einzelkaufmann' => \__('Einzelkaufmann', 'impressum'),
                        'ek' => \__('e.K.', 'impressum'),
                        'ev' => \__('e.V.', 'impressum'),
                        'freelancer' => \__('Freelancer', 'impressum'),
                        'gbr' => \__('GbR', 'impressum'),
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
                    'type' => 'string',
                ],
                'args' => [
                    'class' => 'impressum_row',
                    'label_for' => 'legal_entity',
                    'required' => true,
                ],
                'callback' => 'legal_entity',
                'no_output' => true,
                'page' => 'impressum_imprint',
                'section' => 'impressum_section_imprint',
                'title' => \__('Legal Entity', 'impressum'),
            ],
            'contact_form_page' => [
                'api' => [
                    'description' => \esc_html__('The contact form page ID.', 'impressum'),
                    'type' => 'integer',
                ],
                'args' => [
                    'class' => 'impressum_row',
                    'description' => \__('Since you need a fast contact possibility, you either have to publish your phone number or have a contact form where you can respond within 1 hour.', 'impressum'), // phpcs:ignore Generic.Files.LineLength.TooLong
                    'label_for' => 'contact_form_page',
                ],
                'callback' => 'page',
                'field_title' => \__('Contact', 'impressum'),
                'option' => 'impressum_imprint_options',
                'page' => 'impressum_imprint',
                'section' => 'impressum_section_imprint',
                'title' => \__('Contact Form Page', 'impressum'),
            ],
        ];
        $this->assertEqualsCanonicalizing(
            [
                'legal_entity' => [
                    'field_title' => '',
                    'title' => 'Legal Entity',
                    'value' => '',
                ],
                'contact_form_page' => [
                    'field_title' => 'Contact',
                    'title' => 'Contact Form Page',
                    'value' => '',
                ],
            ],
            Impressum::get_instance()->get_block_fields('impressum_imprint_options')
        );
        $this->assertEqualsCanonicalizing(
            [
                'legal_entity' => [
                    'field_title' => '',
                    'title' => 'Legal Entity',
                    'value' => 'individual',
                ],
                'contact_form_page' => [
                    'field_title' => 'Contact',
                    'title' => 'Contact Form Page',
                    'value' => 'https://www.example.com/permalink',
                ],
            ],
            Impressum::get_instance()->get_block_fields('impressum_imprint_options')
        );
    }

    public function testGetCountries(): void
    {
        Impressum::get_instance()->load_textdomain();
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
            Impressum::get_instance()->get_countries()
        );
        $this->assertTrue(applied('impressum_country_pre_sort') === 1);
        $this->assertTrue(applied('impressum_country_after_sort') === 1);
    }

    public function testGetLegalEntities(): void
    {
        Impressum::get_instance()->load_textdomain();
        $this->assertEqualsCanonicalizing(
            [
                'ag' => \__('AG', 'impressum'),
                'eg' => \__('eG', 'impressum'),
                'einzelkaufmann' => \__('Einzelkaufmann', 'impressum'),
                'ek' => \__('e.K.', 'impressum'),
                'ev' => \__('e.V.', 'impressum'),
                'freelancer' => \__('Freelancer', 'impressum'),
                'gbr' => \__('GbR', 'impressum'),
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
            Impressum::get_instance()->get_legal_entities()
        );
        $this->assertTrue(applied('impressum_legal_entity_pre_sort') === 1);
        $this->assertTrue(applied('impressum_legal_entity_after_sort') === 1);
    }

    public function testLoadSettings(): void
    {
        expectApplied('impressum_settings_fields')->once()->andReturnFirstArg();
        Impressum::get_instance()->load_settings();
        $this->assertTrue(applied('impressum_settings_fields') === 1);
    }

    public function testLoadTextdomain(): void
    {
        expectApplied('impressum_country_after_sort')->once()->andReturnFirstArg();
        expectApplied('impressum_legal_entity_after_sort')->once()->andReturnFirstArg();
        Impressum::get_instance()->load_textdomain();
        $this->assertTrue(applied('impressum_country_after_sort') === 1);
        $this->assertTrue(applied('impressum_country_pre_sort') === 1);
        $this->assertTrue(applied('impressum_legal_entity_after_sort') === 1);
        $this->assertTrue(applied('impressum_legal_entity_pre_sort') === 1);
    }

    public function testTwiceDailyCronActivation(): void
    {
        when('wp_next_scheduled')->justReturn(true);
        $this->assertEqualsCanonicalizing(
            [],
            Impressum::get_instance()->twice_daily_cron_activation()
        );
        when('wp_next_scheduled')->justReturn(false);
        when('wp_schedule_event')->justReturn();
        $this->assertEqualsCanonicalizing(
            ['test'],
            Impressum::get_instance()->twice_daily_cron_activation(['test'])
        );
    }

    protected function tearDown(): void
    {
        tearDown();
        parent::tearDown();
    }
}
