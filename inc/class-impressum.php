<?php
namespace epiphyt\Impressum;

/**
 * The main Impressum class.
 * 
 * @deprecated	3.0.0 Use \epiphyt\Impressum\Plugin instead
 * 
 * @author	Epiphyt
 * @license	GPL2
 * @package	epiphyt\Impressum
 */
class Impressum {
	use Singleton;
	
	/**
	 * @var		?\epiphyt\Impressum\Admin Admin functionality
	 */
	public ?\epiphyt\Impressum\Admin $admin = null;
	
	/**
	 * @var		array Countries with their country codes in 3-digit ISO form
	 */
	protected array $countries = [];
	
	/**
	 * @var		?\epiphyt\Impressum\Frontend Frontend functionality
	 */
	public ?\epiphyt\Impressum\Frontend $frontend = null;
	
	/**
	 * @var		array All legal entities we support
	 */
	protected array $legal_entities = [];
	
	/**
	 * @var		array All settings fields.
	 */
	public array $settings_fields = [];
	
	/**
	 * @var		?\epiphyt\Impressum\settings\Registry Settings registry
	 */
	public ?\epiphyt\Impressum\settings\Registry $settings_registry = null;
	
	/**
	 * Impressum constructor.
	 */
	public function __construct() {
		// assign variables
		$this->admin = new Admin( \epiphyt\Impressum\get_container()->get( 'settings-registry' ) );
		$this->frontend = Frontend::get_instance();
	}
	
	/**
	 * Initialize the class.
	 * 
	 * @deprecated	3.0.0 Use \epiphyt\Impressum\Plugin::init() instead
	 */
	public function init(): void {
		\_doing_it_wrong(
			__METHOD__,
			\sprintf(
				/* translators: alternative method */
				\esc_html__( 'Use %s instead', 'impressum' ),
				'\epiphyt\Impressum\Plugin::init()'
			),
			'3.0.0'
		);
	}
	
	/**
	 * Get all fields from an option with their title.
	 * 
	 * @deprecated	3.0.0 Use \epiphyt\Impressum\Plugin::get_block_fields() instead
	 * 
	 * @param	string	$option_name The name of the option
	 * @return	array The fields
	 */
	public function get_block_fields( string $option_name ): array {
		\_doing_it_wrong(
			__METHOD__,
			\sprintf(
				/* translators: alternative method */
				\esc_html__( 'Use %s instead', 'impressum' ),
				'\epiphyt\Impressum\Plugin::get_block_fields()'
			),
			'3.0.0'
		);
		
		return \epiphyt\Impressum\get_container()->get( 'plugin' )->get_block_fields( $option_name );
	}
	
	/**
	 * Get a list of countries.
	 * 
	 * @deprecated	3.0.0 Use \epiphyt\Impressum\Plugin::get_countries() instead
	 * 
	 * @return	array The country list
	 */
	public function get_countries(): array {
		\_doing_it_wrong(
			__METHOD__,
			\sprintf(
				/* translators: alternative method */
				\esc_html__( 'Use %s instead', 'impressum' ),
				'\epiphyt\Impressum\Plugin::get_countries()'
			),
			'3.0.0'
		);
		
		return \epiphyt\Impressum\get_container()->get( 'plugin' )->get_countries();
	}
	
	/**
	 * Get a list of legal entities.
	 * 
	 * @deprecated	3.0.0 Use \epiphyt\Impressum\Plugin::get_legal_entities() instead
	 * 
	 * @return	array The legal entity list
	 */
	public function get_legal_entities(): array {
		\_doing_it_wrong(
			__METHOD__,
			\sprintf(
				/* translators: alternative method */
				\esc_html__( 'Use %s instead', 'impressum' ),
				'\epiphyt\Impressum\Plugin::get_legal_entities()'
			),
			'3.0.0'
		);
		
		return \epiphyt\Impressum\get_container()->get( 'plugin' )->get_legal_entities();
	}
	
	/**
	 * Load our settings in an array.
	 * 
	 * @deprecated	3.0.0 Use \epiphyt\Impressum\Plugin::load_settings() instead
	 */
	public function load_settings(): void {
		\_doing_it_wrong(
			__METHOD__,
			\sprintf(
				/* translators: alternative method */
				\esc_html__( 'Use %s instead', 'impressum' ),
				'\epiphyt\Impressum\Plugin::load_settings()'
			),
			'3.0.0'
		);
	}
	
	/**
	 * Load translations.
	 * 
	 * @deprecated	3.0.0 Use \epiphyt\Impressum\Plugin::load_textdomain() instead
	 */
	public function load_textdomain(): void {
		\_doing_it_wrong(
			__METHOD__,
			\sprintf(
				/* translators: alternative method */
				\esc_html__( 'Use %s instead', 'impressum' ),
				'\epiphyt\Impressum\Plugin::load_textdomain()'
			),
			'3.0.0'
		);
	}
	
	/**
	 * Activate the twice-daily cron.
	 * 
	 * @deprecated	3.0.0 Use \epiphyt\Impressum\Plugin::activate() instead
	 * 
	 * @param	array	$value The value on updating option
	 * @return	array The (untouched) value on updating option
	 */
	public function twice_daily_cron_activation( array $value = [] ): array {
		\_doing_it_wrong(
			__METHOD__,
			\sprintf(
				/* translators: alternative method */
				\esc_html__( 'Use %s instead', 'impressum' ),
				'\epiphyt\Impressum\Plugin::get_block_fields()'
			),
			'3.0.0'
		);
		
		return \epiphyt\Impressum\get_container()->get( 'plugin' )->activate( $value );
	}
	
	/**
	 * Deactivate the twice-daily cron.
	 * This should be called only while deactivating the plugin.
	 * 
	 * @deprecated	3.0.0 Use \epiphyt\Impressum\Plugin::deactivate() instead
	 */
	public function twice_daily_cron_deactivation(): void {
		\_doing_it_wrong(
			__METHOD__,
			\sprintf(
				/* translators: alternative method */
				\esc_html__( 'Use %s instead', 'impressum' ),
				'\epiphyt\Impressum\Plugin::deactivate()'
			),
			'3.0.0'
		);
	}
}
