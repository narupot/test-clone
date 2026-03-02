<?php namespace App\Http\Controllers\Admin\Blog;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Input;
use Illuminate\Validation\Rule;
use Illuminate\Database\QueryException;
use App\Http\Controllers\MarketPlace;
use Illuminate\Http\Request;

use App\Language;
use App\Blog;
use App\BlogDesc;
use App\BlogDescRevision;
use App\BlogCat;
use App\BlogTag;
use App\BlogRelated;
use App\BlogCategory;
use App\BlogCategoryDesc;
use App\BlogSlider;
use App\BlogTagList;
use App\BlogConfig;
use Auth;
use Lang;
use Config;
use Session;

class BlogController extends MarketPlace
{ 
    public function __construct()
    {
        $this->middleware('admin.user');
        $this->tblBlog = with(new Blog)->getTable();
        $this->tblBlogDesc = with(new BlogDesc)->getTable();
        $this->tblBlogTag = with(new BlogTag)->getTable();
    }

    /*
    * These function are belongs for blog management | Start | By Satish Anand
    */
    
    public function index(){
        $permission = $this->checkUrlPermission('blog_list');
        if($permission === true) {
            $permission_arr['add'] = $this->checkMenuPermission('add_new_blog');
            $permission_arr['edit'] = $this->checkMenuPermission('edit_blog');
            $permission_arr['delete'] = $this->checkMenuPermission('delete_blog');
            $data_arr = array();
            $blog_dtl = Blog::getBlog();

            if(count($blog_dtl) > 0) {
                foreach ($blog_dtl as $key => $value) {
                    $array_temp['id'] = $value->id;
                    $array_temp['url'] = $value->url;
                    $status = 'Inactive';
                    if($value->status == '1') {
                        $status = 'Active';
                    }
                    $array_temp['status'] = $status;
                    if(isset($value->blogDesc->blog_title)){
                        $array_temp['title'] = $value->blogDesc->blog_title;
                    }else{
                        $array_temp['title'] ="";
                    }              
                    $array_temp['comment'] = ($value->comment==1)?'YES':'NO';
                    $array_temp['features'] = ($value->features==1)?'YES':'NO';
                    $array_temp['publish'] = ($value->publish==1)?'YES':'NO';
                    $array_temp['created_at'] = getDateFormat($value->created_at, '1');
                    $array_temp['updated_at'] = getDateFormat($value->updated_at, '1');

                    $data_arr[] = $array_temp;
                }
            }

            return view('admin.blog.List', ['blog_dtls'=>$data_arr, 'permission_arr'=>$permission_arr]);
        }
    }
   
    public function create(Request $request){

    	$permission = $this->checkUrlPermission('add_new_blog');
        if($permission === true) {

            $categories = BlogCategory::getMainCategory();
            $page='add_blog';
            $lang_lists = Language::getLangugeDetails();

            $data_arr = array();
            $blog_dtl = Blog::getBlog();

            if(count($blog_dtl) > 0) {
                foreach ($blog_dtl as $key => $value) {
                    $array_temp['id'] = $value->id;
                    $array_temp['url'] = $value->url;
                    $status = 'Inactive';
                    if($value->status == '1') {
                        $status = 'Active';
                    }
                    $array_temp['status'] = $status;
                    if(isset($value->blogDesc->blog_title)){
                        $array_temp['title'] = $value->blogDesc->blog_title;
                    }else{
                        $array_temp['title'] ="";
                    }              
                    $array_temp['comment'] = ($value->comment==1)?'YES':'NO';
                    $array_temp['features'] = ($value->features==1)?'YES':'NO';
                    $array_temp['publish'] = ($value->publish==1)?'YES':'NO';
                    $array_temp['created_at'] = getDateFormat($value->created_at, '1');
                    $array_temp['updated_at'] = getDateFormat($value->updated_at, '1');

                    $data_arr[] = $array_temp;
                }
            }

            return view('admin.blog.Create', ['lang_lists'=>$lang_lists,'blog_dtls'=>$data_arr,'categories'=>$categories,'page'=>$page]);
        }
    }
    
    function store(Request $request){ 

        $input = $request->all();  
        $def_lang_id = session('admin_default_lang');     
        $input['title'] = $request->blog_title[$def_lang_id];        
        $input['short_description'] = $request->blog_short_desc[$def_lang_id];
        $input['description'] = $request->blog_desc[$def_lang_id];

        $validate = $this->validateBlog($input);

        if ($validate->passes()) {

            $blog = new Blog;
            
            if(!empty($blog->url)){
                $blog->url = createUrl($request->url);
            }
            else{
                $blog->url = createUrl($request->blog_title[$def_lang_id]);
            }




            $blog->status = $request->status;
            $blog->publish_date = date("Y-m-d H:i:s", strtotime($request->publish_date));

            $blog->created_by = Auth::guard('admin_user')->user()->id;
            $blog->features = ($request->features==1)?$request->features:0;
            $blog->publish = ($request->publish==1)?$request->publish:0;
            $blog->comment = ($request->comment==1)?$request->comment:0;

            if(isset($request->uploadfile)) {
                
                $uploadDetails['path'] = Config::get('constants.blog_feature_img_path');
                $uploadDetails['file'] =  $request->uploadfile;
                $featureWidth = $this->getBlogConfigValue($system_name="FEATURE_IMAGE_WIDTH");
                $featureHeight = $this->getBlogConfigValue($system_name="FEATURE_IMAGE_HEIGHT");
                $uploadDetails['width'] =  $featureWidth;
                $uploadDetails['height'] = $featureHeight;

                $file_name = $this->uploadFileCustom($uploadDetails,'1');

                $blog->feature_image = $file_name;
            }

            if(isset($request->metaimage)) {
                
                $uploadDetails['path'] = Config::get('constants.blog_socialshare_img_path');
                $uploadDetails['file'] =  $request->metaimage;
                $seoWidth = $this->getBlogConfigValue($system_name="SEO_IMAGE_WIDTH");
                $seoHeight = $this->getBlogConfigValue($system_name="SEO_IMAGE_HEIGHT");
                $uploadDetails['width'] =  $seoWidth;
                $uploadDetails['height'] = $seoHeight;

                $file_name = $this->uploadFileCustom($uploadDetails,'1');

                $blog->metaimage = $file_name;
            }

            if(isset($request->fbimage)) {
                
                $uploadDetails['path'] = Config::get('constants.blog_socialshare_img_path');
                $uploadDetails['file'] =  $request->fbimage;
                $socialshareWidth = $this->getBlogConfigValue($system_name="SOCIALSHARE_IMAGE_WIDTH");
                $socialshareHeight = $this->getBlogConfigValue($system_name="SOCIALSHARE_IMAGE_HEIGHT");
                $uploadDetails['width'] =  $socialshareWidth;
                $uploadDetails['height'] = $socialshareHeight;

                $file_name = $this->uploadFileCustom($uploadDetails,'1');

                $blog->fbimage = $file_name;
            }

            if(isset($request->twimage)) {
                
                $uploadDetails['path'] = Config::get('constants.blog_socialshare_img_path');
                $uploadDetails['file'] =  $request->twimage;
                $socialshareWidthT = $this->getBlogConfigValue($system_name="SOCIALSHARE_IMAGE_WIDTH");
                $socialshareHeightT = $this->getBlogConfigValue($system_name="SOCIALSHARE_IMAGE_HEIGHT");
                $uploadDetails['width'] =  $socialshareWidthT;
                $uploadDetails['height'] = $socialshareHeightT;

                $file_name = $this->uploadFileCustom($uploadDetails,'1');

                $blog->twimage = $file_name;
            }

            if(isset($request->insimage)) {
                
                $uploadDetails['path'] = Config::get('constants.blog_socialshare_img_path');
                $uploadDetails['file'] =  $request->insimage;
                $socialshareWidthI = $this->getBlogConfigValue($system_name="SOCIALSHARE_IMAGE_WIDTH");
                $socialshareHeightI = $this->getBlogConfigValue($system_name="SOCIALSHARE_IMAGE_HEIGHT");
                $uploadDetails['width'] =  $socialshareWidthI;
                $uploadDetails['height'] = $socialshareHeightI;

                $file_name = $this->uploadFileCustom($uploadDetails,'1');

                $blog->insimage = $file_name;
            }             

            if($blog->save()){
                $created_blog_id = $blog->id;
            }else{
                $created_blog_id = '';
            }            

            if(isset($request->sliderimage)) {
                $uploadDetails = array();
                $uploadDetails['path'] = Config::get('constants.blog_slider_img_path');
                $sliderWidth = $this->getBlogConfigValue($system_name="SLIDER_IMAGE_WIDTH");
                $sliderHeight = $this->getBlogConfigValue($system_name="SLIDER_IMAGE_HEIGHT");
                $uploadDetails['width'] =  $sliderWidth;
                $uploadDetails['height'] = $sliderHeight;
                foreach($request->sliderimage as $file){
                  if(!empty($file)){
                      $slider = new BlogSlider;
                      $slider->blog_id = $blog->id;                      
                      $uploadDetails['file'] =  $file;
                      $file_name = $this->uploadFileCustom($uploadDetails);
                      $slider->image = $file_name;
                      $slider->save();
                  }                          
                }
            }

            $data_arr = $this->filterPageData($request);

            if(isset($request->blog_cat_id) && !empty($request->blog_cat_id)) {
                $blog_cat_id = [];
                foreach ($request->blog_cat_id as $key => $value) {
                    $blog_cat_data[] = ['blog_id'=>$blog->id, 'cat_id'=>$value];
                }                
                BlogCat::updateBlogInfo($blog->id,$blog_cat_data);
            }

            if(isset($request->related_blog) && !empty($request->related_blog)) {
                
                foreach ($request->related_blog as $key => $value) {
                    $blog_related_data[] = ['blog_id'=>$blog->id, 'related_id'=>$value];
                }                
                BlogRelated::updateBlogRelatedInfo($blog->id,$blog_related_data);
            }

            BlogDesc::insertBlogDesc($data_arr, $blog->id);
            BlogDescRevision::insertBlogDescRevision($data_arr, $blog->id);

            if(!empty($request->tags)){ 

                $tagsArray = explode(',',$request->tags);                
                if(!empty($tagsArray)){
                    foreach($tagsArray as $k=>$tags){

                        $tagsdata = BlogTagList::where(['tag_title'=>$tags])->get();                        
                        if(isset($tagsdata[0]->id) && $tagsdata[0]->id!=''){                                
                            $tag_id = $tagsdata[0]->id;                        
                            $this->saveIntoTagsBlog($blog->id,$tag_id);
                        }else{

                            $tagsObj = new BlogTagList;
                            $tagsObj->tag_title = $tags;
                            $tagsObj->created_at = date('Y-m-d',time());
                            $tagsObj->updated_at = date('Y-m-d',time());
                            $ifTagAlreadyExist = BlogTagList::where(['tag_title'=>$tags])->count();
                            if($ifTagAlreadyExist==0){
                                 $tagsObj->save();
                                 $tag_id = $tagsObj->id;
                                 $this->saveIntoTagsBlog($blog->id,$tag_id);
                            }                       
                        }
                    } 
                }            
            }

            /*update activity log Start*/                        
            $action_type = "created";
            $module_name = "blog";            
            $logdetails = "Admin has created ".$input['title']." blog";
            $old_data = "";
            $new_data = "";
            $logdata = array('action_type' =>$action_type,'module_name' =>$module_name,'logdetails' =>$logdetails,'old_data' =>$old_data,'new_data' =>$new_data );
            $this->updateLogActivity($logdata);
            /*update activity log End*/
           
            if($request->submit_type == 'submit_continue') {

                return $response =['status'=>'success', 'url' =>action('Admin\Blog\BlogController@edit', $created_blog_id)];
            }
            else {
                
                return $response =['status'=>'success', 'url' =>action('Admin\Blog\BlogController@index')];
            }
        } 
        else {
            
            $errors =  $validate->errors(); 
            return array('status'=>'fail','message'=>$errors);
        }       
    }    

    public function filterPageData($request) {

        $langArray = \App\Language::select('id','languageName')->where('status','1')->get();
      
        $def_lang = session('admin_default_lang');        
        $def_title = $request->blog_title[$def_lang];
        $def_desc = $request->blog_desc[$def_lang];
        $def_short_desc = $request->blog_short_desc[$def_lang];
        $def_meta_title = $request->meta_title[$def_lang];
        $def_meta_keyword = $request->meta_keyword[$def_lang];
        $def_meta_desc = $request->meta_desc[$def_lang];
        $def_fbtitle = $request->fbtitle[$def_lang];
        $def_twtitle = $request->twtitle[$def_lang];
        $def_institle = $request->institle[$def_lang];
        $def_fbdesc = $request->fbdesc[$def_lang];
        $def_twdesc = $request->twdesc[$def_lang];
        $def_insdesc = $request->insdesc[$def_lang];
        $comment = $request->comment;
        $features = $request->features;
        $publish = $request->publish;

        foreach ($langArray as $key=>$value){
            
            $blog_title = $request->blog_title[$value->id];
            $blog_short_desc = $request->blog_short_desc[$value->id];
            $blog_desc = $request->blog_desc[$value->id];
            $meta_title = $request->meta_title[$value->id];
            $meta_keyword = $request->meta_keyword[$value->id];
            $meta_desc = $request->meta_desc[$value->id];
            $fbtitle = $request->fbtitle[$value->id];
            $twtitle = $request->twtitle[$value->id];
            $institle = $request->institle[$value->id];
            $fbdesc = $request->fbdesc[$value->id];
            $twdesc = $request->twdesc[$value->id];
            $insdesc = $request->insdesc[$value->id];

            if(empty($blog_title)) {
                $blog_title = $def_title;
            }

            if(empty($blog_short_desc)) {
                $blog_short_desc = $def_short_desc;
            }

            if(empty($blog_desc)) {
                $blog_desc = $def_desc;
            } 

            if(empty($meta_title)) {
                $meta_title = $def_meta_title;
            }

            if(empty($meta_keyword)) {
                $meta_keyword = $def_meta_keyword;
            }
            if(empty($meta_desc)) {
                $meta_desc = $def_meta_desc;
            }

            if(empty($fbtitle)) {
                $fbtitle = $def_fbtitle;
            }

            if(empty($twtitle)) {
                $twtitle = $def_twtitle;
            }

            if(empty($institle)) {
                $institle = $def_institle;
            }

            if(empty($fbdesc)) {
                $fbdesc = $def_fbdesc;
            }

            if(empty($twdesc)) {
                $twdesc = $def_twdesc;
            }

            if(empty($insdesc)) {
                $insdesc = $def_insdesc;
            }


            $data_arr[$key] = array(

                'blog_title'=>$blog_title, 
                'blog_short_desc'=>$blog_short_desc, 
                'blog_desc'=>$blog_desc, 
                'lang_id'=>$value->id,                 
                'meta_title'=>$meta_title,
                'meta_keyword'=>$meta_keyword, 
                'meta_desc'=>$meta_desc,
                'fbtitle' => $fbtitle,
                'twtitle' => $twtitle,
                'institle' => $institle,
                'fbdesc' => $fbdesc,
                'twdesc' => $twdesc,
                'insdesc' => $insdesc,
                'comment'=>$comment,
                'features'=>$features,
                'publish'=>$publish 
            );
        } 

        return $data_arr;       
    }
    
    function edit($id){

        Session::put('edit_blog_id',$id);
        $permission = $this->checkUrlPermission('edit_blog');
        if($permission === true) {

            $blog_dtls = Blog::getBlogbyId($id);
            $categories = BlogCategory::getMainCategory();
            $revisions = BlogDescRevision::select('*')->groupBy('revision')->where('blog_id',$id)->selectRaw('count(*) as total, revision')->get();
            $revision = count($revisions);

            $blogCategoryId = [];
            if(!empty($blog_dtls->blogCat)) {
                foreach ($blog_dtls->blogCat as $value) {
                    $blogCategoryId[] = $value->cat_id;
                }
            }
            
            $tagsArray = [];
            foreach($blog_dtls->getBlogTag as $tg_key => $tagsData){  
                if(!empty($tagsData->hasTags)){
                    $tagsArray[] = $tagsData->hasTags->tag_title;
                }
            }
            
            $blog_tags = implode(',', $tagsArray); 
            $blog_dtls->tags = $blog_tags; 


            $relatedBlogId = [];
            if(!empty($blog_dtls->getBlogRelated)) {
                foreach ($blog_dtls->getBlogRelated as $value) {
                    $relatedBlogId[] = $value->related_id;
                }
            }

            $data_arr = array();
            $blog_dtl = Blog::getBlogList($id);


            if(count($blog_dtl) > 0) {
                foreach ($blog_dtl as $key => $value) {
                    $array_temp['id'] = $value->id;
                    $array_temp['url'] = $value->url;
                    $status = 'Inactive';
                    if($value->status == '1') {
                        $status = 'Active';
                    }
                    $array_temp['status'] = $status;
                    if(isset($value->blogDesc->blog_title)){
                        $array_temp['title'] = $value->blogDesc->blog_title;
                    }else{
                        $array_temp['title'] ="";
                    }              
                    $array_temp['comment'] = ($value->comment==1)?'YES':'NO';
                    $array_temp['features'] = ($value->features==1)?'YES':'NO';
                    $array_temp['publish'] = ($value->publish==1)?'YES':'NO';
                    $array_temp['created_at'] = getDateFormat($value->created_at,'1');
                    $array_temp['updated_at'] = getDateFormat($value->updated_at,'1');

                    $data_arr[] = $array_temp;
                }
            }           
            //dd($blog_dtls);
           
            $page='edit_blog';
            return view('admin.blog.Edit', [
                'blog_dtls'=>$blog_dtls, 
                'blog_dtl'=>$data_arr,
                'categories'=>$categories,
                'blogCategoryId'=>$blogCategoryId,
                'relatedBlogId'=>$relatedBlogId,
                'tblBlogDesc'=>$this->tblBlogDesc,                                
                'tblBlogTag'=>$this->tblBlogTag, 
                'page'=>$page,'revision'=>$revision
            ]);
        }
    }    


    function update(Request $request, $blog_id ){
        
        $input = $request->all();       
        $def_lang_id = session('admin_default_lang');     
        $input['title'] = $request->blog_title[$def_lang_id];        
        $input['short_description'] = $request->blog_short_desc[$def_lang_id];
        $input['description'] = $request->blog_desc[$def_lang_id];
        $blog_dtls = Blog::getBlogbyId($blog_id);

        $validate = $this->validateBlog($input, $blog_id);

        if ($validate->passes()) {
            
            $delete_file = [];
            $blog = Blog::find($blog_id); 
            $start_date = date('Y-m-d',strtotime($request->start_date));
        $end_date = $request->end_date?date('Y-m-d',strtotime($request->end_date)):'';

            $update_data = ['url' =>$this->getURL($request->url), 'features' => $request->features, 'publish' => $request->publish,'start_date' => $start_date,'end_date' => $end_date, 'comment'=>$request->comment, 'status'=>$request->status, 'publish_date'=>date("Y-m-d H:i:s", strtotime($request->publish_date)), 'updated_by'=>Auth::guard('admin_user')->user()->id];

            if(isset($request->uploadfile)) {
                
                $uploadDetails['path'] = Config::get('constants.blog_feature_img_path');
                $uploadDetails['file'] =  $request->uploadfile;
                $featureWidth = $this->getBlogConfigValue($system_name="FEATURE_IMAGE_WIDTH");
                $featureHeight = $this->getBlogConfigValue($system_name="FEATURE_IMAGE_HEIGHT");
                $uploadDetails['width'] =  $featureWidth;
                $uploadDetails['height'] = $featureHeight;

                $file_name = $this->uploadFileCustom($uploadDetails,'1');   
                
                $update_data['feature_image'] = $file_name;             

                $delete_file[] = Config::get('constants.blog_feature_img_path').'/'.$blog->feature_image;               
            }

            if(isset($request->metaimage)) {
                
                $uploadDetails['path'] = Config::get('constants.blog_socialshare_img_path');
                $uploadDetails['file'] =  $request->metaimage;
                $seoWidth = $this->getBlogConfigValue($system_name="SEO_IMAGE_WIDTH");
                $seoHeight = $this->getBlogConfigValue($system_name="SEO_IMAGE_HEIGHT");
                $uploadDetails['width'] =  $seoWidth;
                $uploadDetails['height'] = $seoHeight;

                $file_name = $this->uploadFileCustom($uploadDetails,'1');   
                
                $update_data['metaimage'] = $file_name;             

                $delete_file[] = Config::get('constants.blog_socialshare_img_path').'/'.$blog->metaimage;               
            }

            if(isset($request->fbimage)) {
                
                $uploadDetails['path'] = Config::get('constants.blog_socialshare_img_path');
                $uploadDetails['file'] =  $request->fbimage;
                $socialshareWidth = $this->getBlogConfigValue($system_name="SOCIALSHARE_IMAGE_WIDTH");
                $socialshareHeight = $this->getBlogConfigValue($system_name="SOCIALSHARE_IMAGE_HEIGHT");
                $uploadDetails['width'] =  $socialshareWidth;
                $uploadDetails['height'] = $socialshareHeight;

                $file_name = $this->uploadFileCustom($uploadDetails,'1');   
                
                $update_data['fbimage'] = $file_name;             

                $delete_file[] = Config::get('constants.blog_socialshare_img_path').'/'.$blog->fbimage;               
            }

            if(isset($request->twimage)) {
                
                $uploadDetails['path'] = Config::get('constants.blog_socialshare_img_path');
                $uploadDetails['file'] =  $request->twimage;
                $socialshareWidthT = $this->getBlogConfigValue($system_name="SOCIALSHARE_IMAGE_WIDTH");
                $socialshareHeightT = $this->getBlogConfigValue($system_name="SOCIALSHARE_IMAGE_HEIGHT");
                $uploadDetails['width'] =  $socialshareWidthT;
                $uploadDetails['height'] = $socialshareHeightT;

                $file_name = $this->uploadFileCustom($uploadDetails,'1');   
                
                $update_data['twimage'] = $file_name;             

                $delete_file[] = Config::get('constants.blog_socialshare_img_path').'/'.$blog->twimage;               
            }

            if(isset($request->insimage)) {
                
                $uploadDetails['path'] = Config::get('constants.blog_socialshare_img_path');
                $uploadDetails['file'] =  $request->insimage;
                $socialshareWidthI = $this->getBlogConfigValue($system_name="SOCIALSHARE_IMAGE_WIDTH");
                $socialshareHeightI = $this->getBlogConfigValue($system_name="SOCIALSHARE_IMAGE_HEIGHT");
                $uploadDetails['width'] =  $socialshareWidthI;
                $uploadDetails['height'] = $socialshareHeightI;

                $file_name = $this->uploadFileCustom($uploadDetails,'1');   
                
                $update_data['insimage'] = $file_name;             

                $delete_file[] = Config::get('constants.blog_socialshare_img_path').'/'.$blog->insimage;               
            }

            if(!empty($delete_file)) {
                $this->fileDelete($delete_file);
            }           

            if(isset($request->sliderimage)) {
                $uploadDetails = array();
                $uploadDetails['path'] = Config::get('constants.blog_slider_img_path');
                $sliderWidth = $this->getBlogConfigValue($system_name="SLIDER_IMAGE_WIDTH");
                $sliderHeight = $this->getBlogConfigValue($system_name="SLIDER_IMAGE_HEIGHT");
                $uploadDetails['width'] =  $sliderWidth;
                $uploadDetails['height'] = $sliderHeight;
                foreach($request->sliderimage as $file){
                  if(!empty($file)){
                      $slider = new BlogSlider;
                      $slider->blog_id = $blog_id;                      
                      $uploadDetails['file'] =  $file;
                      $file_name = $this->uploadFileCustom($uploadDetails);
                      $slider->image = $file_name;
                      $slider->save();
                  }                          
                }
            }             

            if(isset($request->blog_cat_id) && !empty($request->blog_cat_id)) {
                $blog_cat_id = [];
                foreach ($request->blog_cat_id as $key => $value) {
                    $blog_cat_data[] = ['blog_id'=>$blog->id, 'cat_id'=>$value];
                }                
                BlogCat::updateBlogInfo($blog->id,$blog_cat_data);
            }

            if(isset($request->related_blog) && !empty($request->related_blog)) {
                
                foreach ($request->related_blog as $key => $value) {
                    $blog_related_data[] = ['blog_id'=>$blog->id, 'related_id'=>$value];
                }                
                BlogRelated::updateBlogRelatedInfo($blog->id,$blog_related_data);
            }


            if(!empty($request->tags)){                 
                   
                $tagsArray = explode(',',$request->tags);                   
                // Delete all old tags from relation
                if(!empty($tagsArray)){
                    $oldTags = BlogTag::where('blog_id',$blog->id)->delete();
                
                    foreach($tagsArray as $k=>$tags){                        
                        $tagsdata = BlogTagList::where(['tag_title'=>$tags])->get();                        
                        if(isset($tagsdata[0]->id) && $tagsdata[0]->id!=''){                                
                            $tag_id = $tagsdata[0]->id;
                            $this->saveIntoTagsBlog($blog->id,$tag_id);
                        }else{

                            $tagsObj = new BlogTagList;
                            $tagsObj->tag_title = $tags;
                            $tagsObj->created_at = date('Y-m-d',time());
                            $tagsObj->updated_at = date('Y-m-d',time());

                            $ifTagAlreadyExist = BlogTagList::where(['tag_title'=>$tags])->count();
                            if($ifTagAlreadyExist==0){
                                 $tagsObj->save();
                                 $tag_id = $tagsObj->id;
                                 $this->saveIntoTagsBlog($blog->id,$tag_id);
                            }                       
                        }
                    }
                }             
            }else{
                $oldTags = BlogTag::where('blog_id',$blog->id)->delete();
            }
            
            $data_arr = $this->filterPageData($request);

            Blog::where(['id'=>$blog_id])->update($update_data);
            $oldpage_title = $blog_dtls->BlogDesc->blog_title;
            $oldpage_desc = $blog_dtls->BlogDesc->blog_desc;
            $oldpage_short_desc = $blog_dtls->BlogDesc->blog_short_desc;
            BlogDesc::updatebogDesc($data_arr, $blog_id); 

            //For revision insert data
            $revisions = BlogDescRevision::select('*')->groupBy('revision')->where('blog_id',$blog_id)->selectRaw('count(*) as total, revision')->get();
            $count = count($revisions);

            $newpage_title = $request->blog_title[session('admin_default_lang')];
            $newpage_desc = $request->blog_desc[session('admin_default_lang')];
            $newpage_short_desc = $request->blog_short_desc[session('admin_default_lang')];
            //dd($oldpage_title,$newpage_title);
            if($oldpage_title != $newpage_title || $oldpage_desc != $newpage_desc || $oldpage_short_desc!= $newpage_short_desc){
                
                BlogDescRevision::updatebogDescRevision($data_arr, $blog_id);
                $count = $count+1;
    
            }

            /*BlogDescRevision::updatebogDescRevision($data_arr, $blog_id);*/  

            /*update activity log start*/
            $action_type = "updated"; 
            $module_name = "blog";            
            $logdetails = "Admin has updated ".$input['title']." blog";
            $old_data = "";
            $new_data = "";
            $logdata = array('action_type' =>$action_type,'module_name' =>$module_name,'logdetails' =>$logdetails,'old_data' =>$old_data,'new_data' =>$new_data );
            $this->updateLogActivity($logdata);
            /*update activity log End*/

            if($request->submit_type == 'submit_continue') {

                return $response =['status'=>'update','count'=>$count, 'url' =>action('Admin\Blog\BlogController@edit',$blog_id)];
            }
            else {
                
                return $response =['status'=>'success','count'=>$count,'url' =>action('Admin\Blog\BlogController@index')];
            } 

        }
        else {

            $errors =  $validate->errors(); 
            return array('status'=>'fail','message'=>$errors);
        }      
                 
    }     
    
    function destroy($id){

        Blog::where('id', $id)->delete();
        BlogDesc::where('blog_id', $id)->delete();
        BlogCat::where('blog_id', $id)->delete();

        /*update activity log start*/
        $action_type = "deleted"; 
        $module_name = "blog";            
        $logdetails = "Admin has deleted blog id ".$id;
        $old_data = "";
        $new_data = "";
        $logdata = array('action_type' =>$action_type,'module_name' =>$module_name,'logdetails' =>$logdetails,'old_data' =>$old_data,'new_data' =>$new_data );
        $this->updateLogActivity($logdata);
        /*update activity log End*/ 

        return redirect()->action('Admin\Blog\BlogController@index')->with('succMsg', 'Record Deleted Successfully!');
    }

    function deleteSliderImages(Request $request){
       
       $deletes =  $request->ids;
       if(!empty($deletes)){
        $deleteid = explode(',', $deletes);
        $sliders = BlogSlider::whereIn('id', $deleteid)->get();
        $file_path = Config::get('constants.blog_slider_img_path');
        foreach($sliders as $slider){
          $file_path_with_name = $file_path.'/'.$slider->image; 
          $this->fileDelete( $file_path_with_name);
        }
        
        BlogSlider::whereIn('id', $deleteid)->delete();
        $response = ['status'=>'success'];

       }else{
         $response = ['status'=>'fail','msg'=>'Select any one image'];
       }
       return json_encode($response);
        
    }

    function changeStatus($id) {
        
        $static_blog = Blog::getBlogbyId($id);

        if($static_blog->status == '1') {
            $status = '0';
            $status_msg = 'Inactive';
        }
        else {
            $status = '1';
            $status_msg = 'Active';            
        }

        $static_blog->status = $status;
        $static_blog->updated_at = date('Y-m-d H:i:s');
        $static_blog->updated_by = Auth::guard('admin_user')->user()->id;
        $static_blog->save();
        return $status_msg;
    }

    private function validateBlog($input, $blog_id='') {   
     
        $rules['url'] = 'unique:'.$this->tblBlog.',url';

        if(!empty($blog_id) && !empty($input['url'])) {
            $rules['url'] = Rule::unique($this->tblBlog)->ignore($blog_id);
        }

        $rules['status'] = 'Required';
        $rules['title'] = 'Required';
        //$rules['short_description'] = 'Required';
        //$rules['description'] = 'Required';
        $rules['features'] = 'Required';
        $rules['comment'] = 'Required';
        $rules['publish'] = 'Required';        
        $rules['publish_date'] = 'Required';
        $rules['blog_cat_id'] = 'Required';     

        $error_msg['url.required'] = Lang::get('blog.enter_url_key');
        $error_msg['title.required'] = Lang::get('blog.enter_blog_title');        
        //$error_msg['short_description.required'] = Lang::get('blog.enter_short_description');
        //$error_msg['description.required'] = Lang::get('blog.enter_description');
        $error_msg['url.unique'] = Lang::get('blog.this_url_has_already_been_taken');        
        $error_msg['publish_date.required'] = Lang::get('blog.select_publish_date');
        $error_msg['blog_cat_id.required'] = Lang::get('blog.select_blog_category');            
        

        $validate = Validator::make($input, $rules, $error_msg);
        return $validate; 
    } 

    public function getURL($s) {
            $tr = array('ş','Ş','ı','İ','ğ','Ğ','ü','Ü','ö','Ö','Ç','ç');
            $eng = array('s','s','i','i','g','g','u','u','o','o','c','c');
            $s = str_replace($tr,$eng,$s);
            $s = strtolower($s);
            $s = preg_replace('/&.+?;/', '', $s);
            $s = preg_replace('/[^%a-z0-9 _-]/', '', $s);
            $s = preg_replace('/\s+/', '-', $s);
            $s = preg_replace('|-+|', '-', $s);
            $s = trim($s, '-');

            return $s;
    }

    protected function saveIntoTagsBlog($blog_id,$tag_id){
        
        // Check if tag is related to blog
        $is_tag_matched = BlogTag::where(['blog_id'=>$blog_id,'tag_id'=>$tag_id])->count();        
        if($is_tag_matched==0){
            $blogTagsObj = new BlogTag;
            $blogTagsObj->blog_id = $blog_id;
            $blogTagsObj->tag_id = $tag_id;
            $blogTagsObj->created_at = date('Y-m-d',time());
            $blogTagsObj->updated_at = date('Y-m-d',time());
            $blogTagsObj->save();
        }
        
    }

    public function getAllTags(){
                
        $tags = BlogTagList::getAllTags();   
                     
        if(!empty($tags)){
            foreach ($tags as $key => $value) {
                $alltag[]=$value['tag_title'];
            }
            $response = $alltag;
           }else{             
             $response = "Tags not found";
           }
        return json_encode($response);
        
    }

    public function getBlogConfigValue($system_name=null) {
        $system_val = '';
        if(!empty($system_name)){ 
          $system_val = \App\BlogConfig::getBlogValue($system_name);
        }  
        return $system_val;         
    }

    function blogrevision($blog_id) {
        $def_lang_id = session('admin_default_lang');
        $revisions = BlogDescRevision::select('*')->groupBy('revision')->where(['blog_id'=>$blog_id,'lang_id'=>$def_lang_id])->selectRaw('count(*) as total, revision')->get();
        
        
        $revision = count($revisions);
        return view('admin.blog.revisionList', ['revisions'=>$revisions,'revision'=>$revision,'blog_id'=>$blog_id]);

    }

    function restoreblogrevision($blog_id,$revision) {
        //dd("ok");
        $revisions = BlogDescRevision::select('*')->where('blog_id',$blog_id)->where('revision',$revision)->first();

        $data = BlogDesc::where(['blog_id'=>$blog_id])
                ->update(['blog_title' => $revisions->blog_title,'blog_desc' => $revisions->blog_desc,'blog_short_desc' => $revisions->blog_short_desc]);
                
        $permission = $this->checkUrlPermission('static_block');
        if($permission === true) {
        return redirect()->action('Admin\Blog\BlogController@index')->with('succMsg', 'Record updated Successfully!');
    }

    }
    
}
