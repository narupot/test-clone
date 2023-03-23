        
@extends('layouts/admin/default')

@section('title')
    @lang('admin_payment.bank_list')
@stop

@section('header_styles')
    <!--page level css -->
    <link rel="stylesheet" href="{{ Config('constants.admin_css_url') }}dataTables.bootstrap.css"/>
    <!-- end of page level css -->
@stop

@section('content')
    <div class="content">
        <div class="header-title">
            <h1 class="title">@lang('admin_payment.bank_list')</h1>
             @if($permission_arr['add'] === true)
            <div class="float-right">
                <a class="btn btn-create" href="{{ action('Admin\Config\PaymentBankController@create') }}"> @lang('admin_common.create_new')</a> 
            </div>
            @endif
        </div>
            
        <!-- Main content -->         
           
        <div class="content-wrap">
            <div class="breadcrumb">
                <ul class="bredcrumb-menu">
                    {!!getBreadcrumbAdmin('config','bank','list')!!}
                </ul>
            </div>
            @if(Session::has('succMsg'))
                <div class="alert alert-success alert-dismissable margin5">
                    <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
                    <strong>@lang('admin_common.success'):</strong> {{ Session::get('succMsg') }}
                </div>
            @elseif(Session::has('errorMsg'))
                <div class="alert alert-danger alert-dismissable margin5">
                    <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
                    <strong>@lang('admin_common.error'):</strong> {{ Session::get('errorMsg') }}
                </div>    
            @endif
            <table class="table table-bordered " id="table">
                <thead>
                    <tr class="filters">
                        <th>@lang('admin_common.slno')</th>
                        <th>@lang('admin_payment.bank_name')</th>
                        <th>@lang('admin_payment.bank_image')</th>
                        <th>@lang('admin_common.created_at')</th>
                        <th>@lang('admin_common.updated_at')</th>
                        <th>@lang('admin_common.status')</th>
                        <th>@lang('admin_common.actions')</th>
                    </tr>
                </thead>
                <tbody>

                @foreach ($bank_list as $key => $bank_dtl)
                
                    <tr>
                        <td>{{ ++$key }}</td>
                        <td>{{ $bank_dtl->paymentBankName->bank_name }}</td>
                        <td>@if($bank_dtl->bank_image) <img style="max-width: 48px;max-height: 48px;border-radius: 8px;float: none;margin: 0 auto;" src="{{ Config::get('constants.payment_bank_url').$bank_dtl->bank_image }}"></a> @endif</td>
                        <td> {{ getDateFormat($bank_dtl->created_at, '1') }}</td>
                        <td>{{ getDateFormat($bank_dtl->updated_at, '1') }}</td>
                        <th>
                            <label class="button-switch mb-0" style="height: 1.5rem;">
                               <input type="checkbox" class="switch switch-orange" checked="checked" value="" name="status">
                               <span for="switch-orange" class="lbl-off">Off</span>
                               <span for="switch-orange" class="lbl-on">On</span>
                            </label>
                            <a id="status_{{ $bank_dtl->id }}" href="javascript:void(0);" onclick="callForAjax('{{ action('Admin\Config\PaymentBankController@changeBankStatus', $bank_dtl->id) }}', 'status_{{ $bank_dtl->id }}')" class="{{($bank_dtl->status == 1)?'status active':'status inactive'}}">
                        @if($bank_dtl->status == '0')
                            @lang('admin_common.inactive')
                        @else
                            @lang('admin_common.active')
                        @endif
                        </a></th>
                    
                        <td>
                        @if($permission_arr['edit'] === true)
                            <a class="btn btn-dark" href="{{ action('Admin\Config\PaymentBankController@edit', $bank_dtl->id) }}">
                               <i class="livicon" data-name="pen" data-loop="true" data-color="#000" data-hovercolor="black" data-size="14"></i>@lang('admin_common.edit')
                            </a>
                        @endif                                        
                        </td>
                    </tr>
                    
                 @endforeach 
                 
                 </tbody>
            </table>
        </div>
    </div>

@stop

@section('footer_scripts')

    <!-- begining of page level js -->

    <script src="{{ Config('constants.admin_js_url') }}jquery.dataTables.js"></script>
    <script src="{{ Config('constants.admin_js_url') }}dataTables.bootstrap.js"></script>
    <script>
    $(document).ready(function() {
        $('#table').dataTable();

        // jQuery('select.selectpicker').selectpicker();

    });
    </script>
    <!-- end of page level js -->
    
@stop
