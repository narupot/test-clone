<?php

namespace App;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Auth;

class FilterLog extends Model {
    
    protected $table = 'filter_log';

    public static function getFilter($filter_name){
        $admin_id = Auth::guard('admin_user')->user()->id;

        $filter_data = \App\FilterLog::where(['filter_name'=>$filter_name,'user_id'=>$admin_id])->first();

        $result_data = ['pq_datatype'=>'JSON','pq_curpage'=>'1','pq_rpp'=>'10'];

        if($filter_data){
            return $filter_data->filter_value;
        }
        return jsonEncode($result_data);
    }

}
