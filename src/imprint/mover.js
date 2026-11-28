import { speak } from '@wordpress/a11y';
import { Button } from '@wordpress/components';
import { useState } from '@wordpress/element';
import { __, sprintf } from '@wordpress/i18n';
import { chevronDown, chevronUp, dragHandle } from '@wordpress/icons';

const getDirectionArrow = ( direction ) => {
	if ( direction === 'down' ) {
		return chevronDown;
	} else if ( direction === 'up' ) {
		return chevronUp;
	}

	return null;
};

const getDirectionLabel = ( direction ) => {
	if ( direction === 'down' ) {
		return __( 'Move down', 'impressum' );
	} else if ( direction === 'up' ) {
		return __( 'Move up', 'impressum' );
	}

	return null;
};

/**
 * Move a field before or after another field.
 *
 * @param {string[]} fields      List of field names
 * @param {string}   field       Name of the field to move
 * @param {string}   targetField Name of the field to move it next to
 * @param {string}   position    Either 'before' or 'after'
 * @return {string[]} New list of field names
 */
export const moveField = ( fields, field, targetField, position ) => {
	if ( field === targetField || fields.indexOf( field ) === -1 ) {
		return fields;
	}

	const newFields = fields.filter( ( key ) => key !== field );
	const targetIndex = newFields.indexOf( targetField );

	if ( targetIndex === -1 ) {
		return fields;
	}

	newFields.splice(
		position === 'after' ? targetIndex + 1 : targetIndex,
		0,
		field
	);

	return newFields;
};

const onClick = (
	availableFields,
	direction,
	field,
	fields,
	setAttributes
) => {
	const availableFieldsIndex = availableFields.indexOf( field );
	let newFields;

	if ( direction === 'up' ) {
		newFields = moveField(
			fields,
			field,
			availableFields[ availableFieldsIndex - 1 ],
			'before'
		);
	} else {
		newFields = moveField(
			fields,
			field,
			availableFields[ availableFieldsIndex + 1 ],
			'after'
		);
	}

	setAttributes( { enabledFields: newFields } );
};

/**
 * Get keys for the field rows, which can be changed to remount a row.
 *
 * When moving a field down, React moves the DOM node of the moved row instead
 * of the one of the row it is moved after, which blurs the focused mover button.
 * Even though React restores the focus, screen readers then announce the
 * button again instead of the new position. Remounting the other row instead
 * keeps the moved row and thus the focus in place.
 *
 * @return {{getRowKey: Function, remountRow: Function}} Row key functions
 */
export const useRowKeys = () => {
	const [ versions, setVersions ] = useState( {} );

	const getRowKey = ( field ) =>
		versions[ field ] ? field + '-' + versions[ field ] : field;

	const remountRow = ( field ) =>
		setVersions( ( currentVersions ) => ( {
			...currentVersions,
			[ field ]: ( currentVersions[ field ] || 0 ) + 1,
		} ) );

	return { getRowKey, remountRow };
};

const MoverButton = ( {
	availableFields,
	direction,
	isDisabled,
	field,
	fields,
	remountRow,
	setAttributes,
} ) => {
	return (
		<Button
			accessibleWhenDisabled
			className={ 'impressum__mover--button is-' + direction + '-button' }
			disabled={ isDisabled }
			icon={ getDirectionArrow( direction ) }
			label={ getDirectionLabel( direction ) }
			onClick={ () => {
				const newIndex =
					availableFields.indexOf( field ) +
					( direction === 'up' ? -1 : 1 );

				if ( direction === 'down' ) {
					// must be batched with the attribute update to take effect
					remountRow( availableFields[ newIndex ] );
				}

				onClick(
					availableFields,
					direction,
					field,
					fields,
					setAttributes
				);
				speak(
					sprintf(
						/* translators: 1: new position, 2: number of fields */
						__( 'Moved to position %1$d of %2$d', 'impressum' ),
						newIndex + 1,
						availableFields.length
					)
				);
			} }
		/>
	);
};

export const DragHandle = () => {
	return (
		<Button
			className="impressum__mover--button is-drag-handle"
			draggable
			icon={ dragHandle }
			label={ __( 'Drag to reorder', 'impressum' ) }
			// the mover buttons are the alternative for keyboard users
			tabIndex={ -1 }
		/>
	);
};

export const MoverDownButton = ( {
	availableFields,
	isDisabled,
	field,
	fields,
	remountRow,
	setAttributes,
} ) => {
	return (
		<MoverButton
			availableFields={ availableFields }
			direction="down"
			isDisabled={ isDisabled }
			field={ field }
			fields={ fields }
			remountRow={ remountRow }
			setAttributes={ setAttributes }
		/>
	);
};

export const MoverUpButton = ( {
	availableFields,
	isDisabled,
	field,
	fields,
	setAttributes,
} ) => {
	return (
		<MoverButton
			availableFields={ availableFields }
			direction="up"
			isDisabled={ isDisabled }
			field={ field }
			fields={ fields }
			setAttributes={ setAttributes }
		/>
	);
};
