import { getEffectiveValues, getErrors } from '../validate';

describe( 'getEffectiveValues', () => {
	it( 'falls back to network wide values for empty values', () => {
		expect(
			getEffectiveValues(
				{ email: 'site@example.com', name: '' },
				{ email: 'network@example.com', name: 'Network' }
			)
		).toEqual( { email: 'site@example.com', name: 'Network' } );
	} );

	it( 'keeps empty values without network wide value', () => {
		expect( getEffectiveValues( { name: '' }, {} ) ).toEqual( {
			name: '',
		} );
	} );

	it( 'handles missing values and defaults', () => {
		expect( getEffectiveValues( undefined, undefined ) ).toEqual( {} );
		expect( getEffectiveValues( { name: 'Site' } ) ).toEqual( {
			name: 'Site',
		} );
	} );
} );

describe( 'getErrors', () => {
	const fields = [
		{
			name: 'name',
			validation: {
				messages: { required: 'Name is required.' },
				required: true,
			},
		},
	];

	it( 'reports an empty required value', () => {
		expect( getErrors( fields, { name: '' } ) ).toEqual( {
			name: 'Name is required.',
		} );
	} );

	it( 'accepts a required value set network wide', () => {
		expect(
			getErrors(
				fields,
				getEffectiveValues( { name: '' }, { name: 'Network' } )
			)
		).toEqual( {} );
	} );
} );
