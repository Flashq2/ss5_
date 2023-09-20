<?php
namespace App\Helpers;

class GlobalFunction
{
    public static function numberFormate($value,$formate){
         switch ($formate) {
            case 'amount':
                 $result = number_format($value,2,'.');
                break;
            case 'quantity':
                    $result = number_format($value,1,'.');
                   break;
            case 'double':
                    $result = str_replace(",","",$value);;
                   break;
            
            default:
                # code...
                break;
         }
         return $result;
    }

}


?>