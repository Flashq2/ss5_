<?php

namespace App\Http\Controllers;

use toastr;
use Exception;
use App\Models\ItemModel;
use Illuminate\Http\Request;
use App\Models\CustomerModel;
use App\Models\salesLineModel;
use App\Helpers\GlobalFunction;
use App\Models\lineNoserieModel;
use App\Models\salesHeaderModel;
use App\Models\ItemCategoryModel;
use Illuminate\Support\Facades\DB;
use function GuzzleHttp\Promise\all;
use Illuminate\Support\Facades\Auth;

use App\Models\ItemUnitofMeasureModel;
use Symfony\Component\HttpKernel\Event\ViewEvent;

class PosController extends Controller
{
    public $currenccy_khr = '';
    protected $document_no = '';
    public function index(){
        // $items=ItemModel::all();
        $category=ItemCategoryModel::where('inactived','No')->get();
        $customer = CustomerModel::get();
        $header = salesHeaderModel::whereIn('status',['New'])->first();
        $noline_serie = lineNoserieModel::where('id','INE')->first();
        $exchangeRate = 'https://v6.exchangerate-api.com/v6/b0a408b32a536b764a49b3ae/latest/USD';
        $response_json = file_get_contents($exchangeRate);
       if(false !== $response_json) {
           try {
               $response = json_decode($response_json);
               if('success' === $response->result) {
                   $base_price = 12;
                   $this->currenccy_khr = round($response->conversion_rates->KHR);
               }
           }
           catch(Exception $e) {
               $this->currenccy_khr=4000;
           }
       
       }
        $line = null;  
        $amount = 0;
        $discount_amont = 0;
        $subtotal = 0;
        $total_reil = 0;
        $currenccy_khr = $this->currenccy_khr;
        $document_no = $noline_serie->id.'-'.($noline_serie->prefix)+1;
        $this->document_no =   $document_no;
        if($header){
            $document_no = $header->no;
            $line = salesLineModel::where('document_no', $document_no)->get();
            $amount =  $line->sum(function ($amount) {
                return $amount->amount;
            });
            $discount_amont =  $line->sum(function ($amount) {
                return $amount->discount_amount;
            });
            $amount =  $line->sum(function ($amount) {
                return $amount->amount;
            });
            
            $subtotal = $amount +  $discount_amont;
            $total_reil = $amount * $currenccy_khr;
        }
        // config()->set('database.connections.mysql.strict', false);
        $items=  DB::table('item_unit_of_measures')
            ->select('items.no', 'item_unit_of_measures.item_no', 'item_unit_of_measures.id', 'item_unit_of_measures.price', 'items.picture', 'items.unit_price', 'items.description', 'items.description_2', 'item_unit_of_measures.unit_of_measure_code', 'item_unit_of_measures.qty_per_unit', 'items.item_category_code', 'items.item_group_code')
            ->join('items', 'items.no', '=', 'item_unit_of_measures.item_no')
            ->offset(0)
            ->limit(60)
            ->orderBy('item_unit_of_measures.id')
            ->groupBy('item_unit_of_measures.item_no')
            ->paginate(10);
        return view('pos.pos',compact('items','category','currenccy_khr','customer','document_no','line','total_reil','subtotal','amount','discount_amont'));
    }
    public function addItem(Request $request){
        $data = $request->all();
        $currenccy_khr= $data['value'];
        $noline_serie = lineNoserieModel::where('id','INE')->first();
        $customer = CustomerModel::where('no',$request->customer)->first();
        $item = ItemUnitofMeasureModel::where('item_no',$data['product_code'])->first();
        $item_detail = ItemModel::where('no',$data['product_code'])->first();
        if(!$item)     return response()->json(['status'=>'warning','message'=>"Product  Not found , Something went wrong !"]);
        if(!$customer) return response()->json(['status'=>'warning','message'=>"Customer Not found , Please select customer !"]);
        
        // Create New Sales Header if document is Open
        $header = salesHeaderModel::where('no',$noline_serie->id.'-'.$noline_serie->prefix)->first();
        if(!$header){
            $newHeader = new salesHeaderModel();
            $newHeader->no=$noline_serie->id.'-'.($noline_serie->prefix);
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
        $header = salesHeaderModel::whereIn('status',['New'])->first();
        $document_no = $header->no;
        $line_check= salesLineModel::where('document_no', $document_no)
        ->where('unit_of_measure', $data['uom'])
        ->where('item_no', $data['product_code'])
        ->first();
        if($line_check){
            $tital_qty = $line_check->quantity+1;
            $unit_price = GlobalFunction::numberFormate($line_check->unit_price,'amount');
            $descount_amount = GlobalFunction::numberFormate($line_check->discount_amount,'quantity');
            $line_check->quantity = $tital_qty;
            $line_check->amount =  $tital_qty*$unit_price- $descount_amount ;
            $line_check->save();
        }else{
            $salesLine = new salesLineModel();
            $salesLine->document_no = $header->no;
            $salesLine->item_no = $item->item_no;
            $salesLine->item_description = $item->description;
            $salesLine->item_description_2 = $item->description_2;
            $salesLine->unit_of_measure = $item->unit_of_measure_code;
            $salesLine->quantity = 1;
            $salesLine->qty_per_unit_of_measure = $item->qty_per_unit;
            $salesLine->unit_price = $item->price;
            $salesLine->unit_price_lcy = $item->price;
            $salesLine->discount_percentage = 0;
            $salesLine->discount_amount = 0;
            $salesLine->amount = $item->price;
            $salesLine->amount_lcy = $item->price;
            $salesLine->item_category_code =   $item_detail->item_category_code;
            $salesLine->item_group_code = $item_detail->item_group_code;
            $salesLine->created_by = Auth::user()->email;
            $salesLine->save();
        }

      
        $line = salesLineModel::where('document_no', $document_no)->get();
        $discount_amont =  $line->sum(function ($amount) {
            return $amount->discount_amount;
        });
        $amount =  $line->sum(function ($amount) {
            return $amount->amount;
        });
        $subtotal = $amount +  $discount_amont;
        $total_reil = $amount * $currenccy_khr;
        $view = view("pos.pos_list",compact('document_no','currenccy_khr','line','total_reil','subtotal','amount','discount_amont'))->render();
        return response()->json([
            'status'=>"Success ",
            'view' =>$view,
        ]);
    }
    public function updateLine(Request $request){
        $data = $request->all();
        $line = salesLineModel::where("id",$data['code'])->first();
        $item = ItemUnitofMeasureModel::where("item_no",$data['item_no'])
        ->where('unit_of_measure_code',$data['uom'])
        ->first();
        $document_no = $data['document_no'];
        if(!$item) return response()->json(['status'=>"warning",'message'=>"Item Not found in Databases"]);
        if(!$line) return response()->json(['status'=>"warning",'message'=>"Record Not found in Databases"]);
        $total_des = 0;
        $total_qty = GlobalFunction::numberFormate($data['qty'],'amount');
        $total_amount = GlobalFunction::numberFormate($item->price,'amount')*$total_qty;
        if(strpos($data['des'], '%') !== false){
            $descount_per=str_replace('%','',$data['des']);
            $total_des = $descount_per*$total_amount/100;
            $line->discount_percentage =   $descount_per;
            $line->discount_amount =$total_des;
        }else{
            $total_des = $data['des'];
            $line->discount_amount =$total_des ;

        }
        $line->quantity =  $total_qty;
        $line->unit_price = $item->price;
        $line->amount = $total_amount-$total_des;
        $line->amount_lcy = GlobalFunction::numberFormate($line->unit_price,'amount')*$total_qty;
        $line->created_by = Auth::user()->email;
        $line->unit_of_measure = $item->unit_of_measure_code;
        $line->save();
        $document_no = $line->document_no;
        $currenccy_khr = 0;
        $line_header = salesLineModel::where("id",$data['code'])->get();
        $view = view("pos.generate_tr",compact('line_header','document_no'))->render();
        dd((int)$this->currenccy_khr);
        $line = salesLineModel::where('document_no', $document_no)->get();
        $discount_amont =  $line->sum(function ($amount) {
            return $amount->discount_amount;
        });
        $amount =  $line->sum(function ($amount) {
            return $amount->amount;
        });
        $subtotal = $amount +  $discount_amont;
        $total_reil = $amount * (int)$this->currenccy_khr;
        $view_total = view('pos.pos_total',compact('discount_amont','amount','subtotal','total_reil'))->render();
        return response()->json([
            'status'=>"success",
            'message'=>"Data Update Successfully",
            'view' =>$view,
            'view_total' =>$view_total,
        ]
            
    );


    }
    public function deleteLine(Request $request){
        $data = $request->all();
        $line = salesLineModel::where("id",$data['code'])->first();
        if(!$line) return response()->json(['status'=>"warning",'message'=>"Record Not found in Databases"]);
        if($line){
            $line->delete();
        }
        $line = salesLineModel::where('document_no', $this->document_no)->get();
        $discount_amont =  $line->sum(function ($amount) {
            return $amount->discount_amount;
        });
        $amount =  $line->sum(function ($amount) {
            return $amount->amount;
        });
        $subtotal = $amount +  $discount_amont;
        $total_reil = $amount * (int)$this->currenccy_khr;
        $view = view('pos.pos_total',compact('discount_amont','amount','subtotal','total_reil'))->render();
        return response()->json([
            'status' => 'Success',
            'message'=>"Record delete Successfully",
            'view' => $view,
        ]);
    }
    public function getmodalPayment(Request $request){
        $data = $request->all();
        $currenccy_khr= $data['value'];
        $line = salesLineModel::where('document_no', $data['document'])->get();
        $document = $data['document'];
        $amount =  $line->sum(function ($amount) {
            return $amount->amount;
        });
        $discount_amont =  $line->sum(function ($amount) {
            return $amount->discount_amount;
        });
        $amount =  $line->sum(function ($amount) {
            return $amount->amount;
        });
        $subtotal = $amount +  $discount_amont;
        $total_reil = $amount * $currenccy_khr;
        $item = 1;
        $view = view('pos.pos_payment',compact('subtotal','total_reil','amount','discount_amont','item','document','currenccy_khr'))->render();
        return response()->json([
            'status' =>"success",
            'subtotal' => $subtotal,
            'total_reil' => $total_reil,
            'amount' => $amount,
            'discount_amont' => $discount_amont,
            'item' => $item,
            'view' => $view,
            'currenccy_khr' => $currenccy_khr
            
        ]);
    }
    public function submitPayment(Request $request){
        $data = $request->all();
        $header  = salesHeaderModel::where("no",$data['no'])->first();
        $noline_serie = lineNoserieModel::where('id','INE')->first();
        $currenccy_khr= $data['currenccy_khr'];
        $line = salesLineModel::where('document_no', $data['no'])->get();
        $amount =  $line->sum(function ($amount) {
            return $amount->amount;
        });
        $discount_amont =  $line->sum(function ($amount) {
            return $amount->discount_amount;
        });
        $amount =  $line->sum(function ($amount) {
            return $amount->amount;
        });
        $subtotal = $amount +  $discount_amont;
        $total_reil = $amount * $currenccy_khr;
        if(!$header) return response()->json(['status'=>"warning",'message'=>"Something when wrong!"]);
        foreach($data as $key=>$value){
            if($key != "_token" ){
                     $header[$key] =$value;
            }
        }
        $header->status ="Posted";
        $header->save();
        $noline_serie->prefix =  $noline_serie->prefix+1;
        $noline_serie->save();
        $document_no = $noline_serie->prefix+1;
        $this->document_no = $document_no;
        $currenccy_khr = 4100;
       
        $view = view('documents.ss5.pos_invoice',compact('amount','discount_amont','subtotal','total_reil','line'))->render();
        $line = null;
        $subtotal =  0;
        $total_reil = 0;
        $amount = 0;
        $discount_amont = 0;
        $view_card =  view("pos.pos_list",compact('document_no','currenccy_khr','line','total_reil','subtotal','amount','discount_amont'))->render();
        return response()->json([
            'status' => "Success",
            'view' =>$view,
            'view_card' => $view_card ,
        ]);

    }
    public function filterCategory(Request $request){
        $data = $request->all();
        $items=  DB::table('item_unit_of_measures')
        ->select('items.no', 'item_unit_of_measures.item_no', 'item_unit_of_measures.id', 'item_unit_of_measures.price', 'items.picture', 'items.unit_price', 'items.description', 'items.description_2', 'item_unit_of_measures.unit_of_measure_code', 'item_unit_of_measures.qty_per_unit', 'items.item_category_code', 'items.item_group_code')
        ->join('items', 'items.no', '=', 'item_unit_of_measures.item_no')
        ->offset(0)
        ->limit(60)
        ->groupBy('item_no')
        ->orderBy('item_unit_of_measures.id');
        if($data['code'] != "all"){
            $items = $items->where("items.item_category_code",$data['code']);
        }
        $items = $items->paginate(10);
        $view = view('pos.pos_action',compact('items'))->render();
        return response()->json([
            'status'=>'success',
            'view' =>$view
        ]);
    }
    public function SearchItem(Request $request){
        $data = $request->all();
        $items=  DB::table('item_unit_of_measures')
        ->select('items.no', 'item_unit_of_measures.item_no', 'item_unit_of_measures.id', 'item_unit_of_measures.price', 'items.picture', 'items.unit_price', 'items.description', 'items.description_2', 'item_unit_of_measures.unit_of_measure_code', 'item_unit_of_measures.qty_per_unit', 'items.item_category_code', 'items.item_group_code')
        ->join('items', 'items.no', '=', 'item_unit_of_measures.item_no')
        ->offset(0)
        ->limit(60)
        ->orderBy('item_unit_of_measures.id');
        if($data['code'] != " " || $data['code'] != ""){
            $items = $items->whereRaw(" item_unit_of_measures.item_no  LIKE '%".$data['code']."%' OR items.description  LIKE '%".$data['code']."%'");
        }
        $items = $items->paginate(10);
        $view = view('pos.pos_action',compact('items'))->render();
        return response()->json([
            'status'=>'success',
            'view' =>$view
        ]);
    }

    public function ajaxPagination(Request $request){
        $items=  DB::table('item_unit_of_measures')
        ->select('items.no', 'item_unit_of_measures.item_no', 'item_unit_of_measures.id', 'item_unit_of_measures.price', 'items.picture', 'items.unit_price', 'items.description', 'items.description_2', 'item_unit_of_measures.unit_of_measure_code', 'item_unit_of_measures.qty_per_unit', 'items.item_category_code', 'items.item_group_code')
        ->join('items', 'items.no', '=', 'item_unit_of_measures.item_no')
        ->offset(0)
        ->limit(60)
        ->orderBy('item_unit_of_measures.id')
        ->groupBy('item_unit_of_measures.item_no')
        ->paginate(10);
        $view =  view('pos.pos_action',compact('items'))->render();
        return response()->json([
        'status' => 'success',
        'view' => $view,
        ]);
    }
    public function logout(Request $request){
        DB::table('sessions')->where('user_id',Auth::user()->id)->delete();
        return redirect()->back();
    }
    public function hold(Request $request){
        $data = $request->all();
        $noline_serie = lineNoserieModel::where('id',Auth::user()->no_serise)->first();
        $noline_serie->prefix =  $noline_serie->prefix+1;
        $this->document_no = $noline_serie->prefix+1;
        $noline_serie->save();
        salesHeaderModel::where('no',$data['code'])->update(['status'=>'hold']);
        return  response()->json([
            'status' => "success",
        ]);

    }
}