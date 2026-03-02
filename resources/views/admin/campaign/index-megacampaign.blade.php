@extends('layouts/admin/default')

@section('title')
    {{-- @lang('admin_campaign.title') --}}
    MEGA Campaign
@stop

@section('header_styles')

    <!--page level css -->

    <link rel="stylesheet" href="{{ Config('constants.admin_css_url') }}dataTables.bootstrap.css"/>
    <!-- end of page level css -->
    
@stop

@section('content')
    <div class="content">

        <div class="header-title">
            <h1 class="title">
                {{-- @lang('admin_campaign.title') --}}MEGA Campaign
            </h1>
            @if($permission_arr['add'] === true)
                <div class="float-right">
                    <a class="btn btn-success mr-3" href="{{ route('admin.campaign.megaCampaign.create') }}">+ เพิ่ม MEGA Campaign</a> 
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


            <div class="tab-content listing-tab ">
                
                {{-- <div class="d-flex bg-white p-4">
                    <div class="dropdown">
                        <button class="btn btn-primary dropdown-toggle btn-sm" type="button" id="megaMenu" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                            + สร้าง Campaign <i class="fa fa-angle-down mt-1 ml-2"></i>
                        </button>
                        <div class="dropdown-menu" aria-labelledby="dropdownMenuButton">
                        @foreach ($megaCampaigns as $item)
                            <a class="dropdown-item" href="{{route('admin.campaign.subCampaign.create',['megaCampaignId'=>$item->id])}}">{{$item->name??''}} </a>
                        @endforeach
                        </div>
                    </div>
                </div> --}}

                <table class="table table-bordered " id="table">
                    <thead>
                        <tr class="filters">
                            <th width="30">#</th>
                            <th>ชื่อแคมเปญ</th>
                            <th class="text-center">แคมเปญ</th>
                            <th>วันที่</th>
                            <th>ภาพ</th>
                            <th>สถานะ</th>
                            <th width="70"></th>
                        </tr>
                    </thead>
                    <tbody>
                        @if($campaigns??false)
                            @foreach ($campaigns??[] as $key => $res)
                                <tr>
                                    <td>{{ ++$key }}</td>
                                    <td class="text-nowrap text-truncate">{{ $res->name }}</td>
                                    <td class="text-center">{{ $res->campaign->count() }}</td>
                                    <td class="align-middle text-nowrap small">
                                        <div>สร้าง : {{ $res->created_at ? \Carbon\Carbon::parse($res->created_at)->format('d/m/Y H:i') : '' }}</div>
                                        <div>แก้ไข : {{ $res->updated_at ? \Carbon\Carbon::parse($res->updated_at)->format('d/m/Y H:i') : '' }}</div>
                                    </td>
                                    <td>  @if ($res->image) <a href="{{ asset('files/campaign/' . $res->image) }}" target="_blank">ภาพแคมเปญ</a>@endif</td>
                                    <td>
                                        <a id="status_{{ $res->id }}" href="javascript:void(0);" 
                                            {{-- onclick="callForAjax('{{ action('Admin\Badge\BadgeController@changeStatus', $res->id) }}', 'status_{{ $res->id }}')"   --}}
                                            class="status badge {{($res->status == 1)?'badge-success ':' badge-danger '}}">
                                            @if($res->status)
                                                @lang('admin_common.active')
                                            @else
                                                @lang('admin_common.inactive')
                                            @endif
                                        </a>
                                    </td>
                                    <td style="d-flex ">
                                        <div class="d-flex justify-content-between">
                                            @if($permission_arr['edit'] === true)
                                                <a class="btn btn-dark btn-sm" href="{{ route('admin.campaign.megaCampaign.edit', ['campaign'=>$res->id]) }}">@lang('common.edit')</a>
                                            @endif

                                            @if($permission_arr['delete'] === true && $res->campaign->count() == 0) 
                                            <form method="post" action="{{ route('admin.campaign.megaCampaign.destroy', ['campaign'=>$res->id]) }}" 
                                                onsubmit="return confirm('@lang("admin_common.are_you_sure_to_delete_this_record")');" class="inblock"> 
                                                {{ csrf_field() }}{{ method_field('DELETE') }}   
                                                <a class="btn btn-delete btn-danger btn-sm" onclick="$(this).closest('form').submit();" data-toggle="modal">@lang('common.delete')</a>
                                            </form>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        @endif  
                    </tbody>
                </table>

            </div>
            
        </div>
    </div>
@stop

@section('footer_scripts')

    <!-- begining of page level js -->

    <script src="{{ Config('constants.admin_js_url') }}dataTables.bootstrap.js"></script>
    <script>
    $(document).ready(function() {
        $('#table').dataTable();
    });
    </script>
    <!-- end of page level js -->

@stop
