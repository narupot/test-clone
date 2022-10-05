<?php

$api_key = $_GET['data-apikey'];
$amount = $_GET['data-amount'];
$payment_methods = $_GET['data-payment-methods'];
$data_order_id = $_GET['data-order-id'];

$secret_key = 'skey_test_819qkHSYsDeOdwNfpJq3AzIF4OkzugTFFA';
$ref_no = substr(number_format(time() * rand(),0,'',''),0,10);
$post_array = array('amount'=>$amount,'currency'=>'THB','description'=>'item','source_type'=>'qr','reference_order'=>$ref_no);
$post_json = json_encode($post_array);

$ch = curl_init();

curl_setopt($ch, CURLOPT_URL,"https://dev-kpaymentgateway-services.kasikornbank.com/qr/v2/order");
curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
curl_setopt($ch, CURLOPT_POST, 1);
curl_setopt($ch, CURLOPT_POSTFIELDS,$post_json);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, array(
        'Content-Type: application/json',
        'x-api-key: '.$secret_key.'')
);

$server_output = curl_exec($ch);
$order_id = 0;
$response = json_decode($server_output);
if(!empty($response->id)){
    $order_id = $response->id;
}else{
    echo 'invalid order id';
}
?>

<!DOCTYPE html>
<html>
<head>
	<title>Kbank</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <style type="text/css">
        .card_wrapper {
            max-width: 350px;
            margin: 100px auto 30px auto;
            text-align: center;
        }        
        .card_wraps {
            background: #FFF;
            padding: 30px;
            box-shadow: 0 0 6px rgba(0, 0, 0, 0.1);
            margin-bottom: 1rem;
            border-radius: 15px;
        }
        .card_wraps img {
            max-width: 150px;
            margin-bottom: 1rem;
        }
        .pay-button[_kpayment] {
            text-align: center !important;
            max-width: 350px;
            margin: 0 auto;
            display: block !important;
        }

    </style>
</head>
<body>
    <div class="card_wrapper">
        <div class="row h-100 justify-content-center align-items-center ">
            <div class="card card_wraps text-center col-5">
                <img src="https://www.mercular.com/img/footer/kbank.png" class="card-img-top" alt="Omise">
                <div class="card-body">

                </div>
            </div>
        </div>
    </div>
</body>
<script id="qr_js" type="text/javascript"
            src="https://dev-kpaymentgateway.kasikornbank.com/ui/v2/kpayment.min.js"
            data-apikey="<?php echo $api_key; ?>"
            data-amount ="<?php echo $amount; ?>"
            data-payment-methods="<?php echo $payment_methods; ?>"
            data-order-id="<?php echo $order_id; ?>"
    >
    </script>
</html>