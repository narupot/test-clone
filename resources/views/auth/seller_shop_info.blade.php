@extends('layouts.app')

@section('header_style')
{!! CustomHelpers::combineCssJs(['css/myaccount'],'css') !!}
@endsection

@section('header_script')
var register_by = 'seller';
var seller_user_id = {{ $user_info->id }};
var url_checkStoreName = "{{ action('Auth\SellerRegisterController@checkStoreName') }}";
var url_checkStoreUrl = "{{ action('Auth\SellerRegisterController@checkStoreUrl') }}";
var url_checkPanel = "{{ action('Auth\SellerRegisterController@checkPanelNo') }}";
var url_checkCitizen = "{{ action('Auth\SellerRegisterController@checkCitizenId') }}";
@endsection

@section('breadcrumbs')

@stop

@section('content')
    @if(Session::has('verify_msg'))
    <div class="alert alert-success alert-dismissible">
        <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
        {{Session::get('verify_msg')}}
    </div>
    @else  

    <div class="content-wrap">
        <div class="container">
            <div class="register-step">
                @include('auth.register_step')

                <div class="row justify-content-md-center">
                    <div class="col-sm-12 col-md-8">                    
                        <form action="{{ action('Auth\SellerRegisterController@insertShopInfo') }}" method="post" id="shopInfoForm" enctype="multipart/form-data">    
                            {{ csrf_field() }}                                       
                            <div class="form-group">
                                <label>@lang('shop.panel_no')<i class="red">*</i></label>
                                <input type="text" name="panel_no" value="{{ $temp_data?$temp_data->panel_no:'' }}">
                                <p class="error" id="e_panel_no"></p>
                            </div>
                            <div class="form-group">
                                <label>@lang('shop.store_name')<i class="red">*</i></label>
                                <input type="text" name="store_name" value="{{ $temp_data?$temp_data->shop_name:'' }}">
                                <p class="error" id="e_store_name"></p>
                            </div>

                            <div class="form-group" style="display: none;">
                                <label>@lang('shop.store_url')<i class="red">*</i></label>
                                <input type="text" name="store_url" readonly="readonly" value="{{ $temp_data?$temp_data->shop_url:'' }}">
                                <p class="error" id="e_store_url"></p>
                            </div>
                            
                            <div class="form-group">
                                <label>@lang('shop.id_no').<i class="red">*</i></label>
                                <input type="text" name="citizen_id" value="{{ $temp_data?$temp_data->citizen_id:'' }}">
                                <p class="error" id="e_citizen_id"></p>
                            </div>
                            
                            <div class="form-group">
                                <label>@lang('common.attach') @lang('shop.citizen_image')</label>                     
                                <div class="file-wrapper">
                                    <div class="custom-img-file">
                                        <input type="file" name="citizen_id_image" accept="image/*" id="img-input">
                                        <span class="file-img btn-default"><img src="images/file-upload.png"></span>
                                        <p class="error" id="e_citizen_id_image"></p>
                                    </div> 
                                    <span class="image-preview"><img id="blah" src="" width="100" /></span>                                                  
                                </div>
                            </div>

                            <div class="form-group text-center">
                                <button type="button" id="btn_shop_info" class="btn">@lang('common.next')</button>
                            </div>
                            
                        </form>
                    </div>
                </div>
            </div>
        </div>      
    </div>  

    <script type="text/javascript">
    $(document).ready(function(){      

        //Active bank select
            function readURL(input) {
                if (input.files && input.files[0]) {
                    var reader = new FileReader();                        
                    reader.onload = function (e) {
                        $('#blah').attr('src', e.target.result);
                    }
                    
                    reader.readAsDataURL(input.files[0]);
                }
            }                
            $("#img-input").change(function(){
                readURL(this);
            });
    });
</script>
    
    @endif
@endsection
@section('footer_scripts')
{!! CustomHelpers::combineCssJs(['js/seller/seller_register'],'js') !!}
@stop