<?php

if ( ! class_exists( 'Timber' ) ) {
    add_action( 'admin_notices', function() {
        echo '<div class="error"><p>Timber not activated. Make sure you activate the plugin in <a href="' . esc_url( admin_url( 'plugins.php#timber' ) ) . '">' . esc_url( admin_url( 'plugins.php' ) ) . '</a></p></div>';
    } );
    return;
}

Timber::$dirname = array('templates', 'components');

class StarterSite extends TimberSite {

    function __construct() {
        add_theme_support( 'post-formats' );
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

        add_filter( 'emoji_svg_url', '__return_false' );

        // Timber Actions
        add_action( 'init', array( $this, 'register_post_types' ) );
        add_action( 'init', array( $this, 'register_taxonomies' ) );
        add_action( 'wp_enqueue_scripts', array( $this, 'assets' ) );

        // First party actions
        add_action('inline_file', array($this, 'inline_file'));
        add_action('admin_head', array($this, 'fix_svg_thumb_display'));
        add_action( 'init', 'disable_wp_emojicons' );

        // Plugin Dependancies
        require_once('includes/required-plugins/class-tgm-plugin-activation.php');
        require_once('includes/required-plugins/register-plugin.php');

        if ( is_admin() && function_exists('register_required_plugins')) {
            add_action ('tgmpa_register', 'register_plugins');
        }

        // Add Advanced Custom Fields options page
        if( function_exists('acf_add_options_page') ) {
            acf_add_options_sub_page('Analytics/Tracking');
            acf_add_options_sub_page('Social Profiles');

            if (current_user_can('administrator') || get_field('show_debug_menu', 'option')) {
              acf_add_options_sub_page('Debug Options');
            }
        }

        parent::__construct();
    }

    function register_post_types() {
        // require_once custom post types here
        require_once('includes/post-types/form.php');
    }

    function register_taxonomies() {
        // require_once custom taxonomies here
    }

    function add_to_context( $context ) {
        $context['menu'] = new TimberMenu('Global Header Navigation');
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
        wp_deregister_script('jquery'); // Uncomment to disable jQuery

        // Define globals with for cache busting
        require_once 'enqueues.php';

        wp_enqueue_script( 'bundle', BUNDLE_JS_SRC, array(), null, false); // These will appear at the top of the page
        wp_enqueue_script( 'deferred_bundle', DEFERRED_BUNDLE_JS_SRC, array(), null, true); // These will appear in the footer

        // Enqueue a main stylesheet as a sensible default
        wp_enqueue_style( 'main', MAIN_CSS_SRC, array(), null, 'all' );
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
			'name' => 'Advanced Custom Fields Pro',
			'slug' => 'advanced-custom-fields-pro',
			'source' => get_stylesheet_directory() . '/includes/plugins/advanced-custom-fields-pro.zip',
			'required' => true,
            'force_activation' => true
        ),
        array(
            'name' => 'Advanced Custom Fields: Font Awesome Field',
            'slug' => 'advanced-custom-fields-font-awesome',
            'required' => true,
            'force_activation' => true
        ),
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

/**
 * Hide Custom Fields Menu in the backend.
 *
 * Hides the acf edit menu in the backend by default, can be disabled via a
 * checkbox option on the options page.
 *
 * This helps avoid syncing issues with local acf-json and dev site fields.
 *
 * DO NOT COMMENT OUT OR DISABLE
 */
require_once('includes/acf-edit-screen-disabler.php');

if (function_exists('get_field')) {
    if (!get_field('enable_acf_edit', 'option')) {
        add_filter('acf/settings/show_admin', '__return_false'); //DO NOT COMMENT OUT OR DISABLE USE DEBUG OPTIONS PAGE TICK BOX INSTEAD
    }
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

    if (!get_field('enable_comments_menu', 'option')) {
      remove_menu_page( 'edit-comments.php' );
    }
}
add_action('_admin_menu', 'remove_editor_menu', 1);

/*
*   Remove the detail from the wordpress errors
*/
function no_wordpress_errors() {
    return 'Something is wrong';
}
add_filter('login_errors', 'no_wordpress_errors');

/*
*   Enqueue the styles of WP Dashicons to be used on the front end.
*/
function load_dashicons_front_end() {
    wp_enqueue_style('dashicons');
}
add_action('wp_enqueue_scripts', 'load_dashicons_front_end');

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
*   Remove version numbers from loaded assets so they do not cache too hard.
*/
function remove_css_js_ver($src) {
    if (strpos($src, '?ver=')) {
        $src = remove_query_arg('ver', $src);
    }
    return $src;
}

add_filter('style_loader_src', 'remove_css_js_ver', 10, 2);
add_filter('script_loader_src', 'remove_css_js_ver', 10, 2);

/*
*   Replaces the WP logo in the admin bar.
*/
function ec_dashboard_custom_logo() {
    echo '
    <style type="text/css">
        #wpadminbar #wp-admin-bar-wp-logo > .ab-item .ab-icon:before {
        background-image: url(' . get_bloginfo('stylesheet_directory') . '/dist/images/admin_logo.png)
        !important; background-position: 0 0; color:rgba(0, 0, 0, 0);background-size:cover;
    }

    #wpadminbar #wp-admin-bar-wp-logo.hover > .ab-item .ab-icon { background-position: 0 0; }

    </style>
    ';
}
add_action('wp_before_admin_bar_render', 'ec_dashboard_custom_logo');

/*
*   Replaces the logo on the WP login screen
*/
function my_login_logo() { ?>
    <style type="text/css">
        #login h1 a, .login h1 a {
            background-image: url(<?php echo get_stylesheet_directory_uri(); ?>/dist/images/admin_logo.png);
		height:65px;
		width:65px;
		background-size: contain;
		background-repeat: no-repeat;
        	padding-bottom: 0px;
        }
    </style>
<?php }
add_action( 'login_enqueue_scripts', 'my_login_logo' );

if (function_exists('get_field') && !is_admin()) {
    if (get_field('show_enqueued_scripts', 'option')) {
        function wpa54064_inspect_scripts() {
          global $wp_scripts;
          echo '<pre>';
          echo '<h1>Enqueued Scripts</h1>';
          foreach( $wp_scripts->queue as $handle ) :
              echo $handle . '<br>';
          endforeach;
          echo '</pre>';
        }
        add_action( 'wp_print_scripts', 'wpa54064_inspect_scripts' );
    }

    if (get_field('debug_rewrites', 'option')) {
        ini_set( 'error_reporting', -1 );
        ini_set( 'display_errors', 'On' );

        echo '<pre>';

        add_action( 'parse_request', 'debug_404_rewrite_dump' );
        function debug_404_rewrite_dump( &$wp ) {
            global $wp_rewrite;

            echo '<h2>rewrite rules</h2>';
            echo var_export( $wp_rewrite->wp_rewrite_rules(), true );

            echo '<h2>permalink structure</h2>';
            echo var_export( $wp_rewrite->permalink_structure, true );

            echo '<h2>page permastruct</h2>';
            echo var_export( $wp_rewrite->get_page_permastruct(), true );

            echo '<h2>matched rule and query</h2>';
            echo var_export( $wp->matched_rule, true );

            echo '<h2>matched query</h2>';
            echo var_export( $wp->matched_query, true );

            echo '<h2>request</h2>';
            echo var_export( $wp->request, true );

            global $wp_the_query;
            echo '<h2>the query</h2>';
            echo var_export( $wp_the_query, true );
        }
        add_action( 'template_redirect', 'debug_404_template_redirect', 99999 );
        function debug_404_template_redirect() {
            global $wp_filter;
            echo '<h2>template redirect filters</h2>';
            echo var_export( $wp_filter[current_filter()], true );
        }
        add_filter ( 'template_include', 'debug_404_template_dump' );
        function debug_404_template_dump( $template ) {
            echo '<h2>template file selected</h2>';
            echo var_export( $template, true );

            echo '</pre>';
            exit();
        }
    }
}

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