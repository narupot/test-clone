  <script>
    window.userFolderDefaultPath = "{{Config::get('constants.froala_img_path').md5(Auth::id()).'/'}}";
    froalaloadimages_url = "{{action('FroalaEditorController@froalaLoadImages')}}";
    froaladeletefolder_url = "{{action('FroalaEditorController@froalaDeleteFolder')}}";
    froalaupload_url = "{{action('FroalaEditorController@uploadImage')}}";
    froalaNewFolder_url = "{{action('FroalaEditorController@froalaNewFolder')}}";
    var csrftoken = "{{csrf_token()}}";
  </script>
  <script id="fr-fek">try{(function (k){localStorage.FEK=k;t=document.getElementById('fr-fek');t.parentNode.removeChild(t);})('3Xa1TEWUf1d1QSDb1HAc1==')}catch(e){}
  </script>
    <!-- Include Font Awesome. -->
  <link href="{{asset('angular-froala/bower_components/font-awesome/css/font-awesome.min.css')}}" rel="stylesheet" type="text/css" />

  <link rel="stylesheet" href="{{asset('angular-froala/demo/app.css')}}">


 <link rel="stylesheet" type="text/css" href="{{asset('feditor_plugins/fb-insta-sdk/image-manager.css')}}?v=1502965890">

  <link rel="stylesheet" href="{{asset('feditor_plugins/themes/default/style.min.css')}}">


    <!-- Include Froala Editor styles -->
  {{-- <link rel="stylesheet" href="{{ asset('froala_editor_2.6.5/css/froala_editor.min.css')}}"/>
 --}}

  <link rel="stylesheet" href="{{ asset('froala_editor_2.6.5/css/froala_editor.css')}}"/>

  <link rel="stylesheet" href="{{ asset('froala_editor_2.6.5/css/froala_style.min.css')}}"/>

    <!-- Include Froala Editor Plugins styles -->
  <link rel="stylesheet" href="{{asset('froala_editor_2.6.5/css/plugins/char_counter.css')}}">
  <link rel="stylesheet" href="{{asset('froala_editor_2.6.5/css/plugins/code_view.css')}}">
  <link rel="stylesheet" href="{{asset('froala_editor_2.6.5/css/plugins/colors.css')}}">
  <link rel="stylesheet" href="{{asset('froala_editor_2.6.5/css/plugins/emoticons.css')}}">
  <link rel="stylesheet" href="{{asset('froala_editor_2.6.5/css/plugins/file.css')}}">
  <link rel="stylesheet" href="{{asset('froala_editor_2.6.5/css/plugins/fullscreen.css')}}">
  {{-- <link rel="stylesheet" href="{{asset('froala_editor_2.6.5/css/plugins/image_manager.css')}}"> --}}
  <link rel="stylesheet" href="{{asset('froala_editor_2.6.5/css/plugins/image_manager.css')}}">
  <link rel="stylesheet" href="{{asset('froala_editor_2.6.5/css/plugins/image.css')}}">
  <link rel="stylesheet" href="{{asset('froala_editor_2.6.5/css/plugins/line_breaker.css')}}">
  <link rel="stylesheet" href="{{asset('froala_editor_2.6.5/css/plugins/table.css')}}">
  <link rel="stylesheet" href="{{asset('froala_editor_2.6.5/css/plugins/video.css')}}">

    <!-- Include Froala Editor -->
  <script src="{{asset('froala_editor_2.6.5/js/froala_editor.min.js')}}"></script>




<script type="text/javascript" src="//cdnjs.cloudflare.com/ajax/libs/jquery/1.11.0/jquery.min.js"></script>
<script type="text/javascript" src="//cdnjs.cloudflare.com/ajax/libs/codemirror/5.3.0/codemirror.min.js"></script>
<script type="text/javascript" src="//cdnjs.cloudflare.com/ajax/libs/codemirror/5.3.0/mode/xml/xml.min.js"></script>
<script type="text/javascript" src="{{asset('feditor_plugins/fb-insta-sdk/fb-ig-sdk.js')}}?v=1502969984"></script>
<script type="text/javascript" src="{{asset('feditor_plugins/jstree.min.js')}}"></script>
<script type="text/javascript" src="{{asset('froala_editor_2.6.5/js/froala_editor.pkgd.min.js')}}"></script>
<script type="text/javascript" src="{{asset('froala_editor_2.6.5/js/third_party/image_aviary.min.js')}}?v=1502965890"></script>

    <!-- Include Froala Editor Plugins -->
  <script src="{{asset('froala_editor_2.6.5/js/plugins/align.min.js')}}"></script>
  <script src="{{asset('froala_editor_2.6.5/js/plugins/char_counter.min.js')}}"></script>
  <script src="{{asset('froala_editor_2.6.5/js/plugins/code_beautifier.min.js')}}"></script>
  <script src="{{asset('froala_editor_2.6.5/js/plugins/code_view.min.js')}}"></script>
  <script src="{{asset('froala_editor_2.6.5/js/plugins/colors.min.js')}}"></script>
  <script src="{{asset('froala_editor_2.6.5/js/plugins/emoticons.min.js')}}"></script>
  <script src="{{asset('froala_editor_2.6.5/js/plugins/entities.min.js')}}"></script>
  <script src="{{asset('froala_editor_2.6.5/js/plugins/file.min.js')}}"></script>
  <script src="{{asset('froala_editor_2.6.5/js/plugins/font_family.min.js')}}"></script>
  <script src="{{asset('froala_editor_2.6.5/js/plugins/font_size.min.js')}}"></script>
  <script src="{{asset('froala_editor_2.6.5/js/plugins/fullscreen.min.js')}}"></script>
  {{-- <script src="{{asset('froala_editor_2.6.5/js/plugins/image.js')}}"></script> --}}
  <script src="{{asset('froala_editor_2.6.5/js/plugins/image.min.js')}}"></script>


  {{-- <script src="{{asset('froala_editor_2.6.5/js/plugins/image_manager.js')}}"></script> --}}

  <script src="{{asset('froala_editor_2.6.5/js/plugins/image_manager.min.js')}}"></script>
  
  <script src="{{asset('froala_editor_2.6.5/js/plugins/inline_style.min.js')}}"></script>
  <script src="{{asset('froala_editor_2.6.5/js/plugins/line_breaker.min.js')}}"></script>
  <script src="{{asset('froala_editor_2.6.5/js/plugins/link.min.js')}}"></script>
  <script src="{{asset('froala_editor_2.6.5/js/plugins/lists.min.js')}}"></script>
  <script src="{{asset('froala_editor_2.6.5/js/plugins/paragraph_format.min.js')}}"></script>
  <script src="{{asset('froala_editor_2.6.5/js/plugins/paragraph_style.min.js')}}"></script>
  <script src="{{asset('froala_editor_2.6.5/js/plugins/quote.min.js')}}"></script>
  <script src="{{asset('froala_editor_2.6.5/js/plugins/save.min.js')}}"></script>
  <script src="{{asset('froala_editor_2.6.5/js/plugins/table.min.js')}}"></script>
  <script type="text/javascript" src="{{asset('froala_editor_2.6.5/js/plugins/url.min.js')}}"></script>
  <script src="{{asset('froala_editor_2.6.5/js/plugins/video.min.js')}}"></script>

    <!-- End Froala -->

  {{-- <script src="{{asset('angular-froala/bower_components/angular/angular.min.js')}}"></script> --}}