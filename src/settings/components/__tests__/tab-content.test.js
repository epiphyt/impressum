/**
 * @jest-environment jsdom
 */
import { render } from '@testing-library/react';
import { addFilter, removeFilter } from '@wordpress/hooks';
import TabContent, { TabIntro } from '../tab-content';
import SettingsTab from '../tab-panel';

describe( 'tab content', () => {
	afterEach( () => {
		removeFilter( 'impressum.settings.tabContents', 'test' );
		removeFilter( 'impressum.settings.tabIntros', 'test' );
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

	it( 'renders no introduction for a tab without one', () => {
		const { container } = render(
			<TabIntro tab={ { slug: 'imprint' } } />
		);

		expect( container.innerHTML ).toBe( '' );
	} );

	it( 'renders the introduction above the fields', () => {
		addFilter( 'impressum.settings.tabIntros', 'test', ( components ) => ( {
			...components,
			privacy: ( { tab } ) => <p>{ `Intro of ${ tab.slug }` }</p>,
		} ) );

		const { container } = render(
			<SettingsTab
				errors={ {} }
				fieldData={ {} }
				fields={ [
					{
						group: 'impressum_privacy_options',
						key: 'impressum_privacy_options_name',
						name: 'name',
						title: 'Name',
						type: 'text',
					},
				] }
				payload={ { choices: {}, i18n: {} } }
				serverErrors={ {} }
				tab={ { slug: 'privacy' } }
				touched={ {} }
				values={ {} }
			/>
		);
		const intro = container.querySelector( 'p' );

		expect( intro.textContent ).toBe( 'Intro of privacy' );
		expect(
			intro.compareDocumentPosition( container.querySelector( 'input' ) )
		).toBe( intro.DOCUMENT_POSITION_FOLLOWING );
	} );
} );
