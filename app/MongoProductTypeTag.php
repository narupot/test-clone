<?php

namespace App;

use Jenssegers\Mongodb\Eloquent\Model as Molequent;

class MongoProductTypeTag extends Molequent
{
    protected $connection = 'mongodb';
    protected $collection = 'product_type_tag';

    protected $primaryKey = '_id';
    protected $keyType = 'string';
    public $incrementing = false;

    public $timestamps = true;

    protected $fillable = [
        'product_type_id',
        'tag',
        'tag_status',
        'created_at',
        'updated_at',
    ];

    public static function upsertTag($productTypeId, array $tagData)
    {
        return self::updateOrCreate(
            [
                'product_type_id' => (int) $productTypeId,
                'tag'             => $tagData['tag'],
            ],
            array_merge($tagData, [
                'updated_at' => now(),
            ])
        );
    }

    public static function updateProductTypeTag($productTypeId, $productTagCollection)
    {
        
        self::where('product_type_id', $productTypeId)->delete();


        foreach ($productTagCollection as $tagRow) {
            self::create([
                'product_type_id' => $productTypeId,
                'tag'             => $tagRow->tag,
                'tag_status'      => $tagRow->tag_status ?? 1,
                'created_at'      => $tagRow->created_at ?? now(),
            ]);
        }
    }

}