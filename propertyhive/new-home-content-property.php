<?php

global $property, $propertyhive_loop;

// Store loop count we're currently on
if ( empty( $propertyhive_loop['loop'] ) )
    $propertyhive_loop['loop'] = 0;

// Store column count for displaying the grid
if ( empty( $propertyhive_loop['columns'] ) )
    $propertyhive_loop['columns'] = apply_filters( 'loop_search_results_columns', 1 );

// Ensure visibility
if ( ! $property )
    return;

// Increase loop count
++$propertyhive_loop['loop'];

// Extra post classes
$classes = array('dev-item');
if ( 0 == ( $propertyhive_loop['loop'] - 1 ) % $propertyhive_loop['columns'] || 1 == $propertyhive_loop['columns'] )
    $classes[] = 'first';
if ( 0 == $propertyhive_loop['loop'] % $propertyhive_loop['columns'] )
    $classes[] = 'last';
if ( $property->featured == 'yes' )
    $classes[] = 'featured';
?>

<li <?php post_class( $classes ); ?>>
    <div class="dev-item--floorplan">

        <?php if(!empty($property->_floorplan_urls)) {
            $first_image = reset($property->_floorplan_urls); ?>

            <a href="<?php echo $first_image['url']; ?>" data-fancybox="floorplan">
                <img src="<?php echo $first_image['url']; ?>">
            </a>

        <?php } ?>

    </div>
    <div class="dev-item--details">
        <?php

        Timber::render('templates/propertyhive/parts/residential-details.twig',[
            'bedrooms' => $property->bedrooms,
            'bathrooms' => $property->bathrooms,
            'reception_rooms' => $property->reception_rooms
        ]);

        ?>
        <h6><?php echo $property->post_title; ?></h6>
    </div>
    <div class="dev-item--price">
        <div class="price-box">
            Price From
            <div class="price"><?php echo $property->get_formatted_price(); ?></div>
        </div>
        <?php

        $template_assistant = PH_Template_Assistant::instance();
        $template_assistant->add_flag_single();

        ?>
    </div>
</li>
