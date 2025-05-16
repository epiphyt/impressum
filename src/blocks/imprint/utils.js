/**
 * Inserts HTML line breaks before all newlines in a string.
 * 
 * @param	{String}	value The input string
 * @return	{Fragment|null} A React fragment or null
 */
const nl2br = ( value ) => {
	if ( ! value.length ) return null;
	
	return value.trim().split( '\n' ).map( ( item, key ) => {
		return (
			<React.Fragment key={ key }>
				{ item }
				{ key + 1 < value.trim().split( '\n' ).length && <br /> }
			</React.Fragment>
		);
	} );
}

/* global impressum_fields */
export default function getFields( enabledFields, className ) {
	return Object.keys( impressum_fields.values ).map( ( key ) => {
		if ( ! impressum_fields.values[ key ]['value'].length ) return false;
		if (
			key === 'country'
			|| key === 'legal_entity'
			|| key === 'page'
			|| key === 'press_law_checkbox'
		 ) return false;
		if ( enabledFields.length && enabledFields.indexOf( key ) === -1 ) return false;
		
		let value = '';
		
		if ( key.includes( 'contact_form_page' ) ) {
			value = <dd><a href={ impressum_fields.values[ key ].value }>{ __( 'To the contact form', 'impressum-plus' ) }</a></dd>;
		}
		else if ( key.includes( 'email' ) ) {
			value = <a href={ 'mailto:' + impressum_fields['values'][ key ]['value'] }>{ impressum_fields['values'][ key ]['value'] }</a>;
		}
		else if ( key.includes( 'social_media' ) ) {
			value = <a href={ impressum_fields.values[ key ]['value'] }>{ impressum_fields.values[ key ]['value'] }</a>;
		}
		else if ( key === 'data_protection_officer_name' ) {
			return ( <div key={ key }>
				{ ! className.includes( 'is-style-no-title' ) && <dl>
					<dt>{ impressum_fields.values[ key ]['field_title'] || impressum_fields.values[ key ]['title'] }</dt>
					<dd>
						{ impressum_fields.values[ key ]['value'] }
					</dd>
				</dl> }
				{ className.includes( 'is-style-no-title' ) && <p key={ key }>{ impressum_fields.values[ key ][ 'value' ] }</p> }
			</div>);
		}
		
		return ( <div key={ key }>
			{ ! className.includes( 'is-style-no-title' ) && <dl>
				<dt>{ impressum_fields.values[ key ]['field_title'] || impressum_fields.values[ key ]['title'] }</dt>
				{ key !== 'free_text'
					? <dd>{ value || nl2br( impressum_fields[ 'values' ][ key ][ 'value' ] ) }</dd>
					: <dd dangerouslySetInnerHTML={ { __html: ( value || impressum_fields[ 'values' ][ key ][ 'value' ] ).replace(/(?:\r\n|\r|\n)/g, '<br />') } } />
				}
			</dl> }
			{ className.includes( 'is-style-no-title' ) && <p>{ value || nl2br( impressum_fields.values[ key ]['value'] ) }</p> }
		</div> );
	} );
}
