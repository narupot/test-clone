@extends('layouts.app') 

@section('header_style')
    {!! CustomHelpers::combineCssJs(['css/myaccount','css/ui-grid-unstable'],'css') !!}
@endsection

@section('header_script')

    var fieldSetJson  = {!!$fielddata!!};
    var fieldset = fieldSetJson.fieldSets;
    var pagelimit = "{{action('JsonController@pageLimit')}}";
    var showSearchSection = false;
    var showHeadrePagination = false;
    var getAllDataFromServerOnce = true;
    var dataJsonUrl = "{{action('Seller\CustomerController@customerListData')}}";
    var tableLoaderImgUrl = "{{Config::get('constants.loader_url')}}ajax-loader.gif";
    var pagination = {!!getPagination()!!};
    var per_page_limt = "{{getPagination('limit')}}";
    var txt_no = "@lang('common.no')";
    var text_ok_btn = "@lang('common.ok_btn')";
    var text_success = "@lang('common.text_success')";
    var text_error = "@lang('common.text_error')";
    var text_yes_remove_it = "@lang('common.yes_remove_it')";
    
    //columns setting of table where field is field name of database filed.
    var columsSetting = [{ 
            field : 'logo',
            displayName : '@lang('customer.customer_name')',
            cellTooltip: true,
            cellTemplate: '<span class="product-img"><img src="<%row.entity.image%>" width="49" height="50"> <%row.entity.user_name%></span>',
            enableSorting : false,
            width : 280,
            cellClass : "text-align:'text-center'",
        },{ 
            field : 'desinated_val',
            displayName : '@lang('customer.desigated_name')',
            cellTooltip: true,
            cellTemplate: '<span class="d-block w-100" ng-if="row.entity.desinated_val"> <%row.entity.desinated_val%> <a href="javascript:;" class="link float-right" ng-click="grid.appScope.userActionHandler.custom($event, row.entity, rowRenderIndex)">@lang('common.edit')</a></span><span ng-if="!row.entity.desinated_val"><a href="javascript:;" class="link" ng-click="grid.appScope.userActionHandler.custom($event, row.entity, rowRenderIndex)">@lang("customer.create_name_for_buyer")</a></span>',
            enableSorting : false,
            enableSorting : false,
            width : 190,
            cellClass : "text-align:'text-center'",
        },{ 
            field : 'user_status',
            displayName : '@lang('customer.buyer_status')',
            cellTooltip: true,
            enableSorting : false,
            width : 105,
            cellClass : "text-align:'text-center'",
        }, { 
            field : 'user_email',
            displayName : '@lang('common.email')',
            cellTooltip: true,
            enableSorting : false,
            width : 220,
            cellClass : "text-align:'text-center'",
        },{ 
            field : 'ph_number',
            displayName : '@lang('customer.tel')',
            cellTooltip: true,
            enableSorting : false,
            width : 145,
            cellClass : "text-align:'text-center'",
        },{ 
            field : 'action',
            displayName : '@lang('common.action')',
            cellTooltip: true,
            cellTemplate: '<a href="<%row.entity.history_view_url%>" class="btn-grey">@lang('customer.view_history')</a> <a href="<%row.entity.manage_credit_url%>" ng-if="row.entity.manage_credit_url" class="btn-blue d-none"> @lang('customer.manage_credit')</a>',
            enableSorting : false,
            width : 230,
            cellClass : "text-align:'text-center'",
        }];
@endsection

@section('content')

<div class="ng-cloak">

    <div class="row">
        <div class="col-sm-12"> 
            <h1 class="page-title title-border">@lang('customer.manage_customer')</h1>
            <div class="tab-content">
                <div class="product-detail prod-review-tbl" ng-controller="gridtableCtrl">
                    @include('includes.gridtable')                           
                </div>
            </div>  
        </div>
    </div>  

</div>
          
@endsection 

@section('footer_scripts')
    @include('includes.gridtablejsdeps')
    <script stype="text/javascript" src="{{Config('constants.js_url')}}angular/smmApp/controller/gridTableCtrl.js"></script>

    <script type="text/javascript">
        var change_cust_name_url = "{{ action('Seller\CustomerController@changeCustName')}}";
        function changeNickName(evt,row,rowIndex,$scope){
            swal({
              title: "@lang('customer.please_enter_name')",
              input: 'text',
              inputAttributes: {
                autocapitalize: 'off'
              },
              showCancelButton: true,
              confirmButtonText: lang_ok,
              cancelButtonText: lang_cancel,
              showLoaderOnConfirm: true,
              allowOutsideClick: () => !Swal.isLoading()
            }).then(result => {   
                if(!result) return;                
                $.ajax({
                    url : change_cust_name_url,
                    method: 'post',
                    dataType: "json",
                    headers: {
                        'Content-Type': 'application/json',
                        // 'Content-Type': 'application/x-www-form-urlencoded',
                        _token:"{{ csrf_token() }}",
                        'X-CSRF-TOKEN':"{{ csrf_token() }}",
                    },
                    data: JSON.stringify({name : result, user_id : row.user_id }), 
                }).done(resp=>{ 
                    if(resp && resp.status === "success"){
                        $scope.$evalAsync(()=>{
                            row.desinated_val = result;
                        });
                        swal({
                            type: resp.status, 
                            title: text_success, 
                            text: "@lang('common.records_updated_successfully')",
                            confirmButtonText : text_ok_btn,
                        });
                        return;
                    }
                    swal('Opps..!', lang_oops, 'error');
                    
                }).fail(err=>{
                   swal('Opps..!', lang_oops, 'error');
                });             
            }, err=>{
                console.log;
            });
        }
    </script>
@stop