@extends('layouts.admin.default')
@section('title')
    @lang('admin_product.delete_product')
@stop
@section('header_styles')
<link rel="stylesheet" type="text/css" href="{{Config('constants.admin_css_url') }}flatpickr.min.css">
<script src="{{ Config('constants.js_url') }}flatpickr.min.js"></script>
@stop
@section('content')
<div class="content">
  <div class="header-title">
    <h1 class="title">@lang('admin_product.delete_product')</h1>
  </div>
  <div class="content-wrap clearfix">
    <div class="breadcrumb">
          <ul class="bredcrumb-menu">
              {!!getBreadcrumbAdmin('delete_product')!!}
          </ul>
      </div>    
    @if($actionInsert==1)    
          <div class="row">
            <div class="col-sm-9">&nbsp;</div>
            <div class="col-sm-3"><a href="{{action('Admin\Product\ProductController@deleteProductManual')}}" class="btn btn-secondary">Go To Delete Product</a></div>
          </div>
       
          @if(count($duplicateArr)>0)
              <h2>Categoeies Import Error</h2>
               <ul>
                  @foreach($duplicateArr as $data)
                      <li> {!! $data !!}</li>
                  @endforeach
              </ul>

          @else

            <h2>Total Categoeies</h2>
              <div class="progress">
                  <div class="progress-bar {!! $class !!}" role="progressbar" aria-valuenow="70"
                  aria-valuemin="0" aria-valuemax="100" style="width:{!!$percentage!!}%">
                  {!! $percentage !!}% @lang('common.complete')
                  </div>
              </div>
          
              <h2>Global State</h2>
             
              @foreach($globalstate as $key=>$value)
              @if($key=='Error')
              @foreach($globalstate[$key] as $k=>$err)
              <div class="row" >
                  <div class="col-sm-3"><h2><font color="red">Row-{!! $k !!}</font></h2></div>
                  <div class="col-sm-9">{!! $err !!}</div>
              </div>
              @endforeach
              @elseif($key=='Attributes')
              @foreach($globalstate[$key] as $kk=>$errorStr)
              <div class="row">
                 <div class="col-sm-3"><h2><font color="red">{!! $kk !!}</font></h2></div>
                  <div class="col-sm-9">{!! $errorStr !!}</div>
              </div>
              @endforeach
              @elseif($key=='WarningMess')

                  <div class="row mt-10">
                      <div class="col-sm-12"><h2>Results:</h2></div>
                      @foreach($globalstate[$key] as $kk=>$errorStr)
                        <div class="col-sm-12">{!! $errorStr !!}</div>
                      @endforeach
                  </div>

              @elseif($key=='SuccessMess')
              <div class="row mt-10">
                  <div class="col-sm-12"><h2>Results:</h2></div>
                  @foreach($globalstate[$key] as $kk=>$errorStr)
                    <div class="col-sm-12">{!! $errorStr !!}</div>
                  @endforeach
              </div>

              @elseif($key=='categoryNotUploaded')
              <div class="row mt-10">
                  <div class="col-sm-12"><h2>Not Assigned Category:</h2></div>
                  @foreach($globalstate[$key] as $kk=>$categories)
                  <div class="col-sm-12">
                      <div class="col-sm-3">{{$kk}}</div>
                      <div class="col-sm-9">
                             {!! implode(', ', $categories) !!}
                      </div>
                  </div>    
                  @endforeach
              </div>
              
              @else
               <div class="row">
                  <div class="col-sm-3"> {!! $key !!}</div>
                  <div class="col-sm-9">{!! $value !!}</div>
              </div>
              @endif
              @endforeach
              
             </div>

          @endif   

    @else 
      <div class="row">
        <div class="col-sm-4">
          <form method="post" action="{{action('Admin\Product\ProductController@deleteProductBySky')}}" enctype="multipart/form-data">
        {{ csrf_field() }}
          
            <div class="form-group">
                <label><input type="radio" name="delete_type" value="sku" checked="checked"> By Sku</label>
            </div>
            <div class="form-group hide_div" id="sku_div">
              <label>Product Sku</label>
                <input type="text" name="sku">
                @if ($errors->has('sku'))
                   <p id="name-error" class="error error-msg">{{ $errors->first('sku') }}</p>
                @endif
            </div>
            <div class="form-group">
                <label><input type="radio" name="delete_type" value="csv"> By CSV</label>
            </div>
            <div class="form-group hide_div" id="csv_div" style="display: none;">
              <label>Files(csv)</label>
                <input type="file" name="import_file">
                @if ($errors->has('import_file'))
                   <p id="name-error" class="error error-msg">{{ $errors->first('import_file') }}</p>
                @endif
            </div>
            <div class="form-group">
                <label><input type="radio" name="delete_type" value="daterange"> By Product Create Date Range</label>
            </div>
            <div class="form-group hide_div" id="daterange_div" style="display: none;">
                <div class="form-row">
                    <div class="col-sm-6">
                        <label for="form-text-input">From Date </label>
                        <div>
                            {!! Form::text('from_date', old('from_date'), ['class' => 'date-select date-picker flatpickr', 'id'=>'from_date'] ) !!}
                        </div>
                    </div>
                    <div class="col-sm-6">
                        <label for="form-text-input">To Date</label>
                        <div>
                            {!! Form::text('to_date', old('to_date'), ['class' => 'form-control date-select date-picker flatpickr', 'id'=>'to_date'] ) !!}
                        </div>
                    </div>
                </div>
            </div>
            <div class="form-group float-right">
                <input type="submit" value="Submit" class="btn btn-primary">
            </div>
            
        </form>
        </div>
      </div>
    @endif
  </div>
</div>
      
@stop

@section('footer_scripts')  

<script type="text/javascript">
    $(document).ready(function(e){
        $('input[name="delete_type"]').change(function(e){
            var delete_type = $(this).val();
            $('.hide_div').hide();
            $('#'+delete_type+'_div').show();
        })
    });

    ;(function(){
          
            flatpickr("#from_date", {
                onChange: function(dateObj, dateStr) {
                    flatpickr("#to_date", {
                        minDate : dateStr,
                    });             
                }
            });

            flatpickr("#to_date", {
                onChange: function(dateObj, dateStr) {  
                    
                    flatpickr("#from_date", {
                        maxDate : dateObj
                    });
                 from_date.set("maxDate", dateObj);
                }
            });
            
        })(jQuery);
</script>
@stop
