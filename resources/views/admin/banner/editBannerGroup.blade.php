@extends('layouts/admin/default')

@section('title')
    @lang('common.banner_group') - {{getSiteName()}} 
@stop

@section('header_styles')

<!--page level css -->

<!-- end of page level css -->
    
@stop

@section('content')
    <div class="content">
      @if(Session::has('succMsg'))    
          <script type="text/javascript">               
              _toastrMessage('success', "{{ Session::get('succMsg') }}");    
          </script>                              
      @endif 
      {!! Form::open(['url' => action('Admin\Banner\BannerGroupController@update', $result->id),'method'=>'PUT' ,'id'=>'addTranslationGroupForm', 'class'=>'form-horizontal']) !!}
      
        <div class="header-title">
          <h1 class="title">@lang('common.edit_banner_group') : @if(isset($result->group_name)) {{$result->group_name}} @else {{'N/A'}} @endif </h1>   
            <div class="form-actions float-right">
                <div class="btnmn btns-group">
                    <a class="btn btn-back" href="{{ action('Admin\Banner\BannerGroupController@index') }}">@lang('common.back')</a>
                    <button type="submit" class="btn-md btn-effect-ripple btn btn-save btn-success">@lang('common.save')</button>                    
                </div>
            </div>
        </div>
        <div class="content-wrap">
            <div class="breadcrumb">
                  <ul class="bredcrumb-menu">
                      {!!getBreadcrumbAdmin('block','bannergroup')!!}
                  </ul>
            </div>
            <div class="row"> 
                <div class="col-sm-4">
                   <div class="form-group">
                        <label for="form-text-input">@lang('cms.group_name')</label>
                        <div class="">
                            {!! Form::text('group_name', old('group_name', $result->group_name), ['placeholder'=>'Group Name', 'class'=>'form-control'])
                            !!}
                            @if ($errors->has('group_name'))
                          <p id="group_name-error" class="error error-msg">{{ $errors->first('group_name') }}</p>
                           @endif
                        </div>                    
                   </div>
                   <div class="form-group">
                          <label for="form-text-input">@lang('common.status')</label>
                          <div>
                              {!! Form::select('status', ['1'=>'Active', '0'=>'Deactive'],  $result->status) !!}
                         </div>
                    </div>
                    <div class="form-group">
                        <label for="form-text-input">@lang('common.height')</label>
                        <div class="">
                        {!! Form::text('height', old('height', $result->height), ['placeholder'=>'', 'class'=>'form-control', 'onkeypress' => 'return isNumberKey(event)'])
                        !!}
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="form-text-input">@lang('common.width')</label>
                        <div class="">
                        {!! Form::text('width', old('width', $result->width), ['placeholder'=>'', 'class'=>'form-control', 'onkeypress' => 'return isNumberKey(event)'])
                        !!}
                        </div>
                    </div>
                    <div class="form-group">                    
                          <label class="check-wrap">
                              @if($result->auto_loop == 'true')
                             {!! Form::checkbox('auto_loop',old('auto_loop'),$result->auto_loop)
                              !!}
                              @else
                              {!! Form::checkbox('auto_loop',old('auto_loop'))
                              !!}
                              @endif

                              <span class="chk-label">@lang('cms.slide_auto_loop')</span>
                         </label>                        
                    </div>
                    <div class="form-group">
                      <label for="form-text-input">@lang('cms.slide_speed') (@lang('cms.milliseconds'))</label>
                          <div class="">
                            @if($result->slide_speed>0)                      
                             {!! Form::text('slider_speed', old('slider_speed',$result->slide_speed ), ['placeholder'=>'slider speed', 'class'=>'form-control', 'id'=> 'slide_speed', 'onkeypress' => 'return isNumberKey(event)'])
                              !!}
                            @else
                               {!! Form::text('slider_speed', old('slider_speed'), ['placeholder'=>'slider speed', 'class'=>'form-control', 'id'=> 'slide_speed', 'onkeypress' => 'return isNumberKey(event)'])
                              !!}
                            @endif                        
                         </div>                        
                    </div>             
                </div>
            </div>
        </div>

        @if(count($banners))
            <table id="banners_table" class="banner-group-table">
              <thead>
                <tr>
                  <th></th>
                  <th>S.No</th>
                  <th>Banner Title</th>
                  <th>Image</th>
                  <th>Status</th>
                </tr>
              </thead>
              <tbody>
                @foreach($banners as $index => $banner)                  
                  <tr>
                    <td>                      
                        <span class="glyphicon col glyphicon-menu-hamburger ui-sortable-handle handle"></span>
                    </td>
                    <td>
                      <input type="hidden" name="banner[]" value="{{$banner->id}}" data-attr="{{$banner->id}}" />
                      <span>{{$index+1}} </span>                 
                    </td>
                    <td>
                      {{$banner->admin_title  ?? ''}}
                    </td>
                    <td>
                      <img src="{{Config::get('constants.banner_url')}}{{$banner->banner_image}}"  width="150">
                    </td>
                    <td>Active</td> 
                  </tr>
                @endforeach
              </tbody>
            </table>
        @endif

        @if(count($dbanners))
            <table class="banner-group-table">
              <tbody>
                @foreach($dbanners as $index => $banner)                  
                  <tr>
                    <td></td>
                    <td>
                      <input type="hidden" name="banner[]" value="{{$banner->id}}" data-attr="{{$banner->id}}" />
                      <span>{{$index+1}} </span>                 
                    </td>
                    <td>
                      {{$banner->admin_title  or ''}}
                    </td>
                    <td>
                      <img src="{{Config::get('constants.banner_url')}}{{$banner->banner_image}}"  width="150">
                    </td>
                    <td>Deactive</td> 
                  </tr>
                @endforeach
              </tbody>
            </table>
        @endif
      {!! Form::close() !!}
    </div>

@stop

@section('footer_scripts') 
<!-- begining of page level js -->
  <script type="text/javascript">
    (function($){
      //apply sortable on banner table
      $('#banners_table tbody').sortable({
        handle: ".glyphicon-menu-hamburger",
        axis: 'y',
        cursor: 'move',
        containment: "parent",
        tolerance: "pointer",
        update: function(event, ui ) {        
          $(this).children().each(function(index) {
            var id = $(this).find('td:nth-child(2) input[type="hidden"]').val();
            $(this).find('td:nth-child(2) span').html(index + 1);
            id = id.split('_')[0];
            id = id + '_'+ (index + 1);
            $(this).find('td:nth-child(2) input[type="hidden"]').val(id);           
          });
        }
      }).disableSelection();

    })(jQuery);

  </script>

<!-- end of page level js -->       
@stop
