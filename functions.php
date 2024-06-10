<?php

require_once 'includes/help-functions.php';
require_once 'includes/classes/class.mailer.php';
//require_once 'includes/classes/class.property-branch.php';

include "includes/shortcodes.php";
//include "includes/properyhive-hooks.php";

//include_once 'includes/classes/class.workable-api.php';
//include_once 'includes/classes/class.workable-ajax.php';

add_filter('https_ssl_verify', '__return_false');

/**
 * Registers any plugin dependancies the theme has.
 *
 * Requires TGMPA
 */
function register_plugins () {
	$plugins = array(
		/* Register any required plugins:
		array(
			'name'               => 'Example Plugin', // Required. The plugin name.
			'slug'               => 'example-plugin', // Requried. The plugin slug (typically the folder name).
			'source'             => 'http://example-plugin.com', // The plugin source. Often a .zip file. Do not include this if the plugin is from the Wordpress Repository.
			'required'           => true, // If false, the plugin is only 'recommended' instead of required.
			'version'            => '', // E.g. 1.0.0. If set, the active plugin must be this version or higher. If the plugin version is higher than the plugin version installed, the user will be notified to update the plugin.
			'force_activation'   => false, // If true, plugin is activated upon theme activation and cannot be deactivated until theme switch.
			'force_deactivation' => false, // If true, plugin is deactivated upon theme switch, useful for theme-specific plugins.
			'external_url'       => '', // If set, overrides default API URL and points to an external URL.
			'is_callable'        => '', // If set, this callable will be be checked for availability to determine if a plugin is active.
        ),*/
        array(
            'name' => 'Timber',
            'slug' => 'timber-library',
            'required' => true,
            'force_activation' => true
        ),
		array(
			'name' => 'Advanced Custom Fields Pro',
            'slug' => 'advanced-custom-fields-pro',
            'source' => get_template_directory_uri() . '/includes/plugins/advanced-custom-fields-pro.zip',
			'required' => true,
            'force_activation' => true
        ),
        /*array(
            'name' => 'Advanced Custom Fields: Font Awesome Field',
            'slug' => 'advanced-custom-fields-font-awesome',
            'required' => true,
            'force_activation' => true
        ),*/
        array(
            'name' => 'Yoast SEO',
            'slug' => 'wordpress-seo',
            'required' => true,
            'force_activation' => true
        ),
        array(
            'name' => 'Safe SVG',
            'slug' => 'safe-svg',
            'required' => true,
            'force_activation' => true
        ),
        array(
            'name' => 'WPS Hide Login',
            'slug' => 'wps-hide-login',
            'required' => false
        )
	);
	register_required_plugins ($plugins);
}

// Plugin Dependancies
require_once('includes/required-plugins/class-tgm-plugin-activation.php');
require_once('includes/required-plugins/register-plugin.php');

if ( is_admin() && function_exists('register_required_plugins')) {
    add_action ('tgmpa_register', 'register_plugins');
}

require_once 'includes/cache_bust.php';
function get_cache_ver() {
    include 'includes/cache_bust.php';
    return $cache_ver;
}

if ( ! class_exists( 'Timber' ) ) {
    add_action( 'admin_notices', function() {
        echo '<div class="error"><p>Timber not activated. Make sure you activate the plugin in <a href="' . esc_url( admin_url( 'plugins.php#timber' ) ) . '">' . esc_url( admin_url( 'plugins.php' ) ) . '</a></p></div>';
    } );
    return;
}

Timber::$dirname = array('templates', 'components');

class StarterSite extends TimberSite {

    function __construct() {
        add_theme_support( 'post-formats',[] );
        add_theme_support( 'post-thumbnails' );
        add_theme_support( 'menus' );

        // Timber filters
        add_filter( 'timber_context', array( $this, 'add_to_context' ) );
        add_filter( 'get_twig', array( $this, 'add_to_twig' ) );
        add_filter( 'upload_mimes', array($this, 'svg_mime_types' ));

        // Comment out to Enable oEmbed (responsible for embedding twitter etc)
        remove_action( 'wp_head', 'wp_oembed_add_discovery_links', 10 );
        remove_action('wp_head', 'wp_oembed_add_host_js');
        remove_action('rest_api_init', 'wp_oembed_register_route');
        remove_filter('oembed_dataparse', 'wp_filter_oembed_result', 10);

        // Header Removal
        remove_action('wp_head', 'rsd_link');
        remove_action('wp_head', 'wlwmanifest_link');
        remove_action('wp_head', 'wp_generator'); // Hide WP Version for security
        remove_action('wp_head', 'wp_shortlink_wp_head');
        remove_action('wp_head', 'rest_output_link_wp_head', 10); //Remove wp-json/ link
        add_action( 'wp_enqueue_scripts', 'bs_dequeue_dashicons' );
            function bs_dequeue_dashicons() {
                if ( ! is_user_logged_in() ) {
                    wp_deregister_style( 'dashicons' );
                }
            }



        add_filter( 'emoji_svg_url', '__return_false' );

        add_action('after_setup_theme', function () {
            add_theme_support( 'html5', ['script', 'style'] );
        });

        // Timber Actions
        add_action( 'init', array( $this, 'register_post_types' ) );
        add_action( 'init', array( $this, 'register_taxonomies' ) );
        add_action( 'init', array( $this, 'register_acf_blocks' ) );

        add_action( 'wp_enqueue_scripts', array( $this, 'assets' ) );

        // First party actions
        add_action('inline_file', array($this, 'inline_file'));
        add_action('admin_head', array($this, 'fix_svg_thumb_display'));
        add_action( 'init', 'disable_wp_emojicons' );

        // Add Advanced Custom Fields options page
        if( function_exists('acf_add_options_page') ) {
            acf_add_options_sub_page('Theme');
            acf_add_options_sub_page('Social');
            acf_add_options_sub_page('Sections');
            acf_add_options_sub_page('Analytics/Tracking');

            if (current_user_can('administrator') || get_field('show_debug_menu', 'option')) {
              acf_add_options_sub_page('Debug Options');
            }
        }

        require_once('includes/toggle_acf_edit.php');

        if (!$showacf) {
            require_once('includes/acf-edit-screen-disabler.php');
            if (function_exists('get_field')) {
                if (!get_field('enable_acf_edit', 'option')) {
                    add_filter('acf/settings/show_admin', '__return_false'); //DO NOT COMMENT OUT OR DISABLE USE THEME OPTIONS TICK BOX INSTEAD
                }
            }
        }


        parent::__construct();
    }

    function register_post_types() {
        // require_once custom post types here
        //require_once('includes/post-types/form.php');
        require_once('includes/post-types/property.php');
        //require_once('includes/post-types/new-home.php');
    }

    function register_taxonomies() {
        // require_once custom taxonomies here
    }

    function register_acf_blocks() {
        if ( ! function_exists( 'acf_register_block' ) ) {
            return;
        }
        // require_once custom acf blocks here

        // require_once('includes/blocks/example.php');
        //require_once('includes/blocks/cta-box.php');
        //require_once('includes/blocks/latest-post.php');
        //require_once('includes/blocks/promo.php');
    }

    function add_to_context( $context ) {
        $context['top_menu'] = new TimberMenu('top-nav');
        $context['menu'] = new TimberMenu('main-nav',['menu_id' => 'main-nav']);
        $context['footer_menu'] = new TimberMenu('footer-nav');

        $context['footer_widgets1'] = Timber::get_widgets('footer-widget');
        $context['footer_widgets2'] = Timber::get_widgets('footer-widget-2');
        $context['footer_widgets3'] = Timber::get_widgets('footer-widget-3');

        $context['insight_widgets'] = Timber::get_widgets('insight-widgets');

        $context['site'] = $this;
        if (function_exists('get_fields')) {
            $context['options'] = get_fields('option');
        }
        $context['page_stats'] = TimberHelper::start_timer();
        return $context;
    }

    function add_to_twig( $twig ) {
        // Add your own twig functions
        $twig->addFunction( new Twig_SimpleFunction('query_cat', array($this, 'query_cat')));
        $twig->addFilter(new Twig_SimpleFilter('json', array($this, 'json')));
        $twig->addTest(new \Twig\TwigTest('numeric', function ($value) { return  is_numeric($value); }));
        return $twig;
    }

    function assets( $twig ) {
        $google_map_api_key = get_google_map_api_key();

        // Get rid of default media element
        // wp_deregister_script('wp-mediaelement'); // Uncomment to disable Media Element
        // wp_deregister_style('wp-mediaelement'); // Uncomment to disable Media Element

        // Remove Wp's jQuery
        // wp_deregister_script('jquery'); // Uncomment to disable jQuery

        wp_enqueue_script('google-maps',"https://maps.googleapis.com/maps/api/js?key=" . $google_map_api_key);

        wp_enqueue_style('remote-flexslider','https://www.stirlingackroyd.com/wp-content/plugins/propertyhive/assets/css/flexslider.css');

        // Define globals with for cache busting
        require_once 'enqueues.php';
        require('includes/cache_bust.php');

        wp_enqueue_script( 'fancybox', 'https://cdnjs.cloudflare.com/ajax/libs/fancybox/3.5.7/jquery.fancybox.min.js', ['jquery'] );
        wp_enqueue_style( 'fancybox', 'https://cdnjs.cloudflare.com/ajax/libs/fancybox/3.5.7/jquery.fancybox.min.css' );

        $cache_ver = rand();

        //wp_enque
        wp_enqueue_script( 'essential.js', BUNDLE_JS_SRC, array(), $cache_ver, false); // These will appear at the top of the page
        wp_enqueue_script( 'deferred.js', DEFERRED_BUNDLE_JS_SRC, array(), $cache_ver, true); // These will appear in the footer

        // Enqueue a main stylesheet as a sensible default
        wp_enqueue_style( 'main.css', MAIN_CSS_SRC, array(), $cache_ver, 'all' );

        wp_localize_script('deferred.js', 'sg_config', [
            'google_maps' => [
                'api_key'     => $google_map_api_key,
                'marker_url' => get_template_directory_uri() . '/dist/images/map-marker.svg'
                //'marker_url'  => 'https://tinyurl.com/markerurl',
            ],
            'images_path' => get_template_directory_uri() . '/dist/images/'
        ]);
    }

    /**
     * Inline File
     *
     * This action will echo the contents of a file when passed a relative path, ath
     * the point the function was called.
     *
     * The intended use of this function is for inlining files within templates, for
     * example: embedding an SVG.
     */
    function inline_file($path) {
        if ( $path ) {
            echo file_get_contents($_SERVER['DOCUMENT_ROOT'] . parse_url($path)['path']);
        }
    }

    /**
     * Allows SVGs to be uploaded in the wordpress media library
     */
    function svg_mime_types( $mimes ) {
        $mimes['svg'] = 'image/svg+xml';
        return $mimes;
    }

    /**
     * Limits sizes of SVGs in WordPress backend
     */
    function fix_svg_thumb_display() {
        echo '<style> td.media-icon img[src$=".svg"], img[src$=".svg"].attachment-post-thumbnail { width: 100% !important; height: auto !important; } </style>';
    }

    /**
     * Query Cat
     * Queries passed category id's and limits results to passed limit
     *
     * This is registered as a Timber function and can be called in templates
     * with the following syntax:
     *
     *      {{ query_cat([1, 2, 3], 3) }}
     *
     * This would return posts in categories 1, 2, or 3 and limit the response
     * to 3 results.
     */
    function query_cat(
        $cats = [],
        $limit = 3,
        $post_type = 'any',
        $orderby = 'date',
        $offset = 0,
        $exclude = []
    ) {
        return Timber::get_posts(array(
            'post_type' => $post_type,
            'cat' => $cats,
            'posts_per_page' => $limit,
            'orderby' => $orderby,
            'offset' => $offset,
            'post__not_in' => $exclude
        ));
    }

    /**
     * JSON - Twig Filter
     *
     * Returns object as JSON string
     *
     * Features:
     * - Strips newline characters from String
     * - Escapes and quotes properly, preventing double-encoding of JSON data.
     *
     * Usage:
     *
     *     <script>
     *         var jsonData = '{{ twigObject|json }}';
     *     </script>
     */
    function json($o) {
        return str_replace(array('\r', '\n'), '', str_replace("\u0022","\\\\\"", json_encode($o, JSON_NUMERIC_CHECK | JSON_HEX_QUOT)));
    }

}

new StarterSite();

/*******************************************************************************
 * Global Functions
 ******************************************************************************/

/**
 * Console Log
 *
 * Takes array of strings and returns a javascript console.log.
 */
function console_log($args, $delimiter = ' ') {
    $s = '<script>console.log("';
    $s .= join($delimiter, $args);
    $s .= '")</script>';

    return $s;
}

/**
 * Stop Timber Timer
 *
 * A timer is started at the beginning of every page load that times how long it
 * takes to generate a page. This function stops the timer and reports the
 * following stats using the console_log function:
 *
 * - How long the page took to generate
 * - How many database queries did it take
 */
function stop_timber_timer() {
    $context = Timber::get_context();

    return console_log([
        'Page generated in ' . TimberHelper::stop_timer($context['page_stats']),
        get_num_queries() . ' database queries'
    ]);
}

function custom_login_style() {
  echo '<link rel="stylesheet" type="text/css" href="' . get_bloginfo('stylesheet_directory') . '/login/custom-login-styles.css" />';
}

add_action('login_head', 'custom_login_style');


/**
 * Disables Emjois in TinyMCE
 *
 * Is a filter.
 */
function disable_emojicons_tinymce( $plugins ) {
    if ( is_array( $plugins ) ) {
        return array_diff( $plugins, array( 'wpemoji' ) );
    } else {
        return array();
    }
}

/**
 * Dequeues all scripts and plugins relating to Wordpress emoji defaults
 */
function disable_wp_emojicons() {
    // all actions related to emojis
    remove_action( 'admin_print_styles', 'print_emoji_styles' );
    remove_action( 'wp_head', 'print_emoji_detection_script', 7 );
    remove_action( 'admin_print_scripts', 'print_emoji_detection_script' );
    remove_action( 'wp_print_styles', 'print_emoji_styles' );
    remove_filter( 'wp_mail', 'wp_staticize_emoji_for_email' );
    remove_filter( 'the_content_feed', 'wp_staticize_emoji' );
    remove_filter( 'comment_text_rss', 'wp_staticize_emoji' );

    // filter to remove TinyMCE emojis
    add_filter( 'tiny_mce_plugins', 'disable_emojicons_tinymce' );
}

/**
 * Ajax Forms
 *
 * A function to handle the ajax submission of the various flex forms around the
 * site
 */
add_action( 'wp_ajax_flex_form', 'flex_form' );
add_action( 'wp_ajax_nopriv_flex_form', 'flex_form' );
function flex_form() {
    $currentForm = new Timber\Post($_POST['form_id']);
    $result = array();

    // Parse & Sanitize Fields
    $fields = json_decode(stripslashes($_POST['fields']), true);
    foreach ($fields as $key => $value) {
        $fields[$key] = sanitize_text_field($value);
    }

    // Email Setup
    $to_address = sanitize_email($currentForm->destination_email_address);
    $subject = $currentForm->title . ' submission';
    $message = $subject . "\r\n\r\n";
    foreach ($fields as $key => $value) {
        if ($key == 'FileID') {
            $result['download'] = wp_get_attachment_url(intval($value));
        } else {
            $message.= $key . ": " . $value . "\r\n";
        }
    }

    // Send
    if (wp_mail($to_address, $subject, $message)) {
        $result['message'] = $currentForm->get_field('thank_you_message');
    } else {
        $result['message'] = $currentForm->get_field('error_message');
    }

    echo json_encode($result);

    wp_die(); // Terminate response
}

/*
*   Remove the Back-End code editor
*/
function remove_editor_menu() {
    remove_action('admin_menu', '_add_themes_utility_last', 101);
    if (!function_exists('get_field')) {
        return;
    }
    if (!get_field('enable_comments_menu', 'option')) {
      remove_menu_page( 'edit-comments.php' );
    }
}
add_action('_admin_menu', 'remove_editor_menu', 1);

/*
*   Remove Gutenburg CSS
*/
function remove_block_css(){
    wp_dequeue_style( 'wp-block-library' );
}
add_action( 'wp_enqueue_scripts', 'remove_block_css', 100 );

/**
 * Disable Yoast's Hidden love letter about using the WordPress SEO plugin.
 */
add_action( 'template_redirect', function () {

    if ( ! class_exists( 'WPSEO_Frontend' ) ) {
        return;
    }

    $instance = WPSEO_Frontend::get_instance();

    // make sure, future version of the plugin does not break our site.
    if ( ! method_exists( $instance, 'debug_mark') ) {
        return ;
    }

    // ok, let us remove the love letter.
     remove_action( 'wpseo_head', array( $instance, 'debug_mark' ), 2 );
}, 9999 );


/*
*   Remove the detail from the wordpress errors
*/
function no_wordpress_errors() {
    return 'Something is wrong';
}
add_filter('login_errors', 'no_wordpress_errors');


/*
*   Add the async attribute to loaded script tags.
*/
function add_async_attribute($tag, $handle) {
    $scripts_to_async = array('iss-suggest', 'iss', 'addthis');
    foreach($scripts_to_async as $async_script) {
        if($async_script === $handle) {
            return str_replace('src', 'async="async" src', $tag);
        }
    }
    return $tag;
}

add_filter('script_loader_tag', 'add_async_attribute', 10, 2);

/*
*   Replaces the WP logo in the admin bar.
*/
function ec_dashboard_custom_logo() {
    echo '
    <style type="text/css">
        #wpadminbar #wp-admin-bar-wp-logo > .ab-item{
			background-color:#3aa1fa;
			background-image: url(' . get_theme_file_uri('/dist/images/admin_logo.svg') . ') !important;
			background-size: 30px;
			background-repeat: no-repeat;
			background-position: center;
		}
		#wpadminbar #wp-admin-bar-wp-logo > .ab-item .ab-icon:before {
			color:rgba(0, 0, 0, 0);
		}

    </style>
    ';
}
add_action('wp_before_admin_bar_render', 'ec_dashboard_custom_logo');

/*
 *  Noindex Author
 *  Adds a noindex meta tag on author archives so they are not indexed by Google
 */

function noindex_author() {
    if (is_author()) {
        echo '<meta name="robots" content="noindex" />';
    }
}
add_action('wp_head', 'noindex_author');

/**
 * Add fontawesome icons for social share links
 * @return string[][]
 */
function sa_social_link_icons() {
    return [
        'facebook'  => array(
            'icon' => '<i class="fa-brands fa-facebook-square"></i>'
        ),
        'twitter'   => array(
            'prepend' => 'https://twitter.com/',
            'icon'    => '<i class="fa-brands fa-twitter-square"></i>'
        ),
        'instagram' => array(
            'icon' => '<i class="fa-brands fa-instagram-square"></i>'
        ),
        'linkedin'  => array(
            'icon' => '<i class="fa-brands fa-linkedin"></i>'
        )
    ];
}
add_filter('crowd_social_link_options','sa_social_link_icons');

/**
 * Add custom body classes
 * @param $classes
 * @return mixed
 */
function sa_body_class($classes) {
    global $wp_query;

    $classes[] = 'sticky-header';

    if(!empty($wp_query->query['shortcode'])) {
        $classes[] = 'page-job-detail';
    }

    if(is_singular('sa_property')) {
        $classes[] = 'single-property';
    }

    return $classes;
}
add_filter('body_class','sa_body_class');

/**
 * Register widget area.
 */
function sa_widgets_init() {
    register_sidebars(
        3,
        array(
            'name'          => __('Footer Widgets %d', 'stirlingack'),
            'id'            => 'footer-widget',
            'description'   => __('Add widgets here to appear in your footer.', 'stirlingack'),
            'before_widget' => '<div id="%1$s" class="footer-widget %2$s">',
            'after_widget'  => '</div>',
            'before_title'  => '<h5 class="h3 widget-title">',
            'after_title'   => '</h5>',
        )
    );

    register_sidebar([
        'name'          => __('Insight Widgets', 'stirlingack'),
        'id'            => 'insight-widgets',
        'description'   => __('Add widgets here to appear in your area.', 'stirlingack'),
        'before_widget' => '<div id="%1$s" class="insight-widget %2$s">',
        'after_widget'  => '</div>',
        'before_title'  => '<h5 class="h3 widget-title">',
        'after_title'   => '</h5>',
    ]);

    register_sidebar([
        'name'          => __('Content Widgets', 'stirlingack'),
        'id'            => 'content-widgets',
        'description'   => __('Add widgets here to appear in your area.', 'stirlingack'),
        'before_widget' => '<div id="%1$s" class="content-widget %2$s">',
        'after_widget'  => '</div>',
        'before_title'  => '<h5 class="h3 widget-title">',
        'after_title'   => '</h5>',
    ]);

    register_sidebar([
        'name'          => __('Content Widgets 2', 'stirlingack'),
        'id'            => 'content-widgets-2',
        'description'   => __('Add widgets here to appear in your area.', 'stirlingack'),
        'before_widget' => '<div id="%1$s" class="content-widget %2$s">',
        'after_widget'  => '</div>',
        'before_title'  => '<h5 class="h3 widget-title">',
        'after_title'   => '</h5>',
    ]);
}
add_action('widgets_init', 'sa_widgets_init');

function get_latest_posts() {
    return new Timber\PostQuery([
        'post_type' => 'post',
        'posts_per_page' => 3
    ]);
}

function sa_save_insight($post_ID) {
    // bail out if this is an autosave
    if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
        return;
    }

    $post_branch_ids = $_POST['acf']['field_617fd0350b13e'];

    delete_post_meta($post_ID, 'post_branch_id');

    if(!empty($post_branch_ids)) {
        foreach ($post_branch_ids as $branch_id) {
            add_post_meta($post_ID,'post_branch_id',$branch_id);
        }
    }
}
add_action('save_post_post', 'sa_save_insight', 10);

/**
 * Add breadcrumbs to the pages
 */
function sa_breadcrumbs() {
    global $post;

    $context = Timber::get_context();

    if($post->ID == $context['options']['page']['thank_you_page_id']) return;

    if(is_front_page()) return;

    if(is_singular('sa_property')) return;

    if ( function_exists('yoast_breadcrumb') ) {
        yoast_breadcrumb( '<div id="breadcrumbs"><div class="container">','</div></div>' );
    }
}
add_action('after_header_breadcrumbs','sa_breadcrumbs',10);


/**
 * Add dynamic slug for custom post types
 * @param $args
 * @param $post_type
 * @return mixed
 */
function sa_assign_post_type_dynamic_slugs($args, $post_type) {
    $page_options = get_field('page','option');

    if($post_type == 'sa_new_home') {
        if(!empty($page_options['new_homes_list_page_id'])) {
            $args['rewrite']['slug'] = get_post_field('post_name', $page_options['new_homes_list_page_id']);
        }
    } else if($post_type == 'post') {
        if(!empty($page_options['blog_list_page_id'])) {
            $args['rewrite']['slug'] = get_post_field('post_name', $page_options['blog_list_page_id']);
            $args['rewrite']['with_front'] = false;
        }
    }

    return $args;
}
add_filter('register_post_type_args','sa_assign_post_type_dynamic_slugs',10,2);


function post_permalink_add_parent_slug($permalink, $post) {
    $page_options = get_field('page','option');

    if ($post->post_type !== 'post' && empty($page_options['blog_list_page_id'])) {
        return $permalink;
    }

    return '/' . get_post_field('post_name', $page_options['blog_list_page_id']) . '/%postname%/';
}
add_filter('pre_post_link', 'post_permalink_add_parent_slug', 10, 2);


function breadcrumbs_add_post_parent_page( $links ) {
    $page_options = get_field('page','option');

    if ( is_singular( 'post' )) {
        if(!empty($page_options['blog_list_page_id'])) {
            $breadcrumb[] = array(
                'url' => get_permalink($page_options['blog_list_page_id']),
                'text' => get_the_title($page_options['blog_list_page_id']),
            );

            array_splice( $links, 1, -2, $breadcrumb );
        }
    } else if ( is_singular( 'sa_new_home' )) {
        if(!empty($page_options['new_homes_list_page_id'])) {
            $breadcrumb[] = array(
                'url' => get_permalink($page_options['new_homes_list_page_id']),
                'text' => get_the_title($page_options['new_homes_list_page_id']),
            );

            array_splice( $links, 1, -2, $breadcrumb );
        }
    } else if ( is_singular( 'sa_branch' )) {
        if(!empty($page_options['branch_list_page_id'])) {
            $breadcrumb[] = array(
                'url' => get_permalink($page_options['branch_list_page_id']),
                'text' => get_the_title($page_options['branch_list_page_id']),
            );

            array_splice( $links, 1, -2, $breadcrumb );
        }
    }

    return $links;
}
add_filter( 'wpseo_breadcrumb_links', 'breadcrumbs_add_post_parent_page' );

function get_google_map_api_key() {
    return get_field('google_api_key','option');
}

function sa_acf_set_google_map( $api ){
    $api_key = get_google_map_api_key();

    if(!empty($api_key)) {
        $api['key'] = $api_key;
    }

    return $api;

}
add_filter('acf/fields/google_map/api', 'sa_acf_set_google_map');


/**
 * Add current css class for post type parent pages
 * @param $classes
 * @param $item
 * @param $args
 * @return mixed
 */
function sa_highlight_post_type_parent_pages_nav($classes, $item, $args) {
    $page_options = get_field('page','option');

    if($args->menu_id == 'main-nav' && !empty($page_options['new_homes_list_page_id'])) {
        if (
            (is_singular('sa_new_home') && $item->object_id == $page_options['new_homes_list_page_id']) ||
            (is_singular('sa_branch') && $item->object_id == $page_options['branch_list_page_id'])
        ) {
            $classes[] = 'current-menu-item';
        }
    }

    return $classes;
}
add_filter('nav_menu_css_class', 'sa_highlight_post_type_parent_pages_nav', 1, 3);

function sa_branch_contact_popup() {
    $context = Timber::get_context();

    $branch_id = $_REQUEST['id'];

    $post_type = get_post_type($branch_id);
    if($post_type == 'property') {
        $property = get_property(get_post($branch_id));

        $branch = new SA_PropertyBranch($property);
        $branch_id = $branch->get_branch_id();
    }


    $context['branch_title'] = get_the_title($branch_id);
    $context['id'] = $_REQUEST['id'];
    $context['department'] = $_REQUEST['department'];

    die(Timber::compile('components/popups/branch-contact.twig',$context));
}
add_action('wp_ajax_branch_contact_popup','sa_branch_contact_popup');
add_action('wp_ajax_nopriv_branch_contact_popup','sa_branch_contact_popup');

function branch_contact_submit() {
    $result = $errors = [];

    $property = null;
    $id = $_REQUEST['id'];
    $department_key = ($_REQUEST['department'] == 'sales') ? 'sale' : 'let';

    $post_type = get_post_type($id);

    if($post_type == 'property') {
        $property = get_property(get_post($id));

        $branch = new SA_PropertyBranch($property);
        $id = $branch->get_branch_id();
    }

    $email_subject = get_the_title($id) . ' viewing request';
    $enquire_form_email = get_field("enquire_form_{$department_key}_email_address", $id);
    $email_destination = get_field("branch_{$department_key}_email_address", $id);
    $branch_phone = get_field("branch_{$department_key}_phone", $id);

    if(!empty($enquire_form_email) && $post_type != 'property') {
        $email_destination = $enquire_form_email;
    }

    if(!empty($email_destination)) {
        if(empty($_REQUEST['first_name'])) {
            $errors['first_name'] = 'First Name must not be empty';
        }

        if(empty($_REQUEST['surname'])) {
            $errors['surname'] = 'Surname must not be empty';
        }

        if(empty($_REQUEST['phone_number'])) {
            $errors['phone_number'] = 'Phone Number must not be empty';
        }

        if(empty($_REQUEST['email_address'])) {
            $errors['email_address'] = 'Email Address must not be empty';
        }

        if(!empty($_REQUEST['email_address']) && !is_email($_REQUEST['email_address'])) {
            $errors['email_address'] = 'Email Address is not valid';
        }

        if(sizeof($errors) == 0) {
            $mailer = new WP_Mailer();

            $sent = $mailer
                ->set_type('branch-contact')
                ->set_header_line("From: Stirling Ackroyd <no-reply@" . $_SERVER['SERVER_NAME'] . ">")
                ->add_recipient_email($email_destination)
                ->set_subject($email_subject)
                ->set_email_data([
                    'form_data' => $_REQUEST,
                    'property' => $property,
                    'branch' => new TimberPost($id),
                    'site_title' => get_bloginfo('name')
                ])
                ->send();

            $mail_body_data = [
                'form_data' => $_REQUEST,
                'branch' => new TimberPost($id),
                'branch_phone' => $branch_phone,
                'site_title' => get_bloginfo('name'),
                'logo_url' => get_template_directory_uri() . '/dist/images/email-logo.jpg'
            ];

            if(!is_null($property)) {
                $mail_body_data['property'] = $property;
                $mail_body_data['property_image'] = $property->get_main_photo_src();
                $mail_body_data['property_desc'] = $property->get_formatted_description();
                $mail_body_data['property_price'] = $property->get_formatted_price();
                $mail_body_data['property_beds'] = $property->bedrooms;
                $mail_body_data['property_department'] = $property->_department;
            }

            $mailer
                ->set_type('branch-contact-user')
                ->set_header_line("From: Stirling Ackroyd <no-reply@" . $_SERVER['SERVER_NAME'] . ">")
                ->add_recipient_email($_REQUEST['email_address'])
                ->set_subject('Thank you for your Stirling Ackroyd enquiry.')
                ->set_email_data($mail_body_data)
                ->send();

            if ($sent) {
                $pages = get_field('page','option');

                $result['status'] = true;
                $result['message'] = 'Email sent successfully.';
                $result['department'] = $department_key;
                $result['type'] = $post_type;
                $result['redirect_url'] = get_the_permalink($pages['thank_you_page_id']);
            } else {
                $result['status'] = false;
                $result['message'] = 'Email not sent. There seems to be a problem with the server. Please try again later.';
            }
        } else {
            $result['status'] = false;
            $result['errors'] = $errors;
        }
    } else {
        $result['status'] = false;
        $result['message'] = 'Email destination not found';
    }

    wp_send_json($result);
}
add_action('wp_ajax_branch_contact_submit','branch_contact_submit');
add_action('wp_ajax_nopriv_branch_contact_submit','branch_contact_submit');


/**
 * Change headers to text/plain for branch contact email
 * @param $headers
 * @param $type
 * @return mixed
 */
function sa_mailer_mail_headers($headers, $type) {
    if($type == 'branch-contact') {
        $headers[0] = 'Content-Type: text/plain; charset=\"utf-8\"\r\n';
    }

    return $headers;
}
//add_filter('mailer_mail_headers','sa_mailer_mail_headers',10, 2);

/**
 * CF7: Change default dropdown value to custom one
 * @param $html
 * @return string|string[]
 */
function sa_cf7_change_dropdown_label($html) {
    return str_replace('---', 'Please select', $html);
}
add_filter('wpcf7_form_elements', 'sa_cf7_change_dropdown_label');

/**
 * Add REL attribute to the pagination
 * @param $r
 * @param $args
 * @return string|string[]
 */
function sa_pagination_add_rel_attribute($r,$args) {
    $r = str_replace('<a class="next', '<a rel="next" class="next', $r);
    $r = str_replace('<a class="prev', '<a rel="prev" class="prev', $r);

    return $r;
}
add_filter('paginate_links_output','sa_pagination_add_rel_attribute',10,2);

/**
 * Add phone tracking code to the bottom branch page
 */
function sa_branch_add_phone_tracking_code() {
    global $wp_query;

    if(!is_singular('sa_branch')) return;

    $tracking_code = get_post_meta($wp_query->post->ID, 'branch_phone_tracking', true);

    if(!empty($tracking_code)) echo $tracking_code;
}
add_action('wp_footer', 'sa_branch_add_phone_tracking_code');

function sa_page_custom_js_code() {
    global $wp_query;

    $js_code = get_post_meta($wp_query->post->ID, 'page_custom_javascript', true);

    if(!empty($js_code)) echo $js_code;
}
add_action('wp_footer', 'sa_page_custom_js_code');


/**
 * Add Property reference code to the redirect page
 */
function sa_property_ref_rewrite_rule() {
    add_rewrite_rule('^more-details/([^/]*)/?', 'index.php?prop_ref_id=$matches[1]', 'top');
}
add_action('init', 'sa_property_ref_rewrite_rule', 10, 0);


/**
 * Add property ref number to query wars
 * @param $query_vars
 * @return mixed
 */
function sa_property_ref_query_vars($query_vars) {
    $query_vars[] = 'prop_ref_id';

    return $query_vars;
}
add_filter('query_vars', 'sa_property_ref_query_vars');


/**
 * Redirect to property page if access by reference number
 */
function sa_property_ref_redirect() {
    global $wpdb;

    $property_ref_id = get_query_var('prop_ref_id');

    if(!empty($property_ref_id)) {
        $meta_item_data = $wpdb->get_row("SELECT * FROM `{$wpdb->postmeta}` WHERE `meta_value` LIKE '%{$property_ref_id}%'");

        if(!empty($meta_item_data->post_id)) {
            wp_redirect(get_the_permalink($meta_item_data->post_id));die;
        }
    }
}
add_action('wp_head', 'sa_property_ref_redirect');

/*if(isset($_GET['test_email'])) {
    $id = 7607;
    $property = get_property(get_post(6107));
    $branch_phone = '12312312313123';

    $mailer = new WP_Mailer();

    $sent = $mailer
        ->set_debug()
        ->set_type('branch-contact-user')
        ->set_header_line("From: Stirling Ackroyd <no-reply@" . $_SERVER['SERVER_NAME'] . ">")
        ->add_recipient_email('test@gmail.com')
        ->set_subject('Thank You')
        ->set_email_data([
            'form_data' => [
                'first_name' => 'Sergey',
                'surname' => 'Test',
                'phone_number' => '12312312313',
                'email_address' => 'ddddd@asdasd.com',
            ],
            'property' => $property,
            'property_image' => $property->get_main_photo_src(),
            'property_desc' => $property->get_formatted_description(),
            'property_price' => $property->get_formatted_price(),
            'property_beds' => $property->bedrooms,
            'property_department' => $property->_department,
            'branch' => new TimberPost($id),
            'branch_phone' => $branch_phone,
            'site_title' => get_bloginfo('name'),
            'logo_url' => get_template_directory_uri() . '/dist/images/logo.svg'
        ])
        ->send();
}*/

function get_latest_new_homes($limit = 3) {
    return new Timber\PostQuery([
        'post_type' => 'sa_new_home',
        'posts_per_page' => $limit,
        'order' => 'ASC',
        'orderby' => 'date'
    ]);
}

function get_insight_list_style() {
    $style = get_field('insight_list_style','option');

    return !empty($style) ? $style : 'grid';
}

add_filter( 'sgo_javascript_combine_excluded_inline_content', 'js_combine_exclude_inline_script' );
function js_combine_exclude_inline_script( $exclude_list ) {
    $exclude_list[] = 'var myLatlng';
    $exclude_list[] = 'console.log';
    $exclude_list[] = 'var data';

    return $exclude_list;
}

//added by SG support
add_filter( 'sgo_javascript_combine_exclude', 'js_combine_exclude' );
function js_combine_exclude( $exclude_list ) {
    $exclude_list[] = 'ph_ryc_calculate';
    $exclude_list[] = 'data-add-to-shortlist';
    $exclude_list[] = 'ph_doing_shortlist_request';
    $exclude_list[] = 'ph_mc_add_commas';
    $exclude_list[] = 'rental-yield-calculator';
    $exclude_list[] = 'ph_rac_calculate';
    $exclude_list[] = 'google_business_reviews_rating';
    $exclude_list[] = 'initialize_property_map';
    $exclude_list[]	= 'ph_doing_save_search_request';
    $exclude_list[] = 'google.maps.LatLng';
    $exclude_list[] = 'console.log';
    $exclude_list[] = 'ph_sdc_calculate';
    return $exclude_list;
}

function time_elapsed_string($datetime, $full = false) {
    $now = new DateTime;
    $ago = new DateTime($datetime);
    $diff = $now->diff($ago);

    $diff->w = floor($diff->d / 7);
    $diff->d -= $diff->w * 7;

    $string = array(
        'y' => 'year',
        'm' => 'month',
        'w' => 'week',
        'd' => 'day',
        'h' => 'hour',
        'i' => 'minute',
        's' => 'second',
    );
    foreach ($string as $k => &$v) {
        if ($diff->$k) {
            $v = $diff->$k . ' ' . $v . ($diff->$k > 1 ? 's' : '');
        } else {
            unset($string[$k]);
        }
    }

    if (!$full) $string = array_slice($string, 0, 1);
    return $string ? implode(', ', $string) . ' ago' : 'just now';
}

function add_workable_job_detail_rules() {
    $careers_page_id  = get_page_id_by_template_name( 'page-careers' );
    $career_page_slug = get_post_field( 'post_name', $careers_page_id );

    add_rewrite_tag( "%shortcode%", '(\d+)' );
    add_rewrite_rule( '^' . $career_page_slug . '/j/([^/]*)/?', 'index.php?shortcode=$matches[1]', 'top' );

    //flush_rewrite_rules();
}
add_action( 'init', 'add_workable_job_detail_rules' );


function job_details_template_include( $template ) {
    global $wp_query;

    if ( ! empty( $wp_query->query['shortcode'] ) ) {
        $template = TEMPLATEPATH . '/' . "page-job-detail.php";
    }

    return $template;
}
add_filter( 'template_include', 'job_details_template_include', 1, 1 );

remove_filter('template_redirect', 'redirect_canonical');
