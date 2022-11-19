@extends('layouts.admin.default')
@section('title')
    @lang('admin_product.copy_product')
@stop

@section('header_styles')
   <link rel="stylesheet" href="{{ Config('constants.admin_css_url') }}table/pqgrid.min.css"/>
   <link rel="stylesheet" href="{{ Config('constants.admin_css_url') }}table/pqgrid.ui.min.css"/>
   <link rel="stylesheet" href="{{ Config('constants.admin_css_url') }}table/pqgrid.css"/>
   <link rel="stylesheet" href="{{ Config('constants.admin_css_url') }}table/pqselect.min.css"/>
   <script type="text/javascript">
        var lang_json = {"ok":"@lang('common.ok')", "success":"@lang('common.success')", "select":"@lang('common.select')"};
        var base_unit_url = '{{action('Admin\Product\ProductController@baseUnit')}}';
        var currency = "@lang('common.baht')";
        var base_unit_id = "{{$result->base_unit_id}}";
    </script>
@stop

@section('content')

<aside class="content">
   {!! Form::open(['url' => action('Admin\Product\ProductController@copystore', $result->id), 'id'=>'addProductForm', 'class'=>'form-horizontal  form-bordered col-sm-6', 'files'=>True]) !!}
    
        <div class="form-group row">
            <label>@lang('product.select_seller')<i class="red">*</i></label>
            <div class="col-sm-12">
                <input type="text" name="shop_name" id="shop_name" value="@if(isset($result->shop->shop_name)){{$result->shop->shop_name}}@endif">
                <input type="hidden" name="shop_id" id="shop_id" value="{{$result->shop_id}}">
                <!--div>
                    <button type="button" id="open_popup">Open Popup</button>
                </div-->

                <div title="Grid in Dialog" id="popup" style="overflow:hidden;">
                    <div id="grid_popup"></div>
                </div>
               
                @if($errors->has('shop_id'))
                   <p class="error error-msg">{{ $errors->first('shop_id') }}</p>
                @endif  
            </div>                      
        </div>
        <div class="content-wrap column-modal clearfix">
         <div class="breadcrumb">
                <ul class="bredcrumb-menu">
                    {!!getBreadcrumbAdmin('product')!!}
                </ul>
            </div>

        <div class="form-group row">
            <div class="col-sm-12">
                <label>@lang('product.select_product')<i class="red">*</i></label>
                 <ul class="select-product-img" id="select-product-img">
                    @if(count($seller_prod_cat) > 0)
                        @foreach($seller_prod_cat as $prod_cat)
                            <li @if($prod_cat->id == $result->cat_id)class="active"@endif>
                                <div class="img-block"><img src="{{getCategoryImageUrl($prod_cat->img)}}" width="76" height="57" alt=""></div>
                                <label class="radio-wrap">
                                    <input type="radio" name="product_cat" value="{{$prod_cat->id}}" @if($prod_cat->id == $result->cat_id)checked="checked" @endif>
                                    <span class="radio-mark"></span>
                                </label>
                            </li>
                        @endforeach
                    @endif
                </ul>
                @if($errors->has('product_cat'))
                   <p class="error error-msg">{{ $errors->first('product_cat') }}</p>
                @endif
            </div>                      
        </div>
    
        @include('includes.edit_product_include')


        <div class="form-group">
            <button type="submit" class="change-pwd btn btn-save">@lang('common.submit')</button>
            <span id="loader_span"></span>
        </div> 

        </div>
    {!! Form::close() !!}

</aside>
@stop


@section('footer_scripts')
    <script src="{{ Config('constants.admin_js_url') }}table/pqgrid.min.js"></script>
    <script src="{{ Config('constants.admin_js_url') }}table/pqselect.min.js"></script>
    <script src="{{ Config('constants.admin_js_url') }}table/jquery.resize.js"></script>
    <script src="{{ Config('constants.admin_js_url') }}table/pqtouch.min.js"></script>
    <script src="{{ Config('constants.admin_js_url') }}table/jszip.min.js"></script>
    <script src="{{ Config('constants.admin_js_url') }}table/FileSaver.min.js"></script>
    <script>
         // $(document).ready(function() {
        //     var table =  $('table.table').DataTable();
        // });
        //Init table 
        $(function jQGridTable($){   
            //function to change format from dd/mm/yy to mm/dd/yy and vice versa
            function changeFormat(value){
                d1 = value ? value.split('/') : null;
                return value ? d1[1] + '/' + d1[0] + '/' + d1[2] : "";
            }
            function pqDatePicker(ui) {
                var $this = ui.$editor;
                $this
                    //.css({ zIndex: 3, position: "relative" })
                    .datepicker({
                        yearRange: "-25:+0", //25 years prior to present.
                        changeYear: true,
                        changeMonth: true,
                        dateFormat: "dd/mm/yy"
                        //showButtonPanel: true
                    });
                //default From date
                var $from = $this.filter(".pq-from").datepicker("option", "defaultDate", new Date("01/01/1996"));
                //default To date
                var $to = $this.filter(".pq-to").datepicker("option", "defaultDate", new Date("12/31/1998"));

                var value = changeFormat(ui.column.filter.value),
                    value2 = changeFormat(ui.column.filter.value2);

                $from.val(value);
                $to.val(value2);
            };      
            

            //let view_url = "{{action('Admin\Customer\UserController@index')}}";
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

            /*
            *@desc : Table column configrations
                Array of column 

            */
            let columnModel = [  
                { title: "ID", width: 100, dataType: "integer", dataIndx: "id" },        
                { title: "@lang('admin_customer.booth_no')", dataIndx:'panel_no', minWidth: 200,filter : { attr : "@lang('admin_common.enter_booth_no')",                
                    crules: [{condition: 'contain'}],
                        type: 'textbox', 
                        listeners: ['keyup'],
                        // conditionList: ['begin', 'contain', 'notbegin', 'notcontain'],                        
                        menuIcon : !1,
                    },
                },
                { title: "@lang('admin_customer.shop_name')", dataIndx:'shop_name', minWidth: 250,
                    filter : {
                        attr : "@lang('admin_common.enter_shop_name')",
                        crules: [{condition: 'contain'}],
                        type: 'textbox', 
                        listeners: ['keyup'],
                        // conditionList: ['begin', 'contain', 'notbegin', 'notcontain'],                        
                        menuIcon : !1,
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

            /*
            *@desc : Init table 
            */

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
                    height: 450,
                    width: 600,
                    //width: 'auto',
                    modal: true,
                    open: function (evt, ui) {   
                        $("#grid_popup").pqGrid($gridObj);
                    },
                    close: function () {
                        $("#grid_popup").pqGrid('destroy');
                    },
                    show: {
                        effect:"blind",
                        duration: 500
                    }
                });
            });

        

            



        });
    </script>

   
 {!!CustomHelpers::combineCssJs(['js/price_formatter', 'js/seller/product', 'js/sgCustom'],'js') !!}
 <script type="text/javascript">
    $(function(){
       $('.active input[name="product_cat"]').trigger('click');
    });
</script>  

@stop

