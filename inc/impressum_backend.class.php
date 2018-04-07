<?php
// exit if ABSPATH is not defined
defined( 'ABSPATH' ) || exit;

require_once( __DIR__ . '/impressum.class.php' );

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
	 * @param string $plugin_file The path of the main plugin file
	 */
	public function __construct( $plugin_file ) {
		parent::__construct( $plugin_file );
		
		// hooks
		add_action( 'admin_init', [ $this, 'impressum_settings_init' ] );
		add_action( 'admin_menu', [ $this, 'impressum_options_page' ] );
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
		
		// legal entity
		add_settings_field(
			'legal_entity',
			__( 'Legal Entity', 'impressum' ),
			[ __CLASS__, 'legal_entity_callback' ],
			'impressum_imprint',
			'impressum_section_imprint',
			[
				'label_for' => 'legal_entity',
				'class' => 'impressum_row',
			]
		);
		
		// name
		add_settings_field(
			'name',
			__( 'Name', 'impressum' ),
			[ __CLASS__, 'impressum_input_text_callback' ],
			'impressum_imprint',
			'impressum_section_imprint',
			[
				'label_for' => 'name',
				'class' => 'impressum_row',
			]
		);
		
		// address
		add_settings_field(
			'address',
			__( 'Address', 'impressum' ),
			[ __CLASS__, 'impressum_textarea_callback' ],
			'impressum_imprint',
			'impressum_section_imprint',
			[
				'label_for' => 'address',
				'class' => 'impressum_row',
			]
		);
		
		// address alternative
		add_settings_field(
			'address_alternative',
			__( 'Alternative Address', 'impressum' ),
			[ __CLASS__, 'impressum_textarea_callback' ],
			'impressum_imprint',
			'impressum_section_imprint',
			[
				'label_for' => 'address_alternative',
				'class' => 'impressum_row',
			]
		);
		
		// email
		add_settings_field(
			'email',
			__( 'Email Address', 'impressum' ),
			[ __CLASS__, 'impressum_email_callback' ],
			'impressum_imprint',
			'impressum_section_imprint',
			[
				'label_for' => 'email',
				'class' => 'impressum_row',
			]
		);
		
		// phone
		add_settings_field(
			'phone',
			__( 'Telephone', 'impressum' ),
			[ __CLASS__, 'impressum_phone_callback' ],
			'impressum_imprint',
			'impressum_section_imprint',
			[
				'label_for' => 'phone',
				'class' => 'impressum_row',
			]
		);
		
		// fax
		add_settings_field(
			'fax',
			__( 'Fax', 'impressum' ),
			[ __CLASS__, 'impressum_phone_callback' ],
			'impressum_imprint',
			'impressum_section_imprint',
			[
				'label_for' => 'fax',
				'class' => 'impressum_row',
			]
		);
		
		// press law checkbox
		add_settings_field(
			'press_law_checkbox',
			__( 'Journalistic/Editorial Content', 'impressum' ),
			[ __CLASS__, 'impressum_press_law_checkbox_callback' ],
			'impressum_imprint',
			'impressum_section_imprint',
			[
				'label_for' => 'press_law_checkbox',
				'class' => 'impressum_row',
			]
		);
		
		// press law person
		add_settings_field(
			'press_law_person',
			__( 'Responsible according to the German Press Law', 'impressum' ),
			[ __CLASS__, 'impressum_textarea_callback' ],
			'impressum_imprint',
			'impressum_section_imprint',
			[
				'label_for' => 'press_law_person',
				'class' => 'impressum_row impressum_press_law',
			]
		);
		
		// vat id
		add_settings_field(
			'vat_id',
			__( 'VAT ID', 'impressum' ),
			[ __CLASS__, 'impressum_input_text_callback' ],
			'impressum_imprint',
			'impressum_section_imprint',
			[
				'label_for' => 'vat_id',
				'class' => 'impressum_row vat_id',
			]
		);
	}
	
	/**
	 * Legal Entity field callback.
	 * @param $args array
	 */
	public static function legal_entity_callback( array $args ) {
		// get the value of the setting we've registered with register_setting()
		$options = get_option( 'impressum_imprint_options' );
		
		// check for selected option
		$select_ag = isset( $options['legal_entity'] ) ? ( selected( $options['legal_entity'], 'ag', false ) ) : ( '' );
		$select_ev = isset( $options['legal_entity'] ) ? ( selected( $options['legal_entity'], 'ev', false ) ) : ( '' );
		$select_ek = isset( $options['legal_entity'] ) ? ( selected( $options['legal_entity'], 'ek', false ) ) : ( '' );
		$select_einzelkaufmann = isset( $options['legal_entity'] ) ? ( selected( $options['legal_entity'], 'einzelkaufmann', false ) ) : ( '' );
		$select_freelancer = isset( $options['legal_entity'] ) ? ( selected( $options['legal_entity'], 'freelancer', false ) ) : ( '' );
		$select_ggmbH = isset( $options['legal_entity'] ) ? ( selected( $options['legal_entity'], 'ggmbH', false ) ) : ( '' );
		$select_gmbh = isset( $options['legal_entity'] ) ? ( selected( $options['legal_entity'], 'gmbh', false ) ) : ( '' );
		$select_gbr = isset( $options['legal_entity'] ) ? ( selected( $options['legal_entity'], 'gbr', false ) ) : ( '' );
		$select_gmbh_co_kg = isset( $options['legal_entity'] ) ? ( selected( $options['legal_entity'], 'gmbh_co_kg', false ) ) : ( '' );
		$select_kg = isset( $options['legal_entity'] ) ? ( selected( $options['legal_entity'], 'kg', false ) ) : ( '' );
		$select_kgag = isset( $options['legal_entity'] ) ? ( selected( $options['legal_entity'], 'kgag', false ) ) : ( '' );
		$select_ohg = isset( $options['legal_entity'] ) ? ( selected( $options['legal_entity'], 'ohg', false ) ) : ( '' );
		$select_individual = ! isset( $options['legal_entity'] ) ? ' selected' : ( isset( $options['legal_entity'] ) ? ( selected( $options['legal_entity'], 'individual', false ) ) : ( '' ) );
		$select_ug = isset( $options['legal_entity'] ) ? ( selected( $options['legal_entity'], 'ug', false ) ) : ( '' );
		$select_ug_co_kg = isset( $options['legal_entity'] ) ? ( selected( $options['legal_entity'], 'ug_co_kg', false ) ) : ( '' );
		
		// output the field
		?>
<select id="<?php echo esc_attr( $args['label_for'] ); ?>" name="impressum_imprint_options[<?php echo esc_attr( $args['label_for'] ); ?>]">
	<option value="ag" <?php echo $select_ag; ?>><?php esc_html_e( 'AG', 'impressum' ); ?></option>
	<option value="ev" <?php echo $select_ev; ?>><?php esc_html_e( 'e.V.', 'impressum' ); ?></option>
	<option value="ek" <?php echo $select_ek; ?>><?php esc_html_e( 'e.K.', 'impressum' ); ?></option>
	<option value="einzelkaufmann" <?php echo $select_einzelkaufmann; ?>><?php esc_html_e( 'Einzelkaufmann', 'impressum' ); ?></option>
	<option value="freelancer" <?php echo $select_freelancer; ?>><?php esc_html_e( 'Freelancer', 'impressum' ); ?></option>
	<option value="ggmbH" <?php echo $select_ggmbH; ?>><?php esc_html_e( 'gGmbH', 'impressum' ); ?></option>
	<option value="gmbh" <?php echo $select_gmbh; ?>><?php esc_html_e( 'GmbH', 'impressum' ); ?></option>
	<option value="gbr" <?php echo $select_gbr; ?>><?php esc_html_e( 'GbR', 'impressum' ); ?></option>
	<option value="gmbh_co_kg" <?php echo $select_gmbh_co_kg; ?>><?php esc_html_e( 'GmbH & Co. KG', 'impressum' ); ?></option>
	<option value="kg" <?php echo $select_kg; ?>><?php esc_html_e( 'KG', 'impressum' ); ?></option>
	<option value="kgag" <?php echo $select_kgag; ?>><?php esc_html_e( 'KGaA', 'impressum' ); ?></option>
	<option value="ohg" <?php echo $select_ohg; ?>><?php esc_html_e( 'OHG', 'impressum' ); ?></option>
	<option value="individual" <?php echo $select_individual; ?>><?php esc_html_e( 'Individual', 'impressum' ); ?></option>
	<option value="ug" <?php echo $select_ug; ?>><?php esc_html_e( 'UG (haftungsbeschränkt)', 'impressum' ); ?></option>
	<option value="ug_co_kg" <?php echo $select_ug_co_kg; ?>><?php esc_html_e( 'UG (haftungsbeschränkt) & Co. KG', 'impressum' ); ?></option>
</select>
		<?php
	}
	
	/**
	 * Email field callback.
	 * @param $args array
	 */
	public static function impressum_email_callback( array $args ) {
		// get the value of the setting we've registered with register_setting()
		$options = get_option( 'impressum_imprint_options' );
		// output the field
		?>
<input type="email" id="<?php echo esc_attr( $args['label_for'] ); ?>" name="impressum_imprint_options[<?php echo esc_attr( $args['label_for'] ); ?>]" class="regular-text"<?php echo ( isset( $options[ $args['label_for'] ] ) ? ' value="' . $options[ $args['label_for'] ] . '"' : '' ); ?>>
		<?php
	}
	
	/**
	 * Text input field callback.
	 * @param $args array
	 */
	public static function impressum_input_text_callback( array $args ) {
		// get the value of the setting we've registered with register_setting()
		$options = get_option( 'impressum_imprint_options' );
		// output the field
		?>
<input type="text" id="<?php echo esc_attr( $args['label_for'] ); ?>" name="impressum_imprint_options[<?php echo esc_attr( $args['label_for'] ); ?>]" class="regular-text"<?php echo ( isset( $options[ $args['label_for'] ] ) ? ' value="' . $options[ $args['label_for'] ] . '"' : '' ); ?>>
		<?php
	}
	
	/**
	 * Phone field callback.
	 * @param $args array
	 */
	public static function impressum_phone_callback( array $args ) {
		// get the value of the setting we've registered with register_setting()
		$options = get_option( 'impressum_imprint_options' );
		// output the field
		?>
<input type="tel" id="<?php echo esc_attr( $args['label_for'] ); ?>" name="impressum_imprint_options[<?php echo esc_attr( $args['label_for'] ); ?>]" class="regular-text"<?php echo ( isset( $options[ $args['label_for'] ] ) ? ' value="' . $options[ $args['label_for'] ] . '"' : '' ); ?>>
		<?php
	}
	
	/**
	 * Press Law Checkbox field callback.
	 * @param $args array
	 */
	public static function impressum_press_law_checkbox_callback( array $args ) {
		// get the value of the setting we've registered with register_setting()
		$options = get_option( 'impressum_imprint_options' );
		// output the field
		?>
<label for="<?php echo esc_attr( $args['label_for'] ); ?>"><input type="checkbox" id="<?php echo esc_attr( $args['label_for'] ); ?>" name="impressum_imprint_options[<?php echo esc_attr( $args['label_for'] ); ?>]" value="1"<?php checked( isset( $options[ $args['label_for'] ] ) ); ?>>
	<?php _e( 'I have journalistic/editorial content on my website', 'impressum' ); ?>
</label>
		<?php
	}
	
	/**
	 * Textarea callback.
	 * @param $args array
	 */
	public static function impressum_textarea_callback( array $args ) {
		// get the value of the setting we've registered with register_setting()
		$options = get_option( 'impressum_imprint_options' );
		// output the field
		?>
<textarea cols="50" rows="10" id="<?php echo esc_attr( $args['label_for'] ); ?>" name="impressum_imprint_options[<?php echo esc_attr( $args['label_for'] ); ?>]"><?php echo ( isset( $options[ $args['label_for'] ] ) ? $options[ $args['label_for'] ] : '' ); ?></textarea>
		<?php
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
		$current_tab = isset( $_GET[ 'imprint_tab' ] ) ? $_GET[ 'imprint_tab' ] : 'imprint';
?>
<div class="wrap">
	<h1><?php echo esc_html( get_admin_page_title() ); ?></h1>
	<h2 class="nav-tab-wrapper">
		<a href="?page=impressum&imprint_tab=imprint" class="nav-tab <?php echo $current_tab == 'imprint' ? 'nav-tab-active' : ''; ?>"><?php _e( 'Imprint', 'impressum' ); ?></a>
		<a href="?page=impressum&imprint_tab=get_plus" class="nav-tab <?php echo $current_tab == 'get_plus' ? 'nav-tab-active' : ''; ?>"><?php _e( 'Get Plus', 'impressum' ); ?></a>
	</h2>
	
	<?php
	switch ( $current_tab ) {
		case 'imprint':
			echo '<form action="options.php" method="post">';
			// output security fields for the registered setting "impressum"
			settings_fields( 'impressum_' . $current_tab );
			// output setting sections and their fields
			// (sections are registered for "impressum", each field is registered to a specific section)
			do_settings_sections( 'impressum_' . $current_tab );
			// output save settings button
			submit_button( __( 'Save Settings', 'impressum' ) );
			echo '</form>';
			break;
		case 'get_plus':
			echo '<h3>' . __( 'Get an imprint for your company website!', 'impressum' ) . '</h3>';
			echo '<p>' . __( 'We designed “Impressum” to be the perfect companion to individuals for all imprint things on their WordPress websites. However, if your site is operated by another legal entity than an individual person, “Impressum Plus” is the plugin you should use.', 'impressum' ) . '</p>';
			echo '<p>' . __( 'For a small fee, “Impressum Plus” will provide you with the same seamless user experience as the free version. But in addition to the features of the free version, it will also cover a load of different legal entities and their quite diverse need for imprint data.', 'impressum' ) . '</p>';
			echo '<h3>' . __( 'Go Plus to support development', 'impressum' ) . '</h3>';
			echo '<p>' . __( 'Even as a private website owner you can upgrade to “Impressum Plus” anytime. Every single Plus user means the world to us, since it’s those users who support our ongoing work on both the free and paid version. In addition, Plus is equipped to make handling imprints across your WordPress multisite an ease. And we’ll continue to add nifty features.', 'impressum' ) . '</p>';
			echo '<p>' . __( 'Get Impressum Plus very soon!', 'impressum' ) . '</p>';
			break;
	}
	?>
</div>
	<?php
	}
}