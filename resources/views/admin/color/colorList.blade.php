@extends('layouts/admin/default')

@section('title')
    @lang('cms.color_setting')
@stop

@section('header_styles')
    <link rel="stylesheet" href="{{ Config('constants.admin_css_url') }}bootstrap-colorpicker.css"/>
@stop

@section('content')

    <!-- Right side column. Contains the navbar and content of the page -->
    <div class="content">
        @if(Session::has('succMsg'))    
            <script type="text/javascript">               
                _toastrMessage('success', "{{ Session::get('succMsg') }}");    
            </script>                              
        @endif        
        <div id="tab3" class="tab-pane">
        
            <div class="header-title clearfix">                     
                <h1 class="title">
                   @lang('cms.color_setting')     
                </h1>
                    
            </div>
            <div class="content-wrap">
              
              {!! Form::open(['url' => action('Admin\Color\ColorController@updateAll'), 'id'=>'colorForm', 'class'=>'form-horizontal  form-bordered']) !!}

                      <div class="form-group">
                        <div class="col-md-4">&nbsp;</div>
                        <div class="col-md-8">
                           {!!Form::submit('Update All', ['class' => 'btn btn-success', 'name'=>"updateall"]) !!}
                          
                          </div>
                      </div>
                     <div class="form-group clearfix">
                       <div class="col-md-2 text-right"><h3><strong class=""> @lang('cms.variable_name') <strong></h3></div>
                        <div class="col-md-4"><h3><strong> @lang('cms.color_code') </strong></h3></div>
                        <div class="col-md-2"></div>
                    </div>
                                                  
                    <div class="form-group original-group">
                    @php ($i = 1) 
                    @if(count($color_list) > 0)
                      @foreach($color_list as $key=>$result)

                            <div class="clearfix form-group  @if($i == 1) original @else cloneData @endif">
                              <div class="col-md-2">
                                 <label class="control-label">{{$result->variable_name}}</label>
                                   
                              </div>
                              <div class="col-md-4">
                                <span class="input-group colorpickers colorpicker-component colorpicker-element">
                                <input type="text" name="cvalues[{{$result->id}}]" value="{{ $result->color_code or ''}}" class="form-control tvalues" placeholder="source value">
                                <span class="input-group-addon"><i style="background-color: #df455f;"></i></span>
                               </span>
                                <!-- <span class="input-group-addon"><i></i></span> -->
                               </div> 
                               {{-- <div class="col-md-4">
                                 <input type="text" name="comments[{{$result->id}}]" value="{{ $result->comment or ''}}" class="form-control comments" placeholder="Comment">
                               </div>  --}}
                               <div class="col-md-2">
                                 <button type="button" class="btn singleUpdate"> @lang('common.updated') </button><br>
                                 
                               </div>
                               <div class="alert alert-warning updatemessage" style="display: none;"></div>
                             </div> 
                            @php ($i++) 
                       @endforeach

                       @else
                           @lang('common.please_add_source').
                       @endif

                    </div>
                    

                    

                  {!! Form::close() !!}
            </div>
          
        </div>
    </div>
      
@stop

@section('footer_scripts')
 
    <!-- begining of page level js -->
    <!-- end of page level js -->
    <script >
    var singleUrl = "{{ action('Admin\Color\ColorController@updateSingleColor') }}";
     jQuery('button.singleUpdate').click(function(e){
         e.preventDefault();
         var thiscap = jQuery(this);
         var path = thiscap.parent('div').siblings();
         var sources = path.children('input[name^="cvalues"]').val();
         var sourcesname = path.children('input[name^="cvalues"]').attr('name');
         var comments = path.children('input[name^="comments"]').val();
         jQuery.ajax({
                    url: singleUrl,
                    type: 'POST',
                    data: '_token=' + window.Laravel.csrfToken + '&'+sourcesname+'=' + sources + '&comments=' + comments,
                    success: function (response) {
                        jQuery('.updatemessage').hide();
                        thiscap.parent().siblings('.updatemessage').text(response).show();  
                  }

                });
     });

    </script>    
    <script src="{{ asset('assets/js/bootstrap-colorpicker.js') }}"></script>
    <script>
      $(function() { $('.colorpickers').colorpicker(); });
    </script>
@stop
