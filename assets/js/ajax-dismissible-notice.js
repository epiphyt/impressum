/**
 * Permanently dismiss an admin notice via AJAX (vanilla JavaScript).
 *
 * @author	Epiphyt
 * @license	GPL2
 */
/* global imprintNotice */
( function () {
	/**
	 * Send the dismiss request for a given notice type.
	 *
	 * @param {string} type The notice type to dismiss
	 * @return {Promise} The resulting fetch promise
	 */
	function dismissNotice( type ) {
		const body = new URLSearchParams();
		body.append( 'action', 'impressum_dismissed_notice_handler' );
		body.append( 'nonce', imprintNotice.nonce );
		body.append( 'type', type );

		return fetch( imprintNotice.ajaxUrl, {
			body: body.toString(),
			credentials: 'same-origin',
			headers: {
				'Content-Type':
					'application/x-www-form-urlencoded; charset=UTF-8',
			},
			method: 'POST',
		} );
	}

	document.addEventListener( 'click', function ( event ) {
		const validationDismiss = event.target.closest(
			'.impressum-validation-notice > .notice-dismiss'
		);

		if ( validationDismiss ) {
			const notice = validationDismiss.closest(
				'.impressum-validation-notice'
			);

			if ( notice ) {
				dismissNotice( notice.dataset.notice );
			}

			return;
		}

		const welcomeDismiss = event.target.closest(
			'.impressum-welcome-notice-dismiss'
		);

		if ( welcomeDismiss ) {
			dismissNotice( welcomeDismiss.dataset.notice ).then( () => {
				const panel = document.querySelector(
					'.impressum-welcome-panel'
				);
				const wrap = panel ? panel.closest( '.impressum-wrap' ) : null;

				if ( wrap ) {
					wrap.style.display = 'none';
				}
			} );
		}
	} );
} )();
