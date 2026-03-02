@extends('layouts.app') 

@section('header_style')
    {!! CustomHelpers::combineCssJs(['css/myaccount','css/ui-grid-unstable'],'css') !!}
@endsection

@section('header_script')
    var txt_no = "@lang('common.no')";
    var text_ok_btn = "@lang('common.ok_btn')";
    var text_success = "@lang('common.text_success')";
    var text_error = "@lang('common.text_error')";
    var qty_error = "@lang('stock_memo.quantity_can_not_be_zero')";
    
    var update_qty = "{{action('Seller\StockMemoController@store')}}";

    //for table
    var fieldSetJson  = {!!$fielddata!!};
    var fieldset = fieldSetJson.fieldSets;
    var pagelimit = "{{action('JsonController@pageLimit')}}";
    var showSearchSection = false;
    var showHeadrePagination = false;
    var getAllDataFromServerOnce = true;
    var dataJsonUrl = "{{action('Seller\StockMemoController@getStockMemo', $product_data->id)}}";
    var tableLoaderImgUrl = "{{Config::get('constants.loader_url')}}ajax-loader.gif";
    var pagination = {!!getPagination()!!};
    var per_page_limt = "{{getPagination('limit')}}";

    //columns setting of table where field is field name of database filed.
    var columsSetting = [{ 
            field : 'date',
            displayName : '@lang('common.date')',
            cellTooltip: true,
            enableSorting : false,
            width : 200,
            cellClass : "text-align:'text-center'",
        },{ 
            field : 'time',
            displayName : '@lang('common.time')',
            cellTooltip: true,
            enableSorting : false,
            width : 140,
            cellClass : "text-align:'text-center'",

        },{ 
            field : 'comulative_val',
            displayName : '@lang('stock_memo.comulative')',
            cellTooltip: true,
            enableSorting : false,
            width : 150,
            cellClass : "text-align:'text-center'",
        },{ 
            field : 'import_val',
            displayName : '@lang('stock_memo.import')',
            cellTemplate: '<span ng-class="{blue:row.entity.import}"><%row.entity.import_val%></span>',
            cellTooltip: true,
            enableSorting : false,
            width : 150,
            cellClass : "text-align:'text-center'",
        },{ 
            field : 'sold_val',
            displayName : '@lang('stock_memo.sold')',
            cellTemplate: '<span ng-class="{green:row.entity.sold}"><%row.entity.sold_val%></span>',
            cellTooltip: true,
            enableSorting : false,
            width : 150,
            cellClass : "text-align:'text-center'",
        },{ 
            field : 'channel',
            displayName : '@lang('stock_memo.channel')',
            cellTooltip: true,
            enableSorting : false,
            width : 150,
            cellClass : "text-align:'text-center'",
        },{ 
            field : 'balance_val',
            displayName : '@lang('stock_memo.balance')',
            cellTooltip: true,
            enableSorting : false,
            width : 200,
            cellClass : "text-align:'text-center'",            
    }];
@endsection

@section('content')

<div class="container ng-cloak">
    <div class="row">
        <div class="col-sm-12"> 

            @include('includes.seller_panel_top_menu')

            <div class="tab-content">
                <div class="tab-pane active show" id="tab-seler4">
                    <div class="form-group text-right">
                        <a href="{{action('Seller\StockMemoController@index')}}" class="btn-grey">@lang('common.back')</a>
                    </div>

                    <div class="form-group">
                        <ul class="unit-box">
                            <input type="hidden" id="product_id" value="{{$product_data->id}}">
                            <li class="unit-product">
                                <span class="prod-img"> 
                                    <img src="{{$product_data->product_img}}" width="170" height="126" alt=""> 
                                </span>
                                <span class="unit-product-info">
                                    <h2>{{$product_data->category_name}}</h2>
                                    <span class="Standard">@lang('stock_memo.standard') <img src="{{$product_data->badge_img}}" width="34" height="34"></span>
                                </span>
                            </li>

                            <li class="unit-soldImport bdr">
                                <h2>@lang('stock_memo.import')</h2>
                                <div class="input-sign">
                                    <span class="sec-label"><i class="fas fa-plus number-spinner"></i></span>
                                    <input type="text" id="import" value="1" class="quantity">
                                    <span class="txt-unit">{{$product_data->unit_name}}</span><button class="btn import update_stock">@lang('common.save')</button>
                                </div>
                            </li>

                            <li class="unit-soldImport">
                                <h2>@lang('stock_memo.sold_offline')</h2>
                                <div class="input-sign">
                                    <span class="sec-label"><i class="fas fa-minus number-spinner"></i></span>
                                    <input type="text" id="sold" value="1" class="quantity">
                                    <span class="txt-unit">{{$product_data->unit_name}}</span><button class="btn sold update_stock">@lang('common.save')</button>
                                </div>
                            </li>
                        </ul>
                    </div>

                    <div class="product-detail" ng-controller="gridtableCtrl">
                        @include('includes.gridtable')                           
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

    <script type="text/javascript">

        $('body').on('change','.quantity',function() {
        
            var quantity = $(this).val();
            var is_int = Number.isInteger(Number(quantity));

            if(is_int == false || quantity <= 0){
                $(this).val(1);
            }
            return;
        });

        $('body').on('click','.update_stock',function() {

            var product_id = $('#product_id').val();
            
            _this = $(this);
            if(_this.hasClass('import')) {
                var qty = $('#import').val();
                var type = 'import';
            }
            else if(_this.hasClass('sold')) {
                var qty = $('#sold').val();
                var type = 'sold';               
            }
            else {
                return;
            }
            //alert('product_id:'+product_id+', qty:'+qty+', type:'+type);return;

            var data = {product_id:product_id, qty:qty, type:type};
            callAjaxRequest(update_qty,"post",data,function(result) {
                var response = jQuery.parseJSON(result);

                if(response.status=='success') {
                    swal({
                        type: response.status, 
                        text: response.message, 
                        confirmButtonText : text_ok_btn,
                    })
                    .then(function(){ location.reload(); });
                } 
                else {
                    swal({
                        type: 'error', 
                        text: response.message, 
                        confirmButtonText : text_ok_btn,
                    });
                }
            });
        });

        $(function() {
            $(".number-spinner").mousedown(function () {
                _this = $(this);
                if(_this.hasClass('fa-plus')) {
                    var qty = $('#import').val();
                    $('#import').val(++qty);                
                }
                else if(_this.hasClass('fa-minus')){
                    var qty = $('#sold').val();
                    // if(qty <= 1){
                    //     swal(lang_json.error, lang_json.qty_error, 'error');
                    //     return;
                    // }
                    //$('#sold').val(--qty);
                    $('#sold').val(++qty);                
                }
                else {
                    return;
                }
            });
        });            
    </script>
@stop