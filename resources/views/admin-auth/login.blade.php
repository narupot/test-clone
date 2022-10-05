<!DOCTYPE html>
<html>
<head>
    <title>Login | Admin</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="{{ Config('constants.admin_css_url') }}bootstrap.min.css"/>
    <link rel="stylesheet" href="{{ Config('constants.admin_css_url') }}allfontawesome.css" />
    <link rel="stylesheet" href="{{ Config('constants.admin_css_url') }}global.css" />
    <link rel="stylesheet" href="{{ Config('constants.admin_css_url') }}login.css" /> 
</head>
<body>
    <div class="admin-logo">
        <img src="{{ getSiteLogo('SITE_LOGO_HEADER') }}" alt="Logo"> 
    </div>
    <div class="container main-container">
        <div id="wrapper">            
            <div class="row align-items-center">
                <div class="col-md-6 mb-3">
                  <img src="{{ Config::get('constants.image_url')}}user-login.svg" alt="">
                  <div class="powerby">
                      <div>Power By Smoothgraph Connect Co., Ltd.</div>
                      <div>Email support : <a href="mailto:Thanut@smoothgraph.com">Thanut@smoothgraph.com</a></div>
                      <div><i class="fas fa-phone-volume"></i>: <a href="tel:02-291-2269">02-291-2269</a>, <a href="tel:02-291-2269">081-xxxxxxx</a></div>
                  </div>
                </div>
                <div class="col-md-6 mb-3">
                    <div id="login" class="animate form">
                        <form action="{{ action('AdminAuth\LoginController@login') }}" id="authentication" autocomplete="on" method="post">
                            {{ csrf_field() }}
                            <h2 class="black_bg">Sign in to the {{ getConfigValue('SITE_SHORT_NAME')}}</h2>  
                            @if ($errors->has('email'))
                                <div class="form-group ">
                                    <p class="error error-msg" id="admin_email-error">{!! $errors->first('email') !!}</p>
                                </div>
                            @endif                                       
                            <div class="form-group">
                                <i class="fas fa-user licon"></i>
                                <input id="email" name="email" type="email" placeholder="E-mail" value="{{ old('email') }}" />
                            </div>
                            <div class="form-group">
                                <i class="fas fa-unlock-alt licon"></i>
                                <input type="password" id="password" name="password" placeholder="Enter a password" />
                                <i class="fas fa-eye ricon" onclick="pwdShow()"></i>
                            </div>
                            <div class="form-group">
                                <label class="check-wrap"><input type="checkbox" name="remember" id="remember-me" value="remember-me" class="square-blue" /> <span class="chk-label">Keep me logged in</span></label>
                            </div>
                            <div class="login button mb-0 text-center">
                                <input type="submit" value="SIGN IN" class="btn btn-primary w-75" />
                            </div>
                        </form>
                    </div>
                </div>
            </div>            
        </div>
    </div>

    <script src="{{ Config('constants.admin_js_url') }}jquery.min.js"></script>    
    <script src="{{ Config('constants.admin_js_url') }}bootstrap.min.js"></script>
    <!-- <script src="{{ Config('constants.admin_js_url') }}custom-admin.js"></script>
    <script src="{{ Config('constants.admin_js_url') }}common.js"></script> -->
    <script src="{{ Config('constants.admin_js_url') }}bootstrapValidator.min.js"></script>
    <script src="{{ Config('constants.admin_js_url') }}login.js"></script>
    <script>
        function pwdShow() {
          var pwdval = document.getElementById("password");
          jQuery(".ricon").toggleClass("fa-eye fa-eye-slash");
          if (pwdval.type === "password") {
            pwdval.type = "text";       
          } 
          else {
            pwdval.type = "password";
          }
        }
  </script>
</body>
</html>
