<div class="attr-mgt-view">
    <h2 class="title-prod">
        <i class="count icon-search"></i> @lang('product.points')    
    </h2>
    
  	<div class="form-group ">
  		<label>@lang('product.earn') </label>
  		<span class="measure-wrap">
                <input type="text" ng-model="product.earn_point">
                <span class="measure-unit">@lang('product.points')</span>
        </span>
  	</div>
  	<div class="form-group ">
  		<label>@lang('product.spent')</label>
  		<span class="measure-wrap">
                <input type="text" ng-model="product.spent_point">
                <span class="measure-unit">@lang('product.points')</span>
        </span>
  	</div>   
                            
</div>