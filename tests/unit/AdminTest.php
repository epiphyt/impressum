<?php
declare(strict_types=1);

namespace Tests\Unit;

use epiphyt\Impressum\Admin;
use epiphyt\Impressum\Helper;
use epiphyt\Impressum\Impressum;
use Mockery;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use PHPUnit\Framework\Attributes\CoversClass;

use function Brain\Monkey\Filters\expectApplied;
use function Brain\Monkey\Functions\stubEscapeFunctions;
use function Brain\Monkey\Functions\stubs;
use function Brain\Monkey\Functions\stubTranslationFunctions;
use function Brain\Monkey\setUp;
use function Brain\Monkey\tearDown;

#[CoversClass(Admin::class)]
final class AdminTest extends MockeryTestCase
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
            'sanitize_text_field',
            'wp_unslash'
        ]);
    }

    public function testInit(): void
    {
        Admin::get_instance()->init();
        $this->assertSame(10, \has_action( 'admin_enqueue_scripts', '\epiphyt\Impressum\Admin->enqueue_assets()' ) );
        $this->assertSame(10, \has_action( 'admin_init', '\epiphyt\Impressum\Admin->init_settings()' ) );
        $this->assertSame(10, \has_action( 'admin_menu', '\epiphyt\Impressum\Admin->options_page()' ) );
        $this->assertSame(10, \has_action( 'admin_notices', '\epiphyt\Impressum\Admin->invalid_notice()' ) );
        $this->assertSame(10, \has_action( 'admin_notices', '\epiphyt\Impressum\Admin->welcome_notice()' ) );
        $this->assertSame(10, \has_action( 'enqueue_block_editor_assets', '\epiphyt\Impressum\Admin->block_assets()' ) );
        $this->assertSame(10, \has_action( 'update_option_impressum_imprint_options', '\epiphyt\Impressum\Admin->reset_invalid_notice()' ) );
        $this->assertSame(10, \has_action( 'wp_ajax_impressum_dismissed_notice_handler', '\epiphyt\Impressum\Admin->ajax_notice_handler()' ) );
        $this->assertSame(10, \has_filter( 'impressum_admin_tab', '\epiphyt\Impressum\Admin->register_plus_tab()' ) );
    }
    
    public function testGetInvalidFields(): void
    {
        $helper = Mockery::mock('alias:' . Helper::class);
        $helper->shouldReceive('get_option')->andReturn(
            [],
            [
                'legal_entity' => 'individual',
                'contact_form_page' => 1,
            ],
            [
                'legal_entity' => 'individual',
                'phone' => '0123456789',
            ],
            [
                'business_id' => 'DE1234567890', // invalid
                'legal_entity' => 'individual',
                'vat_id' => 'DE1234567890', // invalid
            ]
        );
        expectApplied('impressum_required_fields')->times(4)->andReturnFirstArg();
        Impressum::get_instance()->load_settings();
        $this->assertEqualsCanonicalizing(
            [
                'Address',
                'Email Address',
                'Name',
                'Telephone or Contact Form Page',
            ],
            Admin::get_invalid_fields()
        );
        $this->assertEqualsCanonicalizing(
            [
                'Address',
                'Email Address',
                'Name',
            ],
            Admin::get_invalid_fields()
        );
        $this->assertEqualsCanonicalizing(
            [
                'Address',
                'Email Address',
                'Name',
            ],
            Admin::get_invalid_fields()
        );
        $this->assertEqualsCanonicalizing(
            [
                'Address',
                'Business ID',
                'Email Address',
                'Name',
                'Telephone or Contact Form Page',
                'VAT ID',
            ],
            Admin::get_invalid_fields()
        );
    }
    
    public function testIsValidImprint(): void
    {
        $helper = Mockery::mock('alias:' . Helper::class);
        $helper->shouldReceive('get_option')->andReturn(
            [],
            [
                'legal_entity' => 'individual',
                'contact_form_page' => 1,
            ],
            [
                'legal_entity' => 'individual',
                'name' => 'Name',
                'address' => 'Address',
                'email' => 'mail@example.com',
                'contact_form_page' => 1,
            ]
        );
        expectApplied('impressum_required_fields')->times(2)->andReturnFirstArg();
        expectApplied('impressum_is_valid_imprint')->once()->andReturnFirstArg();
        $this->assertFalse(Admin::get_instance()->is_valid_imprint());
        $this->assertFalse(Admin::get_instance()->is_valid_imprint());
        $this->assertTrue(Admin::get_instance()->is_valid_imprint());
    }
    
    protected function tearDown(): void
    {
        tearDown();
        parent::tearDown();
    }
}
