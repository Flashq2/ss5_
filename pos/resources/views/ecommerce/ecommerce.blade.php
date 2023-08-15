<!DOCTYPE html>
<html lang="en">

<head>
    @include('layouts.header')
</head>
<style>
     
    body {
        font-family: battambong;
        box-sizing: border-box;
    }

    .filter_statistice {

        padding: 5px;
    }
    .hidden-xs{
        color: white;
    }

    .cart_box {
        /* background: linear-gradient(to right,rgb(91, 91, 182),rgb(150, 54, 150)); */
        background-color: white;
        height: 100px;
        border-radius: 10px !important;
        padding: 5px;
        border: 1px solid rgba(180, 208, 224, 0.5);
        box-shadow: 3px 4px 23px #edecec;
        width: 93%;
        float: right;
    }

    .cart_box .style_img {
        width: 50px;
        height: 50px;
        border-radius: 50% 50% !important;
        background-color: rgb(245, 245, 245);
        float: right !important;
        overflow: hidden;
        align-items: center;
        display: flex;
        justify-content: center;
    }

    .cart_box .style_img img {
        width: 50%;
        height: 50%;
        object-fit: contain;

    }
    .cart_box_heightx2{
          /* background: linear-gradient(to right,rgb(91, 91, 182),rgb(150, 54, 150)); */
        background-color: white;
        height: 200px;
        border-radius: 10px !important;
        padding: 5px;
        border: 1px solid rgba(180, 208, 224, 0.5);
        box-shadow: 3px 4px 23px #edecec;
        width: 93%;
        float: right;
    }

    span {
        color: gray;
    }

    .titile_box {
        margin-left: 10px;
         
    }

    .main_title {
        width: 100%;
        font-size: 18px;
        color: #334257;
        font-weight: 600;

    }

    .data_quantity {
        width: 100%;
        font-size: 24px;
        color: gray;
        font-weight: 600;
    }

    .top_cart_title {
        padding: 10px;
        font-size: 22px;
        margin-bottom: 10px;
    }

    .filter_statistice {
        background-color: #ffff;
        border-radius: 10px !important;
        width: 97%;
        margin: 0 auto;
        margin-top: 10px;

    }
    .form_subdata{
        
     
    }
    .sub_data{
        
       background-color: rgba(110, 137, 175, 0.0509803922);
        border-radius: 10px !important;
         float:right;
       padding: 20px;
       width: 93%;
    }
    .sub_title_data{
        text-align: right;
        font-size: 20px;
        font-weight: 600;
        color: rgba(0, 0, 255, 0.688)
    }
    .sub_title_img img{
        width: 20px;
        height: 20px;
        object-fit: contain;
    }
    .sub_title_title{
        font-size: 18px;
    }
    .cart_space_bottom{
        margin-bottom: 9px;
        /* margin-left: 5px; */
    }
    .cart_space_bottom:last-child{
        margin-bottom: 30px;
    }
    .sub_data:hover{
        /* transform: scale(1.05); */
         transition: 0.2s ease;
        box-shadow: 2px 2px 15px rgba(7, 59, 116, 0.15);
        cursor: pointer;
    }
    .dashboard-content-container{
        background-color: #ffe1e100 !important;
    }
    .merchent{
        width: 100%;
        background: white;
        height: 230px;
        display: flex;
        flex-direction: column;
        justify-content: center;
        align-items: center;
        border-radius: 10px !important;
    padding: 5px;
    border: 1px solid rgba(180, 208, 224, 0.5);
    box-shadow: 3px 4px 23px #edecec;
    }
    .merchent_sub{
       
        width: 100%;
        background: white;
        height: 100px;
        display: flex;
        flex-direction: column;
        justify-content: center;
        align-items: center;
        border-radius: 10px !important;
    padding: 10px;
    border: 1px solid rgba(180, 208, 224, 0.5);
    box-shadow: 3px 4px 23px #edecec;
    margin-top: 10px;
    }
    .list{
        display: flex;
        justify-content: space-between;
         width: 100%;
    }
    .merchent .control_img {
        width: 50px;
        height: 50px;
    }
    .merchent .control_img img{
        width: 100%;
        height: 100%;
        object-fit: contain;
        margin-bottom: 5px;
    }
    h4{
        color: #334257 !important;
        margin: 0 !important;
    }
    p.card_data_title{
        color: #334257 !important;
        font-weight: 500;
    }
    h4{
        padding: 0;
        margin: 0;
    }
    p{
        padding: 0 !important;
        margin: 0 !important;
    }
    
    .merchent_sublist img{
        width: 50px;
        height: 50px;
        object-fit: contain;
        
    }
    .merchent_sublist{
        display: flex;
        flex-direction: column;
        align-items: start;
    }
    .form_img_logo ,.form_img_bener{
        display: flex;
        justify-content: center;
        align-items: center;
        flex-direction: column;
        height: 200px;
        padding: 10px
    }
    .form_img_logo img ,.form_img_bener img{
        width: 30%;
        height: 150px;
        object-fit: cover;
        border:   1px solid #ced4da;
        border-radius: 5px !important;
        margin-bottom: 5px;
    }
    .form_img_bener img{
        width: 90%;
        height:90%;
    }
    .sub_reset_button{
        display: flex;
        justify-content: end;

    }
    button.reset{
        background-color: #ededed;
    border-color: #ededed;
    color: #334257;
    border-radius: 4px !important;
    padding: 10px 20px;
    margin-right: 10px;
    }
    button.add{
        background-color: #04a3e;
    border-color: #ededed;
    color: white;
    border-radius: 4px !important;
    padding: 10px 20px;
    margin-right: 10px;
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
                    <div class="filter_statistice">
                        <div class="row">
                            <div class="col-lg-12">
                                <div class="top_cart_title">
                                    <div class="row">
                                        <div class="col-lg-10">
                                            <span>Business Analytics</span>
                                        </div>
                                        <div class="col-lg-2">
                                            <div class="row">
                                                <input type="date" class="form-control">
                                            </div>

                                        </div>
                                    </div>
                                </div>

                            </div>

                        </div>
                        <div class="row">
                            <div class="col-xl-3 col-lg-3 col-md-6 col-sm-6 col-12">
                                <div class="cart_box">
                                    <div class="row">
                                        <div class="col-lg-8 col-md-8 col-sm-8 col-xs-8">
                                            <div class="titile_box">
                                                <span class="main_title">Today Sale</span><br>
                                                <span class="data_quantity">89.00$</span><br>
                                                <span class="data_remark">+2% since last month</span>
                                            </div>
                                        </div>
                                        <div class="col-lg-3 col-md-3 col-sm-3 col-xs-3">
                                            <div class="style_img">
                                                <img src="{{ asset('/img/shopcon.jpg') }}" alt="">
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-xl-3 col-lg-3 col-md-6 col-sm-6 col-12">
                                <div class="cart_box">
                                    <div class="row">
                                        <div class="col-lg-8 col-md-8 col-sm-8 col-xs-8">
                                            <div class="titile_box">
                                                <span class="main_title">Today Sale</span><br>
                                                <span class="data_quantity">89.00$</span><br>
                                                <span class="data_remark">+2% since last month</span>
                                            </div>
                                        </div>
                                        <div class="col-lg-3 col-md-3 col-sm-3 col-xs-3">
                                            <div class="style_img">
                                                <img src="{{ asset('/img/shopcon.jpg') }}" alt="">
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-xl-3 col-lg-3 col-md-6 col-sm-6 col-12">
                                <div class="cart_box">
                                    <div class="row">
                                        <div class="col-lg-8 col-md-8 col-sm-8 col-xs-8">
                                            <div class="titile_box">
                                                <span class="main_title">Today Sale</span><br>
                                                <span class="data_quantity">89.00$</span><br>
                                                <span class="data_remark">+2% since last month</span>
                                            </div>
                                        </div>
                                        <div class="col-lg-3 col-md-3 col-sm-3 col-xs-3">
                                            <div class="style_img">
                                                <img src="{{ asset('/img/shopcon.jpg') }}" alt="">
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-xl-3 col-lg-3 col-md-6 col-sm-6 col-12">
                                <div class="cart_box">
                                    <div class="row">
                                        <div class="col-lg-8 col-md-8 col-sm-8 col-xs-8">
                                            <div class="titile_box">
                                                <span class="main_title">Today Sale</span><br>
                                                <span class="data_quantity">89.00$</span><br>
                                                <span class="data_remark">+2% since last month</span>
                                            </div>
                                        </div>
                                        <div class="col-lg-3 col-md-3 col-sm-3 col-xs-3">
                                            <div class="style_img">
                                                <img src="{{ asset('/img/shopcon.jpg') }}" alt="">
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                             
                        </div>
<hr>
                        <div class="row ">

                            <div class="form_subdata">
                                <div class="row">

                                
                                <div class="col-xl-3 col-lg-3 col-md-6 col-sm-6 col-xs-6 cart_space_bottom  ">
                                    <div class="sub_data">
                                        <div class="col-lg-1 col-xs-1">
                                            <div class="sub_title_img">
                                                    <img src="{{ asset('/img/shopcon.jpg') }}" alt="">
                                            </div>
                                        </div>
                                        <div class="col-lg-4 col-xs-4">
                                            <div class="sub_title_title">
                                                <span>Order</span>
                                            </div>
                                        </div>
                                        <div class="col-lg-7 col-xs-7">
                                            <div class="sub_title_data">
                                                 <span>90</span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-xl-3 col-lg-3 col-md-6 col-sm-6 col-xs-6 cart_space_bottom">
                                    <div class="sub_data">
                                        <div class="col-lg-1 col-xs-1">
                                            <div class="sub_title_img">
                                                    <img src="{{ asset('/img/shopcon.jpg') }}" alt="">
                                            </div>
                                        </div>
                                        <div class="col-lg-4 col-xs-4">
                                            <div class="sub_title_title">
                                                <span>Order</span>
                                            </div>
                                        </div>
                                        <div class="col-lg-7 col-xs-7">
                                            <div class="sub_title_data">
                                                 <span style="color:#0052ea;">90</span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-xl-3 col-lg-3 col-md-6 col-sm-6 col-xs-6 cart_space_bottom">
                                    <div class="sub_data">
                                        <div class="col-lg-1 col-xs-1">
                                            <div class="sub_title_img">
                                                    <img src="{{ asset('/img/shopcon.jpg') }}" alt="">
                                            </div>
                                        </div>
                                        <div class="col-lg-4 col-xs-4">
                                            <div class="sub_title_title">
                                                <span>Order</span>
                                            </div>
                                        </div>
                                        <div class="col-lg-7 col-xs-7">
                                            <div class="sub_title_data">
                                                 <span>90</span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-xl-3 col-lg-3 col-md-6 col-sm-6 col-xs-6 cart_space_bottom">
                                    <div class="sub_data">
                                        <div class="col-lg-1 col-xs-1">
                                            <div class="sub_title_img">
                                                    <img src="{{ asset('/img/shopcon.jpg') }}" alt="">
                                            </div>
                                        </div>
                                        <div class="col-lg-4 col-xs-4">
                                            <div class="sub_title_title">
                                                <span>Order</span>
                                            </div>
                                        </div>
                                        <div class="col-lg-7 col-xs-7">
                                            <div class="sub_title_data">
                                                 <span>90</span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-xl-3 col-lg-3 col-md-6 col-sm-6 col-xs-6 cart_space_bottom">
                                    <div class="sub_data">
                                        <div class="col-lg-1 col-xs-1">
                                            <div class="sub_title_img">
                                                    <img src="{{ asset('/img/shopcon.jpg') }}" alt="">
                                            </div>
                                        </div>
                                        <div class="col-lg-4 col-xs-4">
                                            <div class="sub_title_title">
                                                <span>Order</span>
                                            </div>
                                        </div>
                                        <div class="col-lg-7 col-xs-7">
                                            <div class="sub_title_data">
                                                 <span>90</span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-xl-3 col-lg-3 col-md-6 col-sm-6 col-xs-6 cart_space_bottom">
                                    <div class="sub_data">
                                        <div class="col-lg-1 col-xs-1">
                                            <div class="sub_title_img">
                                                    <img src="{{ asset('/img/shopcon.jpg') }}" alt="">
                                            </div>
                                        </div>
                                        <div class="col-lg-4 col-xs-4">
                                            <div class="sub_title_title">
                                                <span>Order</span>
                                            </div>
                                        </div>
                                        <div class="col-lg-7 col-xs-7">
                                            <div class="sub_title_data">
                                                 <span>90</span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-xl-3 col-lg-3 col-md-6 col-sm-6 col-xs-6 cart_space_bottom">
                                    <div class="sub_data">
                                        <div class="col-lg-1 col-xs-1">
                                            <div class="sub_title_img">
                                                    <img src="{{ asset('/img/shopcon.jpg') }}" alt="">
                                            </div>
                                        </div>
                                        <div class="col-lg-4 col-xs-4">
                                            <div class="sub_title_title">
                                                <span>Order</span>
                                            </div>
                                        </div>
                                        <div class="col-lg-7 col-xs-7">
                                            <div class="sub_title_data">
                                                 <span>90</span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-xl-3 col-lg-3 col-md-6 col-sm-6 col-xs-6 cart_space_bottom">
                                    <div class="sub_data">
                                        <div class="col-lg-1 col-xs-1">
                                            <div class="sub_title_img">
                                                    <img src="{{ asset('/img/shopcon.jpg') }}" alt="">
                                            </div>
                                        </div>
                                        <div class="col-lg-4 col-xs-4">
                                            <div class="sub_title_title">
                                                <span>Order</span>
                                            </div>
                                        </div>
                                        <div class="col-lg-7 col-xs-7">
                                            <div class="sub_title_data">
                                                 <span>90</span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="filter_statistice">
                    <div class="row">
                        <div class="col-lg-12">
                            <div class="top_cart_title">
                                <div class="row">
                                    <div class="col-lg-10">
                                        <span>Wallet</span>
                                    </div>
                                    <div class="col-lg-2">
                                        <div class="row">
                                            <input type="date" class="form-control">
                                        </div>

                                    </div>
                                </div>
                            </div>

                        </div>

                    </div>
                    <div class="row">
                        <div class="col-xl-4 col-lg-3 col-md-6 col-sm-6 col-12">
                            <div class="cart_box_heightx2">
                                <div class="row">
                                    <div class="col-lg-8 col-md-8 col-sm-8 col-xs-8">
                                        <div class="titile_box">
                                            <span class="main_title">Today Sale</span><br>
                                            <span class="data_quantity">89.00$</span><br>
                                            <span class="data_remark">+2% since last month</span>
                                        </div>
                                    </div>
                                    <div class="col-lg-3 col-md-3 col-sm-3 col-xs-3">
                                        <div class="style_img">
                                            <img src="{{ asset('/img/shopcon.jpg') }}" alt="">
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-xl-8 col-lg-3 col-md-6 col-sm-6 col-12">
                            <div class="row">
                                <div class="col-xl-6"><div class="cart_box">
                                    <div class="row">
                                        <div class="col-lg-8 col-md-8 col-sm-8 col-xs-8">
                                            <div class="titile_box">
                                                <span class="main_title">Today Sale</span><br>
                                                <span class="data_quantity">89.00$</span><br>
                                                <span class="data_remark">+2% since last month</span>
                                            </div>
                                        </div>
                                        <div class="col-lg-3 col-md-3 col-sm-3 col-xs-3">
                                            <div class="style_img">
                                                <img src="{{ asset('/img/shopcon.jpg') }}" alt="">
                                            </div>
                                        </div>
                                    </div>
                                </div></div>
                                <div class="col-xl-6"><div class="cart_box">
                                <div class="row">
                                    <div class="col-lg-8 col-md-8 col-sm-8 col-xs-8">
                                        <div class="titile_box">
                                            <span class="main_title">Today Sale</span><br>
                                            <span class="data_quantity">89.00$</span><br>
                                            <span class="data_remark">+2% since last month</span>
                                        </div>
                                    </div>
                                    <div class="col-lg-3 col-md-3 col-sm-3 col-xs-3">
                                        <div class="style_img">
                                            <img src="{{ asset('/img/shopcon.jpg') }}" alt="">
                                        </div>
                                    </div>
                                </div>
                             </div></div>
                            </div>
                            <div class="row">
                                <div class="col-xl-6"><div class="cart_box">
                                    <div class="row">
                                        <div class="col-lg-8 col-md-8 col-sm-8 col-xs-8">
                                            <div class="titile_box">
                                                <span class="main_title">Today Sale</span><br>
                                                <span class="data_quantity">89.00$</span><br>
                                                <span class="data_remark">+2% since last month</span>
                                            </div>
                                        </div>
                                        <div class="col-lg-3 col-md-3 col-sm-3 col-xs-3">
                                            <div class="style_img">
                                                <img src="{{ asset('/img/shopcon.jpg') }}" alt="">
                                            </div>
                                        </div>
                                    </div>
                                </div></div>
                                <div class="col-xl-6"><div class="cart_box">
                                <div class="row">
                                    <div class="col-lg-8 col-md-8 col-sm-8 col-xs-8">
                                        <div class="titile_box">
                                            <span class="main_title">Today Sale</span><br>
                                            <span class="data_quantity">89.00$</span><br>
                                            <span class="data_remark">+2% since last month</span>
                                        </div>
                                    </div>
                                    <div class="col-lg-3 col-md-3 col-sm-3 col-xs-3">
                                        <div class="style_img">
                                            <img src="{{ asset('/img/shopcon.jpg') }}" alt="">
                                        </div>
                                    </div>
                                </div>
                             </div></div>
                            </div>
                            
                        </div>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="filter_statistice">
                    <div class="row">
                        <div class="col-xl-12">
                            <div id="chart"></div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="filter_statistice">
                        <div class="row">
                            <div class="col-lg-12">
                                <div class="top_cart_title">
                                    <div class="row">
                                        <div class="col-lg-10">
                                            <span>Merchent Wallet</span>
                                        </div>
                                        <div class="col-lg-2">
                                            

                                        </div>
                                    </div>
                                </div>

                            </div>

                        </div>
                        <div class="row">
                            <div class="col-lg-4">
                                <div class="merchent">
                                    <div class="control_img">
                                        <img src="https://cdn-icons-png.flaticon.com/512/10773/10773779.png" alt="">
                                    </div>
                                    <h4>$3,465.0</h4>
                                    <p>Withdrawable Balance</p>
                                </div>
                            </div>
                            <div class="col-lg-8">
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="merchent_sub">
                                            <div class="list">
                                                <div class="merchent_sublist" >
                                                    <h4>$500.0</h4>
                                                    <p class="card_data_title">Aready Withdrawn</p>
                                                    
                                                </div>
                                                <div class="merchent_sublist">
                                                    <img src="https://cdn-icons-png.flaticon.com/512/10773/10773779.png" alt="">
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="merchent_sub">
                                            <div class="list">
                                                <div class="merchent_sublist" >
                                                    <h4>$500.0</h4>
                                                    <p class="card_data_title">Aready Withdrawn</p>
                                                    
                                                </div>
                                                <div class="merchent_sublist">
                                                    <img src="https://cdn-icons-png.flaticon.com/512/10773/10773779.png" alt="">
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="merchent_sub">
                                            <div class="list">
                                                <div class="merchent_sublist" >
                                                    <h4>$500.0</h4>
                                                    <p class="card_data_title">Aready Withdrawn</p>
                                                    
                                                </div>
                                                <div class="merchent_sublist">
                                                    <img src="https://cdn-icons-png.flaticon.com/512/10773/10773779.png" alt="">
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="merchent_sub">
                                            <div class="list">
                                                <div class="merchent_sublist" >
                                                    <h4>$500.0</h4>
                                                    <p class="card_data_title">Aready Withdrawn</p>
                                                    
                                                </div>
                                                <div class="merchent_sublist">
                                                    <img src="https://cdn-icons-png.flaticon.com/512/10773/10773779.png" alt="">
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="merchent_sub">
                                            <div class="list">
                                                <div class="merchent_sublist" >
                                                    <h4>$500.0</h4>
                                                    <p class="card_data_title">Aready Withdrawn</p>
                                                    
                                                </div>
                                                <div class="merchent_sublist">
                                                    <img src="https://cdn-icons-png.flaticon.com/512/10773/10773779.png" alt="">
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="merchent_sub">
                                            <div class="list">
                                                <div class="merchent_sublist" >
                                                    <h4>$500.0</h4>
                                                    <p class="card_data_title">Aready Withdrawn</p>
                                                    
                                                </div>
                                                <div class="merchent_sublist">
                                                    <img src="https://cdn-icons-png.flaticon.com/512/10773/10773779.png" alt="">
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                 
            </div>
        </div>
        <div class="row">
            <div class="filter_statistice">
                <div class="row">
                    <div class="col-lg-12">
                        <div class="top_cart_title">
                            <div class="row">
                                <div class="col-lg-10">
                                    <span>Merchent Wallet</span>
                                </div>
                                <div class="col-lg-2">
                                    

                                </div>
                            </div>
                        </div>

                    </div>

                </div>
                <div class="row">
                    <div class="col-lg-6 form-group">
                        <label for="shop_name" class="title-color d-flex gap-1 align-items-center">Shop name</label>
                        <input type="text" class="form-control form-control-user" id="shop_name" name="shop_name" placeholder="Ex: Jhon" value="" required="">
                    </div>
                    <div class="col-lg-6 form-group">
                        <label for="shop_name" class="title-color d-flex gap-1 align-items-center">Shop name</label>
                        <input type="text" class="form-control form-control-user" id="shop_name" name="shop_name" placeholder="Ex: Jhon" value="" required="">
                        </div>
                    <div class="col-lg-6">
                        <div class="form_img_logo">
                            <img src="https://cdn-icons-png.flaticon.com/512/10773/10773779.png" alt="">
                            <p>Click on image to upload</p>
                        </div>
                    </div>
                    <div class="col-lg-6">
                        <div class="form_img_bener">
                            <img src="	https://6valley.6amtech.com/public/assets/back-end/img/400x400/img2.jpg" alt="">
                            <p>Click on image to upload</p>
                        </div>
                    </div>
                    <div class="col-lg-12">
                        <div class="sub_reset_button">
                            <button class="reset">Reset</button>
                            <button class="add">Submit</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
            </main>

        </div>
    </div>
</body>
<script src="https://cdnjs.com/libraries/Chart.js"></script>
<script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>
<script>
      var options = {
          series: [{
          name: 'Net Profit',
          data: [44, 55, 57, 56, 61, 58, 63, 60, 66]
        }, {
          name: 'Revenue',
          data: [76, 85, 101, 98, 87, 105, 91, 114, 94]
        }, {
          name: 'Free Cash Flow',
          data: [35, 41, 36, 26, 45, 48, 52, 53, 41]
        }],
          chart: {
          type: 'bar',
          height: 350
        },
        plotOptions: {
          bar: {
            horizontal: false,
            columnWidth: '55%',
            endingShape: 'rounded'
          },
        },
        dataLabels: {
          enabled: false
        },
        stroke: {
          show: true,
          width: 2,
          colors: ['transparent']
        },
        xaxis: {
          categories: ['Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct'],
        },
        yaxis: {
          title: {
            text: '$ (thousands)'
          }
        },
        fill: {
          opacity: 1
        },
        tooltip: {
          y: {
            formatter: function (val) {
              return "$ " + val + " thousands"
            }
          }
        }
        };

        var chart = new ApexCharts(document.querySelector("#chart"), options);
        chart.render();
</script>
@include('script');
 
</html>
