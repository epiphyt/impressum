<?php
namespace epiphyt\Impressum;

/**
 * Helper functions for the Impressum plugin.
 * 
 * @author	Epiphyt
 * @license	GPL2
 * @package	epiphyt\Impressum
 */
class Helper {
	/**
	 * Print out the settings fields for a particular settings section.
	 * 
	 * Part of the Settings API. Use this in a settings page to output
	 * a specific section. Should normally be called by do_settings_sections()
	 * rather than directly.
	 * 
	 * do_settings_fields() from core with adjustments to the <th> element.
	 * 
	 * @global	array	$wp_settings_fields Storage array of settings fields and their pages/sections.
	 * @since	2.1.0
	 * 
	 * @param	string	$page Slug title of the admin page whose settings fields you want to show.
	 * @param	string	$section Slug title of the settings section whose fields you want to show.
	 */
	public static function do_settings_fields( $page, $section ) {
		global $wp_settings_fields;
		
		if ( ! isset( $wp_settings_fields[ $page ][ $section ] ) ) {
			return;
		}
		
		$field_data = self::get_option( 'impressum_field_data', ! \is_network_admin() );
		
		foreach ( (array) $wp_settings_fields[ $page ][ $section ] as $field ) {
			$class = '';
			
			if ( ! empty( $field['args']['class'] ) ) {
				$class = ' class="' . \esc_attr( $field['args']['class'] ) . '"';
			}
			
			echo "<tr{$class}>"; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
			
			if ( ! empty( $field['args']['label_for'] ) ) {
				$title = $field['title'];
				
				if ( isset( $field_data[ $field['args']['label_for'] ]['name'] ) ) {
					$title = $field_data[ $field['args']['label_for'] ]['name'];
				}
				
				echo '<th scope="row">';
				
				if ( ! empty( $field_data[ $field['args']['label_for'] ]['name'] ) ) {
					echo '<input type="hidden" name="impressum_field_data[' . \esc_attr( $field['args']['label_for'] ) . '][name]" value="' . \esc_attr( $field_data[ $field['args']['label_for'] ]['name'] ) . '" />';
				}
				
				echo '<label for="' . \esc_attr( $field['args']['label_for'] ) . '">' . \esc_html( $title ) . '</label></th>';
			}
			else {
				echo '<th scope="row">' . $field['title'] . '</th>'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
			}
			
			echo '<td>';
			\call_user_func( $field['callback'], $field['args'] ); // phpcs:ignore NeutronStandard.Functions.DisallowCallUserFunc.CallUserFunc
			echo '</td>';
			echo '</tr>';
		}
	}
	
	/**
	 * Prints out all settings sections added to a particular settings page
	 * 
	 * Part of the Settings API. Use this in a settings page callback function
	 * to output all the sections and fields that were added to that $page with
	 * add_settings_section() and add_settings_field()
	 * 
	 * do_settings_sections() from core with custom do_settings_fields()
	 * 
	 * @global	array	$wp_settings_sections Storage array of all settings sections added to admin pages.
	 * @global	array	$wp_settings_fields Storage array of settings fields and info about their pages/sections.
	 * @since	2.1.0
	 * 
	 * @param	string	$page The slug name of the page whose settings sections you want to output.
	 */
	public static function do_settings_sections( $page ) {
		global $wp_settings_sections, $wp_settings_fields;
		
		if ( ! isset( $wp_settings_sections[ $page ] ) ) {
			return;
		}
		
		foreach ( (array) $wp_settings_sections[ $page ] as $section ) {
			if ( $section['title'] ) {
				echo "<h2>{$section['title']}</h2>\n"; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
			}
			
			if ( $section['callback'] ) {
				\call_user_func( $section['callback'], $section ); // phpcs:ignore NeutronStandard.Functions.DisallowCallUserFunc.CallUserFunc
			}
			
			if (
				! isset( $wp_settings_fields )
				|| ! isset( $wp_settings_fields[ $page ] )
				|| ! isset( $wp_settings_fields[ $page ][ $section['id'] ] )
			) {
				continue;
			}
			echo '<table class="form-table" role="presentation">';
			self::do_settings_fields( $page, $section['id'] );
			echo '</table>';
		}
	}
	
	/**
	 * Get an option from the database.
	 * The real function of the wrapper is in the Plus version only.
	 * 
	 * @param	string	$option The option you want to get
	 * @param	bool	$useless Useless in the free version
	 * @return	mixed Option value
	 */
	public static function get_option( $option, $useless = false ) { // phpcs:ignore Generic.CodeAnalysis.UnusedFunctionParameter.FoundAfterLastUsed
		return \get_option( $option );
	}
}
