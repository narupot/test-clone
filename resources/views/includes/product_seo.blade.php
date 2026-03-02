<div class="form-row">
	<ul class="tab-list">
		<li ng-repeat="lang in activalangs" ng-class="lang.id =={{session('default_lang')}} ? 'active': ''">
			<a data-toggle="tab" href="#langid<%lang.id%>" ng-attr-aria-expanded="lang.id =={{session('default_lang')}} ? true : false">										
			<img ng-src="{{Config::get('constants.language_url')}}<%lang.languageFlag%>" alt="">
			</a>
		</li>
		<li class="lang-label">@lang('product.choose_language')</li>
	</ul>
	<div class="tab-content">
	  <div ng-repeat="lang in activalangs" id="langid<%lang.id%>" class="tab-pane fade in" ng-class="lang.id =={{session('default_lang')}} ? 'active' : ''">
	  	<div class="form-row">
		    <label> @lang('product.meta_title')</label>
		    <input name="metatitle[<%lang.id%>]" ng-model="metatitle[lang.id]" placeholder="@lang('product.meta_title')" type="text">
	    </div>	

	  	<div class="form-row">
		    <label>@lang('product.meta_keywords')</label>
		    <input name="metakeyword[<%lang.id%>]" ng-model="metakeyword[lang.id]" placeholder="@lang('product.meta_keywords')" type="text" >
	    </div>
	    <div class="form-row">
	    	<label>@lang('product.meta_description')</label>
	   		<textarea name="metadesc[<%lang.id%>]" ng-model="metadesc[lang.id]" cssClass='texteditor1' ui-tinymce='tinymceOptions'></textarea>
	   	</div>	      
	  </div>
	</div>
</div>    