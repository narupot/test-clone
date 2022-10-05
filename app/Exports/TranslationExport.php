<?php
namespace App\Exports;
use DB;
use App\Language;
use App\TranslationModule;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;

class TranslationExport implements FromCollection,WithHeadings,ShouldAutoSize
{
    protected $guarded = [];
    protected $module_id;
    protected $lang_id;
    protected $lang_code;

    public function __construct($module_id, $lang_id, $lang_code)
    {
        $this->module_id = $module_id;
        $this->lang_id = $lang_id;
        $this->lang_code = $lang_code;
    }

    public function collection()
    {
        /*$language = Language::select('languageCode')->where('id', $this->lang_id)->first();
        $sources_key = TranslationModule::getModuleSourceValue($this->module_id, $this->lang_id);
        $sources_key_arr[] = ['source', $language->languageCode , 'comment'];
        foreach($sources_key as $source) {
            $sources_key_arr[] = [$source->source, $source->source_value, $source->comment];
        } 

        return $sources_key_arr;*/
        return collect(self::getModuleSourceValue($this->module_id, $this->lang_id));



    }

    public function getModuleSourceValue($module_id, $lang_id) {
        return DB::table(with(new \App\TranslationModule)->getTable() . ' as tm')
            ->join(with(new \App\TranslationSource)->getTable() . ' as ts', 'tm.id', '=', 'ts.module_id')
            ->leftjoin(with(new \App\TranslationSourceDetails)->getTable() . ' as tsd', [['ts.id', '=', 'tsd.source_id'], ['tsd.lang_id', '=', DB::raw($lang_id)]])
            ->select('ts.source', 'tsd.source_value', 'tsd.comment')
            ->where(['tm.id' => $module_id])
            ->orderBy('ts.id', 'asc')
            ->get()->toArray();
    }

    public function headings(): array {

        return ['source', $this->lang_code, 'comment'];
    }
}
