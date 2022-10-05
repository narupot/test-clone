@extends('layouts.app') 

@section('header_style')
    {!! CustomHelpers::combineCssJs(['css/myaccount','css/bootstrap-select','css/ui-grid-unstable', 'css/select'],'css') !!}
     <link rel="stylesheet" type="text/css" href="{{Config('constants.css_url') }}selectize.css">
     <style type="text/css">
        .table-shoplist .prod-sizetype span:first-child {
           order: 2;
        }
        .table-shoplist .prod-sizetype > a, .table-shoplist .prod-sizetype > button {
           order: 2;
        }
     </style>
@endsection

@section('header_script')
  var currency = "@lang('common.baht')";
  var create_shopping_list_url = "{{action('User\ShoppinglistController@createNewShoppingList')}}";
  var get_shopping_list_items_url = "{{action('User\ShoppinglistController@getShoppingListItems')}}";
  var delete_shopping_list_url = "{{action('User\ShoppinglistController@deleteShoppingList')}}";
  var save_shopping_list_size_grade_url = "{{action('User\ShoppinglistController@saveItemStandered')}}";
  var complete_shopping_list_item_url = "{{action('User\ShoppinglistController@completeShoppingItem')}}";
  var delete_shopping_list_item_url = "{{action('User\ShoppinglistController@deleteShoppingItem')}}";
  var check_shopping_list_items_url = "{{action('User\ShoppinglistController@checkShoppingListLoadingStatus')}}";
  var save_shopping_list_price_url = "{{action('User\ShoppinglistController@saveItemPrice')}}";
  var save_shopping_list_qty_url = "{{action('User\ShoppinglistController@saveItemQty')}}";
  var save_shopping_list_badge_edit_url = "{{action('User\ShoppinglistController@editBadge')}}";
  var save_shopping_list_price_edit_url = "{{action('User\ShoppinglistController@editPrice')}}";
  var save_shopping_list_qty_edit_url = "{{action('User\ShoppinglistController@editQty')}}";
  var get_shopping_list_prd_shop_url = "{{action('User\ShoppinglistController@getCategorySellers')}}";

  var save_shopping_list_all_url = "{{action('User\ShoppinglistController@saveAllItem')}}";
  var txt_no = "@lang('common.no')";
  var txt_select = "@lang('common.select')";
  var text_ok_btn = "@lang('common.ok_btn')";
  var text_success = "@lang('common.text_success')";
  var text_error = "@lang('common.text_error')";
  var text_yes_remove_it = "@lang('common.yes_remove_it')";
  var yes_complete_it = "@lang('shopping_list.yes_complete_it')";
  var are_you_sure = "@lang('shopping_list.are_you_sure')";
  var would_you_like_to_complete_this_item = "@lang('shopping_list.would_you_like_to_complete_this_item')";
  var text_warning = "@lang('common.warning')";
  var error_msg ={
    are_you_sure : "@lang('shopping_list.are_you_sure')",
    txt_delete_confirm : "@lang('shopping_list.are_you_sure_to_delete_this_shopping_list')",
    yes_delete_it : "@lang('shopping_list.yes_delete_it')",
    server_error : "@lang('common.something_went_wrong')",
    shopping_name_empty_msg : "@lang('shopping_list.shopping_name_empty_msg')",
    create_shopping_list_name : "@lang('shopping_list.create_shopping_list_name')",
    create_shopping_list : "@lang('shopping_list.create_shopping_list')"
  }; 
@endsection
@section('content')
<div class="row">
    <div class="col-sm-12"> 
        @include('includes.buyer_shopping_tab',['purchased_products'=>$pur_prds_in_shop_list,'list_of_shopping_list'=>$total_prds_in_shop_list])  
        <div class="tab-content" >
            <div class="tab-pane active" id="tab-seler1">   
                <div class="form-group mt-3 text-right">
                    <h1 class="page-title title-border shipmenu-title">
                        <span><i class="fas fa-pencil-alt"></i>@lang('shopping_list.shopping_list_today')</span> 
                        <span class="select-shopping-list pb-0">
                            <select class="selectpicker" name="shoppinglist_from" id="shoppinglist_from">
                                @foreach($option_array as $key => $option)
                                    <option value="{{$option['key']}}" @if($default_shopping==$option['key']) selected='selected' @endif>{{$option['value']}}</option>
                                @endforeach
                            </select>
                        </span>
                    </h1>
                </div>  
                <div class="form-group" id="create_new_shopping_list"></div>
                <div class="creatlist-shopping" id="show_shopping_list">
                </div>
            </div>
        </div>               
    </div>
</div>  

@include('includes.shopping_list_popups',['prdOptArray'=>$prdOptArray])        
@endsection 

@section('footer_scripts')
    @include('includes.gridtablejsdeps')
    <script stype="text/javascript" src="{{Config('constants.js_url')}}angular/smmApp/controller/gridTableCtrl.js"></script>
    <script src="{{ Config('constants.js_url') }}selectize.js"></script>
    
    {!! CustomHelpers::combineCssJs(['js/price_formatter','js/user/product','js/bootstrap-select', 'js/chosen.jquery.min', 'js/user/shopping_list'],'js') !!}

    <script type="text/javascript">

    	$('.select1').selectize({});
        $(".select").selectize().change(function (event) {
            //console.log($(event.target).val());
            var cat_id = $(event.target).val();
            var data = {'cat_id':cat_id};
            callAjaxRequest(get_shopping_list_prd_shop_url, 'post', data, function(response){
                var seller_html = '<option value="" selected="selected">'+txt_select+'</option>';
                if(response.status==='success'){
                    $.each( response.seller_data, function( key, value ) {
                      seller_html += "<option value='"+value.shop_id+"'>"+value.shop_name+"</option>";
                    });
                }
                $("#seller_list").html(seller_html);
            });
        });


        var div_top = $('#show_shopping_list').offset().top;;
        $(window).scroll(function() {
            var window_top = $(window).scrollTop() - 0;
            if (window_top > div_top) {
                if (!$('#shipping_btns').is('.sticky')) {
                    $('#shipping_btns').addClass('sticky');
                }
            } else {
                $('#shipping_btns').removeClass('sticky');
            }
        });


        
    </script>
    <script>
        $(document).ready(function(){
            $('.prod-sizetype #item_grade').parent().addClass('order2');
        })
    </script>

@stop

