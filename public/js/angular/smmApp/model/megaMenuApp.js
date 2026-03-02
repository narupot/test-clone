(function(){
  /*******This module used for megamenu in sabina 
  *Created at 10/11/2017
  *Author : Smoothgraph Connect PVT. LTD
  *******/
  "use strict"
  //'ui.bootstrap',
  angular.module('smm-app',['ui.tree','jsonseivice','froala'],function($interpolateProvider){
      $interpolateProvider.startSymbol('<%');
      $interpolateProvider.endSymbol('%>');
  }).filter('trustAsResourceUrl',['$sce',function($sce) {
      return function(val) {
        return $sce.trustAsResourceUrl(val);
      }
  }]).filter('to_trusted', ['$sce', function($sce){
      return function(text) {
          return $sce.trustAsHtml(text);
      };
  }]).value('froalaConfig', {
    toolbarInline: false,
    //for toolbar
    toolbarButtons :['bold', 'italic', 'underline', 'paragraphFormat', 'formatOL', 'formatUL', 'insertHTML', 'undo', 'redo', 'html','insertImage'],
    
    //enter: $.FroalaEditor.ENTER_BR,
    //Folder Path   
    placeholderText: 'Edit Your Content Here!',  
    //The list of allowed attributes to be used for tags.
    htmlAllowedAttrs : ['accept', 'accept-charset', 'accesskey', 'action', 'align', 'allowfullscreen', 'allowtransparency', 'alt', 'async', 'autocomplete', 'autofocus', 'autoplay', 'autosave', 'background', 'bgcolor', 'border', 'charset', 'cellpadding', 'cellspacing', 'checked', 'cite', 'class', 'color', 'cols', 'colspan', 'content', 'contenteditable', 'contextmenu', 'controls', 'coords', 'data', 'data-.*', 'datetime', 'default', 'defer', 'dir', 'dirname', 'disabled', 'download', 'draggable', 'dropzone', 'enctype', 'for', 'form', 'formaction', 'frameborder', 'headers', 'height', 'hidden', 'high', 'href', 'hreflang', 'http-equiv', 'icon', 'id', 'ismap', 'itemprop', 'keytype', 'kind', 'label', 'lang', 'language', 'list', 'loop', 'low', 'max', 'maxlength', 'media', 'method', 'min', 'mozallowfullscreen', 'multiple', 'muted', 'name', 'novalidate', 'open', 'optimum', 'pattern', 'ping', 'placeholder', 'playsinline', 'poster', 'preload', 'pubdate', 'radiogroup', 'readonly', 'rel', 'required', 'reversed', 'rows', 'rowspan', 'sandbox', 'scope', 'scoped', 'scrolling', 'seamless', 'selected', 'shape', 'size', 'sizes', 'span', 'src', 'srcdoc', 'srclang', 'srcset', 'start', 'step', 'summary', 'spellcheck', 'style', 'tabindex', 'target', 'title', 'type', 'translate', 'usemap', 'value', 'valign', 'webkitallowfullscreen', 'width', 'wrap','sample'],
  });


}).call(this);