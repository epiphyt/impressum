/**
 * A field rendered on the server.
 *
 * Field types the settings app does not know – most of them registered by
 * extensions through impressum_admin_fields_callback_instance – are rendered by
 * their PHP callback and injected here. Their inputs carry the real option
 * names, so their current values are read from the form while saving.
 *
 * @param {Object}    props         Component properties
 * @param {Object}    props.field   Field to render
 * @param {?Function} props.onInput Handler called when a value of the markup changes
 */
export default function LegacyField( { field, onInput } ) {
	if ( ! field.legacyHtml ) {
		return null;
	}

	return (
		<div
			className="impressum-settings__legacy-field"
			dangerouslySetInnerHTML={ { __html: field.legacyHtml } }
			onInput={ onInput }
		/>
	);
}
