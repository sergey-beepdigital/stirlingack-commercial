<div class="rental-affordability-calculator">
    <div class="row">
        <div class="col-xl-8">
            <div class="calculator-fields-wrap">
                <div class="row">
                    <div class="col-lg-8">
                        <div class="form-group">
                            <label><?php echo __( 'How would you like to calculate your affordability?', 'propertyhive' ); ?></label>
                            <select name="calculation_basis" id="calculation_basis" class="form-control">
                                <option value=""></option>
                                <option value="rent"><?php echo __( 'Using monthly rent', 'propertyhive' ); ?></option>
                                <option value="income"><?php echo __( 'Using your total annual income', 'propertyhive' ); ?></option>
                            </select>
                        </div>

                        <div class="form-group" id="from_rent" style="display:none">
                            <label><?php echo __( 'Monthly rent', 'propertyhive' ); ?></label>
                            <div class="input-group">
                                <div class="input-group-prepend">
                                    <div class="input-group-text">£</div>
                                </div>
                                <input class="form-control" type="text" name="rent" value="" placeholder="e.g. 600">
                            </div>
                        </div>

                        <div class="form-group" id="from_income" style="display:none">
                            <label><?php echo __( 'Annual income', 'propertyhive' ); ?></label>
                            <div class="input-group">
                                <div class="input-group-prepend">
                                    <div class="input-group-text">£</div>
                                </div>
                                <input class="form-control" type="text" name="income" value="" placeholder="e.g. 18000">
                            </div>
                        </div>

                        <button class="btn btn-lg btn-primary"><?php echo __( 'Calculate', 'propertyhive' ); ?></button>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-4 pl-xl-0">
            <div id="results_rent" class="rental-affordability-results blue-results-box text-center" style="display:none">
                Your total income will need to be:

                <h3 id="results_rent_total" class="mb-3">£-</h3>

                If a guarantor is required then they will also need to have a total income of:

                <h3 id="results_rent_guarantor">£-</h3>
            </div>

            <div id="results_income" class="rental-affordability-results blue-results-box text-center" style="display:none">
                With this total income the monthly rent that you might be able to afford would be:

                <h3 id="results_income_total" class="mb-3">£-</h3>

                If a guarantor is required then they will also need to have a total income of:

                <h3 id="results_income_guarantor">£-</h3>
            </div>
        </div>
    </div>

</div>
