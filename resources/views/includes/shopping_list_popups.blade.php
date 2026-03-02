<style type="text/css">
    #basic {
        min-width: 250px;
        height: 200px;
    }
</style>


<!--  Login register popup -->
<div id="edit_shopping_list" class="modal fade" role="dialog" >
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header border-bottom">
                <h1 class="mb-0">@lang('shopping_list.edit_shopping_list_name')</h1>                    
                <span class="close fas fa-times" data-dismiss="modal"></span>
            </div>
            <div class="modal-body">
                <form name="editShoppingList" id="editShoppingList" action="{{action('User\ShoppinglistController@editShoppingListName')}}" method="POST">
                     {{ csrf_field() }}
                    <div class="reg-customer-form">
                        <div class="form-group">
                            <label>@lang('shopping_list.shopping_list_name')</label>
                            <input type="hidden" name="id" value="" id="hidden_id">
                            <input type="text" name="name" id="name">
                            <p id="error-info"></p>
                        </div>
                        <div class="form-group">
                            <button class="btn update_shopping_list_name" type="button">@lang('common.update')</button>
                            <!-- <input type="submit" class="btn" value="Give Credit"> -->
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
<!-- Add product to shopping list -->
<div id="add_product" class="modal fade" role="dialog" >
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header border-bottom">
                <h1 class="mb-0">@lang('shopping_list.add_product_to_shopping_list')</h1>                    
                <span class="close fas fa-times" data-dismiss="modal"></span>
            </div>
            <div class="modal-body">
                <form name="addProduct" id="addProduct" action="{{action('User\ShoppinglistController@addProductToShoppinglist')}}" method="POST">
                    {{ csrf_field() }}
                    <div class="form-group mt-4">
                        <input type="hidden" name="shopping_list_id" id="shopping_list_id">
                        <label>@lang('shopping_list.select_product')</label>
                        <select data-placeholder="@lang('shopping_list.sreach_product')" name="products[]" multiple class="select chosen-select">
                            @foreach($prdOptArray as $key =>$potGroup)
                            <optgroup label="{{$potGroup['label']}}">
                                @foreach($potGroup['child'] as $key =>$childCat)
                                    <option value="{{$childCat->id}}">{{$childCat->getCatDesc->name}}</option>
                                @endforeach
                            </optgroup>
                            @endforeach                   
                        </select>
                    </div>
                    <div class="form-group mt-4">
                        <label>@lang('shopping_list.select_seller')</label>
                        <select data-placeholder="@lang('shopping_list.sreach_seller')" name="seller" id="seller_list" class="seller  chosen-select">
                            <option value="">@lang('common.select')</option>              
                        </select>
                    </div>
                    <div class="form-group mt-4">
                        <button class="btn add_product_in_shopping_list" type="button">@lang('common.save')</button>
                        <!-- <input type="submit" class="btn" value="Give Credit"> -->
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<div id="edit_note" class="modal fade" role="dialog" >
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header border-bottom">
                <h1 class="mb-0">@lang('shopping_list.edit_product_note')</h1>                    
                <span class="close fas fa-times" data-dismiss="modal"></span>
            </div>
            <div class="modal-body">
                <form name="editNote" id="editNote" action="{{action('User\ShoppinglistController@editNote')}}" method="POST">
                     {{ csrf_field() }}
                    <input type="hidden" name="shopping_list_id" id="shopping_id">
                    <input type="hidden" name="shopping_list_item_id" id="shopping_list_item_id">
                    <div class="form-group mt-6">
                        <textarea name="note" id="note"></textarea>
                    </div>
                    <div class="form-group mt-4">
                        <button class="btn edit_note" type="button">@lang('common.update')</button>
                        <!-- <input type="submit" class="btn" value="Give Credit"> -->
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>



