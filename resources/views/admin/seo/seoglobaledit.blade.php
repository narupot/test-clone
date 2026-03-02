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
    <div class="content">
        <div class="header-title">
            <h1 class="title">@lang('seo.edit_seo_template') : @if(isset($result->title)) {{$result->title}} @else {{'N/A'}} @endif</h1>
         </div>
      
 
        <div class="content-wrap">
          <div class="breadcrumb">
                <ul class="bredcrumb-menu">
                    {!!getBreadcrumbAdmin('config','seoglobal')!!}
                </ul>
            </div>
            <form id="addCountryForm" action="{{ action('Admin\SEO\SeoGlobalController@update', $result->id) }}" method="post" enctype="multipart/form-data">
                {{ csrf_field() }}
                <input name="_method" value="PUT" type="hidden">

                <div class="row">
                  <div class="col-sm-6">
                      <div class="form-group">
                          <label>@lang('seo.template_user_for')</label>                         
                          {!! Form::select('type', ['1'=>Lang::get('seo.products'), '2'=>Lang::get('seo.others')],  $result->type,['class' => 'form-control']) !!}                        
                      </div> 
                      <div class="form-group">
                         <label>@lang('seo.title')<i class="strick">*</i></label>                          
                          <input type="text" name="title" value="{{ old('title', $result->title) }}" class="form-control" placeholder="eg: Product, Category etc.">
                          @if($errors->has('title'))
                              <p class="error error-msg">{{ $errors->first('title') }}</p>
                          @endif                                    
                         
                      </div>
                      <div class="form-group">
                          <label>@lang('seo.meta_title')</label>
                          <div class="formgroup-lang">
                            {!! CustomHelpers::textWithEditLanuage('text','meta_title', $tblSeoGlobalDesc, $result->id, 'seo_global_id', 'meta-title') !!}  
                          </div>
                      </div>
                      <div class="form-group">
                          <label>@lang('seo.meta_description')</label>
                          <div class="formgroup-lang">
                            {!! CustomHelpers::textWithEditLanuage('textarea','meta_description', $tblSeoGlobalDesc, $result->id, 'seo_global_id', 'meta-description') !!} 
                          </div>
                      </div>
                      <div class="form-group">
                          <label>@lang('seo.meta_keywords')</label>   
                          <div class="formgroup-lang">                         
                            {!! CustomHelpers::textWithEditLanuage('text','meta_keyword', $tblSeoGlobalDesc, $result->id, 'seo_global_id', 'meta-keyword') !!} 
                          </div>
                            
                      </div>                   
                        
                      <div class="form-group">
                            <label>@lang('seo.status')</label>                         
                            {!! Form::select('status', ['1'=>Lang::get('seo.active'), '2'=>Lang::get('seo.inactive')],  $result->status,['class' => 'form-control']) !!}                                      
                           
                      </div>                                 
                      <div class="form-group">                            
                          <button type="submit" class="btn btn-save btn-primary">@lang('seo.submit')</button>
                            
                      </div>
                  </div>
                </div>
 
                       
            </form>

            <div class="row">
              <div class="col-sm-5">
                  <strong>  @lang('seo.varible_use_in_this_form_with_content')</strong> 
                 <h4><strong>@lang('seo.use_any_where')</strong></h4>
                 [SITE_NAME] ===> @lang('seo.use_for_website_name') <br/>
                 [SITE_URL]  ====> @lang('seo.use_for_website_url') <br/>

                <h4><strong>@lang('seo.only_for_any_product')</strong></h4>
                   [PRODUCT_NAME]  ====> @lang('seo.use_for_product_name') <br/>
                   [PRODUCT_SKU]  ====> @lang('seo.use_for_product_sku') <br/>
                   [PRODUCT_PRICE]  ====> @lang('seo.use_for_product_price') <br/>

                 <h4><strong>@lang('seo.for_any_category')</strong></h4>
                   [CATEGORY_NAME]  ====> @lang('seo.use_for_category_name') <br/>

               
                  <!--h4><strong>@lang('seo.for_shop')</strong></h4>

                  [SHOP_NAME]  ====> @lang('seo.use_for_shop_name') <br/>

                  [SHOP_COUNTRY]  ====> @lang('seo.use_for_shop_country') <br/>

                  [SHOP_TOTAL_SALE]  ====> @lang('seo.use_for_shop_total_sale')<br/>
               
                  [SHOP_OPENED_SINCE]  ====> @lang('seo.use_for_shop_opened_since')<br/>

                  [SELLER_NAME]  ====> @lang('seo.use_for_shop_seller_name')<br/-->

                  <h4><strong>@lang('seo.for_cms')</strong></h4>
                  
                  [CMS_TITLE]  ====> @lang('seo.use_for_cms_title') <br/>

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
    
@stop
