<div class="rental-yield-calculator">

    <div class="row">
        <div class="col-xl-5">
            <div class="form-group">
                <label><?php echo __( 'Purchase Price', 'propertyhive' ); ?> (&pound;)</label>
                <input class="form-control" type="text" name="purchase_price" value="">
            </div>
        </div>
        <div class="col-xl-5 offset-xl-1">
            <div class="form-group">
                <label><?php echo __( 'Monthly Rent', 'propertyhive' ); ?> (&pound;)</label>
                <input class="form-control" type="text" name="monthly_rent" value="">
            </div>
        </div>
    </div>

    <button class="btn btn-lg btn-primary"><?php echo __( 'Calculate', 'propertyhive' ); ?></button>

    <div class="rental-yield-calculator-results" id="results" style="display:none">

        <h4><?php echo __( 'Results', 'propertyhive' ); ?>:</h4>

        <label><?php echo __( 'Annual Rent', 'propertyhive' ); ?> (&pound;)</label>
        <input type="text" name="annual_rent" value="" placeholder="" disabled>

        <label><?php echo __( 'Rental Yield', 'propertyhive' ); ?> (%)</label>
        <input type="text" name="rental_yield" value="" placeholder="" disabled>
    </div>

</div>
