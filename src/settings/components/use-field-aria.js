import { useEffect, useRef } from '@wordpress/element';

const CONTROLS = 'input:not([type="hidden"]), select, textarea';

/**
 * Split a list of identifiers.
 *
 * @param {?string} list Space separated identifiers
 * @return {string[]} List of identifiers
 */
function split( list ) {
	return ( list ?? '' ).split( ' ' ).filter( Boolean );
}

/**
 * Set or remove an attribute that is only managed by this association.
 *
 * @param {HTMLElement} control Control to update
 * @param {string}      name    Attribute name
 * @param {boolean}     isSet   Whether the attribute should be set
 */
function toggleOwnAttribute( control, name, isSet ) {
	const marker = `data-impressum-${ name }`;

	if ( isSet ) {
		control.setAttribute( name, 'true' );
		control.setAttribute( marker, '' );

		return;
	}

	// only what this association set is removed again
	if ( control.hasAttribute( marker ) ) {
		control.removeAttribute( name );
		control.removeAttribute( marker );
	}
}

/**
 * Associate every control of a field with its descriptions and state.
 *
 * The association is applied to the rendered controls rather than passed to
 * them as a property: not every control passes an unknown property on to its
 * input, and the ones that do not would drop it without a trace. Markup coming
 * from the server cannot take a property at all.
 *
 * Identifiers a control describes itself with are kept.
 *
 * @param {Object}   args             Association arguments
 * @param {string[]} args.describedBy Identifiers of the elements describing the field
 * @param {boolean}  args.invalid     Whether the field is invalid
 * @param {boolean}  args.required    Whether the field is required
 * @return {Object} Ref of the container holding the controls
 */
export default function useFieldAria( {
	describedBy = [],
	invalid = false,
	required = false,
} ) {
	const ref = useRef( null );
	const ids = describedBy.filter( Boolean ).join( ' ' );

	useEffect( () => {
		const controls = ref.current?.querySelectorAll( CONTROLS ) ?? [];

		for ( const control of controls ) {
			const own = split(
				control.getAttribute( 'data-impressum-describedby' )
			);
			const foreign = split(
				control.getAttribute( 'aria-describedby' )
			).filter( ( id ) => ! own.includes( id ) );
			const next = [ ...split( ids ), ...foreign ];

			if ( next.length ) {
				control.setAttribute( 'aria-describedby', next.join( ' ' ) );
			} else {
				control.removeAttribute( 'aria-describedby' );
			}

			if ( ids ) {
				control.setAttribute( 'data-impressum-describedby', ids );
			} else {
				control.removeAttribute( 'data-impressum-describedby' );
			}

			toggleOwnAttribute( control, 'aria-invalid', invalid );
			toggleOwnAttribute(
				control,
				'aria-required',
				required &&
					control.type !== 'checkbox' &&
					control.type !== 'radio'
			);
		}
	}, [ ids, invalid, required ] );

	return ref;
}
