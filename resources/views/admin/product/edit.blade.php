@extends('layouts.admin.default')
@section('title')
    @lang('admin_product.edit_product')
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
        var parent_cat_data_url = "{{url('/admin/product/parent-cat-data')}}";
        var package_id = "{{$result->package_id}}";
    </script>

    <style>
        .product-selection-wrap {
            width: 100%;
            background-color: #f9f9fa;
            border: 1px solid #e3e3e3;
            border-radius: 10px;
            padding: 20px;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        label.category-option {
            margin-bottom: 0px;
        }
        /* Search box */
        #product-search {
            width: 200px;
            max-width: 100%;
            padding: 8px 14px;
            border-radius: 8px;
            border: 1px solid #ced4da;
            font-size: 14px;
            margin-bottom: 12px;
            transition: all 0.3s ease;
        }
        #product-search:focus {
            width: 350px;
            border-color: #28a745;
            box-shadow: 0 0 8px rgba(40,167,69,0.2);
        }

        /* Grid layout with scroll */
        /* .select-product-grid-wrapper {
            max-height: 200px; 
            overflow-y: auto;
            border: 1px solid #e3e3e3; 
            border-radius: 2px;
            padding: 4px;
        } */

        .select-product-grid {
            display: flex;   
            flex-wrap: wrap;       
            gap: 4px;               
            list-style: none;
            padding: 0;
            margin: 0;
        }
        .select-product-grid li {
            display: inline-flex;  
            align-items: center;
            border: 2px solid #d2d2d7;
            border-radius: 6px;
            padding: 4px 8px;  
            background-color: #fff;
            white-space: nowrap;
            cursor: pointer;
            transition: all 0.2s ease;
        }


        /* Label inside li */
        .select-product-grid label {
            display: flex;
            align-items: center;
            gap: 6px;
            padding: 4px 8px;
            cursor: pointer;
            font-size: 0.85rem;
            user-select: none;
        }

        /* Radio button */
        .select-product-grid input[type="radio"] {
            accent-color: #28a745;
            margin: 0;
        }

        /* Hover effect */
        .select-product-grid li:hover {
            border-color: #28a745;
            box-shadow: 0 1px 4px rgba(40,167,69,0.2);
        }

        /* Checked / active state */
        .select-product-grid li.active {
            border-color: #28a745;
            background-color: #f0fff4;
            font-weight: 600;
        }
        #select-product-img {
            min-height: 80px; 
            display: flex;
            align-items: center;
            justify-content: center;
            text-align: center;
        }
        #select-product-img p {
            margin: 0;
            padding: 10px 0;
            font-size: 0.95rem;
            color: #6c757d;
            font-style: italic;
        }

        .category-item {
            list-style: none;
        }

        .category-option .category-name {
            font-size: 0.9rem;
            color: #333;
            
        }
        .select-product-grid .empty-message {
            list-style: none;
            grid-column: 1 / -1;  
            text-align: center;
            color: #6c757d;
            font-style: italic;
            padding: 0px 0;
            margin: 0;           
        }

        

    </style>

    <style>
    /* ซ่อน Radio Button เดิม */
    .btn-check {
        position: absolute;
        clip: rect(0,0,0,0);
        pointer-events: none;
    }

    .product-status-wrapper {
        display: flex;
        gap: 10px; /* ระยะห่างระหว่างปุ่ม */
    }

    /* สไตล์ปุ่มพื้นฐาน */
    .status-btn {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        padding: 10px 20px;
        border-radius: 50px; /* ทรงแคปซูล */
        border: 2px solid #e9ecef;
        background-color: #fff;
        color: #6c757d;
        cursor: pointer;
        transition: all 0.3s ease;
        font-weight: 500;
        user-select: none;
    }

    .status-btn:hover {
        background-color: #f8f9fa;
    }

    /* --- สีเขียว เมื่อเลือก In Stock --- */
    #status_instock:checked + label {
        background-color: #e8f5e9;
        border-color: #28a745;
        color: #28a745;
        box-shadow: 0 4px 6px rgba(40, 167, 69, 0.2);
    }

    /* --- สีแดง/ส้ม เมื่อเลือก Out of Stock --- */
    #status_outstock:checked + label {
        background-color: #ffebee;
        border-color: #dc3545;
        color: #dc3545;
        box-shadow: 0 4px 6px rgba(220, 53, 69, 0.2);
    }

    /* --- Stock Selector Card Styles --- */
.stock-selector-card {
    cursor: pointer;
    position: relative;
    display: block;
    margin: 0;
}

.stock-selector-card input.stock-radio {
    position: absolute;
    opacity: 0;
    cursor: pointer;
}

.stock-selector-card .card-content {
    display: flex;
    align-items: center;
    padding: 15px;
    border: 2px solid #eaecf0;
    border-radius: 12px;
    transition: all 0.2s ease-in-out;
    background: #fff;
    position: relative;
    overflow: hidden;
}

.stock-selector-card:hover .card-content {
    border-color: #b4b7bd;
    background-color: #f8f9fa;
}

.icon-wrapper {
    width: 45px;
    height: 45px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    margin-right: 15px;
    flex-shrink: 0;
}
.bg-soft-success { background-color: #e6f9ed; }
.bg-soft-primary { background-color: #ebf5ff; }

/* Check Icon (ซ่อนไว้ก่อน) */
.check-icon {
    position: absolute;
    top: 10px;
    right: 10px;
    color: #28a745;
    opacity: 0;
    transform: scale(0.5);
    transition: all 0.2s;
}

/* --- State: Checked (เมื่อถูกเลือก) --- */
.stock-selector-card input:checked + .card-content {
    border-color: #0d47a1;
    background-color: #f0f7ff;
    box-shadow: 0 4px 12px rgba(13, 71, 161, 0.08);
}

.stock-selector-card input:checked + .card-content .check-icon {
    opacity: 1;
    transform: scale(1);
}

/* --- Animation ช่องกรอก --- */
.stock-input-wrapper {
    transition: all 0.3s ease;
    animation: slideDown 0.3s ease-out;
}

@keyframes slideDown {
    from { opacity: 0; transform: translateY(-10px); }
    to { opacity: 1; transform: translateY(0); }
}
</style>
@stop

@section('content')

<div class="content">
   {!! Form::open(['url' => action('Admin\Product\ProductController@update', $result->id), 'id'=>'addProductForm', 'class'=>'form-horizontal  form-bordered ', 'files'=>True]) !!}

        <div class="header-title">
            <h1 class="title">@lang('admin_customer.edit_product')</h1>
            <div class="float-right">
                <a href="{{ action('Admin\Product\ProductController@index') }}" class="btn btn-back">@lang('admin_common.back')</a>
                <button type="submit" class="change-pwd btn btn-save btn-primary">@lang('common.submit')</button>
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
                    <div class="col-sm-6 form-group">
                        <label for="shop_name">@lang('product.select_seller')<i class="red">*</i></label>
                         <input type="text" name="shop_name" id="shop_name" value="@if(isset($result->shop->shop_name)){{$result->shop->shop_name}}@endif">
                        <input type="hidden" name="shop_id" id="shop_id" value="{{$result->shop_id}}">

                        {{-- div> <button type="button" id="open_popup">Open Popup</button> </div> --}}

                        <div title="Grid in Dialog" id="popup" style="overflow:hidden;">
                            <div id="grid_popup"></div>
                        </div>
                       
                        @if($errors->has('shop_id'))
                           <p class="error error-msg">{{ $errors->first('shop_id') }}</p>
                        @endif
                    </div>
                </div>
                <div class="form-group row mb-4">

                    <div class="col-sm-6">
                        <label class="form-label fw-bold" for="product-search">
                            @lang('product.select_product') <span class="red">*</span>
                        </label>
                        
                        <div class="product-selection-wrap">
                            <input type="text" id="product-search" placeholder="@lang('common.search') ...">
                            
                            <ul class="select-product-grid" id="select-product-grid">
                                @if(isset($seller_prod_cat) && count($seller_prod_cat) > 0)
                                    @foreach($seller_prod_cat as $prod_cat)
                                        <li class="category-item {{ (isset($result) && $prod_cat->id == $result->cat_id) ? 'active' : '' }}" data-category-name="{{ $prod_cat->category_name }}">
                                            <label class="category-option">
                                                <input type="radio" name="product_cat" value="{{ $prod_cat->id }}" {{ (isset($result) && $prod_cat->id == $result->cat_id) ? 'checked' : '' }}>
                                                <span class="category-name">{{ $prod_cat->category_name }}</span>
                                            </label>
                                        </li>
                                    @endforeach
                                @else
                                    <p class="empty-message">
                                        ไม่มีข้อมูลหมวดหมู่สินค้า กรุณาเลือกผู้ขายก่อน
                                    </p>
                                @endif
                            </ul>
                            
                            @if($errors->has('product_cat'))
                                <p class="error-msg mt-2">{{ $errors->first('product_cat') }}</p>
                            @endif
                        </div>
                    </div>
                </div>
            
                @include('includes.edit_product_include')

            </div>
        </div>


    {!! Form::close() !!}

</div>
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
                    // Show loading indicator
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

