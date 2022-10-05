<?php namespace App\Modules;

use Illuminate\Support\Facades\Auth;
use DB;
use Config;
use App\Module;
class ModulesManager 
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */

    public function __construct()
    {
        //
    }

   /**
    * 
    */
   public static function paymentModuleConnector($paymentOptionDetails,$payment_gateway_name,$formatedOrderId){

        //$payment_gateway_details = Module::select('module_name')->where('category_name','Payment Gateway')->get();

        $pg_details = self::getPaymentGateway($payment_gateway_name);        
        
        $module_name = str_replace(' ', '', $pg_details->module_name);
        
        $base_url = Config::get('constants.public_url');
        $payment_2c2p_url = $base_url . 'en/'.$module_name.'/payment' . '?orderid=' . $formatedOrderId .'&payment_gateway='.$payment_gateway_name. '&paymentId=' . $paymentOptionDetails->id;
        $return_array['success'] = 'true';
        $return_array['url'] = $payment_2c2p_url;


        return $return_array;
         
       }


    protected static function getPaymentGateway($payment_gateway_name){
       
        switch ($payment_gateway_name) {
            case '2C2P Payment Gateway':
              $table_name = "payment_gateway2c2p_module";
              $response_data = DB::table($table_name)->where('payment_gateway_name',$payment_gateway_name)->first();                
            break;

            case 'Paypal':
              $table_name = "payment_paypal_module";
              $response_data = DB::table($table_name)->where('payment_gateway_name',$payment_gateway_name)->first();                
            break;
            
        }

        return $response_data;
        
    }
    

}
