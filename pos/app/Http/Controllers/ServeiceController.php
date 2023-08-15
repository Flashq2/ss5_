<?php

/**
 * Created by PhpStorm.
 * User: Ratana Keo
 * Date: 11/17/2016
 * Time: 8:32 AM
 */

namespace App\Services;


use App\Models\Administration\ApplicationSetup\Currency;
use App\Models\Administration\ApplicationSetup\ItemUnitOfMeasure;
use App\Models\Financial\Setup\CurrencyExchangeRate;
use App\Models\System\SystemSettings as SystemSystemSettings;
use App\Models\Administration\ApplicationSetup\Item;
use App\Models\Administration\ApplicationSetup\PostCode;
use App\Models\Administration\GeneralSetup\NoSeries;
use App\Models\Administration\GeneralSetup\NoSeriesLine;
use App\Models\Financial\History\VAvgCostOverview;
use App\Models\Financial\History\VAvgCostOverviewLocation;
use App\Models\Financial\Transaction\ItemAverageCost;
use App\Models\System\MenuUrlShortcut;
use App\Models\Sales\Transaction\SalesShipmentLine;
use App\Services\CostingService;
use App\Models\Financial\Setup\FixAsset;
use App\Models\System\ApplicationSetup;
use App\Models\System\CompanyInformation;
use App\Models\Purchase\Transaction\PurchaseLine;
use App\Models\Administration\ApplicationSetup\ItemCharge;
use App\Models\System\Document;
use App\Models\System\DocumentLine;
use App\Models\Financial\Setup\VatPostingSetup;
use App\Models\Sales\Setup\ItemSpecification;
use App\Models\Financial\Setup\Vendor;
use App\Models\System\Page;
use App\Models\System\MenuUrl;
use App\Models\System\PageGroup;
use App\Models\System\PageGroupField;
use App\Models\Purchase\Transaction\ItemForecastLineQuantity;
use App\Models\System\PageGroupFieldRelation;
use App\Models\System\PageGroupFieldTemplate;
use App\Models\System\TableFieldUrlParas;
use App\Models\System\TableFlowField;
use App\Models\Administration\Database\Setup\ExcelUploadLog;
use App\Models\System\TableRelation;
use App\Models\System\TableFieldFlowFieldParas;
use App\Models\Administration\Database\Setup\Tables;
use App\Models\Warehouse\Transaction\ItemLedgerEntry;
use App\Models\Warehouse\Transaction\ItemValueEntry;
use App\Models\Financial\Setup\GeneralPostingSetup;
use App\Models\Financial\Setup\InventoryPostingSetup;
use App\Models\Financial\History\GeneralLedgerEntry;
use App\Models\Financial\Setup\ChartOfAccount;
use App\Models\System\TableFieldUrl;
use App\Models\Sales\Transaction\OpportunityHeader;
use App\Models\Sales\Transaction\OpportunityLine;
use App\Models\Workflow\Transaction\WorkflowActivity;
use App\Models\Financial\Transaction\ItemJournalLine;
use App\Models\Administration\ApplicationSetup\Location;
use App\Models\Sales\Transaction\SaleLine;
use App\Organizations;
use App\OrganizationUploadPath;
use App\SystemError500;
use App\User;
use Faker\Provider\DateTime;
use Image;
use GuzzleHttp\Psr7\Request;
use Illuminate\Support\Facades\Input;
use App\Models\Administration\Database\Setup\TableField;
use Auth;
use Carbon\Carbon;
use Config;
use Illuminate\Support\Facades\DB;
use Lang;
use Mockery\Exception;
use Pusher\Pusher;
use Illuminate\Support\Facades\Session;
use App\Models\Financial\Transaction\PaymentJournal;
use Symfony\Component\HttpKernel\Tests\EventListener\ValidateRequestListenerTest;
use App\Models\Sales\Setup\SalespersonCustomer;
use App\Models\Financial\Setup\Customer;
use App\Models\Administration\ApplicationSetup\Salesperson;
use App\Models\System\TempDownlineUplineBuffer;
use App\Models\PointOfSales\POSSaleLine;
use App\Models\System\TableRecordsSorted;
use \App\Models\Administration\ApplicationSetup\SalespersonSalesperson;
use App\Models\Financial\Setup\CustomerTimelineHistory;
use Kreait\Firebase;
use Kreait\Firebase\Factory;
use Kreait\Firebase\ServiceAccount;

use App\Models\Administration\SystemSetup\UserSetup;
use App\Models\Financial\Transaction\ItemTrackingBuffer;
use App\Models\Purchase\Transaction\TransferLine;
use App\Models\System\PermissionDenyModifyFields;
use App\Models\Sales\Setup\LsnSetupPriceByUser;
use App\Models\System\CustomizeSettingsByCompany;
use App\MyNotification; 
use App\Traits\CUServices;
use App\Traits\SystemSettings;
use App\Models\System\CountryCarrier;
use App\Traits\FilterDataByBusinessUnit;
use App\Models\Administration\ApplicationSetup\ItemSKU; 
use App\Models\Purchase\Transaction\PurchaseReceiptLine;
use App\Models\Warehouse\Transaction\ItemJournalLine as TransactionItemJournalLine;

class service
{
    use SystemSettings;
    use CUServices;
    public $relation_record;
    public function relational_data($relations)
    {
        $data = array();
        if ($relations) {
            foreach ($relations as $r) {
                if ($r->modal_path <> null) {
                    $path = urldecode($r->modal_path);
                    $data[$r->data_table_name]  = $this->LoadList($path, $r->relation_field_name, $r->relation_desc_field);
                }
            }
        }
        return $data;
    }
    public function relational_data_with_condition($relations, $record = null)
    {
        try {
            $data = array();
            if ($relations) {
                foreach ($relations as $r) {
                    if ($r->type != 'Select2') {
                        if ($r->modal_path <> null) {
                            $path = urldecode($r->modal_path);
                            $this->relation_record = $r;
                            $data[$r->relation_table_name]  = $this->LoadListCondition($path, $r->field_id, $r->relation_field_name, $r->relation_desc_field, $record);
                        } else {
                            $this->relation_record = $r;
                            $data[$r->relation_table_name]  = $this->LoadListCondition2($r->data_table_name, $r->field_id, $r->relation_field_name, $r->relation_desc_field, $record);   
                        }
                    }
                }
            }
            return $data;
        } catch (\Exception $ex) {
            $this->saveErrorLog($ex);
            return null;
        }
    }
    public function LoadList($model_name, $value_member, $display_member)
    {
        $code = $value_member;
        $desc = $display_member;
        $_list = $model_name::select($code, $desc)->get();
        return $_list;
    }
    public function LoadListCondition($model_name, $relation_id, $value_member, $display_member, $record)
    {
        try {
            $conditions = PageGroupFieldRelation::where('field_id', $relation_id)->get();
            $criterias = array();
            $criterias2 = '1=1';
            $user_setup = Auth::user()->user_setup;
            $i = 0;
            if ($conditions) {
                foreach ($conditions as $condition) {
                    if ($condition->condition_type == 'field') {
                        $a1 = array($condition->field_name, $condition->condition_operator, $record[$condition->condition_value]);
                        $criterias[$i] = $a1;
                        $i = $i + 1;
                    } else if ($condition->condition_type == 'user') {
                        
                        $user_setup = Auth::user()->user_setup;
                        $condiction_name = $condition->condition_value;
                        if ($user_setup[$condition->condition_value] && strtolower($user_setup[$condition->condition_value]) <> 'all' && $user_setup[$condition->condition_value] <> '' && $user_setup[$condition->condition_value] <> 'ï¿½') {
                            if(hasColumnHelper("table_relation", 'data_type') && $this->relation_record->data_type == 'Multiple'){
                                $convert_value = explode(",",$user_setup[$condiction_name]);
                                $field_value = implode("','", $convert_value);
                                $criterias2 .= " AND $condition->field_name IN ('".$field_value."')";
                                
                            }else{
                                $a1 = array($condition->field_name, $condition->condition_operator, $user_setup[$condition->condition_value]);
                                $criterias[$i] = $a1;
                                $i = $i + 1;
                                
                            }
                        }
                    } else {
                        $a1 = array($condition->field_name, $condition->condition_operator, $condition->condition_value);
                        $criterias[$i] = $a1;
                        $i = $i + 1;
                    }
                }
            }
            $code = $value_member;
            $desc = $display_member;
            $_list = null;
            
            if ($model_name) {
                if ($model_name == 'App\Models\Administration\ApplicationSetup\InventoryPostGroup') {
                    $_list = $model_name::select($code, $desc);
                } else {
                    $_list = $model_name::select($code, $desc)->where($criterias)->whereRaw($criterias2);
                }
                if ($model_name == 'App\Models\Financial\Setup\ChartOfAccount') {
                    $_list = $_list->where('account_type', 'Posting');
                }
                
                
                $_list = $_list->get();
            }
            return $_list;
        } catch (\Exception $ex) {
            $this->saveErrorLog($ex);
            return null;
        }
    }
    public function LoadListCondition2($model_name, $relation_id, $value_member, $display_member, $record)
    {
        try {
            $conditions = PageGroupFieldRelation::where('field_id', $relation_id)->get();
            $criterias2 = '1=1';
            $criterias = array();
            $i = 0;
            if ($conditions) {
                foreach ($conditions as $condition) {
                    if ($condition->condition_type == 'field') {
                        $a1 = array($condition->field_name, $condition->condition_operator, $record[$condition->condition_value]);
                        $criterias[$i] = $a1;
                        $i = $i + 1;
                    } else if ($condition->condition_type == 'user') {
                        $user_setup = Auth::user()->user_setup;
                        $condiction_name = $condition->condition_value;
                        if ($user_setup[$condition->condition_value] && strtolower($user_setup[$condition->condition_value]) <> 'all' && $user_setup[$condition->condition_value] <> '' && $user_setup[$condition->condition_value] <> 'ï¿½') {
                            if(hasColumnHelper("table_relation", 'data_type') && $this->relation_record->data_type == 'Multiple'){
                                $convert_value = explode(",",$user_setup[$condiction_name]);
                                $field_value = implode("','", $convert_value);
                                $criterias2 .= " AND $condition->field_name IN ('".$field_value."')";
                            }else{
                                $a1 = array($condition->field_name, $condition->condition_operator, $user_setup[$condition->condition_value]);
                                $criterias[$i] = $a1;
                                $i = $i + 1;
                                
                            }
                        }
                    } else {
                        $a1 = array($condition->field_name, $condition->condition_operator, $condition->condition_value);
                        $criterias[$i] = $a1;
                        $i = $i + 1;
                    }
                }
            }
            
                
            
            $code = $value_member;
            $desc = $display_member;
            $_list = null;
            
            if ($model_name) {
                if(hasTableHelper($model_name) && hasColumnHelper($model_name, $code) && hasColumnHelper($model_name, $desc)){
                    $_list = DB::connection('company')->table($model_name)->select($code, $desc)->where($criterias)->whereRaw($criterias2);
                    if ($model_name == 'chart_of_account')  $_list = $_list->where('account_type', 'Posting');
                    $_list = $_list->get()->toArray();
                }
            }
           
            return $_list;
        } catch (\Exception $ex) {
            $this->saveErrorLog($ex);
            return null;
        }
    }
    public function getTableRelational($tableName)
    {
        $relations = TableRelation::where('table_name', $tableName)->get();
        return $relations;
    }
    public function resizeImage($target, $newcopy, $w, $h, $ext)
    {
        list($w_orig, $h_orig) = getimagesize($target);
        $scale_ratio = $w_orig / $h_orig;
        if (($w / $h) > $scale_ratio) {
            $w = $h * $scale_ratio;
        } else {
            $h = $w / $scale_ratio;
        }
        $img = "";
        $ext = strtolower($ext);
        if ($ext == "gif") {
            $img = imagecreatefromgif($target);
        } else if ($ext == "png") {
            if (is_file($target) && mime_content_type($target) == 'image/png') {
                $img = imagecreatefrompng($target);
            } else {
                return 'error';
            }
        } else {
            $img = imagecreatefromjpeg($target);
        }
        $tci = imagecreatetruecolor($w, $h);
        imagecopyresampled($tci, $img, 0, 0, 0, 0, $w, $h, $w_orig, $h_orig);
        imagejpeg($tci, $newcopy, 80);
    }
    public function get_client_ip()
    {
        $ipaddress = '';
        if (isset($_SERVER['HTTP_CLIENT_IP']))
            $ipaddress = $_SERVER['HTTP_CLIENT_IP'];
        else if (isset($_SERVER['HTTP_X_FORWARDED_FOR']))
            $ipaddress = $_SERVER['HTTP_X_FORWARDED_FOR'];
        else if (isset($_SERVER['HTTP_X_FORWARDED']))
            $ipaddress = $_SERVER['HTTP_X_FORWARDED'];
        else if (isset($_SERVER['HTTP_FORWARDED_FOR']))
            $ipaddress = $_SERVER['HTTP_FORWARDED_FOR'];
        else if (isset($_SERVER['HTTP_FORWARDED']))
            $ipaddress = $_SERVER['HTTP_FORWARDED'];
        else if (isset($_SERVER['REMOTE_ADDR']))
            $ipaddress = $_SERVER['REMOTE_ADDR'];
        else
            $ipaddress = 'UNKNOWN';
        return $ipaddress;
    }
    public function isExistPageGroupFieldByUser($objId, $user)
    {
        $count = PageGroupField::where('object_id', $objId)->where('user_name', $user)->count();
        return ($count > 0) ? true : false;
    }
    public function insertPageGroupField($objId, $user, $lst = null)
    {
        $records = PageGroupFieldTemplate::where('object_id', $objId)->orderBy('object_group_id')->orderBy('index')->get();
        \DB::beginTransaction();
        foreach ($records as $record) {
            $pageGroupField = new PageGroupField();
            $keys = array_keys($record['attributes']);
            $pageGroupField['user_name'] = $user;
            foreach ($keys as $key) {
                $pageGroupField[$key] = $record[$key];
            }
            try {
                $pageGroupField->save();
                \DB::commit();
            } catch (Exception $ex) {
                \DB::rollback();
                return false;
            }
        }
        return true;
    }
    public function getPageGroupFieldByUser($objId)
    {
        $data = PageGroup::where('object_id', $objId)->orderBy('group_range')->get();
        return $data;
    }

    public function getPageGroupFieldByUserMyTest()
    {
        $data = PageGroup::where('object_id', '1196000')->get();
        return $data;
    }
    public function getPageByUser($Id)
    {
        $data = Page::where('id', $Id)->first();
        return $data;
    }
    public function getPageByPageType($get_page_id)
    {
        $data = Page::whereIn('id', $get_page_id)->get();
        $str_data = array();
        foreach ($data as $value) {
            $str_data[] .= $value->object_name;
        }
        return $str_data;
    }
    public static function getPageGroupField($objId, $obj_group_id)
    {
        $data = PageGroup::select('stage', 'object_id', 'id',  'group_range')->where('object_id', $objId)->where('id', $obj_group_id)->orderBy('group_range')->first();
        return $data;
    }
    public function PageGroupFieldTemplateObjType($get_page_id)
    {
        $data = PageGroupFieldTemplate::whereIn('object_id', $get_page_id)->get();
        $arr = '';
        $i = 0;
        $p = 0;
        $l = 0;
        foreach ($data as $k => $value) {
            if ($value->object_type == 'page') {
                $i += 1;
                if ($i == 1) {
                    $arr .= 'page,';
                }
            }
            if ($value->object_type == 'list') {
                $l += 1;
                if ($l == 1) {
                    $arr .= 'list,';
                }
            }
            if ($value->object_type == 'sub_list') {
                $p += 1;
                if ($p == 1) {
                    $arr .= 'sub_list,';
                }
            }
        }
        $arr = explode(',', $arr);
        return $arr;
    }
    public function getDocumentHeader($tableId)
    {
        $data = Document::where('table_id', $tableId)->get();
        return $data;
    }
    public function setUserDetailApplication()
    {
        if (Auth::check()) {
            $org = Organizations::selectRaw("custom_language, custom_language_key")->where('id', Auth::user()->account_id)->first();
            $language_code = 'en';
            if ($org->custom_language == 'Yes') $language_code = Auth::user()->locale . '.' . $org->custom_language_key;
            else $language_code = Auth::user()->locale;
            Session::put('locale', $language_code);
            Session::put('timezone', Auth::user()->time_zone);
            Config::set('database.connections.company.database', Auth::user()->database_name);
            Config::set('app.timezone', Auth::user()->time_zone);
            \DB::purge('company');
            Lang::setlocale(($language_code) ? $language_code : 'en');

            $report_pagination = Config::get('app.report_pagination');
            if(hasColumnHelper("application_setup", "report_pagination")) {
                $application_setup = ApplicationSetup::select("report_pagination")->first();
                $report_pagination = Service::toDouble($application_setup->report_pagination);
                Config::set('app.report_pagination', $report_pagination);
            }elseif(hasColumnHelper("system_settings", "report_pagination")) {
                $report_pagination_val = getSettingHelper(3,'', true);
                Config::set('app.report_pagination', $report_pagination);
            }

            // System Settings
            if(hasTableHelper('system_settings')){
                $default_settings = \DB::connection('mysql')->table("system_settings")->get();
                foreach($default_settings as $default_setting){
                    if($default_setting->customize == "No"){ // Default settings is customize
                        $system_settings_company = SystemSystemSettings::where("key_id", $default_setting->key_id)->first(); 
                        if(!$system_settings_company){
                            $system_settings_company = new SystemSystemSettings();
                            $system_settings_company->key_id = $default_setting->key_id;
                            $system_settings_company->key_value = $default_setting->key_value;
                            $system_settings_company->key_name = $default_setting->key_name;
                            $system_settings_company->description = $default_setting->description;
                            $system_settings_company->customize = $default_setting->customize;
                            $system_settings_company->comment = $default_setting->comment;
                            $system_settings_company->source_type = $default_setting->source_type;
                            if(hasColumnHelper('system_settings', 'input_type')){
                                $system_settings_company->input_type = $default_setting->input_type;
                                $system_settings_company->field_description = $default_setting->field_description;
                            }
                            $system_settings_company->save();
                        }
                    }else{ // If the system settings is customize
                        $custmizeSettings = CustomizeSettingsByCompany::where("org_id", Auth::user()->account_id)->get();
                        $arr_key = collect($custmizeSettings)->pluck('key_settings')->toArray();
                        // Insert Setting if dosn't have in the company but the system 
                        foreach($custmizeSettings as $custmizeSetting){
                            $system_settings_company = SystemSystemSettings::where("key_id", $custmizeSetting->key_settings)->first();    
                            if(!$system_settings_company){
                                $default_setting = \DB::connection('mysql')->table("system_settings")->where("key_id", $custmizeSetting->key_settings)->first();
                                $system_settings_company = new SystemSystemSettings();
                                $system_settings_company->key_id = $default_setting->key_id;
                                $system_settings_company->key_value = $custmizeSetting->value_settings;
                                $system_settings_company->key_name = $default_setting->key_name;
                                $system_settings_company->description = $default_setting->description;
                                $system_settings_company->customize = $default_setting->customize;
                                $system_settings_company->comment = $default_setting->comment;
                                $system_settings_company->source_type = $default_setting->source_type;
                                if(hasColumnHelper('system_settings', 'input_type')){
                                    $system_settings_company->input_type = $default_setting->input_type;
                                    $system_settings_company->field_description = $default_setting->field_description;
                                }
                                $system_settings_company->save();
                            }
                        }
                        SystemSystemSettings::whereNotIn("key_id", $arr_key)->where("customize", "Yes")->delete();
                    }
                }
                
            }
            
        }
    }
    public function setUserApiApplication($request)
    {
        $language_code = 'en';
        // $org = Organizations::where('id',Auth::user()->account_id)->first(); 
        // $language_code = 'en';
        // if($org->custom_language == 'Yes'){
        //     $language_code = Auth::user()->locale.'.'.$org->custom_language_key;                
        // }else {
        //     $language_code = Auth::user()->locale;

        // }      
        Session::put('locale', $language_code);
        Session::put('timezone', $request->time_zone);
        Config::set('database.connections.company.database', $request->database_name);
        Config::set('database.connections.mysql.database', 'clearviewerp');
        Config::set('app.timezone', $request->time_zone);
        \DB::purge('company');
        \DB::purge('mysql');
        Lang::setlocale(($language_code) ? $language_code : 'en');
    }
    public function setExternalUserApiApplication($request)
    {
        $language_code = 'en';
        Session::put('locale', $language_code);
        Session::put('timezone', Auth::user()->time_zone);
        Config::set('database.connections.company.database', Auth::user()->database_name);
        Config::set('database.connections.mysql.database', 'clearviewerp');
        Config::set('app.timezone', Auth::user()->time_zone);
        \DB::purge('company');
        \DB::purge('mysql');
        Lang::setlocale(($language_code) ? $language_code : 'en');
    }
    public function QRCodeEncryptToURL($id, $appKey)
    {
        $httpPrefix = "http://ts.mpwt.gov.kh/";
        $keyB10 = $this->to10($appKey); //Convert key to base 10
        $prefixKey = $this->to62($keyB10 - 5); //New Code
        $string = sprintf('%010s', $id); //Format to 10 digits
        $string = sprintf('%06s', substr($string, 0, 5) + $keyB10)
            . $keyB10 . substr($string, 5, strlen($string) - 5);
        $string = strrev($string); //Reverse the base 10
        $string = $this->to62($string); // to base 62 [0-9a-zA-Z]
        $string = strrev($string); //revert string
        return $httpPrefix . $prefixKey . $string; //Update Code
    }
    public function QRCodeDecryptFromURL($url)
    {
        //Update
        $httpPrefix = "http://ts.mpwt.gov.kh/";
        $string = str_replace($httpPrefix, "", $url);
        $keyB10 = $this->to10(substr($string, 0, 1)) + 5; //UPDATE
        $kLen = strlen($keyB10); //Find the key length
        $string = substr($string, 1, strlen($string) - 1); // Remove prefix key
        $string = strrev($string); //revert string
        $string = $this->to10($string); //Convert base 64 to 10
        $string = number_format($string, 0, '', ''); //Prevent too long number!
        $string = sprintf('%-0' . (11 + $kLen) . 's', strrev($string));
        $string = (substr($string, 0, 6) - $keyB10) . substr($string, 6 + $kLen, strlen($string) - 6 - $kLen); //Reject key out
        $string = number_format($string, 0, '', ''); //UPDATE //convert to int.
        return array($this->to62($keyB10), $string); //NEW //Array {key,id}
    }
    public function Authentication($credential)
    {
        return (Auth::attempt($credential)) ? true : false;
    }
    public function getPostCodeByCode($code)
    {
        $postcode = PostCode::where('code', $code)->first();
        return $postcode;
    }
    public function generateLotSerialNo($no_series_code, $ref, $date, $no_series, $no_line)
    {

        if (!service::toDouble($no_line['last_used_no'])) {
            $no = service::toDouble($no_line['starting_no']);
        } else {
            $no = service::toDouble($no_line['last_used_no']) + service::toDouble($no_line['increment']);
        }
        $no_line['last_used_no'] =  $no;
        $no_line['last_used_date'] =  Carbon::now()->toDateTimeString();

        if ($no_line['prefix']) {
            $no = $no_line['prefix'] . str_pad($no, service::toDouble($no_line['no_digits']), '0', STR_PAD_LEFT);
        } else {
            $no = str_pad($no, service::toDouble($no_line['no_digits']), '0', STR_PAD_LEFT);
        }
        return $no;
    }
    public function generateNo($no_series_code, $ref)
    {
        $no_series_code = rtrim(ltrim($no_series_code, ' '), ' ');
        if (Auth::check()) {
            $date = Auth::user()->user_setup;
        } else {
            $date = ApplicationSetup::first();
        }
        if (!$date['default_working_date']) {
            $working_date = Carbon::now()->toDateString();
        } else {
            $working_date = Carbon::parse($date['default_working_date'])->toDateString();
        }

        if ($no_series_code) {
            $no_series = NoSeries::where('code', $no_series_code)->first();
            if (!$no_series) {
                $no_series = NoSeries::where('is_default', 'Yes')->whereRaw("LOWER(reference)=LOWER('$ref')")->first();
                if (!$no_series) {
                    return "error_no_series";
                }
                if ($no_series->allow_manual == 'Yes') {
                    return $no_series_code;
                } else {
                    return "error_no_series";
                }
            }
        } else {
            $no_series = NoSeries::where('is_default', 'Yes')->whereRaw("LOWER(reference)=LOWER('$ref')")->first();
        }
        if (!$no_series) {
            return "error_no_series";
        }

        $no_line = NoSeriesLine::where('no_series_code', $no_series['code'])
            ->whereRaw("'$working_date' between starting_date and ending_date")
            ->orderBy('starting_date')->first();
        
        if (!$no_line) return "error_no_series";
        if($no_line->increment == 0 || $no_line->increment == "") $no_line->increment = 1;

        if (service::toDouble($no_line['last_used_no']) == 0) $no = service::toDouble($no_line['starting_no']);
        else $no = service::toDouble($no_line['last_used_no']) + service::toDouble($no_line['increment']);

        $no_line['last_used_no'] =  $no;
        $no_line['last_used_date'] =  Carbon::now()->toDateTimeString();
        $no_line->save();

        if ($no_line['prefix']) {
            if ($no_line['prefix'] == 'DMY' || $no_line['prefix'] == 'MY' || $no_line['prefix'] == 'YMD' || $no_line['prefix'] == 'YM') {
                $no = Carbon::now()->format(strtolower($no_line['prefix'])) . str_pad($no, service::toDouble($no_line['no_digits']), '0', STR_PAD_LEFT);
            } else {
                $no = $no_line['prefix'] . str_pad($no, service::toDouble($no_line['no_digits']), '0', STR_PAD_LEFT);
            }
        } else {
            $no = str_pad($no, service::toDouble($no_line['no_digits']), '0', 0);
        }

        return $no;
    }
    public function generateNoV2($no_series_code, $ref)
    {
        $no_series_code = rtrim(ltrim($no_series_code, ' '), ' ');
        if (Auth::check()) $date = Auth::user()->userSetup();
        else $date = ApplicationSetup::first();
        
        if (!$date['default_working_date']) $working_date = Carbon::now()->toDateString();
        else $working_date = Carbon::parse($date['default_working_date'])->toDateString();
        
        if ($no_series_code) {
            $no_series = NoSeries::where('code', $no_series_code)->first();
            if (!$no_series) {
                $no_series = NoSeries::where('is_default', 'Yes')->whereRaw("LOWER(reference)=LOWER('$ref')")->first();
                if (!$no_series) {
                    $msg = trans('greetings.TheNumberSerialWasNotDefault', ['document_type' => $ref]);
                    return ['status' => 'warning', 'msg' => $msg];
                }
                if ($no_series->allow_manual == 'Yes')  return ['status' => 'success', 'no_serial' => $no_series_code]; 
                else {
                    $msg = trans('greetings.TheNumberSerialWasNotAllowmanual', ['document_type' => $ref, "serial_code" => $no_series_code]);
                    return ['status' => 'warning', 'msg' => $msg];
                } 
            }
        } else {
            $no_series = NoSeries::where('is_default', 'Yes')->whereRaw("LOWER(reference)=LOWER('$ref')")->first();
        }
        if (!$no_series) {
            $msg = trans('greetings.TheNumberSerialWasNotDefaultOrNotSetup', ['document_type' => $ref]);
            return ['status' => 'warning', 'msg' => $msg];
        }

        $no_line = NoSeriesLine::where('no_series_code', $no_series['code'])->whereRaw("'$working_date' between starting_date and ending_date")
            ->orderBy('starting_date')->first();
        if (!$no_line) {
            $msg = trans('greetings.WorkingDateWrongOfNumberSerial', ['document_type' => $ref, 'working_date' => Service::number_formattor($working_date, "date"), 'serial_code' => $no_series['code']]);
            return ['status' => 'warning', 'msg' => $msg];
        }
        if($no_line->increment == 0 || $no_line->increment == "") $no_line->increment = 1;

        if (service::toDouble($no_line['last_used_no']) == 0) $no = service::toDouble($no_line['starting_no']);
        else $no = service::toDouble($no_line['last_used_no']) + service::toDouble($no_line['increment']);
        
        $no_line['last_used_no'] =  $no;
        $no_line['last_used_date'] =  Carbon::now()->toDateTimeString();
        $no_line->save();
        if ($no_line['prefix']) {
            if ($no_line['prefix'] == 'DMY' || $no_line['prefix'] == 'MY' || $no_line['prefix'] == 'YMD' || $no_line['prefix'] == 'YM') {
                $no = Carbon::now()->format(strtolower($no_line['prefix'])) . str_pad($no, service::toDouble($no_line['no_digits']), '0', STR_PAD_LEFT);
            } else {
                $no = $no_line['prefix'] . str_pad($no, service::toDouble($no_line['no_digits']), '0', STR_PAD_LEFT);
            }
        } else {
            $no = str_pad($no, service::toDouble($no_line['no_digits']), '0', STR_PAD_LEFT);
        }
        return ['status' => 'success', 'no_serial' => $no];
    }

    public function generatePostNo($no_series_code, $ref)
    {
        if (Auth::check()) {
            $app_setup = Auth::user()->userSetup();
        } else {
            $app_setup = ApplicationSetup::first();
        }
        $date = $app_setup->userSetup();
        if (!$date['default_working_date']) {
            $working_date = Carbon::now()->toDateString();
        } else {
            $working_date = $date['default_working_date'];
        }
        if (!$no_series_code) {
            $no_series = NoSeries::where('is_default', 'Yes')->whereRaw("LOWER(reference)=LOWER('$ref')")->first();
        } else {
            $no_series = NoSeries::whereRaw("LOWER(code)=LOWER('$no_series_code')")->whereRaw("LOWER(reference)=LOWER('$ref')")->first();
        }
        if (!$no_series) {
            $data = array(
                'status' => 'error_no_series',
            );
            return $data;
        }
        $no_line = NoSeriesLine::where('no_series_code', $no_series['code'])->whereRaw("'$working_date' between starting_date and ending_date")
            ->orderBy('starting_date')->first();
        if (!$no_line) {
            $data = array(
                'status' => 'error_no_series',
            );
            return $data;
        }
        if (service::toDouble($no_line['last_used_no']) == 0) {
            $no = service::toDouble($no_line['starting_no']);
        } else {
            $no = service::toDouble($no_line['last_used_no']) + service::toDouble($no_line['increment']);
        }
        if ($no_line['prefix']) {
            $no = $no_line['prefix'] . str_pad($no, service::toDouble($no_line['no_digits']), '0', STR_PAD_LEFT);
        } else {
            $no = str_pad($no, service::toDouble($no_line['no_digits']), '0', STR_PAD_LEFT);
        }
        $data = array(
            'status' => 'success',
            'no'  => $no,
            'no_series' => $no_series['code'],
        );
        return $data;
    }
    public static function selectBlankValue()
    {
        return 'ï¿½';
    }
    public static function encrypt($string, $key = 5)
    {
        // $result = '';
        // for($i=0, $k= strlen($string); $i<$k; $i++) {
        //     $char = substr($string, $i, 1);
        //     $keychar = substr($key, ($i % strlen($key))-1, 1);
        //     $char = chr(ord($char)+ord($keychar));
        //     $result .= $char;
        // }        
        return base64_encode($string);
    }
    public static function decrypt($string, $key = 5)
    {
        // $result = '';
        // $string = base64_decode($string);
        // for($i=0,$k=strlen($string); $i< $k ; $i++) {
        //     $char = substr($string, $i, 1);
        //     $keychar = substr($key, ($i % strlen($key))-1, 1);
        //     $char = chr(ord($char)-ord($keychar));
        //     $result.=$char;
        // }
        // return $result;
        return base64_decode($string);
    }
    public static function CollectionCulumn($salesperson)
    {
        $status_analysis_time_visited = null;
        $status_analysis_positioning = null;
        $diff_human = null;
        $actual_distance = null;
        $customer = Customer::select('sales_kpi_analysis_code')->where('no', $salesperson->customer_no)->first();
        $sales_kpi_analysis_code = '';
        if ($customer) $sales_kpi_analysis_code = $customer->sales_kpi_analysis_code;

        if ($sales_kpi_analysis_code) {
            $status_analysis_header = \App\Models\Administration\ApplicationSetup\StatusAnalysisCode::where('analysis_type', 'Salesperson KPI')
                ->where('code', $sales_kpi_analysis_code)->first();
        } else {
            $status_analysis_header = \App\Models\Administration\ApplicationSetup\StatusAnalysisCode::where('analysis_type', 'Salesperson KPI')->where('is_default', '<>', 'No')->first();
        }
        if ($status_analysis_header) {
            if ($salesperson->ending_time != '' && $salesperson->starting_time != '') {
                $starting_date_time = \Carbon\Carbon::parse($salesperson->schedule_date . $salesperson->ending_time);
                $visit_date_time = \Carbon\Carbon::parse($salesperson->schedule_date . $salesperson->starting_time);
                $diff = $starting_date_time->diffInMinutes($visit_date_time);
                $diff_human = $starting_date_time->diffInMinutes($visit_date_time) . 'mn';
                $status_analysis_time_visited = \App\Models\Administration\ApplicationSetup\StatusAnalysisLine::where('analysis_code', $status_analysis_header->code)
                    ->where('from_number', '<=', $diff)->where('to_number', '>=', $diff)
                    ->where('analysis_option', 'Time of Visited')->first();
            }
            if (\App\Services\service::toDouble($salesperson->actual_distance) != 0) {
                $actual_distance = $salesperson->actual_distance;
                $actual_distance_in_meter = \App\Services\service::toDouble($actual_distance);
                $status_analysis_positioning = \App\Models\Administration\ApplicationSetup\StatusAnalysisLine::where('analysis_code', $status_analysis_header->code)
                    ->where('from_number', '<=', $actual_distance_in_meter)->where('to_number', '>=', $actual_distance_in_meter)
                    ->where('analysis_option', 'Positioning')->first();
            }
        }
        return array(
            'status_analysis_time_visited' => $status_analysis_time_visited,
            'diff_human' => $diff_human,
            'status_analysis_positioning' => $status_analysis_positioning,
        );
    }

    public static function toDouble($number)
    {
        if (!$number || $number == '') $number = 0;
        if (Auth::check()) {
            $app_setup = Auth::user()->app_setup;
        } else {
            $app_setup = ApplicationSetup::first();
        }
        if (!$app_setup) {
            $app_setup = ApplicationSetup::first();
        }

        return (float)str_replace($app_setup->separator_symbol, '', $number);
    }
    public static function convertToHoursMins($time)
    {
        if ($time < 1) {
            return;
        }
        $hours = floor($time / 60);
        $minutes = ($time % 60);
        return $hours . 'h' . $minutes . 'mn';
    }
    public static function toGDouble($number)
    {
        if (!$number || $number == '') $number = 0;
        return (float)str_replace(',', '', $number);
    }
    public static function number_formattor($number, $format, $currency = null)
    {

        if (Auth::check()) {
            $app_setup = Auth::user()->app_setup;
        } else {
            $app_setup = ApplicationSetup::first();
        }
        if (!$app_setup) {
            $app_setup = ApplicationSetup::first();
        }
        $price_decimal = $app_setup->price_decimal;
        $amount_decimal = $app_setup->amount_decimal;
        $amount_lcy_decimal = $app_setup->amount_decimal;
        $cost_decimal = $app_setup->cost_decimal;
        $acy_amount = $app_setup->amount_decimal;
        $acy_cost = $app_setup->cost_decimal;
        if ($currency) {
            $price_decimal = $currency->unit_amount_decimal;
            $amount_decimal = $currency->amount_decimal;
            $cost_decimal = $currency->unit_amount_decimal;
            $acy_amount = $currency->amount_decimal;
        }
        if ($format != 'date' && $format != 'datetime') {
            $number = (float)str_replace($app_setup->separator_symbol, '', $number);
        }
        if ($number == 0) return '';
        switch (strtolower($format)) {
            case 'quantity':
                $result = number_format($number, $app_setup->quantity_decimal, $app_setup->decimalpoint, $app_setup->separator_symbol);
                break;
            case 'quantity_size':
                $result = number_format($number, $app_setup->quantity_size_decimal, $app_setup->decimalpoint, $app_setup->separator_symbol);
                break;
            case 'cost':
                $result = number_format($number, $cost_decimal, $app_setup->decimalpoint, $app_setup->separator_symbol);
                break;
            case 'price':
                $result = number_format($number, $price_decimal, $app_setup->decimalpoint, $app_setup->separator_symbol);
                break;
            case 'amount':
                $result = number_format($number, $amount_decimal, $app_setup->decimalpoint, $app_setup->separator_symbol);
                break;
            case 'amount_lcy':
                $result = number_format($number, $amount_lcy_decimal, $app_setup->decimalpoint, $app_setup->separator_symbol);
                break;
            case 'measurement':
                $result = number_format($number, $app_setup->measurement_decimal, $app_setup->decimalpoint, $app_setup->separator_symbol);
                break;
            case 'percentage':
                $result = number_format($number, $app_setup->percentage_decimal, $app_setup->decimalpoint, $app_setup->separator_symbol);
                break;
            case 'general_decimal':
                $result = number_format($number, $app_setup->general_decimal, $app_setup->decimalpoint, $app_setup->separator_symbol);
                break;
            case 'general':
                $result = number_format($number, 0, $app_setup->decimalpoint, $app_setup->separator_symbol);
                break;
            case 'number':
                $result = number_format($number, 0, '.', '');
                break;
            case 'amount_reil':
                $result = number_format($number, 0, '.', ',');
                $result = strpos($result, '.') !== false ? rtrim(rtrim($result, '0'), '.') : $result;
                break;
            case 'currency_factor':
                $result = number_format($number, 10, '.', '');
                break;
            case 'qty_to_assign':
                $result = number_format($number, $app_setup->item_qty_format, $app_setup->decimalpoint, $app_setup->separator_symbol);
                break;
            case 'acy_cost':
                $result = number_format($number, $acy_cost, $app_setup->decimalpoint, $app_setup->separator_symbol);
                break;
            case 'acy_amount':
                $result = number_format($number, $acy_amount, $app_setup->decimalpoint, $app_setup->separator_symbol);
                break;
            case 'date':
                if ($number) {
                    if ($number == '1900-01-01' || $number == '2500-01-01' || $number == '2900-01-01') {
                        return '';
                    } else {
                        return Carbon::parse($number)->format('d-M-Y');
                    }
                } else {
                    return '';
                }
                break;
            case 'datetime':
                if ($number) {
                    if ($number == '1900-01-01 00:00:00' || $number == '2500-01-01 00:00:00') {
                        return '';
                    } else {
                        return Carbon::parse($number)->format('d-M-Y h:i:s');
                    }
                } else {
                    return '';
                }
                break;
            case 'dateAndTime':
                if ($number) {
                    if ($number == '1900-01-01 00:00:00' || $number == '2500-01-01 00:00:00') {
                        return '';
                    } else {
                        return Carbon::parse($number)->format('d-M-Y h:i:s');
                    }
                } else {
                    return '';
                }
                break;
            case 'gps':
                $result = number_format($number, 12, $app_setup->decimalpoint, $app_setup->separator_symbol);
                break;
            case 'day':
                $result = number_format($number, 0, $app_setup->decimalpoint, $app_setup->separator_symbol);
                break;
            case 'hour':
                $result = number_format($number, 1, $app_setup->decimalpoint, $app_setup->separator_symbol);
                break;
            case 'decimal':
                $result = number_format($number, 18, $app_setup->decimalpoint, $app_setup->separator_symbol);
                break;
            case 'vat_amount':
                $result = number_format($number, 3, '.', '');
                break;
            case '0':
                $result = number_format($number, 0, '.', $app_setup->separator_symbol);
                break;
            case '1':
                $result = number_format($number, 1, '.', $app_setup->separator_symbol);
                break;
            case '2':
                $result = number_format($number, 2, '.', $app_setup->separator_symbol);
                break;
            case '3':
                $result = number_format($number, 3, '.', $app_setup->separator_symbol);
                break;
            case '4':
                $result = number_format($number, 4, '.', $app_setup->separator_symbol);
                break;
            case '5':
                $result = number_format($number, 5, '.', $app_setup->separator_symbol);
                break;
            case '6':
                $result = number_format($number, 6, '.', $app_setup->separator_symbol);
                break;
            case '7':
                $result = number_format($number, 7, '.', $app_setup->separator_symbol);
                break;
            case '8':
                $result = number_format($number, 8, '.', $app_setup->separator_symbol);
                break;
            case '9':
                $result = number_format($number, 9, '.', $app_setup->separator_symbol);
                break;
            case '10':
                $result = number_format($number, 10, '.', $app_setup->separator_symbol);
                break;
            case '11':
                $result = number_format($number, 11, '.', $app_setup->separator_symbol);
                break;
            default:
                $result = number_format($number, $app_setup->general_decimal, $app_setup->decimalpoint, $app_setup->separator_symbol);
                break;
        }
        if (strpos($result, $app_setup->decimalpoint) !== false && $app_setup->decimal_zero !== 'Yes') {
            return rtrim($result, '0');
        } else {
            return $result;
        }
    }
    public static function number_gformattor($number, $format, $currency = null)
    {
        $app_setup = DB::connection('mysql')->table('application_setup')->first();
        if ($format != 'date' && $format != 'datetime') {
            $number = (float)str_replace($app_setup->separator_symbol, '', $number);
        }

        if ($number == 0) return '';
        switch ($format) {
            case 'quantity':
                $result = number_format($number, $app_setup->quantity_decimal, $app_setup->decimalpoint, $app_setup->separator_symbol);
                break;
            case 'quantity_size':
                $result = number_format($number, $app_setup->quantity_size_decimal, $app_setup->decimalpoint, $app_setup->separator_symbol);
                break;
            case 'cost':
                $result = number_format($number, $app_setup->cost_decimal, $app_setup->decimalpoint, $app_setup->separator_symbol);
                break;
            case 'price':
                $result = number_format($number, $app_setup->price_decimal, $app_setup->decimalpoint, $app_setup->separator_symbol);
                break;
            case 'amount':
                $result = number_format($number, $app_setup->amount_decimal, $app_setup->decimalpoint, $app_setup->separator_symbol);
                break;
            case 'measurement':
                $result = number_format($number, $app_setup->measurement_decimal, $app_setup->decimalpoint, $app_setup->separator_symbol);
                break;
            case 'percentage':
                $result = number_format($number, $app_setup->percentage_decimal, $app_setup->decimalpoint, $app_setup->separator_symbol);
                break;
            case 'general':
                $result = number_format($number, 0, $app_setup->decimalpoint, $app_setup->separator_symbol);
                break;
            case 'currency_factor':
                $result = number_format($number, 10, '.', '');
                break;
            case 'qty_to_assign':
                $result = number_format($number, $app_setup->item_qty_format, $app_setup->decimalpoint, $app_setup->separator_symbol);
                break;
            case 'acy_cost':
                if ($currency) {
                    $result = number_format($number, $currency->unit_amount_decimal, $app_setup->decimalpoint, $app_setup->separator_symbol);
                } else {
                    $result = number_format($number, $app_setup->cost_decimal, $app_setup->decimalpoint, $app_setup->separator_symbol);
                }
                break;
            case 'acy_amount':
                if ($currency) {
                    $result = number_format($number, $currency->amount_decimal, $app_setup->decimalpoint, $app_setup->separator_symbol);
                } else {
                    $result = number_format($number, $app_setup->amount_decimal, $app_setup->decimalpoint, $app_setup->separator_symbol);
                }
                break;
            case 'date':
                if ($number) {
                    if ($number == '1900-01-01' || $number == '2500-01-01') {
                        return '';
                    } else {
                        return Carbon::parse($number)->format('d-M-Y');
                    }
                } else {
                    return '';
                }
                break;
            case 'datetime':
                if ($number) {
                    if ($number == '1900-01-01' || $number == '2500-01-01') {
                        return '';
                    } else {
                        return Carbon::parse($number)->format('d-M-Y');
                    }
                } else {
                    return '';
                }
                break;
            case 'dateAndTime':
                if ($number) {
                    if ($number == '1900-01-01' || $number == '2500-01-01') {
                        return '';
                    } else {
                        return Carbon::parse($number)->format('d-M-Y h:i:s');
                    }
                } else {
                    return '';
                }
                break;
            case 'gps':
                $result = number_format($number, 12, '.', '');
                break;
            case 'day':
                $result = number_format($number, 0, '.', '');
                break;
            case 'month':
                $result = number_format($number, 0, '.', '');
                break;
            case 'hour':
                $result = number_format($number, 1, '.', '');
                break;
            case 'decimal':
                $result = number_format($number, 18, $app_setup->decimalpoint, $app_setup->separator_symbol);
                break;
            case 'vat_amount':
                $result = number_format($number, 3, '.', '');
                break;
            default:
                $result = number_format($number, $app_setup->general_decimal, $app_setup->decimalpoint, $app_setup->separator_symbol);
                break;
        }
        return $result;
    }
    public static function number_formattor_link($number, $format, $currency = null, $is_print = '')
    {
        if ($is_print == 'excel') {
            if ((float)$number == 0 || (float)$number == -0 || $number == '') return '0';
        } else {
            if ((float)$number == 0 || (float)$number == -0 || $number == '') return '-';
        }
        if (Auth::check()) {
            $app_setup = Auth::user()->app_setup;
        } else {
            $app_setup = ApplicationSetup::first();
        }
        if (!$app_setup) {
            $app_setup = ApplicationSetup::first();
        }
        $price_decimal = $app_setup->price_decimal;
        $amount_decimal = $app_setup->amount_decimal;
        $amount_lcy_decimal = $app_setup->amount_decimal;
        $cost_decimal = $app_setup->cost_decimal;
        $acy_amount = $app_setup->amount_decimal;
        $acy_cost = $app_setup->cost_decimal;
        if ($currency) {
            $price_decimal = $currency->unit_amount_decimal;
            $acy_amount = $currency->amount_decimal;
            $cost_decimal = $currency->unit_amount_decimal;
            $amount_decimal  = $currency->amount_decimal;
        }
        $separator_symbol = $app_setup->separator_symbol;
        if ($is_print == 'excel') {
            $separator_symbol = '';
        }

        if ($format != 'date' && $format != 'datetime') {
            $number = (float)str_replace($separator_symbol, '', $number);
        }
        switch (strtolower($format)) {
            case 'quantity':
                $result = number_format($number, $app_setup->quantity_decimal, $app_setup->decimalpoint, $separator_symbol);
                break;
            case 'quantity_size':
                $result = number_format($number, $app_setup->quantity_size_decimal, $app_setup->decimalpoint, $separator_symbol);
                break;
            case 'cost':
                $result = number_format($number, $cost_decimal, $app_setup->decimalpoint, $separator_symbol);
                break;
            case 'price':
                $result = number_format($number, $price_decimal, $app_setup->decimalpoint, $separator_symbol);
                break;
            case 'amount':
                $result = number_format($number, $amount_decimal, $app_setup->decimalpoint, $separator_symbol);
                break;
            case 'amount_lcy':
                $result = number_format($number, $amount_lcy_decimal, $app_setup->decimalpoint, $separator_symbol);
                break;
            case 'measurement':
                $result = number_format($number, $app_setup->measurement_decimal, $app_setup->decimalpoint, $separator_symbol);
                break;
            case 'percentage':
                $result = number_format($number, $app_setup->percentage_decimal, $app_setup->decimalpoint, $separator_symbol);

                break;
            case 'general':
                $result = number_format($number, 0, $app_setup->decimalpoint, $separator_symbol);
                break;
            case 'number':
                $result = number_format($number, 0, $app_setup->decimalpoint, $separator_symbol);
                break;
            case 'currency_factor':
                $result = number_format($number, 10, '.', '');
                break;
            case 'qty_to_assign':
                $result = number_format($number, $app_setup->item_qty_format, $app_setup->decimalpoint, $separator_symbol);
                break;
            case 'acy_cost':
                if ($currency) {
                    $result = number_format($number, $currency->unit_amount_decimal, $app_setup->decimalpoint, $separator_symbol);
                } else {
                    $result = number_format($number, $app_setup->cost_decimal, $app_setup->decimalpoint, $separator_symbol);
                }
                break;
            case 'acy_amount':
                if ($currency) {
                    $result = number_format($number, $currency->amount_decimal, $app_setup->decimalpoint, $separator_symbol);
                } else {
                    $result = number_format($number, $app_setup->amount_decimal, $app_setup->decimalpoint, $separator_symbol);
                }
                break;
            case 'date':
                if ($number) {
                    if ($number == '1900-01-01' || $number == '2500-01-01') {
                        return '';
                    } else {
                        return Carbon::parse($number)->format('d-M-Y');
                    }
                } else {
                    return '';
                }
                break;
            case 'datetime':
                if ($number) {
                    if ($number == '1900-01-01 00:00:00' || $number == '2500-01-01 00:00:00') {
                        return '';
                    } else {
                        return Carbon::parse($number)->format('d-M-Y h:i:s');
                    }
                } else {
                    return '';
                }
                break;
            case 'dateAndTime':
                if ($number) {
                    if ($number == '1900-01-01 00:00:00' || $number == '2500-01-01 00:00:00') {
                        return '';
                    } else {
                        return Carbon::parse($number)->format('d-M-Y h:i:s');
                    }
                } else {
                    return '';
                }
                break;
            case 'gps':
                $result = number_format($number, 12, '.', '');
                break;
            case 'day':
                $result = number_format($number, 0, '.', '');
                break;
            case 'hour':
                $result = number_format($number, 1, '.', '');
                break;
            case 'decimal':
                $result = number_format($number, 18, '.', '');
                break;
            case 'vat_amount':
                $result = number_format($number, 3, '.', '');
                break;
            case '0':
                $result = number_format($number, 0, '.', $separator_symbol);
                break;
            case '1':
                $result = number_format($number, 1, '.', $separator_symbol);
                break;
            case '2':
                $result = number_format($number, 2, '.', $separator_symbol);
                break;
            case '3':
                $result = number_format($number, 3, '.', $separator_symbol);
                break;
            case '4':
                $result = number_format($number, 4, '.', $separator_symbol);
                break;
            case '5':
                $result = number_format($number, 5, '.', $separator_symbol);
                break;
            case '6':
                $result = number_format($number, 6, '.', $separator_symbol);
                break;
            case '7':
                $result = number_format($number, 7, '.', $separator_symbol);
                break;
            case '8':
                $result = number_format($number, 8, '.', $separator_symbol);
                break;
            case '8':
                $result = number_format($number, 9, '.', $separator_symbol);
                break;
            case '9':
                $result = number_format($number, 10, '.', $separator_symbol);
                break;
            case '10':
                $result = number_format($number, 11, '.', $separator_symbol);
                break;
            default:
                $result = number_format($number, $app_setup->general_decimal, $app_setup->decimalpoint, $separator_symbol);
                break;
        }
        if (strpos($result, $app_setup->decimalpoint) !== false && $app_setup->decimal_zero !== 'Yes') {
            return rtrim($result, '0');
        } else {
            return $result;
        }
    }
    public static function number_formattor_database_round($number, $format)
    {
        if (Auth::check()) {
            $app_setup = Auth::user()->app_setup;
            if (!$app_setup) {
                $app_setup = ApplicationSetup::first();
            }
        } else {
            $app_setup = ApplicationSetup::first();
        }

        if (!$number || $number == '') $number = 0;
        $number = (float)str_replace($app_setup->separator_symbol, '', $number);
        $database_decimal_format = 18;
        switch (strtolower($format)) {
            case 'quantity':
                $result = number_format($number, $database_decimal_format, '.', '');
                break;
            case 'quantity_size':
                $result = number_format($number, $database_decimal_format, '.', '');
                break;
            case 'cost':
                $result = number_format($number, $database_decimal_format, '.', '');
                break;
            case 'price':
                $result = number_format($number, $database_decimal_format, '.', '');
                break;
            case 'amount':
                $result = number_format($number, $database_decimal_format, '.', '');
                break;
            case 'measurement':
                $result = number_format($number, $database_decimal_format, '.', '');
                break;
            case 'percentage':
                $result = number_format($number, $database_decimal_format, '.', '');
                break;
            case 'general':
                $result = number_format($number, $database_decimal_format, '.', '');
                break;
            case 'currency_factor':
                $result = number_format($number, $database_decimal_format, '.', '');
                break;
            case 'qty_to_assign':
                $result = number_format($number, $database_decimal_format, '.', '');
                break;
            case 'gps':
                $result = number_format($number, $database_decimal_format, '.', '');
                break;
            case 'day':
                $result = number_format($number, $database_decimal_format, '.', '');
                break;
            case 'hour':
                $result = number_format($number, $database_decimal_format, '.', '');
                break;
            default:
                $result = number_format($number, $database_decimal_format, '.', '');
                break;
        }
        return $result;
    }
    public static function number_formattor_database($number, $format)
    {
        if (Auth::check()) {
            $app_setup = Auth::user()->app_setup;
            if (!$app_setup) $app_setup = ApplicationSetup::first();
        } else {
            $app_setup = ApplicationSetup::first();
        }

        if (!$number || $number == '') $number = 0;
        $number = (float)str_replace($app_setup->separator_symbol, '', $number);
        switch (strtolower($format)) {
            case 'quantity':
                $result = number_format($number, $app_setup->quantity_decimal, '.', '');
                break;
            case 'quantity_size':
                $result = number_format($number, $app_setup->quantity_size_decimal, '.', '');
                break;
            case 'cost':
                $result = number_format($number, $app_setup->cost_decimal, '.', '');
                break;
            case 'cost_amount':
                    $result = number_format($number, 2, '.', '');
                    break;
            case 'price':
                $result = number_format($number, $app_setup->price_decimal, '.', '');
                break;
            case 'amount':
                $result = number_format($number, $app_setup->amount_decimal, '.', '');
                break;
            case 'amount_lcy':
                $result = number_format($number, $app_setup->amount_decimal, '.', '');
                break;
            case 'measurement':
                $result = number_format($number, $app_setup->measurement_decimal, '.', '');
                break;
            case 'percentage':
                $result = number_format($number, $app_setup->percentage_decimal, '.', '');
                break;
            case 'general':
                $result = number_format($number, 0, '.', '');
                break;
            case 'currency_factor':
                $result = number_format($number, 10, '.', '');
                break;
            case 'qty_to_assign':
                $result = number_format($number, $app_setup->item_qty_format, '.', '');
                break;
            case 'gps':
                $result = number_format($number, 12, '.', '');
                break;
            case 'day':
                $result = number_format($number, 0, '.', '');
                break;
            case 'hour':
                $result = number_format($number, 1, '.', '');
                break;
            case 'decimal':
                $result = number_format($number, 18, '.', '');
                break;
            case 'vat_amount':
                $result = number_format($number, 3, '.', '');
                break;
            case 'date':
                if ($number == null || $number == '' || $number == '1900-01-01') $result = '1900-01-01';
                else if ($number == '2500-01-01') $result = '2500-01-01';
                else $result = Carbon::parse($number)->toDateString();
                break;
            case 'datetime':
                if ($number == null || $number == '' || $number == '1900-01-01 00:00:00') $result = '1900-01-01 00:00:00';
                else if ($number == '2500-01-01 00:00:00') $result = '2500-01-01 00:00:00';
                else $result = Carbon::parse($number)->toDateTimeString();
                break;
            default:
                $result = number_format($number, $app_setup->general_decimal, '.', '');
                break;
        }
        return $result;
    }
    public static function number_gformattor_database($number, $format)
    {
        $app_setup = DB::connection('mysql')->table('application_setup')->first();
        if (!$number || $number == '') $number = 0;
        $number = (float)str_replace($app_setup->separator_symbol, '', $number);
        switch ($format) {
            case 'quantity':
                $result = number_format($number, $app_setup->quantity_decimal, '.', '');
                break;
            case 'quantity_size':
                $result = number_format($number, $app_setup->quantity_size_decimal, '.', '');
                break;
            case 'cost':
                $result = number_format($number, $app_setup->cost_decimal, '.', '');
                break;
            case 'price':
                $result = number_format($number, $app_setup->price_decimal, '.', '');
                break;
            case 'amount':
                $result = number_format($number, $app_setup->amount_decimal, '.', '');
                break;
            case 'measurement':
                $result = number_format($number, $app_setup->measurement_decimal, '.', '');
                break;
            case 'percentage':
                $result = number_format($number, $app_setup->percentage_decimal, '.', '');
                break;
            case 'general':
                $result = number_format($number, 0, '.', '');
                break;
            case 'currency_factor':
                $result = number_format($number, 10, '.', '');
                break;
            case 'qty_to_assign':
                $result = number_format($number, $app_setup->item_qty_format, '.', '');
                break;
            case 'gps':
                $result = number_format($number, 12, '.', '');
                break;
            case 'day':
                $result = number_format($number, 0, '.', '');
                break;
            case 'hour':
                $result = number_format($number, 1, '.', '');
                break;
            case 'decimal':
                $result = number_format($number, 18, '.', '');
                break;
            case 'vat_amount':
                $result = number_format($number, 3, '.', '');
                break;
            default:
                $result = number_format($number, $app_setup->general_decimal, '.', '');
                break;
        }
        return $result;
    }
    public static function currency_formattor($currency, $number, $format)
    {
        if (Auth::check()) {
            $app_setup = Auth::user()->app_setup;
        } else {
            $app_setup = ApplicationSetup::first();
        }
        $result = null;
        if ($currency) {
            switch ($format) {
                case 'price':
                    $result = number_format($number, $currency->unit_amount_decimal, $app_setup->decimalpoint, $app_setup->separator_symbol);
                    break;
                case 'cost':
                    $result = number_format($number, $currency->unit_amount_decimal, $app_setup->decimalpoint, $app_setup->separator_symbol);
                    break;
                case 'amount':
                    $result = number_format($number, $currency->amount_decimal, $app_setup->decimalpoint, $app_setup->separator_symbol);
                    break;
                case 'amount_lcy':
                    
                    $result = number_format($number, $app_setup->amount_decimal, $app_setup->decimalpoint, $app_setup->separator_symbol);
                    break;
                case 'decimal':
                    $result = number_format($number, 18, '.', '');
                    break;
                case 'vat_amount':
                    $result = number_format($number, 3, '.', '');
                    break;
            }
        } else {
            switch ($format) {
                case 'price':
                    $result = number_format($number, $app_setup->price_decimal, $app_setup->decimalpoint, $app_setup->separator_symbol);
                    break;
                case 'cost':
                    $result = number_format($number, $app_setup->cost_decimal, $app_setup->decimalpoint, $app_setup->separator_symbol);
                    break;
                case 'amount':
                    $result = number_format($number, $app_setup->amount_decimal, $app_setup->decimalpoint, $app_setup->separator_symbol);
                    break;
                case 'decimal':
                    $result = number_format($number, 18, '.', '');
                    break;
                case 'vat_amount':
                    $result = number_format($number, 3, '.', '');
                    break;
            }
        }
        if (strpos($result, $app_setup->decimalpoint) !== false && $app_setup->decimal_zero !== 'Yes') {
            return rtrim($result, '0');
        } else {
            return $result;
        }
    }
    public static function currency_formattor_link($currency, $number, $format, $is_print = '')
    {
        if ((float)$number == 0) return '-';
        if (Auth::check()) {
            $app_setup = Auth::user()->app_setup;
        } else {
            $app_setup = ApplicationSetup::first();
        }
        $separator_symbol = $app_setup->separator_symbol;
        if ($is_print == 'excel') {
            $separator_symbol = '';
        }
        $result = $number;
        if ($currency) {
            switch ($format) {
                case 'price':
                    $result = number_format($number, $currency->unit_amount_decimal, $app_setup->decimalpoint, $separator_symbol);
                    break;
                case 'cost':
                    $result = number_format($number, $currency->unit_amount_decimal, $app_setup->decimalpoint, $separator_symbol);
                    break;
                case 'amount':
                    $result = number_format($number, $currency->amount_decimal, $app_setup->decimalpoint, $separator_symbol);
                    break;
                case 'vat_amount':
                    $result = number_format($number, 3, '.', '');
                    break;
            }
        } else {
            switch ($format) {
                case 'price':
                    $result = number_format($number, $app_setup->price_decimal, $app_setup->decimalpoint, $separator_symbol);
                    break;
                case 'cost':
                    $result = number_format($number, $app_setup->cost_decimal, $app_setup->decimalpoint, $separator_symbol);
                    break;
                case 'amount':
                    $result = number_format($number, $app_setup->amount_decimal, $app_setup->decimalpoint, $separator_symbol);
                    break;
                case 'vat_amount':
                    $result = number_format($number, 3, '.', '');
                    break;
            }
        }
        if (strpos($result, $app_setup->decimalpoint) !== false && $app_setup->decimal_zero !== 'Yes') {
            return rtrim($result, '0');
        } else {
            return $result;
        }
    }
    public static function currency_formattor_database($currency, $number, $format)
    {
        if (Auth::check()) {
            $app_setup = Auth::user()->app_setup;
        } else {
            $app_setup = ApplicationSetup::first();
        }
        $separator_symbol = $app_setup->separator_symbol;
        if (!$number || $number == '') $number = 0;
        $number = (float) str_replace($app_setup->separator_symbol,'', $number);

        $result = $number;
        if ($currency) {
            switch ($format) {
                case 'price':
                    $result = number_format($number, $currency->unit_amount_decimal, '.', '');
                    break;
                case 'cost':
                    $result = number_format($number, $currency->unit_amount_decimal, '.', '');
                    break;
                case 'amount':
                    $result = number_format($number, $currency->amount_decimal, '.', '');
                    break;
                case 'amount_lcy':
                    $result = number_format($number, $app_setup->amount_decimal, '.', '');
                    break;
                case 'vat_amount':
                    $result = number_format($number, 3, '.', '');
                    break;
            }
            if (service::toDouble($currency->minimum_note) > 0) {
                $variant_amount = $number % service::toDouble($currency->minimum_note);
                if ($variant_amount > 0) $number = service::toDouble($number) - service::toDouble($variant_amount);
            }
        } else {
            switch ($format) {
                case 'price':
                    $result = number_format($number, $app_setup->price_decimal, '.', '');
                    break;
                case 'cost':
                    $result = number_format($number, $app_setup->cost_decimal, '.', '');
                    break;
                case 'amount':
                    $result = number_format($number, $app_setup->amount_decimal, '.', '');
                    break;
                case 'vat_amount':
                    $result = number_format($number, 3, '.', '');
                    break;
            }
        }
        return $result;
    }
    public static function date_human_readable($date, $short = true)
    {
        if (Carbon::parse($date)->toDateString() == '2500-01-01' || Carbon::parse($date)->toDateString() == '1900-01-01') {
            return '';
        }
        if (Carbon::parse($date)->toDateString() == Carbon::now()->toDateString()) {
            return 'Today';
        } elseif (Carbon::parse($date)->toDateString() == Carbon::now()->addDay(1)->toDateString()) {
            return 'Tomorrow';
        } elseif (Carbon::parse($date)->toDateString() == Carbon::now()->subDay(1)->toDateString()) {
            return 'Yesterday';
        } else {
            if (Carbon::parse($date)->year == Carbon::now()->year) {
                if ($short) {
                    return Carbon::parse($date)->format('d M');
                } else {
                    return Carbon::parse($date)->format('D d M');
                }
            } else {
                return Carbon::parse($date)->format('d M y');
            }
        }
    }

    // ========================= General Formattor =================================
    public static function currency_gformattor_database($currency, $number, $format)
    {

        $app_setup = DB::connection('mysql')->table('application_setup')->first();
        if (!$number || $number == '') $number = 0;
        $number = (float)str_replace($app_setup->separator_symbol, '', $number);
        $result = null;
        if ($currency) {
            switch ($format) {
                case 'price':
                    $result = number_format($number, $currency->unit_amount_decimal, '.', '');
                    break;
                case 'cost':
                    $result = number_format($number, $currency->unit_amount_decimal, '.', '');
                    break;
                case 'amount':
                    $result = number_format($number, $currency->amount_decimal, '.', '');
                    break;
                case 'vat_amount':
                    $result = number_format($number, 3, '.', '');
                    break;
            }
        }
        return $result;
    }
    public static function currency_gformattor($currency, $number, $format)
    {
        $app_setup = DB::connection('mysql')->table('application_setup')->first();
        //        $app_setup = Auth::user()->app_setup;
        $result = null;
        if ($currency) {
            switch ($format) {
                case 'price':
                    $result = number_format($number, $currency->unit_amount_decimal, $app_setup->decimalpoint, $app_setup->separator_symbol);
                    break;
                case 'cost':
                    $result = number_format($number, $currency->unit_amount_decimal, $app_setup->decimalpoint, $app_setup->separator_symbol);
                    break;
                case 'amount':
                    $result = number_format($number, $currency->amount_decimal, $app_setup->decimalpoint, $app_setup->separator_symbol);
                    break;
                case 'vat_amount':
                    $result = number_format($number, 3, '.', '');
                    break;
            }
        } else {
            switch ($format) {
                case 'price':
                    $result = number_format($number, $app_setup->price_decimal, $app_setup->decimalpoint, $app_setup->separator_symbol);
                    break;
                case 'cost':
                    $result = number_format($number, $app_setup->cost_decimal, $app_setup->decimalpoint, $app_setup->separator_symbol);
                    break;
                case 'amount':
                    $result = number_format($number, $app_setup->amount_decimal, $app_setup->decimalpoint, $app_setup->separator_symbol);
                    break;
                case 'vat_amount':
                    $result = number_format($number, 3, '.', '');
                    break;
            }
        }
        if (strpos($result, $app_setup->decimalpoint) !== false && $app_setup->decimal_zero !== 'Yes') {
            return rtrim($result, '0');
        } else {
            return $result;
        }
    }
    public function lcyExchangeAmount()
    {
        return 1;
    }
    public static function glcyExchangeAmount()
    {
        return 1;
    }
    public function getCurrencyFactor($currency_code, $date = null)
    {
        if (!$date || $date == '1900-01-01' || $date == '2500-01-01') $date = Carbon::now()->toDateString();

        $cur_factor = CurrencyExchangeRate::where('currency_code', trim($currency_code, ' '))
            ->where('starting_date', '<=', Carbon::parse($date)->toDateString())
            ->orderBy('starting_date', 'desc')
            ->first();
        if ($cur_factor) {
            return $cur_factor->exchange_rate;
        } else {
            return 1;
        }
    }
    public static function getCurrencyFactor2($currency_code, $date = null)
    {
        if (!$date || $date == '1900-01-01' || $date == '2500-01-01') $date = Carbon::now()->toDateString();

        $cur_factor = CurrencyExchangeRate::where('currency_code', trim($currency_code, ' '))
            ->where('starting_date', '<=', Carbon::parse($date)->toDateString())
            ->orderBy('starting_date', 'desc')
            ->first();
        if ($cur_factor) {
            return $cur_factor->exchange_rate;
        } else {
            return 1;
        }
    }
    public function getSublistFields($code, $user)
    {
        return PageGroupField::where('object_group_id', $code)->where('user_name', $user)
            ->where('object_type', 'sub_list')
            ->where('hide', 0)
            ->orderBy('index', 'asc')->get();
    }
    public function calcDate($date, $formula)
    {
        try{
            $result = $date;
            if (strtoupper($formula) == 'B') {
                return '01-01-1900';
            } elseif (strtoupper($formula) == 'C') {
                return '01-01-2500';
            } elseif (trim($formula, ' ') == '') {
                return $date;
            }

            if (strlen($formula) == 1 || strpos(strtoupper($formula), 'C') !== false || strpos(strtoupper($formula), 'T') !== false) {
                if (strtoupper($formula) == 'T') { // get current date
                    $result = $date;
                } elseif (strtoupper($formula) == 'TO' || strtoupper($formula) == 'TOMORROW') {
                    $result = Carbon::tomorrow()->toDateString();
                } elseif (strtoupper($formula) == 'CW') { // get last day of current week
                    $result = Carbon::parse($date)->endOfWeek();
                } elseif (strtoupper($formula) == 'CM') { // get last day of current month
                    $result = Carbon::parse($date)->endOfMonth();
                } elseif (strtoupper($formula) == 'CY') { // get last day of current year
                    $result = Carbon::parse($date)->endOfYear();
                }
            } else {
                preg_match_all('!\d+!', $formula, $numbers); // get number out from string
                
                if (strpos(strtoupper($formula), 'D') !== false) {
                    if ($this->getDateOperator($formula) == 'Sub') {
                        $result = Carbon::parse($date)->subDays($numbers[0][0]);
                    } else {
                        $result = Carbon::parse($date)->addDays($numbers[0][0]);
                    }
                } elseif (strpos(strtoupper($formula), 'W') !== false) {
                    if ($this->getDateOperator($formula) == 'Sub') {
                        $result = Carbon::parse($date)->subWeeks($numbers[0][0]);
                    } else {
                        $result = Carbon::parse($date)->addWeeks($numbers[0][0]);
                    }
                } elseif (strpos(strtoupper($formula), 'M') !== false) {
                    if ($this->getDateOperator($formula) == 'Sub') {
                        $result = Carbon::parse($date)->subMonths($numbers[0][0]);
                    } else {
                        $result = Carbon::parse($date)->addMonths($numbers[0][0]);
                    }
                } elseif (strpos(strtoupper($formula), 'Y') !== false) {
                    if ($this->getDateOperator($formula) == 'Sub') {
                        $result = Carbon::parse($date)->subYears($numbers[0][0]);
                    } else {
                        $result = Carbon::parse($date)->addYears($numbers[0][0]);
                    }
                }
                //date
                else {
                    if (strlen($formula) == 2) {
                        $result = Carbon::createFromDate(null, null, $formula);
                    } elseif (strlen($formula) == 4) {
                        $result = Carbon::createFromDate(null, substr($formula, 2, 4), substr($formula, 0, 2));
                    } elseif (strlen($formula) == 6) {
                        $result = Carbon::createFromDate('20' . substr($formula, 4, 2), substr($formula, 2, 2), substr($formula, 0, 2));
                    } else {
                        $result = Carbon::createFromDate(substr($formula, 4, 4), substr($formula, 2, 2), substr($formula, 0, 2));
                    }
                }
            }
            return $result;

        }catch(\Exception $ex) {
            $this->service->saveErrorLog($ex);
            return response()->json(['status' => 'error']);
        }
    }
    public static function _calcDate($date, $formula)
    {
        $result = $date;
        if (strtoupper($formula) == 'B') {
            return '01-01-1900';
        } elseif (strtoupper($formula) == 'C') {
            return '01-01-2500';
        } elseif (trim($formula, ' ') == '') {
            return $date;
        }
        if (strlen($formula) == 1 || strpos(strtoupper($formula), 'C') !== false || strpos(strtoupper($formula), 'T') !== false) {
            if (strtoupper($formula) == 'T') { // get current date
                $result = $date;
            } elseif (strtoupper($formula) == 'TO' || strtoupper($formula) == 'TOMORROW') {
                $result = Carbon::tomorrow()->toDateString();
            } elseif (strtoupper($formula) == 'CW') { // get last day of current week
                $result = Carbon::parse($date)->endOfWeek();
            } elseif (strtoupper($formula) == 'CM') { // get last day of current month
                $result = Carbon::parse($date)->endOfMonth();
            } elseif (strtoupper($formula) == 'CY') { // get last day of current year
                $result = Carbon::parse($date)->endOfYear();
            }
        } else {
            preg_match_all('!\d+!', $formula, $numbers); // get number out from string
            if (strpos(strtoupper($formula), 'D') !== false) {
                if (service::_getDateOperator($formula) == 'Sub') {
                    $result = Carbon::parse($date)->subDays($numbers[0][0]);
                } else {
                    $result = Carbon::parse($date)->addDays($numbers[0][0]);
                }
            } elseif (strpos(strtoupper($formula), 'W') !== false) {
                if (service::_getDateOperator($formula) == 'Sub') {
                    $result = Carbon::parse($date)->subWeeks($numbers[0][0]);
                } else {
                    $result = Carbon::parse($date)->addWeeks($numbers[0][0]);
                }
            } elseif (strpos(strtoupper($formula), 'M') !== false) {
                if (service::_getDateOperator($formula) == 'Sub') {
                    $result = Carbon::parse($date)->subMonths($numbers[0][0]);
                } else {
                    $result = Carbon::parse($date)->addMonths($numbers[0][0]);
                }
            } elseif (strpos(strtoupper($formula), 'Y') !== false) {
                if (service::_getDateOperator($formula) == 'Sub') {
                    $result = Carbon::parse($date)->subYears($numbers[0][0]);
                } else {
                    $result = Carbon::parse($date)->addYears($numbers[0][0]);
                }
            }
            //date
            else {
                if (strlen($formula) == 2) {
                    $result = Carbon::createFromDate(null, null, $formula);
                } elseif (strlen($formula) == 4) {
                    $result = Carbon::createFromDate(null, substr($formula, 2, 4), substr($formula, 0, 2));
                } elseif (strlen($formula) == 6) {
                    $result = Carbon::createFromDate('20' . substr($formula, 4, 2), substr($formula, 2, 2), substr($formula, 0, 2));
                } else {
                    $result = Carbon::createFromDate(substr($formula, 4, 4), substr($formula, 2, 2), substr($formula, 0, 2));
                }
            }
        }
        return $result;
    }
    public function getDateOperator($formula)
    {
        if (strpos($formula, '-') !== false) {
            return 'Sub';
        } else {
            return 'Add';
        }
    }
    public static function _getDateOperator($formula)
    {
        if (strpos($formula, '-') !== false) {
            return 'Sub';
        } else {
            return 'Add';
        }
    }
    function formatMoney($number, $fractional = false)
    {
        if ($fractional) {
            $number = sprintf('%.2f', $number);
        }
        while (true) {
            $replaced = preg_replace('/(-?\d+)(\d\d\d)/', '$1,$2', $number);
            if ($replaced != $number) {
                $number = $replaced;
            } else {
                break;
            }
        }
        return $number;
    }
    public static function explodeFlowField($str, $model, $type)
    {
        $fields = explode(".", $str);
        $fieldsSelected = '';
        foreach ($fields as $field) {
            $fieldsSelected = $fieldsSelected . $model->$field . $type;
        }
        $fieldsSelected = rtrim($fieldsSelected, $type);
        return $fieldsSelected;
    }
    public static function calcField($table, $tablename, $arr_fields)
    {
        if ($arr_fields) {
            $flowfields = TableFlowField::where('table_name', $tablename)->whereIn('field_name', $arr_fields)->with('page_group_field_flowfield')->get();
        } else {
            $flowfields = TableFlowField::where('table_name', $tablename)->with('page_group_field_flowfield')->get();
        }

        $flowfield_data = array();
        foreach ($flowfields as $flowfield) {
            $flowfield_filters = '';
            $criterias = array();
            $i = 0;
            $j = 0;
            foreach ($flowfield->page_group_field_flowfield as $page_group_flowfield) {
                $condition_value = $page_group_flowfield->condition_value;
                if (strtolower($page_group_flowfield->condition_type) == 'field') {
                    $a = array($page_group_flowfield->field_name, $page_group_flowfield->condition_operator, $table->$condition_value);
                } elseif (strtolower($page_group_flowfield->condition_type) == 'flowfield_filter') {
                    if ($j == 0) {
                    } else {
                    }
                    $j += 1;
                } else {
                    $a = array($page_group_flowfield->field_name, $page_group_flowfield->condition_operator, $condition_value);
                }
                $criterias[$i] = $a;
                $i = $i + 1;
            }

            $modal = $flowfield->path;
            if ($flowfield->flow_field_method == 'sum') {
                $result = $modal::where($criterias)->sum($flowfield->field_name);

                if ($flowfield->flow_field_reverse_sign == 'yes') {
                    $result = $result * -1;
                }
                if (strtolower($flowfield->field_data_type) == 'decimal') {
                    if (strtolower($flowfield->local_currency) == 'yes') {
                        $flowfield_data[$flowfield->field_name] = \App\Services\service::number_formattor($result, $flowfield->field_decimal_option);
                    } else {
                        if ($table->currency_code) {
                            $currency = Currency::select('amount_decimal', 'unit_amount_decimal')->where('code', $table->currency_code)->first();
                            $flowfield_data[$flowfield->field_name] = \App\Services\service::currency_formattor($currency, $result, $flowfield->field_decimal_option);
                        } else {
                            $flowfield_data[$flowfield->field_name] = \App\Services\service::number_formattor($result, $flowfield->field_decimal_option);
                        }
                    }
                } else {
                    $flowfield_data[$flowfield->field_name] = $result;
                }
            } elseif (strtolower($flowfield->flow_field_method) == 'count') {
                $result = $modal::where($criterias)->count();
                $flowfield_data[$flowfield->field_name] = $result;
            } elseif (strtolower($flowfield->flow_field_method) == 'min') {
                $result = $modal::where($criterias)->min($flowfield->field_name);
                $flowfield_data[$flowfield->field_name] = $result;
            } elseif (strtolower($flowfield->flow_field_method) == 'max') {
                $result = $modal::where($criterias)->max($flowfield->field_name);
                $flowfield_data[$flowfield->field_name] = $result;
            } elseif (strtolower($flowfield->flow_field_method) == 'text') {
                $flowfield_data[$flowfield->field_name] = \App\Services\service::explodeFlowField($flowfield->flow_field_field_name, $table, $flowfield->text_seperator);
            }
            $table->flow_field = $flowfield_data;
        }
        return $flowfield_data;
    }
    public static function calcChartOfAccount($table, $tablename, $lstFields, $arr_fields, $currency = null, $url_paras = '')
    {
        if ($arr_fields) {
            $flowfields = TableFlowField::where('table_name', $tablename)->whereIn('field_name', $arr_fields)->with('page_group_field_flowfield')->get();
        } else {
            $flowfields = TableFlowField::where('table_name', $tablename)->with('page_group_field_flowfield')->get();
        }
        foreach ($flowfields as $flowfield) {
            $criterias = array();
            $strcriterias = ' ';
            $i = 0;
            $j = 0;
            foreach ($flowfield->page_group_field_flowfield as $page_group_flowfield) {
                $condition_value = $page_group_flowfield->condition_value;
                if (strtolower($page_group_flowfield->condition_type) == 'field') {
                    $a = array($page_group_flowfield->field_name, $page_group_flowfield->condition_operator, $table->$condition_value);
                    $criterias[$i] = $a;
                } elseif (strtolower($page_group_flowfield->condition_type) == 'flowfield_filter') {
                    if ($j == 0) {
                        $strcriterias = $strcriterias . ' 1 = 1 ';
                    } else {
                        $strcriterias = $strcriterias . ' ';
                    }
                    $para_field = array("field_name"  => $page_group_flowfield->field_name, "field_data_type" => $page_group_flowfield->field_data_type);
                    $strcriterias = $strcriterias . \App\Services\service::getAdvanceSearchCriteriasfilter($lstFields, $para_field, $page_group_flowfield->condition_value);
                    $j += 1;
                } else {
                    $a = array($page_group_flowfield->field_name, $page_group_flowfield->condition_operator, $condition_value);
                    $criterias[$i] = $a;
                }
                $i = $i + 1;
            }
            if ($table->totaling_account_no) {
                $para_field = array("field_name"  => "account_no", "field_data_type" => "text");
                $strcriterias = $strcriterias . \App\Services\service::getAdvanceSearchCriteriasTotalAccount($lstFields, $para_field, $table->totaling_account_no);
            }
            $modal = $flowfield->path;
            if ($flowfield->flow_field_method == 'sum') {
                if ($table->totaling_account_no) {
                    $result = $modal::whereraw($strcriterias)->sum($flowfield->flow_field_field_name);
                } else {
                    $result = $modal::where($criterias)->whereraw($strcriterias)->sum($flowfield->flow_field_field_name);
                }
                if (strtolower($flowfield->flow_field_reverse_sign) == 'yes') {
                    $result = abs($result);
                }
                if (strtolower($flowfield->field_data_type) == 'decimal') {
                    if (strtolower($flowfield->local_currency) == 'yes') {
                        return \App\Services\service::number_formattor($result, $flowfield->field_decimal_option);
                    } else {
                        if ($currency) {
                            return \App\Services\service::currency_formattor($currency, $result, $flowfield->field_decimal_option);
                        } else {
                            return \App\Services\service::number_formattor($result, $flowfield->field_decimal_option);
                        }
                    }
                } else {
                    return $result;
                }
            } elseif (strtolower($flowfield->flow_field_method) == 'count') {
                $result = $modal::where($criterias)->count();
                return $result;
            } elseif (strtolower($flowfield->flow_field_method) == 'min') {
                $result = $modal::where($criterias)->min($flowfield->field_name);
                return $result;
            } elseif (strtolower($flowfield->flow_field_method) == 'max') {
                $result = $modal::where($criterias)->max($flowfield->field_name);
                return $result;
            } elseif (strtolower($flowfield->flow_field_method) == 'text') {
                return \App\Services\service::explodeFlowField($flowfield->flow_field_field_name, $table, $flowfield->text_seperator);
            }
        }
    }
    public static function getSpecialCondictionFilter($field, $value)
    {
        $criterias = '';
        $criterias .= $criterias . \App\Services\service::getSpecialConditionRecurringFilter($field, $value);
        return $criterias;
    }
    public static function getSpecialConditionValueFilter($field, $value)
    {
        $criterias = '';
        if ($field['field_data_type'] == 'decimal') {
            return is_numeric($value) ? $value : -999999999999999999999;
        } elseif ($field['field_data_type'] == 'date') {
            //Special date value
            try {
                if (trim($value, ' ') == '') {
                    return date_format(Carbon::createFromDate(1900, 01, 01), 'Y-m-d');
                }
                if (strtoupper($value) == 'T' || $value == 'TODAY') {
                    return Carbon::today()->toDateString();
                } elseif (strtoupper($value) == 'TO' || $value == 'TOMORROW') {
                    return Carbon::tomorrow()->toDateString();
                } elseif (strtoupper($value) == 'Y' || $value == 'YESTERDAY' || $value == 'YES') {
                    return Carbon::yesterday()->toDateString();
                } elseif (strtoupper($value) == 'CM') {
                    return Carbon::now()->endOfMonth()->toDateString();
                } elseif (strtoupper($value) == 'CW') {
                    $dt = Carbon::now()->endOfWeek();
                    if ($dt > Carbon::now()->endOfMonth()) {
                        $dt = Carbon::now()->endOfMonth();
                    }
                    return $dt->toDateString();
                } elseif (strtoupper($value) == 'SW') {
                    $dt = Carbon::now()->startOfWeek();
                    if ($dt < Carbon::now()->startOfMonth()) {
                        $dt =  Carbon::now()->startOfMonth();
                    }
                    return $dt->toDateString();
                } elseif (strtoupper($value) == 'SCW') {
                    $dt = Carbon::now()->endOfWeek();
                    return $dt->toDateString();
                } elseif (strtoupper($value) == 'SSW') {
                    $dt = Carbon::now()->startOfWeek();
                    return $dt->toDateString();
                } elseif (strtoupper($value) == 'CY') {
                    return date_format(Carbon::createFromDate(null, 12, 31), 'Y-m-d');
                } else {
                    $calc_date_formula = service::calcDateFormula($value);
                    if ($calc_date_formula != '1900-01-01') return $calc_date_formula;
                    if (strpos($value, '/') !== false) {
                        $dateparts = explode('/', $value);
                        if (strlen($dateparts[0]) == 4) {
                            return date_format(Carbon::createFromDate(isset($dateparts[0]) ? $dateparts[0] : null, isset($dateparts[1]) ? $dateparts[1] : null, isset($dateparts[2]) ? $dateparts[2] : null), 'Y-m-d');
                        } else {
                            return date_format(Carbon::createFromDate(isset($dateparts[2]) ? $dateparts[2] : null, isset($dateparts[1]) ? $dateparts[1] : null, isset($dateparts[0]) ? $dateparts[0] : null), 'Y-m-d');
                        }
                    } elseif (strpos($value, '-') !== false) {
                        $dateparts = explode('-', $value);
                        if (strlen($dateparts[0]) == 4) {
                            return date_format(Carbon::createFromDate(isset($dateparts[0]) ? $dateparts[0] : null, isset($dateparts[1]) ? $dateparts[1] : null, isset($dateparts[2]) ? $dateparts[2] : null), 'Y-m-d');
                        } else {
                            return date_format(Carbon::createFromDate(isset($dateparts[2]) ? $dateparts[2] : null, isset($dateparts[1]) ? $dateparts[1] : null, isset($dateparts[0]) ? $dateparts[0] : null), 'Y-m-d');
                        }
                    } else {
                        if (strlen($value) == 2) {
                            return date_format(Carbon::createFromDate(null, null, $value), 'Y-m-d');
                        } elseif (strlen($value) == 4) {
                            return date_format(Carbon::createFromDate(null, substr($value, 2, 4), substr($value, 0, 2)), 'Y-m-d');
                        } elseif (strlen($value) == 6) {
                            return date_format(Carbon::createFromDate('20' . substr($value, 4, 2), substr($value, 2, 2), substr($value, 0, 2)), 'Y-m-d');
                        } else {
                            return date_format(Carbon::createFromDate(substr($value, 4, 4), substr($value, 2, 2), substr($value, 0, 2)), 'Y-m-d');
                        }
                    }
                }
            } catch (\Exception $ex) {
                return date_format(Carbon::createFromDate(1900, 01, 01), 'Y-m-d');
            }
        } else {
            return mb_strtoupper($value, 'UTF-8');
        }
    }
    public static function getSpecialConditionRecurringFilter($field, $value)
    {
        $strUpper = '';
        $criterias = '';
        if ($field['field_data_type'] == 'decimal') {
        } elseif ($field['field_data_type'] == 'date') {
        } else {
            $strUpper = 'UPPER';
        }
        if (strpos($value, '|') !== false) {
            $andConditions = explode('|', $value);
            $i = 0;
            foreach ($andConditions as $conditionValue) {
                if ($i == 0) {
                    $criterias .= '(' . \App\Services\service::getSpecialConditionRecurringFilter($field, $conditionValue) . '';
                } else {
                    $criterias .= ' OR ' . \App\Services\service::getSpecialConditionRecurringFilter($field, $conditionValue);
                }
                $i += 1;
            }
            $criterias .= ')';
        } else {
            if (strpos($value, '..') !== false) {
                $andConditions = explode('..', $value);
                $criterias .= " (";
                $criterias = $criterias . ' ' . $strUpper . '(' . $field['field_name'] . ')' . '>=' . "'" . \App\Services\service::getSpecialConditionValueFilter($field, $andConditions[0]) . "' ";
                $criterias = $criterias . ' AND ' . $strUpper . '(' . $field['field_name'] . ')' . '<=' . "'" . \App\Services\service::getSpecialConditionValueFilter($field, $andConditions[1]) . "' ";
                $criterias .= " ) ";
            } else {
                if (strpos($value, '>=') !== false) {
                    $criterias .= " ( ";
                    $criterias = $criterias . ' ' . $strUpper . '(' . $field['field_name'] . ')' . '>=' . "'" . \App\Services\service::getSpecialConditionValueFilter($field, str_replace('>=', '', $value)) . "'";
                    $criterias .= " ) ";
                } elseif (strpos($value, '<=') !== false) {
                    $criterias .= " ( ";
                    $criterias = $criterias . ' ' . $strUpper . '(' . $field['field_name'] . ')' . '<=' . "'" . \App\Services\service::getSpecialConditionValueFilter($field, str_replace('<=', '', $value)) . "'";
                    $criterias .= " ) ";
                } elseif (strpos($value, '<>') !== false) {
                    $criterias .= " ( ";
                    $criterias = $criterias . ' ' . $strUpper . '(' . $field['field_name'] . ')' . '<>' . "'" . \App\Services\service::getSpecialConditionValueFilter($field, str_replace('<>', '', $value)) . "'";
                    $criterias .= " ) ";
                } elseif (strpos($value, '>') !== false) {
                    $criterias .= " ( ";
                    $criterias = $criterias . ' ' . $strUpper . '(' . $field['field_name'] . ')' . '>' . "'" . \App\Services\service::getSpecialConditionValueFilter($field, str_replace('>', '', $value)) . "'";
                    $criterias .= " ) ";
                } elseif (strpos($value, '<') !== false) {
                    $criterias .= " ( ";
                    $criterias = $criterias . ' ' . $strUpper . '(' . $field['field_name'] . ')' . '<' . "'" . \App\Services\service::getSpecialConditionValueFilter($field, str_replace('<', '', $value)) . "'";
                    $criterias .= " ) ";
                } else {
                    $criterias .= " ( ";
                    $criterias = $criterias . ' ' . $strUpper . '(' . $field['field_name'] . ')' . '=' . "'" . \App\Services\service::getSpecialConditionValueFilter($field, $value) . "'";
                    $criterias .= " ) ";
                }
            }
        }
        return $criterias;
    }
    public static function getAdvanceSearchCriteriasfilter($lstFields, $para_field, $fieldname)
    {
        $criterias = '';
        $field = \App\Services\service::getFieldFromListFields($lstFields, $fieldname);
        if ($field) {
            $value = mb_strtoupper($field->flow_filter_value, 'UTF-8');
            if ($value != '') {
                $criterias .= ' AND ' . \App\Services\service::getSpecialCondictionFilter($para_field, $value);
            }
        }
        return $criterias;
    }
    public static function getAdvanceSearchCriteriasTotalAccount($lstFields, $para_field, $value)
    {
        $criterias = '';
        $criterias .= ' AND ' . \App\Services\service::getSpecialCondictionFilter($para_field, $value);
        return $criterias;
    }
    public static function getFieldFromListFields($lstFields, $fieldName)
    {
        foreach ($lstFields as $field) {
            if ($field->field_name == $fieldName) return $field;
        }
        return null;
    }
    public static function getUrlField($record, $field, $type = '')
    {
        if (!$field) return '';
        $urls = TableFieldUrl::where('table_field_id', $field->field_id)->get();
        if (count($urls) == 0) {
            return "";
        }
        foreach ($urls as $url) {
            if ($url->field_name && $url->field_name != '') {
                $name = $url->field_name;
                if ($record->$name == $url->condition_value) {
                    return $url->url;
                }
            } else {
                return $url->url;
            }
        }
    }
    public static function getUrlParameter($record, $field, $type = '')
    {
        $result = '?';
        if ($field->input_type == 'flowfield') {
            $url_paras = TableFieldFlowFieldParas::where('field_id', $field->field_id)->get();
        } else {
            $url_paras = TableFieldUrlParas::where('table_field_id', $field->field_id)->get();
        }

        foreach ($url_paras as $url_para) {
            $condition_value = trim($url_para->condition_value);
            if (strtolower($url_para->condition_type) == 'field') {
                if (isset($record->$condition_value)) {
                    if ($url_para->condition_value_encrypt == 'Yes') {
                        $result = $result . '&' . $url_para->parameter_name . '=' . urlencode(\App\Services\service::encrypt(trim($record->$condition_value)));
                    } else {
                        $result = $result . '&' . $url_para->parameter_name . '=' . urlencode($record->$condition_value);
                    }
                }
            } else {
                if ($url_para->condition_value_encrypt == 'Yes') {
                    $result = $result . '&' . $url_para->parameter_name . '=' . urlencode(\App\Services\service::encrypt(trim($condition_value)));
                } else {
                    $result = $result . '&' . $url_para->parameter_name . '=' . urlencode($condition_value);
                }
            }
        }
        if ($type) $result = $result . '&' . '&type=' . $type;
        return $result;
    }
    public function updateItemAverageCost($item, $location_code, $inv_posting_setup, $posting_date, $app_setup)
    {
        try {
            if ($item) {
                $beginning_quantity = 0;
                $beginning_amount = 0;
                $increase_quantity = 0;
                $increase_amount = 0;
                // Calcuate current posting date average
                if ($app_setup->inventory_cost_setting == 'Item') {
                    $avg_cost_overview_current = VAvgCostOverview::where('closing_date', Carbon::parse($posting_date)
                        ->toDateString())
                        ->where('item_no', $item->no)
                        ->first();
                } else {
                    $avg_cost_overview_current = VAvgCostOverviewLocation::where('closing_date', Carbon::parse($posting_date)
                        ->toDateString())
                        ->where('item_no', $item->no)
                        ->where('location_code', $location_code)
                        ->first();
                }

                if ($app_setup->inventory_cost_setting == 'Item') {
                    $item_avg_cost = ItemAverageCost::where('item_no', $item->no)
                        ->where('closing_date', Carbon::parse($posting_date)->toDateString())->first();
                } else {
                    $item_avg_cost = ItemAverageCost::where('location_code', $location_code)
                        ->where('item_no', $item->no)
                        ->where('closing_date', Carbon::parse($posting_date)->toDateString())->first();
                }
                if ($avg_cost_overview_current) {
                    if (service::toDouble($avg_cost_overview_current->beginning_qty) > 0) {
                        $avg_cost = (service::toDouble($avg_cost_overview_current->beginning_amount) + service::toDouble($avg_cost_overview_current->increase_amount)) / (service::toDouble($avg_cost_overview_current->beginning_qty) + service::toDouble($avg_cost_overview_current->increase_qty));
                        $beginning_quantity = service::toDouble($avg_cost_overview_current->beginning_qty);
                        $beginning_amount = service::toDouble($avg_cost_overview_current->beginning_amount);
                        $increase_quantity = service::toDouble($avg_cost_overview_current->increase_qty);
                        $increase_amount = service::toDouble($avg_cost_overview_current->increase_amount);
                    } else {
                        if (service::toDouble($avg_cost_overview_current->increase_qty) > 0) {
                            $avg_cost = service::toDouble($avg_cost_overview_current->increase_amount) / service::toDouble($avg_cost_overview_current->increase_qty);
                            $beginning_quantity = 0;
                            $beginning_amount = 0;
                            $increase_quantity = service::toDouble($avg_cost_overview_current->increase_qty);
                            $increase_amount = service::toDouble($avg_cost_overview_current->increase_amount);
                        } else {
                            $avg_cost = 0;
                            $beginning_quantity = 0;
                            $beginning_amount = 0;
                            $increase_quantity = 0;
                            $increase_amount = 0;
                        }
                    }
                } else {
                    $avg_cost = 0;
                    $beginning_quantity = 0;
                    $beginning_amount = 0;
                    $increase_quantity = 0;
                    $increase_amount = 0;
                }
                if ($item_avg_cost) {
                    $item_avg_cost->average_cost = $avg_cost;
                    $item_avg_cost->beginning_quantity = $beginning_quantity;
                    $item_avg_cost->beginning_amount = $beginning_amount;
                    $item_avg_cost->increase_quantity = $increase_quantity;
                    $item_avg_cost->increase_amount = $increase_amount;
                    $item_avg_cost->adjustment = 'Yes';
                    $item_avg_cost->save();
                } else {
                    $item_avg_cost = new ItemAverageCost();
                    if ($app_setup->inventory_cost_setting == 'Item & Location') {
                        $item_avg_cost->location_code = $location_code;
                    }
                    $item_avg_cost->item_no = $item->no;
                    $item_avg_cost->closing_date = $posting_date;
                    $item_avg_cost->average_cost = $avg_cost;
                    $item_avg_cost->beginning_quantity = $beginning_quantity;
                    $item_avg_cost->beginning_amount = $beginning_amount;
                    $item_avg_cost->increase_quantity = $increase_quantity;
                    $item_avg_cost->increase_amount = $increase_amount;
                    $item_avg_cost->adjustment = 'Yes';
                    $item_avg_cost->save();
                }
                // Update item ledger entry cost amount
                if ($item->costing_method == 'Average' && $item->item_tracking_code != 'LOTALL' && $item->item_tracking_code != 'SNALL') {
                    if ($app_setup->inventory_cost_setting == 'Item') {
                        $item_avg_cost_to_update = ItemAverageCost::select('closing_date')->where('item_no', $item->no)
                            ->where('closing_date', '>', Carbon::parse($item_avg_cost->closing_date)->toDateString())
                            ->orderBy('closing_date', 'asc')->first();
                        if ($item_avg_cost_to_update) {
                            $to_closing_date = $item_avg_cost_to_update->closing_date;
                        } else {
                            $to_closing_date = '2500-01-01';
                        }
                        $item_ledger_entries = ItemLedgerEntry::view()
                            ->where('item_no', $item->no)
                            ->where('cost_per_unit', '<>', $this->toDouble($item_avg_cost->average_cost))
                            ->where('posting_date', '>=', Carbon::parse($item_avg_cost->closing_date)->toDateString())
                            ->where('posting_date', '<', Carbon::parse($to_closing_date)->toDateString())
                            ->whereIn('document_type', ['Sales Shipment', 'Negative Adj.', 'Purchase Shipment'])->get();
                    } else {
                        $item_avg_cost_to_update = ItemAverageCost::select('closing_date')->where('item_no', $item->no)
                            ->where('location_code', $location_code)
                            ->where('closing_date', '>', Carbon::parse($item_avg_cost->closing_date)->toDateString())
                            ->orderBy('closing_date', 'asc')->first();
                        if ($item_avg_cost_to_update) {
                            $to_closing_date = $item_avg_cost_to_update->closing_date;
                        } else {
                            $to_closing_date = '2500-01-01';
                        }
                        $item_ledger_entries = ItemLedgerEntry::view()->where('cost_per_unit', '<>', $avg_cost)
                            ->where('item_no', $item->no)
                            ->where('posting_date', '>=', Carbon::parse($item_avg_cost->closing_date)->toDateString())
                            ->where('posting_date', '<', Carbon::parse($to_closing_date)->toDateString())
                            ->where('location_code', $location_code)
                            ->whereIn('document_type', ['Sales Shipment', 'Negative Adj.', 'Purchase Shipment'])->get();
                    }
                    foreach ($item_ledger_entries as $item_ledger_entry) {
                        // calculate difference cost
                        $direct_unit_cost_lcy_unit = $this->toDouble($item_avg_cost->average_cost) - $this->toDouble($item_ledger_entry->cost_per_unit);
                        // adjustment cost item value value entry
                        $item_value_entry_existing = ItemValueEntry::view()->where('item_ledger_entry_no', $item_ledger_entry->entry_no)->orderBy('entry_no', 'asc')->first();
                        if ($item_value_entry_existing) {
                            $item_value_entry = new ItemValueEntry();
                            $item_value_entry->item_no = $item_value_entry_existing->item_no;
                            $item_value_entry->item_charge_no = '';
                            $item_value_entry->adjustment = 'Yes';
                            $item_value_entry->entry_type = 'Direct Cost';
                            $item_value_entry->source_type = $item_value_entry_existing->source_type;
                            $item_value_entry->source_no = $item_value_entry_existing->source_no;
                            $item_value_entry->document_type = $item_value_entry_existing->document_type;
                            $item_value_entry->document_no = $item_value_entry_existing->document_no;
                            $item_value_entry->item_ledger_entry_type = $item_value_entry_existing->item_ledger_entry_type;
                            $item_value_entry->item_ledger_entry_no = $item_value_entry_existing->item_ledger_entry_no;
                            $item_value_entry->item_ledger_entry_quantity = $this->toDouble($item_value_entry_existing->item_ledger_entry_quantity);
                            $item_value_entry->currency_code = $item_value_entry_existing->currency_code;
                            $item_value_entry->currency_factor = $item_value_entry_existing->currency_factor;
                            $item_value_entry->invoiced_quantity = $this->toDouble($item_value_entry_existing->item_ledger_entry_quantity);
                            $item_value_entry->purchase_amount = 0;
                            $item_value_entry->sales_amount = 0;
                            $item_value_entry->discount_amount = 0;
                            $item_value_entry->cost_amount = $this->toDouble(abs($item_value_entry_existing->item_ledger_entry_quantity)) * $direct_unit_cost_lcy_unit * -1;
                            $item_value_entry->cost_per_unit = $direct_unit_cost_lcy_unit;
                            $item_value_entry->description = $item_value_entry_existing->description;
                            $item_value_entry->description_2 = $item_value_entry_existing->description_2;
                            $item_value_entry->location_code = $item_value_entry_existing->location_code;
                            $item_value_entry->source_posting_group = $item_value_entry_existing->source_posting_group;
                            $item_value_entry->gen_bus_posting_group = $item_value_entry_existing->gen_bus_posting_group;
                            $item_value_entry->gen_prod_posting_group = $item_value_entry_existing->gen_prod_posting_group;
                            $item_value_entry->inventory_posting_group = $item_value_entry_existing->inventory_posting_group;
                            $item_value_entry->document_date = $item_value_entry_existing->document_date;
                            $item_value_entry->posting_date = $item_value_entry_existing->posting_date;
                            $item_value_entry->order_no = $item_value_entry_existing->order_no;
                            $item_value_entry->order_line_no = $item_value_entry_existing->order_line_no;
                            $item_value_entry->order_type = $item_value_entry_existing->order_type;
                            $item_value_entry->external_document_no = $item_value_entry_existing->external_document_no;
                            $item_value_entry->document_line_no = $item_value_entry_existing->document_line_no;
                            $item_value_entry->journal_batch_code = '';
                            $item_value_entry->return_reason_code = $item_value_entry_existing->return_reason_code;
                            $item_value_entry->reason_code = '';
                            $item_value_entry->valuation_date = $item_value_entry_existing->valuation_date;
                            $item_value_entry->variance_type = '';
                            $item_value_entry->production_type = '';
                            $item_value_entry->production_no = '';
                            $item_value_entry->item_category_code = $item_value_entry_existing->item_category_code;
                            $item_value_entry->item_group_code = $item_value_entry_existing->item_group_code;
                            $item_value_entry->item_disc_group_code = $item_value_entry_existing->item_disc_group_code;
                            $item_value_entry->item_brand_code = $item_value_entry_existing->item_brand_code;
                            $item_value_entry->store_code = $item_value_entry_existing->store_code;
                            $item_value_entry->division_code = $item_value_entry_existing->division_code;
                            $item_value_entry->business_unit_code = $item_value_entry_existing->business_unit_code;
                            $item_value_entry->department_code = $item_value_entry_existing->department_code;
                            $item_value_entry->project_code = $item_value_entry_existing->project_code;
                            $item_value_entry->sales_purchaser_code = $item_value_entry_existing->sales_purchaser_code;
                            $item_value_entry->save();
                            // adjustment cost general ledger entry
                            if ($inv_posting_setup->inventory_setting == 'Perpetual') {
                                // adjustment general ledger entry for inventory account
                                $gl_entry_inven = new GeneralLedgerEntry();
                                $chart_of_account = ChartOfAccount::where('no', $inv_posting_setup->inventory_account_no)->first();
                                if ($chart_of_account) {
                                    $gl_entry_inven->account_name = $chart_of_account->description;
                                    $gl_entry_inven->account_no = $chart_of_account->no;
                                } else {
                                    return 'Inventory account no in inventory posting setup must a value.';
                                }
                                $gl_entry_inven->document_date = $item_value_entry->document_date;
                                $gl_entry_inven->posting_date = $item_value_entry->posting_date;
                                $gl_entry_inven->document_type = $item_value_entry->document_type;
                                $gl_entry_inven->document_no = $item_value_entry->document_no;
                                $gl_entry_inven->description = $item_value_entry->description . ' (Adjustment)';
                                $gl_entry_inven->amount = $this->toDouble($item_value_entry->cost_amount);
                                $gl_entry_inven->bal_account_type = null;
                                $gl_entry_inven->bal_account_no = null;
                                $gl_entry_inven->bal_account_name = null;
                                $gl_entry_inven->journal_batch_name = null;
                                $gl_entry_inven->reason_code = null;
                                if ($this->toDouble($gl_entry_inven->amount) > 0) {
                                    $gl_entry_inven->debit_amount = $this->toDouble($item_value_entry->cost_amount);
                                    $gl_entry_inven->credit_amount = 0;
                                } else {
                                    $gl_entry_inven->debit_amount = 0;
                                    $gl_entry_inven->credit_amount = $this->toDouble($item_value_entry->cost_amount) * (-1);
                                }

                                $gl_entry_inven->external_document_no = $item_value_entry->external_document_no;
                                $gl_entry_inven->source_type = $item_value_entry->source_type;
                                $gl_entry_inven->source_no = $item_value_entry->source_no;
                                $gl_entry_inven->gen_bus_posting_group = $item_value_entry->gen_bus_posting_group;
                                $gl_entry_inven->gen_prod_posting_group = $item_value_entry->gen_prod_posting_group;
                                $gl_entry_inven->vat_bus_posting_group = '';
                                $gl_entry_inven->vat_prod_posting_group = '';
                                $gl_entry_inven->reversed = 'No';
                                $gl_entry_inven->reversed_by_entry_no = 0;
                                $gl_entry_inven->reversed_entry_no = 0;
                                $gl_entry_inven->adjustment = 'No';
                                $gl_entry_inven->item_category_code = $item_value_entry->item_category_code;
                                $gl_entry_inven->item_group_code = $item_value_entry->item_group_code;
                                $gl_entry_inven->item_brand_code = $item_value_entry->item_brand_code;
                                $gl_entry_inven->store_code = $item_value_entry->store_code;
                                $gl_entry_inven->division_code = $item_value_entry->division_code;
                                $gl_entry_inven->business_unit_code = $item_value_entry->business_unit_code;
                                $gl_entry_inven->department_code = $item_value_entry->department_code;
                                $gl_entry_inven->project_code = $item_value_entry->project_code;
                                $gl_entry_inven->sales_purchaser_code = $item_value_entry->salesperson_code;
                                $gl_entry_inven->system_created_entry = 'No';
                                $gl_entry_inven->created_by = Auth::user()->email;
                                $gl_entry_inven->save();
                                // adjustment general ledger entry for cost of good sold & adjustent
                                $gl_entry_cogs = new GeneralLedgerEntry();
                                if ($item_ledger_entry->document_type == 'Sales Shipment') {
                                    //==================== Sales Revenue & COGS =================
                                    $gen_posting_setup = GeneralPostingSetup::where('gen_bus_posting_group', $item_value_entry->gen_bus_posting_group)
                                        ->where('gen_prod_posting_group', $item_value_entry->gen_prod_posting_group)->first();
                                    if ($gen_posting_setup) {
                                        $chart_of_account = ChartOfAccount::where('no', $gen_posting_setup->cogs_account)->first();
                                        if ($chart_of_account) {
                                            $gl_entry_cogs->account_name = $chart_of_account->description;
                                            $gl_entry_cogs->account_no = $chart_of_account->no;
                                        } else {
                                            return 'Cost of good sold account no in general posting setup must a value.';
                                        }
                                    } else {
                                        return 'general posting setup not found';
                                    }
                                } elseif ($item_ledger_entry->document_type == 'Purchase Shipment') {
                                    //==================== Sales Revenue & COGS =================
                                    $gen_posting_setup = GeneralPostingSetup::where('gen_bus_posting_group', $item_value_entry->gen_bus_posting_group)
                                        ->where('gen_prod_posting_group', $item_value_entry->gen_prod_posting_group)->first();
                                    if ($gen_posting_setup) {
                                        $chart_of_account = ChartOfAccount::where('no', $gen_posting_setup->inventory_adj_account)->first();
                                        if ($chart_of_account) {
                                            $gl_entry_cogs->account_name = $chart_of_account->description;
                                            $gl_entry_cogs->account_no = $chart_of_account->no;
                                        } else {
                                            return 'Adjustment account no in general posting setup must a value.';
                                        }
                                    } else {
                                        return 'general posting setup not found';
                                    }
                                } else {
                                    //==================== Sales Revenue & COK)GS =================
                                    $chart_of_account = ChartOfAccount::where('no', $inv_posting_setup->negative_adj_account_no)->first();
                                    if ($chart_of_account) {
                                        $gl_entry_cogs->account_name = $chart_of_account->description;
                                        $gl_entry_cogs->account_no = $chart_of_account->no;
                                    } else {
                                        return 'Negative adjustment account no in inventory posting setup must a value.';
                                    }
                                }
                                $gl_entry_cogs->document_date = $item_value_entry->document_date;
                                $gl_entry_cogs->posting_date = $item_value_entry->posting_date;
                                $gl_entry_cogs->document_type = $item_value_entry->document_type;
                                $gl_entry_cogs->document_no = $item_value_entry->document_no;
                                $gl_entry_cogs->description = $item_value_entry->description . ' (Adjustment)';
                                $gl_entry_cogs->amount = $this->toDouble($item_value_entry->cost_amount) * (-1);
                                $gl_entry_cogs->bal_account_type = null;
                                $gl_entry_cogs->bal_account_no = null;
                                $gl_entry_cogs->bal_account_name = null;
                                $gl_entry_cogs->journal_batch_name = null;
                                $gl_entry_cogs->reason_code = null;
                                if ($this->toDouble($gl_entry_cogs->amount) > 0) {
                                    $gl_entry_cogs->debit_amount = $this->toDouble($item_value_entry->cost_amount) * -1;
                                    $gl_entry_cogs->credit_amount = 0;
                                } else {
                                    $gl_entry_cogs->debit_amount = 0;
                                    $gl_entry_cogs->credit_amount = $this->toDouble($item_value_entry->cost_amount);
                                }

                                $gl_entry_cogs->external_document_no = $item_value_entry->external_document_no;
                                $gl_entry_cogs->source_type = $item_value_entry->source_type;
                                $gl_entry_cogs->source_no = $item_value_entry->source_no;
                                $gl_entry_cogs->gen_bus_posting_group = $item_value_entry->gen_bus_posting_group;
                                $gl_entry_cogs->gen_prod_posting_group = $item_value_entry->gen_prod_posting_group;
                                $gl_entry_cogs->vat_bus_posting_group = '';
                                $gl_entry_cogs->vat_prod_posting_group = '';
                                $gl_entry_cogs->reversed = 'No';
                                $gl_entry_cogs->reversed_by_entry_no = 0;
                                $gl_entry_cogs->reversed_entry_no = 0;
                                $gl_entry_cogs->adjustment = 'No';
                                $gl_entry_cogs->item_category_code = $item_value_entry->item_category_code;
                                $gl_entry_cogs->item_group_code = $item_value_entry->item_group_code;
                                $gl_entry_cogs->item_brand_code = $item_value_entry->item_brand_code;
                                $gl_entry_cogs->store_code = $item_value_entry->store_code;
                                $gl_entry_cogs->division_code = $item_value_entry->division_code;
                                $gl_entry_cogs->business_unit_code = $item_value_entry->business_unit_code;
                                $gl_entry_cogs->department_code = $item_value_entry->department_code;
                                $gl_entry_cogs->project_code = $item_value_entry->project_code;
                                $gl_entry_cogs->sales_purchaser_code = $item_value_entry->salesperson_code;
                                $gl_entry_cogs->system_created_entry = 'No';
                                $gl_entry_cogs->created_by = Auth::user()->email;
                                $gl_entry_cogs->save();
                            }
                        }
                    }
                }

                // Recalculation next average
                if ($app_setup->inventory_cost_setting == 'Item') {
                    $avg_cost_overview_nexts = VAvgCostOverview::where('closing_date', '>', Carbon::parse($item_avg_cost->closing_date)->toDateString())
                        ->where('item_no', $item->no)->orderBy('closing_date', 'asc')->get();
                } else {
                    $avg_cost_overview_nexts = VAvgCostOverviewLocation::where('closing_date', '>', Carbon::parse($item_avg_cost->closing_date)->toDateString())
                        ->where('item_no', $item->no)->where('location_code', $location_code)->orderBy('closing_date', 'asc')->get();
                }
                $last_avg_cost = $avg_cost;
                if (count($avg_cost_overview_nexts) > 0) {
                    foreach ($avg_cost_overview_nexts as $avg_cost_overview_next) {

                        if ($app_setup->inventory_cost_setting == 'Item') {
                            $item_avg_cost_next = ItemAverageCost::where('item_no', $item->no)
                                ->where('closing_date', Carbon::parse($avg_cost_overview_next->closing_date)->toDateString())->first();
                        } else {
                            $item_avg_cost_next = ItemAverageCost::where('location_code', $location_code)->where('item_no', $item->no)
                                ->where('closing_date', Carbon::parse($avg_cost_overview_next->closing_date)->toDateString())->first();
                        }
                        if (service::toDouble($avg_cost_overview_next->beginning_qty) <= 0) {
                            if (service::toDouble($avg_cost_overview_next->increase_qty) > 0) {
                                $avg_cost_next = service::toDouble($avg_cost_overview_next->increase_amount) / service::toDouble($avg_cost_overview_next->increase_qty);
                                $beginning_quantity = 0;
                                $beginning_amount = 0;
                                $increase_quantity = service::toDouble($avg_cost_overview_next->increase_qty);
                                $increase_amount = service::toDouble($avg_cost_overview_next->increase_amount);
                            } else {
                                $avg_cost_next = 0;
                                $beginning_quantity = 0;
                                $beginning_amount = 0;
                                $increase_quantity = service::toDouble($avg_cost_overview_next->increase_qty);
                                $increase_amount = service::toDouble($avg_cost_overview_next->increase_amount);
                            }
                        } else {
                            $avg_cost_next = (service::toDouble($avg_cost_overview_next->beginning_amount) + service::toDouble($avg_cost_overview_next->increase_amount))
                                / (service::toDouble($avg_cost_overview_next->beginning_qty) + service::toDouble($avg_cost_overview_next->increase_qty));
                            $beginning_quantity = service::toDouble($avg_cost_overview_next->beginning_qty);
                            $beginning_amount = service::toDouble($avg_cost_overview_next->beginning_amount);
                            $increase_quantity = service::toDouble($avg_cost_overview_next->increase_qty);
                            $increase_amount = service::toDouble($avg_cost_overview_next->increase_amount);
                        }
                        $last_avg_cost = $avg_cost_next;
                        if ($item_avg_cost_next) {
                            $item_avg_cost_next->average_cost = $avg_cost_next;
                            $item_avg_cost_next->beginning_quantity = $beginning_quantity;
                            $item_avg_cost_next->beginning_amount = $beginning_amount;
                            $item_avg_cost_next->increase_quantity = $increase_quantity;
                            $item_avg_cost_next->increase_amount = $increase_amount;
                            $item_avg_cost_next->adjustment = 'Yes';
                            $item_avg_cost_next->save();

                            if ($item->costing_method == 'Average' && $item->item_tracking_code != 'LOTALL' && $item->item_tracking_code != 'SNALL') {
                                if ($app_setup->inventory_cost_setting == 'Item') {
                                    $item_avg_cost_to_update = ItemAverageCost::select('closing_date')->where('item_no', $item->no)
                                        ->where('closing_date', '>', Carbon::parse($item_avg_cost_next->closing_date)->toDateString())
                                        ->orderBy('closing_date', 'asc')->first();
                                    if ($item_avg_cost_to_update) {
                                        $to_closing_date = $item_avg_cost_to_update->closing_date;
                                    } else {
                                        $to_closing_date = '2500-01-01';
                                    }
                                    $item_ledger_entries = ItemLedgerEntry::view()
                                        ->where('item_no', $item->no)
                                        ->where('cost_per_unit', '<>', $this->toDouble($item_avg_cost_next->average_cost))
                                        ->where('posting_date', '>=', Carbon::parse($item_avg_cost_next->closing_date)->toDateString())
                                        ->where('posting_date', '<', Carbon::parse($to_closing_date)->toDateString())
                                        ->whereIn('document_type', ['Sales Shipment', 'Negative Adj.', 'Purchase Shipment'])->get();
                                } else {
                                    $item_avg_cost_to_update = ItemAverageCost::select('closing_date')->where('item_no', $item->no)
                                        ->where('location_code', $location_code)
                                        ->where('closing_date', '>', Carbon::parse($item_avg_cost_next->closing_date)->toDateString())
                                        ->orderBy('closing_date', 'asc')->first();
                                    if ($item_avg_cost_to_update) {
                                        $to_closing_date = $item_avg_cost_to_update->closing_date;
                                    } else {
                                        $to_closing_date = '2500-01-01';
                                    }
                                    $item_ledger_entries = ItemLedgerEntry::view()->where('cost_per_unit', '<>', $avg_cost)
                                        ->where('item_no', $item->no)
                                        ->where('posting_date', '>=', Carbon::parse($item_avg_cost_next->closing_date)->toDateString())
                                        ->where('posting_date', '<', Carbon::parse($to_closing_date)->toDateString())
                                        ->where('location_code', $location_code)
                                        ->whereIn('document_type', ['Sales Shipment', 'Negative Adj.', 'Purchase Shipment'])->get();
                                }
                                foreach ($item_ledger_entries as $item_ledger_entry) {
                                    // calculate difference cost
                                    $direct_unit_cost_lcy_unit = $this->toDouble($item_avg_cost_next->average_cost) - $this->toDouble($item_ledger_entry->cost_per_unit);
                                    // adjustment cost item value value entry
                                    $item_value_entry_existing = ItemValueEntry::view()->where('item_ledger_entry_no', $item_ledger_entry->entry_no)->orderBy('entry_no', 'asc')->first();
                                    if ($item_value_entry_existing) {
                                        $item_value_entry = new ItemValueEntry();
                                        $item_value_entry->item_no = $item_value_entry_existing->item_no;
                                        $item_value_entry->item_charge_no = '';
                                        $item_value_entry->adjustment = 'Yes';
                                        $item_value_entry->entry_type = 'Direct Cost';
                                        $item_value_entry->source_type = $item_value_entry_existing->source_type;
                                        $item_value_entry->source_no = $item_value_entry_existing->source_no;
                                        $item_value_entry->document_type = $item_value_entry_existing->document_type;
                                        $item_value_entry->document_no = $item_value_entry_existing->document_no;
                                        $item_value_entry->item_ledger_entry_type = $item_value_entry_existing->item_ledger_entry_type;
                                        $item_value_entry->item_ledger_entry_no = $item_value_entry_existing->item_ledger_entry_no;
                                        $item_value_entry->item_ledger_entry_quantity = $this->toDouble($item_value_entry_existing->item_ledger_entry_quantity);
                                        $item_value_entry->currency_code = $item_value_entry_existing->currency_code;
                                        $item_value_entry->currency_factor = $item_value_entry_existing->currency_factor;
                                        $item_value_entry->invoiced_quantity = $this->toDouble($item_value_entry_existing->item_ledger_entry_quantity);
                                        $item_value_entry->purchase_amount = 0;
                                        $item_value_entry->sales_amount = 0;
                                        $item_value_entry->discount_amount = 0;
                                        $item_value_entry->cost_amount = $this->toDouble(abs($item_value_entry_existing->item_ledger_entry_quantity)) * $direct_unit_cost_lcy_unit * -1;
                                        $item_value_entry->cost_per_unit = $direct_unit_cost_lcy_unit;
                                        $item_value_entry->description = $item_value_entry_existing->description;
                                        $item_value_entry->description_2 = $item_value_entry_existing->description_2;
                                        $item_value_entry->location_code = $item_value_entry_existing->location_code;
                                        $item_value_entry->source_posting_group = $item_value_entry_existing->source_posting_group;
                                        $item_value_entry->gen_bus_posting_group = $item_value_entry_existing->gen_bus_posting_group;
                                        $item_value_entry->gen_prod_posting_group = $item_value_entry_existing->gen_prod_posting_group;
                                        $item_value_entry->inventory_posting_group = $item_value_entry_existing->inventory_posting_group;
                                        $item_value_entry->document_date = $item_value_entry_existing->document_date;
                                        $item_value_entry->posting_date = $item_value_entry_existing->posting_date;
                                        $item_value_entry->order_no = $item_value_entry_existing->order_no;
                                        $item_value_entry->order_line_no = $item_value_entry_existing->order_line_no;
                                        $item_value_entry->order_type = $item_value_entry_existing->order_type;
                                        $item_value_entry->external_document_no = $item_value_entry_existing->external_document_no;
                                        $item_value_entry->document_line_no = $item_value_entry_existing->document_line_no;
                                        $item_value_entry->journal_batch_code = '';
                                        $item_value_entry->return_reason_code = $item_value_entry_existing->return_reason_code;
                                        $item_value_entry->reason_code = '';
                                        $item_value_entry->valuation_date = $item_value_entry_existing->valuation_date;
                                        $item_value_entry->variance_type = '';
                                        $item_value_entry->production_type = '';
                                        $item_value_entry->production_no = '';
                                        $item_value_entry->item_category_code = $item_value_entry_existing->item_category_code;
                                        $item_value_entry->item_group_code = $item_value_entry_existing->item_group_code;
                                        $item_value_entry->item_disc_group_code = $item_value_entry_existing->item_disc_group_code;
                                        $item_value_entry->item_brand_code = $item_value_entry_existing->item_brand_code;
                                        $item_value_entry->store_code = $item_value_entry_existing->store_code;
                                        $item_value_entry->division_code = $item_value_entry_existing->division_code;
                                        $item_value_entry->business_unit_code = $item_value_entry_existing->business_unit_code;
                                        $item_value_entry->department_code = $item_value_entry_existing->department_code;
                                        $item_value_entry->project_code = $item_value_entry_existing->project_code;
                                        $item_value_entry->sales_purchaser_code = $item_value_entry_existing->sales_purchaser_code;
                                        $item_value_entry->save();
                                        // adjustment general ledger entry for inventory account
                                        $gl_entry_inven = new GeneralLedgerEntry();
                                        $chart_of_account = ChartOfAccount::where('no', $inv_posting_setup->inventory_account_no)->first();
                                        if ($chart_of_account) {
                                            $gl_entry_inven->account_name = $chart_of_account->description;
                                            $gl_entry_inven->account_no = $chart_of_account->no;
                                        } else {
                                            return 'Inventory account no in inventory posting setup must a value.';
                                        }
                                        $gl_entry_inven->document_date = $item_value_entry->document_date;
                                        $gl_entry_inven->posting_date = $item_value_entry->posting_date;
                                        $gl_entry_inven->document_type = $item_value_entry->document_type;
                                        $gl_entry_inven->document_no = $item_value_entry->document_no;
                                        $gl_entry_inven->description = $item_value_entry->description . ' (Adjustment)';
                                        $gl_entry_inven->amount = $this->toDouble($item_value_entry->cost_amount);
                                        $gl_entry_inven->bal_account_type = null;
                                        $gl_entry_inven->bal_account_no = null;
                                        $gl_entry_inven->bal_account_name = null;
                                        $gl_entry_inven->journal_batch_name = null;
                                        $gl_entry_inven->reason_code = null;
                                        if ($this->toDouble($gl_entry_inven->amount) > 0) {
                                            $gl_entry_inven->debit_amount = $this->toDouble($item_value_entry->cost_amount);
                                            $gl_entry_inven->credit_amount = 0;
                                        } else {
                                            $gl_entry_inven->debit_amount = 0;
                                            $gl_entry_inven->credit_amount = $this->toDouble($item_value_entry->cost_amount) * (-1);
                                        }

                                        $gl_entry_inven->external_document_no = $item_value_entry->external_document_no;
                                        $gl_entry_inven->source_type = $item_value_entry->source_type;
                                        $gl_entry_inven->source_no = $item_value_entry->source_no;
                                        $gl_entry_inven->gen_bus_posting_group = $item_value_entry->gen_bus_posting_group;
                                        $gl_entry_inven->gen_prod_posting_group = $item_value_entry->gen_prod_posting_group;
                                        $gl_entry_inven->vat_bus_posting_group = '';
                                        $gl_entry_inven->vat_prod_posting_group = '';
                                        $gl_entry_inven->reversed = 'No';
                                        $gl_entry_inven->reversed_by_entry_no = 0;
                                        $gl_entry_inven->reversed_entry_no = 0;
                                        $gl_entry_inven->adjustment = 'No';
                                        $gl_entry_inven->item_category_code = $item_value_entry->item_category_code;
                                        $gl_entry_inven->item_group_code = $item_value_entry->item_group_code;
                                        $gl_entry_inven->item_brand_code = $item_value_entry->item_brand_code;
                                        $gl_entry_inven->store_code = $item_value_entry->store_code;
                                        $gl_entry_inven->division_code = $item_value_entry->division_code;
                                        $gl_entry_inven->business_unit_code = $item_value_entry->business_unit_code;
                                        $gl_entry_inven->department_code = $item_value_entry->department_code;
                                        $gl_entry_inven->project_code = $item_value_entry->project_code;
                                        $gl_entry_inven->sales_purchaser_code = $item_value_entry->salesperson_code;
                                        $gl_entry_inven->system_created_entry = 'No';
                                        $gl_entry_inven->created_by = Auth::user()->email;
                                        $gl_entry_inven->save();
                                        // adjustment general ledger entry for cost of good sold & adjustent
                                        $gl_entry_cogs = new GeneralLedgerEntry();
                                        if ($item_ledger_entry->document_type == 'Sales Shipment') {
                                            //==================== Sales Revenue & COGS =================
                                            $gen_posting_setup = GeneralPostingSetup::where('gen_bus_posting_group', $item_value_entry->gen_bus_posting_group)
                                                ->where('gen_prod_posting_group', $item_value_entry->gen_prod_posting_group)->first();
                                            $chart_of_account = ChartOfAccount::where('no', $gen_posting_setup->cogs_account)->first();
                                            if ($chart_of_account) {
                                                $gl_entry_cogs->account_name = $chart_of_account->description;
                                                $gl_entry_cogs->account_no = $chart_of_account->no;
                                            } else {
                                                return 'Cost of good sold account no in general posting setup must a value.';
                                            }
                                        } elseif ($item_ledger_entry->document_type == 'Purchase Shipment') {
                                            //==================== Sales Revenue & COGS =================
                                            $gen_posting_setup = GeneralPostingSetup::where('gen_bus_posting_group', $item_value_entry->gen_bus_posting_group)
                                                ->where('gen_prod_posting_group', $item_value_entry->gen_prod_posting_group)->first();
                                            $chart_of_account = ChartOfAccount::where('no', $gen_posting_setup->inventory_adj_account)->first();
                                            if ($chart_of_account) {
                                                $gl_entry_cogs->account_name = $chart_of_account->description;
                                                $gl_entry_cogs->account_no = $chart_of_account->no;
                                            } else {
                                                return 'Adjustment account no in general posting setup must a value.';
                                            }
                                        } else {
                                            //==================== Sales Revenue & COGS =================
                                            $chart_of_account = ChartOfAccount::where('no', $inv_posting_setup->negative_adj_account_no)->first();
                                            if ($chart_of_account) {
                                                $gl_entry_cogs->account_name = $chart_of_account->description;
                                                $gl_entry_cogs->account_no = $chart_of_account->no;
                                            } else {
                                                return 'Negative adjustment account no in inventory posting setup must a value.';
                                            }
                                        }
                                        $gl_entry_cogs->document_date = $item_value_entry->document_date;
                                        $gl_entry_cogs->posting_date = $item_value_entry->posting_date;
                                        $gl_entry_cogs->document_type = $item_value_entry->document_type;
                                        $gl_entry_cogs->document_no = $item_value_entry->document_no;
                                        $gl_entry_cogs->description = $item_value_entry->description . ' (Adjustment)';
                                        $gl_entry_cogs->amount = $this->toDouble($item_value_entry->cost_amount) * -1;
                                        $gl_entry_cogs->bal_account_type = null;
                                        $gl_entry_cogs->bal_account_no = null;
                                        $gl_entry_cogs->bal_account_name = null;
                                        $gl_entry_cogs->journal_batch_name = null;
                                        $gl_entry_cogs->reason_code = null;
                                        if ($this->toDouble($gl_entry_cogs->amount) > 0) {
                                            $gl_entry_cogs->debit_amount = $this->toDouble($item_value_entry->cost_amount) * -1;
                                            $gl_entry_cogs->credit_amount = 0;
                                        } else {
                                            $gl_entry_cogs->debit_amount = 0;
                                            $gl_entry_cogs->credit_amount = $this->toDouble($item_value_entry->cost_amount);
                                        }

                                        $gl_entry_cogs->external_document_no = $item_value_entry->external_document_no;
                                        $gl_entry_cogs->source_type = $item_value_entry->source_type;
                                        $gl_entry_cogs->source_no = $item_value_entry->source_no;
                                        $gl_entry_cogs->gen_bus_posting_group = $item_value_entry->gen_bus_posting_group;
                                        $gl_entry_cogs->gen_prod_posting_group = $item_value_entry->gen_prod_posting_group;
                                        $gl_entry_cogs->vat_bus_posting_group = '';
                                        $gl_entry_cogs->vat_prod_posting_group = '';
                                        $gl_entry_cogs->reversed = 'No';
                                        $gl_entry_cogs->reversed_by_entry_no = 0;
                                        $gl_entry_cogs->reversed_entry_no = 0;
                                        $gl_entry_cogs->adjustment = 'No';
                                        $gl_entry_cogs->item_category_code = $item_value_entry->item_category_code;
                                        $gl_entry_cogs->item_group_code = $item_value_entry->item_group_code;
                                        $gl_entry_cogs->item_brand_code = $item_value_entry->item_brand_code;
                                        $gl_entry_cogs->store_code = $item_value_entry->store_code;
                                        $gl_entry_cogs->division_code = $item_value_entry->division_code;
                                        $gl_entry_cogs->business_unit_code = $item_value_entry->business_unit_code;
                                        $gl_entry_cogs->department_code = $item_value_entry->department_code;
                                        $gl_entry_cogs->project_code = $item_value_entry->project_code;
                                        $gl_entry_cogs->sales_purchaser_code = $item_value_entry->salesperson_code;
                                        $gl_entry_cogs->system_created_entry = 'No';
                                        $gl_entry_cogs->created_by = Auth::user()->email;
                                        $gl_entry_cogs->save();
                                    }
                                }
                            }
                        }
                    }
                }
                // Update cost of gold sold sales transaction

                // Update item adjustment cost yes
                $item->unit_cost = $last_avg_cost;
                $item->is_adjustment_cost = 'Yes';
                $item->save();
            }
            return 'success';
        } catch (\Exception $ex) {
            return $ex->getLine() . ' ' . $ex->getMessage();
        }
    }
    // ========================================================================
    // Function Name : Advance Filter           ===============================
    // Create By     : Blue Tenology            ===============================
    // Create Date   : 03/08/2017               ===============================
    // ========================================================================
    public function getSpecialCondiction($field, $value)
    {
        $criterias = '';
        $criterias .= $criterias . $this->getSpecialConditionRecurring($field, $value);
        return $criterias;
    }
    static public function _getSpecialCondiction($field, $value)
    {
        $criterias = '';
        $criterias .= $criterias . (new self)->getSpecialConditionRecurring($field, $value);
        return $criterias;
    }
    public function getSpecialCondictionJoin($field, $value, $alias)
    {
        $criterias = '';
        $criterias .= $criterias . $this->getSpecialConditionRecurringJoin($field, $value, $alias);
        return $criterias;
    }
    public function getSpecialConditionValue($field, $value)
    {
        
        $criterias = '';
        if ($field->field_data_type == 'decimal') {
            return is_numeric($value) ? $value : -999999999999999999999;
        } 
        elseif ($field->field_data_type == 'date' || $field->field_data_type === 'timestamp' || $field->field_data_type == 'datetime') {
            //Special date value
            try {
                if (trim($value, ' ') == '') {
                    return date_format(Carbon::createFromDate(1900, 01, 01), 'Y-m-d');
                }
                $result = "";
                if (strtoupper($value) == 'T' || strtoupper($value) == 'TODAY') {
                    return Carbon::today()->toDateString();
                } elseif (strtoupper($value) == 'TO' || strtoupper($value) == 'TOMORROW') {
                    return Carbon::tomorrow()->toDateString();
                } elseif (strtoupper($value) == 'Y' || strtoupper($value) == 'YESTERDAY' || strtoupper($value) == 'YES') {
                    return Carbon::yesterday()->toDateString();
                } elseif (strtoupper($value) == 'CM') {
                    return Carbon::now()->endOfMonth()->toDateString();
                } elseif (strtoupper($value) == 'CW') {
                    $dt = Carbon::now()->endOfWeek();
                    if ($dt > Carbon::now()->endOfMonth()) {
                        $dt = Carbon::now()->endOfMonth();
                    }
                    
                    return $dt->toDateString();
                } elseif (strtoupper($value) == 'SW') {
                    $dt = Carbon::now()->startOfWeek();
                    if ($dt < Carbon::now()->startOfMonth()) {
                        $dt =  Carbon::now()->startOfMonth();
                    }
                    return $dt->toDateString();
                } elseif (strtoupper($value) == 'SCW') {
                    $dt = Carbon::now()->endOfWeek();
                    return $dt->toDateString();
                } elseif (strtoupper($value) == 'SSW') {
                    $dt = Carbon::now()->startOfWeek();
                    return $dt->toDateString();
                } elseif (strtoupper($value) == 'CY') {
                    return date_format(Carbon::createFromDate(null, 12, 31), 'Y-m-d');
                } else {
                    $calc_date_formula = service::calcDateFormula($value);
                    
                    if ($calc_date_formula != '1900-01-01') return $calc_date_formula;

                    if (strpos($value, '/') !== false) {
                        $dateparts = explode('/', $value);
                        if (strlen($dateparts[0]) == 4) {
                            $year = isset($dateparts[0]) ? $dateparts[0] : null;
                            if ($year) {
                                if (strlen($year) == 2) $year = '20' . $year;
                            }
                            $month = isset($dateparts[1]) ? $dateparts[1] : null;
                            if ($month) {
                                if (strlen($month) > 2) $month = $this->convertMonthname2Number($month);
                            }
                            return date_format(Carbon::createFromDate($year, $month, isset($dateparts[2]) ? $dateparts[2] : null), 'Y-m-d');
                        } else {
                            $year = isset($dateparts[2]) ? $dateparts[2] : null;
                            if ($year) {
                                if (strlen($year) == 2) $year = '20' . $year;
                            }
                            $month = isset($dateparts[1]) ? $dateparts[1] : null;
                            if ($month) {
                                if (strlen($month) > 2) $month = $this->convertMonthname2Number($month);
                            }
                            return date_format(Carbon::createFromDate($year, $month, isset($dateparts[0]) ? $dateparts[0] : null), 'Y-m-d');
                        }
                    } elseif (strpos($value, '-') !== false) {
                        $dateparts = explode('-', $value);
                        if (strlen($dateparts[0]) == 4) {
                            $year = isset($dateparts[0]) ? $dateparts[0] : null;
                            if ($year) {
                                if (strlen($year) == 2) $year = '20' . $year;
                            }
                            $month = isset($dateparts[1]) ? $dateparts[1] : null;
                            if ($month) {
                                if (strlen($month) > 2) $month = $this->convertMonthname2Number($month);
                            }
                            return date_format(Carbon::createFromDate($year, $month, isset($dateparts[2]) ? $dateparts[2] : null), 'Y-m-d');
                        } else {
                            $year = isset($dateparts[2]) ? $dateparts[2] : null;
                            if ($year) {
                                if (strlen($year) == 2) $year = '20' . $year;
                            }
                            $month = isset($dateparts[1]) ? $dateparts[1] : null;
                            if ($month) {
                                if (strlen($month) > 2) $month = $this->convertMonthname2Number($month);
                            }
                            return date_format(Carbon::createFromDate($year, $month, isset($dateparts[0]) ? $dateparts[0] : null), 'Y-m-d');
                        }
                    } else {
                        if (strlen($value) == 2) {
                            return date_format(Carbon::createFromDate(null, null, $value), 'Y-m-d');
                        } elseif (strlen($value) == 4) {
                            return date_format(Carbon::createFromDate(null, substr($value, 2, 4), substr($value, 0, 2)), 'Y-m-d');
                        } elseif (strlen($value) == 6) {
                            return date_format(Carbon::createFromDate('20' . substr($value, 4, 2), substr($value, 2, 2), substr($value, 0, 2)), 'Y-m-d');
                        } else {
                            $result = date_format(Carbon::createFromDate(substr($value, 4, 4), substr($value, 2, 2), substr($value, 0, 2)), 'Y-m-d');
                        }
                    }
                    if($field->field_data_type == 'datetime'){
                        $time =$this->getSpecialCondictionTime($field, $value);
                        \Log::info($result. " ". $time);
                    }
                     return $result;
                }
            } catch (\Exception $ex) {
                return date_format(Carbon::createFromDate(2500, 01, 01), 'Y-m-d');
            }
        } elseif ($field->field_data_type == 'time') {
            $result = $this->timeformat($value);
            if ($result == 'false') {
                return '00:00';
            } else {
                return $this->timeformat($value);
            }
        } else {
            return mb_strtoupper(trim($value, ' '), 'UTF-8');
        }
    }
    public static function getSpecialCondictionTime($field, $value) : string
    {
        //Special date value
        try {

            
            return "00:20:00";
        }catch (\Exception $ex) {
            return "";
        }
    }
    public static function getDateConverter($value)
    {
        //Special date value
        try {
            if (trim($value, ' ') == '') {
                return date_format(Carbon::createFromDate(1900, 01, 01), 'Y-m-d');
            }
            if (strtoupper($value) == 'T' || strtoupper($value) == 'TODAY') {
                return Carbon::today()->toDateString();
            } elseif (strtoupper($value) == 'TO' || strtoupper($value) == 'TOMORROW') {
                return Carbon::tomorrow()->toDateString();
            } elseif (strtoupper($value) == 'Y' || strtoupper($value) == 'YESTERDAY' || strtoupper($value) == 'YES') {
                return Carbon::yesterday()->toDateString();
            } elseif (strtoupper($value) == 'CM') {
                return Carbon::now()->endOfMonth()->toDateString();
            } elseif (strtoupper($value) == 'CW') {
                $dt = Carbon::now()->endOfWeek();
                if ($dt > Carbon::now()->endOfMonth()) {
                    $dt = Carbon::now()->endOfMonth();
                }
                return $dt->toDateString();
            } elseif (strtoupper($value) == 'SW') {
                $dt = Carbon::now()->startOfWeek();
                if ($dt < Carbon::now()->startOfMonth()) {
                    $dt =  Carbon::now()->startOfMonth();
                }
                return $dt->toDateString();
            } elseif (strtoupper($value) == 'SCW') {
                $dt = Carbon::now()->endOfWeek();
                return $dt->toDateString();
            } elseif (strtoupper($value) == 'SSW') {
                $dt = Carbon::now()->startOfWeek();
                return $dt->toDateString();
            } elseif (strtoupper($value) == 'CY') {
                return date_format(Carbon::createFromDate(null, 12, 31), 'Y-m-d');
            } else {
                $calc_date_formula = service::calcDateFormula($value);
                if ($calc_date_formula != '1900-01-01') return $calc_date_formula;

                if (strpos($value, '/') !== false) {
                    $dateparts = explode('/', $value);
                    if (strlen($dateparts[0]) == 4) {
                        $year = isset($dateparts[0]) ? $dateparts[0] : null;
                        if ($year) {
                            if (strlen($year) == 2) $year = '20' . $year;
                        }
                        $month = isset($dateparts[1]) ? $dateparts[1] : null;
                        if ($month) {
                            if (strlen($month) > 2) $month = service::_convertMonthname2Number($month);
                        }
                        return date_format(Carbon::createFromDate($year, $month, isset($dateparts[2]) ? $dateparts[2] : null), 'Y-m-d');
                    } else {
                        $year = isset($dateparts[2]) ? $dateparts[2] : null;
                        if ($year) {
                            if (strlen($year) == 2) $year = '20' . $year;
                        }
                        $month = isset($dateparts[1]) ? $dateparts[1] : null;
                        if ($month) {
                            if (strlen($month) > 2) $month = service::_convertMonthname2Number($month);
                        }
                        return date_format(Carbon::createFromDate($year, $month, isset($dateparts[0]) ? $dateparts[0] : null), 'Y-m-d');
                    }
                } elseif (strpos($value, '-') !== false) {
                    $dateparts = explode('-', $value);
                    if (strlen($dateparts[0]) == 4) {
                        $year = isset($dateparts[0]) ? $dateparts[0] : null;
                        if ($year) {
                            if (strlen($year) == 2) $year = '20' . $year;
                        }
                        $month = isset($dateparts[1]) ? $dateparts[1] : null;
                        if ($month) {
                            if (strlen($month) > 2) $month = service::_convertMonthname2Number($month);
                        }
                        return date_format(Carbon::createFromDate($year, $month, isset($dateparts[2]) ? $dateparts[2] : null), 'Y-m-d');
                    } else {
                        $year = isset($dateparts[2]) ? $dateparts[2] : null;
                        if ($year) {
                            if (strlen($year) == 2) $year = '20' . $year;
                        }
                        $month = isset($dateparts[1]) ? $dateparts[1] : null;
                        if ($month) {
                            if (strlen($month) > 2) $month = service::_convertMonthname2Number($month);
                        }
                        return date_format(Carbon::createFromDate($year, $month, isset($dateparts[0]) ? $dateparts[0] : null), 'Y-m-d');
                    }
                } else {
                    if (strlen($value) == 2) {
                        return date_format(Carbon::createFromDate(null, null, $value), 'Y-m-d');
                    } elseif (strlen($value) == 4) {
                        return date_format(Carbon::createFromDate(null, substr($value, 2, 4), substr($value, 0, 2)), 'Y-m-d');
                    } elseif (strlen($value) == 6) {
                        return date_format(Carbon::createFromDate('20' . substr($value, 4, 2), substr($value, 2, 2), substr($value, 0, 2)), 'Y-m-d');
                    } else {
                        return date_format(Carbon::createFromDate(substr($value, 4, 4), substr($value, 2, 2), substr($value, 0, 2)), 'Y-m-d');
                    }
                }
            }
        } catch (\Exception $ex) {
            return date_format(Carbon::createFromDate(2500, 01, 01), 'Y-m-d');
        }
    }
    public function getSpecialConditionRecurring($field, $value)
    {
        $strUpper = '';
        $criterias = '';
        if ($field->field_data_type == 'decimal') {
        } elseif ($field->field_data_type == 'date') {
        } else {
            $strUpper = 'UPPER';
        }

        if (strpos($value, '|') !== false) {
            $andConditions = explode('|', $value);
            $i = 0;
            foreach ($andConditions as $conditionValue) {
                if ($i == 0) {
                    $criterias .= '(' . $this->getSpecialConditionRecurring($field, $conditionValue) . '';
                } else {
                    $criterias .= ' OR ' . $this->getSpecialConditionRecurring($field, $conditionValue);
                }
                $i += 1;
            }
            $criterias .= ')';
        } else {

            if (strpos($value, '..') !== false) {
                $andConditions = explode('..', $value);
                $criterias .= " (";
                $criterias = $criterias . ' ' . $strUpper . '(' . $field->field_name . ')' . '>=' . "'" . $this->getSpecialConditionValue($field, $andConditions[0]) . "' ";
                $criterias = $criterias . ' AND ' . $strUpper . '(' . $field->field_name . ')' . '<=' . "'" . $this->getSpecialConditionValue($field, $andConditions[1]) . "' ";
                $criterias .= " ) ";
            } else {
                if (strpos($value, '>=') !== false) {
                    $criterias .= " ( ";
                    $criterias = $criterias . ' ' . $strUpper . '(' . $field->field_name . ')' . '>=' . "'" . $this->getSpecialConditionValue($field, str_replace('>=', '', $value)) . "'";
                    $criterias .= " ) ";
                } elseif (strpos($value, '<=') !== false) {
                    $criterias .= " ( ";
                    $criterias = $criterias . ' ' . $strUpper . '(' . $field->field_name . ')' . '<=' . "'" . $this->getSpecialConditionValue($field, str_replace('<=', '', $value)) . "'";
                    $criterias .= " ) ";
                } elseif (strpos($value, '<>') !== false) {
                    $criterias .= " ( ";
                    $criterias = $criterias . ' ' . $strUpper . '(' . $field->field_name . ')' . '<>' . "'" . $this->getSpecialConditionValue($field, str_replace('<>', '', $value)) . "'";
                    $criterias .= " ) ";
                } elseif (strpos($value, '>') !== false) {
                    $criterias .= " ( ";
                    $criterias = $criterias . ' ' . $strUpper . '(' . $field->field_name . ')' . '>' . "'" . $this->getSpecialConditionValue($field, str_replace('>', '', $value)) . "'";
                    $criterias .= " ) ";
                } elseif (strpos($value, '<') !== false) {
                    $criterias .= " ( ";
                    $criterias = $criterias . ' ' . $strUpper . '(' . $field->field_name . ')' . '<' . "'" . $this->getSpecialConditionValue($field, str_replace('<', '', $value)) . "'";
                    $criterias .= " ) ";
                } else {
                    if ($field->field_data_type != 'text') {
                        $criterias .= " ( ";
                        $criterias = $criterias . ' ' . $strUpper . '(' . $field->field_name . ')' . '=' . "'" . $this->getSpecialConditionValue($field, $value) . "'";
                        $criterias .= " ) ";
                    } else {
                        if (strpos($value, '*') !== false) {
                            $criterias .= " ( ";
                            $criterias = $criterias . ' ' . $strUpper . '(' . $field->field_name . ') like' . " '" . str_replace('*', '%', $value) . "'";
                            $criterias .= " ) ";
                        } else {
                            if ($value == "!" || strtolower($value) == "null") {
                                $criterias .= " ( ";
                                $criterias = $criterias . ' ' . $strUpper . '(' . $field->field_name . ')' . "<>'' or " . ' ' . $field->field_name . ' is not null';
                                $criterias .= " ) ";
                            } elseif ($value == "''") {
                                $criterias .= " ( ";
                                $criterias = $criterias . ' ' . $strUpper . '(' . $field->field_name . ')' . "='' or " . ' ' . $field->field_name . ' is null';
                                $criterias .= " ) ";
                            } else {
                                $criterias .= " ( ";
                                $criterias = $criterias . ' ' . $strUpper . '(' . $field->field_name . ')' . '=' . "'" . $this->getSpecialConditionValue($field, $value) . "'";
                                $criterias .= " ) ";
                            }
                        }
                    }
                }
            }
        }
        return $criterias;
    }
    public function getSpecialConditionRecurringJoin($field, $value, $alias)
    {
        $strUpper = '';
        $criterias = '';
        if ($field->field_data_type == 'decimal') {
        } elseif ($field->field_data_type == 'date') {
        } else {
            $strUpper = 'UPPER';
        }
        if (strpos($value, '|') !== false) {
            $andConditions = explode('|', $value);
            $i = 0;
            foreach ($andConditions as $conditionValue) {
                if ($i == 0) {
                    $criterias .= '(' . $this->getSpecialConditionRecurringJoin($field, $conditionValue, $alias) . '';
                } else {
                    $criterias .= ' OR ' . $this->getSpecialConditionRecurringJoin($field, $conditionValue, $alias);
                }
                $i += 1;
            }
            $criterias .= ')';
        } else {
            if (strpos($value, '..') !== false) {
                $andConditions = explode('..', $value);
                $criterias .= " (";
                $criterias = $criterias . ' ' . $strUpper . '(' . $alias . '.' . $field->field_name . ')' . '>=' . "'" . $this->getSpecialConditionValue($field, $andConditions[0]) . "' ";
                $criterias = $criterias . ' AND ' . $strUpper . '(' . $alias . '.' . $field->field_name . ')' . '<=' . "'" . $this->getSpecialConditionValue($field, $andConditions[1]) . "' ";
                $criterias .= " ) ";
            } else {
                if (strpos($value, '>=') !== false) {
                    $criterias .= " ( ";
                    $criterias = $criterias . ' ' . $strUpper . '(' . $alias . '.' . $field->field_name . ')' . '>=' . "'" . $this->getSpecialConditionValue($field, str_replace('>=', '', $value)) . "'";
                    $criterias .= " ) ";
                } elseif (strpos($value, '<=') !== false) {
                    $criterias .= " ( ";
                    $criterias = $criterias . ' ' . $strUpper . '(' . $alias . '.' . $field->field_name . ')' . '<=' . "'" . $this->getSpecialConditionValue($field, str_replace('<=', '', $value)) . "'";
                    $criterias .= " ) ";
                } elseif (strpos($value, '<>') !== false) {
                    $criterias .= " ( ";
                    $criterias = $criterias . ' ' . $strUpper . '(' . $alias . '.' . $field->field_name . ')' . '<>' . "'" . $this->getSpecialConditionValue($field, str_replace('<>', '', $value)) . "'";
                    $criterias .= " ) ";
                } elseif (strpos($value, '>') !== false) {
                    $criterias .= " ( ";
                    $criterias = $criterias . ' ' . $strUpper . '(' . $alias . '.' . $field->field_name . ')' . '>' . "'" . $this->getSpecialConditionValue($field, str_replace('>', '', $value)) . "'";
                    $criterias .= " ) ";
                } elseif (strpos($value, '<') !== false) {
                    $criterias .= " ( ";
                    $criterias = $criterias . ' ' . $strUpper . '(' . $alias . '.' . $field->field_name . ')' . '<' . "'" . $this->getSpecialConditionValue($field, str_replace('<', '', $value)) . "'";
                    $criterias .= " ) ";
                } else {
                    if ($field->field_data_type != 'text') {
                        $criterias .= " ( ";
                        $criterias = $criterias . ' ' . $strUpper . '(' . $alias . '.' . $field->field_name . ')' . '=' . "'" . $this->getSpecialConditionValue($field, $value) . "'";
                        $criterias .= " ) ";
                    } else {
                        if (strpos($value, '*') !== false) {
                            $criterias .= " ( ";
                            $criterias = $criterias . ' ' . $strUpper . '(' . $alias . '.' . $field->field_name . ') like' . " '" . str_replace('*', '%', $value) . "'";
                            $criterias .= " ) ";
                        }else {
                            if (strpos($value, "''") !== false) {
                                $criterias .= " ( ";
                                $criterias = $criterias . ' ' . $strUpper . '(' . $field->field_name . ')' . "='' or " . ' ' . $field->field_name . ' is null';
                                $criterias .= " ) ";
                            } else {
                                $criterias .= " ( ";
                                $criterias = $criterias . ' ' . $strUpper . '(' . $alias . '.' . $field->field_name . ')' . '=' . "'" . $this->getSpecialConditionValue($field, $value) . "'";
                                $criterias .= " ) ";
                            }
                        }
                        
                    }
                }
            }
        }
        return $criterias;
    }
    public function convertMonthname2Number($month)
    {
        if (strtolower($month) == 'jan' || strtolower($month) == 'january') {
            return '01';
        } elseif (strtolower($month) == 'feb' || strtolower($month) == 'february') {
            return '02';
        } elseif (strtolower($month) == 'mar' || strtolower($month) == 'march') {
            return '03';
        } elseif (strtolower($month) == 'apr' || strtolower($month) == 'april') {
            return '04';
        } elseif (strtolower($month) == 'may' || strtolower($month) == 'may') {
            return '05';
        } elseif (strtolower($month) == 'jun' || strtolower($month) == 'june') {
            return '06';
        } elseif (strtolower($month) == 'jul' || strtolower($month) == 'july') {
            return '07';
        } elseif (strtolower($month) == 'aug' || strtolower($month) == 'august') {
            return '08';
        } elseif (strtolower($month) == 'sep' || strtolower($month) == 'september') {
            return '09';
        } elseif (strtolower($month) == 'oct' || strtolower($month) == 'october') {
            return '10';
        } elseif (strtolower($month) == 'nov' || strtolower($month) == 'november') {
            return '11';
        } elseif (strtolower($month) == 'dec' || strtolower($month) == 'december') {
            return '12';
        } else {
            return $month;
        }
    }
 
    // ==================== static condition
    public static function getStaticSpecialCondiction($field, $value)
    {
        $criterias = '';
        $criterias .= $criterias . \App\Services\service::getStaticSpecialConditionRecurring($field, $value);
        return $criterias;
    }
    public static function getStaticSpecialConditionRecurring($field, $value)
    {
        $strUpper = '';
        $criterias = '';
        if ($field->field_data_type == 'decimal') {
        } elseif ($field->field_data_type == 'date') {
        } else {
            $strUpper = 'UPPER';
        }
        if (strpos($value, '|') !== false) {
            $andConditions = explode('|', $value);
            $i = 0;
            foreach ($andConditions as $conditionValue) {
                if ($i == 0) {
                    $criterias .= '(' . \App\Services\service::getStaticSpecialConditionRecurring($field, $conditionValue) . '';
                } else {
                    $criterias .= ' OR ' . \App\Services\service::getStaticSpecialConditionRecurring($field, $conditionValue);
                }
                $i += 1;
            }
            $criterias .= ')';
        } else {
            if (strpos($value, '..') !== false) {
                $andConditions = explode('..', $value);
                $criterias .= " (";
                $criterias = $criterias . ' ' . $strUpper . '(' . $field->field_name . ')' . '>=' . "'" . \App\Services\service::getStaticSpecialConditionValue($field, $andConditions[0]) . "' ";
                $criterias = $criterias . ' AND ' . $strUpper . '(' . $field->field_name . ')' . '<=' . "'" . \App\Services\service::getStaticSpecialConditionValue($field, $andConditions[1]) . "' ";
                $criterias .= " ) ";
            } else {
                if (strpos($value, '>=') !== false) {
                    $criterias .= " ( ";
                    $criterias = $criterias . ' ' . $strUpper . '(' . $field->field_name . ')' . '>=' . "'" . \App\Services\service::getStaticSpecialConditionValue($field, str_replace('>=', '', $value)) . "'";
                    $criterias .= " ) ";
                } elseif (strpos($value, '<=') !== false) {
                    $criterias .= " ( ";
                    $criterias = $criterias . ' ' . $strUpper . '(' . $field->field_name . ')' . '<=' . "'" . \App\Services\service::getStaticSpecialConditionValue($field, str_replace('<=', '', $value)) . "'";
                    $criterias .= " ) ";
                } elseif (strpos($value, '<>') !== false) {
                    $criterias .= " ( ";
                    $criterias = $criterias . ' ' . $strUpper . '(' . $field->field_name . ')' . '<>' . "'" . \App\Services\service::getStaticSpecialConditionValue($field, str_replace('<>', '', $value)) . "'";
                    $criterias .= " ) ";
                } elseif (strpos($value, '>') !== false) {
                    $criterias .= " ( ";
                    $criterias = $criterias . ' ' . $strUpper . '(' . $field->field_name . ')' . '>' . "'" . \App\Services\service::getStaticSpecialConditionValue($field, str_replace('>', '', $value)) . "'";
                    $criterias .= " ) ";
                } elseif (strpos($value, '<') !== false) {
                    $criterias .= " ( ";
                    $criterias = $criterias . ' ' . $strUpper . '(' . $field->field_name . ')' . '<' . "'" . \App\Services\service::getStaticSpecialConditionValue($field, str_replace('<', '', $value)) . "'";
                    $criterias .= " ) ";
                } else {
                    $criterias .= " ( ";
                    $criterias = $criterias . ' ' . $strUpper . '(' . $field->field_name . ')' . '=' . "'" . \App\Services\service::getStaticSpecialConditionValue($field, $value) . "'";
                    $criterias .= " ) ";
                }
            }
        }
        return $criterias;
    }
    public static function getStaticSpecialConditionValue($field, $value)
    {
        $criterias = '';
        if ($field->field_data_type == 'decimal') {
            return is_numeric($value) ? $value : -999999999999999999999;
        } elseif ($field->field_data_type == 'date') {
            //Special date value
            try {
                if (trim($value, ' ') == '') {
                    return date_format(Carbon::createFromDate(1900, 01, 01), 'Y-m-d');
                }
                if (strtoupper($value) == 'T' || $value == 'TODAY') {
                    return Carbon::today()->toDateString();
                } elseif (strtoupper($value) == 'TO' || $value == 'TOMORROW') {
                    return Carbon::tomorrow()->toDateString();
                } elseif (strtoupper($value) == 'Y' || $value == 'YESTERDAY' || $value == 'YES') {
                    return Carbon::yesterday()->toDateString();
                } elseif (strtoupper($value) == 'CM') {
                    return Carbon::now()->subMonth()->endOfMonth()->toDateString();
                } elseif (strtoupper($value) == 'CW') {
                    return Carbon::now()->endOfWeek()->toDateString();
                } elseif (strtoupper($value) == 'CY') {
                    return date_format(Carbon::createFromDate(null, 12, 31), 'Y-m-d');
                } else {
                    // date value
                    if (strpos($value, '/') !== false) {
                        $dateparts = explode('/', $value);
                        return date_format(Carbon::createFromDate(isset($dateparts[2]) ? $dateparts[2] : null, isset($dateparts[1]) ? $dateparts[1] : null, isset($dateparts[0]) ? $dateparts[0] : null), 'Y-m-d');
                    } elseif (strpos($value, '-') !== false) {
                        $dateparts = explode('-', $value);
                        return date_format(Carbon::createFromDate(isset($dateparts[2]) ? $dateparts[2] : null, isset($dateparts[1]) ? $dateparts[1] : null, isset($dateparts[0]) ? $dateparts[0] : null), 'Y-m-d');
                    } else {
                        if (strlen($value) == 2) {
                            return date_format(Carbon::createFromDate(null, null, $value), 'Y-m-d');
                        } elseif (strlen($value) == 4) {
                            return date_format(Carbon::createFromDate(null, substr($value, 2, 4), substr($value, 0, 2)), 'Y-m-d');
                        } elseif (strlen($value) == 6) {
                            return date_format(Carbon::createFromDate('20' . substr($value, 4, 2), substr($value, 2, 2), substr($value, 0, 2)), 'Y-m-d');
                        } else {
                            return date_format(Carbon::createFromDate(substr($value, 4, 4), substr($value, 2, 2), substr($value, 0, 2)), 'Y-m-d');
                        }
                    }
                }
            } catch (\Exception $ex) {
                return date_format(Carbon::createFromDate(2900, 01, 01), 'Y-m-d');
            }
        } else {
            return mb_strtoupper($value, 'UTF-8');
        }
    }
    //=====================================
    function GetCheckFilterAndMenu($lstFields)
    {
        $criterias = ' 1=1 ';
        $filters = array();
        $i = 0;
        if (isset($_GET['menu'])) {
            $menucode = service::decrypt($_GET['menu']);
            $menu = MenuUrlShortcut::where('code', '=', $menucode)->first();

            if ($menu) {
                $filters = json_decode($menu->filters, true);
                foreach ($lstFields as $lstField) {
                    foreach ($lstField->page_group_field_search() as $field) {
                        if (isset($filters[$field->field_name])) {
                            $value = mb_strtoupper($filters[$field->field_name], 'UTF-8');
                            if ($value || $value == '0') {
                                if ($i == 0) {
                                    $criterias .= ' AND ' . $this->getSpecialCondiction($field, $value);
                                } else {
                                    $criterias .= ' AND ' . $this->getSpecialCondiction($field, $value);
                                }
                                $i += 1;
                            }
                        }
                    }
                }
            }
        } else {
            $Otherfilters = '';
            $j = 0;
            foreach ($lstFields as $lstField) {
                foreach ($lstField->page_group_field_search() as $field) {
                    if (isset($_GET[$field->field_name])) {
                        $value = mb_strtoupper(service::decrypt($_GET[$field->field_name]), 'UTF-8');
                        if ($value || $value == '0') {
                            if ($i == 0) {
                                $criterias .= ' AND ' . $this->getSpecialCondiction($field, $value);
                            } else {
                                $criterias .= ' AND ' . $this->getSpecialCondiction($field, $value);
                            }
                        }
                        if ($j == 0) {
                            $Otherfilters .= '"' . $field->field_name . '":"' . $value . '"';
                        } else {
                            $Otherfilters .= ',"' . $field->field_name . '":"' . $value . '"';
                        }
                        $j += 1;
                    }
                }
            }
            $filters = json_decode('{' . $Otherfilters . '}', true);
        }

        return array($filters, $criterias);
    }
    public  function getSearchCriteriasTable($lstFields, $filters)
    {
        $criterias = ' 1=1 ';
        $i = 0;
        foreach ($lstFields as $lstField) {
            foreach ($lstField->page_group_field_search() as $field) {
                if (isset($filters[$field->field_name])) {
                    if (is_array($filters[$field->field_name])) {
                        $value = mb_strtoupper(implode('|', $filters[$field->field_name]), 'UTF-8');
                    } else {
                        $value = mb_strtoupper($filters[$field->field_name], 'UTF-8');
                    }
                    if ($value || $value == '0') {
                        if ($i == 0) {
                            $criterias .= ' AND ' . $this->getSpecialCondiction($field, $value);
                        } else {
                            $criterias .= ' AND ' . $this->getSpecialCondiction($field, $value);
                        }
                        $i += 1;
                    }
                }
            }

            $value = Input::get('value');
            if ($value == 'null') {
            } else {
                $criterias .= ' AND ( ';
                $i = 0;
                foreach ($lstFields as $lstField) {
                    foreach ($lstField->page_group_field_search() as $field) {
                        $value = mb_strtoupper($value, 'UTF-8');
                        if ($field->field_data_type == 'decimal') {
                            $criterias = $criterias . ' (' . $field->field_name . ') =' . " '" . $this->getSpecialConditionValue($field, $value) . "' or ";
                        } elseif ($field->field_data_type == 'date') {
                            $criterias = $criterias . ' (' . $field->field_name . ') =' . " '" . $this->getSpecialConditionValue($field, $value) . "' or ";
                        } else {
                            $criterias = $criterias . ' UPPER(' . $field->field_name . ') like' . " '" . str_replace('*', '%', $value) . "' or ";
                        }
                    }
                }
                $criterias = $criterias . ' 1 = 2 )';
            }
            return $criterias;
        }
    }
    function getSearchCriterias($lstFields, $filters)
    {
        $criterias = ' 1=1 ';
        $i = 0;
        foreach ($lstFields as $lstField) {
            foreach ($lstField->page_group_field_search_text_only() as $field) {
                if (isset($filters[$field->field_name])) {
                    if (is_array($filters[$field->field_name])) {
                        $value = mb_strtoupper(implode('|', $filters[$field->field_name]), 'UTF-8');
                    } else {
                        $value = mb_strtoupper(trim($filters[$field->field_name], ' '), 'UTF-8');
                    }
                    if ($value || $value == '0') {
                        if ($i == 0) {
                            $criterias .= ' AND ' . $this->getSpecialCondiction($field, $value);
                        } else {
                            $criterias .= ' AND ' . $this->getSpecialCondiction($field, $value);
                        }
                        $i += 1;
                    }
                }
            }
        }
        
        $value = trim(Input::get('value'), ' ');
        if ($value == 'null') {
        } else {
            $criterias .= ' AND ( ';
            $i = 0;
            foreach ($lstFields as $lstField) {
                foreach ($lstField->page_group_field_search_text_only() as $field) {
                    $value = mb_strtoupper(trim($value, ' '), 'UTF-8');
                    if ($field->field_data_type == 'decimal') {
                        $criterias = $criterias . ' (' . $field->field_name . ') =' . " '" . $this->getSpecialConditionValue($field, $value) . "' or ";
                    } elseif ($field->field_data_type == 'date') {
                        $criterias = $criterias . ' (' . $field->field_name . ') =' . " '" . $this->getSpecialConditionValue($field, $value) . "' or ";
                    } else {
                        $criterias = $criterias . ' UPPER(' . $field->field_name . ') like' . " '" . str_replace('*', '%', $value) . "' or ";
                    }
                }
            }
            $criterias = $criterias . ' 1 = 2 )';
        }
        
        return $criterias;
    }
    function getAdvanceSearchCriterias($lstFields, $filters)
    {
        $criterias = ' 1=1 ';
        $i = 0;
        foreach ($lstFields as $fieldgroup) {
            $search_fields = $fieldgroup->page_group_field_search();
            foreach ($search_fields as $field) {
                if (isset($filters[$field->field_name])) {
                    if (is_array($filters[$field->field_name])) {
                        $value = mb_strtoupper(implode('|', $filters[$field->field_name]), 'UTF-8');
                    } else {
                        $value = mb_strtoupper(trim($filters[$field->field_name], ' '), 'UTF-8');
                    }
                    if ($value || $value == '0') {
                        if ($i == 0) {
                            $criterias .= ' AND ' . \App\Services\service::getSpecialCondiction($field, $value);
                        } else {
                            $criterias .= ' AND ' . \App\Services\service::getSpecialCondiction($field, $value);
                        }
                        $i += 1;
                    }
                }
            }
        }
        return $criterias;
    }
    function getAdvanceReportSearchCriterias($lstFields, $filters, $table_name)
    {
        $criterias = ' 1=1 ';
        $i = 0;
        foreach ($lstFields as $fieldgroup) {
            if ($fieldgroup->table_name == $table_name) {
                foreach ($fieldgroup->page_group_field_report_search() as $field) {
                    if (isset($filters[$field->field_name])) {
                        if (is_array($filters[$field->field_name])) {
                            $value = mb_strtoupper(implode('|', $filters[$field->field_name]), 'UTF-8');
                        } else {
                            $value = mb_strtoupper(trim($filters[$field->field_name], ' '), 'UTF-8');
                        }
                        if ($value || $value == '0') {
                            if ($i == 0) {
                                $criterias .= ' AND ' . \App\Services\service::getSpecialCondiction($field, $value);
                            } else {
                                $criterias .= ' AND ' . \App\Services\service::getSpecialCondiction($field, $value);
                            }
                            $i += 1;
                        }
                    }
                }
            }
        }
        return $criterias;
    }
    function getAdvanceReportSearchCriteriasJoin($lstFields, $filters, $table_name, $alias)
    {
        $criterias = ' 1=1 ';
        $i = 0;
        foreach ($lstFields as $fieldgroup) {
            if ($fieldgroup->table_name == $table_name) {
                foreach ($fieldgroup->page_group_field_report_search() as $field) {
                    if (isset($filters[$field->field_name])) {
                        if (is_array($filters[$field->field_name])) {
                            $value = mb_strtoupper(implode('|', $filters[$field->field_name]), 'UTF-8');
                        } else {
                            $value = mb_strtoupper(trim($filters[$field->field_name], ' '), 'UTF-8');
                        }
                        if ($value || $value == '0') {
                            if ($i == 0) {
                                $criterias .= ' AND ' . \App\Services\service::getSpecialCondictionJoin($field, $value, $alias);
                            } else {
                                $criterias .= ' AND ' . \App\Services\service::getSpecialCondictionJoin($field, $value, $alias);
                            }
                            $i += 1;
                        }
                    }
                }
            }
        }
        return $criterias;
    }
    function extractAdvanceReportSearchCriterias($lstFields, $filters, $type)
    {
        $results = array();
        $my_lstFields = $lstFields->where('table_name', $type);
        foreach ($my_lstFields as $fieldgroup) {
            foreach ($fieldgroup->page_group_field_report_search() as $field) {
                if (isset($filters[$type . '_' . $field->field_name])) {
                    if (is_array($filters[$type . '_' . $field->field_name])) {
                        $value = mb_strtoupper(implode('|', $filters[$type . '_' . $field->field_name]), 'UTF-8');
                    } else {
                        $value = mb_strtoupper(trim($filters[$type . '_' . $field->field_name], ' '), 'UTF-8');
                    }
                    $results[$field->field_name] = $value;
                }
            }
        }
        return $results;
    }
    function getSummarySearchCriterias($lstFields, $filters)
    {
        $criterias = ' 1=1 ';
        $i = 0;
        foreach ($lstFields as $fieldgroup) {
            foreach ($fieldgroup->page_group_field_flowfilter() as $field) {
                if (isset($filters[$field->field_name])) {
                    if (is_array($filters[$field->field_name])) {
                        $value = mb_strtoupper(implode('|', $filters[$field->field_name]), 'UTF-8');
                    } else {
                        $value = mb_strtoupper(trim($filters[$field->field_name], ' '), 'UTF-8');
                    }
                    if ($value || $value == '0') {
                        if ($i == 0) {
                            $criterias .= ' AND ' . \App\Services\service::getSpecialCondiction($field, $value);
                        } else {
                            $criterias .= ' AND ' . \App\Services\service::getSpecialCondiction($field, $value);
                        }
                        $i += 1;
                    }
                }
            }
        }
        return $criterias;
    }
    public function getSummarySearchCriteriasByFieldName($field, $value)
    {

        $criterias = ' 1=1 ';

        $i = 0;
        $value = mb_strtoupper($value, 'UTF-8');
        if ($value || $value == '0') {
            if ($i == 0) {
                $criterias .= ' AND ' . \App\Services\service::getSpecialCondiction($field, $value);
            } else {
                $criterias .= ' AND ' . \App\Services\service::getSpecialCondiction($field, $value);
            }
            $i += 1;
        }
        return $criterias;
    }
    function getSearchajaxPagination($lstFields, $pageid = 0, $filters = null)
    {
        $filters = Input::all();
        if (isset($filters['searchtype']) && $filters['searchtype'] == 'advancesearch') {
            $criterias = ' 1=1 ';
            $i = 0;
            foreach ($lstFields as $fieldgroup) {
                $search_fields = $fieldgroup->page_group_field_search();
                foreach ($search_fields as $field) {
                    if (isset($filters[$field->field_name])) {
                        if (is_array($filters[$field->field_name])) {
                            $value = mb_strtoupper(implode('|', $filters[$field->field_name]), 'UTF-8');
                        } else {
                            $value = mb_strtoupper(trim($filters[$field->field_name], ' '), 'UTF-8');
                        }
                        if ($value || $value == '0') {
                            if ($i == 0) {
                                $criterias .= ' AND ' . \App\Services\service::getSpecialCondiction($field, $value);
                            } else {
                                $criterias .= ' AND ' . \App\Services\service::getSpecialCondiction($field, $value);
                            }
                            $i += 1;
                        }
                    }
                }
            }
            return $criterias;
        } else {
            if (Input::get('searchtype') == 'advancesearch') {
                $filters = Input::all();
                $criterias = ' 1=1 ';
                $i = 0;
                foreach ($lstFields as $fieldgroup) {
                    foreach ($fieldgroup->page_group_field_search($pageid) as $field) {
                        if (isset($filters[$field->field_name])) {
                            if (is_array($filters[$field->field_name])) {
                                $value = mb_strtoupper(implode('|', $filters[$field->field_name]), 'UTF-8');
                            } else {
                                $value = mb_strtoupper(trim($filters[$field->field_name], ' '), 'UTF-8');
                            }

                            if ($field->field_data_type == 'text') {
                                if ($value) {
                                    if ($i == 0) {
                                        $criterias .= ' AND ' . $this->getSpecialCondiction($field, $value);
                                    } else {
                                        $criterias .= ' AND ' . $this->getSpecialCondiction($field, $value);
                                    }
                                    $i += 1;
                                }
                            } else {
                                if ($i == 0) {
                                    $criterias .= ' AND ' . $this->getSpecialCondiction($field, $value);
                                } else {
                                    $criterias .= ' AND ' . $this->getSpecialCondiction($field, $value);
                                }
                                $i += 1;
                            }
                        }
                    }
                }
            } else {
                $criterias = ' 1=1 ';
                $value = Input::get('value');
                if ($value == 'null') {
                } else {
                    $criterias .= ' AND ( ';
                    $i = 0;
                    foreach ($lstFields as $lstField) {
                        foreach ($lstField->page_group_field_search_text_only() as $field) {
                            $value = mb_strtoupper($value, 'UTF-8');
                            if ($field->field_data_type == 'decimal') {
                                $criterias = $criterias . ' (' . $field->field_name . ') =' . " '" .  $this->getSpecialConditionValue($field, $value) . "' or ";
                            } elseif ($field->field_data_type == 'date') {
                                $criterias = $criterias . ' (' . $field->field_name . ') =' . " '" . $this->getSpecialConditionValue($field, $value) . "' or ";
                            } else {
                                $criterias = $criterias . ' UPPER(' . $field->field_name . ') like' . " '" . str_replace('*', '%', $value) . "' or ";
                            }
                        }
                    }
                    $criterias = $criterias . ' 1 = 2 )';
                }
            }
            return $criterias;
        }
    }
    public function getTableFieldSortbyUser($tablename)
    {
        $data = TableRecordsSorted::where('table_name', $tablename)->where('username', Auth::user()->email)->first();
        return $data;
    }
    function getTableAjaxPaginationSort($lstFields)
    {
        if ($lstFields) {
            $criterias = $lstFields->field_name . ' ' . $lstFields->asc_or_desc;
            return $criterias;
        }
        return null;
    }
    function getSearchajaxPaginationwithcondiction($lstFields, $pageid, $notGetField)
    {
        $criterias = ' 1=1 ';

        $value = Input::get('value');
        if ($value == 'null') {
        } else {
            $criterias .= ' AND ( ';
            $i = 0;
            foreach ($lstFields as $lstField) {
                foreach ($lstField->page_group_field() as $field) {
                    if ($field->field_name != $notGetField) {
                        $value = mb_strtoupper($value, 'UTF-8');
                        if ($field->field_data_type == 'decimal') {
                            $criterias = $criterias . ' (' . $field->field_name . ') =' . " '" . $this->getSpecialConditionValue($field, $value) . "' or ";
                        } elseif ($field->field_data_type == 'date') {
                            $criterias = $criterias . ' (' . $field->field_name . ') =' . " '" . $this->getSpecialConditionValue($field, $value) . "' or ";
                        } else {
                            $criterias = $criterias . ' UPPER(' . $field->field_name . ') like' . " '" . str_replace('*', '%', $value) . "' or ";
                        }
                    }
                }
            }
            $criterias = $criterias . ' 1 = 2 )';
        }
        return $criterias;
    }
    public function getItemAVGCost($app_setup, $posting_date, $item_no, $location_code)
    {
        if ($app_setup->inventory_cost_setting == 'Item') {
            return ItemAverageCost::where('item_no', $item_no)->where('closing_date', '<=', Carbon::parse($posting_date)->toDateString())->orderBy('closing_date', 'DESC')->first();
        } else {
            return ItemAverageCost::where('location_code', $location_code)->where('item_no', $item_no)->where('closing_date', '<=', Carbon::parse($posting_date)->toDateString())->orderBy('closing_date', 'DESC')->first();
        }
    }
    public static function ApprovalAndTaskLinkGenerator($type, $value)
    {
        $value = service::encrypt($value);
        $url = '';
        if ($type == 'Order') { //Purchase order
            $url = '/purchase-order/transaction?type=ed&code=';
        } elseif ($type == 'Quote') {
            $url = '/purchase-quote/transaction?type=ed&code=';
        } elseif ($type == 'Return Order') {
            $url = '/purchase-return-order/transaction?type=ed&code=';
        } elseif ($type == 'Invoice') {
            $url = '/purchase-invoice/transaction?type=ed&code=';
        } elseif ($type == 'Credit Memo') {
            $url = '/purchase-credit-memo/transaction?type=ed&code=';
        } elseif ($type == 'Transfer Order') {
            $url = '/transfer-order/transaction?type=ed&code=';
        } elseif ($type == 'General Journal') {
            $url = '/general-journal/transaction?type=ed&code=';
        } elseif ($type == 'Payment Journal') {
            $url = '/payment-journal/transaction?type=ed&code=';
        }

        return $url . urlencode($value);
    }
    function getAdvanceSearchCriteriaDashboard($lstFields, $filters)
    {

        $criterias = ' 1=1 ';
        $i = 0;
        foreach ($lstFields as $field) {
            if (isset($filters[$field->field_name])) {
                $value = mb_strtoupper($filters[$field->field_name], 'UTF-8');
                if ($value || $value == '0') {
                    if ($i == 0) {
                        $criterias .= ' AND ' . \App\Services\service::getSpecialCondiction($field, $value);
                    } else {
                        $criterias .= ' AND ' . \App\Services\service::getSpecialCondiction($field, $value);
                    }
                    $i += 1;
                }
            }
        }
        return $criterias;

    }
    public static function getAdvanceSearchCriteriaDashboard_static($lstFields, $filters)
    {
        $criterias = ' 1=1 ';
        $i = 0;
        foreach ($lstFields as $field) {
            if (isset($filters[$field->field_name])) {
                $value = mb_strtoupper($filters[$field->field_name], 'UTF-8');
                if ($value || $value == '0') {
                    if ($i == 0) {
                        $criterias .= ' AND ' . \App\Services\service::getStaticSpecialCondiction($field, $value);
                    } else {
                        $criterias .= ' AND ' . \App\Services\service::getStaticSpecialCondiction($field, $value);
                    }
                    $i += 1;
                }
            }
        }
        return $criterias;
    }
    public static function buildTableQuery($table_id, $view_name, $database_name="")
    {
        if($database_name){
            \Config::set('database.connections.company.database', $database_name);
            \DB::purge('company');
        }
        
        try {
            $result = 'select ';
            $seperator = '';
            $i = 0;
            $j = 0;
            $table = Tables::where('id', $table_id)->first();
            if (!$table) {
                return 'false';
            }
            if (!service::_isExistedTable($table->table_name)) {
                return 'false';
            }
            $fields = TableField::where('table_id', $table_id)->orderBy('index', 'asc')->get();
            foreach ($fields as $field) {
                if ($i > 0) {
                    $seperator = ',';
                }
                if (strtolower($field->input_type) == 'flowfield') {
                    if (strtolower($field->flow_field_method) == 'formula') {
                        $result .= $seperator . '(' . $field->comments . ') as ' . $field->field_name;
                    
                    } else {
                        $paras = TableFieldFlowFieldParas::where('field_id', $field->id)->get();
                        $criterias = ' 1=1 ';
                        $order = ' ';
                        foreach ($paras as $para) {
                            if (strtolower($para->condition_type) == 'field') {
                                $criterias .= ' AND header.' . $para->condition_value . '' . $para->condition_operator . 'line.' . $para->field_name . '';
                            } elseif (strtolower($para->condition_type) == 'sort') {
                                $order .= ' AND header.' . $para->condition_value . '' . $para->condition_operator . 'line.' . $para->field_name . '';
                                if ($j == 0) {
                                    $order = ' order by ' . $para->field_name . '';
                                } else {
                                    $order = ',' . $para->field_name . '';
                                }
                                $j += 1;
                            } else {
                                $criterias .= ' AND line.' . $para->field_name . '' . $para->condition_operator . '\'' . $para->condition_value . '\'';
                            }
                        }
                        if (strtolower($field->flow_field_method) == 'sum') {
                            $reverse_sign = '';
                            if (strtolower($field->flow_field_reverse_sign) == 'yes') $reverse_sign = ' * (-1)';
                            $result .= $seperator . 'ifnull((select sum(' . $field->flow_field_field_name . ')' . $reverse_sign . ' from ' . $field->flow_field_table_name . ' as line where ' . $criterias . '),0) as ' . $field->field_name . '';
                        } elseif (strtolower($field->flow_field_method) == 'count') {
                            $result .= $seperator . 'ifnull((select count(' . $field->flow_field_field_name . ') from ' . $field->flow_field_table_name . ' as line where ' . $criterias . '),0) as ' . $field->field_name . '';
                        } elseif (strtolower($field->flow_field_method) == 'lookup') {
                            // if(hasColumnHelper("table_field", "flow_field_sortable") && $field->flow_field_sortable){
                            //     $sortable = "ORDER BY ".$field->flow_field_sortable;
                                
                            // }
                            
                            $result .= $seperator . '(select (' . $field->flow_field_field_name . ') from ' . $field->flow_field_table_name . ' as line where ' . $criterias .' limit 1) as ' . $field->field_name . '';
                        } elseif (strtolower($field->flow_field_method) == 'max') {
                            $result .= $seperator . '(select max(' . $field->flow_field_field_name . ') from ' . $field->flow_field_table_name . ' as line where ' . $criterias . ') as ' . $field->field_name . '';
                        } elseif (strtolower($field->flow_field_method) == 'min') {
                            $result .= $seperator . 'ifnull((select min(' . $field->flow_field_field_name . ') from ' . $field->flow_field_table_name . ' as line where ' . $criterias . '),\'' . $field->default_value . '\') as ' . $field->field_name . '';
                        } else {
                            $result .= $seperator . '(select (' . $field->flow_field_field_name . ') from ' . $field->flow_field_table_name . ' as line where ' . $criterias . ' ' . $order . ' desc limit 1) as ' . $field->field_name . '';
                        }
                    }
                    
                } else if (strtolower($field->input_type) == 'scripts') {
                    $result .= $seperator . $field->scripts . ' as ' . $field->field_name;
                } else {
                    if (service::_isExistedTableField($table->table_name, $field->field_name)) {
                        $result .= '' . $seperator . '`' . $field->field_name . '`';
                    }
                }
                $i += 1;
            }
            $result .= ' from `' . $table->table_name . '` as header ';
            DB::connection('company')->statement('drop view if exists ' . $view_name);
            DB::connection('company')->statement('create sql security invoker  view ' . $view_name . ' as ' . $result);

            return $result;
        } catch (Exception $ex) {
            return 'false';
        }
    }
    public function buildTableSchema($table_name, $database_name="")
    {

        if($database_name){
            \Config::set('database.connections.company.database', $database_name);
            \DB::purge('company');
        }else{
            $database_name = Auth::user()->database_name;
        }

        \DB::connection('company')->beginTransaction();
        try {
            $records =  DB::table('information_schema.columns')->where('table_schema', $database_name)->where('table_name', $table_name)
                ->orderBy('table_name', 'ordinal_position')->get();
            $table = Tables::where('table_name', $table_name)->first();
            $last_id = TableField::select('id')->where('table_name', $table_name)->orderBy('id', 'desc')->first();
            if ($last_id) {
                $no_of_field = $last_id->id + 1;
            } else {
                $no_of_field = $table->id + 1;
            }

            foreach ($records as $record) {
                $field = TableField::where('table_name', $record->TABLE_NAME)->where('field_name', $record->COLUMN_NAME)->first();
                $index_name = $table_name."_search_index";
                $indexs = \DB::table('information_schema.statistics')->where('table_schema', $database_name)->where('table_name', $table_name)
                    ->where('column_name', $record->COLUMN_NAME)
                    ->where("index_name", $index_name)->first();

                if ($field) {
                    $field->index = $record->ORDINAL_POSITION;
                    $field->field_name = $record->COLUMN_NAME;
                    $field->field_description = ucwords(str_replace('_', ' ', $record->COLUMN_NAME));
                    $field->placeholder = ucwords(str_replace('_', ' ', $record->COLUMN_NAME));
                    $field->default_value = $record->COLUMN_DEFAULT;
                    $field->is_nullable = ucfirst(strtolower($record->IS_NULLABLE));
                    $field->data_type = strtolower($record->DATA_TYPE);
                    $field->max_length = $record->CHARACTER_MAXIMUM_LENGTH;
                    $field->comments = $record->COLUMN_COMMENT;
                    $field->field_data_type = 'text';
                    $field->input_type = 'input';

                    if ($indexs && $record->COLUMN_NAME != "id") {
                        $field->search_index = 'Yes';
                    } else {
                        $field->search_index = 'No';
                    }
                    $field->number_precision = null;
                    $field->numberic_scale = null;
                    $field->extra = $record->EXTRA;
                    $field->primary_key = $record->COLUMN_KEY;
                    if ($record->DATA_TYPE == 'bigint') {
                        $field->field_data_type = 'big number';
                        $field->input_type = 'input';
                        $field->alignment = 'text-left';
                        $field->max_length = 20;
                        $field->decimal_format = 'general';
                    } elseif ($record->DATA_TYPE == 'int') {
                        $field->field_data_type = 'number';
                        $field->input_type = 'input';
                        $field->alignment = 'text-left';
                        $field->max_length = 10;
                        $field->decimal_format = 'general';
                    } elseif ($record->DATA_TYPE == 'tinyint') {
                        $field->field_data_type = 'small number';
                        $field->input_type = 'input';
                        $field->alignment = 'text-left';
                        $field->max_length = 3;
                    } elseif ($record->DATA_TYPE == 'decimal') {
                        $field->field_data_type = 'decimal';
                        $field->input_type = 'input';
                        $field->alignment = 'text-right';
                        $field->decimal_format = $record->COLUMN_COMMENT;
                        $field->number_precision = ($record->NUMERIC_PRECISION) ? $record->NUMERIC_PRECISION : 32;
                        $field->numberic_scale = ($record->NUMERIC_SCALE) ? $record->NUMERIC_SCALE : 18;
                    } elseif ($record->DATA_TYPE == 'date') {
                        $field->field_data_type = 'date';
                        $field->input_type = 'date';
                        $field->alignment = 'text-left';
                        $field->decimal_format = $record->COLUMN_COMMENT;
                        $field->max_length = 20;
                    } elseif ($record->DATA_TYPE == 'datetime') {
                        $field->field_data_type = 'datetime';
                        $field->input_type = 'datetime';
                        $field->decimal_format = $record->COLUMN_COMMENT;
                        $field->alignment = 'text-left';
                        $field->max_length = 50;
                    } elseif ($record->DATA_TYPE == 'timestamp') {
                        $field->field_data_type = 'timestamp';
                        $field->input_type = 'timestamp';
                        $field->alignment = 'text-left';
                        $field->max_length = 50;
                    } else {
                        $field->field_data_type = 'text';
                        $field->input_type = 'input';
                        $field->alignment = 'text-left';
                        if (strtolower($record->COLUMN_COMMENT) == 'checkbox') {
                            $field->input_type = 'checkbox';
                        } elseif (strtolower($record->COLUMN_COMMENT) == 'time') {
                            $field->input_type = 'time';
                        } else {
                            $keys = explode('|', $record->COLUMN_COMMENT);
                            if ($keys) {
                                if (strtolower($keys[0]) == 'option') {
                                    $field->input_type = 'option';
                                    $field->option_text = $keys[1];
                                } elseif (strtolower($keys[0]) == 'select') {
                                    $field->input_type = 'select';
                                } elseif (strtolower($keys[0]) == 'select2') {
                                    $field->input_type = 'select2';
                                } elseif (strtolower($keys[0]) == 'select3') {
                                    $field->input_type = 'select3';
                                } elseif (strtolower($keys[0]) == 'lookup') {
                                    $field->input_type = 'lookup';
                                }
                            }
                        }
                    }
                    $field->save();
                    // Table Relationship
                    if ($field->input_type == 'select' || $field->input_type == 'select2' || $field->input_type == 'select3' || $field->input_type == 'lookup') {
                        $keys = explode('|', $record->COLUMN_COMMENT);
                        if (count($keys) >= 6) {
                            $table_relation = TableRelation::where('field_id', $field->id)->first();
                            if ($table_relation) {
                                $table_relation->table_name = $field->table_name;
                                $table_relation->field_name = $field->field_name;
                                $table_relation->relation_table_name = $keys[1];
                                $table_relation->data_table_name = $keys[2];
                                $table_relation->relation_field_name = $keys[3];
                                $table_relation->relation_desc_field = $keys[4];
                                $table_relation->relation_desc_field_2 = $keys[5];
                                $table_relation->type = $field->input_type;
                                $table_relation->save();
                            } else {
                                $table_relation = TableRelation::where('table_name', $field->table_name)->where('field_name', $field->field_name)->first();
                                if (!$table_relation) {
                                    $new_table_relation = new TableRelation();
                                    $new_table_relation->field_id = $field->id;
                                    $new_table_relation->table_name = $field->table_name;
                                    $new_table_relation->field_name = $field->field_name;
                                    $new_table_relation->relation_table_name = $keys[1];
                                    $new_table_relation->data_table_name = $keys[2];
                                    $new_table_relation->relation_field_name = $keys[3];
                                    $new_table_relation->relation_desc_field = $keys[4];
                                    $new_table_relation->relation_desc_field_2 = $keys[5];
                                    $new_table_relation->type = $field->input_type;
                                    $new_table_relation->save();
                                } else {
                                    $table_relation->relation_table_name = $keys[1];
                                    $table_relation->data_table_name = $keys[2];
                                    $table_relation->relation_field_name = $keys[3];
                                    $table_relation->relation_desc_field = $keys[4];
                                    $table_relation->relation_desc_field_2 = $keys[5];
                                    $table_relation->type = $field->input_type;
                                    $table_relation->save();
                                }
                            }
                        }
                    }
                } else {
                    $field = new TableField();
                    $field->id = $no_of_field;

                    $field->table_name = $table->table_name;
                    $field->table_id = $table->id;
                    $field->index = $record->ORDINAL_POSITION;
                    $field->field_name = $record->COLUMN_NAME;
                    $field->field_description = ucwords(str_replace('_', ' ', $record->COLUMN_NAME));
                    $field->placeholder = ucwords(str_replace('_', ' ', $record->COLUMN_NAME));
                    $field->default_value = $record->COLUMN_DEFAULT;
                    $field->is_nullable = ucfirst(strtolower($record->IS_NULLABLE));
                    $field->data_type = strtolower($record->DATA_TYPE);
                    $field->max_length = $record->CHARACTER_MAXIMUM_LENGTH;
                    $field->comments = $record->COLUMN_COMMENT;
                    $field->field_data_type = 'text';
                    $field->input_type = 'input';

                    if ($indexs) {
                        $field->search_index = 'Yes';
                    } else {
                        $field->search_index = 'No';
                    }
                    $field->number_precision = null;
                    $field->numberic_scale = null;
                    $field->extra = $record->EXTRA;
                    $field->primary_key = $record->COLUMN_KEY;
                    if ($record->DATA_TYPE == 'bigint') {
                        $field->field_data_type = 'big number';
                        $field->input_type = 'input';
                        $field->alignment = 'text-left';
                        $field->max_length = 20;
                    } elseif ($record->DATA_TYPE == 'int') {
                        $field->field_data_type = 'number';
                        $field->input_type = 'input';
                        $field->alignment = 'text-left';
                        $field->max_length = 10;
                    } elseif ($record->DATA_TYPE == 'tinyint') {
                        $field->field_data_type = 'small number';
                        $field->input_type = 'input';
                        $field->alignment = 'text-left';
                        $field->max_length = 3;
                    } elseif ($record->DATA_TYPE == 'decimal') {
                        $field->field_data_type = 'decimal';
                        $field->input_type = 'input';
                        $field->alignment = 'text-right';
                        $field->decimal_format = $record->COLUMN_COMMENT;
                        $field->number_precision = ($record->NUMERIC_PRECISION) ? $record->NUMERIC_PRECISION : 32;
                        $field->numberic_scale = ($record->NUMERIC_SCALE) ? $record->NUMERIC_SCALE : 18;
                    } elseif ($record->DATA_TYPE == 'date') {
                        $field->field_data_type = 'date';
                        $field->input_type = 'date';
                        $field->alignment = 'text-left';
                        $field->max_length = 20;
                    } elseif ($record->DATA_TYPE == 'datetime') {
                        $field->field_data_type = 'datetime';
                        $field->input_type = 'datetime';
                        $field->alignment = 'text-left';
                        $field->max_length = 50;
                    } else {
                        $field->field_data_type = 'text';
                        $field->input_type = 'input';
                        $field->alignment = 'text-left';
                        $field->field_data_type = 'text';
                        $field->input_type = 'input';
                        $field->alignment = 'text-left';
                        if (strtolower($record->COLUMN_COMMENT) == 'checkbox') {
                            $field->input_type = 'checkbox';
                        } elseif (strtolower($record->COLUMN_COMMENT) == 'time') {
                            $field->input_type = 'time';
                        } else {
                            $keys = explode('|', $record->COLUMN_COMMENT);
                            if ($keys) {
                                if (strtolower($keys[0]) == 'option') {
                                    $field->input_type = 'option';
                                    $field->option_text = $keys[1];
                                } elseif (strtolower($keys[0]) == 'select') {
                                    $field->input_type = 'select';
                                } elseif (strtolower($keys[0]) == 'select2') {
                                    $field->input_type = 'select2';
                                } elseif (strtolower($keys[0]) == 'select3') {
                                    $field->input_type = 'select3';
                                } elseif (strtolower($keys[0]) == 'lookup') {
                                    $field->input_type = 'lookup';
                                }
                            }
                        }
                    }
                    $field->save();
                    // Table Relationship
                    if ($field->input_type == 'select' || $field->input_type == 'select2' || $field->input_type == 'select3' || $field->input_type == 'lookup') {
                        $keys = explode('|', $record->COLUMN_COMMENT);
                        if (count($keys) >= 6) {
                            $table_relation = TableRelation::where('field_id', $field->id)->first();
                            if ($table_relation) {
                                $table_relation->table_name = $field->table_name;
                                $table_relation->field_name = $field->field_name;
                                $table_relation->relation_table_name = $keys[1];
                                $table_relation->data_table_name = $keys[2];
                                $table_relation->relation_field_name = $keys[3];
                                $table_relation->relation_desc_field = $keys[4];
                                $table_relation->relation_desc_field_2 = $keys[5];
                                $table_relation->type = $field->input_type;
                                $table_relation->save();
                            } else {
                                $table_relation = TableRelation::where('table_name', $field->table_name)->where('field_name', $field->field_name)->first();
                                if (!$table_relation) {
                                    $new_table_relation = new TableRelation();
                                    $new_table_relation->field_id = $field->id;
                                    $new_table_relation->table_name = $field->table_name;
                                    $new_table_relation->field_name = $field->field_name;
                                    $new_table_relation->relation_table_name = $keys[1];
                                    $new_table_relation->data_table_name = $keys[2];
                                    $new_table_relation->relation_field_name = $keys[3];
                                    $new_table_relation->relation_desc_field = $keys[4];
                                    $new_table_relation->relation_desc_field_2 = $keys[5];
                                    $new_table_relation->type = $field->input_type;
                                    $new_table_relation->save();
                                } else {
                                    $table_relation->relation_table_name = $keys[1];
                                    $table_relation->data_table_name = $keys[2];
                                    $table_relation->relation_field_name = $keys[3];
                                    $table_relation->relation_desc_field = $keys[4];
                                    $table_relation->relation_desc_field_2 = $keys[5];
                                    $table_relation->type = $field->input_type;
                                    $table_relation->save();
                                }
                            }
                        }
                    }
                    $no_of_field += 1;
                }
            }

            // REMOVE FIELD IF DOES NOT EXISTED IN DATABASE 
            $fields = TableField::where('table_name', $table_name)
                ->where('input_type', '<>', 'flowfield')
                ->where('input_type', '<>', 'scripts')
                ->get();
            foreach ($fields as $field) {
                $record = DB::table('information_schema.columns')->where('table_schema', $database_name)->where('table_name', $table_name)
                    ->where('COLUMN_NAME', $field->field_name)->first();
                if (!$record) {
                    TableField::where('id', $field->id)->delete();
                    TableRelation::where('field_id', $field->id)->delete();
                    PageGroupFieldRelation::where('field_id', $field->id)->delete();
                }
            }
            \DB::connection('company')->commit();
            return true;
        } catch (\Exception $ex) {
            $this->saveErrorLog($ex);
            \DB::connection('company')->rollback();
            return false;
        }
    }
    public function isAllowDeleteRecord($table_name, $primary_key_value, $is_default = 'Yes')
    {
        try {
            if ($is_default == 'Yes') {
                $related_tables = DB::connection('company')->table('table_relation')->where('relation_table_name', $table_name)->where('cascade_on_delete', 'Yes')->get();
                if ($related_tables) {
                    foreach ($related_tables as $related_table) {
                        if ($related_table->field_id) {
                            if ($related_table->table_name) {
                                if ($table_name == 'item') {
                                    $exception_tables = ['item_unit_of_measure', 'item_variant'];
                                    if (in_array($related_table->table_name, $exception_tables)) {
                                        continue;
                                    }
                                }
                                if ($this->isExistedTable($related_table->table_name)) {
                                    $count = DB::connection('company')->table($related_table->table_name)->where($related_table->field_name, $primary_key_value)->count();
                                    if ($count > 0) {
                                        return array('status' => 'no', 'table' => $related_table->table_name);
                                    }
                                }
                            }
                        }
                    }
                    return array('status' => 'yes', 'table' => '');
                }
                return array('status' => 'yes', 'table' => '');
            } else {
                $foreign_tables = array();
                if ($table_name == 'location') {
                    array_push($foreign_tables, ['table_name' => 'item_ledger_entry', 'foreign_key' => 'location_code']);
                    array_push($foreign_tables, ['table_name' => 'user_setup', 'foreign_key' => 'location_code']);
                    array_push($foreign_tables, ['table_name' => 'user_setup', 'foreign_key' => 'from_location_code']);
                    array_push($foreign_tables, ['table_name' => 'user_setup', 'foreign_key' => 'intransit_location_code']);
                }
                foreach ($foreign_tables as $foreign_table) {
                    $count = DB::connection('company')->table($foreign_table['table_name'])->where($foreign_table['foreign_key'], $primary_key_value)->count();
                    if ($count > 0) {
                        return array('status' => 'no', 'table' => $foreign_table['table_name']);
                    }
                }
                return array('status' => 'yes', 'table' => '');
            }
        } catch (Exception $ex) {
            return array('status' => 'no', 'table' => '');
        }
    }
    public function isExistedTable($table_name)
    {
        $table = DB::connection('information_schema')->table('tables')->where('table_schema', Auth::user()->database_name)->where('table_name', $table_name)->first();
        if ($table) {
            return true;
        }
        return false;
    }
    public static function _isExistedTable($table_name)
    {
        $table = DB::connection('information_schema')->table('tables')->where('table_schema', Auth::user()->database_name)->where('table_name', $table_name)->first();
        if ($table) {
            return true;
        }
        return false;
    }
    public static function isExistedTableField($table_name, $field_name)
    {
        $table = DB::connection('information_schema')->table('columns')->where('table_schema', Auth::user()->database_name)
            ->where('table_name', $table_name)->where('column_name', $field_name)
            ->first();
        if ($table) {
            return true;
        }
        return false;
    }
    public static function _isExistedTableField($table_name, $field_name)
    {
        $table = DB::connection('information_schema')->table('columns')->where('table_schema', Auth::user()->database_name)
            ->where('table_name', $table_name)->where('column_name', $field_name)
            ->first();
        if ($table) {
            return true;
        }
        return false;
    }
    public function isAllowUpdate($table_name, $primary_key, $primary_key2 = null)
    {
        try {
            foreach ($table_name as $table_names) {
                $no = 'no';
                if ($table_names == 'v_item_ledger_entry') {
                    $no = 'item_no';
                    $criterias = 'unit_of_measure_code ="' . $primary_key2 . '"';
                } else {
                    $criterias = 'unit_of_measure ="' . $primary_key2 . '"';
                }
                if ($primary_key2 != null) {
                    $record = DB::connection('company')->table($table_names)->whereRaw($criterias)->where($no, $primary_key)->first();
                } else {
                    $record = DB::connection('company')->table($table_names)->where($no, $primary_key)->first();
                }
                if ($record) {
                    return array('status' => 'no', 'table' => $table_names);
                }
            }
            return array('status' => 'yes', 'table' => '');
        } catch (Exception $ex) {
            return array('status' => 'no', 'table' => '');
        }
    }
    public function getApiSearchCriterias($fields, $value)
    {
        $criterias = ' 1=1 ';
        if (trim($value) == '') {
            return $criterias;
        }
        $criterias .= ' AND ( ';
        foreach ($fields as $field) {
            $criterias .= 'UPPER(' . $field . ') like ' . "'%" . strtoupper($value) . "%" . "' OR ";
        }
        $criterias = rtrim($criterias, 'OR ');
        $criterias .= ' )';
        return $criterias;
    }
    public function getSelectBlank()
    {
        return 'ï¿½';
    }
    public function getCriteriasDate($filters, $app_setup)
    {
        $criterias_date = "";
        $field = new PageGroupField();
        $field->field_data_type = 'date';
        if (isset($filters['starting_date']) && $filters['starting_date'] != "") {
            $starting_date = $filters['starting_date'];
            $starting_date = $this->getSpecialConditionValue($field, $starting_date);
        } else {
            $starting_date = Carbon::now()->firstOfMonth()->toDateString();
        }
        if (isset($filters['ending_date']) && $filters['ending_date'] != "") {
            $ending_date = $filters['ending_date'];
            $ending_date =  $this->getSpecialConditionValue($field, $ending_date);
        } else {
            $ending_date = Carbon::now()->endOfMonth()->toDateString();
        }
        $location = 0;
        if (isset($filters['location'])) {
            if ($filters['location'] != '') {
                if ($app_setup->inventory_cost_setting == 'Item & Location') {
                    $criterias_date .= 'location_code = "' . $filters['location'] . '" AND ';
                }
            }
        }
        if (isset($filters['starting_date']) && $filters['starting_date'] !== '' && $filters['ending_date'] !== '') {
            $criterias_date .= 'closing_date BETWEEN "' . $starting_date . '" AND "' . $ending_date . '"';
        } else if ($filters['starting_date'] !== '') {
            $criterias_date .= 'closing_date ="' . $starting_date . '"';
        } else if (isset($filters['ending_date']) && $filters['ending_date'] !== '') {
            $criterias_date .= 'closing_date ="' . $ending_date . '"';
        } else {
            $criterias_date .= 'closing_date BETWEEN "' . Carbon::now()->firstOfMonth()->toDateString() . '" AND "' . Carbon::now()->lastOfMonth()->toDateString() . '"';
        }
        return array($criterias_date, $starting_date, $ending_date);
    }
    public static function TableValidationField($table_name, $field, $database_name, $temp_database_name)
    {
        $sql = "select * from information_schema.columns where table_schema = '" . $database_name . "' and table_name='" . $table_name . "' and column_name='" . $field->field_name . "'";
        $dbfield = DB::connection('mysql')->statement($sql);
        $sql = "select * from information_schema.columns where table_schema = '" . $temp_database_name . "' and table_name='" . $table_name . "' and column_name='" . $field->field_name . "'";
        $tempfield = DB::connection('mysql')->statement($sql);
        if (!$tempfield) {
            return false;
        }
        if (!$dbfield) {
            return false;
        }
        return true;
    }
    public static function ScriptTransferDataOfDatabase($table_name, $database_name, $temp_database_name)
    {
        if (
            $table_name == 'document_line_template' || $table_name == 'menu_department' || $table_name == 'menu_group' || $table_name == 'objects'
            || $table_name == 'page' || $table_name == 'page_group' || $table_name == 'page_group_field' || $table_name == 'page_group_field_template'
            || $table_name == 'table' || $table_name == 'table_field' || $table_name == 'table_field_url'
        ) {
            $table_field = DB::connection('mysql')->table('table_field')
                ->select('table_name', 'field_name')
                ->where('table_name', '=', $table_name)
                ->where('input_type', '<>', 'flowfield')
                ->whereNotIn('field_name', ['created_by', 'created_at', 'updated_by', 'updated_at', 'deleted_by', 'deleted_at'])
                ->get();
        } else {
            $table_field = DB::connection('mysql')->table('table_field')
                ->select('table_name', 'field_name')
                ->where('table_name', '=', $table_name)
                ->where('field_name', '<>', 'id')
                ->where('input_type', '<>', 'flowfield')
                ->whereNotIn('field_name', ['created_by', 'created_at', 'updated_by', 'updated_at', 'deleted_by', 'deleted_at'])
                ->get();
        }
        $scripts = array();
        $counter = 0;
        $query = 'INSERT INTO ' . $database_name . '.';
        $query .= $table_name . ' (';
        $insertQuery = '';
        $selectQuery = ' SELECT ';
        foreach ($table_field as $value) {
            if (\App\Services\service::TableValidationField($table_name, $value, $database_name, $temp_database_name)) {
                if ($counter == 0) {
                }
                if (
                    strtolower($value->field_name == 'separator')
                    || strtolower($value->field_name) == 'code'
                    || strtolower($value->field_name) == 'name'
                    || strtolower($value->field_name) == 'Index'
                    || strtolower($value->field_name) == 'index'
                    || strtolower($value->field_name) == 'type'
                ) {
                    $insertQuery .=  "`" . $value->field_name . "`,";
                    $selectQuery .=  "`" . $value->field_name . "`,";
                } else {
                    $insertQuery .=  $value->field_name . ',';
                    $selectQuery .=  $value->field_name . ',';
                }

                $counter += 1;
                if (count($table_field) == $counter) {
                    $query .= rtrim($insertQuery, ',') . ')' . rtrim($selectQuery, ',') . ' FROM ' . $temp_database_name . '.' . $table_name;
                    array_push($scripts, $query);
                }
            }
        }
        return $query;
    }
    public static function getItemUnitofMeasureByItemNo($item_no)
    {
        $item_uoms = null;
        if ($item_no) {
            $item_uoms = ItemUnitOfMeasure::select('unit_of_measure_code')->where('item_no', $item_no)->get();
        }
        return $item_uoms;
    }
    public function saveErrorLog($ex, $url = null, $request = null)
    {
        if ($ex->getMessage() == '' || $ex->getMessage() == 'Unauthenticated.') return;
        $error = new SystemError500();
        $error->description = $ex->getMessage();
        if (Auth::check()) {
            $error->username = Auth::user()->email;
            $error->user_id = Auth::user()->id;
            $error->database_name = Auth::user()->database_name;
        } else {
            if ($request) {
                if (isset($request->input['username'])) {
                    $username = $request->input('username');
                } else {
                    $error->username = $request->email;
                    $error->user_id = $request->id;
                    $error->database_name = $request->database_name;
                }
            }
        }
        $error->log_date = Carbon::now()->toDateString();
        $error->log_datetime = Carbon::now()->toDateTimeString();
        $error->url = $ex->getFile();
        $error->line_no = $ex->getLine();
        $error->save();
        $thriedays_later = Carbon::now()->subDays(3);
        SystemError500::where("log_date","<", $thriedays_later)->delete();
        $is_send_telegram = true;
        $msg = "";
        if(strstr($ex->getMessage(), 'try restarting transaction')){
            $db_name = Auth::user()->database_name;
            $restarting_log = SystemError500::where("database_name", $db_name)->where("log_date", Carbon::now()->toDateString())->where("username", Auth::user()->email)->where("description", $ex->getMessage())->count();
            if($restarting_log == 10){
                SystemError500::where("database_name", $db_name)->where("log_date", Carbon::now()->toDateString())->where("username", Auth::user()->email)->where("description", $ex->getMessage())->delete();
                $msg = "This company may be log MySQL transactions please check and clear the transaction!";
            }else{
                $is_send_telegram = false;
            }
            
        }  
        if (config('app.env') == 'production' && $is_send_telegram) $this->sendMessageTelegramBot($ex,$url, $msg);
        
    }
    public function saveError($msg)
    {
        $error = new SystemError500();
        $error->username = Auth::user()->email;
        $error->user_id = Auth::user()->id;
        $error->database_name = Auth::user()->database_name;
        $error->log_date = Carbon::now()->toDateString();
        $error->log_datetime = Carbon::now()->toDateTimeString();
        $error->description = $msg;
        $error->save();
        
    }
    public function sendMessageTelegramBot($ex,$url = '', $msg="")
    {
        if(!Auth::user()) return;
        $db_name = (Auth::user())? Auth::user()->database_name: "";
        $organization = Organizations::selectRaw("resource_name")->where('database_name', $db_name)->first();
        $support_by = ($organization)? $organization->resource_name : "";
        $bot_api = "https://api.telegram.org";
        $telegram_id = env('TELEGRAM_ID_CLOULD_ERROR');
        $telegram_token = env('TELEGRAM_TOKEN_ERROR');

        $apiUri = sprintf('%s/bot%s/%s', $bot_api, $telegram_token, 'sendMessage');
        $text = "User Name: ".Auth::user()->email;
        $text .= "\nServer: ".config('app.server_name');
        $text .= "\nFrom FN: ".$url;
        $text .= "\nFrom URL: ". request()->path();
        $text .= "\nSupport By: $support_by";
        $text .= "\nDatabase Name: ".Auth::user()->database_name;
        $text .= "\nURL: ".$ex->getFile();
        $text .= "\nError Line Number: ".$ex->getLine();
        if($msg) $text .= "\nError Message: ".$msg;
        else $text .= "\nError Message: ".$ex->getMessage();
        
        $params = [
            'chat_id' => $telegram_id,
            'text' => $text       
        ];

        $headers = [
            'Content-Type: application/json'
        ];
        
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $apiUri);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($params));
        $result = curl_exec($ch);
        curl_close($ch);
    
        return $result;
    }
    public function savePostingErrorLog($error_desc)
    {
        $error = new SystemError500();
        $error->description = $error_desc;
        if (Auth::check()) {
            $error->username = Auth::user()->email;
            $error->user_id = Auth::user()->id;
            $error->database_name = Auth::user()->database_name;
        }
        $error->log_date = Carbon::now()->toDateString();
        $error->log_datetime = Carbon::now()->toDateTimeString();
        $error->save();
    }
    public static function saveErrorLogV2($ex, $url = null, $request = null)
    {
        if ($ex->getMessage() == '' || $ex->getMessage() == 'Unauthenticated.') return;
        $error = new SystemError500();
        $error->description = $ex->getMessage();
        if (Auth::check()) {
            $error->username = Auth::user()->email;
            $error->user_id = Auth::user()->id;
            $error->database_name = Auth::user()->database_name;
        } else {
            if ($request) {
                if (isset($request->input['username'])) {
                    $username = $request->input('username');
                    $user = User::where('email', $username)->first();
                } else {
                    $error->username = $request->email;
                    $error->user_id = $request->id;
                    $error->database_name = $request->database_name;
                }
            }
        }

        $error->log_date = Carbon::now()->toDateString();
        $error->log_datetime = Carbon::now()->toDateTimeString();
        $error->url = $ex->getFile();
        $error->line_no = $ex->getLine();
        $error->save();
    }
    public static function saveErrorLogV2Email($ex, $url = null, $request = null)
    {
        if ($ex->getMessage() == '' || $ex->getMessage() == 'Unauthenticated.') return;
        $error = new SystemError500();
        $error->description = $ex->getMessage();
        if (Auth::check()) {
            $error->username = Auth::user()->email;
            $error->user_id = Auth::user()->id;
            $error->database_name = Auth::user()->database_name;
        } else {
            if ($request) {
                if (isset($request->input['username'])) {
                    $username = $request->input('username');
                    $user = User::where('email', $username)->first();
                } else {
                    $error->username = $request->email;
                    $error->user_id = $request->id;
                    $error->database_name = $request->database_name;
                }
            }
        }

        $error->log_date = Carbon::now()->toDateString();
        $error->log_datetime = Carbon::now()->toDateTimeString();
        $error->url = $ex->getFile();
        $error->line_no = $ex->getLine();
        $error->save();
        if (config('app.env') == 'production' && $error->description == 'Unauthenticated') {
            $error_report_to_user_id = config('app.error_report_to_user_id');
            $user = User::where('id', $error_report_to_user_id)->first();
            if ($user) {
                // Mail::to($user->email)->send(new SendErrorReport($error));
            }
        }
    }
    public function itemAvailabilityCondiction($qty_as)
    {
        if ($qty_as == 'Qty. as Stock Unit') {
        } elseif ($qty_as == 'Qty. as Sale Unit') {
        } elseif ($qty_as == 'Qty. as Purchase Unit') {
        }
    }
    public function imageWorker($path, $wmax, $hmax)
    {
        $img_max_size = config('app.img_max_size');
        $img_max_quality = config('app.img_max_quality');

        // ini_set('--enable-exif', true);
        $img = Image::make($path)->encode('jpg', $img_max_quality);
        // $img = Image::make($path)->encode('jpg',$img_max_quality);   
        // exif_imagetype($img);


        if ($wmax == null && $hmax == null) {
            $wmax = config('app.img_max_width');
            $hmax = config('app.img_max_height');
            if ($img->filesize() > $img_max_size * 1024) {
                if ($img->width() > $img->height()) {
                    $wmax = ($img->width() > $wmax) ? $wmax : $img->width();
                    $img->resize($wmax, null, function ($constraint) {
                        $constraint->aspectRatio();
                    });
                } else {
                    $hmax = ($img->height() > $hmax) ? $hmax : $img->height();
                    $img->resize(null, $hmax, function ($constraint) {
                        $constraint->aspectRatio();
                    });
                }
                $img->save($path, $img_max_quality);
            }
        } else {
            if ($wmax != null && $hmax == null) {
                $img->resize($wmax, null, function ($constraint) {
                    $constraint->aspectRatio();
                });
                $img->save($path, $img_max_quality);
            } else if ($wmax == null && $hmax != null) {
                $img->resize(null, $hmax, function ($constraint) {
                    $constraint->aspectRatio();
                });
                $img->save($path, $img_max_quality);
            } else {

                $img->resize($wmax, $hmax);
                $img->save($path, $img_max_quality);
            }
        }
        $img->destroy();

        return $path;
    }
    public function imageWorkereCommerce($path, $wmax, $hmax)
    {
        $img_max_size = config('app.img_max_size');
        $img_max_quality = config('app.img_max_quality');
        $img = Image::make($path)
            ->encode('jpg', $img_max_quality);
        if ($wmax == null && $hmax == null) {
            $wmax = config('app.img_max_width');
            $hmax = config('app.img_max_height');
            if ($img->filesize() > $img_max_size * 1024) {
                if ($img->width() > $img->height()) {
                    $wmax = ($img->width() > $wmax) ? $wmax : $img->width();
                    $img->resize($wmax, null, function ($constraint) {
                        $constraint->aspectRatio();
                    });
                } else {
                    $hmax = ($img->height() > $hmax) ? $hmax : $img->height();
                    $img->resize(null, $hmax, function ($constraint) {
                        $constraint->aspectRatio();
                    });
                }
                $img->save($path, $img_max_quality);
            }
        } else {
            if ($wmax != null && $hmax == null) {
                $img->resize($wmax, null, function ($constraint) {
                    $constraint->aspectRatio();
                });
                $img->save($path, $img_max_quality);
            } else if ($wmax == null && $hmax != null) {
                $img->resize(null, $hmax, function ($constraint) {
                    $constraint->aspectRatio();
                });
                $img->save($path, $img_max_quality);
            } else {
                $img->resize($wmax, $hmax);
                $img->save($path, $img_max_quality);
            }
        }
        $img->destroy();
        return $path;
    }
    public function timeformat($value)
    {
        $result = '';
        $count_digi = strlen($value);
        if ($count_digi > 8) {
            return 'false';
        }
        if (strpos($value, ':') !== false) {
            $expl = explode(':', $value);
            if (count($expl) > 3) {
                return 'false';
            }
            if (count($expl) == 2) {
                if ($expl[0] > 24 || $expl[1] > 60) {
                    return 'false';
                }
                $result = Carbon::createFromTime($expl[0], $expl[1], 00);
            } elseif (count($expl) == 3) {
                if ($expl[0] > 24 || $expl[1] > 60 || $expl[2] > 60) {
                    return 'false';
                }
                $result = Carbon::createFromTime($expl[0], $expl[1], $expl[2]);
            }
        } else {
            if (strlen($value) == 1) {
                if ($value == 0) {
                    return 'false';
                }
                $result = Carbon::createFromTime($value, 00, 00);
            } elseif (strlen($value) == 2) {
                if ($value > 24) {
                    return 'false';
                }
                $result = Carbon::createFromTime($value, 00, 00);
            } elseif (strlen($value) == 3) {
                $sub = substr($value, 2, 1);
                $sub_first = substr($value, 0, 2);
                if ($sub_first > 24) {
                    return 'false';
                }
                $result = Carbon::createFromTime($sub_first, $sub, 00);
            } elseif (strlen($value) == 4) {
                $sub_first = substr($value, 0, 2);
                $sub_minute = substr($value, 2, 2);
                if ($sub_first > 24 || $sub_minute > 60) {
                    return 'false';
                }
                $result = Carbon::createFromTime($sub_first, $sub_minute, 00);
            } elseif (strlen($value) == 5) {
                $sub_first = substr($value, 0, 2);
                $sub_minute = substr($value, 2, 2);
                $sub_second = substr($value, 4, 1);
                if ($sub_second < 0 || $sub_first > 24 || $sub_minute > 60) {
                    return $result = 'false';
                }
                $result = Carbon::createFromTime($sub_first, $sub_minute, $sub_second);
            } elseif (strlen($value) == 6) {
                $sub_first = substr($value, 0, 2);
                $sub_minute = substr($value, 2, 2);
                $sub_second = substr($value, 4, 2);
                if ($sub_second > 60 || $sub_first > 24 || $sub_minute > 60) {
                    return $result = 'false';
                }
                $result = Carbon::createFromTime($sub_first, $sub_minute, $sub_second);
            }
        }
        return Carbon::parse($result)->format('H:i');
    }
    public function UploadExcelFile($allow = false)
    {
        if (!$allow) {
            if (Auth::user()->user_type != 'Full User' && !$allow) {
                return 'false';
            }
        }
        try {
            $upload_dir = config('app.upload_dir');
            $upload_temp = config('app.upload_temp_import');
            $database_name = config('database.connections.company.database');
            $file_path = "upload/" . $upload_dir;
            if (!file_exists($file_path)) {
                mkdir($file_path, 0777, true);
            }
            $file_path_company = $file_path . "/" . $database_name;
            if (!file_exists($file_path_company)) {
                mkdir($file_path_company, 0777, true);
            }
            /*$file_path_company_item = $file_path_company."/customer/".$code."/default";
            if (!file_exists($file_path_company_item)) {
                mkdir($file_path_company_item, 0777, true);
            }*/
            $file_path_company_temp = $file_path_company . "/" . $upload_temp;
            if (!file_exists($file_path_company_temp)) {
                mkdir($file_path_company_temp, 0777, true);
            }
            // =============== DELETE EXISTING USER PROFILE PICTURE ==========
            $files = scandir($file_path_company_temp . "/");
            foreach ($files as $file) {
                if ($file != '.' && $file != '..') {
                    if (file_exists($file_path_company_temp . "/" . $file)) {
                        unlink($file_path_company_temp . "/" . $file);
                    }
                }
            }
            // ===============================================================
            $fileName = $_FILES["file"]['name'];
            $fileTmpLoc = $_FILES["file"]["tmp_name"];
            $kaboom = explode(".", $fileName);
            $fileExt = end($kaboom);
            $token = openssl_random_pseudo_bytes(20);
            $token = bin2hex($token);
            $fname = $token . '.' . $fileExt;
            $moveResult = move_uploaded_file($fileTmpLoc, $file_path_company_temp . "/" . $fname);
            return 'true';
        } catch (Exception $ex) {
            return 'false';
        }
    }

    public function DoImportExcel($values, $table_name, $data, $modal_name, $ref = '', $header = null)
    {
        ini_set('max_execution_time', 3600);
        ini_set('memory_limit', '1G');
        //purshser init.
        $options = array(
            'cluster' => 'ap1',
            'encrypted' => true
        );
        $pusher = new Pusher(
            config('app.pusher_key'),
            config('app.pusher_secret'),
            config('app.pusher_id'),
            $options
        );
        if (Auth::user()->user_type != 'Full User') return 'false';

        try {
            $pri_key = TableField::select('field_name')->where('table_name', $table_name)
                ->where('primary_key', 'PRI')
                ->where('input_type', '<>', 'flowfield')
                ->first();
            
            $field_pri_key = $pri_key->field_name;
            $excel_fiels = array();
            foreach ($values as $x => $x_value) {
                if (strpos($x, '.') !== false) {
                    $x = trim(str_replace('.', '', $x), ' ');
                }
                array_push($excel_fiels, $x);
            }
            if (!in_array($field_pri_key, $excel_fiels)) {
                array_push($excel_fiels, $field_pri_key);
            }

            $table_fields = TableField::select('field_name', 'field_data_type', 'input_type', 'primary_key', 'extra', 'mandatory', 'max_length')
                ->where('table_name', $table_name)
                ->where('input_type', '<>', 'flowfield')
                ->where('read_only', '=', 'No')
                ->whereIn('field_name', $excel_fiels)
                ->orderBy('index')->get();
            //=============================== PROCESSING INSERT/UPDATE DATA INTO DATABASE =================
            $no_of_succeed_row = 0;
            $no_of_push = 0;
            $excel_line_no = 1;
            $count_row_excel = count($data->toArray());
            foreach ($data->toArray() as $values) {
                $is_continue = false; 
                $primary_key_value = service::toCode(trim($values[$field_pri_key], ' '));
                if ($primary_key_value == '' || $primary_key_value == null) {
                    continue;
                }
                //============================ Processing '.......'
                $no_of_succeed_row++;
                $no_of_succeed_row_percentage = ' ' . number_format($no_of_succeed_row / $count_row_excel * 100, 0);
                if ($no_of_push != $no_of_succeed_row_percentage) {
                    $data['message'] = trans('greetings.Processing') . ' -> ' . $no_of_succeed_row_percentage . ' %';
                    $pusher->trigger('upload.excel.' . Auth::user()->id, 'upload_excel', $data['message']);
                }

                $no_of_push = $no_of_succeed_row_percentage;
                $record = $modal_name::where($field_pri_key, $primary_key_value)->first();

                if(!$record && $table_name == 'item_sku'){
                    $is_continue = true; 
                    $record = ItemSKU::where('item_no',$values['item_no'])->where('location_code',$values['location_code'])->first();
                }
                
                if ($record) {
                    if ($table_name == 'salesperson_schedule') {
                        if ($record->status != 'Scheduled') {
                            continue;
                        }
                    }
                    if ($table_name == 'purchase_line' || $table_name == 'sales_line') {
                        if (isset($values['type'])) {
                            if ($values['type'] == 'Item') {
                                $item = Item::select('no')->where('no', $record->no)->first();
                                if (!$item) {
                                    $description = "Item no : '" . $values['no'] . "' is not found!";
                                    $this->SaveExcelUploadLog("purchase_line", $description);
                                    continue;
                                }
                            } elseif ($values['type'] == 'G/L Account') {
                                //=================being get from chart of account
                                $gl_account = ChartOfAccount::where('no', $record->no)->first();
                                if (!$gl_account) {
                                    $description = "No : '" . $values['no'] . "' G/L Account Not Found";
                                    $this->SaveExcelUploadLog("Purchase Line", $description);
                                    continue;
                                }
                            } elseif ($values['type'] == 'Item Charge') {
                                //=================being get from item charge
                                $item_charge = ItemCharge::where('code', $record->no)->first();
                                if (!$item_charge) {
                                    $description = "No : '" . $values['no'] . "' Item Charge Not Found";
                                    $this->SaveExcelUploadLog("item_charge", $description);
                                    continue;
                                }
                            } elseif ($values['type'] == 'Fix Asset') {
                                //=================being get from fix asset
                                $fix_asset = FixAsset::where('no', $record->no)->first();
                                if (!$fix_asset) {
                                    $description = "No : '" . $values['no'] . "' Fix Asset Not Found";
                                    $this->SaveExcelUploadLog("fixasset", $description);
                                    continue;
                                }
                            } else {
                                $description = "No : '" . $values['no'] . "' type not found!";
                                $this->SaveExcelUploadLog("purchase line", $description);
                                continue;
                            }
                        } else {
                            $item = Item::select('no')->where('no', $values['no'])->first();
                            if (!$item) {
                                $description = "Item no : '" . $values['no'] . "' is not found!";
                                $this->SaveExcelUploadLog("purchase_line", $description);
                                continue;
                            }
                        }
                    }
                    if($table_name == 'item_sku' && $is_continue) {
                        $description = "Item no : '" . $record->item_no . ' in location code : ' . $record->location_code . ' is already exit.';
                        $this->SaveExcelUploadLog("item_sku", $description);
                        continue; 
                    }else if ($table_name == "item" && isset($values['item_tracking_code']) && $values['item_tracking_code'] != "") {
                        if($record && $record->item_tracking_code == ""){
                            $description = "System not allow to change this item to ".$values['item_tracking_code'];
                            $this->SaveExcelUploadLog('Item', $description);
                            continue; 
                        }
                    }

                    foreach ($table_fields as $table_field) {
                        if ($table_field->field_name != $field_pri_key) {
                            $field_name = $table_field->field_name;
                            if (!array_key_exists($table_field->field_name, $values)) continue;
                            $value = html_entity_decode(preg_replace("/U\+([0-9A-F]{4})/", "&#x\\1;", trim($values[$table_field->field_name], ' ')), ENT_NOQUOTES, 'UTF-8');
                            
                            // ============ sub string by field length ================                                
                            if ($table_field->max_length) {
                                $value = mb_substr($value, 0, $table_field->max_length);
                            }
                            if ($table_field->field_data_type == 'date') {
                                $field = new PageGroupField();
                                $field->field_data_type = 'date';
                                if ($value == null || $value == '') {
                                    if ($field_name == 'ending_date' || $field_name == 'termindate_date') {
                                        $format_date = '2500-01-01';
                                    } else {
                                        $format_date = '1900-01-01';
                                    }
                                } else {
                                    try {
                                        $format_date = Carbon::parse($value)->toDateString();
                                    } catch (\Exception $ex) {
                                        $description = "Line no.: " . $excel_line_no . " Field '" . $field_name . "' input date incorrect!";
                                        $this->SaveExcelUploadLog($table_field->table_name, $description);
                                    }
                                }
                                $record->$field_name = $format_date;
                            } else if ($table_field->field_data_type == 'datetime') {
                                $record->$field_name = Carbon::parse($values[$table_field->field_name])->toDateTimeString();
                            } else if ($table_field->field_data_type == 'decimal') {
                                // ================ Create Log Warning ===================
                                try {
                                    $record->$field_name = $this->toDouble($value);
                                } catch (\Exception $ex) {
                                    $excel_line_no += 1;
                                    $description = "Line no.: " . $excel_line_no . " Field '" . $field_name . "' input value incorrect!";
                                    $this->SaveExcelUploadLog($table_field->table_name, $description);
                                }
                            } else if ($table_field->field_data_type == 'time') {
                                $result = $this->timeformat($value);
                                if ($result == 'false') {
                                    $record->$field_name = '';
                                } else {
                                    $record->$field_name = $result;
                                }
                            } else {
                                if ($table_field->field_name == 'checkbox') {
                                    if ($value != 'Yes') {
                                        $record->$field_name = 'No';
                                    } else {
                                        $record->$field_name = 'Yes';
                                    }
                                } else {
                                    $record->$field_name = $value;
                                }
                            }
                            if ($table_name == 'item_forecast_line') {
                                if ($header->starting_date != '' && $header->ending_date != '') {
                                    if ($header->view_by == 'Year') {
                                        $starting = Carbon::parse($header->starting_date)->firstOfYear();
                                        for ($i = 1; $i <= 12; $i++) {
                                            $excel_date = 'month_' . $i;
                                            $year =  Carbon::parse($starting)->year;
                                            $demand_date = Carbon::parse("$year-$i-1")->endOfMonth()->toDateString();
                                            // $demand_date = Carbon::parse("$year-$i-1")->toDateString();
                                            $item_line_qty = ItemForecastLineQuantity::where('header_id', $header->id)->where('demand_date', $demand_date)
                                                ->where('forecast_type', $header->forecast_type)->where('line_no', $record->line_no)->first();

                                            if ($item_line_qty) {
                                                if ($values[$excel_date]) {
                                                    $item_line_qty->quantity = $values[$excel_date];
                                                    $item_line_qty->unit_of_measure_code = $record->unit_of_measure_code;
                                                    $item_line_qty->qty_per_unit_of_measure = $record->qty_per_unit_of_measure;
                                                    if ($item_line_qty->qty_per_unit_of_measure) {
                                                        $item_line_qty->quantity_base = service::toDouble($item_line_qty->quantity) * service::toDouble($item_line_qty->qty_per_unit_of_measure);
                                                    } else {
                                                        $item_line_qty->quantity_base = service::toDouble($item_line_qty->quantity) * 1;
                                                    }
                                                } else {
                                                    $item_line_qty->quantity = 0;
                                                    $item_line_qty->quantity_base = 0;
                                                }
                                                $item_line_qty->save();
                                            } else {
                                                if ($values[$excel_date]) {
                                                    $item_line_qty = new ItemForecastLineQuantity();
                                                    $item_line_qty->header_id = $header->id;
                                                    $item_line_qty->forecast_type = $header->forecast_type;
                                                    $item_line_qty->forecast_option = $header->forecast_option;
                                                    $item_line_qty->line_no = $record->line_no;
                                                    $item_line_qty->quantity = $values[$excel_date];
                                                    if ($record->unit_of_measure_code) {
                                                        $item_line_qty->unit_of_measure_code = $record->unit_of_measure_code;
                                                        $item_line_qty->qty_per_unit_of_measure = $record->qty_per_unit_of_measure;
                                                        $item_line_qty->quantity_base = $values[$excel_date] * $record->qty_per_unit_of_measure;
                                                    } else {
                                                        $item_line_qty->qty_per_unit_of_measure = 1;
                                                        $item_line_qty->quantity_base = $values[$excel_date] * $item_line_qty->qty_per_unit_of_measure;
                                                    }
                                                    $item_line_qty->view_by = 'Year';
                                                    $item_line_qty->demand_date = $demand_date;
                                                    $item_line_qty->save();
                                                }
                                            }
                                        }
                                    }
                                }
                            }
                        }
                    }
                    // validate field for import
                    foreach ($table_fields as $table_field) {
                        $table_field->excel_fields = $excel_fiels;
                        if(method_exists($record,"validate_field")) {
                            $record->validate_field($table_field, $header);
                        }
                    }

                    if ($table_name == 'purchase_line') {
                        $record->currency = $header->currency;
                        $record->CalculatePurchaseAmount();
                    }

                    $record->save();
                } else {
                    $this->NewRecordImport($table_fields, $field_pri_key, $values, $modal_name, $ref, $header, $excel_fiels, $primary_key_value, $excel_line_no);
                }
            }

            return 'true';
        } catch (\PDOException $ex) {
            $this->SaveExcelUploadLog('DoUpload', $ex->getMessage() . " : line no :" . $ex->getLine());
            $this->saveErrorLog($ex);
            return 'false';
        } catch (\Exception $ex) {
            $this->SaveExcelUploadLog('DoUpload', $ex->getMessage() . " : line no :" . $ex->getLine());
            $this->saveErrorLog($ex);
            return 'false';
        }
    }
    public function NewRecordImport($table_fields, $field_pri_key, $values, $modal_name, $ref = '', $header = null, $excel_fiels, $primary_key_value = null, $excel_line_no)
    {
        try {
            $app_setup = ApplicationSetup::first();
            $record = new $modal_name();
            $new_pri_value = null;
            if (strtoupper($primary_key_value) == '_AUTO') {
                if ($ref) $new_pri_value = service::generateNo('', $ref);
            } else {
                $new_pri_value = $primary_key_value;
            }

            $record->$field_pri_key = $new_pri_value;
            // ============ system default value ================                
            if ($modal_name == 'App\Models\Administration\ApplicationSetup\Item') {
                if (isset($values['identifier_code'])) {
                    if ($values['identifier_code'] != '') {
                        $identifier_code = service::toCode(trim($values['identifier_code'], ' '));
                        $identifier = Item::where('identifier_code', $identifier_code)->first();
                        if ($identifier) {
                            $description = "Field identifier_code " . $identifier->identifier_code . " duplicated with item no " . $values['no'];
                            $this->SaveExcelUploadLog('Item', $description);
                            return 'false';
                        }
                    }
                }
                $record->inv_posting_group_code = $app_setup->default_inv_posting_group;
                $record->gen_prod_posting_group_code = $app_setup->default_gen_prod_posting_group;
                $record->vat_prod_posting_group_code = $app_setup->default_vat_prod_posting_group;
                if (isset($values['stock_uom_code'])) {
                    $record->stock_uom_code = $values['stock_uom_code'];
                    $record->sales_uom_code = $values['stock_uom_code'];
                    $record->purchase_uom_code = $values['stock_uom_code'];
                } else {
                    if ($app_setup->default_stock_unit_measure) {
                        $record->stock_uom_code = $app_setup->default_stock_unit_measure;
                        $record->sales_uom_code = $app_setup->default_stock_unit_measure;
                        $record->purchase_uom_code = $app_setup->default_stock_unit_measure;
                    }
                }
            } elseif ($modal_name == 'App\Models\Financial\Setup\Customer') {
                $record->payment_term_code = $app_setup->default_payment_term;
                $record->rec_posting_group_code = $app_setup->default_ar_posting_group;
                $record->vat_posting_group_code = $app_setup->default_vat_bus_posting_group;
                $record->gen_bus_posting_group_code = $app_setup->default_gen_bus_posting_group;
                $record->price_include_vat = $app_setup->default_item_price_include_vat;
            } elseif ($modal_name == 'App\Models\Financial\Setup\Customer') {
                $record->payment_term_code = $app_setup->default_payment_term;
                $record->rec_posting_group_code = $app_setup->default_ar_posting_group;
                $record->vat_posting_group_code = $app_setup->default_vat_bus_posting_group;
                $record->gen_bus_posting_group_code = $app_setup->default_gen_bus_posting_group;
                $record->price_include_vat = $app_setup->default_item_price_include_vat;
            } elseif ($modal_name == 'App\Models\Financial\Setup\CustomerTimelineHistory') {
                $customer = Customer::where('no', $values['no'])->first();
                if (!$customer) {
                    $description = "Customer: " . $values['no'] . ' not found!';
                    $this->SaveExcelUploadLog('Customer', $description);
                    return 'false';
                }
                $is_existed = CustomerTimelineHistory::where('no', $values['no'])->where('salesperson_code', $values['salesperson_code']);
                if (isset($values['ship_to_code'])) $is_existed = $is_existed->where('ship_to_code', $values['ship_to_code']);
                $is_existed = $is_existed->first();
                if ($is_existed) {
                    $description = "Customer: " . $values['no'] . ' and Salesperson:' . $values['salesperson_code'];
                    if (isset($values['ship_to_code'])) $description .= "Ship to Code: " . $values['ship_to_code'];
                    $description .= ' is already existed!';
                    $this->SaveExcelUploadLog('Customer', $description, $record);
                    return 'false';
                }
            
            } elseif ($modal_name == 'App\Models\Purchase\Transaction\TransferLine') {
                $item = Item::where('no', $values['no'])->first();
                if (!$item) {
                    $description = "Item: " . $values['no'] . ' not found!';
                    $this->SaveExcelUploadLog('Item', $description, $record);
                    return 'false';
                }

                $lines = TransferLine::select('line_no')->where('document_no',$header->no)->max('line_no');
                $line_no = ($lines) ? $lines : 0;
                $line_no = $line_no + 10000;
                
                $record->line_no = $line_no;
                $record->quantity_to_ship = service::toDouble($record->quantity) - service::toDouble($record->quantity_shipped);
                $record->quantity_to_receive = service::toDouble($record->quantity) - service::toDouble($record->quantity_received);

            } elseif ($modal_name == 'App\Models\Administration\ApplicationSetup\ItemUnitOfMeasure') {
                // b den request check item 
                $item = Item::where('no',$values['item_no'])->where('inactived', '<>', 'Yes')->first();
                if(!$item){
                    $description = "This item no ".$values['item_no']. " doesn't exit.";
                    $this->SaveExcelUploadLog('ItemUnitOfMeasure', $description, $record);
                    return 'false';
                }
                if (isset($values['identifier_code'])) {
                    if ($values['identifier_code'] != '') {
                        $identifier_code = service::toCode(trim($values['identifier_code'], ' '));
                        $identifier = ItemUnitOfMeasure::where('identifier_code', $identifier_code)->first();
                        if ($identifier) {
                            $description = "Field identifier_code " . $identifier->identifier_code . " duplicated with item no " . $identifier->item_no;
                            $this->SaveExcelUploadLog('Item', $description, $record);
                            return 'false';
                        }
                    }
                }
                $item_uom = ItemUnitOfMeasure::where('item_no', $values['item_no'])->where('unit_of_measure_code', $values['unit_of_measure_code'])->first();
                if ($item_uom) {
                    $description = "item UOM already exsit! item no " . $item_uom->item_no . " with Unit of Measure Code" . $item_uom->unit_of_measure_code;
                    $this->SaveExcelUploadLog('ItemUnitOfMeasure', $description, $record);
                    return 'false';
                }
            } elseif ($modal_name == 'App\Models\Purchase\Transaction\PurchaseLine') {
                $record->document_type = $header->document_type;
                $record->document_no = $header->no;

                $record->vendor_no = $header->vendor_no;
                $record->location_code = $header->location_code;
                if ($header->currency_code) {
                    $record->currency_code = $header->currency_code;
                    $record->currency_factor = $header->currency_factor;
                } else {
                    $record->currency_factor = 1;
                }
                $record->store_code = $header->store_code;
                $record->division_code = $header->division_code;
                $record->business_unit_code = $header->business_unit_code;
                $record->department_code = $header->department_code;
                $record->project_code = $header->project_code;
                $record->purchaser_code = $header->purchaser_code;
                $record->request_receipt_date = $header->request_receipt_date;
                $record->gen_bus_posting_group_code = $header->gen_bus_posting_group_code;
                $record->vat_bus_posting_group_code = $header->vat_bus_posting_group_code;
                $purchase_line = PurchaseLine::select('line_no')->where('document_no',$header->no)->max('line_no');
                $refer_line = PurchaseLine::select('refer_line_no')->where('document_no',$header->no)->max('refer_line_no');

                $refer_line_no = ($refer_line) ? $refer_line : 0;
                $refer_line_no = $refer_line_no + 1;
                $line_no = ($purchase_line) ? $purchase_line : 0;
                $line_no = $purchase_line + 10000;
                $record->line_no = $line_no;
                $record->refer_line_no = $refer_line_no;
                $no_value = html_entity_decode(preg_replace("/U\+([0-9A-F]{4})/", "&#x\\1;", trim($values['no'], ' ')), ENT_NOQUOTES, 'UTF-8');
                if (isset($values['type'])) {
                    if ($values['type'] == 'Item') {
                        $item = Item::where('no', $no_value)->first();
                        if (!$item) {
                            $description = "Item no : '" . $values['no'] . "' is not found!";
                            $this->SaveExcelUploadLog("Purchase Line", $description, $record);
                            return 'false';
                        }
                        $record = $this->TypeItemUploadExcel($values, $item, $record, $header);
                    } elseif ($values['type'] == 'G/L Account') {
                        //=================being get from chart of account
                        $gl_account = ChartOfAccount::where('no', $no_value)->first();
                        if (!$gl_account) {
                            $description = "No : '" . $values['no'] . "' G/L Account Not Found";
                            $this->SaveExcelUploadLog("Purchase Line", $description, $record);
                            return 'false';
                        }
                        if ($gl_account) {
                            $record->no = $gl_account->no;
                            $record->description = $gl_account->description;
                            $record->description_2 = $gl_account->description_2;
                            $record->vat_prod_posting_group_code = $gl_account->vat_prod_posting_group_code;
                            $record->gen_prod_posting_group_code = $gl_account->gen_prod_posting_group_code;
                            $record->qty_per_unit_of_measure = 1;
                            //            being check VAT
                            $vendor = Vendor::select('vat_bus_posting_group_code')->where('no', $header->vendor_no)->first();
                            if ($vendor->vat_bus_posting_group_code && $gl_account->vat_prod_posting_group_code) {
                                $vat_post_group = VatPostingSetup::select('vat_calculation_type', 'vat_amount')
                                    ->where('vat_bus_posting_group', $vendor->vat_bus_posting_group_code)
                                    ->where('vat_prod_posting_group', $gl_account->vat_prod_posting_group_code)->first();
                                if ($vat_post_group) {
                                    $record->vat_calculation_type = $vat_post_group->vat_calculation_type;
                                    $record->vat_percentage = $vat_post_group->vat_amount;
                                }
                                $record->vat_bus_posting_group_code = $header->vat_bus_posting_group_code;
                                $record->gen_bus_posting_group_code = $header->gen_bus_posting_group_code;
                                $record->vat_bus_posting_group_code = $header->vat_bus_posting_group_code;
                            }
                            //            end check VAT
                            $record->type = 'G/L Account';
                            $record->created_by = Auth::user()->email;
                            $record->unit_of_measure = 'UNIT';
                            $record->division_code = $header->division_code;
                            $record->business_unit_code = $header->business_unit_code;
                            $record->department_code = $header->department_code;
                            $record->project_code = $header->project_code;
                        }
                    } elseif ($values['type'] == 'Item Charge') {
                        //=================being get from item charge
                        $item_charge = ItemCharge::where('code', $no_value)->first();
                        if (!$item_charge) {
                            $description = "No : '" . $values['no'] . "' Item Charge Not Found";
                            $this->SaveExcelUploadLog("item_charge", $description, $record);
                            return 'false';
                        }
                        if ($item_charge) {
                            $record->no = $item_charge->code;
                            $record->description = $item_charge->description;
                            $record->description_2 = $item_charge->description_2;
                            $record->vat_prod_posting_group_code = $item_charge->vat_prod_posting_group_code;
                            $record->gen_prod_posting_group_code = $item_charge->gen_prod_posting_group_code;
                            $record->qty_per_unit_of_measure = 1;
                            $record->unit_of_measure = 'UNIT';
                            $record->division_code = $item_charge->division_code ? $item_charge->division_code : $header->division_code;
                            $record->business_unit_code = $item_charge->business_unit_code ? $item_charge->business_unit_code : $header->business_unit_code;
                            $record->department_code = $item_charge->department_code ? $item_charge->department_code : $header->department_code;
                            $record->project_code = $item_charge->project_code ? $item_charge->project_code : $header->project_code;
                        }
                        //            being check VAT
                        $vendor = Vendor::select('vat_bus_posting_group_code')->where('no', $header->vendor_no)->first();
                        if ($vendor->vat_bus_posting_group_code && $item_charge->vat_prod_posting_group_code) {
                            $vat_post_group = VatPostingSetup::select('vat_calculation_type', 'vat_amount')
                                ->where('vat_bus_posting_group', $vendor->vat_bus_posting_group_code)
                                ->where('vat_prod_posting_group', $item_charge->vat_prod_posting_group_code)->first();
                            if ($vat_post_group) {
                                $record->vat_calculation_type = $vat_post_group->vat_calculation_type;
                                $record->vat_percentage = $vat_post_group->vat_amount;
                            }
                            $record->vat_bus_posting_group_code = $header->vat_bus_posting_group_code;
                            $record->gen_bus_posting_group_code = $header->gen_bus_posting_group_code;
                            $record->vat_bus_posting_group_code = $header->vat_bus_posting_group_code;
                        }
                        //            end check VAT
                        $record->type = 'Item Charge';
                        $record->created_by = Auth::user()->email;
                    } elseif ($values['type'] == 'Fix Asset') {
                        //=================being get from fix asset
                        $fix_asset = FixAsset::where('no', $no_value)->first();
                        if (!$fix_asset) {
                            $description = "No : '" . $values['no'] . "' Fix Asset Not Found";
                            $this->SaveExcelUploadLog("fixasset", $description);
                            return 'false';
                        }
                        if ($fix_asset) {
                            $record->no = $fix_asset->no;
                            $record->description = $fix_asset->description;
                            $record->description_2 = $fix_asset->description_2;
                            $record->qty_per_unit_of_measure = 1;
                            $record->unit_of_measure = 'UNIT';
                            $record->division_code = $fix_asset->division_code ? $fix_asset->division_code : $header->division_code;
                            $record->business_unit_code = $fix_asset->business_unit_code ? $fix_asset->business_unit_code : $header->business_unit_code;
                            $record->department_code = $fix_asset->department_code ? $fix_asset->department_code : $header->department_code;
                            $record->project_code = $fix_asset->project_code ? $fix_asset->project_code : $header->project_code;
                            $record->posting_group = $fix_asset->fa_posting_group_code;
                            $record->vat_prod_posting_group_code = $fix_asset->vat_prod_posting_group_code;
                        }
                        //            being check VAT
                        $vendor = Vendor::select('vat_bus_posting_group_code')->where('no', $header->vendor_no)->first();
                        if ($vendor->vat_bus_posting_group_code && $fix_asset->vat_prod_posting_group_code) {
                            $vat_post_group = VatPostingSetup::select('vat_calculation_type', 'vat_amount')
                                ->where('vat_bus_posting_group', $vendor->vat_bus_posting_group_code)
                                ->where('vat_prod_posting_group', $fix_asset->vat_prod_posting_group_code)->first();

                            if ($vat_post_group) {
                                $record->vat_calculation_type = $vat_post_group->vat_calculation_type;
                                $record->vat_percentage = $vat_post_group->vat_amount;
                            }
                            $record->vat_bus_posting_group_code = $header->vat_bus_posting_group_code;
                            $record->gen_bus_posting_group_code = $header->gen_bus_posting_group_code;
                            $record->vat_bus_posting_group_code = $header->vat_bus_posting_group_code;
                        }
                        //            end check VAT
                        $record->type = 'Fix Asset';
                        $record->created_by = Auth::user()->email;
                    } else {
                        $description = "No : '" . $values['no'] . "' type not found!";
                        $this->SaveExcelUploadLog("purchase line", $description, $record);
                        return 'false';
                    }

                    if ($record == 'false') {
                        return 'false';
                    }
                } else {
                    $item = Item::where('no', $no_value)->first();
                    if (!$item) {
                        $description = "Item no : '" . $values['no'] . "' is not found!";
                        $this->SaveExcelUploadLog("Purchase Line", $description, $record);
                        return 'false';
                    }
                    $record = $this->TypeItemUploadExcel($values, $item, $record, $header);
                    if ($record == 'false') {
                        return 'false';
                    }
                }
            } elseif ($modal_name == 'App\Models\Sales\Transaction\SaleLine') {
                $record->document_type = $header->document_type;
                $record->document_no = $header->no;
                $record->customer_no = $header->customer_no;
                $record->location_code = $header->location_code;
                if ($header->currency_code) {
                    $record->currency_code = $header->currency_code;
                    $record->currency_factor = $header->currency_factor;
                } else {
                    $record->currency_factor = 1;
                }
                $record->store_code = $header->store_code;
                $record->division_code = $header->division_code;
                $record->business_unit_code = $header->business_unit_code;
                $record->department_code = $header->department_code;
                $record->project_code = $header->project_code;
                $record->salesperson_code = $header->salesperson_code;
                $record->request_shipment_date = $header->request_shipment_date;
                $record->apply_to_item_entry_no = 0;
                $record->gen_bus_posting_group_code = $header->gen_bus_posting_group_code;
                $record->vat_bus_posting_group_code = $header->vat_bus_posting_group_code;

                $sale_line = SaleLine::select('line_no')->where('document_no',$header->no)->max('line_no');
                $refer_line = SaleLine::select('refer_line_no')->where('document_no',$header->no)->max('refer_line_no');

                $refer_line_no = ($refer_line) ? $refer_line : 0;
                $refer_line_no = $refer_line_no + 1;
                $line_no = ($sale_line) ? $sale_line : 0;
                $line_no = $sale_line + 10000;
                $record->line_no = $line_no;
                $record->refer_line_no = $refer_line_no;

                $no_value = html_entity_decode(preg_replace("/U\+([0-9A-F]{4})/", "&#x\\1;", trim($values['no'], ' ')), ENT_NOQUOTES, 'UTF-8');

                if (isset($values['type'])) {
                    /**
                     * If $no_value empty ,It is Text
                     */
                    if ($values['type'] == 'Item' && $no_value !="") {
                        $item = Item::where('no', $no_value)->first();
                        if (!$item) {
                            $description = "Item no : '" . $no_value . "' is not found!";
                            $this->SaveExcelUploadLog("Sale Line", $description, $record);
                            return 'false';
                        }
                        $record = $this->TypeItemUploadExcelForSales($values, $item, $record, $header);
                    } elseif ($values['type'] == 'G/L Account') {
                        //=================being get from chart of account
                        $gl_account = ChartOfAccount::where('no', $no_value)->first();
                        if (!$gl_account) {
                            $description = "No : '" . $values['no'] . "' G/L Account Not Found";
                            $this->SaveExcelUploadLog("Sale Line", $description, $record);
                            return 'false';
                        }
                        if ($gl_account) {
                            $record->no = $gl_account->no;
                            $record->description = $gl_account->description;
                            $record->description_2 = $gl_account->description_2;
                            $record->vat_prod_posting_group_code = $gl_account->vat_prod_posting_group_code;
                            $record->gen_prod_posting_group_code = $gl_account->gen_prod_posting_group_code;
                            $record->qty_per_unit_of_measure = 1;
                            //            being check VAT
                            $customer = Customer::select('vat_posting_group_code')->where('no', $header->customer_no)->first();
                            if ($customer->vat_posting_group_code && $gl_account->vat_prod_posting_group_code) {
                                $vat_post_group = VatPostingSetup::select('vat_calculation_type', 'vat_amount')
                                    ->where('vat_bus_posting_group', $customer->vat_posting_group_code)
                                    ->where('vat_prod_posting_group', $gl_account->vat_prod_posting_group_code)->first();
                                if ($vat_post_group) {
                                    $record->vat_calculation_type = $vat_post_group->vat_calculation_type;
                                    $record->vat_percentage = $vat_post_group->vat_amount;
                                }
                                $record->vat_bus_posting_group_code = $header->vat_bus_posting_group_code;
                                $record->gen_bus_posting_group_code = $header->gen_bus_posting_group_code;
                            }
                            $record->type = 'G/L Account';
                            $record->outstanding_quantity = 0;
                            $record->quantity_to_ship = 0;
                            $record->quantity_shipped = 0;
                            $record->quantity_invoiced = 0;
                            $record->quantity_to_invoice = 0;
                            $record->created_by = Auth::user()->email;
                            $record->apply_to_item_entry_no = 0;
                            $record->unit_of_measure = 'UNIT';
                            $record->division_code = $header->division_code;
                            $record->business_unit_code = $header->business_unit_code;
                            $record->department_code = $header->department_code;
                            $record->project_code = $header->project_code;
                        }
                    } elseif ($values['type'] == 'Item Charge') {
                        //=================being get from item charge
                        $item_charge = ItemCharge::where('code', $no_value)->first();
                        if (!$item_charge) {
                            $description = "Item Charge Not Found, Item no : '" . $values['no'] . "'";
                            $this->SaveExcelUploadLog("Sale Line", $description);
                            return 'false';
                        }
                        if ($item_charge) {
                            $record->no = $item_charge->code;
                            $record->description = $item_charge->description;
                            $record->description_2 = $item_charge->description_2;
                            $record->vat_prod_posting_group_code = $item_charge->vat_prod_posting_group_code;
                            $record->gen_prod_posting_group_code = $item_charge->gen_prod_posting_group_code;
                            $record->qty_per_unit_of_measure = 1;
                            $record->unit_of_measure = 'UNIT';
                            $record->division_code = $item_charge->division_code ? $item_charge->division_code : $header->division_code;
                            $record->business_unit_code = $item_charge->business_unit_code ? $item_charge->business_unit_code : $header->business_unit_code;
                            $record->department_code = $item_charge->department_code ? $item_charge->department_code : $header->department_code;
                            $record->project_code = $item_charge->project_code ? $item_charge->project_code : $header->project_code;
                        }
                        $customer = Customer::select('vat_posting_group_code')->where('no', $header->customer_no)->first();
                        if ($customer->vat_posting_group_code && $item_charge->vat_prod_posting_group_code) {
                            $vat_post_group = VatPostingSetup::select('vat_calculation_type', 'vat_amount')
                                ->where('vat_bus_posting_group', $customer->vat_posting_group_code)
                                ->where('vat_prod_posting_group', $item_charge->vat_prod_posting_group_code)->first();
                            if ($vat_post_group) {
                                $record->vat_calculation_type = $vat_post_group->vat_calculation_type;
                                $record->vat_percentage = $vat_post_group->vat_amount;
                            }
                            $record->vat_bus_posting_group_code = $header->vat_bus_posting_group_code;
                            $record->gen_bus_posting_group_code = $header->gen_bus_posting_group_code;
                        }
                        $record->type = 'Item Charge';
                        $record->outstanding_quantity = 0;
                        $record->quantity_to_ship = 0;
                        $record->quantity_shipped = 0;
                        $record->quantity_invoiced = 0;
                        $record->quantity_to_invoice = 0;
                        $record->created_by = Auth::user()->email;
                        $record->apply_to_item_entry_no = 0;
                    } elseif ($values['type'] == 'Fix Asset') {
                        //=================being get from fix asset
                        $fix_asset = FixAsset::where('no', $no_value)->first();
                        if (!$fix_asset) {
                            $description = "Fix Asset Not Found, Item no : '" . $values['no'] . "'";
                            $this->SaveExcelUploadLog("Sale Line", $description);
                            return 'false';
                        }
                        if ($fix_asset) {
                            $record->no = $fix_asset->no;
                            $record->description = $fix_asset->description;
                            $record->description_2 = $fix_asset->description_2;
                            $record->qty_per_unit_of_measure = 1;
                            $record->unit_of_measure = 'UNIT';
                            $record->division_code = $fix_asset->division_code ? $fix_asset->division_code : $header->division_code;
                            $record->business_unit_code = $fix_asset->business_unit_code ? $fix_asset->business_unit_code : $header->business_unit_code;
                            $record->department_code = $fix_asset->department_code ? $fix_asset->department_code : $header->department_code;
                            $record->project_code = $fix_asset->project_code ? $fix_asset->project_code : $header->project_code;
                        }
                        $customer = Customer::select('vat_posting_group_code')->where('no', $header->customer_no)->first();
                        if ($customer->vat_posting_group_code && $fix_asset->vat_prod_posting_group_code) {
                            $vat_post_group = VatPostingSetup::select('vat_calculation_type', 'vat_amount')
                                ->where('vat_bus_posting_group', $customer->vat_posting_group_code)
                                ->where('vat_prod_posting_group', $fix_asset->vat_prod_posting_group_code)->first();
                            if ($vat_post_group) {
                                $record->vat_calculation_type = $vat_post_group->vat_calculation_type;
                                $record->vat_percentage = $vat_post_group->vat_amount;
                            }
                            $record->vat_bus_posting_group_code = $header->vat_bus_posting_group_code;
                            $record->gen_bus_posting_group_code = $header->gen_bus_posting_group_code;
                        }
                        $record->type = 'Fix Asset';
                        $record->outstanding_quantity = 0;
                        $record->quantity_to_ship = 0;
                        $record->quantity_shipped = 0;
                        $record->quantity_invoiced = 0;
                        $record->quantity_to_invoice = 0;
                        $record->created_by = Auth::user()->email;
                        $record->apply_to_item_entry_no = 0;
                    }
                } else {
                    /**
                     * If $no_value empty ,It is Text
                     */
                    if($no_value !="") {
                        $item = Item::where('no', $no_value)->first();
                        if (!$item) {
                            $description = "Item no : '" . $values['no'] . "' is not found!";
                            $this->SaveExcelUploadLog("Sale Line", $description);
                            return 'false';
                        }
                        $record = $this->TypeItemUploadExcelForSales($values, $item, $record, $header);
                        if ($record == 'false') {
                            return 'false';
                        }
                    }
                    
                }
            } elseif ($modal_name == 'App\Models\Financial\Transaction\PaymentJournal') {
                $ref = "Payment Journal";
                if($values['journal_type'] == "Purchase Journal") $ref = "Purchase Journal";
                
                if(isset($values['document_no']) && $values['document_no'] != ""){
                    $no_series = $values['document_no'];    
                }else{
                    $no_series = $this->generateNoV2("", $ref);
                    if (isset($no_series['status']) && $no_series['status'] != 'success'){
                        $description = '['.__LINE__.']'. $no_series['msg'];
                        $this->SaveExcelUploadLog('Payment Journal', $description);
                        return 'false';
                    } 
                    $no_series = $no_series["no_serial"];
                }
                
                $payment_journal = PaymentJournal::where("document_no", $no_series)
                    ->where("document_type", $values['document_type'])
                    ->where("journal_type", $values['journal_type'])->first();
                if($payment_journal){
                    $description = '['.__LINE__.'] The document number: ['.$values['document_no'].'] is already exist!';
                    $this->SaveExcelUploadLog('Payment Journal', $description);
                    return 'false';
                }

                $record->document_no = $no_series;
                $record->status = "Open";
                $record->document_type = $values['document_type'];
                $record->journal_type = $values['journal_type'];
                $record->assign_to_userid = Auth::user()->id;
                $record->assign_to_username = Auth::user()->email;
                $record->created_by = Auth::user()->email;
            } elseif ($modal_name == 'App\Models\Financial\Transaction\CashReceiptJournal') {
                $batch_name = 'Cash Receipts Journal';
                $document_type = 'Receipt';
                if ($values['journal_type'] == 'Sales Journal') {
                    $batch_name = 'Sales Journal';
                    $document_type = 'Invoice';
                } else if ($values['journal_type'] == 'Cash Refund Journal') {
                    $batch_name = 'Cash Refund Journal';
                    $document_type = 'Refund';
                } else if ($values['journal_type'] == 'Cash Deposit Journal') {
                    $batch_name = 'Cash Deposit Journal';
                    $document_type = 'Deposit';
                }
                $record->journal_type = $batch_name;
                $record->document_type = $document_type;
                $record->assign_to_userid = Auth::user()->id;
                $record->assign_to_username = Auth::user()->email;
                $record->created_by = Auth::user()->email;
            } elseif ($modal_name == 'App\Models\Financial\Transaction\GeneralJournalLine') {
                $record->currency_code = $header['currency_code'];
                $record->currency_factor = service::number_formattor_database($header['currency_factor'], 'currency_factor');

                $journal_line = \App\Models\Financial\Transaction\GeneralJournalLine::select('line_no')->max('line_no');
                $line_no = ($journal_line) ? $journal_line : 0;
                $line_no = $journal_line + 10000;
                $record->line_no = $line_no;
                $record->document_date = $header['document_date'];
                $record->posting_date = $header['posting_date'];
                $record->document_no = $header['document_no'];
                $record->Journal_type = "General Journal";
                $record->created_by = Auth::user()->email;
            }

            foreach ($table_fields as $table_field) {
                $field_name = $table_field->field_name;
                $field_extra = $table_field->extra;
                if ($field_name == $field_pri_key || $field_extra == 'auto_increment') {
                    continue;
                }
                // if(isset($values[$field_name])){
                //     if(!$values[$field_name]){
                //         continue;                            
                //     }
                // }
                if (!isset($values[$table_field->field_name])) continue;

                // ============ sub string by field length ================                
                $value = html_entity_decode(preg_replace("/U\+([0-9A-F]{4})/", "&#x\\1;", trim($values[$table_field->field_name], ' ')), ENT_NOQUOTES, 'UTF-8');
                if ($table_field->max_length) {
                    $value = mb_substr($value, 0, $table_field->max_length);
                }
                // update another field value reference to header document 
                if ($modal_name != 'App\Models\Financial\Setup\CustomerTimelineHistory') {
                    if (isset($header[$field_name])) {
                        $record->$field_name = $header->$field_name;
                    }
                }
                // update another field value reference to header document                     
                if ($table_field->field_data_type == 'date') {
                    $field = new PageGroupField();
                    $field->field_data_type = 'date';
                    if ($value == null || $value == '') {
                        if ($field_name == 'ending_date' || $field_name == 'termindate_date') {
                            $format_date = '2500-01-01';
                        } else {
                            $format_date = '1900-01-01';
                        }
                    } else {
                        try {
                            $format_date = Carbon::parse($value)->toDateString();
                        } catch (\Exception $ex) {
                            $excel_line_no += 1;
                            $description = "Line no.: " . $excel_line_no . " Field '" . $field_name . "' input date incorrect!";
                            $this->SaveExcelUploadLog($table_field->table_name, $description);
                            return 'false';
                        }
                    }
                    $record->$field_name = $format_date;
                } else if ($table_field->field_data_type == 'datetime') {
                    $record->$field_name = Carbon::parse($value)->toDateTimeString();
                } else if ($table_field->field_data_type == 'decimal') {
                    try {
                        $record->$field_name = $this->toDouble($value);
                    } catch (\Exception $ex) {
                        $excel_line_no += 1;
                        $description = "Line no.: " . $excel_line_no . " Field '" . $field_name . "' input value incorrect!";
                        $this->SaveExcelUploadLog($table_field->table_name, $description);
                        return 'false';
                    }
                } else if ($table_field->field_data_type == 'time') {
                    $result = $this->timeformat($value);
                    if ($result == 'false') {
                        $record->$field_name = '';
                    } else {
                        $record->$field_name = $result;
                    }
                } else {
                    if ($table_field->input_type == 'checkbox') {
                        if ($value != 'Yes') {
                            $record->$field_name = 'No';
                        } else {
                            $record->$field_name = 'Yes';
                        }
                    } else {
                        $record->$field_name = $value;
                    }
                }

                if ($modal_name == 'App\Models\Purchase\Transaction\ItemForecastLine') {
                    if ($header->starting_date != '' && $header->ending_date != '') {
                        $starting = Carbon::parse($header->starting_date)->firstOfYear();
                        if ($header->view_by == 'Year') {
                            for ($i = 1; $i <= 12; $i++) {
                                $excel_date = 'month_' . $i;
                                $year =  Carbon::parse($starting)->year;
                                $demand_date = Carbon::parse("$year-$i-1")->endOfMonth()->toDateString();
                                if ($header) {
                                    $record->header_id = $header->id;
                                    $record->forecast_type = $header->forecast_type;
                                    if (!$record->line_no) {
                                        $lines = $modal_name::select('line_no')->where('header_id', $header->id)->max('line_no');
                                        $line_no = ($lines) ? $lines : 0;
                                        $line_no = $line_no + 10000;
                                        $record->line_no = $line_no;
                                    }
                                }
                                if ($values[$excel_date]) {
                                    $item_line_qty = new ItemForecastLineQuantity();
                                    $item_line_qty->header_id = $header->id;
                                    $item_line_qty->forecast_type = $header->forecast_type;
                                    $item_line_qty->forecast_option = $header->forecast_option;
                                    $item_line_qty->line_no = $record->line_no;
                                    $item_line_qty->quantity = $values[$excel_date];
                                    if ($record->unit_of_measure_code) {
                                        $item_line_qty->unit_of_measure_code = $record->unit_of_measure_code;
                                        $item_line_qty->qty_per_unit_of_measure = $record->qty_per_unit_of_measure;
                                        $item_line_qty->quantity_base = $values[$excel_date] * $record->qty_per_unit_of_measure;
                                    } else {
                                        $item_line_qty->qty_per_unit_of_measure = 1;
                                        $item_line_qty->quantity_base = $values[$excel_date] * $item_line_qty->qty_per_unit_of_measure;
                                    }
                                    $item_line_qty->view_by = 'Year';
                                    $item_line_qty->demand_date = $demand_date;
                                    $item_line_qty->save();
                                }
                            }
                        }
                    }
                }
            }
            // validate field for import
            foreach ($table_fields as $table_field) {
                $table_field->excel_fields = $excel_fiels;
                if(method_exists($record,"validate_field")) {
                    $record->validate_field($table_field, $header);
                }
            }
            
            if ($modal_name == 'App\Models\Purchase\Transaction\PurchaseLine') {
                $record->currency = $header->currency;
                $record->CalculatePurchaseAmount();
            }
            if ($modal_name == 'App\Models\Sales\Transaction\SaleLine') {
                $record->currency = $header->currency;
                $record->CalculateAmount();
                if($record->type == "Item" && $record->isItemTracking() == true){
                    $location_code = $record->location_code;
                    $is_failed = false;
                    $item_no = $record->no;
                    $line_no = $record->line_no;
                    $uom_code = $record->unit_of_measure;
                    $document_no = $record->document_no;
                    $document_line_no = $record->line_no;
                    $document_type = "Sales ".$record->document_type;
                    
                    $uom_qty_per_unit = service::toDouble($record->qty_per_unit_of_measure);
                    $total_quantity = service::toDouble($record->quantity);
                    $total_qty_to_handle = service::toDouble($record->quantity) * service::toDouble($record->qty_per_unit_of_measure);
                    $quantity_to_handle_base = (service::toDouble($record->quantity)
                        * service::toDouble($record->qty_per_unit_of_measure))
                        - $this->getAllocatedQuantityBase($record->document_type, $record->document_no, $record->line_no, $record->no);

                    $item_stock_uom = $item->stock_uom_code ?? "";
                    $item_ledger_entries = ItemLedgerEntry::where('remaining_quantity', '>', 0)->where('item_no', $item_no)->where('location_code', $location_code)
                    ->where('posting_date', "<=", Carbon::parse($header->posting_date)->toDateString());
                    if ($item->item_tracking_code == 'LOTALL') $item_ledger_entries = $item_ledger_entries->orderBy("expiration_date", "ASC")->get();
                    else $item_ledger_entries = $item_ledger_entries->get();
        
                    if (!$item_ledger_entries) {
                        $description = "[".__LINE__."][Service] Total remaining quantity record of item tracking for item " . $values['no'] . " is 0 with demand: ". service::number_formattor($record->quantity_to_ship,"quantity");
                        $this->SaveExcelUploadLog('Item', $description, $record);
                        $is_failed  = true;
                    }
        
                    if ($quantity_to_handle_base <= 0) {
                        $description = "[".__LINE__."][Service] Nothing to handle with item no: ". $values['no'];
                        $this->SaveExcelUploadLog('Item', $description, $record);
                        $is_failed  = true;
                    }
                    ItemTrackingBuffer::where('item_no', $item_no)->where('document_line_no', $record->line_no)->where('document_no', $record->document_no)->delete();
                    $quantity_base_by_line = service::toDouble($total_quantity) * $uom_qty_per_unit;
                    $inventory = ItemLedgerEntry::where('location_code', $location_code)->where('item_no', $item->no)->sum('quantity');
        
                    $qty_allocated = ItemTrackingBuffer::where('item_no', $item_no)
                        ->where('document_no', $document_no)
                        ->where('document_line_no', $document_line_no)
                        ->sum('quantity_to_handle_base');

                    $avalability = service::toDouble($inventory) - service::toDouble($qty_allocated);
                    if ($avalability <= 0 || $avalability < $quantity_base_by_line) {
                        $description = "[".__LINE__."][Service] Do not have enough quantity. Item tracking item no: ". $values['no'];
                        $this->SaveExcelUploadLog('Item', $description, $record);
                        $is_failed  = true; 
                    }
                    if(!$is_failed){
                        foreach ($item_ledger_entries as $item_ledger_entry) {
                            if ($item_ledger_entry->remaining_quantity <= $item_ledger_entry->allocated_quantity) continue;
                            
                            // ===== Check Existing Records And Update
                            $item_buffer = ItemTrackingBuffer::where('item_no', $item_no)
                                ->where('document_no', $record->document_no)
                                ->where('document_line_no', $record->document_line_no)
                                ->where('lot_no', $item_ledger_entry->lot_no)
                                ->where('item_ledger_entry_no', $item_ledger_entry->entry_no)
                                ->where('expiration_date', Carbon::parse($item_ledger_entry->expiration_date)->toDateString())
                                ->first();
                            if ($item_buffer) {
                                if ($quantity_to_handle_base >= service::toDouble($item_ledger_entry->remaining_quantity)) {
                                    $qty_headle = service::toDouble($item_ledger_entry->remaining_quantity) / service::toDouble($record->qty_per_unit_of_measure);
                                    $quantity_to_handle = service::toDouble($qty_headle);
                                    $quantity_to_handle_base = service::toDouble($item_ledger_entry->remaining_quantity);
                                    $quantity_to_handle_base = $quantity_to_handle_base - service::toDouble($item_ledger_entry->remaining_quantity);
                                    // Update Qty
                                    $item_buffer->quantity_to_handle = service::toDouble($item_buffer->quantity_to_handle) + service::toDouble($quantity_to_handle);
                                    $item_buffer->quantity_to_handle_base = service::toDouble($item_buffer->quantity_to_handle_base) + service::toDouble($quantity_to_handle_base);
                                } else {
                                    $qty_headle = service::toDouble($quantity_to_handle_base) / service::toDouble($record->qty_per_unit_of_measure);
                                    $quantity_to_handle = service::toDouble($qty_headle);
                                    $quantity_to_handle_base = service::toDouble($quantity_to_handle_base);
                                    // Update Qty
                                    $item_buffer->quantity_to_handle = service::toDouble($item_buffer->quantity_to_handle) + service::toDouble($quantity_to_handle);
                                    $item_buffer->quantity_to_handle_base = service::toDouble($item_buffer->quantity_to_handle_base) + service::toDouble($quantity_to_handle_base);
                                    $quantity_to_handle_base = 0;
                                }
                                $item_buffer->save();
                            } else {
                                $item_buffer = new ItemTrackingBuffer();
                                $item_buffer->table_name = "sales_line";
                                $item_buffer->document_type = $document_type;
                                $item_buffer->document_no = $document_no;
                                $item_buffer->document_line_no = $line_no;
                                $item_buffer->location_code = $location_code;
                                $item_buffer->item_no = $item_no;
                                $item_buffer->item_ledger_entry_no = $item_ledger_entry->entry_no;
                                $item_buffer->apply_from_item_entry = $item_ledger_entry->entry_no;
                                $item_buffer->serial_no = $item_ledger_entry->serial_no;
                                $item_buffer->lot_no = $item_ledger_entry->lot_no;
                                $item_buffer->warranty_date = $item_ledger_entry->warranty_date;
                                $item_buffer->expiration_date = $item_ledger_entry->expiration_date;
                                if ($quantity_to_handle_base >= service::toDouble($item_ledger_entry->remaining_quantity)) {
                                    $qty_headle = service::toDouble($item_ledger_entry->remaining_quantity) / service::toDouble($record->qty_per_unit_of_measure);
                                    $item_buffer->quantity_to_handle = service::toDouble($qty_headle);
                                    $item_buffer->quantity_to_handle_base = service::toDouble($item_ledger_entry->remaining_quantity);
                                    $quantity_to_handle_base = $quantity_to_handle_base - service::toDouble($item_ledger_entry->remaining_quantity);
                                } else {
                                    $qty_headle = service::toDouble($quantity_to_handle_base) / service::toDouble($record->qty_per_unit_of_measure);
                                    $item_buffer->quantity_to_handle = service::toDouble($qty_headle);
                                    $item_buffer->quantity_to_handle_base = service::toDouble($quantity_to_handle_base);
                                    $quantity_to_handle_base = 0;
                                }
        
                                $item_buffer->unit_of_measure = $uom_code;
                                $item_buffer->qty_per_unit_of_measure = $uom_qty_per_unit;
                                $item_buffer->save();
                            }
                            if ($quantity_to_handle_base <= 0) break;
                        }
                    }
                }
            }
            if ($modal_name == 'App\Models\Warehouse\Transaction\ItemJournalLine') {
                $item = Item::where('no', $values['item_no'])->first();
                if (!$item) {
                    $description = "Item: " . $values['item_no'] . ' not found!';
                    $record->document_type = $record->journal_type;
                    $this->SaveExcelUploadLog('Item', $description, $record);
                    return 'false';
                }
            }
            $record->save();
            if ($modal_name == 'App\Models\Warehouse\Transaction\ItemJournalLine' && $record->journal_type == "Phy. Inventory Journal") {
                if($record->isItemTracking() == true){
                    $record->quantity = ToDoubleHelper($record->quantity_count) * ToDoubleHelper($record->count_qty_per_unit_of_measure);
                    $record->expiry_date = Carbon::parse($values['expiration_date'])->toDateString();
                    if(isset($values['expiration_date'])) {
                        $record->expiry_date = $values['expiration_date'];
                        $record->save();   
                    }
                    
                    $record->expiration_date = Carbon::parse($record->expiry_date)->toDateString();
                    $result = $record->addTracking($record);
                    if($result['status'] == "error") {
                        $description = $result['msg'];
                        $this->SaveExcelUploadLog('ItemJournalLine', $description, $record);
                        return 'false';
                    }  
                    
                    $wrongs_stock = ItemLedgerEntry::selectRaw("item_no,lot_no,expiration_date, location_code, SUM(quantity) as inventory, SUM(remaining_quantity) as remaining_quantity,
                            (SUM(remaining_quantity) - SUM(quantity)) as diff_qty")
                            ->where('item_no', $record->item_no)->where('location_code', $record->location_code)
                            ->whereRaw("lot_no IS NOT NULL AND lot_no != ''")
                            ->GroupBy(\db::raw("item_no,lot_no,expiration_date, location_code"))
                            ->having("diff_qty", "!=", 0)
                            ->get();
                    foreach($wrongs_stock as $wrong_stock){
                        $arr_entry_no  = ItemLedgerEntry::where('item_no', $record->item_no)->where('location_code', $record->location_code)
                            ->where("lot_no", $wrong_stock->lot_no)
                            ->where("expiration_date", Carbon::parse($wrong_stock->expiration_date)->toDateString())
                            ->pluck("entry_no");
                        ItemLedgerEntry::whereIn("entry_no", $arr_entry_no)->update(['remaining_quantity' => 0]);
                    }

                    
                    if(isset($values['lot_no'])) $record->lot_no = $values['lot_no'];
                    $org_record = $record;
                    
                    if($header->clear_old_stock){
                        $item_ledger_entries = ItemLedgerEntry::where('item_no', $record->item_no)->where('location_code', $record->location_code)
                            ->where('remaining_quantity', '>', 0)->get();
                        if(count($item_ledger_entries) > 0){
                            $record->quantity_count = 0;
                            $copy_record = $record->NewRecord($header, $record, true);
                            if(isset($copy_record['status']) && $copy_record['status'] != "success"){
                                if($copy_record['status'] == "remove") return "false";
                                $description = "Create Copy: ". $copy_record['msg'];
                                $org_record->document_type = $org_record->journal_type;
                                $this->SaveExcelUploadLog('ItemJournalLine', $description, $copy_record);
                                return 'false';
                            }
                            $record = $copy_record['record'];
                            
                            foreach ($table_fields as $table_field) {
                                $table_field->excel_fields = $excel_fiels;
                                if(method_exists($record,"validate_field")) {
                                    $result = $record->validate_field($table_field, $header, false);
                                }
                            }
                            $record->external_document_no = "Clear Stock";
                            $record->save();
                            $line = $record;
                        
                        
                            $table_name = 'Item Journal';
                            $uom_qty_per_unit = ToDoubleHelper($line->qty_per_unit_of_measure);
                            $uom_code = $line->unit_of_measure_code;
                            $line_quantity = $line->quantity;
                            $location_code = $line->location_code;
                            $document_no = $line->document_no;
                            $document_type = $line->document_type;
                            $document_line_no = $line->line_no;
                            $item_no = $line->item_no;
                            $quantity_per_unit = ToDoubleHelper($line_quantity) * $uom_qty_per_unit;
        
                            foreach($item_ledger_entries as $ile){
                                //CHECK EXISTING LOT IN BUFFER
                                $item_buffer = ItemTrackingBuffer::where('item_no', $line->item_no)
                                    ->where('document_no', $document_no)
                                    ->where('document_type', $document_type)
                                    ->where('document_line_no', $document_line_no)
                                    ->where('location_code', $location_code)
                                    ->where('lot_no', $ile->lot_no)
                                    ->where('item_ledger_entry_no', $ile->entry_no)
                                    ->where('expiration_date', Carbon::parse($ile->expiration_date)->toDateString())
                                    ->first();
        
                                if (!$item_buffer) {
                                    $item_buffer = new ItemTrackingBuffer();
                                    $item_buffer->table_name = $table_name;
                                    $item_buffer->document_type = $document_type;
                                    $item_buffer->document_no = $document_no;
                                    $item_buffer->document_line_no = $line->line_no;
                                    $item_buffer->location_code = $location_code;
                                    $item_buffer->item_no = $item_no;
                                    $item_buffer->item_ledger_entry_no = $ile->entry_no;
                                    $item_buffer->apply_from_item_entry = $ile->entry_no;
                                    $item_buffer->serial_no = $ile->serial_no;
                                    $item_buffer->lot_no = $ile->lot_no;
                                    $item_buffer->warranty_date = $ile->warranty_date;
                                    $item_buffer->expiration_date = $ile->expiration_date;
                                    $item_buffer->quantity = ToDoubleHelper($line_quantity); 
                                    $item_buffer->quantity_base = ToDoubleHelper($quantity_per_unit); 
                                }
                                $qty_to_add = ToDoubleHelper($ile->remaining_quantity) / ToDoubleHelper($uom_qty_per_unit);
        
                                $item_buffer->quantity_to_handle = ToDoubleHelper($qty_to_add);
                                $item_buffer->quantity_to_handle_base = ToDoubleHelper($qty_to_add) * ToDoubleHelper($uom_qty_per_unit);
                                $item_buffer->unit_of_measure = $uom_code;
                                $item_buffer->qty_per_unit_of_measure = $uom_qty_per_unit;
                                $item_buffer->save();
                            }
                            $org_record = TransactionItemJournalLine::where("id", $org_record->id)->first();
                            $org_record->quantity = $org_record->quantity_count;
                            $org_record->document_type = "Positive Adj.";
                            $org_record->save();

                            $org_record->expiration_date = Carbon::parse($values['expiration_date'])->toDateString();
                            $result = $org_record->addTracking($org_record);
                            if($result['status'] == "error") {
                                $description = $result['msg'];
                                $org_record->document_type = $org_record->journal_type;
                                $this->SaveExcelUploadLog('ItemJournalLine', $description, $org_record);
                                return 'false';
                            }
                        }else{
                            // Negative stock
                            $wrongs_stock = ItemLedgerEntry::selectRaw("item_no,lot_no,expiration_date, location_code, SUM(quantity) as inventory, SUM(remaining_quantity) as remaining_quantity,
                                (SUM(remaining_quantity) - SUM(quantity)) as diff_qty")
                                ->where('item_no', $record->item_no)->where('location_code', $record->location_code)
                                ->whereRaw("lot_no IS NOT NULL AND lot_no != ''")
                                ->GroupBy(\db::raw("item_no,lot_no,expiration_date, location_code"))
                                ->having("diff_qty", "!=", 0)
                                ->get();
                                
                            if(count($wrongs_stock) > 0){
                                $record->quantity_count = 0;
                                $copy_record = $record->NewRecord($header, $record, true);
                                if(isset($copy_record['status']) && $copy_record['status'] != "success"){
                                    if($copy_record['status'] == "remove") return "false";
                                    $description = "Create Copy: ". $copy_record['msg'];
                                    $org_record->document_type = $org_record->journal_type;
                                    $this->SaveExcelUploadLog('ItemJournalLine', $description, $copy_record);
                                    return 'false';
                                }
                                $record = $copy_record['record'];
                                
                                foreach ($table_fields as $table_field) {
                                    $table_field->excel_fields = $excel_fiels;
                                    if(method_exists($record,"validate_field")) {
                                        $result = $record->validate_field($table_field, $header, false);
                                    }
                                }
                                $record->external_document_no = "Clear Stock";
                                $record->save();
                                $line = $record;

                                $table_name = 'Item Journal';
                                $uom_qty_per_unit = ToDoubleHelper($line->qty_per_unit_of_measure);
                                $uom_code = $line->unit_of_measure_code;
                                $line_quantity = $line->quantity;
                                $location_code = $line->location_code;
                                $document_no = $line->document_no;
                                $document_type = $line->document_type;
                                $document_line_no = $line->line_no;
                                $item_no = $line->item_no;
                                foreach($wrongs_stock as $wrong_stock){
                                    $record->expiration_date = Carbon::parse($wrong_stock->expiration_date)->toDateString();
                                    $record->lot_no = $wrong_stock->lot_no;
                                    $result = $record->addTracking($record);
                                    if($result['status'] == "error") {
                                        $description = $result['msg'];
                                        $this->SaveExcelUploadLog('ItemJournalLine', $description, $record);
                                        return 'false';
                                    }  
                                }
                            }else{
                                $record->expiration_date = Carbon::parse($values['expiration_date'])->toDateString();
                                $record->quantity = ToDoubleHelper($record->quantity_count) * ToDoubleHelper($record->count_qty_per_unit_of_measure);
                                $record->save();
                                $result = $record->addTracking($record);
                                if($result['status'] == "error") {
                                    $description = $result['msg'];
                                    $this->SaveExcelUploadLog('ItemJournalLine', $description, $record);
                                    return 'false';
                                }  
                            }

                        }
                    }else{
                        $record->expiration_date = Carbon::parse($values['expiration_date'])->toDateString();
                        $result = $record->addTracking($record);
                        if($result['status'] == "error") {
                            $description = $result['msg'];
                            $this->SaveExcelUploadLog('ItemJournalLine', $description, $record);
                            return 'false';
                        }
                    }
                }
            }
            
            return 'success';
        } catch (\Exception $ex) {
            $this->saveErrorLog($ex);
            return 'false';
        }
    }
    public function getAllocatedQuantityBase($document_type, $document_no, $line_no, $item_no)
    {
        $allocated_quantity_base = ItemTrackingBuffer::where('document_type', $document_type)
            ->where('document_no', $document_no)
            ->where('document_line_no', "!=",$line_no)
            ->where('item_no', $item_no)
            ->sum('quantity_to_handle_base');
        return service::number_formattor_database($allocated_quantity_base, 'quantity');

    }
    public function TypeItemUploadExcel($values, $item, $record, $header)
    {
        if (!$item->stock_uom_code || $item->stock_uom_code == ' ') {
            $description = "Item Purchase UOM Code Or Stock UOM Code Must Have Value stock_uom_code" . $item->stock_uom_code . " - ";
            $this->SaveExcelUploadLog("Item", $description);
            return 'false';
        } else if (!$item->inv_posting_group_code || $item->inv_posting_group_code == ' ') {
            $description = "Inventory posting group must has a value!";
            $this->SaveExcelUploadLog("Item", $description);
            return 'false';
        } else if (!$item->costing_method || $item->costing_method == ' ') {
            $description = "Item costing method must has a value!";
            $this->SaveExcelUploadLog("Item", $description);
            return 'false';
        } else if (!$item->gen_prod_posting_group_code || $item->gen_prod_posting_group_code == ' ') {
            $description = "Gen. prod. posting group must has a value!";
            $this->SaveExcelUploadLog("Item", $description);
            return 'false';
        } else if (!$item->vat_prod_posting_group_code || $item->vat_prod_posting_group_code == ' ') {
            $description = "VAT. prod. posting group of item no:[$item->no] must has a value!";
            $this->SaveExcelUploadLog("Item", $description);
            return 'false';
        }
        try {
            $record->no = $item->no;
            $record->description = $item->description;
            $record->description_2 = $item->description_2;
            $record->vat_prod_posting_group_code = isset($values['vat_prod_posting_group_code']) ? $values['vat_prod_posting_group_code'] : $item->vat_prod_posting_group_code;
            $record->gen_prod_posting_group_code = $item->gen_prod_posting_group_code;
            $record->item_category_code = $item->item_category_code;
            $record->item_group_code = $item->item_group_code;
            $record->posting_group = $item->inv_posting_group_code;
            $record->item_disc_group_code = $item->item_discount_group_code;
            $record->item_brand_code = $item->item_brand_code;
            $record->division_code = ($item->division_code != '') ? $item->division_code : $header->division_code;
            $record->business_unit_code = ($item->business_unit_code != '') ? $item->business_unit_code : $header->business_unit_code;
            $record->department_code = ($item->department_code != '') ? $item->department_code : $header->department_code;
            $record->project_code = ($item->project_code != '') ? $item->project_code : $header->project_code;
            $record->request_receipt_date = $this->calcDate($header->order_date, $item->lead_time_calculation);

            // get quantity_per_unit from item_unit_of_measure
            $item_uom = ItemUnitOfMeasure::select('qty_per_unit')->where('item_no', $item->no)->where('unit_of_measure_code', $values['unit_of_measure_code'])->first();
            if (!$item_uom) {
                $record->unit_of_measure = $item->stock_uom_code;
                $record->qty_per_unit_of_measure = 1;
            } else {
                $record->unit_of_measure = $values['unit_of_measure_code'];
                $record->qty_per_unit_of_measure = $this->number_formattor_database($item_uom->qty_per_unit, 'quantity');
            }

            $record->direct_unit_cost = $this->toDouble($item->last_direct_cost) * $this->toDouble($record->qty_per_unit_of_measure);
            $record->direct_unit_cost_lcy = $this->toDouble($item->last_direct_cost) * $this->toDouble($record->qty_per_unit_of_measure) * $this->lcyExchangeAmount() / $this->toDouble($header->currency_factor);
            $record->division_code = ($item->division_code) ? $item->division_code : $header->division_code;
            $record->business_unit_code = ($item->business_unit_code) ? $item->business_unit_code : $header->business_unit_code;
            $record->department_code = ($item->department_code) ? $item->department_code : $header->department_code;
            $record->project_code = ($item->project_code) ? $item->project_code : $header->project_code;

            $vendor = Vendor::select('vat_bus_posting_group_code')->where('no', $header->vendor_no)->first();
            if ($vendor->vat_bus_posting_group_code && $record->vat_prod_posting_group_code) {
                $vat_post_group = VatPostingSetup::select('vat_calculation_type', 'vat_amount')
                    ->where('vat_bus_posting_group', $vendor->vat_bus_posting_group_code)
                    ->where('vat_prod_posting_group', $record->vat_prod_posting_group_code)->first();
                if ($vat_post_group) {
                    $record->vat_calculation_type = $vat_post_group->vat_calculation_type;
                    $record->vat_percentage = $vat_post_group->vat_amount;
                } else {
                    $record->vat_prod_posting_group_code = 'NOVAT';
                    $record->vat_calculation_type = 'VAT After Disc.';
                    $record->vat_percentage = 0;
                }
                $record->vat_bus_posting_group_code = $header->vat_bus_posting_group_code;
                $record->gen_bus_posting_group_code = $header->gen_bus_posting_group_code;
                $record->vat_bus_posting_group_code = $header->vat_bus_posting_group_code;
            } else {
                $record->vat_prod_posting_group_code = 'NOVAT';
                $record->vat_calculation_type = 'VAT After Disc.';
                $record->vat_percentage = 0;
            }
            //  end check VAT
            $record->type = 'Item';
            $record->created_by = Auth::user()->email;

            $item_specifications = ItemSpecification::where('item_no', $record->no)->orderBy('id')->get();
            if ($item_specifications) {
                foreach ($item_specifications as $item_specification) {
                    $specification = new PurchaseLine();
                    $specification->document_type = $document_type;
                    $specification->vendor_no = $purchase_header->vendor_no;
                    $specification->type = 'Text';
                    $specification->document_no = $purchase_header->no;
                    $line_no = $line_no + 10;
                    $specification->line_no = $line_no;
                    $specification->refer_line_no = $refer_line_no;
                    $specification->description = $item_specification->description;
                    $specification->description_2 = $item_specification->description_2;
                    $specification->created_by = Auth::user()->email;
                    $specification->save();
                }
            }
            return $record;
        } catch (\Exception $ex) {
            $description = $ex->getMessage();
            $this->SaveExcelUploadLog("Item", $description . " Line : " . $ex->getLine());
            return 'false';
        }
    }
    public function TypeItemUploadExcelForSales($values, $item, $record, $header)
    {
        if (!$item->stock_uom_code || $item->stock_uom_code == ' ') {
            $description = "Item Purchase UOM Code Or Stock UOM Code Must Have Value stock_uom_code" . $item->stock_uom_code . " - ";
            $this->SaveExcelUploadLog("Item", $description);
            return 'false';
        } else if (!$item->inv_posting_group_code || $item->inv_posting_group_code == ' ') {
            $description = "Inventory posting group must has a value!";
            $this->SaveExcelUploadLog("Item", $description);
            return 'false';
        } else if (!$item->costing_method || $item->costing_method == ' ') {
            $description = "Item costing method must has a value!";
            $this->SaveExcelUploadLog("Item", $description);
            return 'false';
        } else if (!$item->gen_prod_posting_group_code || $item->gen_prod_posting_group_code == ' ') {
            $description = "Gen. prod. posting group must has a value!";
            $this->SaveExcelUploadLog("Item", $description);
            return 'false';
        } else if (!$item->vat_prod_posting_group_code || $item->vat_prod_posting_group_code == ' ') {
            $description = "VAT. prod. posting group of item no:[$item->no] must has a value!";
            $this->SaveExcelUploadLog("Item", $description);
            return 'false';
        }
        try {
            $record->no = $item->no;
            $record->description = $item->description;
            $record->description_2 = $item->description_2;
            $record->vat_prod_posting_group_code = isset($values['vat_prod_posting_group_code']) ? $values['vat_prod_posting_group_code'] : $item->vat_prod_posting_group_code;
            $record->gen_prod_posting_group_code = $item->gen_prod_posting_group_code;
            $record->item_category_code = $item->item_category_code;
            $record->item_group_code = $item->item_group_code;
            $record->posting_group = $item->inv_posting_group_code;
            $record->item_disc_group_code = $item->item_discount_group_code;
            $record->item_brand_code = $item->item_brand_code;
            $record->division_code = ($item->division_code != '') ? $item->division_code : $header->division_code;
            $record->business_unit_code = ($item->business_unit_code != '') ? $item->business_unit_code : $header->business_unit_code;
            $record->department_code = ($item->department_code != '') ? $item->department_code : $header->department_code;
            $record->project_code = ($item->project_code != '') ? $item->project_code : $header->project_code;
            $record->request_shipment_date = $header->request_shipment_date;
            // get quantity_per_unit from item_unit_of_measure
            $item_uom = ItemUnitOfMeasure::select('qty_per_unit')->where('item_no', $item->no)->where('unit_of_measure_code', $values['unit_of_measure_code'])->first();
            if (!$item_uom) {
                $record->unit_of_measure = $item->stock_uom_code;
                $record->qty_per_unit_of_measure = 1;
            } else {
                $record->unit_of_measure = $values['unit_of_measure_code'];
                $record->qty_per_unit_of_measure = $this->number_formattor_database($item_uom->qty_per_unit, 'quantity');
            }
            $record->unit_price = $this->toDouble($item->unit_price) * $this->toDouble($record->qty_per_unit_of_measure);
            $record->unit_price_lcy = $this->toDouble($item->unit_price) * $this->toDouble($record->qty_per_unit_of_measure) * $this->lcyExchangeAmount() / $this->toDouble($record->currency_factor);
            $record->division_code = ($item->division_code) ? $item->division_code : $header->division_code;
            $record->business_unit_code = ($item->business_unit_code) ? $item->business_unit_code : $header->business_unit_code;
            $record->department_code = ($item->department_code) ? $item->department_code : $header->department_code;
            $record->project_code = ($item->project_code) ? $item->project_code : $header->project_code;

            $customer = Customer::select('vat_posting_group_code')->where('no', $header->customer_no)->first();
            if ($customer->vat_bus_posting_group_code && $record->vat_prod_posting_group_code) {
                $vat_post_group = VatPostingSetup::select('vat_calculation_type', 'vat_amount')
                    ->where('vat_bus_posting_group', $customer->vat_posting_group_code)
                    ->where('vat_prod_posting_group', $record->vat_prod_posting_group_code)->first();
                if ($vat_post_group) {
                    $record->vat_calculation_type = $vat_post_group->vat_calculation_type;
                    $record->vat_percentage = $vat_post_group->vat_amount;
                } else {
                    $record->vat_prod_posting_group_code = 'NOVAT';
                    $record->vat_calculation_type = 'VAT After Disc.';
                    $record->vat_percentage = 0;
                }
                $record->vat_bus_posting_group_code = $header->vat_bus_posting_group_code;
                $record->gen_bus_posting_group_code = $header->gen_bus_posting_group_code;
                $record->vat_bus_posting_group_code = $header->vat_bus_posting_group_code;
            } else {
                $record->vat_prod_posting_group_code = 'NOVAT';
                $record->vat_calculation_type = 'VAT After Disc.';
                $record->vat_percentage = 0;
            }
            //  end check VAT
            $record->type = 'Item';
            $record->created_by = Auth::user()->email;
            if ($item->auto_insert_specification == 'Yes') {
                $item_specifications = ItemSpecification::where('item_no', $record->no)->orderBy('id')->get();
                if ($item_specifications) {
                    foreach ($item_specifications as $item_specification) {
                        $specification = new SaleLine();
                        $specification->document_type = $header->document_type;
                        $specification->customer_no = $header->customer_no;
                        $specification->type = 'Text';
                        $specification->document_no = $header->no;
                        $line_no = $record->line_no + 10;
                        $specification->line_no = $line_no;
                        $specification->refer_line_no = $record->refer_line_no;
                        $specification->description = $item_specification->description;
                        $specification->description_2 = $item_specification->description_2;
                        $specification->created_by = Auth::user()->email;
                        $specification->save();
                    }
                }
            }
            return $record;
        } catch (\Exception $ex) {
            $description = $ex->getMessage();
            $this->SaveExcelUploadLog("Item", $description . " Line : " . $ex->getLine());
            return 'false';
        }
    }
    //======================== general static function
    public static function toRandomDigits($digits = 10)
    {
        return rand(pow(10, $digits - 1), pow(10, $digits) - 1);
    }
    function folderSize($dir)
    {
        $size = 0;
        foreach (glob(rtrim($dir, '/') . '/*', GLOB_NOSORT) as $each) {
            $size += is_file($each) ? filesize($each) : $this->folderSize($each);
        }
        return $size;
    }
    public function FileSizeConvert($bytes, $convertTo = 'MB')
    {
        $bytes = floatval($bytes);
        $arBytes = array(
            0 => array(
                "UNIT" => "TB",
                "VALUE" => pow(1024, 4)
            ),
            1 => array(
                "UNIT" => "GB",
                "VALUE" => pow(1024, 3)
            ),
            2 => array(
                "UNIT" => "MB",
                "VALUE" => pow(1024, 2)
            ),
            3 => array(
                "UNIT" => "KB",
                "VALUE" => 1024
            ),
            4 => array(
                "UNIT" => "B",
                "VALUE" => 1
            ),
        );

        foreach ($arBytes as $arItem) {
            if ($convertTo == $arItem["UNIT"]) {
                $result = $bytes / $arItem["VALUE"];
                $result = str_replace(".", ".", strval(round($result, 2))) . " " . $arItem["UNIT"];
                break;
            }
        }
        return $result;
    }
    public function validate_maximum_upload_size($file_path_company = '', $user = null)
    {
        return false;
        if (!$user) {
            $user = Auth::user();
        }
        $org = Organizations::where('id', $user->account_id)->first();
        if ($file_path_company) {
            $org_upload_path = OrganizationUploadPath::where('account_no', $user->account_id)->where('upload_path', $file_path_company)->first();
            if (!$org_upload_path) {
                $org_upload_path = new OrganizationUploadPath();
                $org_upload_path->account_no = $user->account_id;
                $org_upload_path->upload_path = $file_path_company;
                $org_upload_path->save();
            }
        }
        $org_upload_paths = OrganizationUploadPath::where('account_no', $user->account_id)->get();
        $total_upload_size_bytes = 0;
        foreach ($org_upload_paths as $org_upload_path) {
            $total_upload_size_bytes += $this->folderSize($org_upload_path->upload_path);
        }
        $total_upload_size_gbytes = $this->FileSizeConvert($total_upload_size_bytes, 'GB');
        if ($total_upload_size_gbytes >= service::toDouble($org->maximum_upload_size)) {
            return true;
        } else {
            return false;
        }
    }
    // ======================= Calcula latitude with actual latitude
    public static function haversineGreatCircleDistance($latitude, $longitude, $actual_latitude, $actual_longitude)
    {
        $details_url = 'https://maps.googleapis.com/maps/api/distancematrix/json?units=metric&origins=' . $latitude . ',' . $longitude . '&destinations=' . $actual_latitude . '%2C' . $actual_longitude . '&key=AIzaSyCrgzflhJRbGANfgJbmm-QwI2S8oFFQ2Qw';
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $details_url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        $geoloc = json_decode(curl_exec($ch), true);
        $get_distance = $geoloc['rows'][0]['elements'][0]['distance']['text'];
        return $get_distance;
    }
    public function getDistanceKm($latitude, $longitude, $actual_latitude, $actual_longitude){
        $details_url = 'https://maps.googleapis.com/maps/api/distancematrix/json?units=metric&origins=' . $latitude . ',' . $longitude . '&destinations=' . $actual_latitude . '%2C' . $actual_longitude . '&key=AIzaSyCrgzflhJRbGANfgJbmm-QwI2S8oFFQ2Qw';
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $details_url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        $geoloc = json_decode(curl_exec($ch), true);
        $get_distance = $geoloc['rows'][0]['elements'][0]['distance']['text'];
        $get_value_distance = $geoloc['rows'][0]['elements'][0]['distance']['value'];
        return ['text' => $get_distance, 'value' => $get_value_distance ];
    }
    public function webErrorRespoense($ex)
    {
        if (config('app.debug') == true) {
            if ($ex) {
                return 'line no:' . $ex->getLine() . ' message:' . $ex->getMessage();
            } else {
                return trans('greetings.Ooooop, something when wrong!');
            }
        } else {
            return trans('greetings.Ooooop, something when wrong!');
        }
    }
    public function apiErrorRespoense($ex, $request = null, $error_code = '0000', $response_code = '401')
    {
        $this->saveErrorLog($ex, null, $request);
        $message = trans('greetings.Oop, something when wrong!');
        if (config('app.debug') == true) {
            $message .= 'No:' . $ex->getLine() . ' ' . $ex->getMessage();
        }
        return response()->json([
            'status' => 'failed', 'response_code' => $response_code, 'error_code' => $error_code, 'message' => $message, 'mng' => $message, 'response_msg' => $message, 'err' => $message, 'error' => $message
        ]);
    }
    public function apiRespoense($status, $message, $error_code = '401')
    {
        return response()->json([
            'status' => $status, 'response_code' => $error_code, 'error_code' => $error_code, 'message' => $message, 'mng' => $message, 'response_msg' => $message, 'err' => $message, 'error' => $message
        ]);
    }
    public function ViewofPrinter($custom_template, $data, $file_name)
    {
        if (strtoupper($custom_template->custom_template) == 'CUSTOMIZE') {
            if ($data['data_type'] == 'A5') {
                $view = 'document.' . strtolower(Auth::user()->database_name) . '.A5.' . $file_name . '_A5';
            } elseif ($data['data_type'] == 'small_receipt') {
                $view = 'document.' . strtolower(Auth::user()->database_name) . '.small_receipt.' . $file_name . '_small_receipt';
            } else {
                $view = 'document.' . strtolower(Auth::user()->database_name) . '.' . $file_name;
            }
        } else {
            if ($data['data_type'] == 'A5') {
                $view = 'document.default.A5.' . $file_name . '_A5';
            } elseif ($data['data_type'] == 'small_receipt') {
                $view = 'document.default.small_receipt.' . $file_name . '_small_receipt';
            } else {
                $view = 'document.default.' . $file_name;
            }
        }
        return $view;
    }
    public function timeElapsedShort($secs)
    {
        $bit = array(
            'y' => $secs / 31556926 % 12,
            'w' => $secs / 604800 % 52,
            'd' => $secs / 86400 % 7,
            'h' => $secs / 3600 % 24,
            'm' => $secs / 60 % 60,
            's' => $secs % 60
        );

        foreach ($bit as $k => $v)
            if ($v > 0) $ret[] = $v . $k;

        return join(' ', $ret);
    }
    public function timeElapsedLong($secs)
    {
        $bit = array(
            ' year'        => $secs / 31556926 % 12,
            ' week'        => $secs / 604800 % 52,
            ' day'        => $secs / 86400 % 7,
            ' hour'        => $secs / 3600 % 24,
            ' minute'    => $secs / 60 % 60,
            ' second'    => $secs % 60
        );

        foreach ($bit as $k => $v) {
            if ($v > 1) $ret[] = $v . $k . 's';
            if ($v == 1) $ret[] = $v . $k;
        }
        array_splice($ret, count($ret) - 1, 0, 'and');
        $ret[] = 'ago.';

        return join(' ', $ret);
    }

    public function getSalespersonCustomerFilter()
    {
        if (Auth::check()) {
            $user_setup = Auth::user()->user_setup;
            if (!$user_setup) {
                return " 1 = 1";
            }
        } else {
            return " 1 = 1";
        }
        $customer_criteria = ' 1=1 ';
        if ($user_setup->salesperson_code) {
            $customer_filters = SalespersonCustomer::where('salesperson_code', $user_setup->salesperson_code)->where('inactived', 'No')->get();
            if (count($customer_filters) > 0) {
                $i = 0;
                $is_or_existed = false;
                foreach ($customer_filters as $customer_filter) {
                    $field = new PageGroupField();
                    $field->field_name = $customer_filter->field_name;
                    $field->field_data_type = $customer_filter->field_type;
                    if ($i == 0) {
                        $customer_criteria = $customer_criteria . ' ' . $customer_filter->field_condition . ' ' . $this->getSummarySearchCriteriasByFieldName($field, $customer_filter->field_condition_value);
                    } else {
                        $customer_criteria = $customer_criteria . ' ' . $customer_filter->field_condition . ' ' . $this->getSummarySearchCriteriasByFieldName($field, $customer_filter->field_condition_value);
                    }
                    if ($customer_filter->field_condition == 'or') $is_or_existed = true;
                    $i++;
                }
                if ($is_or_existed) {
                    $customer_criteria = str_replace('1=1 and', '1=2 or ', $customer_criteria);
                }
            }
        }
        return $customer_criteria;
    }
    public function getSalespersonCriteriaLevel()
    {
        if (Auth::check()) {
            $user_setup = Auth::user()->user_setup;
        } else {
            return " 1=1";
        }
        $salesperson_criteria = ' (1=1 ';
        if ($user_setup->salesperson_code) {
            $salesperson_filters = Salesperson::with('SalesPersonLevel.SalesPersonLevel.SalesPersonLevel.SalesPersonLevel.SalesPersonLevel')->where('code', $user_setup->salesperson_code)->where('inactived', 'No')->get();
            if (count($salesperson_filters) > 0) {
                foreach ($salesperson_filters as $salesperson_level) {
                    if ($salesperson_level) {
                        $salesperson_criteria .= " AND `salesperson_code`='" . $salesperson_level->code . "'";
                        foreach ($salesperson_level->SalesPersonLevel as $salesperson_level) {
                            if ($salesperson_level) {
                                $salesperson_criteria .= " OR `salesperson_code`='" . $salesperson_level->code . "'";
                                foreach ($salesperson_level->SalesPersonLevel as $salesperson_level) {
                                    if ($salesperson_level) {
                                        $salesperson_criteria .= " OR `salesperson_code`='" . $salesperson_level->code . "'";
                                        foreach ($salesperson_level->SalesPersonLevel as $salesperson_level) {
                                            if ($salesperson_level) {
                                                $salesperson_criteria .= " OR `salesperson_code`='" . $salesperson_level->code . "'";
                                                foreach ($salesperson_level->SalesPersonLevel as $salesperson_level) {
                                                    if ($salesperson_level) {
                                                        $salesperson_criteria .= " OR `salesperson_code`='" . $salesperson_level->code . "'";
                                                    }
                                                }
                                            }
                                        }
                                    }
                                }
                            }
                        }
                    }
                }
            }
        }
        $salesperson_criteria .= ")";
        return $salesperson_criteria;
    }
    public function checkCloseAccountPeriod($posting_date)
    {
        $user = Auth::user();
        $user_setup = $user->user_setup;
        $app_setup = $user->app_setup;
        // Level User
        $user_allow_posting_from = Carbon::parse($user_setup->allow_posting_from)->toDateString();
        $user_allow_posting_to = Carbon::parse($user_setup->allow_posting_to)->toDateString();


        if (Carbon::parse($posting_date) < Carbon::parse($user_allow_posting_from) || Carbon::parse($posting_date) > Carbon::parse($user_allow_posting_to)) {
            return trans('greetings.You do not have permission to post transaction on') . ' ' . Carbon::parse($posting_date)->format('d-M-Y');
        }
        // Level General
        $gen_allow_posting_from = Carbon::parse($app_setup->allow_posting_from)->toDateString();
        $gen_allow_posting_to = Carbon::parse($app_setup->allow_posting_to)->toDateString();
        if (Carbon::parse($posting_date) < Carbon::parse($user_allow_posting_from) || Carbon::parse($posting_date) > Carbon::parse($user_allow_posting_to)) {
            return trans('greetings.The system do not allow to post transaction on') . ' ' . Carbon::parse($posting_date)->format('d-M-Y');
        }
        // Accoutning Period
        $accounting_period = \App\Models\Financial\Setup\AccountingPeriod::whereRaw("'$posting_date' between starting_date and ending_date")
            ->whereRaw("(is_closed = 'Yes' OR is_income_closed = 'Yes')")->first();
        if ($accounting_period) {
            return trans('greetings.Accounting period closed.');
        }
        return 'success';
    }
    public function return_bytes($para)
    {
        $para = trim($para);
        $last = strtolower($para[strlen($para) - 1]);
        $value = trim(substr($para, 0, strlen($para) - 1));
        switch ($last) {
                // The 'G' modifier is available since PHP 5.1.0
            case 'g':
                $value = $value * (1024 * 1024 * 2014);
                break;
            case 'm':
                $value = $value * (1024 * 1024);
                break;
            case 'k':
                $value = $value * (1024);
                break;
            default:
                return $value;
                break;
        }
        return $value;
    }

    public static function getArraySalespersonCode()
    {
        $salespersons = Salesperson::whereNotNull('upline_data')->where('inactived', '<>', 'Yes')->orderBy('level', 'ASC')->get();
        $arr = array();
        foreach ($salespersons as $salesperson) {
            array_push($arr, $salesperson->upline_data);
        }
        return $arr;
    }

    public static function getSalespersonDownlineRecurring($salesperon, $me)
    {
        $results_string = '';
        $downlines = Salesperson::where('upline_code', $salesperon->code)->where('inactived', 'No')->get();
        if (count($downlines) > 0) {
            $results_string = $salesperon->code . ',';
            foreach ($downlines as $downline) {
                if ($downline->code == $me->code) {
                    return '';
                }
                $existing_downline = TempDownlineUplineBuffer::where('downline_code', $downline->code)->first();
                if ($existing_downline) {
                    return '';
                }
                $temp = new TempDownlineUplineBuffer();
                $temp->code = $me->code;
                $temp->downline_code = $downline->code;
                $temp->save();
                $results_string = $results_string . service::getSalespersonDownlineRecurring($downline, $me) . ',';
            }
        } else {
            return $salesperon->code;
        }
        return substr($results_string, 0, strlen($results_string) - 1);
    }
    public static function getSalespersonUplineRecurring($salesperon, $me)
    {
        $results_string = '';
        $results_string = $salesperon->upline_code . ',';
        $upline = Salesperson::where('code', $salesperon->upline_code)->where('inactived', 'No')->first();
        if ($upline) {
            if ($upline->code == $me->code) {
                return '';
            }
            $existing_upline = TempDownlineUplineBuffer::where('upline_code', $upline->code)->first();
            if ($existing_upline) {
                return '';
            }
            $temp = new TempDownlineUplineBuffer();
            $temp->code = $me->code;
            $temp->upline_code = $upline->code;
            $temp->save();
            $results_string = $results_string . service::getSalespersonUplineRecurring($upline, $me) . ',';
        } else {
            return $salesperon->upline_code;
        }
        return substr($results_string, 0, strlen($results_string) - 1);
    }
    public static function getSalespersonLevelRecurring($salesperon)
    {
        $level = '';
        $downlines = Salesperson::where('upline_code', $salesperon->code)->where('inactived', 'No')->get();
        if (count($downlines) > 0) {
            foreach ($downlines as $downline) {
                $level = $level + service::getSalespersonLevelRecurring($downline);
            }
        } else {
            return 1;
        }
        return $level;
    }
    
    public static function toCode($value)
    {
        $value = str_replace(' ', '_', $value);
        // Replace space khmer
        $value = str_replace('​', '_', $value);
        return preg_replace("/[^A-Za-z0-9]-_/", '', strtoupper(trim($value)));

        // $value = str_replace(' ', '_', $value);
        // $value = preg_replace('/[^A-Za-z0-9\-_.]/', '', $value);
        // return strtoupper(removeWhitespaceHelper($value));
    }

    public function headerValidation($fields, $header)
    {
        try {
            foreach ($fields as $field) {
                foreach ($field->page_group_field_mandatory() as $page_group_field) {
                    if ($page_group_field->mandatory == 1) {
                        if (!$header[$page_group_field->field_name]) {
                            return $page_group_field->field_description;
                        }
                    }
                }
            }
            return 'passed';
        } catch (\Exception $ex) {
            return 'failed';
        }
    }
    public function saveExcelUploadLog($table_name, $description = null, $record=null)
    {
        $excel_log = new ExcelUploadLog();
        $excel_log->username = Auth::user()->email;
        
        if(hasColumnHelper('excel_upload_log', 'document_no') && $record){
            $excel_log->document_no = isset($record->document_no)? $record->document_no : "";
            $excel_log->document_type = isset($record->document_type)? $record->document_type : "";
        }
        $excel_log->table_name = $table_name;
        $excel_log->description = $description;
        $excel_log->log_date = Carbon::now()->toDateString();
        $excel_log->log_datetime = Carbon::now();
        $excel_log->save();
    }
    public static function saveExcelUploadLogStatic($table_name, $description = null)
    {
        $excel_log = new ExcelUploadLog();
        $excel_log->username = Auth::user()->email;
        $excel_log->table_name = $table_name;
        $excel_log->description = $description;
        $excel_log->log_date = Carbon::now()->toDateString();
        $excel_log->log_datetime = Carbon::now();
        $excel_log->save();
    }
    public static function totalAmount($header, $lines, $is_return_arr_key=false)
    {
        try {
            $app_setup = Auth::user()->app_setup;
            if (!$header) {
                if($is_return_arr_key) return ['sub_total' => 0, 'total_disc' => 0, 'total_vat' => 0, 'amount_due'=> 0, 'total_amount_text' => 0, 'total_vat_plt' => 0];
                else return array(0, 0, 0, 0);
            }
            
            if (count($lines) == 0) {
                if($is_return_arr_key) return ['sub_total' => 0, 'total_disc' => 0, 'total_vat' => 0, 'amount_due'=> 0, 'total_amount_text' => 0, 'total_vat_plt' => 0];
                else return array(0, 0, 0, 0);
            }
            $subTotal = 0;
            $discountTotal = 0;
            $subtotalExcludeVAT = 0;
            $vatTotal = 0;
            $vatPLTTotal = 0;
            $amountDue = 0;
            $totalAmountText= 0;
            $currency = \App\Models\Administration\ApplicationSetup\Currency::where('code', $header->currency_code)->first();
            if ($header->price_include_vat == 'Yes') {
                foreach ($lines as $line) {
                    $line->currency = $currency;
                    $price_option_ration = 1;
                    $sales_amount_calc_option = $app_setup->sales_amount_calc_option;
                    if ($line->type == 'Item') {
                        if($line->price_option == 'By Length'){
                            if(service::toDouble($line->length) > 0) $price_option_ration = service::toDouble($line->length);
                        }else if($line->price_option == 'By Width'){
                            if(service::toDouble($line->width) > 0) $price_option_ration = service::toDouble($line->width);
                        }else if($line->price_option == 'By Height'){
                            if(service::toDouble($line->height) > 0) $price_option_ration = service::toDouble($line->height);
                        }else if($line->price_option == 'By Cubage'){
                            if(service::toDouble($line->cubage) > 0) $price_option_ration = service::toDouble($line->cubage);
                        }else if($line->price_option == 'By Weight'){
                            if(service::toDouble($line->weight) > 0) $price_option_ration = service::toDouble($line->weight);
                        }else if($line->price_option == 'By Qty Per Unit'){
                            if(service::toDouble($line->qty_per_unit_of_measure) > 0) $price_option_ration = service::toDouble($line->qty_per_unit_of_measure);
                        }else if($line->price_option == 'Width x Height'){
                            $price_option_ration = service::toDouble($line->width) * service::toDouble($line->height);
                        }
                        $item = Item::where('no', $line->no)->first();
                        if ($item) $sales_amount_calc_option = $item->sales_amount_calc_option;
                    }
                    if ($line->type != 'Text') {
                        if ($sales_amount_calc_option == 'Quantity 2') {
                            $subTotal += $price_option_ration * service::toDouble($line->quantity_size) * service::toDouble($line->unit_price);
                            $discountTotalPercentage = service::number_formattor_database(service::toDouble($line->quantity_size) * service::toDouble($line->unit_price)
                                * service::toDouble($line->discount_percentage) / 100, 'amount');
                            $discountTotal += $price_option_ration * $discountTotalPercentage + service::toDouble($line->discount_amount);
                            $subtotalExcludeVAT += service::toDouble($line->amount);
                            $vatTotal += service::toDouble($line->vat_amount);
                            $amountDue += service::toDouble($line->amount_including_vat);
                        } else {
                            $subTotal += $price_option_ration * service::toDouble($line->quantity) * service::toDouble($line->unit_price);
                            $sub_total_line = service::number_formattor_database($price_option_ration * service::toDouble($line->quantity) * service::toDouble($line->unit_price),  'amount');
                            $disc_percentage_amt = (service::toDouble($sub_total_line) * service::toDouble($line->discount_percentage)) / 100;

                            $discountTotalPercentage = service::number_formattor_database($disc_percentage_amt, 'amount');
                            $discountTotal += $price_option_ration * $discountTotalPercentage + service::toDouble($line->discount_amount);
                            $subtotalExcludeVAT += service::toDouble($line->amount);
                            $vatTotal += service::toDouble($line->vat_amount);
                            $amountDue += service::toDouble($line->amount_including_vat);
                            $vatPLTTotal += service::toDouble($line->plt_amount);
                        }
                    }

                    if ($line->type == 'Text') {
                        $totalAmountText += service::toDouble($line->amount_including_vat);
                    }
                }
                if($is_return_arr_key) {
                    return ['sub_total' => $subtotalExcludeVAT + $discountTotal, 'total_disc' => $discountTotal, 'total_vat' => $vatTotal, 'amount_due'=> $amountDue, 'total_amount_text' => $totalAmountText,'total_vat_plt' => $vatPLTTotal];
                }else {
                    return array($subtotalExcludeVAT + $discountTotal, $discountTotal, $vatTotal, $amountDue, $totalAmountText, $vatPLTTotal);
                }
            } else {
                $price_option_ration = 1;
                foreach ($lines as $line) {
                    $sales_amount_calc_option = $app_setup->sales_amount_calc_option;
                    $line->currency = $currency;
                    if ($line->type == 'Item') {
                        if($line->price_option == 'By Length'){
                            if(service::toDouble($line->length) > 0) $price_option_ration = service::toDouble($line->length);
                        }else if($line->price_option == 'By Width'){
                            if(service::toDouble($line->width) > 0) $price_option_ration = service::toDouble($line->width);
                        }else if($line->price_option == 'By Height'){
                            if(service::toDouble($line->height) > 0) $price_option_ration = service::toDouble($line->height);
                        }else if($line->price_option == 'By Cubage'){
                            if(service::toDouble($line->cubage) > 0) $price_option_ration = service::toDouble($line->cubage);
                        }else if($line->price_option == 'By Weight'){
                            if(service::toDouble($line->weight) > 0) $price_option_ration = service::toDouble($line->weight);
                        }else if($line->price_option == 'By Qty Per Unit'){
                            if(service::toDouble($line->qty_per_unit_of_measure) > 0) $price_option_ration = service::toDouble($line->qty_per_unit_of_measure);
                        }else if($line->price_option == 'Width x Height'){
                            $price_option_ration = service::toDouble($line->width) * service::toDouble($line->height);
                        }
                        $item = Item::where('no', $line->no)->first();
                        if ($item) $sales_amount_calc_option = $item->sales_amount_calc_option;
                    }
                    if ($line->type != 'Text') {
                        if ($sales_amount_calc_option == 'Quantity 2') {
                            $subTotal += $price_option_ration * service::toDouble($line->quantity_size) * service::toDouble($line->unit_price);
                            $discountTotalPercentage = service::number_formattor_database(service::toDouble($line->quantity_size) * service::toDouble($line->unit_price) * service::toDouble($line->discount_percentage) / 100, 'amount');
                            $discountTotal += $price_option_ration * $discountTotalPercentage + service::toDouble($line->discount_amount);
                            $vatTotal += service::toDouble($line->vat_amount);
                            $vatPLTTotal += service::toDouble($line->plt_amount);
                            $amountDue += service::toDouble($line->amount_including_vat);
                        } else {
                            $calc_subTotal = service::number_formattor_database($price_option_ration * (service::toDouble($line->quantity) * service::toDouble($line->unit_price)), 'amount');
                            $subTotal += service::number_formattor_database($calc_subTotal, 'amount');
                            
                            $disc_percentage_amt = (service::toDouble($calc_subTotal) * service::toDouble($line->discount_percentage)) / 100;
                            $discountTotalPercentage = service::number_formattor_database($disc_percentage_amt, 'amount');
                            //CHECK EXISTED COLUMN IN TABLE
                            $has_col = service::_isExistedTable('customer', 'price_decimal_after_dis');
                            if($has_col && $app_setup->price_decimal_after_dis != 'Yes') $has_col = false;
                            if($has_col) $customer = Customer::select('price_decimal_after_dis')->where('no',$header->customer_no)->first();
                            if($has_col && $customer && $customer->price_decimal_after_dis == 'Yes') {
                                // $price_after_disc = (service::toDouble($line->unit_price) * service::toDouble($line->discount_percentage)) / 100;
                                // $total_disc = service::toDouble($price_after_disc) * service::toDouble($line->quantity);
                                $discountTotal += $price_option_ration * (service::toDouble($calc_subTotal) - service::toDouble($line->amount));
                                
                            }else{
                                $discountTotal += $price_option_ration * $discountTotalPercentage + service::toDouble($line->discount_amount);
                            }
                            $vatTotal += service::toDouble($line->vat_amount);
                            $vatPLTTotal += service::toDouble($line->plt_amount);
                            $amountDue += service::toDouble($line->amount_including_vat);
                        }
                    }
                    if ($line->type == 'Text') {
                        $totalAmountText += service::toDouble($line->amount_including_vat);
                    }

                    // Reset Price option
                    $price_option_ration = 1;
                }
                
                $disc_percentage_diff_value = collect($lines)->where('type','Item')->unique('discount_percentage')->count();
                /**
                 * The settings is Calc discount percentage on whole invoice
                 * but all of the discount percentage must be equl
                 * @param one is Key
                 * @param two is value 'Yes|No'
                 */
                if(getSettingHelper(14, 'Yes') && $disc_percentage_diff_value == 1 && count($lines) > 0){
                    $get_first = collect($lines)->where('discount_percentage','>', 0)->sortByDesc('id')->first();
                    $sub_total = collect($lines)->where('discount_percentage','>', 0)->sum(function($record) use ($price_option_ration){
                        return service::number_formattor_database($price_option_ration * service::toDouble($record->quantity) * service::toDouble($record->unit_price), 'amount');
                    });
                    if($get_first) $discountTotal = (service::toDouble($sub_total) * $get_first->discount_percentage) / 100;
                }
                if($is_return_arr_key) {
                    return ['sub_total' => $subTotal, 'total_disc' => $discountTotal, 'total_vat' => $vatTotal,'total_vat_plt' => $vatPLTTotal, 'amount_due'=> $amountDue, 'total_amount_text' => $totalAmountText];
                }else {
                    return array($subTotal, $discountTotal, $vatTotal, $amountDue, $totalAmountText, $vatPLTTotal);
                }
            }
        }catch (\Exception $ex){
            return $ex;
          } 
    }
    public function totalAmountV2($header, $lines)
    {
        $app_setup = Auth::user()->app_setup;
        if (!$header) {
            return array(0, 0, 0, 0);
        }
        $subTotal = 0;
        $discountTotal = 0;
        $subtotalExcludeVAT = 0;
        $vatTotal = 0;
        $amountDue = 0;
        $currency = \App\Models\Administration\ApplicationSetup\Currency::where('code', $header->currency_code)->first();
        if ($header->price_include_vat == 'Yes') {
            foreach ($lines as $line) {
                $line->currency = $currency;
                $price_option_ration = 1;
                $sales_amount_calc_option = $app_setup->sales_amount_calc_option;
                if ($line->type == 'Item') {
                    $price_option_ration = $this->getPriceOption($line);
                    $item = Item::where('no', $line->no)->first();
                    if ($item) $sales_amount_calc_option = $item->sales_amount_calc_option;
                }
                
                if ($line->type != 'Text') {
                    if ($sales_amount_calc_option == 'Quantity 2') {
                        $subTotal += $price_option_ration * service::toDouble($line->quantity_size) * service::toDouble($line->unit_price);
                        $discountTotalPercentage = service::number_formattor_database(service::toDouble($line->quantity_size) * service::toDouble($line->unit_price)
                            * service::toDouble($line->discount_percentage) / 100, 'amount');
                        $discountTotal += $price_option_ration * $discountTotalPercentage + service::toDouble($line->discount_amount);
                        $subtotalExcludeVAT += service::toDouble($line->amount);
                        $vatTotal += service::toDouble($line->vat_amount);
                        $amountDue += service::toDouble($line->amount_including_vat);
                    } else {
                        $subTotal += $price_option_ration * service::toDouble($line->quantity) * service::toDouble($line->unit_price);
                        $discountTotalPercentage = service::number_formattor_database(service::toDouble($line->quantity) * service::toDouble($line->unit_price)
                            * service::toDouble($line->discount_percentage) / 100, 'amount');
                        $discountTotal += $price_option_ration * $discountTotalPercentage + service::toDouble($line->discount_amount);
                        $subtotalExcludeVAT += service::toDouble($line->amount);
                        $vatTotal += service::toDouble($line->vat_amount);
                        $amountDue += service::toDouble($line->amount_including_vat);
                    }
                }
            }
            return array($subtotalExcludeVAT + $discountTotal, $discountTotal, $vatTotal, $amountDue);
        } else {
            foreach ($lines as $line) {
                $price_option_ration = 1;
                $sales_amount_calc_option = $app_setup->sales_amount_calc_option;
                $line->currency = $currency;
                if ($line->type == 'Item') {
                    $price_option_ration = $this->getPriceOption($line);
                    $item = Item::where('no', $line->no)->first();
                    if ($item) $sales_amount_calc_option = $item->sales_amount_calc_option;
                }
                if ($line->type != 'Text') {
                    if ($sales_amount_calc_option == 'Quantity 2') {
                        $subTotal += $price_option_ration * service::toDouble($line->quantity_size) * service::toDouble($line->unit_price);
                        $discountTotalPercentage = service::number_formattor_database(service::toDouble($line->quantity_size) * service::toDouble($line->unit_price) * service::toDouble($line->discount_percentage) / 100, 'amount');
                        $discountTotal += $price_option_ration * $discountTotalPercentage + service::toDouble($line->discount_amount);
                        $vatTotal += service::toDouble($line->vat_amount);
                        $amountDue += service::toDouble($line->amount_including_vat);
                    } else {
                        $calc_subTotal = $price_option_ration * service::toDouble($line->quantity) * service::toDouble($line->unit_price);
                        $subTotal += service::number_formattor_database($calc_subTotal, 'amount');
                        $discountTotalPercentage = service::number_formattor_database(service::toDouble($line->quantity) * service::toDouble($line->unit_price) * (service::toDouble($line->discount_percentage) / 100), 'amount');
                        $discountTotal += $price_option_ration * $discountTotalPercentage + service::toDouble($line->discount_amount);
                        $vatTotal += service::toDouble($line->vat_amount);
                        $amountDue += service::toDouble($line->amount_including_vat);
                    }
                }
            }
            return array($subTotal, $discountTotal, $vatTotal, $amountDue);
        }
    }
    public static function totalAmountOldNotUse($header, $lines)
    {
        if (!$header) {
            return array(0, 0, 0, 0);
        }
        if ($header->price_include_vat == 'Yes') {
            $subTotal =  count($lines) > 0 ?  $lines->sum(function ($r) {
                return service::toDouble($r->quantity) * service::toDouble($r->unit_price);
            }) : 0;
            $subtotalExcludeVAT =  count($lines) > 0 ?  $lines->sum(function ($r) {
                return service::toDouble($r->amount);
            }) : 0;

            $discountTotal = count($lines) > 0 ? $lines->sum(function ($r) {
                return (service::toDouble($r->quantity) * service::toDouble($r->unit_price) * service::toDouble($r->discount_percentage) / 100) + service::toDouble($r->discount_amount);
            }) : 0;
            $vatTotal = count($lines) > 0 ? $lines->sum(function ($r) {
                return service::toDouble($r->vat_amount);
            }) : 0;
            $amountDue = count($lines) > 0 ? $lines->sum(function ($r) {
                return service::toDouble($r->amount_including_vat);
            }) : 0;

            return array($subtotalExcludeVAT + $discountTotal, $discountTotal, $vatTotal, $amountDue, $subtotalExcludeVAT);
        } else {
            $subTotal =  count($lines) > 0 ?  $lines->sum(function ($r) {
                return service::toDouble($r->quantity) * service::toDouble($r->unit_price);
            }) : 0;

            $discountTotal = count($lines) > 0 ? $lines->sum(function ($r) {
                return (service::toDouble($r->quantity) * service::toDouble($r->unit_price) * service::toDouble($r->discount_percentage) / 100) + service::toDouble($r->discount_amount);
            }) : 0;
            $vatTotal = count($lines) > 0 ? $lines->sum(function ($r) {
                return service::toDouble($r->vat_amount);
            }) : 0;
            $amountDue = count($lines) > 0 ? $lines->sum(function ($r) {
                return service::toDouble($r->amount_including_vat);
            }) : 0;
            return array($subTotal, $discountTotal, $vatTotal, $amountDue, $subTotal);
        }
    }

    public static function totalSalesAmount($header, $lines)
    {
        if (!$header) {
            return array(
                'amount_excluded_vat' => 0, 'discount_amount' => 0, 'vat_amount' => 0, 'amount_included_vat' => 0, 'amount_excluded_vat_after_discount' => 0
            );
        }
        if ($header->price_include_vat == 'Yes') {
            $subTotal =  count($lines) > 0 ?  $lines->sum(function ($r) {
                return service::toDouble($r->quantity) * service::toDouble($r->unit_price);
            }) : 0;
            $subtotalExcludeVAT =  count($lines) > 0 ?  $lines->sum(function ($r) {
                return service::toDouble($r->amount);
            }) : 0;
            $discountTotal = count($lines) > 0 ? $lines->sum(function ($r) {
                return (service::toDouble($r->quantity) * service::toDouble($r->unit_price) * service::toDouble($r->discount_percentage) / 100) + service::toDouble($r->discount_amount);
            }) : 0;
            $vatTotal = count($lines) > 0 ? $lines->sum(function ($r) {
                return service::toDouble($r->vat_amount);
            }) : 0;
            $amountDue = count($lines) > 0 ? $lines->sum(function ($r) {
                return service::toDouble($r->amount_including_vat);
            }) : 0;
            return array(
                'amount_excluded_vat' => $subtotalExcludeVAT, 'discount_amount' => $discountTotal, 'vat_amount' => $vatTotal, 'amount_included_vat' => $amountDue, 'amount_excluded_vat_included_discount' => $subtotalExcludeVAT + $discountTotal
            );
        } else {
            $subTotal =  count($lines) > 0 ?  $lines->sum(function ($r) {
                return service::toDouble($r->quantity) * service::toDouble($r->unit_price);
            }) : 0;

            $discountTotal = count($lines) > 0 ? $lines->sum(function ($r) {
                return (service::toDouble($r->quantity) * service::toDouble($r->unit_price) * service::toDouble($r->discount_percentage) / 100) + service::toDouble($r->discount_amount);
            }) : 0;
            $vatTotal = count($lines) > 0 ? $lines->sum(function ($r) {
                return service::toDouble($r->vat_amount);
            }) : 0;
            $amountDue = count($lines) > 0 ? $lines->sum(function ($r) {
                return service::toDouble($r->amount_including_vat);
            }) : 0;
            return array(
                'amount_excluded_vat' => $subTotal, 'discount_amount' => $discountTotal, 'vat_amount' => $vatTotal, 'amount_included_vat' => $amountDue, 'amount_excluded_vat_included_discount' => $subTotal + $discountTotal
            );
        }
    }

    public static function totalPOSSalesAmount($header, $lines, $other_currency_rate)
    {
        if (!$header) {
            return array(
                'amount_excluded_vat' => 0, 'discount_amount' => 0, 'vat_amount' => 0, 'amount_included_vat' => 0, 'amount_excluded_vat_after_discount' => 0, 'inv_discount_percentage' => 0, 'inv_discount_amount' => 0, 'payment_amount' => 0, 'change_in_usd' => 0, 'change_in_other' => 0
            );
        }
        if ($header->price_include_vat == 'Yes') {
            $sub_total_excluded_vat =  count($lines) > 0 ?  $lines->sum(function ($r) {
                $unit_price_exclude_vat =  service::toDouble($r->unit_price) / (1 + (service::toDouble($r->vat_percentage) / 100));
                return service::toDouble($r->quantity) * service::toDouble($unit_price_exclude_vat);
            }) : 0;
            $amount_excluded_vat =  count($lines) > 0 ?  $lines->sum(function ($r) {
                return service::toDouble($r->amount);
            }) : 0;

            $discount_amount = count($lines) > 0 ? $lines->sum(function ($r) {
                return (service::toDouble($r->quantity) * service::toDouble($r->unit_price) * service::toDouble($r->discount_percentage) / 100) + service::toDouble($r->discount_amount);
            }) : 0;
            $vat_amount = count($lines) > 0 ? $lines->sum(function ($r) {
                return service::toDouble($r->vat_amount);
            }) : 0;
            $amount_included_vat = count($lines) > 0 ? $lines->sum(function ($r) {
                return service::toDouble($r->amount_including_vat);
            }) : 0;
            $inv_discount_percentage = service::toDouble($header->payment_discount_percentage);
            $inv_discount_amount = service::toDouble($header->payment_discount_percentage) * $amount_included_vat / 100;
            $amount_included_vat = $amount_included_vat - $inv_discount_amount;
            $amount_excluded_vat = $amount_excluded_vat - $inv_discount_amount;
            $payment_amount = service::toDouble($header->payment_amount);
            $amount_included_vat_other = service::toDouble($amount_included_vat) * service::toDouble($other_currency_rate);
            if ($payment_amount > $amount_included_vat) {
                $change_in_usd = $payment_amount - $amount_included_vat;
                $change_in_other = service::toDouble($change_in_usd) * service::toDouble($other_currency_rate);
            } else {
                $change_in_usd = 0;
                $change_in_other = 0;
            }
            return array(
                'amount_excluded_vat' => $amount_excluded_vat, 'discount_amount' => $discount_amount, 'vat_amount' => $vat_amount, 'amount_included_vat' => $amount_included_vat, 'sub_total_excluded_vat' => $sub_total_excluded_vat, 'inv_discount_percentage' => $inv_discount_percentage, 'inv_discount_amount' => $inv_discount_amount, 'payment_amount' => $payment_amount, 'amount_included_vat_other' => $amount_included_vat_other, 'change_in_usd' => $change_in_usd, 'change_in_other' => $change_in_other, 'amount_excluded_vat_included_discount' => $amount_excluded_vat + $discount_amount + $inv_discount_amount
            );
        } else {
            $sub_total_excluded_vat =  count($lines) > 0 ?  $lines->sum(function ($r) {
                return service::toDouble($r->quantity) * service::toDouble($r->unit_price);
            }) : 0;
            $amount_excluded_vat =  count($lines) > 0 ?  $lines->sum(function ($r) {
                return service::toDouble($r->amount);
            }) : 0;
            $discount_amount = count($lines) > 0 ? $lines->sum(function ($r) {
                return (service::toDouble($r->quantity) * service::toDouble($r->unit_price) * service::toDouble($r->discount_percentage) / 100) + service::toDouble($r->discount_amount);
            }) : 0;
            $vat_amount = count($lines) > 0 ? $lines->sum(function ($r) {
                return service::toDouble($r->vat_amount);
            }) : 0;
            $amount_included_vat = count($lines) > 0 ? $lines->sum(function ($r) {
                return service::toDouble($r->amount_including_vat);
            }) : 0;
            $inv_discount_percentage = service::toDouble($header->payment_discount_percentage);
            $inv_discount_amount = service::toDouble($header->payment_discount_percentage) * $amount_included_vat / 100;
            $amount_included_vat = $amount_included_vat - $inv_discount_amount;
            $amount_excluded_vat = $amount_excluded_vat - $inv_discount_amount;
            $payment_amount = service::toDouble($header->payment_amount);
            $amount_included_vat_other = service::toDouble($amount_included_vat) * service::toDouble($other_currency_rate);
            if ($payment_amount > $amount_included_vat) {
                $change_in_usd = $payment_amount - $amount_included_vat;
                $change_in_other = service::toDouble($change_in_usd) * service::toDouble($other_currency_rate);;
            } else {
                $change_in_usd = 0;
                $change_in_other = 0;
            }
            return array(
                'amount_excluded_vat' => $amount_excluded_vat, 'discount_amount' => $discount_amount, 'vat_amount' => $vat_amount, 'amount_included_vat' => $amount_included_vat, 'sub_total_excluded_vat' => $sub_total_excluded_vat, 'inv_discount_percentage' => $inv_discount_percentage, 'inv_discount_amount' => $inv_discount_amount, 'payment_amount' => $payment_amount, 'change_in_usd' => $change_in_usd, 'amount_included_vat_other' => $amount_included_vat_other, 'change_in_other' => $change_in_other, 'amount_excluded_vat_included_discount' => $amount_excluded_vat + $discount_amount + $inv_discount_amount
            );
        }
    }

    public function SaveWorkflowActivity($header, $activity, $description, $document_type, $url)
    {
        $workflow_activity = new WorkflowActivity();
        $workflow_activity->activity = $activity;
        $workflow_activity->description = $description;
        $workflow_activity->user_name = Auth::user()->user_setup['email'];
        $workflow_activity->name = Auth::user()['name'];
        $workflow_activity->document_type = $document_type;
        $workflow_activity->entry_date = Carbon::now();
        $workflow_activity->entry_datetime = Carbon::now();
        $workflow_activity->due_date = Carbon::now();
        $workflow_activity->document_no = $header->no;
        $workflow_activity->document_url = '/service-ticket/transaction?type=ed&code=' . service::encrypt($header->no);
        $workflow_activity->save();
    }
    public function isExistGLEntry($record, $document_no, $account_no, $type = '')
    {
        $user = Auth::user();
        $app_setup = $user->app_setup;
        if ($app_setup->sales_gl_compress != 'Yes' || $type == '') return null;
        $criteria = '';
        if ($record->item_category_code) {
            $criteria = $criteria . "item_category_code = '" . $record->item_category_code . "' and ";
        } else {
            $criteria = $criteria . "item_category_code is null and ";
        }
        if ($record->item_group_code) {
            $criteria = $criteria . "item_group_code = '" . $record->item_group_code . "' and ";
        } else {
            $criteria = $criteria . "item_group_code is null and ";
        }
        if ($record->item_brand_code) {
            $criteria = $criteria . "item_brand_code = '" . $record->item_brand_code . "' and ";
        } else {
            $criteria = $criteria . "item_brand_code is null and ";
        }
        if ($record->store_code) {
            $criteria = $criteria . "store_code = '" . $record->store_code . "' and ";
        } else {
            $criteria = $criteria . "store_code is null and ";
        }
        if ($record->division_code) {
            $criteria = $criteria . "division_code = '" . $record->division_code . "' and ";
        } else {
            $criteria = $criteria . "division_code is null and ";
        }
        if ($record->business_unit_code) {
            $criteria = $criteria . "business_unit_code = '" . $record->business_unit_code . "' and ";
        } else {
            $criteria = $criteria . "business_unit_code is null and ";
        }
        if ($record->department_code) {
            $criteria = $criteria . "department_code = '" . $record->department_code . "' and ";
        } else {
            $criteria = $criteria . "department_code is null and ";
        }
        if ($record->project_code) {
            $criteria = $criteria . "project_code = '" . $record->project_code . "' and ";
        } else {
            $criteria = $criteria . "project_code is null and ";
        }
        if ($type == 'debit') {
            $criteria = $criteria . ' and amount >= 0 and 1 = 1';
        } else {
            $criteria = $criteria . ' and amount <0 and 1 = 1';
        }
        $general_ledger_entry = GeneralLedgerEntry::where('document_no', $document_no)->where('account_no', $account_no)->whereRaw($criteria)->first();
        return $general_ledger_entry;
    }
    public function IndentLevel()
    {
        //purshser init.
        $options = array(
            'cluster' => 'ap1',
            'encrypted' => true
        );
        $pusher = new Pusher(
            config('app.pusher_key'),
            config('app.pusher_secret'),
            config('app.pusher_id'),
            $options
        );
        $salesperson = Salesperson::where('code', Auth::user()->user_setup->salesperson_code)->first();
        $records = Salesperson::where('inactived', '<>', 'Yes')->get();
        \DB::connection('company')->beginTransaction();
        try {
            if ($records) {
                $index = 0;
                $count_row = count($records);
                $no_of_succeed_row = 0;
                $no_of_push = 0;
                foreach ($records as $record) {
                    //============================ Processing '.......'
                    $no_of_succeed_row++;
                    $no_of_succeed_row_percentage = ' ' . number_format($no_of_succeed_row / $count_row * 100, 0);
                    if ($no_of_push != $no_of_succeed_row_percentage) {
                        $data['message'] = trans('greetings.Processing Indent Level') . ' -> ' . $no_of_succeed_row_percentage . ' %';
                        $pusher->trigger('Salesperson.IndentLevel.' . Auth::user()->id, 'IndentLevel', $data['message']);
                    }
                    $no_of_push = $no_of_succeed_row_percentage;


                    $index++;
                    // ===================== VALIDATE ERROR UPLINE =======================
                    // ===================== VALIDATE ERROR DOWNLOAD =======================                
                    $donwline = $record->getDownlineSalespersonCode();
                    $upline = $record->getUplineSalespersonCode();
                    $record->downline_data = $donwline;
                    $record->upline_data = $upline;
                    if ($record->upline_data == null || $record->upline_data == '') {
                        $record->level = 0;
                    } else {
                        $record->level = count(explode(',', $record->upline_data));
                    }
                    $record->level_index = 0;
                    $record->save();
                    $downline_data = explode(',', $donwline);
                    SalespersonSalesperson::where('upline_code', $record->code)->delete();
                    foreach ($downline_data as $key => $value) {
                        $downline = new SalespersonSalesperson();
                        $downline->upline_code = $record->code;
                        $downline->downline_code = $value;
                        $downline->save();
                    }
                }
            }
            // ============== Crated Level index =====================   
            $records = Salesperson::where('inactived', '<>', 'Yes')->orderBy('level', 'asc')->orderBy('upline_code', 'asc')->get();
            $befor_index_level = 0;
            $last = 0;
            $j = 0;
            $index = 1;
            $old_upline_code = '';
            $count_row = count($records);
            $no_of_succeed_row = 0;
            $no_of_push = 0;
            foreach ($records as $record) {
                //============================ Processing '.......'
                $no_of_succeed_row++;
                $no_of_succeed_row_percentage = ' ' . number_format($no_of_succeed_row / $count_row * 100, 0);
                if ($no_of_push != $no_of_succeed_row_percentage) {
                    $data['message'] = trans('greetings.Processing Set Level') . ' -> ' . $no_of_succeed_row_percentage . ' %';
                    $pusher->trigger('Salesperson.IndentLevel.' . Auth::user()->id, 'IndentLevel', $data['message']);
                }
                $no_of_push = $no_of_succeed_row_percentage;


                $upline = $record->getUpline();
                if ($record->level == 0) {
                    $befor_index_level = $befor_index_level + 1000000;
                    $record->level_index = $befor_index_level;
                } else {
                    if ($record->upline_code) {
                        $new_level = (int)substr('1000000', 0, -$record->level);
                        if ($old_upline_code != $record->upline_code) {
                            $last = 0;
                            $j = 0;
                            $index = 0;
                        }
                        $level_upline = Salesperson::select('level', 'level_index', 'code')->where('code', $record->upline_code)->first();
                        if ($level_upline) {
                            $old_upline_code = $level_upline->code;
                            if ($record->upline_code == $old_upline_code) {
                                $j++;
                                $index++;
                                if ($j == 1) {
                                    $new_level = $level_upline->level_index + $new_level;
                                    $last = $new_level;
                                } else {
                                    $new_level = $last + $new_level;
                                    $last = $new_level;
                                }
                            } else {
                                $new_level = $this->service->toDouble($level_upline->level_index) + $new_level;
                            }
                            $record->level_index =  $new_level;
                        }
                    }
                }
                $record->save();
            }
            \DB::connection('company')->commit();
            return 'success';
        } catch (\Exception $ex) {
            \DB::connection('company')->rollback();
            $this->saveErrorLog($ex);
            return $ex;
        }
    }
    public function IndentLevelOrganizationChart()
    {
        //purshser init.
        $options = array(
            'cluster' => 'ap1',
            'encrypted' => true
        );
        $pusher = new Pusher(
            config('app.pusher_key'),
            config('app.pusher_secret'),
            config('app.pusher_id'),
            $options
        );
        $salesperson = Salesperson::where('code', Auth::user()->user_setup->salesperson_code)->first();
        $records = Salesperson::where('inactived', '<>', 'Yes')->get();
        \DB::connection('company')->beginTransaction();
        try {
            if ($records) {
                $index = 0;
                $count_row = count($records);
                $no_of_succeed_row = 0;
                $no_of_push = 0;
                foreach ($records as $record) {
                    //============================ Processing '.......'
                    $no_of_succeed_row++;
                    $no_of_succeed_row_percentage = ' ' . number_format($no_of_succeed_row / $count_row * 100, 0);
                    if ($no_of_push != $no_of_succeed_row_percentage) {
                        $data['message'] = trans('greetings.Processing Indent Level') . ' -> ' . $no_of_succeed_row_percentage . ' %';
                        $pusher->trigger('Salesperson.IndentLevel.' . Auth::user()->id, 'IndentLevel', $data['message']);
                    }
                    $no_of_push = $no_of_succeed_row_percentage;


                    $index++;
                    // ===================== VALIDATE ERROR UPLINE =======================
                    // ===================== VALIDATE ERROR DOWNLOAD =======================                
                    $donwline = $record->getDownlineSalespersonCode();
                    $upline = $record->getUplineSalespersonCode();
                    $record->downline_data = $donwline;
                    $record->upline_data = $upline;
                    if ($record->upline_data == null || $record->upline_data == '') {
                        $record->level = 0;
                    } else {
                        $record->level = count(explode(',', $record->upline_data));
                    }
                    $record->level_index = 0;
                    $record->save();
                    $downline_data = explode(',', $donwline);
                    SalespersonSalesperson::where('upline_code', $record->code)->delete();
                    foreach ($downline_data as $key => $value) {
                        $downline = new SalespersonSalesperson();
                        $downline->upline_code = $record->code;
                        $downline->downline_code = $value;
                        $downline->save();
                    }
                }
            }
            // ============== Crated Level index =====================   
            $records = Salesperson::where('inactived', '<>', 'Yes')->orderBy('level', 'asc')->orderBy('upline_code', 'asc')->get();
            $befor_index_level = 0;
            $last = 0;
            $j = 0;
            $index = 1;
            $old_upline_code = '';
            $count_row = count($records);
            $no_of_succeed_row = 0;
            $no_of_push = 0;
            foreach ($records as $record) {
                //============================ Processing '.......'
                $no_of_succeed_row++;
                $no_of_succeed_row_percentage = ' ' . number_format($no_of_succeed_row / $count_row * 100, 0);
                if ($no_of_push != $no_of_succeed_row_percentage) {
                    $data['message'] = trans('greetings.Processing Set Level') . ' -> ' . $no_of_succeed_row_percentage . ' %';
                    $pusher->trigger('Salesperson.IndentLevel.' . Auth::user()->id, 'IndentLevel', $data['message']);
                }
                $no_of_push = $no_of_succeed_row_percentage;


                $upline = $record->getUpline();
                if ($record->level == 0) {
                    $befor_index_level = $befor_index_level + 1000000;
                    $record->level_index = $befor_index_level;
                } else {
                    if ($record->upline_code) {
                        $new_level = (int)substr('1000000', 0, -$record->level);
                        if ($old_upline_code != $record->upline_code) {
                            $last = 0;
                            $j = 0;
                            $index = 0;
                        }
                        $level_upline = Salesperson::select('level', 'level_index', 'code')->where('code', $record->upline_code)->first();
                        if ($level_upline) {
                            $old_upline_code = $level_upline->code;
                            if ($record->upline_code == $old_upline_code) {
                                $j++;
                                $index++;
                                if ($j == 1) {
                                    $new_level = $level_upline->level_index + $new_level;
                                    $last = $new_level;
                                } else {
                                    $new_level = $last + $new_level;
                                    $last = $new_level;
                                }
                            } else {
                                $new_level = $this->service->toDouble($level_upline->level_index) + $new_level;
                            }
                            $record->level_index =  $new_level;
                        }
                    }
                }
                $record->save();
            }
            \DB::connection('company')->commit();
            return 'success';
        } catch (\Exception $ex) {
            \DB::connection('company')->rollback();
            $this->saveErrorLog($ex);
            return $ex;
        }
    }
    function grandTotal($header)
    {
        $lines = POSSaleLine::where('document_type', $header->document_type)->where('document_no', $header->no)->get();

        $amountDue = count($lines) > 0 ? $lines->sum(function ($r) {
            return service::toDouble($r->amount) + service::toDouble($r->discount_amount);
        }) : 0;
        $grandTotal = count($lines) > 0 ? $lines->sum(function ($r) {
            return service::toDouble($r->amount_including_vat);
        }) : 0;

        $subTotal = count($lines) > 0 ? $lines->sum(function ($r) {
            return service::toDouble($r->quantity) * service::toDouble($r->unit_price);
        }) : 0.00;
        $discountTotal = count($lines) > 0 ? $lines->sum(function ($r) {
            return (service::toDouble($r->quantity) * service::toDouble($r->unit_price) * service::toDouble($r->discount_percentage) / 100);
        }) : 0.00;
        // $discountTotal = count($lines) > 0 ? $lines->sum(function ($r) {
        //     return (service::toDouble($r->quantity) * service::toDouble($r->unit_price) * service::toDouble($r->discount_percentage) / 100) + service::toDouble($r->discount_amount);
        // }) : 0.00;
        $vatTotal = count($lines) > 0 ? $lines->sum(function ($r) {
            return service::toDouble($r->vat_amount);
        }) : 0.00;

        $cur_khr = $this->getCurrencyFactor('KHR', Carbon::parse($header->posting_date)->toDateString());

        $amountDueKhr = $amountDue * service::toDouble($cur_khr);
        $grandTotalKhr = $grandTotal  * service::toDouble($cur_khr);
        return [
            'amountDue' => service::number_formattor_link($amountDue, 'amount'),
            'amountDueKhr' => service::number_formattor_link($amountDueKhr, 'general'),
            'grandTotalKhr' => service::number_formattor_link($grandTotalKhr, 'amount'),
            'subTotal' => service::number_formattor_link($subTotal, 'amount'),
            'discountTotal' => service::number_formattor_link($discountTotal, 'amount'),
            'vatTotal' => ($vatTotal) ? service::number_formattor_link($vatTotal, 'amount') : 0,
            'grandTotal' =>  service::number_formattor_link($grandTotal, 'amount'),
        ];
    }
    public static function roundQty($qty)
    {
        return service::number_formattor_link($qty, 'quantity');
    }
    public function checkAllTicked($records, $tablename, $key = 'id', $page_id = null)
    {
        $user = Auth::user();
        $condition = ($page_id <> null) ? " and page_id = $page_id " : " and 1 =1  ";
        if(hasTableHelper("sv_table_record_marked")){
            Config::set('database.connections.company.strict', false);
            \DB::purge('company');
            $count_check =  DB::connection('company')->table("sv_table_record_marked")
                ->whereraw("primary_key_field_name='$key' $condition and table_name='$tablename' and username='$user->email'")
                ->first();
            Config::set('database.connections.company.strict', true);
            \DB::purge('company');
            $count_check = $count_check? $count_check->records_checked : 0;
        }else{
            $count_check =  DB::connection('company')->table($tablename)->whereraw(" " . $key . " 
                in (select primary_key_field_value from table_record_marked where table_name = '" . $tablename . "' 
                and primary_key_field_name = '" . $key . "' $condition  and username = '$user->email')")->count();
        }
        
        $check_all = "No";
        if ($records->count() <= $count_check) {
            $check_all = 'Yes';
        }
        return $check_all;
    }
    public function checkAllTickedReturnArray($records, $tablename, $key_value, $criteria = '1=1', $page_id = null)
    {
        $user = Auth::user();
        $condition = ($page_id <> null) ? " and page_id = $page_id " : " and 1 =1  ";
        if(hasTableHelper("sv_table_record_marked")){
            Config::set('database.connections.company.strict', false);
            \DB::purge('company');
            
            $count_check =  DB::connection('company')->table("sv_table_record_marked")
                ->whereraw("primary_key_field_name='$key_value' $condition and table_name='$tablename' and username='$user->email'")
                ->first();
            Config::set('database.connections.company.strict', true);
            \DB::purge('company');
            
            $count_check = ($count_check)? $count_check->records_checked : 0;
        }else{
            $count_check =  \DB::connection('company')->table($tablename)->whereraw($key_value . " in (select primary_key_field_value from table_record_marked 
                where table_name = '" . $tablename . "' and primary_key_field_name = '" . $key_value . "' and username = '$user->email' $condition)")
                ->whereRaw($criteria)->count();
        }
        
        $check_all = 'No';
        if ($records->total() == $count_check) {
            $check_all = 'Yes';
        } else {
            if ($count_check > 0) $check_all = 'HasMarkedRecord';
        }
        return ['check_all' => $check_all, 'count_check' => $count_check];
    }
    public function sortOrder($tablename, $orderByField = 'id')
    {
        $lstFieldsSort = $this->getTableFieldSortbyUser($tablename);
        if (count($lstFieldsSort) <= 0) {
            $sorts = $orderByField . ' asc';
        } else {
            $sorts = $this->getTableAjaxPaginationSort($lstFieldsSort);
        }
        return $sorts;
    }

    public static function checkTableExisted($table_name)
    {
        $record = DB::table('information_schema.tables')->where('table_schema', Auth::user()->database_name)->where('table_name', $table_name)->first();
        if ($record) {
            return true;
        } else {
            return false;
        }
    }

    public static function getGeoDistance($lat1, $lon1, $lat2, $lon2, $unit = 'km', $rounding = 0)
    {
        $theta = $lon1 - $lon2;
        $miles = (sin(deg2rad($lat1)) * sin(deg2rad($lat2))) + (cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * cos(deg2rad($theta)));
        $miles = acos($miles);
        $miles = rad2deg($miles);
        $miles = $miles * 60 * 1.1515;
        $feet = $miles * 5280;
        $yards = $feet / 3;
        $kilometers = $miles * 1.609344;
        $meters = $kilometers * 1000;
        if (strtolower($unit) == 'km') {
            return round($kilometers, $rounding);
        } else if (strtolower($unit) == 'me') {
            return round($meters, $rounding);
        } else if (strtolower($unit) == 'ya') {
            return round($yards, $rounding);
        } else if (strtolower($unit) == 'fe') {
            return round($feet, $rounding);
        } else if (strtolower($unit) == 'fe') {
            return round($miles, $rounding);
        }
    }

    public static function sendNotification($fcmNotificationData)
    {
        try {
            $fcmUrl = 'https://fcm.googleapis.com/fcm/send';
            $headers = [
                'Authorization: key=' . Config::get('app.salesforce_fcm_legacy_server_key'),
                'Content-Type: application/json'
            ];
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $fcmUrl);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($fcmNotificationData));
            $result = curl_exec($ch);
            curl_close($ch);
            return $result;
        } catch (\Exception $ex) {
            return $ex->getMessage();
        }
    }
    public static function sendNotificationTopic($to, $notif)
    {
        try {
            $ch = curl_init();
            $url = 'https://fcm.googleapis.com/fcm/send';
            $fields = json_encode(array('to' => $to, 'notification' => $notif));

            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $fields);

            $header = array();
            $header[] = 'Authorization: key =' . Config::get('app.salesforce_fcm_legacy_server_key');
            $header[] =  'Content-Type: application/json';
            curl_setopt($ch, CURLOPT_HTTPHEADER, $header);

            $result = curl_exec($ch);
            if (curl_errno($ch)) {
                return false; 
            }
            curl_close($ch);
            return true; 
        } catch (\Exception $ex) {
            return null;
        }
    }
    
    public static function FirebaseRealTimeDatabase()
    {
        try {
            $path_service_account = public_path();
            $url = "";
            if (config('app.env') == 'production') {
                $url = "https://clearview-erp.firebaseio.com";
                $path_service_account .= "/Firebase/Production/ClearViewERPServiceAccount.json";
            } else {
                $url = "https://clearview-erp-development.firebaseio.com";
                $path_service_account .= "/Firebase/Development/ClearViewERPServiceAccount.json";
            }
            $serviceAccount = ServiceAccount::fromJsonFile($path_service_account);
            // ========= Create new ===
            $firebase = (new Factory)->withServiceAccount($serviceAccount)->withDatabaseUri($url)->create();
            return $firebase;
        } catch (\Exception $ex) {
            (new self)->saveErrorLog($ex);
            return null;
        }
    }
    public static function convertNumberTowords($num)
    {
        $ones = array(
            0 => "Zero", '01' => "Zero One", '02' => 'Zero Two', '03' => 'Zero Three', '04' => 'Zero Four', '05' => 'Zero Five', '06' => 'Zero Six',
            '07' => 'Zero Seven', '08' => 'Zero Eight', '09' => 'Zero Nine', 1 => "One", 2 => "Two", 3 => "Three", 4 => "Four", 5 => "Five", 6 => "Six",
            7 => "Seven", 8 => "Eight", 9 => "Nine", 10 => "Ten", 11 => "Eleven", 12 => "Twelve", 13 => "Thirteen", 14 => "Fourteen", 15 => "Fifteen",
            16 => "Sixteen", 17 => "Seventeen", 18 => "Eighteen", 19 => "Nineteen"
        );
        $tens = array(
            0 => "Zero", 1 => "Ten", 11 => "Eleven", 12 => "Twelve", 13 => "Thirteen", 14 => "Fourteen", 15 => "Fifteen", 16 => "Sixteen", 17 => "Seventeen", 18 => "Eighteen", 19 => "Nineteen",
            2 => "Twenty", 21 => "Twenty-one", 22 => "Twenty-two", 23 => "Twenty-three", 24 => "Twenty-four", 25 => "Twenty-five", 26 => "Twenty-six", 27 => "Twenty-seven", 28 => "Twenty-eight", 29 => "Twenty-nine",
            3 => "Thirty", 31 => "Thirty-one", 32 => "Thirty-two", 33 => "Thirty-three", 34 => "Thirty-four", 35 => "Thirty-five", 36 => "Thirty-six", 37 => "Thirty-sevent", 38 => "Thirty-eight", 39 => "Thirty-nine",
            4 => "Forty", 41 => "Forty-one", 42 => "Forty-two", 43 => "Forty-three", 44 => "Forty-four", 45 => "Forty-five", 46 => "Forty-six", 47 => "Forty-seven", 48 => "Forty-eight", 49 => "Forty-nine",
            5 => "Fifty", 51 => "Fifty-one", 52 => "Fifty-two", 53 => "Fifty-three", 54 => "Fifty-four", 55 => "Fifty-five", 56 => "Fifty-six", 57 => "Fifty-seven", 58 => "Fifty-eight", 59 => "Fifty-nine",
            6 => "Sixty", 61 => "Sixty-one", 62 => "Sixty-two", 63 => "Sixty-three", 64 => "Sixty-four", 65 => "Sixty-five", 66 => "Sixty-six", 67 => "Sixty-seven", 68 => "Sixty-eight", 69 => "Sixty-nine",
            7 => "Seventy", 71 => "Seventy-one", 72 => "Seventy-two", 73 => "Seventy-three", 74 => "Seventy-four", 75 => "Seventy-five", 76 => "Seventy-six", 77 => "Seventy-seven", 78 => "Seventy-eight", 79 => "Seventy-nine",
            8 => "Eighty", 81 => "Eighty-one", 82 => "Eighty-two", 83 => "Eighty-three", 84 => "Eighty-four", 85 => "Eighty-five", 86 => "Eighty-six", 87 => "Eighty-seven", 88 => "Eighty-eight", 89 => "Eighty-nine",
            9 => "Ninety", 91 => "Ninety-one", 92 => "Ninety-two", 93 => "Ninety-three", 94 => "Ninety-four", 95 => "Ninety-five", 96 => "Ninety-six", 97 => "Ninety-seven", 98 => "Ninety-eight", 99 => "Ninety-nine"
        );
        $hundreds = array("Hundred", "Thousand", "Million", "Billion", "Trillion", "Quardrillion");
        $num = service::toDouble($num);
        $num = number_format($num, 2, ".", ",");
        $num_arr = explode(".", $num);
        $wholenum = $num_arr[0];
        $decnum = $num_arr[1];
        $whole_arr = array_reverse(explode(",", $wholenum));
        krsort($whole_arr, 1);
        $rettxt = "";
        foreach ($whole_arr as $key => $i) {
            while (substr($i, 0, 1) == "0")
                $i = substr($i, 1, 5);
            if ($i < 20) {
                $rettxt .= $ones[$i];
            } elseif ($i < 100) {
                if (substr($i, 0, 1) != "0")  $rettxt .= $tens[substr($i, 0, 1)];
                if (substr($i, 1, 1) != "0") $rettxt .= " " . $ones[substr($i, 1, 1)];
            } else {
                if (substr($i, 0, 1) != "0") $rettxt .= $ones[substr($i, 0, 1)] . " " . $hundreds[0];
                if (substr($i, 1, 1) != "0") $rettxt .= " " . $tens[substr($i, 1, 1)];
                if (substr($i, 2, 1) != "0") $rettxt .= " " . $ones[substr($i, 2, 1)];
            }
            if ($key > 0) {
                $rettxt .= " " . $hundreds[$key] . " ";
            }
        }
        $rettxt .= " Dollars ";
        if ($decnum > 0) {
            $rettxt .= " And ";
            if ($decnum < 20) {
                $rettxt .= $ones[$decnum];
            } elseif ($decnum < 100) {
                // $rettxt .= $tens[substr($decnum,0,2)];
                $rettxt .= " " . $ones[substr($decnum, 1, 1)];
            }
            $rettxt .= " Cents ";
        }
        return $rettxt;
    }
    public function checkPasswordDownload($password, $remember = 'No')
    {
        if ($remember == 'Yes') {
            if (!Session::get('remember_password')) Session::put('remember_password', $password);
            $password = Session::get('remember_password');
        } else {
            Session::forget('remember_password');
        }

        return verifyPasswordHelper($password); // updated by Yon

        // $credential = array(
        //     'email' => Auth::user()->email,
        //     'password' => $password,
        // );
        // if (!Auth::once($credential)) {
        //     Session::forget('remember_password');
        //     return false;
        // }
        // Auth::user()->user_setup = UserSetup::where('email', Auth::user()->email)->first();
        // Auth::user()->company = CompanyInformation::first();
        // Auth::user()->app_setup = ApplicationSetup::first();
        // return true;
    }
    public function num_paginate()
    {
        return Auth::user()->table_pagination ? Auth::user()->table_pagination : config('app.table_pagination');
    }
    public static function _num_paginate()
    {
        return Auth::user()->table_pagination ? Auth::user()->table_pagination : config('app.table_pagination');
    }
    public function num_paginate_report_preview()
    {
        return config('app.report_pagination');
    }
    public function insertOrUpdate($fields, $input)
    {
        foreach ($fields as $value) {
            $field_name = $value->field_name;

            if ($value->input_type == 'date') {
                $this->$field_name = Carbon::parse($input[$value->field_name])->toDateString();
            } elseif ($value->input_type == 'checkbox') {
                if (isset($input[$value->field_name])) {
                    $this->$field_name = 'Yes';
                } else {
                    $this->$field_name = 'No';
                }
            } else if ($value->input_type == 'select3') {
                if (isset($input[$value->field_name])) {
                    $this->$field_name = implode(',', $input[$value->field_name]);
                }
            } else {
                if (isset($input[$value->field_name])) {
                    if ($value->field_name != "id") {
                        $this->$field_name = trim($input[$value->field_name], ' ');
                    }
                }
            }
        }
    }

    public static function calcDateFormula($value)
    {
        try {
            $string = $value;
            $string = strtoupper($string);
            $strStart = "";
            $processSymbol = "";
            $strToProcess = "";
            $arr_symbol = ["-", "+"];
            $arr_special_str = ["CW", "CM", "SW", "CY"];
            //Validation && Set Value
            if (strlen($string) < 2) return "1900-01-01";
            if (substr($string, 0, 1) === "T") { //Today
                if (!in_array(substr($string, 1, 1), $arr_symbol)) return "1900-01-01";
                $strStart = substr($string, 0, 1);
                $processSymbol = substr($string, 1, 1);
                $strToProcess = substr($string, 1, strlen($string) - 1);
            } elseif (in_array(substr($string, 0, 1), $arr_symbol)) {
                $strStart = "T";
                $strToProcess = $string;
            } else {
                if (!in_array(substr($string, 0, 2), $arr_special_str) || !in_array(substr($string, 2, 1), $arr_symbol)) return "1900-01-01";
                $strStart = substr($string, 0, 2);
                $processSymbol = substr($string, 2, 1);
                $strToProcess = substr($string, 2, strlen($string) - 2);
            }

            $arr_value = service::getDateArrayValues($strToProcess);
            if (count($arr_value) <= 1 || $arr_value == 'false') return "1900-01-01";

            if (strtoupper($strStart) == 'T' || strtoupper($strStart) == 'TODAY') $date_value = Carbon::today()->toDateString();
            elseif (strtoupper($strStart) == 'TO' || strtoupper($strStart) == 'TOMORROW') $date_value = Carbon::tomorrow()->toDateString();
            elseif (strtoupper($strStart) == 'Y' || strtoupper($strStart) == 'YESTERDAY') $date_value = Carbon::yesterday()->toDateString();
            elseif (strtoupper($strStart) == 'CM') $date_value = Carbon::now()->endOfMonth()->toDateString();
            elseif (strtoupper($strStart) == 'CW') $date_value = Carbon::now()->endOfWeek();
            elseif (strtoupper($strStart) == 'SW') $date_value = Carbon::now()->startOfWeek();
            elseif (strtoupper($strStart) == 'CY') $date_value = Carbon::now()->endOfYear();


            foreach ($arr_value as $key => $value) {
                if (!is_numeric($value) && in_array($value, $arr_symbol)) {
                    $next_value = $arr_value[$key + 1];
                    $get_date_number = service::getDateOnlyNumber($next_value);

                    if (substr($next_value, -1) === "D") {
                        if ($value === "+") $date_value = Carbon::parse($date_value)->addDays($get_date_number);
                        if ($value === "-") $date_value = Carbon::parse($date_value)->subDays($get_date_number);
                    } else if (substr($next_value, -1) === "W") {
                        if ($value === "+") $date_value = Carbon::parse($date_value)->addWeeks($get_date_number);
                        if ($value === "-") $date_value = Carbon::parse($date_value)->subWeeks($get_date_number);
                    } else if (substr($next_value, -1) === "M") {
                        if ($value === "+") $date_value = Carbon::parse($date_value)->addMonths($get_date_number);
                        if ($value === "-") $date_value = Carbon::parse($date_value)->subMonths($get_date_number);
                    } else if (substr($next_value, -1) === "Y") {
                        if ($value === "+") $date_value = Carbon::parse($date_value)->addYear($get_date_number);
                        if ($value === "-") $date_value = Carbon::parse($date_value)->subYear($get_date_number);
                    } else {
                        return "1900-01-01";
                    }
                }
            }
            return $date_value->toDateString();
        } catch (\Expection $ex) {
            return "1900-01-01";
        }
    }

    public static function getDateArrayValues($string)
    {
        $newarr = [];
        $arr = str_split($string);
        $reserved = "";
        $is_special = "";
        try {

            foreach ($arr as $key => $obj) {
                if (is_numeric($obj) || in_array($obj, ["W", "D", "M", "Y"])) {
                    // ======= Check Special String > 1
                    if (!is_numeric($obj)) {
                        if ($is_special) return "false";
                        $is_special = "Yes";
                    }

                    $reserved .= $obj;
                    if ($key !== count($arr) - 1) continue;
                } else {
                    if (!in_array($obj, ["-", "+"])) return 'false';
                }
                if (strlen($reserved) > 0) {
                    array_push($newarr, $reserved);
                    $is_special = "";
                    if ($key == count($arr) - 1) continue;
                }
                array_push($newarr, $obj);
                $reserved = "";
            }
            return $newarr;
        } catch (\Exception $ex) {
            return "false";
        }
    }

    public static function getDateOnlyNumber($string)
    {
        return str_replace(['+', '-'], '', filter_var($string, FILTER_SANITIZE_NUMBER_INT));
    }

    public static function convertNumberToWord($num = false)
    {
        $num = str_replace(array(',', ' '), '', trim($num));
        if (!$num) {
            return false;
        }
        $num = (int) $num;
        $words = [];
        $list1 = [
            '', 'one', 'two', 'three', 'four', 'five', 'six', 'seven', 'eight', 'nine', 'ten', 'eleven',
            'twelve', 'thirteen', 'fourteen', 'fifteen', 'sixteen', 'seventeen', 'eighteen', 'nineteen'
        ];

        $list2 = ['', 'ten', 'twenty', 'thirty', 'forty', 'fifty', 'sixty', 'seventy', 'eighty', 'ninety', 'hundred'];
        $list3 = [
            '', 'thousand', 'million', 'billion', 'trillion', 'quadrillion', 'quintillion', 'sextillion', 'septillion',
            'octillion', 'nonillion', 'decillion', 'undecillion', 'duodecillion', 'tredecillion', 'quattuordecillion',
            'quindecillion', 'sexdecillion', 'septendecillion', 'octodecillion', 'novemdecillion', 'vigintillion'
        ];


        $num_length = strlen($num);
        $levels = (int) (($num_length + 2) / 3);
        $max_length = $levels * 3;
        $num = substr('00' . $num, -$max_length);
        $num_levels = str_split($num, 3);
        for ($i = 0; $i < count($num_levels); $i++) {
            $levels--;
            $hundreds = (int) ($num_levels[$i] / 100);
            $hundreds = ($hundreds ? ' ' . $list1[$hundreds] . ' hundred' . ' ' : '');
            $tens = (int) ($num_levels[$i] % 100);
            $singles = '';
            if ($tens < 20) {
                $tens = ($tens ? ' ' . $list1[$tens] . ' ' : '');
            } else {
                $tens = (int)($tens / 10);
                $tens = ' ' . $list2[$tens] . ' ';
                $singles = (int) ($num_levels[$i] % 10);
                $singles = ' ' . $list1[$singles] . ' ';
            }
            $words[] = $hundreds . $tens . $singles . (($levels && (int) ($num_levels[$i])) ? ' ' . $list3[$levels] . ' ' : '');
        } //end for loop
        $commas = count($words);
        if ($commas > 1) {
            $commas = $commas - 1;
        }
        return implode(' ', $words);
    }

    public function getModels($path)
    {
        $out = [];
        $results = scandir($path);
        foreach ($results as $result) {
            if (in_array($result,['.','..','VposSaleData.php'])) continue;
            $filename = $path . '/' .  $result;
            
            if (is_dir($filename)) {
                $out = array_merge($out, $this->getModels($filename));
            } else {
                if (strpos($filename, '.php')) {
                    $s_index = strpos($filename, '/Models');
                    $model_path = substr($filename, $s_index, strlen($filename));
                    $model_path = str_replace("/", "\\", $model_path);
                    $model_path = 'App' . substr($model_path, 0, -4);
                    $model = new $model_path;
                    $config = $model->getConnection()->getConfig();
                    if ($config['name'] == 'company') $out[$model->getTable()] = $model_path;
                }
            }
        }
        $out['bank_account'] =  'App\BankAccount';
        return $out;
    }

    /**
     * @param $dir directory path
     * 
     * @return array
     */
    public function getSubDirectories($dir)
    {

        $subDir = [];
        $directories = array_filter(glob($dir), 'is_dir');
        foreach ($directories as $directory) {
            array_push($subDir, $directory);
            if ($this->getSubDirectories($directory . '/*')) {
                array_push($subDir, $this->getSubDirectories($directory . '/*'));
            }
        }

        $finalArrays = array_flatten($subDir);
        return $finalArrays;
    }

    public function checkIsExistedModal($modals_path, $table)
    {
        isset($modals_path[$table]) ? $is_ture = 'Yes' : $is_ture = 'No';
        return $is_ture;
    }

    /**
     * @param $object_id   Page id
     * @param $field_name  Table field name
     * 
     * @return string
     */
    public function allowToUpdate($object_id, $field_name)
    {
        $user = Auth::user();
        $user_setup = $user->user_setup;
        $record = PermissionDenyModifyFields::where('permission_code', $user_setup->permission_code)->where('username', $user_setup->email)->where('object_id', $object_id)->where('field_name', $field_name)->first();
        if ($record) return "No"; //allow
        return "Yes"; //not allow
    }
    public static function custome_number_formattor_link($number, $format, $currency = null, $is_print = '')
    {
        if ($is_print == 'excel') {
            if ((float)$number == 0 || (float)$number == -0 || $number == '') return '0';
        } else {
            if ((float)$number == 0 || (float)$number == -0 || $number == '') return '-';
        }
        if (Auth::check()) {
            $app_setup = Auth::user()->app_setup;
        } else {
            $app_setup = ApplicationSetup::first();
        }
        if (!$app_setup) {
            $app_setup = ApplicationSetup::first();
        }
        $price_decimal = $app_setup->price_decimal;
        $amount_decimal = $app_setup->amount_decimal;
        $amount_lcy_decimal = $app_setup->amount_decimal;
        $cost_decimal = $app_setup->cost_decimal;
        $acy_amount = $app_setup->amount_decimal;
        $acy_cost = $app_setup->cost_decimal;
        if ($currency) {
            $price_decimal = $currency->unit_amount_decimal;
            $acy_amount = $currency->amount_decimal;
            $cost_decimal = $currency->unit_amount_decimal;
            $amount_decimal  = $currency->amount_decimal;
        }
        $separator_symbol = $app_setup->separator_symbol;
        if ($is_print == 'excel') {
            $separator_symbol = '';
        }

        if ($format != 'date' && $format != 'datetime') {
            $number = (float)str_replace($separator_symbol, '', $number);
        }
        switch (strtolower($format)) {
            case 'quantity':
                $result = number_format($number, $app_setup->quantity_decimal, $app_setup->decimalpoint, $separator_symbol);
                $result = strpos($result, '.') !== false ? rtrim(rtrim($result, '0'), '.') : $result;
                break;
            case 'quantity_size':
                $result = number_format($number, $app_setup->quantity_size_decimal, $app_setup->decimalpoint, $separator_symbol);
                $result = strpos($result, '.') !== false ? rtrim(rtrim($result, '0'), '.') : $result;
                break;
            case 'cost':
                $result = number_format($number, $cost_decimal, $app_setup->decimalpoint, $separator_symbol);
                break;
            case 'price':
                $result = number_format($number, $price_decimal, $app_setup->decimalpoint, $separator_symbol);
                break;
            case 'amount':
                $result = number_format($number, $amount_decimal, $app_setup->decimalpoint, $separator_symbol);
                break;
            case 'amount_lcy':
                $result = number_format($number, $amount_lcy_decimal, $app_setup->decimalpoint, $separator_symbol);
                break;
            case 'measurement':
                $result = number_format($number, $app_setup->measurement_decimal, $app_setup->decimalpoint, $separator_symbol);
                break;
            case 'percentage':
                $result = number_format($number, $app_setup->percentage_decimal, $app_setup->decimalpoint, $separator_symbol);
                $result = strpos($result, '.') !== false ? rtrim(rtrim($result, '0'), '.') : $result;
                break;
            case 'general':
                $result = number_format($number, 0, $app_setup->decimalpoint, $separator_symbol);
                break;
            case 'number':
                $result = number_format($number, 0, $app_setup->decimalpoint, $separator_symbol);
                $result = strpos($result, '.') !== false ? rtrim(rtrim($result, '0'), '.') : $result;
                break;
            case 'currency_factor':
                $result = number_format($number, 10, '.', '');
                break;
            case 'qty_to_assign':
                $result = number_format($number, $app_setup->item_qty_format, $app_setup->decimalpoint, $separator_symbol);
                break;
            case 'acy_cost':
                if ($currency) {
                    $result = number_format($number, $currency->unit_amount_decimal, $app_setup->decimalpoint, $separator_symbol);
                } else {
                    $result = number_format($number, $app_setup->cost_decimal, $app_setup->decimalpoint, $separator_symbol);
                }
                break;
            case 'acy_amount':
                if ($currency) {
                    $result = number_format($number, $currency->amount_decimal, $app_setup->decimalpoint, $separator_symbol);
                } else {
                    $result = number_format($number, $app_setup->amount_decimal, $app_setup->decimalpoint, $separator_symbol);
                }
                break;
            case 'date':
                if ($number) {
                    if ($number == '1900-01-01' || $number == '2500-01-01') {
                        return '';
                    } else {
                        return Carbon::parse($number)->format('d-M-Y');
                    }
                } else {
                    return '';
                }
                break;
            case 'datetime':
                if ($number) {
                    if ($number == '1900-01-01 00:00:00' || $number == '2500-01-01 00:00:00') {
                        return '';
                    } else {
                        return Carbon::parse($number)->format('d-M-Y h:i:s');
                    }
                } else {
                    return '';
                }
                break;
            case 'dateAndTime':
                if ($number) {
                    if ($number == '1900-01-01 00:00:00' || $number == '2500-01-01 00:00:00') {
                        return '';
                    } else {
                        return Carbon::parse($number)->format('d-M-Y h:i:s');
                    }
                } else {
                    return '';
                }
                break;
            case 'gps':
                $result = number_format($number, 12, '.', '');
                break;
            case 'day':
                $result = number_format($number, 0, '.', '');
                break;
            case 'hour':
                $result = number_format($number, 1, '.', '');
                break;
            case 'decimal':
                $result = number_format($number, 18, '.', '');
                break;
            case 'vat_amount':
                $result = number_format($number, 3, '.', '');
                break;
            case '0':
                $result = number_format($number, 0, '.', $separator_symbol);
                break;
            case '1':
                $result = number_format($number, 1, '.', $separator_symbol);
                break;
            case '2':
                $result = number_format($number, 2, '.', $separator_symbol);
                break;
            case '3':
                $result = number_format($number, 3, '.', $separator_symbol);
                break;
            case '4':
                $result = number_format($number, 4, '.', $separator_symbol);
                break;
            case '5':
                $result = number_format($number, 5, '.', $separator_symbol);
                break;
            case '6':
                $result = number_format($number, 6, '.', $separator_symbol);
                break;
            case '7':
                $result = number_format($number, 7, '.', $separator_symbol);
                break;
            case '8':
                $result = number_format($number, 8, '.', $separator_symbol);
                break;
            case '8':
                $result = number_format($number, 9, '.', $separator_symbol);
                break;
            case '9':
                $result = number_format($number, 10, '.', $separator_symbol);
                break;
            case '10':
                $result = number_format($number, 11, '.', $separator_symbol);
                break;
            default:
                $result = number_format($number, $app_setup->general_decimal, $app_setup->decimalpoint, $separator_symbol);
                break;
        }
        if (strpos($result, $app_setup->decimalpoint) !== false && $app_setup->decimal_zero !== 'Yes') {
            return rtrim($result, '0');
        } else {
            return $result;
        }
    }

    public static function custome_number_formattor($value, $option, $precision = 2)
    {
        $result = '';
        switch ($option) {
            case 'quantity':
                $result = (new self)->number_formattor($value, $option);
                $result = strpos($result, '.') !== false ? rtrim(rtrim($result, '0'), '.') : $result;
                break;
            case 'price':
                $result = (new self)->number_formattor($value, $option);
                $result = strpos($result, '.') !== false ? rtrim(rtrim($result, '0'), '.') : $result;
                break;
            case 'percentage':
                $result = (new self)->number_formattor($value, $option);
                $result = strpos($result, '.') !== false ? rtrim(rtrim($result, '0'), '.') : $result;
                break;
            case 'hours':
                $result = strpos($value, '.') !== false ? rtrim(rtrim($value, '0'), '.') : $value;
                break;
            case 'minute':
                $totaltime = strpos($value, '.') !== false ? rtrim(rtrim($value, '0'), '.') : $value;
                $totaltime_arr = explode(":", $totaltime);
                $h = intval($totaltime_arr[0] ?? 0);
                $m = intval($totaltime_arr[1] ?? 0);
                $s = intval($totaltime_arr[2] ?? 0);
                $result = ($h * 60) + $m;
                break;
            case 'seconds':
                $totaltime = strpos($value, '.') !== false ? rtrim(rtrim($value, '0'), '.') : $value;
                $totaltime_arr = explode(":", $totaltime);
                $h = intval($totaltime_arr[0] ?? 0);
                $m = intval($totaltime_arr[1] ?? 0);
                $s = intval($totaltime_arr[2] ?? 0);
                $result = ($h * 3600) + ($m * 60) + $s;
                break;
            case 'noround':
                $result1 = service::toDouble($value);
                $result = preg_replace('/\.(\d{2}).*/', '.$1', $result1);

            default:
                break;
        }

        return $result;
    }

    /**
     * Copy of Excel's PMT function.
     *
     * @param double $rate                  The interest rate for the loan.
     * @param int    $number_of_month    The total number of payments for the loan in months.(peroid)
     * @param double $present_value         The present value is loan value
     *                                
     * @param double $future_value          The future value, or a cash balance you want to attain after the last payment is made.
     *                                      If fv is omitted, it is assumed to be 0 (zero), that is, the future value of a loan is 0.
     * @param int    $Type                  Optional, defaults to 0. The number 0 (zero) or 1 and indicates when payments are due.
     *                                      0 = At the end of period
     *                                      1 = At the beginning of the period
     *
     * @return float
     */

    public static function pmt_calc($rate, $number_of_month, $present_value, $future_value = 0.0, $type = 0)
    {
        if ($rate != 0.0) {
            // Interest rate exists
            $q = pow(1 + $rate, $number_of_month);
            return ($rate * ($future_value + ($q * $present_value))) / ((-1 + $q) * (1 + $rate * ($type)));

        } else if ($number_of_month != 0.0) {
            // No interest rate, but number of payments exists
            return ($future_value + $present_value) / $number_of_month;
        }

        return 0.0;
    }

    // public static function daysIn360($starting_date, $ending_date, $day = 0)
    // {
    //     // int d1, m1, y1, d2, m2, y2;
    //     $d1 = Carbon::parse($starting_date)->day; //dtStartDate.Day;
    //     $m1 = Carbon::parse($starting_date)->month; //dtStartDate.Month;
    //     $y1 = Carbon::parse($starting_date)->year; //dtStartDate.Year;
    //     $d2 = Carbon::parse($ending_date)->day; //dtEndDate.Day;
    //     $m2 = Carbon::parse($ending_date)->month; //dtEndDate.Month;
    //     $y2 = Carbon::parse($ending_date)->year; //dtEndDate.Year;

    //     if ($d1 == 31) $d1 = 30;
    //     if ($d2 == 31 && $d1 == 30) $d2 = 30;
    //     return (360 * ($y2 - $y1) + 30 * ($m2 - $m1) + ($d2 - $d1)) / 360e0;
    // }

    public static function isLeapYear($year)
    {
        return ((($year % 4) == 0) && (($year % 100) != 0) || (($year % 400) == 0));
    }

    public static function daysIn360($starting_date, $ending_date,$methodUS = true)
    {

        $startDay = Carbon::parse($starting_date)->day;
        $startMonth = Carbon::parse($starting_date)->month;
        $startYear = Carbon::parse($starting_date)->year;
        $endDay = Carbon::parse($ending_date)->day;
        $endMonth = Carbon::parse($ending_date)->month;
        $endYear = Carbon::parse($ending_date)->year;

        if ($startDay == 31) {
            --$startDay;
        } elseif ($methodUS && ($startMonth == 2 && ($startDay == 29 || ($startDay == 28 && !self::isLeapYear($startYear))))) {
            $startDay = 30;
        }
        if ($endDay == 31) {
            if ($methodUS && $startDay != 30) {
                $endDay = 1;
                if ($endMonth == 12) {
                    ++$endYear;
                    $endMonth = 1;
                } else {
                    ++$endMonth;
                }
            } else {
                $endDay = 30;
            }
        }

        return $endDay + $endMonth * 30 + $endYear * 360 - $startDay - $startMonth * 30 - $startYear * 360;
    }


    /**
     * @param string $start_time (00:00:00)
     * @param array Or string $end_time  (00:00:00)
     * @param string $formart_type
     * @return string
     */
    public static function distant_times($start_time, $end_time, $formart_type = 'hours')
    {
        if (is_array($end_time)) $time = $end_time;
        else {
            if (!$end_time) return '00:00:00';
            $time = [$end_time];
        }

        $sum = strtotime($start_time);
        $totaltime = 0;
        foreach ($time as $element) {
            // Converting the time into seconds 
            $timeinsec = strtotime($element) - $sum;
            // Sum the time with previous value 
            $totaltime = $totaltime + $timeinsec;
        }

        $h = intval($totaltime / 3600);
        $totaltime = $totaltime - ($h * 3600);
        $m = intval($totaltime / 60);
        $s = $totaltime - ($m * 60);

        if ($formart_type == 'hours') {
            $_h = (new self)->count_digits($h);
            $_m = (new self)->count_digits($m);
            $_s = (new self)->count_digits($s);

            //formart 
            $add_zero_h = ($_h > 1) ? '' : '0';
            $add_zero_m = ($_m > 1) ? '' : '0';
            $add_zero_s = ($_s > 1) ? '' : '0';

            return ("$add_zero_h$h:$add_zero_m$m:$add_zero_s$s");
        } elseif ($formart_type == 'minute') {
            return ($h * 60) + $m;
        } else {
            return ($h * 3600) + ($m * 60) + $s;
        }
    }

    public function count_digits($num)
    {
        return (int) (log($num, 10) + 1);
    }
    public function checkCustomerCredit($customer, $payment_amount, $document_no, $date = "")
    {
        if ($date == "") $date = Carbon::now()->toDateString();
        $aging_date = $date;
        $total_agin_amount = $customer->credit_limited_amount;
        if ($customer->credit_limited_type == "Balance" && $total_agin_amount != '') {
            $customer_ledger_enties = DB::connection('company')->table('v_customer_ledger_entry')
                ->selectRaw("datediff('$aging_date', posting_date) as day_due, document_no,remaining_amount_lcy")
                ->where('customer_no', $customer->no)->where('remaining_amount', '<>', 0)->get();
            $lines = SaleLine::where('document_no', $document_no)->get();
            $total_amount_current_invoice = count($lines) > 0 ? $lines->sum(function ($r) {
                if ($r->amount_lcy) {
                    return service::toDouble($r->amount_lcy);
                }
            }) : 0;
            $total_amount = count($customer_ledger_enties) > 0 ? $customer_ledger_enties->sum(function ($r) {
                if ($r->remaining_amount_lcy) {
                    return service::toDouble($r->remaining_amount_lcy);
                }
            }) : 0;
            
            $total_amount = $total_amount + $total_amount_current_invoice - service::toDouble($payment_amount);
            if (service::toDouble($total_agin_amount) < service::toDouble($total_amount)) {
                return 1;
            }
        } elseif ($customer->credit_limited_type == 'No of Invoices' && $total_agin_amount != '') {
            $customer_ledger_enties = DB::connection('company')->table('v_customer_ledger_entry')
                ->where('customer_no', $customer->no)
                ->where('remaining_amount', '<>', 0)
                ->where('document_type', 'Invoice')
                ->count();
            if ($customer_ledger_enties >= $total_agin_amount) return 2;
        } elseif ($customer->credit_limited_type == 'No Credit') {
            if ($payment_amount == '' || service::toDouble($payment_amount) == 0) return 3;
        }
        //CHECK TOTAL AMOUNT BIGGER THEN PAYMENT AMOUNT 
        $total_amount = SaleLine::where('document_no', $document_no)->where('type', '<>', 'Text')->sum('amount_including_vat');
        if (service::toDouble($payment_amount) > service::toDouble($total_amount))  return 4;
    }
    public function getSublistGroupFieldByObjectID($objecgroupId, $objId)
    {
        $data = PageGroup::where('id', $objecgroupId)->where('object_id', $objId)->orderBy('group_range')->get();
        return $data;
    }
    public function checkItemSalesLineByUser($line, $unit_price)
    {
        $record = LsnSetupPriceByUser::where('item_no', $line->no)->where('unit_of_measure_code',$line->unit_of_measure)->whereRaw("( CONCAT(',',user_id,',') LIKE '%,".Auth::user()->email.",%')")->first();
        if ($record) {
            if (service::toDouble($record->min_price) != 0 && service::toDouble($record->max_price)  != 0) {
                if (service::toDouble($unit_price) >= service::toDouble($record->min_price) && service::toDouble($unit_price) <= service::toDouble($record->max_price)) {
                    return ['status' => true];
                }
                return ['status' => false, 'record' => $record];
            } else {
                if (service::toDouble($record->min_price) != 0 && (service::toDouble($unit_price) >= service::toDouble($record->min_price))) {
                    return ['status' => true];
                } elseif (service::toDouble($record->max_price) != 0 && (service::toDouble($unit_price) <= service::toDouble($record->max_price))) {
                    return ['status' => true];
                }
                return ['status' => false, 'record' => $record];
            }
        }
        return ['status' => true];
    }
    public function getSearchCriteriasSubList($lstFields, $filters)
    {
        $criterias = ' 1=1 ';
        $i = 0;
        // foreach ($lstFields as $lstField) {
        //     foreach ($lstField->page_group_field_search_sublist() as $field) {
        //         if (isset($filters[$field->field_name])) {
        //             $value = mb_strtoupper(trim($filters[$field->field_name], ' '), 'UTF-8');
        //             if ($value || $value == '0') {
        //                 if ($i == 0) {
        //                     $criterias .= ' AND ' . $this->getSpecialCondiction($field, $value);
        //                 } else {
        //                     $criterias .= ' AND ' . $this->getSpecialCondiction($field, $value);
        //                 }
        //                 $i += 1;
        //             }
        //         }
        //     }
        // }
        $value = trim(Input::get('value'), ' ');
        if ($value == 'null') {
        } else {
            $criterias .= ' AND ( ';
            $i = 0;
            foreach ($lstFields as $lstField) {
                foreach ($lstField->page_group_field_search_sublist() as $field) {
                    $value = mb_strtoupper(trim($value, ' '), 'UTF-8');
                    if ($field->field_data_type == 'decimal') {
                        $criterias = $criterias . ' (' . $field->field_name . ') =' . " '" . $this->getSpecialConditionValue($field, $value) . "' or ";
                    } elseif ($field->field_data_type == 'date') {
                        $criterias = $criterias . ' (' . $field->field_name . ') =' . " '" . $this->getSpecialConditionValue($field, $value) . "' or ";
                    } else {
                        $criterias = $criterias . ' UPPER(' . $field->field_name . ') like' . " '" . str_replace('*', '%', $value) . "' or ";
                    }
                }
            }
            $criterias = $criterias . ' 1 = 2 )';
        }
        return $criterias;
    }
    public function getInitSendNotification($header,$app_id,$message){
        $api_sessions_users = \DB::connection('mysql')->table('api_sessions')
                    ->where('user_id', $header->assign_to_userid)
                    ->whereIn('app_id', $app_id)
                    ->where('firebase_client_key', '<>','')
                    ->get();
        $noti_sessions = $api_sessions_users->unique("user_id");
        if(count($noti_sessions) > 0){
            foreach($noti_sessions as $api_session){
                $sessionToken = openssl_random_pseudo_bytes(20);
                $sessionToken = bin2hex($sessionToken);
                $notification = new MyNotification();
                $notification->id = $sessionToken;
                $notification->type = 'App';
                $notification->notifiable_id = $api_session->user_id;
                $notification->notifiable_type = 'App\User';
                $notification->description = $message;
                $notification->entry_date = Carbon::now()->toDateString();
                $notification->entry_datetime = Carbon::now();
                $notification->document_type = 'Transfer Order';
                $notification->document_no = $header->no;
                $notification->app_id = $api_session->app_id; 
                $notification->data = json_encode([
                    'sender_id' => Auth::user()->id,
                    'header' => $header,
                ]);
                $notification->save();
            }
            $array_ecommerce_users = $api_sessions_users->pluck("firebase_client_key");
            $fcmNotificationData = [
                'registration_ids' => $array_ecommerce_users,
                'notification' => [
                    'title' =>  $header->status,  
                    'body' => $message,
                    'sound' => 'default'
                ],
                'data' => [
                    'title' => $header->status,  
                    'document_no' => $header->no,
                    'document_type' => $header->document_type,
                    'avatar' => null,
                    'type' => 'shipped',
                    'status' => 'Shipped'
                ]
            ];
            $this->sendNotification($fcmNotificationData);
        }
    }
    
    public static function roundingRiel($amount){
        $amount = abs($amount); 
        $reminging = fmod($amount, 100); 
        if($reminging >= 50){
            $amount = ($amount - $reminging ) + 100; 
        }else {
            $amount =  $amount - abs($reminging); 
        }
        return $amount; 
    }
    public static function findGainLossAmount($amount,$amount_header,$line_factor,$line_remaining_amount,$line_remaining_amount_lcy){
        if(abs(service::toDouble($amount)) != abs(service::toDouble($line_remaining_amount))) return 0;
        $factor = 1; 
        $gain_loss_amount_line = 0; 
        $new_gain_loss_amount = 0;
        $amount_to_apply_header_lcy = service::number_formattor($amount_header, 'amount'); 
        $amount_to_apply_line_lcy = service::number_formattor(service::toDouble($amount) / service::toDouble($line_factor), 'amount');
     
        if(abs(service::toDouble($amount_to_apply_header_lcy)) != abs(service::toDouble($line_remaining_amount_lcy))){
            $gain_loss_amount_line = abs(service::toDouble($amount_to_apply_header_lcy)) - abs(service::toDouble($amount_to_apply_line_lcy));
            $new_gain_loss_amount = $gain_loss_amount_line; 

            if(abs(service::toDouble(service::number_formattor($amount_to_apply_header_lcy, 'amount'))) != abs(service::toDouble(service::number_formattor($line_remaining_amount_lcy, 'amount')))){
                $remaining_amount_lcy = abs(service::toDouble(service::number_formattor($line_remaining_amount_lcy, 'amount'))) 
                                    - (abs(service::toDouble(service::number_formattor($amount_header, 'amount'))) 
                                    + (service::toDouble(service::number_formattor($gain_loss_amount_line, 'amount')) * (-1) ) ); 
                                    
                if(service::toDouble($remaining_amount_lcy) != 0){
                    $new_gain_loss_amount = abs(service::toDouble($gain_loss_amount_line)) + abs(service::toDouble($remaining_amount_lcy)); 
                }
                if(service::toDouble($gain_loss_amount_line) < 0) $factor = $factor * (-1); 
                $new_gain_loss_amount = abs($new_gain_loss_amount) * $factor; 
            }
        } 
        return $new_gain_loss_amount; 
    }
    public static function validationPhoneNumber($phone_no){
        $plus_symobol = substr($phone_no, 0, 1); 
        $country_code = '+855'; 
        if($plus_symobol == '+'){
            $country_code = substr($phone_no,0,4); 
            $phone_no = '0'.substr($phone_no,4); 
        }else if ($plus_symobol != '0'){
            $phone_no = '0'.$phone_no; 
        } 
        $carrier_code =  substr($phone_no, 0, 3);
        $length_phone = strlen(substr($phone_no, 3));
        $new_phone_number = $country_code.substr($phone_no,1); 

        $valid_phone = CountryCarrier::where('carrier_code',$carrier_code)->where('country_code',$country_code)->where('no_of_digits',$length_phone)->first(); 
        if(!$valid_phone) return ''; 
        return $new_phone_number; 
    }
    public static function getPhoneNumber($phone_no){
        $phone_no = service::validationPhoneNumber($phone_no); 
        if(!$phone_no) return ''; 
        return substr($phone_no,4); 
    }

    public static function checkitemNew($item_no){
        $is_new_item = 'No'; 
        // CHECK ITEM HAS TRANSACTION ORDER OR NOT 
        $purcahse_data = PurchaseReceiptLine::selectRaw('posting_date')->where('no',$item_no)->orderBy('posting_date', 'ASC')->first();
       
        // IF NO TRANSACTION ORDER (ALOS REMARK IT NEW ITEM)
        if(!$purcahse_data) $is_new_item = 'Yes'; 
        if($purcahse_data && Carbon::parse($purcahse_data->posting_date)->diffInDays(Carbon::now()) < 1)  $is_new_item = 'Yes'; 
        return $is_new_item; 
    }
   
}
