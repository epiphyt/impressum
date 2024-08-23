<?php
namespace epiphyt\Impressum;
use function __;
use function add_action;
use function add_filter;
use function add_settings_section;
use function add_submenu_page;
use function apply_filters;
use function array_key_exists;
use function array_merge;
use function current_user_can;
use function defined;
use function do_settings_sections;
use function esc_attr;
use function esc_html;
use function esc_html__;
use function esc_html_e;
use function esc_url;
use function file_exists;
use function filemtime;
use function get_admin_page_title;
use function implode;
use function is_array;
use function ob_get_clean;
use function ob_start;
use function plugin_dir_path;
use function plugin_dir_url;
use function plugins_url;
use function preg_match;
use function printf;
use function register_setting;
use function sanitize_text_field;
use function settings_errors;
use function settings_fields;
use function sprintf;
use function submit_button;
use function update_option;
use function wp_enqueue_script;
use function wp_enqueue_style;
use function wp_localize_script;
use function wp_register_style;
use function wp_send_json_error;
use function wp_send_json_success;
use function wp_set_script_translations;
use function wp_unslash;

/**
 * Represents functions for the admin in Impressum.
 * 
 * @author		Epiphyt
 * @license		GPL2 <https://www.gnu.org/licenses/gpl-2.0.html>
 */
class Admin {
	/**
	 * @var		bool Whether the backend is disabled or not
	 */
	private static $backend_disabled = false;
	
	/**
	 * @var		bool If admin notice is disabled or not
	 */
	private static $disabled_notice = false;
	
	/**
	 * @var		\epiphyt\Impressum\Admin
	 */
	private static $instance;
	
	/**
	 * @var		string The full path to the main plugin file
	 */
	public $plugin_file = '';
	
	/**
	 * Admin constructor.
	 */
	public function __construct() {
		self::$instance = $this;
	}
	
	/**
	 * Initialize the admin functions.
	 */
	public function init() {
		// hooks
		add_action( 'admin_enqueue_scripts', [ $this, 'enqueue_assets' ] );
		add_action( 'admin_init', [ $this, 'init_settings' ] );
		add_action( 'admin_menu', [ $this, 'options_page' ] );
		add_action( 'admin_notices', [ $this, 'invalid_notice' ] );
		add_action( 'admin_notices', [ $this, 'welcome_notice' ] );
		add_action( 'enqueue_block_editor_assets', [ $this, 'block_assets' ] );
		add_action( 'update_option_impressum_imprint_options', [ $this, 'reset_invalid_notice' ] );
		add_action( 'wp_ajax_impressum_dismissed_notice_handler', [ $this, 'ajax_notice_handler' ] );
		
		add_filter( 'impressum_admin_tab', [ $this, 'register_plus_tab' ], 10, 3 );
	}
	
	/**
	 * AJAX handler to store the state of dismissible notices.
	 */
	public function ajax_notice_handler() {
		if ( apply_filters( 'impressum_disabled_notice', self::$disabled_notice ) === true ) {
			return;
		}
		
		// phpcs:disable WordPress.Security.NonceVerification.Recommended, WordPress.Security.NonceVerification.Missing
		if ( ! isset( $_POST['type'] ) ) {
			wp_send_json_error();
		}
		
		$type = esc_attr( sanitize_text_field( wp_unslash( $_POST['type'] ) ) );
		// phpcs:enable
		
		if ( update_option( 'dismissed-' . $type, true ) ) {
			wp_send_json_success();
		}
		
		wp_send_json_error();
	}
	
	/**
	 * Enqueue block assets.
	 */
	public function block_assets() {
		// automatically load dependencies and version
		$asset_file = include plugin_dir_path( $this->plugin_file ) . 'build/imprint.asset.php' ;
		
		wp_enqueue_script( 'impressum-imprint-block', plugin_dir_url( $this->plugin_file ) . 'build/imprint.js', $asset_file['dependencies'], $asset_file['version'] );
		wp_localize_script( 'impressum-imprint-block', 'impressum_fields', [
			'fields' => Impressum::get_instance()->settings_fields,
			'values' => Impressum::get_instance()->get_block_fields( 'impressum_imprint_options' ),
		] );
		wp_set_script_translations( 'impressum-imprint-block', 'impressum' );
		wp_register_style( 'impressum-imprint-block-editor-styles', plugin_dir_url( $this->plugin_file ) . 'build/editor.css', [], $asset_file['version'] );
	}
	
	/**
	 * Enqueue admin assets.
	 * 
	 * @param	string	$hook The current admin page
	 */
	public function enqueue_assets( $hook ) {
		// check for SCRIPT_DEBUG
		$suffix = defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ? '' : '.min';
		
		$file_path = plugin_dir_path( $this->plugin_file ) . 'assets/js/ajax-dismissible-notice' . $suffix . '.js';
		
		if ( file_exists( $file_path ) ) {
			wp_enqueue_script( 'impressum-dismissible-notice', plugins_url( '/assets/js/ajax-dismissible-notice' . $suffix . '.js', $this->plugin_file ), [], filemtime( $file_path ) );
		}
		
		// check for settings page
		if ( 'settings_page_impressum' !== $hook ) {
			return;
		}
		
		$file_path = plugin_dir_path( $this->plugin_file ) . '/assets/js/admin-options' . $suffix . '.js';
		
		if ( file_exists( $file_path ) ) {
			wp_enqueue_script( 'impressum-admin-options', plugins_url( '/assets/js/admin-options' . $suffix . '.js', $this->plugin_file ), [], filemtime( $file_path ) );
		}
		
		$file_path = plugin_dir_path( $this->plugin_file ) . '/assets/style/style' . $suffix . '.css';
		
		if ( file_exists( $file_path ) ) {
			wp_enqueue_style( 'impressum-admin-style', plugins_url( '/assets/style/style' . $suffix . '.css', $this->plugin_file ), [], filemtime( $file_path ) );
		}
		
		// prepare for translation
		wp_localize_script( 'impressum-admin-options', 'imprintL10n', [
			'addressErrorMessage' => esc_html__( 'You need to enter an address.', 'impressum' ),
			'countryErrorMessage' => esc_html__( 'You need to select a country.', 'impressum' ),
			'emailErrorMessage' => esc_html__( 'You need to enter an email address.', 'impressum' ),
			'legalEntityErrorMessage' => esc_html__( 'The Free version doesn’t contain the needed features for your selection. If your legal entity is not “Individual” or “Self-employed”, you need to purchase the Plus version.', 'impressum' ),
			'nameErrorMessage' => esc_html__( 'You need to enter a name.', 'impressum' ),
			'phoneErrorMessage' => esc_html__( 'You need to enter a phone number.', 'impressum' ),
			'vatIdErrorMessage' => esc_html__( 'The entered value is not valid. Please use a valid format for your VAT ID.', 'impressum' ),
		] );
	}
	
	/**
	 * Custom option and settings.
	 */
	public function init_settings() {
		// register a new setting for "impressum" page
		register_setting( 'impressum_imprint', 'impressum_imprint_options' );
		
		// register a new section in the "impressum" page
		add_settings_section(
			'impressum_section_imprint',
			null,
			null,
			'impressum_imprint'
		);
		
		// register option fields
		Admin_Fields::get_instance()->init_fields();
	}
	
	/**
	 * Get a unique instance of the class.
	 * 
	 * @return	\epiphyt\Impressum\Admin
	 */
	public static function get_instance() {
		if ( static::$instance === null ) {
			static::$instance = new static();
		}
		
		return static::$instance;
	}
	
	/**
	 * Get all invalid fields.
	 * 
	 * @return	array A list of invalid fields
	 */
	private function get_invalid_fields() {
		$invalid_fields = [];
		$options = Helper::get_option( 'impressum_imprint_options', true );
		
		// get defaults
		if ( ! isset( $options['legal_entity'] ) ) {
			$defaults = ( isset( $options['default'] ) ? $options['default'] : [] );
			$options = $defaults;
		}
		
		// set default if there are no defaults yet
		if ( ! isset( $options['legal_entity'] ) ) {
			$options['legal_entity'] = 'individual';
		}
		
		// detect required fields according to the legal entity
		switch ( $options['legal_entity'] ) {
			default:
				$required_fields = [
					'address',
					'email',
					'name',
					'phone',
				];
				break;
		}
		
		foreach ( $required_fields as $field ) {
			if ( ! is_array( $options ) || ! array_key_exists( $field, $options ) || empty( $options[ $field ] ) ) {
				if ( ! isset( Impressum::get_instance()->settings_fields[ $field ] ) ) continue;
				$invalid_fields[ $field ] = Impressum::get_instance()->settings_fields[ $field ]['title'];
			}
		}
		
		// special case for VAT
		if ( ! isset( $invalid_fields['vat_id'] ) ) {
			$regex = '/^(|(AT)?U[0-9]{8}|(BE)?0[0-9]{9}|(BG)?[0-9]{9,10}|(CY)?[0-9]{8}L|(CZ)?[0-9]{8,10}|(DE)?[0-9]{9}|(DK)?[0-9]{8}|(EE)?[0-9]{9}|(EL|GR)?[0-9]{9}|(ES)?[0-9A-Z][0-9]{7}[0-9A-Z]|(FI)?[0-9]{8}|(FR)?[0-9A-Z]{2}[0-9]{9}|(GB)?([0-9]{9}([0-9]{3})?|[A-Z]{2}[0-9]{3})|(HU)?[0-9]{8}|(IE)?[0-9]S[0-9]{5}L|(IT)?[0-9]{11}|(LT)?([0-9]{9}|[0-9]{12})|(LU)?[0-9]{8}|(LV)?[0-9]{11}|(MT)?[0-9]{8}|(NL)?[0-9]{9}B[0-9]{2}|(PL)?[0-9]{10}|(PT)?[0-9]{9}|(RO)?[0-9]{2,10}|(SE)?[0-9]{12}|(SI)?[0-9]{8}|(SK)?[0-9]{10})$/';
			
			if ( ! empty( $options['vat_id'] ) && ! preg_match( $regex, $options['vat_id'] ) ) {
				$invalid_fields['vat_id'] = Impressum::get_instance()->settings_fields['vat_id']['title'];
			}
		}
		
		return $invalid_fields;
	}
	
	/**
	 * Add a warning notice if the current imprint is not valid yet.
	 */
	public function invalid_notice() {
		if ( apply_filters( 'impressum_disabled_notice', self::$disabled_notice ) === true ) {
			return;
		}
		
		global $pagenow;
		
		// hide invalid notice on impressum options|settings page
		if ( ( $pagenow === 'options-general.php' || $pagenow === 'settings.php' ) && isset( $_GET['page'] ) && $_GET['page'] === 'impressum' ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
			return;
		}
		
		if ( ! get_option( 'dismissed-impressum_validation_notice' ) && ! $this->is_valid_imprint() ) :
		$invalid_fields = $this->get_invalid_fields();
		?>
<div class="notice notice-warning is-dismissible impressum-validation-notice" data-notice="impressum_validation_notice">
	<p>
		<?php esc_html_e( 'Your imprint has not been configured successfully, yet.', 'impressum' ); ?>
		<a href="options-general.php?page=impressum&imprint_tab=imprint"><?php esc_html_e( 'Configure now!', 'impressum' ); ?></a>
	</p>
	<?php if ( ! empty( $invalid_fields ) ) : ?>
	<p>
		<?php
		esc_html_e( 'Please make sure, you fill out at least the following fields:', 'impressum' );
		echo '<br>' . esc_html( implode( ', ', $invalid_fields ) );
		?>
	</p>
	<?php endif; ?>
</div>
		<?php
		endif;
	}
	
	/**
	 * Check if the current imprint is valid.
	 * Valid means: All necessary fields are filled with data.
	 * 
	 * @return	bool True if imprint is valid, false otherwise
	 */
	public function is_valid_imprint() {
		$options = Helper::get_option( 'impressum_imprint_options', true );
		
		// merge global and local options
		if ( ! empty( $options['default'] ) ) {
			$options_global = $options['default'];
			unset( $options['default'] );
			$options = array_merge( $options_global, $options );
		}
		
		// return false if there is no imprint option yet
		if ( ! $options || ! isset( $options['legal_entity'] ) || empty( $options['legal_entity'] ) ) {
			return false;
		}
		
		// check for legal entity
		switch ( $options['legal_entity'] ) {
			default:
				if (
					! isset( $options['address'] ) || empty( $options['address'] )
					|| ! isset( $options['email'] ) || empty( $options['email'] )
					|| ! isset( $options['name'] ) || empty( $options['name'] )
					|| ! isset( $options['phone'] ) || empty( $options['phone'] )
				) {
					return false;
				}
				break;
		}
		
		// the default
		return true;
	}
	
	/**
	 * Add sub menu item in options menu.
	 */
	public static function options_page() {
		if ( apply_filters( 'impressum_disabled_backend', self::$backend_disabled ) === true ) {
			return;
		}
		
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
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}
		
		// show error/update messages
		settings_errors( 'impressum_messages' );
		
		/**
		 * Filter the default tab.
		 * 
		 * @param	string	$default_tab The default tab
		 */
		$default_tab = apply_filters( 'impressum_admin_default_tab', 'imprint' );
		
		// get current tab
		// phpcs:disable WordPress.Security.NonceVerification.Recommended
		$current_tab = isset( $_GET['imprint_tab'] ) ? sanitize_text_field( wp_unslash( $_GET['imprint_tab'] ) ) : $default_tab;
		// phpcs:enable
		
		// set form action
		$form_action = 'options.php';
		
		ob_start();
		?>
		<div class="nav-tab-content<?php echo ( $current_tab === 'imprint' ? ' nav-tab-content-active' : '' ); ?>" id="nav-tab-content-imprint">
			<form action="<?php echo esc_html( $form_action ); ?>" method="post">
				<?php
				// output security fields for the registered setting "impressum"
				settings_fields( 'impressum_imprint' );
				// output setting sections and their fields
				// (sections are registered for "impressum", each field is registered to a specific section)
				do_settings_sections( 'impressum_imprint' );
				?>
				<h3><?php esc_html_e( 'Disclaimer', 'impressum' ); ?></h3>
				<p><?php esc_html_e( 'Please keep in mind that this plugin does not guarantee any legal compliance. You are responsible for the data you enter here. “Impressum” helps you to fill all necessary fields.', 'impressum' ); ?></p>
				<?php
				// output save settings button
				submit_button( esc_html__( 'Save Settings', 'impressum' ) );
				?>
			</form>
			
			<h3><?php esc_html_e( 'Usage', 'impressum' ); ?></h3>
			<p><?php \esc_html_e( 'There are two methods available on how to output the imprint:', 'impressum' ); ?></p>
			<ol>
				<li><?php \esc_html_e( 'Add the "Imprint" block in your block editor wherever you want to output your imprint. It works everywhere the block editor is supported.', 'impressum' ); ?></li>
				<li>
					<?php
					\printf(
						/* translators: shortcode name */
						\esc_html__( 'Add the %s in your editor wherever you want to output your imprint. It works everywhere shortcodes are supported.', 'impressum' ),
						'<code>[impressum]</code>'
					);
					?></li>
			</ol>
		</div>
		<?php
		$content = ob_get_clean();
		
		/**
		 * Filter the imprint tab content.
		 * 
		 * @param	string	$content The imprint tab content
		 */
		$content = apply_filters( 'impressum_imprint_tab_content', $content );
		
		$tabs = [];
		$tabs[] = [
			'content' => $content,
			'slug' => 'imprint',
			'title' => __( 'Imprint', 'impressum' ),
		];
		
		/**
		 * Filter tabs to the content.
		 * Make sure the following keys exist and are not empty:
		 * - content
		 * - slug
		 * - title
		 * 
		 * @param	array	$tabs Tabs in the backend
		 * @param	string	$form_action The current form action
		 * @param	string	$current_tab The current active tab
		 */
		$tabs = apply_filters( 'impressum_admin_tab', $tabs, $form_action, $current_tab );
		?>
		<div class="wrap impressum-wrap">
			<h1><?php echo esc_html( get_admin_page_title() ); ?></h1>
			<h2 class="nav-tab-wrapper">
				<?php
				foreach ( $tabs as $tab ) :
				if ( empty( $tab['slug'] ) || empty( $tab['title'] ) ) {
					continue;
				}
				?>
				<a href="?page=impressum&imprint_tab=<?php echo esc_attr( $tab['slug'] ); ?>" class="nav-tab<?php echo $current_tab === $tab['slug'] ? ' nav-tab-active' : ''; ?>" data-slug="<?php echo esc_attr( $tab['slug'] ); ?>"><?php echo esc_html( $tab['title'] ); ?></a>
				<?php endforeach; ?>
			</h2>
			<?php // phpcs:disable WordPress.WhiteSpace.PrecisionAlignment.Found ?>
			
			<?php // phpcs:enable ?>
			<div class="impressum-content-wrapper">
				<?php
				foreach ( $tabs as $tab ) {
					if ( empty( $tab['content'] ) || empty( $tab['slug'] ) ) continue;
					if ( $current_tab !== $tab['slug'] ) continue;
					
					echo $tab['content']; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
				}
				?>
			</div>
		</div>
		<?php
	}
	
	/**
	 * Register the Impressum Plus tab.
	 * 
	 * @param	array	$tabs Currently registered tabs
	 * @return	array All registered tabs
	 */
	public function register_plus_tab( $tabs ) {
		$slug = 'get_plus';
		ob_start();
		?>
		<h3><?php esc_html_e( 'Get an imprint for your company website!', 'impressum' ); ?></h3>
		<p>
			<?php
			/* translators: 1: plugin name, 2: commercial plugin name */
			printf( esc_html__( 'We designed %1$s to be the perfect companion to individuals for all things around the imprint on their WordPress websites. However, if your site is operated by another legal entity than an individual person, %2$s is the plugin you should use.', 'impressum' ), '<em>' . esc_html__( 'Impressum', 'impressum' ) . '</em>', '<em>' . esc_html__( 'Impressum Plus', 'impressum' ) . '</em>' );
			?>
		</p>
		<p>
			<?php
			/* translators: commercial plugin name */
			printf( esc_html__( 'For a small fee, %s will provide you with the same seamless user experience as the free version. But in addition to the free versions features it will also cover a load of different legal entities and their quite diverse need for imprint data.', 'impressum' ), '<em>' . esc_html__( 'Impressum Plus', 'impressum' ) . '</em>' );
			?>
		</p>
		<h3><?php esc_html_e( 'Go Plus to support development', 'impressum' ); ?></h3>
		<p>
			<?php
			/* translators: commercial plugin name */
			printf( esc_html__( 'Even as a private website owner you can upgrade to %s anytime. Every single Plus user means the world to us, since it\'s those users who support our ongoing work on both the free and paid version. In addition, we\'ll continue to add even more nifty features to Plus.', 'impressum' ), '<em>' . esc_html__( 'Impressum Plus', 'impressum' ) . '</em>' );
			?>
		</p>
		<p><a href="<?php echo esc_url( __( 'https://impressum.plus/en/', 'impressum' ) ); ?>" class="button button-primary button-hero"><?php esc_html_e( 'Get Impressum Plus now', 'impressum' ); ?></a></p>
		
		<h2><?php esc_html_e( 'Compare now', 'impressum' ); ?></h2>
		<table class="wp-list-table widefat striped impressum__compare-table">
			<tbody>
				<thead>
					<th><strong><?php esc_html_e( 'Feature', 'impressum' ); ?></strong></th>
					<th><strong><em><?php esc_html_e( 'Impressum', 'impressum' ); ?></em></strong></th>
					<th><strong><em><?php esc_html_e( 'Impressum Plus', 'impressum' ); ?></em></strong></th>
				</thead>
				<tr>
					<td><strong><?php esc_html_e( 'Imprint Generator', 'impressum' ); ?></strong></td>
					<td><span class="green"><?php esc_html_e( 'Yes', 'impressum' ); ?></span></td>
					<td><span class="green"><?php esc_html_e( 'Yes', 'impressum' ); ?></span></td>
				</tr>
				<tr>
					<td><strong><?php esc_html_e( 'Privacy Policy Generator', 'impressum' ); ?></strong></td>
					<td><span class="red"><?php esc_html_e( 'No', 'impressum' ); ?></span></td>
					<td><span class="green"><?php esc_html_e( 'Yes', 'impressum' ); ?></span></td>
				</tr>
				<tr>
					<td><?php esc_html_e( 'Multisite: Base Compatibility', 'impressum' ); ?></td>
					<td><span class="green"><?php esc_html_e( 'Yes', 'impressum' ); ?></span></td>
					<td><span class="green"><?php esc_html_e( 'Yes', 'impressum' ); ?></span></td>
				</tr>
				<tr>
					<td><?php esc_html_e( 'Block Editor Support', 'impressum' ); ?></td>
					<td><span class="green"><?php esc_html_e( 'Yes', 'impressum' ); ?></span></td>
					<td><span class="green"><?php esc_html_e( 'Yes', 'impressum' ); ?></span></td>
				</tr>
				<tr>
					<td><?php esc_html_e( 'Legal content for personal usage', 'impressum' ); ?></td>
					<td><span class="green"><?php esc_html_e( 'Yes', 'impressum' ); ?></span></td>
					<td><span class="green"><?php esc_html_e( 'Yes', 'impressum' ); ?></span><br></td>
				</tr>
				<tr>
					<td><?php esc_html_e( 'Legal content for private companies', 'impressum' ); ?></td>
					<td><span class="red"><?php esc_html_e( 'No', 'impressum' ); ?></span></td>
					<td><span class="green"><?php esc_html_e( 'Yes', 'impressum' ); ?></span></td>
				</tr>
				<tr>
					<td><?php esc_html_e( 'Legal content for corporations', 'impressum' ); ?></td>
					<td><span class="red"><?php esc_html_e( 'No', 'impressum' ); ?></span></td>
					<td><span class="green"><?php esc_html_e( 'Yes', 'impressum' ); ?></span></td>
				</tr>
				<tr>
					<td><?php esc_html_e( 'Multisite: preset for new sites', 'impressum' ); ?></td>
					<td><span class="red"><?php esc_html_e( 'No', 'impressum' ); ?></span></td>
					<td><span class="green"><?php esc_html_e( 'Yes', 'impressum' ); ?></span></td>
				</tr>
				<tr>
					<td><?php esc_html_e( 'Enhanced REST API', 'impressum' ); ?></td>
					<td><span class="red"><?php esc_html_e( 'No', 'impressum' ); ?></span></td>
					<td><span class="green"><?php esc_html_e( 'Yes', 'impressum' ); ?></span></td>
				</tr>
				<tr>
					<td><?php esc_html_e( 'Many filters for developers', 'impressum' ); ?></td>
					<td><span class="red"><?php esc_html_e( 'No', 'impressum' ); ?></span> <?php esc_html_e( '(10+)', 'impressum' ); ?></td>
					<td><span class="green"><?php esc_html_e( 'Yes', 'impressum' ); ?></span> <?php esc_html_e( '(50+)', 'impressum' ); ?></td>
				</tr>
				<tr>
					<td><br></td>
					<td></td>
					<td>
						<a href="<?php echo esc_url( __( 'https://epiph.yt/en/product/impressum-plus/', 'impressum' ) ); ?>" class="button button-primary"><?php esc_html_e( 'Purchase', 'impressum' ); ?></a>
					</td>
				</tr>
			</tbody>
		</table>
		<?php
		$content = ob_get_clean();
		$tabs[] = [
			'content' => $content,
			'slug' => $slug,
			'title' => __( 'Get Plus', 'impressum' ),
		];
		
		return $tabs;
	}
	
	/**
	 * Set the plugin file.
	 * 
	 * @param	string	$file The path to the file
	 */
	public function set_plugin_file( $file ) {
		if ( file_exists( $file ) ) {
			$this->plugin_file = $file;
		}
	}
	
	/**
	 * Updated option to reset the dismiss of the imprint validation notice.
	 */
	public function reset_invalid_notice() {
		if ( apply_filters( 'impressum_disabled_notice', self::$disabled_notice ) === true ) {
			return;
		}
		
		update_option( 'dismissed-impressum_validation_notice', false );
	}
	/**
	 * Add a welcome notice.
	 */
	public function welcome_notice() {
		global $pagenow;
		
		// hide invalid notice everywhere except on impressum options|settings page
		// phpcs:disable WordPress.Security.NonceVerification.Recommended
		if (
			$pagenow !== 'options-general.php'
			&& $pagenow !== 'settings.php'
			|| ( isset( $_GET['page'] ) && $_GET['page'] !== 'impressum' )
			|| ! isset( $_GET['page'] )
		) {
			return;
		}
		// phpcs:enable
		
		if ( ! get_option( 'dismissed-impressum_welcome_notice' ) ) :
		?>
		<div class="impressum-wrap">
			<div class="impressum-welcome-panel" data-notice="impressum_welcome_notice">
				<div class="impressum-welcome-panel-content">
					<h2>
						<?php
						/* translators: plugin name */
						printf( esc_html__( 'Welcome to %s, we’re glad you’re here!', 'impressum' ), '<em>' . esc_html__( 'Impressum', 'impressum' ) . '</em>' );
						?>
					</h2>
					<p class="about-description">
						<?php
						/* translators: plugin name */
						printf( esc_html__( '%s is a generator for legal content, integrated into your WordPress interface.', 'impressum' ), '<em>' . esc_html__( 'Impressum', 'impressum' ) . '</em>' );
						?>
					</p>
					<hr>
					<?php // phpcs:disable WordPress.WhiteSpace.PrecisionAlignment.Found ?>
					
					<?php // phpcs:enable ?>
					<div class="impressum-welcome-panel-column-container">
						<div class="impressum-welcome-panel-column">
							<div>
								<h3>
									<?php
									/* translators: plugin name */
									printf( esc_html__( '%s is free, because we love you', 'impressum' ), '<em>' . esc_html__( 'Impressum', 'impressum' ) . '</em>' );
									?>
								</h3>
								<p>
									<?php
									/* translators: plugin name */
									printf( esc_html__( 'Best things in life are free. That’s why we decided to make this plugin available for free for everyone running a WordPress site as private person or single person business. That’s you? Awesome, then give %s a spin and generate your first legal content without leaving your site.', 'impressum' ), '<em>' . esc_html__( 'Impressum', 'impressum' ) . '</em>' );
									?>
								</p>
							</div>
							<?php // phpcs:disable WordPress.WhiteSpace.PrecisionAlignment.Found ?>
							
							<?php // phpcs:enable ?>
							<div class="impressum-welcome-action">
								<p><a class="button button-secondary button-hero impressum-welcome-notice-dismiss" data-notice="impressum_welcome_notice"><?php esc_html_e( 'Hide this message', 'impressum' ); ?></a></p>
							</div>
						</div>
						<?php // phpcs:disable WordPress.WhiteSpace.PrecisionAlignment.Found ?>
						
						<?php // phpcs:enable ?>
						<div class="impressum-welcome-panel-column">
							<div>
								<h3>
									<?php
									/* translators: commercial plugin name */
									printf( esc_html__( 'Got bigger plans? %s is here for you', 'impressum' ), '<em>' . esc_html__( 'Impressum Plus', 'impressum' ) . '</em>' );
									?>
								</h3>
								<p>
									<?php
									/* translators: 1: plugin name, 2: commercial plugin name */
									printf( esc_html__( 'If this site is run by a corporation or partnership, you might like %1$s’s bigger brother %2$s. With advanced features and support for multiple kinds of legal entities, %2$s covers business from your local book shop to bigger multi-location corporations.', 'impressum' ), '<em>' . esc_html__( 'Impressum', 'impressum' ) . '</em>', '<em>' . esc_html__( 'Impressum Plus', 'impressum' ) . '</em>' );
									?>
								</p>
							</div>
							<?php // phpcs:disable WordPress.WhiteSpace.PrecisionAlignment.Found ?>
							
							<?php // phpcs:enable ?>
							<div class="impressum-welcome-action">
								<p><a class="button button-primary button-hero" href="https://impressum.plus"><?php esc_html_e( 'Learn more about Plus', 'impressum' ); ?></a></p>
							</div>
						</div>
						<?php // phpcs:disable WordPress.WhiteSpace.PrecisionAlignment.Found ?>
						
						<?php // phpcs:enable ?>
						<div class="impressum-welcome-panel-column">
							<div>
								<h3><?php esc_html_e( 'Hi there, we are Epiphyt 👋', 'impressum' ); ?></h3>
								<p><?php esc_html_e( 'Epiphyt is a small WordPress coding shop from southern Germany. As members of the German WordPress community we value clean code, straight forward communication and the GPL.', 'impressum' ); ?></p>
							</div>
							<?php // phpcs:disable WordPress.WhiteSpace.PrecisionAlignment.Found ?>
							
							<?php // phpcs:enable ?>
							<div class="impressum-welcome-action">
								<p><a href="https://epiph.yt"><?php esc_html_e( 'Get in touch with us or read more on epiph.yt', 'impressum' ); ?></a></p>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>
		<?php
		endif;
	}
}
