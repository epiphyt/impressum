/**
 * JavaScript functions for the admin options page.
 * 
 * @version		1.0.2
 * @author		Epiphyt
 * @license		GPL3 <https://www.gnu.org/licenses/gpl-3.0.html>
 */

// all fields that should be be checked by there value
var fields_to_check = [
	'address',
	'country',
	'email',
	'name',
	'phone',
	'register',
	'representative',
];

document.addEventListener( 'DOMContentLoaded', function() {
	var legal_entity_select = document.getElementById( 'legal_entity' );
	var press_law_checkbox = document.getElementById( 'press_law_checkbox' );
	var press_law_input_row = document.querySelector( '.impressum_press_law' );
	var vat_id = document.getElementById( 'vat_id' );
	
	// function calls
	for ( var i = 0; i < fields_to_check.length; i++ ) {
		check_field_length( document.getElementById( fields_to_check[ i ] ), fields_to_check[ i ] );
	}
	
	if ( legal_entity_select ) check_legal_entity();
	if ( press_law_checkbox && press_law_input_row ) check_press_law();
	
	// check formal vat id validity
	if ( vat_id ) {
		// use keyup instead of input to match also autocomplete values
		vat_id.addEventListener( 'keyup', function( event ) {
			var current_target = event.currentTarget;
			
			// replace any whitespaces
			var regex = new RegExp( /[^A-Za-z0-9]+/g );
			// test before, otherwise you can’t select the value
			if ( regex.test( current_target.value ) ) {
				current_target.value = current_target.value.replace( /[^A-Za-z0-9]+/g, '' );
			}
			
			// do the check
			if ( ! is_valid_vat_id_format( current_target.value ) ) {
				var message = imprintL10n.vat_id_error_message;
				
				toggle_message( false, vat_id, message );
			}
			else {
				toggle_message( true, vat_id, '' );
			}
		} );
	}
	
	/**
	 * Check given value of a field and show or hide a message.
	 * 
	 * @param	{Element}		field The field DOM element to check
	 * @param	{String}		field_name The name of the field
	 */
	function check_field_length( field, field_name ) {
		var error_function = field_name + '_error_message';
		var message = imprintL10n[error_function];
		
		// check on change or input
		[ 'change', 'input' ].forEach( function( event ) {
			if ( ! field ) return;
			
			field.addEventListener( event, function( event ) {
				var current_target = event.currentTarget;
				var hide_message = current_target.value.length !== 0;
				
				toggle_message( hide_message, field, message );
			} );
		} );
	}
	
	/**
	 * Check for given values of the legal entity and show or hide elements.
	 */
	function check_legal_entity() {
		// check on page load
		var message = imprintL10n.legal_entity_error_message;
		var need_pro_message = legal_entity_select.value === 'individual' || legal_entity_select.value === 'self' || current_target.value === '';;
		toggle_message( need_pro_message, legal_entity_select, message );
		
		// check on select change
		legal_entity_select.addEventListener( 'change', function( event ) {
			var current_target = event.currentTarget;
			
			need_pro_message = current_target.value === 'individual' || current_target.value === 'self' || current_target.value === '';
			toggle_message( need_pro_message, legal_entity_select, message );
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
	
	/**
	 * Check if VAT ID has a valid format.
	 * 
	 * @param		{string}		value
	 * @return		{boolean}
	 */
	function is_valid_vat_id_format( value ) {
		// see: https://www.oreilly.com/library/view/regular-expressions-cookbook/9781449327453/ch04s21.html
		var regex = new RegExp( '^((AT)?U[0-9]{8}|(BE)?0[0-9]{9}|(BG)?[0-9]{9,10}|(CY)?[0-9]{8}L|(CZ)?[0-9]{8,10}|(DE)?[0-9]{9}|(DK)?[0-9]{8}|(EE)?[0-9]{9}|(EL|GR)?[0-9]{9}|(ES)?[0-9A-Z][0-9]{7}[0-9A-Z]|(FI)?[0-9]{8}|(FR)?[0-9A-Z]{2}[0-9]{9}|(GB)?([0-9]{9}([0-9]{3})?|[A-Z]{2}[0-9]{3})|(HU)?[0-9]{8}|(IE)?[0-9]S[0-9]{5}L|(IT)?[0-9]{11}|(LT)?([0-9]{9}|[0-9]{12})|(LU)?[0-9]{8}|(LV)?[0-9]{11}|(MT)?[0-9]{8}|(NL)?[0-9]{9}B[0-9]{2}|(PL)?[0-9]{10}|(PT)?[0-9]{9}|(RO)?[0-9]{2,10}|(SE)?[0-9]{12}|(SI)?[0-9]{8}|(SK)?[0-9]{10})$' );
		
		return regex.test( value );
	}
	
	/**
	 * Toggle the notification about using the Pro version.
	 * 
	 * @param		{boolean}		hide_message
	 * @param		{element}		container
	 * @param		{string}		text
	 */
	function toggle_message( hide_message, container, text ) {
		var notice_element = container.nextElementSibling;
		
		if ( ! hide_message && ( notice_element === null || ! notice_element.classList.contains( 'notice' ) ) ) {
			var message = document.createElement( 'p' );
			var notice = document.createElement( 'div' );
			
			message.innerText = text;
			notice.style.maxWidth = '436px';
			notice.classList.add( 'notice' );
			notice.classList.add( 'notice-warning' );
			notice.appendChild( message );
			container.after( notice );
		}
		else if ( hide_message ) {
			if ( notice_element !== null && notice_element.classList.contains( 'notice' ) ) {
				notice_element.remove();
			}
		}
	}
} );

if ( ! Array.prototype.inArray ) {
	/**
	 * Check if an array contains a specified value.
	 * 
	 * @param	{String}	needle
	 * @return	{boolean}
	 */
	Array.prototype.inArray = function( needle ) {
		var length = this.length;
		
		for ( var i = 0; i < length; i++ ) {
			if ( this[ i ] === needle ) return true;
		}
		
		return false;
	}
}

/**
 * Polyfill for Child.after()
 * 
 * @see		https://github.com/jserz/js_piece/blob/master/DOM/ChildNode/after()/after().md
 * @see		https://developer.mozilla.org/en-US/docs/Web/API/ChildNode/after
 */
( function ( arr ) {
	arr.forEach( function ( item ) {
		if ( item.hasOwnProperty( 'after' ) ) {
			return;
		}
		Object.defineProperty( item, 'after', {
			configurable: true,
			enumerable: true,
			writable: true,
			value: function after() {
				var argArr = Array.prototype.slice.call( arguments ),
					docFrag = document.createDocumentFragment();
				
				argArr.forEach( function ( argItem ) {
					var isNode = argItem instanceof Node;
					docFrag.appendChild( isNode ? argItem : document.createTextNode( String( argItem ) ) );
				} );
				
				this.parentNode.insertBefore( docFrag, this.nextSibling );
			}
		} );
	} );
} ) ( [ Element.prototype, CharacterData.prototype, DocumentType.prototype ] );

/**
 * Polyfill for ChildNode.remove()
 * 
 * @see		https://github.com/jserz/js_piece/blob/master/DOM/ChildNode/remove()/remove().md
 * @see		https://developer.mozilla.org/en-US/docs/Web/API/ChildNode/remove
 */
( function( arr ) {
	arr.forEach( function( item ) {
		if ( item.hasOwnProperty( 'remove' ) ) {
			return;
		}
		Object.defineProperty( item, 'remove', {
			configurable: true,
			enumerable: true,
			writable: true,
			value: function remove() {
				if ( this.parentNode !== null ) {
					this.parentNode.removeChild( this );
				}
			}
		} );
	} );
} )( [ Element.prototype, CharacterData.prototype, DocumentType.prototype ] );