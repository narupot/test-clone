@extends('layouts/admin/default')

@section('title')
 
 @lang('admin.global_seo_management')
  
   
@stop

@section('header_styles')
<link rel="stylesheet" type="text/css" href="{{Config('constants.css_url') }}ui-grid-unstable.css">
    
@stop

@section('content')
  
   <div class="content">
        <div class="header-title">
            <h1 class="title">@lang('seo.global_seo_management') </h1>
             <div class="float-right">
              <a class="float-right btn btn-primary" href="{{ action('Admin\SEO\SeoController@createpageseo') }}"> @lang('seo.add_global_seo')
              </a>
             </div>            
      </div>
       
    <div class="content-wrap">
      <div class="breadcrumb">
                <ul class="bredcrumb-menu">
                    {!!getBreadcrumbAdmin('config','seopage','list')!!}
                </ul>
            </div>
            <table class="table table-bordered " id="table">
                <thead>
                    <tr class="filters">
                        <th>@lang('seo.sno')</th>
                        <th>@lang('seo.name')</th>
                        <th>@lang('seo.use_for')</th>
                        <!--th>Code</th-->
                        <th>@lang('seo.status')</th>
                        <th>@lang('seo.created_at')</th>
                        <th>@lang('seo.action')</th>
                    </tr>
                </thead>
                <tbody>

                @foreach ($results as $key => $result)

                    <tr>
                        <td>{{ ++$key }}</td>
                       
                        <td>{{ $result->name }}</td>
                        <!--td>{{ $result->slug }}</td-->
                         @if($result->type == '2')
                          <td>@lang('seo.others')</td>
                       @else
                          <td>@lang('seo.products')</td>
                       @endif
                     @if($result->status == '2')
                          <td><span class=" inactive-btn">@lang('seo.inactive')</span></td>
                       @else
                          <td><span class="active-btn">@lang('seo.active')</span></td>
                       @endif

                        <td>{{ getDateFormat($result->created_at, '1') }}</td>
                        
                        
                        <td>
                        
                            <a class="btn btn-dark" href="{{ action('Admin\SEO\SeoController@editpageseo', $result->id) }}">
                               @lang('seo.edit')
                            </a>
                            <form method="post" action="{{ action('Admin\SEO\SeoController@deletepageseo', $result->id) }}" onsubmit="return confirm('Are you sure to delete this record ?');" class="inblock"> 
                                {{ csrf_field() }}
                                {{ method_field('GET') }}                             
                                <a class="btn btn-delete btn-danger" onclick="$(this).closest('form').submit();" data-toggle="modal">
                                   @lang('common.delete')
                                </a>
                            </form>
                                                             
                        </td>
                    </tr>
                    
                 @endforeach 
                 
                </tbody>
            </table>
       </div>

 </div>


        
@stop


@section('footer_scripts') 
@stop


