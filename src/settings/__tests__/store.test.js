import { getInitialState } from '../store';

const COUNTRY = {
	group: 'impressum_imprint_options',
	name: 'country',
	suggestedValue: 'deu',
};

describe( 'getInitialState', () => {
	it( 'preselects the suggested value of an empty field', () => {
		const state = getInitialState( {
			fields: { impressum_imprint_options_country: COUNTRY },
			// an empty option is encoded as a list by PHP
			values: { impressum_imprint_options: [] },
		} );

		expect( state.values.impressum_imprint_options ).toEqual( {
			country: 'deu',
		} );
		expect( state.isDirty ).toBe( false );
	} );

	it( 'keeps the stored values of every other field', () => {
		const state = getInitialState( {
			fields: {
				impressum_imprint_options_country: COUNTRY,
				impressum_imprint_options_name: {
					group: 'impressum_imprint_options',
					name: 'name',
					suggestedValue: null,
				},
			},
			values: { impressum_imprint_options: { name: 'Jane Doe' } },
		} );

		expect( state.values.impressum_imprint_options ).toEqual( {
			country: 'deu',
			name: 'Jane Doe',
		} );
	} );
} );
