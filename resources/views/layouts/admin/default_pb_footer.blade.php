<!DOCTYPE html>
<html>
   <head>
      <meta charset="UTF-8">
      <base href="{{ asset('/') }}" />
      <title>@yield('title')</title>
      <!-- <meta name="viewport" content="width=device-width, initial-scale=1.0"> -->
      <meta http-equiv="Content-Type" content="1200; charset=utf-8" />
      <!-- HTML5 Shim and Respond.js IE8 support of HTML5 elements and media queries -->
      <!-- WARNING: Respond.js doesn't work if you view the page via file:// -->
      <!--[if lt IE 9]>
      <script src="https://oss.maxcdn.com/libs/html5shiv/3.7.0/html5shiv.js"></script>
      <script src="https://oss.maxcdn.com/libs/respond.js/1.3.0/respond.min.js"></script>
      <![endif]-->
      <link rel="shortcut icon" href="{{ getSiteLogo('SITE_FEVICON_ICON') }}" type="image/x-icon">
      <!-- global css -->
      <link rel="stylesheet" href="{{ Config('constants.admin_css_url') }}bootstrap.min.css"/>
      <link rel="stylesheet" href="{{Config('constants.admin_css_url') }}sweetalert2.min.css" />
      <link rel="stylesheet" href="{{ Config('constants.admin_css_url') }}allfontawesome.css" />
   
      <script src="{{ Config('constants.admin_js_url') }}jquery.min.js"></script> 
      <script src="{{ Config('constants.admin_js_url') }}jquery-ui.min.js"></script>
      <script src="{{ Config('constants.admin_js_url') }}bootstrap.bundle.js"></script>
      <script src="{{ Config('constants.admin_js_url') }}bootstrap.min.js"></script> 
      <script src="{{ Config('constants.admin_js_url') }}SweetAlert.min.js"></script> 
       
      @yield('header_styles')
      <script>
         window.Laravel = <?php
            echo json_encode([
                'csrfToken' => csrf_token(),
            ]);
            ?>;        
         var siteUrl ="{{url('/')}}/{{ session('lang_code')}}";
        
      </script>   
   </head>
   <body data-ng-app="sabinaAdminApp">     
      <!--  Header Start -->
      <header id="header">
         <div class="header-top">
            <div class="header-top-builder">
               <div class="header-col no-border p-0">
                  <a href="javascript:void();" class="logo"><img src="images/logo-econg.png"></a>
               </div>
               <div class="header-col page-build-link dropdown">
                  <a href="javascript:void(0)" class="dropdown-toggle" data-toggle="dropdown"><span class="text-nwrap">@lang('cms.footer_management')</span></a>
                  <div class="dropdown-menu">
                     <a href="javascript:void(0)" class="dropdown-item">@lang('cms.footer_management')</a>
                     <a href="{{ action('Admin\PageBuilder\PageBuilderController@index') }}" class="dropdown-item">@lang('cms.page_builder')</a>
                     <a href="{{ action('Admin\WebsiteMaintenance\WebsiteMaintenanceController@index') }}" class="dropdown-item">@lang('cms.theme')</a>
                     <!-- <a href="javascript:void(0)" class="dropdown-item">Magic block</a>
                     <a href="javascript:void(0)" class="dropdown-item btns btn- btn"><i class="fas fa-angle-left"></i> Exit to Shop</a> -->
                  </div>
               </div>              
               <div class="header-col no-border dropdown lang-dropdown">
                  <a href="javascript:void(0)" class="dropdown-toggle pl-0 lang-dropdown-text" data-toggle="dropdown">TH</a>
                  <div class="dropdown-menu">
                     @if(count($language))
                        @foreach($language as $key => $lval)
                           <a href="javascript:void(0)" data-langid="{{ $lval->id }}" class="dropdown-item">
                              {{ $lval->languageCode }}</a>
                        @endforeach
                     @endif
                     
                  </div>
               </div>
               <div class="header-col no-border">
                  <label class="view-language-avail">Thai</lable>
               </div>
               <div class="header-right">
                  <ul class="header-rightlist">
                     <li><a href="{{ action('Admin\AdminHomeController@index') }}" class="btns btn-back"><i class="fas fa-chevron-left"></i> <span class="text-nwrap">@lang('cms.back_to_admin')</span></a></li>
                     <li class="preview dropdown">
                        <a href="javascript:void(0)" class="dropdown-toggle" data-toggle="dropdown"><span class="text-nwrap">@lang('cms.preview')</span></a>
                        <div class="dropdown-menu">
                           <span class="preview-label">@lang('cms.preview_your_site_as')</span>
                           <!-- <a href="javascript:void(0)" class="dropdown-item"><i class="fas fa-desktop"></i>Desktop</a> -->
                           <a href="javascript:void(0)" class="dropdown-item tablet-mobile-preview" data-preview-class="tablet-portrait"><i class="fas fa-tablet-alt"></i>Tablet</a>
                           <a href="javascript:void(0)" class="dropdown-item tablet-mobile-preview" data-preview-class="mobile-portrait"><i class="fas fa-mobile-alt"></i>Mobile</a>
                        </div>
                     </li>
                     <li><a href="javascript:void(0);" class="btns btn-green" id="save_content"><span class="text-nwrap">@lang('common.save')</span></a></li>
                     <li class="publish">
                        <div class="switchToggle">
                           <input type="checkbox" id="switch">
                           <label for="switch">Toggle</label>
                        </div>
                     </li>
                  </ul>
               </div>
            </div>
         </div>
         <span class="header-top-slide fas fa-chevron-up"></span>
         <div class="is-tool custom-tool" style="position:fixed;border:none;top:70px;bottom:auto;left:auto;right:30px;text-align:right;display:none">
            <button id="btnViewSnippets" class="classic">+ @lang('common.add')</button>
            <button id="btnViewHtml" class="classic">@lang('cms.html')</button>
         </div>
      </header>
      @yield('content')
      <!--  Footer  Start -->
      <!-- <footer id="footer">
         <div class="container">
            Footer gores here
         </div>
      </footer>  -->
      <div class="loader-wrapper" id="showHideLoader">
         <span class="loader">
              <img src="{{ getSiteLoader('SITE_LOADER_IMAGE')}}" alt="Loader" width="30" height="30"> 
              <div>Please wait...</div>
         </span>
      </div>
      <script src="{{ Config('constants.assets_url') }}pagebuilder/js/footerbuilder.app.js"></script>
      @yield('footer-scripts')
      <!-- end page level js -->
   </body>
</html>