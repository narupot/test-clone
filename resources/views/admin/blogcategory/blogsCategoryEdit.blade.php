@extends('layouts.admin.default')
<?php $langCode=session('lang_code'); ?>
@section('title')
    @lang('admin_blog.blog_category')
@stop

@section('header_styles')
@stop

@section('content')
<div class="content">

    <div class="row">
        <div class="col-sm-12">
        @if(Session::has('succMsg'))    
            <script type="text/javascript">               
                _toastrMessage('success', "{{ Session::get('succMsg') }}");    
            </script>                            
        @elseif(Session::has('errorMsg'))
            <script type="text/javascript"> 
                _toastrMessage('error', "{{ Session::get('errorMsg') }}");
            </script>  
        @endif            
        </div>
    </div>

    <div class="header-title">
        <h1 class="title">@lang('admin_blog.blog_category')</h1>    
        <div class="float-right">
        <a class="btn btn-primary" href="{{ action('BlogController@categoryBlogList',$category->url)}}" target="_blank">@lang('admin_product.preview')</a>
        <form method="post" action="{{ action('Admin\BlogCategory\BlogCategoryController@destroy', $category->id) }}" onsubmit="return confirm('Are you sure to delete this category ?');" class="inblock"> 
            {{ csrf_field() }}
            {{ method_field('DELETE') }}   

            @if($category->id)                          
            <a class="btn btn-delete btn-danger" onclick="$(this).closest('form').submit();" data-toggle="modal">
               @lang('admin_blog.delete_blog_category')
            </a>
            @endif
        </form>
        </div>
    </div>

    <div class="content-wrap clearfix">
        <div class="breadcrumb">
            <ul class="bredcrumb-menu">
                {!!getBreadcrumbAdmin('blogcategory','blogcategory')!!}
            </ul>
        </div>
        @include('admin.includes.category_left_menu')
        <div class="content-right" style="min-height: 508px;">
            <div class="category-right">
                <form method="POST" action="{{ action('Admin\BlogCategory\BlogCategoryController@update', $category->id) }}" class="form-horizontal" enctype="multipart/form-data">
                    {{ csrf_field() }}
                    {{ method_field('PUT') }}
                    <h2 class="sub-title">@lang('admin_blog.blog_category_gen')</h2>
                    <div class="row">
                    <div id="cetegory-general-info" class="col-sm-7">
                        <div class="category-gen-form form-group"> 
                                <div class="form-group">
                                        {!! CustomHelpers::fieldstabWithLanuageEdit([['field'=>'text', 'name'=>'name', 'label'=>'Name', 'errorkey'=>'name'], 
                                        ['field'=>'textarea', 'name'=>'comments', 'label'=>'Comments', 'errorkey'=>'comments','editor_required'=>'N'],['field'=>'textarea', 'name'=>'description', 'label'=>'Description', 'errorkey'=>'description']], '1','cat_id',$category->id,$tblcategoryDesc, $errors) !!}
                                </div>
                            <div class="form-group">
                                <label>@lang('admin_blog.active_blog_category')<span class="star-top">*</span></label>
                                <select name="status">
                                    <option value="1" @if($category->status == '1') selected="selected" @endif>Yes</option>
                                    <option value="0" @if($category->status == '0') selected="selected" @endif>No</option>
                                </select>
                            </div> 
                            <div class="form-group"> 
                                        {!! CustomHelpers::fieldstabWithLanuageEdit([['field'=>'text', 'name'=>'meta_title', 'label'=>Lang::get('admin_blog.meta_title'), 'cssClass'=>''], ['field'=>'text', 'name'=>'meta_keyword', 'label'=>Lang::get('admin_blog.meta_keyword'), 'cssClass'=>''], ['field'=>'textarea', 'name'=>'meta_description', 'label'=>Lang::get('admin_blog.meta_description'), 'cssClass'=>'','editor_required'=>'N']], '2','cat_id',$category->id,$tblcategoryDesc, $errors)!!}                                
                            </div>
                        </div>
                        <button class="btn btn-secondary" value="Update Category" type="submit">@lang('admin_blog.update_blog_category')</button>
                    </div>
                    </div>
                </form>
            </div>
        </div>        
    </div>
    <div class="push-content"></div>
</div>
@endsection 

@section('footer_scripts')

@include('includes.froalaeditor_dependencies')
<script type="text/javascript">
   var csrftoken = window.Laravel.csrfToken;
</script>
<script src="{{asset('js/normal_froala_editor_setting.js')}}"></script>        

@stop