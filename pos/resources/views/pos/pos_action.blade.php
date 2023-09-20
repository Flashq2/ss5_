<div class="pos-content-container h-100">
    <div class="product-row">
        @foreach ($items as $item)
            <div class="product-container" data-type="meat">
                <a href="#" class="product add_card" data-bs-toggle="modal" data-bs-target="#modalPos" data-code="{{$item->no}}" data-uom = {{$item->unit_of_measure_code}} data-price ={{$item->price }}>
                    <div class="img" style="background-image: url({{ asset("item/$item->picture") }})">
                    </div>
                    <div class="text">
                        <div class="title">{{ $item->description }}&reg;</div>
                        <div class="desc"> </div>
                        <div class="price">{{ $item->price }}$  ({{$item->unit_of_measure_code}})</div>
                    </div>
                </a>
            </div>
        @endforeach
    </div>
    <div class="paginate">
        {!! $items->links() !!}
    </div>
</div>
