<!DOCTYPE html>
<html>
  <head>
    <meta charset="utf-8">
    <meta name="csrf-token" id="csrftoken"  content="{{ csrf_token() }}">
    <meta name="viewport" content="width=device-width, height=device-height, initial-scale=1.0, maximum-scale=1.0"/>
    <script id="fr-fek">try{(function (k){localStorage.FEK=k;t=document.getElementById('fr-fek');t.parentNode.removeChild(t);})('3Xa1TEWUf1d1QSDb1HAc1==')}catch(e){}
    </script>

    <script src="{{asset('angular-froala/bower_components/jquery/dist/jquery.min.js')}}"></script>
      @include('includes.froalaeditor_dependencies')
    <!-- already on page -->
    <link href="{{asset('css/global.css')}}" rel="stylesheet" />
  </head>

  <body>
      <div class="sample">
        <h2>Froala Normal Editor</h2>
        <textarea class="froala-editor-apply" name="editor"></textarea>      
      </div>    
  </body>
  <script src="{{asset('js/normal_froala_editor_setting.js')}}"></script>
</html>
