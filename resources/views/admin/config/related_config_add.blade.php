@extends('layouts/admin/default')
@section('title')
    @lang('setting.setting')
@stop
@section('header_styles')
    <link rel="stylesheet" href="{{ Config('constants.css_url') }}sweetalert.css"/>
    <link rel="stylesheet" type="text/css" href="{{Config('constants.css_url') }}sweetalert2.min.css">
    <script src="{{ Config('constants.js_url') }}sweetalert2.min.js"></script>
    <script type="text/javascript">
        var tableLoaderImgUrl = "{{ Config::get('constants.loader_url')}}ajax-loader.gif";
        var SAVE_SETTING_URL = "{{action('Admin\Config\RelatedConfigController@store')}}";
        var RELATED_SETTING = {!! $related_product_setting !!};
        //for form field name 
       var formFieldName = ["price-from","price-to","price-less-more"];
       var errorMsg = ["Price range(from) is required", "Price range(to) is required", "Price range is required"];
    </script> 
@stop
@section('content')    

    <div class="content ng-cloak" ng-controller="relatedSettingCtrl" ng-cloak>
        <form name="relatedConfigForm" enctype='multipart/form-data'>
            <!--Overlay loader show on save or save and continue click -->
            <div class="loader-wrapper" ng-if="related_data.loading.save_and_continue">
              <span class="loader">
                <img ng-src="<%related_data.loading.btnloaderpath%>" alt="Loader" width="30" height="30"> 
                    <div>Please wait...</div>
              </span>
            </div>
            <!-- Setting section start here -->
            <div class="header-title">
                <h1 class="title">@lang('setting.related_product_config')</h1>
                <div class="float-right">
                    <button  name="submit_type" ng-click="saveSetting($event, relatedConfigForm, 'save')" value="save" class="btn btn-save" ng-disabled="related_data.loading.disableBtn">
                      @lang('common.save')
                    </button>
                </div>
            </div>

            <div class="content-wrap">
                <div class="breadcrumb">
                    <ul class="bredcrumb-menu">
                        {!!getBreadcrumbAdmin('product')!!}
                    </ul>
                </div>
                   <!--content will come over here -->
                   <div class="row">
                       <div class="col-sm-4">
                        <div class="border-wrap-full mb-15">
                           <div class="form-group">
                                <label>Enabled auto related products</label>
                                <select ng-model="related.enable">
                                    <option value="1">Yes</option>
                                    <option value="2">No</option>
                                </select>
                           </div>
                           <div class="form-group">
                                <label>Added Product</label>
                                <select ng-model="related.added_product">
                                    <option value="1">Replace manually added product</option>
                                    <option value="2">Append to manually added product to maximum related product</option>
                                </select>
                           </div>
                           <div class="form-group">
                                <label>Sort result by</label>
                                <select ng-model="related.sort" ng-options="opt.id as opt.label for opt in related_data.sort">
                                    <option value="" selected>--Please select--</option>
                                </select> 
                            </div>
                            </div>
                            <div class="form-group">
                                <h3 class="title">Condition</h3>
                            </div>
                            <div class="border-wrap-full mb-15">
                            <div class="form-group">
                                <label>Category condition</label>
                                <select ng-model="related.cat" ng-options="opt.id as opt.label for opt in related_data.categ">
                                    <option value="">--Please select--</option>
                                </select>
                            </div>
                            <div class="form-group">
                                <label>Price condition</label>
                                <select ng-model="related.price.selection" ng-change="priceCondition()" ng-options="opt.id as opt.label for opt in related_data.price.selection">
                                    <option value="">--Please select--</option>
                                </select>
                                <div ng-if="related_data.price.from_to">
                                    <label>&nbsp;</label>
                                    <div class="row" >
                                        <label class="col-sm-2 nopadding text-center">From :</label>
                                        <div class="col-sm-4">
                                           <input type="text" name="price-from" ng-model="related.price.from" onkeypress="return isNumberKey(event)"  required/>
                                        </div>
                                        <label class="col-sm-1 nopadding text-center">TO :</label>
                                        <div class="col-sm-5">
                                           <input type="text" name="price-to" ng-model="related.price.to" onkeypress="return isNumberKey(event)"  required/>
                                        </div>
                                    </div>                        
                                </div>
                                <div class="more-less" ng-if="related_data.price.single">
                                    <label>&nbsp;</label>
                                    <label class="col-sm-2 nopadding text-center">Price :</label>
                                    <div class="col-sm-4">
                                        <input type="text" name="price-less-more" ng-model="related.price.less_more" onkeypress="return isNumberKey(event)"  required/>
                                    </div>
                                </div>                        
                            </div>
                            </div>
                           @if(isset($last_updated_date))
                           <div class="form-group">
                                <strong>Last process Completed Time:  {{$last_updated_date}}</strong>
                           </div>
                       </div>
                   </div>
                   @endif
            </div>
            <!-- Setting section end here -->
        </form>        
    </div>

@stop

@section('footer_scripts')
    <script src="{{ Config('constants.js_url') }}angular.js"></script>
    <script src="{{ Config('constants.angular_app_url') }}services/service.js"></script>
    <script src="{{ Config('constants.angular_app_url') }}controller/related-config-setting.js"></script>
    <script src="{{asset('js/SweetAlert.min.js')}}"></script>
@stop
