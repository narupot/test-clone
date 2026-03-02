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
    'format': 'Format',
    'done' : 'Done',
    'opps' : 'Opss',
    'delete_title' : 'are u sure',
    'delete_msg' : 'you want to remove',
    'select_row' : 'please select row first',
    'status_msg' : 'you want to chnage status',
    'to' : "to",
    'of' : "of",
};

if (typeof table_message != "undefined") $.extend(tbMsgOption, table_message);

/*
 *@desc : Listent to set old filter data 
 *@param : cn {column name} -sting 
 *@param : vt {string} -> Type of value need to fetch 
 */
/*
 *@desc : Get previous filetr if have in case of page load 
 **/
let prevFtData = (typeof filter_data!="undefined" && filter_data.pq_filter) ? JSON.parse(filter_data.pq_filter)['data'] : null;

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

/*
*@desc : Listen to delete rows
*@param : evt,
*@param : ,
*@param : ,
*@param : ,
*/
function deleteSelected(evt, ui, flag, delete_url, method_type) {
    var data;
    switch(flag){
        case 'single':
            data =  ui.rowData.id;
            break;
        case 'multi':
            data = jqGrid.SelectRow().getSelection().map(function(rowList) {
                return rowList.rowData.id;
            });
            break;
        default:
            break;
    };
    if((!data && flag == 'single') || (!data.length && flag == 'multi')){
        swal(tbMsgOption.opps, tbMsgOption.select_row, 'warning');
        return;
    }
    var api_url =  typeof BATCH_ACTION_DELETE!="undefined" ? BATCH_ACTION_DELETE.action_url : delete_url;
    swal({
        title : tbMsgOption.delete_title,
        text : tbMsgOption.delete_msg,
        type : 'warning',
        showCancelButton : !0,
        showConfirmButton : !0,
        reverseButtons : true,
    }).then(res=>{
        ajaxRequest(api_url, method_type || 'POST', {'ids' :data, '_method' : 'delete'})
        .then(res=>{
            if(res.status === 'success'){
                swal(tbMsgOption.done, res.msg, 'success');
                if(flag === 'single'){
                    jqGrid.deleteRow({ rowIndx: ui.rowIndx });
                }else if('multi'){
                    jqGrid.deleteRow({
                        rowList: jqGrid.SelectRow().getSelection()
                    });
                }                
                jqGrid.refresh();
            }else{
                swal(tbMsgOption.opps, res.msg, 'error');
            }
        }, err=>{
            swal('Opss..', 'something went wrong please try again', 'error')
            console.log('SERVER ERROR');
        });
    }, err=>{
        console.log;
    });
};

/*
*@desc : change status Batch or single
*
**/

function changeSelectedStatus(evt, ui, flag, change_status_url, method_type) {
    var data;
    switch(flag){
        case 'single':
            data =  ui.rowData.id;
            break;
        case 'multi':
            data = jqGrid.SelectRow().getSelection().map(function(rowList) {
                return rowList.rowData.id;
            });
            break;
        default:
            break;
    };
    if((!data && flag == 'single') || (!data.length && flag == 'multi')){
        swal(tbMsgOption.opps, tbMsgOption.select_row, 'warning');
        return;
    }
    let status;
    //in case of multi show popup 
    try{
        if(flag == 'multi'){
            swal({
                title: BATCH_ACTION_STATUS.action_name,
                input: 'select',
                inputOptions: BATCH_ACTION_STATUS.action_options,
                inputPlaceholder: 'Select a status',
                showCancelButton: true,
                inputValidator : (value)=>{
                    if(value) return Promise.resolve(value);
                    else return Promise.reject();
                },
                reverseButtons : true,
            }).then(res=>{
                status = res;
                chnageStatus();
            }, err=>{
                console.log;
            });
        }else{
          chnageStatus();  
        }
    }catch(err){
        console.log;
    }

    function chnageStatus(){
        var api_url =  typeof BATCH_ACTION_STATUS!="undefined" ? BATCH_ACTION_STATUS.action_url : change_status_url;
        swal({
            title : tbMsgOption.delete_title,
            text : tbMsgOption.status_msg,
            type : 'warning',
            showCancelButton : !0,
            showConfirmButton : !0,
        }).then(res=>{
            ajaxRequest(api_url, method_type || 'POST', {'ids' :data, 'status' : status})
            .then(res=>{
                if(res.status === 'success'){
                    swal(tbMsgOption.done, res.msg, 'success');                    
                    if(flag === 'single'){
                        jqGrid.updateRow( {
                            rowIndx: ui.rowData.rowIndx,
                            newRow: { 'status': res.change_status}
                        });
                        jqGrid.refreshDataAndView();
                    }else if('multi'){
                        window.location.reload(true);
                        // jqGrid.deleteRow({
                        //     rowList: jqGrid.SelectRow().getSelection()
                        // });
                    }
                }else{
                    swal(tbMsgOption.opps, res.msg, 'error');
                }
            }, err=>{
                swal('Opss..', 'something went wrong please try again', 'error')
                console.log('SERVER ERROR');
            });
        }, err=>{
            console.log;
        });
    };
};

function batchPrdUpdate(evt, ui, api_url){
    let data = jqGrid.SelectRow().getSelection().map(function(rowList) {
        return rowList.rowData.id;
    });
    if(!data.length){
        swal(tbMsgOption.opps, tbMsgOption.select_row, 'warning');
        return;
    }
    window.location = api_url+'?pid='+data.join(',')
}

/*****
 *@desc : Init param Query gird for table 
 *****/
$(function() {

    let assigned_product_ids = [];
    let dataModel = {
        location: "remote",
        dataType: "JSON",
        method: (typeof METHOD_TYPE!="undefined") ? METHOD_TYPE : "GET",
        recIndx: "id",
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
            //console.log(resp);
            if(resp.assigned_product_ids) {
                assigned_product_ids = resp.assigned_product_ids;
                $("#assigned_product_ids").val(assigned_product_ids.join());
            }
            return {
                curPage: resp.current_page,
                totalRecords: resp.total,
                data: assignGridRowHeight(resp.data, 'set_height_in_data')/*resp.data*/
            };
        },
    };

    //enable custom row height of table 
    function assignGridRowHeight(grid, flag){
        switch(flag){
            case 'set_height_in_data':
                //SET SELECTION
                if(typeof JQ_GRID_OLD_SELECTED_ID!="undefined" && JQ_GRID_OLD_SELECTED_ID.length){
                    grid.forEach(item=>{
                        if(_exitsPrevId(JQ_GRID_OLD_SELECTED_ID, item.id)!=-1){
                            item.state = !0;
                        }
                    });
                }
                if(typeof CUSTOM_ROW_HEIGHT!="undefined" && CUSTOM_ROW_HEIGHT.row_height){
                    grid.forEach(item=>{
                        item['pq_ht'] = parseInt(CUSTOM_ROW_HEIGHT.row_height) || 28;
                    });
                    return grid;
                }else{
                    return grid;
                }
                break;
            case 'row_height_flag' :
                if(typeof CUSTOM_ROW_HEIGHT!="undefined"){
                    return false;
                }else{
                    return true;
                }
                break;
            default :
                break; 
        };
    };

    /*****
    *@desc : toolbar config
    *@param : items
        *@desc : batch action delete
        *param : BATCH_ACTION        
        var BATCH_ACTION_DELETE = {
            action_name : 'delete',
            action_url : null,
            btn_class : 'btn',
        };
    *****/
    let $_toolbar = {       
        items: [
            {
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
            // {
            //     type: 'button',
            //     label: 'Get Row ID of selected rows',
            //     listener: function() {

            //         var ids = this.SelectRow().getSelection().map(function(rowList) {
            //             return rowList.rowData.id;
            //         })

            //         alert(ids);
            //     }
            // },
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
            // {
            //     type: 'select',
            //     label: 'Sorting Type: ',
            //     value: 'single', //default value.
            //     options: [{
            //             'single': 'Single without shift key'
            //         },
            //         {
            //             'singlemulti': 'Single with shift key for multiple'
            //         },
            //         {
            //             'multi': 'Multiple columns'
            //         }
            //     ],
            //     listener: function(evt) {
            //         var val = $(evt.target).val(),
            //             single = true,
            //             multiKey = null;

            //         if (val == 'singlemulti') {
            //             multiKey = 'shiftKey';
            //         } else if (val == 'multi') {
            //             single = false;
            //         }

            //         this.option("sortModel.single", single);
            //         this.option("sortModel.multiKey", multiKey);
            //         this.sort(); //refresh sorting.                        
            //     }
            // },
            {
                type: 'button',
                label: tbMsgOption.reset_filter,
                listener: function() {
                    this.reset({
                        filter: true
                    });
                }
            },
            /*{
                type: 'button',
                label: 'Delete', 
                icon: 'ui-icon-minus', 
                cls : 'btn-danger',
                listener: function (evt) {
                    if(typeof deletehandler!="undefined") deletehandler(evt);
                }
            },*/
        ],
    };
    if(typeof BATCH_ACTION_DELETE!="undefined"){
        $_toolbar.items.push({
            type : 'button',
            label : BATCH_ACTION_DELETE.action_name,
            cls : BATCH_ACTION_DELETE.btn_class,
            listener : function(evt, ui){
                deleteSelected(evt, ui, 'multi');
            },
        });
    }
    if(typeof BATCH_ACTION_STATUS!="undefined"){
        $_toolbar.items.push({
            type : 'button',
            label : BATCH_ACTION_STATUS.action_name,
            cls : BATCH_ACTION_STATUS.btn_class,
            listener : function(evt, ui){
                changeSelectedStatus(evt, ui, 'multi');
            },
        });
    }
    if(typeof BATCH_ACTION_ACCEPT_REJECT!="undefined"){
        $_toolbar.items.push({
            type : 'select',
            label:  BATCH_ACTION_ACCEPT_REJECT.action_name,
            value: null, //default value.
            options : BATCH_ACTION_ACCEPT_REJECT.action_options,
            listener : function(evt, ui){
                acceptReject(ui, $(evt.target).val(), 'multi');
            },            
        });
    }

    if(typeof BATCH_ACTION_PRD_UPDATE!="undefined"){
        $_toolbar.items.push({
            type : 'button',
            label:  BATCH_ACTION_PRD_UPDATE.action_name,
            cls : BATCH_ACTION_PRD_UPDATE.btn_class,
            listener : function(evt, ui){
                batchPrdUpdate(evt, ui, BATCH_ACTION_PRD_UPDATE.action_url)
            },            
        });
    }


    if(typeof ADD_CUSTOM_TOOL!="undefined"){
        let tool = ADD_CUSTOM_TOOL.concat($_toolbar.items);
        $_toolbar.items = tool/*$_toolbar.items.concat(ADD_CUSTOM_TOOL)*/;
    };
    if(typeof BATCH_SAVE_CHANGE!="undefined"){
        $_toolbar.items.push(
            { type: 'button', icon: 'ui-icon-disk', label: 'Save Changes', cls: 'batch_action_btn changes', listener: function () {
                    saveChanges();
                },
                options: { disabled: true }
            },
            { type: 'button', icon: 'ui-icon-cancel', label: 'Reject Changes', cls: 'batch_action_btn changes', listener: function () {
                    jqGrid.rollback();
                    jqGrid.history({ method: 'resetUndo' });                        
                },
                options: { disabled: true }
            },
            { type: 'button', icon: 'ui-icon-arrowreturn-1-s', label: 'Undo', cls: 'batch_action_btn changes undo', listener: function () {
                    jqGrid.history({ method: 'undo' });
                },
                options: { disabled: true }
            },
            { type: 'button', icon: 'ui-icon-arrowrefresh-1-s', label: 'Redo', cls: 'redo', listener: function () {
                    jqGrid.history({ method: 'redo' });                        
                },
                options: { disabled: true }
            }
        );
    };
    if(typeof BATCH_SAVE_BLOG!="undefined"){

        $_toolbar.items.push(
            { type: 'button', icon: 'ui-icon-disk', label: 'Save Changes', cls: 'btn-primary', listener: function () {
                    saveChanges();
                }
            });
    }
    if(typeof BATCH_SAVE_BRAND!="undefined"){
        $_toolbar.items.push(
            { type: 'button', icon: 'ui-icon-disk', label: 'Save Changes', cls: 'btn-primary', listener: function () {
                    saveChanges();
                }
            });
    }
    //enable custom row height of table 
    function assignHeightRows(grid, flag){
        switch(flag){
            case 'set_height_in_data':
                if(typeof CUSTOM_ROW_HEIGHT!="undefined" && CUSTOM_ROW_HEIGHT.row_height){
                    grid.forEach(item=>{
                        item['pq_ht'] = parseInt(CUSTOM_ROW_HEIGHT.row_height) || 28;
                    });
                    return grid;
                }else{
                    return grid;
                }
                break;
            case 'row_height_flag' :
                if(typeof CUSTOM_ROW_HEIGHT!="undefined"){
                    return false;
                }else{
                    return true;
                }
                break;
            default :
                break; 
                console.log(flag);
        };
    };

    /********** end toolbar ***********/

    let $gridObj = $("div#jq_grid_table").pqGrid({
        /** width of table (100 or '100%' or 'flex')*/
        width: '100%',
        /** height of table (100 or '100%' or 'flex')*/
        height: 'flex',
        rowHtHead: typeof rowHeadHeight != "undefined" ? rowHeadHeight : 25,
        autoRowHead : typeof rowHeadHeight != "undefined" ? false : true,
        /*** set row height ****/
        autoRow : assignGridRowHeight([], 'row_height_flag'),
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
            rPP: typeof filter_data!="undefined" && filter_data.pq_rpp || 10,
            rPPOptions: [10, 20, 50, 100, 200, 500, 1000],
            /*set old data */
            curPage: typeof filter_data!="undefined" && parseInt(filter_data.pq_curpage) || 1,
            strPage: tbMsgOption.page + '{0} '+tbMsgOption.of+' {1}',
            strRpp: tbMsgOption.record + ': {0}',
            strDisplay: tbMsgOption.display + ' {0}  '+tbMsgOption.to+' {1} '+tbMsgOption.of+' {2}',
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
        editable: (typeof JQ_EDIT_SETTING!="undefined" && JQ_EDIT_SETTING.editable) ? JQ_EDIT_SETTING.editable : false,
        //track model 
        trackModel: { on: true/*(typeof JQ_EDIT_SETTING!="undefined" && JQ_EDIT_SETTING.track_model)? JQ_EDIT_SETTING.track_model : false*/ }, //to turn on the track changes.       
        scrollModel:{autoFit:(typeof SCROLL_Model!="undefined") ? SCROLL_Model : false},
        //
        collapsible: !1,
        /*synchronously or asynchronously*/
        postRenderInterval: -1, //call postRender synchronously.
        /*selection model*/
        // selectionModel: { type: 'cell', fireSelectChange: true },
        /*animModel:  { on: true, duration: 400 },*/
        /*custom hide show feature for coulmns */
        create: function (evt, ui) {
            this.widget().pqTooltip();
            //console.log(this.widget());
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
        toolbar: $_toolbar,
        history: function(evt, ui){
            var $tb = this.toolbar();
            // if (ui.canUndo != null) {
            //     $(".batch_action_btn").attr('disabled', false);
            //     //$("button.changes", $tb).button("option", { disabled: !ui.canUndo });
            // }
            if (ui.canUndo != null) {
                $(".batch_action_btn").attr('disabled', !ui.canUndo);
                $("button.changes", $tb).button("option", { disabled: !ui.canUndo });
            }
            if (ui.canRedo != null) {
                $(".redo").attr('disabled', !ui.canRedo);
            }
            $(".undo").html('Undo (' + ui.num_undo + ')');
            $(".redo").html('Redo (' + ui.num_redo + ')');
            // $("button:contains('Undo')", $tb).button("option", { label: 'Undo (' + ui.num_undo + ')' });
            // $("button:contains('Redo')", $tb).button("option", { label: 'Redo (' + ui.num_redo + ')' });
        },
        //Listen to row selection
        rowSelect : function(evt, ui){
            let row_ids = (ui['addList'].length) ? ui['addList'].map(o=> o.rowData.id) : ui['deleteList'].map(o=> o.rowData.id);    
            //console.log(evt, ui);
            //for related product tab              
            // if($rootScope.prd_tab.enable_related_tab){
            //     angular.forEach(row_ids, item=>{
            //         let r_ind = _exitsPrevId($rootScope.prd_data.related_product_id_id, item);
            //         if (r_ind == -1){
            //             $rootScope.prd_data.related_product_id_id.push(item);
            //         }else{
            //             $rootScope.prd_data.related_product_id_id.splice(r_ind, 1);
            //         }
            //     });                            
            // }

            //In case of brand page edit page (for product selection) 
            if($('#brand_product').length){
                let brv = $('#brand_product').val() && $('#brand_product').val().split(',') || [];
                row_ids.forEach(item=>{
                    let r_ind = _exitsPrevId(brv, item);
                    if(r_ind == -1) brv.push(item);
                    else brv.splice(r_ind, 1);
                });
                $('#brand_product').val(brv.join(','));
            }
        },
        cellClick: function(evt, ui) {
            if(ui.column.type == 'checkbox'){
                if(ui.rowData.selected == false) {
                    assigned_product_ids.push(ui.rowData.id);
                } else {
                    assigned_product_ids.splice( assigned_product_ids.indexOf(ui.rowData.id), 1 );
                }
                $("#assigned_product_ids").val(assigned_product_ids.join());
            }
        }
    });
    //add instance in window 
    window.jqGrid = $("div#jq_grid_table").pqGrid('instance');

    //insert element after pq-toolbar in table
    if(jQuery('.pq-toolbar .pq-select-button').length){
      jQuery('.pq-toolbar .pq-select-button').after('<div class="w-100"></div>');  
    }
});

//check prev exits id 
function _exitsPrevId(item, id) {
    var exits = item.findIndex(function (o) {
        return o == id;
    });
    return exits;
};

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
