<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\CustomerModel;
use App\Models\TableModel;
use Exception;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use PDO;
use Throwable;
use Yajra\DataTables\Facades\DataTables;

class CustomerController extends Controller
{
    public function index()
    {
        $title = 'Customer Setup';
        $table_name = 'customer';
        $is_card = '1';
        $link = 'customer/addnewcustomer';
        $field =  DB::getSchemaBuilder()->getColumnListing('customers');
        return view('customer.customer', compact('field','title','table_name','is_card','link'));
    }
    public function create(Request $request)
    {
        try {
            $field =  DB::getSchemaBuilder()->getColumnListing('customers');
            $data = new CustomerModel();
            foreach ($field as $f) {
                if ($f != 'updated_at' && $f != 'created_at' && $f != 'deleted_at') {
                    $data->$f = $request->$f;
                }
            }
            $data->save();
            toastr()->success('Data has been saved successfully!');
        return redirect()->back();
        } catch (\Exception $ex) {
            toastr()->warning('Some thing when wrong!');
            return redirect()->back();
        }
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $tablename = "Customer";
        // $code=CustomerModel::where('code',($request->code))->get();
        $data =  DB::getSchemaBuilder()->getColumnListing('customers');
        return view('customer.customer_card', compact('data'));
    }
    public function updaterecord(Request $request)
    {
        $update_field = [];
        $field =  DB::getSchemaBuilder()->getColumnListing('customers');
        $data = CustomerModel::find($request->no);
        // dd($data->no);
        foreach ($field as $f) {
            if ($f != 'updated_at' && $f != 'created_at' && $f != 'deleted_at') {
                $data->$f = $request->$f;
            }
        }
        $data->save();
        toastr()->success('Data has been saved successfully!');
        return redirect()->back();
    }

    /**
     * Display the specified resource.
     */
    public function show(Request $request)
    {
        if ($request->ajax()) {
            return DataTables::eloquent(CustomerModel::query())
                ->addIndexColumn()
                ->addColumn('action', function ($row) {
                    $actionBtn = " <a href='addnewcustomer?code=$row->no '> </button>
                <button class='edit' data-edit=$row->no> Edit
                </button>
                    </a>
                </button>
                <button class='actiondelete' data-delete=$row->no > Delete
                </button>";
                    return $actionBtn;
                })
                ->rawColumns(['action'])
                ->make(true);
        }
    }
    public function getfield()
    {
        $field =  DB::getSchemaBuilder()->getColumnListing('customers');
        return response()->json([
            'data' => $field,
        ]);
    }
    public function destroy(Request $request, $id)
    {
        $data = CustomerModel::where('no', $id)->delete();
        return response()->json([
            'status' => "Success"
        ]);
    }
}
