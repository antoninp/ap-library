( function( blocks, element, blockEditor, components ) {
    var el = element.createElement;
    var MediaUpload = blockEditor.MediaUpload;
    var MediaUploadCheck = blockEditor.MediaUploadCheck;
    var InspectorControls = blockEditor.InspectorControls;
    var CheckboxControl = components.CheckboxControl;
    var PanelBody = components.PanelBody;
    var RangeControl = components.RangeControl || blockEditor.RangeControl; // fallback
    var SelectControl = components.SelectControl;
    var ColorPalette = components.ColorPalette || blockEditor.ColorPalette;
    var PanelRow = components.PanelRow;

    blocks.registerBlockType( 'ap-slideshow/ap-slideshow', {
        title: 'AP Slideshow',
        icon: 'format-gallery',
        category: 'media',
        attributes: {
            images: { type: 'array', default: [] },
            auto: { type: 'boolean', default: false },
            delay: { type: 'number', default: 4000 },
            effect: { type: 'string', default: 'fade' },
            showArrows: { type: 'boolean', default: true },
            showDots: { type: 'boolean', default: false },
            pauseOnHover: { type: 'boolean', default: true },
            randomize: { type: 'boolean', default: false },
            loop: { type: 'boolean', default: true },
            showCaptions: { type: 'boolean', default: false },
            arrowColor: { type: 'string', default: '#AEACA6' },
            captionSource: { type: 'string', default: 'caption' }
        },
        edit: function( props ) {
            var images = props.attributes.images || [];
            var auto = props.attributes.auto;
            var delay = props.attributes.delay || 4000;
            var effect = props.attributes.effect || 'fade';
            var showArrows = props.attributes.showArrows;
            var showDots = props.attributes.showDots;
            var pauseOnHover = props.attributes.pauseOnHover;
            var randomize = props.attributes.randomize;
            var loop = props.attributes.loop;
            var showCaptions = props.attributes.showCaptions;
            var arrowColor = props.attributes.arrowColor || '#AEACA6';
            var captionSource = props.attributes.captionSource || 'caption';

            return [
                el( InspectorControls, {},
                    el( PanelBody, { title: 'Slideshow Settings', initialOpen: true },
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
                        } ),
                        el( SelectControl, {
                            label: 'Transition Effect',
                            value: effect,
                            options: [
                                { label: 'Fade', value: 'fade' },
                                { label: 'Slide', value: 'slide' },
                                { label: 'Zoom', value: 'zoom' }
                            ],
                            onChange: function( val ) { props.setAttributes( { effect: val } ); }
                        } ),
                        el( CheckboxControl, {
                            label: 'Show arrows',
                            checked: !!showArrows,
                            onChange: function( val ) { props.setAttributes( { showArrows: val } ); }
                        } ),
                        el( CheckboxControl, {
                            label: 'Show dots/pagination',
                            checked: !!showDots,
                            onChange: function( val ) { props.setAttributes( { showDots: val } ); }
                        } ),
                        el( CheckboxControl, {
                            label: 'Pause on link hover',
                            checked: !!pauseOnHover,
                            onChange: function( val ) { props.setAttributes( { pauseOnHover: val } ); }
                        } ),
                        el( CheckboxControl, {
                            label: 'Randomize image order',
                            checked: !!randomize,
                            onChange: function( val ) { props.setAttributes( { randomize: val } ); }
                        } ),
                        el( CheckboxControl, {
                            label: 'Loop images',
                            checked: !!loop,
                            onChange: function( val ) { props.setAttributes( { loop: val } ); }
                        } ),
                        el( CheckboxControl, {
                            label: 'Show captions',
                            checked: !!showCaptions,
                            onChange: function( val ) { props.setAttributes( { showCaptions: val } ); }
                        } ),
                        el( SelectControl, {
                            label: 'Caption Source',
                            value: captionSource,
                            options: [
                                { label: 'Caption', value: 'caption' },
                                { label: 'Title', value: 'title' },
                                { label: 'Description', value: 'description' }
                            ],
                            onChange: function( val ) { props.setAttributes( { captionSource: val } ); }
                        } ),
                        el( PanelRow, {},
                            el( 'span', {}, 'Arrow color:' ),
                            el( ColorPalette, {
                                value: arrowColor,
                                onChange: function( val ) { props.setAttributes( { arrowColor: val } ); },
                                colors: [
                                    { name: 'Gray', color: '#AEACA6' },
                                    { name: 'Black', color: '#23282d' },
                                    { name: 'White', color: '#fff' },
                                    { name: 'Blue', color: '#007cba' },
                                    { name: 'Red', color: '#d7263d' }
                                ]
                            } )
                        )
                    )
                ),
                el( 'div', { className: 'ap-slideshow-block' },
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
                    el( 'div', { className: 'ap-slideshow-preview' },
                        images.map( function( img, i ) {
                            return el( 'div', { key: img.id, style: { marginBottom: '10px' } },
                                el( 'img', { src: img.url, style: { maxWidth: '100px', display: 'block' } } ),
                                el( 'input', {
                                    type: 'text',
                                    placeholder: 'Link URL',
                                    value: img.link || '',
                                    onChange: function( e ) {
                                        var newImgs = images.slice();
                                        newImgs[i] = Object.assign( {}, newImgs[i], { link: e.target.value } );
                                        props.setAttributes( { images: newImgs } );
                                    },
                                    style: { width: '100%' }
                                } ),
                                el( 'input', {
                                    type: 'text',
                                    placeholder: 'Link Title',
                                    value: img.linkTitle || '',
                                    onChange: function( e ) {
                                        var newImgs = images.slice();
                                        newImgs[i] = Object.assign( {}, newImgs[i], { linkTitle: e.target.value } );
                                        props.setAttributes( { images: newImgs } );
                                    },
                                    style: { width: '100%', marginTop: '4px' }
                                } )
                            );
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