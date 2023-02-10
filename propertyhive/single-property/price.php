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

    $tenure_type = get_post_meta($post->ID, '_tenure_type', true);
    if(!empty($tenure_type) && in_array($tenure_type,['Short Let'])) {
        $flag_html = '<span class="flag" style="padding: 7px 20px;color: #FFF;background: #151e46;">Short Let</span>';
    }

    if(!empty($flag_html)) {
        echo '<div class="property-flag-wrap">'.$flag_html.'</div>';
    }

    ?>

</div>
