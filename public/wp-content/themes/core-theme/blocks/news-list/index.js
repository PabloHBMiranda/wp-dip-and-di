( function ( blocks, element, blockEditor, components, apiFetch ) {
    var el        = element.createElement;
    var useState  = element.useState;
    var useEffect = element.useEffect;

    var InspectorControls = blockEditor.InspectorControls;
    var useBlockProps     = blockEditor.useBlockProps;
    var PanelBody         = components.PanelBody;
    var SelectControl     = components.SelectControl;
    var RangeControl      = components.RangeControl;
    var Spinner           = components.Spinner;

    blocks.registerBlockType( 'core-theme/news-list', {
        edit: function ( props ) {
            var attributes    = props.attributes;
            var setAttributes = props.setAttributes;
            var blockProps    = useBlockProps();

            var strategiesState = useState( [] );
            var strategies      = strategiesState[ 0 ];
            var setStrategies   = strategiesState[ 1 ];

            var loadingState = useState( true );
            var isLoading    = loadingState[ 0 ];
            var setLoading   = loadingState[ 1 ];

            useEffect( function () {
                apiFetch( { path: '/core-theme/v1/news-block/strategies' } )
                    .then( function ( data ) { setStrategies( data ); setLoading( false ); } )
                    .catch( function ()       { setLoading( false ); } );
            }, [] );

            return el(
                'div',
                blockProps,
                el( InspectorControls, null,
                    el( PanelBody, { title: 'News Settings', initialOpen: true },
                        isLoading
                            ? el( Spinner, null )
                            : el( SelectControl, {
                                label:    'Display Strategy',
                                value:    attributes.strategy,
                                options:  strategies.map( function ( s ) {
                                    return { value: s.value, label: s.label };
                                } ),
                                onChange: function ( val ) { setAttributes( { strategy: val } ); }
                            } ),
                        el( RangeControl, {
                            label:    'Number of Posts',
                            value:    attributes.postsPerPage,
                            onChange: function ( val ) { setAttributes( { postsPerPage: val } ); },
                            min: 1,
                            max: 24
                        } )
                    )
                ),
                el( wp.serverSideRender, {
                    block:      'core-theme/news-list',
                    attributes: attributes
                } )
            );
        },

        save: function () { return null; }
    } );

} )(
    window.wp.blocks,
    window.wp.element,
    window.wp.blockEditor,
    window.wp.components,
    window.wp.apiFetch
);
