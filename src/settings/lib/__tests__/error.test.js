import { getErrorMessage, getFieldErrors } from '../error';

// exactly what the REST API answers when a page that does not exist is submitted
const ENUM_ERROR = {
	code: 'rest_invalid_param',
	message: 'Ungültige(r) Parameter: values',
	data: {
		status: 400,
		params: {
			values: 'values[impressum_imprint_options][page] ist nicht eins von , 2, 5, 2 und 5.',
		},
		details: {
			values: {
				code: 'rest_not_in_enum',
				message:
					'values[impressum_imprint_options][page] ist nicht eins von , 2, 5, 2 und 5.',
				data: null,
			},
		},
	},
};

describe( 'getFieldErrors', () => {
	it( 'finds the setting even though the details carry no parameter', () => {
		expect( getFieldErrors( ENUM_ERROR ) ).toEqual( [
			{
				group: 'impressum_imprint_options',
				message: ENUM_ERROR.data.details.values.message,
				name: 'page',
			},
		] );
	} );

	it( 'reads the parameter when it is reported', () => {
		const error = {
			data: {
				details: {
					values: {
						message: 'Something is wrong.',
						data: {
							param: 'values[impressum_imprint_options][country]',
						},
					},
				},
			},
		};

		expect( getFieldErrors( error ) ).toEqual( [
			{
				group: 'impressum_imprint_options',
				message: 'Something is wrong.',
				name: 'country',
			},
		] );
	} );

	it( 'falls back to the parameter map when there are no details', () => {
		const error = {
			data: {
				params: {
					values: 'values[impressum_imprint_options][vat_id] is invalid.',
				},
			},
		};

		expect( getFieldErrors( error ) ).toEqual( [
			{
				group: 'impressum_imprint_options',
				message: 'values[impressum_imprint_options][vat_id] is invalid.',
				name: 'vat_id',
			},
		] );
	} );

	it( 'collects nested errors', () => {
		const error = {
			data: {
				details: {
					values: {
						message: 'values[g][a] is invalid.',
						additional_errors: [
							{ message: 'values[g][b] is invalid.' },
						],
					},
				},
			},
		};

		expect( getFieldErrors( error ).map( ( one ) => one.name ) ).toEqual( [
			'a',
			'b',
		] );
	} );

	it( 'returns nothing for an error that belongs to no setting', () => {
		expect(
			getFieldErrors( { code: 'rest_forbidden', message: 'Nope.' } )
		).toEqual( [] );
	} );
} );

describe( 'getErrorMessage', () => {
	it( 'prefers the detailed message over the generic one', () => {
		expect( getErrorMessage( ENUM_ERROR ) ).toBe(
			ENUM_ERROR.data.details.values.message
		);
	} );

	it( 'falls back to the generic message', () => {
		expect(
			getErrorMessage( { code: 'rest_forbidden', message: 'Nope.' } )
		).toBe( 'Nope.' );
	} );

	it( 'always returns something', () => {
		expect( getErrorMessage( undefined ) ).toBeTruthy();
	} );
} );

describe( 'getFieldErrors with reported settings', () => {
	const FIELD_ERROR = {
		code: 'impressum_invalid_settings',
		message: '1 setting is invalid.',
		data: {
			status: 400,
			fields: {
				'impressum_imprint_options.page': {
					code: 'impressum_invalid_page',
					group: 'impressum_imprint_options',
					message: 'Select a page from the list.',
					name: 'page',
				},
			},
		},
	};

	it( 'reads them without parsing a parameter path', () => {
		expect( getFieldErrors( FIELD_ERROR ) ).toEqual( [
			{
				group: 'impressum_imprint_options',
				message: 'Select a page from the list.',
				name: 'page',
			},
		] );
	} );

	it( 'carries no parameter path in the message', () => {
		const [ first ] = getFieldErrors( FIELD_ERROR );

		expect( first.message ).not.toMatch( /values\[/ );
	} );
} );
