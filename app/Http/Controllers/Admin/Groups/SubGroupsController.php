<?php

namespace App\Http\Controllers\Admin\Groups;

use App\Http\Controllers\MarketPlace;
use App\ProductSubGroup;
use App\ProductGroup;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Intervention\Image\Facades\Image;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\DB;
use Auth;

class SubGroupsController extends MarketPlace
{
    public function index()
    {
        $groups = ProductGroup::with('subgroups', 'updater')
        ->where('status', 1)
        ->orderBy('sorting_no', 'asc')
        ->get();

        $permission_arr = [
            'add'    => true,
            'edit'   => true,
            'delete' => true,
            'add_main_group' => true,
            'add_sub_group' => true,
        ];
        
        return view('admin.sub-groups.index', compact('groups', 'permission_arr'));
    }

    public function create() {
        $subgroup = null;
        $groups = ProductGroup::orderBy('sorting_no', 'asc')->get();
        return view('admin.sub-groups.create', compact('groups', 'subgroup'));
    }

    public function store(Request $request)
{
    $request->validate([
        'subgroup_name' => 'required|string|max:255|unique:product_subgroup,subgroup_name',
        'group_image'   => 'nullable|string', 
        'sort_order'    => 'nullable|integer',
        'pro_group_id'  => 'required|exists:product_group,id',
        'status'        => 'nullable|in:0,1',
    ],
    [
            'subgroup_name.required' => 'กรุณากรอกชื่อหมวดสินค้า',
            'subgroup_name.unique'   => 'ชื่อหมวดสินค้านี้มีอยู่ในระบบแล้ว',
            'subgroup_name.required' => 'กรุณาเลือกรูปภาพหมวดสินค้า',
        ]);

    // “subgroup_name ห้ามซ้ำ ภายในกลุ่มหลักเดียวกัน (pro_group_id เดียวกัน)”
    // $request->validate([
    //     'subgroup_name' => [
    //         'required',
    //         'string',
    //         'max:255',
    //         Rule::unique('product_subgroup', 'subgroup_name')
    //             ->ignore($id) // <-- ยกเว้น record ตัวเอง
    //             ->where(fn ($query) => $query->where('pro_group_id', $request->pro_group_id)),
    //     ],
    //     'group_image'   => 'nullable|string', 
    //     'sort_order'    => 'nullable|integer',
    //     'pro_group_id'  => 'required|exists:product_group,id',
    //     'status'        => 'nullable|in:0,1',
    // ]);  

    $finalImagePathForDb = null;

    $imageData = $request->input('group_image');

    if (!empty($imageData)) {
        
        list($type, $imageData) = explode(';', $imageData);
        list(, $imageData) = explode(',', $imageData);
        $imageData = base64_decode($imageData);

        $extension = 'jpg'; 
        $randomName = Str::random(20);
        $fileName = $randomName . '.' . $extension;
        $mainPath = public_path('images/product_groups/subgroups'); 
        
        if (!File::isDirectory($mainPath)) {
            File::makeDirectory($mainPath, 0755, true);
        }

        $fullPath = $mainPath . '/' . $fileName;

        try {

            $image = Image::make($imageData);

            $image->save($fullPath);
            $finalImagePathForDb = 'images/product_groups/subgroups/' . $fileName;


        } catch (\Exception $e) {
            Log::error("Error processing Base64 image for SubGroup: " . $e->getMessage());
            return redirect()->back()->withInput()->withErrors(['group_image' => 'เกิดข้อผิดพลาดในการประมวลผลรูปภาพที่ครอปแล้ว กรุณาลองใหม่']);
        }
    }

        DB::beginTransaction();
        try {
            $requestedSortNo = $request->input('sort_order', null);

            if ($requestedSortNo) {
                ProductSubGroup::where('pro_group_id', $request->input('pro_group_id'))
                    ->where('sorting_no', '>=', $requestedSortNo)
                    ->increment('sorting_no');
            } else {
                $requestedSortNo = ProductSubGroup::where('pro_group_id', $request->input('pro_group_id'))->max('sorting_no') + 1;
            }

            ProductSubGroup::create([
                'subgroup_name' => $request->input('subgroup_name'),
                'images'        => $finalImagePathForDb,
                'sorting_no'    => $requestedSortNo,
                'status'        => $request->input('status') ?? 0,
                'pro_group_id'  => $request->input('pro_group_id'),
                'updated_date'  => now(),
                'updated_by'    => optional(Auth::guard('admin_user')->user())->id,
            ]);

            DB::commit();

            return redirect()
                ->action('Admin\Groups\SubGroupsController@index')
                ->with('succMsg', 'เพิ่มหมวดเรียบร้อยแล้ว และเรียงลำดับใหม่สำเร็จ');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error saving subgroup with sorting reorder: ' . $e->getMessage());
            return redirect()->back()->withInput()->with('errorMsg', 'เกิดข้อผิดพลาดในการบันทึกข้อมูล');
        }
    }

    public function edit($id)
    {
        $subgroup = ProductSubGroup::findOrFail($id);
        $groups = ProductGroup::with('subgroups')->orderBy('sorting_no', 'asc')->get();

        $treeData = collect();

        foreach ($groups as $group) {
            $treeData->push([
                'id' => 'group_' . $group->id,
                'parent' => '#',
                'text' => $group->name,
                'state' => ['opened' => true],
                'icon' => 'fas fa-folder'
            ]);

            foreach ($group->subgroups as $sub) {
                $treeData->push([
                    'id' => $sub->id,
                    'parent' => 'group_' . $sub->pro_group_id,
                    'text' => $sub->subgroup_name,
                    'icon' => 'fas fa-tag'
                ]);
            }
        }

        return view('admin.sub-groups.create', compact('groups', 'subgroup', 'treeData'));
    }

    public function update(Request $request, $id)
{
    $group = ProductSubGroup::findOrFail($id);

    $request->validate([
        'subgroup_name' => 'required|string|max:255|unique:product_subgroup,subgroup_name,' . $id,
        'group_image'   => 'nullable|string',
        'sort_order'    => 'nullable|integer',
        'pro_group_id'  => 'required|exists:product_group,id',
        'status'        => 'nullable|in:0,1',
    ], 
    [
            'subgroup_name.required' => 'กรุณากรอกชื่อหมวดสินค้า',
            'subgroup_name.unique'   => 'ชื่อหมวดสินค้านี้มีอยู่ในระบบแล้ว',
            'subgroup_name.required' => 'กรุณาเลือกรูปภาพหมวดสินค้า',
    ]);

//subgroup_name ห้ามซ้ำ ภายในกลุ่มหลักเดียวกัน (pro_group_id เดียวกัน)
    // $request->validate([
    //     'subgroup_name' => [
    //         'required',
    //         'string',
    //         'max:255',
    //         Rule::unique('product_subgroup', 'subgroup_name')
    //             ->ignore($id) // <-- ยกเว้น record ตัวเอง
    //             ->where(fn ($query) => $query->where('pro_group_id', $request->pro_group_id)),
    //     ],
    //     'group_image'   => 'nullable|string', 
    //     'sort_order'    => 'nullable|integer',
    //     'pro_group_id'  => 'required|exists:product_group,id',
    //     'status'        => 'nullable|in:0,1',
    // ]);

    DB::beginTransaction();
    try {
        $finalImagePathForDb = $group->images;
        $base64Image = $request->input('group_image');

        if ($base64Image && Str::startsWith($base64Image, 'data:image/')) {

            if ($group->images && File::exists(public_path($group->images))) {
                File::delete(public_path($group->images));
            }

            $randomName = Str::random(20);
            $svgFileName = $randomName . '.svg';
            $mainPath = public_path('images/product_groups');

            if (!File::isDirectory($mainPath)) {
                File::makeDirectory($mainPath, 0755, true);
            }

            $svgFullPath = $mainPath . '/' . $svgFileName;

            try {
                $resizedImage = Image::make($base64Image)->resize(250, 250);
                $svgContent = $resizedImage->encode('data-url');
                $svgMarkup = <<<SVG
                    <svg xmlns="http://www.w3.org/2000/svg" width="250" height="250">
                        <image href="{$svgContent}" width="250" height="250"/>
                    </svg>
                SVG;

                File::put($svgFullPath, $svgMarkup);
                $finalImagePathForDb = 'images/product_groups/' . $svgFileName;

            } catch (\Exception $e) {
                \Log::error("Error processing Base64 image to SVG for subgroup ID {$group->id}: " . $e->getMessage());
                DB::rollBack();
                return redirect()->back()->withInput()->withErrors([
                    'group_image' => 'เกิดข้อผิดพลาดในการประมวลผลรูปภาพ กรุณาลองใหม่: ' . $e->getMessage()
                ]);
            }
        }

        $newSort = $request->input('sort_order', $group->sorting_no);
        $oldSort = $group->sorting_no;
        $groupId = $request->input('pro_group_id');

        if ($newSort != $oldSort) {
            if ($newSort > $oldSort) {
                ProductSubGroup::where('pro_group_id', $groupId)
                    ->whereBetween('sorting_no', [$oldSort + 1, $newSort])
                    ->decrement('sorting_no');
            } else {
                ProductSubGroup::where('pro_group_id', $groupId)
                    ->whereBetween('sorting_no', [$newSort, $oldSort - 1])
                    ->increment('sorting_no');
            }
        }

        $group->update([
            'subgroup_name' => $request->input('subgroup_name'),
            'pro_group_id'  => $groupId,
            'images'        => $finalImagePathForDb,
            'sorting_no'    => $newSort,
            'status'        => $request->input('status', 0),
            'updated_date'  => now(),
            'updated_by'    => optional(Auth::guard('admin_user')->user())->id,
        ]);

        \App\ParentCategory::where('subgroup_id', $group->id)
            ->update(['group_id' => $groupId]);

        DB::commit();

        return redirect()->back()->with('succMsg', 'อัปเดตหมวดเรียบร้อยแล้ว และอัปเดต ParentCategory สำเร็จ');
    } catch (\Exception $e) {
        DB::rollBack();
        \Log::error('Error updating subgroup reorder: ' . $e->getMessage());
        return redirect()->back()->with('errorMsg', 'เกิดข้อผิดพลาดในการอัปเดตข้อมูล');
    }
}


    public function destroy($id)
    {
        $group = ProductSubGroup::find($id);

        if (!$group) {
            return redirect()->back()->with('errorMsg', 'ไม่พบกลุ่มสินค้าที่ต้องการลบ');
        }

        if ($group->images && File::exists(public_path($group->images))) {
            File::delete(public_path($group->images));
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

        $group = ProductSubGroup::find($id);

        if (!$group) {
            return response()->json(['status' => false, 'message' => 'ไม่พบข้อมูลกลุ่มที่ระบุ'], 404);
        }

        $group->sorting_no = $request->input('sort_order');
        $group->save();

        return response()->json(['status' => true, 'message' => 'อัปเดตลำดับเรียบร้อยแล้ว', 'data' => $group]);
    }

    public function changeStatus(Request $request, $id)
    {
        $request->validate([
            'status' => 'required|in:0,1'
        ]);

        $group = ProductSubGroup::find($id);

        if (!$group) {
            return response()->json(['status' => false, 'message' => 'ไม่พบข้อมูลกลุ่มที่ระบุ'], 404);
        }

        $group->status = $request->input('status');
        $group->save();

        return response()->json(['status' => true, 'message' => 'เปลี่ยนสถานะเรียบร้อยแล้ว', 'data' => $group]);
    }

    public function listJson()
    {
        $groups = ProductSubGroup::orderBy('sorting_no')->get();
        echo json_encode($groups);
    }

    public function treeView(Request $request)
    {
        $query = $request->input('q');

        $groups = ProductGroup::with(['subgroups', 'updater'])
            ->when($query, function ($q) use ($query) {
                $q->where('name', 'like', '%' . $query . '%')
                ->orWhereHas('subgroups', function ($sub) use ($query) {
                    $sub->where('subgroup_name', 'like', '%' . $query . '%');
                });
            })
            ->get();

        return view('admin.sub-groups.index', compact('groups'));
    }

    public function getByGroup($groupId)
    {
        try {
            $subGroups = ProductSubGroup::where('pro_group_id', $groupId)
                ->orderBy('sorting_no', 'asc')
                ->get();

            $data = $subGroups->map(function ($item) {
                return [
                    'id'            => $item->id,
                    'subgroup_name' => $item->subgroup_name,
                    'images'        => $item->images ? asset($item->images) : null,
                    'sorting_no'    => $item->sorting_no,
                    'pro_group_id'  => $item->pro_group_id,
                    'status'        => $item->status == 1 ? true : false,
                    'updated_by'    => $item->updater ? $item->updater->nick_name : null,
                    'updated_date'  => $item->updated_date ? Carbon::parse($item->updated_date)->format('Y-m-d H:i') : null,
                ];
            });

            return response()->json([
                'success' => true,
                'data'    => $data
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function updateOrder(Request $request)
    {
        foreach ($request->order as $item) {
            ProductSubGroup::where('id', $item['id'])
                ->update(['sorting_no' => $item['sorting_no']]);
        }
        return response()->json(['success' => true]);
    }



}