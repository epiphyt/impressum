<?php
namespace epiphyt\Impressum;

/**
 * Represents functions for the frontend in Impressum.
 * 
 * @author	Epiphyt
 * @license	GPL2
 * @package	epiphyt\Impressum
 */
class Frontend {
	use Singleton;
	
	/**
	 * Initialize the frontend functions.
	 */
	public function init() {
		\add_action( 'init', [ $this, 'register_blocks' ] );
		\add_shortcode( 'impressum', [ $this, 'render' ] );
	}
	
	/**
	 * Register Gutenberg blocks.
	 */
	public function register_blocks() {
		\register_block_type( 'impressum/imprint', [
			'editor_script' => 'impressum-imprint-block',
			'editor_style' => 'impressum-imprint-block-editor-styles',
			'render_callback' => [ $this, 'render_block' ],
		] );
	}
	
	/**
	 * Render the imprint output.
	 * 
	 * @param	array|string	$attributes A set of attributes
	 * @return	string The imprint output
	 */
	public function render( $attributes ) {
		$attributes = (array) $attributes;
		$attributes['field_data'] = Helper::get_option( 'impressum_field_data', true );
		$fields = \array_filter( (array) Helper::get_option( 'impressum_imprint_options', true ) );
		$field_data = Impressum::get_instance()->get_block_fields( 'impressum_imprint_options' );
		$field_keys = \array_keys( $fields );
		$output = '';
		$sections = ( ! empty( $attributes['sections'] ) ? \array_map( 'trim', \explode( ',', $attributes['sections'] ) ) : [] );
		
		\usort( $field_keys, static function( $a, $b ) use ( $attributes, $field_data ) {
			if ( empty( $attributes['enabledFields'] ) ) {
				return 0;
			}
			
			$flipped = \array_flip( $attributes['enabledFields'] );
			$left_title = isset( $field_data[ $a ]['custom_title'] ) ? $field_data[ $a ]['custom_title'] : '';
			$right_title = isset( $field_data[ $b ]['custom_title'] ) ? $field_data[ $b ]['custom_title'] : '';
			
			if ( ! $left_title ) {
				$left_title = isset( $field_data[ $a ]['title'] ) ? $field_data[ $a ]['title'] : '';
			}
			
			if ( ! $right_title ) {
				$right_title = isset( $field_data[ $b ]['title'] ) ? $field_data[ $b ]['title'] : '';
			}
			
			if ( ! isset( $flipped[ $left_title ] ) && ! isset( $flipped[ $right_title ] ) ) {
				return 0;
			}
			
			if ( ! isset( $flipped[ $left_title ] ) ) {
				return -1;
			}
			
			if ( ! isset( $flipped[ $right_title ] ) ) {
				return 1;
			}
			
			if ( $flipped[ $left_title ] === $flipped[ $right_title ] ) {
				return 0;
			}
			
			return $flipped[ $left_title ] < $flipped[ $right_title ] ? -1 : 1;
		} );
		
		$fields = \array_merge( \array_flip( $field_keys ), $fields );
		
		// merge global and local options
		if ( ! empty( $fields['default'] ) ) {
			$fields_global = $fields['default'];
			unset( $fields['default'] );
			$fields = \array_merge( $fields_global, $fields );
		}
		
		// return empty string if there are no fields
		if ( empty( $fields ) ) {
			return $output;
		}
		
		// check for markup output
		if ( ! isset( $attributes['markup'] ) ) {
			$attributes['markup'] = true;
		}
		else {
			$attributes['markup'] = $attributes['markup'] !== 'false' && \boolval( $attributes['markup'] );
		}
		
		// check for title output
		if ( ! empty( $attributes['className'] ) && \strpos( $attributes['className'], 'is-style-no-title' ) !== false ) {
			$attributes['titles'] = false;
		}
		if ( ! isset( $attributes['titles'] ) ) {
			$attributes['titles'] = true;
		}
		else {
			$attributes['titles'] = $attributes['titles'] !== 'false' && \boolval( $attributes['titles'] );
		}
		
		if ( $attributes['markup'] ) {
			// open imprint container
			$output .= '<div class="impressum__imprint-container">';
		}
		
		$output .= $attributes['titles'] && $attributes['markup'] ? '<dl>' : '';
		
		foreach ( $fields as $field => $value ) {
			// check shortcode sections
			if ( ! empty( $sections ) && ! \in_array( $field, $sections, true ) ) {
				continue;
			}
			
			// check block enabled fields
			if ( ! empty( $attributes['enabledFields'] ) ) {
				if (
					! \in_array( $field_data[ $field ]['custom_title'], $attributes['enabledFields'], true )
					&& ! \in_array( $field_data[ $field ]['title'], $attributes['enabledFields'], true )
					// deprecated old value
					&& ! \in_array( $field, $attributes['enabledFields'], true )
				) {
					continue;
				}
			}
			
			// check whether the field should be displayed
			if (
				empty( $value )
				|| (
					! empty( Impressum::get_instance()->settings_fields[ $field ]['no_output'] )
					&& Impressum::get_instance()->settings_fields[ $field ]['no_output'] === true
					&& ! \in_array( $field, $sections, true )
				)
			) {
				continue;
			}
			
			// special case for press law person, which should only be displayed
			// if the checkbox is checked
			if ( $field === 'press_law_person' && empty( $fields['press_law_checkbox'] ) ) {
				continue;
			}
			
			$output .= $this->render_field( $field, $value, $attributes, $fields );
		}
		
		$output .= $attributes['titles'] && $attributes['markup'] ? '</dl>' : '';
		
		if ( $attributes['markup'] ) {
			// close imprint container
			$output .= '</div>';
		}
		else {
			// remove trailing comma
			$output = \rtrim( $output, ', ' );
		}
		
		return $output;
	}
	
	/**
	 * Render the block output.
	 * 
	 * @param	array	$attributes The block attributes
	 * @return	string The imprint output
	 */
	public function render_block( $attributes ) {
		return $this->render( $attributes );
	}
	
	/**
	 * Render a single field of the imprint.
	 * 
	 * @param	string	$field The field name (identifier)
	 * @param	string	$value The field value
	 * @param	array	$attributes The output attributes
	 * @param	array	$fields All available fields
	 * @return	string The formatted field value
	 */
	private function render_field( $field, $value, $attributes, $fields ) {
		$output = '';
		$title = '';
		
		// the field title
		if ( $attributes['titles'] ) {
			if ( ! empty( $attributes['field_data'][ $field ]['name'] ) ) {
				$title = $attributes['field_data'][ $field ]['name'];
			}
			else if ( ! empty( Impressum::get_instance()->settings_fields[ $field ]['field_title'] ) ) {
				$title = Impressum::get_instance()->settings_fields[ $field ]['field_title'];
			}
			else if ( ! empty( Impressum::get_instance()->settings_fields[ $field ]['title'] ) ) {
				$title = Impressum::get_instance()->settings_fields[ $field ]['title'];
			}
		}
		
		/**
		 * Filter the field title in the imprint shortcode.
		 * 
		 * @since	2.0.0
		 * 
		 * @param	string	$title The current field title
		 * @param	array	$attributes The field arguments
		 * @param	string	$field The field name
		 */
		$title = (string) \apply_filters( "impressum_imprint_output_title_{$field}", $title, $attributes, $field );
		
		// set the output
		switch ( $field ) {
			case 'contact_form_page':
				$permalink = \get_permalink( $value );
				
				if ( $permalink ) {
					$field_output = '<a href="' . \esc_url( $permalink ) . '">' . \esc_html__( 'To the contact form', 'impressum' ) . '</a>';
				}
				break;
			case 'email':
				$field_output = '<a href="mailto:' . \sanitize_email( $value ) . '">' . \esc_html( $value ) . '</a>';
				break;
			default:
				$field_output = \nl2br( \esc_html( $value ) );
				break;
		}
		
		// remove last break
		$field_output = \preg_replace( '/(<br>)+$/', '', $field_output );
		
		/**
		 * Filter the output of a field.
		 * 
		 * @since	2.1.0
		 * 
		 * @param	string		$field_output Field output
		 * @param	string		$value Field value
		 * @param	string		$field Field name
		 * @param	mixed[]		$attributes Field rendering attributes
		 * @param	mixed[][]	$fields All fields to render
		 */
		$field_output = (string) \apply_filters( "impressum_imprint_output_field_{$field}", $field_output, $value, $field, $attributes, $fields );
		
		/**
		 * Filter the output of a field.
		 * 
		 * @since	2.1.0
		 * 
		 * @param	string		$field_output Field output
		 * @param	string		$value Field value
		 * @param	string		$field Field name
		 * @param	mixed[]		$attributes Field rendering attributes
		 * @param	mixed[][]	$fields All fields to render
		 */
		$field_output = (string) \apply_filters( 'impressum_imprint_output_field', $field_output, $value, $field, $attributes, $fields );
		
		if ( $attributes['titles'] && $attributes['markup'] ) {
			$output .= '<dt>' . \esc_html( $title ) . '</dt>';
			$output .= '<dd>' . $field_output . '</dd>';
		}
		else if ( $attributes['markup'] ) {
			// paragraph if only markup but no titles are enabled
			$output .= \wpautop( $field_output );
		}
		else {
			// comma separated list without markup
			$output .= \esc_html( \str_replace( "\r\n", ', ', $value ) ) . ', ';
		}
		
		return $output;
	}
}
