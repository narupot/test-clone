@extends('layouts/admin/default') 

@section('title')
    @lang('admin_shipping.add_shipping_profile')
@stop

@section('header_styles')
 <link rel="stylesheet" href="{{ Config('constants.admin_css_url') }}select.css" />

<!--page level css -->

<!-- end of page level css -->

<script src="{{ Config('constants.admin_js_url') }}jquery.easy-autocomplete.min.js"></script> 
<link rel="stylesheet" href="{{ Config('constants.admin_css_url') }}easy-autocomplete.min.css"> 
    
@stop

@section('content')
    <div class="content ng-cloak" ng-controller="shipProfileCtrl" ng-cloak>
        @if(Session::has('succMsg'))
        <div class="alert alert-success alert-dismissable margin5">
            <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
            <strong>@lang('admin_common.success'):</strong> {{ Session::get('succMsg') }}
        </div>
        @endif 

        <form action="{{action('Admin\ShippingProfile\ShippingRateTableController@saveWizardRate')}}" method="post" class="" data-validate  data-invalid-class="invalid">
            {{ csrf_field() }}
            
            <div class="header-title">
                <h1 class="title">Edit "{{$shipping_profile->getShippingProfileDesc->name}}"</h1>
                <div class="float-right">
                <a href="{{ action('Admin\ShippingProfile\ShippingRateTableController@deliveryAtAddress') }}" class="btn btn-back">@lang('admin_common.back')</a>
                <button  name="submit_type" value="save_and_continue" class="btn btn-secondary save_and_continue" ng-disabled="prdData.loading.disableBtn" id="save_and_continue">@lang('admin_common.save_and_continue')</button>
                <button name="submit_type" value="save" class="btn btn-save" id="btn-save">@lang('admin_common.save')</button>
                </div>
            </div>
            <div class="content-wrap shipping-wizards clearfix"> 
                <input type="hidden" name="shipping_profile_id" value="{{$shipping_profile->id}}">
                <div class="row">
                    <div class="col-sm-9">
                        <h3 class="title">@lang('admin_shipping.rate_configuration')</h3>
                        <div class="box">@lang('admin_shipping.destination')</div>         
                        <div class="col-sm-12">
                            <div class="form-group row">
                                <label class="col-sm-12">@lang('admin_shipping.country')</label>
                                <div class="col-sm-12">
                                   <select name="country_id" id="country" class="getRelatedData">
                                       <!-- <option value="All">All</option> -->
                                       @foreach($country_data as $country_key => $country)
                                       <option value="{{$country['id']}}">{{$country['country_name']['country_name']}}</option>
                                       @endforeach
                                   </select>
                                </div>
                            </div>
                        </div>
                        <div class="box">@lang('admin_shipping.conditions')</div>
                        <div class="col-sm-12" >
                            <div class="form-group row">
                                <label class="col-sm-12">@lang('admin_shipping.product_type_id')</label>
                                <div class="col-sm-12">
                                    {!! Form::select('product_type_id',['all'=>Lang::get('shipping.all')],  null,['id'=>'product_type_id']) !!}
                                </div>
                            </div>
                            <div class="form-group row">
                                <label class="col-sm-12">@lang('admin_shipping.estimate_ship_time')</label>
                                <div class="col-sm-12">
                                    <input type="number" name="estimate_shipping" value="1">
                                </div>
                            </div>
                        </div>
                        <div class="box">@lang('admin_shipping.wizard_rate_options')</div>
                        <div class="col-sm-12">
                            <div class="form-group">                                  
                                <label class="radio-wrap">
                                    <input type="radio" class="select_shipping" name="shipping_type" checked="checked" value="free_shipping"> <span class="radio-label">@lang('admin_shipping.free_shipping')</span>
                                </label>
                            </div>
                            <div class="form-group">                                   
                                <label class="radio-wrap">
                                    <input type="radio" class="select_shipping" name="shipping_type" value="fixed_rate_per_order"> <span class="radio-label">@lang('admin_shipping.fixed_rate_per_order')</span>
                                </label>
                                <div class="range-group-container dynamic_container d-none">
                                    <div class="row range-group">
                                        <div class="rangecol text-nowrap">Fee</div>
                                        <div class="rangecol">
                                            <input type="text" name="fixed_rate_per_order[first][ship_fee]" placeholder="0">
                                        </div>
                                        <div class="rangecol">THB</div>
                                    </div>                                                                
                                </div>
                            </div>
                            <div class="form-group">                                   
                                <label class="radio-wrap">
                                    <input type="radio" class="select_shipping" name="shipping_type" value="first_and_next_item"> <span class="radio-label">@lang('admin_shipping.first_and_next_item')</span>
                                </label>
                                <div class="range-group-container dynamic_container d-none">
                                    <div class="row range-group">
                                        <div class="rangecol text-nowrap">First Item</div>
                                        <div class="rangecol">
                                            <input type="text" name="first_and_next_item[first][ship_fee]" placeholder="0" value="0">
                                        </div>
                                        <div class="rangecol">THB</div>
                                    </div>
                                    <div class="row range-group">
                                        <div class="rangecol text-nowrap">Next Item</div>
                                        <div class="rangecol">
                                            <input type="text" name="first_and_next_item[next][ship_fee]" placeholder="0" value="0">
                                        </div>
                                        <div class="rangecol">THB/Unit</div>
                                    </div>                                                            
                                </div>
                            </div>
                            <div class="form-group">                                   
                                <label class="radio-wrap">
                                    <input type="radio" class="select_shipping" name="shipping_type" value="base_on_qty_range"> <span class="radio-label">@lang('admin_shipping.base_on_qty_range')</span>
                                </label>
                                <div class="range-group-container dynamic_container d-none">
                                    
                                    <div class="wizardOption range-group-wrap">
                                        <div class="row range-group" id="Unit">
                                            <div class="rangecol text-nowrap">Less Than</div>
                                            <div class="rangecol">
                                                <input type="text" class="ship_type" id="base_on_qty_range" name="base_on_qty_range[first][first_input]" placeholder="2" value="2"> 
                                            </div>
                                            <div class="rangecol custom-width">Unit</div>
                                            <div class="rangecol">
                                                <label class="radio-wrap">
                                                    <input type="radio" name="base_on_qty_range[first][ship_status]" value="no_ship" class="ship_status"><span class="radio-label">No Ship</span>
                                                </label>
                                            </div>
                                            <div class="rangecol">
                                                <label class="radio-wrap">
                                                    <input type="radio" name="base_on_qty_range[first][ship_status]" value="ship" checked="checked" class="ship_status"><span class="radio-label">Fee</span>
                                                </label>
                                            </div>
                                            <div class="rangecol ship_fee">
                                                <input type="text" name="base_on_qty_range[first][ship_fee]" placeholder="0" value="0"> THB
                                            </div>
                                            
                                            <div class="rangecol addremove">
                                                <button class="btn btn-primary mr-2 add_row" type="button"><i class="fas fa-plus"></i></button>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="row range-group">
                                        <div class="rangecol text-nowrap">More than <span class="last_number"> </span> </div>
                                        <div class="rangecol">
                                            <label class="radio-wrap"><input type="radio" name="base_on_qty_range[last][ship_status]" value="no_ship" class="ship_status">
                                                <span class="radio-label">No Ship</span>
                                            </label>
                                        </div>
                                        <div class="rangecol">
                                            <label class="radio-wrap">
                                                <input type="radio" name="base_on_qty_range[last][ship_status]" checked="checked" value="ship" class="ship_status"><span class="radio-label" >Fee</span>
                                            </label>
                                        </div>
                                        <div class="rangecol ship_fee">
                                            <input type="text" name="base_on_qty_range[last][ship_fee]" placeholder="0" value="0"> THB
                                        </div>
                                        
                                    </div>                                                                
                                </div>
                            </div>
                            <div class="form-group">                                   
                                <label class="radio-wrap">
                                    <input type="radio" class="select_shipping" name="shipping_type" value="base_on_weight_range"> <span class="radio-label">@lang('admin_shipping.base_on_weight_range')</span>
                                </label>
                                <div class="range-group-container dynamic_container d-none">
                                    <div class="wizardOption range-group-wrap">
                                        <div class="row range-group" id="gram">
                                            <div class="rangecol text-nowrap">Less Than</div>
                                            <div class="rangecol">
                                                <input type="text" class="ship_type" id="base_on_weight_range" name="base_on_weight_range[first][first_input]" placeholder="0" value="2"> 
                                            </div>
                                            <div class="rangecol custom-width">gram</div>
                                            <div class="rangecol">
                                                <label class="radio-wrap">
                                                    <input type="radio" name="base_on_weight_range[first][ship_status]" value="no_ship" class="ship_status"><span class="radio-label">No Ship</span>
                                                </label>
                                            </div>
                                            <div class="rangecol">
                                                <label class="radio-wrap">
                                                    <input type="radio" name="base_on_weight_range[first][ship_status]" value="ship" class="ship_status" checked="checked"><span class="radio-label">Fee</span>
                                                </label>
                                            </div>
                                            <div class="rangecol ship_fee">
                                                <input type="text" name="base_on_weight_range[first][ship_fee]" placeholder="0" value="0"> THB
                                            </div>
                                            <div class="rangecol addremove">
                                                <button class="btn btn-primary mr-2 add_row" type="button"><i class="fas fa-plus"></i></button>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="row range-group">
                                        <div class="rangecol text-nowrap">More than <span class="last_number"> </span> </div>
                                        <div class="rangecol">
                                            <label class="radio-wrap">
                                                <input type="radio" name="base_on_weight_range[last][ship_status]" value="no_ship" class="ship_status"><span class="radio-label">No Ship</span>
                                            </label>
                                        </div>
                                        <div class="rangecol">
                                            <label class="radio-wrap">
                                                <input type="radio" name="base_on_weight_range[last][ship_status]" value="ship" checked="checked" class="ship_status"><span class="radio-label">Fee</span>
                                            </label>
                                        </div>
                                        <div class="rangecol ship_fee">
                                            <input type="text" name="base_on_weight_range[last][ship_fee]" placeholder="0" value="0"> THB
                                        </div>
                                   </div>                                                                 
                                </div>
                            </div>
                            <div class="form-group">                                   
                                <label class="radio-wrap">
                                    <input type="radio" class="select_shipping" name="shipping_type" value="base_on_price_subtotal_range"> <span class="radio-label">@lang('admin_shipping.base_on_price_subtotal_range')</span>
                                </label>
                                <div class="range-group-container dynamic_container d-none">
                                    <div class="wizardOption range-group-wrap">
                                        <div class="row range-group" id="THB">
                                            <div class="rangecol text-nowrap">Less Than</div>
                                            <div class="rangecol">
                                                <input type="text" class="ship_type" id="base_on_price_subtotal_range" name="base_on_price_subtotal_range[first][first_input]" placeholder="0" value="0"> 
                                            </div>
                                            <div class="rangecol custom-width">THB</div>
                                            <div class="rangecol">
                                                <label class="radio-wrap">
                                                    <input type="radio" name="base_on_price_subtotal_range[first][ship_status]" value="no_ship" class="ship_status"><span class="radio-label">No Ship</span>
                                                </label>
                                            </div>
                                            <div class="rangecol">
                                                <label class="radio-wrap">
                                                    <input type="radio" name="base_on_price_subtotal_range[first][ship_status]" value="ship" class="ship_status" checked="checked"><span class="radio-label">Fee</span>
                                                </label>
                                            </div>
                                            <div class="rangecol ship_fee">
                                                <input type="text" name="base_on_price_subtotal_range[first][ship_fee]" placeholder="0" value="0"> THB
                                            </div>
                                            <div class="rangecol addremove">
                                                <button class="btn btn-primary mr-2 add_row" type="button"><i class="fas fa-plus"></i></button>
                                            </div>
                                        </div>
                                    </di    v>
                                    <div class="row range-group">
                                        <div class="rangecol text-nowrap">More than <span class="last_number"> </span> </div>
                                        
                                        <div class="rangecol">
                                            <label class="radio-wrap">
                                                <input type="radio" name="base_on_price_subtotal_range[last][ship_status]" value="no_ship" class="ship_status"><span class="radio-label">No Ship</span>
                                            </label>
                                        </div>
                                        <div class="rangecol">
                                            <label class="radio-wrap">
                                                <input type="radio" name="base_on_price_subtotal_range[last][ship_status]" checked="checked" value="ship" class="ship_status"><span class="radio-label">Fee</span>
                                            </label>
                                        </div>
                                        <div class="rangecol ship_fee">
                                            <input type="text" name="base_on_price_subtotal_range[last][ship_fee]" placeholder="0" value="0"> THB
                                        </div>
                                        
                                    </div>
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

        $(document).on('click','.select_shipping', function(){
            if($(this).prop('checked')===true){
                $('.radio-wrap').next('.dynamic_container').addClass('d-none');
                $(this).parents('.radio-wrap').next('.dynamic_container').removeClass('d-none');
            }
            
        });

        $(document).on('click','.add_row', function(){

            var total = $(this).parents('.dynamic_container').children('.wizardOption').children('.range-group').length;
            var dyn_cont = '<div class="row range-group" id="{UNIT}"><div class="rangecol text-nowrap" >More than <span class="row_number"> {NUM} {UNIT}</span> but not more than</div><div class="rangecol"><input type="text" class="ship_type" id="{PREFIX_ID}" name="{PREFIX_NAME}[first_input]" placeholder="0" value="0"></div><div class="rangecol custom-width change_unit">{UNIT}</div><div class="rangecol"><label class="radio-wrap"><input type="radio" name="{PREFIX_NAME}[ship_status]" value="no_ship" class="ship_status"><span class="radio-label">No Ship</span></label></div><div class="rangecol"><label class="radio-wrap"><input type="radio" name="{PREFIX_NAME}[ship_status]" value="ship" checked="checked" class="ship_status"><span class="radio-label">Fee</span></label> </div><div class="rangecol ship_fee"><input type="text" name="{PREFIX_NAME}[ship_fee]" placeholder="0" value="0">THB</div><div class="rangecol addremove"><button class="btn btn-primary mr-2 add_row" type="button"><i class="fas fa-plus"></i></button><button class="btn btn-danger remove_row" type="button"><i class="fas fa-minus"></i></button></div></div>';

            var PREFIX_ID = $(this).parents('.dynamic_container').find('.ship_type').attr('id');
            var count = parseInt(total)+parseInt(1);
            var PREFIX_NAME = PREFIX_ID+'['+count+']';

            var UNIT = $(this).parents('.dynamic_container').find('.range-group').attr('id');
            var NUM = $(this).parents('.dynamic_container').children('.wizardOption').children('.range-group').last().find('.ship_type').val();

            if(NUM===undefined){
                NUM = $(this).parents('.dynamic_container').find('.ship_type').val();
            }

            dyn_cont = dyn_cont.replace(/{PREFIX_NAME}/g,PREFIX_NAME);
            dyn_cont = dyn_cont.replace(/{PREFIX_ID}/g,PREFIX_ID);
            dyn_cont = dyn_cont.replace(/{UNIT}/g,UNIT);
            dyn_cont = dyn_cont.replace(/{NUM}/g,NUM);

            $(this).parents('.dynamic_container').children('.wizardOption').append(dyn_cont);
            //validateElementValues($(this).parents('.wizardOption').children());
        });

        $(document).on('click','.remove_row', function(){
            let $that = $(this).parents('.wizardOption');            
            $(this).parents('.wizardOption  > .range-group').last().remove();
            validateElementValues($that.children());
        });

        $(document).on('blur','.ship_type', function(){
            var UNIT = $(this).parents('.dynamic_container').find('.range-group').attr('id');
            $(this).parents('.range-group').next().find('.row_number').html($(this).val()+' '+UNIT);

            if($(this).parents('.range-group').next().find('.row_number').length===0){
                $(this).parents('.dynamic_container').find('.last_number').html($(this).val()+' '+UNIT);
            }
            
            validateElementValues($(this).parents('.wizardOption').children()); 
        });

        $(document).on('click','.ship_status', function(){
            
            if($(this).val()=='no_ship'){
                $(this).parents('.rangecol').siblings('.ship_fee').hide();
            }

            if($(this).val()=='ship'){
                $(this).parents('.rangecol').siblings('.ship_fee').show();
            }
            
        });

        function validateElementValues($elem){             
            let arrays = [];
            let erIndexArrs = [];
            $elem.each(function(){
                let $el = $(this).children(':nth-child(2)');
                arrays.push(parseInt($el.find(".ship_type").val()));
            });

            function checkOrderedArrElm(a, b) {                
                let m = 0, 
                    current_num, 
                    next_num;               
                // loop through array elements.
                while (m < a.length) { 
                    current_num = a[m];
                    next_num = a[m + 1];
                    if (typeof current_num === "number" && typeof next_num === "number") {    
                        // found unordered/same elements.
                        console.log(current_num, next_num);                    
                        if (current_num >= next_num)erIndexArrs.push(m+1);
                    }
                    m += 1;
                };            
            };
            checkOrderedArrElm(arrays);                       
            $elem.each(function(ind){ 
                let $el = $(this).children(':nth-child(2)');
                if(erIndexArrs.length && erIndexArrs.indexOf(ind)!=-1)
                   $el.addClass('error');
                else $el.removeClass('error');
            });

            return (erIndexArrs.length===0) ? true:false;
        };

        $(document).on('click','#save_and_continue, #btn-save', function(){
            //var save_url = "{{action('Admin\ShippingProfile\ShippingRateTableController@saveWizardRate')}}";
            
             return validateElementValues($(".select_shipping:checked").parents('.radio-wrap').next('.dynamic_container').children('.wizardOption').children());
        });
    });
</script>
<!-- end of page level js --> 
     
@stop
