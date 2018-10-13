/**
 * JavaScript function to permanently dismiss a notice in admin.
 * 
 * @author		Epiphyt
 * @license		GPL3 <https://www.gnu.org/licenses/gpl-3.0.html>
 * @version		1.0.0
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
} );