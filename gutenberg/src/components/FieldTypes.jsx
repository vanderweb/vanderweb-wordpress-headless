import { TextControl, TextareaControl, ToggleControl, SelectControl, Button } from '@wordpress/components';
import { useState } from '@wordpress/element';
import { MediaUpload, MediaUploadCheck } from '@wordpress/block-editor';

export function TextField( { field, value, onChange } ) {
	return (
		<TextControl
			label={ field.label }
			value={ value ?? '' }
			onChange={ onChange }
		/>
	);
}

export function TextareaField( { field, value, onChange } ) {
	return (
		<TextareaControl
			label={ field.label }
			value={ value ?? '' }
			onChange={ onChange }
		/>
	);
}

export function NumberField( { field, value, onChange } ) {
	return (
		<TextControl
			label={ field.label }
			type="number"
			value={ value ?? '' }
			onChange={ ( v ) => onChange( v === '' ? '' : Number( v ) ) }
		/>
	);
}

export function ToggleField( { field, value, onChange } ) {
	return (
		<ToggleControl
			label={ field.label }
			checked={ !! value }
			onChange={ onChange }
		/>
	);
}

export function SelectField( { field, value, onChange } ) {
	const options = ( field.options ?? [] ).map( ( o ) => ( { label: o.label, value: o.value } ) );
	return (
		<SelectControl
			label={ field.label }
			value={ value ?? ( options[0]?.value ?? '' ) }
			options={ options }
			onChange={ onChange }
		/>
	);
}

export function ImageField( { field, value, onChange } ) {
	return (
		<div className="vander-image-field">
			<p className="components-base-control__label">{ field.label }</p>
			<MediaUploadCheck>
				<MediaUpload
					onSelect={ ( media ) => onChange( media.id ) }
					allowedTypes={ [ 'image' ] }
					value={ value }
					render={ ( { open } ) => (
						<div className="vander-image-field__preview">
							{ value ? (
								<>
									<ImagePreview id={ value } />
									<Button variant="secondary" onClick={ open }>Change Image</Button>
									<Button variant="link" isDestructive onClick={ () => onChange( null ) }>Remove</Button>
								</>
							) : (
								<Button variant="secondary" onClick={ open }>Select Image</Button>
							) }
						</div>
					) }
				/>
			</MediaUploadCheck>
		</div>
	);
}

function ImagePreview( { id } ) {
	const [ src, setSrc ] = useState( '' );

	if ( id && ! src ) {
		wp.apiFetch( { path: `/wp/v2/media/${ id }` } ).then( ( media ) => {
			setSrc( media?.source_url ?? '' );
		} ).catch( () => {} );
	}

	return src ? <img src={ src } alt="" style={ { maxWidth: '100%', maxHeight: 120, marginBottom: 8, display: 'block' } } /> : null;
}

export function RepeaterField( { field, value, onChange } ) {
	const rows = Array.isArray( value ) ? value : [];

	const addRow = () => {
		const empty = {};
		( field.fields ?? [] ).forEach( ( f ) => { empty[ f.key ] = ''; } );
		onChange( [ ...rows, empty ] );
	};

	const updateRow = ( index, key, val ) => {
		const next = rows.map( ( row, i ) =>
			i === index ? { ...row, [ key ]: val } : row
		);
		onChange( next );
	};

	const removeRow = ( index ) => {
		onChange( rows.filter( ( _, i ) => i !== index ) );
	};

	const moveRow = ( index, direction ) => {
		const next = [ ...rows ];
		const target = index + direction;
		if ( target < 0 || target >= next.length ) return;
		[ next[ index ], next[ target ] ] = [ next[ target ], next[ index ] ];
		onChange( next );
	};

	return (
		<div className="vander-repeater">
			<p className="components-base-control__label">{ field.label }</p>
			{ rows.map( ( row, index ) => (
				<div key={ index } className="vander-repeater__row">
					<div className="vander-repeater__row-controls">
						<Button variant="tertiary" size="small" onClick={ () => moveRow( index, -1 ) } disabled={ index === 0 }>↑</Button>
						<Button variant="tertiary" size="small" onClick={ () => moveRow( index, 1 ) } disabled={ index === rows.length - 1 }>↓</Button>
						<Button variant="link" isDestructive size="small" onClick={ () => removeRow( index ) }>Remove</Button>
					</div>
					{ ( field.fields ?? [] ).map( ( subField ) => (
						<FieldRenderer
							key={ subField.key }
							field={ subField }
							value={ row[ subField.key ] }
							onChange={ ( val ) => updateRow( index, subField.key, val ) }
						/>
					) ) }
				</div>
			) ) }
			<Button variant="secondary" onClick={ addRow }>Add { field.label }</Button>
		</div>
	);
}

export function FieldRenderer( { field, value, onChange } ) {
	switch ( field.type ) {
		case 'text':     return <TextField     field={ field } value={ value } onChange={ onChange } />;
		case 'textarea': return <TextareaField field={ field } value={ value } onChange={ onChange } />;
		case 'number':   return <NumberField   field={ field } value={ value } onChange={ onChange } />;
		case 'toggle':   return <ToggleField   field={ field } value={ value } onChange={ onChange } />;
		case 'select':   return <SelectField   field={ field } value={ value } onChange={ onChange } />;
		case 'image':    return <ImageField    field={ field } value={ value } onChange={ onChange } />;
		case 'post':     return <TextField     field={ { ...field, label: field.label + ' (Post ID)' } } value={ value } onChange={ onChange } />;
		case 'repeater': return <RepeaterField field={ field } value={ value } onChange={ onChange } />;
		default:         return null;
	}
}
