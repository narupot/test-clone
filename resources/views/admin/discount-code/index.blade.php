@extends('layouts/admin/default')

@section('title')
    @lang('admin_discount_code.title')
@stop

@section('header_styles')

    <!--page level css -->

    <link rel="stylesheet" href="{{ Config('constants.admin_css_url') }}dataTables.bootstrap.css"/>
    <!-- end of page level css -->
    <style>
        select{
            min-width: max-content;
        }
        .custom-select, select:not([multiple]){
            background-size: 8px 10px;
        }
    </style>
@stop

@section('content')
    <div class="content">

        <div class="header-title">
            <h1 class="title">
                @lang('admin_discount_code.title')
            </h1>
            @if($permission_arr['add'] === true)
                <div class="float-right">
                    <a class="btn btn-success btn-sm mr-3" href="{{ action('Admin\DiscountCode\DiscountCodeController@create') }}">+ สร้างโค้ดส่วนลด</a>
                </div>
            @endif
        </div>
               
        <!-- Main content -->
           
        <div class="content-wrap ">
            <div class="breadcrumb">
                <ul class="bredcrumb-menu">
                    {{-- {!!getBreadcrumbAdmin('product')!!} --}}
                </ul>
            </div>

            <div class="tab-content listing-tab">

                
                <div class="card shadow-sm p-3 p-lg-4 mb-4">
                    <form action="{{action("Admin\DiscountCode\DiscountCodeController@index")}}" method="GET">
                        @csrf
                        <div class="row">
                            <div class="form-group col-md-4">
                                <label for="filterMegacampaign">เมกาแคมเปญ</label>
                                <select name="mega_campaign_id" id="filterMegacampaign" class="form-control">
                                    <option value=""> -- ไม่ระบุ --</option>
                                    @foreach($megaCampaigns??[] as $key => $value)
                                        <option value="{{ $value['id'] }}"  {{request('mega_campaign_id')==$value['id']?'selected':''}} >
                                            {{ $value['name'] }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="form-group col-md-4">
                                <label for="filterCampaign">แคมเปญ</label>
                                <select name="campaign_id" id="filterCampaign" class="form-control">
                                    <option value=""> -- ไม่ระบุ --</option>
                                    @foreach($campaigns??[] as $key => $value)
                                        <option value="{{ $value['id'] }}" {{request('campaign_id')==$value['id']?'selected':''}}>
                                            {{ $value['name'] }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="form-group col-md-4">
                                <label for="searchText">ค้นหา</label>
                                <input type="text" name="searchText" id="searchText" class="form-control" placeholder="Search" />
                            </div>

                        </div>
                        <hr>
                        <div class="form-group row mb-3">
                            <div class="col-sm-4">
                                <label for="limit">แสดงผล</label>
                                <select name="limit" id="limit">
                                    <option value="10" >10</option>
                                    <option value="20" {{request('limit')==20?'selected':''}}>20</option>
                                    <option value="30" {{request('limit')==30?'selected':''}}>30</option>
                                    <option value="50" {{request('limit')==50?'selected':''}}>50</option>
                                    <option value="100" {{request('limit')==100?'selected':''}}>100</option>
                                </select>
                            </div>

                            <div class="form-group col-md-4">
                                <label for="searchText">เรียงตาม</label>
                                <select name="orderBy" id="orderBy">
                                    <option value="created_at" {{request('orderBy')=='created_at'?'selected':''}} >วันที่สร้าง</option>
                                    <option value="updated_at" {{request('orderBy')=='updated_at'?'selected':''}}>วันที่อัปเดต</option>
                                    <option value="code" {{request('orderBy')=='code'?'selected':''}}>โค้ด</option>
                                </select>
                            </div>
                            <div class="form-group col-md-4">
                                <label for="searchText">ประเภทการจัดเรียง</label>
                                <select name="sortType" id="sortType">
                                    <option value="desc" {{request('sortType')=='DESC'?'selected':''}} >หลัง-ก่อน</option>
                                    <option value="asc" {{request('sortType')=='ASC'?'selected':''}} >ก่อน-หลัง</option>
                                </select>
                            </div>
                        </div>
                        
                        <div class="row mb-3 ">
                            
                            <div class="col d-flex justify-content-end">
                                <button type="submit" class="btn btn-primary">ค้นหา</button>
                            </div>
                        </div>
                    </form>

                </div>
                <div class="table-responsive card p-3 p-lg-4 mb-4">
                    <table class="table table-bordered " id="table">
                        <thead>
                            <tr class="filters text-center">
                                <th>#</th>
                                <th>ชื่อ</th>
                                <th>ประเภท</th>
                                {{-- <th>เงื่อนไข</th> --}}
                                <th>จำนวนโค้ด</th>
                                <th>ใช้ได้/โค้ด</th>
                                <th>คงเหลือ</th>
                                <th>จำกัด/บัญชี</th>
                                <th>วันที่ใช้งาน</th>
                                <th>เมกาแคมเปญ</th>
                                <th>แคมเปญ</th>
                                <th>วันที่</th>
                                <th class="text-center">สถานะ</th>
                                <th class="text-center">ปิด/เปิด</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody class="small">
                            @foreach ($discountCodeCriteria??[] as $index => $res)
                                <tr>
                                    <td class="align-middle text-center">{{ $discountCodeCriteria->firstItem() + $index }}</td>
                                    <td class="align-middle text-nowrap text-truncate" style="max-width: 200px;">{{ $res->name??'' }}</td>
                                    <td class="align-middle text-center">{{ $res->discount_code_type??'' }}</td>
                                    {{-- <td>{{ $res->desc??'' }}</td> --}}
                                    <td class="align-middle text-center">{{ $res->discountCode()->count()??'' }}</td>
                                    <td class="align-middle text-center text-nowrap">{{ $res->is_limit? $res->quantity??'':'ไม่จำกัด' }}</td>
                                    <td class="align-middle text-center text-nowrap">{{ $res->is_limit? $res->discountCode()->sum('remaining_quantity')??'':'ไม่จำกัด' }}</td>
                                    <td class="align-middle text-center text-nowrap">{{ $res->limit_per_account??false && $res->limit_per_account > 0?$res->limit_per_account:'ไม่จำกัด' }}</td>
                                    <td class="align-middle text-nowrap">
                                        <div>เริ่มต้น : {{ $res->start_date??false ? \Carbon\Carbon::parse($res->start_date)->format('d/m/Y H:i') : '' }}</div>
                                        <div>สิ้นสุด : {{ $res->end_date??false ? \Carbon\Carbon::parse($res->end_date)->format('d/m/Y H:i') : '' }}</div>
                                    </td>
                                    <td class="align-middle ">{{ $res->campaign->megacampaign->name??'' }}</td>
                                    <td class="align-middle ">{{ $res->campaign->name??'' }}</td>
                                    <td class="align-middle text-nowrap">
                                        <div>สร้าง : {{ $res->created_at ? \Carbon\Carbon::parse($res->created_at)->format('d/m/Y H:i') : '' }}</div>
                                        <div>แก้ไข : {{ $res->updated_at ? \Carbon\Carbon::parse($res->updated_at)->format('d/m/Y H:i') : '' }}</div>
                                    </td>
                                    <td class="align-middle text-center"><span class="py-1 px-2 badge {{$res->derived_status['badge_class']??null}}">{{$res->derived_status['name_th']??null }} </span> </td>
                                    <td class="align-middle text-center">
                                        {{-- <a id="status_{{ $res->id }}" href="javascript:void(0);" onclick="callForAjax('{{ action('Admin\Badge\BadgeController@changeStatus', $res->id) }}', 'status_{{ $res->id }}')"  
                                            class="badge ">
                                            {{$res->status == 1?'เปิด':'ปิด'}}
                                        </a> --}}
                                        <select class="status-select custom-select h-auto btn-sm pr-4 {{($res->status == 1)?'badge-success':'badge-danger'}}" 
                                            style="font-size: smaller"  onchange="updateStatus(event,this)" data-current="{{$res->status}}" data-id={{$res->id}}>
                                            <option value="1" {{($res->status == '1')?'selected':''}}>Enable</option>
                                            <option value="0" {{($res->status == '0')?'selected':''}}>Disable</option>
                                        </select>
                                    </td>
                                    <td class="align-middle ">
                                        <div class="d-flex justify-content-between">
                                            @if($permission_arr['edit']??false === true)
                                                <a class="btn btn-dark btn-sm mr-1" href="{{ action('Admin\DiscountCode\DiscountCodeController@edit', $res->id) }}">@lang('common.edit')</a>
                                            @endif

                                            @if($permission_arr['delete']??false === true) 
                                            @if($res->start_date ? now()->lt(\Carbon\Carbon::parse($res->start_date)->subHours(2)):false)
                                            <form method="post" action="{{ action('Admin\DiscountCode\DiscountCodeController@destroy', $res->id) }}" onsubmit="return confirm('@lang("admin_common.are_you_sure_to_delete_this_record")');" class="inblock"> 
                                                {{ csrf_field() }}
                                                {{ method_field('DELETE') }}   
                                                <button type="submit" class="btn btn-delete btn-danger btn-sm" data-toggle="modal">@lang('common.delete')</button>
                                            </form>
                                            @endif
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                    
                    <div class="row">
                        <div class="col d-flex justify-content-between">
                            <div class="col d-flex align-items-center ">
                                <div>พบทั้งหมด <strong>{{ $discountCodeCriteria->total() }}</strong>  รายการ &nbsp;&nbsp;</div>
                                <div>จำนวนหน้าทั้งหมด <strong>{{ $discountCodeCriteria->lastPage() }}</strong> หน้า</div>
                            </div>
                            <div class="pagination">
                                <x-pagination :paginator="$discountCodeCriteria" />
                            </div>
                        </div>
                    </div>

                </div>

            </div>
        </div>
    </div>
@stop

@section('footer_scripts')

    <!-- begining of page level js -->

    <script src="{{ Config('constants.admin_js_url') }}dataTables.bootstrap.js"></script>
    <script>
    $(document).ready(function() {
        // $('#table').dataTable();
        // $.ajaxSetup({
        //     headers: {
               
        //     }
        // });
        
        // var table = $('#table').DataTable({
        //     processing: true,
        //     serverSide: true,
        //     ajax: {
        //         headers: { 'X-CSRF-TOKEN': window.Laravel.csrfToken},
        //         url: 'http://202.44.218.45/admin/discount-code/getdata',
        //         type: 'POST',
        //         data: function(d) {
        //             d.megacampaign = $('#filterMegacampaign').val();
        //             d.campaign = $('#filterCampaign').val();
        //             d.searchText = $('#searchText').val();
        //         }
        //     },
        //     columns: [
        //         { data: 'megacampaign' },
        //         { data: 'campaign' },
        //         { data: 'text' },
        //     ]
        // });

        // $('#filterMegacampaign, #filterCampaign').change(function() {
        //     table.ajax.reload();
        // });

        // $('#searchText').keyup(function() {
        //     table.ajax.reload();
        // });

    });

    
    const updateStatus = (e,el)=>{
        e.preventDefault();
        const previousValue = el.dataset.current;
        const newValue = el.value;
        const statusText = newValue === '1' ? 'Enable' : 'Disable';
        const id = $(el).data('id');

        Swal.fire({
            title: 'ยืนยันการเปลี่ยนสถานะ?',
            text: `คุณต้องการเปลี่ยนสถานะเป็น "${statusText}" ใช่หรือไม่?`,
            icon: 'question',
            showCancelButton: true,
            confirmButtonText: 'ใช่, ดำเนินการ',
            cancelButtonText: 'ยกเลิก'
        }).then((result) => {
            if (result.isConfirmed) {

                Swal.fire({
                    title: 'กำลังอัปเดต...',
                    allowOutsideClick: false,
                    didOpen: () => {
                        Swal.showLoading();
                    }
                });

                $.ajax({
                    url: "{{route('admin.discount_code.update-status')}}",
                    type: 'PUT',
                    headers: { 'X-CSRF-TOKEN': window.Laravel.csrfToken },
                    data: {  status: newValue, id: id },
                    success: function (response) {
                        
                        el.dataset.current = newValue;
                        Swal.fire({
                            icon: 'success',
                            title: 'สำเร็จ',
                            text: 'สถานะถูกอัปเดตแล้ว'
                        });
                        $(el).removeClass('badge-danger badge-success')
                        .addClass(newValue === '1' ? 'badge-success' : 'badge-danger')
                    },
                    error: function (xhr) {
                        el.value = previousValue;
                        Swal.fire({
                            icon: 'error',
                            title: 'เกิดข้อผิดพลาด',
                            text: 'ไม่สามารถอัปเดตสถานะได้'
                        });
                    }
                });
            } else {
                el.value = previousValue;
            }
        });
    };

    </script>
    <!-- end of page level js -->

@stop
