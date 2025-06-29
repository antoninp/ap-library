( function( blocks, element, blockEditor, components ) {
    var el = element.createElement;
    var MediaUpload = blockEditor.MediaUpload;
    var MediaUploadCheck = blockEditor.MediaUploadCheck;
    var CheckboxControl = components.CheckboxControl;

    blocks.registerBlockType( 'ap-library/fade-gallery', {
        title: 'Fade Gallery',
        icon: 'format-gallery',
        category: 'media',
        attributes: {
            images: {
                type: 'array',
                default: [],
            },
            auto: {
                type: 'boolean',
                default: false
            }
        },
        edit: function( props ) {
            var images = props.attributes.images || [];
            var auto = props.attributes.auto;
            return el( 'div', { className: 'ap-fade-gallery-block' },
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
                ),
                el( CheckboxControl, {
                    label: 'Auto transition',
                    checked: !!auto,
                    onChange: function( val ) { props.setAttributes( { auto: val } ); }
                } )
            );
        },
        save: function() { return null; } // Rendered in PHP
    } );
} )(
    window.wp.blocks,
    window.wp.element,
    window.wp.blockEditor || window.wp.editor,
    window.wp.components
);