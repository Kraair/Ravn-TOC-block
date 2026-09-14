( function ( blocks, element, blockEditor, components, serverSideRender, i18n ) {
	var el = element.createElement;
	var Fragment = element.Fragment;
	var __ = i18n.__;
	var InspectorControls = blockEditor.InspectorControls;
	var PanelColorSettings = blockEditor.PanelColorSettings;
	var PanelBody = components.PanelBody;
	var TextControl = components.TextControl;
	var CheckboxControl = components.CheckboxControl;
	var ToggleControl = components.ToggleControl;
	var RangeControl = components.RangeControl;
	var SelectControl = components.SelectControl;
	var ServerSideRender = serverSideRender && serverSideRender.default ? serverSideRender.default : serverSideRender;

	function levelCheckboxes( levels, onToggle, keyPrefix ) {
		return [ 1, 2, 3, 4, 5, 6 ].map( function ( level ) {
			return el( CheckboxControl, {
				key: keyPrefix + level,
				label: 'H' + level,
				checked: levels.indexOf( level ) > -1,
				onChange: function () {
					onToggle( level );
				},
			} );
		} );
	}

	blocks.registerBlockType( 'toc-block/inhoudsopgave', {
		edit: function ( props ) {
			var attributes = props.attributes;
			var setAttributes = props.setAttributes;
			var desktopLevels = attributes.desktopLevels || [];
			var mobileLevels = attributes.mobileLevels || [];

			function toggleDesktop( level ) {
				var next = desktopLevels.indexOf( level ) > -1
					? desktopLevels.filter( function ( l ) { return l !== level; } )
					: desktopLevels.concat( [ level ] ).sort();
				setAttributes( { desktopLevels: next } );
			}

			function toggleMobile( level ) {
				var next = mobileLevels.indexOf( level ) > -1
					? mobileLevels.filter( function ( l ) { return l !== level; } )
					: mobileLevels.concat( [ level ] ).sort();
				setAttributes( { mobileLevels: next } );
			}

			return el(
				Fragment,
				{},
				el(
					InspectorControls,
					{},
					el(
						PanelBody,
						{ title: __( 'Instellingen', 'toc-block' ) },
						el( TextControl, {
							label: __( 'Titel boven de lijst', 'toc-block' ),
							value: attributes.title,
							onChange: function ( value ) {
								setAttributes( { title: value } );
							},
						} ),
						el( 'p', { style: { fontWeight: '600', marginBottom: '4px' } }, __( 'Titels op desktop', 'toc-block' ) ),
						levelCheckboxes( desktopLevels, toggleDesktop, 'desktop-' ),
						el( 'p', { style: { fontWeight: '600', marginTop: '16px', marginBottom: '4px' } }, __( 'Titels op mobiel', 'toc-block' ) ),
						levelCheckboxes( mobileLevels, toggleMobile, 'mobile-' ),
						el( ToggleControl, {
							label: __( 'Actieve sectie highlighten tijdens scrollen', 'toc-block' ),
							checked: !! attributes.highlightActive,
							onChange: function ( value ) {
								setAttributes( { highlightActive: value } );
							},
							__nextHasNoMarginBottom: true,
						} )
					),
					el(
						PanelBody,
						{ title: __( 'Links', 'toc-block' ), initialOpen: false },
						el( RangeControl, {
							label: __( 'Lettergrootte links (px)', 'toc-block' ),
							value: attributes.linkFontSize || undefined,
							onChange: function ( value ) {
								setAttributes( { linkFontSize: value || 0 } );
							},
							min: 10,
							max: 48,
							allowReset: true,
						} ),
						el( SelectControl, {
							label: __( 'Dikte van de letters', 'toc-block' ),
							value: attributes.linkFontWeight,
							options: [
								{ label: __( 'Standaard', 'toc-block' ), value: 'inherit' },
								{ label: __( 'Normaal (400)', 'toc-block' ), value: '400' },
								{ label: __( 'Medium (500)', 'toc-block' ), value: '500' },
								{ label: __( 'Vet (600)', 'toc-block' ), value: '600' },
								{ label: __( 'Extra vet (700)', 'toc-block' ), value: '700' },
							],
							onChange: function ( value ) {
								setAttributes( { linkFontWeight: value } );
							},
						} ),
						el( SelectControl, {
							label: __( 'Onderstreping', 'toc-block' ),
							value: attributes.linkTextDecoration,
							options: [
								{ label: __( 'Geen', 'toc-block' ), value: 'none' },
								{ label: __( 'Onderstreept', 'toc-block' ), value: 'underline' },
							],
							onChange: function ( value ) {
								setAttributes( { linkTextDecoration: value } );
							},
						} ),
						el( PanelColorSettings, {
							title: __( 'Kleuren', 'toc-block' ),
							initialOpen: true,
							colorSettings: [
								{
									value: attributes.linkColor,
									onChange: function ( value ) {
										setAttributes( { linkColor: value || '' } );
									},
									label: __( 'Linkkleur', 'toc-block' ),
								},
								{
									value: attributes.linkHoverColor,
									onChange: function ( value ) {
										setAttributes( { linkHoverColor: value || '' } );
									},
									label: __( 'Linkkleur bij hover', 'toc-block' ),
								},
							],
						} )
					)
				),
				el(
					'div',
					{ className: props.className },
					ServerSideRender
						? el( ServerSideRender, {
								block: 'toc-block/inhoudsopgave',
								attributes: attributes,
						  } )
						: el( 'p', {}, __( 'Voorbeeld wordt geladen…', 'toc-block' ) )
				)
			);
		},
		save: function () {
			return null;
		},
	} );
} )( window.wp.blocks, window.wp.element, window.wp.blockEditor, window.wp.components, window.wp.serverSideRender, window.wp.i18n );
