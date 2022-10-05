<?php 

return [

    /*
    |--------------------------------------------------------------------------
    | Default Filesystem Disk
    |--------------------------------------------------------------------------
    |
    | Here you may specify the default filesystem disk that should be used
    | by the framework. A "local" driver, as well as a variety of cloud
    | based drivers are available for your choosing. Just store away!
    |
    | Supported: "local", "ftp", "s3", "rackspace"
    |
    */

    'default' => 'local',

    /*
    |--------------------------------------------------------------------------
    | Default Cloud Filesystem Disk
    |--------------------------------------------------------------------------
    |
    | Many applications store files both locally and in the cloud. For this
    | reason, you may specify a default "cloud" driver here. This driver
    | will be bound as the Cloud disk implementation in the container.
    |
    */

    'cloud' => 's3',

    /*
    |--------------------------------------------------------------------------
    | Filesystem Disks
    |--------------------------------------------------------------------------
    |
    | Here you may configure as many filesystem "disks" as you wish, and you
    | may even configure multiple disks of the same driver. Defaults have
    | been setup for each driver as an example of the required options.
    |
    */

    'disks' => [

        'local' => [
            'driver' => 'local',
            'root' => storage_path('app'),
        ],

        'public' => [
            'driver' => 'local',
            'root' => storage_path('app/public'),
            'visibility' => 'public',
        ],

        's3' => [
            'driver' => 's3',
            'key' => env('S3_KEY_ID'),
            'secret' =>env('S3_SECRET_KEY'),
            'region' => env('S3_REGION'),
            'bucket' => env('S3_BUCKET'),
            'visibility' => 'public',
        ],

        /* 
        ************************ VERY IMPORTANT ****************************
        * Following disk is added to manage files/medias of Modules/New packages
        * This disk will help to access the files and images from directory /app/Modules/{modules_dir_path}
        * @Author::Dinesh Kumar Kovid | Start | Date : 18/12/2017
        */
        'modules' => [
            'driver' => 'local',
            'root' => base_path("app/Modules/"),
            'visibility' => 'public',
            'url'    => '/app/Modules/',
        ],

        /* 
        ************************ VERY IMPORTANT ****************************
        * Following disk is added to manage files/medias of Modules/New packages
        * This disk will help to access the files and images from directory /app/Modules/{modules_dir_path}
        * @Author::Dinesh Kumar Kovid | End | Date : 18/12/2017
        */

    ],
    'all_product_folders'=> ['original','large_405','medium_265360','thumb_105145','thumb_185185'],
    'sub_folders'=>['large_405','medium_265360','thumb_105145','thumb_185185'],
    'all_blog_folders'=>['original_blog_image','large_images','blog-570x402','small_images'],
    'blog_sub_folders'=>['large_images','blog-570x402','small_images']

];
