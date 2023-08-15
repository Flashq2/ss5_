<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8" />
    <title>SS5 POS</title>
    <meta content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no" name="viewport" />
    <meta content name="description" />
    <meta content name="author" />

    <link href="{{ asset('css/pos.css') }}" rel="stylesheet" />
    <link href="{{ asset('css/pos_min.css') }}" rel="stylesheet" />
    <link href="{{ asset('css/config.css') }}" rel="stylesheet" />
    <script src="https://kit.fontawesome.com/bca9825c0c.js" crossorigin="anonymous"></script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Battambang:wght@300;400&display=swap" rel="stylesheet">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Battambang:wght@300;400&family=Crimson+Text&display=swap" rel="stylesheet">
</head>

<body class="pace-top" onload="startTime()">

    {{-- <div id="loader" class="app-loader">
        <span class="spinner"></span>
    </div> --}}
<hr>
    <div class="container-fluid">
        <div class="row">
            <div class="col-1">
                <h5  id="time"></h5>
            </div>
            <div class="col-2">
                <input type="text" class="form-control" placeholder="Search Product here">
            </div>
            <div class="col-2">
                <input type="text" class="form-control" placeholder="Barcode">
            </div>
            <div class="col-1">
                <button class="btn btn-primary ">Create Barcode</button>
            </div>
            <div class="col-1">
                <button class="btn btn-primary ">Sales Report</button>
            </div>
       
            <div class="col-1">
                <button class="btn btn-warning ">Hold Invoice</button>
            </div>
           
            <div class="col-1">
                <button class="btn btn-danger ">Log Out</button>
            </div>
            <div class="col-2">
               
                <select name="customer" id="customer" style="width:100%;" >
                    <option value="1">Pleas select customer</option>
                    @foreach ($customer as $customers)
                    <option value="{{$customers->no}}">{{$customers->name}}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-1">
                <button class="btn btn-primary ">New Customer</button>
            </div>
        </div>
    </div>
    <hr>
    <div id="app" class="app app-content-full-height app-without-sidebar app-without-header">

        <div id="content" class="app-content p-0">

            <div class="pos pos-with-menu pos-with-sidebar" id="pos">

                <div class="pos-menu">
                    <div class="logo">
                        <a href="index_v3.html">
                            <div class="logo-img"><i class="fa fa-bowl-rice"></i></div>
                            <div class="logo-text">Pine & Dine</div>
                        </a>
                    </div>
                    <div class="nav-container">
                        <div data-scrollbar="true" data-height="100%" data-skip-mobile="true">
                           @include('pos.pos_category')
                        </div>
                    </div>
                </div>


                <div class="pos-content">
                <div class="col-10">
                    @include('pos.pos_action')

                </div>
                </div>
                <div class="pos-sidebar">
                    <div class="h-100 d-flex flex-column p-0">
                        <div class="pos-sidebar-header">
                            <div class="back-btn">
                                <button type="button" data-dismiss-class="pos-sidebar-mobile-toggled"
                                    data-target="#pos" class="btn border-0">
                                    <i class="fa fa-chevron-left"></i>
                                </button>
                            </div>
                            <div class="icon"><i class="fa fa-plate-wheat"></i></div>
                            <div class="title">Table 01</div>
                            <div class="order">Invoice: <b>INE-1</b></div>
                        </div>
                        <div class="pos-sidebar-header" style="background: rgb(255, 255, 255);color:black;">
                            <div class="back-btn">
                                <button type="button" data-dismiss-class="pos-sidebar-mobile-toggled"
                                    data-target="#pos" class="btn border-0">
                                    <i class="fa fa-chevron-left"></i>
                                </button>
                            </div>
                            <div class="icon"><i class="fa-regular fa-money-bill-1"></i></div>
                            <div class="title">Exchange Rate(1$) : {{$currenccy_khr}} KHR
                             
                            </div>

                            <div class="order">Invoice: <b>INE-1</b></div>
                        </div>
                        <div class="pos-sidebar-nav">
                            <ul class="nav nav-tabs nav-fill">
                                <li class="nav-item">
                                    <a class="nav-link active" href="#" data-bs-toggle="tab"
                                        data-bs-target="#newOrderTab">New Order (5)</a>
                                </li>
   
                            </ul>
                        </div>
                        <div class="pos-sidebar-body tab-content" data-scrollbar="true" data-height="100%">
                            <div class="tab-pane fade h-100 show active" id="newOrderTab">
                                <div class="pos-table">
                            <table class="table">
                                <thead>
                                    <tr>
                                        <th></th>
                                        <th>CODE</th>
                                        <th>DESCRIPTION</th>
                                        <th  style="width: 100px;">UOM</th>
                                        <th>PRICE</th>
                                        <th>QTY</th>
                                        <th>DES % </th>
                                        <th>AMOUNT</th>
                                    </tr>
                                    <tbody>
                                        <tr>    
                                            <td>
                                                <p class="respon3"> <i class="fa-regular fa-trash-can" style="color: #cf204c;"></i></p>    
                                            </td>
                                            <td> <p class="respon3">120</p></td>
                                            <td><p class="respon3">120</p></td>
                                            <td>
                                                <select name="uom" id="uom" style="width: 100px;">
                                                    <option value="">&nbsp;</option>
                                                    <option value="">unit</option>
                                                    <option value="">unit</option>
                                                    <option value="">unit</option>
                                                </select>
                                            </td>
                                            <td><p class="respon3">120</p></td>
                                            <td>
                                                <input type="number" class="form-control respone2" style="width: 100px;" >
                                            </td>
                                            <td>
                                                <input type="text" class="form-control respone2" style="width: 100px;" placeholder=" % or $">
                                               
                                        </td>
                                            <td><p class="respon3">120</p></td>
                                        </tr>
                                    </tbody>
                                </thead>
                            </table>
                                </div>
                            </div>
                            <div class="tab-pane fade h-100" id="orderHistoryTab">
                                <div class="h-100 d-flex align-items-center justify-content-center text-center p-20">
                                    <div>
                                        <div class="mb-3 mt-n5">
                                            <svg width="6em" height="6em" viewBox="0 0 16 16"
                                                class="text-gray-300" fill="currentColor"
                                                xmlns="http://www.w3.org/2000/svg">
                                                <path fill-rule="evenodd"
                                                    d="M14 5H2v9a1 1 0 0 0 1 1h10a1 1 0 0 0 1-1V5zM1 4v10a2 2 0 0 0 2 2h10a2 2 0 0 0 2-2V4H1z" />
                                                <path
                                                    d="M8 1.5A2.5 2.5 0 0 0 5.5 4h-1a3.5 3.5 0 1 1 7 0h-1A2.5 2.5 0 0 0 8 1.5z" />
                                            </svg>
                                        </div>
                                        <h4>No order history found</h4>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="pos-sidebar-footer">
                            <div class="d-flex align-items-center mb-2">
                                <div>Subtotal</div>
                                <div class="flex-1 text-end h6 mb-0">$30.98</div>
                            </div>
                            <div class="d-flex align-items-center">
                                <div>Discount (6%)</div>
                                <div class="flex-1 text-end h6 mb-0">$2.12</div>
                            </div>
                            <div class="d-flex align-items-center">
                                <div>Taxes (10%)</div>
                                <div class="flex-1 text-end h6 mb-0">$2.12</div>
                            </div>
                            <hr class="opacity-1 my-10px">
                            <div class="d-flex align-items-center mb-2">
                                <div>Total (Dollar)</div>
                                <div class="flex-1 text-end h4 mb-0">$33.10</div>
                            </div>
                            <div class="d-flex align-items-center mb-2">
                                <div>Total (Riel)</div>
                                <div class="flex-1 text-end h4 mb-0">14000</div>
                            </div>
                            <div class="d-flex align-items-center mt-3">
                                <a href="#" class="btn btn-danger rounded-3 text-center me-10px w-70px"><i
                                        class="fa-regular fa-trash-can d-block fs-18px my-1"></i>Clear </a>
                                <a href="#" class="btn btn-primary rounded-3 text-center me-10px w-70px"><i
                                        class="fa fa-receipt d-block fs-18px my-1"></i>Invoice</a>
                                <a href="#" class="btn btn-theme rounded-3 text-center flex-1"><i
                                        class="fa fa-shopping-cart d-block fs-18px my-1"></i> Submit Order</a>
                            </div>
                        </div>
                    </div>
                </div>

            </div>


            <a href="#" class="pos-mobile-sidebar-toggler" data-toggle-class="pos-sidebar-mobile-toggled"
                data-target="#pos">
                <i class="iconify display-6" data-icon="solar:bag-smile-bold-duotone"></i>
                <span class="badge">5</span>
            </a>

        </div>
    </div>
    @include('pos_script_link')
<script>
    
    function startTime() {
            const date = new Date();
            let secs = date.getSeconds();   // Store seconds in secs variable
            let mints = date.getMinutes(); //  Minutes are stored in mints variable
            let hrs = date.getHours();  //  variable hrs stores the number of hours
            secs = Time(secs);
            mints = Time(mints);
            document.getElementById('time').innerHTML =  hrs + ":" + mints + ":" + secs;
            setTimeout(startTime, 500);
            }
            function Time(i) {
            if (i <10) {i = "0" + i};  // add zero in front of numbers < 10
            return i;
            }
</script>
<script>
    $(document).ready(function () {
        toastr.options = {
            "closeButton": true,
            "debug": false,
            "newestOnTop": true,
            "progressBar": true,
            "positionClass": "toast-bottom-left",
            "preventDuplicates": false,
            "onclick": null,
            "showDuration": "300",
            "hideDuration": "1000",
            "timeOut": "5000",
            "extendedTimeOut": "1000",
            "showEasing": "swing",
            "hideEasing": "linear",
            "showMethod": "fadeIn",
            "hideMethod": "fadeOut"
            }
        $('#uom').select2({
            tags: true,
        });
        $('#descount').select2();
        $('#customer').select2();
    // Action on Pos Page
    $(document).on('click','.add_card',function(e){
        let product_code = $(this).data('code');
        let customer = $("#customer").val()
        let data = {
            product_code:product_code,
            customer:customer
        }
        $.ajax({
            type: "GET",
            url: "/pos/additem",
            data:data,
            success: function (response) {
                if(response.status =="warning"){
                    toastr.error(response.message,"Error");
                }
            }
        });
    })
    //End Action on PO 
    // Work with function in javascript
    function addItem(ctrl){
        alert("This function is work")
    }
    });
</script>
</body>

</html>
