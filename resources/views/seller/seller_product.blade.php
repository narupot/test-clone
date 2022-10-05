@extends('layouts.app') 

@section('header_style')
    {!! CustomHelpers::combineCssJs(['css/myaccount','css/bootstrap-select','css/ui-grid-unstable'],'css') !!}
    <style type="text/css">
        .action-btn-wrap {
            flex-wrap: wrap;
        }
    </style>

@endsection

@section('header_script')

        var lang_json = {"ok":"@lang('common.ok')", "success":"@lang('common.success')"};      
        var fieldSetJson  = {!! $fielddata !!};
        var fieldset = fieldSetJson.fieldSets;
        var pagelimit = "{{action('JsonController@pageLimit')}}";
        var showSearchSection = true;
        var showHeadrePagination = true;
        var getAllDataFromServerOnce = true;
        var dataJsonUrl = "{{ action('Seller\ProductController@getProductlist') }}";
        var text_ok_btn = "@lang('common.ok_btn')";

        var getSellerProductUrl = "{{ action('PopUpController@getSellerProductPopUp')}}";

        var updateStatus = "{{ action('Seller\ProductController@updateStatus')}}";

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
            width : 290,
            cellClass : _getInfo('category_name','align'),
          },{ 
            field : 'badgeimage',
            displayName : '@lang('product.product_standard')',
            cellTemplate: '<span class="sa"><img src="<%row.entity.badgeimage%>" width="" height="34" alt=""></span>',
            enableSorting : _getInfo('product_standard','sortable'),
            width : _getInfo('product_standard','width'),
            width : 130,
           // cellClass : _getInfo('product_standard','align'),

          },{ 
            field : 'quantity',
            displayName : '@lang('product.stock')',
            cellTooltip: true,
            enableSorting : false, //_getInfo('paid','sortable'),
            width : _getInfo('quantity','width'),
            width : 170,
            cellClass : _getInfo('quantity','align'),
          },{ 
            field : 'unit_price',
            displayName : '@lang('product.unit_price')',
            cellTemplate: '<span class="action-btn-wrap justify-content-between"><span class="count-price"><%row.entity.unit_price%></span><a ng-if="row.entity.unit_price !=\'Not show\'?true:false" href="{{ action('PopUpController@getSellerProductPopUp')}}/<%row.entity.id%>" rel="<%row.entity.id%>" class="btn-grey changePriceModel" data-toggle="modal">@lang('product.change_prize')</a></span>',
            //cellTooltip: true,
            enableSorting : false, //_getInfo('paid','sortable'),
            //width : _getInfo('unit_price','width'),
            minWidth: 145,
            //cellClass : _getInfo('unit_price','align'),
          }
          ,{ 
            field : 'package_name',
            displayName : '@lang('product.package_name')',
            cellTooltip: true,
            enableSorting : false, //_getInfo('paid','sortable'),
            //width : _getInfo('package_name','width'),
            minWidth: 170,
            cellClass : _getInfo('package_name','align'),
          }
          ,{ 
            field : 'status',
            displayName : '@lang('product.status')',
            cellTemplate:'<label class="button-switch-sm ml-1"><input type="checkbox" class="switch switch-orange" ng-true-value="\'Active\'" ng-false-value="\'Inactive\'" ng-model="row.entity.status" value="<%row.entity.id%>"><span for="autoRelated" class="lbl-off">@lang('shop.closed')</span><span for="autoRelated" class="lbl-on">@lang('shop.open')</span></label>',
            cellTooltip: true,
            enableSorting : false, //_getInfo('paid','sortable'),
            //width : _getInfo('status','width'),
            minWidth: 100,
            cellClass : _getInfo('status','align'),
          }



          ];

          columsSetting = columsSetting.concat([{  
            field : 'action',
            displayName : '@lang('common.action')',
            cellTemplate: '<span class="action-btn-wrap"><a href="<%row.entity.detail_url%>" class="btn-grey ">@lang('product.edit')</a><a href="<%row.entity.stock_memo_url%>" class="btn d-none">@lang('product.adjust_stock')</a></span>',
            minWidth: 200,
            enableSorting : false,
            cellClass:_getInfo('action','align'),
          }
        ]);       
      
@endsection

@section('content')
<div class="ng-cloak">     
    <div class="row">
        <div class="col-sm-12"> 
            @include('includes.seller_panel_top_menu')
             <div class="tab-content">
                <div class="tab-pane active" id="tab-seler1">  
                   
                    <div class="form-group mt-3 text-right">
                         <a class="btn" href="{{action('Seller\ProductController@create')}}">@lang('product.create_new_product')</a>
                    </div>

                    <div class="product-detail" ng-controller="gridtableCtrl">    @include('includes.gridtable')                           
                    </div> 


                    <!--div class="prod-review-tbl bdr-table">
                            <div class="table">
                                <div class="table-header">
                                    <ul>
                                        <li>Product</li>
                                        <li>Standard</li>
                                        <li>Left in stock</li>                              
                                        <li>Price(Baht)/Unit</li>
                                        <li>Unit</li>
                                        <li>Weight/unit</li>
                                        <li>Status</li>
                                        <li>&nbsp;</li>                                                 
                                    </ul>
                                </div>
                                <div class="table-content">
                                    <ul>
                                        <li>
                                            <div class="product">
                                                <span class="prod-img">
                                                    <img src="images/banner/prod-item9.jpg" width="59" height="50" alt="">
                                                    <span class="prod-cam"><i class="fas fa-camera"></i></span>
                                                </span>
                                                <span class="prod-name">Product Name</span>
                                            </div>
                                        </li>
                                        <li><span class="xsa">XSA</span></li>
                                        <li>100</li>
                                        <li class="prize">
                                            <span class="count-price">100</span>                                                    
                                            <button class="btn-grey" type="submit" data-toggle="modal" data-target="#changePriceModel">change prize</button> 
                                        </li>
                                        <li>Bag</li>
                                        <li>1 Kg</li>
                                        <li>
                                            <label class="button-switch-sm ml-3">
                                               <input type="checkbox" name="" value="" class="switch switch-orange" checked="checked">                        
                                                 <span for="autoRelated" class="lbl-off">Close</span>
                                                 <span for="autoRelated" class="lbl-on">Open</span>
                                           </label> 
                                        </li>
                                        <li><a href="#" class="link"><i class="fas fa-edit"></i> Edit</a></li>
                                    </ul>

                                    <ul>
                                        <li>
                                            <div class="product">
                                                <span class="prod-img">
                                                    <img src="images/banner/prod-item9.jpg" width="59" height="50" alt="">
                                                    <span class="prod-cam"><i class="fas fa-camera"></i></span>
                                                </span>
                                                <span class="prod-name">Product Name</span>
                                            </div>
                                        </li>
                                        <li><span class="mb">MB</span></li>
                                        <li>100</li>
                                        <li class="prize">
                                            <span class="count-price no-price">No price</span> 
                                            <button type="submit" class="btn-grey">change prize</button> 
                                        </li>
                                        <li>Bag</li>
                                        <li>1 Kg</li>
                                        <li>
                                            <label class="button-switch-sm ml-3">
                                               <input type="checkbox" name="" value="" class="switch switch-orange" checked="checked">                        
                                                 <span for="autoRelated" class="lbl-off">Close</span>
                                                 <span for="autoRelated" class="lbl-on">Open</span>
                                           </label>
                                        </li>
                                        <li><a href="#" class="link"><i class="fas fa-edit"></i> Edit</a></li>
                                    </ul>

                                    <ul>
                                        <li>
                                            <div class="product">
                                                <span class="prod-img">
                                                    <img src="images/banner/prod-item9.jpg" width="59" height="50" alt="">
                                                    <span class="prod-cam"><i class="fas fa-camera"></i></span>
                                                </span>
                                                <span class="prod-name">Product Name</span>
                                            </div>
                                        </li>
                                        <li><span class="ma">MA</span></li>
                                        <li>100</li>
                                        <li class="prize">
                                            <span class="count-price">100</span> 
                                            <button type="submit" class="btn-grey">change prize</button> 
                                        </li>
                                        <li>Bag</li>
                                        <li>1 Kg</li>
                                        <li>
                                            <label class="button-switch-sm ml-3">
                                               <input type="checkbox" name="" value="" class="switch switch-orange" checked="checked">                        
                                                 <span for="autoRelated" class="lbl-off">Close</span>
                                                 <span for="autoRelated" class="lbl-on">Open</span>
                                           </label>
                                        </li>
                                        <li><a href="#" class="link"><i class="fas fa-edit"></i> Edit</a></li>
                                    </ul>

                                    <ul>
                                        <li>
                                            <div class="product">
                                                <span class="prod-img">
                                                    <img src="images/banner/prod-item9.jpg" width="59" height="50" alt="">
                                                    <span class="prod-cam"><i class="fas fa-camera"></i></span>
                                                </span>
                                                <span class="prod-name">Product Name</span>
                                            </div>
                                        </li>
                                        <li><span class="la">LA</span></li>
                                        <li>100</li>
                                        <li class="prize">
                                            <span class="count-price no-price">No price</span> 
                                            <button type="submit" class="btn-grey">change prize</button> 
                                        </li>
                                        <li>Bag</li>
                                        <li>1 Kg</li>
                                        <li>
                                            <label class="button-switch-sm ml-3">
                                               <input type="checkbox" name="" value="" class="switch switch-orange" checked="checked">                        
                                                 <span for="autoRelated" class="lbl-off">Close</span>
                                                 <span for="autoRelated" class="lbl-on">Open</span>
                                           </label>
                                        </li>
                                        <li><a href="#" class="link"><i class="fas fa-edit"></i> Edit</a></li>
                                    </ul>

                                    <ul>
                                        <li>
                                            <div class="product">
                                                <span class="prod-img">
                                                    <img src="images/banner/prod-item9.jpg" width="59" height="50" alt="">
                                                    <span class="prod-cam"><i class="fas fa-camera"></i></span>
                                                </span>
                                                <span class="prod-name">Product Name</span>
                                            </div>
                                        </li>
                                        <li><span class="xsb">XSB</span></li>
                                        <li>100</li>
                                        <li class="prize">
                                            <span class="count-price">100</span> 
                                            <button class="btn-grey" type="submit" data-toggle="modal" data-target="#changePriceModel">change prize</button>
                                        </li>
                                        <li>Bag</li>
                                        <li>1 Kg</li>
                                        <li>
                                            <label class="button-switch-sm ml-3">
                                               <input type="checkbox" name="" value="" class="switch switch-orange" checked="checked">                        
                                                 <span for="autoRelated" class="lbl-off">Close</span>
                                                 <span for="autoRelated" class="lbl-on">Open</span>
                                           </label>
                                        </li>
                                        <li><a href="#" class="link"><i class="fas fa-edit"></i> Edit</a></li>
                                    </ul>
                                </div>
                            </div>
                    </div-->
                </div>
            </div>
        </div>
    </div>  
</div>  
 
{!!LayoutHtml::AddSellerProductQtyPop()!!}
@endsection 
@section('footer_scripts')
    @include('includes.gridtablejsdeps')
    <script stype="text/javascript" src="{{Config('constants.js_url')}}angular/smmApp/controller/gridTableCtrl.js"></script>
    
    {!! CustomHelpers::combineCssJs(['js/price_formatter','js/seller/product'],'js') !!}
@stop

