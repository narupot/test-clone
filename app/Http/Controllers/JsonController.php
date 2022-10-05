<?php

namespace App\Http\Controllers;

use App\Http\Controllers\MarketPlace;
use Illuminate\Http\Request;
use App\AdminUser;
use App\Role;
use App\AdminLogDetail;
use Config;
use Intervention\Image\ImageManagerStatic as Image;

class JsonController extends MarketPlace {

    public function __construct() {
       
    }

    function mikeTestRoute(Request $request){
        //dd('22');
        $slider_data = \App\CmsSlider::where('id',2)->with(['sliderdesc','sliderCat'])->first();
        $test = \App\CmsSlider::getSliderProductTestMike($slider_data);
        $prd = \App\MongoProduct::where('cat_id',284)->orWhere('cat_id',285)->get();
        //$a  = 0.0011;
        //dd((float)$a);
        dd($prd,$test);
    }
    
    function pageLimit() {
        //echo '<pre>';print_r($request->role);die;
       $limit = array(10,20,50,100,150,200);
       foreach($limit as $limitRes){
           $myres[] = array('key' => $limitRes, 'value' => $limitRes);
       }
       echo json_encode($myres);
       die;
      // echo json_encode($users->toArray());
    }

    function generateImg(Request $request){
        if(isset($request->url) && isset($request->w) && isset($request->h)){
            
            $url=$request->url;

            $this->uploadFileRunTime($url,['w'=>$request->w,'h'=>$request->h]);
        }
    }

    function convertImage(Request $request,$mf=null,$img=null,$size=null,$sf=null){
        
        if(!$mf || !$img || !$size){
            echo '';exit;
        }
        $original_path = Config('constants.files_path').'/'.$mf.'/';
        if($sf){
            $original_path = $original_path.$sf.'/';
        }
        $original_img = $original_path.$img;
        if(!file_exists($original_img)){

            $img = 'product_image.jpg';
            $original_path  =Config::get('constants.placeholder_path');
        }

        $exp_size = explode('x',$size);
 
        $width = $exp_size[0]??0;
        $height = $exp_size[1]??0;

        $files['path'] = '';
        $files['original_path'] = rtrim($original_path,'/');
        $files['file_name'] = $img;
        $files['width'] = $width;
        $files['height'] = $height;  

        return $this->uploadFileRunTime($files,'showonly');

    }

    function imageResize(Request $request,$sf=null,$img=null){

        /**
        ** img src to pass action('JsonController@imageResize',['thumb_200x200',$image_name]);
        **ttp://192.168.1.250:8005/en/files/product/thumb_100x100/5a717a115a4776c386fb4818d803baca0PRDfe1bf42fa444d4c7309a2a9c9903aac9.jpeg
        **
        **/
        if(!empty($sf)){

            $original_path = Config::get('constants.product_original_image_path').'/';
            $original_img = $original_path.$img;

            if(!file_exists($original_img) || empty($img)){

                $img = 'product_image.jpg';
                $original_path  =Config::get('constants.placeholder_path');

            }
           
            $target_file = Config::get('constants.product_path').'/'.$sf.'/'.$img;
            $dir = Config::get('constants.product_path').'/';
            $path = $sf;

            $file_extention = explode(".", $img);
            $file_extention = strtolower(end($file_extention));
                
            if(!file_exists($target_file)){

                $exp_sf = explode('_',$sf);
                if(empty($exp_sf)){
                    echo '';exit;
                }

                $chksize = isset($exp_sf['1'])?explode('x',$exp_sf['1']):'';
                if(empty($chksize)){
                    echo '';exit;
                }

                $width = isset($chksize[0])?$chksize[0]:0;
                $height = isset($chksize[1])?$chksize[1]:null;
                if($width < 1){
                    echo '';exit;
                }

                $files = [];
                $files['original_path'] = rtrim($original_path,'/');
                $files['path'] = rtrim($dir.$sf,'/');
                $files['file_name'] = $img;
                $files['width'] = $width;
                $files['height'] = $height;
                //$this->uploadFileCustom($files);    
                $this->uploadFileRunTime($files);
                
            }

            header("Content-Type: image/".$file_extention."");
            $rf = readfile($target_file);
            
        }else{
            echo '';
        }
    }

    function imageOptimize(){
        $dir_arr = ['banner'];
        $file_path = Config::get('constants.files_path');
        
        foreach ($dir_arr as $key => $value) {
            $dirval = $file_path.'/'.$value;
            if (is_dir($dirval)) {
                
                $path = realpath($dirval);

                $images = $this->recurseImages($path);
            }
            # code...
        }
    }

    function recurseImages($dir, $imgtype=null)
    {
        $images = $initialBytes = $bytesSaved = 0;

        $stack[] = $dir;
        
        while ($stack) {
            sort($stack);
            $thisdir = array_pop($stack);
        
            if ($dircont = scandir($thisdir)) {
                for ($i = 0; isset($dircont[$i]); $i++) {
                    if ($dircont[$i]{0} !== '.') {
                        $current_file = $thisdir .'/'. $dircont[$i];
                        
                        if (!is_link($current_file)) {
                            if (is_dir($current_file)) {
                                $stack[] = $current_file;
                                /*if($current_file !='/home/thaitrade/www.thaitrade.com/pub/media/catalog/product/cache'){
                                    $stack[] = $current_file;
                                }*/
                                
                            } else {
                                switch (strtoupper(pathinfo($current_file, PATHINFO_EXTENSION))) {
                                    case 'GIF':
                                            $bytesSaved = $this->opt_image($current_file,'GIF');
                                            
                                        break;
                                    case 'PNG':
                                            $bytesSaved = $this->opt_image($current_file,'PNG');
                                            
                                        break;
                                    case 'JPG':
                                    case 'JPEG':
                                            $bytesSaved = $this->opt_image($current_file,'JPG');

                                        break;
                                }
                            }
                        }
                    }
                }
            }
        }

        return $images;
    }

    function opt_image($target_file,$type)
    {
        $old_filesize = filesize($target_file);
        $img_name = basename($target_file);
        //echo $img_name.'<br>';
        $image = realpath($target_file);
        if($type == 'JPG'){
            exec('jpegoptim -f -o --strip-all --strip-icc --strip-iptc '.$image);   

        }elseif($type == 'GIF'){
            exec('gifsicle -b --careful -k 256 -O3 '.$image.' '.$image.'', $result, $return_var);

        }elseif($type == 'PNG'){
            exec('optipng -strip all '.$image);

        }
        clearstatcache();
        $new_filesize = filesize($target_file);

        if($old_filesize != $new_filesize){
            echo $img_name.' => '.$old_filesize.' == '.$new_filesize.'<br>';
        }
    }
}
