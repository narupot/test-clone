@extends('layouts/admin/default')

@section('title')
    @lang('admin_shipping..add_shipping_profile')
@stop

@section('header_styles')
 <link rel="stylesheet" href="{{ Config('constants.admin_css_url') }}select.css" />
<script type="text/javascript">    
    var countryListData = {!! $country_data !!};
   var getShippingState = "{{action('Admin\ShippingProfile\ShippingProfileController@getShippingState')}}";
</script>
<!--page level css -->

<!-- end of page level css -->
    
@stop

@section('content')
    <div class="content ng-cloak" ng-controller="shipProfileCtrl" ng-cloak>
        @if(Session::has('succMsg'))
        <div class="alert alert-success alert-dismissable margin5">
            <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
            <strong>@lang('admin_common.success'):</strong> {{ Session::get('succMsg') }}
        </div>
        @endif 
        <form id="cmsForm" action="{{ action('Admin\ShippingProfile\ShippingProfileController@store') }}" method="post" class="">
            <div class="header-title">
                <h1 class="title">@lang('admin_shipping.add_shipping_profile')</h1>
                <div class="float-right">
                    <a href="{{ action('Admin\ShippingProfile\ShippingProfileController@index') }}" class="btn btn-back">@lang('admin_common.back')</a>
                    <button  name="submit_type" type="submit" value="save_and_continue" class="btn btn-secondary save_and_continue" >
                      @lang('admin_common.save_and_continue')                      
                    </button>
                    <button name="submit_type" value="save" class="btn btn-save" type="submit">@lang('admin_common.save')</button>
                </div>
            </div>       
            <div class="content-wrap">
                
                {{ csrf_field() }}
                <div class="form-group row">
                    <div class="col-md-6">
                    <label for="form-text-input">@lang('admin_shipping.shipping_key') <i class="strick">*</i></label> 
                        <input type="text" name="shipping_key" value="{{ old('shipping_key') }}">
                        @if ($errors->has('shipping_key'))
                            <p class="error error-msg">{{ $errors->first('shipping_key') }}</p>
                        @endif
                    </div>
                </div> 

                <div class="form-group row">
                 <div class="col-md-6">
                    <label for="form-text-input">@lang('admin_shipping.name') <i class="strick">*</i></label>
                        {!! CustomHelpers::fieldstabWithLanuage([['field'=>'text', 'name'=>'name', 'label'=>' ', 'errorkey'=>'ship_name']], '1', $errors) !!}

                    </div>
                </div>
                <div class="form-group row">
                    <div class="col-md-6">
                    <label for="form-text-input">@lang('admin_shipping.type')</label>
                     {!! Form::select('shippingType', ['flat-rate'=>Lang::get('admin_shipping.flat_rate'), 'free-shipping'=>Lang::get('admin_shipping.free_shipping')],  null,['id'=>'shippingType']) !!}
                     @if ($errors->has('shippingType'))
                        <p id="comment-error" class="error error-msg">{{ $errors->first('shippingType') }}</p>
                    @endif
                    </div>
                </div>
                  
                <div class="form-group" id="flat-row">
                    <div class="condition-rulebox">
                        <div class="form-group row flat-row-field">
                            <div class="col-sm-6 radio-group">
                            <label>@lang('admin_shipping.use_with_promo')</label>
                                <label class="radio-wrap"><input type="radio" name="promo" value="1"><span class="radio-label "> @lang('admin_shipping.yes')</span></label>
                                <label class="radio-wrap"><input type="radio" name="promo" value="0" checked="checked"> <span class="radio-label ">@lang('admin_shipping.no') </span></label>
                            </div>
                        </div>
                        <div class="form-group row flat-row-field">
                            <div class="col-sm-6">
                            <label>@lang('admin_shipping.type')</label>
                                  {!! Form::select('flat_rate_type', ['per-item'=>Lang::get('admin_shipping.per_item')],  null,[ 'class'=>'custom-select']) !!}
                            </div>
                        </div>                
                        <div class="row form-group flat-row-field">
                            <div class="col-sm-6">
                            <label>@lang('admin_shipping.shipping_fee')</label>
                                <input type="text" value="{{old('shipping_fee')}}" name="shipping_fee"> 
                                @if ($errors->has('shipping_fee'))
                                <p id="comment-error" class="error error-msg">{{ $errors->first('shipping_fee') }}</p>
                                @endif
                            </div>                              
                        </div>
                        <div class="row form-group free-ship-field">
                            <div class="col-sm-6">
                            <label>@lang('admin_shipping.minimum_order_amount')</label>
                                {!! Form::text('freeShipamt', old('freeShipamt'), ['placeholder'=>'Enter Amount...'] ) !!}
                                 @if ($errors->has('freeShipamt'))
                                <p id="comment-error" class="error error-msg">{{ $errors->first('freeShipamt') }}</p>
                                @endif
                            </div>                              
                        </div>                          
                        <div class="row form-group search-select" id="specific_country">
                            <div class="col-sm-9">                              
                                <label>@lang('admin_shipping.ship_to_specific_countries')</label>
                                <select size="5" multiple ng-model="availableCountry" class="multiselect form-control" ng-options="country as country.name for country in countryListData">
                                </select> 
                                 <div class="search-select-btn">
                                     <button type="button" ng-click="moveAll(countryListData,selectedCountry,'addAlList')" id="right_All_1" class="btn btn-block"><i class="glyphicon glyphicon-forward"></i></button>
                                     <button type="button" ng-click="moveItem(availableCountry, countryListData,selectedCountry ,'addItems')" id="right_Selected_1" class="btn btn-block"><i class="glyphicon glyphicon-chevron-right"></i></button>
                                     <button type="button" ng-click="moveItem(countrySelected, selectedCountry,countryListData,'removeItems')" id="left_Selected_1" class="btn btn-block"><i class="glyphicon glyphicon-chevron-left"></i></button>
                                     <button type="button" ng-click="moveAll(selectedCountry,countryListData ,'removeAllList')" id="left_All_1" class="btn btn-block"><i class="glyphicon glyphicon-backward"></i></button>
                                </div>                                
                                <select size="5" class="multiselect custom-select" multiple ng-model="countrySelected" ng-options="country as country.name for country in selectedCountry">
                                </select>
                                @if ($errors->has('countryId'))
                                <p id="comment-error" class="error error-msg">{{ $errors->first('countryId') }}</p>
                                @endif
                                <input type="hidden" name="countryId" value="<%countryArr.join(',')%>" ng-modal="countryArr">
                            </div>
                        </div>

                        <div class="row form-group flat-row-field">
                            <div class="col-sm-8">
                            <label style="margin-bottom: 5px;">@lang('admin_shipping.rest_of_uncheck_country_action')</label>
                                <label class="radio-wrap"><input type="radio" name="rest_country" class="rest_country" value="1">  <span class="radio-label ">@lang('admin_shipping.set_delivery_for_the_rest_of_the_country')</span></label>
                                <div class="delv-input form-group">
                                    <input type="text" class="rest_country_opt" placeholder="price" name="rest_country_price" style="width: 20%" value="{{old('rest_country_price')}}">
                                    <select style="width: 28%; display: none;" class="rest_country_opt" name="rest_country_type">
                                        <option value="per-item">@lang('admin_shipping.per_item')</option>
                                        <option value="per-order">@lang('admin_shipping.per_order')</option>
                                    </select>
                                </div>
                                <label class="radio-wrap"><input type="radio" name="rest_country" class="rest_country" value="0" checked="checked">
                                <span class="radio-label ">@lang('admin_shipping.not_ship_at_all')</span>
                                </label>
                            </div>                          
                            
                        </div>
                        <div class="row form-group flat-row-field">
                            <!-- country list selection -->
                            <div class="col-sm-6 country-list" ng-if="selectedCountry.length">
                               <select ng-model="country_manage.country_selection" ng-options="item.id as item.name for item in selectedCountry track by item.id" ng-change="countryChangeHandler()"><option value="">-- choose an option --</option></select>
                              <!-- Display List of country -->
                               {{-- <div class="country-list">
                                  <ul ng-repeat="list in country_list.countries">
                                    <span><%list.country%></span> 
                                    <li ng-repeat"state in list.states">
                                        <span ng-bind="state"></span>
                                    </li>
                                  </ul>
                               </div> --}}
                            </div>
                            <!-- list of province/state -->                        
                            <div class="form-group" ng-show="country_manage.show_state== true && provinceData.length"> 
                                <label class="col-sm-12">@lang('admin_shipping.apply_city/state_only_to_exception_city/state_(not_delivery_if_city/state_appear_in_below:)')</label>
                                <div class="col-sm-6">             
                                    <div class="form-group custom-width">
                                        <select multiple options="provinceData" ng-model="selectedProvince" class="select province-selection" name="shipping_province[]" ng-change="provinceChange()">
                                        <option ng-repeat="x in provinceData" value="<%x.id%>" on-finish-render=""><%x.name%></option>
                                        </select>
                                    </div>
                                    <label class="check-wrap"><input type="checkbox" name="province_mode" value="1"> <span class="chk-label">@lang('admin_shipping.include_mode_(if_checked,_list_above_will_be_delivery_only)')</span></label>
                                </div>                        
                            </div>        
                        </div>
                        <!-- selected country & province -->
                        <div class="row form-group flat-row-field">
                            <input type="hidden" name="country_province" value="<%country_manage.country_province | json%>" />
                            <div class="selecte-country-province" ng-if="country_manage.country_province.length">
                                <div class="form-group chosen-container-multi" ng-repeat="item in country_manage.country_province track by item.id">
                                    <h4 class="country-name"><%item.name%></h4>
                                    <ul class="chosen-choices">
                                        <li class="search-choice" ng-repeat="prov in item.province track by $index">
                                            <span><%prov.name%></span>
                                            <a class="search-choice-close" data-option-array-index="1" ng-click="removeCp(item, prov, $index)"></a>
                                        </li>
                                    </ul>                            
                                </div>
                            </div>
                        </div>
                        <div class="row form-group flat-row-field">
                            <label class="col-sm-12">@lang('admin_shipping.apply_product_only_to_exception_city/state_(not_delivery_if_city/state_appear_in_below:)')</label>
                            <div class="form-group custom-width">
                                <div class="col-sm-6">
                                    <div class="form-group">
                                        <select multiple name="shipping_product[]" class="select">
                                            @foreach($productList as $productRes)
                                                <option value="{{$productRes['id']}}">{{$productRes['url']}}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <label class="check-wrap"><input type="checkbox" name="product_mode" value="1"> <span class="chk-label">@lang('admin_shipping.include_mode_(if_checked,_list_above_will_be_delivery_only)')</span></label>
                                </div>
                            </div> 
                        </div>
                    </div>
                </div>                                                        
            </div>
        </form>
    </div>
      
@stop

@section('footer_scripts')
 
<!-- begining of page level js -->
 @include('includes.gridtablejsdeps')
 <script src="{{ Config('constants.admin_js_url') }}chosen.jquery.min.js"></script>

 <script stype="text/javascript" src="{{Config('constants.angular_app_url')}}controller/shipProfileCtrl.js"></script>
<script src="{{ Config('constants.admin_js_url') }}shipping.js"></script>
<script type="text/javascript">
    jQuery('.select').chosen();
</script>
<!-- end of page level js --> 
<script type="text/javascript">
    var shippingTypeChange = function(){};
    (function($){
        changeShipType();

        $(document).on('change', '#shippingType', function(){
            changeShipType();
        });

        $('body').on('change','.rest_country',function(event){
            var rVal = $(this).val();
            if(rVal == 1){
                $('.rest_country_opt').show();
            }else{
                $('.rest_country_opt').hide();
            }
        });

        $('body').on('change','#country_type',function(){
            var val = $(this).val();
            if(val == 'all'){
             $('#specific_country').hide();
            }else{
             $('#specific_country').show();
            }
        });

        function changeShipType(){
            var val = $('#shippingType').val();
            if(val == 'table-rate'){
                $('#table-row').show();
                $('#flat-row').hide();
            }else if(val == 'flat-rate'){
                $('#table-row').hide();
                $('#flat-row').show();
                $('.free-ship-field').hide();
                $('.flat-row-field').show();
            }else{
                $('#table-row').hide();
                $('#flat-row').show();
                $('.flat-row-field').hide();
                $('.free-ship-field').show();
                shippingTypeChange();
            }
        };

    })(jQuery);
</script>     
@stop
