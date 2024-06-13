<?php /* Template Name: Properties Listing */

$context                = Timber::get_context();
$post                   = new TimberPost();
$context['post']        = $post;
$context['list_view']   = $_GET['list_view'] ? $_GET['list_view'] : 'grid';
$context['list_sortby'] = $_GET['sortby'] ? $_GET['sortby'] : 'price-desc';

$page           = get_query_var( 'page' ) ? get_query_var( 'page' ) : 1;
$posts_per_page = get_option( 'posts_per_page' );

$link_query['address_keyword'] = ! empty( $_GET['address_keyword'] ) ? $_GET['address_keyword'] : '';
$link_query['minimum_desks'] = ! empty( $_GET['minimum_desks'] ) ? $_GET['minimum_desks'] : '';
$link_query['maximum_desks'] = ! empty( $_GET['maximum_desks'] ) ? $_GET['maximum_desks'] : '';
$link_query['sortby'] = $context['list_sortby'];

$context['link_query'] = $link_query;

$query_args = [
    'post_status'    => 'publish',
    'post_type'      => 'sa_property',
    'posts_per_page' => get_option( 'posts_per_page' ),
    'paged'          => $page,
    'meta_key'       => 'price_desk_per_month',
    'orderby'        => [ 'meta_value_num' => $context['list_sortby'] == 'price-asc' ? 'ASC' : 'DESC' ]
];

if ( isset( $_GET['address_keyword'] ) && ! empty( $_GET['address_keyword'] ) ) {
    $query_args['meta_query'][] = [
        'key'     => 'address_postcode',
        'value'   => $_GET['address_keyword'],
        'compare' => 'LIKE'
    ];
}

if ( isset( $_GET['minimum_desks'] ) && ! empty( $_GET['minimum_desks'] ) ) {
    $query_args['meta_query'][] = [
        'key'     => 'availability_size_desks_from',
        'value'   => $_GET['minimum_desks'],
        'compare' => '<=',
        'type'    => 'NUMERIC'
    ];
}

if ( isset( $_GET['maximum_desks'] ) && ! empty( $_GET['maximum_desks'] ) ) {
    $query_args['meta_query'][] = [
        'key'     => 'availability_size_desks_to',
        'value'   => $_GET['maximum_desks'],
        'compare' => '>=',
        'type'    => 'NUMERIC'
    ];
}

if($context['list_view'] == 'map') {
    $query_args['posts_per_page'] = -1;
}

$properties_query = new Timber\PostQuery( $query_args );

if($context['list_view'] == 'map') {
    $property_coords = [];
    $properties_list = $properties_query->get_posts();

    foreach ( $properties_list as $property ) {
        if(!empty($property->address_map) && !empty($property->address_map['lat'] && $property->address_map['lng'])) {
            $gallery = $property->gallery;

            $property_coords[] = [
                'title' => $property->post_title,
                'lat'   => $property->address_map['lat'],
                'lng'   => $property->address_map['lng'],
                'image' => $gallery ? $gallery[0]['url'] : '',
                'url'   => get_the_permalink( $property->ID )
            ];
        }
    }

    $context['map_properties'] = $property_coords;
}

$context['properties_query']      = $properties_query;
$context['properties_page_show1'] = $properties_query->found_posts ? ( ( $page * $posts_per_page ) - $posts_per_page ) + 1 : 0;
$context['properties_page_show2'] = ( $page * $posts_per_page < $properties_query->found_posts ) ? $page * $posts_per_page : $properties_query->found_posts;
$context['properties_total']      = $properties_query->found_posts;

Timber::render( array( 'page-properties-listing.twig', 'page.twig' ), $context );
