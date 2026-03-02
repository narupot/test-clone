@extends('layouts.app') 

@section('header_style')

    {!! CustomHelpers::combineCssJs(['css/myaccount','css/bootstrap-select','css/ui-grid-unstable'],'css') !!}



<style>
.product-card {
    border-radius: 20px;
    border: 1px solid #E5E5E5;
    padding: 18px;
    transition: 0.2s;
    background: #fff;
}
.product-card:hover {
    box-shadow: 0 3px 14px rgba(0,0,0,0.08);
}

/* รูปสินค้า */
.product-card img.product-image {
    width: 232px;
    height: 180px;
    object-fit: cover;
    border-radius: 12px;
}

/* ชื่อสินค้า */
.product-title {
    font-size: 16px;
    font-weight: 600;
    color: #333;
    line-height: 1.3;
}

/* Badge มาตรฐานสินค้า */
.badge-la {
    background: #8CD77A;
    color: #fff;
    font-size: 12px;
    padding: 4px 10px;
    border-radius: 20px;
    font-weight: 600;
}
.badge-text {
    font-size: 13px;
    color: #999;
}

/* ข้อความรายละเอียด */
.product-text {
    font-size: 13px;
    color: #666;
}

/* input ราคา */
.price-input {
    background: #F3F3F3 !important;
    border-radius: 10px;
    border: 1px solid #DCDCDC;
    font-size: 14px;
}

/* ปุ่มปรับราคา */
/* .btn-price {
    background: #F3453F;
    border: none;
    padding: 5px 0;
    border-radius: 4px;
    font-size: 12px;
    font-weight: 400;
    color: #fff;
} */

    .btn-price {
        background: #F3453F;
        border: none;
        padding: 8px 15px; 
        border-radius: 4px;
        font-size: 14px; 
        font-weight: 400;
        color: #fff;
        display: inline-block;
        text-align: center; 
        text-decoration: none; 
    }

/* ปุ่มล่าง */
.btn-edit {
    border: 2px solid #F3453F;
    color: #F3453F;
    font-weight: 400;
    border-radius: 4px;
    height: 36px;
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 0 15px;
    text-decoration: none;
}
.btn-delete {
    border: 2px solid #F3453F;
    background: #F3453F;
    color: #fff;
    border-radius: 4px;
    font-weight: 400;
}
.btn-view {
    background: #23B06B;
    color: #fff;
    border-radius: 4px;
    font-weight: 400;
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 0 15px;
    text-decoration: none;
}

/* Toggle Switch */
.switch {
    position: relative;
    width: 46px;
    height: 24px;
}
.switch input { display: none; }

.slider {
    position: absolute;
    cursor: pointer;
    top: 0; left: 0; right: 0; bottom: 0;
    background-color: #ccc;
    transition: .4s;
    border-radius: 24px;
}
.slider:before {
    position: absolute;
    content: "";
    height: 18px; width: 18px;
    left: 3px; bottom: 3px;
    background-color: white;
    transition: .4s;
    border-radius: 50%;
}
input:checked + .slider {
    background-color: #23B06B;
}
input:checked + .slider:before {
    transform: translateX(22px);
}

.price-box-border {
    border: 1px solid #ccc;
    border-radius: 8px;
    padding: 6px 10px;
}

/* 💡 ทำให้ Input Group ทั้งก้อนมีขอบโค้งมนแบบ Pill */
.input-group {
 
    border-radius: 50px !important; 
    overflow: hidden; 
}

.input-group > .form-control {
    border-top-left-radius: 50px !important;
    border-bottom-left-radius: 50px !important;
    border-right: none; 
}
.input-group > .btn {
    border-top-right-radius: 50px !important;
    border-bottom-right-radius: 50px !important;
    border-left: none; 
}

.input-group > .btn-danger {
    padding-left: 15px;
    padding-right: 15px;
    z-index: 2;
}
.input-group > .form-control {
    padding-right: 0.5rem;
}

/* */
/* จัดกึ่งกลาง Pagination */
.custom-pagination-wrapper {
    display: flex;
    justify-content: center;
    margin-top: 30px;
    margin-bottom: 50px;
}

/* ปรับแต่งตัว UL ของ Pagination */
.custom-pagination-wrapper .pagination {
    display: flex;
    gap: 5px; /* ระยะห่างระหว่างปุ่ม */
    align-items: center;
}

/* ปรับแต่งลิงก์ (ตัวเลข) ปกติ */
.custom-pagination-wrapper .pagination > li > a,
.custom-pagination-wrapper .pagination > li > span {
    border: none !important; /* ลบขอบเดิม */
    border-radius: 50% !important; /* ทำเป็นวงกลม */
    width: 40px;
    height: 40px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 14px;
    font-weight: 600;
    color: #666;
    background-color: #fff;
    box-shadow: 0 2px 5px rgba(0,0,0,0.05); /* เงาบางๆ */
    transition: all 0.3s ease;
    margin: 0 2px;
}

/* Hover (เอาเมาส์ชี้) */
.custom-pagination-wrapper .pagination > li > a:hover {
    background-color: #f8f9fa;
    color: #F3453F; /* สีแดงตอนชี้ */
    transform: translateY(-2px); /* ลอยขึ้นนิดนึง */
    box-shadow: 0 4px 8px rgba(0,0,0,0.1);
}

/* สถานะ Active (หน้าปัจจุบัน) */
.custom-pagination-wrapper .pagination > .active > a,
.custom-pagination-wrapper .pagination > .active > span,
.custom-pagination-wrapper .pagination > .active > a:hover,
.custom-pagination-wrapper .pagination > .active > span:hover {
    background-color: #F3453F !important; /* สีแดงพื้นหลัง */
    color: #fff !important; /* ตัวหนังสือขาว */
    box-shadow: 0 4px 10px rgba(243, 69, 63, 0.4); /* เงาสีแดงฟุ้งๆ */
    border: none;
    pointer-events: none; /* ห้ามกดซ้ำ */
}

/* ปุ่ม Disabled (เช่น หน้าแรกสุด/หน้าท้ายสุด เมื่อกดไม่ได้) */
.custom-pagination-wrapper .pagination > .disabled > a,
.custom-pagination-wrapper .pagination > .disabled > span,
.custom-pagination-wrapper .pagination > .disabled > a:hover,
.custom-pagination-wrapper .pagination > .disabled > span:hover {
    background-color: #f3f3f3;
    color: #ccc;
    cursor: not-allowed;
    box-shadow: none;
    transform: none;
}
.content-wrap form button,
.content-wrap form .btn {
    min-width: 0px !important;
}

.search-box-2 input {
        width: 100%;
        border-radius: 50px;
        padding: 0 50px 0 20px;
        height: 45px;
        border: 1px solid #ddd;
        background: #f9f9f9;
        transition: all 0.3s;
    }
</style>




@endsection
@php
    isset($_GET['searchproductname'] ) ? $searchproductname = $_GET['searchproductname'] : $searchproductname = "";
@endphp
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
        var base_url = "{{ url('') }}";
        // อ๊อฟ Start 09-06-2568 
        @if ($searchproductname != null )
            var dataJsonUrl = "{{ action('Seller\ProductController@getProductlistSearch') }}?searchproductname=<?=$searchproductname?>";
        @else
            var dataJsonUrl = "{{ action('Seller\ProductController@getProductlist') }}";
        @endif
        // End

        var text_ok_btn = "@lang('common.ok_btn')";

        var getSellerProductUnitEditUrl = "{{ action('PopUpController@getSellerProductUnitEditPopUp')}}";

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
            displayName : 'Stock คงเหลือ',
            cellTooltip: true,
            enableSorting : false, //_getInfo('paid','sortable'),
            //width : _getInfo('quantity','width'),
            width : 170,
            cellClass : _getInfo('quantity','align'),
          },
          { 
            field : 'unit_price',
            displayName : '@lang('product.unit_price')',
            cellTooltip: true,
            enableSorting : false, //_getInfo('paid','sortable'),
           // width : _getInfo('unit_price','width'),
            minWidth: 100,
            //cellClass : _getInfo('unit_price','align'),
          }
          ,{ 
            field : 'unit_price',
            displayName : ' ',
            cellTemplate: '<a ng-if="row.entity.unit_price !=\'Not show\'?true:false" href="{{ action('PopUpController@getSellerProductPopUp')}}/<%row.entity.id%>" rel="<%row.entity.id%>" class="btn-grey changePriceModel" data-toggle="modal">@lang('product.change_prize')</a></span>',
            //cellTooltip: true,
            enableSorting : false, //_getInfo('paid','sortable'),
            //width : _getInfo('unit_price','width'),
            minWidth: 100,            
            //cellClass : _getInfo('unit_price','align'),
          }
          ,{ 
            field : 'package_name',
            displayName : '@lang('product.package_name')',
            cellTooltip: true,
            enableSorting : false, //_getInfo('paid','sortable'),
            //width : _getInfo('package_name','width'),
            minWidth: 150,
            cellClass : _getInfo('package_name','align'),
          }
          ,{ 
            field : 'status',
            displayName : '@lang('product.status')',
            cellTemplate:'<label class="button-switch-sm ml-1"><input type="checkbox" class="switch switch-orange" ng-true-value="\'Active\'" ng-false-value="\'Inactive\'" ng-model="row.entity.status" value="<%row.entity.id%>"><span for="autoRelated" class="lbl-off">@lang('shop.closed')</span><span for="autoRelated" class="lbl-on">@lang('shop.open')</span></label>',
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
            cellTemplate: '<span class="action-btn-wrap" ><a href="<%row.entity.copy_url%>" class="btn-blue" style="display: none" >@lang('product.copy')</a><a href="<%row.entity.detail_url%>" class="btn-grey">@lang('product.edit')</a><a href="<%row.entity.view_url%>" class="btn-grey">@lang('product.view')</a><a href="javascript:void(0)"  rel="<%row.entity.delete_url%>" class="btn btn-danger red action-del">@lang('product.delete')<i class="fas fa-times"></i></a></span>',
            minWidth: 310,
            enableSorting : false,
            cellClass:_getInfo('action','align'),
          }
        ]);   
        
    $(function() { 
        $( "#searchproductname" ).autocomplete({
            source: function( request, response ) {                    
                $.ajax({
                    url: "{{url('/seller/searchcategorydesc')}}",
                    type: 'get',
                    dataType: "json",
                    data: {
                            search: request.term
                        },
                        success: function( data ) {
                                response( data );
                        }
                    });
                },
                select: function (event, ui) {
                    $('#searchproductname').val(ui.item.label); // display the selected text
                
                    return false;
                },
                focus: function(event, ui){
                    $( "#searchproductname" ).val( ui.item.label );                
                    return false;
            },
        });
    });

    function split( val ) {
                return val.split( /,\s*/ );
    }
    function extractLast( term ) {
                return split( term ).pop();
    }
    
@endsection

@section('content')

<div class="ng-cloak">
    <div class="row">
        <div class="col-sm-12">
            <form name="formseachkeyword" method="get" id="seachkeyword" action="{{ url('/seller/product') }}">
                <div class="d-flex flex-column flex-md-row justify-content-between align-items-center mb-3">
                    
                    <div class="search-container col-12 col-md-3 mb-3 mb-md-0">
                        <div class="search-box">
                            <input type="search" 
                                name="searchproductname" 
                                id="searchproductname"
                                class="form-control"
                                placeholder="ค้นหาสินค้าของฉัน"
                                autocomplete="off" 
                                value="{{ $searchproductname }}" />
                            <button type="submit" class="search-btn"><i class="fas fa-search"></i></button>
                        </div>
                    </div>

                    <div class="col-12 col-md-auto text-end">
                        <a href="{{ action('Seller\ProductController@create') }}" 
                        class="btn btn-danger rounded-pill px-4 fw-bold w-100 w-md-auto">
                            <i class="fas fa-plus-circle me-1"></i> สร้างสินค้าใหม่
                        </a>
                    </div>

                </div>
            </form>

            <div class="product-detail" ng-controller="gridtableCtrl">
                <!-- การ์ดสินค้า -->
                <div class="product-list row" ng-if="gridOptions.data.length > 0">

                    <div class="col-md-6 mb-4" ng-repeat="product in gridOptions.data">

                        <div class="product-card">
                            <div class="row">
                                <div class="col-4">
                                    <img ng-src="<% product.productimg %>"
                                        class="product-image">
                                </div>

                                <div class="col-8">
                                    <div class="row">
                                        <div class="col-8">
                                            <div class="product-title mb-2">
                                                <% product.category_name %>
                                            </div>
                                        </div>
                                        <div class="col-4">   
                                            <div class="d-flex justify-content-end mb-2">
                                                <label class="switch">
                                                    <input type="checkbox"
                                                        ng-model="product.status"
                                                        ng-true-value="'Active'"
                                                        ng-false-value="'Inactive'"
                                                        ng-change="toggleProductStatus(product)">
                                                    <span class="slider"></span>
                                                </label>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="d-flex align-items-center mb-1">
                                        <span class="product-text">มาตรฐานสินค้า </span>
                                        <img src="<% product.badgeimage %>" width="90" alt="">
                                    </div>

                                    <div class="badge-text ms-2"><i class="fa fa-inbox" aria-hidden="true"></i> คลัง <% product.quantity %></div>
                                    <div class="badge-text ms-2">หน่วยบรรจุภัณฑ์ : <% product.package_name %></div>
                                </div>

                            </div class="row">
                                 
                                 <div class="col-12 mt-3">
                                    <div class="row mt-2">
                                        <!-- กลุ่มซ้าย 3 ช่อง มีเส้นขอบ -->
                                        <div class="col-9">
                                            <div class="price-box-border">
                                                <div class="row">
                                                    <div class="col-4 d-flex align-items-center">
                                                        <div class="product-text">ราคาสินค้า</div>
                                                    </div>

                                                    <div class="col-4 d-flex align-items-center">
                                                        <div class="badge-text ms-2"><% product.unit_price | number:2 %></div>
                                                    </div>

                                                    <div class="col-4 d-flex align-items-center">
                                                        <div class="badge-text ms-2">
                                                            บาท / <% product.package_name %>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- ปุ่มด้านขวา ไม่มี border -->
                                        <div class="col-3 ps-0">
                                            <!-- <button class="btn-price w-100">ปรับราคา</button> -->
                                             <a href="{{ action('PopUpController@getSellerProductPopUp')}}/<% product.id %>" 
                                                rel="<% product.id %>"
                                                class="btn-price w-100 changePriceModel"
                                                data-toggle="modal">
                                                    ปรับราคา
                                            </a>
                                        </div>

                                    </div>

                                    <div class="row mt-2">
                                        <!-- กลุ่มซ้าย 3 ช่อง มีเส้นขอบ -->
                                        <div class="col-9">
                                            <div class="price-box-border">
                                                <div class="row">
                                                    <div class="col-4 d-flex align-items-center">
                                                        <div class="product-text">ราคา/หน่วย</div>
                                                    </div>

                                                    <div class="col-4 d-flex align-items-center">
                                                        <div class="badge-text ms-2"><% product.unit_convert_price | number:2 %></div>
                                                    </div>

                                                    <div class="col-4 d-flex align-items-center">
                                                        <div class="badge-text ms-2">
                                                            บาท / <% product.unit_name %>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- ปุ่มด้านขวา ไม่มี border -->
                                        <div class="col-3 ps-0">
                                            <a href="{{ action('PopUpController@getSellerProductUnitEditPopUp')}}/<% product.id %>" 
                                                rel="<% product.id %>"
                                                class="btn-price w-100 changePriceUnitModel"
                                                data-toggle="modal">
                                                    ปรับราคา
                                            </a>

                                        </div>

                                    </div>


                                    <div class="d-flex justify-content-between mt-3">
                                        <a href="<% product.detail_url %>" class="btn-edit w-100 me-1 text-center">แก้ไขข้อมูล</a>
                                        <button class="btn-delete w-100 mx-1 action-del" rel="<% product.delete_url %>">ลบสินค้า</button>
                                        <a href="<% product.view_url %>" class="btn-view w-100 ms-1 text-center">ดูสินค้า</a>
                                    </div>
                                 </div>

                            <div>

                            </div>
                        </div>
                    </div>

                </div>


                <!-- กรณีไม่มีสินค้า -->
                <div class="text-center py-5 text-muted" ng-if="gridOptions.data.length == 0">
                    <i class="fa fa-box-open fa-3x mb-3"></i>
                    <p>ไม่พบรายการสินค้า</p>
                </div>

                <!-- Pagination -->
               <div class="custom-pagination-wrapper" data-ng-show="gridOptions.data.length > 0">
                    <pagination 
                        total-items="gridOptions.totalItems" 
                        items-per-page="gridOptions.paginationPageSize" 
                        ng-model="gridOptions.paginationCurrentPage" 
                        max-size="5" 
                        rotate="false" 
                        boundary-links="true" 
                        data-my-call-back="clickOnNext"
                        previous-text="&lsaquo;" 
                        next-text="&rsaquo;" 
                        first-text="&laquo;" 
                        last-text="&raquo;">
                    </pagination>
                </div>

            </div>
        </div>
    </div>


</div>

{!!LayoutHtml::AddSellerProductQtyPop()!!}
{!!LayoutHtml::AddSellerProductQtyUnitPop()!!}
@endsection 

@section('footer_scripts')
    @include('includes.gridtablejsdeps')
    <script stype="text/javascript" src="{{Config('constants.js_url')}}angular/smmApp/controller/gridTableCtrl.js"></script>
    
    {!! CustomHelpers::combineCssJs(['js/price_formatter','js/seller/product'],'js') !!}

    


@stop