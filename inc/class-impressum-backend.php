<?php
namespace epiphyt\Impressum;

// exit if ABSPATH is not defined
defined( 'ABSPATH' ) || exit;

require_once( __DIR__ . '/class-impressum.php' );

/**
 * Impressum backend functions.
 * 
 * @author		Epiphyt
 * @license		GPL3 <https://www.gnu.org/licenses/gpl-3.0.html>
 * @version		1.0.0
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
		add_action( 'network_admin_menu', [ $this, 'impressum_network_options_page' ] );
		add_action( 'network_admin_edit_impressum_network_options_update', [ $this, 'impressum_network_options_update' ] );
		add_action( 'pre_update_option_impressum_license_options', [ $this, 'impressum_protect_license_key' ], 10, 3 );
		add_action( 'pre_update_site_option_impressum_license_options', [ $this, 'impressum_protect_license_key' ], 10, 3 );
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
		register_setting( 'impressum_license', 'impressum_license_options' );
		
		// register a new section in the "impressum" page
		add_settings_section(
			'impressum_section_imprint',
			null,
			null,
			'impressum_imprint'
		);
		// register a new section in the "impressum" page
		add_settings_section(
			'impressum_section_license',
			null,
			null,
			'impressum_license'
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
			case 'coverage':
				echo '<p>' . esc_html__( 'If you link to this imprint from several other domains, enter them here.', 'impressum' ) . '</p>';
				break;
			case 'register':
				echo '<p>' . esc_html__( 'You need at least enter your register number and the register where your company is registered.', 'impressum' ) . '</p>';
				break;
			case 'vat_id':
				echo '<p>' . esc_html__( 'Your VAT ID in format XX123456789, which means at least two letters by following some numbers (the amount depends on your country).', 'impressum' ) . '</p>';
				break;
		}
	}
	
	/**
	 * Email input field callback.
	 * @param $args array
	 */
	public static function impressum_license_input_email_callback( array $args ) {
		// get the value of the setting we've registered with register_setting()
		$options = self::impressum_get_option( 'impressum_license_options' );
		// output the field
		?>
<input type="email" id="<?php echo esc_attr( $args['label_for'] ); ?>" name="impressum_license_options[<?php echo esc_attr( $args['label_for'] ); ?>]" class="regular-text"<?php echo ( isset( $options[ $args['label_for'] ] ) ? ' value="' . esc_attr( $options[ $args['label_for'] ] ) . '"' : '' ); ?>>
		<?php
	}
	
	/**
	 * Text input field callback.
	 * @param $args array
	 */
	public static function impressum_license_input_text_callback( array $args ) {
		// get the value of the setting we've registered with register_setting()
		$options = self::impressum_get_option( 'impressum_imprint_options' );
		// output the field
		?>
<input type="text" id="<?php echo esc_attr( $args['label_for'] ); ?>" name="impressum_license_options[<?php echo esc_attr( $args['label_for'] ); ?>]" class="regular-text"<?php echo ( isset( $options[ $args['label_for'] ] ) ? ' value="' . esc_attr( $options[ $args['label_for'] ] ) . '"' : '' ); ?>>
		<?php
	}
	
	/**
	 * License (password) input field callback.
	 * @param $args array
	 */
	public static function impressum_license_input_license_callback( array $args ) {
		// get the value of the setting we've registered with register_setting()
		$options = self::impressum_get_option( 'impressum_license_options' );
		// output the field
		?>
<input type="password" id="<?php echo esc_attr( $args['label_for'] ); ?>" name="impressum_license_options[<?php echo esc_attr( $args['label_for'] ); ?>]" class="regular-text"<?php echo ( isset( $options[ $args['label_for'] ] ) ? ' value="' . esc_attr( str_repeat( '*', strlen( $options[ $args['label_for'] ] ) ) ) . '"' : '' ); ?>>
		<?php
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
			case 'free_text':
				echo '<p>' . esc_html__( 'You can add some additional free text if the predefined input fields don’t suite your needs.', 'impressum' ) . '</p>';
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
	* Add sub menu item in network options menu.
	*/
	public static function impressum_network_options_page() {
		// add top level menu page
		add_submenu_page(
			'settings.php',
			'Impressum',
			'Impressum',
			'manage_network_options',
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
		
		// set form action
		$form_action = 'options.php';
		
		if ( is_network_admin() ) {
			// phpcs:disable WordPress.CSRF.NonceVerification.NoNonceVerification
			if ( isset( $_GET['updated'] ) ) {
			// phpcs:enable
				?>
				<div id="message" class="updated notice is-dismissible">
					<p><?php esc_html_e( 'Options saved.', 'impressum' ) ?></p>
				</div>
				<?php
			}
			
			// modify form action
			$form_action = 'edit.php?action=impressum_network_options_update';
		}
?>
<div class="wrap">
	<h1><?php echo esc_html( get_admin_page_title() ); ?></h1>
	<h2 class="nav-tab-wrapper">
		<a href="?page=impressum&imprint_tab=imprint" class="nav-tab <?php echo $current_tab === 'imprint' ? 'nav-tab-active' : ''; ?>"><?php esc_html_e( 'Imprint', 'impressum' ); ?></a>
		<a href="?page=impressum&imprint_tab=license" class="nav-tab <?php echo $current_tab === 'license' ? 'nav-tab-active' : ''; ?>"><?php esc_html_e( 'License', 'impressum' ); ?></a>
	</h2>
	<?php // phpcs:disable WordPress.WhiteSpace.PrecisionAlignment.Found ?>
	
	<?php // phpcs:enable ?>
	<div class="impressum-content-wrapper">
	<?php
	switch ( $current_tab ) {
		case 'imprint':
			echo '<form action="' . esc_html( $form_action ) . '" method="post">';
			// output security fields for the registered setting "impressum"
			settings_fields( 'impressum_' . $current_tab );
			// output setting sections and their fields
			// (sections are registered for "impressum", each field is registered to a specific section)
			do_settings_sections( 'impressum_' . $current_tab );
			// disclaimer
			echo '<h3>' . esc_html__( 'Disclaimer', 'impressum' ) . '</h3>';
			echo '<p>' . esc_html__( 'Please keep in mind that this plugin does not guarantee any legal compliance. You are responsible for the data you enter here. “Impressum Plus” helps you to fill all necessary fields.', 'impressum' ) . '</p>';
			// output save settings button
			submit_button( esc_html__( 'Save Settings', 'impressum' ) );
			echo '</form>';
			// usage description
			echo '<h3>' . esc_html__( 'Usage', 'impressum' ) . '</h3>';
			/* translators: the shortcode */
			echo '<p>' . sprintf( esc_html__( 'Add the %1$s shortcode wherever you want to output your imprint. It works on pages, posts and even widgets (anywhere shortcodes work).', 'impressum' ), '<code>[impressum]</code>' ) . '</p>';
			break;
		case 'license':
			echo '<form action="' . esc_html( $form_action ) . '" method="post">';
			// output security fields for the registered setting "impressum"
			settings_fields( 'impressum_' . $current_tab );
			// output setting sections and their fields
			// (sections are registered for "impressum", each field is registered to a specific section)
			do_settings_sections( 'impressum_' . $current_tab );
			// output save settings button
			submit_button( __( 'Save Settings', 'impressum' ) );
			echo '</form>';
			break;
	}
	?>
	</div>
</div>
	<?php
	}
	
	/**
	 * Update network options.
	 */
	public static function impressum_network_options_update() {
		if (
			! isset( $_POST['option_page'], $_POST['_wpnonce'] )
			|| ! wp_verify_nonce( sanitize_key( $_POST['_wpnonce'] ), sanitize_key( $_POST['option_page'] ) . '-options' )
		) return;
		
		// get most recent active tab
		$tab = substr( strstr( sanitize_text_field( wp_unslash( $_POST['option_page'] ) ), '_' ), 1 );
		
		// make sure we are posting from our options page
		check_admin_referer( 'impressum_' . $tab . '-options' );
		
		// list of registered options
		global $new_whitelist_options;
		$options = array_merge(
			$new_whitelist_options['impressum_imprint'],
			$new_whitelist_options['impressum_license']
		);
		
		foreach ( $options as $option ) {
			if ( isset( $_POST[ $option ] ) ) {
				$option_sanitized = [];
				
				// sanitize
				foreach ( wp_unslash( $_POST[ $option ] ) as $key => $value ) {
					$option_sanitized[ sanitize_key( $key ) ] = sanitize_textarea_field( wp_unslash( $value ) );
				}
				
				update_site_option( $option, $option_sanitized );
			}
		}
		
		// redirect to network options page
		wp_redirect( add_query_arg( [
			'page' => 'impressum&imprint_tab=' . $tab,
			'updated' => 'true',
		], network_admin_url( 'settings.php' ) ) );
		exit;
	}
	
	/**
	 * Protect the license key against source code sniffing.
	 * 
	 * @param $value The new value
	 * @param $old_value The old value
	 * @param $option The option name
	 * @return mixed|void
	 */
	public function impressum_protect_license_key( $value, $old_value, $option ) {
		// if license key contains an asterisk, take the previous value
		if ( strpos( $value['license_key'], '*' ) !== false ) {
			$value['license_key'] = $old_value['license_key'];
		}
		
		return $value;
	}
}
