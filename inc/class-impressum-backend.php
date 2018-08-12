<?php
namespace epiphyt\Impressum;

// exit if ABSPATH is not defined
defined( 'ABSPATH' ) || exit;

require_once( __DIR__ . '/class-impressum.php' );

/**
 * Impressum backend functions.
 * 
 * @version		0.1
 * @author		Epiphyt
 * @license		GPL3 <https://www.gnu.org/licenses/gpl-3.0.html>
 */
class Impressum_Backend extends Impressum {
	/**
	 * Impressum Backend constructor.
	 * 
	 * @param	string		$plugin_file The path of the main plugin file
	 */
	public function __construct( $plugin_file ) {
		parent::__construct( $plugin_file );
		
		// hooks
		add_filter( 'plugin_row_meta', [ $this, 'add_meta_link' ], 10, 2 );
		add_action( 'admin_init', [ $this, 'impressum_settings_init' ] );
		add_action( 'admin_menu', [ $this, 'impressum_options_page' ] );
	}
	
	/**
	 * Add plugin meta links.
	 * 
	 * @param array $input Registered links.
	 * @param string $file  Current plugin file.
	 * @return array Merged links
	 */
	public function add_meta_link( $input, $file ) {
		// bail on other plugins
		if ( IMPRESSUM_BASE !== $file ) return $input;
		
		return array_merge(
			$input,
			[
				'<a href="https://impressum.plus/dokumentation/?version=' . get_plugin_data( $this->plugin_file )['Version'] . '" target="_blank" rel="noopener noreferrer">' . esc_html__( 'Documentation', 'impressum' ) . '</a>',
			]
		);
	}
	
	/**
	 * Custom option and settings.
	 */
	public static function impressum_settings_init() {
		// register a new setting for "impressum" page
		register_setting( 'impressum_imprint', 'impressum_imprint_options' );
		
		// register a new section in the "impressum" page
		add_settings_section(
			'impressum_section_imprint',
			'',
			null,
			'impressum_imprint'
		);
		
		/**
		 * Register option fields
		 */
		foreach ( self::$settings_fields as $id => $settings_field ) {
			add_settings_field(
				$id,
				$settings_field['title'],
				[ __CLASS__, $settings_field['callback'] ],
				$settings_field['page'],
				$settings_field['section'],
				$settings_field['args']
			);
		}
	}
	
	/**
	 * Country field callback.
	 * 
	 * @param	array		$args The arguments from the HTML field
	 */
	public static function country_callback( array $args ) {
		// get the value of the setting we've registered with register_setting()
		$options = self::impressum_get_option( 'impressum_imprint_options' );
		?>
<select id="<?php echo esc_attr( $args['label_for'] ); ?>" name="impressum_imprint_options[<?php echo esc_attr( $args['label_for'] ); ?>]">
	<option value=""><?php esc_html_e( 'Please select &hellip;', 'impressum' ); ?></option>
		<?php
		foreach ( self::$countries as $country_code => $country ) {
			$is_selected = ( isset( $options['country'] ) ? selected( $options['country'], $country_code, false ) : '' );
			
			echo '<option value="' . esc_attr( $country_code ) . '"' . ( $is_selected ?: '' ) . '>' . esc_html( $country ) . '</option>';
		}
		?>
</select>
<p><?php esc_html_e( 'In order to determine the needed fields for your imprint we need to know your country.', 'impressum' ); ?></p>
		<?php
	}
	
	/**
	 * Legal Entity field callback.
	 * 
	 * @param	array		$args The arguments from the HTML field
	 */
	public static function legal_entity_callback( array $args ) {
		// get the value of the setting we've registered with register_setting()
		$options = self::impressum_get_option( 'impressum_imprint_options' );
		
		// output the field
		?>
<select id="<?php echo esc_attr( $args['label_for'] ); ?>" name="impressum_imprint_options[<?php echo esc_attr( $args['label_for'] ); ?>]">
		<?php
		foreach ( self::$legal_entities as $abbr => $entity ) {
			$is_selected = ( isset( $options['legal_entity'] ) ? selected( $options['legal_entity'], $abbr, false ) : '' );
			
			echo '<option value="' . esc_attr( $abbr ) . '"' . ( $is_selected ?: '' ) . '>' . esc_html( $entity ) . '</option>';
		}
		?>
</select>
<p><?php esc_html_e( 'In order to guide you the needed fields we need to know what kind of legal entity you are.', 'impressum' ); ?></p>
		<?php
	}
	
	/**
	 * Email field callback.
	 * 
	 * @param	array		$args The arguments from the HTML field
	 */
	public static function impressum_email_callback( array $args ) {
		// get the value of the setting we've registered with register_setting()
		$options = self::impressum_get_option( 'impressum_imprint_options' );
		// output the field
		?>
<input type="email" id="<?php echo esc_attr( $args['label_for'] ); ?>" name="impressum_imprint_options[<?php echo esc_attr( $args['label_for'] ); ?>]" class="regular-text"<?php echo ( isset( $options[ $args['label_for'] ] ) ? ' value="' . esc_attr( $options[ $args['label_for'] ] ) . '"' : '' ); ?>>
		<?php
	}
	
	/**
	 * Text input field callback.
	 * 
	 * @param	array		$args The arguments from the HTML field
	 */
	public static function impressum_input_text_callback( array $args ) {
		// get the value of the setting we've registered with register_setting()
		$options = self::impressum_get_option( 'impressum_imprint_options' );
		// output the field
		?>
<input type="text" id="<?php echo esc_attr( $args['label_for'] ); ?>" name="impressum_imprint_options[<?php echo esc_attr( $args['label_for'] ); ?>]" class="regular-text"<?php echo ( isset( $options[ $args['label_for'] ] ) ? ' value="' . esc_attr( $options[ $args['label_for'] ] ) . '"' : '' ); ?>>
		<?php
		switch ( $args['label_for'] ) {
			case 'vat_id':
				echo '<p>' . esc_html__( 'Your VAT ID in format XX123456789, which means at least two letters by following some numbers (the amount depends on your country).', 'impressum' ) . '</p>';
				break;
		}
	}
	
	/**
	 * Phone field callback.
	 * 
	 * @param	array		$args The arguments from the HTML field
	 */
	public static function impressum_phone_callback( array $args ) {
		// get the value of the setting we've registered with register_setting()
		$options = self::impressum_get_option( 'impressum_imprint_options' );
		// output the field
		?>
<input type="tel" id="<?php echo esc_attr( $args['label_for'] ); ?>" name="impressum_imprint_options[<?php echo esc_attr( $args['label_for'] ); ?>]" class="regular-text"<?php echo ( isset( $options[ $args['label_for'] ] ) ? ' value="' . esc_attr( $options[ $args['label_for'] ] ) . '"' : '' ); ?>>
		<?php
	}
	
	/**
	 * Press Law Checkbox field callback.
	 * 
	 * @param	array		$args The arguments from the HTML field
	 */
	public static function impressum_press_law_checkbox_callback( array $args ) {
		// get the value of the setting we've registered with register_setting()
		$options = self::impressum_get_option( 'impressum_imprint_options' );
		// output the field
		?>
<label for="<?php echo esc_attr( $args['label_for'] ); ?>"><input type="checkbox" id="<?php echo esc_attr( $args['label_for'] ); ?>" name="impressum_imprint_options[<?php echo esc_attr( $args['label_for'] ); ?>]" value="1"<?php checked( isset( $options[ $args['label_for'] ] ) ); ?>>
	<?php esc_html_e( 'I have journalistic/editorial content on my website', 'impressum' ); ?>
</label>
		<?php
	}
	
	/**
	 * Textarea callback.
	 * 
	 * @param	array		$args The arguments from the HTML field
	 */
	public static function impressum_textarea_callback( array $args ) {
		// get the value of the setting we've registered with register_setting()
		$options = self::impressum_get_option( 'impressum_imprint_options' );
		// output the field
		?>
<textarea cols="50" rows="10" id="<?php echo esc_attr( $args['label_for'] ); ?>" name="impressum_imprint_options[<?php echo esc_attr( $args['label_for'] ); ?>]"><?php echo ( isset( $options[ $args['label_for'] ] ) ? esc_html( $options[ $args['label_for'] ] ) : '' ); ?></textarea>
		<?php
		switch ( $args['label_for'] ) {
			case 'address':
				echo '<p>' . esc_html__( 'You need to set at least your street with number, your zip code and your city.', 'impressum' ) . '</p>';
				break;
			case 'address_alternative':
				echo '<p>' . esc_html__( 'You can set an alternative address to be displayed in your imprint.', 'impressum' ) . '</p>';
				break;
		}
	}
	
	/**
	* Add sub menu item in options menu.
	*/
	public static function impressum_options_page() {
		// add top level menu page
		add_submenu_page(
			'options-general.php',
			'Impressum',
			'Impressum',
			'manage_options',
			'impressum',
			[ __CLASS__, 'options_page_html' ]
		);
	}
	
	/**
	* Sub menu item:
	* callback functions
	*/
	public static function options_page_html() {
		// check user capabilities
		if ( ! current_user_can( 'manage_options' ) ) return;
		
		// show error/update messages
		settings_errors( 'impressum_messages' );
		
		// get current tab
		// phpcs:disable WordPress.CSRF.NonceVerification.NoNonceVerification
		$current_tab = isset( $_GET['imprint_tab'] ) ? sanitize_text_field( wp_unslash( $_GET['imprint_tab'] ) ) : 'imprint';
		// phpcs:enable
?>
<div class="wrap">
	<h1><?php echo esc_html( get_admin_page_title() ); ?></h1>
	<h2 class="nav-tab-wrapper">
		<a href="?page=impressum&imprint_tab=imprint" class="nav-tab <?php echo $current_tab === 'imprint' ? 'nav-tab-active' : ''; ?>"><?php esc_html_e( 'Imprint', 'impressum' ); ?></a>
		<a href="?page=impressum&imprint_tab=get_plus" class="nav-tab <?php echo $current_tab === 'get_plus' ? 'nav-tab-active' : ''; ?>"><?php esc_html_e( 'Get Plus', 'impressum' ); ?></a>
	</h2>
	<?php // phpcs:disable WordPress.WhiteSpace.PrecisionAlignment.Found ?>
	
	<?php // phpcs:enable ?>
	<div class="impressum-content-wrapper">
	<?php
	switch ( $current_tab ) {
		case 'imprint':
			echo '<form action="options.php" method="post">';
			// output security fields for the registered setting "impressum"
			settings_fields( 'impressum_' . $current_tab );
			// output setting sections and their fields
			// (sections are registered for "impressum", each field is registered to a specific section)
			do_settings_sections( 'impressum_' . $current_tab );
			// disclaimer
			echo '<h3>' . esc_html__( 'Disclaimer', 'impressum' ) . '</h3>';
			echo '<p>' . esc_html__( 'Please keep in mind that this plugin does not guarantee any legal compliance. You are responsible for the data you enter here. “Impressum” helps you to fill all necessary fields.', 'impressum' ) . '</p>';
			// output save settings button
			submit_button( esc_html__( 'Save Settings', 'impressum' ) );
			echo '</form>';
			// usage description
			echo '<h3>' . esc_html__( 'Usage', 'impressum' ) . '</h3>';
			/* translators: the shortcode */
			echo '<p>' . sprintf( esc_html__( 'Add the %1$s shortcode wherever you want to output your imprint. It works on pages, posts and even widgets (anywhere shortcodes work).', 'impressum' ), '<code>[impressum]</code>' ) . '</p>';
			break;
		case 'get_plus':
			echo '<h3>' . esc_html__( 'Get an imprint for your company website!', 'impressum' ) . '</h3>';
			echo '<p>' . esc_html__( 'We designed “Impressum” to be the perfect companion to individuals for all imprint things on their WordPress websites. However, if your site is operated by another legal entity than an individual person, “Impressum Plus” is the plugin you should use.', 'impressum' ) . '</p>';
			echo '<p>' . esc_html__( 'For a small fee, “Impressum Plus” will provide you with the same seamless user experience as the free version. But in addition to the features of the free version, it will also cover a load of different legal entities and their quite diverse need for imprint data.', 'impressum' ) . '</p>';
			echo '<h3>' . esc_html__( 'Go Plus to support development', 'impressum' ) . '</h3>';
			echo '<p>' . esc_html__( 'Even as a private website owner you can upgrade to “Impressum Plus” anytime. Every single Plus user means the world to us, since it’s those users who support our ongoing work on both the free and paid version. In addition, Plus is equipped to make handling imprints across your WordPress multisite an ease. And we’ll continue to add nifty features.', 'impressum' ) . '</p>';
			echo '<p>' . esc_html__( 'Get Impressum Plus very soon!', 'impressum' ) . '</p>';
			break;
	}
	?>
	</div>
</div>
	<?php
	}
}
