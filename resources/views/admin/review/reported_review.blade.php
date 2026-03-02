@extends('layouts/admin/default')

@section('title')
    @lang('admin_product.product_list')
@stop

@section('header_styles')

   {!! CustomHelpers::dataTableCss() !!}
    <script type="text/javascript">
        var filter_data = {!! $filter !!};  
        var page_type="reported_review";
    </script>
    
@stop

@section('content')
    <div class="content">
        <div class="header-title">
            <h1 class="title">@lang('review.reported_reviews')</h1>
        </div>
             
        <!-- Main content -->         
           
        <div class="content-wrap">
            <div class="breadcrumb">
                <ul class="bredcrumb-menu">
                    {!!getBreadcrumbAdmin('user_rating_list','user_rating','list')!!}
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
       
        let delete_url = "{{action('Admin\Review\ReviewController@destroy')}}";
        // let front_url = "{{action('ProductDetailController@display')}}";
        let JQ_GRID_DATA_URL = "{{ action('Admin\Review\ReviewController@getProductReviews') }}";
         let JQ_GRID_TITLE = "@lang('review.reported_reviews')"       
        /*
        *@desc : Table column configrations
            Array of column 
        */
        let columnModel = [  
            /* check for row selection ***/
            // { 
            //     title: "ID", 
            //     width: 100, 
            //     dataType: "integer",
            //     // dataIndx: "id", 
            //     type:'checkbox', 
            //     cbId: 'state',
            //     menuIcon : false, 
            //     sortable : false
            // },
            { 
                dataIndx: 'order_shop_id', 
                cb: {header: true, select: true, all: true}, 
                dataType: 'bool',
                hidden: true
            },
            {   title: "@lang('admin_product.order_id')", 
                dataIndx:'order_shop_id', 
                minWidth: 140,
                menuIcon : !0,
                filter : {
                    attr : "@lang('admin_product.product_sku')",                        
                    crules: [
                        {
                            condition: getFilter('sku', 'condition') ||  'contain',
                            value : getFilter('sku', 'value')  || "",
                        }
                    ],
                    type: 'textbox', 
                    listeners: ['change'],
                },
            }, 
            /**** end selection *******/
            {   title: "@lang('admin_product.product_sku')", 
                dataIndx:'sku', 
                minWidth: 140,
                menuIcon : !0,
                filter : {
                    attr : "@lang('admin_product.product_sku')",                        
                    crules: [
                        {
                            condition: getFilter('sku', 'condition') ||  'contain',
                            value : getFilter('sku', 'value')  || "",
                        }
                    ],
                    type: 'textbox', 
                    listeners: ['change'],
                },
            }, 
            {   title: "@lang('admin_product.product_name')", 
                dataIndx:'product_name', 
                minWidth: 140,
                menuIcon : !0,
                sortable : false
                // filter : {
                //     attr : "@lang('admin_product.category_name')",                        
                //     crules: [
                //         {
                //             condition: getFilter('category_name', 'condition') ||  'contain',
                //             value : getFilter('category_name', 'value')  || "",
                //         }
                //     ],
                //     type: 'textbox', 
                //     listeners: ['change'],
                // },
            },         
            {   title: "@lang('admin_product.quantity')", 
                dataIndx:'quantity', 
                minWidth: 140,
                menuIcon : !0,
                // filter : {
                //     attr : "@lang('admin_product.category_name')",                        
                //     crules: [
                //         {
                //             condition: getFilter('category_name', 'condition') ||  'contain',
                //             value : getFilter('category_name', 'value')  || "",
                //         }
                //     ],
                //     type: 'textbox', 
                //     listeners: ['change'],
                // },
            },
            {   title: "@lang('admin_product.total_price')", 
                dataIndx:'total_price', 
                minWidth: 140,
                menuIcon : !0,
                // filter : {
                //     attr : "@lang('admin_common.enter_name')",                        
                //     crules: [
                //         {
                //             condition: getFilter('badge_name', 'condition') ||  'contain',
                //             value : getFilter('badge_name', 'value')  || "",
                //         }
                //     ],
                //     type: 'textbox', 
                //     listeners: ['change'],
                // },
            },
            {   title: "@lang('admin_customer.buyer_name')", 
                dataIndx:'buyer_name', 
                minWidth: 140,
                menuIcon : !0,
                
            },
            {   title: "@lang('admin_product.seller_name')", 
                dataIndx:'seller_name',
                minWidth: 140,
                menuIcon : !2,
                
            },
            {   title: "@lang('admin_product.shop_name')", 
                dataIndx:'shop_name',
                minWidth: 140,
                menuIcon : !2,
                
            },
            
            {   title: "@lang('admin_product.review')", 
                    dataIndx:'review', 
                    minWidth: 140,
                    menuIcon : !1,

            },
            {   title: "@lang('admin_product.review_star')", 
                    dataIndx:'rating', 
                    minWidth: 140,
                    menuIcon : !1,
                    render : function(ui) {
                        if(ui.rowData.rating>0){
                            return {
                                text:'<div class="review-star"><div class="grey-stars"></div><div class="filled-stars" style="width: '+ui.rowData.rating*20+'%"></div></div>',
                            };                
                        }
                    },
                    sortable : !1,
                    menuIcon : !1,


            },
            {   title: "@lang('admin_review.seller_mesg')", 
                    dataIndx:'seller_mesg', 
                    minWidth: 140,
                    menuIcon : !1,

            },
            {   title: "@lang('admin_review.seller_attachment')", 
                    dataIndx:'seller_attachment', 
                    minWidth: 140,
                    menuIcon : !1,
                    render : function(ui) {
                        
                        if(ui.rowData.seller_attachment){
                            return {
                            text:'<a href="'+ui.rowData.seller_attachment+'" target="_blank">@lang("admin_common.file")</a>',    
                            };    
                        }
                                        
                    },


            },
            {   title: "@lang('admin_common.status')", 
                dataIndx:'status', 
                minWidth: 140, 
                // render : function(ui){
                //     return {
                //         text : (ui.cellData.toString() == "1") ? "{{Lang::get('common.active')}}" : "{{Lang::get('common.inactive')}}",
                //     }
                // },
                // filter : {
                //     attr: "placeholder='@lang('admin_common.please_select')'",
                //     crules: [
                //         {
                //             condition: getFilter('status', 'condition') || 'range',
                //             value : getFilter('status', 'value') || "",
                //         }
                //     ],                    
                //     options: [ 
                //         {"all": "{{Lang::get('common.all')}}"}, 
                //         {"pending": "{{Lang::get('common.pending')}}"},
                //         {"completed": "{{Lang::get('common.completed')}}"},
                //         {"deleted": "{{Lang::get('common.deleted')}}"},
                //     ],                                           
                // },
            },
            {   title: "@lang('admin_common.created_at')", 
                dataIndx:'created_at', 
                minWidth: 140, 
                dataType: "date",
                // filter: { 
                //     crules :[
                //         {
                //             condition: getFilter('created_at', 'condition') ||  "between",
                //             value : getFilter('created_at', 'value') || "",
                //             value2 : getFilter('created_at', 'value2') || ""
                //         }
                //     ]           
                // },
            },
            // {   title: "@lang('admin_common.last_updated')", 
            //     dataIndx:'updated_at', 
            //     minWidth: 140,
            //     dataType: "date",
            //     // menuIcon : !0,
            //     filter: { 
            //         crules :[
            //             {
            //                 condition: getFilter('updated_at', 'condition') ||  "between",
            //                 value : getFilter('updated_at', 'value') || "",
            //                 value2 : getFilter('updated_at', 'value2') || ""
            //             }
            //         ]           
            //     },
            // },
            {   
                title: "@lang('admin_common.actions')", 
                    dataIndx:'id', 
                     minWidth: 100,
                    render : function(ui) { 
                        var front_btn = '';
                        if(ui.rowData.rating>0 && ui.rowData.is_deleted!='1' && typeof ui.rowData.seller_mesg == 'string'){
                            return {
                            text:'<a href="'+delete_url+'/'+ui.rowData.review_id+'" class="btn btn-danger" onclick="return confirm(\'@lang("admin_common.do_you_wanto_delete_this_data")\')">@lang("admin_common.delete")</a>'+front_btn,    
                            };    
                        }else{
                            return {text: ''};
                        }
                                        
                    },
                    sortable : !1,
                    menuIcon : !1,
            },
        ];  
    </script>

    <!-- end of page level js -->
    
@stop
