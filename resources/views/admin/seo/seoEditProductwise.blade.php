@extends('layouts/admin/default')

@section('title')
   
  @if(isset($type) && $type == 'blog')
    @lang('admin.edit_blog_seo')
  @else
    @lang('admin.edit_product_seo')
  @endif 



   
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
        @if(Session::has('succMsg'))
        <div class="alert alert-success alert-dismissable margin5">
            <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
            <strong>@lang('admin.success'):</strong> {{ Session::get('succMsg') }}
        </div>
        @endif  
        <div class="content-wrap">
        <div class="breadcrumb">
                <ul class="bredcrumb-menu">
                    {!!getBreadcrumbAdmin('config','seo')!!}
                </ul>
            </div>   
        <div id="tab3" class="tab-pane">

            <div class="containers">
                <div class="heading-wrapper row clearfix">
                    <h2 class="title col-sm-7"> 
                      @if(isset($type) && $type == 'blog')
                        @lang('admin.edit_blog_seo')
                      @else
                       @lang('admin.edit_product_seo')
                      @endif 

                    </h2>
                 </div>
                <div class="border">
                    <form id="addCountryForm" action="{{ action('SeoController@updateproductSeo', $result->id) }}" method="post">
                        {{ csrf_field() }}
                        
                        <input name="product_id" value="{{$result->product_id}}" type="hidden">

                        <input name="user_id" value="{{$result->user_id}}" type="hidden">

                        @if(isset($type) && $type == 'blog')

                          <input type="hidden" name="type" value="blog">

                        @endif  

                        <div class="row">
                          <div class="col-sm-6">
                      
                            <div class="form-group">
                                <label>@lang('admin.meta_title')</label>
                                <div class="formgroup-lang">
                                   
                                     {!! CustomHelpers::textWithEditLanuage('text','meta_title', $tblSeoProductWiseDesc, $result->id, 'seo_product_id', 'meta-title') !!}  
                                    
                                </div>
                            </div>

                             <div class="form-group">
                                <label>@lang('admin.meta_description')</label>
                                <div class="formgroup-lang">
                                    
                                    {!! CustomHelpers::textWithEditLanuage('textarea','meta_description', $tblSeoProductWiseDesc, $result->id, 'seo_product_id', 'meta-description') !!}  
                                    
                                </div>
                            </div>
                            <div class="form-group">
                                <label>@lang('admin.meta_keywords')</label>
                                <div class="formgroup-lang">
                                    {!! CustomHelpers::textWithEditLanuage('text','meta_keyword', $tblSeoProductWiseDesc, $result->id, 'seo_product_id', 'meta-keyword') !!} 
                                    
                                </div>
                            </div>

                           <div class="form-group">
                              <label>@lang('admin.meta_robots')</label>
                              <label class="check-wrap">
                                  <input type="checkbox" 
                                    name="meta_robots[]" 
                                    value="nofollow" class="form-control" @if(!empty($meta_robots) && in_array( 'nofollow', $meta_robots)) checked = "checked" @endif >  
                                   <span class="chk-label">@lang('admin.nofollow')</span>
                              </label> 
                              <label class="check-wrap"> 
                                  <input type="checkbox" 
                                    name="meta_robots[]" 
                                    value="noindex" class="form-control" 
                                     
                                     @if(!empty($meta_robots) && in_array('noindex', $meta_robots)) checked = "checked" @endif
                                      
                                    >  
                                   <span class="chk-label">@lang('admin.noindex')</span>
                              </label>
                              <label class="check-wrap"> 
                                  <input type="checkbox" 
                                    name="meta_robots[]" 
                                    value="noodp" class="form-control" 

                                      @if(!empty($meta_robots) && in_array('noodp', $meta_robots)) checked = "checked" @endif

                                    >  
                                   <span class="chk-label">@lang('admin.noodp')</span>
                              </label>
                              <label class="check-wrap"> 
                                  <input type="checkbox" 
                                    name="meta_robots[]" 
                                    value="noydir" class="form-control" 

                                     
                                      @if(!empty($meta_robots) && in_array('noydir', $meta_robots)) checked = "checked" @endif

                                    >  
                                   <span class="chk-label">@lang('admin.noydir')</span>
                              </label>
                              <label class="check-wrap"> 
                                  <input type="checkbox" 
                                    name="meta_robots[]" 
                                    value="follow" class="form-control" 

                                    @if(!empty($meta_robots) && in_array('follow', $meta_robots)) checked = "checked" @endif


                                    >  
                                   <span class="chk-label">@lang('admin.follow')</span>
                              </label>
                              <label class="check-wrap"> 
                                  <input type="checkbox" 
                                    name="meta_robots[]" 
                                    value="index" class="form-control" 

                                     @if(!empty($meta_robots) && in_array('index', $meta_robots)) checked = "checked" @endif


                                    ><span class="chk-label">@lang('admin.index')</span>
                              </label>
                          </div>  
                            
                            <div class="form-group">
                                <label class="col-md-3 control-label">@lang('admin.status')</label>
                                <div class="col-md-5">
                                     {!! Form::select('status', ['1'=>'Active', '2'=>'Inactive'],  $result->status,['class' => 'form-control']) !!}                                      
                                </div>
                            </div>                                 
                            <div class="form-group">
                                <div class="col-md-9 btn-group col-md-offset-3">
                                    <button type="submit" class="btn btn-save btn-primary">@lang('admin.save')</button>
                                    <a href="{{ action('CountryController@index') }}" class="btn btn-back">@lang('admin.back')</a>
                                </div>
                            </div>
                            </div>
                        </div>
                    </form>
                </div>
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
