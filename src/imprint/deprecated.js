/* eslint-disable camelcase */
/* global impressumImprintBlock */
const v1 = {
	attributes: {
		enabledFields: {
			default: [],
			type: 'array',
		},
	},
	migrate: ( { enabledFields } ) => {
		// merge titles to names
		const fields = Object.keys( impressumImprintBlock.fields );
		const newFields = [];

		for ( const fieldName of fields ) {
			for ( const enabledField of enabledFields ) {
				if (
					impressumImprintBlock.fields[ fieldName ].data.title ===
					enabledField
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
