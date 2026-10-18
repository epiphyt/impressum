/**
 * @jest-environment jsdom
 */
import { render } from '@testing-library/react';
import FieldRow from '../field-row';

const base = {
	choices: {},
	i18n: { requiredNotice: 'Required.', selectPlaceholder: '— Select —', selectPlaceholderShort: '- Select -' },
	isNetwork: false,
	onChange: () => {},
	onCustomTitleChange: () => {},
	values: {},
};

function field( overrides ) {
	return {
		conditions: {},
		control: null,
		cssClass: '',
		description: '',
		descriptionHtml: '',
		group: 'impressum_imprint_options',
		key: 'k',
		label: '',
		legacyHtml: null,
		name: 'name',
		notices: [],
		options: null,
		placeholder: '',
		rows: null,
		supportsCustomTitle: false,
		title: 'Name',
		type: 'text',
		validation: {},
		...overrides,
	};
}

function renderRow( props ) {
	return render(
		<table>
			<tbody>
				<FieldRow { ...base } { ...props } />
			</tbody>
		</table>
	);
}

describe( 'the error message is associated with its input', () => {
	it.each( [
		[ 'text', {} ],
		[ 'textarea', { type: 'textarea' } ],
		[ 'email', { type: 'email' } ],
		[ 'checkbox', { type: 'checkbox' } ],
		[ 'select', { type: 'select', options: [ { label: 'A', value: 'a' } ] } ],
		[ 'descriptor', { control: { type: 'radio', options: [ { label: 'A', value: 'a' } ] } } ],
	] )( '%s', ( _name, overrides ) => {
		const { container } = renderRow( {
			error: 'Something is wrong.',
			field: field( overrides ),
			value: '',
		} );

		const message = container.querySelector( '.impressum-settings__error' );

		expect( message ).not.toBeNull();
		expect( message.id ).toBeTruthy();

		const controls = container.querySelectorAll( 'input, select, textarea' );

		expect( controls.length ).toBeGreaterThan( 0 );

		for ( const control of controls ) {
			expect( control.getAttribute( 'aria-describedby' ) ).toBe( message.id );
			expect( control.getAttribute( 'aria-invalid' ) ).toBe( 'true' );
		}
	} );

	it( 'server rendered markup', () => {
		const { container } = renderRow( {
			error: 'Something is wrong.',
			field: field( {
				legacyHtml: '<input type="text" name="legacy" id="legacy">',
				type: 'license_key',
			} ),
			value: '',
		} );

		const message = container.querySelector( '.impressum-settings__error' );
		const control = container.querySelector( '#legacy' );

		expect( control.getAttribute( 'aria-describedby' ) ).toBe( message.id );
		expect( control.getAttribute( 'aria-invalid' ) ).toBe( 'true' );
	} );

	it( 'is absent without an error', () => {
		const { container } = renderRow( { error: null, field: field(), value: '' } );

		expect( container.querySelector( '.impressum-settings__error' ) ).toBeNull();
		expect(
			container.querySelector( 'input' ).getAttribute( 'aria-describedby' )
		).toBeNull();
	} );

	it( 'is removed again once the error is resolved', () => {
		const props = { field: field(), value: '' };
		const { container, rerender } = renderRow( { ...props, error: 'Wrong.' } );

		rerender(
			<table>
				<tbody>
					<FieldRow { ...base } { ...props } error={ null } />
				</tbody>
			</table>
		);

		const input = container.querySelector( 'input' );

		expect( input.getAttribute( 'aria-describedby' ) ).toBeNull();
		expect( input.getAttribute( 'aria-invalid' ) ).toBeNull();
	} );
} );

describe( 'the field is described', () => {
	it( 'by its required notice and description', () => {
		const { container } = renderRow( {
			error: null,
			field: field( { description: 'Your full name.' } ),
			required: true,
			value: '',
		} );
		const input = container.querySelector( 'input' );
		const ids = input.getAttribute( 'aria-describedby' ).split( ' ' );

		expect( ids ).toHaveLength( 2 );

		for ( const id of ids ) {
			expect( container.querySelector( `#${ id }` ) ).not.toBeNull();
		}

		expect( input.getAttribute( 'aria-required' ) ).toBe( 'true' );
	} );

	it( 'without dropping a description of the markup itself', () => {
		const { container } = renderRow( {
			error: 'Something is wrong.',
			field: field( {
				legacyHtml:
					'<input type="text" id="legacy" aria-describedby="own-help"><p id="own-help">Help</p>',
				type: 'license_key',
			} ),
			value: '',
		} );
		const message = container.querySelector( '.impressum-settings__error' );

		expect(
			container.querySelector( '#legacy' ).getAttribute( 'aria-describedby' )
		).toBe( `${ message.id } own-help` );
	} );

	it( 'by its notices', () => {
		const { container } = renderRow( {
			error: null,
			field: field(),
			notices: [ { id: 'hint', status: 'warning', text: 'A hint.' } ],
			value: '',
		} );
		const id = container
			.querySelector( 'input' )
			.getAttribute( 'aria-describedby' );

		expect( container.querySelector( `#${ id }` ).textContent ).toBe(
			'A hint.'
		);
	} );
} );

describe( 'the field is labeled', () => {
	it( 'by its title', () => {
		const { getByLabelText } = renderRow( {
			error: null,
			field: field(),
			value: '',
		} );

		expect( getByLabelText( 'Name' ).tagName ).toBe( 'INPUT' );
	} );

	it( 'by its title inside a radio group', () => {
		const { getByRole } = renderRow( {
			error: null,
			field: field( {
				control: {
					options: [ { label: 'A', value: 'a' } ],
					type: 'radio',
				},
			} ),
			value: '',
		} );

		expect( getByRole( 'group', { name: 'Name' } ) ).not.toBeNull();
	} );

	it( 'by its title in server rendered markup', () => {
		const { getByLabelText } = renderRow( {
			error: null,
			field: field( {
				labelFor: 'legacy',
				legacyHtml: '<input type="text" id="legacy">',
				type: 'license_key',
			} ),
			value: '',
		} );

		expect( getByLabelText( 'Name' ).id ).toBe( 'legacy' );
	} );

	it( 'with a title edit button naming the field', () => {
		const { getByRole } = renderRow( {
			customTitle: 'Owner',
			error: null,
			field: field( { supportsCustomTitle: true } ),
			value: '',
		} );

		expect(
			getByRole( 'button', { name: /Owner/ } )
		).not.toBeNull();
	} );
} );
