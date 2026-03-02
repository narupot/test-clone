<?php

namespace App\Http\Middleware;

use Closure;
use Request;
use Redirect;
use App\WebsiteConfiguration;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Http\Exceptions\MaintenanceModeException;

class CheckForMaintenanceMode
{
    protected $app;

    public function __construct(Application $app)
    {
        $this->app = $app;
    }

    
    public function handle($request, Closure $next)
    {
        //Get Website Maintenance Confiruration Data
        $ip = Request::ip();
        //$activation = $this->getWebsiteConfigValue($system_name="ACTIVATION");
        $site_maintenanc = $this->getWebsiteConfigValue($system_name="SITE_MAINTENANCE");
        $page_title = $this->getWebsiteConfigValue($system_name="PAGE_TITLE");
        $allowed_ip = $this->getWebsiteConfigValue($system_name="ALLOWED_IP");
        $by_pass_url = $this->getWebsiteConfigValue($system_name="BY_PASS_URL");
        $maintenance ='';
        $maintenance .= '<head><title>'.$page_title.'</title> </head>';
        $maintenance .= '<style type="text/css">img.fr-dib {
                        margin: 5px auto;
                        display: block;
                        float: none;
                        vertical-align: top;
                        }
                        img.fr-dib.fr-fir {
                        margin-right: 0;
                        }
                        img.fr-dib.fr-fil {
                        margin-left: 0;
                        }</style>';
        $maintenance .= $this->getWebsiteConfigValue($system_name="MAINTENANCE_PAGE_HTML");
        //Check condition for website under maintence           
        $allowed_ips = explode(',', $allowed_ip);  
        if(!empty($site_maintenanc) && !in_array($ip, $allowed_ips) && !$this->isBackendRequest($request)){
            if(!empty($by_pass_url)){
                return Redirect::away($by_pass_url);
            }else{
                return response($maintenance);                    
            }                                        
        }         

        /*if ($this->app->isDownForMaintenance() && !$this->isBackendRequest($request)) {
            $data = json_decode(file_get_contents($this->app->storagePath() . '/framework/down'), true);
            throw new MaintenanceModeException($data['time'], $data['retry'], $data['message']);
            return response('Be right back!', 503);
        }*/       

        return $next($request);
                
    }

    function getWebsiteConfigValue($system_name=null) {
        $system_val = '';
        if(!empty($system_name)){ 
            $system_val = \App\WebsiteConfiguration::getWebsiteValue($system_name);
        }  
        return $system_val;         
    } 

    private function isBackendRequest($request)
    {        
        $lang_code_url = getConfigValue('LANG_CODE_IN_URL');
        if ($lang_code_url=="Y") {
            $def_lang_id = Request::segment(1);        
            return ($request->is($def_lang_id.'/admin/*') or $request->is($def_lang_id.'/admin') or $request->is($def_lang_id.'/handleServerRequest'));
        }else{
            $def_lang_id = Request::segment(1);
            return ($request->is('admin/*') or $request->is($def_lang_id));
        }
    }
}