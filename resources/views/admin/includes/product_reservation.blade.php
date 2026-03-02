<div class="attr-mgt-view">
    <h2 class="title-prod">
        <i class="count icon-search"></i> @lang('product.cart_reservation')    
    </h2>
 {{--        <label> @lang('enable_product_reservation')</label>&nbsp;
        <div class="onoffswitch myonoffswitch">
            <input name="cartreservation" class="myonoffswitch-checkbox" id="myonoffswitch" type="checkbox">
            <label class="myonoffswitch-label" for="myonoffswitch">      
                <div class="onofftravelbox travelon-onoff">     
                  <span class="myonoffswitch-inner">@lang('product.on')</span>     
                  <span class="myonoffswitch-switch">@lang('product.off')</span>     
                </div>     
             <span class="myonoffswitch-circle circletravel"></span>     
            </label>  
        </div> --}}
        {{-- <div class="col-sm-4"> --}}
                <label> @lang('product.enable_product_reservation')</label>&nbsp;
                <div class="onoffswitch myonoffswitch">
                    <input name="cartreservation" class="myonoffswitch-checkbox" id="myonoffswitch2" ng-model="product.cartreservation" type="checkbox">
                    <label class="myonoffswitch-label" for="myonoffswitch">      
                        <div class="onofftravelbox">     
                          <span class="myonoffswitch-inner">@lang('product.on')</span>     
                          <span class="myonoffswitch-switch">@lang('product.off')</span>     
                        </div>     
                     <span class="myonoffswitch-circle"></span>     
                    </label>  
                </div>
        {{--     </div> --}}

       {{--  <% product.cartreservation %>        --}}       
</div>