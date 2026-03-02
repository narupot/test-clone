<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\MarketPlace;

use App\StoreLocation;

class StaticPageController extends MarketPlace
{
    public function index() {

      return view(loadFrontTheme('home'),['page'=>$page]);
    }

    public function pagedata(Request $request,$urlkey) {
        $data = \App\StaticPage::where(['status'=>'1', 'url'=>$urlkey])->with('staticPageDesc')->first();
        if(!empty($data)){
            $preview = !empty($request->preview)?'yes':'no';
            return view(loadFrontTheme('staticPages.page'),['data'=>$data,'preview'=>$preview]); 
        }else{
            abort(404);
        } 
    }   

    public function termsAndConditions(Request $request){
        $terms_and_conditions_data = \App\StaticPage::where(['status'=>'1', 'url'=>'terms-and-condition'])->with('staticPageDesc')->first();

        //echo "<pre>"; print_r($terms_and_conditions_data); die;
        return view(loadFrontTheme('staticPages.termsAndConditions'),['data'=>$terms_and_conditions_data]);
    }  


}
