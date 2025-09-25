<?php
namespace epiphyt\Impressum;

/**
 * Represents admin fields for the imprint of Impressum.
 * 
 * @author	Epiphyt
 * @license	GPL2
 * @package	epiphyt\Impressum
 */
class Admin_Fields {
	use Singleton;
	
	/**
	 * Checkbox field callback.
	 * 
	 * @param	array	$args The field arguments
	 */
	public function checkbox( array $args ) {
		$settings_name = self::get_settings_name( $args );
		$options = Helper::get_option( $settings_name, true );
		?>
		<label for="<?php echo \esc_attr( $args['label_for'] ); ?>"><input type="checkbox" id="<?php echo \esc_attr( $args['label_for'] ); ?>" name="<?php echo \esc_attr( $settings_name ); ?>[<?php echo \esc_attr( $args['label_for'] ); ?>]" value="1"<?php \checked( isset( $options[ $args['label_for'] ] ) ); ?>>
			<?php echo \esc_html( isset( $args['label'] ) ? $args['label'] : '' ); ?>
		</label>
		<?php
	}
	
	/**
	 * Country field callback.
	 * 
	 * @param	array	$args The field arguments
	 */
	public function country( array $args ) {
		$settings_name = self::get_settings_name( $args );
		$options = Helper::get_option( $settings_name, ! \is_network_admin() );
		?>
		<select id="<?php echo \esc_attr( $args['label_for'] ); ?>" name="<?php echo \esc_attr( $settings_name ); ?>[<?php echo \esc_attr( $args['label_for'] ); ?>]">
			<option value=""><?php \esc_html_e( '- Select -', 'impressum' ); ?></option>
				<?php
				foreach ( Impressum::get_instance()->get_countries() as $country_code => $country ) {
					$is_selected = ( ! empty( $options['country'] ) ? \selected( $options['country'], $country_code, false ) : ( ! empty( $options['default']['country'] ) ? \selected( $options['default']['country'], $country_code, false ) : '' ) );
					
					if ( empty( $options['country'] ) && ! empty( $_SERVER['HTTP_ACCEPT_LANGUAGE'] ) ) {
						$accept_language = \sanitize_text_field( \wp_unslash( $_SERVER['HTTP_ACCEPT_LANGUAGE'] ) );
						$is_selected = ( \strpos( \strtolower( $accept_language ), $country_code ) !== false ? ' selected' : '' );
						
						if ( empty( $is_selected ) && ! empty( $country['locale_primary'] ) ) {
							$is_selected = ( \strpos( \strtolower( $accept_language ), $country['locale_primary'] ) !== false ? ' selected' : '' );
						}
					}
					
					echo '<option value="' . \esc_attr( $country_code ) . '"' . ( $is_selected ?: '' ) . '>' . \esc_html( $country['title'] ) . '</option>'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
				}
				?>
		</select>
		<p class="description impressum__description"><?php \esc_html_e( 'In order to determine the needed fields for your imprint we need to know your country.', 'impressum' ); ?></p>
		<?php
		if ( isset( $args['required'] ) && $args['required'] === true ) {
			echo '<p class="description impressum__description impressum-required-field">' . \esc_html__( 'This is a required field.', 'impressum' ) . '</p>';
		}
	}
	
	/**
	 * Email field callback.
	 * 
	 * @param	array	$args The field arguments
	 */
	public function email( array $args ) {
		$settings_name = self::get_settings_name( $args );
		$options = Helper::get_option( $settings_name, ! \is_network_admin() );
		$placeholder = ( ! empty( $options['default'][ $args['label_for'] ] ) && ! \is_network_admin() ? ' placeholder="' . \esc_attr( $options['default'][ $args['label_for'] ] ) . '"' : '' );
		$value = ( isset( $options[ $args['label_for'] ] ) ? ' value="' . \esc_attr( ( isset( $options[ $args['label_for'] ] ) ? $options[ $args['label_for'] ] : ( isset( $options['default'][ $args['label_for'] ] ) ? $options['default'][ $args['label_for'] ] : '' ) ) ) . '"' : '' );
		?>
		<input type="email" id="<?php echo \esc_attr( $args['label_for'] ); ?>" name="<?php echo \esc_attr( $settings_name ); ?>[<?php echo \esc_attr( $args['label_for'] ); ?>]" class="regular-text"<?php echo $value; echo $placeholder; ?>><?php // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
		<?php
		if ( ! empty( $args['description'] ) ) {
			echo '<p class="description impressum__description">' . \esc_html( $args['description'] ) . '</p>';
		}
		
		if ( isset( $args['required'] ) && $args['required'] === true ) {
			echo '<p class="description impressum__description impressum-required-field">' . \esc_html__( 'This is a required field.', 'impressum' ) . '</p>';
		}
	}
	
	/**
	 * Get the name of the settings.
	 * 
	 * @param	array	$attributes Field attributes
	 * @return	string Settings name
	 */
	private static function get_settings_name( array $attributes ) {
		return ! empty( $attributes['setting'] ) ? $attributes['setting'] : 'impressum_imprint_options';
	}
	
	/**
	 * Initialize fields.
	 */
	public function init_fields() {
		// register option fields
		foreach ( Impressum::get_instance()->settings_fields as $id => $settings_field ) {
			/**
			 * Filter the callback instance for admin fields.
			 * 
			 * @since	2.1.0
			 * 
			 * @param	callable	$this Current instance
			 * @param	mixed[]		$settings_field Current settings field
			 * @param	string		$id Current field ID
			 */
			$callback_instance = \apply_filters( 'impressum_admin_fields_callback_instance', $this, $settings_field, $id );
			
			if (
				! isset( $settings_field['callback'] )
				|| ! \is_callable( [ $callback_instance, $settings_field['callback'] ] )
			) {
				continue;
			}
			
			\add_settings_field(
				$id,
				$settings_field['title'],
				[ $callback_instance, $settings_field['callback'] ],
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
		$settings_name = self::get_settings_name( $args );
		$options = Helper::get_option( $settings_name, ! \is_network_admin() );
		?>
		<select id="<?php echo \esc_attr( $args['label_for'] ); ?>" name="<?php echo \esc_attr( $settings_name ); ?>[<?php echo \esc_attr( $args['label_for'] ); ?>]">
			<option value=""><?php \esc_html_e( '- Select -', 'impressum' ); ?></option>
				<?php
				foreach ( Impressum::get_instance()->get_legal_entities() as $abbr => $entity ) {
					$is_selected = ( ! empty( $options['legal_entity'] ) ? \selected( $options['legal_entity'], $abbr, false ) : ( ! empty( $options['default']['legal_entity'] ) ? \selected( $options['default']['legal_entity'], $abbr, false ) : '' ) );
					
					echo '<option value="' . \esc_attr( $abbr ) . '"' . ( $is_selected ?: '' ) . '>' . \esc_html( $entity ) . '</option>'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
				}
				?>
		</select>
		<p class="description impressum__description"><?php \esc_html_e( 'In order to guide you the needed fields we need to know what kind of legal entity you are.', 'impressum' ); ?></p>
		<?php
		if ( isset( $args['required'] ) && $args['required'] === true ) {
			echo '<p class="description impressum__description impressum-required-field">' . \esc_html__( 'This is a required field.', 'impressum' ) . '</p>';
		}
	}
	
	/**
	 * Text input field callback.
	 * 
	 * @param	array	$args The field arguments
	 */
	public function text( array $args ) {
		$settings_name = self::get_settings_name( $args );
		$options = Helper::get_option( $settings_name, ! \is_network_admin() );
		$placeholder = ( ! empty( $options['default'][ $args['label_for'] ] ) && ! \is_network_admin() ? ' placeholder="' . \esc_attr( $options['default'][ $args['label_for'] ] ) . '"' : '' );
		$value = ( isset( $options[ $args['label_for'] ] ) ? ' value="' . \esc_attr( ( isset( $options[ $args['label_for'] ] ) ? $options[ $args['label_for'] ] : ( isset( $options['default'][ $args['label_for'] ] ) ? $options['default'][ $args['label_for'] ] : '' ) ) ) . '"' : '' );
		?>
		<input type="text" id="<?php echo \esc_attr( $args['label_for'] ); ?>" name="<?php echo \esc_attr( $settings_name ); ?>[<?php echo \esc_attr( $args['label_for'] ); ?>]" class="regular-text"<?php echo $value; echo $placeholder; ?>><?php // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
		<?php
		if ( ! empty( $args['description'] ) ) {
			echo '<p class="description impressum__description">' . \esc_html( $args['description'] ) . '</p>';
		}
		
		if ( isset( $args['required'] ) && $args['required'] === true ) {
			echo '<p class="description impressum__description impressum-required-field">' . \esc_html__( 'This is a required field.', 'impressum' ) . '</p>';
		}
	}
	
	/**
	 * Page field callback.
	 * 
	 * @param	array	$args The field arguments
	 */
	public function page( array $args ) {
		if ( \is_network_admin() ) {
			echo '<p>' . \esc_html__( 'This setting is not available in network options.', 'impressum' ) . '</p>';
			
			return;
		}
		
		$settings_name = self::get_settings_name( $args );
		$options = Helper::get_option( $settings_name, ! \is_network_admin() );
		$has_pages = (bool) \get_posts( [
			'posts_per_page' => 1,
			'post_status' => [ 'draft', 'publish' ],
			'post_type' => 'page',
		] );
		
		if ( $has_pages ) {
			\wp_dropdown_pages( [
				'id' => \esc_html( $args['label_for'] ),
				'name' => \esc_attr( $settings_name ) . '[' . \esc_attr( $args['label_for'] . ']' ),
				'post_status' => [ 'draft', 'publish' ],
				'selected' => ( isset( $options[ $args['label_for'] ] ) ? \esc_html( $options[ $args['label_for'] ] ) : ( isset( $options['default'][ $args['label_for'] ] ) ? \esc_html( $options['default'][ $args['label_for'] ] ) : '' ) ),
				'show_option_none' => \esc_html__( '— Select —', 'impressum' ),
			] );
		}
		else {
			echo '<p>' . \esc_html__( 'There are no pages. Please create a page first.', 'impressum' ) . '</p>';
		}
		
		if ( ! empty( $args['description'] ) ) {
			echo '<p class="description impressum__description">' . \esc_html( $args['description'] ) . '</p>';
		}
		
		if ( isset( $args['required'] ) && $args['required'] === true ) {
			echo '<p class="description impressum__description impressum-required-field">' . \esc_html__( 'This is a required field.', 'impressum' ) . '</p>';
		}
	}
	
	/**
	 * Phone field callback.
	 * 
	 * @param	array	$args The field arguments
	 */
	public function phone( array $args ) {
		$settings_name = self::get_settings_name( $args );
		$options = Helper::get_option( $settings_name, ! \is_network_admin() );
		$placeholder = ( ! empty( $options['default'][ $args['label_for'] ] ) && ! \is_network_admin() ? ' placeholder="' . \esc_attr( $options['default'][ $args['label_for'] ] ) . '"' : '' );
		$value = ( isset( $options[ $args['label_for'] ] ) ? ' value="' . \esc_attr( ( isset( $options[ $args['label_for'] ] ) ? $options[ $args['label_for'] ] : ( isset( $options['default'][ $args['label_for'] ] ) ? $options['default'][ $args['label_for'] ] : '' ) ) ) . '"' : '' );
		?>
		<input type="tel" id="<?php echo \esc_attr( $args['label_for'] ); ?>" name="<?php echo \esc_attr( $settings_name ); ?>[<?php echo \esc_attr( $args['label_for'] ); ?>]" class="regular-text"<?php echo $value; echo $placeholder; ?>><?php // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
		<?php
		if ( ! empty( $args['description'] ) ) {
			echo '<p class="description impressum__description">' . \esc_html( $args['description'] ) . '</p>';
		}
		
		if ( isset( $args['required'] ) && $args['required'] === true ) {
			echo '<p class="description impressum__description impressum-required-field">' . \esc_html__( 'This is a required field.', 'impressum' ) . '</p>';
		}
	}
	
	/**
	 * Press Law Checkbox field callback.
	 * 
	 * @deprecated	2.2.0 Use epiphyt\Impressum\Admin_Fields::checkbox() instead
	 * 
	 * @param	array	$args The field arguments
	 */
	public function press_law_checkbox( array $args ) {
		\_doing_it_wrong(
			__METHOD__,
			\sprintf(
				/* translators: alternative method */
				\esc_html__( 'Use %s instead', 'impressum' ),
				'epiphyt\Impressum\Admin_Fields::checkbox()'
			),
			'2.2.0'
		);
		
		self::checkbox( $args );
	}
	
	/**
	 * Select callback.
	 * 
	 * @since	2.3.0
	 * 
	 * @param	array	$args The field arguments
	 */
	public function select( array $args ) {
		$settings_name = self::get_settings_name( $args );
		$options = Helper::get_option( $settings_name, ! \is_network_admin() );
		$value = ( isset( $options[ $args['label_for'] ] ) ? \esc_attr( ( isset( $options[ $args['label_for'] ] ) ? $options[ $args['label_for'] ] : ( isset( $options['default'][ $args['label_for'] ] ) ? $options['default'][ $args['label_for'] ] : '' ) ) ) : '' );
		$select_options = ! empty( $args['options'] ) ? $args['options'] : [];
		?>
		<select id="<?php echo \esc_attr( $args['label_for'] ); ?>" name="<?php echo \esc_attr( $settings_name ); ?>[<?php echo \esc_attr( $args['label_for'] ); ?>]">
			<option value=""><?php \esc_html_e( '— Select —', 'impressum' ); ?></option>
			<?php foreach ( $select_options as $option ) : ?>
			<option<?php \selected( $option['value'], $value ); ?> value="<?php echo \esc_attr( $option['value'] ); ?>"><?php echo \esc_html( $option['label'] ); ?></option>
			<?php endforeach; ?>
		</select>
		<?php
		if ( ! empty( $args['description'] ) ) {
			echo '<p class="description impressum__description">' . \esc_html( $args['description'] ) . '</p>';
		}
		
		if ( isset( $args['required'] ) && $args['required'] === true ) {
			echo '<p class="description impressum__description impressum-required-field">' . \esc_html__( 'This is a required field.', 'impressum' ) . '</p>';
		}
	}
	
	/**
	 * Textarea callback.
	 * 
	 * @param	array	$args The field arguments
	 */
	public function textarea( array $args ) {
		$settings_name = self::get_settings_name( $args );
		$options = Helper::get_option( $settings_name, ! \is_network_admin() );
		$placeholder = ( ! empty( $options['default'][ $args['label_for'] ] ) && ! \is_network_admin() ? ' placeholder="' . \esc_html( \str_replace( "\r\n", ', ', $options['default'][ $args['label_for'] ] ) ) . '"' : '' );
		$value = ( isset( $options[ $args['label_for'] ] ) ? \esc_attr( ( isset( $options[ $args['label_for'] ] ) ? $options[ $args['label_for'] ] : ( isset( $options['default'][ $args['label_for'] ] ) ? $options['default'][ $args['label_for'] ] : '' ) ) ) : '' );
		?>
		<textarea cols="50" rows="10" id="<?php echo \esc_attr( $args['label_for'] ); ?>" name="<?php echo \esc_attr( $settings_name ); ?>[<?php echo \esc_attr( $args['label_for'] ); ?>]"<?php echo $placeholder; ?>><?php echo $value; ?></textarea><?php // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
		<?php
		if ( ! empty( $args['description'] ) ) {
			echo '<p class="description impressum__description">' . \esc_html( $args['description'] ) . '</p>';
		}
		
		if ( isset( $args['required'] ) && $args['required'] === true ) {
			echo '<p class="description impressum__description impressum-required-field">' . \esc_html__( 'This is a required field.', 'impressum' ) . '</p>';
		}
	}
}
