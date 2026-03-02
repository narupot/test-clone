@extends('layouts.admin.default')

@section('title')
    โครงสร้างหมวดสินค้า
@stop

@section('header_styles')
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/Sortable/1.15.0/sortable.min.css" />
<style>
    .content-wrap {
        background-color: #ffffff !important;  
        min-height: 100% !important;
    }
    .group-container {
        width: 80%;
        margin: 60px auto 0 auto;
    }  
    .header-title .title {
        font-size: 1.75rem;
        font-weight: bold;
        color: #374151;
        margin-bottom: 1rem;
    }
    .content-wrap {
        background: #fff;
        padding: 1.5rem;
        border-radius: 0.75rem;
        box-shadow: 0 4px 6px rgba(0,0,0,0.1);
    }
    .campaign-page .title {
        font-size: 24px !important;
        font-weight: bold !important;
        color: #374151 !important;
    }

    .campaign-page .btn-create {
        background-color: #3B82F6 !important;
        color: white !important;
        font-size: 14px !important;
        font-weight: 600 !important;
        border-radius: 8px !important;
        padding: 8px 16px !important;
        transition: background-color 0.2s ease-in-out !important;
    }

    .campaign-page .btn-create:hover {
        background-color: #2563EB !important;
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
    .btn-success-toggle {
        background-color: #28a745;
        color: white;
        padding: 2px 8px;
        border: none;
        border-radius: 999px !important;
    }
    .btn-secondary-toggle {
        background-color: #e61e1e;
        color: white;
        padding: 2px 8px;
        border: none;
        border-radius: 999px !important;
    }
    /* highlight row while dragging */
    .sortable-chosen {
        background: #f1f5f9 !important;
    }
    .sortable-ghost {
        opacity: 0.6;
    }
    .btn .fa {
        vertical-align: middle;
    }
</style>
@stop

@section('content')
<div class="group-container">
    
    <div class="header-title d-flex justify-content-between align-items-center mb-4">
        <h1 class="title">หมวดสินค้า</h1>
        @if(isset($permission_arr['add_sub_group']) && $permission_arr['add_sub_group'] === true)
            <a class="btn btn-success" href="{{ route('admin.product-sub-groups.create') }}">
                 <i class="fa fa-plus"></i> เพิ่มหมวดใหม่
            </a>
        @endif
    </div>
    <div class="content-wrap campaign-page">
        {{-- dropdown เลือก group --}}
        <div class="form-group mb-4" style="margin-top:60px">
            <label for="main_group_id"><strong>เลือกกลุ่ม:</strong></label>
            <select id="main_group_id" class="form-control">
                <option value="">-- กรุณาเลือกกลุ่ม --</option>
                @foreach($groups as $group)
                    <option value="{{ $group->id }}">{{ $group->name }}</option>
                @endforeach
            </select>
        </div>

        {{-- NEW: ช่องค้นหาหมวด --}}
        <div class="form-group mb-2">
            <label for="search_subgroup"><strong>ค้นหาหมวด:</strong></label>
            <input type="text" id="search_subgroup" class="form-control" placeholder="พิมพ์ชื่อหมวดเพื่อค้นหา..." disabled>
        </div>

        {{-- ตารางแสดง subgroup --}}
        <table class="table table-bordered table-striped" id="subgroup_table">
            <thead class="thead-dark">
                <tr style="text-align: center;">
                    <th>ลำดับ</th>
                    <th>ชื่อหมวดสินค้า</th>
                    <th>รูปภาพ</th>
                    <th>ลำดับ</th>
                    <th>สถานะ</th>
                    <th>อัปเดตโดย</th>
                    <th>วันที่สร้าง</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <tr><td colspan="8" class="text-center">กรุณาเลือกกลุ่มหลักเพื่อแสดงข้อมูลหมวด</td></tr>
            </tbody>
        </table>
    </div>
</div>
@stop

@section('footer_scripts')
<script src="https://cdnjs.cloudflare.com/ajax/libs/Sortable/1.15.0/Sortable.min.js"></script>
<script>
    // อ่านค่าจาก query string เช่น ?group_id=5
    function getQueryParam(param) {
        let urlParams = new URLSearchParams(window.location.search);
        return urlParams.get(param);
    }

    $(document).ready(function() {
        let preselectedGroupId = getQueryParam('group_id');
        if (preselectedGroupId) {
            $('#main_group_id').val(preselectedGroupId).trigger('change');
        }
    });

    // ----------------------------------------------------
    // ฟังก์ชันสำหรับแสดงวันที่แบบไทย (ใช้ใน JavaScript)
    // ----------------------------------------------------
    function formatThaiDate(dateString) {
        if (!dateString) return '-';
        try {
            // สร้าง Date Object จาก string
            const date = new Date(dateString);
            
            // ดึง วัน, เดือน, ปี (ค.ศ.)
            const day = date.getDate().toString().padStart(2, '0');
            const month = (date.getMonth() + 1).toString().padStart(2, '0'); // เดือนเริ่มจาก 0
            const yearCE = date.getFullYear();

            // คำนวณปี พ.ศ. (+543)
            const yearBE = yearCE + 543;

            return `${day}/${month}/${yearBE}`;
        } catch (e) {
            console.error("Error formatting date:", e);
            return dateString; // คืนค่าเดิมหากเกิดข้อผิดพลาด
        }
    }


    // โหลดข้อมูล subgroup เมื่อเลือก group
    $('#main_group_id').on('change', function() {
        var groupId = $(this).val();
        var table = $('#subgroup_table');
        var tbody = table.find('tbody');
        
        // Clear search box and disable it initially
        $('#search_subgroup').val('').prop('disabled', true);
        tbody.html('<tr><td colspan="8" class="text-center">กำลังโหลดข้อมูล...</td></tr>');

        if(groupId === '') {
            tbody.html('<tr><td colspan="8" class="text-center">กรุณาเลือกกลุ่มหลักเพื่อแสดงข้อมูลหมวด</td></tr>');
            table.show();
            return;
        }

        $.ajax({
            url: "{{ url('admin/product-sub-groups/by-group') }}/" + groupId,
            type: "GET",
            success: function(response) {
                if(response.success && response.data.length > 0) {
                    let rows = '';
                    response.data.forEach(function(item, index) {
                        const updatedDateThai = formatThaiDate(item.updated_date); // ใช้ฟังก์ชันแปลงวันที่
                        rows += `
                            <tr data-id="${item.id}">
                                <td>${index+1}</td>
                                <td>${item.subgroup_name}</td>
                                <td style="text-align: center;">
                                    ${item.images ? `<img src="${item.images}" width="50" height="50" style="object-fit:cover; border-radius:5px;">` : '-'}
                                </td>
                                <td class="sort-col" style="text-align: center;">${item.sorting_no}</td>
                                <td style="text-align: center;">
                                    <button class="btn btn-sm ${item.status ? 'btn-success-toggle' : 'btn-secondary-toggle'}" 
                                        onclick="toggleStatus('${item.id}', 'sub_group', this)">
                                        ${item.status ? 'ใช้งาน' : 'ไม่ใช้งาน'}
                                    </button>
                                </td>
                                <td>${item.updated_by ?? '-'}</td>
                                <td>${updatedDateThai}</td>
                                <td>
                                    <a href="/admin/product-sub-groups/edit/${item.id}" class="btn btn-info btn-sm">
                                        <i class="fa fa-edit"></i> แก้ไข
                                    </a>
                                </td>
                            </tr>
                        `;
                    });
                    tbody.html(rows);
                    table.show();
                    $('#search_subgroup').prop('disabled', false); // เปิดใช้งานช่องค้นหา
                    
                    // เปิด drag & drop หลังจาก render เสร็จ
                    new Sortable(tbody[0], {
                        animation: 150,
                        onEnd: function () {
                            updateOrder(tbody);
                        }
                    });
                } else {
                    tbody.html('<tr><td colspan="8" class="text-center">ไม่พบข้อมูลหมวดสินค้า</td></tr>');
                    table.show();
                    $('#search_subgroup').prop('disabled', true); // ปิดใช้งานช่องค้นหา
                }
            },
            error: function() {
                alert('เกิดข้อผิดพลาดในการโหลดข้อมูล');
                tbody.html('<tr><td colspan="8" class="text-center">เกิดข้อผิดพลาดในการโหลดข้อมูล</td></tr>');
            }
        });
    });

    // ----------------------------------------------------
    // ลอจิกสำหรับช่องค้นหา (Live Filter)
    // ----------------------------------------------------
    $('#search_subgroup').on('keyup', function() {
        var searchTerm = $(this).val().toLowerCase().trim();
        var tbody = $('#subgroup_table').find('tbody');
        let visibleRowCount = 0;
        
        // ซ่อนข้อความ "ไม่พบหมวดที่ตรงกับการค้นหา" ก่อนเริ่มค้นหาใหม่
        $('#no_search_result').remove(); 
        
        tbody.find('tr').each(function() {
            // ตรวจสอบเฉพาะแถวที่มี data-id (คือแถวข้อมูลจริง ไม่ใช่แถวสถานะ)
            if ($(this).data('id')) {
                // subgroup name อยู่ที่ <td> ลำดับที่ 2 (index 1)
                var subgroupName = $(this).children('td').eq(1).text().toLowerCase();
                
                if (subgroupName.includes(searchTerm)) {
                    $(this).show();
                    visibleRowCount++;
                } else {
                    $(this).hide();
                }
            }
        });

        // แสดงข้อความ "ไม่พบผลลัพธ์" หากไม่มีแถวใดแสดงผลเลย (และไม่ใช่กรณีที่ตารางว่างเปล่าแต่แรก)
        if (visibleRowCount === 0 && tbody.find('tr[data-id]').length > 0) {
            tbody.append('<tr id="no_search_result"><td colspan="8" class="text-center">ไม่พบหมวดสินค้า ที่ตรงกับการค้นหา</td></tr>');
        }
    });


    // ฟังก์ชันอัปเดตลำดับเมื่อมีการลาก
    function updateOrder(tbody) {
        let order = [];
        tbody.find('tr[data-id]').each(function(index) { // เลือกเฉพาะแถวที่มีข้อมูล
            let id = $(this).data('id');
            order.push({ id: id, sorting_no: index+1 });
            $(this).find('.sort-col').text(index+1);
        });

        $.ajax({
            url: "{{ url('admin/product-sub-groups/update-order') }}",
            method: "POST",
            data: {
                _token: "{{ csrf_token() }}",
                order: order
            },
            success: function(res) {
                console.log("อัปเดตลำดับเรียบร้อย", res);
            },
            error: function() {
                alert("อัปเดตลำดับไม่สำเร็จ");
            }
        });
    }
</script>
@stop
