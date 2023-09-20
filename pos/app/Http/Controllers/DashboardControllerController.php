<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use Illuminate\Http\Request;
use App\Models\salesLineModel;
use Illuminate\Support\Facades\DB;
use App\Models\DashboardController;
use Symfony\Component\CssSelector\Node\FunctionNode;

class DashboardControllerController extends Controller
{
 
    public function index()
    {
        $stating_date = Carbon::now()->firstOfMonth()->toDateString();
        $ending_date = Carbon::now()->endOfMonth()->toDateString();
        $total = salesLineModel::whereBetween('sales_lines.created_at',[$stating_date,$ending_date])
        ->join('sales_headers', 'sales_headers.no', '=', 'sales_lines.document_no')
        ->get();
        $total_sales = $total->groupBy('document_no')->count(function($r){
            return $r->document;
        });
        $total_itemSales = $total->sum(function($r){
            return $r->quantity;
        });
        $total_customer = $total->groupBy('customer_no')->count(function($r){
                return $r->customer_no;
        });

        // dd( $total_sales );
        return view('hello',compact('total_sales','total_itemSales','total_customer','stating_date','ending_date'));
    }
 
}
