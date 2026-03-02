@extends('layouts/admin/default')

@section('title')
    @lang('admin_order.export_order_list')
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
            <h1 class="title">@lang('admin_order.export_order_list')</h1>
        </div>
             
        <!-- Main content -->         
           
        <div class="content-wrap">
             <div class="breadcrumb">
                <ul class="bredcrumb-menu">
                    {!!getBreadcrumbAdmin()!!}<li>@lang('admin_order.export_order_list')</li>
                </ul>
            </div>
           <div id="jq_grid_table" class="table table-bordered">                 
                

            </div>
        </div>

        <div id="status_modal" class="modal fade">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <span class="close fas fa-times" data-dismiss="modal"></span> 
                    </div>
                    <div class="modal-body">
                        <div class="search-content">
                            <input type="hidden" id="export_id" name="export_id" value="">
                            <select id="dd_status">
                                <option value="pending">@lang('admin_order.pending')</option>
                                <option value="exported">@lang('admin_order.exported')</option>
                                <option value="imported">@lang('admin_order.imported')</option>
                            </select>
                            <button type="button" id="btn_submit">@lang('admin_common.submit')</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@stop

@section('footer_scripts')

    {!! CustomHelpers::dataTableJs() !!}
    <!-- end grid table js files -->  
    <script>
        let JQ_GRID_DATA_URL = "{{ action('Admin\Transaction\ExportOrderController@listExportOrderData') }}"; 
        var BATCH_ACTION_DELETE = {
            action_name : "@lang('admin_common.delete')",
            action_handler : '',
            btn_class : 'btn',
        };
        var BATCH_ACTION_STATUS = {
            action_name : "@lang('admin_common.change_status')",
            action_handler : '',
            btn_class : 'btn',
        }; 
        const JQ_GRID_TITLE = "@lang('admin_order.order_list')";
        /*
        *@desc : Table column configrations
            Array of column 
        */
        let columnModel = [  
            /* check for row selection ***/
            { 
                dataIndx: 'state', 
                editable: true,
                cb: {header: true, select: true, all: true}, 
                dataType: 'bool',
                hidden: true
            },
            /**** end selection *******/          
            {   title: "@lang('admin_order.status')", 
                dataIndx:'status', 

                minWidth: 140,
                filter : {
                    attr : "@lang('admin_order.status')",                        
                    crules: [
                        {
                            condition: getFilter('status', 'condition') ||  'contain',
                            value : getFilter('status', 'value')  || "",
                        }
                    ],
                    type: 'textbox', 
                    listeners: ['change'],
                },
                render : function(ui) {
                    
                    return {
                        text:'<a href="javascript:;" class="status_change" data-id="'+ui.rowData.id+'" data-val="'+ui.cellData+'">'+ui.cellData+'</a>',    
                    };                
                },
            },
            {   title: "@lang('admin_order.file_name')", 
                dataIndx:'file_name', 
                minWidth: 140,
                filter : {
                    attr : "@lang('admin_order.file_name')",                        
                    crules: [
                        {
                            condition: getFilter('file_name', 'condition') ||  'contain',
                            value : getFilter('file_name', 'value')  || "",
                        }
                    ],
                    type: 'textbox', 
                    listeners: ['change'],
                },
            },
            {   title: "@lang('admin_order.total_order')", 
                dataIndx:'total_order', 
                minWidth: 140,
                filter : {
                    attr : "@lang('admin_order.total_order')",                        
                    crules: [
                        {
                            condition: getFilter('total_order', 'condition') ||  'contain',
                            value : getFilter('total_order', 'value')  || "",
                        }
                    ],
                    type: 'textbox', 
                    listeners: ['change'],
                },
            },
            {   title: "@lang('admin_order.total_seller')", 
                dataIndx:'total_seller', 
                minWidth: 140,
                align : "right",
                filter : {
                    attr : "@lang('admin_order.total_seller')",                        
                    crules: [
                        {
                            condition: getFilter('total_seller', 'condition') ||  'contain',
                            value : getFilter('total_seller', 'value')  || "",
                        }
                    ],
                    type: 'textbox', 
                    listeners: ['change'],
                },
            },
            
            {   title: "@lang('admin_order.order_date')", 
                dataIndx:'order_date', 
                minWidth: 140, 
                dataType: "date",
                filter: { 
                    init: pqDatePicker,
                    crules :[
                        {
                            condition: getFilter('order_date', 'condition') ||  "between",
                            value : getFilter('order_date', 'value') || "",
                            value2 : getFilter('order_date', 'value2') || ""
                        }
                    ]           
                },
            },
            {   title: "@lang('admin_order.created_at')", 
                dataIndx:'created_at', 
                minWidth: 140, 
                dataType: "date",
                filter: { 
                    init: pqDatePicker,
                    crules :[
                        {
                            condition: getFilter('created_at', 'condition') ||  "between",
                            value : getFilter('created_at', 'value') || "",
                            value2 : getFilter('created_at', 'value2') || ""
                        }
                    ]           
                },
            },
            
            
            {   title: "@lang('admin_common.actions')", 
                dataIndx:'dwn_url', 
                render : function(ui) {
                    return {
                        text:'<a href="'+ui.cellData+'" class="btn-primary">@lang("admin_common.download")</a>',    
                    };                
                },
                sortable : !1,
                minWidth : 150,
            },
        ];    

        $(document).on('click','.status_change',function(e){
            
            var id = $(this).data('id');
            var val = $(this).data('val');
            $('#status_modal').modal('show');
            $('#export_id').val(id);
            $('#dd_status').val(val);
        });

        $('#btn_submit').click(function(e){
            var export_id = $('#export_id').val();
            var status = $('#dd_status').val();
             $.ajax({
                url: "{{action('Admin\Transaction\ExportOrderController@changeStatus')}}",
                type: 'POST', 
                     
                data: {export_id: export_id, status : status,'_token' : window.Laravel.csrfToken},
                success:function(result){  
                    if(result.status=='success'){
                        swal('', result.msg, 'success').then(function () {
                            location.reload();
                        });
                    }else{
                         swal('error', result.msg, 'error');
                    }
                }
            });
            
        })
    </script>

    <!-- end of page level js -->
    
@stop
