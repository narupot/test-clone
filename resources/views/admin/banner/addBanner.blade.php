@extends('layouts/admin/default')

@section('title')
    @lang('cms.create_banner') - {{getSiteName()}} 
@stop

@section('header_styles')
    <link rel="stylesheet" type="text/css" href="{{Config('constants.admin_css_url') }}flatpickr.min.css">
    <script src="{{ Config('constants.js_url') }}flatpickr.min.js"></script>
    <link rel="stylesheet" type="text/css" href="{{Config('constants.admin_css_url') }}cropper.min.css">

    <link rel="stylesheet" type="text/css" href="{{Config('constants.public_url')}}/angular-froala/bower_components/font-awesome/css/font-awesome.min.css">

    <script type="text/javascript">
        //for banner image cropper setting
        var cropper_setting = {!! getImageDimension('banner')!!};
        var banner_groups_data = {!! $banner_groups_data !!};
    </script>
@stop

@section('content')

    <!-- Right side column. Contains the navbar and content of the page -->
    <aside class="content">
        @if(Session::has('succMsg'))    
            <script type="text/javascript">               
                _toastrMessage('success', "{{ Session::get('succMsg') }}");    
            </script>                             
        @endif 
       {!! Form::open(['url' => action('Admin\Banner\BannerController@store'), 'id'=>'addTranslationBannerForm', 'class'=>'form-horizontal  form-bordered col-sm-4', 'files'=>True]) !!}
        <div id="tab3" class="tab-pane">
            <div class="header-title">
            
                <h1 class="title"> @lang('cms.create_banner')</h1>
             
                <div class="btn-groups float-right">
                    <a class="btn btn-back" href="{{ action('Admin\Banner\BannerController@index') }}">@lang('common.back')</a>
                    <button type="submit" name="submit_type" value="submit_continue" class="btn-secondary">@lang('common.save_and_continue')</button>
                    <button type="submit" name="submit_type" value="submit" class="btn btn-save">@lang('common.save')</button>
                </div>
            </div>
            <div class="content-wrap">
                <div class="breadcrumb">
                    <ul class="bredcrumb-menu">
                        {!!getBreadcrumbAdmin('block','banner')!!}
                    </ul>
                </div>   
                <div class="form-group">
                    <label for="form-text-input">@lang('cms.title') <i class="strick">*</i></label>
                    <div>
                        {!! Form::text('title', old('title'), ['class'=>'form-control']) !!}
                        @if ($errors->has('title'))
                            <p id="banner_image-error" class="error error-msg">{{ $errors->first('title') }}</p>
                        @endif                        
                    </div>
                </div>

                <div class="form-group">
                    <label for="form-text-input">@lang('cms.group')</label>
                    <div>
                        {!! Form::select('group_id', $groups,  null, ['class'=>'form-control', "id" => "group_id"]) !!}
                    </div> 
                </div>                

                <div class="form-group">
                    <label for="form-text-input">@lang('cms.tooltip')</label> 
                    <div>
                       {!! CustomHelpers::fieldstabWithLanuage([['field'=>'text', 'name'=>'banner_title', '', 'errorkey'=>'name']], '1', $errors) !!}
                    </div>
                </div>

                <div class="form-group">
                   {{--  <label for="form-text-input">@lang('cms.choose_banner') <i class="strick">*</i></label> --}}
                    <input type="hidden" name="banner_image" value="" id="banner_image_input">
                    @include('admin.includes.banner_image_upload')
                    <div>
                        {{-- {!! Form::file('banner_image') !!} --}}
                       
                        @if ($errors->has('banner_image'))
                            <p id="banner_image-error" class="error error-msg">{{ $errors->first('banner_image') }}</p>
                        @endif
                    </div>
                </div>

                <div class="form-group">
                    <div class="row">
                        <div class="col-sm-6">
                            <label for="form-text-input">@lang('common.start_date') <i class="strick">*</i></label>
                            <div>
                                {!! Form::text('start_date', old('start_date'), ['class' => 'date-select date-picker flatpickr', 'id'=>'start_date'] ) !!}
                                @if ($errors->has('start_date'))
                                    <p id="start_date-error" class="error error-msg">{{ $errors->first('start_date') }}</p>
                                @endif
                            </div>
                        </div>
                        <div class="col-sm-6">
                            <label for="form-text-input">@lang('common.expire_date')</label>
                            <div>
                                {!! Form::text('end_date', old('end_date'), ['class' => 'form-control date-select date-picker flatpickr', 'id'=>'end_date'] ) !!}
                            </div>
                        </div>
                    </div>
                </div>

                <div class="form-group">
                    <label for="form-text-input">@lang('cms.url_target')</label>
                    <div>
                        {!! Form::select('url_target', ['_blank'=>'Blank', '_parent'=>'Parent', '_top'=> 'Top', '_self'=>'Self'],  null, ['class'=>'form-control']) !!}
                    </div>
                </div>                

                <div class="form-group">
                    <label for="form-text-input">  @lang('cms.banner_url')</label>
                    <div>
                        {!! Form::text('banner_url', old('   banner_url'),  ['class' => 'form-control']) !!}
                    </div>  
                </div>
                <div class="form-group">
                    <label for="form-text-input">  @lang('cms.order')</label>
                    <div>
                        {!! Form::text('sort_order', old('sort_order'),  ['class' => 'form-control']) !!}
                    </div>  
                </div>

                <div class="form-group">
                    <label for="form-text-input">@lang('common.status')</label>
                    <div>
                        {!! Form::select('status', ['1'=>'Active', '2'=>'Deactive'],  null,['class' => 'form-control']) !!}
                    </div> 
                </div>

            </div>
        </div>
        {!! Form::close() !!}
    </aside>
      
@stop

@section('footer_scripts')
    <script src="{{ Config('constants.js_url') }}jquery-cropper.min.js" type="text/javascript"></script>
    <script src="{{ Config('constants.js_url') }}banner_cropper_setting.js" type="text/javascript"></script>
    <script>
        ;(function(){
        
            var from_date = flatpickr("#start_date", {
                 dateFormate : 'Y-m-d',
                 minDate : new Date(),
            });
            var to_date = flatpickr("#end_date", {
                dateFormate : 'Y-m-d',
                minDate : new Date(),
            });
          
            flatpickr("#start_date", {
                onChange: function(dateObj, dateStr) {
                    flatpickr("#end_date", {
                        minDate : dateStr,
                    });             
                }
            });

            flatpickr("#end_date", {
                onChange: function(dateObj, dateStr) {  
                    console.log("fdfsdfdf");   
                    flatpickr("#start_date", {
                        maxDate : dateObj
                    });
                 // from_date.set("maxDate", dateObj);
                }
            });

            //In case of page is load then check seleted banner group
            var group_val= $("#group_id").find("option:selected").val();
            if(group_val!== undefined && group_val) {
                configCropSetting(group_val);
            }
            
            //Listen on banner group change then update cropper setting
            $("#group_id").on('change', function(event){
                var that = $(this);
                var val_id = that.find("option:selected").val();

                if(val_id!== undefined && val_id){
                   configCropSetting(val_id);
                }
            });

            //update cropper setting on banner group change
            function configCropSetting(val_id){
                if(val_id == undefined || val_id == "") return;

                $.map(banner_groups_data, function(elem, index){
                    if(elem.id!== undefined && elem.id == val_id){
                        cropper_setting.width =  (elem.width!== undefined && elem.width) ? parseInt(elem.width) : cropper_setting.width;
                        cropper_setting.height = (elem.height!== undefined && elem.height) ? elem.height : cropper_setting.height;
                    }
                });
            };
            
        })(jQuery);
    </script>  
@stop