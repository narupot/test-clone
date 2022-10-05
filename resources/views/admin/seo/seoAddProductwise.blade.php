@extends('layouts/admin/default')

@section('title')
    @lang('seo.global_seo')
@stop

@section('header_styles')

    <!--page level css -->
    <link href="{{ asset('assets/vendors/jasny-bootstrap/css/jasny-bootstrap.css') }}" rel="stylesheet">
    <link href="{{ asset('assets/vendors/iCheck/css/all.css') }}" rel="stylesheet" type="text/css" />
    <link href="{{ asset('assets/css/pages/form_layouts.css') }}" rel="stylesheet" type="text/css" />
    <link href="{{ asset('assets/vendors/bootstrapvalidator/css/bootstrapValidator.min.css') }}" type="text/css" rel="stylesheet">
    <!-- end of page level css -->
    
@stop

@section('content')

    <!-- Right side column. Contains the navbar and content of the page -->



    <div class="content">
        <div class="header-title">
            <h1 class="title">@lang('seo.add_edit_product_seo') : {{ $result->getProductDetail->name}}</h1>
            
            <!--div class="float-right btn-groups">

                <button class="btn" type="submit">@lang('attribute.save')</button>
            </div-->
           
        </div>
      
 
        <div class="content-wrap">
          <div class="breadcrumb">
                <ul class="bredcrumb-menu">
                    {!!getBreadcrumbAdmin('config','seo')!!}
                </ul>
            </div>
            <form id="addCountryForm" action="{{ action('Admin\SEO\SeoController@addseoproductstore') }}" method="post" enctype="multipart/form-data">
              {{ csrf_field() }}

              <input type="hidden" name="product_id" value="{{$result->id}}">
                     
              <input type="hidden" name="user_id" value="">
                     
              <!--@if(isset($type) && $type == 'blog')-->

                 <!--input type="hidden" name="type" value="blog"-->

              <!--@endif-->  

              <div class="row">
                <div class="col-sm-5">
                    <div class="form-group">
                       <label>@lang('seo.template_type')</label>
                      <!--1=>Auto, 2=> Admin Manual Template, 3=>Manual-->
                      <div class="templateType radio-group">
                          <label class="radio-wrap mt-2">
                             {!! Form::radio('template_type', '2', ($result->template_type == 2),['class' => '', 'id'=>'admintm'] ) !!}
                             <span class="radio-label"> 
                               @lang('seo.admin_manual_template') 
                             </span>
                          </label>
                          <label class="radio-wrap mt-2">
                            {!! Form::radio('template_type', '3', ($result->template_type == 3),['class' => '', 'id'=>'manual'] ) !!}
                            <span class="radio-label"> @lang('seo.manual') </span>
                          </label>
                      </div>
                    </div>
                    <div class="manual hidediv" 
                         @if($result->template_type != '3') 
                           style="display:none" 
                         @endif 
                         >                               
                        <div class="form-group">
                            <label>@lang('seo.meta_title')</label>
                            <div class="formgroup-lang">
                                
                               {!! CustomHelpers::textWithEditLanuage('text','meta_title', $tblProductDesc, $result->id, 'product_id', 'meta-title') !!}
                                
                            </div>
                        </div>

                         <div class="form-group">
                            <label>@lang('seo.meta_description')</label>
                            <div class="formgroup-lang">
                                
                                 {!! CustomHelpers::textWithEditLanuage('textarea','meta_description', $tblProductDesc, $result->id, 'product_id', 'meta-description') !!}  
                                
                            </div>
                        </div>


                        <div class="form-group">
                            <label>@lang('seo.meta_keywords')</label>
                            <div class="formgroup-lang">                      
                                {!! CustomHelpers::textWithEditLanuage('text','meta_keyword', $tblProductDesc, $result->id, 'product_id', 'meta-keyword') !!} 
                                
                            </div>
                        </div>

                    </div>
                    <div class="admin_template hidediv" 
                      @if($result->template_type != '2') 
                                  style="display:none" 
                      @endif

                      >          
                       <div class="form-group">
                          <label>@lang('seo.admin_template')</label>
                            <div class="formgroup-lang">
                                 {!! Form::select('admin_template_id',  $seoSuperAdminTemplate,  null, ['class' => 'form-control']) 
                                 !!}                                      
                            </div>
                       </div>

                    </div>
                    <div class="form-group">
                        <label for="form-text-input">@lang('seo.meta_robots')</label>
                        <label class="check-wrap"> 
                            <input type="checkbox" 
                              name="meta_robots[]" 
                              value="nofollow" class="form-control" @if(!empty($meta_robots) && in_array( 'nofollow', $meta_robots)) checked = "checked" @endif >  
                             <span class="chk-label">@lang('seo.nofollow')</span>
                        </label> 
                        <label class="check-wrap">
                            <input type="checkbox" 
                              name="meta_robots[]" 
                              value="noindex" class="form-control" 
                               
                               @if(!empty($meta_robots) && in_array('noindex', $meta_robots)) checked = "checked" @endif
                                
                              >  
                            <span class="chk-label">@lang('seo.noindex')</span>
                        </label>
                        <label class="check-wrap"> 
                            <input type="checkbox" 
                              name="meta_robots[]" 
                              value="noodp" class="form-control" 

                                @if(!empty($meta_robots) && in_array('noodp', $meta_robots)) checked = "checked" @endif

                              >  
                             <span class="chk-label">@lang('seo.noodp')</span>
                        </label>
                        <label class="check-wrap">
                            <input type="checkbox" 
                              name="meta_robots[]" 
                              value="noydir" class="form-control" 
                                @if(!empty($meta_robots) && in_array('noydir', $meta_robots)) checked = "checked" @endif

                              >  
                             <span class="chk-label">@lang('seo.noydir')</span>
                        </label>
                        <label class="check-wrap"> 
                            <input type="checkbox" 
                              name="meta_robots[]" 
                              value="follow" class="form-control" 

                              @if(!empty($meta_robots) && in_array('follow', $meta_robots)) checked = "checked" @endif


                              >  
                             <span class="chk-label">@lang('seo.follow')</span>
                        </label>
                        <label class="check-wrap">
                            <input type="checkbox" 
                              name="meta_robots[]" 
                              value="index" class="form-control" 

                               @if(!empty($meta_robots) && in_array('index', $meta_robots)) checked = "checked" @endif


                              ><span class="chk-label">@lang('seo.index')</span>
                        </label>
                    </div>  
                              
                                                              
                    <div class="form-group">
                        <div class="btns-group">
                            <button type="button" class="btn btn-back" onclick="history.go(-1); return false;">@lang('seo.back')</button>
                            <button type="submit" class="btn btn-save btn-primary">@lang('seo.submit')</button>
                        </div>
                    </div>

                </div>
              </div>

            
         

        



              
          </form>
            
    
          <div class="row">
            <div class="col-md-4">
                  <strong>  @lang('seo.varible_use_in_this_form_with_content')</strong> 
                 <h4><strong>@lang('seo.use_any_where')</strong></h4>
                 [SITE_NAME] ===> @lang('seo.use_for_website_name') <br/>
                 [SITE_URL]  ====> @lang('seo.use_for_website_url') <br/>

                <h4><strong>@lang('seo.only_for_any_product')</strong></h4>
                   [PRODUCT_NAME]  ====> @lang('seo.use_for_product_name') <br/>
                   [PRODUCT_SKU]  ====> @lang('seo.use_for_product_sku') <br/>
                   [PRODUCT_PRICE]  ====> @lang('seo.use_for_product_price') <br/>

                 <!--h4><strong>@lang('seo.for_any_category')</strong></h4>
                   [CATEGORY_NAME]  ====> @lang('seo.use_for_category_name') <br/-->

               
                  <!--h4><strong>@lang('seo.for_shop')</strong></h4>

                  [SHOP_NAME]  ====> @lang('seo.use_for_shop_name') <br/>

                  [SHOP_COUNTRY]  ====> @lang('seo.use_for_shop_country') <br/>

                  [SHOP_TOTAL_SALE]  ====> @lang('seo.use_for_shop_total_sale')<br/>
               
                  [SHOP_OPENED_SINCE]  ====> @lang('seo.use_for_shop_opened_since')<br/>

                  [SELLER_NAME]  ====> @lang('seo.use_for_shop_seller_name')<br/-->

                  <!--h4><strong>@lang('seo.for_cms')</strong></h4>
                  
                  [CMS_TITLE]  ====> @lang('seo.use_for_cms_title') <br/-->

                  

               </div>
          </div>
                   



        </div>
     
     </div>


@stop

@section('footer_scripts')
 
    <!-- begining of page level js -->
    <script src="{{ asset('assets/vendors/jasny-bootstrap/js/jasny-bootstrap.js') }}"></script>
    <script src="{{ asset('assets/vendors/iCheck/js/icheck.js') }}"></script>
    <script src="{{ asset('assets/js/pages/form_layouts.js') }}"></script>
    <script src="{{ asset('assets/vendors/bootstrapvalidator/js/bootstrapValidator.min.js') }}" type="text/javascript"></script>
    <script src="{{ asset('assets/js/pages/validateCountry.js') }}" type="text/javascript"></script>
    <!-- end of page level js -->   

     <script>
jQuery(document).ready(function(e){
  jQuery('.templateType input[name="template_type"]').click(function(e){
        
        var curr = jQuery(this).val();
        jQuery('.hidediv').hide();
        if(curr == '3'){
           jQuery('.manual').show();
        }else if(curr == '2'){
          jQuery('.admin_template').show();

        }


     })


})
</script> 
    
@stop

