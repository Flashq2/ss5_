
<ul class="nav nav-tabs">
    <li class="nav-item">
        <a class="nav-link active" href="#" data-filter="all">
            <div class="nav-icon"><i class="fa fa-fw fa-utensils"></i></div>
            <div class="nav-text">All Dishes</div>
        </a>
    </li>
    @foreach ($category as $categories)
    <li class="nav-item">
        <a class="nav-link" href="#" data-filter="meat">
            <div class="nav-icon"><i class="fa-solid fa-pizza-slice"></i></div>
            <div class="nav-text">{{$categories->code}}</div>
        </a>
    </li>
    @endforeach
     
     
</ul>