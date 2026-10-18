/**
 * @jest-environment jsdom
 */
import { render } from '@testing-library/react';
import { addFilter, removeFilter } from '@wordpress/hooks';
import TabContent from '../tab-content';

describe( 'tab content', () => {
	afterEach( () => {
		removeFilter( 'impressum.settings.tabContents', 'test' );
	} );

	it( 'renders the text of the imprint tab', () => {
		const { container } = render(
			<TabContent tab={ { slug: 'imprint' } } />
		);

		expect( container.querySelectorAll( 'h2' ) ).toHaveLength( 2 );
		expect( container.querySelector( 'code' ).textContent ).toBe(
			'[impressum]'
		);
	} );

	it( 'renders nothing for a tab without text', () => {
		const { container } = render(
			<TabContent tab={ { slug: 'privacy' } } />
		);

		expect( container.innerHTML ).toBe( '' );
	} );

	it( 'renders text registered through the filter', () => {
		addFilter(
			'impressum.settings.tabContents',
			'test',
			( components ) => ( {
				...components,
				privacy: ( { tab } ) => <p>{ `Text of ${ tab.slug }` }</p>,
			} )
		);

		const { container } = render(
			<TabContent tab={ { slug: 'privacy' } } />
		);

		expect( container.querySelector( 'p' ).textContent ).toBe(
			'Text of privacy'
		);
	} );
} );
