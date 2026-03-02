@extends('layouts.admin.default')

@section('title')
    จัดการข้อมูลกลุ่มสินค้า
@stop

@section('header_styles')
<link rel="stylesheet" href="{{ Config('constants.admin_css_url') }}dataTables.bootstrap.css"/>
<link rel="stylesheet" href="https://code.jquery.com/ui/1.13.2/themes/base/jquery-ui.css"/>
<style>
    .campaign-page .title {
        font-size: 24px !important;
        font-weight: bold !important;
        color: #374151 !important;
    }

    .campaign-page .btn-create {
        background-color: #28a745 !important;
        color: white !important;
        font-size: 14px !important;
        font-weight: 600 !important;
        border-radius: 8px !important;
        padding: 8px 16px !important;
        transition: background-color 0.2s ease-in-out !important;
    }

    .campaign-page .btn-create:hover {
        background-color: #28a745 !important;
    }

    .campaign-page table.table {
        font-size: 14px !important;
        color: #374151 !important;
    }

    .campaign-page table.table thead th {
        background-color: #F3F4F6 !important;
        color: #6B7280 !important;
        font-weight: 600 !important;
    }

    .campaign-page .sorting-input {
        width: 70px !important;
        text-align: center !important;
        padding: 5px 8px !important;
        border: 1px solid #d1d5db !important;
        border-radius: 6px !important;
        font-size: 14px !important;
    }

    .campaign-page .status-toggle-btn {
        padding: 6px 14px !important;
        font-size: 12px !important;
        font-weight: 600 !important;
        border-radius: 999px !important;
        border: none !important;
        color: white !important;
    }

    .campaign-page .btn-success-toggle {
        background-color: #22C55E !important;
    }

    .campaign-page .btn-secondary-toggle {
        background-color: #EF4444 !important;
    }

    .campaign-page .status-toggle-btn:hover {
        opacity: 0.85 !important;
    }

    .campaign-page table td,
    .campaign-page table th {
        vertical-align: middle !important;
    }

    .campaign-page .btn-sm {
        padding: 5px 10px !important;
        font-size: 13px !important;
        border-radius: 6px !important;
    }
    .ui-state-highlight {
    height: 50px;
    background: #E5E7EB;
    border: 2px dashed #9CA3AF;
    }
    .drag-handle {
        cursor: move;
        font-size: 18px;
        color: #6B7280;
    }
    .btn .fa {
        vertical-align: middle;
    }
</style>

@stop

@section('content')
<div class="content">
    <div class="header-title d-flex justify-content-between align-items-center mb-4">
        <h1 class="title">จัดการข้อมูลกลุ่มสินค้า</h1>
        @if(isset($permission_arr['add']) && $permission_arr['add'] === true)
            <div class="float-right">
                <a class="btn btn-success" href="{{ url('admin/product-groups/create') }}">
                    <i class="fa fa-plus"></i> เพิ่มกลุ่มใหม่
                </a>
            </div>
        @endif
    </div>

    {{-- Session Message --}}
    @if(Session::has('succMsg'))
        <div class="alert alert-success alert-dismissable margin5">
            <button type="button" class="close" data-dismiss="alert">×</button>
            {{ Session::get('succMsg') }}
        </div>
    @endif
    @if(Session::has('errorMsg'))
        <div class="alert alert-danger alert-dismissable margin5">
            <button type="button" class="close" data-dismiss="alert">×</button>
            {{ Session::get('errorMsg') }}
        </div>
    @endif

    <div class="content-wrap campaign-page">
        <div class="breadcrumb">
            <ul class="bredcrumb-menu">
                {!!getBreadcrumbAdmin('product_groups','group','list')!!}
            </ul>
        </div>
        <table class="table table-bordered table-striped" id="table">
            <thead>
                <tr class="filters">
                    <th>ลำดับ</th>
                    <th>ชื่อกลุ่ม</th>
                    <th>รูปภาพ</th>
                    <th>ลำดับการจัดเรียง</th>
                    <th style="text-align: center; vertical-align: middle;">สถานะ</th>
                    <th>สร้างโดย</th>
                    <th>วันที่สร้าง</th>
                    <th>วันที่อัปเดต</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody id="group-table-body">
                @if($groups->isEmpty())
                    <tr>
                        <td colspan="8" class="text-center">ยังไม่มีข้อมูลกลุ่มสินค้า</td>
                    </tr>
                @else
                    @foreach ($groups as $key => $group)
                        <tr id="group-row-{{ $group->id }}">
                            <td>{{ $key + 1 }}</td> {{-- S.No. --}}
                            <td>{{ $group->name }}</td>
                            <td>
                                @if($group->image)
                                    <img src="{{ asset($group->image) }}" alt="{{ $group->name }}" style="width: 50px; height: 50px; object-fit: contain;">
                                @else
                                    ไม่มีรูปภาพ
                                @endif
                            </td>
                            {{-- ลำดับการจัดเรียง (Sorting Order) --}}
                            <td>
                                <input
                                       class="sorting-input"

                                       style="width: 70px;"
                                       value="{{ $group->sorting_no }}"
                                       data-group-id="{{ $group->id }}"
                                       readonly>
                            </td>
                            {{-- สถานะ (Status) --}}
                            <td style="text-align: center; vertical-align: middle;">
                                <button type="button"
                                    id="status-toggle-{{ $group->id }}"
                                    data-status="{{ $group->status }}"
                                    class="status-toggle-btn {{ $group->status == 1 ? 'btn-success-toggle' : 'btn-secondary-toggle' }}"
                                    onclick="toggleStatus('{{ $group->id }}', this)">
                                    {{ $group->status == 1 ? 'ใช้งาน' : 'ไม่ใช้งาน' }}
                                </button>
                            </td>
                            <td>{{ optional($group->updater)->nick_name ?? '-' }}</td>
                            {{-- วันที่สร้าง (Created At) --}}

                            <td>{{ getDateFormat($group->created_at ?? $group->updated_date, 'Thai') }}</td>
                            <td>{{ getDateFormat($group->updated_date, 'Thai') }}</td>
                            {{-- Inside your @foreach ($groups as $key => $group) loop, in the Actions column --}}
                            <td>
                                @if(isset($permission_arr['edit']) && $permission_arr['edit'] === true)
                                    <a class="btn btn-sm btn-info" href="{{ action('Admin\Groups\GroupsController@edit', $group->id) }}">
                                        <i class="livicon" data-name="pen" data-loop="true" data-color="#fff" data-hovercolor="white" data-size="14"></i> แก้ไข
                                    </a>
                                @endif
                                
                            </td>
                        </tr>
                    @endforeach
                @endif
            </tbody>
        </table>
    </div>
</div>
@stop

@section('footer_scripts')
<script src="{{ Config('constants.admin_js_url') }}jquery.dataTables.js"></script>
<script src="{{ Config('constants.admin_js_url') }}dataTables.bootstrap.js"></script>
<script src="https://code.jquery.com/ui/1.13.2/jquery-ui.min.js"></script>
<script>


$(document).ready(function () {
    var table = $('#table').DataTable({
        "order": [[3, "asc"]],
        "columnDefs": [
            { "orderable": false, "targets": [2, 7] }
        ],
        "pageLength": -1, // ✅ แสดงทุกแถว
        "paging": false    // ✅ ปิดการแบ่งหน้า
    });

    $("#group-table-body").sortable({
        handle: "td",
        placeholder: "ui-state-highlight",
        update: function (event, ui) {
            let sortedIDs = $(this).sortable("toArray", { attribute: "id" });

            $.ajax({
                url: "{{ url('admin/product-groups/update-sort-order-bulk') }}",
                type: "POST",
                data: {
                    _token: '{{ csrf_token() }}',
                    sorted_ids: sortedIDs
                },
                success: function (response) {
                    if (response.status) {
                        $("#group-table-body tr").each(function (index) {
                            $(this).find("td:nth-child(4)").text(index + 1);
                        });

                        showSessionMessage('succMsg', response.message);
                    } else {
                        showSessionMessage('errorMsg', response.message || 'เกิดข้อผิดพลาดในการบันทึกลำดับ');
                    }
                },
                error: function (xhr) {
                    alert('Error: ' + xhr.responseText);
                }
            });
        }
    }).disableSelection();
});


function toggleStatus(groupId, buttonElement) {
    const currentStatus = parseInt(buttonElement.getAttribute('data-status'));
    const newStatus = currentStatus === 1 ? 0 : 1;

    buttonElement.setAttribute('data-status', newStatus);
    buttonElement.textContent = newStatus === 1 ? 'ใช้งาน' : 'ไม่ใช้งาน';
    $(buttonElement).toggleClass('btn-success-toggle btn-secondary-toggle');

    $.ajax({
        url: "{{ url('admin/product-groups/change-status') }}/" + groupId,
        type: 'POST',
        data: {
            _token: '{{ csrf_token() }}',
            status: newStatus
        },
        success: function(response) {
            // console.log(response);
            if (!response.status) {
                buttonElement.setAttribute('data-status', currentStatus);
                buttonElement.textContent = currentStatus === 1 ? 'ใช้งาน' : 'ไม่ใช้งาน';
                $(buttonElement).toggleClass('btn-success-toggle btn-secondary-toggle');

                showSessionMessage('errorMsg', response.message || 'เกิดข้อผิดพลาดในการเปลี่ยนสถานะ');
                // console.log("Error:", response.message);
            } else {
                reloadGroupTable();
                showSessionMessage('succMsg', response.message || 'อัปเดตเรียบร้อยแล้ว');
            }
        },
        error: function(xhr, status, error) {
            buttonElement.setAttribute('data-status', currentStatus);
            buttonElement.textContent = currentStatus === 1 ? 'ใช้งาน' : 'ไม่ใช้งาน';
            $(buttonElement).toggleClass('btn-success-toggle btn-secondary-toggle');

            console.error("AJAX Error:", error);
            showSessionMessage('errorMsg', 'มีการใช้งานกลุ่มสินค้านี้อยู่ ไม่สามารถเปลี่ยนสถานะได้');
        }
    });
}

function updateSortOrder(inputElement, groupId) {
    const newSortOrder = inputElement.value;

    $.ajax({
        url: "{{ url('admin/product-groups/update-sort-order') }}/" + groupId, 
        type: 'POST', 
        data: {
            _token: '{{ csrf_token() }}',
            sort_order: newSortOrder
        },
        success: function(response) {
            if (response.status) {
                showSessionMessage('succMsg', response.message);
            } else {

                inputElement.value = response.old_sort_order || inputElement.defaultValue;
                showSessionMessage('errorMsg', response.message || 'เกิดข้อผิดพลาดในการเปลี่ยนลำดับ');
            }
        },
        error: function(xhr, status, error) {
            console.error("AJAX Error:", error);
            inputElement.value = inputElement.defaultValue; 
            showSessionMessage('errorMsg', 'เกิดข้อผิดพลาดในการส่งคำขอ: ' + xhr.responseText);
        }
    });
}

function showSessionMessage(type, message) {
    const alertDiv = `<div class="alert alert-${type === 'succMsg' ? 'success' : 'danger'} alert-dismissable margin5">
                        <button type="button" class="close" data-dismiss="alert">×</button>
                        ${message}
                      </div>`;
    $('.content').prepend(alertDiv); 
    setTimeout(function() {
        $('.alert').alert('close');
    }, 5000);
}

function reloadGroupTable() {
    $("#table tbody").load(location.href + " #table tbody>*", "");
}
</script>
@stop