@extends('layouts.app') 

@section('header_style')
    {!! CustomHelpers::combineCssJs(['css/dataTables.bootstrap','css/myaccount','css/bootstrap-select','css/ui-grid-unstable'],'css') !!}

@endsection

@section('header_script')
        var error_msg ={
                    txt_delete_confirm : "@lang('common.are_you_sure_to_delete_this_record')",
                    yes_delete_it : "@lang('common.yes_delete_it')",
                    txt_no : "@lang('common.no')",
                };
        var currency = "@lang('common.baht')";
        var text_ok_btn = "@lang('common.ok_btn')";
        var lang_json = {"ok":"@lang('common.ok')", "success":"@lang('common.success')"};      
        var fieldSetJson  = {!! $fielddata !!};
        var fieldset = fieldSetJson.fieldSets;
        var pagelimit = "{{action('JsonController@pageLimit')}}";
        var showSearchSection = true;
        var showHeadrePagination = true;
        var getAllDataFromServerOnce = true;
        var dataJsonUrl = "{{ action('Seller\OrderController@deliveryListData',['section'=>$section]) }}";
        var lang = ["@lang('shipping.name')","@lang('shipping.type')","@lang('shipping.status')","@lang('shipping.last_updated')","@lang('shipping.action')","@lang('shipping.edit')","@lang('shipping.remove')","@lang('shipping.id')","@lang('shipping.active')","@lang('shipping.deactive')"];
        var tableLoaderImgUrl = "{{ Config::get('constants.loader_url')}}ajax-loader.gif";
        //pagination config 
        var pagination = {!! getPagination() !!};
        var per_page_limt = {{ getPagination('limit') }};

        var confirmMessage = "@lang('common.are_u_want_to_sure_delete_this_data')";
     
        var order_mode = "{{GeneralFunctions::systemConfig('ORDER_MODE')}}";

         //find index cahnge using method
         function findIndexMethod(list, matchEle){
            var index = -1;
            for (var i = 0; i < list.length; ++i) {
              if (list[i].fieldName!== undefined && list[i].fieldName===matchEle) {
                  index = i;
                  break;
              }
            }

            return index;  
          };

        //Listen on columns setting
       _getInfo=function(fName,fType){
         var ind = findIndexMethod(fieldset, fName);
         if(ind>=0){
              var r =false;
              if(fType==='sortable'){
                r= (typeof fieldset[ind].sortable!=='undefined')? fieldset[ind].sortable:false;
              }else if(fType==='width'){
                r= (typeof fieldset[ind].width!=='undefined')? fieldset[ind].width:100;
              }else if(fType==='align'){
                 r= (typeof fieldset[ind].align!=='undefined')? 'text-'+fieldset[ind].align:'text-left';
              }
              return r;
         }else{
              if(fType==='width'){
                return 100;
              }else if(fType==='align'){
                return 'text-left';
              }else if(fType==='sortable'){
                return false;
              } 
         }
         return false;
        };
        /**** This code used for columns setting of table where field is field name of database filed.*****/
        var columsSetting = [
          
          { 
            field : 'user_name',
            displayName : '@lang('order.buyer_name')',
            cellTemplate: '<span class="product-img"><img src="<%row.entity.image_url%>" width="50" height="50" alt="" ng-hide="!row.entity.image_url"></span><%row.entity.user_name%>',
            enableSorting : _getInfo('buyer_name','sortable'),
            //width : _getInfo('buyer_name','width'),
            width : 200,
            cellClass : _getInfo('buyer_name','align'),
          },{ 
            field : 'shop_formatted_id',
            displayName : '@lang('order.order_number')',
            cellTemplate: '<a class="skyblue" href="<%row.entity.url%>"><%row.entity.shop_formatted_id%></a>',
            enableSorting : _getInfo('shop_formatted_id','sortable'),
            width : 180,
           // cellClass : _getInfo('shop_formatted_id','align'),

          },
          { 
            field : 'shipping_method_name',
            displayName : '@lang('order.shipping_method')',
            cellTooltip: true,
            enableSorting : false, //_getInfo('paid','sortable'),
            minWidth: 160,
            cellClass : _getInfo('shipping_method_name','align'),
          },{ 
            field : 'status',
            displayName : '@lang('order.order_status')',
            cellTooltip: true,
            enableSorting : false, //_getInfo('paid','sortable'),
            minWidth: 230,
            //cellClass : _getInfo('status','align'),
          }
          ,{ 
            field : 'total_final_price',
            displayName : '@lang('order.total_price')',
            cellTooltip: true,
            enableSorting : false, //_getInfo('paid','sortable'),
            //width : _getInfo('total_final_price','width'),
            minWidth: 155,
            cellClass : _getInfo('total_final_price','align'),
          }
          ,{ 
            field : 'pickup_time',
            displayName : '@lang('order.pickup_time')',
            cellTooltip: true,
            enableSorting : false, //_getInfo('paid','sortable'),
            //width : _getInfo('pickup_time','width'),
            minWidth: 280,
            cellClass : _getInfo('pickup_time','align'),
          }

          ];
@endsection

@section('content')

@if(Session::has('verify_msg'))
<div class="alert alert-success alert-dismissible">
    <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
    {{Session::get('verify_msg')}}
</div>
@endif

@if(Session::has('not_verify_msg')) 
    <div class="alert alert-danger alert-dismissable margin5"> 
        <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button> 
        {{Session::get('not_verify_msg')}} 
    </div> 
@endif

@include('includes.seller_panel_top_menu')


<div class="ng-cloak" >
    <div class="row">
        <div class="col-sm-12">
            
            <div class="row">
                <div class="col-sm-12">    
                    <div class="productbargain-block">
                        <ul class="sort-by-block nav">
                            <li><a href="{{action('Seller\OrderController@deliveryList')}}" @if($section=='prepare') class="active" @endif >@lang('order.prepaire_delivery_list')</a></li> 
                            <li><a href="{{action('Seller\OrderController@deliveryList',['section'=>'ready'])}}" @if($section=='ready') class="active" @endif>@lang('order.ready_delivery_list')</a></li>
                        </ul>
                    </div> 
                                         
                    <div class="tab-content"> 
                                
                        <div class="product-detail" ng-controller="gridtableCtrl">  
                          <div class="abt-order-status">
                              <span>@lang('order.all_pending_delivery')</span>
                              <span><span class="skyblue pr-1" id="tot_pending_ord" ng-bind="displayTotalNumItems "></span> @lang('order.orders')</span>
                              <!-- <button type="button" class="btn-dark-grey">Export CSV</button> -->
                          </div>                         
                          @include('includes.gridtable')                           
                        </div> 
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@endsection 
@section('footer_scripts') 
    @include('includes.gridtablejsdeps')
    <script stype="text/javascript" src="{{Config('constants.js_url')}}angular/smmApp/controller/gridTableCtrl.js"></script>
    
    {!! CustomHelpers::combineCssJs(['js/jquery.dataTables','js/dataTables.bootstrap'],'js') !!} 

@endsection