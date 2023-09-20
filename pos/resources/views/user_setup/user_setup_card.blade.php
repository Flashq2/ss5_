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
                    {{-- <h1>User</h1> --}}
                    <hr>
                    <div class="col-12">
                        <div class="row">
                            <div class="col-6">
                                <div class="row">
                                   <div class="col-12">
                                    <a href="{{ url('user/newrecord') }}"> <button class="action" style="background-color: #4e8bad"> Save
                                    </button></a>
                                    <a href="{{ url('user/newrecord') }}"> <button class="action"> Save & New
                                    </button></a>
                                    <a href="{{ url('user/newrecord') }}"> <button class="action" style="background-color: #10b3b"> Delete
                                    </button></a>
                                    <a href="{{ url('user/newrecord') }}"> <button class="action" style="background-color: #10b3b"> Inactived
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

                </div>
            </main>

        </div>
    </div>
</body>
@include('script');

</html>
