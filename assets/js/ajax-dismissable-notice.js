/**
 * JavaScript function to permanently dismiss a notice in admin.
 * 
 * @version		1.0.1
 * @author		Matthias Kittsteiner, Simon Kraft
 * @license		GPL3 <https://www.gnu.org/licenses/gpl-3.0.html>
 */
jQuery( function( $ ) {
	$( document ).on( 'click', '.impressum-validation-notice > .notice-dismiss', function() {
		var type = $( this ).closest( '.impressum-validation-notice' ).data( 'notice' );
		
		$.ajax( ajaxurl, {
			type: 'POST',
			data: {
				action: 'impressum_dismissed_notice_handler',
				type: type,
			}
		} );
	} );
	
	$( document ).on( 'click', '.impressum-welcome-notice-dismiss', function( event ) {
		var type = $( event.currentTarget ).data( 'notice' );
		
		$.ajax( ajaxurl, {
			type: 'POST',
			data: {
				action: 'impressum_dismissed_notice_handler',
				type: type,
			}
		} ).done( function() {
			$( '.impressum-welcome-panel' ).parent( '.wrap' ).hide();
		} );
	} );
} );