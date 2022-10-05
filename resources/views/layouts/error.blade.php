<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8">

<!-- disable iPhone inital scale -->
<meta name="viewport" content="width=device-width; initial-scale=1.0">
<title>{{ getConfigValue('SITE_SHORT_NAME') }}</title>
<link rel="stylesheet" type="text/css" href="{{Config::get('constants.public_url')}}/css/bootstrap.css" />
<link rel="stylesheet" type="text/css" href="{{Config::get('constants.public_url')}}/css/global.css" />
<link rel="stylesheet" type="text/css" href="{{Config::get('constants.public_url')}}/css/select.css" />
</head>
<body>
{{-- @include('layouts.Userheader') --}}
@yield('content')

@yield('footer_scripts')

<script type="text/javascript" src="{{Config::get('constants.public_url')}}/js/jquery.min.js"></script>
<script type="text/javascript" src="{{Config::get('constants.public_url')}}/js/bootstrap.min.js"></script>

</body>
</html>