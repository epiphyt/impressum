<?php

declare(strict_types=1);

namespace Tests\Unit;

use epiphyt\Impressum\Helper;
use epiphyt\Impressum\Plugin;
use epiphyt\Impressum\settings\Registry;
use epiphyt\Impressum\settings\Setting;
use epiphyt\Impressum\settings\Validator;
use Mockery;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\PreserveGlobalState;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;

use function Brain\Monkey\Filters\applied;
use function Brain\Monkey\Filters\expectApplied;
use function Brain\Monkey\Functions\stubEscapeFunctions;
use function Brain\Monkey\Functions\stubs;
use function Brain\Monkey\Functions\stubTranslationFunctions;
use function Brain\Monkey\Functions\when;
use function Brain\Monkey\setUp;
use function Brain\Monkey\tearDown;

#[CoversClass(Plugin::class)]
#[RunTestsInSeparateProcesses]
#[PreserveGlobalState(false)]
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
        $this->setUpValidator($plugin);
        $this->assertEqualsCanonicalizing(
            [
                'page' => [
                    'custom_title' => 'Imprint Page',
                    'field_title' => null,
                    'hide_output' => true,
                    'title' => 'Imprint Page',
                    'value' => '',
                ],
                'country' => [
                    'custom_title' => 'Country',
                    'field_title' => null,
                    'hide_output' => true,
                    'title' => 'Country',
                    'value' => '',
                ],
                'legal_entity' => [
                    'custom_title' => 'Legal Entity',
                    'field_title' => null,
                    'hide_output' => true,
                    'title' => 'Legal Entity',
                    'value' => '',
                ],
                'name' => [
                    'custom_title' => 'Name',
                    'field_title' => null,
                    'hide_output' => false,
                    'title' => 'Name',
                    'value' => '',
                ],
                'address' => [
                    'custom_title' => 'Address',
                    'field_title' => null,
                    'hide_output' => false,
                    'title' => 'Address',
                    'value' => '',
                ],
                'address_alternative' => [
                    'custom_title' => 'Alternative Address',
                    'field_title' => 'Address',
                    'hide_output' => false,
                    'title' => 'Address',
                    'value' => '',
                ],
                'email' => [
                    'custom_title' => 'Email Address',
                    'field_title' => null,
                    'hide_output' => false,
                    'title' => 'Email Address',
                    'value' => '',
                ],
                'phone' => [
                    'custom_title' => 'Phone',
                    'field_title' => null,
                    'hide_output' => false,
                    'title' => 'Phone',
                    'value' => '',
                ],
                'contact_form_page' => [
                    'custom_title' => 'Contact Form Page',
                    'field_title' => null,
                    'hide_output' => false,
                    'title' => 'Contact Form Page',
                    'value' => '',
                ],
                'fax' => [
                    'custom_title' => 'Fax',
                    'field_title' => null,
                    'hide_output' => false,
                    'title' => 'Fax',
                    'value' => '',
                ],
                'press_law_checkbox' => [
                    'custom_title' => 'Journalistic/Editorial Content',
                    'field_title' => null,
                    'hide_output' => true,
                    'title' => 'Journalistic/Editorial Content',
                    'value' => '',
                ],
                'press_law_person' => [
                    'custom_title' => 'Responsible for content',
                    'field_title' => null,
                    'hide_output' => true,
                    'title' => 'Responsible for content',
                    'value' => '',
                ],
                'vat_id' => [
                    'custom_title' => 'VAT ID',
                    'field_title' => null,
                    'hide_output' => false,
                    'title' => 'VAT ID',
                    'value' => '',
                ],
                'business_id' => [
                    'custom_title' => 'Business ID',
                    'field_title' => null,
                    'hide_output' => false,
                    'title' => 'Business ID',
                    'value' => '',
                ],
            ],
            $plugin->get_block_fields('impressum_imprint_options')
        );
    }

    public function testGetBlockFieldsHidesFieldsHiddenByTheirConditions(): void
    {
        $base = Mockery::mock('alias:' . Setting::class);
        $definitions = [
            'legal_entity' => [
                'hide_output' => true,
                'value' => 'individual',
            ],
            'name' => [
                'value' => 'Jane Doe',
            ],
            'register' => [
                'conditions' => [
                    'visible' => [
                        'field' => 'legal_entity',
                        'operator' => 'not_in',
                        'value' => ['individual', 'self'],
                    ],
                ],
                'value' => 'HRB 12345',
            ],
        ];
        $settings = [];

        foreach ($definitions as $name => $definition) {
            $setting = clone $base;
            $setting->name = $name;
            $setting->shouldReceive('get_value')->with(true)->andReturn($definition['value']);
            $setting->shouldReceive('get_title')->andReturn($name);
            $setting->shouldReceive('get_data')->with('conditions')->andReturn($definition['conditions'] ?? []);
            $setting->shouldReceive('get_data')->with('field_title')->andReturn(null);
            $setting->shouldReceive('get_data')->with('hide_output')->andReturn($definition['hide_output'] ?? false);
            $setting->shouldReceive('get_data')->with('title')->andReturn($name);
            $settings['impressum_imprint_options_' . $name] = $setting;
        }

        $registry = Mockery::mock('alias:' . Registry::class);
        $registry->shouldReceive('get_settings')->with('impressum_imprint_options')->andReturn($settings);
        /** @disregard P1006 */
        $plugin = new Plugin($registry);
        $this->setUpValidator($plugin);
        $fields = $plugin->get_block_fields('impressum_imprint_options');

        $this->assertTrue($fields['legal_entity']['hide_output']);
        $this->assertFalse($fields['name']['hide_output']);
        $this->assertTrue($fields['register']['hide_output']);
        $this->assertSame('HRB 12345', $fields['register']['value']);
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
                'freelancer-medical' => \__('Freelancer (medical)', 'impressum'),
                'gbr' => \__('GbR', 'impressum'),
                'gesbr' => \__('GesbR', 'impressum'),
                'ggmbh' => \__('gGmbH', 'impressum'),
                'gmbh' => \__('GmbH', 'impressum'),
                'gmbh_co_kg' => \__('GmbH & Co. KG', 'impressum'),
                'individual' => \__('Individual', 'impressum'),
                'kdoer' => \__('KdöR', 'impressum'),
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

    public function testBusinessOrVatIdNoticeIsConditionalOnBothBeingEmpty(): void
    {
        $plugin = $this->getInstantiatedClass();
        $plugin->load_textdomain();
        $plugin->load_settings();

        foreach (['business_id', 'vat_id'] as $name) {
            $conditions = $plugin->settings_fields[$name]['notices'][0]['when']['all'];
            $checked = array_column($conditions, 'operator', 'field');

            $this->assertSame('in', $checked['country']);
            $this->assertSame('empty', $checked['business_id']);
            $this->assertSame('empty', $checked['vat_id']);
        }
    }

    public function testLegalEntityPlusNoticeOnlyAppliesToSelectedPlusEntities(): void
    {
        $plugin = $this->getInstantiatedClass();
        $plugin->load_textdomain();
        $plugin->load_settings();
        $condition = $plugin->settings_fields['legal_entity']['notices'][0]['when'];
        // evaluating a condition doesn't need the registry
        $validator = (new \ReflectionClass(Validator::class))->newInstanceWithoutConstructor();

        $this->assertFalse($validator->evaluate($condition, []));
        $this->assertFalse($validator->evaluate($condition, ['legal_entity' => '']));
        $this->assertFalse($validator->evaluate($condition, ['legal_entity' => 'individual']));
        $this->assertFalse($validator->evaluate($condition, ['legal_entity' => 'self']));
        $this->assertTrue($validator->evaluate($condition, ['legal_entity' => 'ag']));
    }

    public function testValidationPatternsMatchTheLegacyScript(): void
    {
        // the settings app compiles these with RegExp, so they have to stay
        // literally identical to the ones the legacy script used
        $plugin = $this->getInstantiatedClass();
        $plugin->load_textdomain();
        $plugin->load_settings();
        $script = \file_get_contents(__DIR__ . '/../../assets/js/admin-options.js');

        $this->assertIsString($script);
        $this->assertStringContainsString(
            $plugin->settings_fields['vat_id']['validation']['pattern'],
            $script
        );
        $this->assertStringContainsString(
            $plugin->settings_fields['business_id']['validation']['pattern'],
            $script
        );
    }

    protected function tearDown(): void
    {
        tearDown();
        parent::tearDown();
    }

    private function getInstantiatedClass(): Plugin
    {
        $settings_registry = include __DIR__ . '/helpers/SettingsRegistryMock.php';
        /** @disregard P1006 */
        $plugin = new Plugin($settings_registry);

        return $plugin;
    }

    /**
     * Set up a container handing out a validator for the plugin's settings.
     */
    private function setUpValidator(Plugin $plugin): void
    {
        $container = Mockery::mock('Container');
        $container
            ->shouldReceive('get')
            ->with('settings-validator')
            ->andReturn(new Validator($plugin->settings_registry));
        $GLOBALS['impressum_test_container'] = $container;
    }
}
