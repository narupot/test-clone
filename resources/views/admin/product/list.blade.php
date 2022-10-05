@extends('layouts/admin/default')

@section('title')
    @lang('admin_product.product_list')
@stop

@section('header_styles')

   {!! CustomHelpers::dataTableCss() !!}
    <script type="text/javascript">
        var filter_data = {!! $filter !!};  
    </script>
    
@stop

@section('content')
    <div class="content">
        <div class="header-title">
            <h1 class="title">@lang('admin_product.product_list')</h1>
        </div>
             
        <!-- Main content -->         
        <div class="content-wrap">
             <div class="breadcrumb">
                <ul class="bredcrumb-menu">
                    {!!getBreadcrumbAdmin('product', 'product', 'list')!!}
                </ul>
            </div>
           <div id="jq_grid_table" class="table table-bordered">                 
                

            </div>
        </div>
    </div>
@stop

@section('footer_scripts')

    {!! CustomHelpers::dataTableJs() !!}
    <!-- end grid table js files -->  
    <script>
        let view_url = "{{action('Admin\Product\ProductController@index')}}";
        let edit_url = "{{action('Admin\Product\ProductController@edit')}}";
        let copy_url = "{{action('Admin\Product\ProductController@copy')}}";
        let delete_url = "{{action('Admin\Product\ProductController@deleteproduct')}}";
        let front_url = "{{action('ProductDetailController@display')}}";
        let JQ_GRID_DATA_URL = "{{ action('Admin\Product\ProductController@productListData') }}"; 
        var BATCH_ACTION_DELETE = {
            action_name : "@lang('admin_common.delete')",
            action_url : "{{action('Admin\Product\ProductController@deleteSelectedproducts')}}",
            btn_class : 'btn',
        };
        var BATCH_ACTION_STATUS = {
            action_name : "@lang('admin_common.change_status')",
            action_url : "{{action('Admin\Product\ProductController@changeStatusofSelectedproducts')}}",
            btn_class : 'btn',
            action_options : {
                1 : 'Active',
                0 : 'InActive',
            },
        }; 
        const JQ_GRID_TITLE = "@lang('admin_product.product_list')";        
        /*        
        *@desc : Table column configrations
            Array of column 
        */
        let columnModel = [  
            /* check for row selection ***/
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
            /**** end selection *******/
            {   title: "@lang('admin_common.actions')", 
                dataIndx:'id', 
                render : function(ui) {
                    var front_btn = '';
                    if(ui.rowData.status=='1'){
                        front_btn = '<a href="'+front_url+'/'+ui.rowData.caturl+'/'+ui.rowData.sku+'" class="btn-primary" target="_blank">@lang("admin_common.view")</a>';
                    }
                    return {
                        text:'<a href="'+edit_url+'/'+ui.cellData+'" class="btn btn-dark mb-1">@lang("admin_common.edit")</a> <a href="'+copy_url+'/'+ui.cellData+'" class="btn btn-light mb-1">@lang("admin_common.copy")</a> <a href="'+delete_url+'/'+ui.cellData+'" class="btn btn-danger mb-1" onclick="return confirm(\'@lang("admin_common.do_you_wanto_delete_this_data")\')">@lang("admin_common.delete")</a> '+front_btn,    
                    };                
                },
                sortable : !1,
                minWidth : 200,
            },
            { 
                title: "@lang('admin_product.thumbnail_image')",
                dataIndx: 'product_thumb', 
                minWidth: 100,
                render: function(ui){
                    return "<img src='"+ui.rowData.product_thumb+"' class='param-thumb'>&nbsp;";
                },
                cls : 'param-thumb-wrap',
                sortable : false,
            },
            {   title: "@lang('admin_product.product_sku')", 
                dataIndx:'sku', 
                minWidth: 140,
                filter : {
                    attr : "@lang('admin_product.product_sku')",                        
                    crules: [
                        {
                            condition: getFilter('sku', 'condition') ||  'contain',
                            value : getFilter('sku', 'value')  || "",
                        }
                    ],
                    type: 'textbox', 
                    listeners: ['change'],
                },
            },          
            {   title: "@lang('admin_product.category_name')", 
                dataIndx:'category_name', 
                minWidth: 140,
                filter : {
                    attr : "@lang('admin_product.category_name')",                        
                    crules: [
                        {
                            condition: getFilter('category_name', 'condition') ||  'contain',
                            value : getFilter('category_name', 'value')  || "",
                        }
                    ],
                    type: 'textbox', 
                    listeners: ['change'],
                },
            },
            {   title: "@lang('admin_product.badge_name')", 
                dataIndx:'badge_name', 
                minWidth: 140,
                filter : {
                    attr : "@lang('admin_common.enter_name')",                        
                    crules: [
                        {
                            condition: getFilter('badge_name', 'condition') ||  'contain',
                            value : getFilter('badge_name', 'value')  || "",
                        }
                    ],
                    type: 'textbox', 
                    listeners: ['change'],
                },
            },
            {   title: "@lang('admin_customer.name')", 
                dataIndx:'display_name', 
                minWidth: 140,
                filter : {
                    attr : "@lang('admin_common.enter_name')",                        
                    crules: [
                        {
                            condition: getFilter('display_name', 'condition') ||  'contain',
                            value : getFilter('display_name', 'value')  || "",
                        }
                    ],
                    type: 'textbox', 
                    listeners: ['change'],
                },
            },
            {   title: "@lang('admin_product.shop_name')", 
                dataIndx:'shop_name',
                minWidth: 140,
                filter : {
                    attr : "@lang('admin_common.enter_shop_name')",                        
                    crules: [
                        {
                            condition: getFilter('shop_name', 'condition') ||  'contain',
                            value : getFilter('shop_name', 'value')  || "",
                        }
                    ],
                    type: 'textbox', 
                    listeners: ['change'],
                    // conditionList: ['begin', 'contain', 'notbegin', 'notcontain'], 
                },
            },
            {   title: "@lang('admin_product.show_price')", 
                dataIndx:'show_price', 
                minWidth: 140, 
                render : function(ui){
                    return {
                        text : (ui.cellData.toString() == "1") ? "{{Lang::get('common.yes')}}" : "{{Lang::get('common.no')}}",
                    }
                },
                filter : {
                    attr: "placeholder='@lang('admin_common.please_select')'",
                    crules: [
                        {
                            condition: getFilter('show_price', 'condition') || 'range',
                            value : getFilter('show_price', 'value') || "",
                        }
                    ],                    
                    options: [ 
                        {"1": "{{Lang::get('common.yes')}}"}, 
                        {"0": "{{Lang::get('common.no')}}"},
                    ],                                           
                },
            },
            {   title: "@lang('admin_product.unit_price')", 
                    dataIndx:'unit_price', 
                    minWidth: 140,
            },
            {   title: "@lang('admin_common.status')", 
                dataIndx:'status', 
                minWidth: 140, 
                render : function(ui){
                    return {
                        text : (ui.cellData.toString() == "1") ? "{{Lang::get('common.active')}}" : "{{Lang::get('common.inactive')}}",
                    }
                },
                filter : {
                    attr: "placeholder='@lang('admin_common.please_select')'",
                    crules: [
                        {
                            condition: getFilter('status', 'condition') || 'range',
                            value : getFilter('status', 'value') || "",
                        }
                    ],                    
                    options: [ 
                        {"1": "{{Lang::get('common.active')}}"}, 
                        {"0": "{{Lang::get('common.inactive')}}"},
                    ],                                           
                },
            },
            {   title: "@lang('admin_common.created_at')", 
                dataIndx:'created_at', 
                minWidth: 140, 
                dataType: "date",
                filter: { 
                    crules :[
                        {
                            condition: getFilter('created_at', 'condition') ||  "between",
                            value : getFilter('created_at', 'value') || "",
                            value2 : getFilter('created_at', 'value2') || ""
                        }
                    ]           
                },
            },
            {   title: "@lang('admin_common.last_updated')", 
                dataIndx:'updated_at', 
                minWidth: 140,
                dataType: "date",
                filter: { 
                    crules :[
                        {
                            condition: getFilter('updated_at', 'condition') ||  "between",
                            value : getFilter('updated_at', 'value') || "",
                            value2 : getFilter('updated_at', 'value2') || ""
                        }
                    ]           
                },
            },
            
        ];    
    </script>

    <!-- end of page level js -->
    
@stop