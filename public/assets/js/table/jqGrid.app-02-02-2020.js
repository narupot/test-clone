/*
*@desc : jQ-Grid modules used to display data in table fromat
*@Required Param for action (define on blade page):
	@param : JQ_GRID_DATA_URL ->url of data 
	@param : JQ_GRID_COLUMN_SETTING -> colums of table 
	@param : JQ_GRID_SN_COLUMN -> columns setting for s.no
	@param : JQ_TABLE_WIDTH - FOR TABLE WIDTH
	@param : JQ_TABLE_HEIGHT ->FOR HEIGHT OF TABLE

	file include
	<!--PQ Grid files-->
    <link rel="stylesheet" href="pqgrid.min.css" />
    <link rel="stylesheet" href="pqgrid.ui.min.css" />
        <!--pqSelect-->
	<link rel="Stylesheet" href="pqselect.min.css" />   
    <script src="{{ Config('constants.admin_js_url') }}table/pqgrid.min.js"></script>
    <script src="{{ Config('constants.admin_js_url') }}table/pqselect.min.js"></script>
    <script src="{{ Config('constants.admin_js_url') }}table/jquery.resize.js"></script>
    <script src="{{ Config('constants.admin_js_url') }}table/pqtouch.min.js"></script>
*/

jQuery(document).ready(function(){
	/*$.fn.bootstrapBtn = $.fn.button.noConflict();
    $.fn.bootstrapTooltip = $.fn.tooltip.noConflict();*/
});

/*
 *@desc : Listent to set old filter data 
 *@param : cn {column name} -sting 
 *@param : vt {string} -> Type of value need to fetch 
 */
/*
 *@desc : Get previous filetr if have in case of page load 
 **/
let prevFtData = (typeof filter_data && filter_data.pq_filter) ? JSON.parse(filter_data.pq_filter)['data'] : null;

function getFilter(cn, vt) {
    let res;
    if (prevFtData) {
        prevFtData.map((o) => {
            if (o.dataIndx === cn) res = o[vt];
        });
    }
    return res;
};

function pqDatePicker(ui) {
    var $this = ui.$editor;
    $this
        //.css({ zIndex: 3, position: "relative" })
        .datepicker({
            yearRange: "-25:+0", //25 years prior to present.
            changeYear: true,
            changeMonth: true,
        });
   /* //default From date
    var $from = $this.filter(".pq-from").datepicker("option", "defaultDate", new Date("01/01/1996"));
    //default To date
    var $to = $this.filter(".pq-to").datepicker("option", "defaultDate", new Date("12/31/1998"));

    var value = changeFormat(ui.column.filter.value),
        value2 = changeFormat(ui.column.filter.value2);

    $from.val(value);
    $to.val(value2);*/
};

/*****
 *@desc : Init param Query gird for table 
 *****/
$(function() {

    /*
    *@desc : Table message object 
    	1. used for pagination 
    	2. used for serial number
    */
    let tbMsgOption = {
        'page': 'page',
        'record': 'Record per page',
        'display': 'Record',
        's_no': 'S.No',
        'reset_filter': 'Reset filters',
        'export': 'Export',
        'format': 'Format'
    };

    if (typeof table_message != "undefined") $.extend(tbMsgOption, table_message);

    let dataModel = {
        location: "remote",
        dataType: "JSON",
        method: (typeof METHOD_TYPE!="undefined") ? METHOD_TYPE : "GET",
        postData : {
            'page_type' : (typeof page_type!="undefined") ? page_type : "",
            'action_from' : (typeof ACTION_FROM!="undefined") ? ACTION_FROM : null, 
            '_token' : window.Laravel.csrfToken,
            'X-CSRF-TOKEN' : window.Laravel.csrfToken,
        },
        url: typeof JQ_GRID_DATA_URL != "undefined" ? JQ_GRID_DATA_URL : "",
        beforeSend: (jqXHR, settings) => {
            //
        },
        getData: function(resp) {
            return {
                curPage: resp.current_page,
                totalRecords: resp.total,
                data: resp.data
            };
        },
    };

    /****
    *@desc : Batch action handler 
    *****/
    // let BATCH_ACTION_CONFIG = {
    //     DELETE_BATCH : (typeof DELETE_BATCH!="undefined") ? DELETE_BATCH : !1,
    //     STATUS_BATCH : (typeof STATUS_BATCH!="undefined") ? STATUS_BATCH : !1,
    // };



    let $gridObj = $("div#jq_grid_table").pqGrid({
        /** width of table (100 or '100%' or 'flex')*/
        width: '100%',
        /** height of table (100 or '100%' or 'flex')*/
        height: 'flex',
        /** server config **/
        dataModel: dataModel,
        /** column config **/
        colModel: typeof columnModel != "undefined" ? columnModel : [],
        /*It determines the behaviour of cell content which doesn't fit in a single line within the width of the cell.*/
        wrap:true,
        /*Title of the pqGrid*/
        title : (typeof JQ_GRID_TITLE!="undefined") ? JQ_GRID_TITLE : null,
        /*
        *@desc : pagination model config
            1. rPP -> Number item display per page 
            2. rPPOptions -> Array of pagination 
        */
        pageModel: {
            type: "remote",
            /*set old data */
            rPP: filter_data && filter_data.pq_rpp || 10,
            rPPOptions: [10, 20, 50, 100],
            /*set old data */
            curPage: filter_data && filter_data.pq_curpage || 1,
            strPage: tbMsgOption.page + '{0} of {1}',
            strRpp: tbMsgOption.record + ': {0}',
            strDisplay: tbMsgOption.display + '{0}  to {1} of {2}',
        },
        /** display s.no in table **/
        numberCell: {
            title: tbMsgOption.s_no,
            width: 50,
        },
        /** for remote sorting  data send to server pq_sort: [{"dataIndx":"email","dir":"up"}]**/
        sortModel: {
            type: 'remote',
        },
        /**
        @desc: Filter model 
            1. header -> filter will show in header
            2. type -> From where data will filter (mean in table data or from server) 
        **/
        filterModel: {
            type: 'remote',
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
        /*enable menu icon in columns header for filter & show/hide*/
        // menuIcon: true,
        // menuUI: {
        //     tabs: ['hideCols']
        // },
        /*ENABLE INLINE EDIT MODE OF DATA (MEAN USER CAN EDIT DATA OF COLUMN)*/
        editable: false,
        // scrollModel:{autoFit:false},
        //
        collapsible: !1,
        /*synchronously or asynchronously*/
        postRenderInterval: -1, //call postRender synchronously.
        /*selection model*/
        // selectionModel: { type: 'cell', fireSelectChange: true },
        /*animModel:  { on: true, duration: 400 },*/
        /*custom hide show feature for coulmns */
        create: function (evt, ui) {
            var $sel = $(".columnSelector"),
                    grid = this,
                    opts = [];
                grid.getColModel().forEach(function(column){                  
                    if (column.hidden !== true) {
                        opts.push(column.dataIndx);
                    }
                });
                /*initialize the selected options corresponding to visible columns in toolbar select list.*/
                $sel.val(opts);
                /*let's disable ShipCountry column.*/
                $sel.find("option:eq(0)").prop('disabled', true);
                //convert it into pqSelect.
                $sel.pqSelect({
                    checkbox: true,
                    multiplePlaceholder: 'Select visible columns',
                    maxDisplay: 100,
                    width: 'auto'
                }).on('change', (function(){
                                    
                    //save initial value.
                    var oldVal = $sel.val() || [];

                    return function( evt ){
                        var newVal = $(this).val() || [],
                            diff, state;

                        //previously checked but unchecked now.
                        $(oldVal).not(newVal).get().forEach(function(di){
                            grid.getColumn({dataIndx: di }).hidden = true;
                        })
                                
                        //previously unchecked but checked now.
                        $(newVal).not(oldVal).get().forEach(function(di){
                            grid.getColumn({dataIndx: di }).hidden = false;
                        })

                        grid.refreshCM();
                        grid.refresh();
                                                                                                
                        // console.log("diff: ", diff, " state: ", state);
                        oldVal = newVal;
                    }                    
                })());
        },
        /*custom toolbar in header */
        toolbar: {
            items: [{
                    type: 'select',
                    label: tbMsgOption.format + ': ',
                    attr: 'id="export_format"',
                    options: [{
                        xlsx: 'Excel',
                        csv: 'Csv',
                        json: 'Json'
                    }]
                },                
                {
                    type: 'button',
                    label: tbMsgOption.export,
                    icon: 'ui-icon-arrowthickstop-1-s',
                    listener: function() {
                        var format = $("#export_format").val(),
                            blob = this.exportData({
                                //url: "/pro/demos/exportData",
                                format: format,
                                nopqdata: true, //applicable for JSON export.                        
                                render: true
                            });
                        if (typeof blob === "string") {
                            blob = new Blob([blob]);
                        }
                        saveAs(blob, "pqGrid." + format);
                    }
                },
                /*hide/show columns*/
                {
                    type: 'select',
                    cls: 'columnSelector', 
                    attr: "multiple='multiple'", style: "height:60px;",
                    options: function (ui) {
                        //options in the select list correspond to all columns.
                        return this.getColModel().map(function(column){
                            var obj = {};
                            obj[ column.dataIndx ] = column.title;
                            return obj;
                        });
                    }
                },
                {
                    type: 'button',
                    label: 'Get Row ID of selected rows',
                    listener: function() {

                        var ids = this.SelectRow().getSelection().map(function(rowList) {
                            return rowList.rowData.id;
                        })

                        // alert(ids);
                    }
                },
                // {
                //     type: 'button',
                //     label: 'Call Checkbox(dataIndx).checkNodes()',
                //     listener: function() {
                //         var grid = this;
                //         var nodes = [0, 1, 3, 7, 15].map(function(ri) {
                //             return grid.getRowData({
                //                 rowIndx: ri
                //             });
                //         })
                //         var cb = grid.Checkbox('id');
                //         cb.unCheckAll();
                //         cb.checkNodes(nodes);
                //     }
                // },
                // {
                //     type: 'button',
                //     label: 'Call updateRow()',
                //     listener: function() {
                //         var rowList = [{
                //                 rowIndx: 3,
                //                 newRow: {
                //                     state: true
                //                 }
                //             },
                //             {
                //                 rowIndx: 5,
                //                 newRow: {
                //                     state: true
                //                 }
                //             },
                //             {
                //                 rowIndx: 4,
                //                 newRow: {
                //                     state: false
                //                 }
                //             },
                //             {
                //                 rowIndx: 6,
                //                 newRow: {
                //                     state: false
                //                 }
                //             },
                //             {
                //                 rowIndx: 7,
                //                 newRow: {
                //                     state: false
                //                 }
                //             }
                //         ]
                //         this.updateRow({
                //             rowList: rowList
                //         })
                //     }
                // },
                // {
                //     type: 'button',
                //     label: 'Call SelectRow()',
                //     listener: function() {
                //         this.SelectRow().remove({
                //             rows: [{
                //                     rowIndx: 3
                //                 },
                //                 {
                //                     rowIndx: 5
                //                 },
                //             ]
                //         })
                //         this.SelectRow().add({
                //             rows: [{
                //                     rowIndx: 4
                //                 },
                //                 {
                //                     rowIndx: 6
                //                 },
                //                 {
                //                     rowIndx: 7
                //                 }
                //             ]
                //         })
                //     }
                // },
                {
                    type: 'select',
                    label: 'Sorting Type: ',
                    value: 'single', //default value.
                    options: [{
                            'single': 'Single without shift key'
                        },
                        {
                            'singlemulti': 'Single with shift key for multiple'
                        },
                        {
                            'multi': 'Multiple columns'
                        }
                    ],
                    listener: function(evt) {
                        var val = $(evt.target).val(),
                            single = true,
                            multiKey = null;

                        if (val == 'singlemulti') {
                            multiKey = 'shiftKey';
                        } else if (val == 'multi') {
                            single = false;
                        }

                        this.option("sortModel.single", single);
                        this.option("sortModel.multiKey", multiKey);
                        this.sort(); //refresh sorting.                        
                    }
                },
                {
                    type: 'button',
                    label: tbMsgOption.reset_filter,
                    listener: function() {
                        this.reset({
                            filter: true
                        });
                    }
                }
            ]
        },
    });

    //add instance in window 
    window.jqGrid = $("div#jq_grid_table").pqGrid('instance');


    /****
    *@desc : param query grid event handler
    *****/
    /***** pager event *****/
    // jqGrid.pager().on("beforeChange", function( event, ui ) {
    //     console.log('iiiiii');
    // });

    /***** filter event ******/
    // jqGrid.on('filter', function(event, ui){
    //     console.log(ui);
    // });

});


/***
*@desc : Api Request handler (ajax)
*@param : api_method {string -> get || post || detele || put}
*@param : api_url {string}
*@param : query {data}
****/
function ajaxRequest(api_url, api_method, query) {
    return new Promise((resolve, reject)=>{
        $.ajax({
            type : api_method,
            url : api_url,
            headers : {
               '_token' : window.Laravel.csrfToken,
                'X-CSRF-TOKEN' : window.Laravel.csrfToken,
            },
            beforeSend : function(){
                jqGrid.showLoading()
            },
            data : query || null,
        })
        .done((response)=>{
            resolve(response);
        })
        .fail((err)=>{
            reject(err);
        })
        .always(()=>{
            jqGrid.hideLoading()
        });
    });
}
