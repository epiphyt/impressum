/* eslint-disable camelcase */
/* global impressum_fields */
const v1 = {
	attributes: {
		enabledFields: {
			default: [],
			type: 'array',
		},
	},
	migrate: ( { enabledFields } ) => {
		// merge titles to names
		const fields = Object.keys( impressum_fields.fields );
		const newFields = [];

		for ( const fieldName of fields ) {
			for ( const enabledField of enabledFields ) {
				if (
					impressum_fields.fields[ fieldName ].title === enabledField
				) {
					newFields.push( fieldName );
				}
			}
		}

		// previously, an empty array defaulted to all fields being showed
		if ( newFields.length === 0 ) {
			newFields.push( 'all' );
		}

		return { enabledFields: newFields };
	},
	isEligible: () => true,
	save: () => null,
	supports: {
		html: false,
	},
};

export default [ v1 ];
