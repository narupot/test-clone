<?php

namespace App\Http\Controllers\Admin\Groups;

use App\Http\Controllers\MarketPlace;
use App\ProductGroup;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Intervention\Image\Facades\Image; 
use Illuminate\Support\Facades\File;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\DB;
use Auth;

class GroupsController extends MarketPlace
{

    public function index()
    {
        $groups = ProductGroup::orderBy('sorting_no', 'asc')->get();

        $permission_arr = [
            'add'    => true, 
            'edit'   => true,
            'delete' => true,
        ];

        return view('admin.groups.index', compact('groups', 'permission_arr'));
    }

    public function create() {
     
        $group = null;
        return view('admin.groups.create', compact('group'));
    }

    public function store(Request $request)
    {
    
        $request->validate([
            'group_name'  => 'required|string|max:255|unique:product_group,name',
            'group_image' => 'required|string',
            'sort_order'  => 'nullable|integer',
            'status'      => 'nullable|in:0,1',
        ], [
            'group_name.required' => 'กรุณากรอกชื่อกลุ่มสินค้า',
            'group_name.unique'   => 'ชื่อกลุ่มสินค้านี้มีอยู่ในระบบแล้ว',
            'group_image.required' => 'กรุณาเลือกรูปภาพกลุ่มสินค้า',
        ]);
        // ---------------------------------------------

        $finalImagePathForDb = null; 
        if ($request->filled('group_image') && Str::startsWith($request->group_image, 'data:image/')) {
            
            $base64Image = $request->group_image;
            
            try {
                list($type, $base64Image) = explode(';', $base64Image);
                list(, $base64Image) = explode(',', $base64Image);
                $image_data = base64_decode($base64Image);

                // 2.2 Define file names and paths
                $randomName = Str::random(20);
                $svgFileName = $randomName . '.svg'; 
                $mainPath = public_path('images/product_groups');
                $svgFullPath = $mainPath . '/' . $svgFileName;

                if (!File::isDirectory($mainPath)) {
                    File::makeDirectory($mainPath, 0755, true);
                }

                $resizedImage = Image::make($image_data)->resize(250, 250); 
                $svgContent = $resizedImage->encode('data-url');

                $svgMarkup = <<<SVG
                    <svg xmlns="http://www.w3.org/2000/svg" width="850" height="850">
                    <image href="{$svgContent}" width="850" height="850"/>
                    </svg>
                    SVG;

                File::put($svgFullPath, $svgMarkup);
                $finalImagePathForDb = 'images/product_groups/' . $svgFileName; 

            } catch (\Exception $e) {
                Log::error("Error processing BASE64 image to SVG for new group: " . $e->getMessage());
                return redirect()->back()->withInput()->withErrors(['group_image' => 'เกิดข้อผิดพลาดในการประมวลผลรูปภาพ Base64 กรุณาลองใหม่']);
            }

        }
        elseif ($request->hasFile('group_image')) {
            
            $image = $request->file('group_image');
            $extension = $image->getClientOriginalExtension();

            $randomName = Str::random(20);
            $originalImageTempName = $randomName . '_original.' . $extension;
            $svgFileName = $randomName . '.svg';

            $mainPath = public_path('images/product_groups');
            if (!File::isDirectory($mainPath)) {
                File::makeDirectory($mainPath, 0755, true);
            }

            $originalImageTempFullPath = $mainPath . '/' . $originalImageTempName;
            $image->move($mainPath, $originalImageTempName);

            $svgFullPath = $mainPath . '/' . $svgFileName;

            try {
                $resizedImage = Image::make($originalImageTempFullPath)->resize(250, 250);
                $svgContent = $resizedImage->encode('data-url'); 

                $svgMarkup = <<<SVG
                    <svg xmlns="http://www.w3.org/2000/svg" width="850" height="850">
                    <image href="{$svgContent}" width="850" height="850"/>
                    </svg>
                    SVG;

                File::put($svgFullPath, $svgMarkup); 
                $finalImagePathForDb = 'images/product_groups/' . $svgFileName;

            } catch (\Exception $e) {
                \Log::error("Error processing standard file upload image to SVG: " . $e->getMessage());
                return redirect()->back()->withInput()->withErrors(['group_image' => 'เกิดข้อผิดพลาดในการประมวลผลรูปภาพ กรุณาลองใหม่']);
            } finally {
                if (File::exists($originalImageTempFullPath)) {
                    File::delete($originalImageTempFullPath);
                }
            }
        }

         DB::beginTransaction();
            try {
                $requestedSort = (int) $request->input('sort_order', 0);

                $maxSort = ProductGroup::max('sorting_no') ?? 0;
                if ($requestedSort <= 0 || $requestedSort > $maxSort + 1) {
                    $requestedSort = $maxSort + 1;
                }

                ProductGroup::where('sorting_no', '>=', $requestedSort)
                    ->increment('sorting_no');

                ProductGroup::create([
                    'name'         => $request->input('group_name'),
                    'image'        => $finalImagePathForDb,
                    'sorting_no'   => $requestedSort,
                    'status'       => $request->input('status', 0),
                    'updated_date' => now(),
                    'updated_by'   => optional(Auth::guard('admin_user')->user())->id,
                ]);

                DB::commit();

                return redirect()->back()->with('succMsg', "เพิ่มกลุ่มเรียบร้อย และจัดเรียงลำดับใหม่แล้ว");
            } catch (\Exception $e) {
                DB::rollBack();
                Log::error('Error saving product group: ' . $e->getMessage());
                return redirect()->back()->with('errorMsg', 'เกิดข้อผิดพลาด: ' . $e->getMessage());
            }
    }

    public function edit($id)
    {
        $group = ProductGroup::findOrFail($id);
       
        return view('admin.groups.create', compact('group'));
    }

    public function update(Request $request, $id)
    {
        $group = ProductGroup::findOrFail($id);

        $imageRule = [
            Rule::requiredIf(function () use ($request) {
                return false; 
            }),
            'nullable', 

            'string',
        ];

        // $request->validate([
        //     'group_name'  => 'required|string|max:255',
        //     'group_image' => $imageRule, 
        //     'sort_order'  => 'nullable|integer',
        //     'status'      => 'nullable|in:0,1',
        // ]);

        $request->validate([
            'group_name'  => 'required|string|max:255|unique:product_groups,group_name,' . $id,
            'group_image' => $imageRule,
            'sort_order'  => 'nullable|integer',
            'status'      => 'nullable|in:0,1',
        ], [
            'group_name.required' => 'กรุณากรอกชื่อกลุ่มสินค้า',
            'group_name.unique'   => 'ชื่อกลุ่มสินค้านี้มีอยู่ในระบบแล้ว',
            'group_image.required' => 'กรุณาเลือกรูปภาพกลุ่มสินค้า',
        ]);

        $finalImagePathForDb = $group->image; 

        if ($request->filled('group_image') && Str::startsWith($request->group_image, 'data:image/')) {
            
            $base64Image = $request->group_image;

            if ($group->image && File::exists(public_path($group->image))) {
                File::delete(public_path($group->image));
            }

            try {
                list($type, $base64Image) = explode(';', $base64Image);
                list(, $base64Image) = explode(',', $base64Image);
                $image_data = base64_decode($base64Image);

                preg_match('/data:image\/(\w+);/', $type, $matches);
                $extension = $matches[1] ?? 'jpg'; 
            
                $randomName = Str::random(20);
                $svgFileName = $randomName . '.svg'; 
                $mainPath = public_path('images/product_groups');
                $svgFullPath = $mainPath . '/' . $svgFileName;

                if (!File::isDirectory($mainPath)) {
                    File::makeDirectory($mainPath, 0755, true);
                }

                $resizedImage = Image::make($image_data)->resize(250, 250); 
                $svgContent = $resizedImage->encode('data-url');

                $svgMarkup = <<<SVG
                    <svg xmlns="http://www.w3.org/2000/svg" width="250" height="250">
                    <image href="{$svgContent}" width="250" height="250"/>
                    </svg>
                    SVG;

                File::put($svgFullPath, $svgMarkup);
                $finalImagePathForDb = 'images/product_groups/' . $svgFileName; 

            } catch (\Exception $e) {
                Log::error("Error processing BASE64 image to SVG for group ID {$group->id}: " . $e->getMessage());
                return redirect()->back()->withInput()->withErrors(['group_image' => 'เกิดข้อผิดพลาดในการประมวลผลรูปภาพ Base64 กรุณาลองใหม่']);
            }

        } 

        elseif ($request->hasFile('group_image')) { 
            
            if ($group->image && File::exists(public_path($group->image))) {
                File::delete(public_path($group->image));
            }

            $uploadedImage = $request->file('group_image');
            $extension = $uploadedImage->getClientOriginalExtension();

            $randomName = Str::random(20);
            $originalImageTempName = $randomName . '_original.' . $extension; 
            $svgFileName = $randomName . '.svg'; 


            $mainPath = public_path('images/product_groups');
            if (!File::isDirectory($mainPath)) {
                File::makeDirectory($mainPath, 0755, true);
            }

            $originalImageTempFullPath = $mainPath . '/' . $originalImageTempName;
            $uploadedImage->move($mainPath, $originalImageTempName);
            $svgFullPath = $mainPath . '/' . $svgFileName;

            try {
                $resizedImage = Image::make($originalImageTempFullPath)->resize(250, 250);
                $svgContent = $resizedImage->encode('data-url');

                $svgMarkup = <<<SVG
                    <svg xmlns="http://www.w3.org/2000/svg" width="250" height="250">
                    <image href="{$svgContent}" width="250" height="250"/>
                    </svg>
                    SVG;

                File::put($svgFullPath, $svgMarkup);
                $finalImagePathForDb = 'images/product_groups/' . $svgFileName; 
            } catch (\Exception $e) {
                Log::error("Error processing standard image to SVG for group ID {$group->id}: " . $e->getMessage());
                return redirect()->back()->withInput()->withErrors(['group_image' => 'เกิดข้อผิดพลาดในการประมวลผลรูปภาพ กรุณาลองใหม่']);
            } finally {
                if (File::exists($originalImageTempFullPath)) {
                    File::delete($originalImageTempFullPath);
                }
            }
        }


        // ---------- ส่วนอัปเดต sorting_no ----------
        DB::beginTransaction();
        try {
            $oldSort = $group->sorting_no;
            $newSort = (int) $request->input('sort_order', $oldSort);

            $maxSort = ProductGroup::max('sorting_no') ?? 0;
            if ($newSort <= 0 || $newSort > $maxSort) {
                $newSort = $maxSort;
            }

            if ($newSort != $oldSort) {
                if ($newSort < $oldSort) {
                    ProductGroup::whereBetween('sorting_no', [$newSort, $oldSort - 1])
                        ->increment('sorting_no');
                } else {
                    ProductGroup::whereBetween('sorting_no', [$oldSort + 1, $newSort])
                        ->decrement('sorting_no');
                }
            }

            // อัปเดตข้อมูล group เอง
                $group->update([
                    'name'         => $request->input('group_name'),
                    'image'        => $finalImagePathForDb,
                    'sorting_no'   => $newSort,
                    'status'       => $request->input('status', 0),
                    'updated_date' => now(),
                    'updated_by'   => optional(Auth::guard('admin_user')->user())->id,
                ]);

            DB::commit();
            return redirect()->back()->with('succMsg', 'อัปเดตกลุ่มเรียบร้อยและเรียงลำดับใหม่แล้ว');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("Error updating product group {$id}: " . $e->getMessage());
            return redirect()->back()->with('errorMsg', 'เกิดข้อผิดพลาดในการอัปเดตข้อมูล');
        }
    }

    public function destroy($id)
    {
        $group = ProductGroup::find($id);

        if (!$group) {
            return redirect()->back()->with('errorMsg', 'ไม่พบกลุ่มสินค้าที่ต้องการลบ');
        }

        if ($group->image && File::exists(public_path($group->image))) {
            File::delete(public_path($group->image));
        }

        if ($group->delete()) {
            return redirect()->back()->with('succMsg', 'ลบกลุ่มเรียบร้อยแล้ว');
        } else {
            return redirect()->back()->with('errorMsg', 'ไม่สามารถลบกลุ่มได้');
        }
    }

    public function updateSortOrder(Request $request, $id)
    {
     
        $request->validate([
            'sort_order' => 'required|integer'
        ]);

        $group = ProductGroup::find($id);

        if (!$group) {
            return response()->json([
                'status' => false,
                'message' => 'ไม่พบข้อมูลกลุ่มที่ระบุ'
            ], 404);
        }

        $group->sorting_no = $request->input('sort_order');
        $group->save();

        return response()->json([
            'status' => true,
            'message' => 'อัปเดตลำดับเรียบร้อยแล้ว',
            'data' => $group
        ]);
    }

    // public function changeStatus(Request $request, $id)
    // {
    //     $request->validate([
    //         'status' => 'required|in:0,1' 
    //     ]);

    //     $group = ProductGroup::find($id);

    //     if (!$group) {
    //         return response()->json([
    //             'status' => false,
    //             'message' => 'ไม่พบข้อมูลกลุ่มที่ระบุ'
    //         ], 404);
    //     }

    //     $group->status = $request->input('status');
    //     $group->save();

    //     return response()->json([
    //         'status' => true,
    //         'message' => 'เปลี่ยนสถานะเรียบร้อยแล้ว',
    //         'data' => $group
    //     ]);
    // }

    public function changeStatus(Request $request, $id)
    {
        $request->validate([
            'status' => 'required|in:0,1' 
        ]);

        $group = ProductGroup::find($id);
        $newStatus = (int)$request->input('status'); 

        if (!$group) {
            return response()->json([
                'status' => false,
                'message' => 'ไม่พบข้อมูลกลุ่มที่ระบุ'
            ], 404);
        }
        
        if ($newStatus === 0) {
        
            $productCount = DB::table('product as p')
                    ->join('category as c', 'p.cat_id', '=', 'c.id') 
                    ->join('parent_category as pc', 'c.parent_id', '=', 'pc.id')
                    ->where('pc.group_id', $id)
                    ->where('p.status', 1)
                    ->count();

            if ($productCount > 0) {
                return response()->json([
                    'status' => false,
                    'message' => "Deactivation failed: $productCount active product(s) must be unlinked from this group before its status can be changed to Inactive."
                ], 400);
            }
        }

        $group->status = $newStatus;
        $group->save();

        return response()->json([
            'status' => true,
            'message' => 'เปลี่ยนสถานะเรียบร้อยแล้ว',
            'data' => $group
        ]);
    }
    
    public function listJson()
    {
        $groups = ProductGroup::orderBy('sorting_no')->get();
  
        echo json_encode($groups);
    }
    
    public function updateSortOrderBulk(Request $request)
    {
        $sortedIds = $request->input('sorted_ids'); 
        // ["group-row-5","group-row-3","group-row-8"]

        if (!$sortedIds || !is_array($sortedIds)) {
            return response()->json(['status' => false, 'message' => 'ไม่พบข้อมูล']);
        }

        foreach ($sortedIds as $index => $rowId) {
            $id = str_replace('group-row-', '', $rowId);
            \App\ProductGroup::where('id', $id)->update(['sorting_no' => $index + 1]);
        }

        return response()->json(['status' => true, 'message' => 'อัปเดตลำดับเรียบร้อยแล้ว']);
    }





}
