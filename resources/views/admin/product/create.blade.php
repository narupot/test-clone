@extends('layouts.admin.default')
@section('title')
    @lang('admin_product.create_product')
@stop

@section('header_styles')
   <link rel="stylesheet" href="{{ Config('constants.admin_css_url') }}table/pqgrid.min.css"/>
   <link rel="stylesheet" href="{{ Config('constants.admin_css_url') }}table/pqgrid.ui.min.css"/>
   <link rel="stylesheet" href="{{ Config('constants.admin_css_url') }}table/pqgrid.css"/>
   <link rel="stylesheet" href="{{ Config('constants.admin_css_url') }}table/pqselect.min.css"/>
    <script type="text/javascript">
        var lang_json = {"ok":"@lang('common.ok')", "success":"@lang('common.success')", "select":"@lang('common.select')"};
        var base_unit_url = '{{action('Seller\ProductController@baseUnit')}}';
    </script>
@stop
@section('content')

<div class="content">
   {!! Form::open(['url' => action('Admin\Product\ProductController@store'), 'id'=>'addProductForm', 'class'=>'form-horizontal  form-bordered', 'files'=>True]) !!}
     <div class="header-title">
        <h1 class="title">@lang('admin_customer.create_new_product')</h1>
         <div class="float-right">
            <button type="submit" class="change-pwd btn btn-save">@lang('common.submit')</button>
            <span id="loader_span"></span>
        </div> 
    </div>
    <div class="content-wrap">
        <div class="breadcrumb">
            <ul class="bredcrumb-menu">
                {!!getBreadcrumbAdmin('product')!!}
            </ul>
        </div>
        <div class="content-right">
            <div class="row">            
                <div class="form-group col-sm-5">
                    <label>@lang('product.select_seller')<i class="red">*</i></label>
                    <input type="text" name="shop_name" id="shop_name">
                    <input type="hidden" name="shop_id" id="shop_id">               
                    @if($errors->has('shop_id'))
                       <p class="error error-msg">{{ $errors->first('shop_id') }}</p>
                    @endif  
                </div>    
                <div class="form-group col-sm-2">
                    <div class="form-group">
                        <label>&nbsp;</label>       
                        <button type="button" id="open_popup" class="btn btn-primary">@lang('product.select_seller')</button>
                        <div title="Grid in Dialog" id="popup" style="overflow:hidden;">
                            <div id="grid_popup"></div>
                        </div> 
                    </div>
                </div>                  
            </div>
            <div class="form-group row">
                <div class="col-sm-12">
                    <label>@lang('product.select_product')<i class="red">*</i></label>
                    <ul class="select-product-img" id="select-product-img">                
                    </ul>
                    @if($errors->has('product_cat'))
                       <p class="error error-msg">{{ $errors->first('product_cat') }}</p>
                    @endif  
                </div>                      
            </div>        
            @include('includes.add_product_include')
        </div>
    {!! Form::close() !!}
    </div>
</div>
@stop


@section('footer_scripts')
   {!! CustomHelpers::dataTableJs() !!}
    <script>
        //let JQ_GRID_DATA_URL = "{{ action('Admin\Product\ProductController@SellerData') }}";

        let dataModel = {
                location: "remote",
                dataType: "JSON",
                method: "GET",
                url: "{{ action('Admin\Product\ProductController@SellerData') }}",
                beforeSend: (jqXHR, settings) => {
                    //
                },                
                getData: function (resp) {
                    return {
                        curPage: resp.current_page,
                        totalRecords: resp.total,
                        data: resp.data
                    };
                },
            };
        let columnModel = [  
            {   title: "", 
                width: 50, 
                dataType: "integer",
                type:'checkbox', 
                cbId: 'state',
                sortable : false,
                align : 'center',
            },
            { 
                dataIndx: 'state', 
                editable: true,
                cb: {header: true, select: true, all: true}, 
                dataType: 'bool',
                hidden: true
            },
                { title: "ID", width: 100, dataType: "integer", dataIndx: "id", menuIcon : !1},        
                { title: "@lang('admin_customer.booth_no')", dataIndx:'panel_no', minWidth: 200,menuIcon : !1, filter : { attr : "@lang('admin_common.enter_booth_no')",                
                    crules: [{condition: 'contain'}],
                        type: 'textbox', 
                        listeners: ['change'],
                        // conditionList: ['begin', 'contain', 'notbegin', 'notcontain'],                        
                    },
                },
                { title: "@lang('admin_customer.shop_name')", dataIndx:'shop_name', minWidth: 250,menuIcon : !1,
                    filter : {
                        attr : "@lang('admin_common.enter_shop_name')",
                        crules: [{condition: 'contain'}],
                        type: 'textbox', 
                        listeners: ['change'],
                        // conditionList: ['begin', 'contain', 'notbegin', 'notcontain'],                        
                        //menuIcon : !1,
                    },
                },
                
                /*{ title: "@lang('admin_customer.phone_no')", dataIndx:'ph_number', minWidth: 140,
                    filter: {
                        attr : "@lang('admin_common.enter_name_phone_number')",
                        crules: [{condition: 'contain'}],
                        type: 'textbox', 
                        listeners: ['keyup'],
                        // conditionList: ['begin', 'contain', 'notbegin', 'notcontain'],                        
                        menuIcon : !1,
                    },
                },
                { title: "@lang('admin_common.status')", dataIndx:'status', minWidth: 140, render : function(ui){
                        return {
                            text : (ui.cellData.toString() == "1") ? "{{Lang::get('common.active')}}" : "{{Lang::get('common.inactive')}}",
                        }
                    },
                    filter : {
                        attr: "placeholder='@lang('admin_common.please_select')'",
                        crules: [{condition: 'range'}],
                        conditionList: ['contain', 'range'],                       
                        options: [ 
                            {"1": "{{Lang::get('common.active')}}"}, 
                            {"0": "{{Lang::get('common.inactive')}}"},                             
                        ],
                    
                    },
                },
                { title: "@lang('admin_common.created_at')", dataIndx:'created_at', minWidth: 140, 
                    dataType: "date",
                    filter: { 
                        type: 'textbox',
                        condition: "between",
                        init: pqDatePicker,                        
                    },
                },
                { title: "@lang('admin_common.last_updated')", dataIndx:'updated_at', minWidth: 140,
                    dataType: "date",
                    filter: { 
                        type: 'textbox',
                        condition: "between",
                        init: pqDatePicker,                        
                    },
                },
                { title: "@lang('admin_common.actions')", dataIndx:'id', render : function(ui) {
                        return {
                            text:'<a href="'+view_url+'/'+ui.cellData+'" class="btn-primary">@lang("admin_common.view")</a>',    
                        };                
                    },
                    sortable : false,
                    menuIcon : false,
                },*/
            ]; 

            var $gridObj = {
                width : '100%',
                height : 'flex',
                dataModel: dataModel,
                colModel : columnModel,
                pageModel: { 
                    type: "remote", 
                    rPP: 10, 
                    rPPOptions: [10, 20, 50, 100],
                    strPage : "@lang('admin_common.pagination_page'){0} of {1}",
                    strRpp: "@lang('admin_common.pagination_records_per_page'): {0}",
                    strDisplay: "@lang('admin_common.pagination_displaying'){0}  to {1} of {2}", 
                },
                numberCell: {
                     title: "@lang('admin_common.sno')",
                     width : 50,
                     show: false,
                },
                sortModel: { 
                    type : 'remote',
                },
                
                filterModel: { 
                    type: 'remote',
                    on: true, 
                    mode: "AND", 
                    header: true,
                     
                },
                menuIcon: true,
                menuUI:{
                    tabs: ['hideCols']
                },
                editable: false,              
                collapsible : !1,  
                selectionModel: { type: 'row', mode: 'single' },
                rowSelect: function (evt, ui) {
                    //console.log('rowSelect', ui);
                    var str = JSON.stringify(ui, function(key, value){                    
                        if( key.indexOf("pq_") !== 0){
                            return value;
                        }
                    }, 2);

                    $('#shop_id').val(ui.addList[0].rowData.id);
                    $('#shop_name').val(ui.addList[0].rowData.shop_name);
                    //$("#grid_popup").pqGrid('destroy'); 
                    $('.ui-dialog-titlebar-close').button().click(); 
                        $.ajax({
                            type: 'get',
                            async: false,
                            url: "{{action('Admin\Product\ProductController@getSellerCategory')}}",
                            data: '_token=' + window.Laravel.csrfToken + '&shop_id=' + ui.addList[0].rowData.id,
                            success: function (data) {
                                if(data){
                                   $('#select-product-img').html(data); 
                                   $('.ui-dialog-titlebar-close').button().click();
                                }else{
                                  //swal('Error', data.Code + ' ' + data.Detail, 'error');
                                }
                            }
                        })
                    },
                swipeModel: {on: false},
            };

            $("button#open_popup").button().click(function (evt) {
                $("#popup").dialog({
                    height: 465,
                    width: 900,
                    //width: 'auto',
                    modal: true,
                    open: function (evt, ui) {   
                        $("#grid_popup").pqGrid($gridObj);
                        //$("#grid_popup").pqGrid('refreshDataAndView');
                    },
                    close: function () {
                        $("#grid_popup").pqGrid('destroy');
                    },
                    show: {
                        effect:"blind",
                        duration: 300
                    }
                });
            });
    </script>

   
 {!!CustomHelpers::combineCssJs(['js/price_formatter', 'js/seller/product', 'js/sgCustom'],'js') !!}

@stop

