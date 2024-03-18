@extends('layouts/admin/default')

@section('title')
    @lang('admin_order.shop_order_list')
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
            <h1 class="title">@lang('admin_order.shop_order_list')</h1>
            <button class="btn btn-outline-primary" type="button" name="export_order_pdf" onclick="generateOrderPdf('export_order_pdf')">@lang('admin_common.export_order_pdf')</button>
        </div>
             
        <!-- Main content -->         
           
        <div class="content-wrap">
            <div class="breadcrumb">
                <ul class="bredcrumb-menu">
                    {!!getBreadcrumbAdmin('shoporder', 'shoporder', 'list')!!}
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
        let JQ_GRID_DATA_URL = "{{ action('Admin\Transaction\ShopOrderController@listOrderData') }}";     
        /*var BATCH_ACTION_DELETE = {
            action_name : "@lang('admin_common.delete')",
            action_handler : '',
            btn_class : 'btn',
        };
        var BATCH_ACTION_STATUS = {
            action_name : "@lang('admin_common.change_status')",
            action_handler : '',
            btn_class : 'btn',
        }; */ 
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
            {   title: "@lang('admin_order.main_order')", 
                dataIndx:'formatted_id', 
                minWidth: 140,
                filter : {
                    attr : "@lang('admin_order.formatted_id')",                        
                    crules: [
                        {
                            condition: getFilter('formatted_id', 'condition') ||  'contain',
                            value : getFilter('formatted_id', 'value')  || "",
                        }
                    ],
                    type: 'textbox', 
                    listeners: ['change'],
                },
            },
            {   title: "@lang('admin_order.pickup_time')", 
                dataIndx:'time', 
                minWidth: 160,
                align : 'center',
                filter : {
                    crules: [
                        {
                            condition: getFilter('time', 'condition') || 'range',
                            value : getFilter('time', 'value') || "",
                        }
                    ],                    
                    options: [ 
                        {"09:00": "09:00"}, 
                        {"14:00": "14:00"},
                        {"16:00": "16:00"},
                    ],                                           
                },
        
            },
            {   title: "@lang('admin_order.shop_order')", 
                dataIndx:'shop_formatted_id', 
                minWidth: 140,
                filter : {
                    attr : "@lang('admin_order.shop_formatted_id')",                        
                    crules: [
                        {
                            condition: getFilter('shop_formatted_id', 'condition') ||  'contain',
                            value : getFilter('shop_formatted_id', 'value')  || "",
                        }
                    ],
                    type: 'textbox', 
                    listeners: ['change'],
                },
            },
            /*{   title: "@lang('admin_order.seller_id')", 
                dataIndx:'seller_id', 
                minWidth: 140,
                filter : {
                    attr : "@lang('admin_order.enter_name')",                        
                    crules: [
                        {
                            condition: getFilter('seller_id', 'condition') ||  'contain',
                            value : getFilter('seller_id', 'value')  || "",
                        }
                    ],
                    type: 'textbox', 
                    listeners: ['change'],
                },
            },*/
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
            {   title: "@lang('admin_order.grand_total')", 
                dataIndx:'total_final_price', 
                minWidth: 140,
                align : "right",
                filter : {
                    attr : "@lang('admin_order.total_final_price')",                        
                    crules: [
                        {
                            condition: getFilter('total_final_price', 'condition') ||  'contain',
                            value : getFilter('total_final_price', 'value')  || "",
                        }
                    ],
                    type: 'textbox', 
                    listeners: ['change'],
                },
            },
            {   
                title: "@lang('admin_order.paid')", 
                minWidth: 60,
                align : 'center',
                render : function(ui) {                        
                    return {
                        text:'<span class="circle '+ui.rowData.payment_status+'"></span>',
                    };                
                },
            },
            {    
                dataIndx:'order_status',
                title: "@lang('admin_order.order_status')", 
                minWidth: 160,
                align : 'center',
                render : function(ui) {    
                    let ord =  {!! $ord_status !!}.filter(o=> {
                      return (Object.keys(o)[0] == ui.cellData);
                    });
                    ord = ord.length && ord[0][ui.cellData] || '';
                    return {
                        text:'<span class="'+ui.rowData.order_status_class+'">'+ord+'</span>',
                    };                
                }, 
                filter : {
                    attr: "placeholder='admin_common.please_select'",
                    crules: [
                        {
                            condition: getFilter('status', 'condition') || 'range',
                            value : getFilter('status', 'value') || "",
                        }
                    ],options: {!! $ord_status !!},                                           
                },               
            },
           
            {   title: "@lang('admin_order.end_shopping_date')", 
                dataIndx:'end_shopping_date_time', 
                minWidth: 140,
                dataType: "date",
                filter: { 
                    init: pqDatePicker,
                    crules :[
                        {
                            condition: getFilter('end_shopping_date', 'condition') ||  "between",
                            value : getFilter('end_shopping_date', 'value') || "",
                            value2 : getFilter('end_shopping_date', 'value2') || ""
                        }
                    ]           
                },
            },
			{   title: "@lang('admin_order.pickup_date')", 
                dataIndx:'pickup_time', 
                minWidth: 140,
                dataType: "date",
                filter: { 
                    init: pqDatePicker,
                    crules :[
                        {
                            condition: getFilter('pickup_time', 'condition') ||  "between",
                            value : getFilter('pickup_time', 'value') || "",
                            value2 : getFilter('pickup_time', 'value2') || ""
                        }
                    ]           
                },
            },
            {   title: "@lang('admin_order.remark')", 
                dataIndx:'admin_remark', 
                minWidth: 140,
                filter : {
                    attr : "@lang('admin_order.remark')",                        
                    crules: [
                        {
                            condition: getFilter('admin_remark', 'condition') ||  'contain',
                            value : getFilter('admin_remark', 'value')  || "",
                        }
                    ],
                    type: 'textbox', 
                    listeners: ['change'],
                },
            },
            
        ];    
    </script>
     <script>
        function getData(){
            return jqGrid.SelectRow().getSelection().map(function(rowList) {
                return rowList.rowData.formatted_order_id;
            });
        };
        function beforeExport(){
          var d = getData();
          if(d && !d.length){
            swal("@lang('admin_common.opps')", "@lang('admin_order.please_select_rows_first')", 'warning');
          }
          return d;
        };
    </script>
     <script>
        function generateOrderPdf(event, orderData){ 
            orderData = beforeExport();
            if(!orderData.length) return;
            //console.log(orderData);return;
            var total_order = orderData.toString();
            var url = "{{action('Admin\Transaction\ShopOrderController@generateOrderPdf')}}?order_list="+total_order;
            window.location.href=url;
            // $.ajax({
            //       type : 'post',
            //       url : "{{action('Admin\Transaction\OrderController@generateOrderPdf')}}",
            //       headers : {
            //           'X-CSRF-TOKEN' : window.Laravel.csrfToken,
            //           '_token' : window.Laravel.csrfToken,
            //       },
            //       beforeSend : ()=>{                        
            //          try{showHideLoaderAdmin('showLoader')}catch(er){console.log};
            //       },
            //       data : {'section':'order', 'order_list':JSON.stringify(orderData)},
            //   }).done((data)=>{
            //       if(data.status && data.status == 'error')
            //           swal('Opps..!', data.message, data.status)
            //       else{
            //         swal('Success', data.message, data.status)
            //       }
            //   })
            //   .always(()=>{
            //       try{showHideLoaderAdmin('hideLoader')}catch(er){console.log};
            //   });         
                
        };  
 
        function getData(){
            return jqGrid.SelectRow().getSelection().map(function(rowList) {
                return rowList.rowData.formatted_id;
            });
        };
        function beforeExport(){
          var d = getData();
          if(d && !d.length){
            swal("@lang('admin_common.opps')", "@lang('admin_order.please_select_rows_first')", 'warning');
          }
          return d;
        };
    </script>  
    
@stop
