@extends('layouts/admin/default')

@section('title')
    @lang('cms.create_banner_group') - {{getSiteName()}} 
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

        {!! Form::open(['url' => action('Admin\Banner\BannerGroupController@store'), 'id'=>'addTranslationGroupForm', 'class'=>'']) !!}
          <div class="header-title">
              <h1 class="title"> <!-- @lang('cms.create_banner') --> Create Banner Group</h1>
              <div class="form-actions float-right">
                <div class="btns-group">                   
                    <a class="btn btn-back" href="{{ action('Admin\Banner\BannerGroupController@index') }}">@lang('common.back')</a>
                     <button type="submit" class="btn btn-save btn-success">@lang('common.save')</button>
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
                            {!! Form::text('group_name', old('group_name'), ['placeholder'=>'Group Name', 'class'=>'form-control'])
                            !!}
                            @if ($errors->has('group_name'))
                          <p id="name-error" class="error error-msg">{{ $errors->first('group_name') }}</p>
                           @endif
                         </div>                      
                  </div>

                  <div class="form-group">
                    <label for="form-text-input">@lang('common.status')</label>
                        <div class="">
                            {!! Form::select('status', ['1'=>'Active', '0'=>'Deactive'],  null) !!}
                       </div>
                        
                  </div>
                  <div class="form-group">
                    <label for="form-text-input">@lang('common.height')</label>
                        <div class="">
                           {!! Form::text('height', old('height'), ['placeholder'=>'', 'class'=>'form-control', 'onkeypress' => 'return isNumberKey(event)'])
                            !!}
                       </div>
                        
                  </div>
                  <div class="form-group">
                    <label for="form-text-input">@lang('common.width')</label>
                        <div class="">
                           {!! Form::text('width', old('width'), ['placeholder'=>'', 'class'=>'form-control', 'onkeypress' => 'return isNumberKey(event)'])
                            !!}
                       </div>
                        
                  </div>
                  <div class="form-group">                    
                        <label class="check-wrap">
                           {!! Form::checkbox('auto_loop', old('auto_loop'))
                            !!}
                            <span class="chk-label">@lang('cms.slide_auto_loop')</span>
                       </label>                        
                  </div>
                  <div class="form-group">
                    <label for="form-text-input">@lang('cms.slide_speed')(@lang('cms.milliseconds'))</label>
                        <div class="">
                           {!! Form::text('slider_speed', old('slider_speed'), ['placeholder'=>'slider speed', 'class'=>'form-control', 'id'=> 'slide_speed', 'onkeypress' => 'return isNumberKey(event)'])
                            !!}                        
                       </div>                        
                  </div>
                </div>
              </div>
          </div>
        {!! Form::close() !!}
    </div>
      
@stop

@section('footer_scripts')
<script type="text/javascript">
//
</script>
 
<!-- begining of page level js -->

<!-- end of page level js -->       
@stop
