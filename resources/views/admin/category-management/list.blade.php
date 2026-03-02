@extends('layouts/admin/default')

@section('title')
    @lang('cms.category_name')
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
            <h1 class="title">@lang('cms.category_name')</h1>
            <div class="float-right">
                <a class="btn btn-success" href="{{ action('Admin\CategoryManagement\CategoryController@create') }}"> @lang('common.create_new_cat')</a>
            </div>
        </div>
        @if(Session::has('succMsg'))    
            <script type="text/javascript">               
                _toastrMessage('success', "{{ Session::get('succMsg') }}");    
            </script>                            
        @elseif(Session::has('errorMsg'))
            <script type="text/javascript"> 
                _toastrMessage('error', "{{ Session::get('errorMsg') }}");
            </script>  
        @endif       
        <!-- Main content -->         
           
        <div class="content-wrap">
            <div class="breadcrumb">
                <ul class="bredcrumb-menu">
                {!!getBreadcrumbAdmin('category','category','list')!!}
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
		let page_type = "category";
        let edit_url = "{{ route('admin.category.parent-category-edit', ['id' => ':id']) }}";
        let delete_url = "{{ route('admin.category.parent-category-delete', ['id' => ':id']) }}";
        let JQ_GRID_DATA_URL = "{{ action('Admin\CategoryManagement\CategoryController@categoryListData') }}"; 
		JQ_GRID_DATA_URL += '?page_type='+page_type;
        const JQ_GRID_TITLE = "@lang('cms.category_list')";

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
        { 
            title: "@lang('admin_common.actions')", 
            dataIndx:'id', 
            render : function(ui) {
                let editLink = edit_url.replace(':id', ui.cellData);
                let deleteLink = delete_url.replace(':id', ui.cellData);
                return {
                text: `
            <div class="d-flex justify-content-center"> 
                <a href="${editLink}" class="btn btn-dark mx-1">@lang("admin_common.edit")</a>
                <a href="${deleteLink}" class="btn btn-danger mx-1" onclick="return confirm('@lang("admin_common.do_you_wanto_delete_this_data")')">@lang("admin_common.delete")</a>
            </div>`
                };
            },
            sortable : !1,
            minWidth : 150,
            align: 'center',
        },
        { 
            title: "@lang('cms.image')",
            dataIndx: 'category_image', 
            minWidth: 100,
            render: function(ui){
                return "<img src='"+ui.rowData.category_image+"' class='param-thumb'>&nbsp;";
            },
            cls : 'param-thumb-wrap',
            sortable : false,
        },
        {   title: "@lang('cms.category_name')", 
            dataIndx:'category_name', 
            minWidth: 140,
            filter : {
                attr : "@lang('cms.category_name')",
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
        {   title: "@lang('cms.url')", 
            dataIndx:'url', 
            minWidth: 140,
            filter : {
                attr : "@lang('cms.url')",
                crules: [
                    {
                        condition: getFilter('url', 'condition') ||  'contain',
                        value : getFilter('url', 'value')  || "",
                    }
                ],
                type: 'textbox', 
                listeners: ['change'],
            },
        },
        {   title: "@lang('cms.sorting_no')", 
            dataIndx:'sorting_no', 
            minWidth: 140,
            align: 'center', 
            filter : {
                attr : "@lang('cms.sorting_no')",
                crules: [
                    {
                        condition: getFilter('sorting_no', 'condition') ||  'contain',
                        value : getFilter('sorting_no', 'value')  || "",
                    }
                ],
                type: 'textbox', 
                listeners: ['change'],
            },
        },
        {   title: "@lang('cms.subgroup_name')", 
            dataIndx:'subgroup_name', 
            minWidth: 140,

            filter : {
                attr : "@lang('cms.subgroup_name')",
                crules: [
                    {
                        condition: getFilter('subgroup_name', 'condition') ||  'contain',
                        value : getFilter('subgroup_name', 'value')  || "",
                    }
                ],
                type: 'textbox', 
                listeners: ['change'],
            },
        },
        {   title: "@lang('cms.group_name')", 
            dataIndx:'group_name', 
            minWidth: 140,
            filter : {
                attr : "@lang('cms.group_name')",
                crules: [
                    {
                        condition: getFilter('group_name', 'condition') ||  'contain',
                        value : getFilter('group_name', 'value')  || "",
                    }
                ],
                type: 'textbox', 
                listeners: ['change'],
            },
        },
        {   title: "@lang('cms.status')", 
            dataIndx:'is_deleted', 
            minWidth: 140,
            align: 'center', 
            render : function(ui){
                    let is_deleted = ui.cellData != null ? ui.cellData.toString() : '0';
                    return {
                        text : (is_deleted === "0") ? "{{Lang::get('common.active')}}" : "{{Lang::get('common.inactive')}}",
                    };
                },
            filter : {
                attr: "placeholder='@lang('admin_common.please_select')'",
                crules: [
                    {
                        condition: getFilter('is_deleted', 'condition') || 'range',
                        value : getFilter('is_deleted', 'value') || "",
                    }
                ],
                options: [ 
                    {"1": "{{Lang::get('common.active')}}"}, 
                    {"0": "{{Lang::get('common.inactive')}}"},
                ],
            },
        },
        {   title: "@lang('cms.created_by')", 
            dataIndx:'nick_name', 
            minWidth: 140,
            align: 'center',
            filter : {
                attr : "@lang('cms.created_by')",
                crules: [
                    {
                        condition: getFilter('nick_name', 'condition') ||  'contain',
                        value : getFilter('nick_name', 'value')  || "",
                    }
                ],
                type: 'textbox', 
                listeners: ['change'],
            },
        },
        {   
            title: "@lang('common.created_at')", 
            dataIndx: 'created_at', 
            minWidth: 140,
            align: 'center',
            dataType: "date",
            render: function(ui) {
                return formatThaiDate(ui.cellData);
            },
            filter: { 
                crules: [
                    {
                        condition: getFilter('created_at', 'condition') || "between",
                        value: getFilter('created_at', 'value') || "",
                        value2: getFilter('created_at', 'value2') || ""
                    }
                ]
            },
        },
        {
            title: "@lang('common.last_updated')",
            dataIndx: 'updated_at', 
            minWidth: 140,
            align: 'center',
            dataType: "date",
            render: function(ui) {
                return formatThaiDate(ui.cellData);
            },
            filter: { 
                crules: [
                    {
                        condition: getFilter('updated_at', 'condition') || "between",
                        value: getFilter('updated_at', 'value') || "",
                        value2: getFilter('updated_at', 'value2') || ""
                    }
                ]
            },
        },
    ];  
   
    </script>
    <script>
        function formatThaiDate(dateStr) {
            if (!dateStr) return '';

            const date = new Date(dateStr);
            if (isNaN(date)) return dateStr;

            const monthsThai = [
                'มกราคม', 'กุมภาพันธ์', 'มีนาคม', 'เมษายน', 'พฤษภาคม', 'มิถุนายน',
                'กรกฎาคม', 'สิงหาคม', 'กันยายน', 'ตุลาคม', 'พฤศจิกายน', 'ธันวาคม'
            ];

            const day = date.getDate().toString().padStart(2, '0');
            const month = monthsThai[date.getMonth()];
            const year = date.getFullYear() + 543;
            return `${day} ${month} ${year}`;
        }
    </script>
    <!-- end of page level js -->
    
@stop
