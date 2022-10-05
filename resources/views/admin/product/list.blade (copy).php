@extends('layouts/admin/default')

@section('title')
    @lang('admin_product.product_list')
@stop

@section('header_styles')
    <link rel="stylesheet" href="{{ Config('constants.admin_css_url') }}table/pqgrid.min.css"/>
    <link rel="stylesheet" href="{{ Config('constants.admin_css_url') }}table/pqgrid.ui.min.css"/>
    <link rel="stylesheet" href="{{ Config('constants.admin_css_url') }}table/pqgrid.css"/>
    <link rel="stylesheet" href="{{ Config('constants.admin_css_url') }}table/pqselect.min.css"/>
@stop

@section('content')
    <div class="content">
        <div class="header-title">
            <h1 class="title">@lang('admin_product.product_list')</h1>
        </div>
             
        <!-- Main content -->         
           
        <div class="content-wrap">
             {{--@if(Session::has('succMsg'))
            <div class="alert alert-success alert-dismissable margin5">
                <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
                <strong>@lang('admin_common.success'):</strong> {{ Session::get('succMsg') }}
            </div>
            @elseif(Session::has('errorMsg'))
            <div class="alert alert-danger alert-dismissable margin5">
                <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
                <strong>@lang('admin_common.error'):</strong> {{ Session::get('errorMsg') }}
            </div>    
            @endif--}}
            <div id="jq_grid_table" class="table table-bordered">
                


            </div>
        </div>
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

        //var $ = jQuery.noConflict();
        //$.fn.bootstrapBtn = $.fn.button.noConflict();
        //$.fn.bootstrapTooltip = $.fn.tooltip.noConflict(); 
        $(function jQGridTable($){ 

            let edit_url = "{{action('Admin\Product\ProductController@edit')}}";
            let copy_url = "{{action('Admin\Product\ProductController@copy')}}";
            let delete_url = "{{action('Admin\Product\ProductController@destroy')}}";
            let dataModel = {
                location: "remote",
                dataType: "JSON",
                method: "GET",
                url: "{{ action('Admin\Product\ProductController@productListData') }}",
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
                 { 
                    title: "ID", 
                    width: 100, 
                    dataType: "integer",  
                    type:'checkbox', 
                    cbId: 'state' , 
                    menuIcon : !1
                },
                //hidden column to store checkbox states.
                { 
                    dataIndx: 'state', 
                    cb: {header: true, select: true, all: true}, 
                    dataType: 'bool',
                    hidden: true
                },          
                {   title: "@lang('admin_product.category_name')", 
                    dataIndx:'category_name', 
                    minWidth: 140,
                    menuIcon : !1,
                    filter : {
                        attr : "@lang('admin_product.category_name')",                        
                        crules: [{condition: 'contain'}],
                        type: 'textbox', 
                        listeners: ['keyup'],
                        // conditionList: ['begin', 'contain', 'notbegin', 'notcontain'], 
                    },
                },
                {   title: "@lang('admin_product.badge_name')", 
                    dataIndx:'badge_name',
                    minWidth: 140,
                    menuIcon : !1,
                    filter : {
                        attr : "@lang('admin_product.enter_badge_name')",                        
                        crules: [{condition: 'contain'}],
                        type: 'textbox', 
                        listeners: ['keyup'],
                        // conditionList: ['begin', 'contain', 'notbegin', 'notcontain'], 
                    },
                },
                {   title: "@lang('admin_product.show_price')", 
                    dataIndx:'show_price', 
                    minWidth: 140,
                    menuIcon : !1,
                    render : function(ui){
                        return {
                            text : (ui.cellData.toString() == "1") ? "{{Lang::get('common.yes')}}" : "{{Lang::get('common.no')}}",
                        }
                    },
                    filter : {
                        attr: "placeholder='@lang('admin_common.please_select')'",
                        crules: [{condition: 'range'}],
                        conditionList: ['contain', 'range'],                       
                        options: [ 
                            {"1": "{{Lang::get('common.yes')}}"}, 
                            {"0": "{{Lang::get('common.no')}}"},                             
                        ],
                    
                    },  

                },
                {   title: "@lang('admin_product.unit_price')", 
                    dataIndx:'unit_price', 
                    minWidth: 140,
                    menuIcon : !1,
                },
                {   title: "@lang('admin_product.stock')", 
                    dataIndx:'stock', 
                    minWidth: 140,
                    menuIcon : !1,
                    render : function(ui){
                        return {
                            text : (ui.cellData.toString() == "1") ? "{{Lang::get('common.unlimited')}}" : "{{Lang::get('common.limited')}}",
                        }
                    },
                    filter : {
                        attr: "placeholder='@lang('admin_common.please_select')'",
                        crules: [{condition: 'range'}],
                        conditionList: ['contain', 'range'],                       
                        options: [ 
                            {"1": "{{Lang::get('common.unlimited')}}"}, 
                            {"0": "{{Lang::get('common.limited')}}"},                             
                        ],
                    
                    },
                },

                {   title: "@lang('admin_common.status')", 
                    dataIndx:'status', 
                    minWidth: 140, 
                    menuIcon : !1,
                    render : function(ui){
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
                {   title: "@lang('admin_common.created_at')", 
                    dataIndx:'created_at', 
                    minWidth: 140, 
                    menuIcon : !1,
                    dataType: "date",
                    filter: { 
                        crules :[{condition: "between"}]           
                    },
                },
                {   title: "@lang('admin_common.last_updated')", 
                    dataIndx:'updated_at', 
                    minWidth: 140,
                    menuIcon : !1,
                    dataType: "date",
                    // menuIcon : !0,
                    filter: { 
                        crules :[{condition: "between"}]           
                    },
                },
                {   title: "@lang('admin_common.actions')", 
                    dataIndx:'id', 
                    render : function(ui) {
                        return {
                            text:'<a href="'+edit_url+'/'+ui.cellData+'" class="btn-primary">@lang("admin_common.edit")</a><a href="'+copy_url+'/'+ui.cellData+'" class="btn-primary">@lang("admin_common.copy")</a><a href="'+delete_url+'/'+ui.cellData+'" class="btn-primary" onclick="return confirm(\'@lang("admin_common.do_you_wanto_delete_this_data")\')">@lang("admin_common.delete")</a>',    
                        };                
                    },
                    sortable : !1,
                    menuIcon : !1,
                },
            ];

            /*
            *@desc : Init table 
            */
            //$("div#jq_grid_table").pqGrid
            let $gridObj = pq.grid("div#jq_grid_table", {
                /** width of table (100 or '100%' or 'flex')*/
                width : '100%',
                /** height of table (100 or '100%' or 'flex')*/
                height : 'flex',
                /** server config **/
                dataModel: dataModel,
                /** column config **/
                colModel : columnModel,
                /*
                *@desc : pagination model config
                    1. rPP -> Number item display per page 
                    2. rPPOptions -> Array of pagination 
                */
                pageModel: { 
                    type: "remote", 
                    //set old data 
                    //rPP: filter_data && filter_data.pq_rpp || 10,                    
                    rPPOptions: [10, 20, 50, 100],
                    //set old data 
                    //curPage : filter_data && filter_data.pq_curpage || 1,
                    strPage : "@lang('admin_common.pagination_page'){0} of {1}",
                    strRpp: "@lang('admin_common.pagination_records_per_page'): {0}",
                    strDisplay: "@lang('admin_common.pagination_displaying'){0}  to {1} of {2}", 
                },
                /** display s.no in table **/
                numberCell: {
                     title: "@lang('admin_common.sno')",
                     width : 50
                },
                /** for remote sorting  data send to server pq_sort: [{"dataIndx":"email","dir":"up"}]**/
                sortModel: { 
                    type : 'remote',
                },
                /**
                @desc: Filter model 
                    1. header -> filter will show in header
                    2. type -> From where data will filter (mean in table data or from server) 
                **/
                filterModel: { 
                    type : 'remote',
                    on: true, 
                    mode: "AND", 
                    header: true,
                    // menuIcon: true,                    
                    // gridOptions: {
                    //     numberCell: {show: false},
                    //     width: 'flex',
                    //     flex: {one: true}
                    // } 
                },
                menuIcon: true,
                menuUI:{
                    tabs: ['hideCols']
                },
                //ENABLE INLINE EDIT MODE OF DATA (MEAN USER CAN EDIT DATA OF COLUMN)
                editable: false,              
                // scrollModel:{autoFit:false},
                //
                collapsible : !1,  
                /*custom toolbar in header */
                toolbar : {
                    items: [
                        {
                            type: 'select',
                            label: 'Format: ',                
                            attr: 'id="export_format"',
                            options: [{ xlsx: 'Excel', csv: 'Csv', json: 'Json'}]
                        },
                        {
                            type: 'button',
                            label: "Export",
                            icon: 'ui-icon-arrowthickstop-1-s',
                            listener: function () {
                                var format = $("#export_format").val(),                            
                                    blob = this.exportData({
                                        //url: "/pro/demos/exportData",
                                        format: format,        
                                        nopqdata: true, //applicable for JSON export.                        
                                        render: true
                                    });
                                if(typeof blob === "string"){                            
                                    blob = new Blob([blob]);
                                }
                                saveAs(blob, "pqGrid."+ format );
                            }
                        },
                        /*{
                            type: 'button',
                            label: 'Get Row ID of selected rows',
                            listener: function () {
                                                        
                                var ids = this.SelectRow().getSelection().map(function(rowList){                            
                                    return rowList.rowData.id;
                                })
                                                                                                        
                                alert(ids);
                            }                    
                        },
                        {
                            type: 'button',
                            label: 'Call Checkbox(dataIndx).checkNodes()',
                            listener: function () {
                                var grid = this;
                                var nodes = [0,1,3,7,15].map(function( ri ){
                                    return grid.getRowData({rowIndx: ri});
                                })
                                var cb = grid.Checkbox('id');
                                cb.unCheckAll();
                                cb.checkNodes( nodes );
                            }                    
                        },
                        {
                            type: 'button',
                            label: 'Call updateRow()',
                            listener: function () {
                                var rowList = [
                                    { rowIndx: 3, newRow: { state: true }},
                                    { rowIndx: 5, newRow: { state: true }},
                                    { rowIndx: 4, newRow: { state: false }},
                                    { rowIndx: 6, newRow: { state: false }},
                                    { rowIndx: 7, newRow: { state: false }}
                                ]
                                this.updateRow({rowList: rowList})                                                
                            }                    
                        },
                        {
                            type: 'button',
                            label: 'Call SelectRow()',
                            listener: function () {
                                this.SelectRow().remove({
                                    rows: [
                                        {rowIndx: 3},
                                        {rowIndx: 5},                                
                                    ]
                                })                                                
                                this.SelectRow().add({
                                    rows: [
                                        {rowIndx: 4},
                                        {rowIndx: 6},
                                        {rowIndx: 7}
                                    ]
                                })                                                
                            }                    
                        },
                        {
                            type: 'select',
                            label: 'Sorting Type:',
                            value: 'single', //default value.
                            options: [
                                {'single': 'Single without shift key'},
                                {'singlemulti': 'Single with shift key for multiple'},
                                {'multi': 'Multiple columns'}
                            ],
                            listener: function(evt){                            
                                var val = $(evt.target).val(),
                                    single = true,
                                    multiKey = null;

                                if(val == 'singlemulti'){                                
                                    multiKey = 'shiftKey';
                                }
                                else if(val == 'multi'){
                                    single = false;
                                }    

                                this.option("sortModel.single", single);
                                this.option("sortModel.multiKey", multiKey);
                                this.sort(); //refresh sorting.                        
                            }
                        }*/
                    ]
                },
            });

            //load. 
            // $gridObj.one("before", function (evt, ui) {
            //     console.log('-----------on load---------', $gridObj);
            //     // grid.getColumn({ dataIndx: "ShipRegion" }).filter.options 
            //     //     = grid.getData({ dataIndx: ["ShipCountry", "ShipRegion"] });

            //     // grid.getColumn({ dataIndx: "ShipVia" }).filter.options 
            //     //     = grid.getData({ dataIndx: ["ShipVia"] });

            //     //and apply initial filtering.
            //     $gridObj.filter({
            //         oper: 'add',
            //         rules:[
            //                 {
            //                 "dataIndx":"display_name",
            //                 "dataType":"string",
            //                 "value":"sonu",
            //                 "condition":"contain"
            //                 },
            //                 {
            //                 "dataIndx":"email",
            //                 "dataType":"string",
            //                 "value":"admin@sabina.com",
            //                 "condition":"contain"
            //                 }
            //         ]
            //     });
            // });
        });
    </script>
    <!-- end of page level js -->
    
@stop
