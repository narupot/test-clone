<?php
function getBreadcrumbAdmin($section='', $module='', $page_type='',$web_type='admin') {

    //section =>  Ex: order, customer, cms, blog, product etc.
    //module => Ex: invoice, shipment, static page, product category etc.
    //page_type => add, edit, list, view, config
    if($web_type == 'admin'){
        $brc_str = '<li><a href="'.action('Admin\AdminHomeController@index').'">'.Lang::get('admin_common.home').'</a></li>';

        switch ($section) {

            case 'order' :

                if ($module == 'order' && $page_type == 'list') {
                    $brc_str .= '<li>'.Lang::get('admin_order.orders_listing').'</li>';
                }else {
                    $brc_str .= '<li><a href="'.action('Admin\Transaction\OrderController@index').'">'.Lang::get('admin_order.orders').'</a></li>';
                } 

                if($module == 'order_status' && $page_type != 'list') {
                    $brc_str .= '<li><a href="'.action('Admin\Transaction\OrderStatusController@index').'">'.Lang::get('admin_order.order_status').'</a></li>';
                }
                elseif ($module == 'invoice' && $page_type != 'list') {
                    $brc_str .= '<li><a href="'.action('Admin\Transaction\OrderInvoiceController@index').'">'.Lang::get('admin_order.invoice').'</a></li>';
                }
                elseif ($module == 'shipment' && $page_type != 'list') {
                    $brc_str .= '<li><a href="'.action('Admin\Transaction\OrderShipmentController@index').'">'.Lang::get('admin_order.shipment').'</a></li>';
                }
                elseif ($module == 'rma' && $page_type != 'list') {
                    $brc_str .= '<li><a href="'.action('Admin\Transaction\OrderRMAController@index').'">'.Lang::get('admin_order.rma').'</a></li>';
                } 
                elseif ($module == 'bank_payment' && $page_type != 'list') {
                    $brc_str .= '<li><a href="'.action('Admin\Transaction\PaymentController@index').'">'.Lang::get('admin_order.bank_payment').'</a></li>';
                }                                   
                break;

            case 'product' :
            
                
                if ($module == 'product' && $page_type == 'list') {
                    $brc_str .= '<li>'.Lang::get('admin_product.product_list').'</li>';
                }else {
                    $brc_str .= '<li><a href="'.action('Admin\Product\ProductController@index').'">'.Lang::get('admin_order.product').'</a></li>';
                } 
                if($module == 'badge' && $page_type != 'list') {
                    $brc_str .= '<li><a href="'.action('Admin\Badge\BadgeController@index').'">'.Lang::get('admin_badge.badge').'</a></li>';
                }elseif($module == 'shipping_type' && $page_type != 'list') {
                    $brc_str .= '<li><a href="'.action('Admin\ShippingType\ShippingTypeController@index').'">'.Lang::get('admin_shipping.shipping_type').'</a></li>';
                }elseif($module == 'brand' && $page_type != 'list') {
                    $brc_str .= '<li><a href="'.action('Admin\Brand\BrandController@index').'">'.Lang::get('admin_brand.brand').'</a></li>';
                }elseif($module == 'variant' && $page_type != 'list') {
                    $brc_str .= '<li><a href="'.action('Admin\Product\VariantController@allVariantList').'">'.Lang::get('admin_product.variant_list').'</a></li>';
                }
                elseif($module == 'commingsoon' && $page_type != 'list') {
                    $brc_str .= '<li><a href="'.action('Admin\Comingsoon\ComingsoonController@index').'">'.Lang::get('admin_common.commingsoon').'</a></li>';
                }elseif($module == 'attribute' && $page_type != 'list') {
                    $brc_str .= '<li><a href="'.action('Admin\Attribute\AttributeController@index').'">'.Lang::get('admin_attribute.attribute_set_management').'</a></li>';
                }
                break;

            case 'customer' :

                if($module == 'customer' && $page_type == 'list') {
                    $brc_str .= '<li>'.Lang::get('admin_customer.user_list').'</li>';
                } else {
                    $brc_str .= '<li><a href="'.action('Admin\Customer\UserController@approveUser').'">'.Lang::get('admin_customer.customer').'</a></li>';
                }

                if($module == 'customer_group' && $page_type != 'list') {
                    $brc_str .= '<li><a href="'.action('Admin\Customer\CustGroupController@index').'">'.Lang::get('admin_customer.customer_group').'</a></li>';
                }elseif($module == 'attribute' && $page_type != 'list') {
                    $brc_str .= '<li><a href="'.action('Admin\Customer\CustAttributeController@index').'">'.Lang::get('admin_customer.customer_attribute_list').'</a></li>';
                }
                break;

            case 'promotion' :

                if($module == 'promotion' && $page_type == 'list') {
                    $brc_str .= '<li>'.Lang::get('admin_product.promotion_management').'</li>';
                } else {
                    $brc_str .= '<li><a href="'.action('Admin\Promotion\PromotionController@index').'">'.Lang::get('admin_product.promotion_management').'</a></li>';
                }
                break;
            	
			case 'contactform' :

                if($module == 'contactform' && $page_type == 'submissionlist') {
                    $brc_str .= '<li>'.Lang::get('admin_contactform.contact_submission_list').'</li>';
                } else {
                    $brc_str .= '<li><a href="'.action('Admin\ContactForm\ContactFormController@contactFormSubmission').'">'.Lang::get('admin_contactform.contact_submission_list').'</a></li>';
                }
                break;	
			
			case 'flashsale' :

                if($module == 'flashsale' && $page_type == 'list') {
                    $brc_str .= '<li>'.Lang::get('admin_flashsale.manage_flashsale').'</li>';
                } else {
                    $brc_str .= '<li><a href="'.action('Admin\Flashsale\FlashsaleController@index').'">'.Lang::get('admin_flashsale.manage_flashsale').'</a></li>';
                }
                break;	
			
			case 'privacypolicy' :

                if($module == 'privacypolicy' && $page_type == 'list') {
                    $brc_str .= '<li>'.Lang::get('admin_privacypolicy.privacy_policy_list').'</li>';
                }elseif($module == 'privacypolicy' && $page_type == 'create') {
					$brc_str .= '<li><a href="'.action('Admin\Privacypolicy\PrivacyPolicyController@index').'">'.Lang::get('admin_privacypolicy.privacy_policy_list').'</a></li>';
                    $brc_str .= '<li>'.Lang::get('admin_privacypolicy.privacy_policy_create').'</li>';
                }elseif($module == 'privacypolicy' && $page_type == 'edit') {
					$brc_str .= '<li><a href="'.action('Admin\Privacypolicy\PrivacyPolicyController@index').'">'.Lang::get('admin_privacypolicy.privacy_policy_list').'</a></li>';
                    $brc_str .= '<li>'.Lang::get('admin_privacypolicy.privacy_policy_edit').'</li>';
                }elseif($module == 'privacypolicy' && $page_type == 'view') {
					$brc_str .= '<li><a href="'.action('Admin\Privacypolicy\PrivacyPolicyController@index').'">'.Lang::get('admin_privacypolicy.privacy_policy_list').'</a></li>';
                    $brc_str .= '<li>'.Lang::get('admin_privacypolicy.privacy_policy_view').'</li>';
                }elseif($module == 'privacypolicy' && $page_type == 'setting') {
					$brc_str .= '<li><a href="'.action('Admin\Privacypolicy\PrivacyPolicyController@index').'">'.Lang::get('admin_privacypolicy.privacy_policy_list').'</a></li>';
                    $brc_str .= '<li>'.Lang::get('admin_privacypolicy.privacy_policy_setting').'</li>';
                }
				else {
                    $brc_str .= '<li><a href="'.action('Admin\Privacypolicy\PrivacyPolicyController@index').'">'.Lang::get('admin_privacypolicy.privacy_policy_list').'</a></li>';
                }
                break;	
				
			case 'consentcheckbox' :

                if($module == 'consentcheckbox' && $page_type == 'list') {
                    $brc_str .= '<li>'.Lang::get('admin_privacypolicy.consent_list').'</li>';
                }elseif($module == 'consentcheckbox' && $page_type == 'create') {
					$brc_str .= '<li><a href="'.action('Admin\Privacypolicy\ConsentCheckboxController@index').'">'.Lang::get('admin_privacypolicy.consent_list').'</a></li>';
                    $brc_str .= '<li>'.Lang::get('admin_privacypolicy.consent_create').'</li>';
                }elseif($module == 'consentcheckbox' && $page_type == 'edit') {
					$brc_str .= '<li><a href="'.action('Admin\Privacypolicy\ConsentCheckboxController@index').'">'.Lang::get('admin_privacypolicy.consent_list').'</a></li>';
                    $brc_str .= '<li>'.Lang::get('admin_privacypolicy.consent_edit').'</li>';
                }elseif($module == 'consentlog' && $page_type == 'list') {
                    $brc_str .= '<li>'.Lang::get('admin_privacypolicy.consent_log').'</li>';
                }elseif($module == 'actionlog' && $page_type == 'list') {
                    $brc_str .= '<li>'.Lang::get('admin_privacypolicy.action_log').'</li>';
                }
				else {
                    $brc_str .= '<li><a href="'.action('Admin\Privacypolicy\ConsentCheckboxController@index').'">'.Lang::get('admin_privacypolicy.consent_list').'</a></li>';
                }
                break;

			case 'cookie' :

                if($module == 'cookie' && $page_type == 'list') {
                    $brc_str .= '<li>'.Lang::get('admin_privacypolicy.cookie_list').'</li>';
                }elseif($module == 'cookie' && $page_type == 'create') {
					$brc_str .= '<li><a href="'.action('Admin\Privacypolicy\CookieController@index').'">'.Lang::get('admin_privacypolicy.cookie_list').'</a></li>';
                    $brc_str .= '<li>'.Lang::get('admin_privacypolicy.cookie_create').'</li>';
                }elseif($module == 'cookie' && $page_type == 'edit') {
					$brc_str .= '<li><a href="'.action('Admin\Privacypolicy\CookieController@index').'">'.Lang::get('admin_privacypolicy.cookie_list').'</a></li>';
                    $brc_str .= '<li>'.Lang::get('admin_privacypolicy.cookie_edit').'</li>';
                }elseif($module == 'cookieconsentlog' && $page_type == 'list') {
                    $brc_str .= '<li>'.Lang::get('admin_privacypolicy.cookie_consent_log').'</li>';
                }
				else {
                    $brc_str .= '<li><a href="'.action('Admin\Privacypolicy\CookieController@index').'">'.Lang::get('admin_privacypolicy.cookie_list').'</a></li>';
                }
                break;	

			case 'cookiegroup' :

                if($module == 'cookiegroup' && $page_type == 'list') {
                    $brc_str .= '<li>'.Lang::get('admin_privacypolicy.cookie_group_list').'</li>';
                }elseif($module == 'cookiegroup' && $page_type == 'create') {
					$brc_str .= '<li><a href="'.action('Admin\Privacypolicy\CookieGroupController@index').'">'.Lang::get('admin_privacypolicy.cookie_group_list').'</a></li>';
                    $brc_str .= '<li>'.Lang::get('admin_privacypolicy.cookie_group_create').'</li>';
                }elseif($module == 'cookiegroup' && $page_type == 'edit') {
					$brc_str .= '<li><a href="'.action('Admin\Privacypolicy\CookieGroupController@index').'">'.Lang::get('admin_privacypolicy.cookie_group_list').'</a></li>';
                    $brc_str .= '<li>'.Lang::get('admin_privacypolicy.cookie_group_edit').'</li>';
                }
				else {
                    $brc_str .= '<li><a href="'.action('Admin\Privacypolicy\CookieGroupController@index').'">'.Lang::get('admin_privacypolicy.cookie_group_list').'</a></li>';
                }
                break;		
				
			case 'visitor' :

                if ($module == 'visitor' && $page_type == 'setting') {
                    $brc_str .= '<li>'.Lang::get('admin_visitor.visitor_setting').'</li>';
                } else {
                    $brc_str .= '<li><a href="'.action('Admin\Visitor\VisitorSettingController@index').'">'.Lang::get('admin_visitor.visitor_list').'</a></li>';
                }
                break;	
				
			case 'customershoppingcart' :

                if($module == 'customershoppingcart' && $page_type == 'list') {
					$brc_str .= '<li><a href="'.action('Admin\Customer\UserController@approveUser').'">'.Lang::get('admin_customer.customer').'</a></li>';
                    $brc_str .= '<li>'.Lang::get('admin_customer.customer_shopping_cart').'</li>';
                } 
				elseif ($module == 'productshoppingcart' && $page_type == 'productcartlist') {
					$brc_str .= '<li><a href="'.action('Admin\Reports\ReportsController@productsReport').'">'.Lang::get('admin_reports.product_reports').'</a></li>';
                    $brc_str .= '<li>'.Lang::get('admin_reports.product_in_cart').'</li>';
                } else {
                    $brc_str .= '<li><a href="'.action('Admin\CustomerShoppingCart\CustomerShoppingCartController@index').'">'.Lang::get('admin_customer.customer_shopping_cart').'</a></li>';
                }
                break;	
			
			case 'searchterm' :

                if($module == 'searchterm' && $page_type == 'list') {
					$brc_str .= '<li><a href="'.action('Admin\Reports\ReportsController@productsReport').'">'.Lang::get('admin_reports.product_reports').'</a></li>';
                    $brc_str .= '<li>'.Lang::get('admin_reports.search_term').'</li>';
                } 
				else {
                    $brc_str .= '<li><a href="'.action('Admin\SearchTerm\SearchTermController@index').'">'.Lang::get('admin_reports.search_term').'</a></li>';
                }
                break;	
				
                
            case 'cms' :
                if($module == 'cms' && $page_type == 'list') {
                    $brc_str .= '<li>'.Lang::get('admin_cms.page_list').'</li>';
                } else {
                    $brc_str .= '<li><a href="'.action('Admin\Page\StaticPageController@index').'">'.Lang::get('admin_cms.page_list').'</a></li>';
                }
                break;
           case 'shoporder' :
                if($module == 'shoporder' && $page_type == 'list') {
                    $brc_str .= '<li>'.Lang::get('order.shop_order_list').'</li>';
                } else {
                    $brc_str .= '<li><a href="'.action('Admin\Transaction\ShopOrderController@index').'">'.Lang::get('order.shop_order_list').'</a></li>';
                }
                break;
           case 'order' :
                if($module == 'order' && $page_type == 'list') {
                    $brc_str .= '<li>'.Lang::get('order.order_list').'</li>';
                } else {
                    $brc_str .= '<li><a href="'.action('Admin\Transaction\OrderController@index').'">'.Lang::get('order.order_list').'</a></li>';
                }
                break;
           case 'package' :
                if($module == 'package' && $page_type == 'list') {
                    $brc_str .= '<li>'.Lang::get('package.list_package').'</li>';
                } else {
                    $brc_str .= '<li><a href="'.action('Admin\Package\PackageController@index').'">'.Lang::get('package.list_package').'</a></li>';
                }
                break;
           case 'unit' :
                if($module == 'unit' && $page_type == 'list') {
                    $brc_str .= '<li>'.Lang::get('admin_product.list_unit').'</li>';
                } else {
                    $brc_str .= '<li><a href="'.action('Admin\Unit\UnitController@index').'">'.Lang::get('admin_product.list_unit').'</a></li>';
                }
                break;
           case 'seller' :
                if($module == 'seller' && $page_type == 'list') {
                    $brc_str .= '<li>'.Lang::get('admin_customer.customer_seller_list').'</li>';
                } else {
                    $brc_str .= '<li><a href="'.action('Admin\Customer\SellerController@index').'">'.Lang::get('admin_customer.customer_seller_list').'</a></li>';
                }
                break;
           case 'buyer' :
                if($module == 'buyer' && $page_type == 'list') {
                    $brc_str .= '<li>'.Lang::get('admin_customer.customer_buyer_list').'</li>';
                } else {
                    $brc_str .= '<li><a href="'.action('Admin\Customer\BuyerController@index').'">'.Lang::get('admin_customer.customer_buyer_list').'</a></li>';
                }
                break;
           case 'avatar' :
                if($module == 'avatar' && $page_type == 'list') {
                    $brc_str .= '<li>'.Lang::get('admin.avatar_list').'</li>';
                } else {
                    $brc_str .= '<li><a href="'.action('Admin\Config\AvatarController@index').'">'.Lang::get('admin.avatar_list').'</a></li>';
                }
                break;
             case 'faq' :
                if($module == 'faq' && $page_type == 'list') {
                    $brc_str .= '<li>'.Lang::get('admin_faq.faq_list').'</li>';
                } else {
                    $brc_str .= '<li><a href="'.action('Admin\Faq\FaqController@index').'">'.Lang::get('admin_faq.faq_list').'</a></li>';
                }
                break;

            case 'newsletter' :
                if($module == 'newsletter' && $page_type == 'list') {
                    $brc_str .= '<li>'.Lang::get('admin_newsletter.list_newsletter').'</li>';
                } else {
                    $brc_str .= '<li><a href="'.action('Admin\Newsletter\NewsletterController@subscriberlisting').'">'.Lang::get('admin_newsletter.list_newsletter').'</a></li>';
                }
                break;
            case 'newsletter_template' :
                if($module == 'newsletter_template' && $page_type == 'list') {
                    $brc_str .= '<li>'.Lang::get('admin_newsletter.newsletter_template_list').'</li>';
                } 
                elseif($module == 'newsletter_queue' && $page_type == 'list'){
                    $brc_str .= '<li>'.Lang::get('admin_newsletter.newsletter_queue_list').'</li>';
                }
                elseif($module == 'newsletter_template' && $page_type == 'edit'){
                    $brc_str .= '<li><a href="'.action('Admin\Newsletter\NewsletterTemplateController@index').'">'.Lang::get('admin_newsletter.list_newsletter').'</a></li><li>'.Lang::get('admin_newsletter.newsletter_template_edit').'</li>';
                }
                elseif($module == 'newsletter_queue' && $page_type == 'view'){
                    $brc_str .= '<li><a href="'.action('Admin\Newsletter\NewsletterTemplateController@newsletterQueue').'">'.Lang::get('admin_newsletter.newsletter_queue_list').'</a></li><li>'.Lang::get('admin_newsletter.newsletter_queue_detail').'</li>';
                }
                else{
                    $brc_str .= '<li>'.Lang::get('admin_newsletter.newsletter_template_create').'</li>';
                }
                break;
            case 'brand' :
                if($module == 'brand' && $page_type == 'list') {
                    $brc_str .= '<li>'.Lang::get('admin_brand.brand').'</li>';
                } else {
                    $brc_str .= '<li><a href="'.action('Admin\Brand\BrandController@index').'">'.Lang::get('admin_brand.brand').'</a></li>';
                }
                break;
             case 'user_rating_list' :
                if($module == 'user_rating' && $page_type == 'list') {
                    $brc_str .= '<li>'.Lang::get('admin_rating.user_ratings').'</li>';
                } else {
                    $brc_str .= '<li><a href="'.action('Admin\Rating\RatingController@allRatings').'">'.Lang::get('admin_rating.user_ratings').'</a></li>';
                }
                break;   
            case 'backorder' :
                if($module == 'backorder' && $page_type == 'list') {
                    $brc_str .= '<li>'.Lang::get('admin_order.back_order_list').'</li>';
                } else {
                    $brc_str .= '<li><a href="'.action('Enterprise\Admin\Backorder\BackOrderController@index').'">'.Lang::get('admin_order.back_order_list').'</a></li>';
                }
                break;
            case 'store' :
                if($module == 'store' && $page_type == 'list') {
                    $brc_str .= '<li>'.Lang::get('admin_store.manage_locations').'</li>';
                } else {
                    $brc_str .= '<li><a href="'.action('Enterprise\Admin\Store\ManageStoreController@locations').'">'.Lang::get('admin_store.manage_locations').'</a></li>';
                }
                break;
            case 'salepersion' :
                if($module == 'salepersion' && $page_type == 'list') {
                    $brc_str .= '<li>'.Lang::get('admin_store.manage_salesperson').'</li>';
                } else {
                    $brc_str .= '<li><a href="'.action('Enterprise\Admin\Store\ManageStoreController@salesperson').'">'.Lang::get('admin_store.manage_salesperson').'</a></li>';
                }
                break;
            case 'block' :
                if($module == 'block' && $page_type == 'list') {
                    $brc_str .= '<li>'.Lang::get('cms.block_list').'</li>';
                } else {
                    $brc_str .= '<li><a href="'.action('Admin\Block\StaticBlockController@index').'">'.Lang::get('cms.block_list').'</a></li>';
                }
                if($module == 'slider_listing' && $page_type != 'list') {
                    $brc_str .= '<li><a href="'.action('Admin\CmsSlider\CmsSliderController@index').'">'.Lang::get('admin_slider.slider_list').'</a></li>';
                }elseif($module == 'layout' && $page_type != 'list') {
                    $brc_str .= '<li><a href="'.action('Admin\Block\BlockController@index').'">'.Lang::get('admin_cms.layout_management').'</a></li>';
                }elseif($module == 'bannergroup' && $page_type != 'list') {
                    $brc_str .= '<li><a href="'.action('Admin\Banner\BannerGroupController@index').'">'.Lang::get('admin_cms.banner_group_list').'</a></li>';
                }elseif($module == 'banner' && $page_type != 'list') {
                    $brc_str .= '<li><a href="'.action('Admin\Banner\BannerController@index').'">'.Lang::get('admin_cms.banner_list').'</a></li>';
                }

                break;
            case 'contact' :
                $brc_str .= '<li>'.Lang::get('admin_contactform.edit_contact_field').'</li>';
                break;
            case 'menu' :
                if($module == 'menu' && $page_type == 'list') {
                    $brc_str .= '<li>'.Lang::get('admin_menu.my_menu').'</li>';
                } else {
                    $brc_str .= '<li><a href="'.action('Admin\Menu\MenuController@index').'">'.Lang::get('admin_menu.my_menu').'</a></li>';
                }
                break;   

            case 'blogcategory' :
                if ($module == 'blogcategory' && $page_type == 'list') {
                    $brc_str .= '<li>'.Lang::get('admin_blog.blog_category').'</li>';
                }   else {
                    $brc_str .= '<li><a href="'.action('Admin\BlogCategory\BlogCategoryController@index').'">'.Lang::get('admin_blog.blog_category').'</a></li>';
                }
                break;
            case 'blog' :
                if ($module == 'blog' && $page_type == 'list') {
                    $brc_str .= '<li>'.Lang::get('admin_blog.blog_list').'</li>';
                }   else {
                    $brc_str .= '<li><a href="'.action('Admin\Blog\BlogController@index').'">'.Lang::get('admin_blog.blog_list').'</a></li>';
                }
                if($module == 'widget' && $page_type != 'list') {
                    $brc_str .= '<li><a href="'.action('Admin\Widget\WidgetController@index').'">'.Lang::get('admin_blog.widget_list').'</a></li>';
                }
                break;        

            case 'config' :
                // if ($module == 'config' && $page_type == 'list') {
                //     $brc_str .= '<li>'.Lang::get('admin_setting.system_config').'</li>';
                // }   else {
                //     $brc_str .= '<li><a href="'.action('Admin\Config\SystemConfigController@show','setting').'">'.Lang::get('admin_setting.system_config').'</a></li>';
                // }

                if($module == 'payment' && $page_type != 'list') {
                    $brc_str .= '<li><a href="'.action('Admin\Config\PaymentOptionController@index').'">'.Lang::get('admin_payment.payment_list').'</a></li>';
                }elseif($module == 'bank' && $page_type != 'list') {
                    $brc_str .= '<li><a href="'.action('Admin\Config\PaymentBankController@index').'">'.Lang::get('admin_payment.bank_list').'</a></li>';
                }elseif($module == 'user' && $page_type != 'list') {
                    $brc_str .= '<li><a href="'.action('Admin\User\AdminController@index').'">'.Lang::get('admin.team_members').'</a></li>';
                }elseif($module == 'role' && $page_type != 'list') {
                    $brc_str .= '<li><a href="'.action('Admin\Role\GroupController@index').'">'.Lang::get('admin.roles_list').'</a></li>';
                }elseif($module == 'pdfdesign' && $page_type != 'list') {
                    $brc_str .= '<li><a href="'.action('Admin\Config\PdfDesignConfigController@index').'">'.Lang::get('admin.pdfdesign').'</a></li>';
                }elseif($module == 'seoglobal' && $page_type != 'list') {
                    $brc_str .= '<li><a href="'.action('Admin\SEO\SeoGlobalController@index').'">'.Lang::get('admin_seo.seo_template_listing').'</a></li>';
                }elseif($module == 'seo' && $page_type != 'list') {
                    $brc_str .= '<li><a href="'.action('Admin\SEO\SeoController@products').'">'.Lang::get('admin_seo.product_seo_management').'</a></li>';
                }elseif($module == 'seopage' && $page_type != 'list') {
                    $brc_str .= '<li><a href="'.action('Admin\SEO\SeoController@pages').'">'.Lang::get('admin_seo.global_seo_management').'</a></li>';
                }elseif($module == 'currency' && $page_type != 'list') {
                    $brc_str .= '<li><a href="'.action('Admin\Config\CurrencyController@index').'">'.Lang::get('admin.currency_list').'</a></li>';
                }elseif($module == 'language' && $page_type != 'list') {
                    $brc_str .= '<li><a href="'.action('Admin\Config\LanguageController@index').'">'.Lang::get('admin.language_list').'</a></li>';
                }elseif($module == 'mail' && $page_type != 'list') {
                    $brc_str .= '<li><a href="'.action('Admin\Notification\MailTemplateController@index').'">'.Lang::get('admin_notification.email_event_management').'</a></li>';
                }elseif($module == 'mastertemplate' && $page_type != 'list') {
                    $brc_str .= '<li><a href="'.action('Admin\Notification\MailTemplateController@masterTempateList').'">'.Lang::get('admin_notification.master_templates').'</a></li>';
                }elseif($module == 'tableConfig' && $page_type != 'list') {
                    $brc_str .= '<li><a href="'.action('Admin\Table\TableConfigController@columnConfig').'">'.Lang::get('admin_common.table_config').'</a></li>';
                }elseif($module == 'translation-module' && $page_type != 'list') {
                    $brc_str .= '<li><a href="'.action('Admin\Translation\TranslationModuleController@index').'">'.Lang::get('admin_translation.language_module_list').'</a></li>';
                }elseif($module == 'translation' && $page_type != 'list') {
                    $brc_str .= '<li><a href="'.action('Admin\Translation\TranslationController@index').'">'.Lang::get('admin_translation.general_translation').'</a></li>';
                }elseif($module == 'translation-menu' && $page_type != 'list') {
                    $brc_str .= '<li><a href="'.action('Admin\Translation\MenuController@index').'">'.Lang::get('admin.menu_list').'</a></li>';
                }elseif($module == 'country' && $page_type != 'list') {
                    $brc_str .= '<li><a href="'.action('Admin\Country\CountryController@index').'">'.Lang::get('admin_country.country_list').'</a></li>';
                }elseif($module == 'state' && $page_type != 'list') {
                    $brc_str .= '<li><a href="'.action('Admin\Country\ProvinceController@index').'">'.Lang::get('admin_country.province_state_list').'</a></li>';
                }elseif($module == 'city' && $page_type != 'list') {
                    $brc_str .= '<li><a href="'.action('Admin\Country\CityController@index').'">'.Lang::get('admin_country.city_district_list').'</a></li>';
                }elseif($module == 'shipping-table-rates' && $page_type != 'list') {
                    $brc_str .= '<li><a href="'.action('Admin\ShippingProfile\ShippingRateTableController@index').'">'.Lang::get('admin_shipping.shipping_rate_table').'</a></li>';
                }
                elseif($module == 'delivery-at-address' && $page_type != 'list') {
                    $brc_str .= '<li><a href="'.action('Admin\Config\SystemConfigController@index').'">'.Lang::get('admin_shipping.delivery_at_address').'</a></li>';
                }
                    
                break;
            case 'widget':

                if($module == 'widget' && $page_type != 'list') {
                    $brc_str .= '<li><a href="'.action('Admin\Widget\WidgetController@index').'">'.Lang::get('admin_blog.widget_list').'</a></li>';
                }
                break;

            case 'category' :

                if($module == 'category' && $page_type == 'list') {
                    $brc_str .= '<li>'.Lang::get('admin_category.cat_title').'</li>';
                } else if($module == 'category' && $page_type == 'create') {
                    $brc_str .= '<li><a href="'.action('Admin\CategoryManagement\CategoryController@index').'">'.Lang::get('admin_category.category').'</a></li>';
                    $brc_str .= '<li>'.Lang::get('admin_category.product_master').'</a></li>';
                }else {
                    $brc_str .= '<li><a href="'.action('Admin\Category\CategoryController@index').'">'.Lang::get('admin_category.cat_title').'</a></li>';
                }
                break;
            case 'attribute' :
                if($module == 'attribute' && $page_type == 'list') {
                    $brc_str .= '<li>'.Lang::get('admin_attribute.attribute_list').'</li>';
                } else {
                    $brc_str .= '<li><a href="'.action('Admin\Attribute\AttributeController@index').'">'.Lang::get('admin_attribute.attribute_list').'</a></li>';
                }
                break;
        }
        return $brc_str;
    }else{
        $brc_str = '<li><a href="'.action('HomeController@index').'">'.Lang::get('admin_common.home').'</a></li>';

        switch ($section) {
            case 'myaccount' :

                if($module == 'myaccount' && $page_type == 'list') {
                    $brc_str .= '<li>'.Lang::get('common.myaccount').'</li>';
                } else {
                    $brc_str .= '<li><a href="'.action('Admin\RoomType\RoomTypeController@index').'">'.Lang::get('admin_room.room_type_list').'</a></li>';
                }
                break;
            case 'rewardpoint' :

                if($module == 'rewardpoint' && $page_type == 'list') {
                    $brc_str .= '<li>'.Lang::get('customer.reward_points').'</li>';
                } 
                break;
            case 'mycollection' :
                if($module == 'mycollection' && $page_type == 'list') {
                    $brc_str .= '<li>My collection</li>';
                } 
                break;
            case 'vat' :
                if($module == 'vat' && $page_type == 'list') {
                    $brc_str .= '<li>'.Lang::get('customer.vat').'</li>';
                } 
                break;
            case 'newsletter' :

                if($module == 'newsletter' && $page_type == 'list') {
                    $brc_str .= '<li>'.Lang::get('customer.newsletter').'</li>';
                } 
                break;
            case 'pinblog' :

                if($module == 'pinblog' && $page_type == 'list') {
                    $brc_str .= '<li>'.Lang::get('customer.pinblog').'</li>';
                } 
                break;
            case 'address' :

                if($module == 'address' && $page_type == 'list') {
                    $brc_str .= '<li>'.Lang::get('customer.address').'</li>';
                } 
                break;
            case 'order' :

                if ($module == 'order' && $page_type == 'list') {
                    $brc_str .= '<li>'.Lang::get('order.order_list').'</li>';
                }else {
                    $brc_str .= '<li><a href="'.action('User\Order\UserOrderController@index').'">'.Lang::get('order.order_list').'</a></li>';
                }
                break;
            case 'completeorder' :

                if ($module == 'completeorder' && $page_type == 'list') {
                    $brc_str .= '<li>'.Lang::get('order.completed_order').'</li>';
                }else {
                    $brc_str .= '<li><a href="'.action('User\Order\UserOrderController@completedOrder').'">'.Lang::get('order.completed_order').'</a></li>';
                }
                break;
            case 'invoice' :

                if ($module == 'invoice' && $page_type == 'list') {
                    $brc_str .= '<li>'.Lang::get('order.invoice_list').'</li>';
                }else {
                    $brc_str .= '<li><a href="'.action('User\Order\UserOrderInvoiceController@index').'">'.Lang::get('order.invoice_list').'</a></li>';
                }
                break;

            case 'trackorder' :

                if ($module == 'trackorder' && $page_type == 'list') {
                    $brc_str .= '<li><a href="'.action('User\Order\UserOrderController@index').'">'.Lang::get('order.order_list').'</a></li> <li>'.Lang::get('order.track_order').'</li>';
                }else {
                    $brc_str .= '<li><a href="'.action('OrderPaymentController@trackOrder').'">'.Lang::get('order.track_order').'</a></li>';
                }
                break;
            case 'shipment' :
                if($module == 'shipment' && $page_type == 'list') {
                    $brc_str .= '<li>'.Lang::get('order.shipment_list').'</li>';
                } else {
                    $brc_str .= '<li><a href="'.action('User\Order\UserOrderShipmentController@index').'">'.Lang::get('order.shipment_list').'</a></li>';
                }
                break;
            case 'wishlist' :
                if($module == 'wishlist' && $page_type == 'list') {
                    $brc_str .= '<li>'.Lang::get('customer.wishlist').'</li>';
                } 
                break;
            case 'review' :
                if($module == 'review' && $page_type == 'list') {
                    $brc_str .= '<li>'.Lang::get('customer.review').'</li>';
                } else {
                    $brc_str .= '<li><a href="'.action('User\ReviewController@productReviewList').'">'.Lang::get('customer.review_list').'</a></li> <li>'.Lang::get('customer.review').'</li>';
                } 
                break;
            case 'rma' :
                if($module == 'rma' && $page_type == 'list') {
                    $brc_str .= '<li>'.Lang::get('customer.rma').'</li>';
                } else {
                    $brc_str .= '<li><a href="'.action('User\Order\UserOrderRmaController@listRmaData').'">'.Lang::get('customer.rma_list').'</a></li> <li>'.Lang::get('customer.rma').'</li>';
                } 
                break;
            case 'rmacreatelist' :
                if($module == 'rmacreatelist' && $page_type == 'list') {
                    $brc_str .= '<li>'.Lang::get('customer.rma').'</li>';
                } else {
                    $brc_str .= '<li><a href="'.action('User\Order\UserOrderRmaController@listRmaData').'">'.Lang::get('order.create_returns').'</a></li>';
                } 
                break;
            case 'compare' :
                if($module == 'compare' && $page_type == 'list') {
                    $brc_str .= '<li>'.Lang::get('product.compare').'</li>';
                } 
                break;

        }
        //dd($brc_str);

        return $brc_str;
    }
}
?>