/* eslint-disable camelcase */
import { Fragment } from 'react'; // eslint-disable-line import/no-extraneous-dependencies
import deprecated from '@wordpress/deprecated';
import { __ } from '@wordpress/i18n';

/**
 * Inserts HTML line breaks before all newlines in a string.
 *
 * @param {string} value The input string
 * @return	{Fragment|null} A React fragment or null
 */
const nl2br = ( value ) => {
	if ( ! value.length ) {
		return null;
	}

	return value
		.trim()
		.split( '\n' )
		.map( ( item, key ) => {
			return (
				<Fragment key={ key }>
					{ item }
					{ key + 1 < value.trim().split( '\n' ).length && <br /> }
				</Fragment>
			);
		} );
};

/* global impressum_fields */
export default function getFields( enabledFields, className ) {
	deprecated( 'getFields', {
		since: '2.5',
		alternative: 'getFieldsByName',
		version: '3.0',
	} );

	return Object.keys( impressum_fields.values ).map( ( key ) => {
		if ( ! impressum_fields.values[ key ].value.length ) {
			return false;
		}

		if ( impressum_fields.fields[ key ].no_output ) {
			return false;
		}

		if ( enabledFields.length && enabledFields.indexOf( key ) === -1 ) {
			return false;
		}

		let value = '';

		if ( key.includes( 'email' ) ) {
			value = (
				<a href={ 'mailto:' + impressum_fields.values[ key ].value }>
					{ impressum_fields.values[ key ].value }
				</a>
			);
		}

		return (
			<div key={ key }>
				{ ! className.includes( 'is-style-no-title' ) && (
					<dl>
						<dt>
							{ impressum_fields.values[ key ].custom_title ||
								impressum_fields.values[ key ].field_title ||
								impressum_fields.values[ key ].title }
						</dt>
						{ key !== 'free_text' ? (
							<dd>
								{ value ||
									nl2br(
										impressum_fields.values[ key ].value
									) }
							</dd>
						) : (
							<dd
								dangerouslySetInnerHTML={ {
									__html: (
										value ||
										impressum_fields.values[ key ].value
									).replace( /(?:\r\n|\r|\n)/g, '<br />' ),
								} }
							/>
						) }
					</dl>
				) }
				{ className.includes( 'is-style-no-title' ) && (
					<p>
						{ value ||
							nl2br( impressum_fields.values[ key ].value ) }
					</p>
				) }
			</div>
		);
	} );
}

// eslint-disable-next-line no-unused-vars
export function getFieldsByName( enabledFields, className, setAttributes ) {
	const displayedFields = enabledFields;

	if ( ! displayedFields.length ) {
		return [];
	}

	return displayedFields
		.map( ( key ) => {
			let value = '';

			if (
				! key ||
				! impressum_fields.values[ key ] ||
				impressum_fields.values[ key ].no_output ||
				! impressum_fields.values[ key ].value
			) {
				return '';
			}

			if ( key.includes( 'email' ) ) {
				value = (
					<a
						href={
							'mailto:' + impressum_fields.values[ key ].value
						}
					>
						{ impressum_fields.values[ key ].value }
					</a>
				);
			}

			let fieldValue = '';

			switch ( key ) {
				case 'contact_form_page':
					fieldValue = (
						<dd>
							<a href={ impressum_fields.values[ key ].value }>
								{ __( 'To the contact form', 'impressum' ) }
							</a>
						</dd>
					);
					break;
				case 'free_text':
					fieldValue = (
						<dd
							dangerouslySetInnerHTML={ {
								__html: (
									value ||
									impressum_fields.values[ key ].value
								).replace( /(?:\r\n|\r|\n)/g, '<br />' ),
							} }
						/>
					);
					break;
				default:
					fieldValue = (
						<dd>
							{ value ||
								nl2br( impressum_fields.values[ key ].value ) }
						</dd>
					);
					break;
			}

			return (
				<div key={ key }>
					{ ! className.includes( 'is-style-no-title' ) && (
						<dl>
							<dt>
								{ impressum_fields.values[ key ].custom_title ||
									impressum_fields.values[ key ]
										.field_title ||
									impressum_fields.values[ key ].title }
							</dt>
							{ fieldValue }
						</dl>
					) }
					{ className.includes( 'is-style-no-title' ) && (
						<p>
							{ value ||
								nl2br( impressum_fields.values[ key ].value ) }
						</p>
					) }
				</div>
			);
		} )
		.filter( Boolean );
}
