<div class="attr-mgt-view">
	<h2 class="title-prod">SEO <span class="title-links d-none"><a>Guide</a></span></h2>
    <div class="form-group">
	{!! CustomHelpers::fieldstabWithLanuage(
		[
		['field'=>'text', 'name'=>'product.seo.meta_title', 'label'=>Lang::get('product.meta_title'), 'cssClass'=>''], 
		['field'=>'text', 'name'=>'product.seo.meta_keyword', 'label'=>Lang::get('product.meta_keyword'), 'cssClass'=>''], 
		['field'=>'textarea', 'name'=>'product.seo.meta_description', 'label'=>Lang::get('product.meta_description'), 'cssClass'=>'froala']], 
		'4')!!} 
	</div>              
</div>