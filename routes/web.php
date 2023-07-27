<?php

    Route::get('bannerconfig','HomeController@bannerSide');
    Route::get('testfire','SendGoogleDocController@sendGoogleDoc');
    Route::post('upload-image-ajax', 'AjaxController@uploadImageAjax');
    Route::post('upload-image-ajax-base64', 'AjaxController@uploadBase64ImageAjax');
    Route::post('switch-language', 'AjaxController@switchLanguage');
    Route::post('switch-currency', 'AjaxController@switchCurrency');
    Route::get('autofill-address', 'AjaxController@getAutofillAddress');
    Route::post('country-detail', 'AjaxController@getCountryDetail');
    Route::post('state-city-option', 'AjaxController@getStateCityDD');
    Route::post('state-city-dd', 'AjaxController@getStateCityDropDown');
 

    //Routing for Module Management
    Route::get('api/getInstallApi/{type?}/{module?}','Admin\Module\ModuleController@getInstallApi');
    //Routing for Module Management
    Route::post('synchronizeBroadcasts', 'SyncController@synchronizeBroadcasts');
    
    Route::group(array('prefix' => 'admin','middleware' => 'escape-back-history'), function () {
        Route::get('sync-mongo', 'Admin\SyncMongoController@index');
        Route::get('adminAutoSearch', 'Admin\Search\AdminSearchController@adminAutoSearch');
        Route::get('adminSearch', 'Admin\Search\AdminSearchController@index');

        // Broadcast Notification Routes | Start
        Route::get('broadcast/notification/{message}','Admin\Broadcast\BroadcastController@index');
        Route::get('broadcast/notificationList','Admin\Broadcast\BroadcastController@broadcastNotifications');
        Route::get('broadcast/deleteNotification/{id}','Admin\Broadcast\BroadcastController@deleteNotification');
        
        Route::get('menu/getmenulisting','Admin\Menu\MenuController@getmenulisting');
        Route::get('menu/typelist','Admin\Menu\MenuController@getTypeList');
        Route::get('menu/blocklist','Admin\Menu\MenuController@blocklist');
        Route::get('menu/imagesList','Admin\Menu\MenuController@imagesList');
        Route::resource('menu', 'Admin\Menu\MenuController');
        Route::get('menu','Admin\Menu\MenuController@index');

        Route::get('menu/create','Admin\Menu\MenuController@createMenu');
        Route::post('menu/save','Admin\Menu\MenuController@saveMenu');
        
        Route::post('menu/update/{id}','Admin\Menu\MenuController@update');

        Route::get('menu/get','Admin\Menu\MenuController@getMenu');
        Route::get('menu/menulisting','Admin\Menu\MenuController@menulisting');



        /*
        *Blog Category and Sub Category Management Start | By Satish Anand | Date 03-12-2018    
        */
        Route::get('blogcategory/subcreate', 'Admin\BlogCategory\BlogCategoryController@subcreate');
        Route::get('blogcategory/checkUnique', 'Admin\BlogCategory\BlogCategoryController@checkUnique');
        Route::resource('blogcategory', 'Admin\BlogCategory\BlogCategoryController');    
        /*
        * Blog Category and Sub Category Management End 
        */
        /*
        *Blog Management Start | By Satish Anand | Date 05-12-2018    
        */         
        Route::resource('blog/config', 'Admin\Blog\BlogConfigController');
        Route::get('blog/getalltags', 'Admin\Blog\BlogController@getAllTags');
        Route::get('blog/{id}/change-status', 'Admin\Blog\BlogController@changeStatus');
        Route::resource('blog', 'Admin\Blog\BlogController');   
        Route::post('blog/deletesliderimage', 'Admin\Blog\BlogController@deleteSliderImages');

        Route::get('restoreblog/{id}/{mid}', 'Admin\Blog\BlogController@restoreblogrevision');
        Route::get('blogrevision/{id}', 'Admin\Blog\BlogController@blogrevision');
        
        /*
        * Blog Management End 
        */
        /*
        *Widget Management Start | By Satish Anand | Date 03-01-2019    
        */ 
        Route::resource('widget', 'Admin\Widget\WidgetController');
        Route::post('delectWidget', 'Admin\Widget\WidgetController@delectWidget');
        Route::post('updateWidgetSection', 'Admin\Widget\WidgetController@updateWidgetSection');
        Route::post('previewWidget', 'Admin\Widget\WidgetController@previewWidget');
        
        /*Website Mainenance Start | By Satish Anand | Date 14-02-2019       
        */         
        Route::resource('website_configuration', 'Admin\WebsiteMaintenance\WebsiteMaintenanceController');
        Route::get('api_configuration', 'Admin\WebsiteMaintenance\WebsiteMaintenanceController@apiindex');
        Route::post('api_maintenance_store', 'Admin\WebsiteMaintenance\WebsiteMaintenanceController@apistore');

        Route::post('website_maintenance_update', 'Admin\WebsiteMaintenance\WebsiteMaintenanceController@updateMaintenance');  
        Route::post('mobile_maintenance_url', 'Admin\WebsiteMaintenance\WebsiteMaintenanceController@updateMobileMaintenance');    
        /*
        * Website Mainenance End 
        */

        /*
        *Gender Management Start | By Satish Anand | Date 15-05-2019    
        */             
        Route::get('gender/{id}/change-status', 'Admin\Gender\GenderController@changeStatus');
        Route::resource('gender', 'Admin\Gender\GenderController');       
        /*
        * Gender Management End 
        */

        Route::get('cleanDatabase','Admin\CleanDatabase\CleanDatabaseController@cleanDatabase');
        Route::get('clean-demo-data','Admin\CleanDatabase\CleanDatabaseController@cleanDemoData');
        // Routing for Module Management

        //before login
        Route::GET('/', 'AdminAuth\LoginController@showLoginForm');
        Route::GET('login', 'AdminAuth\LoginController@showLoginForm');
        Route::POST('login', 'AdminAuth\LoginController@login');
        Route::POST('logout', 'AdminAuth\LoginController@logout');
        Route::POST('password/email', 'AdminAuth\ForgotPasswordController@sendResetLinkEmail');
        Route::GET('password/reset', 'AdminAuth\ForgotPasswordController@showLinkRequestForm');
        Route::POST('password/reset', 'AdminAuth\ResetPasswordControll  er@reset');
        Route::GET('password/reset/{token}', 'AdminAuth\ResetPasswordController@showResetForm'); 
        //before login ended
        Route::resource('tableConfig', 'Admin\Table\TableConfigController');
        Route::get('tableColumnConfig', 'Admin\Table\TableConfigController@columnConfig');
        Route::post('updateColumnConfig', 'Admin\Table\TableConfigController@updateColumn');
        Route::get('related-config','Admin\Config\RelatedConfigController@create'); 
        Route::post('save-related-config','Admin\Config\RelatedConfigController@store');
        Route::get('get-province','Admin\Warehouse\WarehouseController@getProvince');

        /*********block section***/

        Route::resource('block', 'Admin\Block\BlockController');
        Route::post('delectBlock', 'Admin\Block\BlockController@delectBlock');
        Route::post('updateBlockSection', 'Admin\Block\BlockController@updateBlockSection');
        Route::post('previewBlock', 'Admin\Block\BlockController@previewBlock');
        /****static block****/
        Route::get('static-block/{id}/changestatus', 'Admin\Block\StaticBlockController@changeStatus');
        Route::resource('static-block', 'Admin\Block\StaticBlockController');
        Route::get('restoreblock/{id}/{mid}', 'Admin\Block\StaticBlockController@restoreblockrevision');
        Route::get('blockrevision/{id}', 'Admin\Block\StaticBlockController@blockrevision');

        Route::resource('cms-slider', 'Admin\CmsSlider\CmsSliderController');
        Route::get('cms-slider/{id}/changestatus', 'Admin\CmsSlider\CmsSliderController@changeStatus');
        
        /****static page****/
        Route::resource('static-page', 'Admin\Page\StaticPageController');
        Route::get('static-page/{id}/changestatus', 'Admin\Page\StaticPageController@changeStatus');

        Route::get('restorestaticpage/{id}/{mid}', 'Admin\Page\StaticPageController@restorepagerevision');
        Route::get('pagerevision/{id}', 'Admin\Page\StaticPageController@pagerevision');

        /******Cache Managements********/
        Route::get('cache-management','Admin\Config\CacheController@index');
        Route::post('cache-clear','Admin\Config\CacheController@clearCache');
        Route::post('cacheTimeUpdate','Admin\Config\CacheController@cacheTimeUpdate');
        Route::get('config/clearwebsiteview','Admin\Config\CacheController@clearWebsiteView');
        Route::get('config/clearwebsiteconfig','Admin\Config\CacheController@clearWebsiteConfig');
        Route::get('config/clearwebsiteroute','Admin\Config\CacheController@clearWebsiteRoute');
        Route::get('cache-management/clearWebsiteCaches','Admin\Config\CacheController@clearWebsiteCache');
        Route::get('config/updateversion','Admin\Config\CacheController@updateVersion');
        Route::get('config/clearcloudecache','Admin\Config\CacheController@clearCloudeCache');


        /******banner section********/
        Route::resource('bannergroup', 'Admin\Banner\BannerGroupController');
        Route::get('banner/addcategorybanner/{banner_id?}', 'Admin\Banner\BannerController@addCategoryBanner');
        Route::resource('banner', 'Admin\Banner\BannerController'); 

        /*****standard badge*****/
        Route::get('badge/{id}/changestatus', 'Admin\Badge\BadgeController@changeStatus');
        Route::resource('badge', 'Admin\Badge\BadgeController');

        /*****unit*****/
        Route::get('unit/{id}/changestatus', 'Admin\Unit\UnitController@changeStatus');
        Route::resource('unit', 'Admin\Unit\UnitController');
         /*****package*****/
        Route::get('package/{id}/changestatus', 'Admin\Package\PackageController@changeStatus');
        Route::resource('package', 'Admin\Package\PackageController');

        /***order management*****/
        Route::group(array('prefix' => 'order'), function() {
            Route::get('/', 'Admin\Transaction\OrderController@index');
            Route::get('listOrderData', 'Admin\Transaction\OrderController@listOrderData');
            Route::get('/{oid}/detail', 'Admin\Transaction\OrderController@orderDetail');
            Route::post('resend-order-logistic', 'Admin\Transaction\OrderController@resendLogistic');
            Route::post('ordChangeItemStatus', 'Admin\Transaction\OrderController@ordChangeItemStatus');
            Route::post('updateRemark', 'Admin\Transaction\OrderController@updateRemark');
            Route::post('update-order-status', 'Admin\Transaction\OrderController@updateOrderStatus');
            Route::get('export-order-log', 'Admin\Transaction\ExportOrderController@index');
            Route::get('listExportOrderData', 'Admin\Transaction\ExportOrderController@listExportOrderData');
            Route::get('download-export/{id?}', 'Admin\Transaction\ExportOrderController@downloadExport');
            Route::post('change-status', 'Admin\Transaction\ExportOrderController@changeStatus');
        });

        Route::get('generate-txt', 'Admin\Transaction\ExportOrderController@generateTxt');
        Route::get('test-import-txt', 'Admin\Transaction\ExportOrderController@testImportTxt');
        Route::post('import-txt', 'Admin\Transaction\ExportOrderController@importTxt');

        Route::get('shop-detail', 'Admin\Transaction\ShopOrderController@sellerDetail');

        Route::get('seller-order-export', 'Admin\Transaction\ShopOrderController@sellerOrder');
        Route::get('listSellerOrderData', 'Admin\Transaction\ShopOrderController@listSellerOrderData');
        Route::get('get-generated-log', 'Admin\Transaction\ShopOrderController@getGeneratedLog');

        Route::group(array('prefix' => 'shop-order'), function() {
            Route::get('/', 'Admin\Transaction\ShopOrderController@index');
            Route::get('listOrderData', 'Admin\Transaction\ShopOrderController@listOrderData');
            Route::get('/{oid}/detail', 'Admin\Transaction\ShopOrderController@orderDetail');
            Route::post('ordChangeItemStatus', 'Admin\Transaction\ShopOrderController@changeShopOrderStatus');
            Route::post('updateRemark', 'Admin\Transaction\ShopOrderController@updateRemark');
        });

        Route::post('froalaupload','FroalaEditorController@uploadImage');
        Route::get('froalaloadimages','FroalaEditorController@froalaLoadImages');
        Route::post('froalaNewFolder','FroalaEditorController@froalaNewFolder');
        Route::post('froaladeletefolder','FroalaEditorController@froalaDeleteFolder');
       

        /*******notification template configuration*********/
        /*******notification template configuration*********/
        Route::get('mastertempate/{id}/delete', 'Admin\Notification\MailTemplateController@deleteTemplete');
        Route::post('mastertempate/update', 'Admin\Notification\MailTemplateController@masterTemplateUpdate');
        Route::get('mastertempate/{id}/edit', 'Admin\Notification\MailTemplateController@masterTemplateEdit');
        Route::post('mastertempate/submit', 'Admin\Notification\MailTemplateController@masterTemplateSubmit');
        Route::get('mastertempate/create', 'Admin\Notification\MailTemplateController@masterTemplateCreate');
        Route::get('mastertempate', 'Admin\Notification\MailTemplateController@masterTempateList');
        Route::get('edittemplatetype/{id}/{template_type}', 'Admin\Notification\MailTemplateController@editTemplateType');

        Route::post('updatetemplatetype', 'Admin\Notification\MailTemplateController@updateTemplateType'); 

        Route::post('addtemplatetype', 'Admin\Notification\MailTemplateController@addtemplatetype');
        Route::get('mail/showdetails/{id}/{template_type}', 'Admin\Notification\MailTemplateController@showdetails');
        
        Route::get('mail/editevent/{id?}', 'Admin\Notification\MailTemplateController@editevent');

        Route::post('mail/updateeditevent', 'Admin\Notification\MailTemplateController@updateeditevent');
        
        Route::resource('mail', 'Admin\Notification\MailTemplateController');

        Route::get('emailTransmission', 'Admin\Notification\MailTemplateController@manageEmailTransmission');
        Route::post('updateEmailTransMethod','Admin\Notification\MailTemplateController@updateEmailTransMethod');

        Route::post('driverData','Admin\Notification\MailTemplateController@getSelectdDriverData');
        Route::post('testEmailServerConnection','Admin\Notification\MailTemplateController@testEmailServerConnection');

        Route::get('smsTransmission', 'Admin\Notification\MailTemplateController@manageSMSTransmission');
        Route::post('updatesmsTransMethod','Admin\Notification\MailTemplateController@updateSMSTransMethod');
        Route::post('testSmsServerConnection','Admin\Notification\MailTemplateController@testSmsServerConnection');
        Route::post('getSmsData','Admin\Notification\MailTemplateController@getSmsData');

        Route::get('otpTransmission', 'Admin\Notification\MailTemplateController@manageOTPTransmission');
        Route::post('updateotpTransMethod','Admin\Notification\MailTemplateController@updateOTPTransMethod');
        Route::post('testotpServerConnection','Admin\Notification\MailTemplateController@testOTPServerConnection');
        
        Route::get('lineTransmission', 'Admin\Notification\MailTemplateController@manageLineTransmission');
        Route::get('lineTransmission/{id}/delete', 'Admin\Notification\MailTemplateController@deleteLineTransmission');
        Route::post('testlineServerConnection','Admin\Notification\MailTemplateController@testLineServerConnection');
        Route::get('testLineChannel/{id}','Admin\Notification\MailTemplateController@testLineChannel');
        Route::get('addLineChannel','Admin\Notification\MailTemplateController@addLineChannel');
        Route::post('storeLineChannel','Admin\Notification\MailTemplateController@storeLineChannel'); 
        Route::get('editLineChannel/{id}','Admin\Notification\MailTemplateController@editLineChannel');
        Route::post('updateLineChannel','Admin\Notification\MailTemplateController@updateLineChannel');
        
        Route::post('verifyOtp','Admin\Notification\MailTemplateController@verifyOtp');

        Route::get('senderlist','Admin\Notification\MailTemplateController@senderlist');
        Route::get('sendercreate','Admin\Notification\MailTemplateController@senderCreate');
        Route::post('senderstore','Admin\Notification\MailTemplateController@senderStore');
        
        Route::get('editsender/{id?}','Admin\Notification\MailTemplateController@editSender');
        Route::post('updatesender','Admin\Notification\MailTemplateController@updateSender');

        Route::get('deletesender/{id?}','Admin\Notification\MailTemplateController@deleteSender');

        Route::resource('seoglobals', 'Admin\SEO\SeoGlobalController');

        Route::group(array('prefix' => 'product'), function() {
            Route::get('create', 'Admin\Product\ProductController@create');
            Route::post('store', 'Admin\Product\ProductController@store');
            Route::get('sellerdata', 'Admin\Product\ProductController@SellerData');
            Route::get('getsellercat', 'Admin\Product\ProductController@getSellerCategory');
            Route::get('baseunit/{cat_id?}','Admin\Product\ProductController@baseUnit');

            
            Route::get('/', 'Admin\Product\ProductController@index');
            Route::get('productlistdata', 'Admin\Product\ProductController@productListData');
            Route::get('edit/{id?}', 'Admin\Product\ProductController@edit');
            Route::post('update/{id?}', 'Admin\Product\ProductController@update');
            Route::get('copy/{id?}', 'Admin\Product\ProductController@copy');
            Route::post('copystore/{id?}', 'Admin\Product\ProductController@copystore');
            Route::get('delete/{id?}', 'Admin\Product\ProductController@deleteproduct');
            Route::delete('deleteselected', 'Admin\Product\ProductController@deleteSelectedproducts');
            Route::post('changestatusofselectedproducts', 'Admin\Product\ProductController@changeStatusofSelectedproducts');

            Route::get('reviews','Admin\Review\ReviewController@showProductReview');
            Route::get('destroy/{id?}', 'Admin\Review\ReviewController@destroy');
            Route::get('reported-reviews','Admin\Review\ReviewController@showReportedProductReview');
 			Route::get('get-all-reviews','Admin\Review\ReviewController@getProductReviews');
            
        });  

        Route::get('pricelist-by-customer','Admin\Customer\PricePerCustController@index');
        Route::post('pricelist-product-data','Admin\Customer\PricePerCustController@getAllProducts');
        /*******************SEO Management****************************/
        Route::group(array('prefix' => 'seo'), function() {
            Route::get('products', 'Admin\SEO\SeoController@products');
            Route::get('productlist', 'Admin\SEO\SeoController@productlist');
            Route::get('addproductseo/{id?}', 'Admin\SEO\SeoController@addproductseo');
            Route::post('addseoproductstore', 'Admin\SEO\SeoController@addseoproductstore');
            Route::get('pages', 'Admin\SEO\SeoController@pages');
            Route::get('allpageslist', 'Admin\SEO\SeoController@allpageslist');

            Route::get('createpageseo', 'Admin\SEO\SeoController@createpageseo');
            Route::post('storepageseo', 'Admin\SEO\SeoController@storepageseo');
            Route::get('editpageseo/{id?}', 'Admin\SEO\SeoController@editpageseo');
            Route::post('updatepageSeo/{id?}', 'Admin\SEO\SeoController@updatepageSeo');
            Route::get('deletepageseo/{id?}', 'Admin\SEO\SeoController@deletepageseo');

            Route::get('config/seoconfig', 'Admin\Config\SystemConfigController@SEOConfig');

            Route::post('config/storeseoconfig', 'Admin\Config\SystemConfigController@storeSeoConfig');
        });

        Route::group(array('prefix' => 'log'), function(){
            Route::get('admin', 'Admin\LoginLog\LoginLogController@admin'); 
            Route::post('admindeleteLog', 'Admin\LoginLog\LoginLogController@admindeleteLog');
            Route::get('adminclearLog', 'Admin\LoginLog\LoginLogController@adminclearLog');
            Route::get('user', 'Admin\LoginLog\LoginLogController@user');
            Route::post('userdeleteLog', 'Admin\LoginLog\LoginLogController@userdeleteLog');
            Route::get('userclearLog', 'Admin\LoginLog\LoginLogController@userclearLog');
        }); 

        Route::post('city/city-list', 'Admin\Country\CityController@getCityList');
        Route::post('city/province', 'Admin\Country\CityController@getProvinceList');
        Route::resource('city', 'Admin\Country\CityController');
        Route::resource('province', 'Admin\Country\ProvinceController');
        Route::resource('country', 'Admin\Country\CountryController');

        /*******payment option*****/
        Route::get('changePayOptStatus/{id}', 'Admin\Config\PaymentOptionController@changePayOptStatus');

        Route::get('changePayOptMode/{id}', 'Admin\Config\PaymentOptionController@changePayOptMode');
        Route::resource('paymentoption', 'Admin\Config\PaymentOptionController');

        Route::get('upload-bank', 'Admin\Config\PaymentBankController@uploadBank');
        Route::get('upload-bank-branch', 'Admin\Config\PaymentBankController@uploadBankBranch');
        Route::get('changeBankStatus/{id}', 'Admin\Config\PaymentBankController@changeBankStatus');
        Route::resource('paymentbank', 'Admin\Config\PaymentBankController');

        Route::post('translation/exportsource/importsource', 'Admin\Translation\TranslationController@importSource');
        Route::get('translation/exportsource/{module_id}/{lang_code}', 'Admin\Translation\TranslationController@exportSource');
        Route::get('translation/addsource/{id?}', 'Admin\Translation\TranslationController@addsource');
        Route::get('translation/addsourcevalue/{module_id?}/{lang_id?}', 'Admin\Translation\TranslationController@addsourcevalue');
        Route::post('translation/addsourcevaluesave', 'Admin\Translation\TranslationController@addsourcevaluesave');
        Route::get('translation/search', 'Admin\Translation\TranslationController@searchTranslation');
        Route::post('translation/search', 'Admin\Translation\TranslationController@searchTranslation');
        Route::post('translation/search-update', 'Admin\Translation\TranslationController@searchTranslationUpdate');    
        Route::post('translation/addsinglesourcedata', 'Admin\Translation\TranslationController@addsinglesourcedata');
        Route::post('translation/addsinglesourcevalue', 'Admin\Translation\TranslationController@addsinglesourcevalue');
        Route::post('translation/deletesinglesource', 'Admin\Translation\TranslationController@deleteSingleSource');
        Route::resource('translation', 'Admin\Translation\TranslationController');
        
        Route::get('translation-module/{id}/delete', 'Admin\Translation\TranslationModuleController@deleteModule');
        Route::resource('translation-module', 'Admin\Translation\TranslationModuleController');
        Route::resource('translation-menu', 'Admin\Translation\MenuController');
        Route::resource('order-status', 'Admin\Translation\OrderStatusController');

        Route::get('currency/detail', 'Admin\Config\CurrencyController@detail');
        Route::post('currency/detail-update', 'Admin\Config\CurrencyController@detailUpdate');
        Route::resource('currency', 'Admin\Config\CurrencyController');

        Route::resource('language', 'Admin\Config\LanguageController');  

        Route::get('groups/{group_id}/delete', 'Admin\Role\GroupController@delete');
        Route::resource('groups', 'Admin\Role\GroupController'); 

        Route::get('users/account-detail', 'Admin\User\AdminController@accountDetail');
        Route::post('users/confirm-password', 'Admin\User\AdminController@confirmPassword');
        Route::post('users/change-password', 'Admin\User\AdminController@changePassword');
        Route::get('users/{user_id}/deleted', 'Admin\User\AdminController@delete');
        Route::resource('users', 'Admin\User\AdminController');

        Route::resource('avatar','Admin\Config\AvatarController');

        Route::post('sitelogo/update', 'Admin\Config\SystemConfigController@siteLogoUpdate');
        Route::get('sitelogo/image', 'Admin\Config\SystemConfigController@siteLogoEdit');
        
        Route::post('siteloader/update','Admin\Config\SystemConfigController@siteLoaderUpdate');
        Route::get('siteloader/image','Admin\Config\SystemConfigController@siteLoaderEdit');

        
        Route::post('placeholder/imageupload', 'Admin\Config\SystemConfigController@placeholderImageUpload');
        Route::get('placeholder/image', 'Admin\Config\SystemConfigController@placeholderImage');
        Route::post('config/updatePickupCenter', 'Admin\Config\SystemConfigController@updatePickupCenter');
        //Route::get('config/pickup-center', 'Admin\Config\SystemConfigController@pickupCenter');
        Route::resource('config', 'Admin\Config\SystemConfigController', array("as" => "configer")); 

        Route::group(['prefix' => 'shipping','middleware' => 'escape-back-history'], function(){
            Route::get('pickup-at-center','Admin\Config\SystemConfigController@pickupCenter');
            Route::get('pickup-at-store','Admin\Config\SystemConfigController@pickupAtStore');
            Route::get('delivery-at-address','Admin\ShippingProfile\ShippingRateTableController@deliveryAtAddress');
            Route::post('get-delivery-at-address','Admin\ShippingProfile\ShippingRateTableController@getDeliveryAtAddress');
            
            Route::get('delivery-at-address/editRate/{id}','Admin\ShippingProfile\ShippingRateTableController@editRate');
            Route::get('delivery-at-address/deleteRate/{id}','Admin\ShippingProfile\ShippingRateTableController@deleteRate');
            Route::post('delivery-at-address/saveShippingRateProfile','Admin\ShippingProfile\ShippingRateTableController@saveShippingRateProfile');
            Route::get('delivery-at-address/export_rates','Admin\ShippingProfile\ShippingRateTableController@export_rates');
            Route::get('delivery-at-address/addNewTableRate','Admin\ShippingProfile\ShippingRateTableController@addNewTableRate');
            Route::post('delivery-at-address/listData','Admin\ShippingProfile\ShippingRateTableController@listData');
            Route::get('listShippingRates','Admin\ShippingProfile\ShippingRateTableController@listShippingRatesData');
            Route::get('delivery-at-address/addWizardRate','Admin\ShippingProfile\ShippingRateTableController@addWizardRate');
            Route::post('delivery-at-address/autosuggest','Admin\ShippingProfile\ShippingRateTableController@autosuggest');
            Route::post('delivery-at-address/saveRate','Admin\ShippingProfile\ShippingRateTableController@saveRate');
            Route::post('delivery-at-address/saveWizardRate','Admin\ShippingProfile\ShippingRateTableController@saveWizardRate');
            Route::post('delivery-at-address/getRelatedData','Admin\ShippingProfile\ShippingRateTableController@getRelatedData');
        });

         
          
        Route::get('home', 'Admin\AdminHomeController@index');
        Route::post('dismiss', 'Admin\AdminHomeController@dismiss');   

        Route::get('customecss', 'Admin\AdminHomeController@customeCss');
        Route::post('customeCssSet', 'Admin\AdminHomeController@customeCssSet');
        Route::get('restorecss/{id}', 'Admin\CustomCss\CustomCssController@restorecssrevision');
        Route::get('cssrevision', 'Admin\CustomCss\CustomCssController@cssrevision');   

        Route::resource('cssjsembeded', 'Admin\Config\CssJsEmbededController');


        /*****Log Activity Added by Satish Anand Start ******/    
        Route::resource('logactivity', 'Admin\Logactivity\LogactivityController');
        Route::get('logactivity/{id}/poductView', 'Admin\Logactivity\LogactivityController@productView');
        Route::get('logactivity/{id}/orderView', 'Admin\Logactivity\LogactivityController@orderView');
        Route::get('logactivity/{id}/logDetails', 'Admin\Logactivity\LogactivityController@logDetails');    
        /*****Log Activity Added by Satish Anand End ******/ 

        /***category****/
        Route::get('catalog/subcreate/{id?}', 'Admin\Category\CategoryController@subcreate');
        Route::get('catalog/checkUnique', 'Admin\Category\CategoryController@checkUnique');
        Route::get('categorieslist', 'Admin\Category\CategoryController@categorieslist');
        Route::get('categoryedit','Admin\Category\CategoryController@categoryedit');
        Route::get('checkcatmovepossible','Admin\Category\CategoryController@checkCategoryMove');
        Route::get('catalog/deletecat/{id?}', 'Admin\Category\CategoryController@deletecat');
        Route::post('catalog/assign-seller', 'Admin\Category\CategoryController@assignSeller');
        Route::post('catalog/assign-unit', 'Admin\Category\CategoryController@assignUnit');
        
        Route::resource('catalog', 'Admin\Category\CategoryController');

        /***category-management****/
        Route::get('category-management/subcreate/{id?}', 'Admin\CategoryManagement\CategoryController@subcreate');
        Route::get('category-management/checkUnique', 'Admin\CategoryManagement\CategoryController@checkUnique');
        Route::get('category-management-list', 'Admin\CategoryManagement\CategoryController@categorieslist');
        Route::get('category-management-edit','Admin\CategoryManagement\CategoryController@categoryedit');
        Route::get('category-management/deletecat/{id?}', 'Admin\CategoryManagement\CategoryController@deletecat');
        Route::post('category-management/assign-seller', 'Admin\CategoryManagement\CategoryController@assignSeller');
        Route::post('category-management/assign-unit', 'Admin\CategoryManagement\CategoryController@assignUnit');
        
        Route::resource('category-management', 'Admin\CategoryManagement\CategoryController');

        // Billing Address for buy plugin | End

        /**All buyer and seller (Customers) in admin *******/
        Route::get('list_customer/customerdata', 'Admin\Customer\UserController@customerData');
        Route::resource('list_customer', 'Admin\Customer\UserController');
        Route::post('deleteSelectedCustomers', 'Admin\Customer\UserController@deleteSelectedCustomers');
        Route::post('changeStatusofSelectedCustomer', 'Admin\Customer\UserController@changeStatusofSelectedCustomer');
        Route::post('deleteSelectedSeller', 'Admin\Customer\SellerController@deleteSelectedSeller');
        Route::post('changeStatusofSelectedSeller', 'Admin\Customer\SellerController@changeStatusofSelectedSeller'); 
        Route::get('add_buyer','Admin\Customer\BuyerController@addNewBuyer');
        Route::get('edit-buyer/{id}','Admin\Customer\BuyerController@editBuyer'); 
        Route::post('save-buyer','Admin\Customer\BuyerController@saveBuyer'); 
        Route::post('save-seller','Admin\Customer\SellerController@saveSeller'); 
        Route::post('save-customer','Admin\Customer\UserController@saveBuyer'); 
        Route::post('buyer_change_password','Admin\Customer\BuyerController@changePassword'); 
        Route::post('seller/assignCategorySeller', 'Admin\Customer\UserController@assignCategorySeller');
        Route::post('seller/updateShopInfo', 'Admin\Customer\UserController@updateShopInfo');
        Route::post('seller/deleteShopImg','Admin\Customer\UserController@deleteShopImg');
        Route::get('add_seller','Admin\Customer\SellerController@addSeller');
        Route::resource('seller','Admin\Customer\SellerController');
        Route::get('list/seller','Admin\Customer\SellerController@sellerData');
    });
    /**admin route end*******/
    Route::get('track-order/{order_id?}','Checkout\TrackOrderController@trackOrderDetail');
   
    Route::any('payment-gateway/kbank/v1/odd/register/tracking', 'Checkout\PaymentGatewayController@oddRegisterTracking');
    Route::any('payment-gateway/kbank/v1/odd/checkout/tracking', 'Checkout\PaymentGatewayController@oddPaymentTracking');
    Route::group(['prefix' => 'user','middleware' => 'escape-back-history'], function () {
        Route::post('address/default', 'User\UserController@setDefaultAddress');
        Route::post('address/sequence', 'User\UserController@updateSequence');
        Route::post('address/delete', 'User\UserController@delete');
        Route::post('dobrequest', 'User\UserController@dobrequest');
        Route::post('confirm-password', 'User\UserController@confirmPassword');
        Route::post('send-update-otp', 'User\UserController@sendUpdateOtp');
        Route::post('confirm-otp', 'User\UserController@confirmOtp');
        Route::resource('profile', 'User\UserController');
        Route::get('address', 'User\UserController@show');
        Route::get('favorite-shopes', 'User\UserController@favoriteShop');
        Route::get('delete-favorite-shope/{shop_id}', 'User\UserController@deleteFavoriteShop');
        Route::resource('credit-requets', 'User\CreditController');
        Route::post('getcredits', 'User\CreditController@getAllCredits');
        Route::get('credit-balence', 'User\CreditController@creditBalance');
        Route::get('credit-usage','User\CreditController@creditUsage');
        Route::post('getcOverdueCredits', 'User\CreditController@getAllCreditUsage');
        Route::post('getcCreditBalance', 'User\CreditController@getAllCreditBalance');  
        Route::get('wishlist','User\WishlistController@index');
        Route::post('wishlist-products','ProductsController@getProductByWishlist');
        Route::get('register-odd', 'User\ODDController@index');
        Route::get('register-odd-condition', 'User\ODDController@oddCondition');
        Route::post('save-condition', 'User\ODDController@oddConditionStore');
        Route::post('odd-unregister', 'User\ODDController@oddUnregister');
        Route::post('register-odd-token', 'User\ODDController@oddToken');

        Route::group(['prefix' => 'order','middleware' => 'escape-back-history'], function () {
            Route::get('history', 'User\OrderController@orderHistory');
            Route::post('history-data', 'User\OrderController@orderHistoryData');
            Route::get('pending-order', 'User\OrderController@pendingOrder');
            Route::post('pending-order-data', 'User\OrderController@pendingOrderData');
            Route::get('shop-history', 'User\OrderController@sellerOrderHistory');
            Route::post('seller-history-data', 'User\OrderController@sellerOrderHistoryData');
            Route::get('delivery-list', 'User\OrderController@deliveryList');
            Route::post('delivery-list-data', 'User\OrderController@deliveryListData');
            Route::get('{order_id}/detail', 'User\OrderController@mainOrderDetail');
            Route::get('order_detail/{order_id}', 'User\OrderController@orderDetails');
            Route::post('receiveOrdItems', 'User\OrderController@receiveOrdItems');
            Route::post('receiveOrd', 'User\OrderController@receiveOrd');
            Route::get('shop-ord-detail/{order_id}', 'User\OrderController@shopOrderDetails');
            Route::get('{order_id}/payment', 'User\OrderController@orderPayment');

        });
        // Route::get('shopping_list','User\ShoppinglistController@index');
        // Route::post('add_to_shopping_list','User\ShoppinglistController@AddToShoppingList');
        
        Route::resource('review','User\ReviewController');
        Route::post('ordered-product-list','User\ReviewController@getOrderedProductList');

    });

    Route::group(['prefix' => 'buyer','middleware' => 'escape-back-history'], function () {
        Route::get('bargain/{sortby?}','User\BargainController@index');
        Route::get('getbargainlist','User\BargainController@getBargainList');
        Route::post('bargainPriceFromBuyer/{id?}','User\BargainController@bargainPriceFromBuyer');
        Route::post('removebargain/{id?}','User\BargainController@removeBargain');
        Route::post('removeallbargain/{id?}','User\BargainController@removeAllBargain');
        Route::post('selectedAddtoCart','User\BargainController@selectedAddtoCart');
        Route::get('shopping_list','User\ShoppinglistController@index');
        Route::post('add_to_shopping_list','User\ShoppinglistController@AddToShoppingList');
        Route::post('create_shopping_list','User\ShoppinglistController@createNewShoppingList');
        Route::post('get_shopping_list_items','User\ShoppinglistController@getShoppingListItems');
        Route::post('editShoppingListName','User\ShoppinglistController@editShoppingListName');
        Route::post('deleteShoppingList','User\ShoppinglistController@deleteShoppingList');
        Route::post('addProductToShoppinglist','User\ShoppinglistController@addProductToShoppinglist');
        Route::post('editNote','User\ShoppinglistController@editNote');
        Route::post('saveItemStandered','User\ShoppinglistController@saveItemStandered');
        Route::post('completeShoppingItem','User\ShoppinglistController@completeShoppingItem');
        Route::post('deleteShoppingItem','User\ShoppinglistController@deleteShoppingItem');
        Route::post('checkLoadingStatus','User\ShoppinglistController@checkShoppingListLoadingStatus');
        Route::post('saveItemPrice','User\ShoppinglistController@saveItemPrice');
        Route::post('saveAllItems','User\ShoppinglistController@saveAllItem');
        Route::get('track_order','User\OrderController@trackOrder');
        Route::post('editBadge','User\ShoppinglistController@editBadge');
        Route::post('editPrice','User\ShoppinglistController@editPrice');
        Route::post('editQty','User\ShoppinglistController@editQty');
        Route::post('saveItemQty','User\ShoppinglistController@saveItemQty');
        Route::post('getCategorySellers','User\ShoppinglistController@getCategorySellers');
    });
    
    Route::get('home', 'HomeController@index');
    Route::get('/', 'HomeController@index');
    Route::get('export-order', 'HomeController@exportOrder');
    Route::get('my-home', 'HomeController@myhome');
    Route::get('export-order', 'HomeController@exportOrder');
    Route::post('mobile-login', 'HomeController@mobileLogin');
    /* frontend blog pages start by  */
    //Route::get('blog/category/{categoryurl}', 'BlogController@categoryBlogList'); 
    Route::get('page/{url?}', 'StaticPageController@pagedata'); // static pages link

    /****register user*******/
    Route::get('register', 'Auth\RegisterController@index');
    Route::get('login', 'Auth\RegisterController@login');
    Route::post('login','Auth\LoginController@login')->name('userlogin');
    Route::post('insert', 'Auth\RegisterController@insert');
    Route::post('resendverificationlink', 'Auth\RegisterController@resendverificationlink');
    Route::get('register/verify/{token}', 'Auth\RegisterController@verify');
    Route::get('register/verify-otp/{id?}', 'Auth\RegisterController@verifyOtp')->name('buyerVerify');  
    Route::get('register/verify-user', 'Auth\RegisterController@verifyUser');
    Route::get('logout', 'Auth\LogoutController@logout');
    Route::post('requestOtp', 'Auth\RegisterController@requestOtp');
    Route::post('confirmOtp', 'Auth\RegisterController@confirmOtp');
    Route::post('resetPasswordPhone', 'Auth\ResetPasswordController@resetPasswordPhone');

    /***seller register****/
    Route::group(array('prefix' => 'seller-register'), function(){
        Route::get('/', 'Auth\RegisterController@sellerRegister');
        Route::get('verify-otp/{id?}', 'Auth\RegisterController@verifyOtp')->name('sellerVerify');
        Route::get('shop-info/{id?}', 'Auth\SellerRegisterController@index');
        Route::get('account-info/{id?}', 'Auth\SellerRegisterController@accountInfo');
        Route::get('thanks/{id?}', 'Auth\SellerRegisterController@thanks');
        Route::post('bank-branch', 'Auth\SellerRegisterController@getBranchList');
        Route::post('checkStoreName', 'Auth\SellerRegisterController@checkStoreName');

        Route::post('checkStoreUrl', 'Auth\SellerRegisterController@checkStoreUrl');

        Route::post('checkPanelNo', 'Auth\SellerRegisterController@checkPanelNo');

        Route::post('checkCitizenId', 'Auth\SellerRegisterController@checkCitizenId');

        Route::get('account-info/{id?}', 'Auth\SellerRegisterController@accountInfo');

        Route::post('insert-shop-info', 'Auth\SellerRegisterController@insertShopInfo');
        Route::post('insert-account-info', 'Auth\SellerRegisterController@insertAccountInfo');
    });

    Route::group(['middleware' => 'is-seller'], function(){ 
        Route::group(array('prefix' => 'seller'), function(){

            Route::get('manage-shop','Seller\ShopController@index');
            Route::post('updateStore','Seller\ShopController@updateStore');
            Route::post('deleteShopImg','Seller\ShopController@deleteShopImg');
            Route::post('updateShopStatus','Seller\ShopController@updateShopStatus');
            //product section start
            Route::resource('product', 'Seller\ProductController');
            Route::get('getproductlist','Seller\ProductController@getProductlist');  
            Route::get('deleteproduct/{id?}','Seller\ProductController@deleteProduct');
            Route::get('copy/{id?}','Seller\ProductController@copy');
            Route::post('copystore/{id?}','Seller\ProductController@copystore');
            Route::get('baseunit/{cat_id?}','Seller\ProductController@baseUnit');
            
            Route::get('seller-product','Seller\ProductController@sellerProduct');
            
            Route::get('update-status/{id?}/{status?}','Seller\ProductController@updateStatus');
      
            //product stock memo
            Route::resource('stock-memo', 'Seller\StockMemoController');
            Route::get('getStockList','Seller\StockMemoController@getStockList');
            Route::get('getStockMemo/{id}','Seller\StockMemoController@getStockMemo');

            Route::get('bargain/{sortby?}','Seller\BargainController@index');
            //Route::resource('bargain', 'Seller\BargainController');
            Route::get('getbargainlist','Seller\BargainController@getBargainList');

            Route::get('rejectbargain/{id?}','Seller\BargainController@rejectBargain');
            Route::post('rejectallbargain','Seller\BargainController@rejectAllBargain');
            Route::get('acceptbargain/{id?}','Seller\BargainController@acceptBargain');
            Route::post('adjustpricefromseller/{id?}','Seller\BargainController@adjustPriceFromSeller');

            Route::group(array('prefix' => 'credits'), function(){
                Route::resource('manage-credit','Seller\CreditController');
                Route::post('getOverdueCredits','Seller\CreditController@getAllOverdueCredits');
                Route::post('getCredits','Seller\CreditController@getAllCredits');
                Route::post('getCreditsRequest','Seller\CreditController@getCreditsRequest');
                Route::post('updateNickName','Seller\CreditController@editBuyerNickName');
                Route::post('giveCredit','Seller\CreditController@giveCredit');
                Route::post('namageCreditRequest','Seller\CreditController@manageCreditAjaxRequest');
                Route::post('willCreditRemove','Seller\CreditController@willCreditRemove');
                Route::get('viewHistory/{uid}','Seller\CreditController@viewHistory');
                Route::get('manageCredit/{uid}','Seller\CreditController@manageUserCredit');
            });

            Route::group(['prefix'=>'rating'], function (){
                Route::get('shop','Seller\ReviewController@index');
                Route::post('shop-product-rating','Seller\ReviewController@getProductRatings');
                Route::get('report/{order_id}/{product_id}','Seller\ReviewController@reportReview');
                Route::post('send-report','Seller\ReviewController@sendReport');
            });

            Route::group(array('prefix' => 'customer'), function(){
                Route::get('/','Seller\CustomerController@index');
                Route::get('customerListData','Seller\CustomerController@customerListData');
                Route::post('changeCustName','Seller\CustomerController@changeCustName');
                Route::get('details/{id}','Seller\CustomerController@details');
                Route::post('getCustomerOrderList','Seller\CustomerController@getUserOrderList');

                Route::post('credit_paid','Seller\CustomerController@paidCredit');
            });

            Route::group(array('prefix' => 'order'), function(){
                Route::get('details/{id}','Seller\OrderController@details');
                Route::post('getOrderItemList','Seller\OrderController@getOrderItemList');
                Route::get('history','Seller\OrderController@orderHistory');

                Route::post('history-data','Seller\OrderController@orderHistoryData');
                Route::get('delivery-list/{section?}','Seller\OrderController@deliveryList');
                Route::get('delivery-list-data','Seller\OrderController@deliveryListData');
                Route::get('getOrderlist','Seller\OrderController@getOrderlist');
                Route::post('updateShopOrdStatus','Seller\OrderController@updateShopOrdStatus');
            });

            Route::group(array('prefix' => 'bill'), function(){
                Route::get('orderoutstandingbalance','Seller\OrderController@orderOutstandingBalance');

                Route::post('orderOutstandingBalanceData','Seller\OrderController@orderOutstandingBalanceData');
            });

            Route::group(array('prefix' => 'report'), function(){
                Route::get('/', 'Seller\SellerReportController@index');
                Route::post('load_chart_data','Seller\SellerReportController@loadChartData');
            });
        });
    });

    Route::group(array('prefix' => 'shop'), function(){
    	Route::post('get-shop-products','ProductsController@getProductsByShop');
        Route::get('/{cat_url}/data','ShopController@shopListData');
        Route::get('/{shop}/{cat_url?}','ShopController@index');
        
        Route::post('/manageFavoriteShop','ShopController@manageFavoriteShop');
        Route::get('/','ShopController@shopList');
        Route::post('/credit-request','ShopController@sendCreditRequest');
        Route::post('/checkLogin','ShopController@checkLogin');
        
    });
    Route::get('get-shop-filter','ShopController@shopFilter');
    /*****product********/
    Route::get('category/{url?}', 'ProductsController@category');
    Route::get('categorysearch/{url?}', 'ProductsController@categorySearch');
    Route::any('getsearchproducts', 'ProductsController@getSearchProducts');
    Route::any('getproducts', 'ProductsController@getProductsbycategory');
    Route::get('category/{url?}/shop', 'ShopController@shopList');


    /*search product by search*/
    Route::get('search', 'ProductsController@search');
    Route::post('getproductBysearch', 'ProductsController@getProductsBysearch');
    Route::post('getproductsShopBysearch', 'ProductsController@getProductsShopBysearch');
    Route::post('getshopBysearch', 'ProductsController@getShopBysearch');
    
    Route::get('productsrenderhtml', 'ProductsController@productsRenderhtml');
    Route::get('autosearch', 'ProductsController@autosearch');


    /*add to wishlist*/
    Route::get('addIntoWishlist', 'ProductsController@addIntoWishlist');
    Route::get('removeFromWishlist', 'ProductsController@removeFromWishlist');
    
    Route::group(['prefix' => 'product'], function () {

        Route::get('/{cat_url?}/{sku?}', 'ProductDetailController@display');
        Route::post('checkProductBeforeCart', 'ProductDetailController@checkProductBeforeCart');
        Route::post('addProductToCart', 'ProductDetailController@addProductToCart');
        Route::post('productPriceByQuantity', 'ProductDetailController@productPriceByQuantity');
        Route::post('product-reviews','ProductDetailController@getAllReviews');

    });
    Route::get('related-products','ProductDetailController@getRelatedProducts');
    Route::get('get-buyer-order-history','ProductDetailController@getBuyerOrderHistory');
    
    Route::group(['prefix' => 'checkout'], function () {
        Route::resource('/', 'Checkout\CartController');
        Route::get('buy-now-end-shopping', 'Checkout\CartController@index')->name('buy-now-end-shopping')->middleware('escape-back-history');
        /*Route::get('buy-now', 'Checkout\CartController@index')->name('buy-now'); 
        Route::get('end-shopping', 'Checkout\CartController@index')->name('end-shopping');*/ 
        Route::post('removeCart', 'Checkout\CartController@removeCart');
        Route::post('removeOrder', 'Checkout\CartController@removeOrder');
        Route::post('updateCart', 'Checkout\CartController@updateCart');
        Route::post('updateCartPrice', 'Checkout\CartController@updateCartPrice');
        Route::get('shopping-cart', 'Checkout\CartController@shoppingCart');
        Route::get('paid-product', 'Checkout\CartController@alreadyPaid');
        Route::get('thanks/{id?}', 'Checkout\OrderController@thanks');
        Route::get('cancel', 'Checkout\OrderController@cancel');
        Route::post('payProduct', 'Checkout\CartController@payProduct');

        Route::get('cartAddress', 'Checkout\CartController@cartAddress');
        Route::post('saveAddress', 'Checkout\CartController@saveAddress');
        Route::post('changeShipAddress', 'Checkout\CartController@changeShipAddress');
        Route::post('changeBillAddress', 'Checkout\CartController@changeBillAddress');
        Route::get('pickupTime', 'Checkout\CartController@pickupTime');  

        /****kbank payment gateway url*******/     
        Route::get('kbank/{order_id?}', 'Checkout\CartController@kbankPayment'); 
        Route::get('check/{order_id}','Checkout\PaymentGatewayController@Check');
        //Route::get('tracking','Checkout\PaymentGatewayController@ReturnTransaction');
        Route::post('tracking','Checkout\PaymentGatewayController@ReturnTransaction');
        Route::get('success/{order_id}','Checkout\CartController@success');
        /****payplus*******/
        Route::get('payplus/{order_id?}', 'Checkout\CartController@payplusPayment'); 
        Route::get('paypluscheck/{order_id?}','Checkout\PaymentGatewayController@payplusCheck');
        Route::get('waiting/{order?}','Checkout\CartController@payplusWaiting');
        Route::post('payplus/tracking','Checkout\PaymentGatewayController@payplusReturnTransaction');
        Route::post('payplus/submit/{order_id?}','Checkout\CartController@createPayPlusOrder');
        Route::get('payplusresp','Checkout\OrderController@getPayplusResp');
        Route::post('submit-pay', 'Checkout\CartController@submitPayment');
    });
    /**/
    Route::group(['prefix' => 'popup'], function () {
        Route::post('savebargain', 'PopUpController@saveBargain');
        Route::get('getbargain/{id?}', 'PopUpController@getBargainPopUp');
        Route::get('checkbargain/{id?}/{qty?}', 'PopUpController@getCheckBargainPopUp');
        Route::get('getsellerproductpopUp/{id?}', 'PopUpController@getSellerProductPopUp');
        Route::post('saveprice', 'PopUpController@savePrice');

    });    
    
    /********social login url******/
    Route::get('auth/{provider}', 'Auth\LoginController@redirectToProvider');
    Route::get('auth/{provider}/callback', 'Auth\LoginController@handleProviderCallback');
    /***************************/

    Route::post('forgetpassword', 'Auth\ForgotPasswordController@sendResetLinkEmail')->name('forgetpassword');
    Route::get('password/reset/{token}', 'Auth\ResetPasswordController@showResetForm');
    Route::post('password/reset/{token}', 'Auth\ResetPasswordController@reset');
    
Route::get('mike-test', 'JsonController@mikeTestRoute');
Route::get('files/product/{sf?}/{m?}', 'JsonController@imageResize');
Route::get('convert/{mf?}/{img?}/{size?}/{sf?}', 'JsonController@convertImage');
Route::post('pagelimit', 'JsonController@pageLimit');
Route::get('seller-item-scv', 'SyncController@itemcsv');
Route::get('seller-customer-scv', 'SyncController@customercsv');
Route::get('seller-data', 'SyncController@sellerdata');
Route::get('new-seller-data', 'SyncController@newsellerdata');