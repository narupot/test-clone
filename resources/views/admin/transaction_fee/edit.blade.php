@extends('layouts/admin/default')

@section('title')
    Edit Transaction Fee Configuration
@stop

@section('header_styles')
    <!--page level css -->
    <link rel="stylesheet" href="{{ Config('constants.admin_css_url') }}bootstrap-datepicker.min.css"/>
    <!-- end of page level css -->
@stop

@section('content')
    <div class="content">
        <div class="header-title">
            <h1 class="title">Edit Transaction Fee Configuration</h1>
        </div>
        <div class="content-wrap">
            <div class="breadcrumb">
                <ul class="bredcrumb-menu">
                    {!!getBreadcrumbAdmin('config','transaction_fee','edit')!!}
                </ul>
            </div>
            <form action="{{ action('Admin\TransactionFee\TransactionFeeConfigController@update', $result->id) }}" method="POST">
                @csrf
                @method('PUT')
                <div class="form-group">
                    <label for="name">Name <span class="text-danger">*</span></label>
                    <input type="text" class="form-control" id="name" name="name" value="{{ old('name', $result->name) }}" required>
                    @if($errors->has('name'))
                        <span class="text-danger">{{ $errors->first('name') }}</span>
                    @endif
                </div>

                <div class="form-group">
                    <label for="message">Message</label>
                    <textarea class="form-control" id="message" name="message" rows="3">{{ old('message', $result->message) }}</textarea>
                    @if($errors->has('message'))
                        <span class="text-danger">{{ $errors->first('message') }}</span>
                    @endif
                </div>

                <div class="form-group">
                    <label for="tf">Transaction Fee (%) <span class="text-danger">*</span></label>
                    <input type="number" class="form-control" id="tf" name="tf" value="{{ old('tf', $result->tf) }}" step="0.01" min="0" max="100" required>
                    @if($errors->has('tf'))
                        <span class="text-danger">{{ $errors->first('tf') }}</span>
                    @endif
                </div>

                <div class="form-group">
                    <label for="effective_date">Effective Date <span class="text-danger">*</span></label>
                    <input type="text" class="form-control datepicker" id="effective_date" name="effective_date" value="{{ old('effective_date', $result->effective_date->format('Y-m-d')) }}" required>
                    @if($errors->has('effective_date'))
                        <span class="text-danger">{{ $errors->first('effective_date') }}</span>
                    @endif
                </div>

                <div class="form-group">
                    <button type="submit" class="btn btn-primary">Update</button>
                    <a href="{{ action('Admin\TransactionFee\TransactionFeeConfigController@index') }}" class="btn btn-default">Cancel</a>
                </div>
            </form>
        </div>
    </div>
@stop

@section('footer_scripts')
    <!-- begining of page level js -->
    <script src="{{ Config('constants.admin_js_url') }}bootstrap-datepicker.min.js"></script>
    <script>
    $(document).ready(function() {
        $('.datepicker').datepicker({
            format: 'yyyy-mm-dd',
            startDate: '+1d',
            autoclose: true
        });
    });
    </script>
    <!-- end of page level js -->
@stop 