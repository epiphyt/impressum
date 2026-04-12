<?php
namespace epiphyt\Impressum;

use epiphyt\Impressum\settings\Registry;

/**
 * Represents functions for the admin in Impressum.
 * 
 * @author	Epiphyt
 * @license	GPL2
 * @package	epiphyt\Impressum
 */
final class Admin {
	/**
	 * @var		bool Whether the backend is disabled or not
	 */
	private static bool $backend_disabled = false;
	
	/**
	 * @var		bool If admin notice is disabled or not
	 */
	private static bool $disabled_notice = false;
	
	/**
	 * @var		?\epiphyt\Impressum\settings\Registry Settings registry
	 */
	public ?\epiphyt\Impressum\settings\Registry $settings_registry = null;
	
	/**
	 * Admin constructor.
	 * 
	 * @param	\epiphyt\Impressum\settings\Registry		$settings_registry Settings registry
	 */
	public function __construct( Registry $settings_registry ) {
		$this->settings_registry = $settings_registry;
	}
	
	/**
	 * Initialize the admin functions.
	 */
	public function init(): void {
		\add_action( 'admin_enqueue_scripts', [ $this, 'enqueue_assets' ] );
		\add_action( 'admin_init', [ $this, 'init_settings' ] );
		\add_action( 'admin_menu', [ $this, 'register_options_page' ] );
		\add_action( 'admin_notices', [ $this, 'invalid_notice' ] );
		\add_action( 'admin_notices', [ $this, 'welcome_notice' ] );
		\add_action( 'update_option_impressum_imprint_options', [ $this, 'reset_invalid_notice' ] );
		\add_action( 'wp_ajax_impressum_dismissed_notice_handler', [ $this, 'ajax_notice_handler' ] );
		\add_filter( 'impressum_admin_tab', [ $this, 'register_plus_tab' ] );
		\add_filter( 'plugin_row_meta', [ self::class, 'render_plugin_documentation_link' ], 10, 2 );
	}
	
	/**
	 * AJAX handler to store the state of dismissible notices.
	 */
	public function ajax_notice_handler(): void {
		if ( \apply_filters( 'impressum_disabled_notice', self::$disabled_notice ) === true ) {
			return;
		}
		
		// phpcs:disable WordPress.Security.NonceVerification.Recommended, WordPress.Security.NonceVerification.Missing
		if ( ! isset( $_POST['type'] ) ) {
			\wp_send_json_error();
		}
		
		$type = \esc_attr( \sanitize_text_field( \wp_unslash( $_POST['type'] ) ) );
		// phpcs:enable
		
		if ( \update_option( 'dismissed-' . $type, true ) ) {
			\wp_send_json_success();
		}
		
		\wp_send_json_error();
	}
	
	/**
	 * Enqueue admin assets.
	 * 
	 * @param	string	$hook The current admin page
	 */
	public function enqueue_assets( string $hook ): void {
		$is_debug = ( \defined( 'SCRIPT_DEBUG' ) && \SCRIPT_DEBUG ) || ( \defined( 'WP_DEBUG' ) && \WP_DEBUG );
		$suffix = $is_debug ? '' : '.min';
		$file_path = \EPI_IMPRESSUM_BASE . 'assets/js/' . ( $is_debug ? '' : 'build/' ) . 'ajax-dismissible-notice' . $suffix . '.js';
		
		if ( \file_exists( $file_path ) ) {
			$file_version = $is_debug ? (string) \filemtime( $file_path ) : \EPI_IMPRESSUM_VERSION;
			
			\wp_enqueue_script( 'impressum-dismissible-notice', \EPI_IMPRESSUM_URL . 'assets/js/' . ( $is_debug ? '' : 'build/' ) . 'ajax-dismissible-notice' . $suffix . '.js', [], $file_version );
		}
		
		// check for settings page
		if ( $hook !== 'settings_page_impressum' ) {
			return;
		}
		
		$file_path = \EPI_IMPRESSUM_BASE . 'assets/js/' . ( $is_debug ? '' : 'build/' ) . 'admin-options' . $suffix . '.js';
		
		if ( \file_exists( $file_path ) ) {
			$file_version = $is_debug ? (string) \filemtime( $file_path ) : \EPI_IMPRESSUM_VERSION;
			
			\wp_enqueue_script( 'impressum-admin-options', \EPI_IMPRESSUM_URL . 'assets/js/' . ( $is_debug ? '' : 'build/' ) . 'admin-options' . $suffix . '.js', [], $file_version );
		}
		
		$file_path = \EPI_IMPRESSUM_BASE . 'assets/js/' . ( $is_debug ? '' : 'build/' ) . 'admin-tabs' . $suffix . '.js';
		
		if ( \file_exists( $file_path ) ) {
			$file_version = $is_debug ? (string) \filemtime( $file_path ) : \EPI_IMPRESSUM_VERSION;
			
			\wp_enqueue_script( 'impressum-admin-tabs', \EPI_IMPRESSUM_URL . 'assets/js/' . ( $is_debug ? '' : 'build/' ) . 'admin-tabs' . $suffix . '.js', [], $file_version );
		}
		
		$file_path = \EPI_IMPRESSUM_BASE . 'assets/style/build/style' . $suffix . '.css';
		
		if ( \file_exists( $file_path ) ) {
			$file_version = $is_debug ? (string) \filemtime( $file_path ) : \EPI_IMPRESSUM_VERSION;
			
			\wp_enqueue_style( 'impressum-admin-style', \EPI_IMPRESSUM_URL . 'assets/style/build/style' . $suffix . '.css', [], $file_version );
		}
		
		// prepare for translation
		\wp_localize_script( 'impressum-admin-options', 'imprintL10n', [
			'addressErrorMessage' => \esc_html__( 'You need to enter an address.', 'impressum' ),
			'businessIdErrorMessage' => \esc_html__( 'The entered value is not valid. Please use a valid format for your business ID.', 'impressum' ),
			'businessIdOrVatIdMessage' => \esc_html__( 'Either the business ID or the VAT ID have to be set, if one of them is available.', 'impressum' ),
			'contactFormPageErrorMessage' => \esc_html__( 'You need to enter a phone number or a contact form page.', 'impressum' ),
			'countryErrorMessage' => \esc_html__( 'You need to select a country.', 'impressum' ),
			'emailErrorMessage' => \esc_html__( 'You need to enter an email address.', 'impressum' ),
			'legalEntityErrorMessage' => \esc_html__( 'The Free version doesn’t contain the needed features for your selection. If your legal entity is not “Individual” or “Self-employed”, you need to purchase the Plus version.', 'impressum' ),
			'nameErrorMessage' => \esc_html__( 'You need to enter a name.', 'impressum' ),
			'phoneErrorMessage' => \esc_html__( 'You need to enter a phone number or a contact form page.', 'impressum' ),
			'vatIdErrorMessage' => \esc_html__( 'The entered value is not valid. Please use a valid format for your VAT ID.', 'impressum' ),
		] );
	}
	
	/**
	 * Custom option and settings.
	 */
	public function init_settings(): void {
		\register_setting( 'impressum_imprint', 'impressum_imprint_options' );
		\add_settings_section( 'impressum_section_imprint', null, '__return_null', 'impressum_imprint' );
		Admin_Fields::get_instance()->init_fields();
	}
	
	/**
	 * Get all invalid fields.
	 * 
	 * @return	array A list of invalid fields
	 */
	public function get_invalid_fields(): array {
		$invalid_fields = [];
		$options = Helper::get_option( 'impressum_imprint_options', true );
		$settings = $this->settings_registry->get_settings( 'impressum_imprint_options' );
		$settings_prefix = 'impressum_imprint_options_';
		
		// get defaults
		if ( ! isset( $options['legal_entity'] ) ) {
			$defaults = ( $options['default'] ?? [] );
			$options = $defaults;
		}
		
		// set default if there are no defaults yet
		if ( ! isset( $options['legal_entity'] ) ) {
			$options['legal_entity'] = 'individual';
		}
		
		$required_fields = [
			'address',
			'email',
			'name',
		];
		
		/**
		 * Filter required fields.
		 * 
		 * @since	2.1.0
		 * 
		 * @param	string[]	$required_fields List of required fields
		 * @param	array		$options Imprint options
		 */
		$required_fields = (array) \apply_filters( 'impressum_required_fields', $required_fields, $options );
		
		foreach ( $required_fields as $field ) {
			if ( ! \is_array( $options ) || ! \array_key_exists( $field, $options ) || empty( $options[ $field ] ) ) {
				if ( ! isset( $settings[ $settings_prefix . $field ] ) ) {
					continue;
				}
				
				$invalid_fields[ $field ] = $settings[ $settings_prefix . $field ]->get_title();
			}
		}
		
		// special case for phone and contact_form_page
		if ( empty( $options['phone'] ) && empty( $options['contact_form_page'] ) ) {
			$invalid_fields['phone_contact_form'] = \sprintf(
				/* translators: 1: a field title, 2: a field title */
				\__( '%1$s or %2$s', 'impressum' ),
				$settings[ $settings_prefix . 'phone' ]->get_title(),
				$settings[ $settings_prefix . 'contact_form_page' ]->get_title()
			);
		}
		
		// special case for VAT ID
		if ( ! isset( $invalid_fields['vat_id'] ) ) {
			$regex = '/^(|ATU[0-9]{8}|BE0[0-9]{9}|BG[0-9]{9,10}|CY[0-9]{8}L|CZ[0-9]{8,10}|DE[0-9]{9}|DK[0-9]{8}|EE[0-9]{9}|(EL|GR)[0-9]{9}|ES[0-9A-Z][0-9]{7}[0-9A-Z]|FI[0-9]{8}|FR[0-9A-Z]{2}[0-9]{9}|GB([0-9]{9}([0-9]{3})?|[A-Z]{2}[0-9]{3})|HU[0-9]{8}|IE[0-9]S[0-9]{5}L|IT[0-9]{11}|LT([0-9]{9}|[0-9]{12})|LU[0-9]{8}|LV[0-9]{11}|MT[0-9]{8}|NL[0-9\+\*]{9}B[0-9]{2}|PL[0-9]{10}|PT[0-9]{9}|RO[0-9]{2,10}|SE[0-9]{12}|SI[0-9]{8}|SK[0-9]{10})$/';
			
			if ( ! empty( $options['vat_id'] ) && ! \preg_match( $regex, $options['vat_id'] ) ) {
				$invalid_fields['vat_id'] = $settings[ $settings_prefix . 'vat_id' ]->get_title();
			}
		}
		
		// special case for business ID
		if ( ! isset( $invalid_fields['business_id'] ) ) {
			$regex = '/^(|(DE)?[0-9]{9}\-[0-9]{5})$/';
			
			if ( ! empty( $options['business_id'] ) && ! \preg_match( $regex, $options['business_id'] ) ) {
				$invalid_fields['business_id'] = $settings[ $settings_prefix . 'business_id' ]->get_title();
			}
		}
		
		\asort( $invalid_fields, \SORT_NATURAL );
		
		return $invalid_fields;
	}
	
	/**
	 * Add a warning notice if the current imprint is not valid yet.
	 */
	public function invalid_notice(): void {
		if ( \apply_filters( 'impressum_disabled_notice', self::$disabled_notice ) === true ) {
			return;
		}
		
		global $pagenow;
		
		// phpcs:disable WordPress.Security.NonceVerification.Recommended
		// hide invalid notice on impressum options|settings page
		if (
			(
				$pagenow === 'options-general.php'
				|| $pagenow === 'settings.php'
			)
			&& isset( $_GET['page'] )
			&& $_GET['page'] === 'impressum'
		) {
			return;
		}
		// phpcs:enable WordPress.Security.NonceVerification.Recommended
		
		if ( ! \get_option( 'dismissed-impressum_validation_notice' ) && ! $this->is_valid_imprint() ) :
		$invalid_fields = $this->get_invalid_fields();
		?>
		<div class="notice notice-warning is-dismissible impressum-validation-notice" data-notice="impressum_validation_notice">
			<p>
				<?php \esc_html_e( 'Your imprint has not been configured successfully, yet.', 'impressum' ); ?>
				<a href="options-general.php?page=impressum&imprint_tab=imprint"><?php \esc_html_e( 'Configure now!', 'impressum' ); ?></a>
			</p>
			<?php if ( ! empty( $invalid_fields ) ) : ?>
			<p>
				<?php
				\esc_html_e( 'Please make sure, you fill out at least the following fields:', 'impressum' );
				echo '<br>' . \esc_html( \implode( ', ', $invalid_fields ) );
				?>
			</p>
			<?php endif; ?>
		</div>
		<?php
		endif;
	}
	
	/**
	 * Check if the current imprint is valid.
	 * Valid means: All required fields are filled with data.
	 * 
	 * @return	bool True if imprint is valid, false otherwise
	 */
	public function is_valid_imprint(): bool {
		$options = Helper::get_option( 'impressum_imprint_options', true );
		
		// merge global and local options
		if ( ! empty( $options['default'] ) ) {
			$options_global = $options['default'];
			unset( $options['default'] );
			$options = \array_merge( $options_global, $options );
		}
		
		// return false if there is no imprint option yet
		if ( ! $options || ! isset( $options['legal_entity'] ) || empty( $options['legal_entity'] ) ) {
			return false;
		}
		
		$required_fields = [
			'address',
			'email',
			'name',
		];
		
		/**
		 * Filter required fields.
		 * 
		 * @since	2.1.0
		 * 
		 * @param	string[]	$required_fields List of required fields
		 * @param	array		$options Imprint options
		 */
		$required_fields = (array) \apply_filters( 'impressum_required_fields', $required_fields, $options );
		
		foreach ( $required_fields as $required_field ) {
			if ( empty( $options[ $required_field ] ) ) {
				return false;
			}
		}
		
		$is_valid = ! ( empty( $options['phone'] ) && empty( $options['contact_form_page'] ) );
		
		/**
		 * Filter whether the imprint fields are valid.
		 * 
		 * @since	2.1.0
		 * 
		 * @param	bool	$is_valid Whether the imprint fields are valid
		 * @param	array	$options Imprint options
		 */
		$is_valid = \apply_filters( 'impressum_is_valid_imprint', $is_valid, $options );
		
		return $is_valid;
	}
	
	/**
	 * Add sub menu item in options menu.
	 */
	public static function register_options_page(): void {
		if ( \apply_filters( 'impressum_disabled_backend', self::$backend_disabled ) === true ) {
			return;
		}
		
		// add top level menu page
		\add_submenu_page(
			'options-general.php',
			'Impressum',
			'Impressum',
			'manage_options',
			'impressum',
			[ self::class, 'render_options_page' ]
		);
	}
	
	/**
	 * Register the Impressum Plus tab.
	 * 
	 * @param	array	$tabs Currently registered tabs
	 * @return	array All registered tabs
	 */
	public function register_plus_tab( array $tabs ): array {
		$slug = 'get_plus';
		\ob_start();
		?>
		<div class="nav-tab-content" id="nav-tab-content-get_plus">
			<h3><?php \esc_html_e( 'Get an imprint for your company website!', 'impressum' ); ?></h3>
			<p>
				<?php
				/* translators: 1: plugin name, 2: commercial plugin name */
				\printf( \esc_html__( 'We designed %1$s to be the perfect companion to individuals for all things around the imprint on their WordPress websites. However, if your site is operated by another legal entity than an individual person, %2$s is the plugin you should use.', 'impressum' ), \esc_html__( 'Impressum', 'impressum' ), \esc_html__( 'Impressum Plus', 'impressum' ) );
				?>
			</p>
			<p>
				<?php
				/* translators: commercial plugin name */
				\printf( \esc_html__( 'For a small fee, %s will provide you with the same seamless user experience as the free version. But in addition to the free versions features it will also cover a load of different legal entities and their quite diverse need for imprint data.', 'impressum' ), \esc_html__( 'Impressum Plus', 'impressum' ) );
				?>
			</p>
			<h3><?php \esc_html_e( 'Go Plus to support development', 'impressum' ); ?></h3>
			<p>
				<?php
				/* translators: commercial plugin name */
				\printf( \esc_html__( 'Even as a private website owner you can upgrade to %s anytime. Every single Plus user means the world to us, since it\'s those users who support our ongoing work on both the free and paid version. In addition, we\'ll continue to add even more nifty features to Plus.', 'impressum' ), \esc_html__( 'Impressum Plus', 'impressum' ) );
				?>
			</p>
			<p><a href="<?= \esc_url( \__( 'https://impressum.plus/en/', 'impressum' ) ); ?>" class="button button-primary button-hero"><?php \esc_html_e( 'Get Impressum Plus now', 'impressum' ); ?></a></p>
			
			<h2><?php \esc_html_e( 'Compare now', 'impressum' ); ?></h2>
			<table class="wp-list-table widefat striped impressum__compare-table">
				<tbody>
					<thead>
						<th><strong><?php \esc_html_e( 'Feature', 'impressum' ); ?></strong></th>
						<th><strong><?php \esc_html_e( 'Impressum', 'impressum' ); ?></strong></th>
						<th><strong><?php \esc_html_e( 'Impressum Plus', 'impressum' ); ?></strong></th>
					</thead>
					<tr>
						<td><strong><?php \esc_html_e( 'Imprint Generator', 'impressum' ); ?></strong></td>
						<td><span class="green"><?php \esc_html_e( 'Yes', 'impressum' ); ?></span></td>
						<td><span class="green"><?php \esc_html_e( 'Yes', 'impressum' ); ?></span></td>
					</tr>
					<tr>
						<td><strong><?php \esc_html_e( 'Privacy Policy Generator', 'impressum' ); ?></strong></td>
						<td><span class="red"><?php \esc_html_e( 'No', 'impressum' ); ?></span></td>
						<td><span class="green"><?php \esc_html_e( 'Yes', 'impressum' ); ?></span></td>
					</tr>
					<tr>
						<td><strong><?php \esc_html_e( 'Accessibility Information Generator', 'impressum' ); ?></strong></td>
						<td><span class="red"><?php \esc_html_e( 'No', 'impressum' ); ?></span></td>
						<td><span class="green"><?php \esc_html_e( 'Yes', 'impressum' ); ?></span></td>
					</tr>
					<tr>
						<td><?php \esc_html_e( 'Multisite: Base Compatibility', 'impressum' ); ?></td>
						<td><span class="green"><?php \esc_html_e( 'Yes', 'impressum' ); ?></span></td>
						<td><span class="green"><?php \esc_html_e( 'Yes', 'impressum' ); ?></span></td>
					</tr>
					<tr>
						<td><?php \esc_html_e( 'Block Editor Support', 'impressum' ); ?></td>
						<td><span class="green"><?php \esc_html_e( 'Yes', 'impressum' ); ?></span></td>
						<td><span class="green"><?php \esc_html_e( 'Yes', 'impressum' ); ?></span></td>
					</tr>
					<tr>
						<td><?php \esc_html_e( 'Legal content for personal usage', 'impressum' ); ?></td>
						<td><span class="green"><?php \esc_html_e( 'Yes', 'impressum' ); ?></span></td>
						<td><span class="green"><?php \esc_html_e( 'Yes', 'impressum' ); ?></span><br></td>
					</tr>
					<tr>
						<td><?php \esc_html_e( 'Legal content for private companies', 'impressum' ); ?></td>
						<td><span class="red"><?php \esc_html_e( 'No', 'impressum' ); ?></span></td>
						<td><span class="green"><?php \esc_html_e( 'Yes', 'impressum' ); ?></span></td>
					</tr>
					<tr>
						<td><?php \esc_html_e( 'Legal content for corporations', 'impressum' ); ?></td>
						<td><span class="red"><?php \esc_html_e( 'No', 'impressum' ); ?></span></td>
						<td><span class="green"><?php \esc_html_e( 'Yes', 'impressum' ); ?></span></td>
					</tr>
					<tr>
						<td><?php \esc_html_e( 'Multisite: preset for new sites', 'impressum' ); ?></td>
						<td><span class="red"><?php \esc_html_e( 'No', 'impressum' ); ?></span></td>
						<td><span class="green"><?php \esc_html_e( 'Yes', 'impressum' ); ?></span></td>
					</tr>
					<tr>
						<td><?php \esc_html_e( 'WP-CLI support', 'impressum' ); ?></td>
						<td><span class="red"><?php \esc_html_e( 'No', 'impressum' ); ?></span></td>
						<td><span class="green"><?php \esc_html_e( 'Yes', 'impressum' ); ?></span></td>
					</tr>
					<tr>
						<td><?php \esc_html_e( 'Enhanced REST API', 'impressum' ); ?></td>
						<td><span class="red"><?php \esc_html_e( 'No', 'impressum' ); ?></span></td>
						<td><span class="green"><?php \esc_html_e( 'Yes', 'impressum' ); ?></span></td>
					</tr>
					<tr>
						<td><?php \esc_html_e( 'Many filters for developers', 'impressum' ); ?></td>
						<td><span class="red"><?php \esc_html_e( 'No', 'impressum' ); ?></span> <?php \esc_html_e( '(10+)', 'impressum' ); ?></td>
						<td><span class="green"><?php \esc_html_e( 'Yes', 'impressum' ); ?></span> <?php \esc_html_e( '(50+)', 'impressum' ); ?></td>
					</tr>
					<tr>
						<td><br></td>
						<td></td>
						<td>
							<a href="<?= \esc_url( \__( 'https://epiph.yt/en/?add-to-cart=26', 'impressum' ) ); ?>" class="button button-primary"><?php \esc_html_e( 'Purchase', 'impressum' ); ?> <span class="screen-reader-text"><?php \esc_html_e( 'Impressum Plus', 'impressum' ); ?></span></a>
							<a href="<?= \esc_url( \__( 'https://impressum.plus/en/', 'impressum' ) ); ?>" class="button button-secondary"><?php \esc_html_e( 'More information', 'impressum' ); ?> <span class="screen-reader-text"><?= \esc_html_x( 'about Impressum Plus', 'more information about the plugin', 'impressum' ); ?></a>
						</td>
					</tr>
				</tbody>
			</table>
		</div>
		<?php
		$content = \ob_get_clean();
		$tabs[] = [
			'content' => $content,
			'slug' => $slug,
			'title' => \__( 'Get Plus', 'impressum' ),
		];
		
		return $tabs;
	}
	
	/**
	 * Sub menu item:
	 * callback functions
	 */
	public static function render_options_page(): void {
		// check user capabilities
		if ( ! \current_user_can( 'manage_options' ) ) {
			return;
		}
		
		// show error/update messages
		\settings_errors( 'impressum_messages' );
		
		/**
		 * Filter the default tab.
		 * 
		 * @param	string	$default_tab The default tab
		 */
		$default_tab = \apply_filters( 'impressum_admin_default_tab', 'imprint' );
		
		// get current tab
		// phpcs:disable WordPress.Security.NonceVerification.Recommended
		$current_tab = isset( $_GET['imprint_tab'] ) ? \sanitize_text_field( \wp_unslash( $_GET['imprint_tab'] ) ) : $default_tab;
		// phpcs:enable
		
		// set form action
		$form_action = \admin_url( 'options.php' );
		
		if ( \is_network_admin() ) {
			$form_action = \network_admin_url( 'edit.php?action=impressum_imprint-options' );
		}
		
		\ob_start();
		?>
		<div class="nav-tab-content nav-tab-content-active" id="nav-tab-content-imprint">
			<?php
			// output setting sections and their fields
			// (sections are registered for "impressum", each field is registered to a specific section)
			Helper::do_settings_sections( 'impressum_imprint' );
			?>
			<h3><?php \esc_html_e( 'Disclaimer', 'impressum' ); ?></h3>
			<p><?php \esc_html_e( 'Please keep in mind that this plugin does not guarantee any legal compliance. You are responsible for the data you enter here. This plugin helps you to fill all necessary fields.', 'impressum' ); ?></p>
			
			<h3><?php \esc_html_e( 'Usage', 'impressum' ); ?></h3>
			<p><?php \esc_html_e( 'There are two methods available on how to output the imprint:', 'impressum' ); ?></p>
			<ul class="impressum__regular-list">
				<li><?php \esc_html_e( 'Add the "Imprint" block in your block editor wherever you want to output your imprint. It works everywhere the block editor is supported.', 'impressum' ); ?></li>
				<li>
					<?php
					\printf(
						/* translators: shortcode name */
						\esc_html__( 'Add the %s in your editor wherever you want to output your imprint. It works everywhere shortcodes are supported.', 'impressum' ),
						'<code>[impressum]</code>'
					);
					?>
				</li>
			</ul>
		</div>
		<?php
		$content = \ob_get_clean();
		
		/**
		 * Filter the imprint tab content.
		 * 
		 * @param	string	$content The imprint tab content
		 */
		$content = \apply_filters( 'impressum_imprint_tab_content', $content );
		
		$tabs = [];
		$tabs[] = [
			'content' => $content,
			'slug' => 'imprint',
			'title' => \__( 'Imprint', 'impressum' ),
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
		$tabs = \apply_filters( 'impressum_admin_tab', $tabs, $form_action, $current_tab );
		?>
		<div class="wrap impressum-wrap">
			<h1><?= \esc_html( \get_admin_page_title() ); ?></h1>
			
			<?php \do_action( 'impressum_settings_form_before', $form_action, $current_tab, $default_tab ); ?>
			
			<form action="<?= \esc_html( $form_action ); ?>" method="post">
				<input type="hidden" name="option_page" value="impressum_imprint" />
				<input type="hidden" name="action" value="update" />
				
				<?php 
				\wp_nonce_field( 'impressum_imprint-options', '_wpnonce', false );
				
				$referer = \remove_query_arg( '_wp_http_referer' );
				
				if ( ! \str_contains( $referer, '&imprint_tab=' ) && $current_tab !== $default_tab ) {
					$referer .= '&imprint_tab=' . $current_tab;
				}
				?>
				<input type="hidden" name="_wp_http_referer" value="<?= \esc_url( $referer ); ?>" />
				
				<div class="nav-tab-wrapper" role="tablist">
					<?php
					foreach ( $tabs as $tab ) :
					if ( empty( $tab['slug'] ) || empty( $tab['title'] ) ) {
						continue;
					}
					
					$is_active_tab = $current_tab === $tab['slug'];
					?>
					<button type="button" id="tab-<?= \esc_attr( $tab['slug'] ); ?>" data-tab="<?= \esc_attr( $tab['slug'] ); ?>" class="nav-tab<?= $is_active_tab ? ' nav-tab-active' : ''; ?>" role="tab" aria-selected="<?= $is_active_tab ? 'true' : 'false'; ?>" data-slug="<?= \esc_attr( $tab['slug'] ); ?>" tabindex="<?= $is_active_tab ? '0' : '-1'; ?>"><?= \esc_html( $tab['title'] ); ?></button>
					<?php endforeach; ?>
				</div>
				
				<div class="impressum-content-wrapper">
					<?php
					foreach ( $tabs as $tab ) {
						$is_active_tab = $current_tab === $tab['slug'];
						
						echo '<div id="nav-tab__content--' . \esc_attr( $tab['slug'] ) . '" class="nav-tab__content" role="tabpanel" aria-labelledby="tab-' . \esc_attr( $tab['slug'] ) . '"' . ( ! $is_active_tab ? ' hidden' : '' ) . ' tabindex="' . ( $is_active_tab ? '0' : '-1' ) . '">' . $tab['content'] . '</div>'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
					}
					
					\submit_button( \esc_html__( 'Save Settings', 'impressum' ) );
					?>
				</div>
			</form>
			
			<?php \do_action( 'impressum_settings_form_after', $form_action, $current_tab, $default_tab ); ?>
		</div>
		<?php
	}
	
	/**
	 * Add plugin meta links.
	 * 
	 * @param	array	$input Registered links.
	 * @param	string	$file  Current plugin file.
	 * @return	array Merged links
	 */
	public static function render_plugin_documentation_link( array $input, string $file ): array {
		if ( ! \str_ends_with( \EPI_IMPRESSUM_FILE, $file ) ) {
			return $input;
		}
		
		return \array_merge(
			$input,
			[
				/* translators: plugin version */
				'<a href="' . \esc_url( \sprintf( \__( 'https://docs.epiph.yt/impressum/?version=%s', 'impressum' ), \get_plugin_data( \EPI_IMPRESSUM_FILE )['Version'] ) ) . '" target="_blank" rel="noopener noreferrer">' . \esc_html__( 'Documentation', 'impressum' ) . '</a>',
			]
		);
	}
	
	/**
	 * Updated option to reset the dismiss of the imprint validation notice.
	 */
	public function reset_invalid_notice(): void {
		if ( \apply_filters( 'impressum_disabled_notice', self::$disabled_notice ) === true ) {
			return;
		}
		
		\update_option( 'dismissed-impressum_validation_notice', false );
	}
	
	/**
	 * Add a welcome notice.
	 */
	public function welcome_notice(): void {
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
		
		if ( ! \get_option( 'dismissed-impressum_welcome_notice' ) ) :
		?>
		<div class="wrap impressum-wrap">
			<div class="impressum-welcome-panel" data-notice="impressum_welcome_notice">
				<div class="impressum-welcome-panel-content">
					<h2>
						<?php
						/* translators: plugin name */
						\printf( \esc_html__( 'Welcome to %s, we’re glad you’re here!', 'impressum' ), \esc_html__( 'Impressum', 'impressum' ) );
						?>
					</h2>
					<p class="about-description">
						<?php
						/* translators: plugin name */
						\printf( \esc_html__( '%s is a generator for legal content, integrated into your WordPress interface.', 'impressum' ), \esc_html__( 'Impressum', 'impressum' ) );
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
									\printf( \esc_html__( '%s is free, because we love you ❤️', 'impressum' ), \esc_html__( 'Impressum', 'impressum' ) );
									?>
								</h3>
								<p>
									<?php
									/* translators: plugin name */
									\printf( \esc_html__( 'Best things in life are free. That’s why we decided to make this plugin available for free for everyone running a WordPress site as private person or single person business. That’s you? Awesome, then give %s a spin and generate your first legal content without leaving your site.', 'impressum' ), \esc_html__( 'Impressum', 'impressum' ) );
									?>
								</p>
							</div>
							<?php // phpcs:disable WordPress.WhiteSpace.PrecisionAlignment.Found ?>
							
							<?php // phpcs:enable ?>
							<div class="impressum-welcome-action">
								<p><button type="button" class="button button-secondary button-hero impressum-welcome-notice-dismiss" data-notice="impressum_welcome_notice"><?php \esc_html_e( 'Hide this message', 'impressum' ); ?></button></p>
							</div>
						</div>
						<?php // phpcs:disable WordPress.WhiteSpace.PrecisionAlignment.Found ?>
						
						<?php // phpcs:enable ?>
						<div class="impressum-welcome-panel-column">
							<div>
								<h3>
									<?php
									/* translators: commercial plugin name */
									\printf( \esc_html__( 'Got bigger plans? %s is here for you', 'impressum' ), \esc_html__( 'Impressum Plus', 'impressum' ) );
									?>
								</h3>
								<p>
									<?php
									/* translators: 1: plugin name, 2: commercial plugin name */
									\printf( \esc_html__( 'If this site is run by a corporation or partnership, you might like %1$s’s bigger brother %2$s. With advanced features and support for multiple kinds of legal entities, %2$s covers business from your local book shop to bigger multi-location corporations.', 'impressum' ), \esc_html__( 'Impressum', 'impressum' ), \esc_html__( 'Impressum Plus', 'impressum' ) );
									?>
								</p>
							</div>
							<?php // phpcs:disable WordPress.WhiteSpace.PrecisionAlignment.Found ?>
							
							<?php // phpcs:enable ?>
							<div class="impressum-welcome-action">
								<p><a class="button button-primary button-hero" href="<?= \esc_url( \__( 'https://impressum.plus/en/', 'impressum' ) ); ?>"><?php \esc_html_e( 'Learn more about Plus', 'impressum' ); ?></a></p>
							</div>
						</div>
						<?php // phpcs:disable WordPress.WhiteSpace.PrecisionAlignment.Found ?>
						
						<?php // phpcs:enable ?>
						<div class="impressum-welcome-panel-column">
							<div>
								<h3><?php \esc_html_e( 'Hi there, we are Epiphyt 👋', 'impressum' ); ?></h3>
								<p><?php \esc_html_e( 'Epiphyt is a small WordPress coding shop from southern Germany. As members of the German WordPress community we value clean code, straight forward communication and the GPL.', 'impressum' ); ?></p>
							</div>
							<?php // phpcs:disable WordPress.WhiteSpace.PrecisionAlignment.Found ?>
							
							<?php // phpcs:enable ?>
							<div class="impressum-welcome-action">
								<p><a href="<?= \esc_url( \__( 'https://epiph.yt/en/', 'impressum' ) ); ?>"><?php \esc_html_e( 'Get in touch with us or read more on epiph.yt', 'impressum' ); ?></a></p>
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
