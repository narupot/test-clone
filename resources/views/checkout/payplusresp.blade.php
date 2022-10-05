@extends('layouts.app') 
@section('title','Checkout')
@section('content')
    <div class="row h-100 justify-content-center align-items-center">
        <div class="card text-center col-9">
            <div class="card-body">
                <form method="post" action="https://rt05.kasikornbank.com/Payplus/InquiryTransaction.aspx">
                    <input type="text" name="USERNAME" placeholder="USERNAME"><br>
                    <input type="text" name="TMERCHANTID" placeholder="TMERCHANTID"><br>
                    <input type="text" name="TDATE" placeholder="TDATE"><br>
                    <input type="text" name="TINVOICE" placeholder="TINVOICE"><br>
                    <input type="text" name="TAMOUNT" placeholder="TAMOUNT"><br>
                    <input type="text" name="TSTATUS" placeholder="TSTATUS"><br>
                    <input type="text" name="TREF1" placeholder="TREF1"><br>
                    <input type="text" name="TREF2" placeholder="TREF2"><br>
                    <input type="submit" name="submit" value="submit">
                </form>
            </div>
        </div>
    </div>
@endsection