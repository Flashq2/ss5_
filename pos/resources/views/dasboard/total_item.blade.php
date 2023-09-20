    <div class="card">
        <div class="card-body p-3 position-relative">
            <div class="row">
                <div class="col-7 text-start">
                    <p class="text-sm mb-1 text-capitalize font-weight-bold">Total Item Sales</p>
                    <h5 class="font-weight-bolder mb-0">
                     {{ $total_itemSales ?? " "}}
                    </h5>
                    <span class="text-sm text-end text-success font-weight-bolder mt-auto mb-0">+55%
                        <span class="font-weight-normal text-secondary">since last month</span></span>
                </div>
                <div class="col-5">
                    <div class="dropdown text-end">
                        <a href="javascript:;" class="cursor-pointer text-secondary"
                            id="dropdownUsers1" data-bs-toggle="dropdown" aria-expanded="false">
                            <span class="text-xs text-secondary">{{$stating_date}} -  {{$ending_date}}</span>
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end px-2 py-3"
                            aria-labelledby="dropdownUsers1">
                            <li><a class="dropdown-item border-radius-md" href="javascript:;">Last 7
                                    days</a></li>
                            <li><a class="dropdown-item border-radius-md" href="javascript:;">Last
                                    week</a></li>
                            <li><a class="dropdown-item border-radius-md" href="javascript:;">Last 30
                                    days</a></li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>