<?php

namespace App\Http\Controllers;

use App\Models\BarcodeModel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Yajra\DataTables\Facades\DataTables;

class BarcodeController extends Controller
{
    public function index(){
        $title = "Barcode Generator";
        $table_name = 'barcode';
        $is_form = 1;
        $field=  DB::getSchemaBuilder()->getColumnListing('barcode');
        return view('barcode.barcode',compact('field','title','table_name'));
    }
    public function transition(Request $request){
        $value = null;
        if(isset($_GET['code'])){
            $value = BarcodeModel::where('id',$_GET['code'])->first();
        }
        $field =  DB::getSchemaBuilder()->getColumnListing('barcode');
        return view('barcode.barcode_card',compact('field','value'));
    }
    public function store(Request $request){
        $get =$request->all();
        // dd($get);
        $data=new BarcodeModel();
        foreach($get as $key=>$d){
            if($key != "_token"){
                if($key == "password"){
                    $data['password']=bcrypt($d);
                }else{
                    $data->$key=$d;
                }
            }
        }
        $data->save();
        return response()->json([
            'status' =>'Success',
            'msg'    =>'New Barcode has been Setup',
        ]);
    }
    public function getfield(){
        $field=  DB::getSchemaBuilder()->getColumnListing('barcode');
        return response()->json([
            'data'=>$field,
        ]);
        
    }
    public function show(Request $request){
        if($request->ajax()){
            return DataTables::eloquent(BarcodeModel::query())
            ->addIndexColumn()
            ->addColumn('action', function ($row) {
                $actionBtn = "
                </button>
                <button class='print' data-edit=$row->id> Print
                </button>
                 </button>
                  </button>
                 <button class='edit' data-edit=$row->id> Edit
                 </button> 
                
                </button>
                <button class='actiondelete' data-delete=$row->id> Delete
                </button>" ;
                return $actionBtn;
            })
            ->rawColumns(['action']) 
            ->make(true);
        }
    }
    public function creatBarcode(Request $request){
        $barcode = BarcodeModel::where('id',$request->code)->first();
        $view = view('barcode.create_barcode',compact("barcode"))->render();
        return response()->json([
            'status' =>"Success",
            'view' =>$view,
        ]);
        // dd("it work");
    }
    public function delete(Request $request){
        $data = $request->all();
        BarcodeModel::where('id',$data['code_to_delete'])->first()->delete();
        return response()->json([
            'status' => "success"
        ]);
    }
    public function  showadd(Request $request)
    {
    
        if($request->ajax()) {
        $tablename="Barcode ";
          $data=  DB::getSchemaBuilder()->getColumnListing('barcode');
            return view('layouts.modal',compact('data','tablename'))->render();
    }
    }
    public function save(Request $request){
        $data =$request->all();
        $item=new BarcodeModel();
        foreach($data as $key=>$d){
            $item->$key=$d;
        }
        $item->save();
        return response()->json([
            'status'=>'Success'
        ]);
    }
    public function getValueEdit(Request $request){
        if($request->ajax()) {
            $tablename="Item Group";
            $code=BarcodeModel::where('id',($request->code))->get();
            $data=  DB::getSchemaBuilder()->getColumnListing('barcode');
            return view('layouts.modal',compact('code','data','tablename'))->render();
    }
      
    }
    public function editValue(Request $request){
        $data =$request->all();
        $item= BarcodeModel::where('id',$request->id)->first();
        foreach($data as $key=>$d){
            $item->$key=$d; 
        }
        $item->save();
        return response()->json([
            'status'=>'Success'
        ]);
    }
}
