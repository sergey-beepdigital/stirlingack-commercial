<?php

/** Helper for getting page ID by template name
 * @param string $template
 * @param bool $all
 * @return array|int
 */
function get_page_id_by_template_name($template = '',$all = false) {
    if(!empty($template)) {
        $template = $template.'.php';
        $page_info = get_posts(array(
            'post_type'   => 'page',
            'meta_query' => array(
                array(
                    'key' => '_wp_page_template',
                    'value' => $template
                )
            )
        )) ;

        wp_reset_query();
        wp_reset_postdata();

        if($all) {
            $parents = array();

            if(sizeof($page_info) > 0) {
                foreach ($page_info as $page_info_single) {
                    $parents[] = $page_info_single->ID;
                }

            }

            return $parents;
        } else {
            return $page_info[0]->ID;
        }
    }
}


/**
 * Returns page URL by custom template name
 * @param string $template
 * @return false|string
 */
function get_page_link_by_template_name($template = '') {
    return get_the_permalink(get_page_id_by_template_name($template));
}

function array_find($needle, array $haystack, $column = null) {

    if(is_array($haystack[0]) === true) { // check for multidimentional array

        foreach (array_column($haystack, $column) as $key => $value) {
            if (strpos(strtolower($value), strtolower($needle)) !== false) {
                return $key;
            }
        }

    } else {
        foreach ($haystack as $key => $value) { // for normal array
            if (strpos(strtolower($value), strtolower($needle)) !== false) {
                return $key;
            }
        }
    }
    return false;
}

/**
 * Custom function for write data to log file
 */
ini_set( 'error_log', WP_CONTENT_DIR . '/debug.log' );
if ( ! function_exists('write_log')) {
    function write_log ( $log )  {
        if ( is_array( $log ) || is_object( $log ) ) {
            error_log( print_r( $log, true ) );
        } else {
            error_log( $log );
        }
    }
}

function human_filesize($bytes, $decimals = 2) {
    $size = array('B','kB','MB','GB','TB','PB','EB','ZB','YB');
    $factor = floor((strlen($bytes) - 1) / 3);
    return sprintf("%.{$decimals}f", $bytes / pow(1024, $factor)) . @$size[$factor];
}