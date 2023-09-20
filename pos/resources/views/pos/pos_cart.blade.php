<section class="section-content padding-y-sm bg-default ">
    <div class="container-fluid">
        <div class="row">
            <div class="col-md-12 card padding-y-sm card ">
                <ul class="nav bg radius nav-pills nav-fill mb-3 bg" role="tablist">
                    <li class="nav-item">
                        <a class="nav-link active show" data-toggle="pill" href="#nav-tab-card">
                            <i class="fa fa-tags"></i> All</a>
                    </li>
                   
                    
                </ul>
                <span id="items">
                    <div class="row">
                        @foreach ($items as $item)
                         <div class="col-md-2">
                            <figure class="card card-product">
                                <span class="badge-new"> NEW </span>
                                <div class="img-wrap"> <img src="{{asset("item/$item->picture")}}">
                                    <a class="btn-overlay" href="#"><i class="fa fa-search-plus"></i> Quick
                                        view</a>
                                </div>
                                <figcaption class="info-wrap">
                                    <a href="#" class="title">{{$item->description}}</a>
                                    <div class="action-wrap">
                                        <a href="#" class="btn btn-primary btn-sm float-right" data-code="{{$item->no}}"> <i
                                                class="fa fa-cart-plus"></i> Add </a>
                                        <div class="price-wrap h5">
                                            <span class="price-new">{{$item->unit_price}}$</span>
                                        </div> <!-- price-wrap.// -->
                                    </div> <!-- action-wrap -->
                                </figcaption>
                            </figure> <!-- card // -->
                        </div>    
                        @endforeach
                         
                         
                    </div> 
                   
                </span>
            </div>
            
        </div>
    </div><!-- container //  -->
</section>