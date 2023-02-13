<?php
/**
 * The template for displaying Archive pages.
 *
 * Used to display archive-type pages if nothing more specific matches a query.
 * For example, puts together date-based pages if no date.php file exists.
 *
 * Learn more: http://codex.wordpress.org/Template_Hierarchy
 *
 * Methods for TimberHelper can be found in the /lib sub-directory
 *
 * @package  WordPress
 * @subpackage  Timber
 * @since   Timber 0.2
 */

global $wp_query;

$template_style = 'archive-' . get_insight_list_style() . '.twig';

$templates = array( $template_style, 'index.twig' );

$context = Timber::get_context();

$context['title'] = 'Archive';
if ( is_day() ) {
	$context['title'] = 'Archive: '.get_the_date( 'D M Y' );
} else if ( is_month() ) {
	$context['title'] = 'Archive: '.get_the_date( 'M Y' );
} else if ( is_year() ) {
	$context['title'] = 'Archive: '.get_the_date( 'Y' );
} else if ( is_tag() ) {
	$context['title'] = single_tag_title( '', false );
} else if ( is_category() ) {
	$context['title'] = single_cat_title( '', false );
	array_unshift( $templates, 'archive-' . get_query_var( 'cat' ) . '.twig' );
} else if ( is_post_type_archive() ) {
	$context['title'] = post_type_archive_title( '', false );
	array_unshift( $templates, 'archive-' . get_post_type() . '.twig' );
}

$blog_page_id = $context['options']['page']['blog_list_page_id'];

$posts_query = new Timber\PostQuery($wp_query);

$posts_total_text_parts = [];

$posts_total_text_parts[] = $posts_query->found_posts;
$posts_total_text_parts[] = get_the_title($blog_page_id);
$posts_total_text_parts[] = '<span class="branch-name">on '. $context['title'] . '</span>';

$context['head_thumbnail'] = get_the_post_thumbnail_url($blog_page_id);
$context['posts_total_text'] = join(' ', $posts_total_text_parts);
$context['posts_query'] = $posts_query;

$context['categories_list'] = Timber::get_terms('category',['hide_empty' => 1]);
$context['current_cat_link'] = get_term_link(get_queried_object());

Timber::render( $templates, $context );
