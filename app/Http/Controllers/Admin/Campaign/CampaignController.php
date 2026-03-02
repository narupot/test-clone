<?php

namespace App\Http\Controllers\Admin\Campaign;

use App\Campaign;
use App\Http\Controllers\MarketPlace;
use Illuminate\Http\Request;
use Config;
use Lang;
use Auth;
use App\CustomCss;
use Exception;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Log;

class CampaignController extends MarketPlace
{
    
    public function __construct()
    {
        $this->middleware('admin.user');
    }
    
    public function indexMegaCampaign(Request $request)
    {
        try {
            $permission = $this->checkUrlPermission('campaign');
            if(!$permission === true) {throw new Exception("Permission Denied", 1);}

            $permission_arr['add'] = $this->checkMenuPermission('add_mega_campaign');
            $permission_arr['edit'] = $this->checkMenuPermission('edit_mega_campaign');
            $permission_arr['delete'] = $this->checkMenuPermission('delete_mega_campaign');
            
            $langcount = \App\Language::where(['isDefault'=>'1','status'=>'1'])->count();
            $language = ($langcount > 0) ? true : false;

            $megaCampaigns = Campaign::whereNull('parent_id')->get();
            $campaigns = Campaign::whereNull('parent_id')->orderBy('created_at', 'DESC')->get();

            return view('admin.campaign.index-megacampaign',[
                'megaCampaigns'=>$megaCampaigns,
                'campaigns'=>$campaigns,
                'permission_arr'=>$permission_arr
            ]);
        } catch (Exception $e) {
            return redirect()->action('Admin\AdminHomeController@index')
                         ->with('errorMsg', 'เกิดข้อผิดพลาด: ');
        }
    }

    public function createMegacampaign(Request $request)
    {
        try {
            $permission = $this->checkUrlPermission('add_mega_campaign');
            if(!$permission === true){ throw new Exception("Permission Denied", 1);}

            return view('admin.campaign.create-megacampaign');
        } catch (Exception $e) {
            return redirect()->action('Admin\AdminHomeController@index')
                        ->with('errorMsg', 'เกิดข้อผิดพลาด: ');
        }
        
    }
    
    public function storeMegacampaign(Request $request)
    {
        
        $permission = $this->checkUrlPermission('add_mega_campaign');
        if(!$permission === true){ throw new Exception("Permission Denied", 1);}
        $messages = [
            'name.required' => 'กรุณากรอกชื่อแคมเปญ',
            'name.string' => 'ชื่อแคมเปญต้องเป็นข้อความ',
            'name.max' => 'ชื่อแคมเปญต้องไม่เกิน 255 ตัวอักษร',
            'desc.string' => 'คำอธิบายต้องเป็นข้อความ',
            'file_image.image' => 'ไฟล์ที่อัปโหลดต้องเป็นรูปภาพ',
            'file_image.mimes' => 'ไฟล์ภาพต้องเป็นชนิด jpeg, png, jpg, gif หรือ svg เท่านั้น',
            'file_image.max' => 'ไฟล์ภาพต้องมีขนาดไม่เกิน 10MB',
        ];

        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'desc' => 'nullable|string',
            'file_image' => 'sometimes|nullable|image|mimes:jpeg,png,jpg,gif,svg,webp|max:10240',
        ], $messages);

        if ($validator->fails()) {
            $errors =  $validator->errors(); 
            return array('status'=>'fail','message'=>$errors,'validation'=>true);
        }
        
        $imagePath = null;
        try {
            $newCampaign = new Campaign();
            $newCampaign->name = $request->name;
            $newCampaign->parent_id = null;
            $newCampaign->status = $request->status??0;
            $newCampaign->desc = $request->desc;

            if($request->hasFile('file_image')){
                $fileImage = $request->file_image;
                $path = Config::get('constants.campaign_path').'/';
                $imageName = 'campaign_'.md5(microtime()).'.png';
                if(!is_dir($path)) {
                    mkdir($path, 0777, true);
                }
                $imagePath = $this->uploadImage($imageName,$fileImage,$path);
                if($imagePath){
                    $newCampaign->image = $imageName;
                }
            }
    
            $campaign = $newCampaign->save();
    
            return response()->json([
                'status' => 'success',
                'message' => 'Created Mega campaign successfully',
                'url' =>action('Admin\Campaign\CampaignController@editMegaCampaign',['campaign'=>$newCampaign->id]),
                'data' => $newCampaign->id
            ], 201);
            
        } catch (Exception $e) {
            if (!empty($imagePath) && file_exists($imagePath)) {
                unlink($imagePath);
            }
            Log::error('Create failed: ' . $e->getMessage());
            $errors =  $e->getMessage();
            return array('status'=>'fail','message'=>$errors);
        }
    }

    public function editMegaCampaign(Campaign $campaign)
    {
        $permission = $this->checkUrlPermission('edit_mega_campaign');
        if(!$permission === true){ throw new Exception("Permission Denied", 1);}
        return view('admin.campaign.edit-megacampaign',['campaign'=>$campaign]);
    }
    
    public function updateMegaCampaign(Campaign $campaign , Request $request)
    {
        $permission = $this->checkUrlPermission('edit_mega_campaign');
        if(!$permission === true){ throw new Exception("Permission Denied", 1);}
        $messages = [
            'name.required' => 'กรุณากรอกชื่อแคมเปญ',
            'name.string' => 'ชื่อแคมเปญต้องเป็นข้อความ',
            'name.max' => 'ชื่อแคมเปญต้องไม่เกิน 255 ตัวอักษร',
            'desc.string' => 'คำอธิบายต้องเป็นข้อความ',
            'file_image.image' => 'ไฟล์ที่อัปโหลดต้องเป็นรูปภาพ',
            'file_image.mimes' => 'ไฟล์ภาพต้องเป็นชนิด jpeg, png, jpg, gif หรือ svg เท่านั้น',
            'file_image.max' => 'ไฟล์ภาพต้องมีขนาดไม่เกิน 10MB',
        ];

        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255' ,
            'desc' => 'nullable|string',
            'file_image' => 'nullable|sometimes|image|mimes:jpeg,png,jpg,gif,svg,webp|max:10240',
        ], $messages);

        if ($validator->fails()) {
            $errors =  $validator->errors(); 
            return array('status'=>'fail','message'=>$errors,'validation'=>true);
        }
        
        try {
            $campaign->name = $request->name;
            $campaign->parent_id = $request->mega_campaign === '' ? null : $request->mega_campaign;
            $campaign->status = $request->status ?? 0;
            $campaign->desc = $request->desc;

            $fileImage = $request->file_image;
            $oldImage = $campaign->image;
            $path = Config::get('constants.campaign_path').'/';
            if($request->hasFile('file_image')){
                $imageName = 'campaign_'.md5(microtime()).'.png';
                if(!is_dir($path)) {
                    mkdir($path, 0777, true);
                }
                $imagePath = $this->uploadImage($imageName,$fileImage,$path);
                if($imagePath){
                    $campaign->image = $imageName;
                }
            }
            $rsCampaign = $campaign->save();
            
            if ($oldImage && file_exists($path.$oldImage)) {
                unlink($path.$oldImage);
            }
            return response()->json([
                'status' => 'success',
                'message' => 'Updated  successfully',
                'url' =>action('Admin\Campaign\CampaignController@editMegaCampaign',['campaign'=>$campaign->id]),
                'data' => $campaign
            ], 201);
            
        } catch (Exception $e) {
            Log::error('Update failed: ' . $e->getMessage());
            $errors =  $e->getMessage();
            return array('status'=>'error','message'=>$errors);
        }
    }

    public function destroyMegaCampaign(Campaign $campaign)
    {
        try {
            $permission = $this->checkUrlPermission('delete_mega_campaign');
            if(!$permission === true) throw new Exception("Permission Denied", 1);
            if( $campaign->campaign->count() > 0){
                throw new Exception("ไม่สามารถลบได้เ เนื่องจาก โค้ดส่วนลดถูกสร้างแล้ว", 1);
            }
            if ($campaign->delete()) {
                return redirect()->action('Admin\Campaign\CampaignController@indexMegaCampaign')->with('succMsg', Lang::get('admin_common.records_deleted_successfully'));
            }
        } catch (Exception $e) {
            Log::error('Delete failed: ' . $e->getMessage());
            return redirect()->action('Admin\Campaign\CampaignController@indexMegaCampaign')->with('errorMsg', 'Delete failed: '.$e->getMessage());

        }
        
    }


    public function indexSubCampaign(Request $request)
    {
        try {
            $permission = $this->checkUrlPermission('campaign');
            if(!$permission === true){ throw new Exception("Permission Denied", 1);}

            $permission_arr['add'] = $this->checkMenuPermission('add_sub_campaign');
            $permission_arr['edit'] = $this->checkMenuPermission('edit_sub_campaign');
            $permission_arr['delete'] = $this->checkMenuPermission('delete_sub_campaign');
            
            $langcount = \App\Language::where(['isDefault'=>'1','status'=>'1'])->count();
            $language = ($langcount > 0) ? true : false;

            $megaCampaigns = Campaign::whereNull('parent_id')->get();
            $campaigns = Campaign::whereNotNull('parent_id')
            ->has('megacampaign')
            ->orderBy('created_at', 'DESC')
            ->get();

            return view('admin.campaign.index-subcampaign',[
                'megaCampaigns'=>$megaCampaigns,
                'campaigns'=>$campaigns,
                'permission_arr'=>$permission_arr
            ]);
        } catch (Exception $e) {
            return redirect()->action('Admin\AdminHomeController@index')
                         ->with('errorMsg', 'เกิดข้อผิดพลาด: ' . $e->getMessage());
        }
    }

    public function createSubcampaign($megaCampaignId,Request $request)
    {
        try {
            $permission = $this->checkUrlPermission('add_sub_campaign');
            if(!$permission === true){ throw new Exception("Permission Denied", 1);}
            $megaCampaign = Campaign::whereNull('parent_id')->where('id',$megaCampaignId)->first();
            if (!$megaCampaign) {
                throw new Exception("ไม่พบเมกาแคมเปญนี้", 400);
            }
            return view('admin.campaign.create-subcampaign',['megaCampaign'=>$megaCampaign]);
        } catch (Exception $e) {
            return redirect()->action('Admin\AdminHomeController@indexSubCampaign')
                ->with('errorMsg', 'เกิดข้อผิดพลาด: ' . $e->getMessage());
        }
        
    }

    public function storeSubcampaign($megaCampaignId,Request $request)
    {
        $permission = $this->checkUrlPermission('add_sub_campaign');
        if(!$permission === true) throw new Exception("Permission Denied", 1);
        $megaCampaign = Campaign::whereNull('parent_id')->where('id',$megaCampaignId)->first();
        if (!$megaCampaign) {
            return response()->json(['status'=>'error','message'=>'ไม่พบ MEGA Campaign นี้ในระบบ']);
        }
        $messages = [
            'name.required' => 'กรุณากรอกชื่อแคมเปญ',
            'name.string' => 'ชื่อแคมเปญต้องเป็นข้อความ',
            'name.max' => 'ชื่อแคมเปญต้องไม่เกิน 255 ตัวอักษร',
            'desc.string' => 'คำอธิบายต้องเป็นข้อความ',
            'file_image.image' => 'ไฟล์ที่อัปโหลดต้องเป็นรูปภาพ',
            'file_image.mimes' => 'ไฟล์ภาพต้องเป็นชนิด jpeg, png, jpg, gif หรือ svg เท่านั้น',
            'file_image.max' => 'ไฟล์ภาพต้องมีขนาดไม่เกิน 10MB',
        ];

        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'desc' => 'nullable|string',
            'file_image' => 'sometimes|nullable|image|mimes:jpeg,png,jpg,gif,svg,webp|max:10240',
        ], $messages);

        if ($validator->fails()) {
            $errors =  $validator->errors(); 
            return response()->json(['status'=>'fail','message'=>$errors,'validation'=>true]);
        }
        $imagePath = null;
        try {

            $newCampaign = new Campaign();
            $newCampaign->name = $request->name;
            $newCampaign->parent_id = $megaCampaign->id;
            $newCampaign->status = $request->status??0;
            $newCampaign->desc = $request->desc;

            if($request->hasFile('file_image')){
                $fileImage = $request->file_image;
                $path = Config::get('constants.campaign_path').'/';
                $imageName = 'campaign_'.md5(microtime()).'.png';
                if(!is_dir($path)) {
                    mkdir($path, 0777, true);
                }
                $imagePath = $this->uploadImage($imageName,$fileImage,$path);
                if($imagePath){
                    $newCampaign->image = $imageName;
                }
            }
    
            $rsCampaign = $newCampaign->save();
    
            return response()->json([
                'status' => 'success',
                'message' => 'Created Mega Campaign successfully',
                'url' =>action('Admin\Campaign\CampaignController@editMegaCampaign',['campaign'=>$newCampaign->id]),
                'data' => $newCampaign
            ], 201);
            
        } catch (Exception $e) {
            if (!empty($imagePath) && file_exists($imagePath)) {
                unlink($imagePath);
            }
            Log::error('Create failed: ' . $e->getMessage());
            $errors =  $e->getMessage(); 
            return array('status'=>'error','message'=>$errors);
        }
        
    }

    public function editSubCampaign(Campaign $campaign)
    {
        $permission = $this->checkUrlPermission('edit_sub_campaign');
        if(!$permission === true){ throw new Exception("Permission Denied", 1);}
        $megaCampaigns = Campaign::whereNull('parent_id')->where('id', '!=', $campaign->id)->get();
        return view('admin.campaign.edit-subcampaign',['megaCampaigns'=>$megaCampaigns,'campaign'=>$campaign]);
    }
    
    public function updateSubCampaign(Campaign $campaign, Request $request)
    {
        $permission = $this->checkUrlPermission('edit_sub_campaign');
        if(!$permission === true){ throw new Exception("Permission Denied", 1);}
        $messages = [
            'name.required' => 'กรุณากรอกชื่อแคมเปญ',
            'name.unique' => 'ชื่อแคมเปญนี้มีอยู่แล้ว',
            'name.string' => 'ชื่อแคมเปญต้องเป็นข้อความ',
            'name.max' => 'ชื่อแคมเปญต้องไม่เกิน 255 ตัวอักษร',
            'desc.string' => 'คำอธิบายต้องเป็นข้อความ',
            'file_image.image' => 'ไฟล์ที่อัปโหลดต้องเป็นรูปภาพ',
            'file_image.mimes' => 'ไฟล์ภาพต้องเป็นชนิด jpeg, png, jpg, gif หรือ svg เท่านั้น',
            'file_image.max' => 'ไฟล์ภาพต้องมีขนาดไม่เกิน 10MB',
        ];

        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'desc' => 'nullable|string',
            'file_image' => 'nullable|sometimes|image|mimes:jpeg,png,jpg,gif,svg,webp|max:10240',
        ], $messages);

        if ($validator->fails()) {
            $errors =  $validator->errors();
            return array('status'=>'fail','message'=>$errors,'validation'=>true);
        }
        
        try {
            $campaign->name = $request->name;
            $campaign->status = $request->status ?? 0;
            $campaign->desc = $request->desc;

            $fileImage = $request->file_image;
            $oldImage = $campaign->image;
            $path = Config::get('constants.campaign_path').'/';
            if($request->hasFile('file_image')){
                $imageName = 'campaign_'.md5(microtime()).'.png';
                if(!is_dir($path)) {
                    mkdir($path, 0777, true);
                }
                $imagePath = $this->uploadImage($imageName,$fileImage,$path);
                if($imagePath){
                    $campaign->image = $imageName;
                }
            }
            $rsCampaign = $campaign->save();
            
            if ($oldImage && file_exists($path.$oldImage)) {
                unlink($path.$oldImage);
            }
            return response()->json([
                'status' => 'success',
                'message' => 'Updated  successfully',
                'url' =>action('Admin\Campaign\CampaignController@editMegaCampaign',['campaign'=>$campaign->id]),
                'data' => $campaign
            ], 201);
            
        } catch (Exception $e) {
            Log::error('Update failed: ' . $e->getMessage());
            $errors =  $e->getMessage();
            return array('status'=>'error','message'=>$errors);
        }
    }

    public function destroySubCampaign(Campaign $campaign)
    {
        $permission = $this->checkUrlPermission('delete_sub_campaign');
        if(!$permission === true){ throw new Exception("Permission Denied", 1);}
        if($campaign->discountCodeCriteia->count() > 0 ){ throw new Exception("ไม่สามารถลบได้เนื่องจาก โค้ดส่วนลดถูกสร้างในแคมเปญนี้แล้ว", 1);}
        try {
            if ($campaign->delete()) {
                return redirect()->action('Admin\Campaign\CampaignController@indexSubCampaign')->with('succMsg', Lang::get('admin_common.records_deleted_successfully'));
            }
        } catch (Exception $e) {
            Log::error('Delete failed: ' . $e->getMessage());
            return redirect()->action('Admin\Campaign\CampaignController@indexSubCampaign')->with('errorMsg', 'Delete failed: '.$e->getMessage());

        }
        
    }

}