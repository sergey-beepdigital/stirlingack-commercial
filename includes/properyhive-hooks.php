<?php

/**
 * Change radius search value
 */
function set_default_radius() {
    if(is_admin()) return;

    if ( !isset($_GET['radius']) || empty($_GET['radius']) ) {
        $_GET['radius'] = 0.75;
        $_REQUEST['radius'] = 0.75;
    }
}
add_action( 'init', 'set_default_radius' );

/**
 * Save new homes from the import to db
 * @param $post_id
 * @param $property
 */
function DEPREACTED_pickup_new_property($post_id, $property) {
    //update_post_meta($post_id, '_new_home', ((isset($property->New) && $property->New == '1') ? 'yes' : ''));
    $new = '';

    if(isset($property->ID)) {
        $property_id_parts = array_reverse(explode('-',$property->ID));
        $property_id_office_part = reset($property_id_parts);

        if(substr($property_id_office_part,0,3) == 'NEW') {
            $new = 'yes';
        }
    }

    update_post_meta($post_id, '_new_home', $new);
}

function sa_save_custom_meta_property($post_id, $property) {
    $new = $office_id = '';

    if(!empty($property->Age) && in_array('New',$property->Age)) {
        $new = 'yes';
    }

    if(!empty($property->Office) && !empty($property->Office->ID)) {
        $office_id = $property->Office->ID;
    }

    update_post_meta($post_id, '_new_home', $new);
    update_post_meta($post_id, '_office_id', $office_id);
}
add_action("propertyhive_property_imported_jet", "sa_save_custom_meta_property", 10, 2);


add_filter( 'propertyhive_jet_property_fields', 'include_available_field' );
function include_available_field( $fields )
{
    $fields[] = 'Available';
    return $fields;
}

add_filter( 'propertyhive_jet_sales_criteria', 'unavailable_properties_also' );
function unavailable_properties_also( $criteria )
{
    $criteria['PropertyStatus'] = array('for sale', 'under offer'/*, 'sold'*/);
    $criteria['Unavailable'] = true;
    return $criteria;
}

// Only import:
// - All available properties
// - Unavailable properties with status 'Sold'
add_filter( "propertyhive_jet_properties_due_import", 'sort_properties_to_import' );
function sort_properties_to_import($properties)
{
    $new_properties = array();
    foreach ( $properties as $property )
    {
        if ( isset($property->Available) && (string)$property->Available == '1' )
        {
            // do as we normally do as this is an available property
            $new_properties[] = $property;
        }
        else
        {
            // this isn't an available property. only import if status is 'Under Offer'
            if ( isset($property->Status) && (/*$property->Status == 'Sold' || */$property->Status == 'Under Offer') )
            {
                $new_properties[] = $property;
            }
        }
    }
    return $new_properties;
}

add_action( "propertyhive_property_imported_jet", 'correct_sold_unavailable_status', 10, 2 );
function correct_sold_unavailable_status($post_id, $property)
{
    if ( !isset($property->Available) || ( isset($property->Available) && (string)$property->Available != '1' ) )
    {
        // this property is unavailable ...

        if ( isset($property->Status) && ($property->Status == 'Sold' || $property->Status == 'Under Offer') )
        {
            // ... and sold
            wp_suspend_cache_invalidation( false );
            wp_defer_term_counting( false );
            wp_defer_comment_counting( false );

            if ( $property->department == 'residential-lettings' ) {
                wp_set_object_terms( $post_id, 10, 'availability' ); // CHANGE 10 TO BE THE TERM ID YOU WANT TO USE FOR UNDER OFFER UNAVAILABLE LETTINGS PROPERTIES
            }
            else
            {
                wp_set_object_terms( $post_id, 10, 'availability' ); // CHANGE 10 TO BE THE TERM ID YOU WANT TO USE FOR UNDER OFFER UNAVAILABLE SALES PROPERTIES
            }

            wp_suspend_cache_invalidation( true );
            wp_defer_term_counting( true );
            wp_defer_comment_counting( true );
        }
    }
}

/**
 * Create custom URL structure for property detail page
 * @param $post_link
 * @param $post
 * @param $leavename
 * @param $sample
 * @return string|string[]
 */
function customise_property_post_type_link( $post_link, $post, $leavename, $sample ) {
    if ( get_post_type($post->ID) == 'property' )
    {
        $property = new PH_Property($post->ID);

        $suffix = 'for-sale';
        if ( $property->department == 'residential-lettings' )
        {
            $suffix = 'to-rent';
        }

        $area = $property->address_three;
        if ( $area == '' )
        {
            $area = $property->address_four;
        }
        if ( $area == '' )
        {
            $area = $property->address_two;
        }
        if ( $area == '' )
        {
            $area = 'property';
        }

        $post_link = str_replace("/property/", "/property-" . $suffix . "/" . sanitize_title($area) . "/", $post_link);
    }

    return $post_link;
}
add_filter( 'post_type_link', 'customise_property_post_type_link', 10, 4 );


/**
 * Add rewrite rules for custom URL structure for property detail page
 */
function rewrites_init() {
    add_rewrite_rule(
        'property-for-sale/([^/]+)/([^/]+)/?$',
        'index.php?post_type=property&name=$matches[2]',
        'top' );
    add_rewrite_rule(
        'property-to-rent/([^/]+)/([^/]+)/?$',
        'index.php?post_type=property&name=$matches[2]',
        'top' );
}
add_action( 'init', 'rewrites_init' );


//add_filter( 'query_vars', 'propertyhive_register_query_vars' );
function propertyhive_register_query_vars( $vars )
{
    $vars[] = 'property_search_criteria';
    return $vars;
}


//add_action( 'init', 'propertyhive_add_rewrite_rules' );
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


//add_action( 'parse_request', 'propertyhive_parse_request' );
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

            case 'Save Search':
                $translation = '<i class="fa-regular fa-bell"></i> ' . $translation;
                break;

            case 'Remove Saved Search':
                $translation = 'Delete Search';
                break;
        }
    }
    return $translation;
}
add_filter('gettext','sa_wphive_gettext',50,3);


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
    global $property;

    $branch = new SA_PropertyBranch($property);

    Timber::render('templates/propertyhive/parts/branch-details.twig',[
        'branch_data' => $branch->get_data(),
        'property' => $property
    ]);
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
    } else {
        $subtitle_parts[] = 'Studio';
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
 * Relocation for save search button
 */
$save_search = PH_Save_Search::instance();
remove_action( 'propertyhive_before_search_results_loop', array( $save_search, 'save_search_button' ), 99 );
add_action( 'property_search_after_form', array( $save_search, 'save_search_button' ), 50 );


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
remove_action( 'propertyhive_product_thumbnails', 'propertyhive_show_property_thumbnails', 20 );

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
    if(!is_property()) return;

    global $property;

    $content = Timber::context();

    /*$content['property_id'] = get_the_ID();
    $content['property_search_link'] = get_the_permalink(ph_get_page_id('search_results'));*/

    if($property->_department == 'residential-sales') {
        Timber::render('propertyhive/shortcode/calculators.twig', $content);
    } else {
        Timber::render('components/sections/separator.twig');
    }
}
add_action('propertyhive_after_main_content','sa_property_detail_calculators',50);


/**
 * Property Detail: Display similar properties
 */
function sa_property_detail_similar_properties() {
    if(!is_property()) return;

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
    if(!is_property()) return;

    global $property;

    $context = Timber::context();

    $branch = new SA_PropertyBranch($property);
    $branch_data = $branch->get_data();

    Timber::render('components/static-sections/latest-posts.twig', [
        'title' => 'Property Insights',
        'more_link' => [
            'title' => 'More News & Insights for ' . $branch_data['title'],
            'url' => get_the_permalink($context['options']['page']['blog_list_page_id']) . '?branch_id=' . $branch_data['id']
        ],
        'posts' => $branch->get_insights(),
        'theme' => [
            'link' => get_template_directory_uri()
        ]
    ]);
}
add_action('propertyhive_after_main_content','sa_property_detail_related_insights',70);


/**
 * Create tabs for property information
 */
function sa_property_detail_tabs_nav() {
    global $property;

    Timber::render('propertyhive/property-detail/tabs-nav.twig',[
        'floorplan_urls' => $property->_floorplan_urls,
        'epc_urls' => $property->_epc_urls
    ]);
}
//add_action('propertyhive_after_single_property_summary','sa_property_detail_tabs_nav',30);


/**
 * Display property information in tabs
 */
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


/**
 * Display additional links for Let properties
 * Example: Fees page link
 */
function sa_property_let_links() {
    global $property;

    if ($property->department == 'residential-lettings' && get_option('propertyhive_lettings_fees_display_single_property', '') == 'yes') {
        echo '<div class="property-let-additional-links">';
        echo '<a data-fancybox data-src="#lettings_fees_lightbox" href="javascript:;">Permitted Payments</a>';
        echo '<div id="lettings_fees_lightbox" style="display:none; max-width:900px">
        <h3 class="mb-3">Permitted Payments</h3>
        ' . nl2br(get_option('propertyhive_lettings_fees')) . '
        </div>';
        echo '</div>';
    }
}
add_action('propertyhive_single_property_summary','sa_property_let_links',19);

function sa_property_detail_heading_start_block() {
    echo '<div class="property-detail-heading">';
}
add_action('propertyhive_single_property_summary','sa_property_detail_heading_start_block',3);

function sa_property_detail_heading_end_block() {
    echo '</div>';
}
add_action('propertyhive_single_property_summary','sa_property_detail_heading_end_block',15);


/**
 * Show property status and shortlist button as columns in new section
 */
function sa_property_detail_shortlisted_button() {
    /*$flag_html = '';
    ob_start();
    $template_assistant = PH_Template_Assistant::instance();
    $template_assistant->add_flag_single();
    $flag_html = ob_get_contents();
    ob_end_clean();*/

    Timber::render('propertyhive/property-detail/status-shortlisted.twig',[
        //'flag_html' => $flag_html
    ]);
}
add_action('propertyhive_single_property_summary','sa_property_detail_shortlisted_button',45);


/**
 * Remove property status from the images section
 */
$template_assistant = PH_Template_Assistant::instance();
remove_action( 'propertyhive_before_single_property_images', array( $template_assistant, 'add_flag_single' ), 5 );


/**
 * Add back to search results link
 */
function sa_property_detail_back_button() {
    if(is_singular('property')) {
        echo '<div class="property-detail-back-action-wrap"><a href="javascript:;" onclick="history.back();"><i class="fa-regular fa-angle-left"></i> BACK TO SEARCH RESULTS</a></div>';
    }
}
add_action('propertyhive_before_single_property_summary','sa_property_detail_back_button',2);
//add_action('propertyhive_single_property_summary','sa_property_detail_back_button',2);


/**
 * PH_Rental_Yield_Calculator: Remove plugin styles
 */
$rental_yield_calculator = PH_Rental_Yield_Calculator::instance();
remove_action( 'wp_enqueue_scripts', array($rental_yield_calculator,'load_rental_yield_calculator_styles'));


/**
 * PH_Rental_Yield_Calculator: Remove plugin styles
 */
$stamp_duty_calculator = PH_Stamp_Duty_Calculator::instance();
remove_action( 'wp_enqueue_scripts', array( $stamp_duty_calculator, 'load_stamp_duty_calculator_styles' ) );

function sa_property_search_checkboxes() {
    $new_home_checked = $recently_sold_checked = '';
    $new_home_display = $recently_sold_display = 'inline-block';
    if(!empty($_REQUEST['department']) && $_REQUEST['department'] != 'residential-sales') {
        $new_home_display = $recently_sold_display = 'none';
    }

    if($_REQUEST['new_home'] == 1) {
        $new_home_checked = 'checked';
    }

    if($_REQUEST['include_sold'] == 1) {
        $recently_sold_checked = 'checked';
    } ?>

    <div class="property-search-form--checkbox-group">
        <div class="custom-control custom-checkbox custom-control-inline" style="display: <?php echo $recently_sold_display; ?>">
            <input type="checkbox" class="custom-control-input" name="include_sold" value="1" <?php echo $recently_sold_checked; ?> id="include-recent-props-checkbox">
            <label class="custom-control-label" for="include-recent-props-checkbox">Include Recently Sold Properties</label>
        </div>
        <div class="custom-control custom-checkbox custom-control-inline" style="display: <?php echo $new_home_display; ?>">
            <input type="checkbox" class="custom-control-input" name="new_home" value="1" <?php echo $new_home_checked; ?> id="new-homes-checkbox">
            <label class="custom-control-label" for="new-homes-checkbox">New Homes Only</label>
        </div>
        <!--<div class="form-check form-check-inline">
            <input class="form-check-input" type="checkbox" name="include_sold" value="1" <?php /*echo $recently_sold_checked; */?> id="include-recent-props-checkbox">
            <label class="form-check-label" for="include-recent-props-checkbox">Include Recently Sold Properties</label>
        </div>
        <div class="form-check form-check-inline" style="display: <?php echo $new_home_display; ?>">
            <input class="form-check-input" type="checkbox" name="new_home" value="1" <?php echo $new_home_checked; ?> id="new-homes-checkbox">
            <label class="form-check-label" for="new-homes-checkbox">New Homes Only</label>
        </div>-->
    </div>
<?php }
//TODO: Back for display checkboxes in search form
//add_action('property_search_before_end_form','sa_property_search_checkboxes',10);

function sg_get_properties_query($q) {
    if (is_admin())
        return;

    if (!$q->is_main_query())
        return;

    if (!$q->is_post_type_archive('property') && !$q->is_tax(get_object_taxonomies('property')))
        return;

    if (isset($_GET['shortlisted']))
        return;

    $meta_query = $q->get('meta_query');

    if (isset($_REQUEST['new_home'])) {
        $meta_query[] = array(
            'key' => '_new_home',
            'value' => 'yes'
        );
    }

    $q->set('meta_query', $meta_query);
}
add_action('pre_get_posts', 'sg_get_properties_query');


/**
 * Remove Sold Properties by default for search pages
 * @param $q
 */
function remove_sold_properties_by_default($q) {
    if (is_admin())
        return;

    if (!$q->is_main_query())
        return;

    if (!$q->is_post_type_archive('property') && !$q->is_tax(get_object_taxonomies('property')))
        return;

    if (isset($_GET['shortlisted']))
        return;

    $tax_query = $q->get('tax_query');

    if (!isset($_REQUEST['include_sold'])) {
        $tax_query[] = array(
            'taxonomy' => 'availability',
            'field' => 'term_id',
            'terms' => array(4, 5), // 4 - Sold, 5 - Sold STC
            'operator' => 'NOT IN'
        );
    }

    $q->set('tax_query', $tax_query);
}
add_action('pre_get_posts', 'remove_sold_properties_by_default');


/**
 * Remove Hidden Checkboxes for search form
 * @param $form_controls
 * @return mixed
 */
function remove_sold_new_homes_hidden($form_controls) {
    if (isset($form_controls['include_sold'])) { unset($form_controls['include_sold']); }

    if (isset($form_controls['new_home'])) { unset($form_controls['new_home']); }

    return $form_controls;
}
add_filter( 'propertyhive_search_form_fields_after', 'remove_sold_new_homes_hidden', 10, 1 );


/**
 * Display custom property item template for New Home detail page
 * @param $template
 * @param $slug
 * @param $name
 * @return string
 */
function sa_new_home_item_template($template, $slug, $name) {
    if(is_singular('sa_new_home')){
        $template = TEMPLATEPATH . '/propertyhive/new-home-content-property.php';
    }

    return $template;
}
add_filter('ph_get_template_part','sa_new_home_item_template',10,3);


/**
 * Add custom description meta tag for property detail page
 */
function sa_property_meta_description() {
    if(is_singular('property')) {
        global $property;

        $description_parts = [];

        $description_parts[] = $property->post_title;
        if($property->bedrooms) {
            $description_parts[] = $property->bedrooms;
            $description_parts[] = 'bedroom';
        }
        $description_parts[] = 'property for';
        $description_parts[] = str_replace(['-'], ' ', $property->department);
        $description_parts[] = 'in';
        $description_parts[] = $property->get_formatted_summary_address();

        echo "<meta name='description' content='".join(' ',$description_parts)."' />";
    }
}
add_action('wp_head', 'sa_property_meta_description');


/**
 * Custom color options for map view on property listing page
 * @param $options
 * @return mixed
 */
function sa_map_search_draw_options($options) {
    $options['stroke_weight'] = 2;
    $options['fill_color'] = '#151E46';
    $options['fill_opacity'] = 0.1;
    $options['stroke_color'] = '#151E46';

    return $options;
}
add_filter('propertyhive_map_search_draw_options','sa_map_search_draw_options');

/**
 * Change amount of properties on listing page
 * @return int
 */
function sa_loop_search_results_per_page() {
    return 14;
}
add_filter( 'loop_search_results_per_page', 'sa_loop_search_results_per_page', 20 );

function sa_featured_properties_shortcode_output($output) {
    return str_replace('ul','div',$output);
}
add_filter('propertyhive_featured_properties_shortcode_output','sa_featured_properties_shortcode_output');
