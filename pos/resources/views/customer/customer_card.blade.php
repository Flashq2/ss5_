<!DOCTYPE html>
<html lang="en">
<head>
    @include('layouts.header')
</head>
<style>
    .title::first-letter{
        text-transform: capitalize;
    }
</style>
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
                    <h1>Customer</h1>
                    <hr>
                    <div class="col-12">
                        <div class="row">
                            <div class="col-6">
                                <div class="row">
                                    <div class="col-2">
                                        <a href="{{ url('customer/submitform') }}">
                                        <button class="action">
                                             Action
                                           </button>
                                        </a>
                                    </div>
                                @if (isset($_GET['code']))
                                    <?php
                                      $url='updatecusotmer';
                                      $header=App\Models\CustomerModel::select('*')->where('no', $_GET['code'])->first();
                                       ?>
                                @else
                                <?php
                                   $url= 'submitform';
                                ?>
                                @endif
                                </div>
                            </div>
                            <div class="col-6">
                            </div>
                        </div>
                    </div>
                </div>
                <hr>
                @include('layouts.form')

            </main>

        </div>
    </div>
</body>
<script>
    $(document).ready(function () {
        
        $('#userrole').select2();
        $('#permission').select2();
        $('#inactived').select2();
        $('document').on('submit',function(e){
            e.preventDefault();
        })
    });
</script>
</html>
