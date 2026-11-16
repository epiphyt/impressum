/**
 * @jest-environment jsdom
 */
import { addSubmittedOptions, getLegacyValue, getOptionNames } from '../form';

function createForm( html ) {
	const form = document.createElement( 'form' );

	form.innerHTML = html;

	return form;
}

describe( 'getLegacyValue', () => {
	it( 'reads a single value', () => {
		const form = createForm(
			'<input name="group[key]" value="current"><input name="group[other]" value="x">'
		);

		expect( getLegacyValue( form, 'group[key]' ) ).toBe( 'current' );
	} );

	it( 'clears an unchecked checkbox', () => {
		const form = createForm(
			'<input type="checkbox" name="group[key]" value="1">'
		);

		expect( getLegacyValue( form, 'group[key]' ) ).toBe( '' );
	} );

	it( 'reads a list', () => {
		const form = createForm(
			'<input type="checkbox" name="group[key][]" value="a" checked>' +
				'<input type="checkbox" name="group[key][]" value="b">' +
				'<input type="checkbox" name="group[key][]" value="c" checked>'
		);

		expect( getLegacyValue( form, 'group[key]' ) ).toEqual( [ 'a', 'c' ] );
	} );

	it( 'reads keyed values', () => {
		const form = createForm(
			'<input name="group[key][first]" value="1"><input name="group[key][second]" value="2">'
		);

		expect( getLegacyValue( form, 'group[key]' ) ).toEqual( {
			first: '1',
			second: '2',
		} );
	} );

	it( 'is undefined without a control of the field', () => {
		const form = createForm( '<input name="group[other]" value="x">' );

		expect( getLegacyValue( form, 'group[key]' ) ).toBeUndefined();
	} );
} );

describe( 'submitted options', () => {
	it( 'lists every option the form contains controls of', () => {
		const form = createForm(
			'<input name="option_page" value="impressum_imprint">' +
				'<input type="checkbox" name="first_options[a]" value="1">' +
				'<input name="second_options[b][]" value="x">' +
				'<input name="first_options[c]" value="y">'
		);

		expect( getOptionNames( form ) ).toEqual( [
			'first_options',
			'second_options',
		] );
	} );

	it( 'adds the list to the container and replaces a previous one', () => {
		const form = createForm(
			'<div id="container"></div><input type="checkbox" name="first_options[a]" value="1">'
		);
		const container = form.querySelector( '#container' );

		addSubmittedOptions( form, container );
		addSubmittedOptions( form, container );

		const inputs = container.querySelectorAll(
			'input[name="impressum_submitted_options[]"]'
		);

		expect( inputs ).toHaveLength( 1 );
		expect( inputs[ 0 ].value ).toBe( 'first_options' );
	} );
} );
