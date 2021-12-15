<?php
namespace epiphyt\Impressum;
use function get_option;

/**
 * Helper functions for the Impressum plugin.
 * 
 * @author		Epiphyt
 * @license		GPL2 <https://www.gnu.org/licenses/gpl-2.0.html>
 */
class Helper {
	/**
	 * Get an option from the database.
	 * The real function of the wrapper is in the Plus version only.
	 * 
	 * @param	string	$option The option you want to get
	 * @param	bool	$useless Useless in the free version
	 * @return	mixed|void
	 */
	public static function get_option( $option, $useless = false ) {
		return get_option( $option );
	}
}
