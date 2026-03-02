<?php
 
namespace App\Http\Controllers\Admin\TransactionFee;
 
use App\Http\Controllers\MarketPlace;
use App\TransactionFeeConfig;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Config;
use DB;
 
class TransactionFeeConfigController extends MarketPlace
{
    public function __construct()
    {  
        $this->middleware('admin.user');
    }    
 
    public function index()
    {
        $permission = $this->checkUrlPermission('transaction_fee');
        if ($permission === true) {
            $results = TransactionFeeConfig::orderBy('id', 'asc')->get();
            return view('admin.transaction_fee.list', [
                'results' => $results
            ]);
        }
    }
 
    public function bulkUpdate(Request $request)
    {
        $permission = $this->checkUrlPermission('transaction_fee');
        if ($permission === true) {
            $data = $request->input('data');
            foreach ($data as $id => $row) {
                $config = \App\TransactionFeeConfig::find($id);
                if ($config) {
                    $config->message = $row['message'];
                    $config->tf = $row['tf'];
                    $config->effective_date = $row['effective_date'];
                    $config->save();
                }
            }
            return redirect()->route('admin.transaction-fee.index')
                ->with('succMsg', 'Transaction fee configurations updated successfully!');
        }
    }
}