import { Button } from '@wordpress/components';
import { __ } from '@wordpress/i18n';
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

const MoverButton = ( {
	availableFields,
	direction,
	isDisabled,
	field,
	fields,
	setAttributes,
} ) => {
	return (
		<Button
			className={ 'impressum__mover--button is-' + direction + '-button' }
			disabled={ isDisabled }
			icon={ getDirectionArrow( direction ) }
			label={ getDirectionLabel( direction ) }
			onClick={ () =>
				onClick(
					availableFields,
					direction,
					field,
					fields,
					setAttributes
				)
			}
		/>
	);
};

export const DragHandle = () => {
	return (
		<Button
			className="impressum__mover--button is-drag-handle"
			draggable
			icon={ dragHandle }
			label={ __( 'Drag to resort', 'impressum' ) }
		/>
	);
};

export const MoverDownButton = ( {
	availableFields,
	isDisabled,
	field,
	fields,
	setAttributes,
} ) => {
	return (
		<MoverButton
			availableFields={ availableFields }
			direction="down"
			isDisabled={ isDisabled }
			field={ field }
			fields={ fields }
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
