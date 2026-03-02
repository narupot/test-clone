@extends('layouts/admin/default')

@section('title')
@lang('admin_order.order_list')
@stop

@section('header_styles')
{!! CustomHelpers::dataTableCss() !!}

<style>
    .loading-txt {
        color: #000;
    }
</style>
<script type="text/javascript">
    var filter_data = {!! $filter !!};    
</script>

@stop

@section('content')
<div class="content">
    <div class="header-title">
        <h1 class="title">@lang('admin_order.order_list')</h1>
        <button class="btn btn-outline-primary" type="button" name="export_order_pdf"
            onclick="generateOrderPdf('export_order_pdf')">@lang('admin_common.export_order_pdf')</button>
    </div>

    <!-- Main content -->

    <div class="content-wrap">
        <div class="breadcrumb">
            <ul class="bredcrumb-menu">
                {!!getBreadcrumbAdmin()!!}
                <li>@lang('admin_order.order_list')</li>
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
    let JQ_GRID_DATA_URL = "{{ action('Admin\Transaction\OrderController@listOrderData') }}";
        /*var BATCH_ACTION_DELETE = {
            action_name : "@lang('admin_common.delete')",
    action_handler: '',
        btn_class : 'btn',
        };
    var BATCH_ACTION_STATUS = {
        action_name: "@lang('admin_common.change_status')",
        action_handler: '',
        btn_class: 'btn',
    }; */
    const JQ_GRID_TITLE = "@lang('admin_order.order_list')";
    /*
    *@desc : Table column configrations
        Array of column 
    */
    let columnModel = [
        /* check for row selection ***/
        {
            title: "",
            width: 50,
            dataType: "integer",
            type: 'checkbox',
            cbId: 'state',
            sortable: false,
            align: 'center',
        },
        {
            dataIndx: 'state',
            editable: true,
            cb: { header: true, select: true, all: true },
            dataType: 'bool',
            hidden: true
        },
        /**** end selection *******/
        {
            title: "@lang('admin_common.actions')",
            dataIndx: 'detail_url',
            render: function (ui) {
                return {
                    text: '<a href="' + ui.cellData + '" class="btn-primary">@lang("admin_common.view")</a>',
                };
            },
            minWidth: 100,
            align: 'center',
        },
        {
            title: "@lang('admin_order.main_order')",
            dataIndx: 'formatted_id',
            minWidth: 160,
            align: 'center',
            filter: {
                attr: "@lang('admin_order.formatted_id')",
                crules: [
                    {
                        condition: getFilter('formatted_id', 'condition') || 'contain',
                        value: getFilter('formatted_id', 'value') || "",
                    }
                ],
                type: 'textbox',
                listeners: ['change'],
            },
        },
        {
            title: "User ID",
            dataIndx: 'user_id',
            minWidth: 100,
            align: 'center',
            filter: {
                attr: "User ID",
                crules: [
                    {
                        condition: getFilter('user_id', 'condition') || 'contain',
                        value: getFilter('user_id', 'value') || "",
                    }
                ],
                type: 'textbox',
                listeners: ['change'],
            },
        },
        {
            title: "@lang('admin_order.bill_to_name')",
            dataIndx: 'user_name',
            minWidth: 160,
            align: 'center',
            filter: {
                attr: "@lang('admin_order.enter_name')",
                crules: [
                    {
                        condition: getFilter('user_name', 'condition') || 'contain',
                        value: getFilter('user_name', 'value') || "",
                    }
                ],
                type: 'textbox',
                listeners: ['change'],
            },
        },
        {
            title: "@lang('admin_order.total_core_cost')",
            dataIndx: 'total_core_cost',
            minWidth: 160,
            align: 'center',
            filter: {
                attr: "@lang('admin_order.total_core_cost')",
                crules: [
                    {
                        condition: getFilter('total_core_cost', 'condition') || 'contain',
                        value: getFilter('total_core_cost', 'value') || "",
                    }
                ],
                type: 'textbox',
                listeners: ['change'],
            },
        },
        {
            title: "@lang('admin_order.discount_code')",
            dataIndx: 'discount_code',
            minWidth: 160,
            align: 'center',
            filter: {
                attr: "@lang('admin_order.discount_code')",
                crules: [
                    {
                        condition: getFilter('discount_code', 'condition') || 'contain',
                        value: getFilter('discount_code', 'value') || "",
                    }
                ],
                type: 'textbox',
                listeners: ['change'],
            },
        },

        {
            title: "@lang('admin_order.dcc_purchase_discount')",
            dataIndx: 'dcc_purchase_discount',
            minWidth: 160,
            align: 'center',
            filter: {
                attr: "@lang('admin_order.dcc_purchase_discount')",
                crules: [
                    {
                        condition: getFilter('dcc_purchase_discount', 'condition') || 'contain',
                        value: getFilter('dcc_purchase_discount', 'value') || "",
                    }
                ],
                type: 'textbox',
                listeners: ['change'],
            },
        },


        {
            title: "@lang('admin_order.delivery_fee')",
            dataIndx: 'delivery_fee',
            minWidth: 120,
            align: 'center',
            filter: {
                attr: "@lang('admin_order.delivery_fee')",
                crules: [
                    {
                        condition: getFilter('delivery_fee', 'condition') || 'contain',
                        value: getFilter('delivery_fee', 'value') || "",
                    }
                ],
                type: 'textbox',
                listeners: ['change'],
            },
        },
        {
            title: "@lang('admin_order.dcc_shipping_discount')",
            dataIndx: 'dcc_shipping_discount',
            minWidth: 160,
            align: 'center',
            filter: {
                attr: "@lang('admin_order.dcc_shipping_discount')",
                crules: [
                    {
                        condition: getFilter('dcc_shipping_discount', 'condition') || 'contain',
                        value: getFilter('dcc_shipping_discount', 'value') || "",
                    }
                ],
                type: 'textbox',
                listeners: ['change'],
            },
        },
        {
            title: "@lang('admin_order.transaction_fee')",
            dataIndx: 'transaction_fee',
            minWidth: 100,
            align: 'center',
            filter: {
                attr: "@lang('admin_order.transaction_fee')",
                crules: [
                    {
                        condition: getFilter('transaction_fee', 'condition') || 'contain',
                        value: getFilter('transaction_fee', 'value') || "",
                    }
                ],
                type: 'textbox',
                listeners: ['change'],
            },
        },
        {
            title: "@lang('admin_order.grand_total')",
            dataIndx: 'total_final_price',
            minWidth: 100,
            align: 'center',
            filter: {
                attr: "@lang('admin_order.total_final_price')",
                crules: [
                    {
                        condition: getFilter('total_final_price', 'condition') || 'contain',
                        value: getFilter('total_final_price', 'value') || "",
                    }
                ],
                type: 'textbox',
                listeners: ['change'],
            },
        },
        {
            title: "@lang('admin_order.payment_slug')",
            dataIndx: 'payment_slug',
            minWidth: 100,
            align: 'center',
            render: function (ui) {
                var paymentSlug = ui.cellData;
                var displayText = paymentSlug;
                if (paymentSlug) {
                    const paymentMap = {
                        'beam-qr': 'QR พร้อมเพย์',
                        'beam-credit': 'บัตรเครดิต',
                        'beam-banking': 'Mobile Banking',
                        'beam-ewallet': 'E-Wallet',
                        'credit_acc1': 'ลูกค้าเครดิต 1 วัน',
                        'credit_acc': 'ลูกค้าเครดิต',
                        'credit_acc7': 'ลูกค้าเครดิต 7 วัน',
                        'direct_transfer': 'โอนตรง',
                        'Credit_Acc': 'ลูกค้าเครดิต',
                        'Credit_Acc1': 'ลูกค้าเครดิต 1 วัน',
                        'Credit_Acc7': 'ลูกค้าเครดิต 7 วัน',
                        'Direct_Transfer': 'โอนตรง',
                        'Credit Card': 'บัตรเครดิต',

                    };

                    displayText = paymentMap[paymentSlug] || paymentSlug.replace(/_/g, ' ');
                }

                return {
                    text: displayText
                };
            },
            filter: {
                attr: "@lang('admin_order.payment_slug')",
                crules: [
                    {
                        condition: getFilter('payment_slug', 'condition') || 'range',
                        value: getFilter('payment_slug', 'value') || "",
                    }
                ],
                type: 'select',
                options: [

                    ["QR พร้อมเพย์"],
                    ["บัตรเครดิต"],
                    ["Mobile Banking"],
                    ["E-Wallet"],
                    ["ลูกค้าเครดิต 1 วัน"],
                    ["ลูกค้าเครดิต 7 วัน"],
                    ["โอนตรง"]
                    // ["beam-banking", "Mobile Banking"],
                    // ["beam-qr", "QR พร้อมเพย์"]
                ],
                // ลบ valueIndx และ labelIndx ออก
                listeners: ['change'],
            },

        },
        {
            title: "@lang('admin_order.paid')",
            minWidth: 60,
            dataIndx: 'payment_status',
            align: 'center',
            render: function (ui) {
                return {
                    text: '<span class="circle ' + ui.rowData.payment_status + '"></span>',
                };
            },
        },
        {
            dataIndx: 'order_status',
            title: "@lang('admin_order.order_status')",
            minWidth: 160,
            align: 'center',
            render: function (ui) {
                let ord = {!! $ord_status !!}.filter(o => {
                    return (Object.keys(o)[0] == ui.cellData);
                });
                ord = ord.length && ord[0][ui.cellData] || '';
                return {
                    text: '<span class="' + ui.rowData.order_status_class + '">' + ord + '</span>',
                };
            },
            filter: {
                attr: "placeholder='admin_common.please_select'",
                crules: [
                    {
                        condition: getFilter('order_status', 'condition') || 'range',
                        value: getFilter('order_status', 'value') || "",
                    }
                ], options: {!! $ord_status !!},
            },
        },
        {
            title: "@lang('admin_order.end_shopping_date_time')",
            dataIndx: 'end_shopping_date_time',
            minWidth: 160,
            align: 'center',
            dataType: "date",
            filter: {
                init: pqDatePicker,
                crules: [
                    {
                        condition: getFilter('end_shopping_date_time', 'condition') || "between",
                        value: getFilter('end_shopping_date_time', 'value') || "",
                        value2: getFilter('end_shopping_date_time', 'value2') || ""
                    }
                ]
            },
        },
        {
            title: "@lang('admin_order.shipping_method')",
            dataIndx: 'shipping_method',
            minWidth: 160,
            align: 'center',
            filter: {
                attr: "@lang('admin_order.shipping_method')",
                crules: [
                    {
                        condition: getFilter('shipping_method', 'condition') || 'range',
                        value: getFilter('shipping_method', 'value') || "",
                    }
                ],
                options: {!! $shipping_method !!},
            },
        },

        {
            title: "@lang('admin_order.pickup_date_time')",
            dataIndx: 'pickup_time',
            minWidth: 160,
            align: 'center',
            filter: {
                init: pqDatePicker,
                crules: [
                    {
                        condition: getFilter('pickup_time', 'condition') || "between",
                        value: getFilter('pickup_time', 'value') || "",
                        value2: getFilter('pickup_time', 'value2') || ""
                    }
                ]
            },

        },
        {
            title: "@lang('admin_order.pickup_time')",
            dataIndx: 'time',
            minWidth: 160,
            align: 'center',
            filter: {
                crules: [
                    {
                        condition: getFilter('time', 'condition') || 'range',
                        value: getFilter('time', 'value') || "",
                    }
                ],
                options: [
                    { "04:00": "04:00" },
                    { "06:00": "06:00" },
                    { "09:00": "09:00" },
                    { "13:00": "13:00" },
                ],
            },

        },
        {
            title: "@lang('admin_order.total_weight')",
            dataIndx: 'total_weight',
            minWidth: 100,
            align: 'center',
            filter: {
                attr: "@lang('admin_order.total_weight')",
                crules: [
                    {
                        condition: getFilter('total_weight', 'condition') || 'contain',
                        value: getFilter('total_weight', 'value') || "",
                    }
                ],
                type: 'textbox',
                listeners: ['change'],
            },

        },
        {
            title: "@lang('admin_order.remark')",
            dataIndx: 'admin_remark',
            minWidth: 160,
            align: 'center',
            filter: {
                attr: "@lang('admin_order.remark')",
                crules: [
                    {
                        condition: getFilter('admin_remark', 'condition') || 'contain',
                        value: getFilter('admin_remark', 'value') || "",
                    }
                ],
                type: 'textbox',
                listeners: ['change'],
            },
        },
        {
            title: "@lang('admin_order.updated_by')",
            dataIndx: 'updated_by',
            minWidth: 160,
            align: 'center',
            filter: {
                attr: "@lang('admin_order.updated_by')",
                crules: [
                    {
                        condition: getFilter('updated_by', 'condition') || 'contain',
                        value: getFilter('updated_by', 'value') || "",
                    }
                ],
                type: 'textbox',
                listeners: ['change'],
            },
        },
    ];    
</script>
<script>
    function getData() {
        return jqGrid.SelectRow().getSelection().map(function (rowList) {
            return rowList.rowData.formatted_order_id;
        });
    };
    function beforeExport() {
        var d = getData();
        if (d && !d.length) {
            swal("@lang('admin_common.opps')", "@lang('admin_order.please_select_rows_first')", 'warning');
        }
        return d;
    };
</script>
<script>
    function generateOrderPdf(event, orderData) {
        orderData = beforeExport();
        if (!orderData.length) return;
        $("#showHideLoader").removeClass("d-none");
        setTimeout(function () {
            $("#showHideLoader").addClass("d-none");
        }, 10000);
        var total_order = orderData.toString();
        var url = "{{action('Admin\Transaction\OrderController@generateOrderPdf')}}?order_list=" + total_order;
        window.location.href = url;
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

    function getData() {
        return jqGrid.SelectRow().getSelection().map(function (rowList) {
            return rowList.rowData.formatted_id;
        });
    };
    function beforeExport() {
        var d = getData();
        if (d && !d.length) {
            swal("@lang('admin_common.opps')", "@lang('admin_order.please_select_rows_first')", 'warning');
        }
        return d;
    };
</script>
<!-- end of page level js -->

@stop