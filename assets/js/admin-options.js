/**
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
		// check on page load
		var need_pro_message = legal_entity_select.value === 'individual' || legal_entity_select.value === 'self';
		toggle_pro_message( need_pro_message );
		
		// check on select change
		legal_entity_select.addEventListener( 'change', function( event ) {
			var current_target = event.currentTarget;
			need_pro_message = current_target.value === 'individual' || current_target.value === 'self';
			
			toggle_pro_message( need_pro_message );
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
	 * Toggle the notification about using the Pro version.
	 * 
	 * @param {Bool} is_individual
	 */
	function toggle_pro_message( is_individual ) {
		var notice_container = document.querySelector( '#legal_entity + .notice' );
		
		if ( ! is_individual && notice_container === null ) {
			var message = document.createElement( 'p' );
			var notice = document.createElement( 'div' );
			
			message.innerText = imprintL10n.error_message;
			notice.style.maxWidth = '436px';
			notice.classList.add( 'notice' );
			notice.classList.add( 'error' );
			notice.appendChild( message );
			legal_entity_select.after( notice );
		}
		else if ( is_individual ) {
			if (notice_container !== null) notice_container.remove();
		}
	}
} );

/**
 * Polyfill for Child.after()
 * 
 * @see https://github.com/jserz/js_piece/blob/master/DOM/ChildNode/after()/after().md
 * @see https://developer.mozilla.org/en-US/docs/Web/API/ChildNode/after
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