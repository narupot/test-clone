@extends('layouts.app') 
@section('title','Checkout')
@section('content')
    <div class="row h-100 justify-content-center align-items-center">
        <div class="card text-center col-9">
            <div class="card-body">

                        <h2 style="color:#0fad00">Awating payment</h2>
                        <p style="font-size:20px;">Order : {{ $order->formatted_id }}</p>
                        <p style="font-size:20px;">Invoice : {{ $order->id }}</p>
            </div>
        </div>
    </div>
@endsection
@section('footer_scripts')

    <script>
        var invoice = "{{$order->id}}";
        var check_url = "{{action('Checkout\PaymentGatewayController@payplusCheck')}}";
        function CheckPayment(invoice) {
            $.get(check_url+'/'+invoice,function (data,status) {
                if(data.status == "success"){

                    window.location.href = data.url;
                }else{
                    setTimeout(function () {
                        CheckPayment(invoice);
                    },3000);
                }
            });
        }
        CheckPayment(invoice);
    </script>

@endsection