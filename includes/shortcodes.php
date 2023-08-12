<?php

function social_links_shortcode($atts) {
    $atts = shortcode_atts(array(
        'list'      => 1, // true for a <ul>, false for <div>
        'raw'       => 0, // true for text only, no HTML
        'css_class' => '', // class to add to the wrapper
        'delim'     => ' ' // entity between items
    ), $atts);

    // $seo_data = get_option('wpseo_social');
    $profiles = get_field('social_profiles', 'option');
    $seo_data = array();

    if(!empty($profiles)) {
        foreach ($profiles as $key => $profile) {
            $seo_data[$profile['name']] = $profile['url'];
        }
    }

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

    $page_option = get_field('page','option');

    $context['form_url'] = get_the_permalink($page_option['blog_list_page_id']);
    $context['offices'] = Timber::query_posts([
        'post_type' => 'sa_branch',
        'post_status' => 'publish',
        'nopaging'=> true,
        'orderby'=> 'title',
        'order' => 'ASC'
    ]);

    return Timber::compile('components/shortcodes/branch-area-box.twig', $context);
}
add_shortcode('branch_area_box','branch_area_box_shortcode');

function branches_list_shortcode() {
    $context = Timber::context();

    $branches_query = new Timber\PostQuery([
        'post_type' => 'sa_branch',
        'post_status' => 'publish',
        'nopaging' => true,
        'orderby'=> 'title',
        'order' => 'ASC'
    ]);

    $context['branches_list'] = $branches_query->get_posts();

    return Timber::compile('components/shortcodes/branches-list.twig', $context);
}
add_shortcode('branches_list','branches_list_shortcode');

function button_shortcode($atts) {
    $atts = shortcode_atts([
        'title' => 'Button Title',
        'url' => '#',
        'target' => false,
        'css_class' => 'btn btn-lg btn-primary text-uppercase btn-shortcode',
        'block' => false
    ], $atts);

    $css_class = !empty($atts['css_class'])?$atts['css_class']:'';
    $target = $atts['target']?'target="_blank"':'';
    $block = filter_var($atts['block'], FILTER_VALIDATE_BOOLEAN);

    if($block) {
        $css_class = $css_class . ' btn-block';
    }

    return '<a ' . $target . ' class="' . $css_class . '" href="' . $atts['url'] . '">' . $atts['title'] . '</a>';
}
add_shortcode('button','button_shortcode');

function link_arrow_shortcode($atts) {
    $atts = shortcode_atts([
        'title' => 'Link Title',
        'url' => '#',
        'target' => false,
        'css_class' => 'shortcode-link-arrow'
    ], $atts);

    $css_class = !empty($atts['css_class'])?$atts['css_class']:'';
    $target = filter_var($atts['target'], FILTER_VALIDATE_BOOLEAN);

    return '<a ' . $target . ' class="' . $css_class . '" href="' . $atts['url'] . '">' . $atts['title'] . ' <i class="fa-regular fa-chevron-right"></i></a>';
}
add_shortcode('link_arrow','link_arrow_shortcode');

function text_two_columns_shortcode($atts, $content) {
    return '<div class="content-2-columns">' . do_shortcode($content) . '</div>';
}
add_shortcode('text_two_columns','text_two_columns_shortcode');

function new_homes_list_shortcode($atts) {
    $context = Timber::context();

    $atts = shortcode_atts([
        'price_equal' => ''
    ], $atts);

    $meta_query = [];

    if(!empty($atts['price_equal'])) {
        $meta_query[] = [
            'key'   => 'nh_price',
            'value' => trim($atts['price_equal'])
        ];
    }

    $context['new_homes_list'] = Timber::query_posts([
        'post_type'   => 'sa_new_home',
        'post_status' => 'publish',
        'nopaging'    => true,
        'order'       => 'ASC',
        'orderby'     => 'date',
        'meta_query'  => $meta_query
    ]);

    return Timber::compile('components/shortcodes/new-homes-list.twig', $context);
}
add_shortcode('new_homes_list','new_homes_list_shortcode');

function workable_careers_list_shortcode($atts) {
    $atts = shortcode_atts([
        'id' => 0
    ], $atts);

    return Timber::compile('components/shortcodes/workable-careers-list.twig', $atts);
}
add_shortcode('workable_careers_list','workable_careers_list_shortcode');

/*function dataloft_chart_shortcode() {
    return Timber::compile('components/shortcodes/dataloft.twig');
}
add_shortcode('dataloft_chart','dataloft_chart_shortcode');*/

function workable_categories_shortcode() {
    return '<div data-workable="categories"></div>';
}
add_shortcode('workable_categories','workable_categories_shortcode');
