<?php

declare(strict_types=1);

namespace Tests\Unit\settings;

use epiphyt\Impressum\Helper;
use epiphyt\Impressum\settings\Legacy_Renderer;
use epiphyt\Impressum\settings\Registry;
use epiphyt\Impressum\settings\Schema;
use epiphyt\Impressum\settings\Setting;
use epiphyt\Impressum\settings\Tabs;
use epiphyt\Impressum\settings\Validator;
use Mockery;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\PreserveGlobalState;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;

use function Brain\Monkey\Filters\expectApplied;
use function Brain\Monkey\Functions\expect;
use function Brain\Monkey\Functions\stubEscapeFunctions;
use function Brain\Monkey\Functions\stubs;
use function Brain\Monkey\Functions\stubTranslationFunctions;
use function Brain\Monkey\Functions\when;
use function Brain\Monkey\setUp;
use function Brain\Monkey\tearDown;

#[CoversClass(Schema::class)]
#[RunTestsInSeparateProcesses]
#[PreserveGlobalState(false)]
final class SchemaTest extends MockeryTestCase
{
    private string $scope = 'site';

    protected function setUp(): void
    {
        parent::setUp();
        setUp();
        stubEscapeFunctions();
        stubTranslationFunctions();
        stubs([
            'get_pages' => [],
            'get_post' => null,
            'has_filter' => false,
            'is_multisite' => false,
            'sanitize_text_field' => static fn ($value) => $value,
            'wp_unslash' => static fn ($value) => $value,
        ]);
        when('wp_count_posts')->alias(
            static fn (): object => (object) ['draft' => 0, 'publish' => \count(\get_pages())]
        );
        unset($_SERVER['HTTP_ACCEPT_LANGUAGE']);
    }

    /**
     * @param array<string, array<string, mixed>> $definitions Setting data keyed by setting name
     */
    private function buildSchema(array $definitions, array|\Closure $options = []): Schema
    {
        $base = Mockery::mock('alias:' . Setting::class);
        $settings = [];

        foreach ($definitions as $name => $definition) {
            $setting = clone $base;
            $setting->name = $name;
            $setting->type = 'impressum_imprint_options';

            $data = $definition + [
                'conditions' => [],
                'custom_title' => null,
                'data_type' => [],
                'description' => '',
                'field_title' => null,
                'hide_output' => false,
                'network_supported' => true,
                'notices' => [],
                'setting_attributes' => [],
                'setting_callback' => 'text',
                'setting_page' => 'impressum_imprint',
                'setting_section' => 'impressum_section_imprint',
                'title' => $name,
                'validation' => [],
            ];

            foreach ($data as $key => $value) {
                $setting
                    ->shouldReceive('get_data')
                    ->with($key)
                    ->andReturn($value);
            }

            $setting
                ->shouldReceive('get_title')
                ->andReturn($data['title']);
            $settings['impressum_imprint_options_' . $name] = $setting;
        }

        $registry = Mockery::mock('alias:' . Registry::class);
        $registry->shouldReceive('get_settings')->andReturn($settings);
        $registry
            ->shouldReceive('get_setting_types')
            ->andReturn(['impressum_imprint_options']);

        $helper = Mockery::mock('alias:' . Helper::class);
        $helper
            ->shouldReceive('get_option')
            ->andReturnUsing(
                fn (string $option): mixed => $options instanceof \Closure ? $options($option, $this->scope) : $options
            );
        $helper
            ->shouldReceive('with_scope')
            ->andReturnUsing(function (string $scope, callable $callback): mixed {
                $previous = $this->scope;
                $this->scope = $scope;

                try {
                    return $callback();
                } finally {
                    $this->scope = $previous;
                }
            });

        $plugin = Mockery::mock('Plugin');
        $plugin
            ->shouldReceive('get_countries')
            ->andReturn(['deu' => ['locale' => 'de-de', 'locale_primary' => 'de', 'title' => 'Germany']]);
        $plugin
            ->shouldReceive('get_legal_entities')
            ->andReturn(['ag' => 'AG']);

        $container = Mockery::mock('Container');
        $container->shouldReceive('get')->with('plugin')->andReturn($plugin);
        $GLOBALS['impressum_test_container'] = $container;

        // both are final, so they can only be replaced through an alias mock
        $legacy_renderer = Mockery::mock('alias:' . Legacy_Renderer::class);
        $legacy_renderer->shouldReceive('render_field')->andReturn('<span>legacy</span>');
        $legacy_renderer->shouldReceive('get_legacy_payload')->andReturn(['fields' => []]);

        $validator = Mockery::mock('alias:' . Validator::class);
        $validator->shouldReceive('get_errors')->andReturn([]);

        $tabs = Mockery::mock('alias:' . Tabs::class);
        $tabs->shouldReceive('get_tabs')->andReturn([]);

        /** @disregard P1006 */
        return new Schema($registry, $validator, $legacy_renderer, $tabs);
    }

    private function getField(Schema $schema, string $name, array $args = []): array
    {
        return $schema->get_payload($args)['fields']['impressum_imprint_options_' . $name];
    }

    public function testPayloadCarriesTheExpectedTopLevelKeys(): void
    {
        $payload = $this->buildSchema(['name' => []])->get_payload();
        // the key order follows the coding standard's sorting, so only the set matters
        $this->assertEqualsCanonicalizing(
            [
                'choices',
                'defaultTab',
                'defaults',
                'fieldData',
                'fieldDataWritable',
                'fields',
                'formAction',
                'groups',
                'i18n',
                'isNetwork',
                'legacy',
                'nonce',
                'optionPage',
                'referer',
                'tabs',
                'validation',
                'values',
                'version',
            ],
            array_keys($payload)
        );
    }

    public function testFieldsAreKeyedByTypeAndName(): void
    {
        $payload = $this->buildSchema(['vat_id' => []])->get_payload();
        $this->assertArrayHasKey('impressum_imprint_options_vat_id', $payload['fields']);
    }

    public function testSettingCallbackBecomesTheFieldType(): void
    {
        $schema = $this->buildSchema(['name' => ['setting_callback' => 'textarea']]);
        $field = $this->getField($schema, 'name');
        $this->assertSame('textarea', $field['type']);
        $this->assertTrue($field['isNative']);
        $this->assertNull($field['legacyHtml']);
    }

    public function testUnknownCallbackIsRenderedServerSide(): void
    {
        $schema = $this->buildSchema(['license_key' => ['setting_callback' => 'license_key']]);
        $field = $this->getField($schema, 'license_key');
        $this->assertFalse($field['isNative']);
        $this->assertSame('<span>legacy</span>', $field['legacyHtml']);
        // the action fires inside the legacy markup, so it is not captured twice
        $this->assertSame('', $field['descriptionHtml']);
    }

    public function testFieldControlDescriptorMakesAFieldNative(): void
    {
        // a field type the app does not know can describe itself instead
        expectApplied('impressum_settings_field_control')->andReturn([
            'options' => [['label' => 'Yes', 'value' => 'yes']],
            'type' => 'radio',
        ]);
        $field = $this->getField(
            $this->buildSchema(['incompatibilities' => ['setting_callback' => 'radio']]),
            'incompatibilities'
        );
        $this->assertTrue($field['isNative']);
        $this->assertNull($field['legacyHtml']);
        $this->assertSame('radio', $field['control']['type']);
    }

    public function testFieldControlDescriptorNeedsAType(): void
    {
        expectApplied('impressum_settings_field_control')->andReturn(['options' => []]);
        $field = $this->getField(
            $this->buildSchema(['license_key' => ['setting_callback' => 'license_key']]),
            'license_key'
        );
        $this->assertNull($field['control']);
        $this->assertFalse($field['isNative']);
    }

    public function testNativeFieldTypesAreFilterable(): void
    {
        expectApplied('impressum_native_field_types')->andReturn(['text']);
        $schema = $this->buildSchema(['name' => ['setting_callback' => 'textarea']]);
        $this->assertFalse($this->getField($schema, 'name')['isNative']);
    }

    public function testPlaceholderComesFromTheNetworkDefault(): void
    {
        $schema = $this->buildSchema(
            ['name' => ['setting_attributes' => ['label_for' => 'name']]],
            ['default' => ['name' => 'Example GmbH']]
        );
        $this->assertSame('Example GmbH', $this->getField($schema, 'name')['placeholder']);
    }

    public function testPlaceholderIsEmptyInNetworkScope(): void
    {
        $schema = $this->buildSchema(
            ['name' => ['setting_attributes' => ['label_for' => 'name']]],
            ['default' => ['name' => 'Example GmbH']]
        );
        $field = $this->getField($schema, 'name', ['is_network' => true]);
        $this->assertSame('', $field['placeholder']);
    }

    public function testPlaceholderComesFromTheNetworkOptionOnMultisite(): void
    {
        when('is_multisite')->justReturn(true);
        $schema = $this->buildSchema(
            ['name' => ['setting_attributes' => ['label_for' => 'name']]],
            static fn (string $option, string $scope): array => $scope === 'network'
                ? ['name' => 'Network GmbH']
                : ['name' => '']
        );
        $payload = $schema->get_payload();
        $this->assertSame('Network GmbH', $payload['fields']['impressum_imprint_options_name']['placeholder']);
        $this->assertSame(['name' => 'Network GmbH'], $payload['defaults']['impressum_imprint_options']);
        $this->assertSame(['name' => ''], $payload['values']['impressum_imprint_options']);
    }

    public function testNetworkScopeReadsTheNetworkOption(): void
    {
        when('is_multisite')->justReturn(true);
        $schema = $this->buildSchema(
            ['name' => []],
            static fn (string $option, string $scope): array => ['name' => $scope]
        );
        $this->assertSame(
            ['name' => 'network'],
            $schema->get_payload(['is_network' => true])['values']['impressum_imprint_options']
        );
        $this->assertSame(
            ['name' => 'network'],
            $schema->get_values_payload(['is_network' => true])['values']['impressum_imprint_options']
        );
        $this->assertSame(
            ['name' => 'site'],
            $schema->get_values_payload()['values']['impressum_imprint_options']
        );
    }

    public function testMissingOptionIsEmpty(): void
    {
        $schema = $this->buildSchema(['name' => []], static fn (): bool => false);
        $payload = $schema->get_payload();
        $this->assertSame([], $payload['values']['impressum_imprint_options']);
        $this->assertSame([], $payload['fieldData']);
    }

    public function testTextareaPlaceholderReplacesLineBreaksWithCommas(): void
    {
        $schema = $this->buildSchema(
            [
                'address' => [
                    'setting_attributes' => ['label_for' => 'address'],
                    'setting_callback' => 'textarea',
                ],
            ],
            ['default' => ['address' => "Street 1\r\n12345 City"]]
        );
        $this->assertSame(
            'Street 1, 12345 City',
            $this->getField($schema, 'address')['placeholder']
        );
    }

    public function testDefaultsAreNotExposedAsValues(): void
    {
        $schema = $this->buildSchema(
            ['name' => []],
            ['default' => ['name' => 'Network'], 'name' => 'Local']
        );
        $payload = $schema->get_payload();
        $this->assertSame(['name' => 'Local'], $payload['values']['impressum_imprint_options']);
        $this->assertSame(['name' => 'Network'], $payload['defaults']['impressum_imprint_options']);
    }

    public static function choicesReferenceProvider(): array
    {
        return [
            'country' => ['country', 'country'],
            'legal entity' => ['legal_entity', 'legalEntity'],
            'page' => ['page', 'pages'],
            'text has none' => ['text', null],
        ];
    }

    #[DataProvider('choicesReferenceProvider')]
    public function testChoicesAreReferencedInsteadOfInlined(string $type, ?string $reference): void
    {
        $schema = $this->buildSchema(['field' => ['setting_callback' => $type]]);
        $this->assertSame($reference, $this->getField($schema, 'field')['choicesRef']);
    }

    public function testInputNameMatchesTheOptionArrayKey(): void
    {
        $schema = $this->buildSchema(
            ['address' => ['setting_attributes' => ['label_for' => 'address']]]
        );
        $this->assertSame(
            'impressum_imprint_options[address]',
            $this->getField($schema, 'address')['inputName']
        );
    }

    public function testInvalidValuesAreReportedPerSetting(): void
    {
        when('get_pages')->justReturn([(object) ['ID' => 2, 'post_title' => 'Imprint']]);

        $schema = $this->buildSchema([
            'country' => ['data_type' => ['enum' => ['deu'], 'type' => 'string']],
            'name' => ['data_type' => ['type' => 'string']],
            'page' => ['data_type' => ['type' => 'integer'], 'setting_callback' => 'page'],
            'press_law_checkbox' => ['data_type' => ['type' => 'boolean']],
        ]);
        $invalid = $schema->get_invalid_values('impressum_imprint_options', [
            'country' => 'xx',
            'name' => 'anything goes',
            'page' => '999',
            'press_law_checkbox' => 'maybe',
        ]);

        // every invalid setting is reported, not just the first
        $this->assertSame(
            ['country', 'page', 'press_law_checkbox'],
            array_keys($invalid)
        );
        $this->assertSame('impressum_invalid_page', $invalid['page']['code']);
        $this->assertSame('impressum_invalid_choice', $invalid['country']['code']);
        // the message is about the value, never about the parameter path
        foreach ($invalid as $error) {
            $this->assertStringNotContainsString('values[', $error['message']);
        }
    }

    public function testEmptyAndValidValuesAreAccepted(): void
    {
        when('get_pages')->justReturn([(object) ['ID' => 2, 'post_title' => 'Imprint']]);

        $schema = $this->buildSchema([
            'country' => ['data_type' => ['enum' => ['deu'], 'type' => 'string']],
            'page' => ['data_type' => ['type' => 'integer'], 'setting_callback' => 'page'],
            'press_law_checkbox' => ['data_type' => ['type' => 'boolean']],
        ]);
        $this->assertSame([], $schema->get_invalid_values('impressum_imprint_options', [
            'country' => '',
            'page' => '',
            'press_law_checkbox' => '',
        ]));
        $this->assertSame([], $schema->get_invalid_values('impressum_imprint_options', [
            'country' => 'deu',
            'page' => 2,
            'press_law_checkbox' => '1',
        ]));
    }

    public function testUnregisteredValuesAreNotRejected(): void
    {
        // they are dropped while saving instead
        $schema = $this->buildSchema(['name' => []]);
        $this->assertSame(
            [],
            $schema->get_invalid_values('impressum_imprint_options', ['nope' => 'x'])
        );
    }

    public function testValuesPayloadCarriesNoSchema(): void
    {
        // the settings cannot change through a write, so a write does not repeat them
        $payload = $this->buildSchema(['name' => []])->get_values_payload();
        $this->assertSame(
            ['defaults', 'fieldData', 'validation', 'values'],
            array_keys($payload)
        );
    }

    public function testPageSettingOnlyAcceptsAnExistingPage(): void
    {
        when('get_pages')->justReturn([
            (object) ['ID' => 2, 'post_title' => 'Imprint'],
        ]);
        $schema = $this->buildSchema([
            'page' => ['data_type' => ['type' => 'integer'], 'setting_callback' => 'page'],
        ])->get_json_schema('impressum_imprint_options');
        // both forms of an ID, since the value is matched before it is converted
        $this->assertSame(['', 2], $schema['page']['enum']);
        $this->assertSame(['integer', 'string'], $schema['page']['type']);
    }

    public function testPagesAreNotLoadedWhenThereAreTooManyPages(): void
    {
        when('wp_count_posts')->justReturn((object) ['draft' => 1, 'publish' => 1]);
        when('apply_filters')->alias(
            static fn (string $hook, $value) => $hook === 'impressum_page_choices_limit' ? 1 : $value
        );
        expect('get_pages')->never();
        $payload = $this->buildSchema(['name' => []])->get_payload();
        $this->assertNull($payload['choices']['pages']);
    }

    public function testPageIsCheckedWithoutAnEmbeddedList(): void
    {
        $this->definePostClass();
        when('wp_count_posts')->justReturn((object) ['draft' => 0, 'publish' => 500]);
        when('get_post')->alias(static fn (int $id): ?\WP_Post => match ($id) {
            2 => new \WP_Post(2, 'page', 'publish', 'Imprint'),
            3 => new \WP_Post(3, 'post', 'publish', 'A post'),
            4 => new \WP_Post(4, 'page', 'private', 'Private'),
            default => null,
        });
        $schema = $this->buildSchema([
            'page' => ['data_type' => ['type' => 'integer'], 'setting_callback' => 'page'],
        ]);
        $this->assertSame([], $schema->get_invalid_values('impressum_imprint_options', ['page' => '2']));

        foreach (['3', '4', '999'] as $page_id) {
            $this->assertSame(
                'impressum_invalid_page',
                $schema->get_invalid_values('impressum_imprint_options', ['page' => $page_id])['page']['code']
            );
        }
    }

    public function testSelectedPageIsLabeled(): void
    {
        $this->definePostClass();
        when('get_post')->alias(
            static fn (int $id): ?\WP_Post => $id === 2 ? new \WP_Post(2, 'page', 'draft', 'Contact') : null
        );
        $schema = $this->buildSchema(
            ['page' => ['setting_attributes' => ['label_for' => 'page'], 'setting_callback' => 'page']],
            ['page' => '2']
        );
        $this->assertSame('Contact', $this->getField($schema, 'page')['valueLabel']);
        $this->assertNull($this->getField($schema, 'page', ['is_network' => true])['valueLabel']);
    }

    private function definePostClass(): void
    {
        if (\class_exists('WP_Post')) {
            return;
        }

        // phpcs:ignore Squiz.PHP.Eval.Discouraged
        eval('final class WP_Post {
            public function __construct(
                public int $ID,
                public string $post_type,
                public string $post_status,
                public string $post_title
            ) {}
        }');
    }

    public function testPageSettingIsNotEnumeratedWhenThereAreTooManyPages(): void
    {
        when('get_pages')->justReturn([
            (object) ['ID' => 2, 'post_title' => 'Imprint'],
            (object) ['ID' => 3, 'post_title' => 'Contact'],
        ]);
        when('apply_filters')->alias(
            static fn (string $hook, $value) => $hook === 'impressum_page_choices_limit' ? 1 : $value
        );
        $schema = $this->buildSchema([
            'page' => ['data_type' => ['type' => 'integer'], 'setting_callback' => 'page'],
        ])->get_json_schema('impressum_imprint_options');
        $this->assertArrayNotHasKey('enum', $schema['page']);
        $this->assertSame('^[0-9]*$', $schema['page']['pattern']);
    }

    public function testJsonSchemaConstrainsAStringToTheDeclaredType(): void
    {
        // the string branch exists to allow an empty value, not any value
        $schema = $this->buildSchema([
            'free_text' => ['data_type' => ['type' => 'string']],
            'is_active' => ['data_type' => ['type' => 'boolean']],
            'retention' => ['data_type' => ['type' => 'number']],
        ])->get_json_schema('impressum_imprint_options');
        $this->assertSame('^(0|1)?$', $schema['is_active']['pattern']);
        $this->assertSame(['boolean', 'string'], $schema['is_active']['type']);
        $this->assertSame('^-?[0-9]*(\\.[0-9]+)?$', $schema['retention']['pattern']);
        $this->assertArrayNotHasKey('pattern', $schema['free_text']);
        $this->assertSame(['string', 'array'], $schema['free_text']['type']);
    }

    public function testJsonSchemaAllowsEmptyValues(): void
    {
        // an unset setting submits an empty value, whatever type it declares
        $schema = $this->buildSchema([
            'country' => [
                'data_type' => ['enum' => ['aut', 'deu'], 'type' => 'string'],
                'description' => 'The country.',
            ],
            'page' => ['data_type' => ['type' => 'integer']],
        ]);
        $this->assertSame(
            [
                'country' => [
                    'description' => 'The country.',
                    'enum' => ['', 'aut', 'deu'],
                    'type' => 'string',
                ],
                'page' => [
                    'description' => '',
                    'pattern' => '^[0-9]*$',
                    'type' => ['integer', 'string'],
                ],
            ],
            $schema->get_json_schema('impressum_imprint_options')
        );
    }

    public function testPayloadIsJsonEncodable(): void
    {
        // a closure or resource leaking in through a filter would break the page
        $payload = $this->buildSchema(['name' => []])->get_payload();
        $this->assertIsString(json_encode($payload));
    }

    public function testPayloadFilterIsApplied(): void
    {
        expectApplied('impressum_settings_payload')->once()->andReturnFirstArg();
        $this->buildSchema(['name' => []])->get_payload();
    }

    public static function guessCountryProvider(): array
    {
        return [
            'german locale' => ['de-DE,de;q=0.9', 'deu'],
            'primary locale only' => ['de;q=0.9', 'deu'],
            'country code' => ['deu', 'deu'],
            'unknown language' => ['ja-JP,ja;q=0.9', null],
        ];
    }

    #[DataProvider('guessCountryProvider')]
    public function testGuessCountry(string $accept_language, ?string $expected): void
    {
        $_SERVER['HTTP_ACCEPT_LANGUAGE'] = $accept_language;
        $this->assertSame($expected, Schema::guess_country([
            'aut' => ['locale' => 'de-at'],
            'deu' => ['locale' => 'de-de', 'locale_primary' => 'de'],
            'jpn' => ['locale' => 'ja'],
        ]));
    }

    public function testGuessCountryWithoutHeader(): void
    {
        $this->assertSame(null, Schema::guess_country(['deu' => ['locale_primary' => 'de']]));
    }

    protected function tearDown(): void
    {
        unset($_SERVER['HTTP_ACCEPT_LANGUAGE']);
        tearDown();
        parent::tearDown();
    }
}
