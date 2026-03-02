<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="utf-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <title>Laravel</title>

        <!-- Fonts -->
        <link href="https://fonts.googleapis.com/css?family=Raleway:100,600" rel="stylesheet" type="text/css">
        {!! CustomHelpers::combineCssJs(['css/myaccount'],'css') !!}    

        <!-- Styles -->
        <style>
            html, body {
                background-color: #fff;
                color: #636b6f;
                font-family: 'Raleway', sans-serif;
                font-weight: 100;
                height: 100vh;
                margin: 0;
            }

            .full-height {
                height: 100vh;
            }

            .flex-center {
                align-items: center;
                display: flex;
                justify-content: center;
            }

            .position-ref {
                position: relative;
            }

            .top-right {
                position: absolute;
                right: 10px;
                top: 18px;
            }

            .content {
                text-align: center;
            }

            .title {
                font-size: 84px;
            }

            .links > a {
                color: #636b6f;
                padding: 0 25px;
                font-size: 12px;
                font-weight: 600;
                letter-spacing: .1rem;
                text-decoration: none;
                text-transform: uppercase;
            }

            .m-b-md {
                margin-bottom: 30px;
            }
        </style>
        
        <script src="https://code.jquery.com/jquery-3.x.x.min.js"></script>
        <script>
            $(document).ready(function () {
                $('#change_pickup_btn').click(function () {
                    $('#change_pickup_time').show();
                    $(this).hide();
                });
            }); 
        </script>
    </head>
    <body>

        <div id="myTest">Test 888</div>
        @php 
        $tot_cart_prd_noti = getCartProduct();
        $tot_pending_order = getPendingOrderNoti();
        
        $cur_hr = date('H');
        
        $ndate = now()->format('Y-m-d H:i');
        $expdate = explode('-', $ndate);
        @endphp
        
        
        <div class="flex-center position-ref full-height">
            @if (Route::has('login'))
                <div class="top-right links">
                    <a href="{{ url('/login') }}">Login</a>
                    <a href="{{ url('/register') }}">Register</a>
                </div>
            @endif

            
            <div class="content">
                <div class="title m-b-md">
                    Laravel
                    <br>
                    {{ $tot_cart_prd_noti['cart_prd'] }}
                    <br>{{ $tot_pending_order['pendingOrder'] }}
                    <br>@if($cur_hr<19 || $cur_hr>=20)
                            <div>Love Love</div>
                        @endif                        
                    <br>{{ explode(' ', $ndate)[0] }}
                    <br>{{ $ndate }}
                </div>

                
                

                <div class="links">
                    <a href="https://laravel.com/docs">Documentation</a>
                    <a href="https://laracasts.com">Laracasts</a>
                    <a href="https://laravel-news.com">News</a>
                    <a href="https://forge.laravel.com">Forge</a>
                    <a href="https://github.com/laravel/laravel">GitHub</a>
                </div>
            </div>
        </div>

        <form method="post" id="remark-form" action="{{action('Admin\Transaction\OrderController@updateRemark')}}">
            <input type="hidden" name="order_id" value="106514">
            <div class="row">
                <div class="col-sm-6 form-group">
                    <label>remark</label>
                    <textarea name="remark" required="required" id="txt_remark" placeholder="Remark text ..."></textarea>
                    <div class="mt-2">
                        <button type="button" class="btn btn-primary" id="btn-remark">save</button>
                    </div>
                </div>
            </div>
        </form>
        
       
    </body>
   
</html>
