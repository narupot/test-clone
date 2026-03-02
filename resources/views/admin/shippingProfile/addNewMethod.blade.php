@extends('layouts/admin/default')
 
@section('title')
    @lang('admin_shipping.shipping_profile')
@stop

@section('header_styles')
<link rel="stylesheet" type="text/css" href="{{Config('constants.css_url') }}ui-grid-unstable.css"> 

    <!--page level css -->

    <link rel="stylesheet" href="{{ Config('constants.admin_css_url') }}dataTables.bootstrap.css"/>
    <!-- end of page level css -->
    
@stop

@section('content')
    <div class="content">
        <form action="{{action('Admin\ShippingProfile\ShippingRateTableController@saveShippingRateProfile')}}" method="post" name="update" enctype="multipart/form-data">
        <div class="header-title">
            <h1 class="title">Add New Shipping Rate Table Profile</h1>
            <div class="float-right">
                <a href="{{ action('Admin\ShippingProfile\ShippingRateTableController@index') }}" class="btn btn-back">&lt;@lang('admin_common.back')</a>
                <button  name="submit_type" type="submit" value="save_and_continue" class="btn btn-secondary save_and_continue" ng-disabled="prdData.loading.disableBtn">@lang('admin_common.save_and_continue')</button>
                <button name="submit_type" value="save" class="btn btn-save" type="submit">@lang('admin_common.save')</button> 
            </div>
        </div>
        <div class="content-wrap clearfix">
            <div class="breadcrumb">
                <ul class="bredcrumb-menu">
                    {!!getBreadcrumbAdmin('config','shipping-table-rates')!!}
                </ul>
            </div>
            <div class="content-left">
                <div class="tablist">                    
                    <ul class="nav nav-tabs">
                        <li class="nav-item"><a class="nav-link show active" data-toggle="tab" data-target="#general">@lang('admin_shipping.general')</a></li>
                        <li class="nav-item"><a class="nav-link" data-toggle="tab" data-target="#store_and_customer_group">@lang('admin_shipping.store_and_customer_group')</a></li>
                    </ul>
                </div>
            </div>
            
            <div class="content-right">
                {{ csrf_field() }}
                <div class="tab-content">
                    <div id="general" class="tab-pane fade show active">
                        <div>
                            <h2 class="title-prod">@lang('admin_shipping.general')</h2>
                            <!-- //////// Start ///// -->
                            <div class="row">
                                <div class="form-group col-sm-12" id="shipping-rate-table">
                                    <div class="condition-rulebox">
                                        <div class="form-group row shipping-rate-table-field">
                                            <div class="col-sm-6">
                                                <label>@lang('admin_shipping.name')</label>
                                                <input type="text" name="name" value="" required > 
                                            </div>
                                        </div>

                                        <div class="form-group row shipping-rate-table-field">
                                            <div class="col-sm-6">
                                                <label>@lang('admin_shipping.status')</label>
                                                {!! Form::select('status', ['1'=>Lang::get('admin_shipping.active'),'0'=>Lang::get('admin_shipping.deactive')],  null,[ 'class'=>'custom-select']) !!}
                                            </div>
                                        </div>
                                        <div class="form-group row shipping-rate-table-field">
                                            <div class="col-sm-6">
                                                <label>@lang('admin_shipping.comment')</label>
                                                 <textarea name="comment"></textarea>
                                            </div>
                                        </div>                
                                        <div class="row form-group shipping-rate-table-field">
                                            <div class="col-sm-6">
                                                <label>@lang('admin_shipping.minimal_rate')</label>
                                                <input type="text" value="0" name="minimal_rate" required > 
                                                @if ($errors->has('minimal_rate'))
                                                <p id="minimal-rate-error" class="error error-msg">{{ $errors->first('minimal_rate') }}</p>
                                                @endif
                                            </div>                              
                                        </div>
                                        <div class="row form-group shipping-rate-table-field">
                                            <div class="col-sm-6">
                                                <label>@lang('admin_shipping.maximal_rate')</label>
                                                <input type="text" value="999999" name="maximal_rate" required > 
                                                @if ($errors->has('maximal_rate'))
                                                <p id="maximal-rate-error" class="error error-msg">{{ $errors->first('maximal_rate') }}</p>
                                                @endif
                                            </div>                              
                                        </div>
                                        <div class="row form-group shipping-rate-table-field">
                                            <div class="col-sm-6">
                                                <label>@lang('admin_shipping.shipping_calculation_type')</label>
                                                {!! Form::select('shipping_calculation_type', ['0'=>Lang::get('admin_shipping.sum_up_rate'),'1'=>Lang::get('admin_shipping.select_minimal_rate'),'2'=>Lang::get('admin_shipping.select_maximal_rate')],  null,[ 'class'=>'custom-select']) !!}
                                            </div>                              
                                        </div>
                                        <div class="row shipping-rate-table-field">
                                            <div class="col-sm-9">
                                               <label>@lang('admin_shipping.profile_logo')<i class="strick">*</i></label> 
                                                <div class="form-group mt-3">                                   
                                                    <label class="radio-wrap">
                                                        <input type="radio" name="logo_type" id="presetLogo" checked="checked" value="preset_logo"> <span class="radio-label">@lang('admin_shipping.select_preset_logo')</span>
                                                    </label>
                                                </div> 

                                                <div class="mb-2">                                   
                                                    <label class="radio-wrap form-group">
                                                        <input type="radio" name="logo_type" id="customLogo" value="custom_logo"> <span class="radio-label">@lang('admin_shipping.select_custome_logo')</span>
                                                    </label>
                                                    @if(count($predefined_ship_logos)>0)
                                                    <ul class="custom-uploadimg">
                                                        @foreach($predefined_ship_logos as $key => $shipper)
                                                        <li>
                                                             <label class="radio-wrap">
                                                                <input type="radio" name="shipping_logo" value="{{$shipper['url']}}" @if($shipper['default']) checked="checked" @endif >
                                                                <span class="radio-label"></span>
                                                                <div class="labelimg-box">
                                                                    <img src="{{$shipper['url']}}">
                                                                </div>
                                                            </label>                                        
                                                        </li>
                                                        @endforeach
                                                    </ul>
                                                    @endif
                                                    <div class="form-group custom-lblimg">
                                                        <input type="file" name="shipping_logo" accept=".png, .jpg, .jpeg">
                                                        @if($errors->has('shipping_logo'))
                                                            <p class="error error-msg">{{ $errors->first('shipping_logo') }}</p>
                                                        @endif
                                                    </div> 

                                                </div>                                 
                                            </div>                                   
                                        </div>
                                        <div class="row form-group shipping-rate-table-field">
                                            <label class="col-sm-12 check-wrap mb-2">
                                                <input type="checkbox" name="use_dimension_weight" checked="checked" id="chkb_dimension_weight">
                                                <span class="chk-label">@lang('admin_shipping.use_dimension_weight')</span>
                                            </label>
                                            <div id="dimension_weight_container" class="">
                                                <div class="col-sm-12 radio-group mb-2">
                                                    <label class="radio-wrap">
                                                        <input type="radio" name="dimension_weight_type" checked="checked" value="0" ><span class="radio-label">@lang('admin_shipping.maximum_weight')</span>
                                                    </label>
                                                    <label class="radio-wrap">
                                                        <input type="radio" name="dimension_weight_type" value="1"><span class="radio-label">@lang('admin_shipping.less_weight')</span>
                                                    </label>
                                                </div>
                                                <div class="col-sm-12" id="dimension_weight_content">
                                                    <label>@lang('admin_shipping.factor')</label>
                                                    <input type="number"  name="dimension_factor" value="0">
                                                </div> 
                                            </div>                         
                                      </div>
                                </div>

                            </div>
                        </div>
                            <!-- ////// End ///// -->
                        </div>
                    </div>
                    <div id="store_and_customer_group" class="tab-pane fade">                      
                        <div class="attr-variant-view">
                            <h2 class="title-prod">@lang('admin_shipping.customer_group')</h2>
                            <!-- ////// Start ///// -->
                            <div class="row">
                            <div class="form-group col-sm-12" id="shipping-rate-table">
                                <div class="condition-rulebox">
                                    
                                    <div class="row form-group shipping-rate-table-field">
                                        <div class="col-sm-6">
                                            <label>@lang('admin_shipping.customer_group')</label>
                                            <select class="multiple-selectw" name="customer_group[]" multiple="multiple" class="multiple-selectw">
                                                <option value="">--- Select---</option>
                                                @foreach($custGroup as $cus_key => $cust)
                                                <option value="{{$cust['id']}}">{{$cust['group_name']}}</option>
                                                @endforeach
                                            </select>
                                        </div>                              
                                    </div>

                            </div>

                        </div>
                        </div>

                            <!-- ////// End  ////// -->
                                                       
                        </div>                           
                                           
                    </div>

                    <div id="import" class="tab-pane fade">
                       <div class="">
                            <h2 class="title-prod">@lang('admin_shipping.import_csv')</h2>
                            <!-- ///// Start  -->
                            <div class="row">
                            <div class="form-group col-sm-12" id="shipping-rate-table">
                                <div class="condition-rulebox">
                                     <div class="row form-group shipping-rate-table-field">
                                        <div class="col-sm-6">
                                            <label>@lang('admin_shipping.delete_existing')</label>
                                            {!! Form::select('delete_existing', ['no'=>Lang::get('common.no'),'yes'=>Lang::get('common.yes')],  null,[ 'class'=>'custom-select']) !!}
                                        </div>                              
                                    </div>  
                                    <div class="form-group row shipping-rate-table-field">
                                        <div class="col-sm-6">
                                        <label>@lang('admin_shipping.name')</label>
                                            <input type="file" name="csv_rates"> 
                                        </div>
                                    </div>

                            </div>

                        </div>
                        </div>
                        <!-- ///// End //// -->
                            
                        </div> 
                    </div>

                    <div id="methods_and_rates" class="tab-pane fade">
                        <h2 class="title-prod">@lang('admin_shipping.methods_and_rate')</h2>
                        <div class="form-group btns-group">                              
                              <a class="btn-outline-primary ecport_rates" href="">@lang('admin_shipping.export_csv')</a>
                              <a class="btn-primary ecport_rates" href="">@lang('admin_shipping.add_new_rate')</a>
                        </div>
                        <!-- ////// Start  -->
                        <div class="table-wrapper" style="overflow: scroll;">
                            <div class="table">
                                <div class="table-header"  >
                                    <ul>
                                        <li>@lang('admin_shipping.country')</li>
                                        <li>@lang('admin_shipping.state')</li>
                                        <li>@lang('admin_shipping.district')</li>
                                        <li>@lang('admin_shipping.sub_district')</li>
                                        <li>@lang('admin_shipping.zip_from')</li>
                                        <li>@lang('admin_shipping.zip_to')</li>
                                        <li>@lang('admin_shipping.weight_from')</li>
                                        <li>@lang('admin_shipping.weight_to')</li>
                                        <li>@lang('admin_shipping.qty_from')</li>
                                        <li>@lang('admin_shipping.qty_to')</li>
                                        <li>@lang('admin_shipping.price_from')</li>
                                        <li>@lang('admin_shipping.price_to')</li>
                                        <li>@lang('admin_shipping.product_type')</li>
                                        <li>@lang('admin_shipping.base_rate')</li>
                                        <li>@lang('admin_shipping.ppp')</li>
                                        <li>@lang('admin_shipping.frpp')</li>
                                        <li>@lang('admin_shipping.frpuw')</li>
                                        <li>@lang('admin_shipping.action')</li>
                                       
                                    </ul>
                                </div>
                                <div class="table-content">
                                    @if(isset($rates))
                                    @foreach($rates as $r_key => $rate)
                                    
                                    <ul>
                                        <li>
                                        @if(!empty($rate['country_name']))

                                            {{$rate['country_name']->country_name}}

                                        @endif

                                            </li>
                                        <li>    
                                        @if(!empty($rate['state_name']))

                                            {{$rate['state_name']->province_state_name}}

                                        @endif


                                            </li>
                                        <li>
                                        @if(!empty($rate['disctrict_name']))

                                            {{$rate['disctrict_name']->city_district_name}}

                                        @endif
                                            </li>
                                        <li>
                                        @if(!empty($rate['sub_district_name']))

                                            {{$rate['sub_district_name']->sub_district_name}}

                                        @endif

                                        </li>
                                        <li>{{$rate['zip_from']}}</li>
                                        <li>{{$rate['zip_to']}}</li>
                                        <li>{{$rate['weight_from']}}</li>
                                        <li>{{$rate['weight_to']}}</li>
                                        <li>{{$rate['qty_from']}}</li>
                                        <li>{{$rate['qty_to']}}</li>
                                        <li>{{$rate['price_from']}}</li>
                                        <li>{{$rate['price_to']}}</li>
                                        <li>{{$rate['product_type_id']}}</li>
                                        <li>{{$rate['base_rate_for_order']}}</li>
                                        <li>{{$rate['percentage_rate_per_product']}}</li>
                                        <li>{{$rate['fixed_rate_per_product']}}</li>
                                        <li>{{$rate['fixed_rate_per_unit_weight']}}</li>
                                        <li><a href="">Edit</a></li>
                                       
                                    </ul>
                                    @endforeach
                                    @endif
                                    
                                </div>
                            </div>
                        </div>

                        <!-- ////// End  -->

                    </div>
                </div>
            </div>
        </div>
    </div>
</form>
@stop

@section('footer_scripts')
 @include('includes.gridtablejsdeps')
 <script stype="text/javascript" src="{{Config('constants.js_url')}}angular/sabinaApp/controller/gridTableCtrl.js"></script>
    <!-- begining of page level js -->

    <script src="{{ Config('constants.admin_js_url') }}jquery.dataTables.js"></script>
    <script src="{{ Config('constants.admin_js_url') }}dataTables.bootstrap.js"></script>
    <script>
    $(document).ready(function() {

        //category page tab
        function catrgoryRd(){
            var catrd = $("input[name='logo_type']:checked").attr('id'); 
            if(catrd == 'customLogo'){
                $('.custom-uploadimg').hide();
                $('.custom-lblimg').show();
            }else if(catrd == 'presetLogo'){
                $('.custom-uploadimg').show();
                $('.custom-lblimg').hide();
            }
        }

        jQuery("input[name='logo_type']").click(function(){
            catrgoryRd();
        })
        // display badge condition        
        $('.badge-condition .form-group input[name="badge_condition"]').click(function(){
        $('.badge-condition .form-group').find('.box-detail').hide();
           if ($(this).is(':checked')) {
            $(this).parents('.radio-wrap').next('.box-detail').show();                
           }
        });


        // manage dimension weight | Start
        $("#chkb_dimension_weight").on('click', function(){
            if($(this).prop("checked") == true){
                $('#dimension_weight_container').show();
            }else{
                $('#dimension_weight_container').hide();
            }
        });
        // End

    });
</script>
    <!-- end of page level js -->
    
@stop
