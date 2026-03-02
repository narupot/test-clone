@extends('layouts/admin/default')

@section('title')
    Transaction Fee Configuration List
@stop

@section('header_styles')
    <!--page level css -->
    <link rel="stylesheet" href="{{ Config('constants.admin_css_url') }}dataTables.bootstrap.css"/>
    <link href="{{ asset('assets/css/pages/tables.css') }}" rel="stylesheet" type="text/css" />
    <!-- end of page level css -->
    <script type="text/javascript">
        function _toastrMessage(status, message) {
            try {
                Command: toastr[status](message);
            }
            catch (err) {
                console.log;
            };
        };
    </script>
    <style>
        .dataTables_filter {
            float: right !important;
        }
        #saveAll {
            margin-bottom: 10px;
        }
    </style>
@stop

@section('content')
    <div class="content">

        <div class="header-title">
            <h1 class="title">Transaction Fee Configuration List</h1>
        </div>
        <div class="content-wrap">
            <div class="breadcrumb">
                <ul class="bredcrumb-menu">
                    {!!getBreadcrumbAdmin('config','transaction_fee','list')!!}
                </ul>
            </div>
            <form id="updateForm" method="POST" action="{{ url('admin/transaction-fee/bulk-update') }}">
                @csrf
                <div style="display: flex; justify-content: flex-end; align-items: center; margin-bottom: 5px;">
                    <button id="saveAll" class="btn btn-primary">Save All Changes</button>
                </div>
                <table class="table table-bordered" id="table">
                    <thead>
                        <tr class="filters">
                            <th>No.</th>
                            <th>Name</th>
                            <th>Message</th>
                            <th>Current TF (%)</th>
                            <th>Future TF (%)</th>
                            <th>Effective Date</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($results as $key => $result)
                            <tr>
                                <td>{{ ++$key }}</td>
                                <td>{{ $result->name }}</td>
                                <td>
                                    <input type="text" name="data[{{ $result->id }}][message]" value="{{ $result->message }}" class="form-control">
                                </td>
                                <td>{{ number_format($result->current_tf, 2) }}</td>
                                <td>
                                    <input type="number" step="0.01" min="0" max="100" name="data[{{ $result->id }}][tf]" value="{{ $result->tf }}" class="form-control">
                                </td>
                                <td>
                                    <input type="date" name="data[{{ $result->id }}][effective_date]" value="{{ $result->effective_date->format('Y-m-d') }}" class="form-control">
                                </td>
                            </tr>
                        @endforeach 
                    </tbody>
                </table>
            </form>
        </div>
    </div>
@stop

@section('footer_scripts')
    <!-- begining of page level js -->
    <script src="{{ Config('constants.admin_js_url') }}dataTables.bootstrap.js"></script>
    <script>
    $(document).ready(function() {
        var table = $('table.table').DataTable({
            dom: 'lfrtip', // remove the search box
            searching: false
        });

        $('#saveAll').click(function(e) {
            e.preventDefault();
            $('#updateForm').submit();
        });
    });
    </script>
    <!-- end of page level js -->
@stop