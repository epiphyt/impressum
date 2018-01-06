/*!
 * JavaScript functions for the admin options page.
 * 
 * @version		0.1
 * @author		Matthias Kittsteiner, Simon Kraft
 * @license		GPL2 <https://www.gnu.org/licenses/gpl-3.0.html>
 */
document.addEventListener( 'DOMContentLoaded', function() {
	var legal_entity_select = document.getElementById( 'legal_entity' );
	var press_law_checkbox = document.getElementById( 'press_law_checkbox' );
	var press_law_input_row = document.getElementsByClassName( 'impressum_press_law' )[0];
	
	// function calls
	check_legal_entity();
	check_press_law();
	
	/**
	 * Check for given values of the legal entity and show or hide elements.
	 */
	function check_legal_entity() {
		var business_id_row = document.getElementsByClassName( 'impressum_business_id' )[0];
		var capital_stock_row = document.getElementsByClassName( 'impressum_capital_stock' )[0];
		var inspecting_authority_row = document.getElementsByClassName( 'impressum_inspecting_authority' )[0];
		var legal_job_title_row = document.getElementsByClassName( 'impressum_legal_job_title' )[0];
		var pending_deposits_row = document.getElementsByClassName( 'impressum_pending_deposits' )[0];
		var professional_association_row = document.getElementsByClassName( 'impressum_professional_association' )[0];
		var professional_regulations_row = document.getElementsByClassName( 'impressum_professional_regulations' )[0];
		var register_row = document.getElementsByClassName( 'impressum_register' )[0];
		var representative_row = document.getElementsByClassName( 'impressum_representative' )[0];
		
		// check on page load
		switch ( legal_entity_select.value ) {
			case 'individual':
				business_id_row.style.display = 'none';
				capital_stock_row.style.display = 'none';
				inspecting_authority_row.style.display = 'none';
				legal_job_title_row.style.display = 'none';
				pending_deposits_row.style.display = 'none';
				professional_association_row.style.display = 'none';
				professional_regulations_row.style.display = 'none';
				register_row.style.display = 'none';
				representative_row.style.display = 'none';
				break;
		}
		
		// check on select change
		legal_entity_select.addEventListener( 'change', function( event ) {
			var current_target = event.currentTarget;
			
			switch ( current_target.value ) {
				case 'individual':
					business_id_row.style.display = 'none';
					capital_stock_row.style.display = 'none';
					inspecting_authority_row.style.display = 'none';
					legal_job_title_row.style.display = 'none';
					pending_deposits_row.style.display = 'none';
					professional_association_row.style.display = 'none';
					professional_regulations_row.style.display = 'none';
					register_row.style.display = 'none';
					representative_row.style.display = 'none';
					break;
				default:
					business_id_row.style.display = '';
					capital_stock_row.style.display = '';
					inspecting_authority_row.style.display = '';
					legal_job_title_row.style.display = '';
					pending_deposits_row.style.display = '';
					professional_association_row.style.display = '';
					professional_regulations_row.style.display = '';
					register_row.style.display = '';
					representative_row.style.display = '';
					break;
			}
		} );
	}
	
	/**
	 * Check if the user enabled the press law checkbox.
	 */
	function check_press_law() {
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
	}
} );