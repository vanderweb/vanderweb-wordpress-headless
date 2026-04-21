import { useState } from '@wordpress/element';
import { Button, Card, CardBody, CardHeader } from '@wordpress/components';
import SectionEditor from './SectionEditor';

export default function SectionList( { sections, sectionTypes, onChange } ) {
	const [ expandedIndex, setExpandedIndex ] = useState( null );

	const getSectionDef = ( type ) => sectionTypes.find( ( s ) => s.type === type );

	const toggleExpand = ( index ) => {
		setExpandedIndex( ( prev ) => ( prev === index ? null : index ) );
	};

	const moveSection = ( index, direction ) => {
		const next = [ ...sections ];
		const target = index + direction;
		if ( target < 0 || target >= next.length ) return;
		[ next[ index ], next[ target ] ] = [ next[ target ], next[ index ] ];

		if ( expandedIndex === index ) setExpandedIndex( target );
		else if ( expandedIndex === target ) setExpandedIndex( index );

		onChange( next );
	};

	const updateSection = ( index, updated ) => {
		onChange( sections.map( ( s, i ) => ( i === index ? updated : s ) ) );
	};

	const removeSection = ( index ) => {
		onChange( sections.filter( ( _, i ) => i !== index ) );
		if ( expandedIndex === index ) setExpandedIndex( null );
		else if ( expandedIndex > index ) setExpandedIndex( expandedIndex - 1 );
	};

	return (
		<div className="vander-section-list">
			{ sections.map( ( section, index ) => {
				const def = getSectionDef( section.type );
				const isExpanded = expandedIndex === index;

				return (
					<Card key={ index } className="vander-section-item" size="small">
						<CardHeader>
							<div className="vander-section-item__header">
								<div className="vander-section-item__move">
									<Button
										variant="tertiary"
										size="small"
										onClick={ () => moveSection( index, -1 ) }
										disabled={ index === 0 }
										aria-label="Move up"
									>↑</Button>
									<Button
										variant="tertiary"
										size="small"
										onClick={ () => moveSection( index, 1 ) }
										disabled={ index === sections.length - 1 }
										aria-label="Move down"
									>↓</Button>
								</div>
								<span className="vander-section-item__label">
									{ def?.label ?? section.type }
								</span>
								<div className="vander-section-item__actions">
									<Button
										variant="tertiary"
										size="small"
										onClick={ () => toggleExpand( index ) }
									>
										{ isExpanded ? 'Collapse' : 'Edit' }
									</Button>
									<Button
										variant="link"
										isDestructive
										size="small"
										onClick={ () => removeSection( index ) }
									>
										Remove
									</Button>
								</div>
							</div>
						</CardHeader>
						{ isExpanded && (
							<CardBody>
								<SectionEditor
									section={ section }
									sectionDef={ def }
									onChange={ ( updated ) => updateSection( index, updated ) }
								/>
							</CardBody>
						) }
					</Card>
				);
			} ) }
		</div>
	);
}
