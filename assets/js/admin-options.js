/* eslint-disable no-var */
/**
 * JavaScript functions for the admin options page.
 *
 * @author	Epiphyt
 * @license	GPL2
 */

// all fields that should be be checked by there value
var fieldsToCheck = [
	'address',
	'contact_form_page',
	'country',
	'email',
	'name',
	'phone',
];

// at least one of the dependent fields must have a value when the current field
// does not
const fieldDependencies = {
	contact_form_page: [ 'phone' ],
	phone: [ 'contact_form_page' ],
};

/* global imprintL10n */
document.addEventListener( 'DOMContentLoaded', function () {
	const businessId = document.getElementById( 'business_id' );
	const businessIdRow = document.querySelector( '.impressum_business_id' );
	var countrySelect = document.getElementById( 'country' );
	var legalEntitySelect = document.getElementById( 'legal_entity' );
	var pressLawCheckbox = document.getElementById( 'press_law_checkbox' );
	var pressLawCheckboxRow = document.querySelector(
		'.impressum_press_law_checkbox'
	);
	var pressLawInputRow = document.querySelector( '.impressum_press_law' );
	var vatId = document.getElementById( 'vat_id' );

	// function calls
	for ( var i = 0; i < fieldsToCheck.length; i++ ) {
		checkFieldLength(
			document.getElementById( fieldsToCheck[ i ] ),
			fieldsToCheck[ i ]
		);
	}

	if ( countrySelect ) {
		checkCountry();
	}

	if ( legalEntitySelect ) {
		checkLegalEntity();
	}

	if ( pressLawCheckbox && pressLawInputRow ) {
		checkPressLaw();
	}

	// check formal vat id validity
	if ( businessId ) {
		// use keyup instead of input to match also autocomplete values
		businessId.addEventListener( 'keyup', ( event ) => {
			const currentTarget = event.currentTarget;
			// replace any whitespaces
			const regex = new RegExp( /[^A-Za-z0-9\*\+\-]+/g );

			if ( regex.test( currentTarget.value ) ) {
				currentTarget.value = currentTarget.value.replace(
					/[^A-Za-z0-9\*\+\-]+/g,
					''
				);
			}
		} );
		// use keyup instead of input to match also autocomplete values
		businessId.addEventListener(
			'keyup',
			debounce( ( event ) => {
				const currentTarget = event.target;
				// replace any whitespaces
				const regex = new RegExp( /[^A-Za-z0-9\*\+\-]+/g );

				if ( regex.test( currentTarget.value ) ) {
					currentTarget.value = currentTarget.value.replace(
						/[^A-Za-z0-9\*\+\-]+/g,
						''
					);
				}

				// do the check
				if ( ! isValidBusinessIdFormat( currentTarget.value ) ) {
					toggleMessage(
						false,
						businessId,
						imprintL10n.businessIdErrorMessage
					);
				} else {
					toggleMessage( true, businessId, '' );
				}
			} )
		);
	}

	// check formal vat id validity
	if ( vatId ) {
		// use keyup instead of input to match also autocomplete values
		vatId.addEventListener( 'keyup', ( event ) => {
			const currentTarget = event.currentTarget;
			// replace any whitespaces
			const regex = new RegExp( /[^A-Za-z0-9\*\+]+/g );

			if ( regex.test( currentTarget.value ) ) {
				currentTarget.value = currentTarget.value.replace(
					/[^A-Za-z0-9\*\+]+/g,
					''
				);
			}
		} );
		vatId.addEventListener(
			'keyup',
			debounce( ( event ) => {
				const currentTarget = event.target;
				// replace any whitespaces
				const regex = new RegExp( /[^A-Za-z0-9\*\+]+/g );

				if ( regex.test( currentTarget.value ) ) {
					currentTarget.value = currentTarget.value.replace(
						/[^A-Za-z0-9\*\+]+/g,
						''
					);
				}

				// do the check
				if ( ! isValidVatIdFormat( currentTarget.value ) ) {
					toggleMessage(
						false,
						vatId,
						imprintL10n.vatIdErrorMessage
					);
				} else {
					toggleMessage( true, vatId, '' );
				}
			} )
		);
	}

	/**
	 * Check given value of a field and show or hide a message.
	 *
	 * @param {Element} field     The field DOM element to check
	 * @param {string}  fieldName The name of the field
	 */
	function checkFieldLength( field, fieldName ) {
		const snakeToCamel = ( str ) =>
			str
				.toLowerCase()
				.replace( /([-_][a-z])/g, ( group ) =>
					group.toUpperCase().replace( '-', '' ).replace( '_', '' )
				);
		fieldName = snakeToCamel( fieldName );
		var message = imprintL10n[ fieldName + 'ErrorMessage' ];

		// check on change or input
		[ 'change', 'input' ].forEach( ( eventName ) => {
			if ( ! field ) {
				return;
			}

			field.addEventListener( eventName, ( event ) =>
				checkFieldValue( event.currentTarget, fieldName, message )
			);
		} );
	}

	/**
	 * Check field values for content.
	 *
	 * @param {HTMLElement} field     Field to check
	 * @param {string}      fieldName Field name
	 * @param {string}      message   Message to toggle
	 */
	function checkFieldValue( field, fieldName, message ) {
		const hideMessage =
			field.value.length !== 0 || field?.placeholder?.length;
		const dependenciesMet = checkFieldValueDependencies(
			fieldName,
			hideMessage
		);

		toggleMessage( hideMessage || dependenciesMet, field, message );
	}

	/**
	 * Check whether the dependencies of a field met their value requirements.
	 *
	 * @param {string}  fieldName    Field name
	 * @param {boolean} isCurrentMet Whether the current field met the requirements
	 * @return {boolean} Whether the field dependencies all met
	 */
	function checkFieldValueDependencies( fieldName, isCurrentMet ) {
		if ( ! fieldDependencies[ fieldName ] ) {
			return false;
		}

		for ( const dependency of fieldDependencies[ fieldName ] ) {
			const field = document.getElementById( dependency );

			if ( isCurrentMet ) {
				toggleMessage( true, field );
			}

			if ( field.value.length !== 0 || field?.placeholder?.length ) {
				return true;
			}
		}

		return false;
	}

	/**
	 * Check for given values of the country and show or hide elements.
	 */
	function checkCountry() {
		toggleFieldsByCountry( countrySelect.value, legalEntitySelect.value );
		toggleFieldsByCountry( countrySelect.value, businessId.value );

		// check on select change
		countrySelect.addEventListener( 'change', function ( event ) {
			toggleFieldsByCountry(
				event.currentTarget.value,
				legalEntitySelect.value
			);
			toggleFieldsByCountry(
				event.currentTarget.value,
				businessId.value
			);
		} );
	}

	/**
	 * Check for given values of the legal entity and show or hide elements.
	 */
	function checkLegalEntity() {
		// check on page load
		var message = imprintL10n.legalEntityErrorMessage;
		var needProMessage =
			legalEntitySelect.value === 'individual' ||
			legalEntitySelect.value === 'self';
		toggleMessage( needProMessage, legalEntitySelect, message );

		// check on select change
		legalEntitySelect.addEventListener( 'change', function ( event ) {
			var currentTarget = event.currentTarget;

			needProMessage =
				currentTarget.value === 'individual' ||
				currentTarget.value === 'self';
			toggleMessage( needProMessage, legalEntitySelect, message );
		} );
	}

	/**
	 * Check if the user enabled the press law checkbox.
	 */
	function checkPressLaw() {
		// return if there is no input row found
		if ( pressLawInputRow === undefined ) {
			return;
		}

		// if checkbox is not checked
		if ( ! pressLawCheckbox.checked ) {
			// hide the input
			pressLawInputRow.style.display = 'none';
		}

		// on click on checkbox
		pressLawCheckbox.addEventListener( 'click', function ( event ) {
			var currentTarget = event.currentTarget;

			// if checkbox is checked
			if ( currentTarget.checked ) {
				// remove inline style
				pressLawInputRow.removeAttribute( 'style' );
			} else {
				// hide the input
				pressLawInputRow.style.display = 'none';
			}
		} );
	}

	/**
	 * Check if business ID has a valid format.
	 *
	 * @param {string} value The value to check
	 * @return	{boolean} Whether business ID has valid format
	 */
	function isValidBusinessIdFormat( value ) {
		const regex = new RegExp( '^(|DE[0-9]{9}-[0-9]{5})$' );

		return regex.test( value );
	}

	/**
	 * Check if VAT ID has a valid format.
	 *
	 * @param {string} value The value to check
	 * @return	{boolean} True if VAT number is in valid format, false otherwise
	 */
	function isValidVatIdFormat( value ) {
		// see: https://www.oreilly.com/library/view/regular-expressions-cookbook/9781449327453/ch04s21.html
		// modified to also allow * and + for Netherlands and empty values
		var regex = new RegExp(
			'^(|ATU[0-9]{8}|BE0[0-9]{9}|BG[0-9]{9,10}|CY[0-9]{8}L|CZ[0-9]{8,10}|DE[0-9]{9}|DK[0-9]{8}|EE[0-9]{9}|(EL|GR)[0-9]{9}|ES[0-9A-Z][0-9]{7}[0-9A-Z]|FI[0-9]{8}|FR[0-9A-Z]{2}[0-9]{9}|GB([0-9]{9}([0-9]{3})?|[A-Z]{2}[0-9]{3})|HU[0-9]{8}|IE[0-9]S[0-9]{5}L|IT[0-9]{11}|LT([0-9]{9}|[0-9]{12})|LU[0-9]{8}|LV[0-9]{11}|MT[0-9]{8}|NL[0-9+*]{9}B[0-9]{2}|PL[0-9]{10}|PT[0-9]{9}|RO[0-9]{2,10}|SE[0-9]{12}|SI[0-9]{8}|SK[0-9]{10})$'
		);

		return regex.test( value );
	}

	/**
	 * Toggle fields by selected country.
	 *
	 * @param {string} country     The selected country
	 * @param {string} legalEntity The selected legal entity
	 */
	// eslint-disable-next-line no-unused-vars
	function toggleFieldsByCountry( country, legalEntity ) {
		let businessIdOrVatIdMessageContainerBusiness =
			businessId.parentNode.querySelector(
				'.is-business-or-vat-id-description'
			);
		let businessIdOrVatIdMessageContainerVat =
			vatId.parentNode.querySelector(
				'.is-business-or-vat-id-description'
			);

		switch ( country ) {
			case 'de-de':
			case 'deu':
				businessIdRow.style.removeProperty( 'display' );
				pressLawCheckboxRow.style.removeProperty( 'display' );
				pressLawInputRow.style.removeProperty( 'display' );

				businessIdOrVatIdMessageContainerBusiness = toggleDescription(
					'show',
					businessIdOrVatIdMessageContainerBusiness,
					'is-business-or-vat-id-description',
					imprintL10n.businessIdOrVatIdMessage,
					businessId.parentNode
				);
				businessIdOrVatIdMessageContainerVat = toggleDescription(
					'show',
					businessIdOrVatIdMessageContainerBusiness,
					'is-business-or-vat-id-description',
					imprintL10n.businessIdOrVatIdMessage,
					vatId.parentNode
				);
				break;
			default:
				businessIdRow.style.display = 'none';
				pressLawCheckboxRow.style.display = 'none';
				pressLawInputRow.style.display = 'none';

				toggleDescription(
					'hide',
					businessIdOrVatIdMessageContainerBusiness
				);
				toggleDescription(
					'hide',
					businessIdOrVatIdMessageContainerVat
				);
				break;
		}
	}

	/**
	 * Toggle field descriptions.
	 *
	 * @param {string}       mode          The mode to perform, 'show' or 'hide'
	 * @param {?HTMLElement} element       Element to show/hide/create
	 * @param {?string}      identifier    Identifier to add as class
	 * @param {?string}      text          Text to add to the element
	 * @param {?HTMLElement} parentElement Parent element to add the element to
	 * @return {HTMLElement|void} The created element or nothing
	 */
	function toggleDescription(
		mode,
		element,
		identifier,
		text,
		parentElement
	) {
		if ( mode === 'show' && ! element ) {
			element = document.createElement( 'p' );
			element.classList.add(
				'description',
				'impressum__description',
				identifier
			);
			element.textContent = text;
			parentElement.appendChild( element );

			return element;
		} else if ( mode === 'hide' ) {
			element.remove();
		}
	}

	/**
	 * Toggle the notification about using the Pro version.
	 *
	 * @param {boolean} hideMessage True if message should be hidden, false otherwise
	 * @param {Element} container   The container element that should contain the notification
	 * @param {string}  text        The text the notification should contain
	 */
	function toggleMessage( hideMessage, container, text ) {
		var noticeElement = container.nextElementSibling;

		if (
			! hideMessage &&
			( noticeElement === null ||
				! noticeElement.classList.contains( 'notice' ) )
		) {
			var message = document.createElement( 'p' );
			var notice = document.createElement( 'div' );

			message.innerText = text;
			notice.style.maxWidth = '436px';
			notice.classList.add( 'notice' );
			notice.classList.add( 'inline' ); // prevent moving the notice below the headline
			notice.classList.add( 'notice-warning' );
			notice.appendChild( message );
			container.after( notice );
		} else if ( hideMessage ) {
			if (
				noticeElement !== null &&
				noticeElement.classList.contains( 'notice' )
			) {
				noticeElement.remove();
			}
		}
	}
} );

if ( ! Array.prototype.inArray ) {
	/**
	 * Check if an array contains a specified value.
	 *
	 * @param {string} needle Needle to check
	 * @return {boolean} Whether the array contains the vails
	 */
	Array.prototype.inArray = function ( needle ) {
		var length = this.length;

		for ( var i = 0; i < length; i++ ) {
			if ( this[ i ] === needle ) {
				return true;
			}
		}

		return false;
	};
}

/**
 * Polyfill for Child.after()
 *
 * @see https://github.com/jserz/js_piece/blob/master/DOM/ChildNode/after()/after().md
 * @see https://developer.mozilla.org/en-US/docs/Web/API/ChildNode/after
 *
 * @param {Array} arr Array
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
					// eslint-disable-next-line no-undef
					var isNode = argItem instanceof Node;
					docFrag.appendChild(
						isNode
							? argItem
							: document.createTextNode( String( argItem ) )
					);
				} );

				this.parentNode.insertBefore(
					docFrag,
					this.nextElementSibling
				);
			},
		} );
	} );
	// eslint-disable-next-line no-undef
} )( [ Element.prototype, CharacterData.prototype, DocumentType.prototype ] );

/**
 * Polyfill for ChildNode.remove()
 *
 * @see https://github.com/jserz/js_piece/blob/master/DOM/ChildNode/remove()/remove().md
 * @see https://developer.mozilla.org/en-US/docs/Web/API/ChildNode/remove
 *
 * @param {Array} arr Array
 */
( function ( arr ) {
	arr.forEach( function ( item ) {
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
			},
		} );
	} );
	// eslint-disable-next-line no-undef
} )( [ Element.prototype, CharacterData.prototype, DocumentType.prototype ] );

/**
 * Debounce a function.
 *
 * @param {Function} func    Function to debounce
 * @param {number}   timeout Debouncing timeout
 * @return {Function} return value
 */
function debounce( func, timeout = 300 ) {
	let timer;

	return ( ...args ) => {
		clearTimeout( timer );

		timer = setTimeout( () => {
			func.apply( this, args );
		}, timeout );
	};
}
