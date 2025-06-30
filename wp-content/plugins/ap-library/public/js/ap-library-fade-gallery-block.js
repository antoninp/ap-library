( function( blocks, element, blockEditor, components ) {
    var el = element.createElement;
    var MediaUpload = blockEditor.MediaUpload;
    var MediaUploadCheck = blockEditor.MediaUploadCheck;
    var InspectorControls = blockEditor.InspectorControls;
    var CheckboxControl = components.CheckboxControl;
    var PanelBody = components.PanelBody;
    var RangeControl = components.RangeControl || blockEditor.RangeControl; // fallback

    blocks.registerBlockType( 'ap-library/fade-gallery', {
        title: 'Fade Gallery',
        icon: 'format-gallery',
        category: 'media',
        attributes: {
            images: { type: 'array', default: [] },
            auto: { type: 'boolean', default: false },
            delay: { type: 'number', default: 4000 }
        },
        edit: function( props ) {
            var images = props.attributes.images || [];
            var auto = props.attributes.auto;
            var delay = props.attributes.delay || 4000;
            return [
                el( InspectorControls, {},
                    el( PanelBody, { title: 'Gallery Settings', initialOpen: true },
                        el( CheckboxControl, {
                            label: 'Auto transition',
                            checked: !!auto,
                            onChange: function( val ) { props.setAttributes( { auto: val } ); }
                        } ),
                        el( RangeControl, {
                            label: 'Transition Delay (ms)',
                            min: 1000,
                            max: 10000,
                            step: 500,
                            value: delay,
                            onChange: function( val ) { props.setAttributes( { delay: val } ); }
                        } )
                    )
                ),
                el( 'div', { className: 'ap-fade-gallery-block' },
                    el( MediaUploadCheck, {},
                        el( MediaUpload, {
                            onSelect: function( imgs ) { props.setAttributes( { images: imgs } ); },
                            allowedTypes: [ 'image' ],
                            multiple: true,
                            gallery: true,
                            value: images.map( function( img ) { return img.id; } ),
                            render: function( obj ) {
                                return el( components.Button, { onClick: obj.open, variant: 'primary' },
                                    images.length ? 'Edit Gallery' : 'Select Images'
                                );
                            }
                        } )
                    ),
                    el( 'div', { className: 'ap-fade-gallery-preview' },
                        images.map( function( img ) {
                            return el( 'img', { key: img.id, src: img.url, alt: img.alt, style: { maxWidth: '100px', margin: '5px' } } );
                        } )
                    )
                )
            ];
        },
        save: function() { return null; } // Rendered in PHP
    } );
} )(
    window.wp.blocks,
    window.wp.element,
    window.wp.blockEditor || window.wp.editor,
    window.wp.components
);