<?php
namespace epiphyt\Impressum;

// exit if ABSPATH is not defined
defined( 'ABSPATH' ) || exit;

require_once( __DIR__ . '/class-impressum.php' );

/**
 * Impressum frontend functions.
 * 
 * @version		0.1
 * @author		Epiphyt
 * @license		GPL3 <https://www.gnu.org/licenses/gpl-3.0.html>
 */
class Impressum_Frontend extends Impressum {
	/**
	 * @var		array[] Map displaying a field by its entity.
	 */
	public static $field_mapping = [
		'address' => [
			'ag',
			'ev',
			'ek',
			'einzelkaufmann',
			'freelancer',
			'ggmbh',
			'gmbh',
			'gbr',
			'gmbh_co_kg',
			'kg',
			'kgag',
			'ohg',
			'individual',
			'self',
			'ug',
			'ug_co_kg',
		],
		'address_alternative' => [
			'ag',
			'ev',
			'ek',
			'einzelkaufmann',
			'freelancer',
			'ggmbh',
			'gmbh',
			'gbr',
			'gmbh_co_kg',
			'kg',
			'kgag',
			'ohg',
			'individual',
			'self',
			'ug',
			'ug_co_kg',
		],
		'coverage' => [
			'ag',
			'ev',
			'ek',
			'einzelkaufmann',
			'freelancer',
			'ggmbh',
			'gmbh',
			'gbr',
			'gmbh_co_kg',
			'kg',
			'kgag',
			'ohg',
			'ug',
			'ug_co_kg',
		],
		'email' => [
			'ag',
			'ev',
			'ek',
			'einzelkaufmann',
			'freelancer',
			'ggmbh',
			'gmbh',
			'gbr',
			'gmbh_co_kg',
			'kg',
			'kgag',
			'ohg',
			'individual',
			'self',
			'ug',
			'ug_co_kg',
		],
		'fax' => [
			'ag',
			'ev',
			'ek',
			'einzelkaufmann',
			'freelancer',
			'ggmbh',
			'gmbh',
			'gbr',
			'gmbh_co_kg',
			'kg',
			'kgag',
			'ohg',
			'individual',
			'self',
			'ug',
			'ug_co_kg',
		],
		'legal_entity' => [
			'ag',
			'ev',
			'ek',
			'einzelkaufmann',
			'freelancer',
			'ggmbh',
			'gmbh',
			'gbr',
			'gmbh_co_kg',
			'kg',
			'kgag',
			'ohg',
			'individual',
			'self',
			'ug',
			'ug_co_kg',
		],
		'name' => [
			'ag',
			'ev',
			'ek',
			'einzelkaufmann',
			'freelancer',
			'ggmbh',
			'gmbh',
			'gbr',
			'gmbh_co_kg',
			'kg',
			'kgag',
			'ohg',
			'individual',
			'self',
			'ug',
			'ug_co_kg',
		],
		'phone' => [
			'ag',
			'ev',
			'ek',
			'einzelkaufmann',
			'freelancer',
			'ggmbh',
			'gmbh',
			'gbr',
			'gmbh_co_kg',
			'kg',
			'kgag',
			'ohg',
			'individual',
			'self',
			'ug',
			'ug_co_kg',
		],
		'press_law_person' => [
			'ag',
			'ev',
			'ek',
			'einzelkaufmann',
			'freelancer',
			'ggmbh',
			'gmbh',
			'gbr',
			'gmbh_co_kg',
			'kg',
			'kgag',
			'ohg',
			'individual',
			'self',
			'ug',
			'ug_co_kg',
		],
		'vat_id' => [
			'ag',
			'ev',
			'ek',
			'einzelkaufmann',
			'freelancer',
			'ggmbh',
			'gmbh',
			'gbr',
			'gmbh_co_kg',
			'kg',
			'kgag',
			'ohg',
			'individual',
			'self',
			'ug',
			'ug_co_kg',
		],
		'free_text' => [
			'ag',
			'ev',
			'ek',
			'einzelkaufmann',
			'freelancer',
			'ggmbh',
			'gmbh',
			'gbr',
			'gmbh_co_kg',
			'kg',
			'kgag',
			'ohg',
			'ug',
			'ug_co_kg',
		],
		'inspecting_authority' => [
			'ag',
			'ev',
			'ek',
			'einzelkaufmann',
			'freelancer',
			'ggmbh',
			'gmbh',
			'gbr',
			'gmbh_co_kg',
			'kg',
			'kgag',
			'ohg',
			'ug',
			'ug_co_kg',
		],
		'register' => [
			'ag',
			'ev',
			'ek',
			'einzelkaufmann',
			'freelancer',
			'ggmbh',
			'gmbh',
			'gbr',
			'gmbh_co_kg',
			'kg',
			'kgag',
			'ohg',
			'ug',
			'ug_co_kg',
		],
		'business_id' => [
			'ag',
			'ev',
			'ek',
			'einzelkaufmann',
			'freelancer',
			'ggmbh',
			'gmbh',
			'gbr',
			'gmbh_co_kg',
			'kg',
			'kgag',
			'ohg',
			'ug',
			'ug_co_kg',
		],
		'representative' => [
			'ag',
			'ev',
			'ek',
			'einzelkaufmann',
			'freelancer',
			'ggmbh',
			'gmbh',
			'gbr',
			'gmbh_co_kg',
			'kg',
			'kgag',
			'ohg',
			'ug',
			'ug_co_kg',
		],
		'capital_stock' => [
			'ag',
			'ev',
			'ek',
			'einzelkaufmann',
			'freelancer',
			'ggmbh',
			'gmbh',
			'gbr',
			'gmbh_co_kg',
			'kg',
			'kgag',
			'ohg',
			'ug',
			'ug_co_kg',
		],
		'pending_deposits' => [
			'ag',
			'ev',
			'ek',
			'einzelkaufmann',
			'freelancer',
			'ggmbh',
			'gmbh',
			'gbr',
			'gmbh_co_kg',
			'kg',
			'kgag',
			'ohg',
			'ug',
			'ug_co_kg',
		],
		'professional_association' => [
			'ag',
			'ev',
			'ek',
			'einzelkaufmann',
			'freelancer',
			'ggmbh',
			'gmbh',
			'gbr',
			'gmbh_co_kg',
			'kg',
			'kgag',
			'ohg',
			'ug',
			'ug_co_kg',
		],
		'legal_job_title' => [
			'ag',
			'ev',
			'ek',
			'einzelkaufmann',
			'freelancer',
			'ggmbh',
			'gmbh',
			'gbr',
			'gmbh_co_kg',
			'kg',
			'kgag',
			'ohg',
			'ug',
			'ug_co_kg',
		],
		'professional_regulations' => [
			'ag',
			'ev',
			'ek',
			'einzelkaufmann',
			'freelancer',
			'ggmbh',
			'gmbh',
			'gbr',
			'gmbh_co_kg',
			'kg',
			'kgag',
			'ohg',
			'ug',
			'ug_co_kg',
		],
	];
	
	/**
	 * Impressum Frontend constructor.
	 * 
	 * @param	string		$plugin_file The path of the main plugin file
	 */
	public function __construct( $plugin_file ) {
		parent::__construct( $plugin_file );
		
		// hooks
		add_action( 'admin_enqueue_scripts', [ $this, 'enqueue_assets' ] );
		add_action( 'admin_notices', [ $this, 'invalid_notice' ] );
		add_action( 'wp_ajax_impressum_dismissed_notice_handler', [ $this, 'ajax_notice_handler' ] );
		add_action( 'update_option_impressum_imprint_options', [ $this, 'reset_invalid_notice' ] );
		
		// shortcodes
		add_shortcode( 'impressum', [ __CLASS__, 'imprint_shortcode' ] );
	}
	
	/**
	 * Imprint Shortcode.
	 */
	public static function imprint_shortcode() {
		$output = self::get_imprint_output();
		
		return $output;
	}
	
	/**
	 * Generate the output for the imprint shortcode.
	 * 
	 * @param	array		$atts All attributes to configure the output.
	 * @return	string
	 */
	public static function get_imprint_output( array $atts = [] ) {
		// check if there is a custom output
		$custom_output = isset( $atts['sections'] ) ?: false;
		// create an empty output array if there isn’t any
		if ( ! isset ( $atts['output'] ) ) $atts['output'] = [];
		
		// default values to configure output
		if ( ! isset( $atts['output']['address'] ) ) $atts['output']['address'] = ! $custom_output;
		if ( ! isset( $atts['output']['address_alternative'] ) ) $atts['output']['address_alternative'] = ! $custom_output;
		if ( ! isset( $atts['output']['coverage'] ) ) $atts['output']['coverage'] = false;
		if ( ! isset( $atts['output']['country'] ) ) $atts['output']['country'] = false;
		if ( ! isset( $atts['output']['email'] ) ) $atts['output']['email'] = ! $custom_output;
		if ( ! isset( $atts['output']['fax'] ) ) $atts['output']['fax'] = ! $custom_output;
		if ( ! isset( $atts['output']['free_text'] ) ) $atts['output']['free_text'] = false;
		if ( ! isset( $atts['output']['legal_entity'] ) ) $atts['output']['legal_entity'] = false;
		if ( ! isset( $atts['output']['name'] ) ) $atts['output']['name'] = ! $custom_output;
		if ( ! isset( $atts['output']['phone'] ) ) $atts['output']['phone'] = ! $custom_output;
		if ( ! isset( $atts['output']['press_law_checkbox'] ) ) $atts['output']['press_law_checkbox'] = false;
		if ( ! isset( $atts['output']['press_law_person'] ) ) $atts['output']['press_law_person'] = ! $custom_output;
		if ( ! isset( $atts['output']['vat_id'] ) ) $atts['output']['vat_id'] = ! $custom_output;
		if ( ! isset( $atts['output']['inspecting_authority'] ) ) $atts['output']['inspecting_authority'] = false;
		if ( ! isset( $atts['output']['register'] ) ) $atts['output']['register'] = false;
		if ( ! isset( $atts['output']['business_id'] ) ) $atts['output']['business_id'] = false;
		if ( ! isset( $atts['output']['representative'] ) ) $atts['output']['representative'] = false;
		if ( ! isset( $atts['output']['capital_stock'] ) ) $atts['output']['capital_stock'] = false;
		if ( ! isset( $atts['output']['pending_deposits'] ) ) $atts['output']['pending_deposits'] = false;
		if ( ! isset( $atts['output']['professional_association'] ) ) $atts['output']['professional_association'] = false;
		if ( ! isset( $atts['output']['legal_job_title'] ) ) $atts['output']['legal_job_title'] = false;
		if ( ! isset( $atts['output']['professional_regulations'] ) ) $atts['output']['professional_regulations'] = false;
		if ( ! isset( $atts['markup'] ) ) {
			$atts['markup'] = true;
		}
		else {
			$atts['markup'] = $atts['markup'] !== 'false' && boolval( $atts['markup'] );
		}
		
		// check the state if we generate markup
		$do_markup = boolval( $atts['markup'] );
		// get every imprint option
		$options = self::impressum_get_option( 'impressum_imprint_options' );
		// get entity
		$entity = $options['legal_entity'];
		// prepare the output
		$output = '';
		
		// abort if there are no valid options
		if ( empty( $options ) ) return '';
		
		if ( $do_markup ) {
			// open imprint container
			$output .= '<div class="imprint-container">';
			// open definition list
			$output .= '<dl>';
		}
		
		foreach ( $options as $field => $value ) {
			// check if we output this value
			$do_output = boolval( $atts['output'][ $field ] ) && $value;
			
			if ( ! $do_output ) continue;
			
			// check if the given field should be displayed for this legal entity
			if ( ! in_array( $entity, self::$field_mapping[ $field ], true ) ) continue;
			
			// the field title
			$title = '';
			
			// get title according to field name
			switch ( $field ) {
				case 'address':
				case 'address_alternative':
					$title = __( 'Address', 'impressum' );
					break;
				case 'coverage':
					$title = __( 'Coverage', 'impressum' );
					break;
				case 'email':
					$title = __( 'Email Address', 'impressum' );
					break;
				case 'fax':
					$title = __( 'Fax', 'impressum' );
					break;
				case 'free_text':
					$title = __( 'Free Text', 'impressum' );
					break;
				case 'legal_entity':
					$title = __( 'Legal Entity', 'impressum' );
					break;
				case 'name':
					$title = __( 'Name', 'impressum' );
					break;
				case 'phone':
					$title = __( 'Phone', 'impressum' );
					break;
				case 'press_law_person':
					$title = __( 'Responsible for content according to § 55 paragraph 2 RStV', 'impressum' );
					break;
				case 'vat_id':
					$title = __( 'VAT ID', 'impressum' );
					break;
				case 'inspecting_authority':
					$title = __( 'Inspecting Authority', 'impressum' );
					break;
				case 'register':
					$title = __( 'Register', 'impressum' );
					break;
				case 'business_id':
					$title = __( 'Business ID', 'impressum' );
					break;
				case 'representative':
					$title = __( 'Representative', 'impressum' );
					break;
				case 'capital_stock':
					$title = __( 'Capital Stock', 'impressum' );
					break;
				case 'pending_deposits':
					$title = __( 'Pending Deposits', 'impressum' );
					break;
				case 'professional_association':
					$title = __( 'Professional Association', 'impressum' );
					break;
				case 'legal_job_title':
					$title = __( 'Legal Job Title', 'impressum' );
					break;
				case 'professional_regulations':
					$title = __( 'Professional Regulations', 'impressum' );
					break;
			}
			
			// check if field should be displayed
			if ( $field === 'press_law_person' && ! isset( $options['press_law_checkbox'] ) ) {
				continue;
			}
		
			// generate output for this field
			if ( $do_markup ) {
				// definition term and description
				$output .= '
	<dt>' . $title . '</dt>
	' . ( $field === 'email'
		? '<dd><a href="mailto:' . $options[ $field ] . '">' . $options[ $field ] . '</a>'
		: '<dd>' . nl2br( esc_html( $options[ $field ] ) ) . '</dd>'
	);
			}
			else {
				// comma separated list
				$output .= esc_html( $options[ $field ] ) . ', ';
			}
		}
		
		if ( $do_markup ) {
			// close definition list
			$output .= '</dl>';
			// close imprint container
			$output .= '</div>';
		}
		else {
			// remove last comma
			$output = rtrim( $output, ', ' );
		}
		
		return $output;
	}
	
	/**
	 * Enqueue scripts.
	 * 
	 * @param	string		$hook The current admin page.
	 */
	public function enqueue_assets( $hook ) {
		// Check for SCRIPT_DEBUG
		$suffix = defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ? '' : '.min';
		$version = defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ? time() : get_plugin_data( __FILE__ )['Version'];
		
		wp_enqueue_script( 'impressum-dismissable-notice', plugins_url( '/assets/js/ajax-dismissable-notice' . $suffix . '.js', $this->plugin_file ), [], $version );
		
		// check for settings page
		if ( 'settings_page_impressum' !== $hook ) return;
		
		// enqueue scripts
		wp_enqueue_script( 'impressum-admin-options', plugins_url( '/assets/js/admin-options' . $suffix . '.js', $this->plugin_file ), [], $version );
		
		// enqueue styles
		wp_enqueue_style( 'impressum-admin-style', plugins_url( '/assets/style/style' . $suffix . '.css', $this->plugin_file ), [], $version );
		
		// prepare for translation
		wp_localize_script( 'impressum-admin-options', 'imprintL10n', [
			'address_error_message' => esc_html__( 'You need to enter an address.', 'impressum' ),
			'country_error_message' => esc_html__( 'You need to select a country.', 'impressum' ),
			'legal_entity_error_message' => esc_html__( 'The Free version doesn’t contain the needed features for your selection. If your legal entity is not “Individual” or “Self-employed”, you need to purchase the Plus version.', 'impressum' ),
			'email_error_message' => esc_html__( 'You need to enter an email address.', 'impressum' ),
			'name_error_message' => esc_html__( 'You need to enter a name.', 'impressum' ),
			'phone_error_message' => esc_html__( 'You need to enter a phone number.', 'impressum' ),
			'vat_id_error_message' => esc_html__( 'The entered value is not valid. Please use a valid format for your VAT ID.', 'impressum' ),
		] );
	}
	
	/**
	 * Add a warning notice if the current imprint is not valid yet.
	 */
	public static function invalid_notice() {
		global $pagenow;
		
		// hide invalid notice on impressum options|settings page
		// phpcs:disable WordPress.CSRF.NonceVerification.NoNonceVerification
		if ( ( $pagenow === 'options-general.php' || $pagenow === 'settings.php' ) && isset( $_GET['page'] ) && $_GET['page'] === 'impressum' ) return;
		// phpcs:enable
		
		if ( ! get_option( 'dismissed-impressum_validation_notice' ) && ! self::is_valid() ) :
		$invalid_fields = self::get_invalid_fields();
		?>
<div class="notice notice-warning is-dismissible impressum-validation-notice" data-notice="impressum_validation_notice">
	<p>
		<?php esc_html_e( 'Your imprint has not been configured successfully, yet.', 'impressum' ); ?>
		<a href="options-general.php?page=impressum&imprint_tab=imprint"><?php esc_html_e( 'Configure now!', 'impressum' ); ?></a>
	</p>
	<?php if ( ! empty( $invalid_fields ) ) : ?>
	<p>
		<?php
		esc_html_e( 'At least the following fields are invalid:', 'impressum' );
		echo '<br>' . esc_html( implode( ', ', $invalid_fields ) );
		?>
	</p>
	<?php endif; ?>
</div>
		<?php
		endif;
	}
	
	/**
	 * Get all invalid fields.
	 * 
	 * @return	array
	 */
	public static function get_invalid_fields() {
		$invalid_fields = [];
		$options = self::impressum_get_option( 'impressum_imprint_options' );
		
		// detect required fields according to the legal entity
		switch ( $options['legal_entity'] ) {
			case 'individual':
			case 'self':
				$required_fields = [
					'address',
					'email',
					'name',
					'phone',
				];
				break;
			default:
				$required_fields = [
					'address',
					'email',
					'name',
					'phone',
					'register',
					'representative',
					'vat_id',
				];
				break;
		}
		
		foreach ( $required_fields as $field ) {
			if ( ! is_array( $options ) || ! array_key_exists( $field, $options ) || empty( $options[ $field ] ) ) {
				if ( ! isset( self::$settings_fields[ $field ] ) ) continue;
				$invalid_fields[ $field ] = self::$settings_fields[ $field ]['title'];
			}
		}
		
		// special case for VAT
		if ( ! isset( $invalid_fields['vat_id'] ) ) {
			$regex = '/^((AT)?U[0-9]{8}|(BE)?0[0-9]{9}|(BG)?[0-9]{9,10}|(CY)?[0-9]{8}L|(CZ)?[0-9]{8,10}|(DE)?[0-9]{9}|(DK)?[0-9]{8}|(EE)?[0-9]{9}|(EL|GR)?[0-9]{9}|(ES)?[0-9A-Z][0-9]{7}[0-9A-Z]|(FI)?[0-9]{8}|(FR)?[0-9A-Z]{2}[0-9]{9}|(GB)?([0-9]{9}([0-9]{3})?|[A-Z]{2}[0-9]{3})|(HU)?[0-9]{8}|(IE)?[0-9]S[0-9]{5}L|(IT)?[0-9]{11}|(LT)?([0-9]{9}|[0-9]{12})|(LU)?[0-9]{8}|(LV)?[0-9]{11}|(MT)?[0-9]{8}|(NL)?[0-9]{9}B[0-9]{2}|(PL)?[0-9]{10}|(PT)?[0-9]{9}|(RO)?[0-9]{2,10}|(SE)?[0-9]{12}|(SI)?[0-9]{8}|(SK)?[0-9]{10})$/';
			
			if ( ! preg_match( $regex, $options['vat_id'] ) ) {
				$invalid_fields['vat_id'] = self::$settings_fields['vat_id']['title'];
			}
		}
		
		return $invalid_fields;
	}
	
	/**
	 * Check if the current imprint is valid.
	 * 
	 * @return	bool
	 */
	public static function is_valid() {
		$options = self::impressum_get_option( 'impressum_imprint_options' );
		
		// return false if there is no imprint option yet
		if ( ! $options || ! isset( $options['legal_entity'] ) || empty( $options['legal_entity'] ) ) {
			return false;
		}
		
		// check for legal entity
		switch ( $options['legal_entity'] ) {
			case 'individual':
			case 'self':
				if (
					! isset( $options['address'] ) || empty( $options['address'] )
					|| ! isset( $options['email'] ) || empty( $options['email'] )
					|| ! isset( $options['name'] ) || empty( $options['name'] )
					|| ! isset( $options['phone'] ) || empty( $options['phone'] )
				) {
					return false;
				}
				break;
			default:
				if (
					! isset( $options['address'] ) || empty( $options['address'] )
					|| ! isset( $options['email'] ) || empty( $options['email'] )
					|| ! isset( $options['name'] ) || empty( $options['name'] )
					|| ! isset( $options['phone'] ) || empty( $options['phone'] )
					|| ! isset( $options['register'] ) || empty( $options['register'] )
					|| ! isset( $options['representative'] ) || empty( $options['representative'] )
					|| ! isset( $options['vat_id'] ) || empty( $options['vat_id'] )
				) {
					return false;
				}
				break;
		}
		
		// the default
		return true;
	}
	
	/**
	 * AJAX handler to store the state of dismissible notices.
	 */
	public static function ajax_notice_handler() {
		// phpcs:disable WordPress.CSRF.NonceVerification.NoNonceVerification
		if ( ! isset( $_POST['type'] ) ) return;
		
		$type = esc_attr( sanitize_text_field( wp_unslash( $_POST['type'] ) ) );
		// phpcs:enable
		update_option( 'dismissed-' . $type, true );
	}
	
	/**
	 * Updated option to reset the dismiss of the imprint validation notice.
	 */
	public static function reset_invalid_notice() {
		update_option( 'dismissed-impressum_validation_notice', false );
	}
}
