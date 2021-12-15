<?php
namespace epiphyt\Impressum;
use function add_settings_field;
use function checked;
use function esc_html;
use function esc_html__;
use function esc_html_e;
use function get_posts;
use function is_network_admin;
use function sanitize_text_field;
use function selected;
use function str_replace;
use function strpos;
use function strtolower;
use function wp_dropdown_pages;
use function wp_unslash;

/**
 * Represents admin fields for the imprint of Impressum.
 * 
 * @author		Epiphyt
 * @license		GPL2 <https://www.gnu.org/licenses/gpl-2.0.html>
 */
class Admin_Fields {
	/**
	 * @var		\epiphyt\Impressum\Admin_Fields
	 */
	private static $instance;
	
	/**
	 * Admin_Fields constructor.
	 */
	public function __construct() {
		self::$instance = $this;
	}
	
	/**
	 * Country field callback.
	 * 
	 * @param	array	$args The field arguments
	 */
	public function country( array $args ) {
		// get the value of the setting we've registered with register_setting()
		$options = Helper::get_option( 'impressum_imprint_options', is_network_admin() );
		?>
		<select id="<?php echo esc_attr( $args['label_for'] ); ?>" name="impressum_imprint_options[<?php echo esc_attr( $args['label_for'] ); ?>]">
			<option value=""><?php esc_html_e( '- Select -', 'impressum' ); ?></option>
				<?php
				foreach ( Impressum::get_instance()->get_countries() as $country_code => $country ) {
					$is_selected = ( ! empty( $options['country'] ) ? selected( $options['country'], $country_code, false ) : ( ! empty( $options['default']['country'] ) ? selected( $options['default']['country'], $country_code, false ) : '' ) );
					
					if ( empty( $options['country'] ) && ! empty( $_SERVER['HTTP_ACCEPT_LANGUAGE'] ) ) {
						$is_selected = ( strpos( strtolower( sanitize_text_field( wp_unslash( $_SERVER['HTTP_ACCEPT_LANGUAGE'] ) ) ), $country_code ) !== false ? ' selected' : '' );
						
						if ( empty( $is_selected ) && ! empty( $country['locale_primary'] ) ) {
							$is_selected = ( strpos( strtolower( sanitize_text_field( wp_unslash( $_SERVER['HTTP_ACCEPT_LANGUAGE'] ) ) ), $country['locale_primary'] ) !== false ? ' selected' : '' );
						}
					}
					
					echo '<option value="' . esc_attr( $country_code ) . '"' . ( $is_selected ?: '' ) . '>' . esc_html( $country['title'] ) . '</option>';
				}
				?>
		</select>
		<p><?php esc_html_e( 'In order to determine the needed fields for your imprint we need to know your country.', 'impressum' ); ?></p>
		<?php
		if ( isset( $args['required'] ) && $args['required'] === true ) {
			echo '<p class="description impressum-required-field">' . esc_html__( 'This is a required field.', 'impressum' ) . '</p>';
		}
	}
	
	/**
	 * Email field callback.
	 * 
	 * @param	array	$args The field arguments
	 */
	public function email( array $args ) {
		// get the value of the setting we've registered with register_setting()
		$options = Helper::get_option( 'impressum_imprint_options', is_network_admin() );
		$placeholder = ( ! empty( $options['default'][ $args['label_for'] ] ) && ! is_network_admin() ? ' placeholder="' . esc_attr( $options['default'][ $args['label_for'] ] ) . '"' : '' );
		$value = ( isset( $options[ $args['label_for'] ] ) ? ' value="' . esc_attr( ( isset( $options[ $args['label_for'] ] ) ? $options[ $args['label_for'] ] : ( isset( $options['default'][ $args['label_for'] ] ) ? $options['default'][ $args['label_for'] ] : '' ) ) ) . '"' : '' );
		// output the field
		?>
		<input type="email" id="<?php echo esc_attr( $args['label_for'] ); ?>" name="impressum_imprint_options[<?php echo esc_attr( $args['label_for'] ); ?>]" class="regular-text"<?php echo $value . $placeholder; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>>
		<?php
		if ( isset( $args['required'] ) && $args['required'] === true ) {
			echo '<p class="description impressum-required-field">' . esc_html__( 'This is a required field.', 'impressum' ) . '</p>';
		}
	}
	
	/**
	 * Get a unique instance of the class.
	 * 
	 * @return	\epiphyt\Impressum\Admin_Fields
	 */
	public static function get_instance() {
		if ( static::$instance === null ) {
			static::$instance = new static();
		}
		
		return static::$instance;
	}
	
	/**
	 * Initialize fields.
	 */
	public function init_fields() {
		// register option fields
		foreach ( Impressum::get_instance()->settings_fields as $id => $settings_field ) {
			add_settings_field(
				$id,
				$settings_field['title'],
				[ $this, $settings_field['callback'] ],
				$settings_field['page'],
				$settings_field['section'],
				$settings_field['args']
			);
		}
	}
	
	/**
	 * Legal Entity field callback.
	 * 
	 * @param	array	$args The field arguments
	 */
	public function legal_entity( array $args ) {
		// get the value of the setting we've registered with register_setting()
		$options = Helper::get_option( 'impressum_imprint_options', is_network_admin() );
		// output the field
		?>
		<select id="<?php echo esc_attr( $args['label_for'] ); ?>" name="impressum_imprint_options[<?php echo esc_attr( $args['label_for'] ); ?>]">
			<option value=""><?php esc_html_e( '- Select -', 'impressum' ); ?></option>
				<?php
				foreach ( Impressum::get_instance()->get_legal_entities() as $abbr => $entity ) {
					$is_selected = ( ! empty( $options['legal_entity'] ) ? selected( $options['legal_entity'], $abbr, false ) : ( ! empty( $options['default']['legal_entity'] ) ? selected( $options['default']['legal_entity'], $abbr, false ) : '' ) );
					
					echo '<option value="' . esc_attr( $abbr ) . '"' . ( $is_selected ?: '' ) . '>' . esc_html( $entity ) . '</option>';
				}
				?>
		</select>
		<p><?php esc_html_e( 'In order to guide you the needed fields we need to know what kind of legal entity you are.', 'impressum' ); ?></p>
		<?php
		if ( isset( $args['required'] ) && $args['required'] === true ) {
			echo '<p class="description impressum-required-field">' . esc_html__( 'This is a required field.', 'impressum' ) . '</p>';
		}
	}
	
	/**
	 * Text input field callback.
	 * 
	 * @param	array	$args The field arguments
	 */
	public function text( array $args ) {
		// get the value of the setting we've registered with register_setting()
		$options = Helper::get_option( 'impressum_imprint_options', is_network_admin() );
		$placeholder = ( ! empty( $options['default'][ $args['label_for'] ] ) && ! is_network_admin() ? ' placeholder="' . esc_attr( $options['default'][ $args['label_for'] ] ) . '"' : '' );
		$value = ( isset( $options[ $args['label_for'] ] ) ? ' value="' . esc_attr( ( isset( $options[ $args['label_for'] ] ) ? $options[ $args['label_for'] ] : ( isset( $options['default'][ $args['label_for'] ] ) ? $options['default'][ $args['label_for'] ] : '' ) ) ) . '"' : '' );
		// output the field
		?>
		<input type="text" id="<?php echo esc_attr( $args['label_for'] ); ?>" name="impressum_imprint_options[<?php echo esc_attr( $args['label_for'] ); ?>]" class="regular-text"<?php echo $value . $placeholder; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>>
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
		
		if ( isset( $args['required'] ) && $args['required'] === true ) {
			echo '<p class="description impressum-required-field">' . esc_html__( 'This is a required field.', 'impressum' ) . '</p>';
		}
	}
	
	/**
	 * Page field callback.
	 * 
	 * @param	array	$args The field arguments
	 */
	public function page( array $args ) {
		if ( is_network_admin() ) {
			echo '<p>' . esc_html__( 'This setting is not available in network options.', 'impressum' ) . '</p>';
			return;
		}
		
		// get the value of the setting we've registered with register_setting()
		$options = Helper::get_option( 'impressum_imprint_options', is_network_admin() );
		$has_pages = (bool) get_posts( [
			'post_type' => 'page',
			'posts_per_page' => 1,
			'post_status' => [ 'draft', 'publish' ],
		] );
		
		// output the field
		if ( $has_pages ) {
			wp_dropdown_pages( [
				'id' => $args['label_for'],
				'name' => 'impressum_imprint_options[' . $args['label_for'] . ']',
				'post_status' => [ 'draft', 'publish' ],
				'show_option_none' => __( '- Select -', 'impressum' ),
				'selected' => ( isset( $options[ $args['label_for'] ] ) ? $options[ $args['label_for'] ] : ( isset( $options['default'][ $args['label_for'] ] ) ? $options['default'][ $args['label_for'] ] : '' ) ), // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
			] );
		}
		else {
			echo '<p>' . esc_html__( 'There are no pages. Please create a page first.', 'impressum' ) . '</p>';
		}
		
		if ( isset( $args['required'] ) && $args['required'] === true ) {
			echo '<p class="description impressum-required-field">' . esc_html__( 'This is a required field.', 'impressum' ) . '</p>';
		}
	}
	
	/**
	 * Phone field callback.
	 * 
	 * @param	array	$args The field arguments
	 */
	public function phone( array $args ) {
		// get the value of the setting we've registered with register_setting()
		$options = Helper::get_option( 'impressum_imprint_options', is_network_admin() );
		$placeholder = ( ! empty( $options['default'][ $args['label_for'] ] ) && ! is_network_admin() ? ' placeholder="' . esc_attr( $options['default'][ $args['label_for'] ] ) . '"' : '' );
		$value = ( isset( $options[ $args['label_for'] ] ) ? ' value="' . esc_attr( ( isset( $options[ $args['label_for'] ] ) ? $options[ $args['label_for'] ] : ( isset( $options['default'][ $args['label_for'] ] ) ? $options['default'][ $args['label_for'] ] : '' ) ) ) . '"' : '' );
		// output the field
		?>
		<input type="tel" id="<?php echo esc_attr( $args['label_for'] ); ?>" name="impressum_imprint_options[<?php echo esc_attr( $args['label_for'] ); ?>]" class="regular-text"<?php echo $value . $placeholder; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>>
		<?php
		if ( isset( $args['required'] ) && $args['required'] === true ) {
			echo '<p class="description impressum-required-field">' . esc_html__( 'This is a required field.', 'impressum' ) . '</p>';
		}
	}
	
	/**
	 * Press Law Checkbox field callback.
	 * 
	 * @param	array	$args The field arguments
	 */
	public function press_law_checkbox( array $args ) {
		// get the value of the setting we've registered with register_setting()
		$options = Helper::get_option( 'impressum_imprint_options', true );
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
	 * @param	array	$args The field arguments
	 */
	public function textarea( array $args ) {
		// get the value of the setting we've registered with register_setting()
		$options = Helper::get_option( 'impressum_imprint_options', is_network_admin() );
		$placeholder = ( ! empty( $options['default'][ $args['label_for'] ] ) && ! is_network_admin() ? ' placeholder="' . esc_html( str_replace( "\r\n", ', ', $options['default'][ $args['label_for'] ] ) ) . '"' : '' );
		$value = ( isset( $options[ $args['label_for'] ] ) ? esc_attr( ( isset( $options[ $args['label_for'] ] ) ? $options[ $args['label_for'] ] : ( isset( $options['default'][ $args['label_for'] ] ) ? $options['default'][ $args['label_for'] ] : '' ) ) ) : '' );
		// output the field
		?>
		<textarea cols="50" rows="10" id="<?php echo esc_attr( $args['label_for'] ); ?>" name="impressum_imprint_options[<?php echo esc_attr( $args['label_for'] ); ?>]"<?php echo $placeholder; ?>><?php echo $value; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></textarea>
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
		
		if ( isset( $args['required'] ) && $args['required'] === true ) {
			echo '<p class="description impressum-required-field">' . esc_html__( 'This is a required field.', 'impressum' ) . '</p>';
		}
	}
}
