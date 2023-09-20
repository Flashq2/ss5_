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
    <link href="https://fonts.googleapis.com/css2?family=Battambang:wght@300;400&family=Crimson+Text&display=swap"
        rel="stylesheet">
    <script src="https://code.jquery.com/jquery-3.7.0.js" integrity="sha256-JlqSTELeR4TLqP0OG9dxM7yDPqX1ox/HfgiSLBj8+kM="
        crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/js/bootstrap.bundle.min.js"
        integrity="sha384-ENjdO4Dr2bkBIFxQpeoTz1HIcje39Wm4jDKdf19U8gI4ddQ3GYNS7NTKfAdVQSZe" crossorigin="anonymous">
    </script>
    <link href="https://cdn.jsdelivr.net/npm/summernote@0.8.18/dist/summernote-lite.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/summernote@0.8.18/dist/summernote-lite.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>
<style>
    @media print {
        .print-this {
            display: none !important;
        }
    }
    .sid-left{
        height: 700px !important;
        overflow: auto;
    }
    ::-webkit-scrollbar {
    width: 2px;
}

/* Track */
::-webkit-scrollbar-track {
  box-shadow: inset 0 0 5px grey; 
  border-radius: 1px;
}
 
/* Handle */
::-webkit-scrollbar-thumb {
  background: rgba(179, 179, 207, 0); 
  border-radius: 1px;
}

/* Handle on hover */
::-webkit-scrollbar-thumb:hover {
  background: #b30000; 
}
</style>

<body class="pace-top" onload="startTime()">

    {{-- <div id="loader" class="app-loader">
        <span class="spinner"></span>
    </div> --}}
    <hr>
    <!-- Button trigger modal -->
 
  
                    <!-- Modal -->
                    <div class="modal fade" id="exampleModal" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
                        <div class="modal-dialog">
                        <div class="modal-content">
                            <div class="modal-header">
                            <h5 class="modal-title" id="exampleModalLabel">Modal title</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                            </div>
                            <div class="modal-body">
                                Do you want to logout from system ?
                                <form action="/pos/logout">
                                    <button type="submit" class="btn btn-primary">Yes</button>
                                </form>
                            </div>
                            <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                            <button type="submit" class="btn btn-primary">Yes</button>
                            </div>
                        </div>
                        </div>
  </div>
    <div class="container-fluid">
        <div class="row" >
            <div class="col-1">
                <h5 id="time"></h5>
            </div>
            <div class="col-2">
                <input type="text" id="search" name='search' class="form-control" placeholder="Search Product here">
            </div>
            <div class="col-2">
                <input type="text" class="form-control" name="barcode" id="barcode" placeholder="Barcode">
            </div>
            <div class="col-1">
               <a href="{{route('barcode.index')}}"> <button class="btn btn-primary ">Create Barcode</button></a>
            </div>
            <div class="col-1">
                <button class="btn btn-primary ">Sales Report</button>
            </div>

            <div class="col-1">
                <button class="btn btn-warning "data-bs-toggle="modal" data-bs-target="#hold">Hold Invoice</button>
            </div>

            <div class="col-1">
                <button class="btn btn-danger" data-bs-toggle="modal" data-bs-target="#exampleModal">Log Out</button>
            </div>
            <div class="col-2">

                <select name="customer" id="customer" style="width:100%;">
                    <option value="1">Pleas select customer</option>
                    @foreach ($customer as $customers)
                        <option value="{{ $customers->no }}">{{ $customers->name }}</option>
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
                        <div data-scrollbar="true" data-height="100%" data-skip-mobile="true" class="sid-left">
                            @include('pos.pos_category')
                        </div>
                    </div>
                </div>
                <div class="pos-content">
                    <div class="col-10 category_cview">
                        @include('pos.pos_action')

                    </div>
                </div>
                <div class="pos-sidebar">
                    @include('pos.pos_list')
                </div>

            </div>
            <a href="#" class="pos-mobile-sidebar-toggler" data-toggle-class="pos-sidebar-mobile-toggled"
                data-target="#pos">
                <i class="iconify display-6" data-icon="solar:bag-smile-bold-duotone"></i>
                <span class="badge">5</span>
            </a>

        </div>
    </div>
  
    <div class="modal fade" id="payment" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
    </div>

    @include('layouts.loading')
    @include('pos.pos_hold_card')
    <footer class="foot-print">
        <div class="print" style="display: none">
            <div class="print-this">

            </div>
        </div>
    </footer>
    @include('pos.multi_option')
    @include('pos_script_link')
    <script>
        function startTime() {
            const date = new Date();
            let secs = date.getSeconds(); // Store seconds in secs variable
            let mints = date.getMinutes(); //  Minutes are stored in mints variable
            let hrs = date.getHours(); //  variable hrs stores the number of hours
            secs = Time(secs);
            mints = Time(mints);
            document.getElementById('time').innerHTML = hrs + ":" + mints + ":" + secs;
            setTimeout(startTime, 500);
        }

        function Time(i) {
            if (i < 10) {
                i = "0" + i
            }; // add zero in front of numbers < 10
            return i;
        }
 
    </script>
    <script>
        $(document).ready(function() {
            $(document).bind("keyup keydown", function (e) {
            if (e.ctrlKey && e.keyCode == 80) {
                setTimeout(function () { CallAfterWindowLoad();}, 5000);
                return true;
            }
        });
       
            $('.sty_loader').hide();
            $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                }
            });
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
                $('#descount').select2();
                $('#customer').select2();
                $('.uom').select2();
                $('#remark').summernote();

            // Action on Pos Page
            $(document).on('click', '.add_card', function(e) {
                let line_index = $(this).data("line_id");
                let product_code = $(this).data('code');
                let uom = $(this).data('uom');
                let price = $(this).data('price');
                let customer = $("#customer").val();
                let value = $('.cr_value').text();
                let data = {
                    product_code: product_code,
                    customer: customer,
                    value: value,
                    uom: uom,
                    price: price
                }
                $.ajax({
                    type: "GET",
                    url: "/pos/additem",
                    data: data,
                    success: function(response) {
                        if (response.status == "warning") {
                            toastr.error(response.message, "Error");
                        } else {
                            $('.pos-sidebar').html("");
                            $('.pos-sidebar').html(response.view);
                            $('#descount').select2();
                            $('#customer').select2();
                            $('.uom').select2();
                        }
                    }
                });
            });
            $(document).on('change', '.uom ,.qty,.des', function(e) {
                let code = $(this).data("line_id");
                let document_no = $(this).data('document_no');
                let uom = $('body').find(`#uom_${code}`).val();
                let qty = $(`#qty_${code}`).val();
                let des = $(`#des_${code}`).val();
                let item_no = $(`#item_${code}`).text();
                console.log(des);
                let data = {
                    'uom': uom,
                    'qty': qty,
                    'des': des,
                    'code': code,
                    'item_no': item_no,
                    'document_no' : document_no
                }

                $.ajax({
                    type: "GET",
                    url: "/pos/updateLine",
                    data: data,
                    success: function(response) {
                        if (response.status == "warning") {
                            toastr.error(response.message, "Error");
                        } else {
                            $(`tr#${code}`).replaceWith(response.view);
                            $('#descount').select2();
                            $('#customer').select2();   
                            $('.uom').select2(); 
                            $('.pos-sidebar-footer').html("");
                             $('.pos-sidebar-footer').html(response.view_total);
                            toastr.success(response.message, "Success");
                        }
                    }
                });
            })
            $(document).on('click', '.order', function() {
                $('.pos-sidebar').css({
                    "width": "1720px"
                });
            });
            $(document).on('click', '.remove', function() {
                let code = $(this).data("line_id");
                $(this).closest("tr").remove();
                let data = {
                    code: code,
                }
                $.ajax({
                    type: "get",
                    url: "/pos/deleteLine",
                    data: data,
                    success: function(response) {
                        $('.pos-sidebar-footer').html("");
                        $('.pos-sidebar-footer').html(response.view);
                        toastr.success(response.message, "Success");
                    }
                });
            });
            $(document).on('click', '#submit_order', function() {
                let value = $('.cr_value').text();
                let document = $(this).data("document");
                let data = {
                    document: document,
                    value: value
                }
                $.ajax({
                    type: "GET",
                    url: "/pos/getmodalPayment",
                    data: data,
                    success: function(response) {
                        $('#payment').html(response.view);
                        $('#payby').select2();
                        $('#currency_code').select2();
                        $('#doc').select2();
                        $('#remark').summernote();
                        $('#payment').modal('show');

                    }
                });
            });
            $(document).on('click', '.masteradd', function(e) {
                $.ajax({
                    type: "POST",
                    url: "/pos/sumit_payment",
                    data: $(document).find('#form_payment').serialize(),
                    success: function(response) {
                        if (response.status == "warning") {
                            toastr.error(response.message, "Error");
                        } else {
                            $('.print-this').html(response.view);
                            $('.print-this').printThis();
                            $('#payment').modal('hide');
                            $('.pos-sidebar').html("");
                            $('.pos-sidebar').html(response.view_card);
                        }
                    }
                });
            });
            $(document).on('click','.nav-link',function(e){
                let code = $(this).data("filter");
                $('.nav-link').removeClass('active');
                $(this).addClass('active');
                let data ={
                    code :code,
                }
                $.ajax({
                    type: "GET",
                    url: "/pos/get_category",
                    data: data,
                    success: function (response) {
                        if(response.status = "success"){
                            $('.category_cview').html("");
                            $('.category_cview').html(response.view);
                        }
                    }
                });
            })
            $(document).on('keyup','#search',function(e){
                let code = $(this).val();
                let data ={
                    code :code,
                }
                $.ajax({
                    type: "GET",
                    url: "/pos/search",
                    data: data,
                    beforeSend:function(){
                        $('.sty_loader').show();
                    },
                    success: function (response) {
                        if(response.status = "success"){
                            $('.sty_loader').hide();
                            $('.category_cview').html("");
                            $('.category_cview').html(response.view);
                        }
                    }
                });
            })
            $(document).on('keyup','#barcode',function(e){
                let product_code = $(this).val();
                let uom = "";
                let customer = $("#customer").val();
                let value = $('.cr_value').text();
                let data = {
                    product_code: product_code,
                    customer: customer,
                    value: value,
                    uom: uom,
                }
                $.ajax({
                    type: "GET",
                    url: "/pos/additem",
                    data: data,
                    success: function(response) {
                        if (response.status == "warning") {
                            // $('#barcode').val("");
                            // toastr.error(response.message, "Error");
                        } else {
                            $('#barcode').val("");
                            $('.pos-sidebar').html("");
                            $('.pos-sidebar').html(response.view);
                            $('#descount').select2();
                            $('#customer').select2();
                            $('.uom').select2();
                        }
                    }
                });
            });
            $(document).on('click', '.pagination a', function(event){
                event.preventDefault(); 
                var page = $(this).attr('href').split('page=')[1];
                $.ajax({
                    url:"/pos/ajaxPagination?page="+page,
                    success:function(data){
                        $('.category_cview').html("");
                        $('.category_cview').html(data.view);
                    }
 
                    });
                });
            $(document).on('click','.on_hold',function(e){
                let rowCount = $('#table tr').length;
                if(rowCount == 0) {
                    toastr.warning("Nothing to hold", "warning");
                    return; 
                }
                let code = $(this).data('document_no');
                let refer = $('#refer').val();
                $.ajax({
                    type:'GET',
                    url: "/pos/hold",
                    data: {
                        code : code,
                        refer : refer,
                    },
                  success: function (response) {
                    if(response.status == 'success')   {
                        window.location.href = window.location.href;
                    }
                    }
                });
            });
                $(document).on('click','#option_w',function(e){
                    $('#option').modal('show');
                })
        });
    </script>
</body>

</html>
