<?php

namespace App\Http\Controllers\Admin\Color;
use App\Http\Controllers\MarketPlace;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\QueryException;

use App\ColorManagement;
use Auth;
use Config;
use File;
//use App\SystemConfig;

class ColorController extends MarketPlace
{
    //public $tableTranslation;

    public function __construct()
    {   
        $this->middleware('admin.user'); 

        //$this->tableTranslation = with(new TranslationSource)->getTable();      
    }     
    
    /**
     * Display a listing of the resource.   
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {

       $color_list = ColorManagement::OrderBy('id','Desc')->get();

       return view('admin.color.colorList', ['color_list'=>$color_list]);
    }

    public function updateSingleColor(Request $request) {

        $user_id = Auth::guard('admin_user')->user()->id;
        $cvalues = $request->cvalues;
        $comments = $request->comments;
        foreach ($cvalues as $key=>$data) {

            $affected = ColorManagement::where(['id' => $key])->update(['color_code' => $data, 'updated_by'=> $user_id,'updated_at'=>date('Y-m-d H:i:s')]);
         }
                 
         /*update language key file*/
         $this->UpdateOrCreateColorKeyInFile();
         echo 'Records has been Updated Successfully!';
            exit;
        
    }

    public function updateAll(Request $request){

        $user_id = Auth::guard('admin_user')->user()->id;

        $cvalues = $request->cvalues; 
        $updateLangFile = 0;

       /**********************Update All ******************************/
       if(isset($request->updateall)){
           
            foreach ($cvalues as $key => $data) {
                if(!empty($data)){
                    $affected = ColorManagement::where(['id' => $key])->update(['color_code' => $data, 'updated_by'=> $user_id,'updated_at'=>date('Y-m-d H:i:s')]);

                    $updateLangFile = 1; //for update lang file

                }
            }
            
                 
         /*update language key file*/
         $this->UpdateOrCreateColorKeyInFile();

         return redirect()->action('Admin\Color\ColorController@index')->with('succMsg', 'Records have been Updated Successfully!');

        }
    }

    public function UpdateOrCreateColorKeyInFile(){

        $file_complete_path = base_path('public/scss/component').'/_variable.scss';
        //dd($file_complete_path);
        $saveddatas = ColorManagement::get();
                  $file_content = '';

                  foreach($saveddatas as $key => $value){
                    //$file_content[$value->sourceName->source] = $value->tvalues;
                     $file_content .= "$".$value->variable_name." : ".addslashes($value->color_code).";\n";

                  }
                  dd($file_complete_path, $file_content);
         File::put($file_complete_path, $file_content);        
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create($id){
        
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {        
      
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }

    
}
