/*
*@desc : This module used for handle grid table in angular
*
*/
/*
 *@desc : Listent to set old filter data 
 *@param : cn {column name} -sting 
 *@param : vt {string} -> Type of value need to fetch 
 */
/*
 *@desc : Get previous filetr if have in case of page load 
 **/
// let prevFtData = (typeof filter_data && filter_data.pq_filter) ? JSON.parse(filter_data.pq_filter)['data'] : null;

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
//check prev exits id 
function _exitsPrevId(item, id) {
    var exits = _.findIndex(item, function (o) {
        return o == id;
    });
    return exits;
};

(function(angular){

	
	/*
	*@desc : Listen to delete rows
	*@param : evt,
	*@param : ,
	*@param : ,
	*@param : ,
	*/
	function deleteSelected(evt, ui, flag) {
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
	        swal('Opss.', 'Please_select_row_first', 'warning');
	        return;
	    }
	    var api_url =  typeof BATCH_ACTION_DELETE!="undefined" ? BATCH_ACTION_DELETE.action_url : '';
	    swal({
	        title : "@lang('admin_common.are_you_sure')",
	        text : "@lang('admin_common.do_you_wanto_delete_this_data')?",
	        type : 'warning',
	        showCancelButton : !0,
	        showConfirmButton : !0,
	    }).then(res=>{
	        ajaxRequest(api_url, 'POST', {'ids' :data})
	        .then(res=>{
	            if(res.status === 'success'){
	                swal("@lang('admin_common.done')", res.mesg, 'success');
	                if(flag === 'single'){
	                    jqGrid.deleteRow({ rowIndx: ui.rowIndx });
	                }else if('multi'){
	                    jqGrid.deleteRow({
	                        rowList: jqGrid.SelectRow().getSelection()
	                    });
	                }                
	                jqGrid.refresh();
	            }else{
	                swal("@lang('admin_common.opps')", res.mesg, 'error');
	            }
	        }, err=>{
	            console.log('SERVER ERROR');
	        });
	    }, err=>{
	        console.log;
	    });
	};


	function jgGridNgBlogHandler($rootScope, uiGridConstants){
		return{
			restrict : 'EA',
			scope : {
				//
			},
			link : function(scope, elem, attrs){
				console.log('publish_date');
				//set default selected value in case of edit page (product)
		        if (typeof blog_product_id !== "undefined" && blog_product_id.length)
		            $rootScope.prd_data.blog_product_id = angular.copy(blog_product_id);
		       

		        //this variabled used for tab section in table 
		        var current_active_tab = "related_product",
		            sub_action_type = "";
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
			        url: typeof JQ_GRID_BLOG_URL != "undefined" ? JQ_GRID_BLOG_URL : "",
			        beforeSend: (jqXHR, settings) => {
			            //
			        },
			        getData: function(resp) {
			        	if($rootScope.prd_data.blog_product_id && $rootScope.prd_data.blog_product_id.length){
			        		angular.forEach(resp.data, item=>{
			        			if(_exitsPrevId($rootScope.prd_data.blog_product_id, item.id)!=-1){
			        				item.state = !0;
			        			}
			        		});
			        	}
			            return {
			                curPage: resp.current_page,
			                totalRecords: resp.total,
			                data: resp.data
			            };
			        },
			    };

			    /*****
			    *@desc : toolbar config
			    *@param : items
			        *@desc : batch action delete
			        *param : BATCH_ACTION        
			        var BATCH_ACTION_DELETE = {
			            action_name : 'delete',
			            action_handler : null,
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
			    /********** end toolbar ***********/
			    let $gridObj = $(elem).find("div#jq_grid_table").pqGrid({
			        /** width of table (100 or '100%' or 'flex')*/
			        width: '100%',
			        /** height of table (100 or '100%' or 'flex')*/
			        height: 'flex',
			        rowHtHead: typeof rowHeadHeight != "undefined" ? rowHeadHeight : 25,
			        autoRowHead : typeof rowHeadHeight != "undefined" ? false : true,
			        /** server config **/
			        dataModel: dataModel,
			        /** column config **/
			        colModel: typeof blogColumnModel != "undefined" ? blogColumnModel : [],
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
			            curPage: filter_data && parseInt(filter_data.pq_curpage) || 1,
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
			        toolbar: $_toolbar,

			        /**** event sections ********/
			        rowSelect : function(evt, ui){
			        	let row_ids = (ui['addList'].length) ? ui['addList'].map(o=> o.rowData.id) : ui['deleteList'].map(o=> o.rowData.id);	
			        	//for related product tab		      
			        	if($rootScope.prd_tab.enable_related_tab){
			        		angular.forEach(row_ids, item=>{
			        			let r_ind = _exitsPrevId($rootScope.prd_data.blog_product_id, item);
			        			if (r_ind == -1){
                            		$rootScope.prd_data.blog_product_id.push(item);
			        			}else{
			        				$rootScope.prd_data.blog_product_id.splice(r_ind, 1);
			        			}
			        		});			        		
			        	}
			        }
			        /**** end *********/
			    });

				/******* event section ********/
				$gridObj.on('pqGrid:check', function(){
					console.log('on checkbox');
				})
			},
		};
		
	};

	angular.module('sabinaAdminApp')
		.directive('jqGridNgBlog', ['$rootScope', 'uiGridConstants', jgGridNgBlogHandler]);
})(window.angular);

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
                //jqGrid.showLoading()
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
            //jqGrid.hideLoading()
        });
    });
}