/**
 * A notice that is part of the content.
 *
 * Unlike the Notice component it is not announced to screen readers when it
 * appears, which would announce every notice whenever a value changes. Notices
 * belonging to a field are announced with the field instead, since they are
 * associated with its controls.
 *
 * @param {Object}  props           Component properties
 * @param {*}       props.children  Notice content
 * @param {string}  props.className Additional class name
 * @param {?string} props.id        Identifier of the notice
 * @param {string}  props.status    Status of the notice
 */
export default function InlineNotice( {
	children,
	className = '',
	id = null,
	status = 'info',
} ) {
	return (
		<div
			className={ `components-notice is-${ status } ${ className }`.trim() }
			id={ id ?? undefined }
		>
			<div className="components-notice__content">{ children }</div>
		</div>
	);
}
