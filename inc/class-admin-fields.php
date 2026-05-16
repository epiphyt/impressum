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
	public function checkbox( array $args ): void {
		$settings_name = self::get_settings_name( $args );
		$options = \epiphyt\Impressum\get_container()->get( 'helper' )::get_option( $settings_name, true );
		?>
		<input type="checkbox" id="<?= \esc_attr( $args['label_for'] ); ?>" name="<?= \esc_attr( $settings_name ); ?>[<?= \esc_attr( $args['label_for'] ); ?>]" value="1"<?php \checked( isset( $options[ $args['label_for'] ] ) ); ?>>
		<label for="<?= \esc_attr( $args['label_for'] ); ?>"><?= \esc_html( $args['label'] ?? '' ); ?></label>
		<?php
		/**
		 * Fires after the checkbox field has been rendered.
		 * 
		 * @since	3.0.0
		 * 
		 * @param	string	$settings_name Settings group name
		 * @param	array	$args Field arguments
		 * @param	array	$options Settings of this settings group
		 */
		\do_action( "impressum_option_description_{$args['label_for']}", $settings_name, $args, $options );
	}
	
	/**
	 * Country field callback.
	 * 
	 * @param	array	$args The field arguments
	 */
	public function country( array $args ): void {
		$settings_name = self::get_settings_name( $args );
		$options = \epiphyt\Impressum\get_container()->get( 'helper' )::get_option( $settings_name, ! \is_network_admin() );
		?>
		<select id="<?= \esc_attr( $args['label_for'] ); ?>" name="<?= \esc_attr( $settings_name ); ?>[<?= \esc_attr( $args['label_for'] ); ?>]">
			<option value=""><?php \esc_html_e( '- Select -', 'impressum' ); ?></option>
				<?php
				foreach ( \epiphyt\Impressum\get_container()->get( 'plugin' )->get_countries() as $country_code => $country ) {
					$is_selected = ( ! empty( $options['country'] ) ? \selected( $options['country'], $country_code, false ) : ( ! empty( $options['default']['country'] ) ? \selected( $options['default']['country'], $country_code, false ) : '' ) );
					
					if ( empty( $options['country'] ) && ! empty( $_SERVER['HTTP_ACCEPT_LANGUAGE'] ) ) {
						$accept_language = \sanitize_text_field( \wp_unslash( $_SERVER['HTTP_ACCEPT_LANGUAGE'] ) );
						$is_selected = ( \str_contains( \strtolower( $accept_language ), $country_code ) ? ' selected' : '' );
						
						if ( empty( $is_selected ) && ! empty( $country['locale_primary'] ) ) {
							$is_selected = ( \str_contains( \strtolower( $accept_language ), $country['locale_primary'] ) ? ' selected' : '' );
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
		
		/**
		 * This action is described in inc/class-admin-fields.php
		 */
		\do_action( "impressum_option_description_{$args['label_for']}", $settings_name, $args, $options );
	}
	
	/**
	 * Email field callback.
	 * 
	 * @param	array	$args The field arguments
	 */
	public function email( array $args ): void {
		$settings_name = self::get_settings_name( $args );
		$options = \epiphyt\Impressum\get_container()->get( 'helper' )::get_option( $settings_name, ! \is_network_admin() );
		$placeholder = ( ! empty( $options['default'][ $args['label_for'] ] ) && ! \is_network_admin() ? ' placeholder="' . \esc_attr( $options['default'][ $args['label_for'] ] ) . '"' : '' );
		$value = ( isset( $options[ $args['label_for'] ] ) ? ' value="' . \esc_attr( ( $options[ $args['label_for'] ] ?? ( $options['default'][ $args['label_for'] ] ?? '' ) ) ) . '"' : '' );
		?>
		<input type="email" id="<?= \esc_attr( $args['label_for'] ); ?>" name="<?= \esc_attr( $settings_name ); ?>[<?= \esc_attr( $args['label_for'] ); ?>]" class="regular-text"<?= $value . $placeholder; ?>><?php // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
		<?php
		if ( ! empty( $args['description'] ) ) {
			echo '<p class="description impressum__description">' . \esc_html( $args['description'] ) . '</p>';
		}
		
		if ( isset( $args['required'] ) && $args['required'] === true ) {
			echo '<p class="description impressum__description impressum-required-field">' . \esc_html__( 'This is a required field.', 'impressum' ) . '</p>';
		}
		
		/**
		 * This action is described in inc/class-admin-fields.php
		 */
		\do_action( "impressum_option_description_{$args['label_for']}", $settings_name, $args, $options );
	}
	
	/**
	 * Get the name of the settings.
	 * 
	 * @param	array	$attributes Field attributes
	 * @return	string Settings name
	 */
	private static function get_settings_name( array $attributes ): string {
		return ! empty( $attributes['setting'] ) ? $attributes['setting'] : 'impressum_imprint_options';
	}
	
	/**
	 * Initialize fields.
	 */
	public function init_fields(): void {
		$settings = \epiphyt\Impressum\get_container()->get( 'settings-registry' )->get_settings();
		
		// register option fields
		foreach ( $settings as $id => $settings_field ) {
			/**
			 * Filter the callback instance for admin fields.
			 * 
			 * @since	2.1.0
			 * @since	3.0.0 Second parameter is a Setting object now
			 * 
			 * @param	object								$this Current instance
			 * @param	\epiphyt\Impressum\settings\Setting	$settings_field Current settings field
			 * @param	string								$id Current field ID
			 */
			$callback_instance = \apply_filters( 'impressum_admin_fields_callback_instance', $this, $settings_field, $id );
			
			if (
				empty( $settings_field->get_data( 'setting_callback' ) )
				|| ! \is_callable( [ $callback_instance, $settings_field->get_data( 'setting_callback' ) ] )
			) {
				continue;
			}
			
			\add_settings_field(
				$id,
				$settings_field->get_title(),
				[ $callback_instance, $settings_field->get_data( 'setting_callback' ) ],
				$settings_field->get_data( 'setting_page' ),
				$settings_field->get_data( 'setting_section' ),
				$settings_field->get_data( 'setting_attributes' )
			);
		}
	}
	
	/**
	 * Legal Entity field callback.
	 * 
	 * @param	array	$args The field arguments
	 */
	public function legal_entity( array $args ): void {
		$settings_name = self::get_settings_name( $args );
		$options = \epiphyt\Impressum\get_container()->get( 'helper' )::get_option( $settings_name, ! \is_network_admin() );
		?>
		<select id="<?= \esc_attr( $args['label_for'] ); ?>" name="<?= \esc_attr( $settings_name ); ?>[<?= \esc_attr( $args['label_for'] ); ?>]">
			<option value=""><?php \esc_html_e( '- Select -', 'impressum' ); ?></option>
				<?php
				foreach ( \epiphyt\Impressum\get_container()->get( 'plugin' )->get_legal_entities() as $abbr => $entity ) {
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
		
		/**
		 * This action is described in inc/class-admin-fields.php
		 */
		\do_action( "impressum_option_description_{$args['label_for']}", $settings_name, $args, $options );
	}
	
	/**
	 * Text input field callback.
	 * 
	 * @param	array	$args The field arguments
	 */
	public function text( array $args ): void {
		$settings_name = self::get_settings_name( $args );
		$options = \epiphyt\Impressum\get_container()->get( 'helper' )::get_option( $settings_name, ! \is_network_admin() );
		$placeholder = ( ! empty( $options['default'][ $args['label_for'] ] ) && ! \is_network_admin() ? ' placeholder="' . \esc_attr( $options['default'][ $args['label_for'] ] ) . '"' : '' );
		$value = ( isset( $options[ $args['label_for'] ] ) ? ' value="' . \esc_attr( ( $options[ $args['label_for'] ] ?? ( $options['default'][ $args['label_for'] ] ?? '' ) ) ) . '"' : '' );
		?>
		<input type="text" id="<?= \esc_attr( $args['label_for'] ); ?>" name="<?= \esc_attr( $settings_name ); ?>[<?= \esc_attr( $args['label_for'] ); ?>]" class="regular-text"<?= $value . $placeholder; ?>><?php // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
		<?php
		if ( ! empty( $args['description'] ) ) {
			echo '<p class="description impressum__description">' . \esc_html( $args['description'] ) . '</p>';
		}
		
		if ( isset( $args['required'] ) && $args['required'] === true ) {
			echo '<p class="description impressum__description impressum-required-field">' . \esc_html__( 'This is a required field.', 'impressum' ) . '</p>';
		}
		
		/**
		 * This action is described in inc/class-admin-fields.php
		 */
		\do_action( "impressum_option_description_{$args['label_for']}", $settings_name, $args, $options );
	}
	
	/**
	 * Page field callback.
	 * 
	 * @param	array	$args The field arguments
	 */
	public function page( array $args ): void {
		if ( \is_network_admin() ) {
			echo '<p>' . \esc_html__( 'This setting is not available in network options.', 'impressum' ) . '</p>';
			
			return;
		}
		
		$settings_name = self::get_settings_name( $args );
		$options = \epiphyt\Impressum\get_container()->get( 'helper' )::get_option( $settings_name, ! \is_network_admin() );
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
		
		/**
		 * This action is described in inc/class-admin-fields.php
		 */
		\do_action( "impressum_option_description_{$args['label_for']}", $settings_name, $args, $options );
	}
	
	/**
	 * Phone field callback.
	 * 
	 * @param	array	$args The field arguments
	 */
	public function phone( array $args ): void {
		$settings_name = self::get_settings_name( $args );
		$options = \epiphyt\Impressum\get_container()->get( 'helper' )::get_option( $settings_name, ! \is_network_admin() );
		$placeholder = ( ! empty( $options['default'][ $args['label_for'] ] ) && ! \is_network_admin() ? ' placeholder="' . \esc_attr( $options['default'][ $args['label_for'] ] ) . '"' : '' );
		$value = ( isset( $options[ $args['label_for'] ] ) ? ' value="' . \esc_attr( ( $options[ $args['label_for'] ] ?? ( $options['default'][ $args['label_for'] ] ?? '' ) ) ) . '"' : '' );
		?>
		<input type="tel" id="<?= \esc_attr( $args['label_for'] ); ?>" name="<?= \esc_attr( $settings_name ); ?>[<?= \esc_attr( $args['label_for'] ); ?>]" class="regular-text"<?= $value . $placeholder; ?>><?php // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
		<?php
		if ( ! empty( $args['description'] ) ) {
			echo '<p class="description impressum__description">' . \esc_html( $args['description'] ) . '</p>';
		}
		
		if ( isset( $args['required'] ) && $args['required'] === true ) {
			echo '<p class="description impressum__description impressum-required-field">' . \esc_html__( 'This is a required field.', 'impressum' ) . '</p>';
		}
		
		/**
		 * This action is described in inc/class-admin-fields.php
		 */
		\do_action( "impressum_option_description_{$args['label_for']}", $settings_name, $args, $options );
	}
	
	/**
	 * Select callback.
	 * 
	 * @since	2.3.0
	 * 
	 * @param	array	$args The field arguments
	 */
	public function select( array $args ): void {
		$settings_name = self::get_settings_name( $args );
		$options = \epiphyt\Impressum\get_container()->get( 'helper' )::get_option( $settings_name, ! \is_network_admin() );
		$value = ( isset( $options[ $args['label_for'] ] ) ? \esc_attr( ( $options[ $args['label_for'] ] ?? ( $options['default'][ $args['label_for'] ] ?? '' ) ) ) : '' );
		$select_options = ! empty( $args['options'] ) ? $args['options'] : [];
		?>
		<select id="<?= \esc_attr( $args['label_for'] ); ?>" name="<?= \esc_attr( $settings_name ); ?>[<?= \esc_attr( $args['label_for'] ); ?>]">
			<option value=""><?php \esc_html_e( '— Select —', 'impressum' ); ?></option>
			<?php foreach ( $select_options as $option ) : ?>
			<option<?php \selected( $option['value'], $value ); ?> value="<?= \esc_attr( $option['value'] ); ?>"><?= \esc_html( $option['label'] ); ?></option>
			<?php endforeach; ?>
		</select>
		<?php
		if ( ! empty( $args['description'] ) ) {
			echo '<p class="description impressum__description">' . \esc_html( $args['description'] ) . '</p>';
		}
		
		if ( isset( $args['required'] ) && $args['required'] === true ) {
			echo '<p class="description impressum__description impressum-required-field">' . \esc_html__( 'This is a required field.', 'impressum' ) . '</p>';
		}
		
		/**
		 * This action is described in inc/class-admin-fields.php
		 */
		\do_action( "impressum_option_description_{$args['label_for']}", $settings_name, $args, $options );
	}
	
	/**
	 * Textarea callback.
	 * 
	 * @param	array	$args The field arguments
	 */
	public function textarea( array $args ): void {
		$settings_name = self::get_settings_name( $args );
		$options = \epiphyt\Impressum\get_container()->get( 'helper' )::get_option( $settings_name, ! \is_network_admin() );
		$placeholder = ( ! empty( $options['default'][ $args['label_for'] ] ) && ! \is_network_admin() ? ' placeholder="' . \esc_html( \str_replace( "\r\n", ', ', $options['default'][ $args['label_for'] ] ) ) . '"' : '' );
		$value = ( isset( $options[ $args['label_for'] ] ) ? \esc_attr( ( $options[ $args['label_for'] ] ?? ( $options['default'][ $args['label_for'] ] ?? '' ) ) ) : '' );
		?>
		<textarea cols="50" rows="10" id="<?= \esc_attr( $args['label_for'] ); ?>" name="<?= \esc_attr( $settings_name ); ?>[<?= \esc_attr( $args['label_for'] ); ?>]"<?= $placeholder; ?>><?= $value; ?></textarea><?php // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
		<?php
		if ( ! empty( $args['description'] ) ) {
			echo '<p class="description impressum__description">' . \esc_html( $args['description'] ) . '</p>';
		}
		
		if ( isset( $args['required'] ) && $args['required'] === true ) {
			echo '<p class="description impressum__description impressum-required-field">' . \esc_html__( 'This is a required field.', 'impressum' ) . '</p>';
		}
		
		/**
		 * This action is described in inc/class-admin-fields.php
		 */
		\do_action( "impressum_option_description_{$args['label_for']}", $settings_name, $args, $options );
	}
}
