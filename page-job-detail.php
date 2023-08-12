<?php
/**
 * The Template for displaying all single posts
 *
 * Methods for TimberHelper can be found in the /lib sub-directory
 *
 * @package  WordPress
 * @subpackage  Timber
 * @since    Timber 0.1
 */

$careers_page_id  = get_page_id_by_template_name( 'page-careers' );

$context = Timber::get_context();
$post = Timber::query_post();
$context['post'] = new Timber\Post($careers_page_id);
$context['shortcode'] = get_query_var('shortcode');

remove_action('after_header_breadcrumbs','sa_breadcrumbs',10);

Timber::render( 'page-job-detail.twig', $context );
