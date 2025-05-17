document.addEventListener( 'DOMContentLoaded', () => {
	const tabs = document.querySelectorAll( '.nav-tab' );
	const tabContents = document.querySelectorAll( '.nav-tab-content' );
	const tabParameter = 'imprint_tab';
	const urlParams = new URLSearchParams( window.location.search );
	const activeTab = urlParams.get( tabParameter );
	const refererInput = document.querySelector( '[name="_wp_http_referer"]' );
	
	if ( activeTab ) {
		setActiveTab( activeTab );
	}
	
	for ( const tab of tabs ) {
		tab.addEventListener( 'click', onClick );
	}
	
	/**
	 * Function to run on tab click.
	 * 
	 * @param {MouseEvent} event Click event
	 */
	function onClick( event ) {
		event.preventDefault();
		
		const currentTarget = event.currentTarget;
		const slug = currentTarget.getAttribute( 'data-slug' );
		
		setActiveTab( slug );
		
		// set url in browser
		history.pushState( null, null, currentTarget.href );
	}
	
	/**
	 * Set the active tab.
	 * 
	 * @param {string} activeTab Active tab identifier
	 */
	function setActiveTab( activeTab ) {
		for ( const thisTab of tabs ) {
			thisTab.classList.remove( 'nav-tab-active' );
		}
		
		for ( const tabContent of tabContents ) {
			tabContent.classList.remove( 'nav-tab-content-active' );
			const submitButtons = tabContent.querySelectorAll( '[type="submit"]' );
			
			if ( submitButtons.length ) {
				for ( const submitButton of submitButtons ) {
					if ( ! submitButton.name.endsWith( '--disabled' ) ) {
						submitButton.name += '--disabled';
					}
					
					if ( ! submitButton.id.endsWith( '--disabled' ) ) {
						submitButton.id += '--disabled';
					}
				}
			}
		}
		
		let activeTabElement = document.querySelector( '.nav-tab[data-slug="' + activeTab + '"]' );
		let activeTabContentElement = document.getElementById( 'nav-tab-content-' + activeTab );
		
		if ( ! activeTabElement ) {
			activeTabElement = tabs[0];
		}
		
		if ( ! activeTabContentElement ) {
			activeTabContentElement = tabContents[0];
		}
		
		activeTabElement.classList.add( 'nav-tab-active' );
		activeTabContentElement.classList.add( 'nav-tab-content-active' );
		
		const submitButtons = activeTabContentElement.querySelectorAll( '[type="submit"]' );
		
		if ( submitButtons.length ) {
			for ( const submitButton of submitButtons ) {
				if ( submitButton.name.endsWith( '--disabled' ) ) {
					submitButton.name = submitButton.name.replace( /--disabled$/, '' );
				}
				
				if ( submitButton.id.endsWith( '--disabled' ) ) {
					submitButton.id = submitButton.id.replace( /--disabled$/, '' );
				}
			}
		}
		
		updateReferer( activeTab );
	}
	
	/**
	 * Update the _wp_http_referer input depending on the active tab.
	 * 
	 * @param {string} tab Current active tab identifier
	 */
	function updateReferer( tab ) {
		const url = refererInput.value;
		
		if ( ! url.includes( '?' ) ) {
			return;
		}
		
		const params = url.split( '?' ).pop();
		const urlParams = new URLSearchParams( params );
		urlParams.set( tabParameter, tab );
		
		refererInput.value = refererInput.value.replace( params, urlParams.toString() );
	}
} );
