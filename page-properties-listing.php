<?php /* Template Name: Properties Listing */

$context = Timber::get_context();
$post = new TimberPost();
$context['post'] = $post;

$page = get_query_var( 'page' ) ? get_query_var( 'page' ) : 1;
$posts_per_page = get_option( 'posts_per_page' );

$query_args = [
    'post_status'    => 'publish',
    'post_type'      => 'sa_property',
    'posts_per_page' => get_option( 'posts_per_page' ),
    'paged'          => $page
];

$properties_query = new Timber\PostQuery($query_args);

$context['properties_query']      = $properties_query;
$context['properties_page_show1'] = $properties_query->found_posts ? ( ( $page * $posts_per_page ) - $posts_per_page ) + 1 : 0;
$context['properties_page_show2'] = ( $page * $posts_per_page < $properties_query->found_posts ) ? $page * $posts_per_page : $properties_query->found_posts;
$context['properties_total']      = $properties_query->found_posts;

Timber::render(array('page-properties-listing.twig','page.twig'), $context);
