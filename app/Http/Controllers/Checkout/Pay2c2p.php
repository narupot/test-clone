<?php 
namespace App\Http\Controllers\Checkout;

use DB;
use App\C2PDatabase;
use App\OrdersTemp;
use App\Orders;
use Illuminate\Http\Request;

class Pay2c2p {

    private $db_helper;
    private $settings;
    private $mode;
    private $merchant_id;
    private $terminal_id;

    public function __construct(){
        $this->db_helper = new C2PDatabase();
        $this->settings = $this->db_helper->GetSettings();
        $this->mode = $this->db_helper->GetMode();

        switch ($this->mode->mode){
            case 1:
                $this->merchant_id = $this->settings->live_detail->merchant_id;
                $this->secret = $this->settings->live_detail->key;
                break;
            case 2:
                $this->merchant_id = $this->settings->sandbox_detail->merchant_id;
                $this->secret = $this->settings->sandbox_detail->key;
                break;
        }
    }

    public function Payload(Request $request){

        $orderInfo = Orders::where('formatted_order_id',$request->input('formatted_order_id'))->first();
        $merchantID = $this->merchant_id;
        $secretKey = $this->secret;
        $method = $request->input('method');
        //Transaction Information
        $desc = "Payment on Atmos";
        $uniqueTransactionCode = $request->input('formatted_order_id');
        $currency = $this->db_helper->GetCurrency($orderInfo->currency_id);
        $currencyCode = $this->convertCurrencyFormat(strtoupper($currency));
        $total = $orderInfo->total_final_price;
        $amt  = str_pad(($total*100), 12, '0', STR_PAD_LEFT);
        $panCountry = "TH";

        $cardholderName = $request->input('holder_name');

        $encCardData = $request->input('encryptedCardInfo');

        //Request Information
        $version = "9.9";

        $alternative = "";

        if($method == "OVERTHECOUNTER" || $method == "BANKCOUNTER"){
            $encCardData = "";
            $agent = $this->db_helper->GetAgent($request->input('agent'));

            $alternative = "<paymentChannel>".$agent->agent_channel."</paymentChannel>
            <agentCode>".$request->input('agent')."</agentCode>
            <channelCode>".$agent->agent_method."</channelCode>
            <paymentExpiry>".(new \DateTime('tomorrow'))->format("Y-m-d 23:59:59")."</paymentExpiry>
            <mobileNo>".$request->input('payer_phone')."</mobileNo>
            <cardholderEmail>".$request->input('payer_email')."</cardholderEmail>";
        }elseif ($method == "WEBPAY"){
            $encCardData = "";
            $wallet = $this->db_helper->GetWalllet($request->input('wallet'));

            $alternative = "<paymentChannel>".$wallet->agent_channel."</paymentChannel>";
        }

        $xml = "<PaymentRequest>
        <merchantID>$merchantID</merchantID>
        <uniqueTransactionCode>$uniqueTransactionCode</uniqueTransactionCode>
        <desc>$desc</desc>
        <amt>$amt</amt>
        <currencyCode>$currencyCode</currencyCode>  
        <panCountry>$panCountry</panCountry> 
        <cardholderName>$cardholderName</cardholderName>
        ".$alternative."
        <encCardData>$encCardData</encCardData>
        </PaymentRequest>";

        //echo '===>'.$xml;die;

        $paymentPayload = base64_encode($xml);
        $signature = strtoupper(hash_hmac('sha256', $paymentPayload, $secretKey, false));
        $payloadXML = "<PaymentRequest>
           <version>$version</version>
           <payload>$paymentPayload</payload>
           <signature>$signature</signature>
           </PaymentRequest>";
           dd($payloadXML);
        $payload = base64_encode($payloadXML); //encode with base6

        //echo '===>'.$payload;die;

        echo $payload;
    }

    public function GetAgents($method){

        $agents = $this->db_helper->GetAgents($method);
        echo json_encode($agents);
    }

    public function GetWallets($method){

        // $method can be used later on to be more spcific

        $wallets = $this->db_helper->GetWallets();
        echo json_encode($wallets);
    }

    public function Tracking(Request $request){

        $response = $request->input("paymentResponse");

        $reponsePayLoadXML = base64_decode($response);

        $xmlObject =simplexml_load_string($reponsePayLoadXML) or die("Error: Cannot create object");

        $payloadxml = base64_decode($xmlObject->payload);

        $payment_response = simplexml_load_string($payloadxml);

        $signaturexml = $xmlObject->signature;

        $secretKey = $this->secret;

        $base64EncodedPayloadResponse=base64_encode($payloadxml);

        $signatureHash = strtoupper(hash_hmac('sha256', $base64EncodedPayloadResponse ,$secretKey, false));

        if($signaturexml == $signatureHash){

            $orderInfo = OrdersTemp::where('formatted_order_id',$payment_response->uniqueTransactionCode)->first();
            if(!empty($orderInfo)){
                if($payment_response->respCode == "00" || $payment_response->respCode == "000" || intval($payment_response->respCode) == 0){

                    OrdersTemp::where(['id'=>$orderInfo->id])->update(['txn_id'=>$payment_response->tranRef,'payment_status'=>1,'payment_response_json'=> $payloadxml]);
                    
                    $order_formatted_id = OrderController::saveFinalOrderOffline($orderInfo->id);
                }
            }
        }
    }

    private function convertCurrencyFormat($currency, $type='number') {
        $currencies = array(
            'THB' => '764',
            'AUD' => '036',
            'GBP' => '826',
            'EUR' => '978',
            'HKD' => '344',
            'JPY' => '392',
            'NZD' => '554',
            'SGD' => '702',
            'CHF' => '756',
            'USD' => '840'
        );

        if ($type == 'string') {
            $currencies = array_flip($currencies);
        }

        if (empty($currencies[$currency])) {
            throw new Exception('Currency "'.$currencies.'" not support.');
        }
        return $currencies[$currency];
    }

    public function CheckTransaction($id) {

        $order = $this->db_helper->GetFinalOrder($id);

        if(is_object($order)){

            $object = new \stdClass();

            $object->status = "success";

            $object->url = action('Checkout\OrderController@thanks',$order->formatted_order_id);

            echo json_encode($object);

        }else{

            echo "pending";
        }
    }

    public function ReturnTransaction(Request $request) {


        // ---- This is wrong ----
        // $order = $this->db_helper->GetFinalOrder($request->cookie('orderid'));

        // echo $request->cookie('orderid');
        // echo "<br />" . $order->id;
        // echo "<br />" . $order->formatted_order_id;
        // exit;

        // if(is_object($order) && isset($order->id)){

        //     return redirect(action('Checkout\OrderController@thanks').'?orderId='.$order->formatted_order_id);
        // }else{

        //     return redirect()->action("Checkout\OrderController@cancelPayment");
        // }
        //

        // Modify By Thanut : 12 Feb 2020
        $response = $request->input("paymentResponse");

        $reponsePayLoadXML = base64_decode($response);

        $xmlObject =simplexml_load_string($reponsePayLoadXML) or die("Error: Cannot create object");

        $payloadxml = base64_decode($xmlObject->payload);

        $payment_response = simplexml_load_string($payloadxml);

        $signaturexml = $xmlObject->signature;

        $secretKey = $this->secret;

        $base64EncodedPayloadResponse=base64_encode($payloadxml);

        $signatureHash = strtoupper(hash_hmac('sha256', $base64EncodedPayloadResponse ,$secretKey, false));

        if($signaturexml == $signatureHash){
            

            if($payment_response->respCode == "00" || $payment_response->respCode == "000" || intval($payment_response->respCode) == 0){
                $formatted_order_id = '';
                $original_ord = \App\Orders::where('temp_formated_order_id',$payment_response->uniqueTransactionCode)->first();
                if(!empty($original_ord)){
                    $formatted_order_id = $original_ord->formatted_order_id;
                }
                if($formatted_order_id){
                    return redirect(action('Checkout\OrderController@thanks',$formatted_order_id));
                }else{
                    return redirect()->action("Checkout\OrderController@processing");
                }
                
            }else{
                return redirect()->action("Checkout\OrderController@cancelPayment");
            }

        }
        // End Modify By Thanut : 12 Feb 2020

    }
}
