<?php 

acf_register_block( array(
    'name'            => 'sa_cta_box',
    'title'           => __( 'CTA Box', 'your-text-domain' ),
    'description'     => __( 'Display call to action content', 'your-text-domain' ),
    'render_callback' => 'sa_cta_box_render_callback',
    'category'        => 'formatting',
    'icon'            => 'admin-comments',
    'keywords'        => array( 'cta', 'call to action' ),
) );

function sa_cta_box_render_callback( $block, $content = '', $is_preview = false ) {
    $context = Timber::context();

    // Store block values.
    $context['block'] = $block;

    // Store field values.
    $context['fields'] = get_fields();

    // Store $is_preview value.
    $context['is_preview'] = $is_preview;

    // Render the block.
    Timber::render( 'components/blocks/cta-box.twig', $context );
}
