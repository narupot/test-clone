@extends('layouts/admin/default')

@section('title')
    @lang('blog.blog_configuration')
@stop

@section('header_styles')
@stop

@section('content')

    <div class="content">
        @if(Session::has('succMsg'))
            <script type="text/javascript">               
                _toastrMessage('success', "{{ Session::get('succMsg') }}");    
            </script>             
        @endif     
        <div class="header-title">
            <h1 class="title">@lang('blog.blog_configuration')</h1>
        </div>
        <div class="content-wrap clearfix">
            <div class="breadcrumb">
                <ul class="bredcrumb-menu">
                    {!!getBreadcrumbAdmin('blog','blog')!!}
                </ul>
            </div>
            <div class="row">
                <form action="{{ action('Admin\Blog\BlogConfigController@store') }}" method="post"  enctype="multipart/form-data" class="col-sm-5">
                    {{ csrf_field() }}

                    <div class="form-group">
                        <label>@lang('blog.comment_enable')</label>
                        <select name="COMMENT_ENABLE">                            
                            <option value="1" @if($config_arr['COMMENT_ENABLE'] == '1') selected="selected" @endif>@lang('common.yes')</option>
                            <option value="0" @if($config_arr['COMMENT_ENABLE'] == '0') selected="selected" @endif>@lang('common.no')</option>
                        </select>
                    </div> 

                    <!--<div class="form-group">
                        <label>@lang('blog.require_approve')</label>
                        <select name="REQUIRE_APPROVE">
                            <option value="1" @if($config_arr['REQUIRE_APPROVE'] == '1') selected="selected" @endif>@lang('common.yes')</option>
                            <option value="0" @if($config_arr['REQUIRE_APPROVE'] == '0') selected="selected" @endif>@lang('common.no')</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>@lang('blog.only_login_user')</label>
                        <select name="ONLY_LOGIN_USER">
                            <option value="1" @if($config_arr['ONLY_LOGIN_USER'] == '1') selected="selected" @endif>@lang('common.yes')</option>
                            <option value="0" @if($config_arr['ONLY_LOGIN_USER'] == '0') selected="selected" @endif>@lang('common.no')</option>
                        </select>
                    </div> 
                    <div class="form-group">
                        <label>@lang('blog.show_comment_per_page')</label>
                        <input type="text" name="SHOW_COMMENT_PER_PAGE" value="{{ $config_arr['SHOW_COMMENT_PER_PAGE'] }}" class="form-control">
                    </div>
                    <div class="form-group">
                        <label>@lang('blog.comment_email')</label>
                        <select name="COMMENT_EMAIL">
                            <option value="1" @if($config_arr['COMMENT_EMAIL'] == '1') selected="selected" @endif>@lang('common.yes')</option>
                            <option value="0" @if($config_arr['COMMENT_EMAIL'] == '0') selected="selected" @endif>@lang('common.no')</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>@lang('blog.author_approved_comment')</label>
                        <select name="AUTHOR_MUST_APPROVED_PREVIOUS_COMMENT">
                            <option value="1" @if($config_arr['AUTHOR_MUST_APPROVED_PREVIOUS_COMMENT'] == '1') selected="selected" @endif>@lang('common.yes')</option>
                            <option value="0" @if($config_arr['AUTHOR_MUST_APPROVED_PREVIOUS_COMMENT'] == '0') selected="selected" @endif>@lang('common.no')</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>@lang('blog.enable_comment_thread_level')</label>
                        <select name="ENABLE_COMMENT_THREAD_LEVEl">
                            <option value="1" @if($config_arr['ENABLE_COMMENT_THREAD_LEVEl'] == '1') selected="selected" @endif>@lang('common.yes')</option>
                            <option value="0" @if($config_arr['ENABLE_COMMENT_THREAD_LEVEl'] == '0') selected="selected" @endif>@lang('common.no')</option>
                        </select>
                    </div>                                         
                    <div class="form-group">
                        <label>@lang('blog.comment_thread_level')</label>
                         <input type="text" name="COMMENT_THREAD_LEVEl" value="{{ $config_arr['COMMENT_THREAD_LEVEl'] }}" class="form-control">
                    </div> -->
                    <div class="form-group">
                        <label>@lang('blog.enable_captcha')</label>
                        <select name="ENABLE_CAPTCHA">
                            <option value="1" @if($config_arr['ENABLE_CAPTCHA'] == '1') selected="selected" @endif>@lang('common.yes')</option>
                            <option value="0" @if($config_arr['ENABLE_CAPTCHA'] == '0') selected="selected" @endif>@lang('common.no')</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>@lang('blog.captch_public_code')</label>
                        <input type="text" name="CAPTCHA_PUBLIC_CODE" value="{{ $config_arr['CAPTCHA_PUBLIC_CODE'] }}" class="form-control">
                    </div>
                    <div class="form-group">
                        <label>@lang('blog.captch_private_code')</label>
                        <input type="text" name="CAPTCHA_PRIVATE_CODE" value="{{ $config_arr['CAPTCHA_PRIVATE_CODE'] }}" class="form-control">
                    </div> 
                    <div class="form-group">
                        <label>@lang('blog.facebook_appid')</label>
                        <input type="text" name="FACEBOOK_APPID" value="{{ $config_arr['FACEBOOK_APPID'] }}" class="form-control">
                    </div>
                    <div class="form-group">
                        <label>@lang('blog.facebook_secretkey')</label>
                        <input type="text" name="FACEBOOK_SECRETKEY" value="{{ $config_arr['FACEBOOK_SECRETKEY'] }}" class="form-control">
                    </div>                       
                    <div class="form-group">
                        <label>@lang('blog.recent_view_blog')</label>
                         <input type="text" name="RECENT_VIEW_BLOG" value="{{ $config_arr['RECENT_VIEW_BLOG'] }}" class="form-control">
                    </div>    
                    <div class="form-group">
                        <label>@lang('blog.related_blog')</label>
                         <input type="text" name="RELATED_BLOG" value="{{ $config_arr['RELATED_BLOG'] }}" class="form-control">
                    </div>
                    <div class="form-group">
                        <label>@lang('blog.feature_blog')</label>
                         <input type="text" name="FEATURE_BLOG" value="{{ $config_arr['FEATURE_BLOG'] }}" class="form-control">
                    </div>
                    <div class="form-group">
                        <label>@lang('blog.search_list_config')</label>
                        <select name="BLOG_SEARCH_CONFIG[]" multiple="multiple" class="multiple-selectw" style="height:100px;">                  
                            <option value="title" @if(in_array('title',$config_arr_search['BLOG_SEARCH_CONFIG'])) selected="selected" @endif>@lang('common.title')</option>
                            <option value="short_description" @if(in_array('short_description',$config_arr_search['BLOG_SEARCH_CONFIG'])) selected="selected" @endif>@lang('blog.short_description')</option>
                            <option value="description" @if(in_array('description',$config_arr_search['BLOG_SEARCH_CONFIG'])) selected="selected" @endif>@lang('common.description')</option>
                            <option value="tags" @if(in_array('tags',$config_arr_search['BLOG_SEARCH_CONFIG'])) selected="selected" @endif>@lang('blog.tags')</option>                        
                        </select>
                    </div> 
                    <div class="form-group">
                        <label>@lang('blog.pagination')</label>
                        <select name="BLOG_PAGINATION">
                            <option value="ajax_load" @if($config_arr['BLOG_PAGINATION'] == 'ajax_load') selected="selected" @endif>@lang('blog.ajax_load')</option>
                            <option value="page_normal" @if($config_arr['BLOG_PAGINATION'] == 'page_normal') selected="selected" @endif>@lang('blog.page_normal')</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>@lang('blog.show_pagination_per_page')</label>
                        <input type="text" name="SHOW_PAGINATION_PER_PAGE" value="{{ $config_arr['SHOW_PAGINATION_PER_PAGE'] }}" class="form-control">
                    </div>   
                    <div class="formate-select">

                        <div class="row form-group date-formate">
                            <div class="col-sm-4">
                                <span>@lang('blog.dateformat')</span>
                            </div>
                            <div class="col-sm-8">
                                <div class="form-group row">
                                    <label class="radio-wrap col-sm-8">
                                        <input type="radio" name="DATE_FORMAT" value="F j, Y" @if($config_arr['DATE_FORMAT']=='F j, Y') checked="checked" @endif>
                                        <span class="radio-label">November 24, 2018</span>
                                    </label>
                                    <span class="col-sm-4"><span class="formate-preview">F j, Y</span></span>
                                </div>
                                <div class="form-group row">
                                    <label class="radio-wrap col-sm-8">
                                        <input type="radio" name="DATE_FORMAT" value="Y-m-d" @if($config_arr['DATE_FORMAT']=='Y-m-d') checked="checked" @endif>
                                        <span class="radio-label">2018-11-24</span>
                                    </label>
                                    <span class="col-sm-4"><span class="formate-preview">Y-m-d</span></span>
                                </div>
                                <div class="form-group row">
                                    <label class="radio-wrap col-sm-8">
                                        <input type="radio" name="DATE_FORMAT" value="m/d/y" @if($config_arr['DATE_FORMAT']=='m/d/y') checked="checked" @endif>
                                        <span class="radio-label">11/24/2018</span>
                                    </label>
                                    <span class="col-sm-4"><span class="formate-preview">m/d/y</span></span>
                                </div>
                                <div class="form-group row">
                                    <label class="radio-wrap col-sm-8">
                                        <input type="radio" name="DATE_FORMAT" value="d/m/y" @if($config_arr['DATE_FORMAT']=='d/m/y') checked="checked" @endif>
                                        <span class="radio-label">24/11/2018</span>
                                    </label>
                                    <span class="col-sm-4"><span class="formate-preview">d/m/y</span></span>
                                </div>
                                <!--<div class="form-group row">
                                    <label class="radio-wrap col-sm-8">
                                        <input type="radio" name="DATE_FORMAT" value="{{ $config_arr['DATE_FORMAT'] }}">
                                        <span class="radio-label">Custom</span>
                                    </label>
                                    <span class="col-sm-4"><span class="formate-preview custom"><input type="text" value="F j, Y"></span></span>
                                </div>
                                <div class="form-group">
                                    <span class="preview">Preview : <span class="current-date">24 November 2018 </span></span>
                                </div>-->

                            </div>
                        </div>
                        <div class="row form-group time-formate">
                            <div class="col-sm-4">
                                <span>@lang('blog.timeformat')</span>
                            </div>
                            <div class="col-sm-8">
                                <div class="form-group row">
                                    <label class="radio-wrap col-sm-8">
                                        <input type="radio" name="TIME_FORMAT" value="g:i a" @if($config_arr['TIME_FORMAT']=='g:i a') checked="checked" @endif>
                                        <span class="radio-label">2:25 pm</span>
                                    </label>
                                    <span class="col-sm-4"><span class="formate-preview">g:i a</span></span>
                                </div>
                                <div class="form-group row">
                                    <label class="radio-wrap col-sm-8">
                                        <input type="radio" name="TIME_FORMAT" value="g:i A" @if($config_arr['TIME_FORMAT']=='g:i A') checked="checked" @endif>
                                        <span class="radio-label">2:25 PM</span>
                                    </label>
                                    <span class="col-sm-4"><span class="formate-preview">g:i A</span></span>
                                </div>
                                <div class="form-group row">
                                    <label class="radio-wrap col-sm-8">
                                        <input type="radio" name="TIME_FORMAT" value="H:i" @if($config_arr['TIME_FORMAT']=='H:i') checked="checked" @endif>
                                        <span class="radio-label">14:25</span>
                                    </label>
                                    <span class="col-sm-4"><span class="formate-preview">H:i</span></span>
                                </div>
                                <!--<div class="form-group row">
                                    <label class="radio-wrap col-sm-8">
                                        <input type="radio" name="TIME_FORMAT" value="{{ $config_arr['TIME_FORMAT'] }}">
                                        <span class="radio-label">Custom</span>
                                    </label>
                                    <span class="col-sm-4"><span class="formate-preview custom"><input type="text" value="g:i a"></span></span>
                                </div>
                                <div class="form-group">
                                    <span class="preview">Preview : <span class="current-time">24 November 2018 </span></span>
                                </div>-->
                            </div>
                        </div>

                        <h2 class="title">@lang('blog.image_size_configuration') </h2>

                        <div class="form-group">
                            <label>@lang('blog.feature_image_size')</label>                            
                            <div class="row">
                                <div class="col-sm-6">
                                    <input type="text" name="FEATURE_IMAGE_WIDTH" value="{{ $config_arr['FEATURE_IMAGE_WIDTH'] }}" class="form-control">
                                </div>
                                <div class="col-sm-6">
                                    <input type="text" name="FEATURE_IMAGE_HEIGHT" value="{{ $config_arr['FEATURE_IMAGE_HEIGHT'] }}" class="form-control">
                                </div>
                            </div>
                        </div>

                        <div class="form-group">
                            <label>@lang('blog.slider_image_size')</label>                            
                            <div class="row">
                                <div class="col-sm-6">
                                    <input type="text" name="SLIDER_IMAGE_WIDTH" value="{{ $config_arr['SLIDER_IMAGE_WIDTH'] }}" class="form-control">
                                </div>
                                <div class="col-sm-6">
                                    <input type="text" name="SLIDER_IMAGE_HEIGHT" value="{{ $config_arr['SLIDER_IMAGE_HEIGHT'] }}" class="form-control">
                                </div>
                            </div>
                        </div>

                        <div class="form-group">
                            <label>@lang('blog.seo_image_size')</label>             
                            <div class="row">
                                <div class="col-sm-6">
                                    <input type="text" name="SEO_IMAGE_WIDTH" value="{{ $config_arr['SEO_IMAGE_WIDTH'] }}" class="form-control">
                                </div>
                                <div class="col-sm-6">
                                    <input type="text" name="SEO_IMAGE_HEIGHT" value="{{ $config_arr['SEO_IMAGE_HEIGHT'] }}" class="form-control">
                                </div>
                            </div>
                        </div>

                        <div class="form-group">
                            <label>@lang('blog.socialshare_image_size')</label>
                            <div class="row">
                                <div class="col-sm-6">
                                    <input type="text" name="SOCIALSHARE_IMAGE_WIDTH" value="{{ $config_arr['SOCIALSHARE_IMAGE_WIDTH'] }}" class="form-control">
                                </div>
                                <div class="col-sm-6">
                                    <input type="text" name="SOCIALSHARE_IMAGE_HEIGHT" value="{{ $config_arr['SOCIALSHARE_IMAGE_HEIGHT'] }}" class="form-control">
                                </div>
                            </div>                                                      
                        </div>

                    </div>    
                    <div class="form-group form-actions">
                        <div class="">
                            <button type="submit" class="btn btn-save">@lang('common.update')</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
      
@stop
@section('footer_scripts')  
<script type="text/javascript">
    $(document).ready(function(){
        $('.date-formate input[type="radio"]').change(function(){
            var timval = jQuery(this).val();
            jQuery('.date-formate .current-date').html(timval);
        });
        $('.time-formate input[type="radio"]').change(function(){
            var timval = jQuery(this).val();
            jQuery('.time-formate .current-time').html(timval);
        });
    });
</script>
@stop
