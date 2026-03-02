@extends('layouts/admin/default')

@section('title')
    @lang('admin.system_configuration')
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
            <h1 class="title">@lang('setting.pickup_center')</h1>
        </div>
        <div class="content-wrap clearfix">
            <div class="breadcrumb">
                <ul class="bredcrumb-menu">
                    {!!getBreadcrumbAdmin('config')!!}
                </ul>
            </div>
            <div class="row">
                <form action="{{ action('Admin\Config\SystemConfigController@updatePickupCenter') }}" method="post"  enctype="multipart/form-data" class="col-sm-4">
                    {{ csrf_field() }}
                    <div class="form-group">
                        <label>@lang('admin.pickup_center_name')</label>
                        <input type="text" name="name" value="{{ $res['name']??'' }}" required="required" class="form-control">
                    </div>
                    <div class="form-group">
                        <label>@lang('admin.pickup_center_location')</label>
                        <textarea name="location" required="required" class="form-control">{{ $res['location']??'' }}</textarea>
                    </div>                                
                    <div class="form-group">
                        <label>@lang('admin.pickup_center_contact')</label>
                        <input type="text" name="contact" value="{{ $res['contact']??'' }}" required="required" class="form-control">
                    </div>
                    <!-- <div class="form-group" style="display: none;">
                        <label>@lang('admin.estimate_pickup_from_center')</label>
                        <input type="text" name="estimate" value="" required="required" class="form-control">
                    </div> --> 

                    <div id="delivery_time">
                        <div class="form-group">
                            <label>@lang('admin_shipping.delivery_time_available_after')</label>
                            <input type="text" name="delivery_time_after" value="{{$delivery_time?$delivery_time->delivery_time_after:''}}">
                        </div>
                        <div class="form-group">
                            <label>@lang('admin_shipping.seller_need_to_prepare_item_before_customer_choose_slot')</label>
                            <input type="text" name="prepare_time_before" value="{{$delivery_time?$delivery_time->prepare_time_before:''}}">
                        </div>
                        <div class="input_fields_wrap">
                            
                            @if($delivery_time && count($delivery_time->time_slot))
                                @foreach($delivery_time->time_slot as $tkey => $tval)
                                    <div class="row align-items-center cloneData">
                                        <div class="col-md-8 form-group">
                                            <label>@lang('admin_shipping.time_slot_for_delivery')</label>
                                            <select name="time_slot[]">
                                                <option value="">Select</option>
                                                @for($i=7; $i<=23;$i++)
                                                    
                                                    <option value="{{ $i }}" @if($tval == $i) selected="selected" @endif>{{ $i.':00'}}</option>
                                                    
                                                @endfor
                                            </select>
                                        </div>
                                        <div class="col-sm-4 form-group actionsClone">
                                            <label>&nbsp;</label>
                                            <a href="javascript:;" class="minus-clone"><span class="ui-icon ui-icon-minusthick removeCss cursor-pointer mt10"></span></a>
                                        </div>
                                    </div>
                                    
                                @endforeach
                            @endif
                            <div class="row align-items-center original">
                                <div class="col-md-8 form-group">
                                    <label>@lang('admin_shipping.time_slot_for_delivery')</label>
                                    <select name="time_slot[]" id="times_slots">
                                        <option value="">Select</option>
                                        @for($i=7; $i<=23;$i++)
                                            
                                            <option value="{{ $i }}">{{ $i.':00'}}</option>
                                            
                                        @endfor
                                    </select>
                                </div>
                                <div class="col-sm-4 form-group actionsClone">
                                    <label>&nbsp;</label>
                                    <a href="javascript:;" class="btn btn-primary add_field_button" style="margin-bottom: 5px;"><i class="fa fa-plus align-baseline"></i></a>
                                </div>
                            </div>
                            <ui class="css-board"></ui>
                        </div>
                    
                </div>                                

                    <div class="form-group form-actions">
                        <div class="">
                            <button type="submit" class="btn btn-primary">@lang('common.update')</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
      
@stop

@section('footer_scripts')  
<script type="text/javascript">
    $(document).ready(function() {
        $('.add_field_button').click(function(e){
            e.preventDefault();
            var clone = jQuery(".original").clone(false);
            clone.removeClass('original');
            clone.addClass('cloneData');
            clone.find('.actionsClone').html('<label>&nbsp;</label> <a href="javascript:;" class="minus-clone"><span class="ui-icon ui-icon-minusthick removeCss cursor-pointer mt10"></span></a>');
            jQuery(".input_fields_wrap .row:last").after(clone);

        });
        
        $('body').on("click","a.minus-clone", function(e){ //user click on remove text
            e.preventDefault(); 
            jQuery(this).parent().parent('.cloneData').remove();
        })
    });   
</script>
@stop
