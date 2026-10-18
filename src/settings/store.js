import { useReducer } from '@wordpress/element';

/**
 * Get the initial values from the bootstrap payload.
 *
 * A suggested value is preselected, so that it is validated and saved like a
 * value that has been selected by hand.
 *
 * @param {Object} payload Settings payload
 * @return {Object} Values, keyed by group
 */
function getInitialValues( payload ) {
	const values = { ...( payload.values ?? {} ) };

	for ( const field of Object.values( payload.fields ?? {} ) ) {
		if (
			field.suggestedValue === null ||
			field.suggestedValue === undefined
		) {
			continue;
		}

		values[ field.group ] = {
			...( values[ field.group ] ?? {} ),
			[ field.name ]: field.suggestedValue,
		};
	}

	return values;
}

/**
 * Build the initial state from the bootstrap payload.
 *
 * @param {Object} payload Settings payload
 * @return {Object} Initial state
 */
export function getInitialState( payload ) {
	return {
		fieldData: payload.fieldData ?? {},
		isDirty: false,
		isSaving: false,
		isSubmitted: false,
		payload,
		serverErrors: {},
		touched: {},
		values: getInitialValues( payload ),
	};
}

function reducer( state, action ) {
	switch ( action.type ) {
		case 'SET_VALUE': {
			const serverErrors = { ...state.serverErrors };

			// an error the server reported is stale as soon as the value changes
			delete serverErrors[ `${ action.group }.${ action.name }` ];

			return {
				...state,
				isDirty: true,
				serverErrors,
				values: {
					...state.values,
					[ action.group ]: {
						...( state.values[ action.group ] ?? {} ),
						[ action.name ]: action.value,
					},
				},
			};
		}

		case 'SET_CUSTOM_TITLE':
			return {
				...state,
				fieldData: {
					...state.fieldData,
					[ action.name ]: { name: action.value },
				},
				isDirty: true,
			};

		case 'MARK_DIRTY':
			return state.isDirty ? state : { ...state, isDirty: true };

		case 'TOUCH':
			if ( state.touched[ action.key ] ) {
				return state;
			}

			return {
				...state,
				touched: { ...state.touched, [ action.key ]: true },
			};

		case 'SAVE_START':
			return {
				...state,
				isSaving: true,
				isSubmitted: true,
				serverErrors: {},
			};

		case 'SAVE_SUCCESS':
			// a write only reports back what it could change
			return {
				...state,
				fieldData: action.payload.fieldData ?? state.fieldData,
				isDirty: false,
				isSaving: false,
				payload: {
					...state.payload,
					defaults: action.payload.defaults ?? state.payload.defaults,
				},
				values: action.payload.values ?? state.values,
			};

		case 'SAVE_ERROR':
			return {
				...state,
				isSaving: false,
				serverErrors: action.serverErrors ?? {},
			};

		default:
			return state;
	}
}

/**
 * The state of the settings app.
 *
 * @param {Object} payload Settings payload
 * @return {Array} State and dispatch
 */
export function useSettingsState( payload ) {
	return useReducer( reducer, payload, getInitialState );
}

/**
 * Get the values that have to be submitted for a group.
 *
 * Every registered field is sent, including the empty ones: an omitted key
 * would be merged with the stored value server side and could never be
 * cleared – which is what an unchecked checkbox has to do.
 *
 * @param {Object[]} fields List of fields of the group
 * @param {Object}   values Current values of the group
 * @return {Object} Values to submit
 */
export function getSubmittableValues( fields, values ) {
	const submittable = {};

	for ( const field of fields ) {
		submittable[ field.name ] = values[ field.name ] ?? '';
	}

	return submittable;
}
