import { useSelect, useDispatch } from '@wordpress/data';
import { PluginDocumentSettingPanel } from '@wordpress/editor';
import { useState } from '@wordpress/element';
import { Button, SelectControl } from '@wordpress/components';
import SectionList from '../components/SectionList';

const sectionTypes = window.vanderSectionTypes ?? [];

export default function PageSectionsPanel() {
	const [ addingType, setAddingType ] = useState( sectionTypes[0]?.type ?? '' );

	const { postType, rawMeta } = useSelect( ( select ) => ( {
		postType: select( 'core/editor' ).getCurrentPostType(),
		rawMeta:  select( 'core/editor' ).getEditedPostAttribute( 'meta' ),
	} ) );

	const { editPost } = useDispatch( 'core/editor' );

	if ( postType !== 'page' ) {
		return null;
	}

	const sections = ( () => {
		try {
			return JSON.parse( rawMeta?.page_sections ?? '[]' ) ?? [];
		} catch {
			return [];
		}
	} )();

	const saveSections = ( next ) => {
		editPost( { meta: { page_sections: JSON.stringify( next ) } } );
	};

	const addSection = () => {
		const def = sectionTypes.find( ( s ) => s.type === addingType );
		if ( ! def ) return;

		const empty = { type: addingType };
		( def.fields ?? [] ).forEach( ( f ) => {
			empty[ f.key ] = f.type === 'repeater' ? [] : f.type === 'toggle' ? false : '';
		} );

		saveSections( [ ...sections, empty ] );
	};

	const typeOptions = sectionTypes.map( ( s ) => ( { label: s.label, value: s.type } ) );

	return (
		<PluginDocumentSettingPanel
			name="vander-page-sections"
			title="Page Sections"
			className="vander-sections-panel"
		>
			<SectionList
				sections={ sections }
				sectionTypes={ sectionTypes }
				onChange={ saveSections }
			/>

			{ sectionTypes.length > 0 && (
				<div className="vander-add-section">
					<SelectControl
						value={ addingType }
						options={ typeOptions }
						onChange={ setAddingType }
					/>
					<Button variant="primary" onClick={ addSection }>
						Add Section
					</Button>
				</div>
			) }
		</PluginDocumentSettingPanel>
	);
}
