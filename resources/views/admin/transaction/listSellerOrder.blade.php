@extends('layouts/admin/default')

@section('title')
    @lang('admin_order.shop_order_list')
@stop

@section('header_styles')
    <link rel="stylesheet" type="text/css" href="{{Config('constants.css_url') }}flatpickr.min.css">
   {!! CustomHelpers::dataTableCss() !!}
    <script type="text/javascript">
        var filter_data = {!! $filter !!};    
    </script>
    
@stop

@section('content')
    <div class="content">
        <div class="header-title">
            <h1 class="title">@lang('admin_order.shop_order_list')</h1>
            @if($filter_date)
                <button class="btn btn-primary generate_txt" data-val="without_enc" id="without_enc">Without Encrypt</button>
                <button class="btn btn-primary generate_txt" data-val="with_enc" id="generate_txt">@lang('admin_order.generate_txt')</button>
            @endif
        </div>
             
        <!-- Main content -->         
           
        <div class="content-wrap">
            <div class="breadcrumb">
                <ul class="bredcrumb-menu">
                    {!!getBreadcrumbAdmin('shoporder', 'shoporder', 'list')!!}
                </ul>
            </div>         
            <form action="{{action('Admin\Transaction\ShopOrderController@sellerOrder')}}" method="GET" enctype="multipart/form-data">
                <div class="row">
                    <div class="col-sm-4 form-group">
                        <label>@lang('admin_order.date') <i class="red">*</i></label>
                        <input type="text" class="date-select-new date-picker" name="filter_date" id="order_date" value="{{$filter_date}}">
                        
                    </div>
                    <div class="col-sm-1 form-group">
                        <label>&nbsp;</label>
                       <button class="btn btn-primary" value="refresh" name="refresh">@lang('admin_common.submit')</button>
                    </div>
                    <div class="col-sm-1  form-group">
                        <label>&nbsp;</label>
                        <a class="btn btn-danger" href="{{Request::url()}}">@lang('admin_common.clear_all')</a>
                    </div>
                    
                </div>
                
            </form>
            <input type="hidden" name="brand_product" id="brand_product">
           <div id="jq_grid_table" class="table table-bordered">                 
                

            </div>
        </div>
    </div>
@stop

@section('footer_scripts')
    <script src="{{ Config('constants.admin_js_url') }}flatpickr.min.js"></script>
    <!-- begining of page level js -->
    <script>
        var generate_txt_url  = "{{action('Admin\Transaction\ExportOrderController@generateTxt')}}";
        var csrftoken = window.Laravel.csrfToken;
           $(document).ready(function() {
            $(".date-picker").flatpickr({});

            $('.generate_txt').click(function(e){
                var order_date = $('#order_date').val();
                var shop_ids = $('#brand_product').val();
                var eny_type = $(this).data('val');
                if(!order_date){
                    alert('Please select date');
                    return false;
                }
                if(!shop_ids){
                    alert('Please select shop');
                    return false;
                }
                window.location.href = generate_txt_url+"?order_date="+order_date+"&shop_ids="+shop_ids+"&eny_type="+eny_type;
            });
        });
    </script>
    {!! CustomHelpers::dataTableJs() !!}
    <!-- end grid table js files -->  
    <script>
        let JQ_GRID_DATA_URL = "{{ action('Admin\Transaction\ShopOrderController@listSellerOrderData') }}?filter_date={{$filter_date}}";     
        
        const JQ_GRID_TITLE = "@lang('admin_order.shop_order_list')";    
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
            
            {   title: "@lang('admin_order.shop_name')", 
                dataIndx:'shop_name', 
                minWidth: 140,
                filter : {
                    attr : "@lang('admin_order.enter_name')",                        
                    crules: [
                        {
                            condition: getFilter('shop_name', 'condition') ||  'contain',
                            value : getFilter('shop_name', 'value')  || "",
                        }
                    ],
                    type: 'textbox', 
                    listeners: ['change'],
                },
            },
            {   title: "@lang('admin_order.seller_name')", 
                dataIndx:'seller_name', 
                minWidth: 140,
                filter : {
                    attr : "@lang('admin_order.enter_name')",                        
                    crules: [
                        {
                            condition: getFilter('seller_name', 'condition') ||  'contain',
                            value : getFilter('seller_name', 'value')  || "",
                        }
                    ],
                    type: 'textbox', 
                    listeners: ['change'],
                },
            },
            {   title: "@lang('admin_order.panel_no')", 
                dataIndx:'panel_no', 
                minWidth: 140,
            },
            {   title: "@lang('admin_order.bank_name')", 
                dataIndx:'bank_name', 
                minWidth: 140,
            },
            {   title: "@lang('admin_order.grand_total')", 
                dataIndx:'amount', 
                minWidth: 100,
                align : "right",
            },
            {   title: "@lang('admin_common.actions')", 
                    dataIndx:'detail_url', 
                    minWidth: 75,
                    render : function(ui) {
                        return {
                            text:'<a href="'+ui.cellData+'" class="btn-primary">@lang("admin_common.view")</a>',    
                        };                
                    },
                    sortable : !1,
            }, 
            
        ];    
    </script>
    
@stop
