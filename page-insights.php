<?php /* Template Name: Insights */

$context = Timber::get_context();
$post = new TimberPost();
$context['post'] = $post;

$posts_total_text_parts = [];

$posts_query = new Timber\PostQuery([
    'post_type' => 'post',
    'posts_per_page' => get_option('posts_per_page'),
    //'posts_per_page' => 1,
    'paged' => get_query_var('paged') ? get_query_var('paged') : 1
]);

$posts_total_text_parts[] = $posts_query->found_posts;
$posts_total_text_parts[] = get_the_title($post);

if(isset($_GET['branch_id'])) {
    $posts_total_text_parts[] = '<span class="branch-name">on '. get_the_title($_GET['branch_id']) . '</span>';
}

$context['posts_total_text'] = join(' ', $posts_total_text_parts);
$context['posts'] = $posts_query;

Timber::render(array('archive.twig'), $context);
