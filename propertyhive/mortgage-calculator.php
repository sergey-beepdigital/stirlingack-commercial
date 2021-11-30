<?php
/**
 * The Template for displaying the mortgage calculator form and results
 *
 * Override this template by copying it to yourtheme/propertyhive/mortgage-calculator.php
 *
 * NOTE: For the calculation to still occur it's important that most classes, ids and input names remain unchanged
 */

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly
?>

<div class="mortgage-calculator">
    <div class="row">
        <div class="col-xl-7 col-lg-8 col-md-7">
            <div class="calculator-fields-wrap">
                <div class="row">
                    <div class="col-6 col-xl-5">
                        <div class="form-group">
                            <label>Purchase Price in &pound;</label>
                            <div class="input-group">
                                <div class="input-group-prepend">
                                    <div class="input-group-text">&pound;</div>
                                </div>
                                <input class="form-control" type="text" name="purchase_price" value="<?php echo $atts['price']; ?>" placeholder="e.g 500,000">
                            </div>
                        </div>
                    </div>
                    <div class="col-6 col-xl-5 offset-xl-1">
                        <div class="form-group">
                            <label>Deposit Amount in &pound;</label>
                            <div class="input-group">
                                <div class="input-group-prepend">
                                    <div class="input-group-text">&pound;</div>
                                </div>
                                <input class="form-control" type="text" name="deposit_amount" value="" placeholder="e.g 75,000">
                            </div>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-6 col-xl-5">
                        <div class="form-group">
                            <label>Interest Rate Percentage</label>
                            <div class="input-group">
                                <input class="form-control" type="text" name="interest_rate" value="" placeholder="e.g 3.2">
                                <div class="input-group-append">
                                    <div class="input-group-text">%</div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-6 col-xl-5 offset-xl-1">
                        <div class="form-group">
                            <label>Repayment Period in Years</label>
                            <div class="input-group">
                                <input class="form-control" type="text" name="repayment_period" value="" placeholder="e.g 25">
                                <div class="input-group-append">
                                    <div class="input-group-text">Yrs</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <button class="btn btn-lg btn-primary"><?php echo __( 'Calculate', 'propertyhive' ); ?></button>
            </div>
        </div>
        <div class="col-lg-4 col-md-5 offset-xl-1 pl-md-0">
            <div class="mortgage-calculator-results text-center" id="results" style="display:none">
                <div>
                    <h3 class="mb-3"><?php echo __( 'Monthly Costs', 'propertyhive' ); ?></h3>

                    <div class="form-group mb-2">
                        <label><?php echo __( 'Repayment', 'propertyhive' ); ?> (&pound;)</label>
                        <input type="text" name="repayment" value="" placeholder="" disabled class="form-control">
                    </div>
                    <div class="form-group mb-0">
                        <label><?php echo __( 'Interest Only', 'propertyhive' ); ?> (&pound;)</label>
                        <input type="text" name="interest" value="" placeholder="" disabled class="form-control">
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
