@extends('layouts.app')

@section('header_style')
    {!! CustomHelpers::combineCssJs(['css/dataTables.bootstrap', 'css/myaccount'],'css') !!}    

<!--   {!! CustomHelpers::combineCssJs(['js/jquery.dataTables','js/dataTables.bootstrap', 'js/user/myaccount'],'js') !!} -->
    <!-- begining of page level js -->
   
@endsection

@section('header_script')
  var review_data = {};
@stop

@section('breadcrumbs')

@stop

@section('content')

<h1 class="page-title">@lang('user.user_reviews')</h1>                   
<div class="user-reviews">

    <table id="table" class="table" style="width:100%">
        <thead>
            <tr class="filters">
                <th class="text-center">@lang('order.order_number')</th>
                <th class="text-center">@lang('shop.shop_name')</th>
                <th class="text-center">@lang('product.product_name')</th>
                <th class="text-center">@lang('product.quantity')</th>
                <th class="text-center">@lang('product.rating')</th>
                <th class="text-center">@lang('product.review')</th>
                <th class="text-center">@lang('order.order_date')</th>
            </tr>
        </thead>
        
    </table>


    <div class="modal" id="reviewmodel">
        <div class="modal-dialog">
            <div class="modal-content">

              <!-- Modal Header -->
              <div class="modal-header">
                <h4 class="modal-title">Add Review</h4>
                <span class="close fa fa-times" data-dismiss="modal"></span>
              </div>

              <!-- Modal body -->
              <div class="modal-body">
              @include('includes.review_form')

              </div>

              <!-- Modal footer -->
              <!-- <div class="modal-footer">
                <button type="button" class="btn btn-danger" data-dismiss="modal">Close</button>
              </div> -->
            </div>
        </div>
    </div>
    
</div>
                
@endsection

@section('footer_scripts')
{!! CustomHelpers::combineCssJs(['js/jquery.dataTables','js/dataTables.bootstrap', 'js/user/myaccount'],'js') !!}  
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
    <script type="text/javascript">
        $(document).ready(function() {
            
            $('#table').DataTable({
                processing: true,
                serverSide: true,
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
                ajax:{
                        "url": "{{action('User\ReviewController@getOrderedProductList')}}",
                        "dataType": "json",
                        "type": "POST",
                        "data":{ _token: "{{csrf_token()}}"}
                       },
                columns: [
                    { data: "order_number",searchable:true,orderable:true},
                    { data: "shop_name",searchable:false,orderable:false},
                    { data: "product_name",searchable:false ,orderable:false},
                    { data: "quantity",searchable:false ,orderable:false },
                    { data: "rating",searchable:false ,orderable:false},
                    { data: "review",orderable:false ,orderable:false},
                    { data: "order_date", searchable:false ,orderable:true}
                ]    

            });
        });

        function setData(order_id,shop_id, product_id){
            review_data.order_id = order_id;
            review_data.shop_id = shop_id;
            review_data.product_id = product_id;
            review_data.page = 'user_order_review'; 
            $("input[name='product_rating']:checked").prop("checked", false);
        }
    </script>
@endsection