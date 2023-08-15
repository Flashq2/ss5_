<div class="pos-content-container h-100">
    <div class="product-row">
        @foreach ($items as $item)
            <div class="product-container" data-type="meat">
                <a href="#" class="product add_card" data-bs-toggle="modal" data-bs-target="#modalPos" data-code="{{$item->no}}">
                    <div class="img" style="background-image: url({{ asset("item/$item->picture") }})">
                    </div>
                    <div class="text">
                        <div class="title">{{ $item->description }}&reg;</div>
                        <div class="desc">chicken, egg, mushroom, salad</div>
                        <div class="price">{{ $item->unit_price }}$</div>
                    </div>
                </a>
            </div>
        @endforeach
    </div>
</div>
