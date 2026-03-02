<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\User;
use App\Category;
use App\CategoryDesc;
use App\ProductTypeTag;
use Illuminate\Foundation\Testing\RefreshDatabase;

class CategoryControllerTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_can_update_category_with_desc_and_keywords()
    {
        // สร้าง admin user
        $admin = User::factory()->create([
            'is_admin' => 1,
        ]);

        // สร้าง category และ desc เดิม
        $category = Category::factory()->create([
            'url' => 'old-url',
            'img' => null,
        ]);

        $desc = CategoryDesc::factory()->create([
            'cat_id' => $category->id,
            'lang_id' => session('default_lang') ?? 1,
            'category_name' => 'Old Name',
        ]);

        // จำลอง request data
        $data = [
            'url' => 'new-category-url',
            'parent_id' => 0,
            'status' => 1,
            'cat_comment' => 'Updated comment',
            'category_name' => 'Updated Category',
            'meta_title' => 'Updated Meta Title',
            'meta_keyword' => 'tag1, tag2',
            'meta_description' => 'Updated meta description',
            'cat_description' => 'Updated category description',
            'keywords' => json_encode(['tag1', 'tag2']),
        ];

        // login admin และ post request
        $response = $this->actingAs($admin, 'admin_user')
                         ->post(route('admin.category.update', $category->id), $data);

        // ตรวจสอบ redirect
        $response->assertStatus(302);

        // ตรวจสอบว่า category ถูก update
        $this->assertDatabaseHas('categories', [
            'id' => $category->id,
            'url' => 'new-category-url',
            'comment' => 'Updated comment',
        ]);

        // ตรวจสอบว่า CategoryDesc ถูก update
        $this->assertDatabaseHas('category_desc', [
            'cat_id' => $category->id,
            'category_name' => 'Updated Category',
            'meta_title' => 'Updated Meta Title',
            'meta_keyword' => 'tag1, tag2',
        ]);

        // ตรวจสอบว่า ProductTypeTag ถูก insert
        $this->assertDatabaseHas('product_type_tags', [
            'product_type_id' => $category->id,
            'tag' => 'tag1',
        ]);

        $this->assertDatabaseHas('product_type_tags', [
            'product_type_id' => $category->id,
            'tag' => 'tag2',
        ]);
    }
}
