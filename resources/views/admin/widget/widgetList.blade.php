@extends('layouts/admin/default')

@section('title')
    @lang('blog.widget_list')
@stop

@section('header_styles')
    <style type="text/css">
        #loader-wrapper {
            display: none;
        }
    </style>  
@stop

@section('content')
    <!--Overlay loader show on save or save and continue click -->
    <div class="loader-wrapper" id="loader-wrapper">
        <span class="loader">
            <img src="" alt="Loader" width="30" height="30" id="loader-img"> 
            <div>Please wait...</div>
        </span>
    </div>
    <!-- Right side column. Contains the navbar and content of the page -->
    <div class="content">
        @if(Session::has('succMsg'))
            <script type="text/javascript">               
                _toastrMessage('success', "{{ Session::get('succMsg') }}");    
            </script> 
        @endif 
        <div class="header-title">
            <h1 class="title">@lang('blog.widget_list')</h1>
            <div class="float-right"><input type="button" id="btnSave" value="Save" class="btn btn-success"></div>
        </div>
        <div class="content-wrap">
            <div class="breadcrumb">
                <ul class="bredcrumb-menu">
                    {!!getBreadcrumbAdmin('blog','blog')!!}
                </ul>
            </div>
            <div class="table ">
                <ul class="blockConfig">
                    <li class="row">
                        <div class="col-sm-4"><h3>@lang('blog.widget')</h3></div>
                        <div class="col-sm-4">
                            <h3>@lang('cms.region')</h3>
                        </div>
                        <div class="col-sm-4">
                            <h3>@lang('cms.opertation')</h3>
                        </div>
                    </li>
                    @foreach($section as $key => $sec_res)
                        <li class="row">
                            <div class="col-sm-12">
                                <strong class="configblock-title">{{ $sec_res->sec_name }}</strong>
                            </div>
                        </li>
                        @if(isset($sec_res->block_list) && count($sec_res->block_list))
                            @foreach($sec_res->block_list as $bkey => $block)
                        <li class="list-group-item @if($block->is_fix == '1') dragFalse @endif" data-block="{{ $block->id }}" data-attr="sec_{{ $sec_res->id }}">
                            <div class="row">
                                <div class="col-sm-4"><span class="fas fa-bars menu-hamburger"></span> {{ $block->type_name }}</div>
                                <div class="col-sm-4">
                                    <select class="section-dd" @if($block->is_fix == '1') disabled="disabled" @endif>
                                    <option value="0">@lang('common.none')</option>
                                    @foreach($section as $skey => $detail)
                                    <option value="{{ $detail->id }}" @if($detail->id == $sec_res->id) selected="selected" @endif>{{ $detail->sec_name }}</option>
                                    @endforeach
                                    </select>
                                </div>
                                <div class="col-sm-4">
                                <div class="btns-group">
                                    @if($permission_arr['edit'] === true && $block->is_fix == '0')
                                        <a class="btn btn-dark" href="{{ action('Admin\Widget\WidgetController@edit',$block->id) }}">@lang('common.edit')</a> 
                                    @endif
                                    @if($block->is_fix == '0' && $permission_arr['delete'] === true)
                                        <a href="javascript:;" class="btn btn-delete btn-danger">@lang('common.delete')</a>
                                    @endif
                                    <!--
                                    <a href="javascript:;" class="preview" id="{{ $block->id }}">@lang('common.preview')</a>-->
                                    </div>
                                </div>
                            </div>
                        </li>
                            @endforeach
                        @endif
                    @endforeach

                    @if(isset($widget_disable[0]) && count($widget_disable[0]))
                        <li class="row">
                            <div class="col-sm-12"><strong class="configblock-title">@lang('common.unassigned')</strong></div>
                        </li>                    
                        @foreach($widget_disable[0] as $dkey => $disable)
                            <li class="list-group-item" data-block="{{ $disable->id }}" data-attr="sec_0">
                                <div class="row">
                                    <div class="col-sm-4"><span class="fas fa-bars menu-hamburger"> </span> {{ $disable->type_name }}</div>
                                    <div class="col-sm-4">
                                        <select class="section-dd">
                                        <option value="0">@lang('common.none')</option>
                                        @foreach($section as $skey => $detail)
                                        <option value="{{ $detail->id }}">{{ $detail->sec_name }}</option>
                                        @endforeach
                                        </select>
                                    </div>
                                    <div class="col-sm-4">
                                        <!-- <a href="javascript:;" class="del-block">@lang('common.delete')</a> -->
                                        <a class="btn btn-dark" href="{{ action('Admin\Widget\WidgetController@edit',$disable->id) }}">@lang('common.edit')</a>
                                        <!--<a href="javascript:;" class="preview" id="{{ $block->id }}">@lang('common.preview')</a>-->
                                    </div>
                                </div>
                            </li>
                        @endforeach
                    @endif
                </ul>
            </div> 
        </div>
    </div>
    <div id="popupdiv" class="modal fade" role="dialog">
      <div class="modal-dialog">
        <!-- Modal content-->
        <div class="modal-content">
          <div class="modal-header">
            <h2 class="modal-title"></h2>
            <span class="fas fa-times" data-dismiss="modal"></span> 
          </div>
          <div class="modal-body" id="contentSec">
            <h3></h3>         
            
          </div>
          <div class="col-sm-12 form-group">
              <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
          </div>
        </div>
      </div>
    </div>  
@stop

@section('footer_scripts')

<script type="text/javascript">
    //variable declare
    var delUrl = "{{ action('Admin\Widget\WidgetController@delectWidget') }}";
    var saveUrl = "{{ action('Admin\Widget\WidgetController@updateWidgetSection') }}";
    var prevUrl = "{{ action('Admin\Widget\WidgetController@previewWidget') }}";
    var tableLoaderImgUrl = "{{ Config::get('constants.loader_url')}}ajax-loader.gif";

    ;(function($){
        //toster setting
        toastr.options = {
          "positionClass": "toast-top-center",          
        }; 
           
        //Listen on content change
        $(document).on('change','.section-dd',function(){
            var sec_id = $(this).val();
            var $lisec = $(this).closest("li");
            let sind =0;
            $('.blockConfig li.list-group-item').each(function(){
                if($(this).attr('data-attr') ==='sec_'+sec_id){
                    sind = $(this).index();
                }
            });
            $($lisec).attr('data-attr','sec_'+sec_id);
            $('.blockConfig li:eq('+sind+')').after($lisec);
        });
        $(document).on('click','#btnSave',function(){
            var output =[];
            $('.blockConfig li.list-group-item').each(function(index){
                var section = $(this).attr('data-attr');
                var sid = section.replace('sec_','');
                var block = $(this).attr('data-block');
                let existing = output.filter(function(v, i) {return v.name == section;});
                if(existing.length){
                   var existingIndex = output.indexOf(existing[0]);
                   output[existingIndex].value = output[existingIndex].value.concat(block);
                }else{
                output.push({"name" :section,"value":[block]});
                }
            });
           
            if(confirm('Are u sure want to save ?')){
                $.ajax({
                    url: saveUrl,
                    method : 'POST',
                    beforeSend : ()=>{
                        $('#loader-wrapper').show();
                        $('#loader-img').attr('src',tableLoaderImgUrl);
                    },
                    data : {data : output,_token : window.Laravel.csrfToken},
                }).done((data)=>{
                    let msg = data.split('admin.')[1];
                    $('#loader-wrapper').hide();
                    location.reload(true);
                    Command: toastr["success"](msg);                                                       
                }).fail((err)=>{
                   try{
                    throw new Error("Something went badly wrong!");
                   }catch(e){
                    alert(e);
                   };
                }).always((data)=>{
                    $('#loader-wrapper').hide();
                });             
            }
        });
        $(document).on('click','.del-block',function(){
            var blockId = $(this).closest("li").attr('data-block');
            var _this = $(this);
            if(confirm('Are u sure want to delete ?')){
                $.ajax({
                    url: delUrl,
                    method : 'POST',
                    beforeSend : ()=>{
                        $('#loader-wrapper').show();
                        $('#loader-img').attr('src',tableLoaderImgUrl);
                    },
                    data : {id : blockId,_token : window.Laravel.csrfToken},
                }).done((data)=>{
                    // let msg = data.split('admin.')[1];
                    // _this.closest('li').remove();
                    // $('#loader-wrapper').hide();
                    // Command: toastr["success"](msg);
                    //setTimeout(function(){ location.reload(); }, 1000);
                    location.reload();
                }).fail((err)=>{
                   try{
                    throw new Error("Something went badly wrong!");
                   }catch(e){
                    alert(e);
                   };
                }).always((data)=>{
                    $('#loader-wrapper').hide();
                });
            }
        });
        $(document).on('click','.preview',function(){
            var id = $(this).attr('id');
            $.post(prevUrl,{id : id,_token : window.Laravel.csrfToken},function(response){
                    $('#contentSec h3').html(response);
                  jQuery('#popupdiv').modal('show');
            })
        });
        $('.blockConfig').sortable({
          placeholderClass: 'list-group-item',
          handle: 'span.menu-hamburger',
          update : function(event,ui){
            var $nextSiblingElement=ui.item[0].nextElementSibling,
                $prevSiblingElement =ui.item[0].previousElementSibling;
                currentDataAttr =ui.item[0].dataset,
                prevDataAttr ='';
            if(typeof $prevSiblingElement!==undefined && $($prevSiblingElement).length>0 && typeof $($prevSiblingElement).attr('data-attr')!=="undefined"){               
              let sec = $($prevSiblingElement).attr('data-attr'); 
               if(sec!=currentDataAttr.attr){
                    $(ui.item[0]).attr('data-attr',sec);
               }                     
            }else if(typeof $nextSiblingElement!==undefined && $($nextSiblingElement).length>0 && typeof $($nextSiblingElement).attr('data-attr')!=="undefined"){
                let sec = $($nextSiblingElement).attr('data-attr'); 
                if(sec!=currentDataAttr.attr){
                    $(ui.item[0]).attr('data-attr',sec);        
                }                            
            }
          },         
        });
        $('.blockConfig li.dragFalse').each(function(){
            $(this).attr('draggable',false);
        });     
    })(jQuery);
</script>
    
@stop
