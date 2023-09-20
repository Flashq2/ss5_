<!DOCTYPE html>
<html lang="en">

<head>
    @include('layouts.header')
</head>

<body>
    <div class="wrapper">
        @include('layouts.side_left')

        <div class="main">
            <nav class="navbar navbar-expand navbar-light navbar-bg">
                <a class="sidebar-toggle js-sidebar-toggle">
                    <i class="hamburger align-self-center"></i>
                </a>


            </nav>

            <main class="content">
                <div class="row">
                    <h1>Create Barcode</h1>
                    
                </div>
                <hr>
                <div class="row">
                    <div class="col-lg-8">
                        @include('layouts.form_in_card');   
                    </div>
                </div>
            </main>

        </div>
    </div>
</body>
@include('script');
 <script>
    $(document).ready(function () {
        $(document).on('click','.submit',function(e){
            $.ajax({
                url: "/barcode/barcode_create",
                data: $(document).find('#item_form').serialize(),
                success: function (response) {
                    
                }
            });
        })
    });
 </script>
</html>
