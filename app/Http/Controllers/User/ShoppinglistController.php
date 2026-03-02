<?php 

namespace App\Http\Controllers\User;

use App\Http\Controllers\MarketPlace;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use App\Helpers\CustomHelpers;
use Exception;

use Lang;
use Auth;
use Config;
use App\UserShoppingList;
use App\UserShoppingListItems;
use App\UserShoppingListDesc;

class ShoppinglistController extends MarketPlace
{   
    public function __construct()
    {
        //$this->middleware('auth');
        $this->middleware('authenticate');
    }

    
    public function index(Request $request){
        // check if last purchased order shopping list not created yet, then create for current user
        $default_shopping_list = UserShoppingList::where(['user_id'=>Auth::user()->id])->count();
        $view_create_shopping_sec = false;
        if($default_shopping_list===0){
            $view_create_shopping_sec = true;
            $this->createDefaultShoppingList(); // Create default shopping list for latest order//
            $this->createDefaultShoppingList(Lang::get('shopping_list.favorite_shopping_list')); // Create default shopping list for
            
        }

    	$user_shopping_list = UserShoppingList::where(['user_id'=>Auth::user()->id])->with(['getShoppingDesc'])->orderBy('is_default','desc')->get();
        //dd($user_shopping_list);
    	$option_array = [];

    	/*foreach ($user_shopping_list as $key => $value) {
            if($value->is_default=='1'){
                if($value->allow_add_product=='1'){
                    array_push($option_array, ['key'=>$value->id,'value'=>Lang::get('shopping_list.favorite_shopping_list')]);
                }else{
                    array_push($option_array, ['key'=>$value->id,'value'=>Lang::get('shopping_list.last_order_items')]);
                }
            }else{
                array_push($option_array, ['key'=>$value->id,'value'=>$value->getShoppingDesc->name]);
            }
    	}*/
         
        $i = 0;
        foreach ($user_shopping_list as $key => $value){
            if($value->is_default=='1'){
                if($value->allow_add_product=='1'){
                    array_push($option_array, ['key'=>$value->id,'value'=>Lang::get('shopping_list.favorite_shopping_list')]);
                }else{
                    if($i < 1){
                       array_push($option_array, ['key'=>$value->id,'value'=>Lang::get('shopping_list.last_order_items')]);
                       $i++;  
                    }
                    
                }
            }else{
                
                array_push($option_array, ['key'=>$value->id,'value'=>$value->getShoppingDesc->name]);
            }
        }
    	array_push($option_array, ['key'=>'create_new_list','value'=>Lang::get('shopping_list.create_shopping_list')]);


        $activeParentProList = \App\Category::where(['status'=>'1','parent_id'=>'0'])->with(['getCatDesc'])->get();
        
        $prdOptArray = [];
        foreach ($activeParentProList as $key => $parentProd) {
            $clildCat = \App\Category::where(['status'=>'1','parent_id'=>$parentProd->id])->with(['getCatDesc'])->get();
            $prdOptArray[$key]['label'] = $parentProd->getCatDesc->name;
            $prdOptArray[$key]['child'] = $clildCat;
        }

        
        $default_shopping = session('shopping_list');
        $pur_prds_in_shop_list = \App\UserShoppingListItems::where(['shopping_list_id'=>$default_shopping,'is_completed'=>'1'])->count();
        $total_prds_in_shop_list = \App\UserShoppingListItems::where(['shopping_list_id'=>$default_shopping])->count();
        //dd($pur_prds_in_shop_list,$total_prds_in_shop_list);
        return view('user.shopping_list',['page_class'=>'cart-wrap','page'=>'shopping_list','option_array'=>$option_array,'prdOptArray'=>$prdOptArray,'default_shopping'=>$default_shopping,'view_create_shopping_sec'=>$view_create_shopping_sec,'total_prds_in_shop_list'=>$total_prds_in_shop_list,'pur_prds_in_shop_list'=>$pur_prds_in_shop_list]);

    }

    public function getCategorySellers(Request $request){
        //dd($request->all());
        try{
            $final_array = [];
            $first_ar = [];
            $next_array = [];
            foreach ($request->cat_id as $ckey => $cat) {
                $shoIds = \App\ShopAssignCategory::where('category_id',$cat)->pluck('shop_id');
                if($ckey=='0'){
                    $final_array = $first_ar = $shoIds->all();
                }else{
                    $next_array = $shoIds->all();
                    $final_array = array_intersect($first_ar,$next_array);
                    $first_ar = $final_array;
                }
            }

            //dd($first_ar,$next_array, $final_array);


            $shopList = \App\ShopAssignCategory::whereIn('shop_id',$final_array)->select('shop_id')->distinct()->with('shopDesc')->get();
            $seller_data = [];
            foreach ($shopList as $key => $shop) {
                $seller_data[$key]['shop_id'] = $shop->shop_id;
                $seller_data[$key]['shop_name'] = isset($shop->shopDesc->shop_name)?$shop->shopDesc->shop_name:'';
            }
            
            $return = ['status'=>'success', 'seller_data'=>$seller_data];
        }
        catch(Exception $e){
            //echo $e; die;
            $return = ['status'=>'error', 'seller_data'=>[],'message'=>$e->getMessage()];
        }
        return $return;
    }

    public function createNewShoppingList(Request $request){
    	$status = 'error';
    	$message = Lang::get('shopping_list.something_went_wrong');
    	try{
    		if(Auth::check()){
    			//dd(Auth::user()->id);
    			$shoppingObj = new UserShoppingList;
    			$shoppingObj->user_id = Auth::user()->id;
    			if($shoppingObj->save()){
    				$shopping_list_id = $shoppingObj->id;
    				$lang_ids = \App\Language::where('status', '1')->pluck('id');
                	foreach ($lang_ids as $lang_id) {
                		$shoppingDescObj = new UserShoppingListDesc;
                		$shoppingDescObj->lang_id = $lang_id;
	    				$shoppingDescObj->shopping_list_id = $shopping_list_id;
	    				$shoppingDescObj->name = $request->shopping_list_name;
	    				$shoppingDescObj->save();
                	}
                	$status = 'success';
                	$message = Lang::get('shopping_list.new_shopping_list_created_successfully');
    			}
	    	}
	    	$return = ['status'=>$status,'message'=>$message];
    	}
    	catch(Exception $e){
    		$message = $e->getMessage();
    		$return = ['status'=>$status,'message'=>$message];
    	}
    	return $return;
    }

    protected function inserLastOrderItemsInshopping($last_order_id,$shopping_id){
        $itemListData = \App\OrderDetail::where(['order_id'=>$last_order_id,'user_id'=>Auth::user()->id])->with(['product'=>function($prdBadgeQuery){
                $prdBadgeQuery->with(['getbadge']);

        }])->get();
        $lastOrderItemArray = [];
        if(count($itemListData)){
            foreach ($itemListData as $itm_key => $itm) {
                $lastOrderItemArray[$itm_key]['shopping_list_id'] = $shopping_id;
                $lastOrderItemArray[$itm_key]['cat_id'] = $itm->cat_id;
                $lastOrderItemArray[$itm_key]['shop_id'] = $itm->shop_id;
                $lastOrderItemArray[$itm_key]['size'] = isset($itm->product->getbadge->size)?$itm->product->getbadge->size:'';
                $lastOrderItemArray[$itm_key]['grade'] = isset($itm->product->getbadge->grade)?$itm->product->getbadge->grade:'';
                $lastOrderItemArray[$itm_key]['price'] = '';
                $lastOrderItemArray[$itm_key]['qty'] = $itm->quantity;
            }
        }

        \App\UserShoppingListItems::where(['shopping_list_id'=>$shopping_id])->delete();
        \App\UserShoppingListItems::insert($lastOrderItemArray);
    }

    public function getShoppingListItems(Request $request){
    	//dd($request->all());
    	$status = 'error';
    	$message = Lang::get('shopping_list.something_went_wrong');
        $shopping_list_html = '';
        $shoppingListItemStatus = [];
    	try{
    		if(Auth::check()){
                $itemListArray = [];
                $shoppData = UserShoppingList::where(['id'=>$request->shopping_list_id,'user_id'=>Auth::user()->id])->first(); 

                if($shoppData->is_default=='1' && $shoppData->allow_add_product=='0'){
                    $last_order_id = session('last_order_id');
                    $lastOrderData = \App\Order::where('user_id',Auth::user()->id)->orderBy('end_shopping_date','Desc')->first();
                    if(is_null($last_order_id)){
                        if($lastOrderData!==null){
                            $this->inserLastOrderItemsInshopping($lastOrderData->id,$shoppData->id);
                            session(['last_order_id' => $lastOrderData->id]);
                        }
                    }else{
                        if($lastOrderData!==null && $last_order_id!=$lastOrderData->id){
                            $this->inserLastOrderItemsInshopping($lastOrderData->id,$shoppData->id);
                            session(['last_order_id' => $lastOrderData->id]);
                        }
                    }
                }

                $shoppingData = UserShoppingList::where(['id'=>$request->shopping_list_id,'user_id'=>Auth::user()->id])->with(['getShoppingItems'=>function($itemSubQuery){
                            $itemSubQuery->with(['getCatDesc','getCategory']);
                    },'getShoppingDesc'])->first();

                //dd($shoppingData);
                $badgeSize = CustomHelpers::getBadgeSize();
                $badgeGrade = CustomHelpers::getBadgeGrade();
                //dd($badgeSize,$badgeGrade);

                $gradeOption = '<span><select name="grade" class="item_grade" id="item_grade">';
                $sizeOption = '<span><select name="size" class="item_size" id="item_size">';
                foreach($badgeSize as $skey => $size) {
                    $sizeOption .= '<option value="'.$skey.'">'.$size.'</option>'; 
                }

                foreach ($badgeGrade as $gkey => $grade) {
                    $gradeOption .= '<option value="'.$gkey.'">'.$grade.'</option>';
                }
                $sizeOption .= '</select></span>';
                $gradeOption .= "</select></span>";

                if(count($shoppingData->getShoppingItems)){
                    foreach ($shoppingData->getShoppingItems as $key => $item) {
                        
                        if(!is_null($item->shop_id) && $item->shop_id!=''){
                            $shopInfo = \App\ShopDesc::where(['shop_id'=>$item->shop_id,'lang_id'=>session('default_lang')])->select('id','shop_name')->first();
                            $itemListArray[$key]['shop_name'] =  isset($shopInfo->shop_name)?$shopInfo->shop_name:'';
                            $itemListArray[$key]['chatBtn'] =  isset($shopInfo->shop_name)?true:false;
                        }

                        $badge = \App\Badge::select('id')->where(['size'=>$item->size,'grade'=>$item->grade])->first();
                        //dd($badge,$item);
                        $filter_by = isset($badge->id)?'?filter_by='.$badge->id:'';
                        $badgeImg = isset($badge->id)?getBadgeImage($badge->id):'';
                        //dd($badgeImg,$item->size,$item->grade);
                        $itemListArray[$key]['img'] = isset($item->getCategory->img)?$item->getCategory->img:'';
                        $itemListArray[$key]['category_name'] = isset($item->getCatDesc->category_name)?$item->getCatDesc->category_name:'';
                        $itemListArray[$key]['category_url'] = isset($item->getCategory->url)?$item->getCategory->url.$filter_by:'';
                        $itemListArray[$key]['note'] = isset($item->note)?$item->note:'';
                        $itemListArray[$key]['id'] = $item->id;
                        $itemListArray[$key]['prdBadge'] = ($item->grade!='' && $item->size!='')?"<span class='la'><img src='".$badgeImg."'></span>":$gradeOption.$sizeOption;
                        // $itemListArray[$key]['grade'] = ($item->grade!='')?$badgeImg:$gradeOption;
                        // $itemListArray[$key]['size'] = ($item->size!='')?$badgeImg:$sizeOption;
                        $itemListArray[$key]['is_completed'] = $item->is_completed;
                        $itemListArray[$key]['cat_id'] = $item->cat_id;
                        $itemListArray[$key]['grade_key'] = ($item->grade!='')?$item->grade:'';
                        $itemListArray[$key]['size_key'] = ($item->size!='')?$item->size:'';
                        $itemListArray[$key]['price'] = $item->price;
                        $itemListArray[$key]['qty'] = $item->qty;
                    }
                }

                $shopping_list_name = $shoppingData->getShoppingDesc->name;
                $status = 'success';
                $item_list = '';
                
                if(count($itemListArray)){
                    foreach ($itemListArray as $key => $item) {
                        $paidItemHtml = $waitPItemHtml = $bargainItemHtml = $standeredSaveBtn = $completeHtml = $goToPrdList = '';
                        $totalPaidItems = $totalWPItems = $totalBargainItems = 0;


                        
                        //if(strpos($item['grade'], '</select>') !== false){
                        if(strpos($item['prdBadge'], '</select>') !== false){
                            //$standeredSaveBtn = '<a href="javascript://" class="btn-blue go-to-list btn-sm" id="save_standered" data-item_id="'.$item['id'].'" data-shopping_id="'.$shoppingData->id.'" data-cat_id="'.$item['cat_id'].'">'.Lang::get('shopping_list.save').'</a>';
                            $standeredSaveBtn = "";
                        }else{
                            $totalPaidItems = $this->getCartPiadItems($item);
                            // $totalWPItems = $this->getCartWaitingPaymentItems($item); //commented to hide this section
                            // $totalBargainItems = $this->getCartBargainingItems($item); //commented to hide this section
                            //$totalWPItems = 0;
                            //$totalBargainItems = 0;

                            $goToPrdList = '<a href="'.action('ProductsController@category',['url'=>$item['category_url']]).'" class="btn- btn go-to-list btn-sm">'.Lang::get('shopping_list.go_to_product_list').'</a>';
                            $paidItemHtml = $waitPItemHtml = $bargainItemHtml = '';
                            if($totalPaidItems){
                                $paidItemHtml = '<a href="'.action('Checkout\CartController@alreadyPaid').'" class="btn-grey go-to-list btn-sm"> <span class="btn-txt">'.Lang::get('shopping_list.paid_item_list').'</span> <span class="btn-qty">'.$totalPaidItems.'</span> <span class="list-text">'.Lang::get('shopping_list.list').'</span> </a>';
                            }
                            // if($totalWPItems){
                            //     $waitPItemHtml = '<a href="'.action('Checkout\CartController@shoppingCart').'" class="btn-grey go-to-list btn-sm"> <span class="btn-txt">'.Lang::get('shopping_list.waiting_payment_item_list').'</span> <span class="btn-qty">'.$totalWPItems.'</span> <span class="list-text">'.Lang::get('shopping_list.list').'</span> </a>';
                            // }
                            // if($totalBargainItems){
                            //     $bargainItemHtml = '<a href="'.action('User\BargainController@index',['sortby'=>'bytime']).'" class="btn-grey go-to-list btn-sm"> <span class="btn-txt">'.Lang::get('shopping_list.bargaining_item_list').'</span> <span class="btn-qty">'.$totalBargainItems.'</span> <span class="list-text">'.Lang::get('shopping_list.list').'</span> </a>';
                            // }

                            // if($item['is_completed']=='1'){
                            //      $completeHtml = '<i class="fa fa-check skyblue p-2" style="border: 1px solid #C4C4C4;border-radius: 5px;"></i>'.Lang::get('shopping_list.prodcut_completed').'';
                            // }else{
                            //     $completeHtml = '<label class="chk-wrap"><input type="checkbox" class="item_complete" data-id="'.$shoppingData->id.'" data-item_id="'.$item['id'].'"><span class="chk-mark">'.Lang::get('shopping_list.prodcut_complete').'</span></label>';
                            // }
                            
                        }

                        //if($item['price']===null && $totalPaidItems==0 && $totalWPItems==0 && $totalBargainItems==0){
                        if($item['price']===null && $totalPaidItems==0){
                            //$priceSaveBtn = '<a href="javascript://" class="btn-blue go-to-list btn-sm" id="save_price" data-item_id="'.$item['id'].'" >'.Lang::get('shopping_list.save').'</a>';

                            $priceSaveBtn = "";
                            
                            $price_html = '<input type="number" name="price" id="item_price" class="form-elements" value="0">';
                        }else{
                            $priceSaveBtn = '';
                            $price_html = '<input value="'.numberFormat($item['price']).'" readonly class="text-elements" disabled>';

                        }
                        //if($item['qty']===null && $totalPaidItems==0 && $totalWPItems==0 && $totalBargainItems==0){
                        if($item['qty']===null && $totalPaidItems==0){
                            //$priceSaveBtn = '<a href="javascript://" class="btn-blue go-to-list btn-sm" id="save_price" data-item_id="'.$item['id'].'" >'.Lang::get('shopping_list.save').'</a>';

                            $qtySaveBtn = "";
                            
                            $qty_html = '<input type="number" name="qty" id="item_qty" class="form-elements" value="0">';
                        }else{
                            $qtySaveBtn = '';
                            $qty_html = '<input value="'.$item['qty'].'" class="text-elements" disabled >';
                        }

                        $editNoteText = ($item['note']!='')?Lang::get('shopping_list.edit_product_note'):Lang::get('shopping_list.add_product_note');
                        // $action_list = ($item['is_completed'])?'<div class="blk-btn"></div>':'<div class="blk-btn">'.$bargainItemHtml.$waitPItemHtml.$paidItemHtml.$goToPrdList.'</div>';
                        $action_list = ($item['is_completed'])?'<div class="blk-btn"></div>':'<div class="blk-btn">'.$bargainItemHtml.$waitPItemHtml.$paidItemHtml.'</div>';
                        $prod_link = $goToPrdList;

                        //if($totalPaidItems==0 && $totalWPItems==0 && $totalBargainItems==0){
                        if($totalPaidItems==0){
                            $delete_item_btn = '<div class="del-act">
                                                    <a href="javascript://" class="delete_shopp_item" data-item_id="'.$item['id'].'"><i class="fas fa-trash-alt"></i></a>
                                                </div>';
                            //if(strpos($item['prdBadge'], '</select>') !== false || $item['is_completed']=='1'){
                            if(strpos($item['prdBadge'], '</select>') !== false){
                                $edit_badge_btn = '';
                                $edit_price_btn = '';
                            	$edit_qty_btn = '';
                            }else{
                                $edit_badge_btn = '<a href="javascript://" class="btn- btn go-to-list btn-sm edit_standered"  data-item_id="'.$item['id'].'" data-shopping_id="'.$shoppingData->id.'" data-cat_id="'.$item['cat_id'].'">'.Lang::get('shopping_list.edit').'</a>';
                                $edit_price_btn = '<a href="javascript://" class="btn- btn go-to-list btn-sm edit_price"  data-item_id="'.$item['id'].'" data-shopping_id="'.$shoppingData->id.'" data-cat_id="'.$item['cat_id'].'">'.Lang::get('shopping_list.edit').'</a>';
                                $edit_qty_btn = '<a href="javascript://" class="btn- btn go-to-list btn-sm edit_qty"  data-item_id="'.$item['id'].'" data-shopping_id="'.$shoppingData->id.'" data-cat_id="'.$item['cat_id'].'">'.Lang::get('shopping_list.edit').'</a>';
                            }

                            
                        }else{
                            $delete_item_btn = '';
                            $edit_badge_btn = '';
                            $edit_price_btn = '';
                            $edit_qty_btn = '';
                        }

                        // chat button condition
                        $chatBtn = (isset($item['chatBtn']) && $item['chatBtn'])?'<a href="javascript::void(0)" class="btn- btn go-to-list btn-sm">'.Lang::get('shopping_list.seller_chat_btn').'</a>':'';
                        $chatBtn = '';
                        $seller_name = (isset($item['shop_name']) && $item['shop_name']!='') ?' | '.$item['shop_name']:'';
                        // end
                         
                        $item_list .= '<ul id="'.$item['id'].'" data-shopping_id="'.$shoppingData->id.'" data-cat_id="'.$item['cat_id'].'">
                                    <li>
                                        <div class="product">
                                        <span class="numbr-list">'.($key+1).'.</span>
                                        <span class="prod-img"><a href="'.action('ProductsController@category',['url'=>$item['category_url']]).'"><img src="'.getImgUrl($item['img'],'category').'" width="170px" class="prod-img"></a></span>
                                        <span class="product-info">
                                            <div class="nameproduct"><a href="'.action('ProductsController@category',['url'=>$item['category_url']]).'">'.$item['category_name'].$seller_name.'</a></div>
                                            <div class="standard">'.Lang::get('shopping_list.standard').'</div>
                                            <div class="form-group">        
                                                <div class="prod-sizetype">
                                                    '.$item['prdBadge'].'
                                                    '.$standeredSaveBtn.'
                                                    '.$edit_badge_btn.'
                                                </div>
                                            </div>
                                            <div class="price-box icon-append" id="qty_sec">
                                                <label>'.Lang::get('shopping_list.qty').'</label>
                                                <div class="inputgroup-icon">
                                                    '.$qty_html.'
                                                    <span class="fc-inner-iocns right"><span>'.Lang::get('shopping_list.items').'</span></span>
                                                </div>
                                                '.$qtySaveBtn.'
                                                '.$edit_qty_btn.'
                                            </div>
                                            <div class="action-blk mb-2">'.$action_list.'</div>
                                        </span>
                                        </div>
                                    </li>
                                    <!--<li class="add-note text-left">
                                        <p>'.$item['note'].'</p>
                                        <a href="javascript://" class="skyblue" data-toggle="modal" data-target="#edit_note" data-id="'.$shoppingData->id.'" data-item_id="'.$item['id'].'" data-note="'.$item['note'].'">'.$editNoteText.'</a> 
                                    </li>-->
                                    <li>
                                        <div class="action-blk mb-2"><div class="blk-btn">'.$delete_item_btn.$chatBtn.$prod_link.'</div></div>
                                        <div class="form-group complete big-check">'.$completeHtml.'
                                        </div>
                                    </li></ul>';
                    }
                }

                if($shoppData->is_default=='1'){
                    $link_html = '';
                }else{
                    $link_html = '<a href="javascript://" class="skyblue px-sm-0 px-lg-4" data-toggle="modal" data-target="#edit_shopping_list" data-name="'.$shopping_list_name.'" data-id="'.$shoppingData->id.'"><i class="fas fa-edit"></i> '.Lang::get('shopping_list.edit_shopping_list_name').'</a>
                        <a href="javascript://" class="skyblue" id="delete_shopping_list" data-id="'.$shoppingData->id.'"><i class="fas fa-trash-alt"></i> '.Lang::get('shopping_list.delete_this_shopping_list').'</a>';
                }

                $addProductBtn = '';
                if($shoppData->allow_add_product){
                    $addProductBtn = '<button class="btn-grey" data-toggle="modal" data-target="#add_product" data-id="'.$shoppingData->id.'">'.Lang::get('shopping_list.add_product').'</button>';
                }

                $shopping_list_html = '<div class="form-group"><div class="row">
                                <div class="col-md-8 align-items-center edit-shipping">
                                    <span class="sl-title">'.$shopping_list_name.'</span>
                                    '.$link_html.'
                                </div>
                                <div id="shipping_btns" class="col-md-4 text-left text-md-right ">
                                    '.$addProductBtn.'
                                    <button class="btn" id="save_all">'.Lang::get('common.save').'</button>
                                </div>
                            </div>
                        </div>
                        <div class="table-shoplist">
                            <div class="table-responsive track-order-table">
                                <div class="table">
                                    <div class="table-content">
                                        '.$item_list.'
                                        <h2 class="no-product text-center pt-2">'.Lang::get('common.no_product_on_shopping_list').'</h2>
                                    </div>
                                </div>
                            </div>
                        </div>';
                $message = Lang::get('shopping_list.shopping_list_loaded');
                session(['shopping_list' => $request->shopping_list_id]);
	    	}
    	}
    	catch(Exception $e){
    		$message = $e->getMessage();
    	}

        $return = ['status'=>$status,'message'=>$message,'shopping_list_html'=>$shopping_list_html];

    	return $return;
    }

    public function editShoppingListName(Request $request){
        try{
            UserShoppingListDesc::where('shopping_list_id',$request->id)->update(['name'=>$request->name]);
            $status = 'success';
            $message = Lang::get('shopping_list.edit_shopping_list_name_success');
            $return = ['status'=>$status,'message'=>$message];
        }
        catch(Exception $e){
            $status = 'error';
            $message = $e->getMessage();
            $return = ['status'=>$status,'message'=>$message];
        }
        return $return;
    }

    public function deleteShoppingList(Request $request){
        $status = 'error';
        $message = Lang::get('shopping_list.something_went_wrong');
        try{
            $shoppingListItems = \App\UserShoppingListItems::where(['shopping_list_id'=>$request->shopping_list_id])->get();
            $delete_flag = true;

            if(count($shoppingListItems)){
                foreach ($shoppingListItems as $key => $item) {
                    $item['size_key'] = $item['size'];
                    $item['grade_key'] = $item['grade'];
                    $totalPaidItems = $this->getCartPiadItems($item);
                    $totalWPItems = $this->getCartWaitingPaymentItems($item);
                    $totalBargainItems = $this->getCartBargainingItems($item);
                    if($totalPaidItems!==0 || $totalWPItems!==0 || $totalBargainItems!==0){
                        $delete_flag = false;
                    }
                }       
            }

            if($delete_flag){
                if(UserShoppingList::where('id',$request->shopping_list_id)->delete()){
                    //UserShoppingListDesc::where('shopping_list_id',$request->shopping_list_id)->delete();
                    $status = 'success';
                    $message = Lang::get('shopping_list.delete_shopping_list_success');
                }   
            }else{
                $status = 'warning';
                $message = Lang::get('shopping_list.you_can_not_delete_shopping_list');
            }

            $return = ['status'=>$status,'message'=>$message]; 
        }
        catch(Exception $e){
            $message = $e->getMessage();
            $return = ['status'=>$status,'message'=>$message];
        }
        return $return;
    }

    public function addProductToShoppinglist(Request $request){
        try{
            //dd($request->all());
            $inserArray = [];
            foreach ($request->products as $key => $product) {
                $inserArray[$key]['shopping_list_id'] = $request->shopping_list_id;
                $inserArray[$key]['cat_id'] = $product;
                $inserArray[$key]['shop_id'] = isset($request->seller)?$request->seller:'';
            }

            UserShoppingListItems::insert($inserArray);
            $status = 'success';
            $message = Lang::get('shopping_list.add_product_in_shopping_list_success');
            $return = ['status'=>$status,'message'=>$message];
        }
        catch(Exception $e){
            $status = 'error';
            $message = $e->getMessage();
            $return = ['status'=>$status,'message'=>$message];
        }

        return $return;
    }

    public function editNote(Request $request){
        try{
            $itemObj = UserShoppingListItems::where(['id'=>$request->shopping_list_item_id])->first();
            $itemObj->note = $request->note;
            $itemObj->save();
            $status = "success";
            $message = Lang::get('shopping_list.note_update_success');
        }
        catch(Exception $e){
            $status = "error";
            $message = $e->getMessage();
        }

        return ['status'=>$status,'message'=>$message];
    }

    public function saveItemStandered(Request $request){
        try{

            $totalSameItem = UserShoppingListItems::where(['size'=>$request->size,'grade'=>$request->grade,'shopping_list_id'=>$request->shopping_id,'cat_id'=>$request->cat_id])->count();

            if($totalSameItem===0){
                $itemObj = UserShoppingListItems::where(['id'=>$request->item_id])->first();
                $itemObj->grade = $request->grade;
                $itemObj->size = $request->size;
                $itemObj->save();
            }else{
                $itemObj = UserShoppingListItems::where(['id'=>$request->item_id])->delete();
            }

            $status = "success";
            $message = Lang::get('shopping_list.standered_update_success');
        }
        catch(Exception $e){
            $status = "error";
            $message = $e->getMessage();
        }

        return ['status'=>$status,'message'=>$message];
    }

    protected function addProdInShoppingList($request){
        $currentShoppingList = UserShoppingList::where(['user_id'=>Auth::user()->id,'is_default'=>'1','allow_add_product'=>'1'])->select('id')->first();
        if(empty($currentShoppingList)){
            $def_shop_id = $this->createDefaultShoppingList();
        }else{
            $def_shop_id = $currentShoppingList->id;
        }
        
        $current_shopping_list = (session('shopping_list')!==null)?(int)session('shopping_list'):$def_shop_id;
        $badgeData = \App\Badge::where(['id'=>$request->badge_id])->select('size','grade')->first();

        $total = UserShoppingListItems::where(['shopping_list_id'=>$current_shopping_list,'cat_id'=>$request->cat_id,'size'=>$badgeData->size,'grade'=>$badgeData->grade])->count();

        if($total===0){
            $itemObj = new UserShoppingListItems;
            $itemObj->shopping_list_id = $current_shopping_list;
            $itemObj->cat_id = $request->cat_id;
            $itemObj->grade = isset($badgeData->grade)?$badgeData->grade:'';
            $itemObj->size = isset($badgeData->size)?$badgeData->size:'';
            $itemObj->save();
            $status = "success";
            $message = Lang::get('shopping_list.product_added_into_shoplist_successfully');
        }else{
            $status = "error";
            $message = Lang::get('shopping_list.product_already_added_into_shoplist');
        }

        return ['status'=>$status,'message'=>$message];
    }

    public function AddToShoppingList(Request $request){
        $redirect_url = '';
        try{
            // check if last purchased order shopping list not created yet, then create for current user
            // $default_shopping_list = UserShoppingList::where(['user_id'=>Auth::user()->id])->count();
            // if($default_shopping_list===0){
            //     if(isset($request->shopping_list_name)){
            //         $shopping_id = $this->createDefaultShoppingList($request->shopping_list_name);
            //         if($shopping_id){
            //             session(['shopping_list' => $shopping_id]);
            //             $resDada = $this->addProdInShoppingList($request);
            //             $status = $resDada["status"];
            //             $message = $resDada["message"];
            //             $redirect_url = action('User\ShoppinglistController@index');
            //         }
            //     }else{
            //         $status = "no_shopping_list";
            //         $message = Lang::get('shopping_list.no_shopping_list_found');
            //     }
            // }else{
                $resDada = $this->addProdInShoppingList($request);
                $status = $resDada["status"];
                $message = $resDada["message"];
            //}
        }
        catch(Exception $e){
            $status = "error";
            $message = $e->getMessage();
            $redirect_url = '';
        }
        return ['status'=>$status,'message'=>$message,'redirect_url'=>$redirect_url];
    }

    public function createDefaultShoppingList($shopping_list_name=null){
    	$allow_add_product = ($shopping_list_name)?'1':'0';
        $shopping_list_name = ($shopping_list_name!==null)?$shopping_list_name:Lang::get('shopping_list.last_order_items');

        $createShopObj = new UserShoppingList;
        $createShopObj->user_id = Auth::user()->id;
        $createShopObj->is_default = '1';
        $createShopObj->allow_add_product = $allow_add_product;
        $def_shop_id = 0;
        if($createShopObj->save()){
            $def_shop_id = $createShopObj->id;
            $lang_ids = \App\Language::where('status', '1')->pluck('id');
            foreach ($lang_ids as $lang_id) {
                $shoppingDescObj = new UserShoppingListDesc;
                $shoppingDescObj->lang_id = $lang_id;
                $shoppingDescObj->shopping_list_id = $def_shop_id;
                $shoppingDescObj->name = $shopping_list_name;
                $shoppingDescObj->save();
            }
        }
        return $def_shop_id;
    }

    public function completeShoppingItem(Request $request){
        try{
            $itemObj = UserShoppingListItems::where(['id'=>$request->item_id])->first();
            $itemObj->is_completed = '1';
            $itemObj->save();
            $status = "success";
            $message = Lang::get('shopping_list.item_completed_success');
        }
        catch(Exception $e){
            $status = "error";
            $message = $e->getMessage();
        }

        return ['status'=>$status,'message'=>$message];
    }

    public function deleteShoppingItem(Request $request){
        try{
            $itemObj = UserShoppingListItems::where(['id'=>$request->item_id])->delete();
            $status = "success";
            $message = Lang::get('shopping_list.delete_shopping_item_success');
        }
        catch(Exception $e){
            $status = "error";
            $message = $e->getMessage();
        }

        return ['status'=>$status,'message'=>$message];
    }

    protected function getCartPiadItems($item){
        //dd($item);
        try{
            //DB::enableQueryLog();
            $cat_id = $item['cat_id'];
            $data = DB::table(with(new \App\OrderDetail)->getTable().' as ordd')
                ->join(with(new \App\Order)->getTable().' as ord', 'ord.id', '=', 'ordd.order_id')
                ->leftJoin(with(new \App\Product)->getTable().' as prd', 'ordd.product_id', '=', 'prd.id')
                ->leftJoin(with(new \App\Badge)->getTable().' as bdg', 'prd.badge_id', '=', 'bdg.id')
                ->where('ordd.user_id',Auth::user()->id)
                ->where('prd.cat_id',$cat_id)
                ->where(['bdg.size'=>$item['size_key']])
                ->where(['bdg.grade'=>$item['grade_key']])
                ->whereNull('ord.end_shopping_date')
                ->select('ordd.*')
                ->get();
            //dd(DB::getQueryLog()); 
            $return = count($data);
        }
        catch(Exception $e){
            $return = 0;
        }
        return $return;
    }

    protected function getCartWaitingPaymentItems($item){
        //dd($item);
        try{
            //DB::enableQueryLog();
            $cat_id = $item['cat_id'];
            $data = DB::table(with(new \App\Cart)->getTable().' as crt')
                ->leftJoin(with(new \App\Product)->getTable().' as prd', 'crt.product_id', '=', 'prd.id')
                ->leftJoin(with(new \App\Badge)->getTable().' as bdg', 'prd.badge_id', '=', 'bdg.id')
                ->where('crt.user_id',Auth::user()->id)
                ->where('prd.cat_id',$cat_id)
                ->where(['bdg.size'=>$item['size_key']])
                ->where(['bdg.grade'=>$item['grade_key']])
                ->select('crt.id')
                ->get();
            //dd(DB::getQueryLog()); 
            $return = count($data);
        }
        catch(Exception $e){
            $return = 0;
        }
        return $return;
    }

    protected function getCartBargainingItems($item){
        //dd($item);
        try{
            //DB::enableQueryLog();
            $cat_id = $item['cat_id'];
            $data = DB::table(with(new \App\ProductBargain)->getTable().' as pbar')
                ->leftJoin(with(new \App\Product)->getTable().' as prd', 'pbar.product_id', '=', 'prd.id')
                ->leftJoin(with(new \App\Badge)->getTable().' as bdg', 'prd.badge_id', '=', 'bdg.id')
                ->where('pbar.user_id',Auth::user()->id)
                ->where('prd.cat_id',$cat_id)
                ->where(['bdg.size'=>$item['size_key']])
                ->where(['bdg.grade'=>$item['grade_key']])
                ->select('pbar.*')
                ->get();
                //dd(DB::getQueryLog()); 
                $return = count($data);
        }
        catch(Exception $e){
            $return = 0;
        }
        return $return;
    }

    public function checkShoppingListLoadingStatus(Request $request){
        $status = 'error';
        $message = Lang::get('shopping_list.something_went_wrong');
        $loading_flag = true;
        try{
            if(Auth::check()){
                $shopping_list = session('shopping_list');
                $shoppingData = UserShoppingList::where(['id'=>$shopping_list,'user_id'=>Auth::user()->id])->with(['getShoppingItems','getShoppingDesc'])->first()->toArray();

                if(count($shoppingData['get_shopping_items'])){
                    foreach ($shoppingData['get_shopping_items'] as $key => $item) {
                        $item['size_key'] = $item['size'];
                        $item['grade_key'] = $item['grade'];
                        if(!empty($item['size']) && !empty($item['grade'])){
                            $totalPaidItems = $this->getCartPiadItems($item);
                            $totalWPItems = $this->getCartWaitingPaymentItems($item);
                            $totalBargainItems = $this->getCartBargainingItems($item);
                        }else{
                            $totalPaidItems = $totalWPItems = $totalBargainItems = 0;
                        }
                        
                        // if($totalPaidItems!==0 || $totalWPItems!==0 || $totalBargainItems!==0){
                        //     $loading_flag = false;
                        //     $message = Lang::get('shopping_list.you_can_not_change_shopping_list');
                        // }
                    }
                }
            }
            $status = 'success';
        }
        catch(Exception $e){
            $message = $e->getMessage();
        }

        $return = ['status'=>$status,'message'=>$message,'loading_flag'=>$loading_flag];
        return $return;
    }

    public function saveItemPrice(Request $request){
        try{
            $itemObj = UserShoppingListItems::where(['id'=>$request->item_id])->first();
            $itemObj->price = $request->price;
            $itemObj->save();
            $status = "success";
            $message = Lang::get('shopping_list.price_update_success');
        }
        catch(Exception $e){
            $status = "error";
            $message = $e->getMessage();
        }

        return ['status'=>$status,'message'=>$message];
    }

    public function saveItemQty(Request $request){
        try{
            $itemObj = UserShoppingListItems::where(['id'=>$request->item_id])->first();
            $itemObj->qty = $request->qty;
            $itemObj->save();
            $status = "success";
            $message = Lang::get('shopping_list.qty_update_success');
        }
        catch(Exception $e){
            $status = "error";
            $message = $e->getMessage();
        }

        return ['status'=>$status,'message'=>$message];
    }

    public function saveAllItem(Request $request){
         try{
            //dd($request->all());
            foreach ($request->req as $key => $data) {

                //$totalSameItem = UserShoppingListItems::where(['size'=>$data['item_size'],'grade'=>$data['item_grade'],'shopping_list_id'=>$data['shopping_id'],'cat_id'=>$data['cat_id']])->count();
                //$totalSameItem = UserShoppingListItems::where('id',$data->item_id)->count();

                // if($totalSameItem===0){
                //     $itemObj = UserShoppingListItems::where(['id'=>$data['item_id']])->first();
                //     $itemObj->grade = $data['item_grade'];
                //     $itemObj->size = $data['item_size'];
                //     $itemObj->price = $data['item_price'];
                //     $itemObj->save();
                // }else{
                //     $itemObj = UserShoppingListItems::where(['id'=>$data['item_id']])->delete();
                // }

                $itemObj = UserShoppingListItems::where(['id'=>$data['item_id']])->first();
                if(isset($data['item_grade'])){
                    $itemObj->grade = $data['item_grade'];
                }
                if(isset($data['item_size'])){
                    $itemObj->size = $data['item_size'];
                }
                if(isset($data['item_price'])){
                    $itemObj->price = $data['item_price'];
                }

                if(isset($data['item_qty'])){
                    $itemObj->qty = $data['item_qty'];
                }
                
                $itemObj->save();
            }

            $status = "success";
            $message = Lang::get('shopping_list.shopping_list_items_saved_successfully');;
        }
        catch(Exception $e){
            $status = "error";
            $message = $e->getMessage();
        }

        return ['status'=>$status,'message'=>$message];
    }

    public function editBadge(Request $request){
        try{
            $badgeSize = CustomHelpers::getBadgeSize();
            $badgeGrade = CustomHelpers::getBadgeGrade();
            //dd($badgeSize,$badgeGrade);
            $prdData = \App\UserShoppingListItems::select('size','grade')->where('id',$request->item_id)->first();
            
            $gradeOption = '<span><select name="grade" class="item_grade" id="item_grade">';
            $sizeOption = '<span><select name="size" class="item_size" id="item_size">';

            foreach($badgeSize as $skey => $size) {
                $selected = ($skey==$prdData->size)?'selected="selected"':'';
                $sizeOption .= '<option value="'.$skey.'" '.$selected.'>'.$size.'</option>'; 
            }

            foreach ($badgeGrade as $gkey => $grade) {
                $selected = ($gkey==$prdData->grade)?'selected="selected"':'';
                $gradeOption .= '<option value="'.$gkey.'"  '.$selected.'>'.$grade.'</option>';
            }
            $sizeOption .= '</select></span>';
            $gradeOption .= "</select></span>";
            $data_html = $gradeOption.''.$sizeOption;
            $status = "success";
        }
        catch(Exception $e){
            $status = "error";
            $data_html = '';
        }

        return ['status'=>$status,'data'=>$data_html];
    }

    public function editPrice(Request $request){
        try{
            $prdData = \App\UserShoppingListItems::select('price')->where('id',$request->item_id)->first();
            $price_html = '
            <label>'.Lang::get('shopping_list.price').'</label>
            <div class="inputgroup-icon">
                <input type="number" name="price" id="item_price" class="form-elements" value="'.$prdData->price.'"><span class="fc-inner-iocns right">
                <span>'.Lang::get('shopping_list.currency').'</span></span>
            </div>';
            $status = "success";
        }
        catch(Exception $e){
            $status = "error";
            $price_html = $item_price;
        }

        return ['status'=>$status,'data'=>$price_html];
    }

    public function editQty(Request $request){
        try{
            $prdData = \App\UserShoppingListItems::select('qty')->where('id',$request->item_id)->first();
            $qtye_html = '
            <label>'.Lang::get('shopping_list.qty').'</label>
            <div class="inputgroup-icon">
                <input type="number" name="qty" id="item_qty" class="form-elements" value="'.$prdData->qty.'">
                <span class="fc-inner-iocns right"><span>'.Lang::get('shopping_list.items').'</span></span>

            </div>';
            $status = "success";
        }
        catch(Exception $e){
            $status = "error";
            $qtye_html = $item_qty;
        }

        return ['status'=>$status,'data'=>$qtye_html];
    }
           
}