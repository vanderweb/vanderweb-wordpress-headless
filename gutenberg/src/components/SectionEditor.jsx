import { FieldRenderer } from './FieldTypes';

export default function SectionEditor( { section, sectionDef, onChange } ) {
	if ( ! sectionDef ) {
		return <p>Unknown section type: { section.type }</p>;
	}

	const updateField = ( key, value ) => {
		onChange( { ...section, [ key ]: value } );
	};

	return (
		<div className="vander-section-editor">
			{ ( sectionDef.fields ?? [] ).map( ( field ) => (
				<FieldRenderer
					key={ field.key }
					field={ field }
					value={ section[ field.key ] }
					onChange={ ( val ) => updateField( field.key, val ) }
				/>
			) ) }
		</div>
	);
}
