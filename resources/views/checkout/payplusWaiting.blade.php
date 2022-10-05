@extends('layouts.app') 
@section('title','Checkout')
@section('content')
    <div class="row h-100 justify-content-center align-items-center">
        <div class="card text-center col-9">
            <div class="card-body">

                        <h2 style="color:#0fad00">Awating payment</h2>
                        <p style="font-size:20px;">Phone : {{ $order->phone }}</p>
                        <p style="font-size:20px;">Invoice : {{ $order->invoice }}</p>
                        <p style="font-size:20px;">Reference : {{ $order->ref1 }}</p>
                        <p style="font-size:20px;">Order : {{ $order->ref2 }}</p>


            </div>
        </div>
    </div>
@endsection