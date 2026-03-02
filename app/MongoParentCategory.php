<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Jenssegers\Mongodb\Eloquent\Model as Molequent;

class MongoParentCategory extends Molequent
{
    protected $connection = 'mongodb';
    protected $collection = 'parent_category';

    protected $fillable = [
        'mysql_id',
        'category_name',
        'url',
        'img',
        'is_deleted',
        'meta_title',
        'meta_keyword',
        'meta_description',
        'cat_description',
        'sorting_no',
        'group_id',
        'subgroup_id',
        'created_at',
        'created_by',
        'updated_at',
        'updated_by'
    ];

    public static function updateData($parentCategory)
    {
        if (!$parentCategory) {
            return null;
        }

        $data = $parentCategory->toArray();
        $data['mysql_id'] = $parentCategory->id;

        unset($data['id']);

        return self::create($data); // ใช้ mass assignment ได้แล้ว
    }
}
