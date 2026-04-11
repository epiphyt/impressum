import { Button } from '@wordpress/components';
import { __ } from '@wordpress/i18n';
import { chevronDown, chevronUp } from '@wordpress/icons';

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

const onClick = (
	availableFields,
	direction,
	field,
	fields,
	setAttributes
) => {
	const index = fields.indexOf( field );
	const availableFieldsIndex = availableFields.indexOf( field );

	if ( direction === 'up' ) {
		const newIndex = fields.indexOf(
			availableFields[ availableFieldsIndex - 1 ]
		);

		fields.splice( index, 1 );
		fields.splice( newIndex, 0, field );
	} else {
		const newIndex = fields.indexOf(
			availableFields[ availableFieldsIndex + 1 ]
		);

		fields.splice( newIndex + 1, 0, field );
		fields.splice( index, 1 );
	}

	const newFields = structuredClone( fields );

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
