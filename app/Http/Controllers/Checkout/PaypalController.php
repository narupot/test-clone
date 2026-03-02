<?php
namespace App\Http\Controllers\Checkout;
use App\Http\Controllers\Checkout\OrderController;
use PayPalCheckoutSdk\Orders\OrdersGetRequest;
use Sample\PayPalClient;
use App\Http\Controllers\MarketPlace;
use Illuminate\Http\Request;

use App\OrdersTemp;
use App\PaymentOption;

class PaypalController extends MarketPlace {

	public function __construct() {

 	}

 	public function paypalReturn(Request $request) {
 		//echo '=====>'.$request->orderID;die;

        $paypal_detail = PaymentOption::getPaymentOptionBySlug('paypal');
        if($paypal_detail->mode == '1') {
            $credential = json_decode($paypal_detail->live_detail);
        } else {
            $credential = json_decode($paypal_detail->sandbox_detail);
        }

 		//$client_id = 'ARaDLM_lUNjZFsbRr80Ly2jIX3D04Z28YphHHK6lzPjdWscgxGx3j9WdvezXQsRqnMZO9tPQ7YWumU-A';
 		//$cliend_secret = 'EI1aSGG9jci0Xk3V_4xqWLH-kFnuBm2sLPkBg4rvmQ0POu5RuZu622gCT8loCBIVHG5o0940ITt1r9Dy';

        putenv("CLIENT_ID=$credential->client_id");
        putenv("CLIENT_SECRET=$credential->secret");        

        $client = PayPalClient::client();
        $response = $client->execute(new OrdersGetRequest($request->input('orderID')));

        //echo json_encode($response->result, JSON_PRETTY_PRINT);
        //echo '<pre>';print_r($response->result);

        $status = $response->result->status;
        $txn_id = $response->result->id;
        $invoice_id = $response->result->purchase_units[0]->invoice_id;

        $invoice_id_arr = explode('_', $invoice_id);
        $order_temp_id = $invoice_id_arr['1'];        

        //echo 'status_code=>'.$response->statusCode.', status=>'.$status.', txn_id=>'.$txn_id.', order_temp_id=>'.$order_temp_id;

        $orderInfo = OrdersTemp::where('formatted_order_id',$order_temp_id)->first();

        if($status == 'COMPLETED' && !empty($txn_id) && !empty($orderInfo)) {

	        OrdersTemp::where(['id'=>$orderInfo->id])->update(['txn_id'=>$txn_id,'payment_status'=>1]);

	        $order_frmatted_id = OrderController::saveFinalOrderOffline($orderInfo->id);

            createInvoiceInventoryShipment($order_frmatted_id);

	        $redirect_url = action('Checkout\OrderController@thanks').'?orderId='.$order_frmatted_id;

	        $success = true;
        }
        else {

        	$redirect_url = '';
        	$success = false;
        }
        return json_encode(['success'=>$success, 'redirect_url'=>$redirect_url]);
 	}
}
