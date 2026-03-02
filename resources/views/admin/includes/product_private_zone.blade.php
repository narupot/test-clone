<div class="attr-mgt-view">
    <h2 class="title-prod">
        <i class="count icon-search"></i> @lang('product.private_zone')  
    </h2>
    <div class="row">
        <div class="col-sm-4 form-group">
            <label for="">@lang('product.customer_group_allowed')</label>
            <select name="customer_group" ng-model="product.customer_group" multiple="multiple" class="multiple-selectw">
            <option value="">Select customer group</option>
                @if(count($customer_groups)>0)
                	@foreach($customer_groups as $custgroup)
                		@if(count($custgroup))
            	            <option value="{{$custgroup->customerGroupDesc->id}}">
                                {{$custgroup->customerGroupDesc->group_name}}
                            </option>	            
        	            @endif
        	        @endforeach
                @endif
            </select>
        </div>
    </div>                           
</div>