/**
* Register map and gpx blocks for Gutenberg Editor
*/

    var __                = wp.i18n.__; // The __() function for internationalization.
    var createElement     = wp.element.createElement; // The wp.element.createElement() function to create elements.
    var registerBlockType = wp.blocks.registerBlockType; // The registerBlockType() function to register blocks.
    
    /**
     * Register gpx block
     *
     * @param  {string}   name     Block name.
     * @param  {Object}   settings Block settings.
     * @return {?WPBlock}          Block itself, if registered successfully,
     *                             otherwise "undefined".
     */
registerBlockType(
        'lf-hiker/gpx-block', // Block name. Must be string that contains a namespace prefix. Example: my-plugin/my-custom-block.   
        {
            title: __( 'GPX File' ), // Block title. __() function allows for internationalization.
            icon: 'media-document', // Block icon from Dashicons. https://developer.wordpress.org/resource/dashicons/.
            category: 'common', // Block category. Group blocks together based on common traits E.g. common, formatting, layout widgets, embed.
            attributes: {
                gpxID: {
                    type: 'number'
                },
                src: {
                    type: 'string'
                },
                title: {
                    type: 'string'
                },
                color: {
                    type: 'string'
                },
                width: {
                    type: 'number'
                },
                unit: {
                    type: 'string'
                },
                unit_h: {
                    type: 'string'
                },
                step_min: {
                    type: 'number'
                },
                button: {
                    type: 'boolean'
                }
            },

            // Defines the block within the editor.
            edit: function( props ) {
                
                var {attributes , setAttributes, focus, className} = props;
                var InspectorControls = wp.editor.InspectorControls;
                var Button = wp.components.Button;
                var MediaUpload = wp.editor.MediaUpload;
                var TextControl = wp.components.TextControl;
                var SelectControl = wp.components.SelectControl;
                var RadioControl = wp.components.RadioControl;
                var CheckboxControl = wp.components.CheckboxControl;
                var onSelectGPX = function(media) {
                     return props.setAttributes({
                        src: media.url,
                        gpxID: media.id
                    });
                }
                
                return [
                    createElement(
                        MediaUpload,
                        {
                            onSelect: onSelectSVG,
                            type: 'application/gpx+xml',
                            allowedTypes: ['application/gpx+xml'],
                            value: attributes.svgID,
                            render: function(open) {
                                return createElement(btn,{onClick: open.open },
                                    attributes.src ? 'GPX: ' + attributes.src : 'Click here to Open Media Library to select SVG')
                            }
                        }
                    ),
                                        
                    createElement( InspectorControls, { key: 'inspector' }, // Display the block options in the inspector pancreateElement.
                        createElement('div',{ className: 'gpx_div_main'}    ,
                            createElement(
                                'hr',
                                {},
                            ),
                            createElement(
                                'p',
                                {},
                                'Change SVG interactive'
                                //__('Change SVG interactive')
                            ),
                            createElement(
                                CheckboxControl,
                                {
                                    // label: __('Interactive'),
                                    label: 'Interactive',
                                    value: attributes.interactive,
                                    checked: attributes.interactive,
                                    onChange: onChangeInteractive
                                }
                            ),
                            createElement(
                                SelectControl,
                                {
                                    // label: __('Alignment'),
                                    label: 'Alignment',
                                    value: attributes.class,
                                    options: [
                                        { label: 'No alignment', value: '' },
                                        { label: 'Left', value: 'fm-left' },
                                        { label: 'Right', value: 'fm-right' }
                                    ],
                                    onChange: onChangeClass
                                }
                            ),
                            createElement(
                                    CheckboxControl,
                                    {
                                        // label: __('Interactive'),
                                        label: 'Hide button enlarge/reduce',
                                        value: attributes.hide_button,
                                        checked: attributes.hide_button,
                                        onChange: onChangeHideButton
                                    }
                            )
                        ),
                    ),
                ];
            },

            // Defines the saved block.
            save: function( props ) {
                return createElement(
                    'p',
                    {
                        className: props.className,
                        key: 'return-key',
                    },props.attributes.content);
            },
        }
);