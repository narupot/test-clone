@extends('layouts/admin/default')

@section('title')
    @lang('admin_seo.seo_configuration')
@stop
  
@section('header_styles')
  <link rel="stylesheet" type="text/css" href="{{Config('constants.admin_css_url') }}cropper.min.css">

  <?php  
      $cropper_setting = [
          [
              'section' => 'og_thumb', 'dimension' => ['width' => 1200, 'height' => 630], 'file_field_selector' => '#seoSocailFile', 'section_id'=>'OG-THUMB',
          ],
      ];
  ?>
  <script type="text/javascript">
     var CROPPER_SETTING = {!! json_encode($cropper_setting) !!};   
  </script>
  
@stop

@section('content')
<div class="content">
        @if(Session::has('succMsg'))    
        <script type="text/javascript">               
            _toastrMessage('success', "{{ Session::get('succMsg') }}");    
        </script>                              
        @endif      
        <div class="header-title">
            <h1 class="title">@lang('admin_seo.seo_configuration')</h1>
        </div>
        <div class="content-wrap clearfix">
            <div class="breadcrumb">
                <ul class="bredcrumb-menu">
                    {!!getBreadcrumbAdmin('config')!!}
                </ul>
            </div>
            <div class="row">
              <div class="col-sm-6">
                <div class="">
                    <ul class="nav nav-tabs lang-nav-tabs" role="tablist">
                        <!--li class="active">
                           <a data-toggle="tab" href="#general">General setting</a>
                        </li-->
                        <li>
                          <a class="active show" data-toggle="tab" href="#analytic">@lang('admin_seo.analytic_setting')</a>
                        </li>
                        <li><a data-toggle="tab" href="#tools">@lang('admin_seo.tools')</a></li>
                        <li><a data-toggle="tab" href="#social">@lang('admin_seo.social')</a></li>
                        <li><a data-toggle="tab" href="#social_share">@lang('admin_seo.social_share')</a></li>
                    </ul>
                </div>
                   
                <form action="{{ action('Admin\Config\SystemConfigController@storeSeoConfig') }}" method="post" enctype="multipart/form-data" class="language-tab clearfix">
                    {{ csrf_field() }}
                    <input type="hidden" name="redirect_path" value="Admin\Config\SystemConfigController@SEOConfig">

                    <div class="tab-content">
                      <div id="analytic" class="tab-pane fade show active">
                            
                        <div class="form-group">
                          <label class="check-wrap">
                                <input type="checkbox" name="ANALYTICS_CODE_CHECK_FOR_HEAD_OPEN" for="form-text-input" value="1" class="form-control"  @if(!empty($config_arr['ANALYTICS_CODE_CHECK_FOR_HEAD_OPEN']))  checked = "checked" @endif>  
                                  <span class="chk-label">@lang('admin_seo.analytics_code_postion_for_after_head_open')</span>
                          </label>
                        </div>

                        <div class="form-group">                         
                          <textarea  name="ANALYTICS_CODE_FOR_HEAD_OPEN"  class="form-control"  @if(empty($config_arr['ANALYTICS_CODE_CHECK_FOR_HEAD_OPEN']))  style="display:none" @endif  >{{ $config_arr['ANALYTICS_CODE_FOR_HEAD_OPEN'] }}</textarea>
                         </div>

                         <div class="form-group">
                                <label class="check-wrap" >
                                    <input type="checkbox" 
                                      name="GOOGLE_ANALYTICS_SETTING"  for="form-text-input"
                                      value="1" class="form-control"  @if(!empty($config_arr['GOOGLE_ANALYTICS_SETTING']))  checked = "checked" @endif>  
                                      <span class="chk-label">@lang('admin_seo.analytics_code_postion_for_before_head_close')</span>
                                    
                                 </label>
                            </div>

                             <div class="form-group">                       
                                  <textarea  name="GOOGLE_ANALYTICS_HEAD"  class="form-control" @if(empty($config_arr['GOOGLE_ANALYTICS_SETTING']))  style="display:none" @endif  >{{ $config_arr['GOOGLE_ANALYTICS_HEAD'] }}</textarea>                                  
                                
                            </div>
                            <div class="form-group">
                                <label class="check-wrap">                                 
                                    <input type="checkbox" for="form-text-input"
                                      name="GOOGLE_ANALYTICS_BODY_SETTING" 
                                      value="1" class="checkbox" @if(!empty($config_arr['GOOGLE_ANALYTICS_BODY_SETTING']))  checked = "checked" @endif >  
                                    <span class="chk-label">@lang('admin_seo.analytics_code_postion_for_after_open_body')</span>
                                    
                                </label>
                            </div>
                            <div class="form-group">
                               <textarea  name="GOOGLE_ANALYTICS_BODY"  class="form-control"   @if(empty($config_arr['GOOGLE_ANALYTICS_BODY_SETTING']))  style="display:none" @endif  >{{ $config_arr['GOOGLE_ANALYTICS_BODY'] }}</textarea>  
                                    
                            </div>
                            <div class="form-row">
                              <label class="check-wrap">
                                  <input type="checkbox" name="ANALYTICS_CODE_CHECK_FOR_BEFORE_BODY_CLOSE" for="form-text-input" value="1" class="form-control"  @if(!empty($config_arr['ANALYTICS_CODE_CHECK_FOR_BEFORE_BODY_CLOSE']))  checked = "checked" @endif>  
                                   <span class="chk-label"> @lang('admin_seo.analytics_code_postion_for_before_body_close')</span>
                              </label>
                            </div>
                          <div class="form-row">                         
                             <textarea  name="ANALYTICS_CODE_FOR_BEFORE_BODY_CLOSE"  class="form-control" @if(empty($config_arr['ANALYTICS_CODE_CHECK_FOR_BEFORE_BODY_CLOSE'])) style="display:none" @endif  >{{ $config_arr['ANALYTICS_CODE_FOR_BEFORE_BODY_CLOSE'] }}</textarea>
                           </div>
                        </div>
                        <div id="tools" class="tab-pane fade">
                        <div class="form-group">
                            <label class="" for="form-text-input">@lang('admin_seo.robots'):</label>                        
                            <textarea  name="ROBOTS_TXT"  class="form-control" placeholder="Robots txt">{{ $config_arr['ROBOTS_TXT'] }}</textarea>                         
                        </div>
                       </div> 


                        <div id="social" class="tab-pane fade">

                        <div class="form-group">
                            <label>@lang('admin_seo.facebook') @lang('admin_seo.url'):</label>
                            <input type="text" name="FACEBOOK_URL"    class="form-control"  value="{{ $config_arr['FACEBOOK_URL'] }}" >
                        </div>                        
                         <div class="form-group">
                            <label>@lang('admin_seo.twitter') @lang('admin_seo.url'):</label>
                            <input type="text" name="TWITTER_URL"   class="form-control"  value="{{ $config_arr['TWITTER_URL'] }}">  
                        </div>
                        <div class="form-group">
                            <label> @lang('admin_seo.google')+ @lang('admin_seo.url'):</label>                            
                            <input type="text" name="GOOGLE_PLUS_URL"   class="form-control"  value="{{ $config_arr['GOOGLE_PLUS_URL'] }}">   
                        </div>
                        <div class="form-group">
                            <label> @lang('admin_seo.linkedin') @lang('admin_seo.url'):</label>
                              <input type="text" name="LINKEDIN_URL"   class="form-control"  value="{{ $config_arr['LINKEDIN_URL'] }}">   
                        </div>
                        <div class="form-group">
                            <label> @lang('admin_seo.pinterest') @lang('admin_seo.url'):</label>
                            <input type="text" name="PINTEREST_URL"   class="form-control"  value="{{ $config_arr['PINTEREST_URL'] }}">  
                        </div>
                        <div class="form-group">
                            <label> @lang('admin_seo.instagram') @lang('admin_seo.url'):</label> 
                            <input type="text" name="INSTAGRAM_URL"   class="form-control" value="{{ $config_arr['INSTAGRAM_URL'] }}">
                        </div>

                        <div class="form-group">
                            <label> @lang('admin_seo.youtube') @lang('admin_seo.url'):</label> 
                            <input type="text" name="YOUTUBE_URL"   class="form-control"  value="{{ $config_arr['YOUTUBE_URL'] }}"> 
                        </div>
                      </div>

                     <div id="social_share" class="tab-pane fade">
                        <div class="form-group">
                            <label>@lang('seo.image'):</label> 
                            <div class="row">
                              <div class="col-md-4">  
                                <input type="file" name="OG_IMAGE"    class="form-control" accept="image/*">  
                              @if(isset($config_arr['OG_IMAGE']) &&!empty($config_arr['OG_IMAGE'])) 
                                 <img src="{{ Config::get('constants.social_share_url').$config_arr['OG_IMAGE']}}"  height="250" width="300" class="mt-2">
                                @endif     
                                  
                              </div> 
                              <label class="col-md-3 control-label" for="form-text-input">@lang('seo.max_size_of_image_1_MB')</label>
                            </div>


                        </div>                    
                        
                      </div>

                     </div>                                             
                   
                    <div class="form-group mt-15  clearfix">                       
                        <button type="submit" class="btn btn-primary">@lang('admin_seo.save')</button>                       
                    </div>

                </form>
              </div>
            </div>

            
                

        
        </div>
    </div>
      
@stop

@section('footer_scripts')  
 <script src="{{ Config('constants.js_url') }}jquery-cropper.min.js" type="text/javascript"></script>
 <script src="{{ Config('constants.js_url') }}common_cropper_upload_setting.js" type="text/javascript"></script>
<script>
jQuery(document).ready(function(){
  
  jQuery('input[name="GOOGLE_ANALYTICS_SETTING"]').click(function(){
    if(jQuery(this).is(':checked')){

       jQuery('textarea[name="GOOGLE_ANALYTICS_HEAD"]').show();

    }else{
      
       jQuery('textarea[name="GOOGLE_ANALYTICS_HEAD"]').hide();

    }
  })

  jQuery('input[name="GOOGLE_ANALYTICS_BODY_SETTING"]').click(function(){
    if(jQuery(this).is(':checked')){
    
      jQuery('textarea[name="GOOGLE_ANALYTICS_BODY"]').show();
    }else{
      jQuery('textarea[name="GOOGLE_ANALYTICS_BODY"]').hide();

    }
  })

  jQuery('input[name="ANALYTICS_CODE_CHECK_FOR_HEAD_OPEN"]').click(function(){
    if(jQuery(this).is(':checked')){
      jQuery('textarea[name="ANALYTICS_CODE_FOR_HEAD_OPEN"]').show();
    }else{
      jQuery('textarea[name="ANALYTICS_CODE_FOR_HEAD_OPEN"]').hide();

    }
  })

  jQuery('input[name="ANALYTICS_CODE_CHECK_FOR_BEFORE_BODY_CLOSE"]').click(function(){
    if(jQuery(this).is(':checked')){
      jQuery('textarea[name="ANALYTICS_CODE_FOR_BEFORE_BODY_CLOSE"]').show();
    }else{
      jQuery('textarea[name="ANALYTICS_CODE_FOR_BEFORE_BODY_CLOSE"]').hide();

    }
  })
 


})
</script>
@stop