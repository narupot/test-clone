<!DOCTYPE html>

<!-- define angular app -->
<html ng-app="myApp">

<head>
  <meta charset="utf-8">
  <meta name="csrf-token" id="csrftoken"  content="{{ csrf_token() }}">
  <meta name="viewport" content="width=device-width, height=device-height, initial-scale=1.0, maximum-scale=1.0"/>
  <script id="fr-fek">try{(function (k){localStorage.FEK=k;t=document.getElementById('fr-fek');t.parentNode.removeChild(t);})('3Xa1TEWUf1d1QSDb1HAc1==')}catch(e){}
  </script>

  <script>
  //window.userFolderDefaultPath = "{!! '/files/froala_uploads/'.md5(Auth::id()).'/' !!}";
  </script>
  <script src="{{asset('angular-froala/bower_components/jquery/dist/jquery.min.js')}}"></script>
  <script src="{{asset('angular-froala/bower_components/angular/angular.min.js')}}"></script>
  
  @include('includes.froalaeditor_dependencies')

  <script src="{{asset('angular-froala/src/angular-froala.js')}}"></script>
   <!--- whill change according to our need -->
  <script src="{{asset('angular/Froala/app.js')}}"></script>
 <!-- already on page -->
  <link href="{{asset('css/global.css')}}" rel="stylesheet" />

</head>

<!-- define angular controller -->
<body ng-controller="mainController">
    <div class="sample">
      <h2>Sample 2: Full Editor</h2>
      <textarea id="froala-sample-2" froala ng-model="sample2Text"></textarea>
      <!-- <h4>Rendered Content:</h4>
      <div froala-view="sample2Text"></div> -->
    </div>
    <div>Data
    @{{sample2Text}}
    </div>
</body>
<script type="text/javascript">
//Facebook SDK
(function(d, s, id){
 var js, fjs = d.getElementsByTagName(s)[0];
 if (d.getElementById(id)) {return;}
 js = d.createElement(s); js.id = id;
 js.src = "//connect.facebook.net/en_US/sdk.js";
 fjs.parentNode.insertBefore(js, fjs);
}(document, 'script', 'facebook-jssdk'));
</script>



</html>
