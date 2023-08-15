<?php

namespace App\Http\Controllers\ECommerce;
use App\Http\Controllers\Controller;
use App\Services\service;
use Auth;
use Illuminate\Http\Request;
use App\Models\ECommerce\EComSalesHeader; 
use App\Models\ECommerce\EComSalesLine; 
use App\MyNotification;
use Carbon\Carbon; 
use App\Models\ECommerce\EComShipmentMethod; 
use App\Models\ECommerce\EComShipmentAgent; 
use App\Models\ECommerce\EComDeliveryHeader; 
use DB;
use App\User; 
use App\UserOrganizations;
use App\Organizations;
use App\Models\Administration\ApplicationSetup\PaymentMethod; 
use App\Models\Financial\Setup\Customer;
use App\Models\Financial\Setup\Vendor;
use App\Models\Sales\Transaction\SaleHeader; 
use App\Models\Sales\Transaction\SaleLine; 
use App\Models\Administration\ApplicationSetup\Item; 
use App\Models\Financial\Setup\VatPostingSetup; 
use \App\Services\SalesService;
use App\Models\Administration\ApplicationSetup\ItemUnitOfMeasure; 
use App\Models\System\eCommerceSetup;
use App\Models\Administration\ApplicationSetup\Location;
use App\Models\Administration\ApplicationSetup\ShipmentMethod;
use App\Models\Administration\SystemSetup\ShipmentAgentEcom;
use App\UsersOrganizations; 
use App\Models\ECommerce\EComFeed;
use App\Models\Financial\Setup\NoSeries; 
use App\Models\Comments\Comment;
use App\Models\Financial\Setup\ApPostingGroup;
use App\Models\Sales\Setup\CustomerPriceGroup;
use App\UserVerification; 
use App\Models\Sales\History\CommissionLedgerEntry;
use App\Models\Purchase\Transaction\PurchaseHeader;
use App\Models\Purchase\Transaction\PurchaseLine;
use App\Models\Financial\Setup\ChartOfAccount;
use App\Services\PurchaseService;
use App\Models\Financial\Transaction\VendorDetailLedgerEntry;
use App\Models\Financial\Transaction\VendorLedgerEntry;
use App\Models\Financial\Setup\GeneralJournalBatch;
use App\Models\Financial\History\GeneralLedgerEntry;
use App\Models\System\ApplicationSetup; 
use App\Models\Loyalty\Setup\Stores; 
use App\Services\CashReceiptService;
use App\Models\ECommerce\PaymentLog;
use App\Models\Warehouse\Transaction\ItemLedgerEntry;
use App\Models\Financial\Transaction\ItemTrackingBuffer;

class EComController extends Controller
{
    protected $service;
    protected $sales_service;
    protected $purch_service;

    public function __construct()
    {
        $this->middleware('auth');
        $this->service = new service();
        $this->sales_service = new SalesService();
        $this->purch_service = new PurchaseService();
        $this->cash_receipt_service = new CashReceiptService();
    }
    public function incommingOrderGetConfirm(Request $request){
        try{
            $code = $this->service->decrypt($request->code); 
            $ecom_setup = eCommerceSetup::first();
            $table_field = $ecom_setup->getTableColumns(); 
            if(!$ecom_setup) {
                $ecom_setup = new eCommerceSetup(); 
                $ecom_setup->document_type = 'Invoice';
                $ecom_setup->location_code = 'MAIN';
                $ecom_setup->customer_no = 'GENERAL';
                $ecom_setup->auto_post = 'Yes';
                $ecom_setup->auto_assign_lot_no = 'No';
                $ecom_setup->save(); 
            }
            $record =  EComSalesHeader::where('id', $code)->where('org_id', Auth::user()->account_id)->first(); 
            if(!$record)  return response()->json(['status' => 'warning', "msg" => "Record not found."]);
            $user_org = UsersOrganizations::where('user_id',$record->customer_no)->where('organizations_id', $record->org_id)->first(); 
            if($user_org && $user_org->customer_no == ''){
                $user_org->customer_no = ($ecom_setup) ? $ecom_setup->customer_no : ''; 
                $user_org->save(); 
            }
            if(in_array('is_multiple_store',$table_field) && $ecom_setup->is_multiple_store == 'Yes'){
                $actual_latitude = $record->ship_to_latitude; 
                $actual_longitude = $record->ship_to_longitude; 
                $locations = DB::connection('company')->table('location')
                            ->join('stores','location.code', 'stores.location_code')
                            ->selectRaw('location.code,location.description,location.description_2,stores.latitude,stores.code as store_code, stores.longitude,stores.description as store_description')
                            ->where('location.inactived', '<>', 'Yes')
                            ->get(); 
                
                if($locations->count() > 0){
                    foreach($locations as $location){
                        $getKm = $this->service->getDistanceKm($location->latitude,$location->longitude,$actual_latitude,$actual_longitude);
                        $location->km_text = $getKm['text']; 
                        $location->km_value = $getKm['value']; 
                    }
                }
                $locations = $locations->sortBy('km_value');
            }else{
                $locations = Location::where('inactived', '<>', 'Yes')->get();  
            }
            $default_location = ($record->location_code) ? $record->location_code : $ecom_setup->location_code; 
            
            $series = NoSeries::whereIn('reference', ['Sales Order', 'Sales Invoice'])->where('inactived', '<>', 'Yes')->get(); 
            $view = view('system.modal_comfirm_incomming_order', compact('locations','code', 'series','ecom_setup', 'user_org','table_field', 'default_location'))->render();
            return response()->json(['status' => 'success', 'view' => $view ]);
            
        }catch(\Exception $ex){
            return response()->json(['status' => 'failed', "msg" => "msg updated failed!" . $ex->getMessage()]);
        }
    }
    public function incommingOrderConfirm(Request $request){        
        \DB::connection('company')->beginTransaction();
        try{           
            $data =  $request->all();
            $code = $this->service->decrypt($data['code']); 
            $app_setup = Auth::user()->app_setup; 
            $ecom_setup = eCommerceSetup::first();
            $document_type = 'Sales '.$ecom_setup->document_type;  
            $location_code = explode(",",$data['location_code']);  
            $is_create_so = 'No';   
            $array_user_ecoomer_en = []; 
            $array_user_ecoomer_kh = [];      
            // ===================== check when have multiple store =====
            // $table_field = $ecom_setup->getTableColumns(); 
            // if(in_array('is_multiple_store',$table_field) && $ecom_setup->is_multiple_store == 'Yes'){
            //     $store = Stores::where('code', $location_code[1])->where('inactived', '<>', 'Yes')->first();  
            //     if(!$store) return response()->json(['status' => 'Warning','message' => trans('greetings.Store code in Stores setup need to have a value.')]);
            // }
            
            // ======================== End ====================
            $ecomHeader =  EComSalesHeader::where('id', $code)->where('org_id', Auth::user()->account_id)->first();  
            if(!$ecomHeader)  return response()->json(['status' => 'Warning','message' => trans('greetings.Ecommer header document not found.')]);
            $ecom_lines = EComSalesLine::where('document_no',$ecomHeader->id)->get();  
            $current_date = Carbon::now();
            $order_datetime= Carbon::parse($ecomHeader->order_datetime);
            $response_minute = $order_datetime->diffInMinutes($current_date);                
            $customer = Customer::where('no', $ecomHeader->erp_customer_no)->first(); 
            if(!$customer)  return response()->json(['status' => 'Warning','message' => trans('greetings.Customer not found!.')]);
            $location = Location::where('code',$location_code[0])->first();
            
            // ======================== check payment refund ===
            if( hasColumnHelper('ecommerce_setup','allow_payment_refund') && $ecom_setup->allow_payment_refund == 'Yes' && $ecomHeader->paid == 'Yes'){
                // ACLEDA REQUEST CHECK STATUS 
                if(in_array(strtolower($ecomHeader->payment_method_code), ['acleda_xpay'])){
                   $result = $this->checkAcledaPaymentStatus($ecomHeader);
                   if(strtoupper($result['result']['errorDetails']) == 'REFUND_SUCCESS') return response()->json(['status' => 'Warning','message' => 'You cannot confirm this order because it already refunded from Acleda Bank.']);
                   if(strtoupper($result['status']) != 'SUCCESS')   return response()->json(['status' => 'Warning','message' => 'Something went wrong from Acleda Bank system.']);
                }
            }
            // ======================== End ====================

            //CUSTOMER GROUP PRICE         
            $customer_group_price = CustomerPriceGroup::where('code',$customer->customer_price_group_code)->first(); 
            if($customer_group_price && $customer_group_price->e_order_confirm_option == 'Both'){
                if($ecomHeader->customer_status == 'Confirmed') $is_create_so = 'Yes'; 
            }else{
                $is_create_so = 'Yes';
            }
            
            //UPDATE STATE CLEARVIEW ECOM                 
            if($ecomHeader->customer_status == 'Confirmed') $ecomHeader->status = 'Confirmed'; 
            else  $ecomHeader->status = 'Waiting Confirmation'; 
            $ecomHeader->response_in_minute = $response_minute;
            $ecomHeader->response_datetime = $current_date;
            $ecomHeader->save(); 
            
            if($is_create_so == 'Yes'){
                //CREATE SALES ORDER
                re_generate_no: 
                $no_series = $this->service->generateNo($ecom_setup->so_no_series, $document_type);
                if($no_series == 'error_no_series'){
                    \DB::connection('company')->rollback();
                    return response()->json(['status' => 'Warning','message' => trans('greetings.NoSeriesWasNotSetup')]);
                }
                $is_existed = SaleHeader::where('no',$no_series)->first(); 
                if($is_existed){
                    goto re_generate_no;
                }
                $ref_no_series = NoSeries::where('code',$ecom_setup->so_no_series)->first();
                
                $sale_header = new SaleHeader();
                $sale_header->no = $no_series;
                $sale_header->document_type = $data['document_type'];
                $sale_header->posting_description = $document_type . ' '. $sale_header->no;
                $sale_header->no_series = $ref_no_series->code;
                $sale_header->customer_no = $customer->no;
                $sale_header->customer_name = $customer->name;
                $sale_header->customer_name_2 = $customer->name_2;
                $sale_header->address = $customer->address;
                $sale_header->address_2 = $customer->address_2;
                $sale_header->post_code = $customer->post_code;
                $sale_header->village = $customer->village;
                $sale_header->commune = $customer->commune;
                $sale_header->district = $customer->district;
                $sale_header->province = $customer->province;
                $sale_header->country_code = $customer->country_code;
                $sale_header->contact_name = $customer->contact_name;
                $sale_header->tax_registration_no = $customer->tax_registration_no;
                $sale_header->location_code = $location_code[0];
                $sale_header->ship_to_code = $ecomHeader->ship_to_code;
                $sale_header->ship_to_name = $ecomHeader->ship_to_name;
                $sale_header->ship_to_name_2 = $ecomHeader->ship_to_name_2;
                $sale_header->ship_to_address = $ecomHeader->ship_to_address;
                $sale_header->ship_to_address_2 = $ecomHeader->ship_to_address_2;
                $sale_header->ship_to_post_code = $ecomHeader->ship_to_post_code;
                $sale_header->ship_to_village = $ecomHeader->ship_to_village;
                $sale_header->ship_to_commune = $ecomHeader->ship_to_commune;
                $sale_header->ship_to_district = $ecomHeader->ship_to_district;
                $sale_header->ship_to_province = $ecomHeader->ship_to_province;
                $sale_header->ship_to_country_code = $ecomHeader->ship_to_country_code;
                $sale_header->ship_to_contact_name = $ecomHeader->ship_to_contact_name;
                $sale_header->ship_to_phone_no = $ecomHeader->ship_to_phone_no;
                $sale_header->ship_to_phone_no_2 = $ecomHeader->ship_to_phone_no_2;
                $sale_header->document_date = Carbon::now()->toDateString();
                $sale_header->order_date = Carbon::now()->toDateString();
                $sale_header->posting_date = Carbon::now()->toDateString();
                $sale_header->request_shipment_date = Carbon::now()->toDateString();
                $sale_header->payment_term_code = $customer->payment_term_code;
                $sale_header->shipment_method_code = $customer->shipment_method_code;
                $sale_header->shipment_agent_code = ($ecomHeader->shipment_agent_code) ? $ecomHeader->shipment_agent_code : $customer->shipment_agent_code ;
                $sale_header->ar_posting_group_code = $customer->rec_posting_group_code;
                $sale_header->gen_bus_posting_group_code =  $customer->gen_bus_posting_group_code;
                $sale_header->vat_bus_posting_group_code = $customer->vat_posting_group_code;
                $sale_header->currency_code = $ecomHeader->currency_code;
                $sale_header->currency_factor = $ecomHeader->currency_factor;
                $sale_header->price_include_vat = $ecomHeader->price_include_vat;
                $sale_header->salesperson_code = $customer->salesperson_code; 
                $sale_header->distributor_code = $customer->distributor_code; 
                $sale_header->store_code =  $customer->store_code; 
                $sale_header->division_code = $customer->division_code; 
                // $sale_header->business_unit_code = (in_array('is_multiple_store',$table_field) && $ecom_setup->is_multiple_store == 'Yes') ? $store->business_unit_code :  $customer->business_unit_code; 
                $sale_header->department_code = $customer->department_code; 
                $sale_header->business_unit_code = (hasColumnHelper('location','business_unit_code')) ? $location->business_unit_code :  $customer->business_unit_code;
                $sale_header->project_code = $customer->project_code; 
                $sale_header->customer_group_code = $customer->customer_group_code; 
                $sale_header->external_document_no = $ecomHeader->document_no;  
                $sale_header->status = 'Open';
                $sale_header->created_by = Auth::user()->email;
                $sale_header->assign_to_userid =  Auth::user()->id;
                $sale_header->assign_to_username =  Auth::user()->email;
                $sale_header->source_type =  'eCommerce';            
                $sale_header->source_no =  $ecomHeader->document_no;
                $sale_header->save();

                $ecomHeader->erp_document_type = $document_type; 
                $ecomHeader->erp_document_no = $no_series; 
                $ecomHeader->save(); 

                /// ====== Create Default Comment Document
                $default_comment =  new Comment();
                $default_comment->user_id = Auth::user()->id;
                $default_comment->org_id = Auth::user()->account_id;
                $default_comment->entry_type = 'Sales';
                $default_comment->document_type = $sale_header->document_type;
                $default_comment->document_no = $sale_header->no;
                $default_comment->comment = $sale_header->document_type.' Created!';
                $default_comment->save();
                //SAVE LINE 

                foreach($ecom_lines as  $ecom_line){
                    $lines = SaleLine::select('line_no')->where('document_no',$sale_header->no)->max('line_no');
                    $item = Item::where('no', $ecom_line->no)->first();                 
                    if(!$item){ 
                        $item = new Item();
                        $item->no = $ecom_line->no;
                        $item->identifier_code = null;
                        $item->description = $ecom_line->description.'(Item Removed)';
                        $item->description_2 = $ecom_line->description_2.'(Item Removed)';
                        $item->is_service_item = 'No';
                        $item->prevent_negative_inventory = 'yes';
                        $item->costing_method = 'Average';
                        $item->unit_price = $this->service->toDouble($ecom_line->unit_price_lcy);
                        $item->is_adjustment_cost = 'Yes';
                        $item->standard_cost = 0;
                        $item->unit_cost = 0;
                        $item->last_direct_cost = 0;
                        $item->profit_calculation = 'Profit=Price-Cost';
                        $item->profit_percentage = 0;
                        $item->replenishment_system = 'Purchase';
                        $item->flushing_method = 'Manual';
                        $item->manufacturing_policy = 'Make-to-Stock';
                        $item->assembly_policy = 'Assemble-to-Stock';
                        $item->reordering_policy = 'Fixed Reorder Qty.';
                        $item->dampener_quantity = 0;
                        $item->safety_stock_quantity = 0;
                        $item->reorder_quantity = 0;
                        $item->maximum_inventory = 0;
                        $item->minimum_order_quantity = 0;
                        $item->maximum_order_quantity = 0;
                        $item->net_weight = 0;
                        $item->gross_weight = 0;
                        $item->inv_posting_group_code = $app_setup->default_inv_posting_group;
                        $item->gen_prod_posting_group_code = $app_setup->default_gen_prod_posting_group;
                        $item->vat_prod_posting_group_code = $app_setup->default_vat_prod_posting_group;
                        $item->stock_uom_code = $app_setup->default_stock_unit_measure;
                        $item->sales_uom_code = $app_setup->default_stock_unit_measure;
                        $item->purchase_uom_code = $app_setup->default_stock_unit_measure;
                        $item->save();
    
                        $item_uom_stock = ItemUnitOfMeasure::where('item_no',$item->no)->where('unit_of_measure_code',$item->stock_uom_code)->first();
                        if(!$item_uom_stock) {
                            $item_uom_stock = new ItemUnitOfMeasure();
                            $item_uom_stock->item_no = $item->no;
                            $item_uom_stock->identifier_code = null;
                            $item_uom_stock->unit_of_measure_code = $item->stock_uom_code;
                            $item_uom_stock->qty_per_unit = 1;
                            $item_uom_stock->price = 0;                                
                            $item_uom_stock->save();
                        }
    
                        $sell_unit_of_measure = trim($ecom_line->unit_of_measure, ' ');
                        if($item->stock_uom_code != $sell_unit_of_measure && $sell_unit_of_measure != null && $sell_unit_of_measure != ''){
                            $item_uom_sales = ItemUnitOfMeasure::where('item_no',$item->no)->where('unit_of_measure_code',$sell_unit_of_measure)->first();
                            if(!$item_uom_sales) {
                                $item_uom_sales = new ItemUnitOfMeasure();
                                $item_uom_sales->item_no = $item->no;
                                $item_uom_sales->identifier_code = null;
                                $item_uom_sales->unit_of_measure_code = $sell_unit_of_measure;
                                $item_uom_sales->qty_per_unit = $ecom_line->qty_per_unit_of_measure;
                                $item_uom_sales->price = $this->service->toDouble($ecom_line->unit_price_lcy);  
                                $item_uom_sales->save(); 
                            }
                        }                            
                        
                    }
                    $item_unit_of_measure = ItemUnitOfMeasure::where('unit_of_measure_code', $ecom_line->unit_of_measure)->where('item_no', $ecom_line->no)->first(); 
                    $line_no = ($lines) ? $lines : 0;
                    $line_no = $line_no + 10000;
    
                    $sales_line = new SaleLine();
                    $sales_line->document_type = $sale_header->document_type;
                    $sales_line->document_no = $sale_header->no;
                    $sales_line->line_no = $line_no;
                    $sales_line->refer_line_no = $line_no;
                    $sales_line->customer_no = $sale_header->customer_no;
                    $sales_line->type = 'Item';
                    $sales_line->no = $ecom_line->no;
                    $sales_line->variant_code = $ecom_line->variant_code;
                    $sales_line->location_code = $sale_header->location_code;
                    $sales_line->posting_group = $item->inv_posting_group_code;
                    $sales_line->description = $item->description;
                    $sales_line->description_2 = $item->description_2;
                    $sales_line->gen_prod_posting_group_code = $item->gen_prod_posting_group_code;
                    $sales_line->vat_prod_posting_group_code = $item->vat_prod_posting_group_code;
                    $sales_line->vat_percentage = $ecom_line->vat_percentage; 
                    $sales_line->vat_calculation_type = 'VAT Before Disc.';
                    if($customer->vat_posting_group_code && $item->vat_prod_posting_group_code){
                        $vat_post_group = VatPostingSetup::select('vat_calculation_type','vat_amount')
                                    ->where('vat_bus_posting_group',$customer->vat_posting_group_code)
                                    ->where('vat_prod_posting_group',$item->vat_prod_posting_group_code)->first();
                        if($vat_post_group){
                            $sales_line->vat_calculation_type = $vat_post_group->vat_calculation_type;
                            $sales_line->vat_percentage = $this->service->toDouble($vat_post_group->vat_amount);
                        }
                    }
                    $sales_line->item_category_code = $item->item_category_code;
                    $sales_line->item_group_code = $item->item_group_code;
                    $sales_line->item_disc_group_code = $item->item_disc_group_code;
                    $sales_line->item_brand_code = $item->item_brand_code; 
                    $sales_line->gen_bus_posting_group_code = $customer->gen_bus_posting_group_code;
                    $sales_line->vat_bus_posting_group_code = $customer->vat_posting_group_code;
                    $sales_line->unit_of_measure = $ecom_line->unit_of_measure;
                    // SELECT QTY PER UNIT FROM ITEM UNIT OF MEASURE 
                    $sales_line->qty_per_unit_of_measure = ($item_unit_of_measure) ? $this->service->number_formattor_database($item_unit_of_measure->qty_per_unit, 'amount') : 1;                
                    $sales_line->quantity = $this->service->toDouble($ecom_line->quantity);
                    $sales_line->outstanding_quantity = $this->service->toDouble($ecom_line->quantity);
                    $sales_line->outstanding_quantity_base = $this->service->toDouble($ecom_line->quantity) * $this->service->toDouble($ecom_line->qty_per_unit_of_measure);
                    $sales_line->quantity_to_ship = $this->service->toDouble($ecom_line->quantity);
                    $sales_line->quantity_to_invoice = $this->service->toDouble($ecom_line->quantity);
                    $sales_line->unit_price_ori = $this->service->toDouble($ecom_line->unit_price_ori);
                    $sales_line->unit_price = $this->service->toDouble($ecom_line->unit_price);
                    $sales_line->unit_price_lcy = $this->service->toDouble($ecom_line->unit_price_lcy);                
                    $sales_line->discount_percentage = $this->service->toDouble($ecom_line->discount_percentage);
                    $sales_line->discount_amount = $this->service->toDouble($ecom_line->discount_amount);
                    $sales_line->currency_code = null;
                    $sales_line->currency_factor = 1;
                    $sales_line->CalculateAmount();                       
                    $sales_line->customer_group_code = $sale_header->customer_group_code;                        
                    $sales_line->store_code = $sale_header->store_code;
                    $sales_line->division_code = $sale_header->division_code;
                    $sales_line->business_unit_code = $sale_header->business_unit_code;
                    $sales_line->department_code = $sale_header->department_code;
                    $sales_line->project_code = $sale_header->project_code;
                    $sales_line->distributor_code = $sale_header->distributor_code;
                    $sales_line->salesperson_code = $ecom_line->salesperson_code;
                    $sales_line->created_by = Auth::user()->email;
                    $sales_line->save();
                    // ========== Item Tracking LOT =========== 
                    if($item->item_tracking_code == 'LOTALL' && $ecom_setup->auto_assign_lot_no == 'Yes'){
                        $total_qty_to_handle = $this->service->toDouble($sales_line->quantity) * $this->service->toDouble($sales_line->qty_per_unit_of_measure);
                        $item_ledgers = ItemLedgerEntry::where('remaining_quantity','>',0)->where('item_no',$item->no)->where('location_code',$sales_line->location_code);
                        if($sales_line->variant_code){
                            $item_ledgers = $item_ledgers->where('variant_code',$sales_line->variant_code);
                        }
                        $item_ledgers = $item_ledgers->orderBy('expiration_date')->orderBy('entry_no')->get();
                        $total_remaining_qty = $item_ledgers->sum(function($r) {
                            return $this->service->toDouble($r->remaining_quantity);
                        });
                        if($total_remaining_qty < $total_qty_to_handle){
                            \DB::connection('company')->rollback();
                            return response()->json(['response_code' => '404'], 200);                                
                        }
                        foreach($item_ledgers as $item_ledger){ 
                            $item_buffer = new ItemTrackingBuffer();
                            $item_buffer->table_name = 'Sales';
                            $item_buffer->document_type = $sales_line->document_type;
                            $item_buffer->document_no = $sales_line->document_no;
                            $item_buffer->document_line_no = $sales_line->line_no;
                            $item_buffer->location_code = $sales_line->location_code;
                            $item_buffer->item_no = $sales_line->no;
                            $item_buffer->item_ledger_entry_no = $item_ledger->entry_no;
                            $item_buffer->serial_no = $item_ledger->serial_no;
                            $item_buffer->lot_no = $item_ledger->lot_no;
                            $item_buffer->warranty_date = $item_ledger->warranty_date;
                            $item_buffer->expiration_date = $item_ledger->expiration_date;
                            $item_buffer->quantity = $this->service->toDouble($sales_line->quantity);
                            $item_buffer->quantity_base = $this->service->toDouble($sales_line->quantity) * $this->service->toDouble($sales_line->qty_per_unit_of_measure);    
                            if($total_qty_to_handle > $this->service->toDouble($item_ledger->remaining_quantity)){                                    
                                $item_buffer->quantity_to_handle_base = $this->service->toDouble($item_ledger->remaining_quantity);                                    
                                $item_buffer->quantity_to_handle = $this->service->toDouble($item_buffer->quantity_to_handle_base) / $this->service->toDouble($sales_line->qty_per_unit_of_measure);
                                $total_qty_to_handle = $total_qty_to_handle - $this->service->toDouble($item_ledger->remaining_quantity);
                            }else {                                    
                                $item_buffer->quantity_to_handle_base = $total_qty_to_handle;
                                $item_buffer->quantity_to_handle = $this->service->toDouble($item_buffer->quantity_to_handle_base) / $this->service->toDouble($sales_line->qty_per_unit_of_measure);                                                                        
                                $total_qty_to_handle = 0;
                            }                                
                            $item_buffer->unit_of_measure = $sales_line->unit_of_measure;
                            $item_buffer->qty_per_unit_of_measure = $sales_line->qty_per_unit_of_measure;
                            $item_buffer->save();
                            if($total_qty_to_handle <= 0){
                                break;
                            }
                        }
                    }
                    // assing tracking line 
                    if($item->item_tracking_code != ''){
                        $result = $this->addTrackingLine($sale_header,$sales_line);
                        if ($result['status'] == 'error') {
                            \DB::connection('company')->rollback();
                            return response()->json(['status' => 'failed', 'msg' => $result['msg']]);
                        }
                    }

                    $ecom_line->erp_document_type = $document_type; 
                    $ecom_line->erp_document_no = $no_series; 
                    $ecom_line->save(); 
                }

                // insert delivery fee as service item to sales line 
                if( hasColumnHelper('ecommerce_setup','delivery_fee_item_service') && service::toDouble($ecomHeader->delivery_fee) != 0 ) {
                    // check if not select develery fee item service in eCommerce setup 
                    if(!$ecom_setup->delivery_fee_item_service) return response()->json(['status' => 'Warning','message' => trans('greetings.Field delivery_fee_setup in eCommerce setup need to have a value.')]);
                    $item = Item::where('no', $ecom_setup->delivery_fee_item_service)->first();
                    if(!$item)  return response()->json(['status' => 'Warning','message' => trans('greetings.Item not found.')]);  
                    $item_unit_of_measure = ItemUnitOfMeasure::where('item_no', $item->no)->first(); 

                    $lines = SaleLine::select('line_no')->where('document_no',$sale_header->no)->max('line_no');
                    $line_no = ($lines) ? $lines : 0;
                    $line_no = $line_no + 10000;

                    $sales_line = new SaleLine();
                    $sales_line->document_type = $sale_header->document_type;
                    $sales_line->document_no = $sale_header->no;
                    $sales_line->line_no = $line_no;
                    $sales_line->refer_line_no = $line_no;
                    $sales_line->customer_no = $sale_header->customer_no;
                    $sales_line->type = 'Item';
                    $sales_line->no = $ecom_setup->delivery_fee_item_service;
                    $sales_line->variant_code = $item->variant_code;
                    $sales_line->location_code = $sale_header->location_code;
                    $sales_line->posting_group = $item->inv_posting_group_code;
                    $sales_line->description = $item->description;
                    $sales_line->description_2 = $item->description_2;
                    $sales_line->gen_prod_posting_group_code = $item->gen_prod_posting_group_code;
                    $sales_line->vat_prod_posting_group_code = $item->vat_prod_posting_group_code;
                    $sales_line->vat_percentage = 0; 
                    $sales_line->vat_calculation_type = 'VAT Before Disc.';
                    if($customer->vat_posting_group_code && $item->vat_prod_posting_group_code){
                        $vat_post_group = VatPostingSetup::select('vat_calculation_type','vat_amount')
                                    ->where('vat_bus_posting_group',$customer->vat_posting_group_code)
                                    ->where('vat_prod_posting_group',$item->vat_prod_posting_group_code)->first();
                        if($vat_post_group){
                            $sales_line->vat_calculation_type = $vat_post_group->vat_calculation_type;
                            $sales_line->vat_percentage = $this->service->toDouble($vat_post_group->vat_percentage);
                        }
                    }
                    $sales_line->item_category_code = $item->item_category_code;
                    $sales_line->item_group_code = $item->item_group_code;
                    $sales_line->item_disc_group_code = $item->item_disc_group_code;
                    $sales_line->item_brand_code = $item->item_brand_code; 
                    $sales_line->gen_bus_posting_group_code = $customer->gen_bus_posting_group_code;
                    $sales_line->vat_bus_posting_group_code = $customer->vat_posting_group_code;
                    $sales_line->unit_of_measure = $ecom_line->unit_of_measure;
                    // SELECT QTY PER UNIT FROM ITEM UNIT OF MEASURE 
                    $sales_line->qty_per_unit_of_measure = ($item_unit_of_measure) ? $this->service->number_formattor_database($item_unit_of_measure->qty_per_unit, 'amount') : 1;                
                    $sales_line->quantity = $this->service->toDouble(1);
                    $sales_line->outstanding_quantity = $this->service->toDouble(1);
                    $sales_line->outstanding_quantity_base = $this->service->toDouble($ecom_line->quantity) * $this->service->toDouble($ecom_line->qty_per_unit_of_measure);
                    $sales_line->quantity_to_ship = $this->service->toDouble(1);
                    $sales_line->quantity_to_invoice = $this->service->toDouble(1);
                    $sales_line->unit_price_ori = $this->service->toDouble($ecomHeader->delivery_fee);
                    $sales_line->unit_price = $this->service->toDouble($ecomHeader->delivery_fee);
                    $sales_line->unit_price_lcy = $this->service->toDouble($ecomHeader->delivery_fee);  
                    $sales_line->currency_code = null;
                    $sales_line->currency_factor = 1;
                    $sales_line->CalculateAmount();                       
                    $sales_line->customer_group_code = $sale_header->customer_group_code;                        
                    $sales_line->store_code = $sale_header->store_code;
                    $sales_line->division_code = $sale_header->division_code;
                    $sales_line->business_unit_code = $sale_header->business_unit_code;
                    $sales_line->department_code = $sale_header->department_code;
                    $sales_line->project_code = $sale_header->project_code;
                    $sales_line->distributor_code = $sale_header->distributor_code;
                    $sales_line->salesperson_code = $sale_header->salesperson_code;
                    $sales_line->created_by = Auth::user()->email;
                    $sales_line->save();
                    
                }
            }                
            \DB::connection('company')->commit(); 
            //===== Ecommerce Users =====//
            $provider = $this->getProvider(Auth::user()->account_id); 
            $api_sessions_ecommerce_users = \DB::connection('mysql')->table('api_sessions')
                    ->join('users','users.id','api_sessions.user_id')
                    ->selectRaw('api_sessions.firebase_client_key,api_sessions.app_id,api_sessions.user_id,users.locale')
                    ->where('api_sessions.user_id', $ecomHeader['customer_no'])
                    ->where('api_sessions.app_id',$provider)
                    ->where('api_sessions.firebase_client_key', '<>','')
                    ->orderBy('api_sessions.id','DESC')
                    ->get();
            $noti_sessions = $api_sessions_ecommerce_users->unique("user_id");
            if(count($noti_sessions) > 0){
                foreach($noti_sessions as $api_session){
                    $sessionToken = openssl_random_pseudo_bytes(20);
                    $sessionToken = bin2hex($sessionToken);
                    $notification = new MyNotification();
                    $notification->id = $sessionToken;
                    $notification->type = 'App';
                    $notification->notifiable_id = $api_session->user_id;
                    $notification->notifiable_type = 'App\User';
                    $notification->description = 'The order no #' . $ecomHeader->document_no . ' has been confirmed.';
                    $notification->description_2 = 'ការបញ្ចាទិញលេខ #'. $ecomHeader->document_no. ' បានបញ្ជាក់';
                    $notification->entry_date = Carbon::now()->toDateString();
                    $notification->entry_datetime = Carbon::now();
                    $notification->document_type = 'EOrder';
                    $notification->document_no = $ecomHeader->document_no;
                    $notification->app_id = $api_session->app_id; 
                    $notification->data = json_encode([
                        'sender_id' => Auth::user()->id,
                        'header' => $ecomHeader,
                    ]);
                    $notification->save();
                    if($api_session->locale == 'kh') array_push($array_user_ecoomer_kh,$api_session->firebase_client_key);
                    else array_push($array_user_ecoomer_en,$api_session->firebase_client_key); 
                    
                }
            }
            $ecomHeader->title_kh = 'ការបញ្ជាទិញត្រូវបានបញ្ជាក់'; 
            $ecomHeader->body_kh = 'ការបញ្ចាទិញលេខ #'. $ecomHeader->document_no. ' បានបញ្ជាក់'; 
            $ecomHeader->title_en = 'Order Comfirmed'; 
            $ecomHeader->body_en = 'The order no #' . $ecomHeader->document_no . ' has been confirmed.'; 
            $this->sendNotificationSpacificUserLangaugeApp($ecomHeader,$array_user_ecoomer_en,$array_user_ecoomer_kh);
            $order = DB::connection('company')->table('esales_header')->join('esales_line', 'esales_header.id', 'esales_line.document_no')
                    ->where('esales_header.org_id', Auth::user()->account_id)
                    ->where('esales_header.id',$code)
                    ->whereRaw("(esales_header.erp_status = 'New' or esales_header.status <> 'Delivered')")
                    ->selectRaw('esales_header.id,esales_header.delivery_fee, esales_header.document_no, esales_header.tracking_no,esales_header.currency_code, esales_header.customer_no, esales_header.ship_to_name, esales_header.ship_to_address, sum(esales_line.amount_including_vat_lcy) as amount_including_vat_lcy, esales_header.status,esales_header.ship_to_phone_no,esales_header.payment_method_code,esales_header.erp_status, esales_header.order_datetime')
                    ->groupBy('esales_header.document_no','esales_header.id','esales_header.delivery_fee', 'esales_header.tracking_no','esales_header.currency_code', 'esales_header.customer_no', 'esales_header.ship_to_name', 'esales_header.ship_to_address', 'esales_header.status', 'esales_header.ship_to_phone_no', 'esales_header.payment_method_code', 'esales_header.erp_status', 'esales_header.order_datetime')
                    ->orderBy('esales_header.order_datetime', 'desc')->first(); 
            $titile = trans('greetings.Order Successful Confirmed.'); 
            $sub_title = trans('greetings.Order has been confirmed.'); 
            $view = view('system.message_success', compact('titile','sub_title'))->render(); 
            
            if(isset($request['action_from']) && $request['action_from'] == 'ecommerce_order'){
                $record = $ecomHeader;
                $incomming_view_record = view('sales.history.ecommerce_order_records', compact('record'))->render();
            }else{
                $incomming_view_record = view('dashboard.activites.activity.wholesaler_ordering_records', compact('order','ecom_setup'))->render(); 
            }
            
            return response()->json([
                'status' => 'success',
                'message' => 'This Order has been comfirmed.!',                     
                'record' => $ecomHeader,                    
                'view' => $view,
                'incomming_view_record' => $incomming_view_record, 
                'id' => $code,
            ], 200);
           
        }catch(\Exception $ex){            
            \DB::connection('company')->rollback();
            return response()->json(['status' => 'failed', "message" =>  $ex->getMessage(), 'line' => $ex->getLine()]);
        }
    }
    public function incommingCreateDelivery(Request $request){
        
        try{
            $code = $this->service->decrypt($request->code); 
            $record = EComSalesHeader::where('id', $code)->first(); 
            $default_shipment_agent = ($record) ? $record->shipment_agent_code : ''; 
            $default_shipment_method = ($record) ? $record->shipment_method_code : ''; 
            $shipment_methods = EComShipmentMethod::where('inactived', '<>', 'Yes')->get(); 
        
            $view = view('system.modal_create_delivery', compact('shipment_methods', 'default_shipment_agent', 'default_shipment_method', 'code'))->render(); 
            return response()->json(['status' => 'success', 'view' => $view]);
        }catch(\Exception $ex){
            return response()->json(['status' => 'failed', "message" => "token updated failed!" . $ex->getMessage()]);
        }
    }
    public function getShipmentAgent(Request $request){
        try{
            $code = $this->service->decrypt($request->code); 
            $record = EComSalesHeader::where('id', $code)->first(); 
            $default_shipment_agent = (isset($record)) ? $record->shipment_agent_code : '';
            $shipment_agents = EComShipmentAgent::where('shipment_method_code', $code)->where('inactived', '<>', 'Yes')->get(); 
            
            if($shipment_agents){
                $view = view('system.modal_agent_records', compact('shipment_agents', 'default_shipment_agent', 'code'))->render(); 
                return response()->json(['status' => 'success', 'view' => $view ]);
            }
            
        }catch(\Exception $ex){
            return response()->json(['status' => 'failed', "message" => "token updated failed!" . $ex->getMessage()]);
        }  
    }
    public function createDeliveryOrder(Request $request){
        try {
            $data = $request->all();
            $array_user_ecoomer_en = []; 
            $array_user_ecoomer_kh = []; 
            $id = $this->service->decrypt($data['id']);
            $requester = Auth::user()->id; 
            $agent_code = $this->service->decrypt($data['agent_code']); 
            $header = EComSalesHeader::where('id', $id)->first();
            $ecom_org = Organizations::where('id', Auth::user()->account_id)->first(); 
            $ecom_setup = eCommerceSetup::first();
            if(!$header) return response()->json(['status'=>'warning', 'message'=> trans('greetings.Order is not found!')]);
            if(!in_array($header->status , ['New', 'Confirmed'])){
                return response()->json(['status' => 'warning', 'msg' => trans('greetings.The order cannot make to delivery while the status is not Confirmed!')]);
            }
            $lines = EComSalesLine::where('document_no',$header->id)->where('document_type',$header->document_type)->orderby('line_no')->get();
            $shipment_method = ShipmentMethod::where("code", $this->service->decrypt($data['shipment_method_code']))->first();
            if(!$shipment_method) return response()->json(['status' => 'warning', 'message'=>"shipment_method_is_not_found!"]);
            $shipment_agent = ShipmentAgentEcom::where("code", $agent_code)->where("inactived","<>","Yes")->first();
            if(!$shipment_agent) return response()->json(['status' => 'warning', 'message'=>"shipment_agent_is_not_found!"]);
            $delivery_header = EComDeliveryHeader::where("shipment_agent_code", $shipment_agent->code)->whereIn('status', ['Shipping','Pending'])
                        ->where("org_id", Auth::user()->account_id)->first();
            if(false) return response()->json(['status' => 'warning', 'message'=>trans("greetings.the_shipment_agent_is_busy")]);
            
            \DB::connection('company')->beginTransaction();
            $sales_header = SaleHeader::where('no', $header->erp_document_no)->first();   
            if($sales_header){
                $sales_header->status = "Approved";
                $sales_header->save();
            }             

            $delivery_header = new EComDeliveryHeader();
            $columns = $delivery_header->getTableColumns();
            $excludeColumns = ['id','created_at','updated_at','deleted_at','is_deleted'];
            $decimalColumns = ['actual_longitude', 'actual_distance', 'latitude', 'longitude'];
            $dateColumns = ['order_date'];
            foreach ($columns as $column) {
                if (in_array($column, $excludeColumns)) continue;
                if (!isset($header[$column])) continue;

                if (in_array($column, $decimalColumns)) {
                    if ($header[$column] !== '' && $header[$column] !== null) $delivery_header[$column] = $this->service->toDouble($header[$column]);
                } elseif (in_array($column, $dateColumns)) {
                    if ($header[$column] !== '' && $header[$column] !== null) $delivery_header[$column] = Carbon::parse($header[$column])->toDateString();
                } else {
                    $delivery_header[$column] = $header[$column];
                }
                $delivery_header->save();
            }
            $payment_amount = count($lines) > 0 ? $lines->sum(function($record) {
                return $this->service->toGDouble($record->amount_including_vat);
            }) : 0;
            $delivery_header->tracking_no = $this->generateTrackingNo($delivery_header->id);
            $delivery_header->shipment_agent_code = $shipment_agent->code;
            $delivery_header->agent_id = $shipment_agent->user_id;
            $delivery_header->shipment_agent_name = $shipment_agent->name;
            $delivery_header->shipment_agent_name_2 = $shipment_agent->name_2;
            $delivery_header->shipment_agent_phone_no = $shipment_agent->phone_no;
            $delivery_header->shipment_agent_phone_no_2 = $shipment_agent->phone_no_2;
            $delivery_header->shipment_agent_plate_no = $shipment_agent->plate_no;
            $delivery_header->delivery_fee = $header->delivery_fee;
            $delivery_header->payment_amount = $payment_amount + $this->service->toGDouble($header->delivery_fee);
            if($ecom_setup->use_delivery_app == 'Yes') $delivery_header->status = "Pending";
            else $delivery_header->status = "Shipping";
            $delivery_header->seller_id = Auth::user()->id;
            $delivery_header->customer_id = $header->customer_no;
            $delivery_header->f_latitude = $ecom_org->latitude;
            $delivery_header->f_longitude = $ecom_org->longitude;
            $delivery_header->t_latitude = $header->ship_to_latitude;
            $delivery_header->t_longitude = $header->ship_to_longitude;
            $delivery_header->a_latitude = $ecom_org->latitude;
            $delivery_header->a_longitude = $ecom_org->longitude;
            $delivery_header->save();

            $header->tracking_no = $delivery_header->tracking_no;
            if($ecom_setup->use_delivery_app == 'Yes') $header->status = 'Pending';
            else $header->status = 'Shipping';
            $header->save();

            if($ecom_setup->use_delivery_app == 'Yes'){
                // ===== FIREBASE ========
                $firebase = service::FirebaseRealTimeDatabase();
                $database = $firebase->getDatabase();
                // ===== FIREBASE DRIVER ========== 
                $driver = User::where('id', $shipment_agent->user_id)->first(); 
                if(!$driver) return response()->json(['status' => 'warning', 'message'=>"Driver not found!"]);
                $ref = $driver->account_id."/".$driver->shipment_method_code."/".$driver->id;
                $request_driver_ref = $database->getReference("drivers/gps_tracking/".$ref);
                $request_driver_snapshot = $request_driver_ref->getSnapshot();
                // ===== FIREBASE REMOVE EXISTING DRIVER ========== 
                if(!$request_driver_snapshot->exists()) $request_driver_ref->remove(); 

                $response = $this->createFirebaseDeliveryOrder($header,$shipment_agent); 
                if($response != 'success') {
                    \DB::connection('company')->rollback();
                    return response()->json([
                        'status' => 'failed',
                        'message' => 'Something Went Wrong!', 
                    ]);
                }

                //===== Notify Driver =====//
                $api_sessions_driver_users = \DB::connection('mysql')->table('api_sessions')
                    ->where('user_id', $shipment_agent->user_id)
                    ->whereIn('app_id', ["com.clearviewerp.express"])
                    ->where('firebase_client_key', '<>','')
                    ->get();
                $array_driver_users = $api_sessions_driver_users->pluck("firebase_client_key");                
                $fcmDriverNotificationData = [
                    'registration_ids' => $array_driver_users,
                    'notification' => [
                        'title' => 'Delivery Order Created',  
                        'body' => 'Request delivery order ship to '.$header['ship_to_name'].' '.$header['ship_to_address'].' '.$header['ship_to_phone_no'],
                        'sound' => 'default'
                    ],
                    'data' => [
                        'tracking_no' => $header->tracking_no,
                        'type' => 'RequestShiping',
                        'status' => 'Shipping'
                    ]
                ];

                $driver_noti_sessions = $api_sessions_driver_users->unique("user_id");
                if(count($driver_noti_sessions) > 0){
                    foreach($driver_noti_sessions as $api_session){
                        $sessionToken = openssl_random_pseudo_bytes(20);
                        $sessionToken = bin2hex($sessionToken);
                        $notification = new MyNotification();
                        $notification->id = $sessionToken;
                        $notification->type = 'App';
                        $notification->notifiable_id = $api_session->user_id;
                        $notification->title = $fcmDriverNotificationData['notification']['title'];
                        $notification->description = $fcmDriverNotificationData['notification']['body'];
                        $notification->entry_date = Carbon::now()->toDateString();
                        $notification->entry_datetime = Carbon::now();
                        $notification->document_type = 'EOrder';
                        $notification->document_no = $header->document_no;
                        $notification->app_id = $api_session->app_id; 
                        $notification->data = json_encode([
                            'sender_id' => Auth::user()->id,
                            'header' => $header,
                        ]);
                        $notification->save();
                    }
                }

                $this->service->sendNotification($fcmDriverNotificationData);
            }
            
            //===== Notify Quest =====//
            $provider = $this->getProvider(Auth::user()->account_id); 
            $api_sessions_ecommerce_users = \DB::connection('mysql')->table('api_sessions')
                    ->join('users','users.id','api_sessions.user_id')
                    ->selectRaw('api_sessions.firebase_client_key,api_sessions.app_id,api_sessions.user_id,users.locale')
                    ->where('api_sessions.user_id', $header['customer_no'])
                    ->where('api_sessions.app_id',$provider)
                    ->where('api_sessions.firebase_client_key', '<>','')
                    ->orderBy('api_sessions.id','DESC')
                    ->get();
            $array_session = []; 
            $quest_noti_sessions = $api_sessions_ecommerce_users->unique("user_id");                
            if(count($quest_noti_sessions) > 0){
                foreach($quest_noti_sessions as $api_session){
                    $sessionToken = openssl_random_pseudo_bytes(20);
                    $sessionToken = bin2hex($sessionToken);
                    $notification = new MyNotification();
                    $notification->id = $sessionToken;
                    $notification->type = 'App';
                    $notification->notifiable_id = $api_session->user_id;
                    $notification->notifiable_type = 'App\User';
                    $notification->title = 'Delivery Order Created';
                    $notification->description = 'Your delivery order no #' . $header->document_no . ' has been created!'; 
                    $notification->description_2 = 'ការបញ្ជាទិញដឹកជញ្ជូនរបស់អ្នក លេខ #'.$header->document_no.' ត្រូវបានបង្កើតឡើង'; 
                    $notification->entry_date = Carbon::now()->toDateString();
                    $notification->entry_datetime = Carbon::now();
                    $notification->document_type = 'EOrder';
                    $notification->document_no = $header->document_no;
                    $notification->app_id = $api_session->app_id; 
                    $notification->data = json_encode([
                        'sender_id' => Auth::user()->id,
                        'header' => $header,
                    ]);
                    $notification->save();
                    if($api_session->locale == 'kh') array_push($array_user_ecoomer_kh,$api_session->firebase_client_key);
                    else array_push($array_user_ecoomer_en,$api_session->firebase_client_key);

                }
            } 
            $header->title_kh = 'ការបញ្ជាទិញដឹកជញ្ជូនត្រូវបានបង្កើតឡើង'; 
            $header->body_kh =  'ការបញ្ជាទិញដឹកជញ្ជូនរបស់អ្នក លេខ #'.$header->document_no.' ត្រូវបានបង្កើតឡើង'; 
            $header->title_en = 'Delivery Order Created'; 
            $header->body_en = 'Your delivery order no #' . $header->document_no . ' has been created!'; 
            
            $this->sendNotificationSpacificUserLangaugeApp($header,$array_user_ecoomer_en,$array_user_ecoomer_kh);
            $this->removeFirebaseDriver($header, $delivery_header);
            \DB::connection('company')->commit();
            
            $order = DB::connection('company')->table('esales_header')->join('esales_line', 'esales_header.id', 'esales_line.document_no')
                ->where('esales_header.org_id', Auth::user()->account_id)
                ->where('esales_header.id',$id)
                ->whereRaw("(esales_header.erp_status = 'New' or esales_header.status <> 'Delivered')")
                ->selectRaw('esales_header.id,esales_header.delivery_fee, esales_header.document_no, esales_header.tracking_no,esales_header.currency_code, esales_header.customer_no, esales_header.ship_to_name, esales_header.ship_to_address, sum(esales_line.amount_including_vat_lcy) as amount_including_vat_lcy, esales_header.status,esales_header.ship_to_phone_no,esales_header.payment_method_code,esales_header.erp_status, esales_header.order_datetime')
                ->groupBy('esales_header.document_no','esales_header.id','esales_header.delivery_fee', 'esales_header.tracking_no','esales_header.currency_code', 'esales_header.customer_no', 'esales_header.ship_to_name', 'esales_header.ship_to_address', 'esales_header.status', 'esales_header.ship_to_phone_no', 'esales_header.payment_method_code', 'esales_header.erp_status', 'esales_header.order_datetime')
                ->orderBy('esales_header.order_datetime', 'desc')->first(); 

            $view = view('system.modal_delivery_success')->render(); 
            if(isset($request['action_from']) && $request['action_from'] == 'ecommerce_order'){
                $record = $header;
                $incomming_view_record = view('sales.history.ecommerce_order_records', compact('record'))->render();
            }else{
                $incomming_view_record = view('dashboard.activites.activity.wholesaler_ordering_records', compact('order','ecom_setup'))->render(); 
            }
            // CHECK SEND TO TELEGRAM DELIVERY 
            $table_field = $shipment_agent->getTableColumns(); 
            $table_field_ecom = $ecom_setup->getTableColumns(); 
            $telegram_chat_id = '';
            $telegram_bot_token = ''; 
            if (in_array('telegram_chat_id',$table_field) && $shipment_agent->telegram_chat_id)  $telegram_chat_id = $shipment_agent->telegram_chat_id; 
            if(in_array('telegram_bot_token',$table_field_ecom) && $ecom_setup->telegram_bot_token) $telegram_bot_token = $ecom_setup->telegram_bot_token; 

            return response()->json([
                'status' => 'success',
                'message' => 'success', 
                'id' => $header->id,
                'incomming_view_record' => $incomming_view_record, 
                'telegram_bot_token' => $telegram_bot_token,
                'telegram_chat_id' => $telegram_chat_id, 
                'latitude' => $delivery_header->ship_to_latitude, 
                'longitude' => $delivery_header->ship_to_longitude, 
                'ship_to_address' => $header->ship_to_address, 
                // 'order_no' => $sales_header->no. '('. $header->document_no. ')', 
                // 'customer_name' => $sales_header->customer_name . ' '. $sales_header->ship_to_phone_no, 
                'delivery_note' => $header->delivery_notes,
                'view' => $view, 
            ], 200);

        }catch(\Exception $ex){
            \DB::connection('company')->rollback();
            $this->service->saveErrorLog($ex);
            return response()->json(['status' => 'failed', "message" => "token updated failed!" . $ex->getMessage(), 'line' =>$ex->getLine() ]);
        }
    }
    public function deliveryOrderDelivered(Request $request){
       try {
            $is_gl_posting = Auth::user()->is_gl_posting();
            $user = Auth::user();            
            $user_setup = Auth::user()->user_setup;
            $app_setup = Auth::user()->app_setup; 
            $ecom_setup = eCommerceSetup::first();  
            $table_field = $ecom_setup->getTableColumns(); 
            $id = $this->service->decrypt($request->code); 

            $header = EComSalesHeader::where('id', $id)->where('org_id', Auth::user()->account_id)->first();
            if(!$header) return response()->json(['status'=>'warning', 'message'=>trans('greetings.Order is not found!')]);
            if($header->status != 'Shipping' && $header->status != 'Pending') return response()->json(['status'=>'warning', 'message'=>trans('greetings.This order already delivered.!')]);
            $delivery_header = EComDeliveryHeader::where('tracking_no', $header->tracking_no)->where('org_id', Auth::user()->account_id)->first(); 
            if(!$delivery_header) return response()->json(['status'=>'warning', 'message'=>trans('greetings.Delivery header not found!')]);
            $lines = EComSalesLine::where('document_no', $header->id)->where('org_id', Auth::user()->account_id)->get(); 
            $user_org = UsersOrganizations::where('user_id', $header->customer_no)->where('organizations_id', Auth::user()->account_id)->first(); 
            $amount = count($lines) > 0 ? $lines->sum(function($r) {
                return service::toDouble($r->amount_including_vat_lcy);
            }) : 0; 
            $amount = service::toDouble($amount) + service::toDouble($header->delivery_fee);    
            // CHECK COMFIRM AMOUNT 
            if(in_array('confirm_amount_on_delivered',$table_field) && $ecom_setup->confirm_amount_on_delivered == 'Yes'){
                if(service::toDouble($request->payment_amount) > service::toDouble($amount)) return response()->json(['status'=>'warning', 'message'=>trans('greetings.Payment amount cannot bigger then receipt amount '.$this->service->number_formattor($amount,'amount'))]);
                // CHECK CUSTOMER ALLOW TO CREDIT OR NOT 
                if((in_array('allow_to_credit',$table_field) && $ecom_setup->allow_to_credit == 'No')) {
                    if(service::toDouble($request->payment_amount) == 0 )  response()->json(['status'=>'warning', 'message'=>trans('greetings.Amount need to have a value.')]);
                    if(service::toDouble($request->payment_amount) < service::toDouble($amount))  return response()->json(['status'=>'warning', 'message'=>trans('greetings.Payment amount cannot less then amount '.$this->service->number_formattor($amount,'amount'))]);
                }
                $amount = $request->payment_amount; 
            }
            \DB::connection('company')->beginTransaction();
            $header->status = 'Delivered'; 
            $header->save(); 

            $commission_amount = 0; 
            $commission_amount_lcy = 0;
            $sales_amount = 0; 
            $sales_amount_ori = 0;
            $sales_amount_lcy = 0; 
            $sales_amount_lcy_ori = 0;
            $array_user_ecoomer_en = []; 
            $array_user_ecoomer_kh = []; 

            foreach($lines as $line){
                if(service::toDouble($line->unit_price) <> service::toDouble($line->unit_price_ori)){                    
                    $subtotal_ori = service::toDouble($line->quantity) * service::toDouble($line->unit_price_ori);
                    $direct_unit_cost_exclude_vat_ori = (service::toDouble($line->unit_price_ori) / (1 + (service::toDouble($line->vat_percentage) / 100)));
                    $subtotal_exclude_vat_ori = service::toDouble($line->quantity) * service::toDouble($direct_unit_cost_exclude_vat_ori);            
                    $discount_percentage_amount_ori = $subtotal_ori * service::toDouble($line->discount_percentage) / 100;
                    $ration = 1 + (service::toDouble($line->vat_percentage) / 100);
                    $amount_including_vat_ori = service::toDouble($subtotal_ori) - $discount_percentage_amount_ori - service::toDouble($line->discount_amount);
                    $amount_including_vat_lcy_ori = service::toDouble($amount_including_vat_ori) * service::glcyExchangeAmount() / service::toDouble($line->currency_factor);            
                    $amount_ori = service::toDouble($amount_including_vat_ori) / $ration;
                    $amount_lcy_ori = service::toDouble($amount_ori) * service::glcyExchangeAmount() / service::toDouble($line->currency_factor);            
                    $vat_base_amount_ori = service::toDouble($amount_ori);
                    $vat_amount_ori = service::toDouble($vat_base_amount_ori) * service::toDouble($line->vat_percentage) / 100;

                    $commission_amount += service::toDouble($line->amount_including_vat) - $amount_including_vat_ori;
                    $commission_amount_lcy += service::toDouble($line->amount_including_vat_lcy) - $amount_including_vat_lcy_ori;
                    $sales_amount += service::toDouble($line->amount_including_vat);
                    $sales_amount_ori += service::toDouble($amount_including_vat_ori);
                    $sales_amount_lcy += service::toDouble($line->amount_including_vat_lcy);
                    $sales_amount_lcy_ori += service::toDouble($amount_including_vat_lcy_ori);
                }
            }
            if($commission_amount > 0 && $app_setup->e_commission == 'Yes'){  
                $vendor = Vendor::where('no', $user_org->vendor_no)->first(); 
                if(!$vendor){
                    \DB::connection('company')->rollback();
                    return response()->json(['status'=>'warning', 'message'=> trans('greetings.You cannot create commission ledger entry, while vendor information not yet setup!')]);
                }
                if (!$vendor->ap_posting_group_code || $vendor->ap_posting_group_code == '') {
                    \DB::connection('company')->rollback();
                    return response()->json(['status'=>'warning', 'message'=> trans('greetings.VendorAPPostingGroupCodeMustHaveAValue')]);
                }                
                if (!$vendor->gen_bus_posting_group_code || $vendor->gen_bus_posting_group_code == '') {
                    \DB::connection('company')->rollback();
                    return response()->json(['status'=>'warning', 'message'=> trans('greetings.VendorGenBusPostingGroupCodeMustHaveAValue')]);
                }
                if (!$vendor->vat_bus_posting_group_code || $vendor->vat_bus_posting_group_code == '') {
                    \DB::connection('company')->rollback();
                    return response()->json(['status'=>'warning', 'message'=> trans('greetings.VendorVATBusPostingGroupCodeMustHaveAValue')]);
                }                    
                if (!$vendor->payment_term_code || $vendor->payment_term_code == '') {
                    \DB::connection('company')->rollback();
                    return response()->json(['status'=>'warning', 'message'=> trans('greetings.VendorPaymentTermCodeMustHaveAValue')]);
                } 
                $ap_posting_group = ApPostingGroup::where('code', $vendor->ap_posting_group_code)->first();
                if(!$ap_posting_group){
                    \DB::connection('company')->rollback();
                    return response()->json(['status'=>'warning', 'message'=> trans('greetings.VendorAPPostingGroupCodeMustHaveAValue')]);
                }
                
                $commission_ledger_entry = new CommissionLedgerEntry();
                $commission_ledger_entry->salesperson_code = $header->customer_no; 
                $commission_ledger_entry->salesperson_name = $header->customer_name;  
                $commission_ledger_entry->customer_no = $header->customer_no; 
                $commission_ledger_entry->customer_name = $header->customer_name;
                $commission_ledger_entry->posting_date = Carbon::now()->toDateString(); 
                $commission_ledger_entry->document_date = Carbon::now()->toDateString(); 
                if($header->payment_method_code == 'PAID'){
                    $commission_ledger_entry->document_type = 'Prepayment'; 
                    $commission_ledger_entry->document_no = $header->document_no; 
                    $commission_ledger_entry->currency_factor = 1; 
                    $commission_ledger_entry->amount = $sales_amount_ori * -1; 
                    $commission_ledger_entry->amount_lcy = $sales_amount_lcy_ori * -1; 
                    $commission_ledger_entry->remaining_amount = 0; 
                    $commission_ledger_entry->remaining_amount_lcy = 0; 
                    $commission_ledger_entry->sales_amount = $sales_amount_ori; 
                    $commission_ledger_entry->sales_amount_ori = $sales_amount_ori; 
                    $commission_ledger_entry->sales_amount_lcy = $sales_amount_lcy_ori; 
                    $commission_ledger_entry->sales_amount_lcy_ori = $sales_amount_lcy_ori; 
                }else {
                    $commission_ledger_entry->document_type = 'Invoice'; 
                    $commission_ledger_entry->document_no = $header->document_no; 
                    $commission_ledger_entry->currency_factor = 1; 
                    $commission_ledger_entry->amount = $commission_amount; 
                    $commission_ledger_entry->amount_lcy = $commission_amount_lcy; 
                    $commission_ledger_entry->remaining_amount = $commission_amount; 
                    $commission_ledger_entry->remaining_amount_lcy = $commission_amount_lcy; 
                    $commission_ledger_entry->sales_amount = $sales_amount; 
                    $commission_ledger_entry->sales_amount_ori = $sales_amount_ori; 
                    $commission_ledger_entry->sales_amount_lcy = $sales_amount_lcy; 
                    $commission_ledger_entry->sales_amount_lcy_ori = $sales_amount_lcy_ori; 
                }                
                $commission_ledger_entry->status = 'Open'; 
                $commission_ledger_entry->save();
                if($header->payment_method_code == 'PAID'){
                   
                    $journal_batch = GeneralJournalBatch::where('type','Prepayment Journal')->where('code', $user_setup->prepayment_journal_batch_name)->first();
                    if(!$journal_batch->no_series_code){
                        \DB::connection('company')->rollback();
                        return response()->json(['status' => 'warning','message' => trans('greetings.NoFoundSeriesCodeoItemJournalBatch')]);
                    }
                    $document_no = $this->service->generateNo($journal_batch->no_series_code, 'Prepayment Journal');
                    if($document_no == 'error_no_series'){
                        \DB::connection('company')->rollback();
                        return response()->json(['status' => 'warning','message' => trans('greetings.NoSeriesWasNotSetup')]);
                    }
                    $vendor_ledger_entry = new VendorLedgerEntry();
                    $vendor_ledger_entry->vendor_no = $vendor->no;
                    $vendor_ledger_entry->vendor_name = $vendor->name;
                    $vendor_ledger_entry->vendor_name_2 = $vendor->name_2;
                    $vendor_ledger_entry->posting_date = Carbon::now()->toDateString();
                    $vendor_ledger_entry->document_date = Carbon::now()->toDateString();
                    $vendor_ledger_entry->document_type = 'Prepayment';
                    $vendor_ledger_entry->document_no = $document_no;
                    $vendor_ledger_entry->description = 'Commission #['.$header->id.']';            
                    $vendor_ledger_entry->currency_code = null;
                    $vendor_ledger_entry->currency_factor = 1;
                    $vendor_ledger_entry->ap_posting_group = $vendor->ap_posting_group_code;
                    $vendor_ledger_entry->purchaser_code = $vendor->purchaser_code;
                    $vendor_ledger_entry->store_code = $vendor->store_code;
                    $vendor_ledger_entry->division_code = $vendor->division_code;
                    $vendor_ledger_entry->business_unit_code = $vendor->business_unit_code;
                    $vendor_ledger_entry->department_code = $vendor->department_code;
                    $vendor_ledger_entry->project_code = $vendor->project_code;
                    $vendor_ledger_entry->applies_to_doc_type = '';
                    $vendor_ledger_entry->applies_to_doc_no = '';
                    $vendor_ledger_entry->Applies_to_id = '';
                    $vendor_ledger_entry->journal_batch_name = '';
                    $vendor_ledger_entry->reason_code = '';
                    $vendor_ledger_entry->external_document_no = $header->id;
                    $vendor_ledger_entry->no_series = '';
                    $vendor_ledger_entry->amount_to_apply = 0;
                    $vendor_ledger_entry->reversed = 'No';
                    $vendor_ledger_entry->reversed_by_entry_no = 0;
                    $vendor_ledger_entry->reversed_entry_no = 0;
                    $vendor_ledger_entry->adjustment = 'No';
                    $vendor_ledger_entry->created_by = $user->email;                    
                    $vendor_ledger_entry->customer_no = $header->customer_no; 
                    $vendor_ledger_entry->customer_doc_type = 'eCommission';
                    $vendor_ledger_entry->customer_doc_no = $header->id;  
                    $vendor_ledger_entry->save();

                    $vendor_detail_ledger_entry = new VendorDetailLedgerEntry();                    
                    $vendor_detail_ledger_entry->vend_ledger_entry_no = $vendor_ledger_entry->entry_no;
                    $vendor_detail_ledger_entry->vendor_no = $vendor_ledger_entry->vendor_no;
                    $vendor_detail_ledger_entry->document_type = 'Prepayment';
                    $vendor_detail_ledger_entry->document_no = $vendor_ledger_entry->document_no;
                    $vendor_detail_ledger_entry->document_date = Carbon::now()->toDateString();
                    $vendor_detail_ledger_entry->posting_date = Carbon::now()->toDateString();
                    $vendor_detail_ledger_entry->description = 'Prepayment Commission #['.$header->id.']';                                
                    $vendor_detail_ledger_entry->currency_factor = 1;
                    $vendor_detail_ledger_entry->amount = $sales_amount_ori;
                    $vendor_detail_ledger_entry->amount_lcy = $sales_amount_lcy_ori;
                    $vendor_detail_ledger_entry->applied_vend_ledger_entry_no = 0;
                    $vendor_detail_ledger_entry->journal_batch_name = '';
                    $vendor_detail_ledger_entry->reason_code = '';
                    $vendor_detail_ledger_entry->debit_amount = $this->service->toDouble($vendor_detail_ledger_entry->amount);
                    $vendor_detail_ledger_entry->debit_amount_lcy = $this->service->toDouble($vendor_detail_ledger_entry->amount_lcy);
                    $vendor_detail_ledger_entry->ledger_entry_amount = 'Yes';
                    $vendor_detail_ledger_entry->created_by = $user->email;
                    $vendor_detail_ledger_entry->save();

                    // insert into G/L for bal account type (credit/debit)
                    if($is_gl_posting){
                        $general_ledger_entry_credit = new GeneralLedgerEntry();
                        $general_ledger_entry_credit->document_date = Carbon::now()->toDateString();
                        $general_ledger_entry_credit->posting_date = Carbon::now()->toDateString();
                        $general_ledger_entry_credit->document_type = 'Prepayment';
                        $general_ledger_entry_credit->document_no = $document_no;
                        $general_ledger_entry_credit->description = 'Prepayment Commission #['.$header->id.']';                                
                        $chart_of_account = ChartOfAccount::where('no', $ecom_setup->prepayment_account_no)->first();
                        if(!$chart_of_account){
                            \DB::connection('company')->rollback();
                            return response()->json(['status' => 'warning','message' => trans('greetings.eCommerce setup prepayment account no (equivalent) is not setup!')]);
                        }
                        $general_ledger_entry_credit->account_name = $chart_of_account->description;
                        $general_ledger_entry_credit->amount = $sales_amount_lcy_ori * -1;
                        $general_ledger_entry_credit->credit_amount = abs($sales_amount_lcy_ori);
                        $general_ledger_entry_credit->bal_account_type = 'G/L Account';
                        $general_ledger_entry_credit->bal_account_no = $ap_posting_group->prepayment_account_no;                        
                        $general_ledger_entry_credit->reversed = 'No';
                        $general_ledger_entry_credit->reversed_by_entry_no = 0;
                        $general_ledger_entry_credit->reversed_entry_no = 0;
                        $general_ledger_entry_credit->adjustment = 'No';
                        $general_ledger_entry_credit->system_created_entry = 'No';
                        $general_ledger_entry_credit->created_by = Auth::user()->email;
                        $general_ledger_entry_credit->save();

                        $general_ledger_entry_debit = new GeneralLedgerEntry();
                        $general_ledger_entry_debit->document_date = Carbon::now()->toDateString();
                        $general_ledger_entry_debit->posting_date = Carbon::now()->toDateString();
                        $general_ledger_entry_debit->document_type = 'Prepayment';
                        $general_ledger_entry_debit->document_no = $document_no;
                        $general_ledger_entry_debit->description = 'Prepayment Commission #['.$header->id.']';                                
                        $chart_of_account = ChartOfAccount::where('no', $ap_posting_group->prepayment_account_no)->first();
                        if(!$chart_of_account){                            
                            \DB::connection('company')->rollback();
                            return response()->json(['status' => 'warning','message' => trans('greetings.AP posting group prepayment account no is not setup!')]);
                        }
                        $general_ledger_entry_debit->account_name = $chart_of_account->description;
                        $general_ledger_entry_debit->amount = $sales_amount_lcy_ori;
                        $general_ledger_entry_debit->debit_amount = abs($sales_amount_lcy_ori);
                        $general_ledger_entry_debit->bal_account_type = 'G/L Account';
                        $general_ledger_entry_debit->bal_account_no = $ecom_setup->prepayment_account_no;
                        $general_ledger_entry_debit->reversed = 'No';
                        $general_ledger_entry_debit->reversed_by_entry_no = 0;
                        $general_ledger_entry_debit->reversed_entry_no = 0;
                        $general_ledger_entry_debit->adjustment = 'No';
                        $general_ledger_entry_debit->system_created_entry = 'No';
                        $general_ledger_entry_debit->created_by = Auth::user()->email;
                        $general_ledger_entry_debit->save();
                    }                    
                }else {
                    $ref_no_series = NoSeries::where('code', $user_setup->pi_no_series)->first();
                    if(!$ref_no_series){                        
                        \DB::connection('company')->rollback();
                        return response()->json(['status'=>'warning', 'message'=> trans('greetings.You cannot create commission ledger entry, while user purchase invoice no. series not yet setup!')]);
                    }
                    $document_no = $this->service->generateNo($ref_no_series->code, 'Purchase Invoice');
                    if ($document_no == 'error_no_series') {                        
                        \DB::connection('company')->rollback();
                        return response()->json(['status'=>'warning', 'message'=> trans('greetings.You cannot create commission ledger entry, while user purchase invoice no. series not yet setup!')]);
                    }
                    $purchase_header = new PurchaseHeader();
                    $purchase_header->no = $document_no;                
                    $purchase_header->document_type = 'Invoice';
                    $purchase_header->posting_description = 'Commission #['.$header->id.']';            
                    $purchase_header->no_series = $ref_no_series->code;
                    $purchase_header->order_date = Carbon::now()->toDateString();
                    $purchase_header->document_date = Carbon::now()->toDateString();
                    $purchase_header->posting_date = Carbon::now()->toDateString();
                    $purchase_header->request_receipt_date = Carbon::now()->toDateString();
                    $purchase_header->location_code = $user->user_setup->location_code;
                    $purchase_header->business_unit_code = $user->user_setup->business_unit_code;
                    $purchase_header->division_code = $user->user_setup->division_code;
                    $purchase_header->store_code = $user->user_setup->store_code;
                    $purchase_header->project_code = $user->user_setup->project_code;
                    $purchase_header->purchaser_code = $user->user_setup->salesperson_code;
                    $purchase_header->first_approver_code = $user->user_setup->send_to_approver_code;
                    $purchase_header->second_approver_code = $user->user_setup->substitute_approver_code;
                    $purchase_header->assign_to_userid = $user->id;
                    $purchase_header->assign_to_username = $user->email;                
                    $purchase_header->created_by = $user->email;                
                    $purchase_header->vendor_no = $vendor->no;
                    $purchase_header->vendor_name = $vendor->name;
                    $purchase_header->vendor_name_2 = $vendor->name_2;
                    $purchase_header->address = $vendor->address;
                    $purchase_header->address_2 = $vendor->address_2;
                    $purchase_header->post_code = $vendor->post_code;
                    $purchase_header->village = $vendor->village;
                    $purchase_header->commune = $vendor->commune;
                    $purchase_header->district = $vendor->district;
                    $purchase_header->province = $vendor->province;
                    $purchase_header->country_code = $vendor->country_code;
                    $purchase_header->contact_name = $vendor->contact_name;
                    if ($vendor->store_code) $purchase_header->store_code = $vendor->store_code;
                    if ($vendor->division_code) $purchase_header->division_code = $vendor->division_code;
                    if ($vendor->business_unit_code) $purchase_header->business_unit_code = $vendor->business_unit_code;
                    if ($vendor->department_code) $purchase_header->department_code = $vendor->department_code;
                    if ($vendor->project_code) $purchase_header->project_code = $vendor->project_code;
                    if ($vendor->purchaser_code) $purchase_header->purchaser_code = $vendor->purchaser_code;
                    $purchase_header->ap_posting_group_code = $vendor->ap_posting_group_code;
                    $purchase_header->gen_bus_posting_group_code = $vendor->gen_bus_posting_group_code;
                    $purchase_header->vat_bus_posting_group_code = $vendor->vat_bus_posting_group_code;
                    $purchase_header->payment_term_code = $vendor->payment_term_code;
                    $purchase_header->price_include_vat = $vendor->price_include_vat;
                    $purchase_header->location_code = ($vendor->location_code) ? $vendor->location_code : $user_setup->location_code;
                    $purchase_header->currency_code = $vendor->currency_code;
                    $purchase_header->currency_factor = 1;
                    $location = Location::where('code',$purchase_header->location_code)->first();
                    if ($location) {                
                        $purchase_header->ship_to_name = $location->description;
                        $purchase_header->ship_to_name_2 = $location->description_2;
                        $purchase_header->ship_to_address = $location->address;
                        $purchase_header->ship_to_address_2 = $location->address_2;
                        $purchase_header->ship_to_post_code = $location->post_code;
                        $purchase_header->ship_to_village = $location->village;
                        $purchase_header->ship_to_commune = $location->commune;
                        $purchase_header->ship_to_district = $location->district;
                        $purchase_header->ship_to_province = $location->province;
                        $purchase_header->ship_to_country_code = $location->country_code;
                        $purchase_header->ship_to_contact_name = $location->contact_name;
                        $purchase_header->ship_to_phone_no = $location->phone_no;
                        $purchase_header->ship_to_phone_no_2 = $location->phone_no_2;
                    }
                    $purchase_header->customer_no = $header->customer_no; 
                    $purchase_header->customer_doc_type = 'eCommission';  
                    $purchase_header->customer_doc_no = $header->id; 
                    $purchase_header->external_document_no = $header->id; 
                    $purchase_header->status = 'Approved';  
                    $purchase_header->save();
                    // STORE COMMISSION LINE
                    $commission_account = ChartOfAccount::where('no', $ecom_setup->commission_account_no)->first(); 
                    if(!$commission_account){                        
                        \DB::connection('company')->rollback();
                        return response()->json(['status'=>'warning', 'message'=> trans('greetings.You cannot create commission ledger entry, while commission G/L account no not yet setup!')]);
                    }
                    $purchase_line = new PurchaseLine();
                    $purchase_line->document_type = $purchase_header->document_type;
                    $purchase_line->document_no = $purchase_header->no;
                    $purchase_line->vendor_no = $purchase_header->vendor_no;
                    $purchase_line->location_code = $purchase_header->location_code;
                    $purchase_line->currency_factor = 1;
                    $purchase_line->store_code = $purchase_header->store_code;
                    $purchase_line->division_code = $purchase_header->division_code;
                    $purchase_line->business_unit_code = $purchase_header->business_unit_code;
                    $purchase_line->department_code = $purchase_header->department_code;
                    $purchase_line->project_code = $purchase_header->project_code;
                    $purchase_line->purchaser_code = $purchase_header->purchaser_code;
                    $purchase_line->request_receipt_date = $purchase_header->request_receipt_date;
                    $purchase_line->line_no = 10000;
                    $purchase_line->refer_line_no = 10000;
                    $purchase_line->no = $commission_account->no;
                    $purchase_line->description = $commission_account->description;
                    $purchase_line->description_2 = $commission_account->description_2;
                    $purchase_line->vat_prod_posting_group_code = $commission_account->vat_prod_posting_group_code;
                    $purchase_line->gen_prod_posting_group_code = $commission_account->gen_prod_posting_group_code;                
                    $purchase_line->vat_bus_posting_group_code = $purchase_header->vat_bus_posting_group_code;
                    $purchase_line->gen_bus_posting_group_code = $purchase_header->gen_bus_posting_group_code;
                    $purchase_line->type = 'G/L Account';
                    $purchase_line->created_by = Auth::user()->email;                
                    $purchase_line->division_code = $purchase_header->division_code;
                    $purchase_line->business_unit_code = $purchase_header->business_unit_code;
                    $purchase_line->department_code = $purchase_header->department_code;
                    $purchase_line->project_code = $purchase_header->project_code;
                    $purchase_line->unit_of_measure = 'UNIT';
                    $purchase_line->qty_per_unit_of_measure = 1;
                    $purchase_line->quantity = 1;
                    $purchase_line->direct_unit_cost = $commission_amount;
                    $purchase_line->CalculatePurchaseAmount();
                    $purchase_line->save();
                    // POST
                    $result = $this->purch_service->postInvoice($purchase_header, Auth::user(), 'No', null, null); 
                    if ($result === 'error_no_series') {                        
                        \DB::connection('company')->rollback();
                        return response()->json(['status'=>'warning', 'message'=> trans('greetings.You cannot create commission ledger entry, while user purchase invoice no. series not yet setup!')]);
                    } elseif ($result[0] === 'success') {
                    } else {
                        return response()->json(['status' => 'not_found', 'msg' =>  $result, 'err' => $result]);
                    }                                       
                } 
                if($header->free_delivery == 'Yes' && service::toDouble($header->delivery_fee) > 0){
                    $journal_batch = GeneralJournalBatch::where('type','Prepayment Journal')->where('code', $user_setup->prepayment_journal_batch_name)->first();
                    if(!$journal_batch->no_series_code){
                        \DB::connection('company')->rollback();
                        return response()->json(['status' => 'warning','message' => trans('greetings.NoFoundSeriesCodeoItemJournalBatch')]);
                    }
                    $document_no = $this->service->generateNo($journal_batch->no_series_code, 'Prepayment Journal');
                    if($document_no == 'error_no_series'){
                        \DB::connection('company')->rollback();
                        return response()->json(['status' => 'warning','message' => trans('greetings.NoSeriesWasNotSetup')]);
                    }
                    $vendor_ledger_entry = new VendorLedgerEntry();
                    $vendor_ledger_entry->vendor_no = $vendor->no;
                    $vendor_ledger_entry->vendor_name = $vendor->name;
                    $vendor_ledger_entry->vendor_name_2 = $vendor->name_2;
                    $vendor_ledger_entry->posting_date = Carbon::now()->toDateString();
                    $vendor_ledger_entry->document_date = Carbon::now()->toDateString();
                    $vendor_ledger_entry->document_type = 'Prepayment';
                    $vendor_ledger_entry->document_no = $document_no;
                    $vendor_ledger_entry->description = 'Commission #['.$header->id.']';            
                    $vendor_ledger_entry->currency_code = null;
                    $vendor_ledger_entry->currency_factor = 1;
                    $vendor_ledger_entry->ap_posting_group = $vendor->ap_posting_group_code;
                    $vendor_ledger_entry->purchaser_code = $vendor->purchaser_code;
                    $vendor_ledger_entry->store_code = $vendor->store_code;
                    $vendor_ledger_entry->division_code = $vendor->division_code;
                    $vendor_ledger_entry->business_unit_code = $vendor->business_unit_code;
                    $vendor_ledger_entry->department_code = $vendor->department_code;
                    $vendor_ledger_entry->project_code = $vendor->project_code;
                    $vendor_ledger_entry->applies_to_doc_type = '';
                    $vendor_ledger_entry->applies_to_doc_no = '';
                    $vendor_ledger_entry->Applies_to_id = '';
                    $vendor_ledger_entry->journal_batch_name = '';
                    $vendor_ledger_entry->reason_code = '';
                    $vendor_ledger_entry->external_document_no = $header->id;
                    $vendor_ledger_entry->no_series = '';
                    $vendor_ledger_entry->amount_to_apply = 0;
                    $vendor_ledger_entry->reversed = 'No';
                    $vendor_ledger_entry->reversed_by_entry_no = 0;
                    $vendor_ledger_entry->reversed_entry_no = 0;
                    $vendor_ledger_entry->adjustment = 'No';
                    $vendor_ledger_entry->created_by = $user->email;                    
                    $vendor_ledger_entry->customer_no = $header->customer_no; 
                    $vendor_ledger_entry->customer_doc_type = 'eCommission';
                    $vendor_ledger_entry->customer_doc_no = $header->id;  
                    $vendor_ledger_entry->save();

                    $vendor_detail_ledger_entry = new VendorDetailLedgerEntry();                    
                    $vendor_detail_ledger_entry->vend_ledger_entry_no = $vendor_ledger_entry->entry_no;
                    $vendor_detail_ledger_entry->vendor_no = $vendor_ledger_entry->vendor_no;
                    $vendor_detail_ledger_entry->document_type = 'Prepayment';
                    $vendor_detail_ledger_entry->document_no = $vendor_ledger_entry->document_no;
                    $vendor_detail_ledger_entry->document_date = Carbon::now()->toDateString();
                    $vendor_detail_ledger_entry->posting_date = Carbon::now()->toDateString();
                    $vendor_detail_ledger_entry->description = 'Prepayment Commission #['.$header->id.']';                                
                    $vendor_detail_ledger_entry->currency_factor = 1;
                    $vendor_detail_ledger_entry->amount = service::toDouble($header->delivery_fee);
                    $vendor_detail_ledger_entry->amount_lcy = service::toDouble($header->delivery_fee);
                    $vendor_detail_ledger_entry->applied_vend_ledger_entry_no = 0;
                    $vendor_detail_ledger_entry->journal_batch_name = '';
                    $vendor_detail_ledger_entry->reason_code = '';
                    $vendor_detail_ledger_entry->debit_amount = $this->service->toDouble($vendor_detail_ledger_entry->amount);
                    $vendor_detail_ledger_entry->debit_amount_lcy = $this->service->toDouble($vendor_detail_ledger_entry->amount_lcy);
                    $vendor_detail_ledger_entry->ledger_entry_amount = 'Yes';
                    $vendor_detail_ledger_entry->created_by = $user->email;
                    $vendor_detail_ledger_entry->save();

                    // insert into G/L for bal account type (credit/debit)
                    if($is_gl_posting){
                        $general_ledger_entry_credit = new GeneralLedgerEntry();
                        $general_ledger_entry_credit->document_date = Carbon::now()->toDateString();
                        $general_ledger_entry_credit->posting_date = Carbon::now()->toDateString();
                        $general_ledger_entry_credit->document_type = 'Prepayment';
                        $general_ledger_entry_credit->document_no = $document_no;
                        $general_ledger_entry_credit->description = 'Prepayment Commission #['.$header->id.']';                                
                        $chart_of_account = ChartOfAccount::where('no', $ecom_setup->prepayment_account_no)->first();
                        if(!$chart_of_account){
                            \DB::connection('company')->rollback();
                            return response()->json(['status' => 'warning','message' => trans('greetings.eCommerce setup prepayment account no (equivalent) is not setup!')]);
                        }
                        $general_ledger_entry_credit->account_name = $chart_of_account->description;
                        $general_ledger_entry_credit->amount = service::toDouble($header->delivery_fee) * -1;
                        $general_ledger_entry_credit->credit_amount = abs(service::toDouble($header->delivery_fee));
                        $general_ledger_entry_credit->bal_account_type = 'G/L Account';
                        $general_ledger_entry_credit->bal_account_no = $ap_posting_group->prepayment_account_no;                        
                        $general_ledger_entry_credit->reversed = 'No';
                        $general_ledger_entry_credit->reversed_by_entry_no = 0;
                        $general_ledger_entry_credit->reversed_entry_no = 0;
                        $general_ledger_entry_credit->adjustment = 'No';
                        $general_ledger_entry_credit->system_created_entry = 'No';
                        $general_ledger_entry_credit->created_by = Auth::user()->email;
                        $general_ledger_entry_credit->save();

                        $general_ledger_entry_debit = new GeneralLedgerEntry();
                        $general_ledger_entry_debit->document_date = Carbon::now()->toDateString();
                        $general_ledger_entry_debit->posting_date = Carbon::now()->toDateString();
                        $general_ledger_entry_debit->document_type = 'Prepayment';
                        $general_ledger_entry_debit->document_no = $document_no;
                        $general_ledger_entry_debit->description = 'Prepayment Commission #['.$header->id.']';                                
                        $chart_of_account = ChartOfAccount::where('no', $ap_posting_group->prepayment_account_no)->first();
                        if(!$chart_of_account){                            
                            \DB::connection('company')->rollback();
                            return response()->json(['status' => 'warning','message' => trans('greetings.AP posting group prepayment account no is not setup!')]);
                        }
                        $general_ledger_entry_debit->account_name = $chart_of_account->description;
                        $general_ledger_entry_debit->amount = service::toDouble($header->delivery_fee);
                        $general_ledger_entry_debit->debit_amount = abs(service::toDouble($header->delivery_fee));
                        $general_ledger_entry_debit->bal_account_type = 'G/L Account';
                        $general_ledger_entry_debit->bal_account_no = $ecom_setup->prepayment_account_no;
                        $general_ledger_entry_debit->reversed = 'No';
                        $general_ledger_entry_debit->reversed_by_entry_no = 0;
                        $general_ledger_entry_debit->reversed_entry_no = 0;
                        $general_ledger_entry_debit->adjustment = 'No';
                        $general_ledger_entry_debit->system_created_entry = 'No';
                        $general_ledger_entry_debit->created_by = Auth::user()->email;
                        $general_ledger_entry_debit->save();
                    }
                }           
            }    
            
            //==== update delivery header ==== 
            $delivery_header->status = 'Delivered'; 
            $delivery_header->save(); 
            
            if($ecom_setup->is_post_on_delivered == 'Yes'){
                $sales_header = SaleHeader::where('no', $header->erp_document_no)->first();                
                if($sales_header){
                    $sales_header->payment_method_code = $header->payment_method_code; 
                    if($sales_header->status == 'Open' || $sales_header->status == 'Approved'){
                        $result = null;
                        if($sales_header->document_type == 'Order'){
                            $result = $this->sales_service->postSOShipInvoice($sales_header, null, 'No', 'No', 'No');      
                        }elseif($sales_header->document_type == 'Invoice') {
                            if(service::toDouble($amount) != 0)  $sales_header->payment_amount = $this->service->toDouble($amount); 
                            $result = $this->sales_service->postInvoice($sales_header, $user, null, 'No', 'No', 'No', null);     
                        }
                        if($result[0] == 'success'){
                        }else {
                            \DB::connection('company')->rollback();
                            return response()->json(['status' => 'warning', 'message' => $result]);
                        }
                    }                    
                }                
            }  
                       
            \DB::connection('company')->commit();
            //===== Ecommerce Users =====//
            
            $provider = $this->getProvider(Auth::user()->account_id); 
            $api_sessions_ecommerce_users = \DB::connection('mysql')->table('api_sessions')
                    ->join('users','users.id','api_sessions.user_id')
                    ->selectRaw('api_sessions.firebase_client_key,api_sessions.app_id,api_sessions.user_id,users.locale')
                    ->where('api_sessions.user_id', $header['customer_no'])
                    ->where('api_sessions.app_id',$provider)
                    ->where('api_sessions.firebase_client_key', '<>','')
                    ->orderBy('api_sessions.id','DESC')
                    ->get();
            
            $noti_sessions = $api_sessions_ecommerce_users->unique("user_id");            
            if(count($noti_sessions) > 0){
                foreach($noti_sessions as $api_session){
                    $sessionToken = openssl_random_pseudo_bytes(20);
                    $sessionToken = bin2hex($sessionToken);
                    $notification = new MyNotification();
                    $notification->id = $sessionToken;
                    $notification->type = 'App';
                    $notification->notifiable_id = $api_session->user_id;
                    $notification->notifiable_type = 'App\User';
                    $notification->description = 'Order Delivered';
                    $notification->description = 'The delivery order no #' . $header->document_no . ' has been delivered!';
                    $notification->description_2 = 'ការបញ្ជាទិញដឹកជញ្ជូនលេខ #'.$header->document_no.' ត្រូវបានបញ្ចប់ការដឹក!'; 
                    $notification->entry_date = Carbon::now()->toDateString();
                    $notification->entry_datetime = Carbon::now();
                    $notification->document_type = 'EOrder';
                    $notification->document_no = $header->document_no;
                    $notification->app_id = $api_session->app_id; 
                    $notification->data = json_encode([
                        'sender_id' => Auth::user()->id,
                        'header' => $header,
                    ]);
                    $notification->save();
                    if($api_session->locale == 'kh') array_push($array_user_ecoomer_kh,$api_session->firebase_client_key);
                    else array_push($array_user_ecoomer_en,$api_session->firebase_client_key);
                }
            }   

            $header->title_kh = 'ការបញ្ជាទិញត្រូវបានដឹកជញ្ជូន'; 
            $header->body_kh = 'ការបញ្ជាទិញដឹកជញ្ជូនលេខ #'.$header->document_no.' ត្រូវបានបញ្ចប់ការដឹក!'; 
            $header->title_en = 'Order Delivered'; 
            $header->body_en = 'The delivery order no #' . $header->document_no . ' has been delivered!';
            
            $this->sendNotificationSpacificUserLangaugeApp($header,$array_user_ecoomer_en,$array_user_ecoomer_kh);
            $this->removeFirebaseDeliveryOrder($header, $delivery_header); 
            
            if(isset($request['action_from']) && $request['action_from'] == 'ecommerce_order'){
                $record = $header;
                $view = view('sales.history.ecommerce_order_records', compact('record'))->render();
            }else{
                $order = DB::connection('company')->table('esales_header')->join('esales_line', 'esales_header.id', 'esales_line.document_no')
                    ->where('esales_header.org_id', Auth::user()->account_id)
                    ->where('esales_header.id',$id)
                    ->whereRaw("(esales_header.erp_status = 'New' or esales_header.status <> 'Delivered')")
                    ->selectRaw('esales_header.id, esales_header.document_no,esales_header.delivery_fee, esales_header.tracking_no,esales_header.currency_code, esales_header.customer_no, esales_header.ship_to_name, esales_header.ship_to_address, sum(esales_line.amount_including_vat_lcy) as amount_including_vat_lcy, esales_header.status,esales_header.ship_to_phone_no,esales_header.payment_method_code,esales_header.erp_status, esales_header.order_datetime')
                    ->groupBy('esales_header.document_no','esales_header.id', 'esales_header.tracking_no','esales_header.delivery_fee','esales_header.currency_code', 'esales_header.customer_no', 'esales_header.ship_to_name', 'esales_header.ship_to_address', 'esales_header.status', 'esales_header.ship_to_phone_no', 'esales_header.payment_method_code', 'esales_header.erp_status', 'esales_header.order_datetime')
                    ->orderBy('esales_header.order_datetime', 'desc')->first(); 
                $view = view('dashboard.activites.activity.wholesaler_ordering_records', compact('order','ecom_setup'))->render(); 
            }
            
            return response()->json([
                'status' => 'success',
                'message' => 'This order has been delivered!', 
                'id' => $header->id,
                'view' => $view, 
            ], 200);
                                    
       }catch(\Exception $ex){            
            \DB::connection('company')->rollback();
            $this->service->saveErrorLog($ex);
            return response()->json(['status' => 'failed', "message" => "token updated failed!" . $ex->getMessage()]);
        }  
    }
    public function getSettlement(Request $request){
        try{
            $id = $this->service->decrypt($request->code); 
            $header = EComSalesHeader::where('id', $id)->where('org_id', Auth::user()->account_id)->first();
            if(!$header) return response()->json(['status'=>'warning', 'message'=>trans('greetings.Order is not found!')]);
            $total_amount = EComSalesLine::where('document_no', $header->id)->where('document_type',$header->document_type)->sum('amount'); 
            $locations = Location::where('inactived', '<>', 'Yes')->get(); 
            $ecom_setup = eCommerceSetup::first();
            $payment_methods = PaymentMethod::where('inactived', '<>', 'Yes')->get(); 
            $view = view('system.modal_settlement_card', compact('payment_methods' , 'header', 'total_amount', 'ecom_setup', 'locations' ))->render(); 
            return response()->json(['status' => 'success', 'view' => $view ]);

        }catch(\Exception $ex){
            return response()->json(['status' => 'failed', "message" => "token updated failed!" . $ex->getMessage()]);
        } 
    }
    public function Settlement(Request $request){
        try {
            
            $data = $request->all();
            $auto_post = isset($data['auto_post']) ? 'Yes' : 'No';
            $auto_assign_lot_no = isset($data['auto_assign_lot_no']) ? 'Yes' : 'No';
            $document_type = isset($data['document_type']) ? $data['document_type'] : 'Invoice';
            $location_code = isset($data['location_code']) ? $data['location_code'] : 'MAIN';
            $customer_no = isset($data['customer_no']) ? $data['customer_no'] : 'GENERAL';
            $posting_date = $data['posting_date'];
            $payment_amount = $data['payment_amount'];
            
            $id = $this->service->decrypt($data['code']); 

            $app_setup = Auth::user()->app_setup;
            $user_setup = Auth::user()->user_setup;
            $ecom_setup = eCommerceSetup::first();
            if(!$ecom_setup) {
                $ecom_setup = new eCommerceSetup(); 
                $ecom_setup->document_type = $document_type;
                $ecom_setup->location_code = $location_code;
                $ecom_setup->customer_no = $customer_no;
                $ecom_setup->auto_post = $auto_post;
                $ecom_setup->auto_assign_lot_no = $auto_assign_lot_no;
                $ecom_setup->save(); 
            }
            $auto_post = $ecom_setup->auto_post;
            $ecom_header = EComSalesHeader::where('id', $id)->where('org_id', Auth::user()->account_id)->first();
            if(!$ecom_header) return response()->json(['status'=>'warning', 'message'=>trans('greetings.Order is not found!')]);
            $customer = Customer::where('no', $customer_no)->first(); 
            if(!$customer) return response()->json(['status'=>'warning', 'message'=>trans('greetings.Customer is not found!')]);
            $ecom_delivery_header = EComDeliveryHeader::where('tracking_no', $ecom_header->tracking_no)->where('org_id', Auth::user()->account_id)->first() ;
            $ecom_lines = EComSalesLine::where('document_no', $ecom_header->id)->where('document_type',$ecom_header->document_type)->orderby('line_no')->get(); 

            //===== create sales header ======== 
            \DB::connection('company')->beginTransaction();
            $no_series = $this->service->generateNo($ecom_setup->so_no_series,'Sales Invoice');
            if($no_series == 'error_no_series'){
                \DB::connection('company')->rollback();
                return response()->json(['response_code' => '404', 'response_msg' => 'Sales invoice number series was not setup!'], 200);
            }
            $header = new SaleHeader(); 
            $header->no = $no_series; 
            $header->no_series = $ecom_setup->so_no_series; 
            $header->document_type = $document_type;             
            $header->customer_no = $customer->no; 
            $header->customer_name = $customer->name; 
            $header->customer_name_2 = $customer->name_2; 
            $header->address = $customer->address; 
            $header->address_2 = $customer->address_2; 
            $header->post_code = $customer->post_code; 
            $header->village = $customer->village; 
            $header->commune = $customer->commune; 
            $header->district = $customer->district; 
            $header->province = $customer->province; 
            $header->country_code = $customer->country_code; 
            $header->contact_name = $customer->contact_name; 
            $header->location_code = $ecom_setup->location_code;  
            $header->ship_to_code = $customer->ship_to_code; 
            $header->ship_to_name = $ecom_header->ship_to_name ; 
            $header->ship_to_name_2 = $ecom_header->ship_to_name_2 ; 
            $header->ship_to_address = $ecom_header->ship_to_address ; 
            $header->ship_to_address_2 = $ecom_header->ship_to_address_2 ; 
            $header->ship_to_post_code = $ecom_header->ship_to_post_code ; 
            $header->ship_to_village = $ecom_header->ship_to_village ; 
            $header->ship_to_district = $ecom_header->ship_to_district ; 
            $header->ship_to_province = $ecom_header->ship_to_province ; 
            $header->ship_to_country_code = $ecom_header->ship_to_country_code ; 
            $header->ship_to_contact_name = $ecom_header->ship_to_contact_name ; 
            $header->ship_to_phone_no = $ecom_header->ship_to_phone_no ; 
            $header->document_date = Carbon::parse($ecom_header->document_date)->toDateString(); 
            $header->order_date = Carbon::parse($ecom_header->order_date)->toDateString(); 
            $header->posting_date = Carbon::parse($posting_date)->toDateString();  
            $header->order_datetime = Carbon::parse($ecom_header->order_datetime)->toDateTimeString(); 
            $header->request_shipment_date = Carbon::parse($ecom_header->request_shipment_date)->toDateString(); 
            $header->posting_description = 'eCommerce sales order no #'. $ecom_header->document_no; 
            $header->payment_term_code = $customer->payment_term_code; 
            $header->payment_method_code = $data['payment_method']; 
            $header->payment_amount = $this->service->number_formattor_database($payment_amount, 'amount'); 
            $header->shipment_method_code = $customer->shipment_method_code; 
            $header->shipment_agent_code = $customer->shipment_agent_code; 
            $header->ar_posting_group_code =  $customer->rec_posting_group_code;
            $header->gen_bus_posting_group_code = $customer->gen_bus_posting_group_code;
            $header->vat_bus_posting_group_code =  $customer->vat_posting_group_code;
            $header->currency_code = null; 
            $header->currency_factor = 1; 
            $header->salesperson_code = $customer->salesperson_code; 
            $header->distributor_code = $customer->distributor_code; 
            $header->store_code = $customer->store_code; 
            $header->division_code = $customer->division_code; 
            $header->business_unit_code = $customer->business_unit_code; 
            $header->department_code = $customer->department_code; 
            $header->project_code = $customer->project_code; 
            $header->customer_group_code = $customer->customer_group_code; 
            $header->external_document_no = $ecom_header->document_no;             
            $header->price_include_vat =  'Yes';            
            $header->status =  'Approved';            
            $header->source_type =  'eCommerce';            
            $header->source_no =  $ecom_header->document_no;
            $header->created_by = Auth::user()->email; 
            $header->save(); 

            //========= store sales line ========== 
            foreach($ecom_lines as  $ecom_line){
                $lines = SaleLine::select('line_no')->where('document_no',$header->no)->max('line_no');
                $item = Item::where('no', $ecom_line->no)->first();                 
                if(!$item){ 
                    $item = new Item();
                    $item->no = $ecom_line->no;
                    $item->identifier_code = null;
                    $item->description = $ecom_line->description.'(Item Removed)';
                    $item->description_2 = $ecom_line->description_2.'(Item Removed)';
                    $item->is_service_item = 'No';
                    $item->prevent_negative_inventory = 'yes';
                    $item->costing_method = 'Average';
                    $item->unit_price = $this->service->toDouble($ecom_line->unit_price_lcy);
                    $item->is_adjustment_cost = 'Yes';
                    $item->standard_cost = 0;
                    $item->unit_cost = 0;
                    $item->last_direct_cost = 0;
                    $item->profit_calculation = 'Profit=Price-Cost';
                    $item->profit_percentage = 0;
                    $item->replenishment_system = 'Purchase';
                    $item->flushing_method = 'Manual';
                    $item->manufacturing_policy = 'Make-to-Stock';
                    $item->assembly_policy = 'Assemble-to-Stock';
                    $item->reordering_policy = 'Fixed Reorder Qty.';
                    $item->dampener_quantity = 0;
                    $item->safety_stock_quantity = 0;
                    $item->reorder_quantity = 0;
                    $item->maximum_inventory = 0;
                    $item->minimum_order_quantity = 0;
                    $item->maximum_order_quantity = 0;
                    $item->net_weight = 0;
                    $item->gross_weight = 0;
                    $item->inv_posting_group_code = $app_setup->default_inv_posting_group;
                    $item->gen_prod_posting_group_code = $app_setup->default_gen_prod_posting_group;
                    $item->vat_prod_posting_group_code = $app_setup->default_vat_prod_posting_group;
                    $item->stock_uom_code = $app_setup->default_stock_unit_measure;
                    $item->sales_uom_code = $app_setup->default_stock_unit_measure;
                    $item->purchase_uom_code = $app_setup->default_stock_unit_measure;
                    $item->save();

                    $item_uom_stock = ItemUnitOfMeasure::where('item_no',$item->no)->where('unit_of_measure_code',$item->stock_uom_code)->first();
                    if(!$item_uom_stock) {
                        $item_uom_stock = new ItemUnitOfMeasure();
                        $item_uom_stock->item_no = $item->no;
                        $item_uom_stock->identifier_code = null;
                        $item_uom_stock->unit_of_measure_code = $item->stock_uom_code;
                        $item_uom_stock->qty_per_unit = 1;
                        $item_uom_stock->price = 0;                                
                        $item_uom_stock->save();
                    }

                    $sell_unit_of_measure = trim($ecom_line->unit_of_measure, ' ');
                    if($item->stock_uom_code != $sell_unit_of_measure && $sell_unit_of_measure != null && $sell_unit_of_measure != ''){
                        $item_uom_sales = ItemUnitOfMeasure::where('item_no',$item->no)->where('unit_of_measure_code',$sell_unit_of_measure)->first();
                        if(!$item_uom_sales) {
                            $item_uom_sales = new ItemUnitOfMeasure();
                            $item_uom_sales->item_no = $item->no;
                            $item_uom_sales->identifier_code = null;
                            $item_uom_sales->unit_of_measure_code = $sell_unit_of_measure;
                            $item_uom_sales->qty_per_unit = $ecom_line->qty_per_unit_of_measure;
                            $item_uom_sales->price = $this->service->toDouble($ecom_line->unit_price_lcy);  
                            $item_uom_sales->save(); 
                        }
                    }                            
                    
                }
                $item_unit_of_measure = ItemUnitOfMeasure::where('unit_of_measure_code', $ecom_line->unit_of_measure)->where('item_no', $ecom_line->no)->first(); 
                $line_no = ($lines) ? $lines : 0;
                $line_no = $line_no + 10000;

                $sales_line = new SaleLine();
                $sales_line->document_type = $header->document_type;
                $sales_line->document_no = $header->no;
                $sales_line->line_no = $line_no;
                $sales_line->refer_line_no = $line_no;
                $sales_line->customer_no = $header->customer_no;
                $sales_line->type = 'Item';
                $sales_line->no = $ecom_line->no;
                $sales_line->variant_code = $ecom_line->variant_code;
                $sales_line->location_code = $header->location_code;
                $sales_line->posting_group = $item->inv_posting_group_code;
                $sales_line->description = $item->description;
                $sales_line->description_2 = $item->description_2;
                $sales_line->gen_prod_posting_group_code = $item->gen_prod_posting_group_code;
                $sales_line->vat_prod_posting_group_code = $item->vat_prod_posting_group_code;
                $sales_line->vat_percentage = 0; 
                $sales_line->vat_calculation_type = 'VAT Before Disc.';
                if($customer->vat_posting_group_code && $item->vat_prod_posting_group_code){
                    $vat_post_group = VatPostingSetup::select('vat_calculation_type','vat_amount')
                                ->where('vat_bus_posting_group',$customer->vat_posting_group_code)
                                ->where('vat_prod_posting_group',$item->vat_prod_posting_group_code)->first();
                    if($vat_post_group){
                        $sales_line->vat_calculation_type = $vat_post_group->vat_calculation_type;
                        $sales_line->vat_percentage = $this->service->toDouble($vat_post_group->vat_percentage);
                    }
                }
                $sales_line->item_category_code = $item->item_category_code;
                $sales_line->item_group_code = $item->item_group_code;
                $sales_line->item_disc_group_code = $item->item_disc_group_code;
                $sales_line->item_brand_code = $item->item_brand_code; 
                $sales_line->gen_bus_posting_group_code = $customer->gen_bus_posting_group_code;
                $sales_line->vat_bus_posting_group_code = $customer->vat_posting_group_code;
                $sales_line->unit_of_measure = $ecom_line->unit_of_measure;
                // SELECT QTY PER UNIT FROM ITEM UNIT OF MEASURE 
                $sales_line->qty_per_unit_of_measure = ($item_unit_of_measure) ? $this->service->number_formattor_database($item_unit_of_measure->qty_per_unit, 'amount') : 1;                
                $sales_line->quantity = $this->service->toDouble($ecom_line->quantity);
                $sales_line->outstanding_quantity = $this->service->toDouble($ecom_line->quantity);
                $sales_line->outstanding_quantity_base = $this->service->toDouble($ecom_line->quantity) * $this->service->toDouble($ecom_line->qty_per_unit_of_measure);
                $sales_line->quantity_to_ship = $this->service->toDouble($ecom_line->quantity);
                $sales_line->quantity_to_invoice = $this->service->toDouble($ecom_line->quantity);
                $sales_line->unit_price = $this->service->toDouble($ecom_line->unit_price);
                $sales_line->unit_price_lcy = $this->service->toDouble($ecom_line->unit_price_lcy);                
                $sales_line->discount_percentage = $this->service->toDouble($ecom_line->discount_percentage);
                $sales_line->discount_amount = $this->service->toDouble($ecom_line->discount_amount);
                $sales_line->currency_code = null;
                $sales_line->currency_factor = 1;
                $sales_line->CalculateAmount();                       
                $sales_line->customer_group_code = $header->customer_group_code;                        
                $sales_line->store_code = $header->store_code;
                $sales_line->division_code = $header->division_code;
                $sales_line->business_unit_code = $header->business_unit_code;
                $sales_line->department_code = $header->department_code;
                $sales_line->project_code = $header->project_code;
                $sales_line->distributor_code = $header->distributor_code;
                $sales_line->salesperson_code = $ecom_line->salesperson_code;
                $sales_line->created_by = Auth::user()->email;
                $sales_line->save();
                // ========== Item Tracking LOT =========== 
                if($item->item_tracking_code == 'LOTALL' && $ecom_setup->auto_assign_lot_no == 'Yes'){
                    $total_qty_to_handle = $this->service->toDouble($sales_line->quantity) * $this->service->toDouble($sales_line->qty_per_unit_of_measure);
                    $item_ledgers = ItemLedgerEntry::where('remaining_quantity','>',0)
                                    ->where('item_no',$item->no)->where('location_code',$sales_line->location_code);
                    if($sales_line->variant_code){
                        $item_ledgers = $item_ledgers->where('variant_code',$sales_line->variant_code);
                    }
                    $item_ledgers = $item_ledgers->orderBy('expiration_date')->orderBy('entry_no')->get();
                    $total_remaining_qty = $item_ledgers->sum(function($r) {
                        return $this->service->toDouble($r->remaining_quantity);
                    });
                    if($total_remaining_qty < $total_qty_to_handle){
                        \DB::connection('company')->rollback();
                        return response()->json(['response_code' => '404'], 200);                                
                    }
                    foreach($item_ledgers as $item_ledger){ 
                        $item_buffer = new ItemTrackingBuffer();
                        $item_buffer->table_name = 'Sales';
                        $item_buffer->document_type = $sales_line->document_type;
                        $item_buffer->document_no = $sales_line->document_no;
                        $item_buffer->document_line_no = $sales_line->line_no;
                        $item_buffer->location_code = $sales_line->location_code;
                        $item_buffer->item_no = $sales_line->no;
                        $item_buffer->item_ledger_entry_no = $item_ledger->entry_no;
                        $item_buffer->serial_no = $item_ledger->serial_no;
                        $item_buffer->lot_no = $item_ledger->lot_no;
                        $item_buffer->warranty_date = $item_ledger->warranty_date;
                        $item_buffer->expiration_date = $item_ledger->expiration_date;
                        $item_buffer->quantity = $this->service->toDouble($sales_line->quantity);
                        $item_buffer->quantity_base = $this->service->toDouble($sales_line->quantity) * $this->service->toDouble($sales_line->qty_per_unit_of_measure);    
                        if($total_qty_to_handle > $this->service->toDouble($item_ledger->remaining_quantity)){                                    
                            $item_buffer->quantity_to_handle_base = $this->service->toDouble($item_ledger->remaining_quantity);                                    
                            $item_buffer->quantity_to_handle = $this->service->toDouble($item_buffer->quantity_to_handle_base) / $this->service->toDouble($sales_line->qty_per_unit_of_measure);
                            $total_qty_to_handle = $total_qty_to_handle - $this->service->toDouble($item_ledger->remaining_quantity);
                        }else {                                    
                            $item_buffer->quantity_to_handle_base = $total_qty_to_handle;
                            $item_buffer->quantity_to_handle = $this->service->toDouble($item_buffer->quantity_to_handle_base) / $this->service->toDouble($sales_line->qty_per_unit_of_measure);                                                                        
                            $total_qty_to_handle = 0;
                        }                                
                        $item_buffer->unit_of_measure = $sales_line->unit_of_measure;
                        $item_buffer->qty_per_unit_of_measure = $sales_line->qty_per_unit_of_measure;
                        $item_buffer->save();
                        if($total_qty_to_handle <= 0){
                            break;
                        }
                    }
                }
            }
            
            // ======= post sales invoice =========
            $result[0] = 'success';
            if($auto_post == 'Yes') $result = $this->sales_service->postInvoice($header,Auth::user(),null,'No','No','No');                
            
            if($result[0] != 'success'){
                \DB::connection('company')->rollback();                    
                return response()->json(['status' => 'failed', 'message' => $result]);
            }            
            $ecom_header->erp_status = 'Posted'; 
            $ecom_header->save(); 

            \DB::connection('company')->commit();
            $reponse_title = trans('greetings.Order Successfully Created!'); 
            $reponse_subtitle = trans('greetings.The order is successfully created!'); 
            if($auto_post == 'Yes') {
                $reponse_title = trans('greetings.Order Successfully Posted!'); 
                $reponse_subtitle = trans('greetings.The order is successfully Posted!'); 
            }
            $status = "success";
            $view = view('system.modal_settlement_success', compact('status','reponse_title','reponse_subtitle'))->render(); 
            return response()->json(['status' => 'success', 'message' => $result, 'view' => $view, 'id' => $id]);
            
        }catch(\Exception $ex){
            \DB::connection('company')->rollback();
            $this->service->saveErrorLog($ex);              
            return $this->service->webErrorRespoense($ex);
        } 
    }
    function generateTrackingNo($tracking_no){
        $date = Carbon::now()->format('ymd');
        $digit_no = 6;
        $digit_no = $digit_no - strlen($tracking_no);
        $new_tracking_no = '';
        for($i=0;$i < $digit_no; $i++){
            $new_tracking_no.="0";
        }
        $new_tracking_no.=$tracking_no;
        $random_num = mt_rand(100, 999);

        $tracking_no = $date.$new_tracking_no.$random_num;
        return $tracking_no;
    }

  
    public function createFirebaseDeliveryOrder($header, $shipment_agent){
        try {
            $driver = User::where('id', $shipment_agent->user_id)->first(); 
            $customer = User::where('id', $header['customer_no'])->first();            
            $org = Organizations::where('id', Auth::user()->account_id)->first(); 
            if(!$driver) return "failed"; 
            $requester = Auth::user(); 
            $requester->username = $header->ship_to_name;

            $firebase = service::FirebaseRealTimeDatabase(); 
            $database = $firebase->getDatabase();
            //==== tree driver ========== 
            $request_driver_ref = $database->getReference('smb/delivery/'.$driver->account_id.'/'.$driver->id.'/'.$header->id);
            $request_driver_snapshot = $request_driver_ref->getSnapshot();
            if(!$request_driver_snapshot->exists()){
                $requester->latitude = $org->latitude;
                $requester->longitude = $org->longitude;
                if($customer) $header->customer_avatar_128 = $customer->avatar_128;

                $data_request = [
                    "agent_id" => $driver->agent_id,
                    "customer_address" => $header->ship_to_address,
                    "customer_no" => $header->customer_no,
                    "customer_name" => $header->customer_name,
                    "document_no" => $header->id,
                    "order_datetime" => Carbon::now()->toDateString(),
                    "seller_id" => Carbon::now()->toDateString(),
                    "seller_id" => Auth::user()->id,
                    "tracking_no" => $header->tracking_no,
                    "status" => "Pending",
                ];
                $request_driver_ref->set($data_request);
            }
            return 'success' ;
        }catch (ReferenceHasNotBeenSnapshotted $e) {
            $this->service->saveErrorLog($e);
            return  $e->getReference()->getUri().': '.$e->getMessage();        
        }
        
    }
    public function removeFirebaseDeliveryOrder($header, $delivery_header){
        try{
            $shipment_agent = DB::connection("ecommerce")->table("shipment_agent")->where("org_id", Auth::user()->account_id)
                ->where("code", $delivery_header->shipment_agent_code)
                ->where("inactived","<>","Yes")
                ->first();
            if(!$shipment_agent) return "failed"; 
            $driver = User::where('id', $shipment_agent->user_id)->first(); 
            if(!$driver) return "failed"; 

            $firebase = service::FirebaseRealTimeDatabase(); 
            $database = $firebase->getDatabase();

            //==== tree driver ========== 
            $request_driver_ref = $database->getReference('smb/delivery/'.$driver->account_id.'/'.$driver->id.'/'.$header->id);
            $request_driver_snapshot = $request_driver_ref->getSnapshot();
            if($request_driver_snapshot->exists()){
                $request_driver_ref->remove(); 
            }
            //===== tree requester ======== 
            $request_gps_tracking = $database->getReference('smb/gps_live/'.$driver->account_id.'delivery'.$driver->shipment_method_code.'/'.$header->id);
            $request_gps_tracking_snapshot = $request_gps_tracking->getSnapshot();
            if($request_gps_tracking_snapshot->exists()){
                $request_gps_tracking->remove(); 
            }
            return "success"; 

        }catch (ReferenceHasNotBeenSnapshotted $e) {
            $this->service->saveErrorLog($e);
            return  $e->getReference()->getUri().': '.$e->getMessage();
        
        }
    }    
    public function removeFirebaseDriver($header, $delivery_header){
        try {
            $shipment_agent = DB::connection("ecommerce")->table("shipment_agent")->where("org_id", Auth::user()->account_id)
                                ->where("code", $delivery_header->shipment_agent_code)
                                ->where("inactived","<>","Yes")
                                ->first();
            if(!$shipment_agent) return "failed"; 
            $driver = User::where('id', $shipment_agent->user_id)->first(); 
            if(!$driver) return "failed"; 

            $firebase = service::FirebaseRealTimeDatabase(); 
            $database = $firebase->getDatabase();
            $ref = $driver->account_id."/".$driver->shipment_method_code."/".$driver->id."/".$header->id;

            //===== tree requester ========
            $request_gps_tracking = $database->getReference("drivers/gps_tracking/".$ref);
            $request_gps_tracking_snapshot = $request_gps_tracking->getSnapshot();
            if($request_gps_tracking_snapshot->exists()){
                $request_gps_tracking->remove(); 
            }
            return "success"; 
        }catch (ReferenceHasNotBeenSnapshotted $e) {
            $this->service->saveErrorLog($e);
            return  $e->getReference()->getUri().': '.$e->getMessage();
        
        }
    }
    public function OrderDetail(Request $request){
        try{
          
            $record =  EComSalesHeader::where('org_id', Auth::user()->account_id)->where('id', $this->service->decrypt($request->code))->first();
            if(!$record) return response()->json(['status'=>'warning', 'message'=>trans('greetings.Order is not found!')]);
            $lines = EComSalesLine::where('org_id', Auth::user()->account_id)->where('document_no', $record->id)->get(); 
            $amount_include_vat =  $sub_total = $lines->sum(function($r) {
                return service::toDouble($r->amount_including_vat_lcy);
            });
            $sub_total = $lines->sum(function($r) {
                return service::toDouble($r->amount);
            });
            $vat_amount = $lines->sum(function($r) {
                return service::toDouble($r->vat_amount);
            });
            $amount_due = service::toDouble($record->delivery_fee) + service::toDouble($amount_include_vat); 
            $shipment_agent = DB::connection('ecommerce')->table('shipment_agent')->where('code', $record->shipment_agent_code)->first(); 
        
            $payment_description = "Cash on Delivery"; 
            $payment_method = PaymentMethod::select('description')->where('code', $record->payment_method_code)->where('inactived', '<>', 'Yes')->where('mobile_payment', 'Yes')->first(); 
            if($payment_method) $payment_description =  $payment_method->description; 
            $erp_invoice = SaleHeader::selectRaw('no')->where('no',$record->erp_document_no)->first(); 
            $erp_document_no = ''; 
            if($erp_invoice) $erp_document_no = $erp_invoice->no; 
            $view = view('dashboard.presidentsmb.ecommerce_order_detail', compact('record','amount_include_vat', 'lines','sub_total','vat_amount','shipment_agent','payment_description','amount_due', 'erp_document_no'))->render(); 
            return response()->json(['status' => 'success', 'view' => $view, 'name' => $record->customer_name, 'latitude' => $record->ship_to_latitude, 'longitude' => $record->ship_to_longitude ]);
        
        }catch(\Exception $ex){
            return response()->json(['status' => 'failed', "message" => "token updated failed!" . $ex->getMessage(). $ex->getLine()]);
        } 
    }
    public function getFromAddUser(Request $request){
        try{
            $view = view('dashboard.activites.activity.wholesales_ordering_add_user')->render(); 
            return response()->json(['status' => 'success', 'view' => $view]);
        }catch(\Exception $ex){
            return response()->json(['status' => 'failed', "msg" => "token updated failed!" . $ex->getMessage()]);
        } 
    }
    public function eEcomAddUser(Request $request){
        \DB::connection('company')->beginTransaction();
        \DB::connection('mysql')->beginTransaction();
        try{
            $data = $request->all();             
            $user = User::where('email', $data['phone_no'])->first();   
            $ecom_setup = eCommerceSetup::first(); 
            $app_setup = ApplicationSetup::first(); 
            if($user) {
                $user_organization = UserOrganizations::where('user_id', $user->id)->where('organizations_id', Auth::user()->account_id)->first();                 
                if($user_organization) return response()->json(['status'=>'warning', 'record' => $user, 'msg'=>trans('greetings.The phone number already setup!')]);
                // user orgazation                 
                $user_org = new UsersOrganizations(); 
                $user_org->user_id = $user->id; 
                $user_org->organizations_id = Auth::user()->account_id; 
                $user_org->database_name = Auth::user()->database_name; 
                $user_org->customer_no = $user->email; 
                $user_org->vendor_no = $user->email; 
                $user_org->save();      

                // customer record 
                $customer = Customer::where('no', $user_org->customer_no)->first(); 
                if(!$customer){
                    $customer = new Customer(); 
                    $customer->no = $user_org->customer_no;
                    $customer->name = $user->first_name;
                    $customer->name_2 = $user->last_name; 
                    $customer->address = $user->address; 
                    $customer->phone_no =  $user->phone_no; 
                    $customer->email = $user->email; 
                    $customer->customer_price_group_code = $ecom_setup->wholeser_price_group_code;                     
                    $customer->payment_term_code = 'COD';
                    $customer->rec_posting_group_code = $app_setup->default_ar_posting_group;
                    $customer->vat_posting_group_code = $app_setup->default_vat_bus_posting_group;
                    $customer->gen_bus_posting_group_code = $app_setup->default_gen_bus_posting_group;
                    $customer->price_include_vat = $app_setup->default_item_price_include_vat;
                    $customer->save(); 
                }

                // vendor record 
                $vendor = Vendor::where('no', $user_org->vendor_no)->first(); 
                if(!$vendor){
                    $vendor = new Vendor(); 
                    $vendor->no = $user_org->vendor_no;
                    $vendor->name = $user->first_name;
                    $vendor->name_2 = $user->last_name; 
                    $vendor->address = $user->address; 
                    $vendor->phone_no =  $user->phone_no; 
                    $vendor->email = $user->email; 
                    $vendor->payment_term_code = 'COD';
                    $vendor->ap_posting_group_code = $app_setup->default_ap_posting_group;
                    $vendor->vat_bus_posting_group_code = $app_setup->default_vat_bus_posting_group;
                    $vendor->gen_bus_posting_group_code = $app_setup->default_gen_bus_posting_group;
                    $vendor->price_include_vat = $app_setup->default_item_price_include_vat;
                    $vendor->save(); 
                }                
                \DB::connection('mysql')->commit();
                \DB::connection('company')->commit();                  
                $view = view('dashboard.activites.activity.wholesales_ordering_add_user_complete')->render(); 
                return response()->json(['status' => 'success', "view" => $view]);
            }else {                
                UserVerification::where('email', $request->phone_no)->delete();                                 
                $user_verification = new UserVerification(); 
                $user_verification->email = $data['phone_no']; 
                $user_verification->first_name = $data['first_name']; 
                $user_verification->last_name = $data['last_name']; 
                $user_verification->gender = $data['gender']; 
                $user_verification->date_of_birth = Carbon::parse($data['date_of_birth'])->toDateString(); 
                $user_verification->phone_no = $data['phone_no']; 
                $user_verification->locale = 'en'; 
                $user_verification->user_type = 'ECom User';
                $user_verification->save(); 
                \DB::connection('mysql')->commit();
                \DB::connection('company')->commit();                  
                $view = view('dashboard.activites.activity.wholesales_ordering_user_comfirm_code', compact('user_verification'))->render(); 
                return response()->json(['status' => 'success', 'view' => $view]);
            }                  
        }catch(\Exception $ex){
            \DB::connection('mysql')->rollback();
            \DB::connection('company')->rollback();
            $this->service->saveErrorLog($ex);
            return response()->json(['status' => 'failed', "msg" => "token updated failed!" . $ex->getMessage()]);
        } 
    }
    public function eEcomStoreUser(Request $request){
        \DB::connection('mysql')->beginTransaction();
        \DB::connection('company')->beginTransaction();
        $ecom_setup = eCommerceSetup::first(); 
        $app_setup = ApplicationSetup::first(); 
        try{
            $user_verification = UserVerification::where('id', $this->service->decrypt($request->verified_id))->first(); 
            if(!$user_verification)  return response()->json(['status' => 'warning', "msg" => "Record Not Found."]);
            
            $user = new User(); 
            $user->email = $user_verification->phone_no; 
            $user->first_name = $user_verification->first_name; 
            $user->last_name =  $user_verification->last_name; 
            $user->gender =  $user_verification->gender; 
            $user->date_of_birth = Carbon::parse( $user_verification->date_of_birth)->toDateString(); 
            $user->phone_no =  $user_verification->phone_no; 
            $user->password = bcrypt($request->code);
            $user->locale = 'en'; 
            $user->status = 9;
            $user->currency_code = Auth::user()->app_setup->local_currency_code;  
            $user->user_type = 'ECom User';
            $user->reset_password = 'Yes';
            $user->save(); 
            // // user orgazation 
            $user_org = new UsersOrganizations(); 
            $user_org->user_id = $user->id; 
            $user_org->organizations_id = Auth::user()->account_id; 
            $user_org->database_name = Auth::user()->database_name; 
            $user_org->customer_no = $user->email; 
            $user_org->vendor_no = $user->email; 
            $user_org->is_allow_paid = 1; 
            $user_org->save(); 

            // customer record 
            $customer = Customer::where('no', $user_org->customer_no)->first(); 
            if(!$customer){
                $customer = new Customer(); 
                $customer->no = $user_org->customer_no;
                $customer->name = $user->first_name;
                $customer->name_2 = $user->last_name; 
                $customer->address = $user->address; 
                $customer->phone_no =  $user->phone_no; 
                $customer->email = $user->email; 
                $customer->customer_price_group_code = $ecom_setup->wholeser_price_group_code;                     
                $customer->payment_term_code = 'COD';
                $customer->rec_posting_group_code = $app_setup->default_ar_posting_group;
                $customer->vat_posting_group_code = $app_setup->default_vat_bus_posting_group;
                $customer->gen_bus_posting_group_code = $app_setup->default_gen_bus_posting_group;
                $customer->price_include_vat = $app_setup->default_item_price_include_vat;
                $customer->save(); 
            }

            // vendor record 
            $vendor = Vendor::where('no', $user_org->vendor_no)->first(); 
            if(!$vendor){
                $vendor = new Vendor(); 
                $vendor->no = $user_org->vendor_no;
                $vendor->name = $user->first_name;
                $vendor->name_2 = $user->last_name; 
                $vendor->address = $user->address; 
                $vendor->phone_no =  $user->phone_no; 
                $vendor->email = $user->email; 
                $vendor->payment_term_code = 'COD';
                $vendor->ap_posting_group_code = $app_setup->default_ap_posting_group;
                $vendor->vat_bus_posting_group_code = $app_setup->default_vat_bus_posting_group;
                $vendor->gen_bus_posting_group_code = $app_setup->default_gen_bus_posting_group;
                $vendor->price_include_vat = $app_setup->default_item_price_include_vat;
                $vendor->save();  
            }



            $user_verification->delete(); 
            \DB::connection('mysql')->commit();            
            \DB::connection('company')->commit();            
            $view = view('dashboard.activites.activity.wholesales_ordering_add_user_complete')->render(); 
            return response()->json(['status' => 'success', "view" => $view]);
        }
        catch(\Exception $ex){
            \DB::connection('mysql')->rollback();
            \DB::connection('company')->rollback();
            $this->service->saveErrorLog($ex);
            return response()->json(['status' => 'failed', "msg" => "token updated failed!" . $ex->getMessage()]);
        } 
    }
    public function checkDuplicate(Request $request){
        try{
          
            $record = User::where('email', $request->phone_no)->first();            
            if($record) {
                $user_organization = UserOrganizations::where('user_id', $record->id)->where('organizations_id', Auth::user()->account_id)->first();                 
                if($user_organization){
                    return response()->json(['status'=>'exit', 'record' => $record, 'msg'=>trans('greetings.The phone number already setup!')]);
                }else{
                    return response()->json(['status'=>'new', 'record' => $record, 'msg'=>trans('greetings.The phone number already setup!')]);
                }                
            }            
            return response()->json(['status'=>'success']);
        } catch(\Exception $ex){
            $this->service->saveErrorLog($ex);
            return response()->json(['status' => 'failed', "msg" => "token updated failed!" . $ex->getMessage()]);
        } 
    }
    public function checkDuplicateOnClicked(Request $request){
        try{          
            $record = User::where('email', $request->phone_no)->first();            
            if($record) {
                return response()->json(['status'=>'exit', 'record' => $record, 'msg'=>trans('greetings.The phone number already setup!')]);                
            }else {
                return response()->json(['status'=>'new', 'record' => $record, 'msg'=>trans('greetings.The phone number already setup!')]);
            }            
            return response()->json(['status'=>'success']);
        } catch(\Exception $ex){
            $this->service->saveErrorLog($ex);
            return response()->json(['status' => 'failed', "msg" => "token updated failed!" . $ex->getMessage()]);
        } 
    }
    public function storeCustomer(Request $request){
       try{
            $user_org_id = $request->user_org; 
            $app_setup = Auth::user()->app_setup; 
            \DB::beginTransaction();
            $user_org = UsersOrganizations::where('id', $user_org_id)->first(); 
            if(!$user_org) return response()->json(['status' => 'warning', "msg" => "User Organization Not Found."]);
            $user = User::where('id', $user_org->user_id)->where('status',9)->first(); 
            if(!$user) return response()->json(['status' => 'warning', "msg" => "User Not Found."]);

            if($request->tap_name == 'exit_customer'){
                $customer = Customer::where('no', $request->customer_no)->where('inactived', '<>', 'Yes')->first(); 
            }else{
                re_generate_no: 
                $no_series = $this->service->generateNo($request->no_series, 'Customer');
                if ($no_series == 'error_no_series') {
                    return response()->json(['status' => 'failed', 'msg' => trans('greetings.NoSeriesWasNotSetup')]);
                }
                $customer = Customer::where('no',$no_series)->first(); 
                if($customer){
                    goto re_generate_no;
                }
                $customer = new Customer();
                $customer->no = $no_series;
                $customer->name = $user->first_name;
                $customer->name_2 = $user->last_name; 
                $customer->address = $user->address; 
                $customer->phone_no =  $user->phone_no; 
                $customer->email = $user->email; 
                $customer->customer_price_group_code = $request->customer_price_group_code; 
                $customer->territory_code = $request->territory_code; 
                $customer->customer_group_code = $request->customer_group_code; 
                $customer->store_code = $request->store_code; 
                $customer->division_code = $request->division_code; 
                $customer->business_unit_code = $request->business_unit_code; 
                $customer->project_code = $request->project_code; 
                $customer->department_code = $request->department_code; 
                $customer->budget_code = $request->budget_code; 
                $customer->salesperson_code = $request->salesperson_code; 
                $customer->distributor_code = $request->distributor_code; 
                $customer->location_code = $request->location_code; 
                $customer->payment_term_code = $request->payment_term_code;
                $customer->rec_posting_group_code = $request->rec_posting_group_code;
                $customer->vat_posting_group_code = $request->vat_posting_group_code;
                $customer->gen_bus_posting_group_code = $request->gen_bus_posting_group_code;
                $customer->credit_limited_type = $request->credit_limited_type;
                $customer->credit_limited_amount = $this->service->number_formattor_database($request->credit_limited_amount, 'amount'); 
                $customer->price_include_vat = $app_setup->default_item_price_include_vat;
                $customer->save(); 
            }
            //UPDATE CUSTOMER OF USER ORGANIZATION 
            $user_org->customer_no = $customer->no; 
            $user_org->save(); 
            \DB::commit();
            $view = view('dashboard.activites.activity.wholesales_ordering_add_user_complete')->render(); 
            return response()->json(['status' => 'success', "view" => $view]);

       } catch(\Exception $ex){
            \DB::connection('company')->rollback();
            $this->service->saveErrorLog($ex);
            return response()->json(['status' => 'failed', "msg" => "token updated failed!" . $ex->getMessage()]);
        }
    }
    public function cancelOrder(Request $request){
       try{
        $id = $this->service->decrypt($request->code); 
        $ecom_setup = eCommerceSetup::first();
        $ecomHeader =  EComSalesHeader::where('id',$id)->first();
        if(!$ecomHeader) return response()->json(['status' => 'warning', "msg" => "Ecom order not found."]);
        $delivery_header = EComDeliveryHeader::where('document_no', $ecomHeader->id)->where('org_id', Auth::user()->account_id)->first(); 
         // ======================== check payment refund ===
        if($this->service->isExistedTableField("ecommerce_setup", "allow_payment_refund") && $ecom_setup->allow_payment_refund == 'Yes'){
            // ACLEDA REQUEST CHECK STATUS 
            if(in_array(strtolower($ecomHeader->payment_method_code), ['acleda_xpay'])){
               $result = $this->checkAcledaPaymentStatus($ecomHeader);
               $result = $result['result']; 
               if(strtoupper($result['errorDetails']) == 'SUCCESS') return response()->json(['status' => 'Warning','message' => 'You cannot cancel this order because it already paid.Please go to refund payment first in Acleda Bank system.']);
            }
        }
        // ======================== End ====================
        \DB::connection('company')->beginTransaction();
        $ecomHeader->status = 'Cancelled';
        $ecomHeader->save(); 
        if($delivery_header){
            $delivery_header->status = 'Cancelled';
            $delivery_header->save();
            $this->NotificationCancelToDriver($ecomHeader); 
            $this->removeFirebaseDeliveryOrder($ecomHeader, $delivery_header); 
            $this->removeFirebaseDriver($ecomHeader, $delivery_header);
        }
        $header = SaleHeader::where('no', $ecomHeader->erp_document_no)->first(); 
        if($header){
            $header->status = 'Closed';
            $header->save();
        }
        //REMOVE FIRE TREE 
        \DB::connection('company')->commit();
        $this->NotificationCancelToCustomer($ecomHeader); 
        
        if(isset($request['action_from']) && $request['action_from'] == 'ecommerce_order'){
            $record = $ecomHeader;
            $view = view('sales.history.ecommerce_order_records', compact('record'))->render();
        }else{
            $order = DB::connection('company')->table('esales_header')->join('esales_line', 'esales_header.id', 'esales_line.document_no')
                    ->where('esales_header.org_id', Auth::user()->account_id)
                    ->where('esales_header.id',$id)
                    ->whereRaw("(esales_header.erp_status = 'New' or esales_header.status <> 'Delivered')")
                    ->selectRaw('esales_header.id,esales_header.delivery_fee, esales_header.document_no, esales_header.tracking_no,esales_header.currency_code, esales_header.customer_no, esales_header.ship_to_name, esales_header.ship_to_address, sum(esales_line.amount) as amount, esales_header.status,esales_header.ship_to_phone_no,sum(esales_line.amount) as amount,sum(esales_line.amount_including_vat_lcy) as amount_including_vat_lcy,esales_header.payment_method_code,esales_header.erp_status, esales_header.order_datetime')
                    ->groupBy('esales_header.document_no','esales_header.delivery_fee','esales_header.id', 'esales_header.tracking_no','esales_header.currency_code', 'esales_header.customer_no', 'esales_header.ship_to_name', 'esales_header.ship_to_address', 'esales_header.status', 'esales_header.ship_to_phone_no', 'esales_header.payment_method_code', 'esales_header.erp_status', 'esales_header.order_datetime')
                    ->orderBy('esales_header.order_datetime', 'desc')->first(); 
            $view = view('dashboard.activites.activity.wholesaler_ordering_records', compact('order','ecom_setup'))->render();
        }
        return response()->json(['status' => 'success','id' => $id,'view' => $view, "msg" => trans('greetings.Successful cancel order.')]);
       } catch(\Exception $ex){
            \DB::connection('company')->rollback();
            $this->service->saveErrorLog($ex);
            return response()->json(['status' => 'failed', "msg" => "token updated failed!" . $ex->getMessage()]);
        }
    }
    public function cancelDelivery(Request $request){
        try{
            $id = $this->service->decrypt($request->code); 
            $ecom_setup = eCommerceSetup::first();
            $ecomHeader =  EComSalesHeader::where('id',$id)->first();
            if(!$ecomHeader) return response()->json(['status' => 'warning', "msg" => "Ecom order not found."]);
            $delivery_header = EComDeliveryHeader::where('tracking_no', $ecomHeader->tracking_no)->where('org_id', Auth::user()->account_id)->first(); 
            if(!$delivery_header) return response()->json(['status' => 'warning', "msg" => "Delivery order not found."]);
            \DB::connection('company')->beginTransaction();
            $ecomHeader->status = 'Confirmed';
            $ecomHeader->save(); 
            $delivery_header->status = 'Cancelled';
            $delivery_header->save();
            $this->removeFirebaseDeliveryOrder($ecomHeader, $delivery_header); 
            $this->removeFirebaseDriver($ecomHeader, $delivery_header);
            \DB::connection('company')->commit();
            $this->NotificationCancelToCustomer($ecomHeader); 
            $this->NotificationCancelToDriver($ecomHeader); 
           
            if(isset($request['action_from']) && $request['action_from'] == 'ecommerce_order'){
                $record = $ecomHeader;
                $view = view('sales.history.ecommerce_order_records', compact('record'))->render();
            }else{
                $order = DB::connection('company')->table('esales_header')->join('esales_line', 'esales_header.id', 'esales_line.document_no')
                ->where('esales_header.org_id', Auth::user()->account_id)
                ->where('esales_header.id',$id)
                ->whereRaw("(esales_header.erp_status = 'New' or esales_header.status <> 'Delivered')")
                ->selectRaw('esales_header.id,esales_header.delivery_fee, esales_header.document_no, esales_header.tracking_no,esales_header.currency_code, esales_header.customer_no, esales_header.ship_to_name, esales_header.ship_to_address, sum(esales_line.amount) as amount,sum(esales_line.amount_including_vat_lcy) as amount_including_vat_lcy, esales_header.status,esales_header.ship_to_phone_no,esales_header.payment_method_code,esales_header.erp_status, esales_header.order_datetime')
                ->groupBy('esales_header.document_no','esales_header.id','esales_header.delivery_fee', 'esales_header.tracking_no','esales_header.currency_code', 'esales_header.customer_no', 'esales_header.ship_to_name', 'esales_header.ship_to_address', 'esales_header.status', 'esales_header.ship_to_phone_no', 'esales_header.payment_method_code', 'esales_header.erp_status', 'esales_header.order_datetime')
                ->orderBy('esales_header.order_datetime', 'desc')->first(); 
                $view = view('dashboard.activites.activity.wholesaler_ordering_records', compact('order','ecom_setup'))->render();
            }
            
            return response()->json(['status' => 'success','id' => $id,'view' => $view, "msg" => trans('greetings.Successful cancel delivery order.')]);
        }catch(\Exception $ex){            
            \DB::connection('company')->rollback();
            $this->service->saveErrorLog($ex);
            return response()->json(['status' => 'failed', "msg" => "token updated failed!" . $ex->getMessage()]);
        }
    }
    public function getAmountOfInvoice(Request $request){
        try{
            $code = $this->service->decrypt($request->code);
            $header = EComSalesHeader::where('id',$code)->first(); 
            if(!$header) return response()->json(['status'=>'warning', 'message'=>trans('greetings.Order is not found!')]);
            $records = EComSalesLine::where('document_no',$header->id)->get();
            $total_amount = count($records) > 0 ? $records->sum(function($r) {
                return service::toDouble($r->amount_including_vat_lcy);
            }) : 0; 
            $total_amount = service::toDouble($total_amount) + service::toDouble($header->delivery_fee); 

            return response()->json(['status'=>'success', 'total_amount'=> service::number_formattor($total_amount, 'amount')]);

        }catch (\Exception $ex){
            $this->service->saveErrorLog($ex);
            return $this->service->webErrorRespoense($ex);
        } 
    }
    public function NotificationCancelToCustomer($header){
        //===== Ecommerce Users =====//
        $api_sessions_ecommerce_users = \DB::connection('mysql')->table('api_sessions')
            ->where('user_id', $header['customer_no'])
            ->whereIn('app_id', ["com.clearviewerp.ecommerce","kiddeebebe.clearviewerp.com","hengpiv.clearviewerp.com","royalmart.clearviewerp.com","setra.clearviewerp.com"])
            ->where('firebase_client_key', '<>','')
            ->get();
        $noti_sessions = $api_sessions_ecommerce_users->unique("user_id");
        if(count($noti_sessions) > 0){
            foreach($noti_sessions as $api_session){
                $sessionToken = openssl_random_pseudo_bytes(20);
                $sessionToken = bin2hex($sessionToken);
                $notification = new MyNotification();
                $notification->id = $sessionToken;
                $notification->type = 'App';
                $notification->notifiable_id = $api_session->user_id;
                $notification->notifiable_type = 'App\User';
                $notification->description = 'The order no #' . $header->document_no . ' has been cancelled.';
                $notification->entry_date = Carbon::now()->toDateString();
                $notification->entry_datetime = Carbon::now();
                $notification->document_type = 'EOrder';
                $notification->document_no = $header->document_no;
                $notification->app_id = $api_session->app_id; 
                $notification->data = json_encode([
                    'sender_id' => Auth::user()->id,
                    'header' => $header,
                ]);
                $notification->save();
            }
        }

        $array_ecommerce_users = $api_sessions_ecommerce_users->pluck("firebase_client_key");
        $fcmNotificationData = [
            'registration_ids' => $array_ecommerce_users,
            'notification' => [
            'title' => 'Order Cancelled',  
            'body' => 'The order no #' . $header->document_no . ' has been cancelled.',
            'sound' => 'default'
            ],
            'data' => [
            'document_no' => $header->document_no,
            'type' => 'Cancelled',
            'status' => 'Cancelled'
            ]
        ];
        $this->service->sendNotification($fcmNotificationData);
    }
    public function NotificationCancelToDriver($header){
         //notify to driver 
         $shipment_agent = DB::connection("ecommerce")->table("shipment_agent")->where("org_id", Auth::user()->account_id)
         ->where("code", $header->shipment_agent_code)->where("inactived","<>","Yes")->first();
        if($shipment_agent){
            $api_sessions_driver_users = \DB::connection('mysql')->table('api_sessions')
            ->where('user_id', $shipment_agent->user_id)
            ->whereIn('app_id', ["com.clearviewerp.express"])
            ->where('firebase_client_key', '<>','')
            ->get();

            $array_driver_users = $api_sessions_driver_users->pluck("firebase_client_key");                
            $fcmDriverNotificationData = [
                'registration_ids' => $array_driver_users,
                'notification' => [
                'title' => 'Order Cancelled.',  
                'body' => 'Cancel delivery order ship to '.$header['ship_to_name'].' '.$header['ship_to_address'].' '.$header['ship_to_phone_no'],
                'sound' => 'default'
                ],
                'data' => [
                'document_no' => $header->document_no,
                'type' => 'Arrived',
                'status' => 'shipping'
                ]
            ];
            $driver_noti_sessions = $api_sessions_driver_users->unique("user_id");
            if(count($driver_noti_sessions) > 0){
                foreach($driver_noti_sessions as $api_session){
                    $sessionToken = openssl_random_pseudo_bytes(20);
                    $sessionToken = bin2hex($sessionToken);
                    $notification = new MyNotification();
                    $notification->id = $sessionToken;
                    $notification->type = 'App';
                    $notification->notifiable_id = $api_session->user_id;
                    $notification->title = $fcmDriverNotificationData['notification']['title'];
                    $notification->description = $fcmDriverNotificationData['notification']['body'];
                    $notification->entry_date = Carbon::now()->toDateString();
                    $notification->entry_datetime = Carbon::now();
                    $notification->document_type = 'EOrder';
                    $notification->document_no = $header->document_no;
                    $notification->app_id = $api_session->app_id; 
                    $notification->data = json_encode([
                        'sender_id' => Auth::user()->id,
                        'header' => $header,
                    ]);
                    $notification->save();
                }
            }
            $this->service->sendNotification($fcmDriverNotificationData);
        }
    }
    function NotificationProcess($data, $api_sessions=null, $type=null)
    {
        $sale_header = $data['sales_header'];
        $arr_firebase_client_key = array();
        $description = "";
        $title = '';

        if($type == "Arrived"){
            $title = 'Pickup';
            $description = 'Pickup delivery order #'.$sale_header['document_no'];
        }elseif($type == 'StartDelivery'){
            $title = 'Delivery Confirmation';
            $description = 'Request delivery order to '.$sale_header['ship_to_name'].' '.$sale_header['ship_to_phone_no'];
        }elseif($type == "Delivered"){
            $title = 'Order Delivered';
            $description = 'Your order #'.$sale_header['document_no'].' has been delivered!';
        }
        $arr_firebase_client_key = $api_sessions->pluck("firebase_client_key");
        $api_sessions = $api_sessions->unique("user_id");
        
        if($api_sessions){
            foreach($api_sessions as $api_session){
                // array_push($arr_firebase_client_key, $api_session->firebase_client_key);
                // ======= Create Notification ===
                $sessionToken = openssl_random_pseudo_bytes(20);
                $sessionToken = bin2hex($sessionToken);
                $notification = new MyNotification();
                $notification->id = $sessionToken;
                $notification->type = 'App';
                $notification->notifiable_id = $api_session->user_id;
                $notification->notifiable_type = 'App\User';
                $notification->description = $description;
                $notification->entry_date = Carbon::now()->toDateString();
                $notification->entry_datetime = Carbon::now();
                $notification->document_type = 'EOrder';
                $notification->document_no = $sale_header['document_no'];
                $notification->app_id = $api_session->app_id; 
                $notification->data = json_encode([
                    'sender_id' => Auth::user()->id,
                    'document_type' => "Web Request",
                    'data' => $data,
                    'header' => $sale_header,
                ]);
                $notification->save();
            }

            $customer = User::where('id', $sale_header['customer_no'])->first();
            $sale_header['avatar'] = '';
            $sale_header['avatar_32'] = '';
            $sale_header['avatar_128'] = '';
            if($customer){
                $sale_header['avatar'] = $customer->avatar;
                $sale_header['avatar_32'] = $customer->avatar_32;
                $sale_header['avatar_128'] = $customer->avatar_128;
            }
            
            $sale_header['document_type'] = 'EOrder';         

            $notificationData = [
                'sender_id' => Auth::user()->id,
                'document_type' => 'Request Delivery',
                'data' => [],
                'header' => [],
            ];
            $fcmNotificationData = [
                'registration_ids' => $arr_firebase_client_key,
                'notification' => [
                    'title' => $title,
                    'description' => $description,
                    'body' => $description,
                    'extra_data' => ["type" => "Request Delivery"],
                    "content_available" => true,
                    "priority" => "high",
                    'sound' => 'default'
                ],
                'data' => [
                    'type' => 'Request Ride',
                    'data' => [
                        'sender_id' => Auth::user()->id,
                        'data' => $notificationData,
                    ],
                    'status' => 'Processing'
                ]
            ];
            $result = $this->service->sendNotification($fcmNotificationData);
            return $result;
        }else{
            return false;
        }
    }
    public function sendNotificationSpacificUserLangaugeApp($header,$array_user_ecoomer_en, $array_user_ecommer_kh){
        try{
            if(count($array_user_ecommer_kh) > 0){
                $fcmNotificationData = [
                    'registration_ids' => $array_user_ecommer_kh,
                    'notification' => [
                        'title' =>  $header->title_kh,  
                        'body' => $header->body_kh,
                        'sound' => 'default'
                    ],
                    'data' => [
                        'document_no' => $header->document_no,
                        'type' => 'confirmed',
                        'status' => 'Confirmed'
                    ]
                ];
                $this->service->sendNotification($fcmNotificationData);
            }
            if(count($array_user_ecoomer_en) > 0){
                $fcmNotificationData = [
                    'registration_ids' => $array_user_ecoomer_en,
                    'notification' => [
                        'title' => $header->title_en,  
                        'body' => $header->body_en,
                        'sound' => 'default'
                    ],
                    'data' => [
                        'document_no' => $header->document_no,
                        'type' => 'confirmed',
                        'status' => 'Confirmed'
                    ]
                ];
                
               $this->service->sendNotification($fcmNotificationData);
            }
        }catch (\Exception $ex){
            \DB::connection('ecommerce')->rollback();
            $this->service->saveErrorLog($ex);
            return $this->service->webErrorRespoense($ex);
        } 
    }

    // ============= Blog ==
    public function BlogPagination(Request $request){
        $blogs = EComFeed::where('org_id', Auth::user()->account_id)
            ->orderBy('created_at', 'desc')
            ->paginate($this->service->num_paginate());    
        
        $view = view('dashboard/activites/activity/wholesaler_blog_list', compact("blogs"))->render();
        return response()->json(['status' => 'success', 
            'currentPage' => $blogs->currentPage(),
            'lastPage' => $blogs->lastPage(),
            'view' => $view]);  
    }
    public function BlogDetail(Request $request){
        $blog_id = isset($request['code'])? $this->service->decrypt($request['code']) : '';
        $blog = null;
        if($request['type'] == 'ed'){
            $blog = \DB::connection('company')->table('feed')
                ->where('org_id', Auth::user()->account_id)
                ->where('id', $blog_id)->first();
            if(!$blog) return response()->json(['status' => 'failed' , 'msg' => trans('greetings.The blog can not be found!')]);  
        }

        $view = view('dashboard/activites/activity/blog_dashbord_card', compact("blog"))->render();
        return response()->json(['status' => 'success', 'view' => $view]);  
    }
    public function DeleteBlogGallery(Request $request){
        try{
            $data =$request->all(); 
            $blog_id = $this->service->decrypt($data['code']);
            $gallery_index = $data['gallery_index'];
            $blog = EComFeed::where('org_id', Auth::user()->account_id)->where('id', $blog_id)->first();
            if(!$blog) return response()->json(['status' => 'failed' , 'msg' => trans('greetings.The blog can not be found!')]);  
            $media_urls = explode(',', $blog->media_urls);
            if(isset($media_urls[$gallery_index])){
                $database_name = config('database.connections.company.database');
                $file_path_company = "upload/" . $database_name;
                $get_file = pathinfo($media_urls[$gallery_index]); 
                $file_name = $get_file['basename'];
                $file_path_company_temp = public_path()."/".$file_path_company . "/blog_gallery/" . $blog_id."/".$file_name;
                if (file_exists($file_path_company_temp)) {
                    unlink($file_path_company_temp);   
                }
                $str_media_urls = "";
                foreach($media_urls as $key=>$media_url){
                    if($key != $gallery_index){
                        $str_media_urls .= $media_url.",";
                    }
                }
                $blog->media_urls = substr($str_media_urls, 0, -1);
                $blog->save();
            }
            $view = view('dashboard.activites.activity.blog_gallary_list', compact("blog"))->render();
            return response()->json(['status' => 'success', 'view' => $view]);

        }catch (\Exception $ex){
            \DB::connection('company')->rollback();
            $this->service->saveErrorLog($ex);
            return response()->json(['status' => $ex->getMessage()]);
        }
    }
    public function UploadBlogCover(Request $request){
        try {
            $data =$request->all(); 
            $blog_id = $this->service->decrypt($data['code']);
            $database_name = config('database.connections.company.database');
            // // =============== CHECK DOCUMENT TYPE =======================
            $folder_name = strtolower(preg_replace('/\s+/', '_', $blog_id));
            // =============== CREATE UPLOAD DIRECTORY ===================
            $file_path = "upload/".$database_name;
            if (!file_exists($file_path)) {
                mkdir($file_path, 0777, true);
            }
            $file_path_company_item = $file_path."/blog_cover/".$folder_name;
            if (!file_exists($file_path_company_item)) mkdir($file_path_company_item, 0777, true);
             // =============== END CREATE UPLOAD DIRECTORY ===================
            if($this->service->validate_maximum_upload_size($file_path_company_item)){
                return response()->json(['status' => 'failed', 'message' => trans('greetings.Your upload disk space was exceed maximum size!')]);
            }
            // =============== DELETE EXISTING USER PROFILE PICTURE ==========
            $files = scandir($file_path_company_item . "/");
            foreach ($files as $file) {
                if ($file != '.' && $file != '..') {
                    if (file_exists($file_path_company_item . "/" . $file)) {
                        unlink($file_path_company_item . "/" . $file);
                    }
                }
            }
            $fileName = $_FILES["file"]['name'];
            $fileTmpLoc = $_FILES["file"]["tmp_name"];
            $kaboom = explode(".", $fileName);
            $fileExt = end($kaboom);
            $token = openssl_random_pseudo_bytes(20);
            $token = bin2hex($token);
            $fname = $token.'.'.$fileExt;
            $allowed = array('png','jpg', 'PNG', 'JPG','jpeg','JPEG');
            if (!in_array($fileExt, $allowed)) return response()->json(['status' => 'warning', 'msg' => trans('greetings.Faild to upload file, System suport only picture file!')]);
            $moveResult = move_uploaded_file($fileTmpLoc,$file_path_company_item."/".$fname);
            if ($moveResult == true) {
                $resized_file_ori = $file_path_company_item."/".$fname;
                $file_url = $this->service->imageWorkereCommerce($resized_file_ori, null, null);
                if(isset($_SERVER['HTTPS'])) $link = 'https://';
                else $link = 'http://';
                \DB::connection('company')->beginTransaction();
                $blog = EComFeed::where('org_id', Auth::user()->account_id)->where('id', $blog_id)->first();
                if(!$blog) $blog = new EComFeed(); 
                $blog->cover = $link . \Request::server('HTTP_HOST').'/'.$file_url;
                $blog->save(); 
                \DB::connection('company')->commit();    
                return response()->json(['status' => 'success', 'cover' => $blog->cover, 'blog_id' => $data['code']]);
            }
        }catch (\Exception $ex){
          \DB::connection('company')->rollback();
          $this->service->saveErrorLog($ex);
          return $this->service->webErrorRespoense($ex);
        } 
    }
    public function UploadBlogGallery(Request $request){
        try{
            if($request->hasFile('file')){
                $data =$request->all(); 
                \DB::connection('company')->beginTransaction();
                $blog_id = $this->service->decrypt($data['code']);
                $database_name = config('database.connections.company.database');
                // // =============== CHECK DOCUMENT TYPE =======================
                $folder_name = strtolower(preg_replace('/\s+/', '_', $blog_id));
                // =============== CREATE UPLOAD DIRECTORY ===================
                $file_path = "upload/".$database_name;
                if (!file_exists($file_path)) {
                    mkdir($file_path, 0777, true);
                }
                $file_path_company_item = $file_path."/blog_gallery/".$folder_name;
                if (!file_exists($file_path_company_item)) {
                    mkdir($file_path_company_item, 0777, true);
                }
                 // =============== END CREATE UPLOAD DIRECTORY ===================
                if($this->service->validate_maximum_upload_size($file_path_company_item)){
                    return response()->json(['status' => 'failed', 'message' => trans('greetings.Your upload disk space was exceed maximum size!')]);
                }
                $fileName = $_FILES["file"]['name'];
                
                $fileTmpLoc = $_FILES["file"]["tmp_name"];
                $kaboom = explode(".", $fileName);
                $fileExt = end($kaboom);
                $token = openssl_random_pseudo_bytes(20);
                $token = bin2hex($token);
                $fname = $token.'.'.$fileExt;
                $allowed = array('png','jpg', 'PNG', 'JPG','jpeg','JPEG');
                if (!in_array($fileExt, $allowed)) return response()->json(['status' => 'warning', 'msg' => trans('greetings.Faild to upload file, System suport only picture file!')]);
                $moveResult = move_uploaded_file($fileTmpLoc,$file_path_company_item."/".$fname);
                if ($moveResult == true) {
                    $resized_file_ori = $file_path_company_item."/".$fname;
                    $file_url = $this->service->imageWorkereCommerce($resized_file_ori, null, null);
                    if(isset($_SERVER['HTTPS'])) $link = 'https://';
                    else $link = 'http://';
                    
                    $blog = EComFeed::where('org_id', Auth::user()->account_id)->where('id', $blog_id)->first();
                    if(!$blog->media_urls){
                        $blog->media_urls = $link . \Request::server('HTTP_HOST').'/'.$file_url;
                    }else{
                        $new_gallary = $link . \Request::server('HTTP_HOST').'/'.$file_url;
                        $media_urls = ($blog->media_urls)? $blog->media_urls.','.$new_gallary : $new_gallary;
                        $blog->media_urls = $media_urls;
                    }
                    $blog->save();
                    \DB::connection('company')->commit();
                    $url = '/ecommerce/upload-blog-gallary?type=ed&code='.$this->service->encrypt($blog->id);
                    $view = view('dashboard.activites.activity.blog_gallary_list', compact("blog"))->render();
                    return response()->json(['status' => 'success', 'view' => $view, 'url' => $url, 'blog_id' => $data['code']]);
                }
            }else{
                return response()->json(['status' => trans('greetings.Ooop, Something when wrong, file size must be less than').' '.ini_get('upload_max_filesize')]);
            }
        }catch (\Exception $ex){
            \DB::connection('company')->rollback();
            $this->service->saveErrorLog($ex);
            return response()->json(['status' => $ex->getMessage()]);
        }
    }
    public function StoreBlog(Request $request){
        $data = $request->all();
        try{
            \DB::connection('company')->beginTransaction();
            $blog_id = ($data['code'])? $this->service->decrypt($data['code']) : '';
            $name = ($data['name'])? $data['name'] : '';
            $value = ($data['value'])? trim(html_entity_decode($data['value']), " \t\n\r\0\x0B\xC2\xA0") : '';
            $action_type = "";
            if(!$blog_id){
                $blog = new EComFeed();
                $blog->org_id = Auth::user()->account_id;
                $blog->$name = $value;
                $blog->save();
                $action_type = "New";
            }else{
                $blog = EComFeed::where('org_id', Auth::user()->account_id)->where('id', $blog_id)->first();
                $blog->$name = $value;
                $blog->save();
            }
            \DB::connection('company')->commit();
            return response()->json(['status' => 'success', 
                'blog' => $blog, 
                'blog_id' => $this->service->encrypt($blog->id),
                'action_type' => $action_type
            ]);
        }catch (\Exception $ex){
            \DB::connection('company')->rollback();
            $this->service->saveErrorLog($ex);
            return response()->json(['status' => $ex->getMessage()]);
        }
    }
    public function PubushBlog(Request $request){
        try{
            $data = $request->all();
            \DB::connection('company')->beginTransaction();
            $blog_id = isset($request['code'])? $this->service->decrypt($request['code']) : '';
            $blog = EComFeed::where('org_id', Auth::user()->account_id)
                ->where('id', $blog_id)->first();
            if(!$blog) return response()->json(['status' => 'failed' ,'msg' => trans('greetings.The blog can not be found!')]);
            $validate_field = [];
            if(!trim(html_entity_decode($data['title']), " \t\n\r\0\x0B\xC2\xA0")) array_push($validate_field, ['field_name' => 'title']);
            if(!trim(html_entity_decode($data['description']), " \t\n\r\0\x0B\xC2\xA0")) array_push($validate_field, ['field_name' => 'description']);
            if(count($validate_field) > 0){
                return response()->json([
                    'status' => 'failed', 
                    'msg' => trans('greetings.Please fill in all of the input required!'),
                    'validate_field' => $validate_field]
                );
            }
            $blog->title = trim(html_entity_decode($data['title']), " \t\n\r\0\x0B\xC2\xA0");
            $blog->description = trim(html_entity_decode($data['description']), " \t\n\r\0\x0B\xC2\xA0");
            $blog->status = "Published";
            $blog->published_date = Carbon::now()->toDateString();
            $blog->save();
            \DB::connection('company')->commit();
            $view = view('dashboard/activites/activity/wholesaler_blog_records', compact("blog"))->render();
            return response()->json(['status' => 'success', 'view' => $view, 'blog_id' => $blog_id]);  
        }catch (\Exception $ex){
            \DB::connection('company')->rollback();
            $this->service->saveErrorLog($ex);
            return response()->json(['status' => $ex->getMessage()]);
        }
    }
    public function FetchBlog(Request $request){
        $blog_id = isset($request['code'])? $this->service->decrypt($request['code']) : '';
        $blog = \DB::connection('company')->table('feed')
            ->where('org_id', Auth::user()->account_id)
            ->where('id', $blog_id)->first();
        $view = view('dashboard/activites/activity/wholesaler_blog_records', compact("blog"))->render();
        return response()->json(['status' => 'success', 'view' => $view, 'blog_id'=> $blog_id]);  
    }
    public function DeleteBlog(Request $request){
        \DB::connection('company')->beginTransaction();
        try {
            $blog_id = isset($request['code'])? $this->service->decrypt($request['code']) : '';
            $blog = EComFeed::where('org_id', Auth::user()->account_id)->where('id', $blog_id)->first();
            if(!$blog) return response()->json(['status' => 'failed' , 'msg' => trans('greetings.The blog can not be found!')]);  
            $database_name = config('database.connections.company.database');
            // // =============== CHECK DOCUMENT TYPE =======================
            $folder_name = strtolower(preg_replace('/\s+/', '_', $blog_id));
            // =============== CREATE UPLOAD DIRECTORY ===================
            $file_path = "upload/".$database_name;

            // =============== DELETE EXISTING Cover ==========
            $file_path_blog_cover = $file_path."/blog_cover/".$folder_name;
            \File::deleteDirectory(public_path($file_path_blog_cover));

            // =============== DELETE EXISTING Gallery ==========
            $file_path_blog_gallery = $file_path."/blog_gallery/".$folder_name;
            \File::deleteDirectory(public_path($file_path_blog_gallery));
            $blog->delete();
            \DB::connection('company')->commit();
            return response()->json(['status' => 'success', 'blog' => $blog]);
        }catch (\Exception $ex){
          \DB::connection('company')->rollback();
          $this->service->saveErrorLog($ex);
          return $this->service->webErrorRespoense($ex);
        } 
    }
    public function getProvider($account_id){
        // "com.clearviewerp.ecommerce","kiddeebebe.clearviewerp.com", "hengpiv.clearviewerp.com","royalmart.clearviewerp.com","setra.clearviewerp.com"
        $provider = "com.clearviewerp.ecommerce"; 
        if($account_id == 133) $provider = "royalmart.clearviewerp.com" ; 
        else if($account_id == 169 || $account_id == 178) $provider = "setra.clearviewerp.com"; 
        else if($account_id == 104 ) $provider = "kiddeebebe.clearviewerp.com"; 
        else if ($account_id == 126) $provider = "hengpiv.clearviewerp.com"; 
        return $provider; 
    }
    function addTrackingLine($header, $line)
    {
        try {
            $user = Auth::user();
            $app_setup = $user->app_setup;

            if ($app_setup->ctrl_item_tracking != 'Yes' || !$line->isItemTracking()) {
                return ['status' => 'nocontrol', 'tracking_qty' => 0];
            }

            $item_no = $line->no;
            $uom_code = $line->unit_of_measure;
            $document_type = "Sales ".$header->document_type;
            $document_no = $header->no;
            $line_no = $line->line_no;
            $location_code = $header->location_code;
            $table_name = "ecommerce_sales";
            $line_qty_base = $this->service->toDouble($line->quantity) * $this->service->toDouble($line->qty_per_unit_of_measure);

            $item = Item::where('no', $item_no)->first();

            $item_ledger_entries = ItemLedgerEntry::where('remaining_quantity', '>', 0)->where('item_no', $item_no)->where('location_code', $location_code);
            if ($item->item_tracking_code == 'LOTALL') {
                $item_ledger_entries = $item_ledger_entries->orderBy("expiration_date", "ASC");
            }

            $stock_uom = $item->stock_uom_code ?? "";

            $item_ledger_entries = $item_ledger_entries->get();

            if (count($item_ledger_entries) == 0) {
                return ['status' => 'error', 'msg' => "Item[$item_no] No remaining quantity!"];
            }

            $inventory = $this->checkStock($item,$location_code);
            $qty_allocated = ItemTrackingBuffer::where('item_no', $item_no)
                        ->where('location_code', $location_code)
                        ->where("document_line_no","<>",$line->line_no)
                        ->sum('quantity_to_handle_base');
        
            $avalability = service::toDouble($inventory) - service::toDouble($qty_allocated);

            if($avalability < $line_qty_base) {
               return ['status' => 'error', 'msg' => "No remaining quantity <br/> Remaining Stock is $avalability $stock_uom.<br/> Allocated Qty is $qty_allocated $stock_uom"];
            }

            ItemTrackingBuffer::where('item_no', $item_no)->where('document_line_no', $line_no)
                ->where('location_code', $location_code)->where('document_no', $document_no)->delete();

            foreach ($item_ledger_entries as $item_ledger_entry) {

                if ($item_ledger_entry->remaining_quantity <= $item_ledger_entry->allocated_quantity) {
                    continue;
                }

                if ($item->item_tracking_code == 'SNALL') {
                    $item_buffer = new ItemTrackingBuffer();
                    $item_buffer->table_name = $table_name;
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
                    $item_buffer->quantity_to_handle = 1;
                    $item_buffer->quantity_to_handle_base = 1;
                    $item_buffer->unit_of_measure = $uom_code;
                    $item_buffer->qty_per_unit_of_measure = 1;
                    $item_buffer->save();

                    $line_qty_base = 0;

                } else {
                    $item_buffer = ItemTrackingBuffer::where('item_no', $item_no)
                        ->where('document_no', $document_no)
                        ->where('document_line_no', $line_no)
                        ->where('lot_no', $item_ledger_entry->lot_no)
                        ->where('item_ledger_entry_no', $item_ledger_entry->entry_no)
                        ->where('expiration_date', Carbon::parse($item_ledger_entry->expiration_date)->toDateString())
                        ->first();

                    if ($item_buffer) {
                        if ($line_qty_base >= $this->service->toDouble($item_ledger_entry->remaining_quantity)) {
                            $qty_headle = $this->service->toDouble($item_ledger_entry->remaining_quantity) / $this->service->toDouble($line->qty_per_unit_of_measure);
                            $quantity_to_handle = $this->service->toDouble($qty_headle);
                            $quantity_to_handle_base = $this->service->toDouble($item_ledger_entry->remaining_quantity);
                            $quantity_to_handle_base = $quantity_to_handle_base - $this->service->toDouble($item_ledger_entry->remaining_quantity);
                            // Update Qty
                            $item_buffer->quantity_to_handle = $this->service->toDouble($item_buffer->quantity_to_handle) + $this->service->toDouble($quantity_to_handle);
                            $item_buffer->quantity_to_handle_base = $this->service->toDouble($item_buffer->quantity_to_handle_base) + $this->service->toDouble($quantity_to_handle_base);
                        } else {
                            $qty_headle = $this->service->toDouble($line_qty_base) / $this->service->toDouble($line->qty_per_unit_of_measure);
                            $quantity_to_handle = $this->service->toDouble($qty_headle);
                            $quantity_to_handle_base = $this->service->toDouble($line_qty_base);
                            // Update Qty
                            $item_buffer->quantity_to_handle = $this->service->toDouble($item_buffer->quantity_to_handle) + $this->service->toDouble($quantity_to_handle);
                            $item_buffer->quantity_to_handle_base = $this->service->toDouble($item_buffer->quantity_to_handle_base) + $this->service->toDouble($quantity_to_handle_base);
                            $quantity_to_handle_base = 0;
                        }
                        $item_buffer->save();
                    } else {
                        $item_buffer = new ItemTrackingBuffer();
                        $item_buffer->table_name = $table_name;
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
                        if ($line_qty_base >= $this->service->toDouble($item_ledger_entry->remaining_quantity)) {
                            $qty_headle = $this->service->toDouble($item_ledger_entry->remaining_quantity) / $this->service->toDouble($line->qty_per_unit_of_measure);
                            $item_buffer->quantity_to_handle = $this->service->toDouble($qty_headle);
                            $item_buffer->quantity_to_handle_base = $this->service->toDouble($item_ledger_entry->remaining_quantity);
                            $line_qty_base = $line_qty_base - $this->service->toDouble($item_ledger_entry->remaining_quantity);
                        } else {
                            $qty_headle = $this->service->toDouble($line_qty_base) / $this->service->toDouble($line->qty_per_unit_of_measure);
                            $item_buffer->quantity_to_handle = $this->service->toDouble($qty_headle);
                            $item_buffer->quantity_to_handle_base = $this->service->toDouble($line_qty_base);
                            $line_qty_base = 0;
                        }

                        $item_buffer->unit_of_measure = $uom_code;
                        $item_buffer->qty_per_unit_of_measure = $this->service->toDouble($line->qty_per_unit_of_measure);
                        $item_buffer->save();
                    }
                }

                if ($line_qty_base <= 0) break;
            }

            $tracking_qty = $this->getTrackingQtyBySaleLine($item_buffer->item_no, $item_buffer->document_no, $item_buffer->document_line_no);
            \DB::connection('company')->commit();

            return ['status' => 'success', 'tracking_qty' => $tracking_qty];
        } catch (\Exception $ex) {
            \DB::connection('company')->rollback();
            return ['status' => 'failed','msg' => $ex->getMessage(), 'line' => $ex->getLine() ];
        }
    }
    function checkStock($item,$location_code) {
        $inventory = ItemLedgerEntry::where('location_code', $location_code)->where('item_no', $item->no)->sum('quantity');
        return service::toDouble($inventory);
    }
    public function getTrackingQtyBySaleLine($item_no, $document_no, $line_no)
    {
        $qty = ItemTrackingBuffer::where('item_no', $item_no)
            ->where('document_line_no', $line_no)
            ->where('document_no', $document_no)->sum('quantity_to_handle');

        return service::custome_number_formattor($qty, 'quantity');
    }

   
}
