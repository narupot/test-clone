@extends('layouts/admin/default')

@section('title')
    @lang('cms.category_type_name')
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
            <h1 class="title">@lang('cms.category_type_name')</h1>
            <div class="float-right">
                <a class="btn btn-primary" href="{{ action('Admin\CategoryManagement\CategoryController@subcreate') }}"> @lang('cms.add_category_type')</a>
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
		let page_type = "sub_category";
        let edit_url = "{{action('Admin\CategoryManagement\CategoryController@index')}}";
        let delete_url = "{{action('Admin\CategoryManagement\CategoryController@deletecategory')}}";
        let JQ_GRID_DATA_URL = "{{ action('Admin\CategoryManagement\CategoryController@categoryTypeListData') }}";
		JQ_GRID_DATA_URL += '?page_type='+page_type;
        const JQ_GRID_TITLE = "@lang('category.list')";
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
                    return {
                        text:'<a href="'+edit_url+'/'+ui.cellData+'/edit" class="btn btn-dark mb-1">@lang("admin_common.edit")</a> <a href="'+delete_url+'/'+ui.cellData+'" class="btn btn-danger mb-1" onclick="return confirm(\'@lang("admin_common.do_you_wanto_delete_this_data")\')">@lang("admin_common.delete")</a> ',    
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
			{   title: "@lang('cms.category_type_name')",
                dataIndx:'category_name',
                minWidth: 140,
                filter : {
                    attr : "@lang('cms.category_type_name')",
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
			{   title: "@lang('cms.parent_category')",
                dataIndx:'parent_name',
                minWidth: 140,
                filter : {
                    attr : "@lang('cms.parent_category')",
                    crules: [
                        {
                            condition: getFilter('parent_name', 'condition') ||  'contain',
                            value : getFilter('parent_name', 'value')  || "",
                        }
                    ],
                    type: 'textbox',
                    listeners: ['change'],
                },
            },
            {   title: "@lang('cms.sub_group')",
                dataIndx:'subgroup_name',
                minWidth: 140,
                filter : {
                    attr : "@lang('cms.sub_group')",
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
                dataIndx:'status',
                minWidth: 140,
                render: function (ui) {
                    let val = ui.cellData;
                    if (val === undefined || val === null) return { text: '-' };

                    return {
                        text: (val == "1") 
                            ? "{{ Lang::get('common.active') }}"
                            : "{{ Lang::get('common.inactive') }}"
                    };
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
			{   title: "@lang('cms.created_by')",
                dataIndx:'nick_name',
                minWidth: 140,
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
            {   title: "@lang('cms.created_at')",
                dataIndx:'created_at',
                minWidth: 140,
                dataType: "date",
                render: function(ui) {
                return formatThaiDate(ui.cellData);
                },
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
            {   title: "@lang('cms.last_updated')",
                dataIndx:'updated_at',
                minWidth: 140,
                dataType: "date",
                render: function(ui) {
                return formatThaiDate(ui.cellData);
                },
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
