<div class="stamp-duty-calculator">
    <div class="row">
        <div class="col-xl-8">
            <div class="calculator-fields-wrap">
                <div class="row">
                    <div class="col-xl-4">
                        <div class="form-group">
                            <label>Purchase Price</label>
                            <div class="input-group">
                                <div class="input-group-prepend">
                                    <div class="input-group-text">&pound;</div>
                                </div>
                                <input class="form-control" type="text" name="purchase_price" value="<?php echo $atts['price']; ?>" placeholder="e.g. 500,000">
                            </div>
                        </div>
                    </div>
                </div>

                <div class="form-group">
                    <div class="custom-control custom-checkbox">
                        <input type="checkbox" name="new_rates" id="new_rates" value="1" class="custom-control-input">
                        <label class="custom-control-label" for="new_rates">Property sale will complete on or between 1st of July and 30th of September 2021?</label>
                    </div>
                    <div class="custom-control custom-checkbox">
                        <input type="checkbox" name="ftb" id="ftb" value="1" class="custom-control-input">
                        <label class="custom-control-label" for="ftb">I'm a first time buyer</label>
                    </div>
                    <div class="custom-control custom-checkbox">
                        <input type="checkbox" name="btl_second" id="btl_second" value="1" class="custom-control-input">
                        <label class="custom-control-label" for="btl_second">Property is a buy-to-let or second home</label>
                    </div>
                    <div class="custom-control custom-checkbox">
                        <input type="checkbox" name="buyer_overseas" id="buyer_overseas" value="1" class="custom-control-input">
                        <label class="custom-control-label" for="buyer_overseas">Buyer is a non-UK resident</label>
                    </div>
                </div>

                <button class="btn btn-lg btn-primary"><?php echo __( 'Calculate', 'propertyhive' ); ?></button>
            </div>
        </div>
        <div class="col-xl-4 pl-xl-0">
            <div class="stamp-duty-calculator-results text-center" id="results" style="display:none">
                <div>
                    <!--<h3><?php /*echo __( 'Stamp Duty', 'propertyhive' ); */?></h3>-->
                    <div class="form-group mb-0">
                        <label><?php echo __( 'Stamp Duty', 'propertyhive' ); ?> (&pound;)</label>
                        <input type="text" name="stamp_duty" value="" placeholder="" disabled class="form-control">
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
