@extends('layouts/admin/default')

@section('title')
    @lang('admin_customer.customer_attribute_list')
@stop

@section('header_styles')

    {!! CustomHelpers::dataTableCss() !!} 
    <style>
        .content-wrap {
            overflow: hidden;
        }
    </style>
    <script type="text/javascript">
        var filter_data = {!! $filter !!};   
    </script>
@stop

@section('content')
    <div class="content">
        <div class="header-title">
            <h1 class="title">@lang('admin_customer.customer_attribute_list')</h1>
            <a href="{{action('Admin\Customer\UserController@downloadPDF')}}">@lang('admin_customer.export_pdf')</a>
        </div>
             
        <!-- Main content -->         
           
        <div class="content-wrap">
             @if(Session::has('succMsg'))
            <div class="alert alert-success alert-dismissable margin5">
                <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
                <strong>@lang('admin_common.success'):</strong> {{ Session::get('succMsg') }}
            </div>
            @elseif(Session::has('errorMsg'))
            <div class="alert alert-danger alert-dismissable margin5">
                <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
                <strong>@lang('admin_common.error'):</strong> {{ Session::get('errorMsg') }}
            </div>    
            @endif
            <div class="breadcrumb">
                <ul class="bredcrumb-menu">
                    {!!getBreadcrumbAdmin('customer', 'customer', 'list')!!}
                </ul>
            </div>
            <div id="jq_grid_table" class="table table-bordered"></div>
        </div>
    </div>
@stop

@section('footer_scripts')

    {!! CustomHelpers::dataTableJs() !!}
    <script>
        let view_url = "{{action('Admin\Customer\UserController@index')}}";
        let JQ_GRID_DATA_URL = "{{ action('Admin\Customer\UserController@customerData') }}";
        var BATCH_ACTION_DELETE = {
            action_name : "@lang('admin_common.delete')",
            action_url : "{{action('Admin\Customer\UserController@deleteSelectedCustomers')}}",
            btn_class : 'btn',
        };
        var BATCH_ACTION_STATUS = {
            action_name : "@lang('admin_common.change_status')",
            action_url : "{{action('Admin\Customer\UserController@changeStatusofSelectedCustomer')}}",
            btn_class : 'btn',
            action_options : {
                1 : 'Active',
                0 : 'InActive',
            },
        }; 
        const JQ_GRID_TITLE = "@lang('admin_customer.customer_attribute_list')";
        /*
        *@desc : Table column configrations
            Array of column 
        */
        var status_arr = {'0':"@lang('common.inactive')","1":"@lang('common.active')","2":"@lang('common.delete')"};
        let columnModel = [  
            /* check for row selection ***/
            {   title: "", 
                width: 50, 
                dataType: "integer",
                type:'checkbox', 
                cbId: 'state',
                menuIcon : false, 
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
                minWidth: 80,
                render : function(ui) {
                    return {
                        text:'<a href="'+view_url+'/'+ui.cellData+'" class="btn-primary">@lang("admin_common.view")</a>',    
                    };                
                },
                sortable : !1,
                // menuIcon : !1,
            },      
            {   title: "@lang('admin_customer.name')", 
                dataIndx:'display_name', 
                minWidth: 140,
                // menuIcon : !0,
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
            {   title: "@lang('admin_customer.email')", 
                dataIndx:'email',
                minWidth: 140,
                // menuIcon : !1,
                filter : {
                    attr : "@lang('admin_common.enter_name_email')",                        
                    crules: [
                        {
                            condition: getFilter('email', 'condition') ||  'contain',
                            value : getFilter('email', 'value')  || "",
                        }
                    ],
                    type: 'textbox', 
                    listeners: ['change'],
                    // conditionList: ['begin', 'contain', 'notbegin', 'notcontain'], 
                },
            },
            {   title: "@lang('admin_customer.dob')", 
                dataIndx:'dob', 
                minWidth: 140,
                dataType: "date",
                filter: { 
                    crules: [
                        { 
                            condition: getFilter('dob', 'condition') ||  "between",
                            value : getFilter('dob', 'value') || "",
                            value2 : getFilter('dob', 'value2') || ""
                        }
                    ] 
                }
            },
            {   title: "@lang('admin_customer.phone_no')",
                dataIndx:'ph_number', 
                minWidth: 140,
                // menuIcon : !1,
                filter: {
                    attr : "@lang('admin_common.enter_name_phone_number')",                        
                    crules: [
                        {
                            condition: getFilter('ph_number', 'condition') || 'contain',
                            value : getFilter('ph_number', 'value') || "",
                        }
                    ],
                    type: 'textbox', 
                    listeners: ['change'],
                    // conditionList: ['begin', 'contain', 'notbegin', 'notcontain'], 
                },
            },
            {   title: "@lang('admin_customer.user_type')", 
                dataIndx:'user_type', 
                minWidth: 140,
                // menuIcon : !1,
                filter: { 
                    attr: "placeholder='@lang('admin_common.please_select')'",
                    crules: [
                        { 
                           condition: getFilter('user_type', 'condition') || 'range',
                           value : getFilter('user_type', 'value') || "",
                        }
                    ],
                    options: [ 
                        {"buyer": "{{Lang::get('common.buyer')}}"}, 
                        {"seller": "{{Lang::get('common.seller')}}"},
                    ],                        
                },
            },
            {   title: "@lang('admin_customer.register_from')", 
                dataIndx:'register_from', 
                minWidth: 140,
                // menuIcon : !1,
                filter: { 
                    attr: "placeholder='@lang('admin_common.please_select')'",
                    crules: [
                        { 
                           condition: getFilter('register_from', 'condition') || 'range',
                           value : getFilter('register_from', 'value') || "",
                        }
                    ],
                    options: [ 
                        {"admin": "{{Lang::get('common.admin')}}"}, 
                        {"website": "{{Lang::get('common.website')}}"},
                    ],                        
                },
            },
            {   title: "@lang('admin_common.status')", 
                dataIndx:'status', 
                minWidth: 140, 
                render : function(ui){
                    return {
                        
                        text : status_arr[ui.cellData.toString()],
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
                        {"2": "{{Lang::get('common.delete')}}"},
                    ],                                           
                },
            },
            {   title: "@lang('admin_customer.verified')", 
                dataIndx:'verified', 
                minWidth: 140, 
                render : function(ui){
                    return {

                        text : (ui.cellData.toString() == "1") ? "{{Lang::get('admin_customer.verified')}}" : "{{Lang::get('admin_customer.not_verified')}}",
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
                        {"1": "{{Lang::get('admin_customer.verified')}}"}, 
                        {"0": "{{Lang::get('admin_customer.not_verified')}}"},
                    ],                                           
                },
            },
            {   title: "@lang('admin_common.created_at')", 
                dataIndx:'created_at_dt', 
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
                dataIndx:'updated_at_dt', 
                minWidth: 140,
                dataType: "date",
                // menuIcon : !0,
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
