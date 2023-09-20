<div class="d-flex align-items-center mb-2">
    <div>Subtotal</div>
    <div class="flex-1 text-end h6 mb-0 subtotal">
        {{ \App\Helpers\GlobalFunction::numberFormate($subtotal ?? ' 0', 'amount') }}</div>
</div>
<div class="d-flex align-items-center">
    <div>Discount Amount </div>
    <div class="flex-1 text-end h6 mb-0 descount">
        {{ \App\Helpers\GlobalFunction::numberFormate($discount_amont ?? ' 0', 'amount') }}</div>
</div>
<hr class="opacity-1 my-10px">
<div class="d-flex align-items-center mb-2">
    <div>Total Include VAT (Dollar)</div>
    <div class="flex-1 text-end h4 mb-0 total"> 
        {{ \App\Helpers\GlobalFunction::numberFormate($amount ?? ' 0', 'amount') }}</div>
</div>
<div class="d-flex align-items-center mb-2">
    <div>Total (Riel)</div>
    <div class="flex-1 text-end h4 mb-0 total_kh">
        {{ \App\Helpers\GlobalFunction::numberFormate($total_reil ?? ' 0', 'amount') }}</div>
</div>
<div class="d-flex align-items-center mt-3">
    <a href="#" class="btn btn-danger rounded-3 text-center me-10px w-70px"><i
            class="fa-regular fa-trash-can d-block fs-18px my-1"  data-document={{ $document_no ?? ' ' }}></i>Clear </a>
    <a href="#" class="btn btn-primary rounded-3 text-center me-10px w-70px"><i
            class="fa fa-receipt d-block fs-18px my-1"></i>Invoice</a>
    <a href="#" class="btn btn-primary rounded-3 text-center me-10px w-70px" id="option_w"><i
            class="fa-regular fa-hard-drive d-block fs-18px my-1"></i></i>Function</a>
    <a href="#" class="btn btn-theme rounded-3 text-center flex-1" id="submit_order"
        data-document={{ $document_no ?? ''}}><i class="fa fa-shopping-cart d-block fs-18px my-1 "></i> Submit
        Order</a>
</div>