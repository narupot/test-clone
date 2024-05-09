@extends('layouts/admin/default')
    
@section('title')
    @lang('admin_shipping.shipping_profile')
@stop

@section('header_styles')
{!!CustomHelpers::dataTableCss()!!}
<link rel="stylesheet" type="text/css" href="{{Config('constants.css_url') }}ui-grid-unstable.css"> 

    <!--page level css -->
    <script>
      
      var fieldSetJson  = {!! $fielddata !!};
      var fieldset = fieldSetJson.fieldSets;
      var pagelimit = "{{action('JsonController@pageLimit')}}";
      var showSearchSection = true;
      var showHeadrePagination = true;
      var getAllDataFromServerOnce = true;
      var dataJsonUrl = "{{ action('Admin\ShippingProfile\ShippingRateTableController@listShippingRatesData',['shipping_profile_id'=>$shippingRateData->id]) }}";
      var lang = ["@lang('admin_shipping.country')","@lang('admin_shipping.state')","@lang('admin_shipping.district')","@lang('admin_shipping.sub_district')","@lang('admin_shipping.zip_from')","@lang('admin_shipping.zip_to')","@lang('admin_shipping.weight_from')","@lang('admin_shipping.weight_to')","@lang('admin_shipping.qty_from')","@lang('admin_shipping.qty_to')","@lang('admin_shipping.product_type')","@lang('admin_shipping.price_from')","@lang('admin_shipping.price_to')","@lang('admin_shipping.base_rate_for_order')","@lang('admin_shipping.ppp')","@lang('admin_shipping.frpp')","@lang('admin_shipping.frpuw')","@lang('admin_shipping.action')"];
      var tableLoaderImgUrl = "{{ Config::get('constants.loader_url')}}ajax-loader.gif";
      //pagination config 
      var pagination = {!! getPagination() !!};
      var per_page_limt = {{ getPagination('limit') }};
      //find index cahnge using method
      function findIndexMethod(list, matchEle){
        var index = -1;
        for (var i = 0; i < list.length; ++i) {
          if (list[i].fieldName!== undefined && list[i].fieldName===matchEle) {
              index = i;
              break;
          }
        }

        return index;  
      };
      //Listen on table columns setting
     _getInfo=(fName,fType)=>{
       var ind = findIndexMethod(fieldset, fName);
       if(ind>=0){
            var r =false;
            if(fType==='sortable'){
              r= (typeof fieldset[ind].sortable!=='undefined')? fieldset[ind].sortable:false;
            }else if(fType==='width'){
              r= (typeof fieldset[ind].width!=='undefined')? fieldset[ind].width:100;
            }else if(fType==='align'){
               r= (typeof fieldset[ind].align!=='undefined')? 'text-'+fieldset[ind].align:'text-left';
            }
            return r;
       }else{
          if(fType==='width'){
            return 100;
          }else if(fType==='align'){
            return 'text-left';
          }else if(fType==='sortable'){
            return false;
          } 
       }
       return false;
      };
      /**** This code used for columns setting of table where field is field name of database filed.*****/
      var columsSetting = [
      	{  
          field : 'Action',
          displayName : 'Action',
          cellTemplate: '<a href="<%row.entity.edit_url%>" class="primary-color">@lang('admin_shipping.edit')</a> | <a href="<%row.entity.delete_url%>" class="primary-color">@lang('admin_shipping.delete')</a> ',
          minWidth: _getInfo('Action','width'),
          cellClass:_getInfo('action','align'),
          enableSorting : false,
        },
       	{
          field : 'id',
          displayName : '@lang('admin_shipping.sno')',
          cellTemplate : '<span><%grid.appScope.seqNumber(row)+1%></span>',
          enableSorting : _getInfo('sno','sortable'),
          width : _getInfo('sno','width'),
          cellClass : _getInfo('sno','align'),
        },        
        {
          field : 'priority',
          displayName : '@lang('admin_shipping.priority')',
          enableSorting : false,
          width : _getInfo('priority','width'),
          cellClass : _getInfo('priority','align'),
        },

        
        { 
          field : 'country_id',
          displayName : '@lang('admin_shipping.country')',
          enableSorting : _getInfo('country_id','sortable'),
          width : _getInfo('country_id','width'),
          cellClass : _getInfo('country_id','align'),
        },
        
        { 
          field : 'province_state_id',
          displayName : '@lang('admin_shipping.state')',
          enableSorting : _getInfo('province_state_id','sortable'),
          width : _getInfo('province_state_id','width'),
          cellClass : _getInfo('province_state_id','align'),
        },
        
        {  
          field : 'district_city_id',
          displayName : '@lang('admin_shipping.district')',
          enableSorting : _getInfo('district_city_id','sortable'),
          width : _getInfo('district_city_id','width'),
          cellClass:_getInfo('district_city_id','align'),
        },
        {  
          field : 'sub_district_id',
          displayName : '@lang('admin_shipping.sub_district')',
          enableSorting : _getInfo('sub_district_id','sortable'),
          width : _getInfo('sub_district_id','width'),
          cellClass:_getInfo('sub_district_id','align'),
        },
        {  
          field : 'zip_from',
          displayName : '@lang('admin_shipping.zip_from')',
          enableSorting : _getInfo('zip_from','sortable'),
          width : _getInfo('zip_from','width'),
          cellClass:_getInfo('zip_from','align'),
        },
        {  
          field : 'zip_to',
          displayName : '@lang('admin_shipping.zip_to')',
          enableSorting : _getInfo('zip_to','sortable'),
          width : _getInfo('zip_to','width'),
          cellClass:_getInfo('zip_to','align'),
        },
        {  
          field : 'weight_from',
          displayName : '@lang('admin_shipping.weight_from')',
          enableSorting : _getInfo('weight_from','sortable'),
          width : _getInfo('weight_from','width'),
          cellClass:_getInfo('weight_from','align'),
        },
        {  
          field : 'weight_to',
          displayName : '@lang('admin_shipping.weight_to')',
          enableSorting : _getInfo('weight_to','sortable'),
          width : _getInfo('weight_to','width'),
          cellClass:_getInfo('weight_to','align'),
        },
        {  
          field : 'qty_from',
          displayName : '@lang('admin_shipping.qty_from')',
          enableSorting : _getInfo('qty_from','sortable'),
          width : _getInfo('qty_from','width'),
          cellClass:_getInfo('qty_from','align'),
        },
        {  
          field : 'qty_to',
          displayName : '@lang('admin_shipping.qty_to')',
          enableSorting : _getInfo('qty_to','sortable'),
          width : _getInfo('qty_to','width'),
          cellClass:_getInfo('qty_to','align'),
        },
        {  
          field : 'price_from',
          displayName : '@lang('admin_shipping.price_from')',
          enableSorting : _getInfo('price_from','sortable'),
          width : _getInfo('price_from','width'),
          cellClass:_getInfo('price_from','align'),
        },
        {  
          field : 'price_to',
          displayName : '@lang('admin_shipping.price_to')',
          enableSorting : _getInfo('price_to','sortable'),
          width : _getInfo('price_to','width'),
          cellClass:_getInfo('price_to','align'),
        },
        {  
          field : 'product_type_id',
          displayName : '@lang('admin_shipping.product_type_id')',
          enableSorting : _getInfo('product_type_id','sortable'),
          width : _getInfo('product_type_id','width'),
          cellClass:_getInfo('product_type_id','align'),
        },
        {  
          field : 'base_rate_for_order',
          displayName : '@lang('admin_shipping.base_rate_for_order')',
          enableSorting : _getInfo('base_rate_for_order','sortable'),
          width : _getInfo('base_rate_for_order','width'),
          cellClass:_getInfo('base_rate_for_order','align'),
        },
        {  
          field : 'percentage_rate_per_product',
          displayName : '@lang('admin_shipping.ppp')',
          enableSorting : _getInfo('percentage_rate_per_product','sortable'),
          width : _getInfo('percentage_rate_per_product','width'),
          cellClass:_getInfo('percentage_rate_per_product','align'),
        },
        {  
          field : 'fixed_rate_per_product',
          displayName : '@lang('admin_shipping.frpp')',
          enableSorting : _getInfo('fixed_rate_per_product','sortable'),
          width : _getInfo('fixed_rate_per_product','width'),
          cellClass:_getInfo('fixed_rate_per_product','align'),
        },
        {  
          field : 'fixed_rate_per_unit_weight',
          displayName : '@lang('admin_shipping.frpuw')',
          enableSorting : _getInfo('fixed_rate_per_unit_weight','sortable'),
          width : _getInfo('fixed_rate_per_unit_weight','width'),
          cellClass:_getInfo('fixed_rate_per_unit_weight','align'),
        },
        {  
          field : 'logistic_base_rate_for_order',
          displayName : '@lang('admin_shipping.logistic_base_rate_for_order')',
          enableSorting : _getInfo('logistic_base_rate_for_order','sortable'),
          width : _getInfo('logistic_base_rate_for_order','width'),
          cellClass:_getInfo('logistic_base_rate_for_order','align'),
        },
        {  
          field : 'logistic_percentage_rate_per_product',
          displayName : '@lang('admin_shipping.logistic_ppp')',
          enableSorting : _getInfo('logistic_percentage_rate_per_product','sortable'),
          width : _getInfo('logistic_percentage_rate_per_product','width'),
          cellClass:_getInfo('logistic_percentage_rate_per_product','align'),
        },
        {  
          field : 'logistic_fixed_rate_per_product',
          displayName : '@lang('admin_shipping.logistic_frpp')',
          enableSorting : _getInfo('logistic_fixed_rate_per_product','sortable'),
          width : _getInfo('logistic_fixed_rate_per_product','width'),
          cellClass:_getInfo('logistic_fixed_rate_per_product','align'),
        },
        {  
          field : 'logistic_fixed_rate_per_unit_weight',
          displayName : '@lang('admin_shipping.logistic_frpuw')',
          enableSorting : _getInfo('logistic_fixed_rate_per_unit_weight','sortable'),
          width : _getInfo('logistic_fixed_rate_per_unit_weight','width'),
          cellClass:_getInfo('logistic_fixed_rate_per_unit_weight','align'),
        }


        
      ];
  </script>

    <link rel="stylesheet" href="{{ Config('constants.admin_css_url') }}dataTables.bootstrap.css"/>
    <!-- end of page level css -->
    
@stop

@section('content')
    <div class="content">
        <form action="{{action('Admin\ShippingProfile\ShippingRateTableController@saveShippingRateProfile')}}" method="post" name="update" enctype="multipart/form-data">
        <div class="header-title">
            <h1 class="title">{{$shippingRateData->getShippingProfileDesc->name}}</h1>
            <div class="float-right">
                <button name="submit_type" value="save" class="btn btn-primary save_buttons" type="submit">@lang('admin_common.save')</button>
            </div>
        </div>
        <div class="content-wrap clearfix">
          <div class="breadcrumb">
                    <ul class="bredcrumb-menu">
                        {!!getBreadcrumbAdmin('config')!!}
                    </ul>
                </div>
            <div class="content-left">
                <div class="tablist">                    
                    <ul class="nav nav-tabs">
                        <li class="nav-item"><a class="nav-link show active" @if(!empty($session_data) || !empty($session_rates)) class="" @else class="active"  @endif  id="general_tab" data-toggle="tab" data-target="#general">@lang('admin_shipping.general')</a></li>
                        <li class="nav-item"><a class="nav-link" data-toggle="tab" id="import_tab" data-target="#import" @if(!empty($session_data)) class="active" @endif >@lang('admin_shipping.import')</a></li>
                        <li class="nav-item"><a class="nav-link" data-toggle="tab" id="methods_and_rates_tab" data-target="#methods_and_rates" @if(!empty($session_rates)) class="active" @endif>@lang('admin_shipping.methods_and_rates')</a></li>
                        <li class="nav-item"><a class="nav-link" data-toggle="tab" id="delivery_time_tab" data-target="#delivery_time" @if(!empty($session_rates)) class="active" @endif>@lang('admin_shipping.delivery_time')</a></li>
                    </ul>
                </div>
            </div>
            <div class="content-right">
                {{ csrf_field() }}
                <div class="tab-content">
                    <input type="hidden" name="shipping_profile_id" value="{{$shippingRateData->id}}">
                    <div id="general" class="tab-pane fade @if(!empty($session_data) || !empty($session_rates)) @else show active @endif ">
                        <div>
                            <h2 class="title-prod">@lang('admin_shipping.general')</h2>
                            <!-- //////// Start ///// -->
                            <div class="row">
                              <div></div>
                              <div class="form-group col-sm-12" id="shipping-rate-table">
                                  <div class="condition-rulebox">
                                        <div class="form-group row shipping-rate-table-field">
                                            <div class="col-sm-4">
                                            <label>@lang('admin_shipping.name')</label>
                                                <input type="text" name="name" value="{{$shippingRateData->getShippingProfileDesc->name}}"> 
                                            </div>
                                        </div>

                                        <div class="form-group row shipping-rate-table-field">
                                            <div class="col-sm-4">
                                            <label>@lang('admin_shipping.status')</label>
                                                {!! Form::select('status', ['1'=>Lang::get('admin_shipping.active'),'0'=>Lang::get('admin_shipping.deactive')],  $shippingRateData->status,[ 'class'=>'custom-select']) !!}
                                            </div>
                                        </div>
                                        <div class="form-group row shipping-rate-table-field">
                                            <div class="col-sm-4">
                                            <label>@lang('admin_shipping.comment')</label>
                                                <textarea name="comment">{{$shippingRateData->comment}}</textarea>
                                            </div>
                                        </div>                
                                        <div class="row form-group shipping-rate-table-field">
                                            <div class="col-sm-4">
                                            <label>@lang('admin_shipping.minimal_rate')</label>
                                                <input type="text" value="{{$shippingRateData->minimal_rate}}" name="minimal_rate"> 
                                                @if ($errors->has('minimal_rate'))
                                                <p id="minimal-rate-error" class="error error-msg">{{ $errors->first('minimal_rate') }}</p>
                                                @endif
                                          </div>                              
                                        </div>
                                        <div class="row form-group shipping-rate-table-field">
                                            <div class="col-sm-4">
                                            <label>@lang('admin_shipping.maximal_rate')</label>
                                                <input type="text" value="{{$shippingRateData->maximal_rate}}" name="maximal_rate"> 
                                                @if ($errors->has('maximal_rate'))
                                                <p id="maximal-rate-error" class="error error-msg">{{ $errors->first('maximal_rate') }}</p>
                                                @endif
                                            </div>                              
                                        </div>
                                        <div class="row form-group shipping-rate-table-field">
                                            <div class="col-sm-4">
                                            <label>@lang('admin_shipping.shipping_calculation_type')</label>
                                              {!! Form::select('shipping_calculation_type', ['0'=>Lang::get('admin_shipping.sum_up_rate'),'1'=>Lang::get('admin_shipping.select_minimal_rate'),'2'=>Lang::get('admin_shipping.select_maximal_rate')],  $shippingRateData->shipping_calculation_type,[ 'class'=>'custom-select']) !!}
                                            </div>                              
                                        </div> 
                                        <div class="row shipping-rate-table-field">
                                            <div class="col-sm-9">
                                                <img src="{{$shippingRateData->logo}}" />
                                            </div>
                                            <div class="col-sm-9">
                                               <label>@lang('admin_shipping.profile_logo')<i class="strick">*</i></label> 
                                                <div class="mb-2"> 
                                                    <div class="form-group">
                                                        <input type="file" name="shipping_logo" accept=".png, .jpg, .jpeg">
                                                        @if($errors->has('shipping_logo'))
                                                            <p class="error error-msg">{{ $errors->first('shipping_logo') }}</p>
                                                        @endif
                                                    </div> 

                                                </div>                                 
                                            </div>                                   
                                        </div>
                              </div>
                              <div class="attr-variant-view">
                              <!-- ////// Start ///// -->
                              <div class="row">
                                <div class="form-group col-sm-12" id="shipping-rate-table">
                                  <div class="condition-rulebox">
                                      
                                      <div class="row form-group shipping-rate-table-field">
                                            <div class="col-sm-4">
                                                <label>@lang('admin_shipping.customer_group')</label>
                                                <select class="multiple-selectw" name="customer_group[]" multiple="multiple" class="multiple-selectw">
                                                  <option value="">--- Select---</option>
                                                  @foreach($custGroup as $cus_key => $cust)

                                                  <option value="{{$cust['id']}}" 

                                                  <?php 
                                                     $custGArray = explode(',', $shippingRateData->customer_group);

                                                     if(in_array($cust['id'], $custGArray)){
                                                          echo "selected";
                                                     }
                                                  ?>

                                                  >{{$cust['group_name']}}</option>
                                                  @endforeach
                                                </select>
                                            </div>                              
                                      </div>
                                      <div class="row form-group shipping-rate-table-field">
                                            <label class="col-sm-12 check-wrap mb-2">
                                                <input type="checkbox" name="use_dimension_weight" @if($shippingRateData->use_dimension_weight=='1') checked="checked" @endif id="chkb_dimension_weight" val="1">
                                                <span class="chk-label">@lang('admin_shipping.use_dimension_weight')</span>
                                            </label>
                                            <div id="dimension_weight_container" class="">
                                                <div class="col-sm-12" id="dimension_weight_content">
                                                    <label>@lang('admin_shipping.factor')</label>
                                                    <input type="number"  name="dimension_factor" value="{{$shippingRateData->dimension_factor}}">
                                                </div> 
                                            </div>                         
                                      </div>
                                      

                                  </div>

                                </div>
                              </div>
                            <!-- ////// End  ////// -->
                                                       
                        </div> 
                          </div>
                        </div>
                            <!-- ////// End ///// -->
                        </div>
                    </div>
                    

                    <div id="import" class="tab-pane fade @if(!empty($session_data)) active show @endif ">
                       <div class="">
                            <h2 class="title-prod">@lang('admin_shipping.import_csv_rate')</h2>
                            <!-- ///// Start  -->
                            <div class="row">
                            <div class="form-group col-sm-12" id="shipping-rate-table">
                                <div class="condition-rulebox">
                                     <div class="row form-group shipping-rate-table-field">
                                        <div class="col-sm-4">
                                        <label>@lang('admin_shipping.delete_existing')</label>
                                            {!! Form::select('delete_existing', ['no'=>Lang::get('admin_common.no'),'yes'=>Lang::get('admin_common.yes')],  null,[ 'class'=>'custom-select']) !!}
                                        </div>                              
                                    </div> 
                                    <div id="import_local" class="form-group row">
                                        <div class="col-sm-4">
                                        <label>@lang('admin_shipping.select_file')</label>
                                            <input type="file" name="csv_rates" id="csv_rates"> 
                                        </div>
                                    </div>
                                    <div class="form-group row shipping-rate-table-field">
                                        <div class="col-sm-4">
                                            <input type="submit" class="btn btn-primary import_csv" name="submit_type" value="Import"> 
                                        </div>
                                    </div>
                            </div>

                          </div>

                          <div class="table table-content col-sm-12">
                    
                        @if(!empty($session_data))
                            
                            <div class="custom-paddleft">
                                <h3 class="title">@lang('admin_shipping.import_csv_response')</h3>

                                @foreach($session_data as $res_key => $row)
      
                              <h4>{{$res_key}}</h4>
                                    <div>
                                        @if(!empty($row))
                                          <div class="onlytableScroll" >
                                                <table style="overflow-x: auto !important;" >
                                                  <thead>
                                                    <tr>
                                                        <th>@lang('admin_shipping.s_no')</th>
                                                        <th>@lang('admin_shipping.priority')</th>
                                                        <th>@lang('admin_shipping.country')</th>
                                                        <th>@lang('admin_shipping.state')</th>
                                                        <th>@lang('admin_shipping.district')</th>
                                                        <th>@lang('admin_shipping.sub_district')</th>
                                                        <th>@lang('admin_shipping.zip_from')</th>
                                                        <th>@lang('admin_shipping.zip_to')</th>
                                                        <th>@lang('admin_shipping.weight_from')</th>
                                                        <th>@lang('admin_shipping.weight_to')</th>
                                                        <th>@lang('admin_shipping.qty_from')</th>
                                                        <th>@lang('admin_shipping.qty_to')</th>
                                                        <th>@lang('admin_shipping.price_from')</th>
                                                        <th>@lang('admin_shipping.price_to')</th>
                                                        <th>@lang('admin_shipping.product_type')</th>
                                                        <th>@lang('admin_shipping.base_rate')</th>
                                                        <th>@lang('admin_shipping.ppp')</th>
                                                        <th>@lang('admin_shipping.frpp')</th>
                                                        <th>@lang('admin_shipping.frpuw')</th>
                                                        <th>@lang('admin_shipping.estimate_shipping')</th>
                                                    </tr>
                                                  </thead>
                                            <tbody>
                                            @foreach($row as $k => $data)
                                            <tr>
                                                <td>{{$k+1}}</td>
                                                <td>{{$data['priority']}}</td>
                                                <td>{{$data['country_id']}}</td>
                                                <td>{{$data['province_state']}}</td>
                                                <td>{{$data['district_city']}}</td>
                                                <td>{{$data['sub_district']}}</td>
                                                <td>{{$data['zip_from']}}</td>
                                                <td>{{$data['zip_to']}}</td>
                                                <td>{{$data['weight_from']}}</td>
                                                <td>{{$data['weight_to']}}</td>
                                                <td>{{$data['qty_from']}}</td>
                                                <td>{{$data['qty_to']}}</td>
                                                <td>{{$data['price_from']}}</td>
                                                <td>{{$data['price_to']}}</td>
                                                <td>{{$data['product_type_id']}}</td>
                                                <td>{{$data['base_rate_for_order']}}</td>
                                                <td>{{$data['percentage_rate_per_product']}}</td>
                                                <td>{{$data['fixed_rate_per_product']}}</td>
                                                <td>{{$data['fixed_rate_per_unit_weight']}}</td>
                                                <td>{{$data['estimate_shipping']}}</td>
                                            </tr>
                                            @endforeach
                                            </tbody>
                                          </table> 
                                          </div>
                                        @else
                                        <div>@lang('admin_shipping.no_data_found')</div>
                                        @endif
                                    </div>
                                @endforeach
                            </div>
                        @endif
                        </div>
                        </div>
                        <!-- ///// End //// -->
                        </div> 
                    </div>

                    <div id="methods_and_rates" class="tab-pane fade @if(!empty($session_rates)) show active @endif">
                        <h2 class="title-prod">@lang('admin_shipping.methods_and_rate')</h2>
                        <div class="form-group">
                              <a class="btn-outline-primary ecport_rates mr-1" href="{{action('Admin\ShippingProfile\ShippingRateTableController@export_rates')}}"> @lang('admin_shipping.export_csv')</a>
                              <a class="btn-primary ecport_rates" href="{{action('Admin\ShippingProfile\ShippingRateTableController@addNewTableRate')}}"> @lang('admin_shipping.add_new_rate')</a>
                              <a class="btn-primary ecport_rates" href="{{action('Admin\ShippingProfile\ShippingRateTableController@addWizardRate')}}"> @lang('admin_shipping.add_wizard_rate')</a>
                        </div>
                        <!-- ////// Start  -->
                        <div class="table-wrapper">
                            <div id="jq_grid_table" class="table table-bordered"></div> 
                            
                        </div>

                        <!-- ////// End  -->
                    </div>
                    <div id="delivery_time" class="tab-pane fade @if(!empty($session_rates)) show active @endif">
                        
                            
                            <h2 class="title-prod">@lang('admin_shipping.delivery_time')</h2>
                            <div class="row">
                                <div class="col-sm-4 form-group">
                                    <label>@lang('admin_shipping.delivery_time_available_after')</label>
                                    <input type="text" name="delivery_time_after" value="{{$delivery_time?$delivery_time->delivery_time_after:''}}">
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-sm-4 form-group">
                                    <label>@lang('admin_shipping.seller_need_to_prepare_item_before_customer_choose_slot')</label>
                                    <input type="text" name="prepare_time_before" value="{{$delivery_time?$delivery_time->prepare_time_before:''}}">
                                </div>
                            </div>
                            <div class="input_fields_wrap">
                                @if($delivery_time && count($delivery_time->time_slot))
                                    @foreach($delivery_time->time_slot as $tkey => $tval)
                                        <div class="row cloneData align-items-center">
                                            <div class="col-sm-4 form-group">
                                                <label>@lang('admin_shipping.time_slot_for_delivery')</label>
                                                <select name="time_slot[]">
                                                    <option value="">Select</option>
                                                    @for($i=7; $i<=23;$i++)
                                                        
                                                        <option value="{{ $i }}" @if($tval == $i) selected="selected" @endif>{{ $i.':00'}}</option>
                                                        
                                                    @endfor
                                                </select>
                                            </div>
                                            <div class="col-sm-8 form-group actionsClone">
                                                <label>&nbsp;</label>
                                                <a href="javascript:;" class="minus-clone"><span class="ui-icon ui-icon-minusthick removeCss cursor-pointer"></span></a>
                                            </div>
                                        </div>
                                        
                                    @endforeach
                                @endif
                                <div class="row original">
                                    <div class="col-sm-4 form-group">
                                        <label>@lang('admin_shipping.time_slot_for_delivery')</label>
                                        <select name="time_slot[]" id="times_slots">
                                            <option value="">Select</option>
                                            @for($i=7; $i<=23;$i++)
                                                
                                                <option value="{{ $i }}">{{ $i.':00'}}</option>
                                                
                                            @endfor
                                        </select>
                                    </div>
                                    <div class="col-sm-8 form-group actionsClone">
                                        <label>&nbsp;</label>
                                        <div><a href="javascript:;" class="btn btn-primary add_field_button" style="margin-bottom: 5px;"><i class="fa fa-plus align-baseline"></i></a></div>
                                    </div>
                                </div>
                                <ui class="css-board"></ui>
                            </div>
                        
                    </div>
                </div>
            </div>
        </div>
    </div>
</form>
<div id="delete_record" class="modal fade" role="dialog">
      <form id="delete_record_frm" method="get" action=""> 
          <div class="modal-dialog">
              {{ csrf_field() }}
              <!-- Modal content-->
              <div class="modal-content">
                  <div class="modal-header">
                      <button type="button" class="close" data-dismiss="modal">&times;</button>
                      <h4 class="modal-title">@lang('admin_common.confirm')</h4>
                  </div>
                <div class="modal-body">
                  <p>@lang('admin_common.do_you_realy_want_to_delete_this_record')</p>
                </div>
                <div class="modal-footer">
                  <button type="submit" class="btn-danger">@lang('admin_common.yes')</button>
                  <button type="button" class="btn-default" data-dismiss="modal">@lang('admin_common.no')</button>
                </div>
              </div>
          </div>
      </form>
  </div> 
@stop

@section('footer_scripts')
 @include('includes.gridtablejsdeps')
 {!! CustomHelpers::dataTableJs()!!}
 <script stype="text/javascript" src="{{Config('constants.js_url')}}angular/smmApp/controller/gridTableCtrl.js"></script>
    <!-- begining of page level js -->

    <script src="{{ Config('constants.admin_js_url') }}jquery.dataTables.js"></script>
    <script src="{{ Config('constants.admin_js_url') }}dataTables.bootstrap.js"></script>
    
    <!-- end of page level js -->

    <script>
      var JQ_GRID_DATA_URL = "{{ action('Admin\ShippingProfile\ShippingRateTableController@getDeliveryAtAddress') }}";
      const JQ_GRID_TITLE = "@lang('admin_flashsale.flashsale_product_list')";
      const METHOD_TYPE = 'POST';
      const CUSTOM_ROW_HEIGHT = {
          'row_height' : 30,
      }; 
      let columnModel = [
        {   
            title: "@lang('admin_shipping.priority')",
            dataIndx:'priority',
            align:'left',
            minWidth: 80,               
        },  
        {   
            title: "@lang('admin_shipping.country')",
            dataIndx:'country_name',
            align:'left',
            minWidth: 80,  
            filter : {
                attr : "@lang('admin_shipping.country_name')",                        
                crules: [
                    {
                        condition: getFilter('country_name', 'condition') ||  'contain',
                        value : '{{ $search_type == "country_name"?$search:''}}' || getFilter('country_name', 'value')  || "",
                    }
                ],
                type: 'textbox', 
                listeners: ['change'],
            },             
        },
        {   
            title: "@lang('admin_shipping.state')",
            dataIndx:'state',
            align:'left',
            minWidth: 120,  
            filter : {
                attr : "@lang('admin_shipping.state')",                        
                crules: [
                    {
                        condition: getFilter('state', 'condition') ||  'contain',
                        value : '{{ $search_type == "state"?$search:''}}' || getFilter('state', 'value')  || "",
                    }
                ],
                type: 'textbox', 
                listeners: ['change'],
            },              
        },
        {   
            title: "@lang('admin_shipping.district')",
            dataIndx:'district',
            align:'left',
            minWidth: 120, 
            filter : {
                attr : "@lang('admin_shipping.district')",                        
                crules: [
                    {
                        condition: getFilter('district', 'condition') ||  'contain',
                        value : '{{ $search_type == "district"?$search:''}}' || getFilter('district', 'value')  || "",
                    }
                ],
                type: 'textbox', 
                listeners: ['change'],
            },              
        },
        {   
            title: "@lang('admin_shipping.sub_district')",
            dataIndx:'sub_district',
            align:'left',
            minWidth: 110,
            filter : {
                attr : "@lang('admin_shipping.sub_district')",                        
                crules: [
                    {
                        condition: getFilter('sub_district', 'condition') ||  'contain',
                        value : '{{ $search_type == "sub_district"?$search:''}}' || getFilter('sub_district', 'value')  || "",
                    }
                ],
                type: 'textbox', 
                listeners: ['change'],
            },             
        },
        {   
            title: "@lang('admin_shipping.zip_from')",
            dataIndx:'zip_from',
            align:'left',
            minWidth: 90, 
            filter : {
                attr : "@lang('admin_shipping.zip_from')",                        
                crules: [
                    {
                        condition: getFilter('zip_from', 'condition') ||  'contain',
                        value : '{{ $search_type == "zip_from"?$search:''}}' || getFilter('zip_from', 'value')  || "",
                    }
                ],
                type: 'textbox', 
                listeners: ['change'],
            },              
        },
        {   
            title: "@lang('admin_shipping.zip_to')",
            dataIndx:'zip_to',
            align:'left',
            minWidth: 90, 
            filter : {
                attr : "@lang('admin_shipping.zip_to')",                        
                crules: [
                    {
                        condition: getFilter('zip_to', 'condition') ||  'contain',
                        value : '{{ $search_type == "zip_to"?$search:''}}' || getFilter('zip_to', 'value')  || "",
                    }
                ],
                type: 'textbox', 
                listeners: ['change'],
            },              
        },
        {   
            title: "@lang('admin_shipping.weight_from')",
            dataIndx:'weight_from',
            align:'left',
            minWidth: 90,
            filter : {
                attr : "@lang('admin_shipping.weight_from')",                        
                crules: [
                    {
                        condition: getFilter('weight_from', 'condition') ||  'contain',
                        value : '{{ $search_type == "weight_from"?$search:''}}' || getFilter('weight_from', 'value')  || "",
                    }
                ],
                type: 'textbox', 
                listeners: ['change'],
            },               
        },
        {   
            title: "@lang('admin_shipping.weight_to')",
            dataIndx:'weight_to',
            align:'left',
            minWidth: 90, 
            filter : {
                attr : "@lang('admin_shipping.weight_to')",                        
                crules: [
                    {
                        condition: getFilter('weight_to', 'condition') ||  'contain',
                        value : '{{ $search_type == "weight_to"?$search:''}}' || getFilter('weight_to', 'value')  || "",
                    }
                ],
                type: 'textbox', 
                listeners: ['change'],
            },              
        },
        {   
            title: "@lang('admin_shipping.qty_from')",
            dataIndx:'qty_from',
            align:'left',
            minWidth: 90,
            filter : {
                attr : "@lang('admin_shipping.qty_from')",                        
                crules: [
                    {
                        condition: getFilter('qty_from', 'condition') ||  'contain',
                        value : '{{ $search_type == "qty_from"?$search:''}}' || getFilter('qty_from', 'value')  || "",
                    }
                ],
                type: 'textbox', 
                listeners: ['change'],
            },               
        },
        {   
            title: "@lang('admin_shipping.qty_to')",
            dataIndx:'qty_to',
            align:'left',
            minWidth: 90, 
            filter : {
                attr : "@lang('admin_shipping.qty_to')",                        
                crules: [
                    {
                        condition: getFilter('qty_to', 'condition') ||  'contain',
                        value : '{{ $search_type == "qty_to"?$search:''}}' || getFilter('qty_to', 'value')  || "",
                    }
                ],
                type: 'textbox', 
                listeners: ['change'],
            },              
        },
        {   
            title: "@lang('admin_shipping.price_from')",
            dataIndx:'price_from',
            align:'left',
            minWidth: 90,   
            filter : {
                attr : "@lang('admin_shipping.price_from')",                        
                crules: [
                    {
                        condition: getFilter('price_from', 'condition') ||  'contain',
                        value : '{{ $search_type == "price_from"?$search:''}}' || getFilter('price_from', 'value')  || "",
                    }
                ],
                type: 'textbox', 
                listeners: ['change'],
            },            
        },
        {   
            title: "@lang('admin_shipping.price_to')",
            dataIndx:'price_to',
            align:'left',
            minWidth: 90, 
            filter : {
                attr : "@lang('admin_shipping.price_to')",                        
                crules: [
                    {
                        condition: getFilter('price_to', 'condition') ||  'contain',
                        value : '{{ $search_type == "price_to"?$search:''}}' || getFilter('price_to', 'value')  || "",
                    }
                ],
                type: 'textbox', 
                listeners: ['change'],
            },              
        },
        {   
            title: "@lang('admin_shipping.product_type_id')",
            dataIndx:'product_type_id',
            align:'left',
            minWidth: 90,               
        },
        {   
            title: "@lang('admin_shipping.base_rate_for_order')",
            dataIndx:'base_rate_for_order',
            align:'left',
            minWidth: 90, 
            filter : {
                attr : "@lang('admin_shipping.base_rate_for_order')",                        
                crules: [
                    {
                        condition: getFilter('base_rate_for_order', 'condition') ||  'contain',
                        value : '{{ $search_type == "base_rate_for_order"?$search:''}}' || getFilter('base_rate_for_order', 'value')  || "",
                    }
                ],
                type: 'textbox', 
                listeners: ['change'],
            },              
        },
        {   
            title: "@lang('admin_shipping.ppp')",
            dataIndx:'percentage_rate_per_product',
            align:'left',
            minWidth: 90,
            filter : {
                attr : "@lang('admin_shipping.ppp')",                        
                crules: [
                    {
                        condition: getFilter('percentage_rate_per_product', 'condition') ||  'contain',
                        value : '{{ $search_type == "percentage_rate_per_product"?$search:''}}' || getFilter('percentage_rate_per_product', 'value')  || "",
                    }
                ],
                type: 'textbox', 
                listeners: ['change'],
            },               
        },
        {   
            title: "@lang('admin_shipping.frpp')",
            dataIndx:'fixed_rate_per_product',
            align:'left',
            minWidth: 90,
            filter : {
                attr : "@lang('admin_shipping.frpp')",                        
                crules: [
                    {
                        condition: getFilter('fixed_rate_per_product', 'condition') ||  'contain',
                        value : '{{ $search_type == "fixed_rate_per_product"?$search:''}}' || getFilter('fixed_rate_per_product', 'value')  || "",
                    }
                ],
                type: 'textbox', 
                listeners: ['change'],
            },

        },
        {   
            title: "@lang('admin_shipping.frpuw')",
            dataIndx:'fixed_rate_per_unit_weight',
            align:'left',
            minWidth: 90, 
            filter : {
                attr : "@lang('admin_shipping.frpuw')",                        
                crules: [
                    {
                        condition: getFilter('fixed_rate_per_product', 'condition') ||  'contain',
                        value : '{{ $search_type == "fixed_rate_per_product"?$search:''}}' || getFilter('fixed_rate_per_product', 'value')  || "",
                    }
                ],
                type: 'textbox', 
                listeners: ['change'],
            },
              
        },
        {   
            title: "@lang('admin_shipping.logistic_base_rate_for_order')",
            dataIndx:'logistic_base_rate_for_order',
            align:'left',
            minWidth: 90,
            filter : {
                attr : "@lang('admin_shipping.logistic_base_rate_for_order')",                        
                crules: [
                    {
                        condition: getFilter('logistic_base_rate_for_order', 'condition') ||  'contain',
                        value : '{{ $search_type == "logistic_base_rate_for_order"?$search:''}}' || getFilter('logistic_base_rate_for_order', 'value')  || "",
                    }
                ],
                type: 'textbox', 
                listeners: ['change'],
            },               
        },
        {   
            title: "@lang('admin_shipping.logistic_ppp')",
            dataIndx:'logistic_percentage_rate_per_product',
            align:'left',
            minWidth: 90, 
            filter : {
                attr : "@lang('admin_shipping.logistic_ppp')",                        
                crules: [
                    {
                        condition: getFilter('logistic_percentage_rate_per_product', 'condition') ||  'contain',
                        value : '{{ $search_type == "logistic_percentage_rate_per_product"?$search:''}}' || getFilter('logistic_percentage_rate_per_product', 'value')  || "",
                    }
                ],
                type: 'textbox', 
                listeners: ['change'],
            },              
        },
        {   
            title: "@lang('admin_shipping.logistic_frpp')",
            dataIndx:'logistic_fixed_rate_per_product',
            align:'left',
            minWidth: 90,
            filter : {
                attr : "@lang('admin_shipping.logistic_frpp')",                        
                crules: [
                    {
                        condition: getFilter('logistic_fixed_rate_per_product', 'condition') ||  'contain',
                        value : '{{ $search_type == "logistic_fixed_rate_per_product"?$search:''}}' || getFilter('logistic_fixed_rate_per_product', 'value')  || "",
                    }
                ],
                type: 'textbox', 
                listeners: ['change'],
            },               
        },
        {   
            title: "@lang('admin_shipping.logistic_frpuw')",
            dataIndx:'logistic_fixed_rate_per_unit_weight',
            align:'left',
            minWidth: 90,
            filter : {
                attr : "@lang('admin_shipping.logistic_frpuw')",                        
                crules: [
                    {
                        condition: getFilter('logistic_fixed_rate_per_unit_weight', 'condition') ||  'contain',
                        value : '{{ $search_type == "logistic_fixed_rate_per_unit_weight"?$search:''}}' || getFilter('logistic_fixed_rate_per_unit_weight', 'value')  || "",
                    }
                ],
                type: 'textbox', 
                listeners: ['change'],
            },               
        },
        {   
            title: "@lang('admin_common.actions')",
            minWidth: 150,
            render : function(ui) {
                return {
                    text:'<a href="javascript:void(0);" class="link-primary">&nbsp<a href="'+ui.rowData.edit_url+'" class="link-primary">@lang("admin_common.edit")</a>&nbsp|&nbsp<a href="javascript:void(0);" class="link-primary" onclick="deleteRecord(\''+ui.rowData.delete_url+'\')">@lang("admin_common.delete")</a>&nbsp|&nbsp<a href="javascript:void(0);" class="link-primary">&nbsp<a href="'+ui.rowData.log_url+'" class="link-primary">@lang("admin_common.log")</a>',    
                };
            },
        }, 
      ];
      $(document).ready(function(){

        $("#import_tab").on('click',function (){
          $(".save_buttons").attr('disabled',true);
        });
        
       $("#general_tab").on('click',function (){
          $(".save_buttons").attr('disabled',false);
        });

       $("#methods_and_rates_tab").on('click',function (){
        $("#jq_grid_table").pqGrid('refreshDataAndView');
          $(".save_buttons").attr('disabled',false);
        });

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

        if($("#chkb_dimension_weight").prop("checked") == true){
            $('#dimension_weight_container').show();
        }else{
            $('#dimension_weight_container').hide();
        }

        $(document).on('click','.select_import_location', function(){
            if($(this).val()==='local'){
                $('#import_server').addClass('d-none');
                $('#import_local').removeClass('d-none');
            }else{
                $('#import_local').addClass('d-none');
                $('#import_server').removeClass('d-none');
            }
        });
        
      });
      function deleteRecord(delete_url) {
        $('#delete_record_frm').attr('action', delete_url);
        $('#delete_record').modal('show');
    }    
    </script>
    <script type="text/javascript">
        $(document).ready(function() {
            $('.add_field_button').click(function(e){
                e.preventDefault();
                var clone = jQuery(".original").clone(false);
                clone.removeClass('original');
                clone.addClass('cloneData');
                clone.find('.actionsClone').html('<label>&nbsp;</label><a href="javascript:;" class="minus-clone"><span class="ui-icon ui-icon-minusthick removeCss cursor-pointer"></span></a>');
                jQuery(".input_fields_wrap .row:last").after(clone);

            });
            
            $('body').on("click","a.minus-clone", function(e){ //user click on remove text
                e.preventDefault(); 
                jQuery(this).parent().parent('.cloneData').remove();
            })
        });   
    </script>
    
@stop
