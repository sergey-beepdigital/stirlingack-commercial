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

$context = Timber::get_context();
$post = Timber::query_post();
$context['post'] = $post;

if ( post_password_required( $post->ID ) ) {
	Timber::render( 'single-password.twig', $context );
} else {
    $links = array_map( function ( $category ) {
        return sprintf(
            '<a href="%s" class="link link_text">%s</a>', // Шаблон вывода ссылки
            esc_url( get_category_link( $category ) ), // Ссылка на рубрику
            esc_html( $category->name ) // Название рубрики
        );
    }, get_the_category() );

    $context['category_links'] = implode(', ', $links);

    $related_posts_query = Timber::query_posts([
        'post_type' => 'post',
        'post__not_in' => [$post->ID],
        'posts_per_page' => 2
    ]);
    $context['related_posts_query'] = $related_posts_query;

	Timber::render( array( 'single-' . $post->ID . '.twig', 'single-' . $post->post_type . '.twig', 'single.twig' ), $context );
}
