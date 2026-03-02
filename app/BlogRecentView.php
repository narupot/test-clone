<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class BlogRecentView extends Model
{  
    protected $table = 'blog_recent_view';
    
    public static function insertRecentViewBlog($blog_id,$ip_address) {     

            $recentblog = new BlogRecentView;
            $recentblog->blog_id = $blog_id;
            $recentblog->ip_address = $ip_address;
            $recentblog->save();                   
    }

    public static function updateRecentViewBlog($blog_id,$ip_address) { 

            $countblog = Self::where(['blog_id'=>$blog_id,'ip_address'=>$ip_address])->count();
            if($countblog > 0){
                $updated_at = date('y-m-d h:i:s');
                self::where(['blog_id'=>$blog_id])
                ->update(['updated_at' => $updated_at]);
            }else{
                Self::insertRecentViewBlog($blog_id,$ip_address);
            }
            
    }  

}
