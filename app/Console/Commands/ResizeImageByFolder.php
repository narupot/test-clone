<?php

namespace App\Console\Commands;
use Illuminate\Console\Command;
use Intervention\Image\ImageManagerStatic as Image;
use DB;
use File;
use Config;
class ResizeImageByFolder extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'ResizeImageByFolder:resizeimagebyfolder';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'It will delete buyer and seller api log';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {       
            $logdata = [];
            $original_image_path = Config::get('constants.product_original_image_path');
            $product_img_url = Config::get('constants.product_img_url').'original/';
            $results = scandir($original_image_path);
            foreach($results as $image){
                $original_image = $original_image_path.'/'.$image;
                $sizeImage = @filesize($original_image);
                $sizeImage = floor($sizeImage/1024);
                if($sizeImage > 2048){
                    $width = Image::make($original_image)->width(); 
                    $height = Image::make($original_image)->height();
                    $percent = .5;
                    $newWidth = floor($width*$percent);
                    $newHeight = floor($height*$percent);
                    
                    $newWidth = (int) $newWidth;
                    $newHeight = (int) $newHeight;
                    $msg = 'done';
                    try {
                        if(!empty($newWidth) && !empty($newHeight)){
                            Image::make($original_image)->fit($newWidth, $newHeight, function ($constraint) {
                                $constraint->aspectRatio();
                                $constraint->upsize();
                            })->save($original_image);
                        }

                    } catch (\Exception $e) {
                        $msg = $e->getMessage(); 
                    }
                    

                    $logdata[] = ['product_id' => '000', 'product_sku'=>'BYSCAN', 'image'=>$image, 'msg'=>$msg, 'created_at'=>date('Y-m-d H:i:s')];

                }    
            }
            \App\ResizeImageLog::insert($logdata);

            echo 'done';        
    }
  
} 