<?php
namespace App\Http\Controllers\Admin\CustomCss;

use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Input;
use Illuminate\Validation\Rule;
use Illuminate\Database\QueryException;
use App\Http\Controllers\MarketPlace;
use Illuminate\Http\Request;
use App\Language;
use App\CustomCss;
use Auth;
use Lang;

class CustomCssController extends MarketPlace
{ 
    private $tblCustomCss;

    public function __construct()
    {
        $this->middleware('admin.user');

        $this->tblCustomCss = with(new CustomCss)->getTable();
    }

    function cssrevision() {

        $revisions = CustomCss::select('*')->orderBy('updated_at')->get();
        $revision = count($revisions);
        return view('admin.customcss.revisionCssList', ['revisions'=>$revisions,'revision'=>$revision]);

    }

    function restorecssrevision($id) {

        $revisions = CustomCss::select('*')->where('id',$id)->first();

        $data = CustomCss::where(['id'=>$id])
                ->update(['value' => $revisions->value]);
        $customadmincss =base_path("public/css/custom_style.css");
        file_put_contents($customadmincss, $revisions->value); 
        
        $permission = $this->checkUrlPermission('static_block');
        if($permission === true) {
        return redirect()->action('Admin\AdminHomeController@customeCss')->with('succMsg', 'Record updated Successfully!');
        }

    }      
}
