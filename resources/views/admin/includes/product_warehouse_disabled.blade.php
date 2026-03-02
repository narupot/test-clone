@if(!$warehouse_enabled)
  <div class="form-group">
      <label class="check-wrap">
          <input type="checkbox" ng-model="product.defaultwhouse.isunlimited"><span class="chk-label"> @lang('product.is_unlimited') </span>
      </label>
  </div>
  <div class="row half varient-form-row">
      <div class="col-sm-5">
        <div ng-if="!product.defaultwhouse.isunlimited" class="border-wrap form-group">
          <div class="form-group">
            @lang('product.stock')<i class="strick">*</i>
            <input type="text" ng-model="product.defaultwhouse.stock_value" name="stock_value" required="">        
          </div>
          <div class="form-group">
            @lang('product.low_stock')
            <input type="text" ng-model="product.defaultwhouse.lowstock_value">
          </div>
          <div class="form-group">
            @lang('product.safety_stock')
            <input type="text" ng-model="product.defaultwhouse.safetystock_value">
          </div>
        </div>
      </div>
    </div>
@endif