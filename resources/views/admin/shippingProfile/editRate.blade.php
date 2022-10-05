@extends('layouts/admin/default') 

@section('title')
    @lang('admin_shipping.edit_shipping_profile')
@stop

@section('header_styles')
 <link rel="stylesheet" href="{{ Config('constants.admin_css_url') }}select.css" />

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

        <form  action="{{action('Admin\ShippingProfile\ShippingRateTableController@saveRate')}}" method="post" data-validate  data-invalid-class="invalid">
            {{ csrf_field() }}
            <div class="header-title">
                <h1 class="title">{{$shipping_profile->getShippingProfileDesc->name}}</h1>
                <div class="float-right">
                 <a href="{{ action('Admin\ShippingProfile\ShippingRateTableController@deliveryAtAddress') }}" class="btn btn-back">@lang('admin_common.back')</a>
                 <button  name="submit_type" type="submit" value="save_and_continue" class="btn btn-secondary save_and_continue" ng-disabled="prdData.loading.disableBtn">@lang('admin_common.save_and_continue')</button>
                <button name="submit_type" value="save" class="btn btn-save" type="submit">@lang('admin_common.save')</button>
                </div>
            </div>

            <div class="content-wrap clearfix"> 
                <input type="hidden" name="shipping_profile_id" value="{{$shipping_profile->id}}">
                <input type="hidden" name="rate_id" value="{{$rateData->id}}">
                <div class="row">
                    <div class="col-sm-6">
                        <h3 class="title">@lang('admin_shipping.rate_configuration')</h3>
                        <div class="box">@lang('admin_shipping.destination')</div>
                        <div class="col-sm-12">        
                            <div class="form-group row">
                                <label class="col-sm-5">@lang('admin_shipping.country')</label>
                                <div class="col-sm-7">
                                   <select name="country_id" id="country" class="getRelatedData">
                                       <option value="All">All</option>
                                       @foreach($country_data as $country_key => $country)
                                      
                                       <option value="{{$country['id']}}"
                                       @if($rateData->country_id==$country['country_name']['country_id'])

                                       selected="selected"

                                       @endif

                                       > {{$country['country_name']['country_name']}}</option>
                                       @endforeach
                                   </select>
                                </div>
                            </div>
                            <div class="form-group row">
                                <label class="col-sm-5">@lang('admin_shipping.state')</label>
                                <div class="col-md-7">
                                    <div class="radio-group mb-2">
                                        <label class="radio-wrap">
                                            <input type="radio" name="province_state_type" value="custom" checked="checked" class="custom">
                                            <span class="radio-label">@lang('admin_shipping.select_custom')</span>
                                        </label>
                                        <label class="radio-wrap">
                                            <input type="radio" name="province_state_type" value="all" class="all">
                                            <span class="radio-label">@lang('admin_shipping.select_all')</span>
                                        </label>
                                    </div>
                                    <div id="province_state_type">
                                        {!! CustomHelpers::fieldstabWithLanuageEdit([['field'=>'text', 'name'=>'province_state', 'label'=>'Title', 'errorkey'=>'province_state']], '1', 'rate_id', $rateData->id,$rateDescTable, $errors) !!}
                                        <div id="auto_sugg_province_state_type" class="autosuggest-search"></div>
                                    </div>
                                </div>
                            </div>
                            <div class="form-group row">
                                <label class="col-sm-5">@lang('admin_shipping.district')</label>
                                <div class="col-md-7">
                                    <div class="radio-group mb-2">
                                        <label class="radio-wrap">
                                            <input type="radio" name="district_city_type" value="custom" checked="checked" class="custom">
                                            <span class="radio-label">@lang('admin_shipping.select_custom')</span>
                                        </label>
                                        <label class="radio-wrap">
                                            <input type="radio" name="district_city_type" value="all" class="all">
                                            <span class="radio-label">@lang('admin_shipping.select_all')</span>
                                        </label>
                                    </div>
                                    <div id="district_city_type">
                                        {!! CustomHelpers::fieldstabWithLanuageEdit([['field'=>'text', 'name'=>'district_city', 'label'=>'Title', 'errorkey'=>'district_city']], '2', 'rate_id', $rateData->id, $rateDescTable, $errors) !!}
                                        <div id="auto_sugg_district_city_type" class="autosuggest-search"></div>
                                    </div>
                                </div>
                            </div>
                            <div class="form-group row">
                                <label class="col-sm-5">@lang('admin_shipping.sub_district')</label>
                                <div class="col-md-7">
                                    <div class="radio-group mb-2">
                                        <label class="radio-wrap">
                                            <input type="radio" name="sub_district_type" value="custom" checked="checked" class="custom">
                                            <span class="radio-label">@lang('admin_shipping.select_custom')</span>
                                        </label>
                                        <label class="radio-wrap">
                                            <input type="radio" name="sub_district_type" value="all" class="all">
                                            <span class="radio-label">@lang('admin_shipping.select_all')</span>
                                        </label>
                                    </div>
                                    <div id="sub_district_type">
                                        {!! CustomHelpers::fieldstabWithLanuageEdit([['field'=>'text', 'name'=>'sub_district', 'label'=>'Title', 'errorkey'=>'sub_district']], '3', 'rate_id', $rateData->id, $rateDescTable, $errors) !!}

                                        <div id="auto_sugg_sub_district_type" class="autosuggest-search"></div>
                                    </div>
                                </div>
                            </div>
                            <div class="form-group row">
                                <label class="col-sm-5">@lang('admin_shipping.zip_from')</label>
                                <div class="col-sm-7">
                                    <input type="text" name="zip_from" value="{{$rateData->zip_from}}">
                                </div>
                            </div>
                            <div class="form-group row">
                                <label class="col-sm-5">@lang('admin_shipping.zip_to')</label>
                                <div class="col-sm-7">
                                   <input type="text" name="zip_to" value="{{$rateData->zip_to}}">
                                </div>
                            </div>
                        </div>
                        <div class="box">@lang('admin_shipping.conditions')</div>
                        <div class="col-sm-12">
                            <div class="form-group row">
                                <label class="col-sm-5">@lang('admin_shipping.weight_from')</label>
                                <div class="col-sm-7">
                                    <input type="text" name="weight_from" value="{{$rateData->weight_from}}">
                                </div>
                            </div>
                            <div class="form-group row">
                                <label class="col-sm-5">@lang('admin_shipping.weight_to')</label>
                                <div class="col-sm-7">
                                   <input type="text" name="weight_to" value="{{$rateData->weight_to}}">
                                </div>
                            </div>
                            <div class="form-group row">
                                <label class="col-sm-5">@lang('admin_shipping.qty_from')</label>
                                <div class="col-sm-7">
                                    <input type="text" name="qty_from" value="{{$rateData->qty_from}}">
                                </div>
                            </div>
                            <div class="form-group row">
                                <label class="col-sm-5">@lang('admin_shipping.qty_to')</label>
                                <div class="col-sm-7">
                                    <input type="text" name="qty_to" value="{{$rateData->qty_to}}">
                                </div>
                            </div>
                            <div class="form-group row">
                                <label class="col-sm-5">@lang('admin_shipping.price_from')</label>
                                <div class="col-sm-7">
                                   <input type="text" name="price_from" value="{{$rateData->price_from}}">
                                </div>
                            </div>
                            <div class="form-group row">
                                <label class="col-sm-5">@lang('admin_shipping.price_to')</label>
                                <div class="col-sm-7">
                                    <input type="text" name="price_to" value="{{$rateData->price_to}}">
                                </div>
                            </div>
                            <div class="form-group row">
                                <label class="col-sm-5">@lang('admin_shipping.product_type_id')</label>
                                <div class="col-sm-7">
                                    {!! Form::select('product_type_id',['all'=>Lang::get('shipping.all')],  $rateData->product_type_id,['id'=>'product_type_id']) !!}
                     
                                </div>
                            </div>
                        </div>
                        <div class="form-row">
                            <div class="col-sm-12" >
                                <div class="form-group form-row">
                                    <label class="col-sm-6 check-wrap">@lang('admin_shipping.rate')</label>
                                    <div class="col-sm-3 chbk-container">@lang('admin_shipping.shipping_cost')</div>
                                    <div class="col-sm-3">@lang('admin_shipping.logistic_cost')</div>
                                </div>
                            </div>
                        </div>
                        <div class="col-sm-12">
                            <div class="form-group row">
                                <label class="col-sm-6 check-wrap">
                                    <input type="checkbox" name="chkb_base_rate_for_order" checked="checked" class="chkb">
                                    <span class="chk-label">@lang('admin_shipping.base_rate_for_order')</span>
                                </label>
                                <div class="col-sm-3 chbk-container">
                                    <input type="number" name="base_rate_for_order" value="{{$rateData->base_rate_for_order}}" step="0.01">
                                </div>
                                <div class="col-sm-3 chbk-container">
                                    <input type="number" name="logistic_base_rate_for_order" value="{{$rateData->logistic_base_rate_for_order}}" step="0.01">
                                </div>
                            </div>
                            <div class="form-group row">
                                <label class="col-sm-6 check-wrap" >
                                    <input type="checkbox" name="chkb_percentage_rate_per_product" checked="checked" class="chkb">
                                    <span class="chk-label">@lang('admin_shipping.ppp')</span>
                                </label>
                                <div class="col-sm-3 chbk-container">
                                    <input type="number" name="percentage_rate_per_product" value="{{$rateData->percentage_rate_per_product}}" step="0.01">
                                </div>
                                <div class="col-sm-3 chbk-container">
                                    <input type="number" name="logistic_percentage_rate_per_product" value="{{$rateData->logistic_percentage_rate_per_product}}" step="0.01">
                                </div>
                            </div>
                            <div class="form-group row">
                                <label class="col-sm-6 check-wrap">
                                    <input type="checkbox" name="chkb_fixed_rate_per_product" checked="checked"  class="chkb">
                                   <span class="chk-label">@lang('admin_shipping.frpp')</span>
                                </label>
                                <div class="col-sm-3 chbk-container">
                                    <input type="number" name="fixed_rate_per_product" value="{{$rateData->fixed_rate_per_product}}" step="0.01">
                                </div>
                                <div class="col-sm-3 chbk-container">
                                    <input type="number" name="logistic_fixed_rate_per_product" value="{{$rateData->logistic_fixed_rate_per_product}}" step="0.01">
                                </div>
                            </div>
                            <div class="form-group row">
                                <label class="col-sm-6 check-wrap">
                                    <input type="checkbox" name="chkb_fixed_rate_per_unit_weight" checked="checked" class="chkb"> <span class="chk-label">@lang('admin_shipping.frpuw')</span>
                                </label>
                                <div class="col-sm-3 chbk-container">
                                    <input type="number" name="fixed_rate_per_unit_weight" value="{{$rateData->fixed_rate_per_unit_weight}}" step="0.01">
                                </div>
                                <div class="col-sm-3 chbk-container">
                                    <input type="number" name="logistic_fixed_rate_per_unit_weight" value="{{$rateData->logistic_fixed_rate_per_unit_weight}}" step="0.01">
                                </div>
                            </div>
                            <div class="form-group row">
                                <label class="col-sm-5">@lang('admin_shipping.estimate_ship_time')</label>
                                <div class="col-sm-7">
                                    <input type="text" name="estimate_shipping" value="{{$rateData->estimate_shipping}}">
                                </div>
                            </div>
                            <div class="form-group row">
                                <label class="col-sm-5">@lang('admin_shipping.priority')</label>
                                <div class="col-sm-7">
                                    <input type="text" name="priority" value="{{$rateData->priority}}">
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

 <script stype="text/javascript" src="{{Config('constants.js_url')}}angular/sabinaApp/controller/shipProfileCtrl.js"></script>
<script src="{{ Config('constants.admin_js_url') }}shipping.js"></script>
<script type="text/javascript">
    jQuery('.select').chosen();
</script>
<script>
    $(function(){
        
        $(".getRelatedData").on('change', function(){
            var id = $(this).val();
            var attribute = $(this).attr('id');
            var related_url = "{{action('Admin\ShippingProfile\ShippingRateTableController@getRelatedData')}}";
            var token = window.Laravel.csrfToken;;
            $.ajax({
                url : related_url,
                DataType : 'JSON',
                data : {'_token':token,'id':id,'attribute':attribute},
                method : 'POST',
                success : function(response){
                    var obj = $.parseJSON(response);
                    var htmlContent = "<option value='All'>All</option>";
                    if(obj.success=='success'){
                        
                        if(attribute=='country'){
                            $.each(obj.optionList, function(index, value){
                                htmlContent += "<option value='"+value.id+"'>"+value.province_name.province_state_name+"</option>";
                            });
                        }
                        if(attribute=='state'){
                            console.log(obj.optionList);
                            $.each(obj.optionList, function(index, value){
                                htmlContent += "<option value='"+value.id+"'>"+value.city_name.city_district_name
+"</option>";
                            });
                        }
                        if(attribute=='district'){
                            $.each(obj.optionList, function(index, value){
                                htmlContent += "<option value='"+value.id+"'>"+value.sub_district_name.sub_district_name+"</option>";
                            });
                        }
                        

                    }
                    
                    $("."+attribute+"_dependent").html(htmlContent);
                }
            });
        });

        $(document).on('click','.custom, .all',function(){
            var section_name = $(this).prop('name');
            var section = section_name.replace(/_type/g,'');
            
            if($(this).val()==='all'){
                var content = "<input type='text' readonly='readonly' name='"+section+"' value='*'>";
            }else{

                if(section==='province_state'){
                    var lang = '1';
                }else if(section==='district_city'){
                    var lang = '2';
                }else{
                    var lang = '3';
                }

                var custom_content = '{!!CustomHelpers::fieldstabWithLanuage([["field"=>"text", "name" => "{SECTION}", "label"=>"Title", "errorkey"=>"title"]], "{LANG}", $errors) !!}';
                var content = custom_content.replace(/{SECTION}/g,section);
                content = content.replace(/{LANG}/g,lang);
                content += '<div id="auto_sugg_'+section_name+'" class="autosuggest-search"></div>';
            }

            $('#'+section_name).html(content); 
        });

        $(document).on('keyup',".form-control", function(){
            var field = $(this).prop('name');
            var section = field.replace(/\[[0-9]\]/g,'');
            var input_val = $(this).val();
            var lang = $(this).parents('.tab-pane').attr('id');
            var url = "{{action('Admin\ShippingProfile\ShippingRateTableController@autosuggest')}}";
            var data = {'input_val':input_val,'section':section+'_type','lang':lang};
            
            callAjax(url, 'post', data, function(response){
                var response_data = JSON.parse(response);
                if(response_data.status == 'success'){ 
                    var drop_content = '<select name="auto_suggest" class="auto_suggest multiple-selectw" id="'+field+'" size="10"><option value="">--Select--</option>'; 
                    var count = response_data.data.length;
                    if(count){
                        $.each(response_data.data, function (index, val) {
                            drop_content += "<option value='"+val+"'>"+val+"</option>"; 
                        });
                        drop_content += "</select>";
                        $('#auto_sugg_'+response_data.section).html(drop_content);
                    }else{
                        $('#auto_sugg_'+response_data.section).html('');
                    } 
                } 
            });
        });

        $(document).on('change', ".auto_suggest",function(){
            var text_field = $(this).prop('id');
            var value = $(this).val();
            $("input[name*='"+text_field+"']").val(value); 
            $(this).parent().html('');
        });

        $(document).on('click','.chkb', function(){
            if($(this).prop('checked')===true){
                $(this).parents('.check-wrap').siblings('.chbk-container').show();
            }else{
                $(this).parents('.check-wrap').siblings('.chbk-container').hide();
            }
            
        });
    })
</script>
<!-- end of page level js --> 
     
@stop
