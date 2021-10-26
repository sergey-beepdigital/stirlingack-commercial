<?php

function social_links_shortcode($atts) {
    $atts = shortcode_atts(array(
        'list'      => 1, // true for a <ul>, false for <div>
        'raw'       => 0, // true for text only, no HTML
        'css_class' => '', // class to add to the wrapper
        'delim'     => ' ' // entity between items
    ), $atts);

    $seo_data = get_option('wpseo_social');
    $options = apply_filters('crowd_social_link_options', array());
    $output = array();
    $wrapp_tag = 'div';

    if (sizeof($options) > 0) {
        if (!$atts['raw']) {
            if ($atts['list']) {
                $wrapp_tag = 'ul';
            }

            $output[] = '<' . $wrapp_tag . ' class="' . $atts['css_class'] . '">';
        }

        foreach ($seo_data as $seo_network => $url) {
            $network_settings = !empty($options[$seo_network])?$options[$seo_network]:'';

            if(!empty($url) && !empty($network_settings)) {
                if (!empty($network_settings['prepend']))
                    $url = $network_settings['prepend'] . $url;
                if ($url && !empty($network_settings['icon']) && !$atts['raw']) {
                    if ($atts['list']) $output[] = '<li>';
                    $output[] = '<a target="_blank" href="' . esc_url_raw($url) . '">' . $network_settings['icon'] . '</a>';
                    if ($atts['list']) $output[] = '</li>';
                } else {
                    $output[] = esc_url_raw($url);
                }
            }
        }
        if (!$atts['raw'])
            $output[] = '</' . $wrapp_tag . '>';
    }

    if (!empty($output)) return join($atts['delim'], $output);
}
add_shortcode('social_links', 'social_links_shortcode');

function branch_area_box_shortcode($atts) {
    $context = Timber::context();

    $page_urls = get_field('page_url','option');

    $context['form_url'] = get_the_permalink($page_urls['blog_page']);
    $context['offices'] = Timber::query_posts([
        'post_type' => 'office',
        'nopaging'=> true
    ]);

    Timber::render('components/shortcodes/branch-area-box.twig', $context);
}
add_shortcode('branch_area_box','branch_area_box_shortcode');
