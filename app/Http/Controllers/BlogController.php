<?php
namespace App\Http\Controllers;
use Illuminate\Http\Request;
use App\Http\Requests;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\MarketPlace;
use Illuminate\Support\Facades\Auth;
use Session;
use Route;
use Cache;
use App\Helpers\GeneralFunctions;
use Lang;
use Config;
use App\Blog;
use App\BlogDesc;
use App\BlogCat;
use App\BlogTag;
use App\BlogSlider;
use App\BlogCategory;
use App\BlogCategoryDesc;
use App\BlogRelated;
use App\BlogPin;
use App\AdminUser;
use App\BlogTagList;
use App\BlogRecentView;

class BlogController extends MarketPlace
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {

      $this->siteurl = Config::get('constants.base_url'); 
      //Get Blog Pagination Config 
      $blogPerPage = $this->getBlogConfigValue($system_name="SHOW_PAGINATION_PER_PAGE");
      if(!empty($blogPerPage)){
        $this->pageItem=$blogPerPage;
      }else{
        $this->pageItem=10;  
      }                  

      $this->langCode='';
    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request) {      

    }

    public function blog(Request $request){
      $page = 'blog';  
      $url=$this->siteurl;
      $page_dtl = Blog::where(['publish'=>'1','status'=>'1'])->orderBy('created_at', 'desc')->with(['blogDesc','hasPin'])->paginate($this->pageItem); 
       
      $paginateLinks=$page_dtl->links();
       
      $data_arr=array();
      if(count($page_dtl) > 0) {

            foreach ($page_dtl as $key => $value) {
                
                $array_temp['img']='';
                $array_temp['id'] = $value->id;
                $array_temp['url'] = $value->url;                
                $array_temp['image'] = $value->feature_image;

                if(isset(Auth::user()->id)){
                  $isActivePin = \App\BlogPin::where(['blog_id'=>$value->id,'user_id'=>Auth::user()->id])->count();
                  $array_temp['hasPin'] = ($isActivePin>0)?1:0;
                }else{
                  $array_temp['hasPin'] = 0;
                }
                
                $status = 'Inactive';
                if($value->status == '1') {
                    $status = 'Active';
                }

                $array_temp['status'] = $status;

                $array_temp['title'] = (!empty($value->blogDesc))?$value->blogDesc->blog_title:'';
                $array_temp['short_desc'] = (!empty($value->blogDesc))?$value->blogDesc->blog_short_desc:'';              
                $array_temp['desc'] = (!empty($value->blogDesc)) ? $value->blogDesc->blog_desc : '';       
                preg_match_all('/<img[^>]+>/i',$array_temp['desc'], $result); 
                $img='';
                if(!empty($result)){
                  if(!empty($result[0])){
                    $img=$result[0][0];
                    $array_temp['img'] = $img;       
                  }
                }
                $array_temp['desc']=str_replace($img,'',$array_temp['desc']);       
                $array_temp['created_at'] = date('d F, Y',strtotime(getDateFormat($value->created_at, '1')));
                //Get All Category of this blog
                $categoryArr=array();
                $categoryList=BlogCat::blogCatById($value->id);
                if(!empty($categoryList)){
                  foreach($categoryList as $categoryObj){
                      if(!empty($categoryObj->getBlogCategoryDescription)){
                        $categoryArr[]=array(
                          'id'=>$categoryObj->getBlogCategoryDescription->id,
                          'name'=>$categoryObj->getBlogCategoryDescription->name);
                      }
                    } 
                  }
                //Get All Tag
                $tagIdList=BlogTag::where('blog_id',$value->id)->pluck('tag_id');
                $tagList = \App\BlogTagList::whereIn('id',$tagIdList)->pluck('tag_title');

                //Get blog added user name                                
                $userArr = AdminUser::getAdminDetail($value->created_by);                  

                $array_temp['tags'] = $tagList;
                $array_temp['user'] = $userArr->nick_name;  
                $array_temp['userid'] = $userArr->id;                  
                $array_temp['category'] = $categoryArr;
                $array_temp['updated_at'] = getDateFormat($value->updated_at, '1');
                $data_arr[] = $array_temp;
            }            

        }
        //Get Archive List
        $Arr=$this->getArchiveList($url); 
        //Get all category list
        $allCategory=BlogCategory::getMainCategory();
        //Get Feature blog List
        //Get Blog Feature Config 
        $blogFeatureConfig = $this->getBlogConfigValue($system_name="FEATURE_BLOG");
        $featureBlogs = \App\Blog::getFeaturedBlog($blogFeatureConfig);
        //Get Latest Featured Blog
        $latestFeatureBlog = \App\Blog::getLatestFeaturedBlog();
        //Get Recent blog List                
        //Get Blog Recent Config 
        $blogRecentConfig = $this->getBlogConfigValue($system_name="RECENT_VIEW_BLOG");
        $recentBlogs = Blog::orderBy('created_at', 'desc')->take($blogRecentConfig)->get();
        //Get All Tags List
        $getAllTagList = \App\BlogTagList::orderBy('created_at', 'desc')->get();
        //Get Admin Setting 
        $blogConfig = $this->getBlogConfigValue($system_name="BLOG_PAGINATION"); 
        //Get Blog Global Date Config 
        $blogDATE = $this->getBlogConfigValue($system_name="DATE_FORMAT");
        $blogTIME = $this->getBlogConfigValue($system_name="TIME_FORMAT");
        $blogDATE_FORMAT = $blogDATE." ".$blogTIME;
        //Get Wishlist List
        $loginUserId = Auth::id();
        if(!empty($loginUserId)){
          $isActiveblog_id = \App\BlogPin::where(['user_id'=>Auth::user()->id])->pluck('blog_id');
          $wishlist = Blog::whereIn('id',$isActiveblog_id)->get();          
        }else{
          $wishlist ='';
        }
        

        if ($request->ajax()) {    
          return view(loadFrontTheme('blog.blogdata'),['page'=>$page,'page_dtls'=>$data_arr,'url'=>$url,'paginateLinks'=>$paginateLinks,'page_dtl'=>$page_dtl,'allCategory'=>$allCategory,'featuredBlog'=>$featureBlogs,'next'=>$Arr['next'],'previous'=>$Arr['previous'],'archiveArr'=>$Arr['archiveArr'],'recentBlog'=>$recentBlogs,'getAllTagList'=>$getAllTagList,'latestFeatureBlog'=>$latestFeatureBlog,'blogConfig'=>$blogConfig,'blogDATE_FORMAT'=>$blogDATE_FORMAT,'wishlist'=>$wishlist]);
        }else{      
          return view(loadFrontTheme('blog.blog'),['page'=>$page,'page_dtls'=>$data_arr,'url'=>$url,'paginateLinks'=>$paginateLinks,'page_dtl'=>$page_dtl,'allCategory'=>$allCategory,'featuredBlog'=>$featureBlogs,'next'=>$Arr['next'],'previous'=>$Arr['previous'],'archiveArr'=>$Arr['archiveArr'],'recentBlog'=>$recentBlogs,'getAllTagList'=>$getAllTagList,'latestFeatureBlog'=>$latestFeatureBlog,'blogConfig'=>$blogConfig,'blogDATE_FORMAT'=>$blogDATE_FORMAT,'wishlist'=>$wishlist]);
          }
    }



    public function blogpin(Request $request){
        
        if($request->blog_id){
        
            if($request->pinIn=='false'){
                // $blog_data = BlogPin::where(['blog_id'=>$request->blog_id,'user_id'=>Auth::id()])->get();
                // if(count($blog_data)){
                //   return ['status'=>'unsuccess','message'=>'Blog is already in your wishlist'];
                // }

                $blogPinObj = new BlogPin;
                $blogPinObj->blog_id = $request->blog_id;
                $blogPinObj->user_id = Auth::id();
                $blogPinObj->save();
                $return = ['status'=>'success','message'=>'Blog pinned successfully.'];
            }else{
                BlogPin::where(['blog_id'=>$request->blog_id,'user_id'=>Auth::id()])->delete();
                $return = ['status'=>'success','message'=>'Blog unpinned successfully.'];
            }
        }else{
            $return = ['status'=>'unsuccess','message'=>'Something went wrong.'];
        }
        return $return;
    }

    public function blogDetails(Request $request){

      $page = 'blogDetails';
      $blog_image_base_path = Config::get('constants.base_path');
      $url=$request->url;
      $page_dtl = Blog::where(['url'=>$url,'status'=>'1'])->with(['blogDesc'])->first();
      $blog_image_base_path = Config::get('constants.public_url');
      //Insert recent view blog id      
      $ip_address = $request->ip();
      if(!empty($ip_address) && !empty($page_dtl->id)){
        $recentViewBlog = \App\BlogRecentView::updateRecentViewBlog($page_dtl->id,$ip_address);
      }
      $data_arr=array();
      $tagOfBlog=array();
      
      if($page_dtl!==null) {
       
                $array_temp['img']='';
                $array_temp['id'] = $page_dtl->id;
                $array_temp['url'] = $page_dtl->url;
                $array_temp['image'] = $page_dtl->feature_image;

                $status = 'Inactive';
                if($page_dtl->status == '1') {
                    $status = 'Active';
                }

                $array_temp['status'] = $status;
                $array_temp['title'] = $page_dtl->blogDesc->blog_title;
                $array_temp['short_desc'] = $page_dtl->blogDesc->short_desc;
                $array_temp['desc'] = $page_dtl->blogDesc->blog_desc;

                $img='';
                preg_match_all('/<img[^>]+>/i',$page_dtl->blogDesc->blog_desc, $result);
               
                if(!empty($result)){
                  $imgTag = array();
                  foreach($result as $img){
                    $imgStr=$img;
                    $temp = $imgStr;       
                  }
                   $array_temp['img'] = $temp;  
                }
                
                /**Image for seo*/
                $outImageUrlforSeo = '';
                if(isset($page_dtl->metaimage) && !empty($page_dtl->metaimage)){
                    $outImageUrlforSeo = $blog_image_base_path."files/blog/socialshare_image/".$page_dtl->metaimage;
                }                

                //Get Blog Category  
                $categoryArr=array();
                $categoryList=BlogCat::blogCatById($page_dtl->id);
                
                if(!empty($categoryList)){
                  foreach($categoryList as $categoryObj){
                      if(!empty($categoryObj->getBlogCategoryDescription)){
                        $categoryArr[]=array(
                          'id'=>$categoryObj->getBlogCategoryDescription->id,
                          'name'=>$categoryObj->getBlogCategoryDescription->name,
                          'url'=>$categoryObj->url);
                      }
                    }
                  }
                $array_temp['category'] = $categoryArr;
                //Get Tag List
                $tagIdList=BlogTag::where('blog_id',$page_dtl->id)->pluck('tag_id');
                $tagList = \App\BlogTagList::whereIn('id',$tagIdList)->pluck('tag_title');
                
                //$array_temp['short_desc']=str_replace($img,'',$page_dtl->blogDesc->short_desc);
                //$array_temp['desc']=str_replace($img,'',$page_dtl->blogDesc->blog_desc);
                $array_temp['meta_title']=str_replace($img,'',$page_dtl->blogDesc->meta_title);
                $array_temp['meta_keyword']=str_replace($img,'',$page_dtl->blogDesc->meta_keyword);    
                $array_temp['meta_description']=str_replace($img,'',$page_dtl->blogDesc->meta_desc);
                $array_temp['prd_src']= isset($outImageUrlforSeo)?$outImageUrlforSeo:''; 
                $array_temp['template_type']='3'; 
                $array_temp['comment']=$page_dtl->comment;       
                $array_temp['features']=$page_dtl->features;       
                $array_temp['created_at'] = date('d F, Y',strtotime(getDateFormat($page_dtl->created_at, '1')));
                $array_temp['updated_at'] = getDateFormat($page_dtl->updated_at, '1');
               
                $data_arr = $array_temp;

                //Get All Category List
                $allCategory=BlogCategory::getMainCategory();            
                //Get Archive List
                $Arr=$this->getArchiveList($url);
                $blogCatId = \App\BlogCat::where('blog_id',$page_dtl->id)->pluck('cat_id')->first();
                //Get Blog Feature Config 
                $blogFeatureConfig = $this->getBlogConfigValue($system_name="FEATURE_BLOG");
                $featureBlogs = \App\Blog::getFeaturedBlog($blogFeatureConfig);
                //Get Related Post    
                //Get Blog Related Config 
                $blogRelatedConfig = $this->getBlogConfigValue($system_name="RELATED_BLOG");
                $relatedBlogIds = \App\BlogRelated::where('blog_id',$page_dtl->id)->take($blogRelatedConfig)->pluck('related_id');
                if(!empty($relatedBlogIds)){
                  $relatedBlogArr = array();
                  $relatedBlogtagList = array();
                  foreach($relatedBlogIds as $relatedBlogId){
                    $relatedBlogArrData = Blog::where(['id'=>$relatedBlogId,'status'=>'1'])->with(['blogDesc'])->first();
                    if(!empty($relatedBlogArrData)){ $relatedBlogArr[] = $relatedBlogArrData; }
                    $relatedBlogtagIdList=BlogTag::where('blog_id',$relatedBlogId)->pluck('tag_id');
                    $relatedBlogtagListData = \App\BlogTagList::whereIn('id',$relatedBlogtagIdList)->pluck('tag_title');
                    if(!empty($relatedBlogtagListData)){ $relatedBlogtagList[] = $relatedBlogtagListData; }
                    }
                }  

                //Get Recent View Blogs           
                //Get Blog Recent Config 
                $blogRecentConfig = $this->getBlogConfigValue($system_name="RECENT_VIEW_BLOG");         
                $recentViewBlogIds = BlogRecentView::where(['ip_address'=>$ip_address])->orderBy('updated_at', 'desc')->take($blogRecentConfig)->pluck('blog_id'); 
                if(!empty($recentViewBlogIds)){
                  $recentViewBlogArr = array();
                  $recentViewBlogTagList = array();
                  foreach($recentViewBlogIds as $recentViewBlogId){
                    $recentViewBlogData = Blog::where(['id'=>$recentViewBlogId,'status'=>'1'])->with(['blogDesc'])->first();
                    if(!empty($recentViewBlogData)){ $recentViewBlogArr[] = $recentViewBlogData; }
                    $recentBlogtagIdList=BlogTag::where('blog_id',$recentViewBlogId)->pluck('tag_id');
                    $recentBlogtagListData = \App\BlogTagList::whereIn('id',$recentBlogtagIdList)->pluck('tag_title');
                    if(!empty($recentBlogtagListData)){ $recentViewBlogTagList[] = $recentBlogtagListData; }
                    }
                }
                              
                //Get blog added user name                                
                $userArr = AdminUser::getAdminDetail($page_dtl->created_by);  
                //Get Recent Post List 
                $recentBlogs = Blog::orderBy('created_at', 'desc')->take($blogRecentConfig)->get();
                //Get All Tags List
                $getAllTagList = \App\BlogTagList::orderBy('created_at', 'desc')->get();
                //Get All Slider Images
                $blog_slider = BlogSlider::where(['blog_id'=>$page_dtl->id])->get();
                //Get Blog Global Comment Config 
                $blogCommentConfig = $this->getBlogConfigValue($system_name="COMMENT_ENABLE");
                $facebook_appid = $this->getBlogConfigValue($system_name="FACEBOOK_APPID");
                //Get Blog Global Date Config                 
                $blogDATE = $this->getBlogConfigValue($system_name="DATE_FORMAT");
                $blogTIME = $this->getBlogConfigValue($system_name="TIME_FORMAT");
                $blogDATE_FORMAT = $blogDATE." ".$blogTIME;
                //Get Wishlist List                
                if(Auth::check()){
                  $isActiveblog_id = \App\BlogPin::where(['user_id'=>Auth::user()->id])->pluck('blog_id');
                  if(!empty($isActiveblog_id)){
                    $wishlist = Blog::whereIn('id',$isActiveblog_id)->get();  
                  }else{
                    $wishlist = '';
                  }                  
                }else{
                  $wishlist ='';
                }
                                                               
                
                return view(loadFrontTheme('blog.blogDetails'),[
                'page'=>$page,
                'page_dtls'=>$data_arr,
                'featuredBlog'=>$featureBlogs,
                'relatedBlog'=>$relatedBlogArr,
                'relatedBlogtagList'=>$relatedBlogtagList,
                'next'=>$Arr['next'],
                'previous'=>$Arr['previous'],
                'archiveArr'=>$Arr['archiveArr'],
                'allCategory'=>$allCategory,
                'tagOfBlog'=>$tagList,
                'user' => $userArr->nick_name,
                'userid' => $userArr->id,  
                'recentBlog'=>$recentBlogs,
                'blogslider'=>$blog_slider,
                'recentViewBlog'=>$recentViewBlogArr,
                'recentViewBlogTagList'=>$recentViewBlogTagList,
                'getAllTagList'=>$getAllTagList,
                'blogGlobalCommentConfig'=>$blogCommentConfig,
                'facebook_appid'=>$facebook_appid,
                'blogDATE_FORMAT'=>$blogDATE_FORMAT,
                'wishlist'=>$wishlist                
                ]);      
          
      }
      
    }

    private function getArchiveList($url=null){
      $url=$url;
      $array_temp=array();
      $img='';
      //get blog List For Sidebar
      $data_arrList=array();
      $page_dtl = Blog::getBlog();
      if(count($page_dtl) > 0) {

            foreach ($page_dtl as $key => $value) {
              if($url==''){
                    $array_temp['img']='';
                    $array_temp['id'] = $value->id;
                    $array_temp['url'] = $value->url;
                    $status = 'Inactive';
                    if($value->status == '1') {
                        $status = 'Active';
                    }

                    $array_temp['status'] = $status;
                    $array_temp['title'] = (!empty($value->blogDesc)) ? $value->blogDesc->blog_title:''; 
                    $array_temp['short_desc'] = (!empty($value->blogDesc)) ? $value->blogDesc->short_description :'';              
                    $array_temp['desc'] = (!empty($value->blogDesc)) ? $value->blogDesc->blog_desc : '';       
                    preg_match_all('/<img[^>]+>/i',$array_temp['desc'], $result); 
                    if(!empty($result)){
                      if(!empty($result[0])){
                        $img=$result[0][0];
                        $array_temp['img'] = $img;       
                      }
                    }

                    $categoryArr=$this->getCategoryListOfBlog($value->id);
                    $array_temp['category'] = $categoryArr;

                    $array_temp['desc']=str_replace($img,'',$array_temp['desc']);       
                    
                    $array_temp['created_at'] = date('d F, Y',strtotime(getDateFormat($value->created_at, '1')));
                    $array_temp['updated_at'] = getDateFormat($value->updated_at, '1');

                    $urlArr[]=$value->url;
                    $data_arrList[] = $array_temp;
               
              }else{
                 if($value->url!= $url){
                    $array_temp['img']='';
                    $array_temp['id'] = $value->id;

                    $array_temp['url'] = $value->url;

                    $status = 'Inactive';
                    if($value->status == '1') {
                        $status = 'Active';
                    }
                    $array_temp['status'] = $status;
                    $array_temp['title'] =(!empty($value->blogDesc) && $value->blogDesc->blog_title!='') ? $value->blogDesc->blog_title :'';
                    $array_temp['short_desc'] =(!empty($value->blogDesc) && $value->blogDesc->short_description!='') ? $value->blogDesc->short_description :'';
                    $array_temp['desc'] = (!empty($value->blogDesc) && $value->blogDesc->blog_desc!='') ? $value->blogDesc->blog_desc : '';       
                    preg_match_all('/<img[^>]+>/i',$array_temp['desc'], $result); 
                    if(!empty($result)){
                      if(!empty($result[0])){
                        $img=$result[0][0];
                        $array_temp['img'] = $img;       
                      }
                    }

                    $categoryArr=$this->getCategoryListOfBlog($value->id);
                    $array_temp['category'] = $categoryArr;

                    $array_temp['desc']=str_replace($img,'',$array_temp['desc']);    
                    $array_temp['created_at'] = date('d F, Y',strtotime($value->created_at));
                    $array_temp['updated_at'] = getDateFormat($value->updated_at, '1');

                    $urlArr[]=$value->url;
                    $data_arrList[] = $array_temp;
                }
              }
            }
            $next='';
            $previous='';
            if(!empty($urlArr)){
              foreach ($urlArr as $key => $value) {
                if($value==$url){
                  $current=current($urlArr[$key]);
                }
              }
              $next = next($urlArr);      
              $previous = prev($urlArr);  
            }
            

        }


        //Get All the Archive Directory
        $prefix = DB::getTablePrefix();
        $blog=DB::select( DB::raw( 'select COUNT(*) AS count, t1.*, YEAR(created_at) AS yname,MONTHNAME(created_at) AS monthname from '.$prefix.'blog as t1 GROUP BY MONTH(created_at)'));
        foreach($blog as $archiveData){
          $archiveArr[]=array('month'=>$archiveData->monthname.' '.$archiveData->yname,'count'=>$archiveData->count);
        }

        return array('archiveArr'=>$archiveArr,'next'=>$next,'previous'=>$previous,'data_arrList'=>$data_arrList,'url'=>$url);

    }


    public function archiveList(Request $request){
      $page = 'archiveblogList';

      $dataseo = [];

      $blogs = Blog::with(['blogDesc','hasPin','blogCat'=>function($query){
        $query->with('getBlogCategoryDescription');
      }]);

      if($request->month){
        $date = date_parse($request->month); 
         $blogs->whereMonth('created_at','=',$date['month']);
      }

      if($request->year){
         
         $blogs->whereYear('created_at','=',$request->year);
      }

       $blog=$blogs->paginate($this->pageItem);

       $data_arr=array();
       if(count($blog) > 0) {

          foreach ($blog as $key => $value) {
              
              $array_temp['img']='';
              $array_temp['id'] = $value->id;
              $array_temp['url'] = $value->url;                
              $array_temp['image'] = $value->feature_image;

              if(isset(Auth::user()->id)){
                $isActivePin = \App\BlogPin::where(['blog_id'=>$value->id,'user_id'=>Auth::user()->id])->count();
                $array_temp['hasPin'] = ($isActivePin>0)?1:0;
              }else{
                $array_temp['hasPin'] = 0;
              }
              
              $status = 'Inactive';
              if($value->status == '1') {
                  $status = 'Active';
              }

              $array_temp['status'] = $status;

              $array_temp['title'] = (!empty($value->blogDesc))?$value->blogDesc->blog_title:'';
              $array_temp['short_desc'] = (!empty($value->blogDesc))?$value->blogDesc->blog_short_desc:'';              
              $array_temp['desc'] = (!empty($value->blogDesc)) ? $value->blogDesc->blog_desc : '';       
              preg_match_all('/<img[^>]+>/i',$array_temp['desc'], $result); 
              $img='';
              if(!empty($result)){
                if(!empty($result[0])){
                  $img=$result[0][0];
                  $array_temp['img'] = $img;       
                }
              }
              $array_temp['desc']=str_replace($img,'',$array_temp['desc']);       
              $array_temp['created_at'] = date('d F, Y',strtotime(getDateFormat($value->created_at, '1')));
              
              //Get All Category of this blog
              $categoryArr=array();
              $categoryList=BlogCat::blogCatById($value->id);
              if(!empty($categoryList)){
                foreach($categoryList as $categoryObj){
                    if(!empty($categoryObj->getBlogCategoryDescription)){
                      $categoryArr[]=array(
                        'id'=>$categoryObj->getBlogCategoryDescription->id,
                        'name'=>$categoryObj->getBlogCategoryDescription->name);
                    }
                  } 
                }
              
              //Get All Tag
              $tagIdList=BlogTag::where('blog_id',$value->id)->pluck('tag_id');
              $tagList = \App\BlogTagList::whereIn('id',$tagIdList)->pluck('tag_title');

              //Get blog added user name                                
              $userArr = AdminUser::getAdminDetail($value->created_by);  
                           
              $array_temp['tags'] = $tagList;
              $array_temp['user'] = $userArr->nick_name;    
              $array_temp['userid'] = $userArr->id;                
              $array_temp['category'] = $categoryArr;
              $array_temp['updated_at'] = getDateFormat($value->updated_at, '1');
              $data_arr[] = $array_temp;
          }

       }      

       $dataseo['month'] = isset($request->month)?$request->month:''; 
       $dataseo['year'] = isset($request->year)?$request->year:''; 
       $data = (object) $dataseo;
      
       //Get Archive List
       $arr=$this->getArchiveList();
       //Get All Category List
       $allCategory=BlogCategory::getMainCategory();    
       //Get Feature blog List
       //Get Blog Feature Config 
       $blogFeatureConfig = $this->getBlogConfigValue($system_name="FEATURE_BLOG");
       $featureBlogs = \App\Blog::getFeaturedBlog($blogFeatureConfig);
       //Get All the Category of the Blogs
       $paginateLinks=$blog->links();
       //Get Recent Post List 
       //Get Blog Recent Config 
       $blogRecentConfig = $this->getBlogConfigValue($system_name="RECENT_VIEW_BLOG");   
       $recentBlogs = Blog::orderBy('created_at', 'desc')->take($blogRecentConfig)->get();
       //Get All Tags List
       $getAllTagList = \App\BlogTagList::orderBy('created_at', 'desc')->get();
       //Get Admin Setting 
       $blogConfig = $this->getBlogConfigValue($system_name="BLOG_PAGINATION"); 
       //Get Blog Global Date Config        
       $blogDATE = $this->getBlogConfigValue($system_name="DATE_FORMAT");
       $blogTIME = $this->getBlogConfigValue($system_name="TIME_FORMAT");
       $blogDATE_FORMAT = $blogDATE." ".$blogTIME;
       //Get Wishlist List
       $loginUserId = Auth::id();
       if(!empty($loginUserId)){
        $isActiveblog_id = \App\BlogPin::where(['user_id'=>Auth::user()->id])->pluck('blog_id');
        $wishlist = Blog::whereIn('id',$isActiveblog_id)->get();        
       }else{
         $wishlist ='';
       }


      if ($request->ajax()) { 
        return view(loadFrontTheme('blog.archivedata'),[
          'page'=>$page,
          'page_dtls'=>$blog,
          'paginateLinks'=>$paginateLinks,
          'sideBar'=> (array) $arr['archiveArr'],
          'next'=>$arr['next'],
          'previous'=>$arr['previous'],
          'archiveArr'=> (array) $arr['archiveArr'],
          'allCategory'=>$allCategory,
          'url'=>$this->siteurl,
          'featuredBlog'=>$featureBlogs,
          'recentBlog'=>$recentBlogs,
          'getAllTagList'=>$getAllTagList,
          'archiveBlog'=>$data_arr,
          'blogConfig'=>$blogConfig,
          'wishlist'=>$wishlist,
          'blogDATE_FORMAT'=>$blogDATE_FORMAT,
          'data'=> $data]
        );
      }else{
        return view(loadFrontTheme('blog.archiveblogList'),[
          'page'=>$page,
          'page_dtls'=>$blog,
          'paginateLinks'=>$paginateLinks,
          'sideBar'=> (array) $arr['archiveArr'],
          'next'=>$arr['next'],
          'previous'=>$arr['previous'],
          'archiveArr'=> (array) $arr['archiveArr'],
          'allCategory'=>$allCategory,
          'url'=>$this->siteurl,
          'featuredBlog'=>$featureBlogs,
          'recentBlog'=>$recentBlogs,
          'getAllTagList'=>$getAllTagList,
          'archiveBlog'=>$data_arr,
          'blogConfig'=>$blogConfig,
          'wishlist'=>$wishlist,
          'blogDATE_FORMAT'=>$blogDATE_FORMAT,
          'data'=> $data]
        );
      }
    }


    public function  categoryBlogList(Request $request){
      $pageName = 'categoryBlogList';
      $page = 0;
      $categoryUrl=$request->categoryurl;
      //Get Category Id from the url of the category
      $categoryObj=BlogCategory::getCategoryIdByUrl($categoryUrl)->first();
      $category_id=$categoryObj->id;
      $category_name=ucwords(strtolower($categoryObj->blogcategorydesc->name));
      $data = array(); 	  
      if($categoryObj!==null) {
       
        $cat_array_temp['status'] = $categoryObj->status;
        $cat_array_temp['title'] = $categoryObj->blogcategorydesc->name;
        $cat_array_temp['comments'] = $categoryObj->blogcategorydesc->comments;
        $cat_array_temp['desc'] = $categoryObj->blogcategorydesc->description;

        $img='';
        preg_match_all('/<img[^>]+>/i',$categoryObj->blogcategorydesc->blog_desc, $result);
       
        if(!empty($result)){
          $imgTag = array();
          foreach($result as $img){
            $imgStr=$img;
            $temp = $imgStr;       
          }
           $array_temp['img'] = $temp;  
        }                      
        
        $cat_array_temp['desc']=str_replace($img,'',$categoryObj->blogcategorydesc->description);
        $cat_array_temp['meta_title']=str_replace($img,'',$categoryObj->blogcategorydesc->meta_title);
        $cat_array_temp['meta_keyword']=str_replace($img,'',$categoryObj->blogcategorydesc->meta_keyword);    
        $cat_array_temp['meta_description']=str_replace($img,'',$categoryObj->blogcategorydesc->meta_description);        
        $cat_array_temp['template_type']='3';      
        $cat_array_temp['created_at'] = date('d F, Y',strtotime(getDateFormat($categoryObj->created_at, '1')));
        $cat_array_temp['updated_at'] = getDateFormat($categoryObj->updated_at, '1');
       
        $data = $cat_array_temp;
      }
      
      $relatedBlogsIds = \App\BlogCat::where('cat_id',$category_id)->groupBy('blog_id')->pluck('blog_id');
      $relatedBlogs = \App\Blog::whereIn('id',$relatedBlogsIds)->where(['publish'=>'1','status'=>'1'])->with(['blogDesc','hasPin'])->paginate($this->pageItem);

      $paginateLinks=$relatedBlogs->links();

      $data_arr=array();
      if(count($relatedBlogs) > 0) {

        foreach ($relatedBlogs as $key => $value) {
            
            $array_temp['img']='';
            $array_temp['id'] = $value->id;
            $array_temp['url'] = $value->url;                
            $array_temp['image'] = $value->feature_image;

            if(isset(Auth::user()->id)){
              $isActivePin = \App\BlogPin::where(['blog_id'=>$value->id,'user_id'=>Auth::user()->id])->count();
              $array_temp['hasPin'] = ($isActivePin>0)?1:0;
            }else{
              $array_temp['hasPin'] = 0;
            }
            
            $status = 'Inactive';
            if($value->status == '1') {
                $status = 'Active';
            }

            $array_temp['status'] = $status;

            $array_temp['title'] = (!empty($value->blogDesc))?substr($value->blogDesc->blog_title,0,30):'';
            $array_temp['short_desc'] = (!empty($value->blogDesc))?substr($value->blogDesc->blog_short_desc,0,100):'';              
            $array_temp['desc'] = (!empty($value->blogDesc)) ? $value->blogDesc->blog_desc : '';       
            preg_match_all('/<img[^>]+>/i',$array_temp['desc'], $result); 
            $img='';
            if(!empty($result)){
              if(!empty($result[0])){
                $img=$result[0][0];
                $array_temp['img'] = $img;       
              }
            }
            $array_temp['desc']=str_replace($img,'',$array_temp['desc']);       
            $array_temp['created_at'] = date('d F, Y',strtotime(getDateFormat($value->created_at, '1')));
            
            //Get All Category of this blog
            $categoryArr=array();
            $categoryList=BlogCat::blogCatById($value->id);
            if(!empty($categoryList)){
              foreach($categoryList as $categoryObj){
                  if(!empty($categoryObj->getBlogCategoryDescription)){
                    $categoryArr[]=array(
                      'id'=>$categoryObj->getBlogCategoryDescription->id,
                      'name'=>$categoryObj->getBlogCategoryDescription->name);
                  }
                } 
              }
            
            //Get All Tag
            $tagIdList=BlogTag::where('blog_id',$value->id)->pluck('tag_id');
            $tagList = \App\BlogTagList::whereIn('id',$tagIdList)->pluck('tag_title');

            //Get blog added user name                                
            $userArr = AdminUser::getAdminDetail($value->created_by);  
                         
            $array_temp['tags'] = $tagList;
            $array_temp['user'] = $userArr->nick_name; 
            $array_temp['userid'] = $userArr->id;                   
            $array_temp['category'] = $categoryArr;
            $array_temp['updated_at'] = getDateFormat($value->updated_at, '1');
            $data_arr[] = $array_temp;
        }

      }      
      //Get All Archive List
      $arr=$this->getArchiveList();
      //Get All the Category of the Blogs
      $allCategory=BlogCategory::getMainCategory();
      //Get Feature blog List
      //Get Blog Feature Config 
      $blogFeatureConfig = $this->getBlogConfigValue($system_name="FEATURE_BLOG");
      $featureBlogs = \App\Blog::getFeaturedBlog($blogFeatureConfig);
      //Get Recent Post List 
      //Get Blog Recent Config 
      $blogRecentConfig = $this->getBlogConfigValue($system_name="RECENT_VIEW_BLOG");   
      $recentBlogs = Blog::orderBy('created_at', 'desc')->take($blogRecentConfig )->get();
      //Get All Tags List
      $getAllTagList = \App\BlogTagList::orderBy('created_at', 'desc')->get();
      //Get Admin Setting 
      $blogConfig = $this->getBlogConfigValue($system_name="BLOG_PAGINATION"); 
      //Get Blog Global Date Config       
      $blogDATE = $this->getBlogConfigValue($system_name="DATE_FORMAT");
      $blogTIME = $this->getBlogConfigValue($system_name="TIME_FORMAT");
      $blogDATE_FORMAT = $blogDATE." ".$blogTIME;
      //Get Wishlist List
      $loginUserId = Auth::id();
      if(!empty($loginUserId)){
        $isActiveblog_id = \App\BlogPin::where(['user_id'=>Auth::user()->id])->pluck('blog_id');
        $wishlist = Blog::whereIn('id',$isActiveblog_id)->get();          
      }else{
        $wishlist ='';
      }


      if ($request->ajax()) { 
         return view(loadFrontTheme('blog.categorydata'),[
          'pageName'=>$pageName,
          'page_dtls'=>$relatedBlogs,
          'categoryBlog'=>$data_arr,
          'allCategory'=>$allCategory,
          'category_name'=>$category_name,
          'categoryUrl'=>$categoryUrl,
          'url'=>$this->siteurl,
          'cateData'=>$data,
          'sideBar'=> (array) $arr['archiveArr'],
          'next'=>$arr['next'],
          'previous'=>$arr['previous'],
          'archiveArr'=> (array) $arr['archiveArr'],
          'featuredBlog'=>$featureBlogs,
          'recentBlog'=>$recentBlogs,
          'getAllTagList'=>$getAllTagList,  
          'blogConfig'=>$blogConfig,      
          'blogDATE_FORMAT'=>$blogDATE_FORMAT,
          'wishlist'=>$wishlist,
          'paginateLinks'=>$paginateLinks
        ]);
      }else{
        return view(loadFrontTheme('blog.categoryBlogList'),[
          'pageName'=>$pageName,
          'page_dtls'=>$relatedBlogs,
          'categoryBlog'=>$data_arr,
          'allCategory'=>$allCategory,
          'category_name'=>$category_name,
          'categoryUrl'=>$categoryUrl,
          'url'=>$this->siteurl,
          'cateData'=>$data,
          'sideBar'=> (array) $arr['archiveArr'],
          'next'=>$arr['next'],
          'previous'=>$arr['previous'],
          'archiveArr'=> (array) $arr['archiveArr'],
          'featuredBlog'=>$featureBlogs,
          'recentBlog'=>$recentBlogs,
          'getAllTagList'=>$getAllTagList,  
          'blogConfig'=>$blogConfig,      
          'blogDATE_FORMAT'=>$blogDATE_FORMAT,
          'wishlist'=>$wishlist,
          'paginateLinks'=>$paginateLinks
        ]);

      }   

    }

    private function getCategoryListOfBlog($blog_id){
      $categoryArr=array();
                $categoryList=BlogCat::blogCatById($blog_id);
                if(!empty($categoryList)){
                  foreach($categoryList as $categoryObj){
                      if(!empty($categoryObj->getBlogCategoryDescription)){
                        $categoryArr[]=array(
                          'id'=>$categoryObj->getBlogCategoryDescription->id,
                          'name'=>$categoryObj->getBlogCategoryDescription->category_name);
                      }
                    }
                  }
      $category = $categoryArr;
      return $category;
    }

    public function blogtag(Request $request){
        
        $lang_id = session('default_lang');
        
        $TagData = \App\BlogTagList::select('id')->where('tag_title',$request->tag_name)->first();

        $blogsList = \App\BlogTag::where('tag_id',$TagData->id)->pluck('blog_id');        

        $tagBlogsList = \App\Blog::where(['publish'=>'1','status'=>'1'])->whereIn('id',$blogsList)->with('hasPin')->paginate($this->pageItem);                                   
        
        $paginateLinks=$tagBlogsList->links();   

        $data_arr=array();
        if(count($tagBlogsList) > 0) {

            foreach ($tagBlogsList as $key => $value) {
                
                $array_temp['img']='';
                $array_temp['id'] = $value->id;
                $array_temp['url'] = $value->url;                
                $array_temp['image'] = $value->feature_image;

                if(isset(Auth::user()->id)){
                  $isActivePin = \App\BlogPin::where(['blog_id'=>$value->id,'user_id'=>Auth::user()->id])->count();
                  $array_temp['hasPin'] = ($isActivePin>0)?1:0;
                }else{
                  $array_temp['hasPin'] = 0;
                }
                
                $status = 'Inactive';
                if($value->status == '1') {
                    $status = 'Active';
                }

                $array_temp['status'] = $status;

                $array_temp['title'] = (!empty($value->blogDesc))?$value->blogDesc->blog_title:'';
                $array_temp['short_desc'] = (!empty($value->blogDesc))?$value->blogDesc->blog_short_desc:'';              
                $array_temp['desc'] = (!empty($value->blogDesc)) ? $value->blogDesc->blog_desc : '';       
                preg_match_all('/<img[^>]+>/i',$array_temp['desc'], $result); 
                $img='';
                if(!empty($result)){
                  if(!empty($result[0])){
                    $img=$result[0][0];
                    $array_temp['img'] = $img;       
                  }
                }
                $array_temp['desc']=str_replace($img,'',$array_temp['desc']);       
                $array_temp['created_at'] = date('d F, Y',strtotime(getDateFormat($value->created_at, '1')));
                //Get All Category of this blog
                $categoryArr=array();
                $categoryList=BlogCat::blogCatById($value->id);
                if(!empty($categoryList)){
                  foreach($categoryList as $categoryObj){
                      if(!empty($categoryObj->getBlogCategoryDescription)){
                        $categoryArr[]=array(
                          'id'=>$categoryObj->getBlogCategoryDescription->id,
                          'name'=>$categoryObj->getBlogCategoryDescription->name);
                      }
                    } 
                  }
                //Get All Tag
                $tagIdList=BlogTag::where('blog_id',$value->id)->pluck('tag_id');
                $tagList = \App\BlogTagList::whereIn('id',$tagIdList)->pluck('tag_title');                
                              
                //Get blog added user name                                
                $userArr = AdminUser::getAdminDetail($value->created_by);  

                $array_temp['tags'] = $tagList;
                $array_temp['user'] = $userArr->nick_name;  
                $array_temp['userid'] = $userArr->id;                  
                $array_temp['category'] = $categoryArr;
                $array_temp['updated_at'] = getDateFormat($value->updated_at, '1');
                $data_arr[] = $array_temp;
            }

        }   

        //Get all category list
        $allCategory=BlogCategory::getMainCategory();
        //Get All Archive List
        $arr=$this->getArchiveList();
        //Get Feature blog List
        //Get Blog Feature Config 
        $blogFeatureConfig = $this->getBlogConfigValue($system_name="FEATURE_BLOG");
        $featureBlogs = \App\Blog::getFeaturedBlog($blogFeatureConfig); 
        //Get Recent Post List 
        //Get Blog Recent Config 
        $blogRecentConfig = $this->getBlogConfigValue($system_name="RECENT_VIEW_BLOG");    
        $recentBlogs = Blog::orderBy('created_at', 'desc')->take($blogRecentConfig)->get();
        //Get All Tags List
        $getAllTagList = \App\BlogTagList::orderBy('created_at', 'desc')->get(); 
        //Get Admin Setting 
        $blogConfig = $this->getBlogConfigValue($system_name="BLOG_PAGINATION"); 
        //Get Blog Global Date Config         
        $blogDATE = $this->getBlogConfigValue($system_name="DATE_FORMAT");
        $blogTIME = $this->getBlogConfigValue($system_name="TIME_FORMAT");
        $blogDATE_FORMAT = $blogDATE." ".$blogTIME;
        //Get Wishlist List
        $loginUserId = Auth::id();
        if(!empty($loginUserId)){
          $isActiveblog_id = \App\BlogPin::where(['user_id'=>Auth::user()->id])->pluck('blog_id');
          $wishlist = Blog::whereIn('id',$isActiveblog_id)->get();          
        }else{
          $wishlist ='';
        }


        if ($request->ajax()) {         
          return view(loadFrontTheme('blog.tagdata'),['tag'=>ucwords(strtolower($request->tag_name)),'relatedBlogs'=>$tagBlogsList,'page_dtls'=>$data_arr,'base_url'=>$this->siteurl,'session_lang'=>session('lang_code'),'paginateLinks'=>$paginateLinks,'allCategory'=>$allCategory,'sideBar'=> (array) $arr['archiveArr'],'next'=>$arr['next'],'previous'=>$arr['previous'],'archiveArr'=> (array) $arr['archiveArr'],'featuredBlog'=>$featureBlogs,'recentBlog'=>$recentBlogs,'getAllTagList'=>$getAllTagList,'blogConfig'=>$blogConfig,'blogDATE_FORMAT'=>$blogDATE_FORMAT,'wishlist'=>$wishlist]);
        }else{
           return view(loadFrontTheme('blog.showTag'),['tag'=>ucwords(strtolower($request->tag_name)),'relatedBlogs'=>$tagBlogsList,'page_dtls'=>$data_arr,'base_url'=>$this->siteurl,'session_lang'=>session('lang_code'),'paginateLinks'=>$paginateLinks,'allCategory'=>$allCategory,'sideBar'=> (array) $arr['archiveArr'],'next'=>$arr['next'],'previous'=>$arr['previous'],'archiveArr'=> (array) $arr['archiveArr'],'featuredBlog'=>$featureBlogs,'recentBlog'=>$recentBlogs,'getAllTagList'=>$getAllTagList,'blogConfig'=>$blogConfig,'blogDATE_FORMAT'=>$blogDATE_FORMAT,'wishlist'=>$wishlist]); 
        }
      
    }

    public function blogAuther(Request $request,$userid){

        $lang_id = session('default_lang');

        $blogAutherList = \App\Blog::where(['publish'=>'1','status'=>'1'])->where('created_by',$userid)->with(['blogDesc','hasPin'])->paginate($this->pageItem);               
                
        $paginateLinks=$blogAutherList->links();   

        $data_arr=array();
        if(count($blogAutherList) > 0) {

            foreach ($blogAutherList as $key => $value) {
                
                $array_temp['img']='';
                $array_temp['id'] = $value->id;
                $array_temp['url'] = $value->url;                
                $array_temp['image'] = $value->feature_image;

                if(isset(Auth::user()->id)){
                  $isActivePin = \App\BlogPin::where(['blog_id'=>$value->id,'user_id'=>Auth::user()->id])->count();
                  $array_temp['hasPin'] = ($isActivePin>0)?1:0;
                }else{
                  $array_temp['hasPin'] = 0;
                }
                
                $status = 'Inactive';
                if($value->status == '1') {
                    $status = 'Active';
                }

                $array_temp['status'] = $status;

                $array_temp['title'] = (!empty($value->blogDesc))?$value->blogDesc->blog_title:'';
                $array_temp['short_desc'] = (!empty($value->blogDesc))?$value->blogDesc->blog_short_desc:'';              
                $array_temp['desc'] = (!empty($value->blogDesc)) ? $value->blogDesc->blog_desc : '';       
                preg_match_all('/<img[^>]+>/i',$array_temp['desc'], $result); 
                $img='';
                if(!empty($result)){
                  if(!empty($result[0])){
                    $img=$result[0][0];
                    $array_temp['img'] = $img;       
                  }
                }
                $array_temp['desc']=str_replace($img,'',$array_temp['desc']);       
                $array_temp['created_at'] = date('d F, Y',strtotime(getDateFormat($value->created_at, '1')));
                //Get All Category of this blog
                $categoryArr=array();
                $categoryList=BlogCat::blogCatById($value->id);
                if(!empty($categoryList)){
                  foreach($categoryList as $categoryObj){
                      if(!empty($categoryObj->getBlogCategoryDescription)){
                        $categoryArr[]=array(
                          'id'=>$categoryObj->getBlogCategoryDescription->id,
                          'name'=>$categoryObj->getBlogCategoryDescription->name);
                      }
                    } 
                  }
                //Get All Tag
                $tagIdList=BlogTag::where('blog_id',$value->id)->pluck('tag_id');
                $tagList = \App\BlogTagList::whereIn('id',$tagIdList)->pluck('tag_title');                
                              
                //Get blog added user name                                
                $userArr = AdminUser::getAdminDetail($value->created_by);  

                $array_temp['tags'] = $tagList;
                $array_temp['user'] = $userArr->nick_name;
                $array_temp['userid'] = $userArr->id;                  
                $array_temp['category'] = $categoryArr;
                $array_temp['updated_at'] = getDateFormat($value->updated_at, '1');
                $data_arr[] = $array_temp;
            }

        }   

        //Get blog added user name                                
        $userArrData = AdminUser::getAdminDetail($userid);
        //Get all category list
        $allCategory=BlogCategory::getMainCategory();
        //Get All Archive List
        $arr=$this->getArchiveList();
        //Get Feature blog List
        //Get Blog Feature Config 
        $blogFeatureConfig = $this->getBlogConfigValue($system_name="FEATURE_BLOG");
        $featureBlogs = \App\Blog::getFeaturedBlog($blogFeatureConfig); 
        //Get Recent Post List 
        //Get Blog Recent Config 
        $blogRecentConfig = $this->getBlogConfigValue($system_name="RECENT_VIEW_BLOG");    
        $recentBlogs = Blog::orderBy('created_at', 'desc')->take($blogRecentConfig)->get();
        //Get All Tags List
        $getAllTagList = \App\BlogTagList::orderBy('created_at', 'desc')->get(); 
        //Get Admin Setting 
        $blogConfig = $this->getBlogConfigValue($system_name="BLOG_PAGINATION"); 
        //Get Blog Global Date Config         
        $blogDATE = $this->getBlogConfigValue($system_name="DATE_FORMAT");
        $blogTIME = $this->getBlogConfigValue($system_name="TIME_FORMAT");
        $blogDATE_FORMAT = $blogDATE." ".$blogTIME;
        //Get Wishlist List
        $loginUserId = Auth::id();
        if(!empty($loginUserId)){
          $isActiveblog_id = \App\BlogPin::where(['user_id'=>Auth::user()->id])->pluck('blog_id');
          $wishlist = Blog::whereIn('id',$isActiveblog_id)->get();          
        }else{
          $wishlist ='';
        }


        if ($request->ajax()) {         
          return view(loadFrontTheme('blog.autherdata'),['autherid'=>$userid,'auther'=>ucwords(strtolower($userArrData->nick_name)),'blogAutherList'=>$blogAutherList,'page_dtls'=>$data_arr,'base_url'=>$this->siteurl,'session_lang'=>session('lang_code'),'paginateLinks'=>$paginateLinks,'allCategory'=>$allCategory,'sideBar'=> (array) $arr['archiveArr'],'next'=>$arr['next'],'previous'=>$arr['previous'],'archiveArr'=> (array) $arr['archiveArr'],'featuredBlog'=>$featureBlogs,'recentBlog'=>$recentBlogs,'getAllTagList'=>$getAllTagList,'blogConfig'=>$blogConfig,'blogDATE_FORMAT'=>$blogDATE_FORMAT,'wishlist'=>$wishlist]);
        }else{
           return view(loadFrontTheme('blog.autherBlog'),['autherid'=>$userid,'auther'=>ucwords(strtolower($userArrData->nick_name)),'blogAutherList'=>$blogAutherList,'page_dtls'=>$data_arr,'base_url'=>$this->siteurl,'session_lang'=>session('lang_code'),'paginateLinks'=>$paginateLinks,'allCategory'=>$allCategory,'sideBar'=> (array) $arr['archiveArr'],'next'=>$arr['next'],'previous'=>$arr['previous'],'archiveArr'=> (array) $arr['archiveArr'],'featuredBlog'=>$featureBlogs,'recentBlog'=>$recentBlogs,'getAllTagList'=>$getAllTagList,'blogConfig'=>$blogConfig,'blogDATE_FORMAT'=>$blogDATE_FORMAT,'wishlist'=>$wishlist]); 
        }
      
    }

    public function searchBlog(Request $request){

      $page = 'blogSeachResult';  
      $url=$this->siteurl;
      $search = trim($request->blogsearch);
      $default_lang = session('default_lang');
      //Get Admin Setting 
      $blogSearchConfig = $this->getBlogConfigValue($system_name="BLOG_SEARCH_CONFIG");
      $blogSearchConfigData = explode(',', $blogSearchConfig);
                 
      $page_dtl = DB::table(with(new Blog)->getTable().' as b')
          ->join(with(new BlogDesc)->getTable().' as bd', [['b.id', '=', 'bd.blog_id'], ['bd.lang_id', '=' , DB::raw($default_lang)]])               
          ->where(['b.status' => '1','b.publish' => '1']);

          foreach ($blogSearchConfigData as $key => $configValue) {
            if($configValue=='title'){
              $page_dtl->where('bd.blog_title', 'like', '%'.$search.'%');
            }
            if($configValue=='short_description'){
              $page_dtl->orwhere('bd.blog_short_desc', 'like', '%'.$search.'%');
            }
            if($configValue=='description'){
              $page_dtl->orwhere('bd.blog_desc', 'like', '%'.$search.'%');
            }
            if($configValue=='tags'){
              $searchtags = BlogTagList::where('tag_title','like','%'.$search.'%')->pluck('id');     
              if(count($searchtags)){
                $searchtagsblogid = BlogTag::whereIn('tag_id',$searchtags)->pluck('blog_id');
                if(count($searchtagsblogid)){
                  $page_dtl->orwhereIn('b.id',$searchtagsblogid);
                }
              }                            
            }

          }
                
      $searchresult = $page_dtl->paginate($this->pageItem);
      $paginateLinks=$searchresult->links();
 
      $data_arr=array();
      if(count($searchresult) > 0) {

            foreach ($searchresult as $key => $value) {
                
                $array_temp['img']='';
                $array_temp['id'] = $value->id;
                $array_temp['url'] = $value->url;                
                $array_temp['image'] = $value->feature_image;

                if(isset(Auth::user()->id)){
                  $isActivePin = \App\BlogPin::where(['blog_id'=>$value->id,'user_id'=>Auth::user()->id])->count();
                  $array_temp['hasPin'] = ($isActivePin>0)?1:0;
                }else{
                  $array_temp['hasPin'] = 0;
                }
                
                $status = 'Inactive';
                if($value->status == '1') {
                    $status = 'Active';
                }

                $array_temp['status'] = $status;

                $array_temp['title'] = (!empty($value->blog_title))?$value->blog_title:'';
                $array_temp['short_desc'] = (!empty($value->blog_short_desc))?$value->blog_short_desc:'';              
                $array_temp['desc'] = (!empty($value->blog_desc)) ? $value->blog_desc : '';       
                preg_match_all('/<img[^>]+>/i',$array_temp['desc'], $result); 
                $img='';
                if(!empty($result)){
                  if(!empty($result[0])){
                    $img=$result[0][0];
                    $array_temp['img'] = $img;       
                  }
                }
                $array_temp['desc']=str_replace($img,'',$array_temp['desc']);       
                $array_temp['created_at'] = date('d F, Y',strtotime(getDateFormat($value->created_at, '1')));
                //Get All Category of this blog
                $categoryArr=array();
                $categoryList=BlogCat::blogCatById($value->id);
                if(!empty($categoryList)){
                  foreach($categoryList as $categoryObj){
                      if(!empty($categoryObj->getBlogCategoryDescription)){
                        $categoryArr[]=array(
                          'id'=>$categoryObj->getBlogCategoryDescription->id,
                          'name'=>$categoryObj->getBlogCategoryDescription->name);
                      }
                    } 
                  }
                //Get All Tag
                $tagIdList=BlogTag::where('blog_id',$value->blog_id)->pluck('tag_id');
                $tagList = \App\BlogTagList::whereIn('id',$tagIdList)->pluck('tag_title');

                //Get blog added user name                                
                $userArr = AdminUser::getAdminDetail($value->created_by);  
                                                
                $array_temp['tags'] = $tagList;
                $array_temp['user'] = $userArr->nick_name;    
                $array_temp['userid'] = $userArr->id;                
                $array_temp['category'] = $categoryArr;
                $array_temp['updated_at'] = getDateFormat($value->updated_at, '1');
                $data_arr[] = $array_temp;
            }

        }
        //Get all category list
        $allCategory=BlogCategory::getMainCategory();

        //Get Archive List
        $Arr=$this->getArchiveList($url);

        //Get Feature blog List
        //Get Blog Feature Config 
        $blogFeatureConfig = $this->getBlogConfigValue($system_name="FEATURE_BLOG");
        $featureBlogs = \App\Blog::getFeaturedBlog($blogFeatureConfig);

        //Get Recent blog List                
        //Get Blog Recent Config 
        $blogRecentConfig = $this->getBlogConfigValue($system_name="RECENT_VIEW_BLOG");    
        $recentBlogs = Blog::orderBy('created_at', 'desc')->take($blogRecentConfig)->get();

        //Get All Tags List
        $getAllTagList = \App\BlogTagList::orderBy('created_at', 'desc')->get();

        //Get Admin Setting 
        $blogConfig = $this->getBlogConfigValue($system_name="BLOG_PAGINATION"); 
        //Get Blog Global Date Config         
        $blogDATE = $this->getBlogConfigValue($system_name="DATE_FORMAT");
        $blogTIME = $this->getBlogConfigValue($system_name="TIME_FORMAT");
        $blogDATE_FORMAT = $blogDATE." ".$blogTIME;
        //Get Wishlist List
        $loginUserId = Auth::id();
        if(!empty($loginUserId)){
          $isActiveblog_id = \App\BlogPin::where(['user_id'=>Auth::user()->id])->pluck('blog_id');
          $wishlist = Blog::whereIn('id',$isActiveblog_id)->get();          
        }else{
          $wishlist ='';
        }

        if ($request->ajax()) { 
          return view(loadFrontTheme('blog.searchdata'),['page'=>$page,'page_dtls'=>$data_arr,'url'=>$url,'paginateLinks'=>$paginateLinks,'page_dtl'=>$page_dtl,'allCategory'=>$allCategory,'featuredBlog'=>$featureBlogs,'next'=>$Arr['next'],'previous'=>$Arr['previous'],'archiveArr'=>$Arr['archiveArr'],'recentBlog'=>$recentBlogs,'getAllTagList'=>$getAllTagList,'blogConfig'=>$blogConfig,'blogDATE_FORMAT'=>$blogDATE_FORMAT,'wishlist'=>$wishlist]);
        }else{
            return view(loadFrontTheme('blog.blogSeachResult'),['page'=>$page,'page_dtls'=>$data_arr,'url'=>$url,'paginateLinks'=>$paginateLinks,'page_dtl'=>$page_dtl,'allCategory'=>$allCategory,'featuredBlog'=>$featureBlogs,'next'=>$Arr['next'],'previous'=>$Arr['previous'],'archiveArr'=>$Arr['archiveArr'],'recentBlog'=>$recentBlogs,'getAllTagList'=>$getAllTagList,'blogConfig'=>$blogConfig,'blogDATE_FORMAT'=>$blogDATE_FORMAT,'wishlist'=>$wishlist]);
        }

    }

  function getBlogConfigValue($system_name=null) {
      $system_val = '';
      if(!empty($system_name)){ 
          $system_val = \App\BlogConfig::getBlogValue($system_name);
      }  
      return $system_val;         
  }  

  public function feed()
  {      
      $blogs = Blog::where(['publish'=>'1','status'=>'1'])->orderBy('created_at', 'desc')->with(['blogDesc'])->take(200)->get();      
      $feeddata =  view(loadFrontTheme('blog.feed'),['blogs'=>$blogs,'baseUrl'=>$this->siteurl]);
      return response($feeddata, 200)->header('Content-Type', 'text/xml');
  }

}
