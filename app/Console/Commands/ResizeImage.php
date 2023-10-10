<?php

namespace App\Console\Commands;
use Illuminate\Console\Command;
use Intervention\Image\ImageManagerStatic as Image;
use DB;
use File;
use Config;
class ResizeImage extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'ResizeImage:resizeimage';

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
            $totalProductCount = \App\Product::where('status', '1')->where('id', 1028)->count();
            $countPerpage = 500;
            $totpages = ceil($totalProductCount/$countPerpage); 
            $original_image_path = Config::get('constants.product_original_image_path');
            $product_img_url = Config::get('constants.product_img_url').'original/';
            for($i=1; $i<=$totpages; $i++){
                $offset =  $countPerpage * ($i-1);
                $results = \App\Product::where('status', '1')->where('id', 1028)->with('images')->offset($offset)->limit($countPerpage);
                foreach($results as $result){
                    foreach($result->images as $value){
                        $original_image = $original_image_path.'/'.$value->image;
                        $sizeImage = @filesize($original_image);
                        $sizeImage = floor($sizeImage/1024);
                        if($sizeImage > 2048){
                            $width = Image::make($original_image)->width(); 
                            $height = Image::make($original_image)->height();
                            $percent = .5;
                            $newWidth = $width*$percent;
                            $newHeight = $height*$percent;
                            Image::make($original_image)->fit($newWidth, $newHeight, function ($constraint) {
                                    $constraint->aspectRatio();
                                    $constraint->upsize();
                                })->save($original_image);

                        }    
                    }
                }
            }    
    }
  
} 