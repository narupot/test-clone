@extends('layouts/admin/default')

@section('title')
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
            <h1 class="title">@lang('translation.search_translation')</h1>
        </div>
        <div class="content-wrap">
            <div class="breadcrumb">
                <ul class="bredcrumb-menu">
                    {!!getBreadcrumbAdmin('config','translation')!!}
                </ul>
            </div>                         
            {!! Form::open(['url' => action('Admin\Translation\TranslationController@searchTranslation'), 'id'=>'sourceManageForm', 'class'=>'form-horizontal', 'method'=>'get']) !!}
                <div class="row">
                    <div class="col-sm-4">
                        <div class="form-group">
                            <div class="dataTables_length" id="table_length">
                                <label>@lang('translation.select_language'):</label>
                                {!! Form::select('lang_id', $languages,  old('lang_id', $request->lang_id), ['class'=>'form-control', 'id'=>'lang_id']) !!}
                            </div>
                        </div>
                        <div class="form-group">
                            <div class="dataTables_length" id="table_length">
                                <label>@lang('translation.select_module'):</label>
                                {!! Form::select('module_id', $modules,  old('module_id', 0), ['class'=>'form-control']) !!}
                            </div>
                        </div>
                        <div class="form-group">
                            <label>@lang('common.search_keyword'):</label>
                            {!! Form::text('search_key', old('search_key', $request->search_key), ['placeholder'=>Lang::get('common.search_keyword'), 'class'=>'form-control'] ) !!} 
                        </div>
                        <div class="form-group">   
                            <button type="submit" class="btn btn-primary">@lang('common.search')</button> 
                        </div>
                    </div>
                </div>
                
            {!! Form::close() !!}

            @php ($i = 1) 
            @if(!empty($results) && count($results)>0)
                <div class="col-md-12 form-group"> 
                    <div class="col-md-2 text-right"><h3><strong>@lang('translation.source_key')</h3></strong></div>
                    <div class="col-md-4"><h3><strong>@lang('translation.source_value')</h3></strong></div>
                    <div class="col-md-4"><h3><strong>@lang('common.action')</h3></strong></div>
                    <div class="col-md-2"></div> 
                </div>
                @foreach($results as $key=>$result)
                    <div class="form-group col-md-12 searchdiv">
                        <div class="col-md-2">
                            <label class="control-label text-right">{{$result->source}}</label> 
                        </div>
                        <div class="col-md-4">
                            <input type="text" name="source_value[{{ $result->id.'_'.$result->source_id.'_'.$result->module_id}}]" value="{{$result->source_value}}" class="form-control" placeholder="@lang('translation.source_value')">
                        </div> 
                        <div class="col-md-4">
                            <a type="button" class="btn btn-success singleUpdate" > @lang('common.update') </a>
                            <a type="button" class="btn btn-delete" rev="{{$result->source_id}}"> @lang('common.delete') </a>
                        </div>
                        <div class="col-md-2">
                        </div>
                        <div class="col-md-7 alert alert-danger updatemessage"  style="display: none;"></div> 
                    </div> 
                    @php ($i++) 
                @endforeach
            @elseif(isset($request->lang_id) && $request->lang_id>0)
              <div class="col-md-7 alert alert-danger"> @lang('common.no_record_found')</div>
            @endif                  
        </div>
    </div>   
@stop

@section('footer_scripts')
<script >
    var key_delete_url = "{{ action('Admin\Translation\TranslationController@deleteSingleSource') }}";
    var update_url = "{{ action('Admin\Translation\TranslationController@searchTranslationUpdate') }}";
     
    jQuery('a.singleUpdate').click(function(e){
        e.preventDefault();
        var thiscap = jQuery(this);
        var path = thiscap.parent('div').siblings();
        var source_value = $.trim(path.children('input[name^="source_value"]').val());
        var source_name = path.children('input[name^="source_value"]').attr('name');
        var lang_id = $('#lang_id').val();

        jQuery('.updatemessage').hide();
        if(source_value == '') {
            thiscap.parent().siblings('.updatemessage').removeClass('alert-success').addClass('alert-danger');
            thiscap.parent().siblings('.updatemessage').text('{{ Lang::get('translation.please_enter_source_value') }}').show();
            return false;
        }

        jQuery.ajax({
            url: update_url,
            type: 'POST',
            data: '_token='+window.Laravel.csrfToken+'&'+source_name+'='+source_value+'&lang_id='+lang_id,
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

    jQuery('a.singleDelete').click(function(e){
        e.preventDefault();
        var thiscap = jQuery(this);
        var id = thiscap.attr('rev');
        jQuery.ajax({
            url: key_delete_url,
            type: 'POST',
            data: '_token=' + window.Laravel.csrfToken + '&id=' + id,
            success: function (response) {
                if(response == 'sucess'){
                    thiscap.parent().parent('.searchdiv').remove();
                } 
            }
        });
    });
    </script> 
    
@stop
