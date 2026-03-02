@extends('layouts/admin/default')
@php
    $title_page ='Discount code Report';
    
@endphp
@section('title')
    {{$title_page}}
@stop

@section('header_styles')
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

    <style>
        .select2-container--default .select2-search--inline .select2-search__field{
            min-height: auto;
        }
        .select2-selection__choice{
            color: dimgray;
            font-size: small;
            border: none !important;
        }
        .select2-container--default .select2-selection--multiple .select2-selection__choice__remove{
            border: 0px;
            margin-top: 1px;
        }
        .select2-container--default .select2-selection--multiple .select2-selection__choice{
            padding-left: 15px;
        }
        .select2-container--default .select2-selection--multiple .select2-selection__clear{
            margin-top: 3px;
        }
    </style>

@stop

@section('content')
    <div class="content">
           
        <div class="content-wrap ">
            <div class="breadcrumb">
                <ul class="bredcrumb-menu">
                    {{-- {!!getBreadcrumbAdmin('product')!!} --}}
                </ul>
            </div>

            <div class="tab-content listing-tab">
                
                <div class="card shadow-sm p-3 p-lg-4 mb-4">
                    <form action="{{action("Admin\DiscountCode\DiscountCodeController@report")}}" method="GET">
                        @csrf
                        <div class="row">
                            <div class="form-group  col-sm-4">
                                <label for="filterCampaign">แคมเปญ</label>
                                <select name="campaign_id[]" id="filterCampaign" class="form-control" multiple="multiple">
                                    @foreach($data['campaigns']??[] as $key => $value)
                                        <option value="{{ $value['id'] }}" {{ in_array($value['id'], (array)request('campaign_id')) ? 'selected' : '' }}>
                                            {{ $value['name'] }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="form-group col-sm-4">
                                <label for="filterStatus">สถานะ</label>
                                <select name="status_id[]" id="filterStatus" class="form-control" multiple="multiple">
                                    @foreach($data['status']??[] as $key => $value)
                                        <option value="{{ $key }}" {{ in_array($key, (array)request('status_id')) ? 'selected' : '' }}>
                                            {{ $value }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="row">
                            <div class="form-group col-sm-4 col-md-4">
                                <label for="el_start_date">@lang('admin_discount_code.start_date') <i class="strick">*</i></label>
                                <input type="text" id="el_start_date" name="start_date" autocomplete="off" class=" date-picker flatpickr"
                                    value="{{ request('start_date') ? \Carbon\Carbon::parse(request('start_date'))->format('Y-m-d') : '' }}"
                                >
                                <p class="error" id="start_date"></p>
                            </div>
                            <div class="form-group col-sm-4 col-md-4">
                                <label for="el_end_date">@lang('admin_discount_code.end_date') <i class="strick">*</i></label>
                                <input type="text" id="el_end_date" name="end_date" autocomplete="off" class=" date-picker flatpickr"
                                    value="{{ request('end_date') ? \Carbon\Carbon::parse(request('end_date'))->format('Y-m-d') : '' }}"
                                >
                                <p class="error" id="end_date"></p>
                            </div>
                        </div>
                        
                        <div>
                            <button type="button" class="btn btn-primary" id="btn-clear-dates">ล้างวันที่</button>
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

                        </div>
                        
                        <div class="row mb-3 ">
                            
                            <div class="col d-flex justify-content-between">
                                <input type="hidden" name="action_type" id="action_type" value="search">
                                <button type="submit" class="btn btn-primary" id="btnExport">Export</button>
                                <button type="submit" class="btn btn-primary" id="btnSearch">ค้นหา</button>
                            </div>
                        </div>
                    </form>

                </div>
                <div class="table-responsive card p-3 p-lg-4 mb-4">
                    <table class="table table-bordered " id="table">
                        <thead>
                            <tr class="filters text-center">
                                <th>#</th>
                                <th>วันที่สร้างโค้ดส่วนลด</th>
                                <th>ชื่อโค้ดส่วนลด (Discount Code)</th>
                                <th>ชื่อแคมเปญ (ถ้ามี)</th>
                                <th>ประเภทโค้ด</th>
                                <th>เงื่อนไขโค้ด</th>
                                <th>จำนวนโค้ดทั้งหมด</th>
                                <th>จำนวนโค้ดที่ถูกใช้</th>
                                <th>ระยะเวลาของโค้ด</th>
                                <th>วันเริ่มต้น - วันหมดอายุของโค้ด</th>
                                <th>ยอดขั้นต่ำที่กำหนด</th>
                                <th>Order ID</th>
                                <th>ยอดสั่งซื้อรวม</th>
                                <th>ค่าขนส่ง</th>
                                <th>ส่วนลดค่าสินค้า</th>
                                <th>ส่วนลดค่าขนส่ง</th>
                                <th>ยอดหลังหักส่วนลด</th>
                                <th>ชื่อลูกค้า</th>
                                <th>วันที่ใช้โค้ด</th>
                                <th>สถานะคำสั่งซื้อ</th>
                            </tr>
                        </thead>
                        <tbody class="small">
                            @if ($data['orderDcc'] && $data['orderDcc']->count() > 0)
                                
                                @foreach ($data['orderDcc'] ?? [] as $index => $orDcc)
                                    @php
                                        $orders = $orDcc->order ?? [];
                                        $orderCount = $orders ? $orDcc->discountCode->order->count() : 0;
                                        $criteriaCreatedDate = $orDcc->criteria->created_at ? \Carbon\Carbon::parse($orDcc->criteria->created_at)->format('d/m/Y H:i') : null;
                                        $start = $orDcc->criteria->start_date ? \Carbon\Carbon::parse($orDcc->criteria->start_date) : null;
                                        $end = $orDcc->criteria->end_date ? \Carbon\Carbon::parse($orDcc->criteria->end_date) : null;
                                        $diffText = $start && $end ? $start->diff($end)->days . ' วัน ' . $start->diff($end)->h . ' ชั่วโมง ' . $start->diff($end)->i . ' นาที' : '';
                                    @endphp

                                    {{-- @forelse ($orders as $i => $order) --}}
                                        <tr>
                                            {{-- @if ($i === 0) --}}
                                                <td class="align-middle text-nowrap">{{ $data['orderDcc']->firstItem() + $index }}</td>
                                                <td class="align-middle text-nowrap">{{ $criteriaCreatedDate }}</td>
                                                <td class="align-middle text-nowrap">{{ $orDcc->discount_code ?? '' }}</td>
                                                <td class="align-middle text-nowrap text-left">{{ $orDcc->criteria->campaign->name ?? '' }}</td>
                                                <td class="align-middle">{{ $orDcc->criteria->discount_code_type ?? '' }}</td>
                                                <td class="align-middle">{{ $orDcc->criteria->desc ?? '' }}</td>
                                                <td class="align-middle text-center text-nowrap">{{ $orDcc->criteria->is_limit ? $orDcc->criteria->quantity : 'ไม่จำกัด' }}</td>
                                                <td class="align-middle text-nowrap">{{ $orderCount }}</td>
                                                <td class="align-middle text-nowrap">{{ $diffText }}</td>
                                                <td class="align-middle text-nowrap">{{ $start?$start->format('d/m/Y H:i'):null }} - {{ $end?$end->format('d/m/Y H:i'):null }}</td>
                                                <td class="align-middle text-right">{{number_format($orDcc->criteria->purchase_amount_threshold ?? 0, 2)}}</td>
                                            {{-- @endif --}}
                                            {{-- แสดงข้อมูล order --}}
                                            <td class="align-middle">{{$orDcc->order->formatted_id ??''}}</td>
                                            <td class="align-middle text-right">{{number_format($orDcc->order->total_core_cost ?? 0, 2)}}</td>
                                            <td class="align-middle text-right">{{number_format($orDcc->order->total_shipping_cost ?? 0, 2)}}</td>
                                            <td class="align-middle text-right">{{number_format($orDcc->order->dcc_purchase_discount ?? 0, 2)}}</td>
                                            <td class="align-middle text-right">{{number_format($orDcc->order->dcc_shipping_discount ?? 0, 2)}}</td>
                                            <td class="align-middle text-right">{{number_format($orDcc->order->total_final_price ?? 0, 2)}}</td>
                                            <td class="align-middle text-nowrap">{{$orDcc->order->getUser->display_name??''}}</td>
                                            <td class="align-middle text-nowrap">{{$orDcc->order->created_at?\Carbon\Carbon::parse($orDcc->order->created_at)->format('d/m/Y H:i'):''}}</td>
                                            <td class="align-middle text-nowrap">{{$orDcc->status_text??''}}</td>
                                        </tr>
                                    {{-- @empty --}}
                                        {{-- <tr>
                                            <td class="align-middle text-nowrap">{{ $criteriaCreatedDate }}</td>
                                            <td class="align-middle text-nowrap">{{ $dcc->code ?? '' }}</td>
                                            <td class="align-middle text-left">{{ $dcc->criteria->name ?? '' }}</td>
                                            <td class="align-middle">{{ $dcc->criteria->discount_code_type ?? '' }}</td>
                                            <td class="align-middle">{{ $dcc->criteria->desc ?? '' }}</td>
                                            <td class="align-middle text-center text-nowrap">{{ $dcc->criteria->is_limit ? $dcc->criteria->quantity : 'ไม่จำกัด' }}</td>
                                            <td class="align-middle text-nowrap">0</td>
                                            <td class="align-middle text-nowrap">{{ $diffText }}</td>
                                            <td class="align-middle text-nowrap">{{ $start?$start->format('d/m/Y H:i'):null }} - {{ $end?$end->format('d/m/Y H:i'):null }}</td>
                                            <td class="align-middle">{{ $dcc->criteria->purchase_amount_threshold ?? '' }}</td>
                                            <td colspan="7" class="text-center">ไม่มีข้อมูลคำสั่งซื้อ</td>
                                        </tr> --}}
                                    {{-- @endforelse --}}
                                @endforeach
                                
                            @else
                                <tr>
                                    <td colspan="20" class="text-center">ไม่พบข้อมูล</td>
                                </tr>
                            @endif
                        </tbody>
                    </table>
                    
                    <div class="row">
                        <div class="col d-flex justify-content-between">
                            <div class="col d-flex align-items-center ">
                                <div>ทั้งหมด <strong>{{ $data['orderDcc']->total()??0 }}</strong>  รายการ &nbsp;&nbsp;</div>
                                <div>หน้าทั้งหมด <strong>{{ $data['orderDcc']->lastPage()??0 }}</strong> หน้า</div>
                            </div>
                            <div class="pagination">
                                @if ($data['orderDcc'])
                                    <x-pagination :paginator="$data['orderDcc']" />
                                @endif
                            </div>
                        </div>
                    </div>

                </div>

            </div>
        </div>
    </div>
@stop

@section('footer_scripts')
    <script src="{{ Config('constants.js_url') }}flatpickr.min.js"></script>
    
    <script>
        
        let $el_startDate = $('#el_start_date');
        let $el_endDate = $('#el_end_date');

        startDatePicker = flatpickr($el_startDate.get(0), {
            dateFormat: "Y-m-d",
            time_24hr: true,
            allowInput: true,
            onChange: function(selectedDates, dateStr, instance) {
                if (!dateStr) {
                    this.input.value = '';
                    $(this.input).trigger('change');
                    if (endDatePicker) endDatePicker.set('minDate', null);
                    return;
                }

                this.input.value = dateStr;
                $(this.input).trigger('change');

                if (endDatePicker) {
                    endDatePicker.set('minDate', dateStr);
                }
            }
        });

        endDatePicker = flatpickr($el_endDate.get(0), {
            dateFormat: "Y-m-d",
            time_24hr: true,
            allowInput: true,
            onChange: function(selectedDates, dateStr) {
                if (!dateStr) {
                    this.input.value = '';
                    $(this.input).trigger('change');
                    return;
                }

                this.input.value = dateStr;
                $(this.input).trigger('change');

                let startDate = startDatePicker.selectedDates[0];
                let endDate = selectedDates[0];

                if (startDate && endDate && endDate < startDate) {
                    alert("วันสิ้นสุดต้องมากกว่าวันเริ่มต้น");
                    endDatePicker.clear();
                }
            }
        });

        // Clear dates button
        // $('#btn-clear-dates').on('click', function () {
        //     startDatePicker.clear();
        //     endDatePicker.clear();
        //     endDatePicker.set('minDate', null);
        // });

    $(document).ready(function() {
        
        $('#filterCampaign,#filterStatus').select2({
            // allowClear: true
        });

        $('#btnExport').click(function (e) {
            $('#action_type').val('export');
        });
        $('#btnSearch').click(function (e) {
            $('#action_type').val('search');
        });
        $('#btn-clear-dates').on('click', function () {
            startDatePicker.clear();
            endDatePicker.clear();
            endDatePicker.set('minDate', null);
        });

    });
    
    </script>

@stop
