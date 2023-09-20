<?php

namespace App\Http\Controllers;
use Illuminate\Http\Request;
use App\Http\Controllers\ItemsController;
use App\Models\ItemCategoryModel;
use App\Models\ItemGroupModel;
use App\Models\ItemModel;
use App\Models\ItemUnitofMeasureModel;
use App\Models\PermissionModel;
use App\Models\UnitofMeasureModel;
use App\Models\UserroleModel;
use App\Models\Warehouse;

class SystemController extends Controller
{
    public function getItem(Request $request,$group)
    {
        $data = $request->all();
        $condition = $data['q'];
        switch ($group) {
            case 'item_no':
                 $items = ItemModel::Where('no', 'like', '%' . $condition . '%')->get();
                break;
            case 'unit_of_measure_code':
                 $items = UnitofMeasureModel::select('code as no','description')->Where('code', 'like', '%' . $condition . '%')->get();
                 break;
            case 'item_category_code':
                    $items = ItemCategoryModel::select('code as no','description')->Where('code', 'like', '%' . $condition . '%')->get();
                break;
            case 'item_group_code' :
                    $items = ItemGroupModel::select('code as no','description')->Where('code', 'like', '%' . $condition . '%')->get();
                break;
            case 'permission_code' :
                    $items = PermissionModel::select('code as no','description')->Where('code', 'like', '%' . $condition . '%')->get();
                break;
            case 'user_role_code' :
                    $items = UserroleModel::select('code as no','description')->Where('code', 'like', '%' . $condition . '%')->get();
                break;
            case 'warehouse_code' :
                $items = Warehouse::select('code as no','description')->Where('code', 'like', '%' . $condition . '%')->get();
                //  print_r($items);
            break;
            case 'inactived' :
                $items = collect([(object) [
                                'no' => 'Yes',
                                'description' => 'Yes'
                            ],
                            (object) [
                                'no' => 'No',
                                'description' => 'No'
                            ]]);
                 
            break;
            case 'adjustment_type' :
                $items = collect([(object) [
                                'no' => 'negative',
                                'description' => 'Negative'
                            ],
                            (object) [
                                'no' => 'positive',
                                'description' => 'Positive'
                            ]]);
            break;
            default:
                
                break;
        }
       
        return  response()->json($items);
    }   
    public function getUom(Request $request){
        $data = $request->all();
        $condition = $data['q'];
        $items = UnitofMeasureModel::select('code as no','description')->Where('code', 'like', '%' . $condition . '%')
        ->get();
        return response()->json($items);
    }
   

}

