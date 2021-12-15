/**
 * JavaScript functions for the admin options page.
 * 
 * @author		Epiphyt
 * @license		GPL2 <https://www.gnu.org/licenses/gpl-2.0.html>
 */

// all fields that should be be checked by there value
var fieldsToCheck = [
	'address',
	'country',
	'email',
	'name',
	'phone',
];

document.addEventListener( 'DOMContentLoaded', function() {
	var countrySelect = document.getElementById( 'country' );
	var legalEntitySelect = document.getElementById( 'legal_entity' );
	var pressLawCheckbox = document.getElementById( 'press_law_checkbox' );
	var pressLawCheckboxRow = document.querySelector( '.impressum_press_law_checkbox' );
	var pressLawInputRow = document.querySelector( '.impressum_press_law' );
	var vatId = document.getElementById( 'vat_id' );
	
	// function calls
	for ( var i = 0; i < fieldsToCheck.length; i++ ) {
		checkFieldLength( document.getElementById( fieldsToCheck[ i ] ), fieldsToCheck[ i ] );
	}
	
	if ( countrySelect ) checkCountry();
	if ( legalEntitySelect ) checkLegalEntity();
	if ( pressLawCheckbox && pressLawInputRow ) checkPressLaw();
	
	// check formal vat id validity
	if ( vatId ) {
		// use keyup instead of input to match also autocomplete values
		vatId.addEventListener( 'keyup', function( event ) {
			var currentTarget = event.currentTarget;
			
			// replace any whitespaces
			var regex = new RegExp( /[^A-Za-z0-9\*\+]+/g );
			// test before, otherwise you can’t select the value
			if ( regex.test( currentTarget.value ) ) {
				currentTarget.value = currentTarget.value.replace( /[^A-Za-z0-9\*\+]+/g, '' );
			}
			
			// do the check
			if ( ! isValidVatIdFormat( currentTarget.value ) ) {
				var message = imprintL10n.vatIdErrorMessage;
				
				toggleMessage( false, vatId, message );
			}
			else {
				toggleMessage( true, vatId, '' );
			}
		} );
	}
	
	/**
	 * Check given value of a field and show or hide a message.
	 * 
	 * @param	{Element}	field The field DOM element to check
	 * @param	{String}	fieldName The name of the field
	 */
	function checkFieldLength( field, fieldName ) {
		var message = imprintL10n[ fieldName + 'ErrorMessage' ];
		
		// check on change or input
		[ 'change', 'input' ].forEach( function( event ) {
			if ( ! field ) return;
			
			field.addEventListener( event, function( event ) {
				var currentTarget = event.currentTarget;
				var hideMessage = currentTarget.value.length !== 0 || currentTarget.placeholder.length !== 0;
				
				toggleMessage( hideMessage, field, message );
			} );
		} );
	}
	
	/**
	 * Check for given values of the country and show or hide elements.
	 */
	function checkCountry() {
		toggleFieldsByCountry( countrySelect.value, legalEntitySelect.value );
		
		// check on select change
		countrySelect.addEventListener( 'change', function( event ) {
			toggleFieldsByCountry( event.currentTarget.value, legalEntitySelect.value );
		} );
	}
	
	/**
	 * Check for given values of the legal entity and show or hide elements.
	 */
	function checkLegalEntity() {
		// check on page load
		var message = imprintL10n.legalEntityErrorMessage;
		var needProMessage = legalEntitySelect.value === 'individual' || legalEntitySelect.value === 'self';
		toggleMessage( needProMessage, legalEntitySelect, message );
		
		// check on select change
		legalEntitySelect.addEventListener( 'change', function( event ) {
			var currentTarget = event.currentTarget;
			
			needProMessage = currentTarget.value === 'individual' || currentTarget.value === 'self';
			toggleMessage( needProMessage, legalEntitySelect, message );
		} );
	}
	
	/**
	 * Check if the user enabled the press law checkbox.
	 */
	function checkPressLaw() {
		// return if there is no input row found
		if ( pressLawInputRow === undefined ) return;
		
		// if checkbox is not checked
		if ( ! pressLawCheckbox.checked ) {
			// hide the input
			pressLawInputRow.style.display = 'none';
		}
		
		// on click on checkbox
		pressLawCheckbox.addEventListener( 'click', function( event ) {
			var currentTarget = event.currentTarget;
			
			// if checkbox is checked
			if ( currentTarget.checked ) {
				// remove inline style
				pressLawInputRow.removeAttribute( 'style' );
			}
			else {
				// hide the input
				pressLawInputRow.style.display = 'none';
			}
		} );
	}
	
	/**
	 * Check if VAT ID has a valid format.
	 * 
	 * @param	{string}	value The value to check
	 * @return	{boolean} True if VAT number is in valid format, false otherwise
	 */
	function isValidVatIdFormat( value ) {
		// see: https://www.oreilly.com/library/view/regular-expressions-cookbook/9781449327453/ch04s21.html
		// modified to also allow * and + for Netherlands
		var regex = new RegExp( '^((AT)?U[0-9]{8}|(BE)?0[0-9]{9}|(BG)?[0-9]{9,10}|(CY)?[0-9]{8}L|(CZ)?[0-9]{8,10}|(DE)?[0-9]{9}|(DK)?[0-9]{8}|(EE)?[0-9]{9}|(EL|GR)?[0-9]{9}|(ES)?[0-9A-Z][0-9]{7}[0-9A-Z]|(FI)?[0-9]{8}|(FR)?[0-9A-Z]{2}[0-9]{9}|(GB)?([0-9]{9}([0-9]{3})?|[A-Z]{2}[0-9]{3})|(HU)?[0-9]{8}|(IE)?[0-9]S[0-9]{5}L|(IT)?[0-9]{11}|(LT)?([0-9]{9}|[0-9]{12})|(LU)?[0-9]{8}|(LV)?[0-9]{11}|(MT)?[0-9]{8}|(NL)?[0-9\+\*]{9}B[0-9]{2}|(PL)?[0-9]{10}|(PT)?[0-9]{9}|(RO)?[0-9]{2,10}|(SE)?[0-9]{12}|(SI)?[0-9]{8}|(SK)?[0-9]{10})$' );
		
		return regex.test( value );
	}
	
	/**
	 * Toggle fields by selected country.
	 * 
	 * @param	{String}	country The selected country
	 * @param	{String}	legalEntity The selected legal entity
	 */
	function toggleFieldsByCountry( country, legalEntity ) {
		switch ( country ) {
			case 'de-de':
			case 'deu':
				pressLawCheckboxRow.style.removeProperty( 'display' );
				pressLawInputRow.style.removeProperty( 'display' );
				break;
			default:
				pressLawCheckboxRow.style.display = 'none';
				pressLawInputRow.style.display = 'none';
				break;
		}
	}
	
	/**
	 * Toggle the notification about using the Pro version.
	 * 
	 * @param	{Boolean}	hideMessage True if message should be hidden, false otherwise
	 * @param	{Element}	container The container element that should contain the notification
	 * @param	{String}	text The text the notification should contain
	 */
	function toggleMessage( hideMessage, container, text ) {
		var noticeElement = container.nextElementSibling;
		
		if ( ! hideMessage && ( noticeElement === null || ! noticeElement.classList.contains( 'notice' ) ) ) {
			var message = document.createElement( 'p' );
			var notice = document.createElement( 'div' );
			
			message.innerText = text;
			notice.style.maxWidth = '436px';
			notice.classList.add( 'notice' );
			notice.classList.add( 'inline' ); // prevent moving the notice below the headline
			notice.classList.add( 'notice-warning' );
			notice.appendChild( message );
			container.after( notice );
		}
		else if ( hideMessage ) {
			if ( noticeElement !== null && noticeElement.classList.contains( 'notice' ) ) {
				noticeElement.remove();
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
				
				this.parentNode.insertBefore( docFrag, this.nextElementSibling );
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
