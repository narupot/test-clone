<?php

namespace App\Http\Controllers\Admin\DiscountCode;

use App\Campaign;
use App\Http\Controllers\MarketPlace;
use Illuminate\Http\Request;
use Config;
use Lang;
use Auth;
use App\CustomCss;
use Exception;
use App\DiscountCode;
use App\DiscountCodeCriteria;
use App\Helpers\CustomHelpers;
use App\Order;
use App\OrderDiscountCode;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;
use DateTime;
use Illuminate\Auth\Events\Validated;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;


class DiscountCodeController extends MarketPlace
{
    protected $statusMap;
    public function __construct()
    {
        $this->middleware('admin.user');
        $this->statusMap = [
            1  => 'รอยืนยันการชำระเงิน',
            2  => 'กำลังเตรียมสินค้า',
            3  => 'ได้รับสินค้าแล้ว',
            4  => 'ยกเลิก',
            5  => 'สินค้าอยู่ที่ศูนย์จัดส่งแล้ว',
            6  => 'กำลังจัดส่ง',
            7  => 'Item not match with ordered qty',
            8  => 'กำลังจัดส่ง',
            9  => 'ยกเลิก (Reject)',
            10 => 'ยกเลิก (Returned)',
            11 => 'Cancel Reason failed delivery',
            12 => 'Cancel Reason cancelled',
        ];
    }
    
    public function index(Request $request)
    {
        try {
            $permission = $this->checkUrlPermission('campaign');
            if(!$permission === true) {throw new Exception("Permission Denied", 1);}

            $permission_arr['add'] = $this->checkMenuPermission('add_discount_code');
            $permission_arr['edit'] = $this->checkMenuPermission('edit_discount_code');
            $permission_arr['delete'] = $this->checkMenuPermission('delete_discount_code');
            $langcount = \App\Language::where(['isDefault'=>'1','status'=>'1'])->count();
            $language = ($langcount > 0) ? true : false;

            $limit = $request->limit ?? 10;
            $allowedOrderFields = ['created_at', 'updated_at', 'code'];
            $orderBy = in_array($request->orderBy, $allowedOrderFields)  ? $request->orderBy  : 'created_at';
            $sortType = in_array(strtolower($request->sortType), ['asc', 'desc']) ? strtolower($request->sortType) : 'desc';

            $discountCodeCriteria = DiscountCodeCriteria::with([
                'campaign' => function($q) {
                    $q->select('id','parent_id','name');
                },
                'campaign.megacampaign' => function($q) {
                    $q->select('id', 'name');
                }
            ])
            ->when($request->mega_campaign_id,function($q) use ($request){
                $q->whereHas('campaign.megacampaign', function ($qq) use ($request) {
                    $qq->where('id', $request->mega_campaign_id);
                });
            })
            ->when($request->campaign_id,function($q) use ($request){
                $q->whereHas('campaign', function ($qq) use ($request) {
                    $qq->where('id', $request->campaign_id);
                });
            })
            ->when($request->status,function($q) use ($request){
                $q->where('status', $request->status);
            })
            ->when($request->search,function($q) use ($request){
                $q->where('name', 'like', "%".$request->search."%");
            })
            
            ->orderBy($orderBy, $sortType)
            ->paginate($limit)->appends($request->all());

            $data['campaigns'] = Campaign::whereNotNull('parent_id')->get();
            $data['megaCampaigns'] = Campaign::whereNull('parent_id')->get();

            return view('admin.discount-code.index',[
                'discountCodeCriteria'=>$discountCodeCriteria,
                'campaigns'=>$data['campaigns'],
                'megaCampaigns'=>$data['megaCampaigns'],
                'permission_arr'=>$permission_arr
                ]
            );
        } catch (Exception $e) {
            return redirect()->action('Admin\DiscountCode\DiscountCodeController@index')
                         ->with('errMsg', 'เกิดข้อผิดพลาด: '. json_encode($e->getMessage()));
        }

    }

    public function create(Request $request)
    {
        try {
             $permission = $this->checkUrlPermission('add_discount_code');
            if(!$permission === true) {throw new Exception("Permission Denied", 1);}

            $permission_arr['add'] = $this->checkMenuPermission('add_discount_code');

            $prefix = DB::getTablePrefix();
            $data['campaigns'] = Campaign::whereNotNull('parent_id')->get();
            $data['discount_code_type'] = CustomHelpers::getEnumValues($prefix.'discount_code_criteria', 'discount_code_type');
            // $data['source_type'] = CustomHelpers::getEnumValues($prefix.'discount_code_criteria', 'source_type');
            $data['discount_type'] = CustomHelpers::getEnumValues($prefix.'discount_code_criteria', 'discount_type');
            $data['discount_target'] = CustomHelpers::getEnumValues($prefix.'discount_code_criteria', 'discount_target');

        
            return view('admin.discount-code.create',[
                'data'=>$data
            ]);
        } catch (Exception $e) {
            return redirect()->action('Admin\DiscountCode\DiscountCodeController@index')
                        ->with('errorMsg', 'เกิดข้อผิดพลาด: ');
        }
        
    }

    public function store(Request $request)
    {
        $digit = [
            'random' => ["max" => 8,"minRandom" => 6, "maxRandom" => 8],
            'prefix' => ["max" => 8, "minPrefix" => 2, "maxPrefix" => 4, "minRandom" => 4, "maxRandom" => 6],
            'custom' => ["min" => 6, "max" => 12],
        ];

        $messages = [
            'campaign.required' => 'กรุณาเลือกแคมเปญ',
            'campaign.exists' => 'แคมเปญที่เลือกไม่ถูกต้อง',

            'discount_code_type.required' => 'กรุณาเลือกประเภทของโค้ดส่วนลด',
            'discount_code_type.string' => 'ประเภทของโค้ดต้องเป็นข้อความ',
            'discount_code_type.max' => 'ประเภทของโค้ดยาวเกิน :max ตัวอักษร',
            'discount_code_type.in' => 'ประเภทโค้ดไม่ถูกต้อง',

            'name.required' => 'กรุณากรอกชื่อโค้ด',
            'name.string' => 'ชื่อโค้ดต้องเป็นข้อความ',
            'name.min' => 'ชื่อโค้ดต้องมีอย่างน้อย :min ตัวอักษร',
            'name.max' => 'ชื่อโค้ดยาวเกิน :max ตัวอักษร',

            'start_date.required' => 'กรุณาระบุวันเริ่มต้น',
            'start_date.date_format' => 'รูปแบบวันเริ่มต้นไม่ถูกต้อง (ตัวอย่าง: 2025-06-20 14:00)',
            'start_date.before' => 'วันเริ่มต้นต้องมาก่อนวันสิ้นสุด',

            'end_date.required' => 'กรุณาระบุวันสิ้นสุด',
            'end_date.date_format' => 'รูปแบบวันสิ้นสุดไม่ถูกต้อง',
            'end_date.after' => 'วันสิ้นสุดต้องมากกว่าวันเริ่มต้น',

            'required_purchase_count.numeric' => 'จำนวนครั้งที่ต้องซื้อก่อนใช้โค้ดต้องเป็นตัวเลข',
            'required_purchase_count.max' => 'จำนวนต้องไม่เกิน :max',

            'purchase_amount_threshold.required' => 'กรุณากรอกยอดซื้อขั้นต่ำ',
            'purchase_amount_threshold.numeric' => 'ยอดซื้อขั้นต่ำต้องเป็นตัวเลข',
            'purchase_amount_threshold.min' => 'ยอดซื้อต้องไม่น้อยกว่า :min',
            'purchase_amount_threshold.max' => 'ยอดซื้อต้องไม่เกิน :max',

            'discount_value.required' => 'กรุณาระบุมูลค่าส่วนลด',
            'discount_value.numeric' => 'มูลค่าส่วนลดต้องเป็นตัวเลข',
            'discount_value.min' => 'มูลค่าส่วนลดต้องไม่น้อยกว่า :min',
            'discount_value.max' => 'มูลค่าส่วนลดต้องไม่เกิน :max',

            'discount_type.required' => 'กรุณาเลือกประเภทส่วนลด',
            'discount_type.string' => 'ประเภทส่วนลดต้องเป็นข้อความ',
            'discount_type.max' => 'ประเภทส่วนลดยาวเกิน :max ตัวอักษร',
            'discount_type.in' => 'ประเภทส่วนลดไม่ถูกต้อง',

            'discount_target.required' => 'กรุณาเลือกเป้าหมายส่วนลด',
            'discount_target.string' => 'เป้าหมายส่วนลดต้องเป็นข้อความ',
            'discount_target.max' => 'เป้าหมายส่วนลดยาวเกิน :max ตัวอักษร',
            'discount_target.in' => 'เป้าหมายส่วนลดไม่ถูกต้อง',

            'desc.string' => 'คำอธิบายต้องเป็นข้อความ',

            'file_image.image' => 'ไฟล์ต้องเป็นรูปภาพ',
            'file_image.mimes' => 'รูปภาพต้องเป็นชนิด: jpeg, png, jpg, gif, svg หรือ webp',
            'file_image.max' => 'ขนาดรูปภาพต้องไม่เกิน 10MB',

            // สำหรับฟิลด์ dynamic
            'code.required' => 'กรุณากรอกโค้ดส่วนลด',
            'code.string' => 'โค้ดต้องเป็นข้อความ',
            'code.regex' => 'โค้ดต้องประกอบด้วยตัวอักษร A-Z และตัวเลข 0-9 เท่านั้น',
            'code.min' => 'โค้ดต้องมีความยาวอย่างน้อย :min ตัวอักษร',
            'code.max' => 'โค้ดต้องไม่เกิน :max ตัวอักษร',

            'random_length.required' => 'กรุณาระบุจำนวนตัวอักษรสุ่ม',
            'random_length.integer' => 'จำนวนตัวอักษรสุ่มต้องเป็นตัวเลข',
            'random_length.min' => 'จำนวนสุ่มต้องไม่น้อยกว่า :min ตัวอักษร',
            'random_length.max' => 'จำนวนสุ่มต้องไม่เกิน :max ตัวอักษร',

            'create_amount.required' => 'กรุณาระบุจำนวนโค้ดที่จะสร้าง',
            'create_amount.integer' => 'จำนวนต้องเป็นตัวเลข',
            'create_amount.min' => 'จำนวนต้องไม่น้อยกว่า :min',
            'create_amount.max' => 'จำนวนต้องไม่เกิน :max',

            'quantity.required' => 'กรุณาระบุจำนวนโค้ดทั้งหมด',
            'quantity.integer' => 'จำนวนต้องเป็นตัวเลข',
            'quantity.min' => 'จำนวนต้องไม่น้อยกว่า :min',
            'quantity.max' => 'จำนวนต้องไม่เกิน :max',

            'limit_per_account.required' => 'กรุณาระบุจำนวนครั้งต่อบัญชี',
            'limit_per_account.integer' => 'จำนวนครั้งต่อบัญชีต้องเป็นตัวเลข',
            'limit_per_account.min' => 'จำนวนต้องไม่น้อยกว่า :min',
            'limit_per_account.max' => 'จำนวนต้องไม่เกิน :max',

            'max_discount.required' => 'กรุณาระบุส่วนลดสูงสุด',
            'max_discount.integer' => 'ส่วนลดสูงสุดต้องเป็นตัวเลข',
            'max_discount.min' => 'ส่วนลดต้องไม่น้อยกว่า :min',
            'max_discount.max' => 'ส่วนลดต้องไม่เกิน :max',
        ];


        $rules = [
            'campaign' => 'required|exists:campaign,id',
            'discount_code_type' => [ 'required', 'string', 'max:50', Rule::in(['custom','random', 'prefix']) ],
            'name' => [ 'required', 'string','min:2','max:255', ],
            'start_date' => [ 'required', 'date_format:Y-m-d H:i', 'before:end_date', 
                function ($attribute, $value, $fail) {
                    $startTime = \Carbon\Carbon::createFromFormat('Y-m-d H:i', $value);
                    if ($startTime->lt(now()->addHours(2))) {
                        $fail('เวลาเริ่มต้นต้องห่างจากเวลาปัจจุบันอย่างน้อย 2 ชั่วโมง');
                    }
                }
            ],
            'end_date' => [ 'required', 'date_format:Y-m-d H:i', 'after:start_date', ],
            // 'source_type' => [ 'required', 'string', 'max:50', Rule::in(['system','shop']) ],
            'required_purchase_count' => [ 'nullable', 'integer','max:100000' ],
            'purchase_amount_threshold' => [ 'required', 'integer','min:1','max:100000' ],
            'discount_value' => [ 'required', 'integer','min:1','max:100000' ],
            'discount_type' => [ 'required', 'string', 'max:50', Rule::in(['fixed','percentage']) ],
            'discount_target' => [ 'required', 'string', 'max:50', Rule::in(['purchase','shipping']) ],
            'desc' => 'nullable|string',
            'file_image' => 'sometimes|nullable|image|mimes:jpeg,png,jpg,gif,svg,webp|max:10240'
        ];
        
        $rules['code'] = [];
        $rules['random_length'] = [];
        $discount_code_type = $request->discount_code_type;

        if ($discount_code_type === 'custom') {
            $rules['code'] = [
                'required', 'string', 'regex:/^[A-Z0-9]+$/',
                'min:' . ($digit['custom']['min'] ?? 4),
                'max:' . ($digit['custom']['max'] ?? 12),
            ];
        } elseif ($discount_code_type === 'prefix') {
            $rules['code'] = [
                'required', 'string', 'regex:/^[A-Z0-9]+$/',
                'min:' . ($digit['prefix']['minPrefix'] ?? 2),
                'max:' . ($digit['prefix']['maxPrefix'] ?? 4),
            ];
            $rules['random_length'] = [
                'required', 'integer',
                'min:' . ($digit['prefix']['minRandom'] ?? 4),
                'max:' . ($digit['prefix']['maxRandom'] ?? 6),
            ];
        } elseif ($discount_code_type === 'random') {
            $rules['random_length'] = [
                'required', 'integer',
                'min:' . ($digit['random']['minRandom'] ?? 6),
                'max:' . ($digit['random']['maxRandom'] ?? 8),
            ];
        }
        
        $validator = Validator::make($request->all(), $rules, $messages)
        ->sometimes('create_amount', ['required','integer','min:1','max:1000'], function ($input) {
            return in_array($input->discount_code_type, ['random', 'prefix']);
        })
        ->sometimes('quantity', ['required','integer','min:1','max:100000'], function ($input) {
            return $input->is_limit === '1';
        })
        ->sometimes('limit_per_account', ['required','integer','min:1','max:100'], function ($input) {
            return $input->is_limit_per_account === '1';
        })
        ->sometimes('max_discount', ['required','integer','min:1','max:100000'], function ($input) {
            return $input->is_max_discount === '1';
        })
        ->sometimes('discount_value', ['required','integer','min:1','max:100'], function ($input) {
            return $input->discount_type === 'percentage';
        });

        if ($validator->fails()) {
            $errors =  $validator->errors(); 
            return array('status'=>'fail','message'=>$errors,'validation'=>true);
        }

        $imagePath = null;
        try {
            DB::beginTransaction();
            if($discount_code_type === 'custom'){
                if(!$request->code){
                    return  response()->json(['status'=>'error','message'=>'กรุณากรอก Code']);
                }
                $existCode = DiscountCode::where('code',$request->code)->first();
                if($existCode){
                    return  response()->json(['status'=>'error','message'=>'Code ซ้ำในระบบ']);
                }
            }

            $random_length = $request->random_length??8;
            $status = $request->status??false;
            $code = $request->code;
            $create_amount = $request->create_amount;

            $newDCC = new DiscountCodeCriteria();
            $newDCC->campaign_id        = $request->campaign;
            $newDCC->discount_code_type = $request->discount_code_type;
            $newDCC->name               = $request->name;
            $newDCC->start_date         = $request->start_date;
            $newDCC->end_date           = $request->end_date;
            $newDCC->desc               = $request->desc;
            $newDCC->required_purchase_count = $request->required_purchase_count;
            $newDCC->is_limit           = $request->is_limit? 1 : 0;
            $newDCC->quantity           = $request->is_limit?$request->quantity:null;
            $newDCC->limit_per_account  = $request->is_limit_per_account?$request->limit_per_account:null;
            $newDCC->source_type        = 'system';
            $newDCC->purchase_amount_threshold = $request->purchase_amount_threshold;
            $newDCC->discount_target    = $request->discount_target;
            $newDCC->discount_value     = $request->discount_value;
            $newDCC->discount_type      = $request->discount_type;
            if($request->discount_type === 'percentage' && $request->is_max_discount){
                $newDCC->max_discount = $request->max_discount;
            }
            $newDCC->max_discount       = $request->is_max_discount ? $request->max_discount:null;
            $newDCC->is_free_shipping   = $request->is_free_shipping? 1 : 0;
            $newDCC->status             = $status;

            if($request->hasFile('file_image')){
                $fileImage = $request->file_image;
                $path = Config::get('constants.discount_code_path').'/';
                $imageName = 'discount_code_'.md5(microtime()).'.png';
                if(!is_dir($path)) {
                    mkdir($path, 0777, true);
                }
                $imagePath = $this->uploadImage($imageName,$fileImage,$path);
                if($imagePath){
                    $newDCC->image = $imageName;
                }
            }

            $newDCC->save();

            $newDiscountCode = new DiscountCode();
            if(in_array($discount_code_type,['random','prefix'])){

                $allCode = DiscountCode::all()->pluck('code');
                $arrCode = [];
                
                if($discount_code_type === 'random'){
                    for ($i=0; $i < $create_amount??0; $i++) { 
                        
                        $isRandomLimitReached = false;
                        $maxAttempts = 500;
                        $attempt  = 0;
                        do {
                            $randomCode = CustomHelpers::generateRandomString($random_length);
                            $attempt ++;
                            if ($attempt >= $maxAttempts) {
                                $isRandomLimitReached = true;
                                break;
                            }
                        } while (
                            $allCode->contains($randomCode) ||  collect($arrCode)->contains($randomCode)
                        );
                        if ($isRandomLimitReached) {
                            throw new Exception('ไม่สามารถสร้างโค้ดส่วนลดได้ เนื่องจากเกินจำนวนครั้งที่กำหนด', 500);
                        }
                        $arrCode[] = [
                            'discount_code_criteria_id' => $newDCC->id,
                            'code' => $randomCode,
                            'remaining_quantity' => $newDCC->is_limit == true ?$newDCC->quantity:0,
                            'status' => $status,
                            'created_at' => now(),
                            'updated_at' => now(),
                            'created_by' => auth('admin_user')->id()??request()->ip(),
                        ];
                    }

                }elseif($discount_code_type === 'prefix'){
                    $prefix = $code;
                    for ($i=0; $i < $create_amount??0; $i++) {
                        do {
                            $randomCode = CustomHelpers::generateRandomString($random_length);
                        } while (
                            $allCode->contains($randomCode) || collect($arrCode)->contains($randomCode)
                        );
                        $arrCode[] = [
                            'discount_code_criteria_id' => $newDCC->id,
                            'code' => $prefix.$randomCode,
                            'remaining_quantity' => $newDCC->is_limit == true ?$newDCC->quantity:0,
                            'status' => $status,
                            'created_at' => now(),
                            'updated_at' => now(),
                            'created_by' => auth('admin_user')->id()??request()->ip(),
                        ];
                    }
                }

                $chunkLength = 10;
                $chunks = array_chunk($arrCode, $chunkLength);
                foreach ($chunks  as $chunk) {
                    $newDiscountCode->insert($chunk);
                }
                $newDiscountCode=$arrCode;
                

            }elseif($discount_code_type === 'custom'){
                $newDiscountCode->code = $code;
                $newDiscountCode->discount_code_criteria_id = $newDCC->id;
                $newDiscountCode->remaining_quantity = $newDCC->is_limit == true ?$newDCC->quantity:0;
                $newDiscountCode->status = $status;
                $newDiscountCode->save();
            }
    
            DB::commit();
            return response()->json([
                'status' => 'success',
                'message' => 'Created successfully',
                'url' =>action('Admin\DiscountCode\DiscountCodeController@edit',['discount_code_criteria'=> $newDCC->id]),
                'data' => $newDCC
            ], 201);
            
        } catch (Exception $e) {
            if (DB::transactionLevel() > 0) {
                DB::rollBack();
            }
            Log::warning('Create discount code generation failed  ' . $e->getMessage());
            if (!empty($imagePath) && file_exists($imagePath)) {
                unlink($imagePath);
            }
            return array('status'=>'error','message'=>'Server error');
        }
    }

    public function report(Request $request){
        $request->validate([
            'start_date' => 'nullable|date|date_format:Y-m-d',
            'end_date'   => 'nullable|date|date_format:Y-m-d',
        ]);
        if ($request->input('action_type') === 'export') {
            return $this->exportReport($request);
        }
        try {
            $limit = $request->limit ?? 10;
            $data['orderDcc'] = OrderDiscountCode::query()
            ->with(['order','criteria','discountCode'])
            ->when($request->campaign_id, function ($qry) use ($request) {
                $qry->whereHas('criteria', function ($q) use ($request) {
                    $q->whereIn('campaign_id', (array)$request->campaign_id);
                });
            })
            ->when($request->start_date, function ($qry) use ($request) {
                $qry->whereHas('order', function ($q) use ($request) {
                    $q->whereDate('created_at', '>=', $request->start_date);
                });
            })
            ->when($request->end_date, function ($qry) use ($request) {
                $qry->whereHas('order', function ($q) use ($request) {
                    $q->whereDate('created_at', '<=', $request->end_date);
                });
            })
            ->when($request->status_id,function($qry)use($request){
                $qry->whereHas('order', function ($q) use ($request) {
                    $q->whereIn('order_status',(array)$request->status_id);
                });
            })
            ->whereHas('order')
            ->orderBy('created_at', 'desc')
            ->paginate($limit)->appends($request->all());

            foreach ($data['orderDcc'] as $odc) {
                $odc->status_text = $this->statusMap[$odc->order->order_status] ?? '';
            }


            $data['campaigns'] = Campaign::whereNotNull('parent_id')->where('status',1)->get();
            $data['megaCampaigns'] = Campaign::whereNull('parent_id')->where('status',1)->get();
            $data['status'] = $this->statusMap??[];
            return view('admin.discount-code.report')->with('data',$data);
        }catch (Exception $e) {
            Log::error('Update failed: ' . $e->getMessage());
            return redirect()->back()
            // ->action('Admin\AdminHomeController@index')
            ->with('errorMsg', 'เกิดข้อผิดพลาด: ');
        }
    }

    public function exportReport($request)
    {
        $request->validate([
            'start_date' => 'nullable|date|date_format:Y-m-d',
            'end_date'   => 'nullable|date|date_format:Y-m-d',
        ]);

        try {
            $statusMap = $this->statusMap;

            $orderDcc = OrderDiscountCode::query()
                ->when($request->campaign_id, function ($qry) use ($request) {
                    $qry->whereHas('criteria', function ($q) use ($request) {
                        $q->whereIn('campaign_id', (array)$request->campaign_id);
                    });
                })
                ->when($request->start_date, function ($qry) use ($request) {
                    $qry->whereHas('order', function ($q) use ($request) {
                        $q->whereDate('created_at', '>=', $request->start_date);
                    });
                })
                ->when($request->end_date, function ($qry) use ($request) {
                    $qry->whereHas('order', function ($q) use ($request) {
                        $q->whereDate('created_at', '<=', $request->end_date);
                    });
                })
                ->when($request->status_id, function ($qry) use ($request) {
                    $qry->whereHas('order', function ($q) use ($request) {
                        $q->whereIn('order_status', (array)$request->status_id);
                    });
                })
                ->whereHas('order')
                ->orderBy('created_at', 'desc')
                ->get();

            $timestamp = now()->format('YmdHis');
            $headers = [
                'Content-Type' => 'text/csv',
                'Content-Disposition' => 'attachment; filename="discount_code_report_'.$timestamp.'.csv"',
            ];

            $callback = function () use ($orderDcc, $statusMap) {
                $handle = fopen('php://output', 'w');

                try {
                    fwrite($handle, "\xEF\xBB\xBF");
                    fputcsv($handle, [
                        '#', 'วันที่สร้างโค้ดส่วนลด', 'ชื่อโค้ดส่วนลด', 'ชื่อแคมเปญ', 'ประเภทโค้ด',
                        'เงื่อนไขโค้ด', 'จำนวนโค้ดทั้งหมด', 'จำนวนโค้ดที่ถูกใช้', 'ระยะเวลาโค้ด',
                        'วันเริ่มต้น - วันหมดอายุ', 'ยอดขั้นต่ำที่กำหนด',
                        'Order ID', 'ยอดสั่งซื้อรวม', 'ค่าขนส่ง', 'ส่วนลดค่าสินค้า',
                        'ส่วนลดค่าขนส่ง', 'ยอดหลังหักส่วนลด', 'ชื่อลูกค้า', 'วันที่ใช้โค้ด', 'สถานะ'
                    ]);

                    foreach ($orderDcc as $index => $orDcc) {
                        $criteria = $orDcc->criteria ?? null;
                        $discountCode = $orDcc->discountCode ?? null;
                        $order = $orDcc->order ?? null;

                        if (!$criteria || !$order) {
                            continue; 
                        }

                        $orderCount = $discountCode && method_exists($discountCode, 'order')
                            ? $discountCode->order()->count()
                            : 0;

                        $created = optional($criteria->created_at)->format('d/m/Y H:i');
                        $start = $criteria->start_date ? \Carbon\Carbon::parse($criteria->start_date) : null;
                        $end = $criteria->end_date ? \Carbon\Carbon::parse($criteria->end_date) : null;
                        $diffText = $start && $end
                            ? $start->diff($end)->days . ' วัน ' . $start->diff($end)->h . ' ชั่วโมง ' . $start->diff($end)->i . ' นาที'
                            : '';
                        $startEnd = $start && $end
                            ? $start->format('d/m/Y H:i') . ' - ' . $end->format('d/m/Y H:i')
                            : '';
                        $threshold = number_format($criteria->purchase_amount_threshold ?? 0, 2);
                        $quantity = $criteria->is_limit ? ($criteria->quantity ?? '') : 'ไม่จำกัด';

                        fputcsv($handle, [
                            $index + 1,
                            $created,
                            $orDcc->discount_code,
                            ($criteria->campaign->name ?? ''),
                            $criteria->discount_code_type,
                            $criteria->desc,
                            $quantity,
                            $orderCount,
                            $diffText,
                            $startEnd,
                            $threshold,

                            $order->formatted_id ?? '',
                            number_format($order->total_core_cost ?? 0, 2),
                            number_format($order->total_shipping_cost ?? 0, 2),
                            number_format($order->dcc_purchase_discount ?? 0, 2),
                            number_format($order->dcc_shipping_discount ?? 0, 2),
                            number_format($order->total_final_price ?? 0, 2),
                            optional($order->getUser)->display_name ?? '',
                            optional($order->created_at)->format('d/m/Y H:i'),
                            $statusMap[$order->order_status] ?? '',
                        ]);
                    }
                } catch (\Throwable $e) {
                    Log::error('Export CSV Stream Error: ' . $e->getMessage());
                    fputcsv($handle, ['เกิดข้อผิดพลาด: ' . $e->getMessage()]);
                } finally {
                    fclose($handle);
                }
            };

            return new StreamedResponse($callback, 200, $headers);
        } catch (\Exception $e) {
            Log::error('Export CSV Error: ' . $e->getMessage());
            return redirect()->back()->with('errorMsg', 'ไม่สามารถส่งออกข้อมูลได้');
        }
    }

    public function edit(DiscountCodeCriteria $discount_code_criteria){
        try {
            $prefix = DB::getTablePrefix();
            $data['campaigns'] = Campaign::whereNotNull('parent_id')->get();
            $data['discount_code_type'] = CustomHelpers::getEnumValues($prefix.'discount_code_criteria', 'discount_code_type');
            // $data['source_type'] = CustomHelpers::getEnumValues($prefix.'discount_code_criteria', 'source_type');
            $data['discount_type'] = CustomHelpers::getEnumValues($prefix.'discount_code_criteria', 'discount_type');
            $data['discount_target'] = CustomHelpers::getEnumValues($prefix.'discount_code_criteria', 'discount_target');

            $discount_code_criteria->image = $discount_code_criteria->image?asset('files/discount_code/'.$discount_code_criteria->image):null;

            $can_edit = now()->lessThan(Carbon::parse($discount_code_criteria->start_date)->copy()->subHours(2));
 
            return view('admin.discount-code.edit',[
                'discountCodeCriteria'=>$discount_code_criteria,
                'campaigns'=>Campaign::whereNotNull('parent_id')->get(),
                'megaCampaigns'=>Campaign::whereNull('parent_id')->get(),
                'data'=>$data,
                'can_edit'=>$can_edit
            ]);
        }catch (Exception $e) {
            Log::error('Update failed: ' . $e->getMessage());
            return redirect()->action('Admin\AdminHomeController@index')
                        ->with('errMsg', 'เกิดข้อผิดพลาด: ');
        }

    }

    public function update(DiscountCodeCriteria $discount_code_criteria,Request $request){
        $oldImagePath = null;
        $imagePath = null;
        try {
            DB::beginTransaction();
            if(!$discount_code_criteria){
                throw new Exception('ไม่พบข้อมูล');
            }
            $can_edit = now()->lessThan(Carbon::parse($discount_code_criteria->start_date)->copy()->subHours(2));
            if(!$can_edit){

                $discount_code_criteria->status = $request->status;
                $discount_code_criteria->desc = $request->desc;
                $discount_code_criteria->updated_at = now();
                $discount_code_criteria->save();
            }else{
                $messages = [
                'name.required' => 'กรุณากรอกชื่อโปรโมชั่น',
                'name.string' => 'ชื่อโปรโมชั่นต้องเป็นข้อความ',
                'name.min' => 'ชื่อโปรโมชั่นต้องมีอย่างน้อย :min ตัวอักษร',
                'name.max' => 'ชื่อโปรโมชั่นต้องไม่เกิน :max ตัวอักษร',

                'start_date.required' => 'กรุณากรอกวันที่เริ่มต้น',
                'start_date.date_format' => 'รูปแบบวันเริ่มต้นไม่ถูกต้อง (ต้องเป็น Y-m-d H:i)',
                'start_date.before' => 'วันที่เริ่มต้นต้องก่อนวันที่สิ้นสุด',

                'end_date.required' => 'กรุณากรอกวันที่สิ้นสุด',
                'end_date.date_format' => 'รูปแบบวันสิ้นสุดไม่ถูกต้อง (ต้องเป็น Y-m-d H:i)',
                'end_date.after' => 'วันที่สิ้นสุดต้องหลังจากวันที่เริ่มต้น',

                'required_purchase_count.integer' => 'จำนวนการซื้อที่ต้องการต้องเป็นตัวเลข',
                'required_purchase_count.max' => 'จำนวนการซื้อที่ต้องการต้องไม่เกิน :max',

                'purchase_amount_threshold.required' => 'กรุณากรอกยอดซื้อขั้นต่ำ',
                'purchase_amount_threshold.integer' => 'ยอดซื้อขั้นต่ำต้องเป็นตัวเลข',
                'purchase_amount_threshold.min' => 'ยอดซื้อขั้นต่ำต้องไม่น้อยกว่า :min',
                'purchase_amount_threshold.max' => 'ยอดซื้อขั้นต่ำต้องไม่เกิน :max',

                'discount_value.required' => 'กรุณากรอกมูลค่าส่วนลด',
                'discount_value.integer' => 'มูลค่าส่วนลดต้องเป็นตัวเลข',
                'discount_value.min' => 'มูลค่าส่วนลดต้องไม่น้อยกว่า :min',
                'discount_value.max' => 'มูลค่าส่วนลดต้องไม่เกิน :max',

                'discount_type.required' => 'กรุณาเลือกประเภทส่วนลด',
                'discount_type.string' => 'ประเภทส่วนลดต้องเป็นข้อความ',
                'discount_type.max' => 'ประเภทส่วนลดต้องไม่เกิน :max ตัวอักษร',
                'discount_type.in' => 'ประเภทส่วนลดที่เลือกไม่ถูกต้อง',

                'discount_target.required' => 'กรุณาเลือกเป้าหมายส่วนลด',
                'discount_target.string' => 'เป้าหมายส่วนลดต้องเป็นข้อความ',
                'discount_target.max' => 'เป้าหมายส่วนลดต้องไม่เกิน :max ตัวอักษร',
                'discount_target.in' => 'เป้าหมายส่วนลดที่เลือกไม่ถูกต้อง',

                'desc.string' => 'คำอธิบายต้องเป็นข้อความ',

                'file_image.image' => 'ไฟล์ที่อัปโหลดต้องเป็นรูปภาพ',
                'file_image.mimes' => 'ไฟล์รูปภาพต้องเป็นประเภท jpeg, png, jpg, gif, svg, หรือ webp',
                'file_image.max' => 'ขนาดรูปภาพต้องไม่เกิน 10MB',

                'quantity.required' => 'กรุณากรอกจำนวนโค้ด',
                'quantity.integer' => 'จำนวนโค้ดต้องเป็นตัวเลข',
                'quantity.min' => 'จำนวนโค้ดต้องไม่น้อยกว่า :min',
                'quantity.max' => 'จำนวนโค้ดต้องไม่เกิน :max',

                'limit_per_account.required' => 'กรุณากรอกจำนวนโค้ดที่ใช้ได้ต่อบัญชี',
                'limit_per_account.integer' => 'โค้ดต่อบัญชีต้องเป็นตัวเลข',
                'limit_per_account.min' => 'โค้ดต่อบัญชีต้องไม่น้อยกว่า :min',
                'limit_per_account.max' => 'โค้ดต่อบัญชีต้องไม่เกิน :max',

                'max_discount.required' => 'กรุณากรอกส่วนลดสูงสุด',
                'max_discount.integer' => 'ส่วนลดสูงสุดต้องเป็นตัวเลข',
                'max_discount.min' => 'ส่วนลดสูงสุดต้องไม่น้อยกว่า :min',
                'max_discount.max' => 'ส่วนลดสูงสุดต้องไม่เกิน :max',
            ];
        
                $rules = [
                    'name' => [ 'required', 'string','min:2','max:255', ],
                    'start_date' => [ 'required', 'date_format:Y-m-d H:i', 'before:end_date', 
                        function ($attribute, $value, $fail) {
                            $startTime = \Carbon\Carbon::createFromFormat('Y-m-d H:i', $value);
                            if ($startTime->lt(now()->addHours(2))) {
                                $fail('เวลาเริ่มต้นต้องห่างจากเวลาปัจจุบันอย่างน้อย 2 ชั่วโมง');
                            }
                        }
                    ],
                    'end_date' => [ 'required', 'date_format:Y-m-d H:i', 'after:start_date', ],
                    // 'source_type' => [ 'required', 'string', 'max:50', Rule::in(['system','shop']) ],
                    'required_purchase_count' => [ 'nullable', 'integer','max:100000' ],
                    'purchase_amount_threshold' => [ 'required', 'integer','min:1','max:100000' ],
                    'discount_value' => [ 'required', 'integer','min:1','max:100000' ],
                    'discount_type' => [ 'required', 'string', 'max:50', Rule::in(['fixed','percentage']) ],
                    'discount_target' => [ 'required', 'string', 'max:50', Rule::in(['purchase','shipping']) ],
                    'desc' => 'nullable|string',
                    'file_image' => 'sometimes|nullable|image|mimes:jpeg,png,jpg,gif,svg,webp|max:10240'

                ];

                $validator = Validator::make($request->all(), $rules, $messages)
                ->sometimes('quantity', ['required','integer','min:1','max:100'], function ($input) {
                    return $input->is_limit === '1';
                })
                ->sometimes('limit_per_account', ['required','integer','min:1','max:100'], function ($input) {
                    return $input->is_limit_per_account === '1';
                })
                ->sometimes('max_discount', ['required','integer','min:1','max:100000'], function ($input) {
                    return $input->is_max_discount === '1';
                });

                $startDate = optional($discount_code_criteria)->start_date;
                if (!is_null($startDate) && !Carbon::parse($startDate)->lessThan(Carbon::now())) {
                    
                    $discount_code_criteria->name               = $request->name;
                    $discount_code_criteria->start_date         = $request->start_date;
                    $discount_code_criteria->end_date           = $request->end_date;
                    $discount_code_criteria->required_purchase_count = $request->required_purchase_count;
                    $discount_code_criteria->is_limit           = $request->is_limit? 1 : 0;
                    $discount_code_criteria->quantity           = $request->is_limit?$request->quantity:null;
                    $discount_code_criteria->limit_per_account  = $request->is_limit_per_account?$request->limit_per_account:null;
                    $discount_code_criteria->source_type        = 'system';
                    $discount_code_criteria->purchase_amount_threshold = $request->purchase_amount_threshold;
                    $discount_code_criteria->discount_target    = $request->discount_target;
                    $discount_code_criteria->discount_value     = $request->discount_value;
                    $discount_code_criteria->discount_type      = $request->discount_type;
                    if($request->discount_type === 'percentage' && $request->is_max_discount){
                        $discount_code_criteria->max_discount = $request->max_discount;
                    }
                    $discount_code_criteria->max_discount       = $request->is_max_discount ? $request->max_discount:null;
                    $discount_code_criteria->is_free_shipping   = $request->is_free_shipping? 1 : 0;
                    $discount_code_criteria->desc               = $request->desc;

                    $oldImagePath = $discount_code_criteria->image;
                    if($request->hasFile('file_image')){
                        $fileImage = $request->file_image;
                        $path = Config::get('constants.discount_code_path').'/';
                        $imageName = 'discount_code_'.md5(microtime()).'.png';
                        if(!is_dir($path)) {
                            mkdir($path, 0777, true);
                        }
                        $imagePath = $this->uploadImage($imageName,$fileImage,$path);
                        if($imagePath){
                            $discount_code_criteria->image = $imageName;
                        }
                    }
                }

                $discount_code_criteria->status = $request->status;
                $discount_code_criteria->updated_at = now();
                $discount_code_criteria->save();

                $discount_code_criteria->discountCode()
                ->update([
                    'remaining_quantity' => $request->is_limit ? $request->quantity : null,
                    'updated_at' => now(),
                    'updated_by' => auth('admin_user')->id()??request()->ip(),
                ]);
                
            }
        
            DB::commit();
            if (!empty($oldImagePath) && file_exists($oldImagePath)) {
                unlink($oldImagePath);
            }
            return response()->json([
                'status' => 'success',
                'message' => 'Updated successfully',
                'url' =>action('Admin\DiscountCode\DiscountCodeController@edit',['discount_code_criteria'=> $discount_code_criteria->id]),
                'data' => $discount_code_criteria
            ], 201);
            
        } catch (Exception $e) {
            if (DB::transactionLevel() > 0) {
                DB::rollBack();
            }
            if (!empty($imagePath) && file_exists($imagePath)) {
                unlink($imagePath);
            }
            Log::error('Update failed: ' . $e->getMessage());
            return array('status'=>'fail','message'=>'Server error','validation'=>true);
        }

    }

    public function destroy(DiscountCodeCriteria $discount_code_criteria){
        try {
            $startDate = optional($discount_code_criteria)->start_date;
            if (!is_null($startDate)) {
                $startDate = Carbon::parse($startDate);
                $plusTwoHours = $startDate->copy()->addHours(2);
                $now = Carbon::now();
                if ($now->greaterThan($plusTwoHours)) {
                    throw new Exception('ไม่สามารถลบข้อมูลได้ เนื่องจากมีการใช้งานในช่วงเวลาที่กำหนด หรือ ใกล้ก่อนเริ่มใช้งาน 2 ชั่วโมง', 400);
                }
            }else{
                throw new Exception('ไม่สามารถลบข้อมูลได้ เนื่องจากไม่พบวันเริ่มต้นใช้งาน', 400);
            }

            $discount_code_criteria->forceDelete();
            return redirect()->action('Admin\DiscountCode\DiscountCodeController@index')
                ->with('succMsg', 'ลบข้อมูลสำเร็จ');

        } catch (Exception $e) {
            Log::error('Delete failed: ' . $e->getMessage());
            return redirect()->back()
                ->with('errMsg', 'เกิดข้อผิดพลาด: ' )
                ->withInput();
        }
    }

    public function updateStatus(Request $request){
        try {
            $id = (int)$request->id;
            $validated = $request->validate([
                'id' => 'required|exists:discount_code_criteria,id',
                'status' => ['required', 'in:0,1'],
            ]);
            $dcc = DiscountCodeCriteria::find($id);
            if(!$dcc) throw new Exception("ไม่พบข้อมูล", 404);
            $dcc->status =  (int)$request->status;
            if (!$dcc->save()) {
                throw new Exception("บันทึกไม่สำเร็จ", 500);
            }

            return response()->json([
                'status' => 'success',
                'message' => 'Change Status successfully',
                'result' => $dcc
            ], 201);
            
        }catch (ValidationException $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'ข้อมูลไม่ถูกต้อง',
                'errors' => $e->errors(),
            ], 422);

        } catch (Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Server error',
            ], $e->getCode() ?: 500);
        }
    }
    
    public function validateDiscountCode( $code, $purchase){
        try {
            $userId = auth()->id();
            if(!$code) {
                return response()->json([ 'status' => 'fail', 'message' => 'กรุณากรอกโค้ดส่วนลด', ],200);
            }
            if(!$purchase) {
                return response()->json([ 'status' => 'fail', 'message' => 'ไม่พบยอดการสั่งซื้อ', ],200);
            }
            $discountCode = DiscountCode::where('code', $code)->first();

            if (!$discountCode) {
                return response()->json([ 'status' => 'fail', 'message' => 'ขออภัย! ไม่พบโค้ดส่วนลดในระบบ กรุณากรอกใหม่', ],200);
            }
            $campaign = optional($discountCode->criteria)->campaign;
            if ($campaign && $campaign->status == 0) {
                return response()->json([ 'status' => 'fail', 'message' => 'ขออภัย! แคมเปญหมดอายุ', ],200);
            }
            $megacampaign = optional($campaign->megacampaign);
            if ($megacampaign && $megacampaign->status == 0) {
                return response()->json([ 'status' => 'fail', 'message' => 'ขออภัย! เมกาแคมเปญหมดอายุ', ],200);
            }
            $errorLogs = [];
            if ($discountCode->status == 0 || $discountCode->criteria->status == 0) {
                $errorLogs[] = 'ขออภัย! โค้ดส่วนลดหมดอายุ';
            }
            if ($discountCode->criteria->start_date && Carbon::parse($discountCode->criteria->start_date)->isFuture()) {
                $errorLogs[] = 'ขออภัย! โค้ดส่วนลดนี้ยังไม่เปิดให้ใช้งานในขณะนี้';
            }
            if ($discountCode->criteria->end_date && Carbon::parse($discountCode->criteria->end_date)->isPast()) {
                $errorLogs[] = 'โค้ดส่วนลดนี้ไม่สามารถใช้งานได้ เนื่องจากโปรโมชั่นได้สิ้นสุดลงแล้ว';
            }
            if ($purchase < $discountCode->criteria->purchase_amount_threshold) {
                $errorLogs[] = 'ขออภัย! ยอดสั่งซื้อไม่เข้าเงื่อนไขในการใช้โค้ดส่วนลด';
            }
            if ($discountCode->criteria->is_limit == true && $discountCode->remaining_quantity <= 0) {
                $errorLogs[] = 'ขออภัย! โค้ดนี้ถูกใช้ครบตามจำนวนที่กำหนดแล้ว';
            }
            if ($discountCode->criteria->limit_per_account>0) {
                $odc = OrderDiscountCode::query()
                ->where('discount_code',$code)
                ->whereHas('order',function($qry)use($userId){
                    $qry->where('user_id',$userId);
                })->count();
                if($odc >= $discountCode->criteria->limit_per_account){
                    $errorLogs[] = 'ขออภัย! คุณได้ใช้โค้ดส่วนลดครบตามจำนวนที่กำหนดแล้ว';
                }
            }
            
            // if ($discountCode->criteria->required_purchase_count > 0) {
            //     $order_count = Order::query()->where('user_id',$userId)->count();
            //     if($order_count != $discountCode->criteria->required_purchase_count+1){
            //         $errorLogs[] = "โค้ดส่วนลดสามารถใช้ได้เมื่อคุณมีการสั่งซื้อครบ ".$discountCode->criteria->required_purchase_count." ครั้ง";
            //     }
            // }

            if ($errorLogs) {
                // throw new Exception(implode(', ', $errorLogs), 400);
                return response()->json([
                    'status' => 'fail',
                    'message' => implode(', ', $errorLogs),
                ],200);
            }else{
             
                return response()->json([
                    'status' => 'success',
                    'message' => 'สามารถใช้งานได้',
                    'code' => $discountCode->code
                ], 200);
            }
            
        }catch (Exception $e) {
            Log::error('Failed: ' . $e->getMessage());
            return response()->json([
                'status' => 'fail',
                'message' => 'Server error',
            ], $e->getCode() > 0 ? $e->getCode() : 500);
        }
    }

    public function calulateDiscount( Request $request){
        try {
            $validator = Validator::make($request->all(), [
                'code' => 'required|string',
                'purchase' => 'required|numeric|min:1',
                'shippingCost' => 'nullable|numeric',
            ]);
            if ($validator->fails()) {
                $errors =  $validator->errors(); 
                return array('status'=>'fail','message'=>$errors,'validation'=>true);
            }
            
            $discountPurchase = 0;
            $discountShipping = 0;

            $code = $request->code;
            $purchase = $request->purchase;
            $shippingCost = $request->shippingCost;
            $checkResult = self::validateDiscountCode($code, $purchase);
            $checkResult = $checkResult->getData();
            if($checkResult->status == 'fail'){
                return response()->json($checkResult, 200);
            }
            $discountCode = DiscountCode::where('code', $code)->first();
            if(!$discountCode) {throw new Exception("ไม่พบโค้ดส่วนลด");}
            $criteria = $discountCode->criteria;
            if(!$criteria){ throw new Exception('ไม่พบข้อมูล',400); }
            $discountCodeName = $criteria->name;
            if($criteria->purechase_amount_threshold > $purchase){
                return response()->json([
                    'status' => 'fail',
                    'message' => 'ยอดซื้อไม่ถึงเกณฑ์ที่กำหนด',
                ], 200);
            }

            if($criteria->discount_target == 'purchase'){
                if($criteria->discount_type == 'fixed'){
                    $discountPurchase = $criteria->discount_value;
                }elseif($criteria->discount_type == 'percentage'){
                    $discountPurchase = ($purchase * $criteria->discount_value) / 100;
                    if($criteria->max_discount && $criteria->max_discount > 0){
                        if($discountPurchase > $criteria->max_discount){
                            $discountPurchase = $criteria->max_discount;
                        }
                    }
                    if ($discountPurchase > $purchase){
                        $discountPurchase = $purchase;
                    }
                }
            }elseif($criteria->discount_target == 'shipping'){
                if($criteria->discount_type == 'fixed'){
                    $discountShipping = $criteria->discount_value;
                }elseif($criteria->discount_type == 'percentage'){
                    $discountShipping = ($shippingCost * $criteria->discount_value) / 100;
                    if($criteria->max_discount && $criteria->max_discount > 0){
                        if($discountShipping > $criteria->max_discount){
                            $discountShipping = $criteria->max_discount;
                        }
                    }
                    if ($discountShipping > $shippingCost){
                        $discountShipping = $shippingCost;
                    }
                }
            }
            if($criteria->is_free_shipping){
                $discountShipping = $shippingCost;
            }
            return response()->json([
                'status' => 'success',
                'message' => 'Calulate successfully',
                'data' => [
                    'discountCodeName' => $discountCodeName,
                    'discountPurchase' => round($discountPurchase, 2),
                    'discountShipping' => round($discountShipping, 2),
                ]
            ], 200);
            
        }catch (Exception $e) {
            Log::error('Update failed: ' . $e->getMessage());
            return response()->json([
                'status' => 'fail',
                'message' => 'Server error',
            ], $e->getCode() > 0 ? $e->getCode() : 500);
        }
    }

    public function checkUsable(Request $request){
        try {
            $validator = Validator::make($request->all(), [
                'code' => 'required|string',
                'purchase' => 'required|numeric|min:1',
            ]);
            if ($validator->fails()) {
                $errors =  $validator->errors();
                return array('status'=>'fail','message'=>$errors,'validation'=>true);
            }
            
            $code = $request->code;
            $purchase = $request->purchase;
            $checkResult = self::validateDiscountCode($code, $purchase);
            $checkResult = $checkResult->getData();
            return response()->json($checkResult, 200);
            
        }catch (Exception $e) {
            Log::error('Update failed: ' . $e->getMessage());
            return response()->json([
                'status' => 'fail',
                'message' => 'Server error',
            ], $e->getCode() > 0 ? $e->getCode() : 500);
        }
    }

    public function fortest( Request $request){
        try {
            $discountCode = DiscountCode::orderBy('created_at','desc')->get();
            return view('admin.discount-code.test',[
                'discountCode'=>$discountCode
                ]
            );
        }catch (Exception $e) {
            Log::error('Update failed: ' . $e->getMessage());
            return redirect()->action('Admin\AdminHomeController@index')
                        ->with('errMsg', 'เกิดข้อผิดพลาด: ');
        }
    }
    
    public function downloadCSV($criteriaId){
        $discountCodes = DiscountCode::where('discount_code_criteria_id', $criteriaId)
                            ->pluck('code');

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="discount_codes.csv"',
        ];

        $callback = function () use ($discountCodes) {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, ['Code']);
            foreach ($discountCodes as $code) {
                fputcsv($handle, [$code]);
            }

            fclose($handle);
        };

        return new StreamedResponse($callback, 200, $headers);
    }

}