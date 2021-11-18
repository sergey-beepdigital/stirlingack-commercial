<?php

require_once 'includes/classes/class.property-branch.php';

include "includes/shortcodes.php";
include "includes/properyhive-hooks.php";

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
        require_once('includes/post-types/form.php');
        require_once('includes/post-types/branch.php');
        require_once('includes/post-types/new-home.php');
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
        require_once('includes/blocks/cta-box.php');
        require_once('includes/blocks/latest-post.php');
    }

    function add_to_context( $context ) {
        $context['top_menu'] = new TimberMenu('top-nav');
        $context['menu'] = new TimberMenu('main-nav');

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
        return $twig;
    }

    function assets( $twig ) {
        // Get rid of default media element
        // wp_deregister_script('wp-mediaelement'); // Uncomment to disable Media Element
        // wp_deregister_style('wp-mediaelement'); // Uncomment to disable Media Element

        // Remove Wp's jQuery
        // wp_deregister_script('jquery'); // Uncomment to disable jQuery
        wp_deregister_script( 'flexslider');
        wp_deregister_script( 'flexslider-init');

        if(is_singular('sa_branch')) {
            wp_enqueue_script( 'api-feefo', 'https://api.feefo.com/api/javascript/stirling-ackroyd');
        }

        // Define globals with for cache busting
        require_once 'enqueues.php';
        require('includes/cache_bust.php');

        //wp_enque
        wp_enqueue_script( 'essential.js', BUNDLE_JS_SRC, array(), $cache_ver, false); // These will appear at the top of the page
        wp_enqueue_script( 'deferred.js', DEFERRED_BUNDLE_JS_SRC, array(), $cache_ver, true); // These will appear in the footer

        // Enqueue a main stylesheet as a sensible default
        wp_enqueue_style( 'main.css', MAIN_CSS_SRC, array(), $cache_ver, 'all' );

        wp_localize_script('deferred.js', 'sg_config', [
            'google_maps' => [
                'api_key' => get_option('propertyhive_google_maps_api_key'),
                'marker_url' => get_template_directory_uri() . '/dist/images/map-marker-square.png'
            ]
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
        #wpadminbar #wp-admin-bar-wp-logo > .ab-item .ab-icon:before {
        background-image: url(' . get_bloginfo('stylesheet_directory') . '/dist/images/admin_logo.svg)
        !important; background-position: 0 0; color:rgba(0, 0, 0, 0);background-size:cover;
    }

    #wpadminbar #wp-admin-bar-wp-logo.hover > .ab-item .ab-icon { background-position: 0 0; }

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
        'facebook_site'  => array(
            'icon' => '<i class="fa-brands fa-facebook-square"></i>'
        ),
        'twitter_site'   => array(
            'prepend' => 'https://twitter.com/',
            'icon'    => '<i class="fa-brands fa-twitter-square"></i>'
        ),
        'instagram_url' => array(
            'icon' => '<i class="fa-brands fa-instagram-square"></i>'
        ),
        'linkedin_url'  => array(
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
    $classes[] = 'sticky-header';

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
    if(is_front_page()) return;

    if(is_property()) return;

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
    if($post_type == 'sa_new_home') {
        $page_options = get_field('page','option');

        if(!empty($page_options['new_homes_list_page_id'])) {
            $new_homes_list_page = get_post_field('post_name', $page_options['new_homes_list_page_id']);
            $args['rewrite']['slug'] = $new_homes_list_page;
        }
    }

    return $args;
}
add_filter('register_post_type_args','sa_assign_post_type_dynamic_slugs',10,2);
