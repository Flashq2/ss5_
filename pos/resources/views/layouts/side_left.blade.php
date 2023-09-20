{{-- <nav id="sidebar" class="sidebar js-sidebar">
    <div class="sidebar-content js-simplebar">
        <a class="sidebar-brand" href="index.html">
            <span class="align-middle">Point Of Sales</span>
        </a>

        <ul class="sidebar-nav">
            <li class="sidebar-header">
                Pages
            </li>

            <li class="sidebar-item ">
                <a class="sidebar-link" href="http://127.0.0.1:8000/home">
                    <i class="align-middle" data-feather="sliders"></i> <span class="align-middle">Administraction</span>
                </a>
            </li>

            <li class="sidebar-item">
                <a class="sidebar-link" href="{{route('user.index')}}">
                    <i class="align-middle" data-feather="user"></i> <span class="align-middle">User</span>
                </a>
            </li>
            <li class="sidebar-item">
                <a class="sidebar-link" href="{{route('permission.index')}}">
                    <i class="align-middle" data-feather="log-in"></i> <span
                        class="align-middle">Permission</span>
                </a>
            </li>

            <li class="sidebar-item">
                <a class="sidebar-link" href="{{route('userrole.index')}}">
                    <i class="align-middle" data-feather="user-plus"></i> <span class="align-middle">User
                        Role</span>
                </a>
            </li>

            <li class="sidebar-item">
                <a class="sidebar-link" href="{{route('customer.index')}}">
                    <i class="align-middle" data-feather="book"></i> <span class="align-middle">Customer</span>
                </a>
            </li>
            <li class="sidebar-item">
                <a class="sidebar-link" href="pages-blank.html">
                    <i class="align-middle" data-feather="book"></i> <span class="align-middle">Invoice</span>
                </a>
            </li>
            <li class="sidebar-item">
                <a class="sidebar-link" href="{{route('pos.index')}}">
                    <i class="align-middle" data-feather="book"></i> <span class="align-middle">Point of Sales</span>
                </a>
            </li>
            <li class="sidebar-item">
                <a class="sidebar-link" href="pages-blank.html">
                    <i class="align-middle" data-feather="book"></i> <span class="align-middle">Report</span>
                </a>
            </li>
            <li class="sidebar-item">
                <a class="sidebar-link" href="{{route('ecommerce.index')}}">
                    <i class="align-middle" data-feather="book"></i> <span class="align-middle">eCommerce</span>
                </a>
            </li>

            <li class="sidebar-header">
                Items Management
            </li>
           
            <li class="sidebar-item">
                <a class="sidebar-link" href="{{route('warehouse.index')}}">
                    <i class="align-middle" data-feather="book"></i> <span class="align-middle">Warehouse</span>
                </a>
            </li>
            <li class="sidebar-item">
                <a class="sidebar-link" href="{{route('stockAdjust.index')}}">
                    <i class="align-middle" data-feather="book"></i> <span class="align-middle">Stock Adjustment</span>
                </a>
            </li>
            <li class="sidebar-item">
                <a class="sidebar-link" href="{{route('barcode.index')}}">
                    <i class="align-middle" data-feather="book"></i> <span class="align-middle">Label Printing</span>
                </a>
            </li>
            <li class="sidebar-item">
                <a class="sidebar-link" href="{{route('no_serise.index')}}">
                    <i class="align-middle" data-feather="book"></i> <span class="align-middle">No Serise</span>
                </a>
            </li>


            <li class="sidebar-header">
                 System
            </li>
            <li class="sidebar-item">
                <a class="sidebar-link" href="pages-blank.html">
                    <i class="fa-light fa-gear"></i><span class="align-middle">System Setting</span>
                </a>
            </li>
            <li class="sidebar-item">
                <a class="sidebar-link" href="{{route('user_setup.index')}}">
                    <i class="fa-light fa-gear"></i><span class="align-middle">User Setting</span>
                </a>
            </li>
            <li class="sidebar-item">
                <a class="sidebar-link" href="pages-blank.html">
                    <i class="fa-light fa-gear"></i><span class="align-middle">Sales Setting</span>
                </a>
            </li>

        </ul>


    </div>
</nav> --}}
<aside
class="sidenav navbar navbar-vertical navbar-expand-xs border-0 border-radius-xl my-3 fixed-start ms-3   bg-gradient-dark"
id="sidenav-main">
<div class="sidenav-header">
    <i class="fas fa-times p-3 cursor-pointer text-white opacity-5 position-absolute end-0 top-0 d-none d-xl-none"
        aria-hidden="true" id="iconSidenav"></i>
    <a class="navbar-brand m-0"
        href=" https://demos.creative-tim.com/material-dashboard-pro/pages/dashboards/analytics.html "
        target="_blank">
        <img src="{{asset('img/avatars/avatar-2.jpg')}}" class="navbar-brand-img h-100" alt="main_logo">
        <span class="ms-1 font-weight-bold text-white">Material</span>
    </a>
</div>
<hr class="horizontal light mt-0 mb-2">
<div class="collapse navbar-collapse  w-auto h-auto" id="sidenav-collapse-main">
    <ul class="navbar-nav">
        <li class="nav-item mb-2 mt-0">
            <a data-bs-toggle="collapse" href="#ProfileNav" class="nav-link text-white"
                aria-controls="ProfileNav" role="button" aria-expanded="false">
                <img src="{{asset('img/avatars/avatar-2.jpg')}}" class="avatar">
                <span class="nav-link-text ms-2 ps-1">Brooklyn Alice</span>
            </a>
            <div class="collapse" id="ProfileNav" style>
                <ul class="nav ">
                    <li class="nav-item">
                        <a class="nav-link text-white" href="../../pages/pages/profile/overview.html">
                            <span class="sidenav-mini-icon"> MP </span>
                            <span class="sidenav-normal  ms-3  ps-1"> My Profile </span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link text-white " href="../../pages/pages/account/settings.html">
                            <span class="sidenav-mini-icon"> S </span>
                            <span class="sidenav-normal  ms-3  ps-1"> Settings </span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link text-white " href="../../pages/authentication/signin/basic.html">
                            <span class="sidenav-mini-icon"> L </span>
                            <span class="sidenav-normal  ms-3  ps-1"> Logout </span>
                        </a>
                    </li>
                </ul>
            </div>
        </li>
        <hr class="horizontal light mt-0">
        <li class="nav-item">
            <a data-bs-toggle="collapse" href="#dashboardsExamples" class="nav-link text-white active"
                aria-controls="dashboardsExamples" role="button" aria-expanded="false">
                <i class="material-icons-round opacity-10">dashboard</i>
                <span class="nav-link-text ms-2 ps-1">Dashboards</span>
            </a>
            <div class="collapse  show " id="dashboardsExamples">
                <ul class="nav ">
                    <li class="nav-item active">
                        <a class="nav-link text-white active" href="http://127.0.0.1:8000/home">
                            <span class="sidenav-mini-icon"> A </span>
                            <span class="sidenav-normal  ms-2  ps-1"> Admin </span>
                        </a>
                    </li>
                    <li class="nav-item ">
                        <a class="nav-link text-white  " href="{{route('pos.index')}}">
                            <span class="sidenav-mini-icon"> P </span>
                            <span class="sidenav-normal  ms-2  ps-1"> {{__("Point of Sell")}} </span>
                        </a>
                    </li>
                    <li class="nav-item ">
                        <a class="nav-link text-white  " href="{{route('customer.index')}}">
                            <span class="sidenav-mini-icon"> C </span>
                            <span class="sidenav-normal  ms-2  ps-1"> {{__("Customer")}} </span>
                        </a>
                    </li>
                    
                     
                </ul>
            </div>
        </li>
        <li class="nav-item mt-3">
            <h6 class="ps-4  ms-2 text-uppercase text-xs font-weight-bolder text-white">System Setup</h6>
        </li>
        <li class="nav-item">
            <a data-bs-toggle="collapse" href="#pagesExamples" class="nav-link"
                aria-controls="pagesExamples" role="button" aria-expanded="false">
                <i class="fa-brands fa-product-hunt"></i>
                <span class="nav-link-text ms-2 ps-1">{{__("Items Setup")}}</span>
            </a>
            <div class="collapse " id="pagesExamples">
                <ul class="nav ">
                    <li class="nav-item ">
                        <a class="nav-link text-white "  
                            href="{{route('item_group.index')}}">
                            <span class="sidenav-mini-icon"> I </span>
                            <span class="sidenav-normal  ms-2  ps-1"> Item Group <b class="caret"></b></span>
                        </a>
                    </li>
                    <li class="nav-item ">
                        <a class="nav-link text-white "  
                            href="{{route('item_category.index')}}">
                            <span class="sidenav-mini-icon"> I </span>
                            <span class="sidenav-normal  ms-2  ps-1"> Item Category <b class="caret"></b></span>
                        </a>
                    </li>
                    <li class="nav-item ">
                        <a class="nav-link text-white "  
                            href="{{route('unitOfMeasure.index')}}">
                            <span class="sidenav-mini-icon"> I </span>
                            <span class="sidenav-normal  ms-2  ps-1"> Item Unit of Measure <b class="caret"></b></span>
                        </a>
                    </li>
                    <li class="nav-item ">
                        <a class="nav-link text-white "  
                            href="{{route('unit_of_measure.index')}}">
                            <span class="sidenav-mini-icon"> U </span>
                            <span class="sidenav-normal  ms-2  ps-1">Unit of Measure <b class="caret"></b></span>
                        </a>
                    </li>
                    <li class="nav-item ">
                        <a class="nav-link text-white "  
                            href="{{route('items.index')}}">
                            <span class="sidenav-mini-icon"> I </span>
                            <span class="sidenav-normal  ms-2  ps-1"> Item <b class="caret"></b></span>
                        </a>
                    </li>
                </ul>
            </div>
        </li>
        <li class="nav-item">
            <a data-bs-toggle="collapse" href="#applicationsExamples" class="nav-link text-white "
                aria-controls="applicationsExamples" role="button" aria-expanded="false">
                <i
                    class="material-icons-round {% if page.brand == 'RTL' %}ms-2{% else %} me-2{% endif %}">apps</i>
                <span class="nav-link-text ms-2 ps-1">User Setup</span>
            </a>
            <div class="collapse " id="applicationsExamples">
                <ul class="nav ">
                    <li class="nav-item ">
                        <a class="nav-link text-white " href="{{route('user.index')}}">
                            <span class="sidenav-mini-icon"> U </span>
                            <span class="sidenav-normal  ms-2  ps-1"> User </span>
                        </a>
                    </li>
                    <li class="nav-item ">
                        <a class="nav-link text-white " href="{{route('userrole.index')}}">
                            <span class="sidenav-mini-icon"> R </span>
                            <span class="sidenav-normal  ms-2  ps-1"> Roles </span>
                        </a>
                    </li>
                    <li class="nav-item ">
                        <a class="nav-link text-white " href="{{route('permission.index')}}">
                            <span class="sidenav-mini-icon"> P </span>
                            <span class="sidenav-normal  ms-2  ps-1"> Permission </span>
                        </a>
                    </li>
                     
                </ul>
            </div>
        </li>
        <li class="nav-item">
            <a data-bs-toggle="collapse" href="#ecommerceExamples" class="nav-link text-white "
                aria-controls="ecommerceExamples" role="button" aria-expanded="false">
                <i
                    class="material-icons-round {% if page.brand == 'RTL' %}ms-2{% else %} me-2{% endif %}">shopping_basket</i>
                <span class="nav-link-text ms-2 ps-1">System Setting</span>
            </a>
            <div class="collapse " id="ecommerceExamples">
                <ul class="nav ">
                    <li class="nav-item ">
                        <a class="nav-link text-white " 
                            href="#productsExample">
                            <span class="sidenav-mini-icon"> U </span>
                            <span class="sidenav-normal  ms-2  ps-1"> User Management <b class="caret"></b></span>
                        </a>
                         
                    </li>
                    <li class="nav-item ">
                        <a class="nav-link text-white " 
                            href="{{route('stockAdjust.index')}}">
                            <span class="sidenav-mini-icon"> U </span>
                            <span class="sidenav-normal  ms-2  ps-1"> Stock Adjustment <b class="caret"></b></span>
                        </a>
                         
                    </li>
                    <li class="nav-item ">
                        <a class="nav-link text-white " 
                            href="{{route('barcode.index')}}">
                            <span class="sidenav-mini-icon"> U </span>
                            <span class="sidenav-normal  ms-2  ps-1"> Label Printing <b class="caret"></b></span>
                        </a>
                         
                    </li>
                    <li class="nav-item ">
                        <a class="nav-link text-white " 
                            href="{{route('no_serise.index')}}">
                            <span class="sidenav-mini-icon"> U </span>
                            <span class="sidenav-normal  ms-2  ps-1"> No Serise <b class="caret"></b></span>
                        </a>
                         
                    </li>
                    <li class="nav-item ">
                        <a class="nav-link text-white "  
                            href="{{route('warehouse.index')}}">
                            <span class="sidenav-mini-icon"> U </span>
                            <span class="sidenav-normal  ms-2  ps-1"> Warehouse <b class="caret"></b></span>
                        </a>
                         
                    </li>
                </ul>
            </div>
        </li>
        <li class="nav-item">
            <a data-bs-toggle="collapse" href="#authExamples" class="nav-link text-white "
                aria-controls="authExamples" role="button" aria-expanded="false">
                <i
                    class="material-icons-round {% if page.brand == 'RTL' %}ms-2{% else %} me-2{% endif %}">content_paste</i>
                <span class="nav-link-text ms-2 ps-1">Report</span>
            </a>
            <div class="collapse " id="authExamples">
                <ul class="nav ">
                    <li class="nav-item ">
                        <a class="nav-link text-white " 
                            href="#signinExample">
                            <span class="sidenav-mini-icon"> S </span>
                            <span class="sidenav-normal  ms-2  ps-1"> Sign In <b class="caret"></b></span>
                        </a>
                        <div class="collapse " id="signinExample">
                            <ul class="nav nav-sm flex-column">
                                <li class="nav-item">
                                    <a class="nav-link text-white "
                                        href="../../pages/authentication/signin/basic.html">
                                        <span class="sidenav-mini-icon"> B </span>
                                        <span class="sidenav-normal  ms-2  ps-1"> Basic </span>
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link text-white "
                                        href="../../pages/authentication/signin/cover.html">
                                        <span class="sidenav-mini-icon"> C </span>
                                        <span class="sidenav-normal  ms-2  ps-1"> Cover </span>
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link text-white "
                                        href="../../pages/authentication/signin/illustration.html">
                                        <span class="sidenav-mini-icon"> I </span>
                                        <span class="sidenav-normal  ms-2  ps-1"> Illustration </span>
                                    </a>
                                </li>
                            </ul>
                        </div>
                    </li>
                    <li class="nav-item ">
                        <a class="nav-link text-white " 
                            href="#signupExample">
                            <span class="sidenav-mini-icon"> S </span>
                            <span class="sidenav-normal  ms-2  ps-1"> Sign Up <b class="caret"></b></span>
                        </a>
                        <div class="collapse " id="signupExample">
                            <ul class="nav nav-sm flex-column">
                                <li class="nav-item">
                                    <a class="nav-link text-white "
                                        href="../../pages/authentication/signup/basic.html">
                                        <span class="sidenav-mini-icon"> B </span>
                                        <span class="sidenav-normal  ms-2  ps-1"> Basic </span>
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link text-white "
                                        href="../../pages/authentication/signup/cover.html">
                                        <span class="sidenav-mini-icon"> C </span>
                                        <span class="sidenav-normal  ms-2  ps-1"> Cover </span>
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link text-white "
                                        href="../../pages/authentication/signup/illustration.html">
                                        <span class="sidenav-mini-icon"> I </span>
                                        <span class="sidenav-normal  ms-2  ps-1"> Illustration </span>
                                    </a>
                                </li>
                            </ul>
                        </div>
                    </li>
                    <li class="nav-item ">
                        <a class="nav-link text-white " 
                            href="#resetExample">
                            <span class="sidenav-mini-icon"> R </span>
                            <span class="sidenav-normal  ms-2  ps-1"> Reset Password <b
                                    class="caret"></b></span>
                        </a>
                        <div class="collapse " id="resetExample">
                            <ul class="nav nav-sm flex-column">
                                <li class="nav-item">
                                    <a class="nav-link text-white "
                                        href="../../pages/authentication/reset/basic.html">
                                        <span class="sidenav-mini-icon"> B </span>
                                        <span class="sidenav-normal  ms-2  ps-1"> Basic </span>
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link text-white "
                                        href="../../pages/authentication/reset/cover.html">
                                        <span class="sidenav-mini-icon"> C </span>
                                        <span class="sidenav-normal  ms-2  ps-1"> Cover </span>
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link text-white "
                                        href="../../pages/authentication/reset/illustration.html">
                                        <span class="sidenav-mini-icon"> I </span>
                                        <span class="sidenav-normal  ms-2  ps-1"> Illustration </span>
                                    </a>
                                </li>
                            </ul>
                        </div>
                    </li>
                    <li class="nav-item ">
                        <a class="nav-link text-white " 
                            href="#lockExample">
                            <span class="sidenav-mini-icon"> L </span>
                            <span class="sidenav-normal  ms-2  ps-1"> Lock <b class="caret"></b></span>
                        </a>
                        <div class="collapse " id="lockExample">
                            <ul class="nav nav-sm flex-column">
                                <li class="nav-item">
                                    <a class="nav-link text-white "
                                        href="../../pages/authentication/lock/basic.html">
                                        <span class="sidenav-mini-icon"> B </span>
                                        <span class="sidenav-normal  ms-2  ps-1"> Basic </span>
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link text-white "
                                        href="../../pages/authentication/lock/cover.html">
                                        <span class="sidenav-mini-icon"> C </span>
                                        <span class="sidenav-normal  ms-2  ps-1"> Cover </span>
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link text-white "
                                        href="../../pages/authentication/lock/illustration.html">
                                        <span class="sidenav-mini-icon"> I </span>
                                        <span class="sidenav-normal  ms-2  ps-1"> Illustration </span>
                                    </a>
                                </li>
                            </ul>
                        </div>
                    </li>
                    <li class="nav-item ">
                        <a class="nav-link text-white " 
                            href="#StepExample">
                            <span class="sidenav-mini-icon"> 2 </span>
                            <span class="sidenav-normal  ms-2  ps-1"> 2-Step Verification <b
                                    class="caret"></b></span>
                        </a>
                        <div class="collapse " id="StepExample">
                            <ul class="nav nav-sm flex-column">
                                <li class="nav-item">
                                    <a class="nav-link text-white "
                                        href="../../pages/authentication/verification/basic.html">
                                        <span class="sidenav-mini-icon"> B </span>
                                        <span class="sidenav-normal  ms-2  ps-1"> Basic </span>
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link text-white "
                                        href="../../pages/authentication/verification/cover.html">
                                        <span class="sidenav-mini-icon"> C </span>
                                        <span class="sidenav-normal  ms-2  ps-1"> Cover </span>
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link text-white "
                                        href="../../pages/authentication/verification/illustration.html">
                                        <span class="sidenav-mini-icon"> I </span>
                                        <span class="sidenav-normal  ms-2  ps-1"> Illustration </span>
                                    </a>
                                </li>
                            </ul>
                        </div>
                    </li>
                    <li class="nav-item ">
                        <a class="nav-link text-white " 
                            href="#errorExample">
                            <span class="sidenav-mini-icon"> E </span>
                            <span class="sidenav-normal  ms-2  ps-1"> Error <b class="caret"></b></span>
                        </a>
                        <div class="collapse " id="errorExample">
                            <ul class="nav nav-sm flex-column">
                                <li class="nav-item">
                                    <a class="nav-link text-white "
                                        href="../../pages/authentication/error/404.html">
                                        <span class="sidenav-mini-icon"> E </span>
                                        <span class="sidenav-normal  ms-2  ps-1"> Error 404 </span>
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link text-white "
                                        href="../../pages/authentication/error/500.html">
                                        <span class="sidenav-mini-icon"> E </span>
                                        <span class="sidenav-normal  ms-2  ps-1"> Error 500 </span>
                                    </a>
                                </li>
                            </ul>
                        </div>
                    </li>
                </ul>
            </div>
        </li>
        <li class="nav-item">
            <hr class="horizontal light" />
            <h6 class="ps-4  ms-2 text-uppercase text-xs font-weight-bolder text-white">DOCS</h6>
        </li>
        <li class="nav-item">
            <a data-bs-toggle="collapse" href="#basicExamples" class="nav-link text-white "
                aria-controls="basicExamples" role="button" aria-expanded="false">
                <i
                    class="material-icons-round {% if page.brand == 'RTL' %}ms-2{% else %} me-2{% endif %}">upcoming</i>
                <span class="nav-link-text ms-2 ps-1">Basic</span>
            </a>
            <div class="collapse " id="basicExamples">
                <ul class="nav ">
                    <li class="nav-item ">
                        <a class="nav-link text-white " 
                            href="#gettingStartedExample">
                            <span class="sidenav-mini-icon"> G </span>
                            <span class="sidenav-normal  ms-2  ps-1"> Getting Started <b
                                    class="caret"></b></span>
                        </a>
                        <div class="collapse " id="gettingStartedExample">
                            <ul class="nav nav-sm flex-column">
                                <li class="nav-item">
                                    <a class="nav-link text-white "
                                        href="https://www.creative-tim.com/learning-lab/bootstrap/quick-start/material-dashboard"
                                        target="_blank">
                                        <span class="sidenav-mini-icon"> Q </span>
                                        <span class="sidenav-normal  ms-2  ps-1"> Quick Start </span>
                                    </a>
                                </li>
                                
                            </ul>
                        </div>
                    </li>
                     
                </ul>
            </div>
        </li>
       
        <li class="nav-item">
            <a class="nav-link"
                href="https://github.com/creativetimofficial/ct-material-dashboard-pro/blob/master/CHANGELOG.md"
                target="_blank">
                <i
                    class="material-icons-round {% if page.brand == 'RTL' %}ms-2{% else %} me-2{% endif %}">receipt_long</i>
                <span class="nav-link-text ms-2 ps-1">About Us</span>
            </a>
        </li>
    </ul>
</div>
</aside>