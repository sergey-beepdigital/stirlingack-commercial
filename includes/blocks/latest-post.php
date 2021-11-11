<?php 

acf_register_block( array(
    'name'            => 'sa_latest_post',
    'title'           => __( 'Latest Posts', 'your-text-domain' ),
    'description'     => __( 'Display latest post', 'your-text-domain' ),
    'render_callback' => 'sa_latest_post_render_callback',
    'category'        => 'formatting',
    'icon'            => 'admin-comments',
    'keywords'        => array( 'insight', 'post', 'latest' ),
) );

function sa_latest_post_render_callback( $block, $content = '', $is_preview = false ) {
    $context = Timber::context();
    $fields = get_fields();

    // Store block values.
    $context['block'] = $block;

    // Store field values.
    $context['fields'] = $fields;

    // Posts List
    $context['posts'] = new Timber\PostQuery([
        'post_type' => 'post',
        'posts_per_page' => $fields['amount_of_posts'],
    ]);

    // Store $is_preview value.
    $context['is_preview'] = $is_preview;

    // Render the block.
    Timber::render( 'components/blocks/latest-post.twig', $context );
}
