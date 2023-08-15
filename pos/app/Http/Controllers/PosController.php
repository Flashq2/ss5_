<?php

namespace App\Http\Controllers;

use Exception;
use App\Models\ItemModel;
use Illuminate\Http\Request;
use App\Models\CustomerModel;
use App\Models\salesLineModel;
use App\Models\lineNoserieModel;
use App\Models\salesHeaderModel;
use App\Models\ItemCategoryModel;
use Illuminate\Support\Facades\Auth;

class PosController extends Controller
{
    public function index(){
        $items=ItemModel::all();
        $category=ItemCategoryModel::where('inactived','No')->get();
        $customer = CustomerModel::get();

        // Get exchange Rate
         $exchangeRate = 'https://v6.exchangerate-api.com/v6/e6ed23d21dd77ec67a2f5f02/latest/USD';
         $response_json = file_get_contents($exchangeRate);
        if(false !== $response_json) {
            try {
                $response = json_decode($response_json);
                if('success' === $response->result) {
                    $base_price = 12; // Your price in USD
                    $currenccy_khr = round($response->conversion_rates->KHR);
                }
            }
            catch(Exception $e) {
                $currenccy_khr=4000;
            }
        
        }
        
        return view('pos.pos',compact('items','category','currenccy_khr','customer'));
    }
    public function addItem(Request $request){
        $line = lineNoserieModel::where('id','INE')->first();
        $customer = CustomerModel::where('no',$request->customer)->first();
        if(!$customer) return response()->json(['status'=>'warning','message'=>"Customer Not found , Please select customer !"]);
        // Create New Sales Header if document is Open
        $header = salesHeaderModel::where('no',$line->id.'-'.$line->prefix)->first();
        if(!$header){
            $newHeader = new salesHeaderModel();
            $newHeader->no=$line->id.'-'.$line->prefix;
            $newHeader->document_type = "Invoice";
            $newHeader->created_by = Auth::user()->email;
            $newHeader->customer_name = $customer->name;
            $newHeader->customer_no = $customer->no;
            $newHeader->customer_name_2 = $customer->name_2;
            $newHeader->address = $customer->address;
            $newHeader->address_2 = $customer->address_2;
            $newHeader->salesperson_code = Auth::user()->salesperson_code;
            $newHeader->order_date = now("Asia/Phnom_Penh")->format('Y-m-d');
            $newHeader->order_datetime = now("Asia/Phnom_Penh")->format('Y-m-d H:i:s');
            $newHeader->save();
        }
        //Create Sales Line
        $salesLine = new salesLineModel();
        $salesLine->document_no = $line->id.'-'.$line->prefix;
        
        return response()->json([
            'status'=>"Success "
        ]);
    }
    public function getExchangeRate(){
        $exchangeRate = 'https://fcm.googleapis.com/fcm/send';
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $exchangeRate);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        $result = curl_exec($ch);
        curl_close($ch);
        return $result;
    }
    
}
