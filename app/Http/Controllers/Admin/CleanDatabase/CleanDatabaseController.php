<?php
    
namespace App\Http\Controllers\Admin\CleanDatabase;

use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use App\Http\Controllers\MarketPlace;

use Lang;
use File;
use Config;
use Schema;
use App\Product;
use App\SystemConfig;

class CleanDatabaseController extends MarketPlace
{
    public function __construct()
    {
        $this->middleware('admin.user');        
    }
    
    public function cleanDatabase(Request $request) { 

        // $storage_path = storage_path();
        // File::cleanDirectory($storage_path.'/app/public');
        // File::cleanDirectory($storage_path.'/framework/cache');
        // File::cleanDirectory($storage_path.'/framework/sessions');
        // File::cleanDirectory($storage_path.'/framework/views');
        // File::cleanDirectory($storage_path.'/logs');
        // dd('ok');     

        dd('Please confirm first');

        $prefix =  DB::getTablePrefix();              
        DB::statement('SET FOREIGN_KEY_CHECKS=0');

        $banner_path = Config('constants.banner_path');
        $blog_path = Config('constants.blog_path');
        $blog_cat_icon = $blog_path.'/cat_icon';
        $product_spec_path = Config('constants.product_spec_path');
        $product_path = Config('constants.product_path');
        $user_file_path = Config('constants.user_path');
        $media_manager_path = Config('constants.media_manager_path');
        $buyer_payment_path = Config('constants.buyer_payment_path');
        $cart_option_path = Config('constants.cart_option_path');
        $color_path = Config('constants.color_path');
        $comment_file_path = Config('constants.comment_file_path');
        $csv_path = Config('constants.csv_path');
        $remind_path = Config('constants.remind_path');
        $customer_path = Config('constants.customer_path');

        //dd($media_manager_path);        


        // All Tables to be truncate 
        if(Schema::hasTable((new \App\AdminCustomer)->getTable())){
            \App\AdminCustomer::truncate();
        }
        
        if(Schema::hasTable((new \App\AdminCustomerPermission)->getTable())){
            \App\AdminCustomerPermission::truncate();
        }
        
        if(Schema::hasTable((new \App\AdminOrder)->getTable())){
            \App\AdminOrder::truncate();
        }
        
        if(Schema::hasTable((new \App\AdminOrderPermission)->getTable())){
            \App\AdminOrderPermission::truncate();
        }
        
        if(Schema::hasTable((new \App\AdminProduct)->getTable())){
            \App\AdminProduct::truncate();
        }
        
        if(Schema::hasTable((new \App\AdminProductPermission)->getTable())){
            \App\AdminProductPermission::truncate();
        }
            
        if(Schema::hasTable((new \App\AdminRmaConfig)->getTable())){
            \App\AdminRmaConfig::truncate();
        }
        
        if(Schema::hasTable((new \App\AdminRmaConfigDesc)->getTable())){
            \App\AdminRmaConfigDesc::truncate();
        }

        if(Schema::hasTable((new \App\AdminUser)->getTable())){
            $adminUser = new \App\AdminUser;
            $adminUser->where('admin_level', '!=', '-1')->delete();
            $totalDefaultAdminList = $adminUser->where('admin_level', '1')->get();
            $adminUserTableName = $adminUser->getTable();
            $this->setAutoIncrementIdForTable($adminUserTableName,$totalDefaultAdminList);
        }
             
        if(Schema::hasTable((new \App\Warehouse)->getTable())){
            $wareHouseObj = new \App\Warehouse;
            $wareHouseObj->where('online_store', '!=', 'YES')->delete();
            $totalDefaultWareHouseList = $wareHouseObj->where('online_store', 'YES')->get();
            $wareHouseTableName = $wareHouseObj->getTable();
            $this->setAutoIncrementIdForTable($wareHouseTableName,$totalDefaultWareHouseList);

            $updatedTotalDefaultWareHouseList = $wareHouseObj->where('online_store', 'YES')->get()->toArray();       
        }

        if(Schema::hasTable((new \App\WarehouseDesc)->getTable())){

            $wareHouseDescObj = new \App\WarehouseDesc;
            $wareHouseDescObj->whereNotIn('warehouse_id', $totalDefaultWareHouseList)->delete();
            $wareHouseDescList = $wareHouseDescObj->get();
            $wareHouseDescTableName = $wareHouseDescObj->getTable();

            $this->setOldIndexesInDescTable($wareHouseDescTableName,$wareHouseDescList,'warehouse_id',$totalDefaultWareHouseList,$updatedTotalDefaultWareHouseList);
        }                
        
        if(Schema::hasTable((new \App\Attribute)->getTable())){
            \App\Attribute::truncate();
        }
        
        if(Schema::hasTable((new \App\AttributeDesc)->getTable())){
            \App\AttributeDesc::truncate();
        }
        
        if(Schema::hasTable((new \App\AttributeValue)->getTable())){
            \App\AttributeValue::truncate();
        }

        if(Schema::hasTable((new \App\AttributeValueDesc)->getTable())){
            \App\AttributeValueDesc::truncate();
        }         

        if(Schema::hasTable((new \App\AttributeSet)->getTable())){

            $AttributeSetObj = new \App\AttributeSet;
            $AttributeSetObj->where('is_default', '!=', '1')->delete();
            $defaultAttrSetList = $AttributeSetObj->where('is_default', '1')->pluck('id');
            $totalDefaultAttributeSetList = $AttributeSetObj->where('is_default', '1')->get();
            $AttributeSetTableName = $AttributeSetObj->getTable();
            $this->setAutoIncrementIdForTable($AttributeSetTableName,$totalDefaultAttributeSetList);
            $updatedTotalDefaultAttributeSetList = $AttributeSetObj->where('is_default', '1')->get()->toArray();

            $this->removeMediaFromDir($remind_path);
        }
        
        if(Schema::hasTable((new \App\AttributeSetDetail)->getTable())){

            $AttributeSetDetailObj = new \App\AttributeSetDetail;
            $AttributeSetDetailObj->whereNotIn('attr_set_id', $defaultAttrSetList)->delete();
            $AttributeSetDetailList = $AttributeSetDetailObj->get();
            $AttributeSetDetailTableName = $AttributeSetDetailObj->getTable();
           
            $this->setOldIndexesInDescTable($AttributeSetDetailTableName,$AttributeSetDetailList,'attr_set_id',$totalDefaultAttributeSetList,$updatedTotalDefaultAttributeSetList);
        }           
        /*    
        if(Schema::hasTable((new \App\Banner)->getTable())){

            \App\Banner::truncate();
            $this->removeMediaFromDir($banner_path);
        } 
            
        if(Schema::hasTable((new \App\BannerDesc)->getTable())){
            \App\BannerDesc::truncate();
        }
        
        if(Schema::hasTable((new \App\BannerGroup)->getTable())){
            \App\BannerGroup::truncate();
        }

        if(Schema::hasTable((new \App\Block)->getTable())){

            $BlockObj = new \App\Block;
            $BlockObj->where('is_fix', '!=', '1')->delete();
            $defaultBlockList = $BlockObj->where('is_fix', '1')->pluck('id');
            $totalDefaultBlockList = $BlockObj->where('is_fix', '1')->get();
            $BlockTableName = $BlockObj->getTable();
            $this->setAutoIncrementIdForTable($BlockTableName,$totalDefaultBlockList);
        }

        if(Schema::hasTable((new \App\BlockCustomerGroup)->getTable())){
            \App\BlockCustomerGroup::truncate();
        }                
   
        if(Schema::hasTable((new \App\BlockPage)->getTable())){
            \App\BlockPage::truncate();
        }
        */
            
        if(Schema::hasTable((new \App\Blog)->getTable())){
            \App\Blog::truncate();
        }
        
        if(Schema::hasTable((new \App\BlogDesc)->getTable())){
            \App\BlogDesc::truncate();
        }
        
        if(Schema::hasTable((new \App\BlogCat)->getTable())){
            \App\BlogCat::truncate();
        }
            
        if(Schema::hasTable((new \App\BlogImages)->getTable())){

            \App\BlogImages::truncate();
            $this->removeMediaFromDir($blog_path);
        }

        if(Schema::hasTable((new \App\BlogCategory)->getTable())){

            $BlogCategoryObj = new \App\BlogCategory;
            $BlogCategoryObj->where('is_default', '!=', '1')->delete();
            $defaultBlogCategoryList = $BlogCategoryObj->where('is_default', '1')->pluck('id');
            $totalDefaultBlogCategoryList = $BlogCategoryObj->where('is_default', '1')->get();
            $BlogCategoryTableName = $BlogCategoryObj->getTable();
            $this->setAutoIncrementIdForTable($BlogCategoryTableName,$totalDefaultBlogCategoryList);
            $updatedTotalDefaultBlogCategoryList = $BlogCategoryObj->where('is_default', '1')->get()->toArray();
        }
            
        if(Schema::hasTable((new \App\BlogCategoryDesc)->getTable())){

            $BlogCategoryDescObj = new \App\BlogCategoryDesc;
            $BlogCategoryDescObj->whereNotIn('cat_id', $defaultBlogCategoryList)->delete();
            $BlogCategoryDescList = $BlogCategoryDescObj->get();
            $BlogCategoryDescTableName = $BlogCategoryDescObj->getTable();
            
            $this->setOldIndexesInDescTable($BlogCategoryDescTableName,$BlogCategoryDescList,'cat_id',$totalDefaultBlogCategoryList,$updatedTotalDefaultBlogCategoryList);

            $this->removeMediaFromDir($blog_cat_icon);
        }        
             
        if(Schema::hasTable((new \App\BlogPin)->getTable())){
            \App\BlogPin::truncate();
        }
        
        if(Schema::hasTable((new \App\BlogTags)->getTable())){
            \App\BlogTags::truncate();
        }
        
        if(Schema::hasTable((new \App\Cart)->getTable())){
            \App\Cart::truncate();
        }

        if(Schema::hasTable((new \App\CartAttribute)->getTable())){
            \App\CartAttribute::truncate();
        }

        if(Schema::hasTable((new \App\Category)->getTable())){

            $CategoryObj = new \App\Category;
            $CategoryObj->where('is_default', '!=', '1')->delete();
            $CategoryObj->where('is_default','1')->update(['total_products'=>0]);
            $defaultCategoryList = $CategoryObj->where('is_default', '1')->pluck('id');
            $totalDefaultCategoryList = $CategoryObj->where('is_default', '1')->get();
            $CategoryTableName = $CategoryObj->getTable();
            $this->setAutoIncrementIdForTable($CategoryTableName,$totalDefaultCategoryList);
            $updatedTotalDefaultCategoryList = $CategoryObj->where('is_default', '1')->get()->toArray();
        }
        
        if(Schema::hasTable((new \App\CategoryDesc)->getTable())){

            $CategoryDescObj = new \App\CategoryDesc;
            $CategoryDescObj->whereNotIn('cat_id', $defaultCategoryList)->delete();
            $CategoryDescList = $CategoryDescObj->get();
            $CategoryDescTableName = $CategoryDescObj->getTable();
            
            $this->setOldIndexesInDescTable($CategoryDescTableName,$CategoryDescList,'cat_id',$totalDefaultCategoryList,$updatedTotalDefaultCategoryList);
        }        
            
        if(Schema::hasTable((new \App\CustomerAttribute)->getTable())){
            \App\CustomerAttribute::truncate();
        }
        
        if(Schema::hasTable((new \App\CustomerAttributeDesc)->getTable())){
            \App\CustomerAttributeDesc::truncate();
        }
        
        if(Schema::hasTable((new \App\CustomerAttrValue)->getTable())){
            \App\CustomerAttrValue::truncate();
        }
        
        if(Schema::hasTable((new \App\CustomerAttrValueDesc)->getTable())){
            \App\CustomerAttrValueDesc::truncate();
        }

        if(Schema::hasTable((new \App\CustomerGroup)->getTable())){

            $CustomerGroupObj = new \App\CustomerGroup;
            $CustomerGroupObj->where('type', '!=', '0')->delete();
            $defaultCustomerGroupList = $CustomerGroupObj->where('type', '0')->pluck('id');
            $totalDefaultCustomerGroupList = $CustomerGroupObj->where('type', '0')->get();
            $CustomerGroupTableName = $CustomerGroupObj->getTable();
            $this->setAutoIncrementIdForTable($CustomerGroupTableName, $totalDefaultCustomerGroupList);
            $updatedTotalDefaultCustomerGroupList = $CustomerGroupObj->where('type', '0')->get()->toArray();
        }
   
        if(Schema::hasTable((new \App\CustomerGroupDesc)->getTable())){

            $CustomerGroupDescObj = new \App\CustomerGroupDesc;
            $CustomerGroupDescObj->whereNotIn('group_id', $defaultCustomerGroupList)->delete();
            $CustomerGroupDescList = $CustomerGroupDescObj->get();
            $CustomerGroupDescTableName = $CustomerGroupDescObj->getTable();
            

             $this->setOldIndexesInDescTable($CustomerGroupDescTableName,$CustomerGroupDescList,'group_id',$totalDefaultCustomerGroupList,$updatedTotalDefaultCustomerGroupList);
        }        
        
        if(Schema::hasTable((new \App\Logs)->getTable())){
            \App\Logs::truncate();
        }

        if(Schema::hasTable((new \App\MegaMenu)->getTable())){

            $MegaMenuObj = new \App\MegaMenu;
            $MegaMenuObj->where('default_menu', '!=', '1')->delete();
            $defaultMegaMenuList = $MegaMenuObj->where('default_menu', '1')->pluck('id');
            $totalDefaultMegaMenuList = $MegaMenuObj->where('default_menu', '1')->get();
            $MegaMenuTableName = $MegaMenuObj->getTable();
            $this->setAutoIncrementIdForTable($MegaMenuTableName,$totalDefaultMegaMenuList);
        }
        
        // if(Schema::hasTable('megamenutype')){
        //     DB::table('megamenutype')->truncate();
        // }

        if(Schema::hasTable((new \App\MenusPermission)->getTable())){
            \App\MenusPermission::truncate();
        }

        if(Schema::hasTable((new \App\NewsletterAttribute)->getTable())){
             
            $NewsletterAttributeObj = new \App\NewsletterAttribute;
            $NewsletterAttributeObj->where('is_default','!=','1')->delete();
            $defaultNewsletterAttributeList = $NewsletterAttributeObj->where('is_default','1')->pluck('id');
            $totalDefaultNewsletterAttributeList = $NewsletterAttributeObj->where('is_default', '1')->get();
            $NewsletterAttributeTableName = $NewsletterAttributeObj->getTable();
            $this->setAutoIncrementIdForTable($NewsletterAttributeTableName,$totalDefaultNewsletterAttributeList);

            $updatedTotalDefaultNewsletterAttributeList = $NewsletterAttributeObj->where('is_default', '1')->get()->toArray();
        }

        if(Schema::hasTable((new \App\NewsletterAttributeDesc)->getTable())){

            $NewsletterAttributeDescObj = new \App\NewsletterAttributeDesc;
            $NewsletterAttributeDescObj->whereNotIn('news_attr_id', $defaultNewsletterAttributeList)->delete();
            $NewsletterAttributeDescList = $NewsletterAttributeDescObj->get();
            $NewsletterAttributeDescTableName = $NewsletterAttributeDescObj->getTable();
            
            $this->setOldIndexesInDescTable($NewsletterAttributeDescTableName,$NewsletterAttributeDescList,'news_attr_id',$totalDefaultNewsletterAttributeList,$updatedTotalDefaultNewsletterAttributeList);
        }
            
        if(Schema::hasTable((new \App\NewsletterAttrValue)->getTable())){

            $NewsletterAttrValueObj = new \App\NewsletterAttrValue;
            $NewsletterAttrValueObj->whereNotIn('news_attr_id', $defaultNewsletterAttributeList)->delete();
            $defaultNewsletterAttrValueList = $NewsletterAttrValueObj->whereIn('news_attr_id', $defaultNewsletterAttributeList)->pluck('id');
            $totalDefaultNewsletterAttrValueList = $NewsletterAttrValueObj->where('is_default', '1')->get();
            $NewsletterAttrValueTableName = $NewsletterAttrValueObj->getTable();
            $this->setAutoIncrementIdForTable($NewsletterAttrValueTableName,$totalDefaultNewsletterAttrValueList);
            $updatedTotalDefaultNewsletterAttrValueList = $NewsletterAttrValueObj->where('is_default', '1')->get()->toArray();
            $this->setOldIndexesInDescTable($NewsletterAttrValueTableName,$totalDefaultNewsletterAttrValueList,'news_attr_id',$totalDefaultNewsletterAttributeList,$updatedTotalDefaultNewsletterAttributeList);
        }
        
        if(Schema::hasTable((new \App\NewsletterAttrValueDesc)->getTable())){

            $NewsletterAttrValueDescObj = new \App\NewsletterAttrValueDesc;
            $NewsletterAttrValueDescObj->whereNotIn('news_attr_val_id', $defaultNewsletterAttrValueList)->delete();
            $NewsletterAttrValueDescList = $NewsletterAttrValueDescObj->get();
            $NewsletterAttrValueDescTableName = $NewsletterAttrValueDescObj->getTable();
            
            $this->setOldIndexesInDescTable($NewsletterAttrValueDescTableName,$NewsletterAttrValueDescList,'news_attr_val_id',$totalDefaultNewsletterAttrValueList,$updatedTotalDefaultNewsletterAttrValueList);
        }                
        
        if(Schema::hasTable((new \App\NewsletterSubscriber)->getTable())){
            \App\NewsletterSubscriber::truncate();
        }
        
        if(Schema::hasTable((new \App\NewsletterSubscriberDetail)->getTable())){
            \App\NewsletterSubscriberDetail::truncate();
        }
        
        if(Schema::hasTable((new \App\Orders)->getTable())){
            \App\Orders::truncate();
        }
        
        if(Schema::hasTable((new \App\OrdersTemp)->getTable())){
            \App\OrdersTemp::truncate();
        }

        if(Schema::hasTable((new \App\OrderAttribute)->getTable())){
            \App\OrderAttribute::truncate();
        }
        
        if(Schema::hasTable((new \App\OrderDetail)->getTable())){
            \App\OrderDetail::truncate();
        }
        
        if(Schema::hasTable((new \App\OrderInventory)->getTable())){
            \App\OrderInventory::truncate();
        }
            
        if(Schema::hasTable((new \App\OrderInvoice)->getTable())){
            \App\OrderInvoice::truncate();
        } 
        
        if(Schema::hasTable((new \App\OrderInvoiceDetail)->getTable())){
            \App\OrderInvoiceDetail::truncate();
        }
        
        if(Schema::hasTable((new \App\OrderOfflinePayment)->getTable())){

            \App\OrderOfflinePayment::truncate();
            $this->removeMediaFromDir($buyer_payment_path);
        }
            
        if(Schema::hasTable((new \App\OrderPromotion)->getTable())){
            \App\OrderPromotion::truncate();
        }
        
        if(Schema::hasTable((new \App\OrderRMA)->getTable())){
            \App\OrderRMA::truncate();
        }
        
        if(Schema::hasTable((new \App\OrderRMADetail)->getTable())){
            \App\OrderRMADetail::truncate();
        }
        
        if(Schema::hasTable((new \App\OrderShipment)->getTable())){
            \App\OrderShipment::truncate();
        }
        
        if(Schema::hasTable((new \App\OrderShipmentTracking)->getTable())){
            \App\OrderShipmentTracking::truncate();
        }
        
        if(Schema::hasTable((new \App\OrderTempPromotion)->getTable())){
            \App\OrderTempPromotion::truncate();
        }
        
        if(Schema::hasTable((new \App\OrderTransaction)->getTable())){
            \App\OrderTransaction::truncate();
        }
        
        if(Schema::hasTable('password_resets')){
            DB::table('password_resets')->truncate();
        }

        if(Schema::hasTable((new \App\PaymentOption)->getTable())){

            $PaymentOptionObj = new \App\PaymentOption;
            $PaymentOptionObj->where('source','modules')->delete();
            $totalPaymentOptionList = $PaymentOptionObj->get();
            
            $defaultPaymentOptionList = $PaymentOptionObj->where('source', '!=','modules')->pluck('id');
            $PaymentOptionTableName = $PaymentOptionObj->getTable();
            $this->setAutoIncrementIdForTable($PaymentOptionTableName,$totalPaymentOptionList);
            $updatedTotalPaymentOptionList = $PaymentOptionObj->get();
        }

        if(Schema::hasTable((new \App\PaymentOptionDesc)->getTable())){

            $PaymentOptionDescObj = new \App\PaymentOptionDesc;
            
            $PaymentOptionDescObj->whereNotIn('payment_option_id', $defaultPaymentOptionList)->delete();
            $PaymentOptionDescList = $PaymentOptionDescObj->get();
            $PaymentOptionDescTableName = $PaymentOptionDescObj->getTable();
            
            $this->setOldIndexesInDescTable($PaymentOptionDescTableName,$PaymentOptionDescList,'payment_option_id',$totalPaymentOptionList,$updatedTotalPaymentOptionList);
        }
        
        if(Schema::hasTable((new \App\Product)->getTable())){

            \App\Product::truncate();

            $this->removeMediaFromDir($product_spec_path);
            $this->removeMediaFromDir($product_path);            
        }
        
        if(Schema::hasTable((new \App\ProductAttribute)->getTable())){
            \App\ProductAttribute::truncate();
        }
        
        if(Schema::hasTable((new \App\ProductAttributeValues)->getTable())){
            \App\ProductAttributeValues::truncate();
        }
        
        if(Schema::hasTable((new \App\ProductCat)->getTable())){
            \App\ProductCat::truncate();
        }
        
        if(Schema::hasTable((new \App\ProductDesc)->getTable())){
            \App\ProductDesc::truncate();
        }
        
        if(Schema::hasTable((new \App\ProductImage)->getTable())){
            \App\ProductImage::truncate();
        }
        
        // if(Schema::hasTable((new \App\ProductPrice)->getTable())){
        //     \App\ProductPrice::truncate();
        // }
        
        if(Schema::hasTable((new \App\ProductRelatedBundle)->getTable())){
            \App\ProductRelatedBundle::truncate();
        }
        
        if(Schema::hasTable((new \App\ProductRequirement)->getTable())){
            \App\ProductRequirement::truncate();
        }
        
        if(Schema::hasTable((new \App\ProductRequirementValue)->getTable())){
            \App\ProductRequirementValue::truncate();
        }
        
        if(Schema::hasTable((new \App\ProductTag)->getTable())){
            \App\ProductTag::truncate();
        }
        
        if(Schema::hasTable((new \App\ProductTireBundlePrice)->getTable())){
            \App\ProductTireBundlePrice::truncate();
        }
        
        if(Schema::hasTable((new \App\ProductVideo)->getTable())){
            \App\ProductVideo::truncate();
        }
        
        if(Schema::hasTable((new \App\ProductWarehouse)->getTable())){
            \App\ProductWarehouse::truncate();
        }

        if(Schema::hasTable((new \App\PromotionforSelecedProduct)->getTable())){
            \App\PromotionforSelecedProduct::truncate();
        }        

        if(Schema::hasTable((new \App\PromotionRule)->getTable())){
            \App\PromotionRule::truncate();
        }
        
        if(Schema::hasTable((new \App\PromotionRuleCoupon)->getTable())){
            \App\PromotionRuleCoupon::truncate();
        }
        
        if(Schema::hasTable((new \App\PromotionRuleCustomerGroup)->getTable())){
            \App\PromotionRuleCustomerGroup::truncate();
        }
        
        if(Schema::hasTable((new \App\PromotionRuleDesc)->getTable())){
            \App\PromotionRuleDesc::truncate();
        }
        
        if(Schema::hasTable((new \App\PushEmailNotification)->getTable())){
            \App\PushEmailNotification::truncate();
        }

        if(Schema::hasTable((new \App\Role)->getTable())){

            $RoleObj = new \App\Role;
            $RoleObj->where('id', '!=', '0')->delete();
            $defaultRoleList = $RoleObj->where('id', '0')->pluck('id');
        }
        
        if(Schema::hasTable((new \App\RoleDepartment)->getTable())){

            $RoleDepartmentObj = new \App\RoleDepartment;
            $RoleDepartmentObj->whereNotIn('role_id', $defaultRoleList)->delete();
            $RoleDepartmentList = $RoleDepartmentObj->get();
            $RoleDepartmentTableName = $RoleDepartmentObj->getTable();
            $this->setAutoIncrementIdForTable($RoleDepartmentTableName,$RoleDepartmentList);
        }        
        
        if(Schema::hasTable((new \App\ShippingAddress)->getTable())){
            \App\ShippingAddress::truncate();
        }
        
        if(Schema::hasTable((new \App\ShippingAddressGuest)->getTable())){
            \App\ShippingAddressGuest::truncate();
        }
        
        if(Schema::hasTable((new \App\ShippingProfile)->getTable())){
            \App\ShippingProfile::truncate();
        }
        
        if(Schema::hasTable((new \App\ShippingProfileCountry)->getTable())){
            \App\ShippingProfileCountry::truncate();
        }
        
        if(Schema::hasTable((new \App\ShippingProfileDesc)->getTable())){
            \App\ShippingProfileDesc::truncate();
        }
        
        if(Schema::hasTable((new \App\ShipppingProfileProduct)->getTable())){
            \App\ShipppingProfileProduct::truncate();
        }
        
        if(Schema::hasTable((new \App\ShipppingProfileProvince)->getTable())){
            \App\ShipppingProfileProvince::truncate();
        }
        
        if(Schema::hasTable((new \App\ShippingProfileRates)->getTable())){
            \App\ShippingProfileRates::truncate();
        }
        /*
        if(Schema::hasTable((new \App\StaticBlock)->getTable())){

            $StaticBlockObj = new \App\StaticBlock;
            $StaticBlockObj->where('default_item', '!=', '1')->delete();
            $defaultStaticBlockList = $StaticBlockObj->where('default_item', '1')->pluck('id');
            $totalDefaultStaticBlockList = $StaticBlockObj->where('default_item', '1')->get();
            $StaticBlockTableName = $StaticBlockObj->getTable();
            $this->setAutoIncrementIdForTable($StaticBlockTableName,$totalDefaultStaticBlockList);
            $updatedTotalDefaultStaticBlockList = $StaticBlockObj->where('default_item', '1')->get()->toArray();
        }
        
        if(Schema::hasTable((new \App\StaticBlockDesc)->getTable())){
    
            $StaticBlockDescObj = new \App\StaticBlockDesc;
            $StaticBlockDescObj->whereNotIn('static_block_id', $defaultStaticBlockList)->delete();
            $StaticBlockDescList = $StaticBlockDescObj->get();
            //dd($StaticBlockDescList);
            $StaticBlockDescTableName = $StaticBlockDescObj->getTable();
            
            $this->setOldIndexesInDescTable($StaticBlockDescTableName,$StaticBlockDescList,'static_block_id',$totalDefaultStaticBlockList,$updatedTotalDefaultStaticBlockList);
        }
        
        if(Schema::hasTable((new \App\Block)->getTable())){

            $blockObj = new \App\Block;
            $blockTableName = $blockObj->getTable();
            $this->updateTableColumnValue($blockTableName,'type_id',$totalDefaultStaticBlockList,$updatedTotalDefaultStaticBlockList);
        }
            
        if(Schema::hasTable((new \App\StaticPage)->getTable())){
             
            $StaticPageObj = new \App\StaticPage;
            $StaticPageObj->where('default_item', '!=', '1')->delete();
            $defaultStaticPageList = $StaticPageObj->where('default_item', '1')->pluck('id');
            $totalDefaultStaticPageList = $StaticPageObj->where('default_item', '1')->get();
            $StaticPageTableName = $StaticPageObj->getTable();
            $this->setAutoIncrementIdForTable($StaticPageTableName,$totalDefaultStaticPageList);

            $updatedTotalDefaultStaticPageList = $StaticPageObj->where('default_item', '1')->get()->toArray();
        }
        
        if(Schema::hasTable((new \App\StaticPageDesc)->getTable())){

            $StaticPageDescObj = new \App\StaticPageDesc;
            $StaticPageDescObj->whereNotIn('static_page_id', $defaultStaticPageList)->delete();
            $StaticPageDescList = $StaticPageDescObj->get();
            $SStaticPageDescTableName = $StaticPageDescObj->getTable();
            
            $this->setOldIndexesInDescTable($SStaticPageDescTableName,$StaticPageDescList,'static_page_id',$totalDefaultStaticPageList,$updatedTotalDefaultStaticPageList);
        }
        */
        if(Schema::hasTable((new \App\StoreLocation)->getTable())){
            \App\StoreLocation::truncate();
        }
        
        if(Schema::hasTable((new \App\StoreLocationDesc)->getTable())){
            \App\StoreLocationDesc::truncate();
        }

        if(Schema::hasTable((new \App\StoreLocationSeo)->getTable())){
            \App\StoreLocationSeo::truncate();
        }
        
        if(Schema::hasTable((new \App\StoreLocationSeoDesc)->getTable())){
            \App\StoreLocationSeoDesc::truncate();
        }        

        if(Schema::hasTable((new \App\TableFilter)->getTable())){
            \App\TableFilter::truncate();
        }

        if(Schema::hasTable((new \App\TableUserSetting)->getTable())){
            \App\TableUserSetting::truncate();
        }                                
        
        if(Schema::hasTable((new \App\Tag)->getTable())){
            \App\Tag::truncate();
        }
        
        if(Schema::hasTable((new \App\TempProductImage)->getTable())){
            \App\TempProductImage::truncate();
        }
            
        if(Schema::hasTable((new \App\User)->getTable())){

            \App\User::truncate();
            
            $this->removeMediaFromDir($user_file_path);
        }

        if(Schema::hasTable((new \App\UserDeleted)->getTable())){
            \App\UserDeleted::truncate();
        }        
          
        if(Schema::hasTable((new \App\UserAttribute)->getTable())){
            \App\UserAttribute::truncate();
        }

        if(Schema::hasTable((new \App\UserComment)->getTable())){
            \App\UserComment::truncate();
            $this->removeMediaFromDir($comment_file_path);
        }

        if(Schema::hasTable((new \App\UserDobRequest)->getTable())){
            \App\UserDobRequest::truncate();
        } 

        if(Schema::hasTable((new \App\UserGroupRequest)->getTable())){
            \App\UserGroupRequest::truncate();
        }                       
        
        if(Schema::hasTable((new \App\Wishlist)->getTable())){
            \App\Wishlist::truncate();
        }

        //others directories that need to clean
        //File::cleanDirectory($media_manager_path);
        File::cleanDirectory($cart_option_path);
        File::cleanDirectory($color_path);
        File::cleanDirectory($csv_path);

        //others directories that need to delete
        $this->removeMediaFromDir($customer_path);
        
        //clean server chache
        $storage_path = storage_path();
        File::cleanDirectory($storage_path.'/app/public');
        File::cleanDirectory($storage_path.'/framework/cache');
        File::cleanDirectory($storage_path.'/framework/sessions');
        File::cleanDirectory($storage_path.'/framework/views');
        File::cleanDirectory($storage_path.'/logs');
         
        dd("DB Cleaned Successfully");
    }

    protected function removeMediaFromDir($dir_path_name){

        File::deleteDirectory($dir_path_name);
    }    

    /* This function will set auto increment id and re-arrange the data and its ids in main table */
    protected function setAutoIncrementIdForTable($table_name,$totalRecords){
       
        if(count($totalRecords)>0){
        
            DB::table($table_name)->truncate();
            $tableColumns = DB::getSchemaBuilder()->getColumnListing($table_name);
            $insertArray = [];
            foreach($totalRecords as $key =>$value){
                foreach($tableColumns as $col_key => $col_val){
                    if($col_val!='id'){
                        $insertArray[$key][$col_val] = $value->$col_val;
                    } 
                }
            }
            //echo "<pre>"; print_r($insertArray); die;
            DB::table($table_name)->insert($insertArray);
        }   
    } 

    /* This function will set old ids in related desc table */
    protected function setOldIndexesInDescTable($table_name,$totalRecords,$colmn_name,$oldRecords,$updatedRecords){
    
        if(count($totalRecords)>0){
            $searchArray = [];
            foreach($oldRecords as $old_key => $old_value){
                $searchArray[$old_value->id] = $updatedRecords[$old_key]['id'];
            }
            
            DB::table($table_name)->truncate();
            $tableColumns = DB::getSchemaBuilder()->getColumnListing($table_name);
            $insertArray = [];
            //dd($totalRecords);
            foreach($totalRecords as $key =>$value){

                foreach($tableColumns as $col_key => $col_val){
                    if($col_val!='id'){
                        $insertArray[$key][$col_val] = $value->$col_val;
                    } 

                    if($col_val==$colmn_name){
                        
                        $insertArray[$key][$col_val] = $searchArray[$value->$col_val];
                    }
                }
            }
            
            //dd($insertArray);
            
            DB::table($table_name)->insert($insertArray);
        }
    }
    
    protected function updateTableColumnValue($table_name,$colmn_name,$oldRecords,$updatedRecords){
    
        $totalRecords = DB::table($table_name)->get();
    
        if(count($totalRecords)>0){
            $searchArray = [];
            foreach($oldRecords as $old_key => $old_value){
                $searchArray[$old_value->id] = $updatedRecords[$old_key]['id'];
            }
            
            foreach($totalRecords as $key =>$value){
                
                $valData = (array) $value;
                
                if(array_key_exists($valData[$colmn_name],$searchArray)){
                    $newId = $searchArray[$valData[$colmn_name]];
                    DB::table($table_name)->where('id', $valData['id'])->update([$colmn_name =>$newId]);
                }
            }
        }
    }

    public function cleanDemoData(Request $request) {

        if($request->type == 'enable_demo') {
            SystemConfig::where('system_name', 'IS_DEMO_DATA')->update(['system_val'=>'1']);
            return redirect()->action('Admin\AdminHomeController@index')->with('succMsg', 'Demo Mode Enabled');
        }

        $isdemo = SystemConfig::getSystemVal('IS_DEMO_DATA');

        //dd($isdemo);
        if($isdemo==1){

            DB::statement('SET FOREIGN_KEY_CHECKS=0');

            //truncate product table and images code start
            if(Schema::hasTable((new \App\CompareProduct)->getTable())){
                \App\CompareProduct::truncate();
            }            
            if(Schema::hasTable((new \App\Product)->getTable())){
                \App\Product::truncate();
            }
            if(Schema::hasTable((new \App\ProductAttribute)->getTable())){
                \App\ProductAttribute::truncate();
            }
            if(Schema::hasTable((new \App\ProductAttributeValues)->getTable())){
                \App\ProductAttributeValues::truncate();
            }
            if(Schema::hasTable((new \App\ProductCat)->getTable())){
                \App\ProductCat::truncate();
            }
            if(Schema::hasTable((new \App\ProductDesc)->getTable())){
                \App\ProductDesc::truncate();
            }
            if(Schema::hasTable((new \App\ProductImage)->getTable())){
                \App\ProductImage::truncate();
            }
            if(Schema::hasTable((new \App\ProductRelatedBundle)->getTable())){
                \App\ProductRelatedBundle::truncate();
            }
            if(Schema::hasTable((new \App\ProductRequirement)->getTable())){
                \App\ProductRequirement::truncate();
            }
            if(Schema::hasTable((new \App\ProductRequirementValue)->getTable())){
                \App\ProductRequirementValue::truncate();
            }
            if(Schema::hasTable((new \App\ProductTag)->getTable())){
                \App\ProductTag::truncate();
            }
            if(Schema::hasTable((new \App\ProductTireBundlePrice)->getTable())){
                \App\ProductTireBundlePrice::truncate();
            }
            if(Schema::hasTable((new \App\ProductUserRating)->getTable())){
                \App\ProductUserRating::truncate();
            }            
            if(Schema::hasTable((new \App\ProductUserReview)->getTable())){
                \App\ProductUserReview::truncate();
            }            
            if(Schema::hasTable((new \App\ProductVideo)->getTable())){
                \App\ProductVideo::truncate();
            }
            if(Schema::hasTable((new \App\ProductWarehouse)->getTable())){
                \App\ProductWarehouse::truncate();
            }
            if(Schema::hasTable((new \App\PromotionforSelecedProduct)->getTable())){
                \App\PromotionforSelecedProduct::truncate();
            }            
            if(Schema::hasTable((new \App\RelatedProduct)->getTable())){
                \App\RelatedProduct::truncate();
            }
            if(Schema::hasTable((new \App\SeoProductWise)->getTable())){
                \App\SeoProductWise::truncate();
            }
            if(Schema::hasTable((new \App\SeoProductWiseDesc)->getTable())){
                \App\SeoProductWiseDesc::truncate();
            }
            if(Schema::hasTable((new \App\ShipppingProfileProduct)->getTable())){
                \App\ShipppingProfileProduct::truncate();
            }
            if(Schema::hasTable((new \App\TempProductAndBlogImage)->getTable())){
                \App\TempProductAndBlogImage::truncate();
            }
            if(Schema::hasTable((new \App\UserProductViewDetail)->getTable())){
                \App\UserProductViewDetail::truncate();
            }            

            // The following section will delete all the media files related to the products | Start
            $product_path = Config('constants.product_path');
            $this->removeMediaFromDir($product_path);
            //truncate product table and images code end

            //truncate user table and images code start
            if(Schema::hasTable((new \App\TableUserSetting)->getTable())){
                \App\TableUserSetting::truncate();
            }            
            if(Schema::hasTable((new \App\User)->getTable())){
                \App\User::truncate();
                $user_file_path = Config('constants.user_path');
                $this->removeMediaFromDir($user_file_path);
            }
            if(Schema::hasTable((new \App\UserDeleted)->getTable())){
                \App\UserDeleted::truncate();
            } 
            if(Schema::hasTable((new \App\UserAttribute)->getTable())){
                \App\UserAttribute::truncate();
            }
            if(Schema::hasTable((new \App\UserComment)->getTable())){
                \App\UserComment::truncate();
            }
            if(Schema::hasTable((new \App\UserDobRequest)->getTable())){
                \App\UserDobRequest::truncate();
            }
            if(Schema::hasTable((new \App\UserGroupRequest)->getTable())){
                \App\UserGroupRequest::truncate();
            }
            if(Schema::hasTable((new \App\UserLogDetail)->getTable())){
                \App\UserLogDetail::truncate();
            }            
            if(Schema::hasTable((new \App\Wishlist)->getTable())){
                \App\Wishlist::truncate();
            }
            //truncate user table and images code end

            //truncate order table code start
            if(Schema::hasTable((new \App\Orders)->getTable())){
                \App\Orders::truncate();
            }
            if(Schema::hasTable((new \App\OrdersTemp)->getTable())){
                \App\OrdersTemp::truncate();
            }            
            if(Schema::hasTable((new \App\OrderAttribute)->getTable())){
                \App\OrderAttribute::truncate();
            }
            if(Schema::hasTable((new \App\OrderDetail)->getTable())){
                \App\OrderDetail::truncate();
            }
            if(Schema::hasTable((new \App\OrderInventory)->getTable())){
                \App\OrderInventory::truncate();
            }  
            if(Schema::hasTable((new \App\OrderInvoice)->getTable())){
                \App\OrderInvoice::truncate();
            }
            if(Schema::hasTable((new \App\OrderInvoiceDetail)->getTable())){
                \App\OrderInvoiceDetail::truncate();
            }
            if(Schema::hasTable((new \App\OrderOfflinePayment)->getTable())){
                \App\OrderOfflinePayment::truncate();
                $offline_file_path = Config('constants.buyer_payment_path');
                $this->removeMediaFromDir($offline_file_path);                
            }
            if(Schema::hasTable((new \App\OrderPromotion)->getTable())){
                \App\OrderPromotion::truncate();
            }
            if(Schema::hasTable((new \App\OrderRMA)->getTable())){
                \App\OrderRMA::truncate();
            }
            if(Schema::hasTable((new \App\OrderRMADetail)->getTable())){
                \App\OrderRMADetail::truncate();
            }
            if(Schema::hasTable((new \App\OrderShipment)->getTable())){
                \App\OrderShipment::truncate();
            }
            if(Schema::hasTable((new \App\OrderShipmentTracking)->getTable())){
                \App\OrderShipmentTracking::truncate();
            }
            if(Schema::hasTable((new \App\OrderTempPromotion)->getTable())){
                \App\OrderTempPromotion::truncate();
            }
            if(Schema::hasTable((new \App\OrderTransaction)->getTable())){
                \App\OrderTransaction::truncate();
            }
            //truncate order table code ended

            SystemConfig::where('system_name', 'IS_DEMO_DATA')->update(['system_val'=>'0']);

            return redirect()->action('Admin\AdminHomeController@index')->with('succMsg', Lang::get('admin.data_cleaned_successfully'));
        }
        abort(404);
    }
}



