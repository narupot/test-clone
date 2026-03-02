  <script>
    window.userFolderDefaultPath = "{{Config::get('constants.froala_img_path').md5('sdfsdfs').'/'}}";

    froalaloadimages_url = "{{action('FroalaEditorController@froalaLoadImages')}}";
    froaladeletefolder_url = "{{action('FroalaEditorController@froalaDeleteFolder')}}";
    froalaupload_url = "{{action('FroalaEditorController@uploadImage')}}";
    froalaNewFolder_url = "{{action('FroalaEditorController@froalaNewFolder')}}";
    var csrftoken = "{{csrf_token()}}";
  </script>
  <script id="fr-fek">try{(function (k){localStorage.FEK=k;t=document.getElementById('fr-fek');t.parentNode.removeChild(t);})('3Xa1TEWUf1d1QSDb1HAc1==')}catch(e){}
  </script>
  
<!-- Include Froala Editor CSS -->
<!-- Include Font Awesome. -->
<link href="{{asset('froala_editor/css/font-awesome/css/font-awesome.min.css')}}" rel="stylesheet" type="text/css" />
<link href="{{asset('froala_editor/css/froala_editor.min.css')}}" rel="stylesheet" type="text/css" />
<link href="{{asset('froala_editor/css/froala_style.min.css')}}" rel="stylesheet" type="text/css" />

<!-- Start Froala Editor Plugins styles -->
  <link rel="stylesheet" href="{{asset('froala_editor/css/plugins/char_counter.min.css')}}" type="text/css" />
  <link rel="stylesheet" href="{{asset('froala_editor/css/plugins/colors.min.css')}}" type="text/css" />
  <link rel="stylesheet" href="{{asset('froala_editor/css/plugins/emoticons.min.css')}}" type="text/css" />
  <link rel="stylesheet" href="{{asset('froala_editor/css/plugins/file.min.css')}}" type="text/css" />
  <link rel="stylesheet" href="{{asset('froala_editor/css/plugins/fullscreen.min.css')}}" type="text/css" />
  <link rel="stylesheet" href="{{asset('froala_editor/css/plugins/image_manager.min.css')}}" type="text/css" />
  <link rel="stylesheet" href="{{asset('froala_editor/css/plugins/image.min.css')}}" type="text/css" />
  <link rel="stylesheet" href="{{asset('froala_editor/css/plugins/line_breaker.min.css')}}" type="text/css" />
  <link rel="stylesheet" href="{{asset('froala_editor/css/plugins/table.min.css')}}" type="text/css" />
  <link rel="stylesheet" href="{{asset('froala_editor/css/plugins/video.min.css')}}" type="text/css" />

  <!-- Include TUI CSS. -->
  <!-- <link rel="stylesheet" href="{{asset('froala_editor/css/color-picker/tui-image-editor.css')}}" type="text/css" />
  <link rel="stylesheet" href="{{asset('froala_editor/css/color-picker/tui-color-picker.css')}}" type="text/css" /> -->
  <link rel="stylesheet" href="{{asset('froala_editor/css/color-picker/image_tui.min.css')}}" type="text/css" />

  <!-- Code Mirror Plugin CSS -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.3.0/codemirror.min.css">
  <link rel="stylesheet" href="{{asset('froala_editor/css/plugins/code_view.min.css')}}" type="text/css" />

<!-- End Froala Editor Plugins styles -->

  <script src="{{asset('froala_editor/js/froala_editor.min.js')}}"></script>

<!-- Start Froala Editor Plugins JS -->

  <!-- Code Mirror Plugin JS -->
  <script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.3.0/codemirror.min.js"></script>
  <script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.3.0/mode/xml/xml.min.js"></script>

  <script src="{{asset('froala_editor/js/plugins/code_beautifier.min.js')}}"></script>
  <script src="{{asset('froala_editor/js/plugins/code_view.min.js')}}"></script>

  <script src="{{asset('froala_editor/js/plugins/align.min.js')}}"></script>
  <script src="{{asset('froala_editor/js/plugins/char_counter.min.js')}}"></script>
  <script src="{{asset('froala_editor/js/plugins/colors.min.js')}}"></script>
  <script src="{{asset('froala_editor/js/plugins/emoticons.min.js')}}"></script>
  <script src="{{asset('froala_editor/js/plugins/entities.min.js')}}"></script>
  <script src="{{asset('froala_editor/js/plugins/file.min.js')}}"></script>
  <script src="{{asset('froala_editor/js/plugins/font_family.min.js')}}"></script>
  <script src="{{asset('froala_editor/js/plugins/font_size.min.js')}}"></script>
  <script src="{{asset('froala_editor/js/plugins/fullscreen.min.js')}}"></script>
  <script src="{{asset('froala_editor/js/plugins/inline_style.min.js')}}"></script>
  <script src="{{asset('froala_editor/js/plugins/line_breaker.min.js')}}"></script>
  <script src="{{asset('froala_editor/js/plugins/link.min.js')}}"></script>
  <script src="{{asset('froala_editor/js/plugins/lists.min.js')}}"></script>
  <script src="{{asset('froala_editor/js/plugins/paragraph_format.min.js')}}"></script>
  <script src="{{asset('froala_editor/js/plugins/paragraph_style.min.js')}}"></script>
  <script src="{{asset('froala_editor/js/plugins/quote.min.js')}}"></script>
  <script src="{{asset('froala_editor/js/plugins/save.min.js')}}"></script>
  <script src="{{asset('froala_editor/js/plugins/table.min.js')}}"></script>
  <script src="{{asset('froala_editor/js/plugins/video.min.js')}}"></script>
  <script src="{{asset('froala_editor/js/plugins/image.min.js')}}"></script>
  <script src="{{asset('froala_editor/js/plugins/image_manager.min.js')}}"></script>
  <script src="{{asset('froala_editor/js/plugins/inline_class.min.js')}}"></script>

<!--   <script src="{{asset('froala_editor/js/third_party/image_tui.min.js')}}"></script>
  <script src="{{asset('froala_editor/js/third_party/font_awesome.min.js')}}"></script> -->

  <!-- Include TUI JS. -->
  <!-- <script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/fabric.js/1.6.7/fabric.min.js"></script>
  <script type="text/javascript" src="https://cdn.jsdelivr.net/npm/tui-code-snippet@1.4.0/dist/tui-code-snippet.min.js"></script>
  <script type="text/javascript" src="https://cdn.jsdelivr.net/npm/tui-image-editor@3.2.2/dist/tui-image-editor.min.js"></script> -->

<!-- End Froala Editor Plugins JS -->