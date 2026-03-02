@extends('layouts.app')

@section('header_style')
    {!! CustomHelpers::combineCssJs(['css/dataTables.bootstrap', 'css/myaccount', 'css/cropper.min'],'css') !!}
@endsection

@section('header_script')
    var are_you_sure = "@lang('common.are_you_sure')";
    var txt_no = "@lang('common.txt_no')";
    var text_success = "@lang('common.success')";
    var text_confirm_paid_message = "@lang('common.confirm_paid_message')";
    var text_yes_send_it = "@lang('common.yes_reject_it')";
    var text_yes_reject_it = "@lang('common.yes_reject_it')";
    var text_yes_remove_it = "@lang('common.yes_remove_it')";
    var text_reject_message = "@lang('common.reject_message')";
    var text_yes_paid_it = "@lang('common.yes_paid_it')";  
    var text_ok_btn = "@lang('common.ok_btn')";  
    var want_to_delete_shop_from_fav_list_ = "@lang('user.want_to_delete_shop_from_fav_list_')";    
@stop

@section('breadcrumbs')

@stop

@section('content')
<h1 class="page-title">@lang('shop.favorit_shop')</h1>                   
<div class="favorite-shop">
    <div class="table-responsive">
        <div class="track-order-table">
            <table class="table table-bordered " id="table">
                    <thead>
                        <tr class="filters">
                            <th class="text-center">@lang('shop.favorit_shop_store')</th>
                            <th class="text-center">@lang('shop.favorit_shop_market')</th>
                            <th class="text-center">@lang('shop.favorit_shop_product_type')</th>
                            <th class="text-center">@lang('shop.favorit_shop_rating')</th>
                            <th class="text-center">@lang('shop.favorit_shop_update_price')</th>
                            <th class="text-center">&nbsp;</th>
                        </tr>
                    </thead>
                    <tbody>
                        @if(count($favoriteShopList)>0)
                            @foreach($favoriteShopList as $key => $shop)
                                <tr>
                                    <td>
                                        <div class="product-wrap">
                                            <div class="prod-img">
                                                <img src="{{$shop['logo']}}" width="50" height="50">                         
                                            </div>
                                            <div class="product-info">
                                                <div class="shop-name">
                                                    <a href="{{$shop['shop_url']}}">{{$shop['shop_name']}}</a>
                                                </div>                                                     
                                            </div>
                                        </div>
                                    </td>
                                    <td class="marketname text-center">{{ $shop['market']}}</td>
                                    <td class="product-name text-center">{{ $shop['shop_category']}}</td>
                                    <td class="chat-wrap">
                                        <div class="review-star">
                                            <div class="grey-stars"></div>
                                            <div class="filled-stars" style="width: {{$shop['avg_rating']*20}}%"></div>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="last-updatebox">                             
                                           <?php echo $shop['last_updated_price']; ?>
                                        </div>
                                    </td>
                                    <td>
                                        <a href="javascript://" class="delete-favorite-shop" data-del_url="{{$shop['del_f_shop_url']}}"><i class="fas fa-times">&nbsp;</i></a>
                                    </td>
                                </tr>
                            @endforeach
                        @endif
                    </tbody>
            </table>
        </div>
    </div>
</div>
                
@endsection

@section('footer_scripts')
    {!! CustomHelpers::combineCssJs(['js/jquery.dataTables','js/dataTables.bootstrap', 'js/user/myaccount', 'js/jquery-cropper.min', 'js/common_cropper_upload_setting'],'js') !!}   
    <!-- begining of page level js -->
    <script type="text/javascript">
        var lang = {
            zeroRecords : "@lang('data_table.zeroRecords')",
            emptyTable : "@lang('data_table.emptyTable')",
            search : "@lang('data_table.search')",
            display : "@lang('data_table.display')",
            entries : "@lang('data_table.entries')", 
            filtered_from : "@lang('data_table.filtered_from')",
            total_entries : "@lang('data_table.total_entries')",
            loadingRecords : "@lang('data_table.loadingRecords')",
            paginate_first : "@lang('data_table.paginate_first')",
            paginate_last : "@lang('data_table.paginate_last')",
            paginate_next : "@lang('data_table.paginate_next')",
            paginate_previous : "@lang('data_table.paginate_previous')",
            processing : "@lang('data_table.processing')",
            showing : "@lang('data_table.showing')",
            to : "@lang('data_table.to')",
            of : "@lang('data_table.of')",
        };
    </script>
    <script>
    $(document).ready(function() {
        $('#table').dataTable({
            ordering: false,
            lengthChange: false,
            responsive:true,
            info:false,
            "language": {
                "processing": lang.processing,
                "zeroRecords": lang.zeroRecords,
                "emptyTable": lang.emptyTable,
                "info": lang.showing + "_START_"+ lang.to +"_END_"+ lang.of +"_TOTAL_"+ lang.entries,
                "infoEmpty" : lang.showing+" 0 "+ lang.to + " 0 " + lang.of + " 0 "+ lang.entries,
                "search" : lang.search,
                "lengthMenu" : lang.display+" _MENU_ "+lang.entries,
                "infoFiltered" :   "("+lang.filtered_from+" _MAX_ "+lang.total_entries+")",
                "loadingRecords" : lang.loadingRecords,
                "paginate" : {
                    "first" : lang.paginate_first,
                    "last" : lang.paginate_last,
                    "next" : lang.paginate_next,
                    "previous" : lang.paginate_previous
                },
            },
            //bLengthChange: false,
            //searching: false
        });
    });
    </script>
    <!-- end of page level js -->
@endsection