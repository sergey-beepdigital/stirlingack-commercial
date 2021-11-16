<?php
/**
 * Single Property Price
 *
 * @author 		PropertyHive
 * @package 	PropertyHive/Templates
 * @version     1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

global $post, $property;
?>
<div class="price">

	<?php echo $property->get_formatted_price(); ?>
	
	<?php
       	if ( $price_qualifier != '' )
        {
        	echo ' <span class="price-qualifier">' . $price_qualifier . '</span>';
       	}
    ?>

    <?php

    $flag_html = '';
    ob_start();
    $template_assistant = PH_Template_Assistant::instance();
    $template_assistant->add_flag_single();
    $flag_html = ob_get_contents();
    ob_end_clean();

    if(!empty($flag_html)) {
        echo '<div class="property-flag-wrap">'.$flag_html.'</div>';
    }

    ?>

</div>
