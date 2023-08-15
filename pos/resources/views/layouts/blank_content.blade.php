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
                    <h1>User</h1>
                    <hr>
                    <div class="col-12">
                        <div class="row">
                            <div class="col-6">
                                <div class="row">
                                    <div class="col-2">
                                     <a href="{{url('user/newrecord')}}"> <button class="action"> Action  
                                        </button></a> 
                                    </div>
                                    
                                </div>
                            </div>
                            <div class="col-6">
                            </div>
                        </div>
                    </div>
                </div>
                <hr>
                <div class="row">
                    <table id="user" class="table" style="width:100%">
                        <thead>
                            <th></th>
                            <th>Name</th>
                            <th>Email</th>
                            <th>Sale_Persion</th>
                            <th>Gender</th>
                            <th>Phone_No</th>
                            <th>Permision_Code</th>
                            <th>User Role Code</th>
                            <th>Address</th>
                        </thead>
                        <tbody>
                        </tbody>
                    </table>
                </div>
            </main>

        </div>
    </div>
</body>
@include('script');
 
</html>
