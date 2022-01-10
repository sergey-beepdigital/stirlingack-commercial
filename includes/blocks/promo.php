<?php 

acf_register_block( array(
    'name'            => 'sa_block_promo',
    'title'           => __( 'Promo', 'your-text-domain' ),
    'description'     => __( 'Display promo boxes', 'your-text-domain' ),
    'render_callback' => 'sa_block_promo_render_callback',
    'category'        => 'formatting',
    'icon'            => 'admin-comments',
    'keywords'        => array( 'Promo', 'Valuation', 'New Home' ),
) );

function sa_block_promo_render_callback( $block, $content = '', $is_preview = false ) {
    $context = Timber::context();

    // Store block values.
    $context['block'] = $block;

    $fields = get_fields();

    if($fields['promo_type'] == 'valuation') {
        $valuation_group = get_field('section_valuation','option');

        //$context['promo_content'] = '<p></p>';
        if(!empty($valuation_group['title'])) {
            $context['promo_content'] .= '<h2>' . $valuation_group['title'] . '</h2>';
        }
        $context['promo_content'] .= $valuation_group['content'];
        if(!empty($valuation_group['link'])) {
            $context['promo_content'] .= '<p><a href="' . $valuation_group['link']['url'] . '">' . $valuation_group['link']['title'] . '</a></p>';
        }
    } else if($fields['promo_type'] == 'new_home') {
        $new_home_promo_group = get_field('section_new_homes_promo','option');
        $context['promo_content'] = $new_home_promo_group['content'];
    }

    // Store field values.
    $context['fields'] = $fields;

    // Store $is_preview value.
    $context['is_preview'] = $is_preview;

    // Render the block.
    Timber::render( 'components/blocks/promo.twig', $context );
}
