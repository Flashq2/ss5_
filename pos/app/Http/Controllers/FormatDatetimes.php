<?php

namespace App\Http\Controllers;

use App\Models\cModel;
use Carbon\Carbon;
use Illuminate\Http\Request;

class FormatDatetimes extends Controller
{

    public function index(){
        $cm=cModel::selectRaw("*")
        ->get()
        ->groupBy(['id','name']);
         
        return view('items_group.item_group',compact('cm'));
        
    }
    public function getSpecialConditionValue($field, $value)
    {
        
          // $criterias = '';
        if ($field == 'decimal') {
            return is_numeric($value) ? $value : -999999999999999999999;
        } 
        elseif ($field == 'date' || $field === 'timestamp' || $field == 'datetime') {
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
                        if(strpos($value, ' ') !== false){
                            
                                $value_time=explode(' ', $value);
                            }
                        $dateparts = explode('-', $value_time[0]??$value);
                        if (strlen($dateparts[0]) == 4) {
                            $year = isset($dateparts[0]) ? $dateparts[0] : null;
                            if ($year) {
                                if (strlen($year) == 2) $year = '20' . $year;
                            }
                            $month = isset($dateparts[1]) ? $dateparts[1] : null;
                            if ($month) {
                                if (strlen($month) > 2) $month = $this->convertMonthname2Number($month);
                            }
                            $time =$this->getSpecialCondictionTime($field, $value);
                            return date_format(Carbon::createFromDate($year, $month, isset($dateparts[2]) ? $dateparts[2] : null), 'Y-m-d')." ".$time;
                        } else {
                            $year = isset($dateparts[2]) ? $dateparts[2] : null;
                            if ($year) {
                                if (strlen($year) == 2) $year = '20' . $year;
                            }
                            $month = isset($dateparts[1]) ? $dateparts[1] : null;
                            if ($month) {
                                if (strlen($month) > 2) $month = $this->convertMonthname2Number($month);
                            }
                            $time =$this->getSpecialCondictionTime($field, $value);
                            return date_format(Carbon::createFromDate($year, $month, isset($dateparts[0]) ? $dateparts[0] : null), 'Y-m-d')." ".$time;
                        }
                    } else {
                        $time =$this->getSpecialCondictionTime($field, $value);
                        if(strpos($value, ' ') !== false){
                            $value_time=explode(' ',$value);
                        }
                        if (strlen($value_time[0]??$value) == 2) {
                            return date_format(Carbon::createFromDate(null, null, $value_time[0]??$value), 'Y-m-d')." ".$time;
                        } elseif (strlen($value_time[0]??$value) == 4) {
                            return date_format(Carbon::createFromDate(null, substr($value_time[0]??$value, 2, 4), substr($value_time[0]??$value, 0, 2)), 'Y-m-d')." ".$time;
                        } elseif (strlen($value_time[0]??$value) == 6) {
                            return date_format(Carbon::createFromDate('20' . substr($value_time[0]??$value, 4, 2), substr($value_time[0]??$value, 2, 2), substr($value_time[0]??$value, 0, 2)), 'Y-m-d')." ".$time;
                        } else {
                            $result = date_format(Carbon::createFromDate(substr($value_time[0]??$value, 4, 4), substr($value_time[0]??$value, 2, 2), substr($value_time[0]??$value, 0, 2)), 'Y-m-d');
                        }
                    }
                    if($field == 'datetime'){
                        $time =$this->getSpecialCondictionTime($field, $value);
                      return $result.' '.$time;
                    }
                     
                }
            } catch (\Exception $ex) {
                return date_format(Carbon::createFromDate(2500, 01, 01), 'Y-m-d');
            }
        } elseif ($field == 'time') {
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
       
        try {
            if(strpos($value, ' ') !== false){
                $dateandtime=explode(' ',$value);
                   if(isset($dateandtime[1])){
                       if(strpos($dateandtime[1],':') !==false){
                           $timevalue=explode(':',$dateandtime[1]);
                           if(count($timevalue)>=4){
                               $time = Carbon::createFromTime(00,00, 00);
                           }
                           if(count($timevalue)==1){
                               if($timevalue[0]>24){
                                   $timevalue[0]="00";
                               }
                               $time = Carbon::createFromTime($timevalue[0],00, 00);

                           }
                           if(count($timevalue)==2){
                               
                               if($timevalue[0]>24 && $timevalue[1]>60){
                                   $time = Carbon::createFromTime(00,00, 00);
                               }
                               else if($timevalue[0]>24){
                                   $time = Carbon::createFromTime(00,$timevalue[1], 00);
                               }
                               else if($timevalue[1]>60){
                                   $time = Carbon::createFromTime($timevalue[0],00, 00);
                               }
                               else{
                                $time = Carbon::createFromTime($timevalue[0],$timevalue[1], 00);
                               }

                           }
                           if(count($timevalue)==3){
                               
                               if($timevalue[0]>24 && $timevalue[1]>60 && $timevalue[1]>60){
                                   $time = Carbon::createFromTime(00,00, 00);
                               }
                               if($timevalue[0]>24 && $timevalue[1]>60){
                                   $time = Carbon::createFromTime(00,00,$timevalue[2]);
                               }
                               if($timevalue[0]>24 && $timevalue[2]>60){
                                   $time = Carbon::createFromTime(00,$timevalue[1], 00);
                               }
                               if($timevalue[1]>60 && $timevalue[2]>60){
                                   $time = Carbon::createFromTime($timevalue[1],00, 00);
                               }
                               if($timevalue[0]>24){
                                   $time = Carbon::createFromTime(00,$timevalue[1], $timevalue[2]);
                               }
                               if($timevalue[1]>60){
                                   $time = Carbon::createFromTime($timevalue[0],00, $timevalue[2]);
                               }
                               if($timevalue[2]>60){
                                   $time = Carbon::createFromTime($timevalue[0],$timevalue[0],00);
                               }
                               if($timevalue[0]<24 && $timevalue[1]<60 && $timevalue[1]<60){
                                   $time = Carbon::createFromTime($timevalue[0],$timevalue[1], $timevalue[2]);
                               }

                                

                           }
                       }
                       else{
                           if(strlen($dateandtime[1])==1 || strlen($dateandtime[1])==2){
                               if($dateandtime[1]>24){
                                   $dateandtime[1]="00";
                               }
                               // $time = $dateandtime[1].":"."00:00";
                               $time = Carbon::createFromTime($dateandtime[1],00,00);
                           }
                           if(strlen($dateandtime[1])==3){
                                $h=substr($dateandtime[1], 0, 2);
                                $m=substr($dateandtime[1], 2, 1);
                                if($h>24){
                                   $h=00;
                                }
                               //  $time = $h.":".$m.":"."00";
                               $time = Carbon::createFromTime($h,$m,00);
                           }
                           if(strlen($dateandtime[1])==4){
                               $h=substr($dateandtime[1], 0, 2);
                               $m=substr($dateandtime[1], 2, 2);
                               if($h>24 && $m>60){
                                  $h=00;
                                  $m=00;
                               }
                               if($h>24){
                                   $h=00;
                               }
                               if($m>60){
                                   $m=00;
                               }
                              $time = Carbon::createFromTime($h,$m, 00);
                          }
                          if(strlen($dateandtime[1])==5 || strlen($dateandtime[1])==6){
                           $h=substr($dateandtime[1], 0, 2);
                           $m=substr($dateandtime[1], 2, 2);
                           $s=substr($dateandtime[1], 4, 1);
                           if(strlen($dateandtime[1])==6){
                               $s=substr($dateandtime[1], 4, 2);
                           }
                           
                           if($h>24 && $m>60 && $s>60 ){
                              $h="00";
                              $m="00";
                              $s="00";
                           }
                           if($h>24 && $m>60){
                               $h="00";
                               $m="00";
                           }
                           if($m>60 && $s>60 ){
                               $m="00";
                               $s="00";
                           }
                           if($h>24 && $s>60){
                               $h="00";
                               $s="00";
                           }
                           if($h>24){
                               $h="00";
                           }
                           if($h>24){
                               $h="00";
                           }
                           if($s>60){
                               $s="00";
                           }
                           
                          $time = Carbon::createFromTime($h,$m,$s);
                      }
                      if(strlen($dateandtime[1])>6){
                       $time = Carbon::createFromTime(00,00,00);
                      }

                       }
                      
                   }
                     // $time =$this->getSpecialCondictionTime($field, $value);
           }
           else{
               $time = Carbon::createFromTime(00,00,00);
           }
        if($field=="datetime"){
        return Carbon::parse($time)->format('H:i:s');
        }
        else{
            return "";
        
        
        }
    }catch (\Exception $ex) {
            return "";
        }
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


  
}
