<div class="rental-yield-calculator">
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
                                <input class="form-control" type="text" name="purchase_price" value="">
                            </div>
                        </div>
                    </div>
                    <div class="col-6 col-xl-5 offset-xl-1">
                        <div class="form-group">
                            <label>Monthly Rent in &pound;</label>
                            <div class="input-group">
                                <div class="input-group-prepend">
                                    <div class="input-group-text">&pound;</div>
                                </div>
                                <input class="form-control" type="text" name="monthly_rent" value="">
                            </div>
                        </div>
                    </div>
                </div>

                <button class="btn btn-lg btn-primary"><?php echo __( 'Calculate', 'propertyhive' ); ?></button>

            </div>
        </div>
        <div class="col-lg-4 col-md-5 offset-xl-1 pl-md-0">
            <div class="rental-yield-calculator-results text-center" id="results" style="display:none">

                <!--<h3><?php /*echo __( 'Results', 'propertyhive' ); */?></h3>-->
                <div>
                    <div class="form-group mb-2">
                        <label><?php echo __( 'Annual Rent', 'propertyhive' ); ?> (&pound;)</label>
                        <input type="text" name="annual_rent" value="" placeholder="" disabled class="form-control">
                    </div>
                    <div class="form-group mb-0">
                        <label><?php echo __( 'Rental Yield', 'propertyhive' ); ?> (%)</label>
                        <input type="text" name="rental_yield" value="" placeholder="" disabled class="form-control">
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
