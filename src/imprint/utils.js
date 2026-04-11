/* global impressumImprintBlock */
import { Fragment } from '@wordpress/element';
import { __ } from '@wordpress/i18n';

import { MoverDownButton, MoverUpButton } from './mover';

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

export function getFieldsByName( enabledFields, className, setAttributes ) {
	const displayedFields = enabledFields;

	if ( ! displayedFields.length ) {
		return [];
	}

	const fieldsWithOutput = Object.keys( impressumImprintBlock.values )
		.map( ( key ) => {
			const item = impressumImprintBlock.values[ key ];

			if ( item.hide_output || ! item.value ) {
				return null;
			}

			if ( displayedFields.indexOf( key ) === -1 ) {
				return null;
			}

			return key;
		} )
		.filter( Boolean )
		.sort(
			( a, b ) => enabledFields.indexOf( a ) - enabledFields.indexOf( b )
		);
	let displayedIndex = -1;

	return displayedFields
		.map( ( key ) => {
			let value = '';

			if (
				! key ||
				! impressumImprintBlock.values[ key ] ||
				impressumImprintBlock.values[ key ].hide_output ||
				! impressumImprintBlock.values[ key ].value
			) {
				return '';
			}

			if ( key.includes( 'email' ) ) {
				value = (
					<a
						href={
							'mailto:' +
							impressumImprintBlock.values[ key ].value
						}
					>
						{ impressumImprintBlock.values[ key ].value }
					</a>
				);
			}

			if ( key.includes( 'social_media' ) ) {
				value = (
					<a href={ impressumImprintBlock.values[ key ].value }>
						{ impressumImprintBlock.values[ key ].value }
					</a>
				);
			}

			displayedIndex++;

			if ( key === 'data_protection_officer_name' ) {
				return (
					<div key={ key }>
						{ ! className.includes( 'is-style-no-title' ) && (
							<dl>
								<dt>
									{ impressumImprintBlock.values[ key ]
										.custom_title ||
										impressumImprintBlock.values[ key ]
											.field_title ||
										impressumImprintBlock.values[ key ]
											.title }
									<MoverUpButton
										availableFields={ fieldsWithOutput }
										isDisabled={ displayedIndex === 0 }
										field={ key }
										fields={ enabledFields }
										setAttributes={ setAttributes }
									/>
									<MoverDownButton
										availableFields={ fieldsWithOutput }
										isDisabled={
											displayedIndex ===
											fieldsWithOutput.length - 1
										}
										field={ key }
										fields={ enabledFields }
										setAttributes={ setAttributes }
									/>
								</dt>
								<dd>
									{
										impressumImprintBlock.values[ key ]
											.value
									}
									{ impressumImprintBlock.values
										.data_protection_officer_address
										.value && <br /> }
									{ impressumImprintBlock.values
										.data_protection_officer_address
										.value &&
										nl2br(
											impressumImprintBlock.values
												.data_protection_officer_address
												.value
										) }
									{ impressumImprintBlock.values
										.data_protection_officer_email
										.value && <br /> }
									{ impressumImprintBlock.values
										.data_protection_officer_email
										.value && (
										<a
											href={
												'mailto:' +
												impressumImprintBlock.values
													.data_protection_officer_email
													.value
											}
										>
											{
												impressumImprintBlock.values
													.data_protection_officer_email
													.value
											}
										</a>
									) }
									{ impressumImprintBlock.values
										.data_protection_officer_phone
										.value && <br /> }
									{ impressumImprintBlock.values
										.data_protection_officer_phone.value &&
										impressumImprintBlock.values
											.data_protection_officer_phone
											.value }
								</dd>
							</dl>
						) }
						{ className.includes( 'is-style-no-title' ) && (
							<p key={ key }>
								{ impressumImprintBlock.value.ke.value }
								{ impressumImprintBlock.values
									.data_protection_officer_address.value && (
									<br />
								) }
								{ impressumImprintBlock.values
									.data_protection_officer_address.value &&
									nl2br(
										impressumImprintBlock.values
											.data_protection_officer_address
											.value
									) }
								{ impressumImprintBlock.values
									.data_protection_officer_email.value && (
									<br />
								) }
								{ impressumImprintBlock.values
									.data_protection_officer_email.value && (
									<a
										href={
											'mailto:' +
											impressumImprintBlock.values
												.data_protection_officer_email
												.value
										}
									>
										{
											impressumImprintBlock.values
												.data_protection_officer_email
												.value
										}
									</a>
								) }
								{ impressumImprintBlock.values
									.data_protection_officer_phone.value && (
									<br />
								) }
								{ impressumImprintBlock.values
									.data_protection_officer_phone.value &&
									impressumImprintBlock.values
										.data_protection_officer_phone.value }
							</p>
						) }
					</div>
				);
			}

			let fieldValue = '';

			switch ( key ) {
				case 'contact_form_page':
					fieldValue = (
						<dd>
							<a
								href={
									impressumImprintBlock.values[ key ].value
								}
							>
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
									impressumImprintBlock.values[ key ].value
								).replace( /(?:\r\n|\r|\n)/g, '<br />' ),
							} }
						/>
					);
					break;
				default:
					fieldValue = (
						<dd>
							{ value ||
								nl2br(
									impressumImprintBlock.values[ key ].value
								) }
						</dd>
					);
					break;
			}

			return (
				<div key={ key }>
					{ ! className.includes( 'is-style-no-title' ) && (
						<dl>
							<dt>
								{ impressumImprintBlock.values[ key ]
									.custom_title ||
									impressumImprintBlock.values[ key ]
										.field_title ||
									impressumImprintBlock.values[ key ].title }
								<MoverUpButton
									availableFields={ fieldsWithOutput }
									isDisabled={ displayedIndex === 0 }
									field={ key }
									fields={ enabledFields }
									setAttributes={ setAttributes }
								/>
								<MoverDownButton
									availableFields={ fieldsWithOutput }
									isDisabled={
										displayedIndex ===
										fieldsWithOutput.length - 1
									}
									field={ key }
									fields={ enabledFields }
									setAttributes={ setAttributes }
								/>
							</dt>
							{ fieldValue }
						</dl>
					) }
					{ className.includes( 'is-style-no-title' ) && (
						<p>
							{ value ||
								nl2br(
									impressumImprintBlock.values[ key ].value
								) }
						</p>
					) }
				</div>
			);
		} )
		.filter( Boolean );
}
