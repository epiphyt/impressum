/*!
 * JavaScript functions for the admin options page.
 * 
 * @version		0.1
 * @author		Matthias Kittsteiner, Simon Kraft
 * @license		GPL2 <https://www.gnu.org/licenses/gpl-3.0.html>
 */
document.addEventListener( 'DOMContentLoaded', function() {
	var press_law_checkbox = document.getElementById( 'press_law_checkbox' );
	var press_law_input_row = document.getElementsByClassName( 'impressum_press_law' )[0];
	
	// return if there is no input row found
	if ( press_law_input_row === undefined ) return;
	
	// if checkbox is not checked
	if ( ! press_law_checkbox.checked ) {
		// hide the input
		press_law_input_row.style.display = 'none';
	}
	
	// on click on checkbox
	press_law_checkbox.addEventListener( 'click', function( event ) {
		var current_target = event.currentTarget;
		
		// if checkbox is checked
		if ( current_target.checked ) {
			// remove inline style
			press_law_input_row.removeAttribute( 'style' );
		}
		else {
			// hide the input
			press_law_input_row.style.display = 'none';
		}
	} );
} );