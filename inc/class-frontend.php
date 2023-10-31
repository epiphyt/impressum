<?php
namespace epiphyt\Impressum;
use function add_action;
use function add_shortcode;
use function apply_filters;
use function array_map;
use function array_merge;
use function boolval;
use function defined;
use function esc_html;
use function explode;
use function in_array;
use function nl2br;
use function preg_replace;
use function register_block_type;
use function rtrim;
use function sanitize_email;
use function str_replace;
use function strpos;
use function wp_kses;
use function wpautop;

// exit if ABSPATH is not defined
defined( 'ABSPATH' ) || exit;

/**
 * Represents functions for the frontend in Impressum Plus.
 * 
 * @author		Epiphyt
 * @license		GPL2 <https://www.gnu.org/licenses/gpl-2.0.html>
 */
class Frontend {
	/**
	 * @var		\epiphyt\Impressum\Frontend
	 */
	private static $instance;
	
	/**
	 * Frontend constructor.
	 */
	public function __construct() {
		self::$instance = $this;
	}
	
	/**
	 * Initialize the frontend functions.
	 */
	public function init() {
		add_action( 'init', [ $this, 'register_blocks' ] );
		
		add_shortcode( 'impressum', [ $this, 'render' ] );
	}
	
	/**
	 * Get a unique instance of the class.
	 * 
	 * @return	\epiphyt\Impressum\Frontend
	 */
	public static function get_instance() {
		if ( static::$instance === null ) {
			static::$instance = new static();
		}
		
		return static::$instance;
	}
	
	/**
	 * Register Gutenberg blocks.
	 */
	public function register_blocks() {
		register_block_type( 'impressum/imprint', [
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
		$fields = Helper::get_option( 'impressum_imprint_options', true );
		$output = '';
		$sections = ( ! empty( $attributes['sections'] ) ? array_map( 'trim', explode( ',', $attributes['sections'] ) ) : [] );
		
		// merge global and local options
		if ( ! empty( $fields['default'] ) ) {
			$fields_global = $fields['default'];
			unset( $fields['default'] );
			$fields = array_merge( $fields_global, $fields );
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
			$attributes['markup'] = $attributes['markup'] !== 'false' && boolval( $attributes['markup'] );
		}
		
		// check for title output
		if ( ! empty( $attributes['className'] ) && strpos( $attributes['className'], 'is-style-no-title' ) !== false ) {
			$attributes['titles'] = false;
		}
		if ( ! isset( $attributes['titles'] ) ) {
			$attributes['titles'] = true;
		}
		else {
			$attributes['titles'] = $attributes['titles'] !== 'false' && boolval( $attributes['titles'] );
		}
		
		if ( $attributes['markup'] ) {
			// open imprint container
			$output .= '<div class="impressum__imprint-container">';
		}
		
		foreach ( $fields as $field => $value ) {
			// check shortcode sections
			if ( ! empty( $sections ) && ! in_array( $field, $sections, true ) ) continue;
			// check block enabled fields
			if ( ! empty( $attributes['enabledFields'] ) && ! in_array( $field, $attributes['enabledFields'], true ) ) continue;
			// check whether the field should be displayed
			if (
				empty( $value )
				|| (
					! empty( Impressum::get_instance()->settings_fields[ $field ]['no_output'] )
					&& Impressum::get_instance()->settings_fields[ $field ]['no_output'] === true
					&& ! in_array( $field, $sections, true )
				)
			) continue;
			// special case for press law person, which should only be displayed
			// if the checkbox is checked
			if ( $field === 'press_law_person' && empty( $fields['press_law_checkbox'] ) ) continue;
			
			$output .= $this->render_field( $field, $value, $attributes, $fields );
		}
		
		if ( $attributes['markup'] ) {
			// close imprint container
			$output .= '</div>';
		}
		else {
			// remove trailing comma
			$output = rtrim( $output, ', ' );
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
	 * @param	array	$unused Only used in the Plus version
	 * @return	string The formatted field value
	 */
	private function render_field( $field, $value, $attributes, $unused ) {
		$output = '';
		$title = '';
		
		// the field title
		if ( $attributes['titles'] ) {
			if ( ! empty( Impressum::get_instance()->settings_fields[ $field ]['field_title'] ) ) {
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
		$title = apply_filters( "impressum_imprint_output_title_{$field}", $title, $attributes, $field );
		
		// set the output
		switch ( $field ) {
			case 'email':
				$field_output = '<a href="mailto:' . sanitize_email( $value ) . '">' . esc_html( $value ) . '</a>';
				break;
			default:
				$field_output = nl2br( esc_html( $value ) );
				break;
		}
		
		// remove last break
		$field_output = preg_replace( '/(<br>)+$/', '', $field_output );
		
		if ( $attributes['titles'] && $attributes['markup'] ) {
			// definition list if markup and titles are enabled
			$output .= '<dl>';
			$output .= ( $attributes['markup'] ? '<dt>' : '' ) . esc_html( $title ) . ( $attributes['markup'] ? '</dt>' : '' );
			$output .= ( $attributes['markup'] ? '<dd>' : '' ) . $field_output . ( $attributes['markup'] ? '</dd>' : '' );
			$output .= '</dl>';
		}
		else if ( $attributes['markup'] ) {
			// paragraph if only markup but no titles are enabled
			$output .= wpautop( $field_output );
		}
		else {
			// comma separated list without markup
			$output .= esc_html( str_replace( "\r\n", ', ', $value ) ) . ', ';
		}
		
		return $output;
	}
}
