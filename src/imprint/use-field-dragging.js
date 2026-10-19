import { useRefEffect } from '@wordpress/compose';
import { useRef, useState } from '@wordpress/element';

import { moveField } from './mover';

const DATA_TYPE = 'impressum-field';

/**
 * Get the closest element of an event target.
 *
 * Safari may use text nodes as target of drag events.
 *
 * @param {EventTarget|null} target Event target
 * @return {HTMLElement|null} Element
 */
const getElement = ( target ) =>
	target?.nodeType === 3 ? target.parentElement : target;

/**
 * Get the field row of an event target.
 *
 * @param {EventTarget|null} target Event target
 * @return {HTMLElement|null} Field row
 */
const getRow = ( target ) =>
	getElement( target )?.closest?.( '[data-impressum-field]' ) ?? null;

/**
 * Get the field row and the side of it the pointer is on.
 *
 * @param {DragEvent} event Drag event
 * @return {Object|null} Field name and position or null
 */
const getDropTarget = ( event ) => {
	const row = getRow( event.target );

	if ( ! row ) {
		return null;
	}

	const rect = row.getBoundingClientRect();

	return {
		field: row.dataset.impressumField,
		position:
			event.clientY < rect.top + rect.height / 2 ? 'before' : 'after',
	};
};

/**
 * Reorder fields by dragging their drag handles.
 *
 * Native listeners are used so that the block list drop zone, which listens
 * on an ancestor element, ignores field drags inside the block.
 *
 * @param {Object}   props               Hook properties
 * @param {string[]} props.fields        List of field names in their order
 * @param {Function} props.setAttributes Block attribute setter
 * @return {Object} Ref for the block element and a getter for row properties
 */
export default function useFieldDragging( { fields, setAttributes } ) {
	const draggedFieldRef = useRef( null );
	const fieldsRef = useRef( fields );
	const [ draggedField, setDraggedField ] = useState( null );
	const [ dropTarget, setDropTarget ] = useState( null );

	// the fields are a new array on every render, which must not reattach the
	// listeners during a drag
	fieldsRef.current = fields;

	const ref = useRefEffect(
		( element ) => {
			let timeout;

			const reset = () => {
				clearTimeout( timeout );
				draggedFieldRef.current = null;
				setDraggedField( null );
				setDropTarget( null );
			};

			const updateDropTarget = ( target ) => {
				setDropTarget( ( currentTarget ) =>
					currentTarget?.field === target?.field &&
					currentTarget?.position === target?.position
						? currentTarget
						: target
				);
			};

			const onDragStart = ( event ) => {
				const handle = getElement( event.target )?.closest?.(
					'.impressum__mover--button.is-drag-handle'
				);
				const row = getRow( handle );

				if ( ! row ) {
					return;
				}

				// the block editor cancels any drag inside a selected block
				// that doesn't start at the block element itself
				event.stopPropagation();

				const field = row.dataset.impressumField;
				const handleRect = handle.getBoundingClientRect();
				const rowRect = row.getBoundingClientRect();

				event.dataTransfer.setData( DATA_TYPE, field );
				event.dataTransfer.setDragImage(
					row,
					handleRect.left - rowRect.left + handleRect.width / 2,
					handleRect.top - rowRect.top + handleRect.height / 2
				);
				event.dataTransfer.effectAllowed = 'move';
				draggedFieldRef.current = field;
				// changing the dragged element while starting the drag can
				// cancel it in some browsers
				timeout = setTimeout( () => setDraggedField( field ) );
			};

			// both events need to be canceled to allow dropping
			const onDragEnterOver = ( event ) => {
				if ( ! draggedFieldRef.current ) {
					return;
				}

				event.preventDefault();
				event.dataTransfer.dropEffect = 'move';
				updateDropTarget( getDropTarget( event ) );
			};

			const onDragLeave = ( event ) => {
				if ( ! draggedFieldRef.current ) {
					return;
				}

				// Safari doesn't set the related target, thus compare the
				// pointer position with the block instead
				const rect = element.getBoundingClientRect();

				if (
					event.clientX <= rect.left ||
					event.clientX >= rect.right ||
					event.clientY <= rect.top ||
					event.clientY >= rect.bottom
				) {
					setDropTarget( null );
				}
			};

			const onDrop = ( event ) => {
				if ( ! draggedFieldRef.current ) {
					return;
				}

				event.preventDefault();

				const currentFields = fieldsRef.current;
				const target = getDropTarget( event );

				if ( target ) {
					const newFields = moveField(
						currentFields,
						draggedFieldRef.current,
						target.field,
						target.position
					);

					if ( newFields.join() !== currentFields.join() ) {
						setAttributes( { enabledFields: newFields } );
					}
				}

				reset();
			};

			// capture the event to handle it before the block editor does
			element.addEventListener( 'dragstart', onDragStart, true );
			element.addEventListener( 'dragenter', onDragEnterOver );
			element.addEventListener( 'dragover', onDragEnterOver );
			element.addEventListener( 'dragleave', onDragLeave );
			element.addEventListener( 'drop', onDrop );
			element.addEventListener( 'dragend', reset );

			return () => {
				clearTimeout( timeout );
				element.removeEventListener( 'dragstart', onDragStart, true );
				element.removeEventListener( 'dragenter', onDragEnterOver );
				element.removeEventListener( 'dragover', onDragEnterOver );
				element.removeEventListener( 'dragleave', onDragLeave );
				element.removeEventListener( 'drop', onDrop );
				element.removeEventListener( 'dragend', reset );
			};
		},
		[ setAttributes ]
	);

	const getRowProps = ( field ) => {
		const classNames = [ 'impressum__field' ];

		if ( draggedField === field ) {
			classNames.push( 'is-dragged' );
		}

		if ( draggedField && dropTarget?.field === field ) {
			classNames.push( 'is-drop-target-' + dropTarget.position );
		}

		return {
			className: classNames.join( ' ' ),
			'data-impressum-field': field,
		};
	};

	return { getRowProps, ref };
}
