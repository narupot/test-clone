<!DOCTYPE html>
<html>
  <head>
    <meta charset="utf-8">
    <meta name="csrf-token" id="csrftoken"  content="{{ csrf_token() }}">
    <meta name="viewport" content="width=device-width, height=device-height, initial-scale=1.0, maximum-scale=1.0"/>
    <script id="fr-fek">try{(function (k){localStorage.FEK=k;t=document.getElementById('fr-fek');t.parentNode.removeChild(t);})('3Xa1TEWUf1d1QSDb1HAc1==')}catch(e){}
    </script>

    <style>
body {
  text-align: center;
}

div#editor {
  width: 81%;
  margin: auto;
  text-align: left;
}

.fr-inner:not(.row) {
  border: solid 1px #CCC;
}



/* fb list */

#fb-images-ul{
  list-style:none;
  padding:0;
  height: 450px;
    overflow: scroll;
    overflow-x: hidden;
}
#fb-images-ul .col-md-4{
  padding:5px;
}
#fb-images-ul .grid-item{
  width:100%;
  float:left;
  margin:5px;
  border:#B7B7B7 solid 1px;
  max-width:100%;
  overflow:hidden;
}

#fb-images-ul .grid-item img{
  width:100%;
}
#fb-images-ul .grid-item input[type=checkbox]{
  display:none;
}
#fb-images-ul .grid-item a{
  position:relative;
  display:block;
  width:100%;
  display:block;
}
span.checkbox-label{
  position:absolute;
  font-size:25px;
  border:#E7E7E7 solid 1px;
  box-shadow: 1px 1px 3px #B6B6B6;
  padding:3px 5px 3px 3px;
  width:34px;
  background-color:#FFFFFF;
  right:0px;
}
span.checkbox-label.glyphicon-unchecked{
  color:#6D6D6D !important;
}
</style>

    <script src="{{asset('angular-froala/bower_components/jquery/dist/jquery.min.js')}}"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.3.0/codemirror.min.css">
    <link rel="stylesheet" type="text/css" href="{{asset('froala_editor_2.6.2/css/plugins/code_view.min.css')}}">
    <link rel="stylesheet" href="{{asset('/plugins/themes/default/style.min.css')}}">
      @include('includes.froalaeditor_new_dependencies')
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
