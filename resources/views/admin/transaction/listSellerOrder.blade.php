@extends('layouts/admin/default')

@section('title')
    @lang('admin_order.shop_order_list')
@stop

@section('header_styles')
    <link rel="stylesheet" type="text/css" href="{{Config('constants.css_url') }}flatpickr.min.css">
    <style type="text/css">
        .form-controlselect {
            min-width: 100px;
        }
        .topFilter .form-control, .topFilter .form-controlselect {
            min-width: 150px;
            max-width: 150px;
        }
    </style>
   {!! CustomHelpers::dataTableCss() !!}
    <script type="text/javascript">
        var filter_data = {!! $filter !!};    
    </script>
    
@stop

@section('content')
    <div class="content">
        @if(Session::has('succMsg'))    
        <script type="text/javascript">               
            _toastrMessage('success', "{{ Session::get('succMsg') }}");    
        </script>                              
        @endif
        @if(Session::has('errorMsg'))
        <script type="text/javascript">               
            _toastrMessage('error', "{{ Session::get('errorMsg') }}");    
        </script>    
        @endif 
        <div class="header-title">
            <h1 class="title">@lang('admin_order.shop_order_list')</h1>
            @if($filter_date)
            <div class="float-right">
                <button class="btn btn-primary generate_txt" data-val="without_enc" id="without_enc">Without Encrypt</button>
                <a href="javascript:;" class="btn btn-primary" data-toggle="modal" data-target="#importModal">@lang('admin_order.import_file')</a>
                <button class="btn btn-primary generate_txt" data-val="with_enc" id="generate_txt">@lang('admin_order.generate_txt')</button>
            </div>
            @endif
        </div>
             
        <!-- Main content -->         
           
        <div class="content-wrap">
            <div class="breadcrumb">
                <ul class="bredcrumb-menu">
                    {!!getBreadcrumbAdmin('shoporder', 'shoporder', 'list')!!}
                </ul>
            </div>         
            <form action="{{action('Admin\Transaction\ShopOrderController@sellerOrder')}}" method="GET" enctype="multipart/form-data">
                <!-- <div class="row align-items-center topFilter">
                      <div class="col-auto form-group">
                        <input type="text" class="form-control date-select-new date-picker" name=""/>
                      </div>

                      <div class="col-auto form-group">
                        <label class="col-form-label">Shop Name</label>
                      </div>
                      <div class="col-auto form-group">
                        <input type="text" class="form-control" name="">
                      </div>
                      <div class="col-auto form-group">
                        <label class="col-form-label">Shop Owner Name</label>
                      </div>
                      <div class="col-auto form-group">
                        <input type="text" class="form-control" name="">
                      </div>
                      <div class="col-auto form-group">
                        <label class="col-form-label">Loggen text file</label>
                      </div>
                      <div class="col-auto form-group">
                            <input type="text" class="form-control date-select-new date-picker" name=""/>
                      </div>
                      <div class="col-auto form-group">
                        <label class="col-form-label">Bank Name</label>
                      </div>
                      <div class="col-auto form-group">
                        <select class="form-controlselect">
                            <option>kBank</option>
                        </select>
                      </div>
                </div> -->

                <div class="row">
                    <div class="col-sm-4 form-group">
                        <label>@lang('admin_order.date') <i class="red">*</i></label>
                        <input type="text" class="date-select-new date-picker" name="filter_date" id="order_date" value="{{$filter_date}}">
                        
                    </div>
                    <div class="col-sm-1 form-group">
                        <label>&nbsp;</label>
                       <button class="btn btn-primary" value="refresh" name="refresh">@lang('admin_common.submit')</button>
                    </div>
                    <div class="col-sm-1  form-group">
                        <label>&nbsp;</label>
                        <a class="btn btn-danger" href="{{Request::url()}}">@lang('admin_common.clear_all')</a>
                    </div>
                    @if(!empty($_GET['zip_file']))
                        <div class="col-sm-1  form-group">
                            <label>&nbsp;</label>
                            <a class="btn btn-primary" href="{{Config::get('constants.public_url').'/seller-payment/'.$_GET['zip_file']}}">@lang('admin_common.download')</a>
                        </div>
                    @endif
                </div>
                
            </form>
            <input type="hidden" name="brand_product" id="brand_product">
           <div id="jq_grid_table" class="table table-bordered">                 
                

            </div>
        </div>

        <div class="modal fade" id="importModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
          <div class="modal-dialog" role="document">
            <div class="modal-content">
                <form method="post" action="{{action('Admin\Transaction\ExportOrderController@importTxt')}}" enctype="multipart/form-data">
                    {{csrf_field()}}
                    <input type="hidden" name="filter_date" value="{{$filter_date}}">
                    <div class="modal-header">
                    <h5 class="modal-title" id="exampleModalLabel">@lang('admin_order.import_file')</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                      <span aria-hidden="true">&times;</span>
                    </button>
                  </div>
                  <div class="modal-body">
                    <input type="file" name="import_file" required="required">
                  </div>
                  <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-primary">@lang('admin_common.submit')</button>
                  </div>
                </form>
              
            </div>
          </div>
        </div>

        <div class="modal" id="generatedFileModal" tabindex="-1" role="dialog">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                    <h5 class="modal-title">@lang('admin_order.generated_file')</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                      <span aria-hidden="true">&times;</span>
                    </button>
                    </div>
                    <div class="modal-body" id="file_modal_body">
                    
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                    
                  </div>
                </div>
            </div>
        </div>

    </div>
@stop

@section('footer_scripts')
    <script src="{{ Config('constants.admin_js_url') }}flatpickr.min.js"></script>
    <!-- begining of page level js -->
    <script>
        var generate_txt_url  = "{{action('Admin\Transaction\ExportOrderController@generateTxt')}}";
        var log_date_url = "{{action('Admin\Transaction\ShopOrderController@getGeneratedLog')}}";
        var csrftoken = window.Laravel.csrfToken;
           $(document).ready(function() {
            $(".date-picker").flatpickr({});

            $('.generate_txt').click(function(e){
                var order_date = $('#order_date').val();
                var shop_ids = $('#brand_product').val();
                var eny_type = $(this).data('val');
                if(!order_date){
                    alert('Please select date');
                    return false;
                }
                if(!shop_ids){
                    alert('Please select shop');
                    return false;
                }
                window.location.href = generate_txt_url+"?order_date="+order_date+"&shop_ids="+shop_ids+"&eny_type="+eny_type;
            });
        });
    </script>
    {!! CustomHelpers::dataTableJs() !!}
    <!-- end grid table js files -->  
    <script>
        let JQ_GRID_DATA_URL = "{{ action('Admin\Transaction\ShopOrderController@listSellerOrderData') }}?filter_date={{$filter_date}}";     
        
        const JQ_GRID_TITLE = "@lang('admin_order.shop_order_list')";    
        /*
        *@desc : Table column configrations
            Array of column 
        */
        let columnModel = [  
            /* check for row selection ***/
            {   title: "", 
                width: 50, 
                dataType: "integer",
                type:'checkbox', 
                cbId: 'state',
                sortable : false,
                align : 'center',
            },
            { 
                dataIndx: 'state', 
                editable: true,
                cb: {header: true, select: true, all: true}, 
                dataType: 'bool',
                hidden: true
            },
            /**** end selection *******/         
            
            {   title: "@lang('admin_order.shop_name')", 
                dataIndx:'shop_name', 
                minWidth: 140,
                filter : {
                    attr : "@lang('admin_order.enter_name')",                        
                    crules: [
                        {
                            condition: getFilter('shop_name', 'condition') ||  'contain',
                            value : getFilter('shop_name', 'value')  || "",
                        }
                    ],
                    type: 'textbox', 
                    listeners: ['change'],
                },
            },
            {   title: "@lang('admin_order.seller_name')", 
                dataIndx:'seller_name', 
                minWidth: 140,
                filter : {
                    attr : "@lang('admin_order.enter_name')",                        
                    crules: [
                        {
                            condition: getFilter('seller_name', 'condition') ||  'contain',
                            value : getFilter('seller_name', 'value')  || "",
                        }
                    ],
                    type: 'textbox', 
                    listeners: ['change'],
                },
            },
            {   title: "@lang('admin_order.seller_ph_number')", 
                dataIndx:'seller_ph_number', 
                minWidth: 140,
                filter : {
                    attr : "@lang('admin_order.enter_name')",                        
                    crules: [
                        {
                            condition: getFilter('seller_ph_number', 'condition') ||  'contain',
                            value : getFilter('seller_ph_number', 'value')  || "",
                        }
                    ],
                    type: 'textbox', 
                    listeners: ['change'],
                },
            },
            {   title: "@lang('admin_order.panel_no')", 
                dataIndx:'panel_no', 
                minWidth: 140,
            },
            {   title: "@lang('admin_order.bank_name')", 
                dataIndx:'bank_name', 
                minWidth: 140,
                filter : {
                    attr : "@lang('admin_order.enter_name')",                        
                    crules: [
                        {
                            condition: getFilter('bank_name', 'condition') ||  'contain',
                            value : getFilter('bank_name', 'value')  || "",
                        }
                    ],
                    type: 'textbox', 
                    listeners: ['change'],
                },
            },
            {   title: "@lang('admin_order.grand_total')", 
                dataIndx:'amount', 
                minWidth: 100,
                align : "right",
            },
            {   title: "@lang('admin_order.status')", 
                dataIndx:'status', 
                minWidth: 100,
                align : "right",
            },
            {   title: "@lang('admin_order.log_gen_file')", 
                dataIndx:'latest_log_date', 
                minWidth: 100,
                align : "right",
            },
            {   title: "@lang('admin_common.actions')", 
                    dataIndx:'detail_url', 
                    minWidth: 200,
                    render : function(ui) {
                        return {
                            text:'<a href="'+ui.cellData+'" class="btn-primary">@lang("admin_common.view")</a> <a href="javascript:;" data-val="'+ui.rowData.shop_id+'" class="btn-primary shop_name">Check log</a>',    
                        };                
                    },
                    sortable : !1,
            }, 
            
        ];   

        $('body').on('click','.shop_name',function(e){
            var order_date = "{{$filter_date}}";
            var shop_id = $(this).data('val');
            $.ajax({
                url: log_date_url,
                type:'GET',
                data:{'order_date':order_date,'shop_id':shop_id}, 
                success:function(result){  
                    var txt_html = '';
                    if(result.status=='success'){
                        $(result.data).each(function(key,val){
                            txt_html+='<div class="row"><div class="col-sm-4">'+val.file_name+'</div><div class="col-sm-4">'+val.log_at+'</div></div>';
                        });
                        $('#file_modal_body').html(txt_html);
                        $('#generatedFileModal').modal('show');
                    }else{
                        alert('No log generated');
                    }
                }
            });
        });
    </script>
    
@stop
