<?php

declare(strict_types=1);

namespace Tests\Unit\settings;

use epiphyt\Impressum\settings\Registry;
use epiphyt\Impressum\settings\Setting;
use epiphyt\Impressum\settings\Validator;
use Mockery;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\PreserveGlobalState;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;

use function Brain\Monkey\Filters\expectApplied;
use function Brain\Monkey\Functions\stubEscapeFunctions;
use function Brain\Monkey\Functions\stubTranslationFunctions;
use function Brain\Monkey\setUp;
use function Brain\Monkey\tearDown;

#[CoversClass(Validator::class)]
#[RunTestsInSeparateProcesses]
#[PreserveGlobalState(false)]
final class ValidatorTest extends MockeryTestCase
{
    /**
     * The VAT ID pattern as it is shipped to the settings app.
     */
    private const VAT_ID_PATTERN = '^(|ATU[0-9]{8}|BE0[0-9]{9}|BG[0-9]{9,10}|CY[0-9]{8}L|CZ[0-9]{8,10}|DE[0-9]{9}|DK[0-9]{8}|EE[0-9]{9}|(EL|GR)[0-9]{9}|ES[0-9A-Z][0-9]{7}[0-9A-Z]|FI[0-9]{8}|FR[0-9A-Z]{2}[0-9]{9}|GB([0-9]{9}([0-9]{3})?|[A-Z]{2}[0-9]{3})|HU[0-9]{8}|IE[0-9]S[0-9]{5}L|IT[0-9]{11}|LT([0-9]{9}|[0-9]{12})|LU[0-9]{8}|LV[0-9]{11}|MT[0-9]{8}|NL[0-9+*]{9}B[0-9]{2}|PL[0-9]{10}|PT[0-9]{9}|RO[0-9]{2,10}|SE[0-9]{12}|SI[0-9]{8}|SK[0-9]{10})$';

    /**
     * The business ID pattern as it is shipped to the settings app.
     */
    private const BUSINESS_ID_PATTERN = '^(|DE[0-9]{9}-[0-9]{5})$';

    protected function setUp(): void
    {
        parent::setUp();
        setUp();
        stubEscapeFunctions();
        stubTranslationFunctions();
    }

    /**
     * Build a registry of mocked settings.
     *
     * @param array<string, array<string, mixed>> $definitions Setting data keyed by setting name
     */
    private function buildRegistry(array $definitions): Registry
    {
        $base = Mockery::mock('alias:' . Setting::class);
        $settings = [];

        foreach ($definitions as $name => $definition) {
            $setting = clone $base;
            $setting->name = $name;

            foreach (['conditions', 'notices', 'validation'] as $key) {
                $setting
                    ->shouldReceive('get_data')
                    ->with($key)
                    ->andReturn($definition[$key] ?? []);
            }

            $setting
                ->shouldReceive('get_title')
                ->andReturn($definition['title'] ?? $name);
            $settings['group_' . $name] = $setting;
        }

        $registry = Mockery::mock('alias:' . Registry::class);
        $registry
            ->shouldReceive('get_settings')
            ->andReturn($settings);

        /** @disregard P1006 */
        return $registry;
    }

    private function buildValidator(array $definitions = []): Validator
    {
        return new Validator($this->buildRegistry($definitions));
    }

    public static function operatorProvider(): array
    {
        return [
            'is matches' => ['is', 'deu', 'deu', true],
            'is does not match' => ['is', 'aut', 'deu', false],
            'is compares scalars as strings' => ['is', 1, '1', true],
            'is_not matches' => ['is_not', 'aut', 'deu', true],
            'is_not does not match' => ['is_not', 'deu', 'deu', false],
            'in matches' => ['in', 'deu', ['aut', 'deu'], true],
            'in does not match' => ['in', 'che', ['aut', 'deu'], false],
            'not_in matches' => ['not_in', 'che', ['aut', 'deu'], true],
            'not_in does not match' => ['not_in', 'deu', ['aut', 'deu'], false],
            'empty matches' => ['empty', '', null, true],
            'empty does not match' => ['empty', 'value', null, false],
            'falsy matches' => ['falsy', '', null, true],
            'not_empty matches' => ['not_empty', 'value', null, true],
            'not_empty does not match' => ['not_empty', '', null, false],
            'truthy matches' => ['truthy', '1', null, true],
            'truthy does not match' => ['truthy', '', null, false],
            'matches matches' => ['matches', 'DE123456789', '^DE[0-9]{9}$', true],
            'matches does not match' => ['matches', 'DE12345678', '^DE[0-9]{9}$', false],
            'matches with a delimiter' => ['matches', 'https://example.com', '^https?://', true],
            'matches with an escaped delimiter' => ['matches', 'a/b', '^a\/b$', true],
            'matches with an escaped backslash before a delimiter' => ['matches', 'a\\/b', '^a\\\\/b$', true],
            'matches does not match with a delimiter' => ['matches', 'a-b', '^a/b$', false],
        ];
    }

    #[DataProvider('operatorProvider')]
    public function testEvaluateOperators(
        string $operator,
        mixed $actual,
        mixed $expected,
        bool $result
    ): void {
        $condition = [
            'field' => 'field',
            'operator' => $operator,
        ];

        if ($expected !== null) {
            $condition['value'] = $expected;
        }

        $this->assertSame(
            $result,
            $this->buildValidator()->evaluate($condition, ['field' => $actual])
        );
    }

    public function testEvaluateAllRequiresEveryCondition(): void
    {
        $validator = $this->buildValidator();
        $condition = [
            'all' => [
                ['field' => 'country', 'operator' => 'is', 'value' => 'deu'],
                ['field' => 'checkbox', 'operator' => 'truthy'],
            ],
        ];
        $this->assertTrue($validator->evaluate($condition, ['country' => 'deu', 'checkbox' => '1']));
        $this->assertFalse($validator->evaluate($condition, ['country' => 'deu', 'checkbox' => '']));
        $this->assertFalse($validator->evaluate($condition, ['country' => 'aut', 'checkbox' => '1']));
    }

    public function testEvaluateAnyRequiresOneCondition(): void
    {
        $validator = $this->buildValidator();
        $condition = [
            'any' => [
                ['field' => 'phone', 'operator' => 'not_empty'],
                ['field' => 'contact_form_page', 'operator' => 'not_empty'],
            ],
        ];
        $this->assertTrue($validator->evaluate($condition, ['phone' => '0123', 'contact_form_page' => '']));
        $this->assertTrue($validator->evaluate($condition, ['phone' => '', 'contact_form_page' => '2']));
        $this->assertFalse($validator->evaluate($condition, ['phone' => '', 'contact_form_page' => '']));
    }

    public function testEvaluateSupportsNestedGroups(): void
    {
        $condition = [
            'all' => [
                ['field' => 'country', 'operator' => 'in', 'value' => ['de-de', 'deu']],
                [
                    'any' => [
                        ['field' => 'vat_id', 'operator' => 'not_empty'],
                        ['field' => 'business_id', 'operator' => 'not_empty'],
                    ],
                ],
            ],
        ];
        $validator = $this->buildValidator();
        $this->assertTrue($validator->evaluate($condition, ['country' => 'deu', 'business_id' => 'x']));
        $this->assertFalse($validator->evaluate($condition, ['country' => 'deu']));
        $this->assertFalse($validator->evaluate($condition, ['country' => 'aut', 'business_id' => 'x']));
    }

    public function testPressLawPersonVisibility(): void
    {
        $conditions = [
            'visible' => [
                'all' => [
                    ['field' => 'country', 'operator' => 'in', 'value' => ['de-de', 'deu']],
                    ['field' => 'press_law_checkbox', 'operator' => 'truthy'],
                ],
            ],
        ];
        $registry = $this->buildRegistry([
            'press_law_person' => ['conditions' => $conditions],
        ]);
        $validator = new Validator($registry);
        $setting = $registry->get_settings('group')['group_press_law_person'];
        $this->assertTrue($validator->is_visible($setting, ['country' => 'deu', 'press_law_checkbox' => '1']));
        $this->assertFalse($validator->is_visible($setting, ['country' => 'deu', 'press_law_checkbox' => '']));
        $this->assertFalse($validator->is_visible($setting, ['country' => 'aut', 'press_law_checkbox' => '1']));
    }

    public function testSettingWithoutConditionsIsAlwaysVisible(): void
    {
        $registry = $this->buildRegistry(['name' => []]);
        $validator = new Validator($registry);
        $this->assertTrue($validator->is_visible($registry->get_settings('group')['group_name'], []));
    }

    public function testGetRequiredFieldsExcludesClientOnlyRulesByDefault(): void
    {
        $validator = $this->buildValidator([
            'address' => ['validation' => ['required' => true]],
            'country' => ['validation' => ['client_only' => true, 'required' => true]],
            'name' => ['validation' => ['required' => true]],
        ]);
        $this->assertSame(['address', 'name'], $validator->get_required_fields('group', []));
        $this->assertSame(
            ['address', 'country', 'name'],
            $validator->get_required_fields('group', [], true)
        );
    }

    public function testGetRequiredFieldsHonorsRequiredUnless(): void
    {
        $definitions = [
            'contact_form_page' => [
                'validation' => [
                    'required_unless' => [
                        'any' => [['field' => 'phone', 'operator' => 'not_empty']],
                    ],
                ],
            ],
        ];
        $this->assertSame(
            ['contact_form_page'],
            $this->buildValidator($definitions)->get_required_fields('group', ['phone' => ''])
        );
        $this->assertSame(
            [],
            $this->buildValidator($definitions)->get_required_fields('group', ['phone' => '0123'])
        );
    }

    public function testGetRequiredFieldsAppliesFilter(): void
    {
        expectApplied('impressum_required_fields')->once()->andReturnFirstArg();
        $this->buildValidator(['name' => ['validation' => ['required' => true]]])
            ->get_required_fields('group', []);
    }

    public function testGetPairErrorsProducesCombinedTitle(): void
    {
        $definitions = [
            'contact_form_page' => ['title' => 'Contact Form Page'],
            'phone' => [
                'title' => 'Phone',
                'validation' => [
                    'pair_key' => 'phone_contact_form',
                    'pair_title' => '%1$s or %2$s',
                    'pair_with' => 'contact_form_page',
                ],
            ],
        ];
        $this->assertSame(
            ['phone_contact_form' => 'Phone or Contact Form Page'],
            $this->buildValidator($definitions)->get_pair_errors('group', [])
        );
        $this->assertSame(
            [],
            $this->buildValidator($definitions)->get_pair_errors('group', ['phone' => '0123'])
        );
        $this->assertSame(
            [],
            $this->buildValidator($definitions)->get_pair_errors('group', ['contact_form_page' => '2'])
        );
    }

    public static function vatIdProvider(): array
    {
        return [
            'empty is allowed' => ['', true],
            'german' => ['DE123456789', true],
            'austrian' => ['ATU12345678', true],
            'dutch' => ['NL123456789B01', true],
            'greek alternative prefix' => ['EL123456789', true],
            'german with too few digits' => ['DE12345678', false],
            'german with too many digits' => ['DE1234567890', false],
            'unknown country' => ['XX123456789', false],
        ];
    }

    #[DataProvider('vatIdProvider')]
    public function testVatIdPattern(string $value, bool $valid): void
    {
        $validator = $this->buildValidator([
            'vat_id' => [
                'title' => 'VAT ID',
                'validation' => ['pattern' => self::VAT_ID_PATTERN],
            ],
        ]);
        $errors = $validator->get_errors('group', ['vat_id' => $value]);
        $this->assertSame($valid, !isset($errors['vat_id']));
    }

    public static function businessIdProvider(): array
    {
        return [
            'empty is allowed' => ['', true],
            'with prefix' => ['DE123456789-00001', true],
            // the prefix used to be optional server side and is now required
            'without prefix' => ['123456789-00001', false],
            'without dash' => ['DE12345678900001', false],
            'too few digits' => ['DE12345678-00001', false],
        ];
    }

    #[DataProvider('businessIdProvider')]
    public function testBusinessIdPattern(string $value, bool $valid): void
    {
        $validator = $this->buildValidator([
            'business_id' => [
                'title' => 'Business ID',
                'validation' => ['pattern' => self::BUSINESS_ID_PATTERN],
            ],
        ]);
        $errors = $validator->get_errors('group', ['business_id' => $value]);
        $this->assertSame($valid, !isset($errors['business_id']));
    }

    public function testPatternIsNotCheckedForRequiredEmptyFields(): void
    {
        // an empty required field is reported as missing, not as malformed
        $validator = $this->buildValidator([
            'vat_id' => [
                'title' => 'VAT ID',
                'validation' => [
                    'pattern' => self::VAT_ID_PATTERN,
                    'required' => true,
                ],
            ],
        ]);
        $this->assertSame(['vat_id' => 'VAT ID'], $validator->get_errors('group', ['vat_id' => '']));
    }

    public function testGetErrorsSortsByTitleNaturally(): void
    {
        $validator = $this->buildValidator([
            'name' => ['title' => 'Name', 'validation' => ['required' => true]],
            'address' => ['title' => 'Address', 'validation' => ['required' => true]],
            'email' => ['title' => 'Email Address', 'validation' => ['required' => true]],
        ]);
        $this->assertSame(
            ['address' => 'Address', 'email' => 'Email Address', 'name' => 'Name'],
            $validator->get_errors('group', [])
        );
    }

    public function testGetNoticesOnlyReturnsMatchingNotices(): void
    {
        $notice = [
            'id' => 'business-or-vat-id',
            'status' => 'warning',
            'text' => 'Either the business ID or the VAT ID have to be set.',
            'when' => [
                'all' => [['field' => 'country', 'operator' => 'in', 'value' => ['de-de', 'deu']]],
            ],
        ];
        $validator = $this->buildValidator(['vat_id' => ['notices' => [$notice]]]);
        $this->assertSame(
            ['vat_id' => [$notice]],
            $validator->get_notices('group', ['country' => 'deu'])
        );
        $this->assertSame([], $validator->get_notices('group', ['country' => 'aut']));
    }

    public function testNoticeIsHiddenAsSoonAsASiblingHasAValue(): void
    {
        // the business ID and VAT ID hint only helps while neither is filled in
        $notice = [
            'id' => 'business-or-vat-id',
            'status' => 'warning',
            'text' => 'Either the business ID or the VAT ID have to be set.',
            'when' => [
                'all' => [
                    ['field' => 'country', 'operator' => 'in', 'value' => ['de-de', 'deu']],
                    ['field' => 'business_id', 'operator' => 'empty'],
                    ['field' => 'vat_id', 'operator' => 'empty'],
                ],
            ],
        ];
        $validator = $this->buildValidator([
            'business_id' => ['notices' => [$notice]],
            'vat_id' => ['notices' => [$notice]],
        ]);

        $this->assertSame(
            ['business_id', 'vat_id'],
            array_keys($validator->get_notices('group', ['country' => 'deu']))
        );
        $this->assertSame([], $validator->get_notices('group', [
            'business_id' => 'DE123456789-00001',
            'country' => 'deu',
        ]));
        $this->assertSame([], $validator->get_notices('group', [
            'country' => 'deu',
            'vat_id' => 'DE123456789',
        ]));
        $this->assertSame([], $validator->get_notices('group', ['country' => 'aut']));
    }

    public function testNoticeWithoutConditionIsAlwaysActive(): void
    {
        $notice = ['id' => 'always', 'status' => 'info', 'text' => 'Always shown.'];
        $validator = $this->buildValidator(['name' => ['notices' => [$notice]]]);
        $this->assertSame(['name' => [$notice]], $validator->get_notices('group', []));
    }

    protected function tearDown(): void
    {
        tearDown();
        parent::tearDown();
    }
}
