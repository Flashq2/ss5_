    <div class="modal-dialog modal-dialog-centered modal-xl">
      <div class="modal-content">
        <div class="modal-header">
          <h1 class="modal-title fs-5" id="exampleModalLabel">{{(__('pos.payment'))}}</h1>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body modal-body-color">
          
            <section>
              
              <form   id="form_payment" method="POST">
                @csrf
                <div class="d-flex justify-content-between align-items-center mb-5  ">
                  <div class="d-flex flex-row align-items-center">
                    <h4 class="text-uppercase mt-1">SS5 Small POS</h4>
                    <span class="ms-2 me-3">Pay</span>
                  </div>
                </div>
              
                <div class="row">
                  <div class="col-md-8 col-lg-8 col-xl-8 mb-8 mb-md-0">
                    <div class="row">
                <div class="col-6">
                    <div class="p-2 d-flex justify-content-between align-items-center" style="background-color: #eee;">
                        <span>Subtotal</span>
                        <span class="payprice">{{\App\Helpers\GlobalFunction::numberFormate($subtotal,'amount')}}</span>
                      </div>
                </div>
                  <div class="col-6"><div class="p-2 d-flex justify-content-between align-items-center" style="background-color: #eee;">
                    <span>Total</span>
                    <span class="paydes" id="paydes">{{\App\Helpers\GlobalFunction::numberFormate($amount,'amount')}}</span>
                  </div></div>
                  <div class="col-6">
                     <div class="p-2 d-flex justify-content-between align-items-center" style="background-color: #eee;">
                        <span>{{(__('pos.totalitem'))}}</span>
                        <span class="payitem">{{\App\Helpers\GlobalFunction::numberFormate($item,'quantity')}}</span>
                      </div> 
                  </div>
                  <div class="col-6">
                    <div class="p-2 d-flex justify-content-between align-items-center" style="background-color: #eee;">
                        <span>Total Reil</span>
                        <span >{{\App\Helpers\GlobalFunction::numberFormate($total_reil,'amount')}}</span>
                      </div> 
                  </div>
                  <div class="col-6">
                    <div class="mb-3">
                        <label for="exampleInputEmail1" class="form-label">{{(__('pos.currencycode'))}}</label>
                        <select name="currency_code" id="currency_code" class="form-control">
                          <option value="dollar">Dollar</option>
                          <option value="reil">រៀល</option>
                        </select>
                      </div>
                  </div>
                  <div class="col-6">
                    <div class="mb-3">
                      <label  class="form-label">{{(__('pos.invoiceno'))}}</label>
                      <div class="input-group flex-nowrap">
                        <span class="input-group-text" id="addon-wrapping">#</span>
                        <input type="text" class="form-control" id="no" name="no" aria-describedby="addon-wrapping" readonly value="{{$document}}">
                      </div>
                      </div>
                  </div>
                  <div class="col-6">
                    <div class="mb-3">
                        <label for="exampleInputEmail1" class="form-label">{{(__('pos.amount'))}}</label>
                        <input type="text" class="form-control" id="total_payment" aria-describedby="emailHelp" name="total_payment" autocomplete="off" value="{{\App\Helpers\GlobalFunction::numberFormate($amount,'amount')}}" required >
                      </div>
                  </div>
                  <div class="col-6">
                    <div class="mb-3">
                        <label for="exampleInputEmail1" class="form-label">Sale Person Code</label>
                        <input type="text" class="form-control" id="salesperson_code" aria-describedby="emailHelp" name="salesperson_code" value="{{Auth::user()->email}}" autocomplete="off">
                      </div>
                  </div>
                  <div class="col-6">
                    <label for="exampleInputEmail1" class="form-label">{{(__('pos.payby'))}}</label>
                    <select name="currency_factor" id="currency_factor" class="form-control">
                            <option value="Cash">{{(__('pos.cash'))}}</option>
                            <option value="Check">{{(__('pos.check'))}}</option>
                            <option value="Credit Card">{{(__('pos.credit'))}}</option>
                    </select>
                  </div>
                  <div class="col-6">
                    <label for="exampleInputEmail1" class="form-label">Document Type</label>
                    <select name="document_type" id="document_type" class="form-control">
                            <option value="Invoice">Invoice</option>
                            <option value="Cr">Cr</option>
                             
                    </select>
                  </div>
                  <div class="col-6">
                    <div class="mb-3">
                      <label for="exampleInputEmail1" class="form-label">Curency Code</label>
                      <input type="text" class="form-control" id="currenccy_khr" aria-describedby="emailHelp" name="currenccy_khr" value="{{$currenccy_khr}}" autocomplete="off">
                    </div>
                  </div>
                  <div class="col-12">
                    <label class="form-label" for="textAreaExample">{{(__('pos.remark'))}}</label>
                        <textarea class="form-control"  rows="8" cols="12" id="remark" name="external_document_no"></textarea>
                  </div>
                    </div>
                  
                  </div>
                  <div class="col-md-5 col-lg-4 col-xl-4 offset-lg-1 offset-xl-2">
                  </div>
                </div>
              </form>
              </section>
        </div>
        <div class="modal-footer"> 
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">{{(__('pos.close'))}}</button>
          <button type="button" class="btn btn-primary masteradd"> <span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>{{(__('pos.payment'))}}
            </button> 
         
        </div>
      </div>
    </div>