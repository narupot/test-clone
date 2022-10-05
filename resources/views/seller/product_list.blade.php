@extends('layouts.app') 

@section('header_style')
    {!! CustomHelpers::combineCssJs(['css/myaccount','css/bootstrap-select','css/ui-grid-unstable'],'css') !!}


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
        var dataJsonUrl = "{{ action('Seller\ProductController@getProductlist') }}";
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
          /*{
           field : 'id',
           displayName : '@lang('common.sno')',
           cellTemplate : '<span><%grid.appScope.seqNumber(row)+1%></span>',
           enableSorting : _getInfo('son','sortable'),
           //width : _getInfo('sno','width'),
           minWidth: 250,
           cellClass : _getInfo('sno','align'),
          },*/
          { 
            field : 'category_name',
            displayName : '@lang('product.name')',
            cellTemplate: '<span class="product-img"><img src="<%row.entity.productimg%>" width="50" height="50" alt="" ng-hide="!row.entity.productimg"></span><%row.entity.category_name%>',
            enableSorting : _getInfo('category_name','sortable'),
            //width : _getInfo('category_name','width'),
            width : 250,
            cellClass : _getInfo('category_name','align'),
          },{ 
            field : 'badgeimage',
            displayName : '@lang('product.product_standard')',
            cellTemplate: '<span class="sa"><img src="<%row.entity.badgeimage%>" width="34" height="34" alt=""></span>',
            enableSorting : _getInfo('product_standard','sortable'),
            width : _getInfo('product_standard','width'),
           // cellClass : _getInfo('product_standard','align'),

          },{ 
            field : 'quantity',
            displayName : '@lang('product.stock')',
            cellTooltip: true,
            enableSorting : false, //_getInfo('paid','sortable'),
            width : _getInfo('quantity','width'),
            cellClass : _getInfo('quantity','align'),
          },{ 
            field : 'unit_price',
            displayName : '@lang('product.unit_price')',
            cellTooltip: true,
            enableSorting : false, //_getInfo('paid','sortable'),
            width : _getInfo('unit_price','width'),
            //cellClass : _getInfo('unit_price','align'),
          }
          ,{ 
            field : 'unit_name',
            displayName : '@lang('product.unit_name')',
            cellTooltip: true,
            enableSorting : false, //_getInfo('paid','sortable'),
            //width : _getInfo('unit_name','width'),
            minWidth: 100,
            cellClass : _getInfo('unit_name','align'),
          }
          ,{ 
            field : 'status',
            displayName : '@lang('product.status')',
            cellTooltip: true,
            enableSorting : false, //_getInfo('paid','sortable'),
            //width : _getInfo('status','width'),
            minWidth: 50,
            cellClass : _getInfo('status','align'),
          }



          ];

          columsSetting = columsSetting.concat([{  
            field : 'action',
            displayName : '@lang('common.action')',
            cellTemplate: '<span class="action-btn-wrap"><a href="<%row.entity.copy_url%>" class="btn-blue">@lang('product.copy')</a><a href="<%row.entity.detail_url%>" class="btn-grey">@lang('product.edit')</a><a href="<%row.entity.view_url%>" class="btn-grey">@lang('product.view')</a><a href="javascript:void(0)" rel="<%row.entity.delete_url%>" class="red action-del">@lang('product.delete')<i class="fas fa-times"></i></a></span>',
            minWidth: 310,
            enableSorting : false,
            cellClass:_getInfo('action','align'),
          }
        ]);       
    
@endsection

@section('content')

<div class="ng-cloak" >
    <div class="row">
        <div class="col-sm-12">
            <h1 class="page-title title-border clearfix d-flex align-items-end">@lang('product.manage_product_information') 
                <a class="float-right btn-blue ml-auto" href="{{action('Seller\ProductController@create')}}">@lang('product.create_new_product') 
                </a>
            </h1>
            <div class="row">
                <div class="col-sm-12">                           
                    <div class="tab-content">                        
                        <div class="product-detail" ng-controller="gridtableCtrl">                          
                            @include('includes.gridtable')                           
                        </div> 
                        

                        <!--div class="tab-pane active mng-product" id="outstanding-balance">
                            <div class="prod-review-tbl">
                                <div class="table">
                                    <div class="table-header">
                                        <ul>
                                            <li>Product</li>
                                            <li>Product Standard</li>
                                            <li>Stock</li>                              
                                            <li>Unit Price (Baht) </li>                                                 
                                            <li>Unit</li>
                                            <li>&nbsp;</li>                                                 
                                        </ul>
                                    </div>
                                    <div class="table-content">
                                        <ul>
                                            <li class="product">
                                                <span class="product-img">
                                                    <img src="images/banner/prod-item9.jpg" width="50" height="50" alt="">
                                                </span>
                                                <span class="prod-name">Product Name</span>
                                            </li>
                                            <li>
                                                <span class="xsa">XSA</span>
                                            </li>
                                            <li>50</li>
                                            <li>100</li>                                                    
                                            <li>Bag</li>
                                            <li class="action">
                                               <button class="btn-blue">Add</button>
                                               <button class="btn-grey">Edit</button>                                                      
                                               <a href="#" class="red action-del">Delete <i class="fas fa-times"></i></a>
                                             </li>
                                        </ul>
                                        <ul>
                                            <li class="product">
                                                <span class="product-img">
                                                    <img src="images/banner/prod-item9.jpg" width="50" height="50" alt="">
                                                </span>
                                                <span class="prod-name">Product Name</span>
                                            </li>
                                            <li>
                                                <span class="mb">MB</span>
                                            </li>
                                            <li>50</li>
                                            <li>100</li>
                                            <li>Bag</li>                                                    
                                            <li class="action">
                                               <button class="btn-blue">Add</button>
                                               <button class="btn-grey">Edit</button>                                                      
                                               <a href="#" class="red action-del">Delete <i class="fas fa-times"></i></a>
                                             </li>
                                        </ul>

                                        <ul>
                                            <li class="product">
                                                <span class="product-img">
                                                    <img src="images/banner/prod-item9.jpg" width="50" height="50" alt="">
                                                </span>
                                                <span class="prod-name">Product Name</span>
                                            </li>
                                            <li>
                                                <span class="ma">MA</span>
                                            </li>
                                            <li>50</li>
                                            <li>100</li>
                                            <li>Bag</li>                                                    
                                            <li class="action">
                                               <button class="btn-blue">Add</button>
                                               <button class="btn-grey">Edit</button>                                                      
                                               <a href="#" class="red action-del">Delete <i class="fas fa-times"></i></a>
                                             </li>
                                        </ul>

                                        <ul>
                                            <li class="product">
                                                <span class="product-img">
                                                    <img src="images/banner/prod-item9.jpg" width="50" height="50" alt="">
                                                </span>
                                                <span class="prod-name">Product Name</span>
                                            </li>
                                            <li>
                                                <span class="la">LA</span>
                                            </li>
                                            <li>50</li>
                                            <li>100</li>
                                            <li>Bag</li>                                                    
                                            <li class="action">
                                               <button class="btn-blue">Add</button>
                                               <button class="btn-grey">Edit</button>                                                      
                                               <a href="#" class="red action-del">Delete <i class="fas fa-times"></i></a>
                                             </li>
                                        </ul>

                                        <ul>
                                            <li class="product">
                                                <span class="product-img">
                                                    <img src="images/banner/prod-item9.jpg" width="50" height="50" alt="">
                                                </span>
                                                <span class="prod-name">Product Name</span>
                                            </li>
                                            <li>
                                                <span class="xsb">XSB</span>
                                            </li>
                                            <li>50</li>
                                            <li>100</li>
                                            <li>Bag</li>                                                    
                                            <li class="action">
                                               <button class="btn-blue">Add</button>
                                               <button class="btn-grey">Edit</button>                                                      
                                               <a href="#" class="red action-del">Delete <i class="fas fa-times"></i></a>
                                             </li>
                                        </ul>
                                    </div>
                                </div>
                            </div>  
                        </div>
                        
                        <div class="tab-pane" id="paid-amount">
                                <div class="prod-review-tbl">
                                    <div class="table">
                                        <div class="table-header">
                                            <ul>
                                                <li>&nbsp;</li>
                                                <li>Order No.</li>
                                                <li>Buyer</li>                              
                                                <li>Total</li>
                                                <li>Must be paid on the day</li>
                                                <li>&nbsp;</li>
                                                
                                            </ul>
                                        </div>
                                        <div class="table-content">
                                            <ul>
                                                <li>1.</li>
                                                <li><a href="#" class="link">082889388378</a></li>
                                                <li><a href="#" class="link">Buyer Name</a></li>
                                                <li>20,000.00 Baht</li>
                                                <li>13/09/2018</li>
                                                <li><a href="#" class="link">View Details</a></li>
                                            </ul>
                                            <ul>
                                                <li>2.</li>
                                                <li><a href="#" class="link">082889388378</a></li>
                                                <li><a href="#" class="link">Buyer Name</a></li>
                                                <li>20,000.00 Baht</li>
                                                <li>13/09/2018</li>
                                                <li><a href="#" class="link">View Details</a></li>
                                            </ul>
                                                                        
                                        
                                        </div>
                                    </div>
                                </div>
                        </div>

                       
                        <ul class="pagination">
                           <li class="page-item">
                              <a class="page-link" href="javascript:void()">
                              <i class="fas fa-angle-double-left"></i>                          
                              </a>
                           </li>
                           <li class="page-item">
                              <a class="page-link" href="javascript:void()">
                              <i class="fas fa-angle-left"></i>
                              </a>
                           </li>
                           <li class="page-item"><a class="page-link" href="javascript:void()">1</a></li>
                           <li class="page-item"><a class="page-link" href="javascript:void()">2</a></li>
                           <li class="page-item"><a class="page-link" href="javascript:void()">3</a></li>
                           <li class="page-item"><a class="page-link" href="javascript:void()">4</a></li>
                           <li class="page-item"><a class="page-link" href="javascript:void()"><i class="fas fa-ellipsis-h"></i></a></li>
                           <li class="page-item"><a class="page-link" href="javascript:void()">13</a></li>
                           <li class="page-item">
                              <a class="page-link" href="javascript:void()" aria-label="Next">
                              <i class="fas fa-chevron-right"></i>
                              </a>
                           </li>
                           <li class="page-item">
                              <a class="page-link" href="javascript:void()">
                              <i class="fas fa-angle-double-right"></i>                                                      
                              </a>
                           </li>
                        </ul-->

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
    
    {!! CustomHelpers::combineCssJs(['js/seller/product'],'js') !!}
    

@stop