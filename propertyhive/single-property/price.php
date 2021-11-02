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

</div>
