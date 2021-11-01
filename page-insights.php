<?php /* Template Name: Insights */

$context = Timber::get_context();
$post = new TimberPost();
$context['post'] = $post;

$posts_total_text_parts[] = $posts_query->found_posts;
$posts_total_text_parts[] = get_the_title($post);

$posts_query_args = [
    'post_type' => 'post',
    'posts_per_page' => get_option('posts_per_page'),
    //'posts_per_page' => 1,
    'paged' => get_query_var('paged') ? get_query_var('paged') : 1
];

if(!empty($_GET['branch_id'])) {
    $posts_query_args['meta_query'] = [
        [
            'key' => 'post_branch_id',
            'value' => $_GET['branch_id']
        ]
    ];
    $posts_total_text_parts[] = '<span class="branch-name">on '. get_the_title($_GET['branch_id']) . '</span>';
}

$context['posts_total_text'] = join(' ', $posts_total_text_parts);
$context['posts_query'] = new Timber\PostQuery($posts_query_args);

Timber::render(array('archive.twig'), $context);
