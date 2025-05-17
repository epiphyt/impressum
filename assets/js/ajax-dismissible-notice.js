/**
 * JavaScript function to permanently dismiss a notice in admin.
 * 
 * @author	Epiphyt
 * @license	GPL2
 * @package	epiphyt\Impressum
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
			$( '.impressum-welcome-panel' ).parent( '.impressum-wrap' ).hide();
		} );
	} );
} );
