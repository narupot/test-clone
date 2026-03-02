@extends('layouts.admin.default')

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
    </div>

    <div class="content-wrap clearfix">
        <div class="breadcrumb">
            <ul class="bredcrumb-menu">
                {!!getBreadcrumbAdmin('blogcategory','blogcategory','list')!!}
            </ul>
        </div>
        @include('admin.includes.category_left_menu')
        <div class="content-right" style="min-height: 508px;">
            <div class="category-right">
                <form method="POST" action="{{ action('Admin\BlogCategory\BlogCategoryController@store') }}" class="form-horizontal" enctype="multipart/form-data">

                    {{ csrf_field() }}

                    <input type="hidden" name="type" value="{{ $type }}">
                    <h2 class="sub-title">@lang('admin_blog.blog_category_gen')</h2>
                    <div class="row">
                        <div id="cetegory-general-info" class="col-sm-7">
                            <div class="category-gen-form">
                                @if($type == 'sub_category')
                                    <div class="form-group">
                                        <label>@lang('admin_blog.parent_blog_category')<span class="star-top">*</span></label>
                                        <select name="parent_id">
                                            <option value="">--select--</option>
                                            @foreach($categories as $key=>$mainCategory) 
                                                <option value="{{ $mainCategory->id }}">{{ $mainCategory->getCatDesc->name }}</option>
                                                @foreach($mainCategory->category as $subcategory)
                                                    <option value="{{ $subcategory->id }}">&emsp;&emsp;{{ $subcategory->getCatDesc->name }}</option>
                                                    @foreach($subcategory->category as $subsubcategory)
                                                        <option value="{{ $subsubcategory->id }}">&emsp;&emsp;&emsp;&emsp;{{ $subsubcategory->getCatDesc->name }}</option>
                                                        @foreach($subsubcategory->category as $finalcategory)
                                                            <option value="{{ $finalcategory->id }}">&emsp;&emsp;&emsp;&emsp;&emsp;&emsp;{{ $finalcategory->getCatDesc->name }}</option>
                                                        @endforeach 
                                                    @endforeach 
                                                @endforeach 
                                            @endforeach 
                                       </select>                                          
                                        @if ($errors->has('parent_id'))
                                            <p class="error error-msg">{{ $errors->first('parent_id') }}</p>
                                        @endif
                                    </div>
                                @else
                                    <input type="hidden" name="parent_id" value="0">
                                @endif
                                <div class="form-group">                                            
                                        {!! CustomHelpers::fieldstabWithLanuage([['field'=>'text', 'name'=>'name', 'label'=>'Name', 'errorkey'=>'name'], ['field'=>'textarea', 'name'=>'comments', 'label'=>'Comments', 'errorkey'=>'comments'],['field'=>'textarea', 'name'=>'description', 'label'=>'Description', 'errorkey'=>'description','cssClass'=>'froala-editor-apply']], '1', $errors) !!}
                                </div>
                                <div class="form-group">
                                    <label>@lang('admin_blog.active_blog_category')<span class="star-top">*</span></label>
                                    <select name="status">
                                        <option value="1">Yes</option>
                                        <option value="0">No</option>
                                    </select>
                                </div>  
                                <div class="form-group">                                         
                                        {!! CustomHelpers::fieldstabWithLanuage([['field'=>'text', 'name'=>'meta_title', 'label'=>Lang::get('admin_blog.meta_title'), 'cssClass'=>''], ['field'=>'text', 'name'=>'meta_keyword', 'label'=>Lang::get('admin_blog.meta_keyword'), 'cssClass'=>''], ['field'=>'textarea', 'name'=>'meta_description', 'label'=>Lang::get('admin_blog.meta_description'), 'cssClass'=>'']], '2',$errors)!!}                               
                            </div>
                            </div>
                            <button class="btn btn-primary" value="Save Category" type="submit">@lang('admin_blog.save_blog_category')</button>
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
   var lang_id = "{{Session::get('admin_default_lang')}}";
</script>
<script src="{{asset('js/normal_froala_editor_setting.js')}}"></script>  

@stop