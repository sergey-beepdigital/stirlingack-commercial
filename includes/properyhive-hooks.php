<?php

add_filter( 'query_vars', 'propertyhive_register_query_vars' );
function propertyhive_register_query_vars( $vars )
{
    $vars[] = 'property_search_criteria';
    return $vars;
}

add_action( 'init', 'propertyhive_add_rewrite_rules' );
function propertyhive_add_rewrite_rules()
{
    global $wp_rewrite;

    $post = get_post( ph_get_page_id('search_results') );

    if ( $post instanceof WP_Post )
    {
        add_rewrite_rule( $post->post_name . "/(.*)/{$wp_rewrite->pagination_base}/([0-9]{1,})/?$", 'index.php?post_type=property&property_search_criteria=$matches[1]&paged=$matches[2]', 'top' );
        add_rewrite_rule( $post->post_name . "/(.*)/?$", 'index.php?post_type=property&property_search_criteria=$matches[1]', 'top' );
    }
}

add_action( 'parse_request', 'propertyhive_parse_request' );
function propertyhive_parse_request($wp_query)
{
    // First we do redirect if on the search page and have received the standard query string parameters
    if ( !is_admin() && !isset($wp_query->query_vars['property']) && isset($wp_query->query_vars['post_type']) && $wp_query->query_vars['post_type'] == 'property' && !isset($wp_query->query_vars['p']) )
    {
        $new_url_segments = array();
        if ( !empty($_GET) )
        {
            foreach ( $_GET as $key => $value )
            {
                if ( trim($value) != '' )
                {
                    $new_url_segments[] = $key . '/' . urlencode($value);
                }
            }
            if ( !empty($new_url_segments) )
            {
                wp_redirect( get_permalink( ph_get_page_id('search_results') ) . implode("/", $new_url_segments) . '/', 301 );
                exit();
            }
        }
    }

    // Now parse nice SEO URL back into $_GET
    foreach ($wp_query->query_vars as $name => $value)
    {
        if ($name == 'property_search_criteria' && $value != '')
        {
            // Split property search criteria into blocks:
            // department/X
            // minimum_price/X
            // etc
            $segments = array_map(
                function($value) {
                    return implode('/', $value);
                },
                array_chunk(explode('/', $value), 2)
            );

            // Now turn these into $_GET and $_REQUEST
            foreach ($segments as $segment)
            {
                $explode_segment = explode('/', $segment);
                $_GET[$explode_segment[0]] = urldecode($explode_segment[1]);
                $_REQUEST[$explode_segment[0]] = urldecode($explode_segment[1]);
            }
        }
    }
}

/**
 * Change static text in the plugin
 * @param $translation
 * @param $text
 * @param $domain
 * @return string
 */
function sa_wphive_gettext($translation, $text, $domain) {
    if($domain == 'propertyhive') {
        switch ($text) {
            case 'No preference':
                $translation = 'Select';

                break;

            case 'Add To Shortlist':
                $translation = '<i class="fa-regular fa-heart"></i><span>Save</span>';

                break;

            case 'Remove From Shortlist':
                $translation = '<i class="fa-solid fa-heart"></i><span>Saved</span>';
                break;

            case 'Loading':
                $translation = '<i class="fa-solid fa-loader fa-spin"></i>';
                break;
        }
    }
    return $translation;
}
add_filter('gettext','sa_wphive_gettext',50,3);

function include_off_market( $q )
{
    if ( is_admin() )
        return;

    if ( ! $q->is_main_query() )
        return;

    if  ( ! $q->is_post_type_archive( 'property' ) && ! $q->is_tax( get_object_taxonomies( 'property' ) ) )
        return;

    if ( isset($_GET['marketing_flag']) && $_GET['marketing_flag'] == 73 ) // 123 is our marketing flag ID
    {
        // we're filtering by sold properties

        $meta_query = $q->meta_query;

        $new_meta_query = array();
        foreach ( $meta_query as $meta_query_part )
        {
            if ( isset($meta_query_part['key']) && $meta_query_part['key'] == '_on_market' )
            {
                // we don't want this part so do nothing
            }
            else
            {
                $new_meta_query[] = $meta_query_part;
            }
        }

        $q->set('meta_query', $new_meta_query);
    }
}
//add_action( 'pre_get_posts', 'include_off_market' );

/**
 * Admin Settings: Add image field for property office
 * @param $args
 * @return mixed
 */
function sa_property_admin_office_fields($args) {
    $current_id = empty( $_REQUEST['id'] ) ? '' : (int)$_REQUEST['id'];

    $image_field = [[
        'title' => 'Image',
        'id' => '_office_image',
        'type' => 'image',
        'default' => get_post_meta($current_id, '_office_image', true),
        'desc_tip' => false
    ]];

    array_splice( $args, 7, 0, $image_field );

    return $args;
}
add_filter('propertyhive_office_details_settings','sa_property_admin_office_fields',10);

/**
 * Admin Settings: Save image field for property office
 * @param $office_post_id
 */
function sa_property_admin_office_save($office_post_id) {
    update_post_meta($office_post_id, '_office_image', $_POST['_office_image']);
}
add_action('propertyhive_save_office', 'sa_property_admin_office_save', 10);

/*********************************************************************
 ********************* Properties Search *****************************
 *********************************************************************/

/**
 * Add Font Awesome Icons to Pagination
 * @param $args
 * @return mixed
 */
function sa_propertyhive_pagination_args($args) {
    $args['prev_text'] = '<i class="fa-regular fa-angle-left"></i>';
    $args['next_text'] = '<i class="fa-regular fa-angle-right"></i>';

    return $args;
}
add_filter('propertyhive_pagination_args','sa_propertyhive_pagination_args');

/**
 * Remove default styles
 * @param $styles
 * @return mixed
 */
function sa_propertyhive_enqueue_styles($styles) {
    unset($styles['propertyhive-general']);

    return $styles;
}
add_filter('propertyhive_enqueue_styles','sa_propertyhive_enqueue_styles');

/**
 * Properties listing item inner wrap div
 */
function sa_before_search_results_loop_item() {
    echo '<div class="property-item-inner">';
}
add_action('propertyhive_before_search_results_loop_item','sa_before_search_results_loop_item');

/**
 * Properties listing item inner wrap end div
 */
function sa_after_search_results_loop_item() {
    echo '</div>';
}
add_action('propertyhive_after_search_results_loop_item','sa_after_search_results_loop_item');

/**
 * Remove action from property item
 */
//remove_action('propertyhive_after_search_results_loop_item_title','propertyhive_template_loop_actions',30);
//remove_action('propertyhive_after_search_results_loop_item_title','propertyhive_template_loop_price',10);

/**
 * Display residential details for property item
 */
function sa_property_residential_details() {
    global $property;

    Timber::render('templates/propertyhive/parts/residential-details.twig',[
        'bedrooms' => $property->bedrooms,
        'bathrooms' => $property->bathrooms,
        'reception_rooms' => $property->reception_rooms
    ]);
}
add_action('propertyhive_after_search_results_loop_item_title','sa_property_residential_details',7);

function sa_property_loop_shortlist() {
    global $post;

    $css_class = ['button','sa-shortlist-button'];

    $explode_shortlist = ( isset($_COOKIE['propertyhive_shortlist']) ) ? explode("|", $_COOKIE['propertyhive_shortlist']) : array();

    if ( ($key = array_search($post->ID, $explode_shortlist)) !== FALSE ) {
        $css_class[] = 'property-shortlisted';
    }

    echo do_shortcode('[shortlist_button class="' . join(' ', $css_class) . '"]');
}
add_action('propertyhive_after_search_results_loop_item_title','sa_property_loop_shortlist',8);

function sa_property_loop_residential_start_block() {
    echo '<div class="d-flex justify-content-between">';
}
add_action('propertyhive_after_search_results_loop_item_title','sa_property_loop_residential_start_block',6);

function sa_property_loop_residential_end_block() {
    echo '</div>';
}
add_action('propertyhive_after_search_results_loop_item_title','sa_property_loop_residential_end_block',9);

/**
 * Display branch details for property item
 */
function sa_property_item_branch_details() {
    Timber::render('templates/propertyhive/parts/branch-details.twig',[]);
}
add_action('propertyhive_after_search_results_loop_item_title','sa_property_item_branch_details',20);

/**
 * Show title and subtitle for property item
 */
function sa_property_loop_item_title() {
    global $property;

    $subtitle_parts = [];

    if($property->bedrooms) {
        $subtitle_parts[] = $property->bedrooms . ' Bed';
    }

    if($property->property_type) {
        $subtitle_parts[] = $property->property_type;
    }

    if(sizeof($subtitle_parts) > 0) {
        echo '<div class="property-item-subtitle">'.join(', ',$subtitle_parts).'</div>';
    }
    echo '<h6><a href="' . get_the_permalink() . '">' . get_the_title() . '</a></h6>';
}
add_action('propertyhive_after_search_results_loop_item_title','sa_property_loop_item_title',10);

/**
 * Display images count for property item
 */
function sa_property_item_images_count() {
    global $property;

    if(!empty($property->_photo_urls)) {
        echo '<div class="property-images-count"><i class="fa-light fa-camera"></i> ' . sizeof($property->_photo_urls) . '</div>';
    }
}
add_action('propertyhive_before_search_results_loop_item_title','sa_property_item_images_count',20);

/**
 * Hide Archive Title
 */
add_filter('propertyhive_show_page_title','__return_false');

function sa_property_search_info_wrap_start() {
    echo '<div class="property-search-info-wrap"><div class="container"><div class="property-search-info-wrap--inner">';
}
add_action('propertyhive_before_search_results_loop','sa_property_search_info_wrap_start',15);

function sa_property_search_info_wrap_end() {
    echo '</div></div></div>';
}
add_action('propertyhive_before_search_results_loop','sa_property_search_info_wrap_end',100);

/**
 * Add Grid view to the list of views on property search page
 * @param $views
 * @return mixed
 */
function sa_property_results_views($views) {
    $new_order_view = [];

    unset($views['list']['default']);

    $views['grid'] = array(
        'default' => true,
        'content' => '<i class="fa-regular fa-border-all"></i> Tile View'
    );
    $views['list']['content'] = '<i class="fa-regular fa-bars-staggered"></i> List View';
    $views['map']['content'] = '<i class="fa-regular fa-map"></i> Map View';

    $new_order_view['grid'] = $views['grid'];
    $new_order_view['list'] = $views['list'];
    $new_order_view['map'] = $views['map'];

    return $new_order_view;
}
add_filter('propertyhive_results_views', 'sa_property_results_views', 1);

/**
 * Add breadcrumbs to property pages
 */
function sa_properties_breadcrumbs() {
    if ( function_exists('yoast_breadcrumb') ) {
        yoast_breadcrumb( '<div id="breadcrumbs"><div class="container">','</div></div>' );
    }
}
add_action('propertyhive_before_main_content','sa_properties_breadcrumbs',10);

$save_search = PH_Save_Search::instance();
remove_action( 'propertyhive_before_search_results_loop', array( $save_search, 'save_search_button' ), 99 );

/*function test_button() {
    $save_search = PH_Save_Search::instance();

    ob_start();
    $save_search->save_search_button();
    $html =  ob_get_contents();
    ob_end_clean();

    echo '<div>'.$html.'</div>';
}*/
//add_action('property_search_form_control_end', 'test_button', 10);

/**
 * Wrap Map View on results page - start block
 */
function sa_property_search_map_wrap_start() {
    if(get_query_var('post_type') == 'property' && $_GET['view'] == 'map') {
        echo '<div class="property-search-map-view"><div class="container">';
    }
}
add_action('propertyhive_before_search_results_loop','sa_property_search_map_wrap_start',100);

/**
 * Wrap Map View on results page - end block
 */
function sa_property_search_map_wrap_end() {
    if(get_query_var('post_type') == 'property' && $_GET['view'] == 'map') {
        echo '</div></div>';
    }
}
add_action('propertyhive_after_search_results_loop','sa_property_search_map_wrap_end',10);

/*********************************************************************
 *********************** Property Detail *****************************
 *********************************************************************/

function sa_property_detail_wrap_start() {
    if(is_singular('property')) {
        echo '<section class="page-section page-section--padding50"><div class="container">';
    }
}
add_action('propertyhive_before_main_content','sa_property_detail_wrap_start',20);

function sa_property_detail_wrap_end() {
    if(is_singular('property')) {
        echo '</div></section>';
    }
}
add_action('propertyhive_after_main_content','sa_property_detail_wrap_end',50);

/**
 * Property Detail: Display calculators
 */
function sa_property_detail_calculators() {
    $content = Timber::context();

    /*$content['property_id'] = get_the_ID();
    $content['property_search_link'] = get_the_permalink(ph_get_page_id('search_results'));*/

    Timber::render('propertyhive/shortcode/calculators.twig', $content);
}
add_action('propertyhive_after_main_content','sa_property_detail_calculators',50);

/**
 * Property Detail: Display similar properties
 */
function sa_property_detail_similar_properties() {
    $content = Timber::context();

    $content['property_id'] = get_the_ID();
    $content['property_search_link'] = get_the_permalink(ph_get_page_id('search_results'));

    Timber::render('propertyhive/shortcode/similar-properties.twig', $content);
}
add_action('propertyhive_after_main_content','sa_property_detail_similar_properties',60);

/**
 * Property Detail: Display similar properties
 */
function sa_property_detail_related_insights() {
    global $property;

    $context = Timber::context();

    $posts = new Timber\PostQuery([
        'post_type' => 'post',
        'posts_per_page' => 3
    ]);

    Timber::render('components/static-sections/latest-posts.twig', [
        'title' => 'Property Insights',
        'more_link' => [
            'title' => 'More News & Insights for ' . $property->_address_postcode,
            'url' => get_the_permalink($context['options']['page_url']['blog_page'])
        ],
        'posts' => $posts->get_posts(),
        'theme' => [
            'link' => get_template_directory_uri()
        ]
    ]);
}
add_action('propertyhive_after_main_content','sa_property_detail_related_insights',70);

/*function property_detail_heading() {
    global $property;

    Timber::render('propertyhive/property-detail/heading.twig', [
        'title' => get_the_title(),
        'price' => $property->get_formatted_price()
    ]);
}*/
//add_action('propertyhive_before_single_property_summary','property_detail_heading',2);

function sa_property_detail_tabs_nav() {
    global $property;

    Timber::render('propertyhive/property-detail/tabs-nav.twig',[
        'floorplan_urls' => $property->_floorplan_urls,
        'epc_urls' => $property->_epc_urls
    ]);
}
add_action('propertyhive_after_single_property_summary','sa_property_detail_tabs_nav',30);

function sa_property_detail_tabs_content() {
    if(is_singular('property')) {
        global $property;

        Timber::render('propertyhive/property-detail/tabs-content.twig',[
            'floorplan_urls' => $property->_floorplan_urls,
            'epc_urls' => $property->_epc_urls,
            'department' => $property->_department,
            'property_type' => $property->get_property_type(),
            'available_date' => $property->get_available_date(),
            'furnished' => $property->get_furnished()
        ]);
    }
}
add_action('propertyhive_after_main_content','sa_property_detail_tabs_content');

remove_action('propertyhive_after_single_property_summary','propertyhive_template_single_actions',10);
remove_action('propertyhive_after_single_property_summary','propertyhive_template_single_features',20);
remove_action('propertyhive_after_single_property_summary','propertyhive_template_single_summary',30);
remove_action('propertyhive_after_single_property_summary','propertyhive_template_single_description',40);
add_action('propertyhive_single_property_summary','propertyhive_template_single_summary',40);
add_action('propertyhive_single_property_summary','sa_property_residential_details',20);
remove_action('propertyhive_single_property_summary','propertyhive_template_single_meta',20);

function sa_property_detail_heading_start_block() {
    echo '<div class="property-detail-heading">';
}
add_action('propertyhive_single_property_summary','sa_property_detail_heading_start_block',3);

function sa_property_detail_heading_end_block() {
    echo '</div>';
}
add_action('propertyhive_single_property_summary','sa_property_detail_heading_end_block',15);

function sa_property_detail_status_shortlisted() {
    $flag_html = '';
    ob_start();
    $template_assistant = PH_Template_Assistant::instance();
    $template_assistant->add_flag_single();
    $flag_html = ob_get_contents();
    ob_end_clean();

    Timber::render('propertyhive/property-detail/status-shortlisted.twig',[
        'flag_html' => $flag_html
    ]);
}
add_action('propertyhive_single_property_summary','sa_property_detail_status_shortlisted',18);

$template_assistant = PH_Template_Assistant::instance();
remove_action( 'propertyhive_before_single_property_images', array( $template_assistant, 'add_flag_single' ), 5 );

function sa_property_detail_back_button() {
    if(is_singular('property')) {
        echo '<div class="property-detail-back-action-wrap"><a href="javascript:;" onclick="history.back();"><i class="fa-regular fa-angle-left"></i> BACK TO SEARCH RESULTS</a></div>';
    }
}
add_action('propertyhive_single_property_summary','sa_property_detail_back_button',2);

$rental_yield_calculator = PH_Rental_Yield_Calculator::instance();
remove_action( 'wp_enqueue_scripts', array($rental_yield_calculator,'load_rental_yield_calculator_styles'));

$stamp_duty_calculator = PH_Stamp_Duty_Calculator::instance();
remove_action( 'wp_enqueue_scripts', array( $stamp_duty_calculator, 'load_stamp_duty_calculator_styles' ) );
