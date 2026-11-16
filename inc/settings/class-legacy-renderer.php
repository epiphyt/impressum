<?php
declare(strict_types = 1);

namespace epiphyt\Impressum\settings;

use epiphyt\Impressum\Admin_Fields;

/**
 * Server side rendering of everything the settings app cannot render natively.
 * 
 * Third party code extends the settings page by printing markup – through the
 * impressum_admin_tab filter, through custom field callbacks and through the
 * impressum_settings_form_before and impressum_settings_form_after actions.
 * That markup is captured here and handed to the settings app, which injects it
 * as is.
 * 
 * @since	3.1.0
 * 
 * @author	Epiphyt
 * @license	GPL2
 * @package	epiphyt\Impressum
 */
final class Legacy_Renderer {
	/**
	 * Register the settings API outside of an admin request.
	 * 
	 * admin_init does not run during a REST request, so the global settings
	 * fields and sections the legacy markup relies on are not populated. Firing
	 * admin_init would run the admin bootstrap of every other plugin, so only
	 * the registration of this plugin – and of anything hooking the dedicated
	 * action – is repeated.
	 */
	public function bootstrap_settings_api(): void {
		\epiphyt\Impressum\get_container()->get( 'admin' )->init_settings();
		
		/**
		 * Fires when the settings API is bootstrapped outside of an admin request.
		 * Register settings sections and fields needed for legacy markup here.
		 * 
		 * @since	3.1.0
		 */
		\do_action( 'impressum_rest_register_legacy_settings' );
	}
	
	/**
	 * Get the markup printed before and after the settings form.
	 * 
	 * Both are rendered as siblings of the form, never inside it, since
	 * extensions print whole forms of their own.
	 * 
	 * @param	string	$form_action Form action URL
	 * @param	string	$current_tab Currently active tab
	 * @param	string	$default_tab Default tab
	 * @return	array{after: string, before: string} Markup before and after the form
	 */
	public function get_form_html( string $form_action, string $current_tab, string $default_tab ): array {
		\ob_start();
		
		/**
		 * This action is described in inc/class-admin.php
		 */
		\do_action( 'impressum_settings_form_before', $form_action, $current_tab, $default_tab );
		
		$before = (string) \ob_get_clean();
		
		\ob_start();
		
		/**
		 * This action is described in inc/class-admin.php
		 */
		\do_action( 'impressum_settings_form_after', $form_action, $current_tab, $default_tab );
		
		return [
			'after' => (string) \ob_get_clean(),
			'before' => $before,
		];
	}
	
	/**
	 * Get the full legacy payload.
	 * 
	 * Fields are not part of it: the markup of a field that needs it is
	 * attached to the field itself while the schema is built, so rendering all
	 * of them here would only duplicate it.
	 * 
	 * @param	array{bootstrap?: bool, current_tab?: string, default_tab?: string, form_action?: string}	$args Rendering arguments
	 * @return	array{form: array{after: string, before: string}, tabs: mixed[]} Legacy payload
	 */
	public function get_legacy_payload( array $args = [] ): array {
		if ( ! empty( $args['bootstrap'] ) ) {
			$this->bootstrap_settings_api();
		}
		
		$current_tab = (string) ( $args['current_tab'] ?? '' );
		$default_tab = (string) ( $args['default_tab'] ?? 'imprint' );
		$form_action = (string) ( $args['form_action'] ?? '' );
		
		return [
			'form' => $this->get_form_html( $form_action, $current_tab, $default_tab ),
			'tabs' => $this->get_tabs( $form_action, $current_tab ),
		];
	}
	
	/**
	 * Get all tabs registered through the settings page filter.
	 * 
	 * The imprint tab is passed through with a placeholder content, so that
	 * filters reordering or removing tabs keep working, and is replaced by the
	 * natively rendered tab afterwards.
	 * 
	 * @param	string	$form_action Form action URL
	 * @param	string	$current_tab Currently active tab
	 * @return	mixed[] List of tabs
	 */
	public function get_tabs( string $form_action, string $current_tab ): array {
		$tabs = [
			[
				'appendHtml' => $this->get_imprint_tab_content(),
				'content' => '',
				'slug' => 'imprint',
				'title' => \__( 'Imprint', 'impressum' ),
			],
		];
		
		/**
		 * This filter is described in inc/class-admin.php
		 */
		return (array) \apply_filters( 'impressum_admin_tab', $tabs, $form_action, $current_tab );
	}
	
	/**
	 * Get the static markup that follows the imprint fields.
	 * 
	 * Before the settings app this filter received the whole tab including all
	 * rendered fields. The fields are rendered by the app now, so it only
	 * receives the static part that follows them.
	 * 
	 * @return	string Rendered markup
	 */
	private function get_imprint_tab_content(): string {
		\ob_start();
		?>
		<h2><?php \esc_html_e( 'Disclaimer', 'impressum' ); ?></h2>
		<p><?php \esc_html_e( 'Please keep in mind that this plugin does not guarantee any legal compliance. You are responsible for the data you enter here. This plugin helps you to fill all necessary fields.', 'impressum' ); ?></p>

		<h2><?php \esc_html_e( 'Usage', 'impressum' ); ?></h2>
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
		<?php
		$content = (string) \ob_get_clean();
		
		/**
		 * This filter is described in inc/class-admin.php
		 */
		return (string) \apply_filters( 'impressum_imprint_tab_content', $content );
	}
	
	/**
	 * Render a single field the settings app cannot render natively.
	 * 
	 * The callback is resolved exactly the way the settings API registration
	 * resolves it, so that a callback instance swapped in by an extension keeps
	 * working.
	 * 
	 * @param	\epiphyt\Impressum\settings\Setting	$setting Setting to render
	 * @return	string Rendered markup, or an empty string if there is no callback
	 */
	public function render_field( Setting $setting ): string {
		$callback = $setting->get_data( 'setting_callback' );
		$id = \sprintf( '%1$s_%2$s', $setting->type, $setting->name );
		
		/**
		 * This filter is described in inc/class-admin-fields.php
		 */
		$callback_instance = \apply_filters(
			'impressum_admin_fields_callback_instance',
			Admin_Fields::get_instance(),
			$setting,
			$id
		);
		
		if ( empty( $callback ) || ! \is_callable( [ $callback_instance, $callback ] ) ) {
			return '';
		}
		
		\ob_start();
		$callback_instance->{$callback}( $setting->get_data( 'setting_attributes' ) );
		
		return (string) \ob_get_clean();
	}
}
