@extends('layouts/admin/default')

@section('title')
    @lang('translation.add_module_key')
@stop

@section('header_styles')
    
@stop

@section('content')

    <div class="content">
        @if(Session::has('succMsg'))    
            <script type="text/javascript">               
                _toastrMessage('success', "{{ Session::get('succMsg') }}");    
            </script>                            
        @elseif(Session::has('errorMsg'))
            <script type="text/javascript"> 
                _toastrMessage('error', "{{ Session::get('errorMsg') }}");
            </script>  
        @endif       
        <!-- Main content -->
        {!! Form::open(['url' => action('Admin\Translation\TranslationController@store'), 'id'=>'addTranslationsourceForm', 'class'=>'form  form-bordered']) !!}
            {!! Form::hidden('module_id', $module_id, ['id'=>'module_id']) !!}        
            <div class="header-title clearfix">
                <h1 class="title">@lang('translation.module_name'): {{ $moduleName->module_name }}</h1>
                <div class="float-right">
                    <a class="btn btn-back" href="{{ action('Admin\Translation\TranslationController@index') }}">@lang('common.back')</a>
                    <a class="btn btn-create add-clone">@lang('translation.add_new_key')</a>
                    {!!Form::submit(Lang::get('common.update_all'), ['class' => 'btn', 'name'=>"updateall"]) !!}
                    {!!Form::submit(Lang::get('common.update_selected'), ['class' => 'btn', 'name'=>"updateselected"]) !!}
                    <input class="btn btn-delete" name="removeseleced" onclick="return confirm('@lang('translation.are_you_sure_to_delete_selected_records')')" value="@lang('common.remove_selected')" type="submit">
                    <input class="btn btn-delete" name="removeall" onclick="return confirm('@lang('translation.are_you_sure_to_delete_all_records')')" value="@lang('common.remove_all')" type="submit"> 
                </div>             
            </div>
            <div class="content-wrap">
                <div class="breadcrumb">
                    <ul class="bredcrumb-menu">
                        {!!getBreadcrumbAdmin('config','translation')!!}
                    </ul>
                </div>
                <div class="form-group row">
                    <div class="col-md-1">
                        <label class="check-wrap">
                            <input type="checkbox" id="allcheckbox" name="allcheckbox" value="" class="form-control">
                            <span class="chk-label"></span>
                        </label>
                    </div>
                    <div class="col-md-6"><h3><strong>@lang('translation.source_key')</strong></h3></div>
                    <div class="col-md-4"></div>
                </div>                                  
                <div class="form-group original-group">
                @php ($i = 1) 
                @if(count($results)>0)
                    @foreach($results as $key=>$result)
                        <div class="row @if($i == 1) original @else cloneData @endif">
                            <div class="col-md-1 form-group">
                                <label class="check-wrap mt-10">
                                    <input type="checkbox" name="checkboxes[{{$result->id}}]" value="" class="form-control checkboxes" placeholder="">
                                    <span class="chk-label"></span>
                                </label>   
                            </div>
                            <div class="col-md-6 form-group">
                                <input type="text" name="sources[{{$result->id}}]" value="{{$result->source}}" class="form-control sources" placeholder="@lang('translation.source_key')">
                            </div> 
                            <div class="col-md-4 form-group">
                                <button type="button" class="btn singleUpdate"> @lang('common.update') </button>
                                <a class="btn btn-delete singleDelete" rev="{{$result->id}}"> @lang('common.delete') </a>
                            </div>
                            <div class="col-md-11 alert alert-danger updatemessage" style="display: none;"></div>
                        </div> 
                        @php ($i++) 
                   @endforeach
                @else
                    <div class="original row">
                        <div class="col-md-1 form-group">
                           <input type="checkbox" name="checkboxes[]" value="" class="form-control checkboxes">
                        </div>
                        <div class="col-md-6 form-group">
                            <input type="text" name="sources[]" value="" class="form-control sources" placeholder="@lang('translation.source_key')">
                        </div>
                        <div class="col-md-4 form-group">
                            <button type="button" class="btn singleUpdate"> @lang('common.update') </button>
                            <a style="display:none" class="btn btn-delete" rev="">@lang('common.delete')</a>  
                        </div>
                        <div class="col-md-11 alert alert-danger updatemessage"  style="display: none;"></div>
                    </div> 
                @endif
                </div>    
            </div>
        {!! Form::close() !!}
    </div>
      
@stop

@section('footer_scripts')

    <script src="{{ Config('constants.admin_js_url') }}lang/en.lang.js"></script>
    <script src="{{ Config('constants.admin_js_url') }}translation.js"></script>

    <script >
        var key_update_url = "{{ action('Admin\Translation\TranslationController@addsinglesourcedata') }}";
        var key_delete_url = "{{ action('Admin\Translation\TranslationController@deleteSingleSource') }}";
    </script>    
    
@stop
