<?php 

namespace App\Http\Controllers\Admin\Transaction;

use App\Http\Controllers\MarketPlace;
use App\Helpers\GeneralFunctions;
use App\Helpers\CustomHelpers;
use App\Helpers\EmailHelpers;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Storage;
use Auth;
use App\Order;
use App\OrderDetail;
use App\OrderShop;
use App\OrderExportLog;
use App\Product;
use Lang;
use Config;
use Excel;

class ExportOrderController extends MarketPlace
{
    public function __construct(){
       $this->middleware('admin.user');
    }
    
    public function index(){

        $permission = $this->checkUrlPermission('export_order_log');
        if($permission === true) {
            
            $filter = $this->getFilter('export_order_log');

            return view('admin.transaction.exportOrder', ['filter'=>$filter]);
        }      
    }

    public function listExportOrderData(Request $request){
        $perpage = !empty($request->pq_rpp) ? $request->pq_rpp : 10;
        $request->page = $current_page = !empty($request->pq_curpage)?$request->pq_curpage:0;

        $start_index = ($current_page - 1) * $perpage;
        //dd($perpage,$request->page);
        
        $order_by = 'id';
        $order_by_val = 'desc';
        if(isset($request->pq_sort)){
            $sort_data = jsonDecodeArr($request->pq_sort);
            $order_by = $sort_data[0]['dataIndx'];
            $order_by_val = ($sort_data[0]['dir']=='up')?'asc':'desc';
        }

        try{
            
            $query = OrderExportLog::select('*');
            
            if(isset($request->pq_filter)){
                $filter_req = json_decode($request->pq_filter,true);
                if(!empty($filter_req['data'])){
                    $filter_arr = $filter_req['data'];
                    foreach ($filter_arr as $fkey => $fvalue) {

                        $searchval = $fvalue['value'];
                        switch ($fvalue['dataIndx']) {
                            case 'status':$query->where('formatted_id','like', '%'.$searchval.'%'); break;
                            case 'file_name':$query->where('file_name','like', '%'.$searchval.'%'); break;
                            case 'created_at':
                                $from_date = $fvalue['value']??'';
                                $to_date = $fvalue['value2']??'';
                                createDateFilter($query,'created_at',$from_date,$to_date);
                            break;
                            case 'order_date':
                                $from_date = $fvalue['value']??'';
                                $to_date = $fvalue['value2']??'';
                                createDateFilter($query,'order_date',$from_date,$to_date);
                            break;
                            
                        }
                        
                    }
                }
            }
            $response = $query->orderBy($order_by,$order_by_val)->paginate($perpage,['*'],'page',$current_page);
            $totrec = $response->total();
            //dd($response);
            if($start_index >= $totrec) {
                $current_page = ceil($totrec/$perpage);
                
                $response = $query->orderBy($order_by,$order_by_val)->paginate($perpage,['*'],'page',$current_page);
            }

            foreach ($response as $key => $value) {

                $response[$key]->dwn_url = action('Admin\Transaction\ExportOrderController@downloadExport',$value->id);
                //$response[$key]->dwn_url = Config::get('constants.public_url').'seller-payment/'.$value->file_name.'.txt';;
            }

            /***save filter****/
            $this->setFilter('export_order_log',$request);

            
        }catch(QueryException $e){
            $response = ['status'=>'fail','msg'=>$e->getMessage()];
        }
        

        return $response;
    }

    public function downloadExport(Request $request,$id=null){

        if($id){
            $file_data = OrderExportLog::where('id',$id)->first();
            if($file_data){
                $file_path = Config::get('constants.public_path').'/seller-payment/';
                $filename = $file_data->file_name.'.txt';
                $file_full_path = $file_path.$filename;

                if(file_exists($file_full_path)){

                    header("Content-Type: application/octet-stream");
                    header("Content-Disposition: attachment; filename=" . Urlencode($filename));   
                    header("Content-Type: application/force-download");
                    header("Content-Type: application/octet-stream");
                    header("Content-Type: application/download");
                    header("Content-Description: File Transfer");            
                    header("Content-Length: " . Filesize($file_full_path));
                    flush(); // this doesn't really matter.
                    $fp = fopen($file_full_path, "r");
                    while (!feof($fp))
                    {
                        echo fread($fp, 65536);
                        flush(); // this is essential for large downloads
                    } 
                    fclose($fp);
                    
                }
                
            }
        }
    }

    public function changeStatus(Request $request){

        if(!empty($request->export_id) && !empty($request->status)){
            $status = $request->status;
            OrderExportLog::where('id',$request->export_id)->update(['status'=>$status]);
            return ['status'=>'success','msg'=>\Lang::get('admin_common.records_updated_successfully')];
        }else{
            return ['status'=>'fail','msg'=>\Lang::get('admin_common.something_went_wrong')];
        }
    }
    
    function store(Request $request){
    }
    
    function edit($group_id){
    }
    
    function update(Request $request){
    }
    
}
