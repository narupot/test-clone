<?php

namespace App\Helpers;

use Illuminate\Support\Facades\DB;
use App\Category;
use App\MegaMenu;
use Auth;
use Session;

class LayoutHtmlHelpers {

    public static function getHeaderMenu(){
       $menu= new MegaMenu;
       $menuJson=$menu->select('*')->where('block_name','Header menu')->where('is_default_block','1')->orderBy('id','DESC')->first();
       // echo "<pre>";
       // print_r(json_decode($menuJson));
       // dd($menuJson);
       /*foreach($menuJson as $data){
         $menuRes=$data->menu_json;
       }*/
      return $menuJson;
    }

    public static function getFooterMenu(){
       $menu= new MegaMenu;
       $menuJson=$menu->select('*')->where('block_name','footer')->orderBy('id','DESC')->first();
       /*foreach($menuJson as $data){
         $menuRes=$data->menu_json;
       }*/
      return $menuJson;
    }

    public static function getChildCategory($parent_id){
        $child=array();
        $results = Category::where([['parent_id', $parent_id], ['status', '1']])->limit(50)->get();
        foreach($results as $result){
          $child[]=$result;
        }
        return $child; 
    }

    public static function getCatgoriesMenuList($cat_id=null){
      $langCode=session('lang_code');
       if($cat_id>0){
         $results=Category::where([['parent_id', '0'], ['status', '1'],['id', $cat_id]])->limit(150)->get();
        }else{
          $results=Category::where([['parent_id', '0'], ['status', '1']])->limit(150)->get();
        }
                    
        $html='';
        foreach($results as $result){
          $childCat=self::getChildCategory($result->id);
          $catName = isset($result->CategoryDesc->category_name)?$result->CategoryDesc->category_name:'';
          $html.=' <li class="item level1 parent">
                      <a class="menu-link" href="'.$langCode.'/category/'.$result->url.'"><span>'.$catName.'</span>';
          if(count($childCat)>0){
            $html.=' <i class="glyphicon glyphicon-menu-right"></i>';
          }
          $html.='</a>';
          if(count($childCat)>0){ 
          $html.='<ul class="level1 groupmenu-drop">';
                  foreach($childCat as $childresult){
                  $catNameChild = isset($childresult->CategoryDesc->category_name)?$childresult->CategoryDesc->category_name:'';
                   $childCat2=self::getChildCategory($childresult->id);
                   $html.='<li class="item level2 nav-2-1 first "><a class="menu-link" href="'.$langCode.'/category/'.$childresult->url.'"><span>'.$catNameChild.'</span>';
                    if(!empty($childCat2)){
                        $html.='<i class="glyphicon glyphicon-menu-right"></i>';
                    }
                    $html.='</a>';
                      if(!empty($childCat2)){
                        $html.='<ul class="level1 groupmenu-drop">';
                        foreach($childCat2 as $childresult2){
                        $catNameChild2 = isset($childresult2->CategoryDesc->category_name)?$childresult2->CategoryDesc->category_name:'';
                         $html.='<li class="item level2 nav-2-1 first"><a class="menu-link" href="'.$langCode.'/category/'.$childresult2->url.'"><span>'.$catNameChild2.'</span></a>';
                         $html.='</li>';
                        }
                        $html.='</ul>';
                      }
                   $html.='</li>';
                  }
          $html.='</ul>';

          }
          $html.=' </li>';
        }
        return $html;
    }

    public static function getTemplateChild($childCat,$id){
        $langCode=session('lang_code');
        $html='<ul class="level1 groupmenu-drop">';
                  foreach($childCat as $childresult){
                  $catNameChild = isset($childresult->CategoryDesc->category_name)?$childresult->CategoryDesc->category_name:'';
                   $html.='<li class="item level2 nav-2-1 first"><a class="menu-link" href="'.$langCode.'/'.$childresult->url.'"><span>'.$catNameChild.'</span></a>';
                   $html.='</li>';
                  }
        $html.='</ul>';
        return $html;
    }

    public static function getCatgoriesMenu($cat_id=0) {
      
      $default_lang_code = '';

        //$default_lang_code = session('lang_code');
      // $catUrl = action('ProductsController@category');
       $html = '<div class="megaNavigation">';
       $html.= '<a href="" class="mobileMaxmenu"><span></span><span></span><span></span></a><ul>';
       $results = Category::where([['parent_id', '0'], ['status', '1']])->limit(50)->get();

       //categorydesc, category
       foreach($results as $result){
          //dd($result->CategoryDesc->category_name);
          $catName = isset($result->CategoryDesc->category_name)?$result->CategoryDesc->category_name:'';
           //dd($catName);
          $html.='<li><a href="'.action('ProductsController@category', $result->url).'">'.$catName.'<i class="fa fa-angle-down" aria-hidden="true"></i></a>';

          if(isset($result->category) && count($result->category)>0){
             $html.='<div class="bxMenu"><div class="insidebxmenu">
                       <div class="bxlinkmenu-left clearfix">';
             $html.='<div class="catlisting"><div class="listing-item"><ul>';
             foreach($result->category as $subcat){
                $subcatName = isset($subcat->CategoryDesc->category_name)?$subcat->CategoryDesc->category_name:'';
                $html.='<li><a href="'.action('ProductsController@category', $subcat->url).'">'.$subcatName.'</a></li>';

             }
             $html.= '</ul></div></div>';
             $html.= '</div></div>';             
          }
         $html.='</li>';
       }
       $html .='</ul></div>';
       return $html;
    } 
    
    public static function getSocialLinks(){ 
        $social_data = \App\SystemConfig::whereIn('system_name',['FACEBOOK_URL','TWITTER_URL','GOOGLE_PLUS_URL','LINKEDIN_URL','PINTEREST_URL','INSTAGRAM_URL','YOUTUBE_URL'])->get();

        //dd($social_data);
        //return $social_data;
        $system_val_data = array_filter(array_column($social_data->toArray(),'system_val'));
        $html = '';
        if(count($system_val_data)){
            $html = '<div class="box-social">
            
            <ul class="footer-social">';
            foreach($social_data as $unit_data){
                if($unit_data->system_name=='FACEBOOK_URL' && !empty($unit_data->system_val)){
                    $html.='<li><a href="'.$unit_data->system_val.'" title="Facebook" target="_blank"><i class="fab fa-facebook-f" aria-hidden="true"></i></a></li>';    
                }
                if($unit_data->system_name=='TWITTER_URL' && !empty($unit_data->system_val)){
                    $html.='<li><a href="'.$unit_data->system_val.'" title="Twitter" target="_blank"><i class="fab fa-twitter" aria-hidden="true"></i></a></li>';
                }
                if($unit_data->system_name=='GOOGLE_PLUS_URL' && !empty($unit_data->system_val)){
                    $html.='<li><a href="'.$unit_data->system_val.'" title="Google-plus" target="_blank"><i class="fab fa-google-plus-g" aria-hidden="true"></i></a></li>';
                }
                if($unit_data->system_name=='LINKEDIN_URL' && !empty($unit_data->system_val)){
                    $html.='<li><a href="'.$unit_data->system_val.'" title="Pinterest" target="_blank"><i class="fab fa-linkedin-in" aria-hidden="true"></i></a></li>';
                }
                if($unit_data->system_name=='PINTEREST_URL' && !empty($unit_data->system_val)){
                    $html.='<li><a href="'.$unit_data->system_val.'" title="Pinterest" target="_blank"><i class="fab fa-pinterest" aria-hidden="true"></i></a></li>';
                }
                if($unit_data->system_name=='INSTAGRAM_URL' && !empty($unit_data->system_val)){
                    $html.='<li><a href="'.$unit_data->system_val.'" title="Instagram" target="_blank"><i class="fab fa-instagram" aria-hidden="true"></i></a></li>';
                }
                if($unit_data->system_name=='YOUTUBE_URL' && !empty($unit_data->system_val)){
                    $html.='<li><a href="'.$unit_data->system_val.'" title="Youtube" target="_blank"><i class="fab fa-youtube" aria-hidden="true"></i></a></li>';
                }
                
            }
            $html.='</ul></div>';            
        }
        return $html;
    }

    public static function getBargainPop(){
        
        //$result = \App\MongoProduct::where('_id', $product_id)->first();
        $html ='<div class="modal" id="changePriceModel2">
                    <div class="modal-dialog modal-sm modal-dialog-centered">
                      <div class="modal-content">
                         
                       </div>
                    </div>
              </div><script>
                    jQuery(\'body\').on(\'click\', \'a.bargain\',function(e){ e.preventDefault();
                        var currCap = jQuery(this);
                        var proId = currCap.attr(\'rel\');
                        var qty = currCap.attr(\'qty\');
                        var orderQty = jQuery(\'#orderQty\').val();
                        if(orderQty === undefined){
                            orderQty = 1;
                        }
                        if(qty){
                          orderQty = qty;
                        }
                        var ajax_url = addProductToBargain+\'/\'+proId+\'/\'+orderQty;
                        callAjaxFormRequest(ajax_url, \'get\', \'\', function(response){ 
                            if(response.status == \'fail\'){   
                               swal(\'\', response.msg,\'warning\');
                            }else{
                              jQuery(\'#changePriceModel2\').modal(\'show\').find(\'.modal-content\').load(currCap.attr(\'href\'));

                            }  
                        });    
                    });
              </script>';

        return $html;             
    }

    public static function AddSellerProductQtyPop(){
        
        //$result = \App\MongoProduct::where('_id', $product_id)->first();
        $html ='<div class="modal" id="changePriceModel">
                    <div class="modal-dialog modal-sm modal-dialog-centered">
                      <div class="modal-content">
                         
                       </div>
                    </div>
              </div><script>
                    $(\'body\').on(\'click\', \'a.changePriceModel\',function(e){ e.preventDefault();
                        var currCap = $(this);
                        $(\'#changePriceModel\').modal(\'show\').find(\'.modal-content\').load(currCap.attr(\'href\'));  
                    });
              </script>';

        return $html;             
    }


}
