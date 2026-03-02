@extends('layouts/admin/default')

@section('title')
    @lang('translation.add_source_value')
@stop

@section('header_styles')
    
@stop

@section('content')

    <div class="content">
        
        <!-- Main content -->       
        {!! Form::open(['url' => action('Admin\Translation\TranslationController@addsourcevaluesave'), 'id'=>'addTranslationsourceForm', 'class'=>'form form-bordered']) !!}

            {!! Form::hidden('module_id', $module_id, ['id'=>'module_id']) !!}
            {!! Form::hidden('lang_id', $lang_id, ['id'=>'lang_id']) !!}
            {!! Form::hidden('import_status', $import_detail['csv_import_status'], ['id'=>'import_status']) !!}

            <div class="header-title">
                <h1 class="title">@lang('translation.module_name'): {{ $lang_name->module_name }}</h1>
                @if($import_detail['csv_import_status'] == '0')
                    <div class="float-right">
                        {!!Form::submit(Lang::get('common.update_all'), ['class' => 'btn', 'name'=>"updateall"]) !!}
                    </div>
                @endif                             
            </div>
            <div class="content-wrap">
                <div class="breadcrumb">
                    <ul class="bredcrumb-menu">
                        {!!getBreadcrumbAdmin('config','translation')!!}
                    </ul>
                </div>
                <div class="team-title">
                    <h2>@lang('translation.language'): {{$lang_name->languageName}}</h2>
                    @if($import_detail['csv_import_status'] == '1')
                        <div class="file-import">
                            <span class="form-control"><b>@lang('translation.import_date'): </b>{{ getDateFormat($import_detail['csv_import_date'], '4') }}</span>
                            <span class="form-control"><b>@lang('common.status'): </b>@lang('translation.wating_approve')</span>
                            <button type="submit" class="btn btn-success" name="updateall" value="accept">@lang('common.accept')</button>
                            <button type="submit" class="btn btn-success" name="updateall" value="reject">@lang('common.reject')</button>
                        </div>
                    @endif                     
                </div>             
                <div class="row form-group">
                    <div class="col-md-2 text-right"><h3><strong>@lang('translation.source_key')</h3></strong></div>
                    @if($import_detail['csv_import_status'] == '1')
                        <div class="col-md-4"><h3><strong>@lang('translation.new_value')</h3></strong></div>
                    @endif                    
                    <div class="col-md-4"><h3><strong>@lang('translation.translation')</h3></strong></div>
                    @if($import_detail['csv_import_status'] == '0')
                        <div class="col-md-1"><h3><strong>@lang('common.action')</h3></strong></div>
                    @endif
                </div>                                    
                <div class="form-group original-group">
                @php ($i = 1) 
                @if(count($results) > 0)
                    @foreach($results as $key=>$result)
                        <div class="form-group row @if($i == 1) original @else cloneData @endif">
                            <div class="col-md-2">
                                <label class="control-label">{{$result->source}}</label>  
                            </div>
                            @if($import_detail['csv_import_status'] == '1')
                                <div class="col-md-4">
                                    <input type="text" name="new_value[{{$result->id}}]" value="{{ isset($save_new_value[$result->id])? $save_new_value[$result->id]:''}}" class="form-control" placeholder="new value">
                                </div>
                            @endif                             
                            <div class="col-md-4">
                                <input type="text" name="source_value[{{$result->id}}]" value="{{ isset($savechangetvalues[$result->id])?$savechangetvalues[$result->id]:''}}" class="form-control" placeholder="@lang('translation.source_value')">
                            </div> 
                            <div class="col-md-1">
                            @if($import_detail['csv_import_status'] == '0')
                                <button type="button" class="btn singleUpdate"> @lang('common.update') </button>
                            @endif
                            <br>
                            </div>
                            <div class="col-md-8 alert alert-danger updatemessage" style="display: none;"></div>
                        </div> 
                        @php ($i++) 
                    @endforeach
                @else
                    @lang('admin.please_add_source').
                @endif
                </div> 
            </div>
        {!! Form::close() !!}
    </div>   
@stop

@section('footer_scripts')
 
<script >
    var update_url = "{{ action('Admin\Translation\TranslationController@addsinglesourcevalue') }}";

    jQuery('button.singleUpdate').click(function(e){
        e.preventDefault();
        var thiscap = jQuery(this);
        var path = thiscap.parent('div').siblings();
        var source_value = $.trim(path.children('input[name^="source_value"]').val());
        var source_name = path.children('input[name^="source_value"]').attr('name');
        var lang_id = jQuery('input#lang_id').val();
        var module_id = jQuery('input#module_id').val();
        //alert('source_value: '+source_value+');return;

        jQuery('.updatemessage').hide();
        if(source_value == '') {
            thiscap.parent().siblings('.updatemessage').removeClass('alert-success').addClass('alert-danger');
            thiscap.parent().siblings('.updatemessage').text('{{ Lang::get('translation.please_enter_source_value') }}').show();
            return false;
        }

        jQuery.ajax({
            url: update_url,
            type: 'POST',
            data: '_token='+window.Laravel.csrfToken+'&'+source_name+'='+source_value+'&lang_id='+lang_id+'&module_id='+module_id,
            success: function (response) {

                var response = JSON.parse(response);
                if(response.status == 'success'){
                    thiscap.parent().siblings('.updatemessage').removeClass('alert-danger').addClass('alert-success');
                }
                else {
                    thiscap.parent().siblings('.updatemessage').removeClass('alert-success').addClass('alert-danger');
                }
                thiscap.parent().siblings('.updatemessage').text(response.response).show();
            }
        });
    });
</script>    
    
@stop
