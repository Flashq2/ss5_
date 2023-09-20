<style>
    .tooltip {
        z-index: 1000;
        width: 200px;
        height: 150px;
        padding: 20px;
        border: 1px solid #ccc;
        box-shadow: 0 0 3px rgba(0, 0, 0, .3);
        -webkit-box-shadow: 0 0 3px rgba(0, 0, 0, .3);
        border-radius: 3px;
        -webkit-border-radius: 3px;
        position: absolute;
        top: 5px;
        left: 50px;
        display: none;
    }
</style>
<div class="h-100 d-flex flex-column p-0">
    <div class="pos-sidebar-header">
        <div class="back-btn">
            <button type="button" data-dismiss-class="pos-sidebar-mobile-toggled" data-target="#pos" class="btn border-0">
                <i class="fa fa-chevron-left"></i>
            </button>
        </div>
        <div class="icon"><i class="fa fa-plate-wheat"></i></div>
        <div class="title">Small POs</div>
        <div class="order"><i class="fa-solid fa-expand"></i> <b>Zoom In</b></div>
    </div>
    <div class="pos-sidebar-header" style="background: rgb(255, 255, 255);color:black;">
        <div class="back-btn">
            <button type="button" data-dismiss-class="pos-sidebar-mobile-toggled" data-target="#pos"
                class="btn border-0">
                <i class="fa fa-chevron-left"></i>
            </button>
        </div>
        <div class="icon"><i class="fa-regular fa-money-bill-1"></i></div>
        <div class="title">Exchange Rate(1$) : <span class="cr_value">{{ $currenccy_khr }}</span> KHR

        </div>

        <div class="order">Invoice: <b>{{$document_no}}</b></div>
    </div>
    <div class="pos-sidebar-nav">
        <ul class="nav nav-tabs nav-fill">
            <li class="nav-item">
                <a class="nav-link active" href="#" data-bs-toggle="tab" data-bs-target="#newOrderTab">New Order
                    (5)</a>
            </li>

        </ul>
    </div>
    <div class="pos-sidebar-body tab-content" data-scrollbar="true" data-height="100%">
        <div class="tab-pane fade h-100 show active" id="newOrderTab">
            <div class="pos-table">
                <table class="table">
                    <thead>
                        <tr>
                            <th></th>
                            <th>CODE</th>
                            <th>DESCRIPTION</th>
                            <th style="width: 100px;">UOM</th>
                            <th>PRICE</th>
                            <th>QTY</th>
                            <th>DES % </th>
                            <th>AMOUNT</th>
                        </tr>
                    <tbody class="no_body">
                        @if ($line)
                            <?php
                            $i = 0;
                            ?>
                            @if ($line)


                                @foreach ($line as $lines)
                                    <div class="tooltip">
                                        Hello This is
                                    </div>
                                    <?php
                                    $i++;
                                    $uom = App\Models\ItemUnitofMeasureModel::select('unit_of_measure_code')
                                        ->where('item_no', $lines->item_no)
                                        ->get();
                                    ?>
                                    <tr data-line="{{ $i }}" data-line_id={{ $lines->id }}
                                        id="{{ $lines->id }}">
                                        <td>
                                            <p class="respon3"> <i
                                                    class="fa-regular fa-trash-can remove"style="color: #cf204c;"
                                                    data-line="{{ $i }}"
                                                    data-line_id="{{ $lines->id }}"></i></p>
                                        </td>
                                        <td>
                                            <p class="respon3" name="item_{{ $lines->id }}"
                                                id="item_{{ $lines->id }}">{{ $lines->item_no }}</p>
                                        </td>
                                        <td>
                                            <p class="respon3">{{ $lines->description }}</p>
                                        </td>
                                        <td>
                                            <select name="uom_{{ $lines->id }}" id="uom_{{ $lines->id }}"
                                                style="width: 100px;" class="uom" data-line_id={{ $lines->id }} data-document_no= {{$document_no}}>
                                                <option value="">&nbsp;</option>
                                                @if ($uom)
                                                    @foreach ($uom as $uoms)
                                                        <option value="{{ $uoms->unit_of_measure_code }}"
                                                            {{ $uoms->unit_of_measure_code == $lines->unit_of_measure ? 'selected' : '' }}>
                                                            {{ $uoms->unit_of_measure_code }}</option>
                                                    @endforeach
                                                @endif
                                            </select>
                                        </td>
                                        <td>
                                            <p class="respon3">
                                                {{ \App\Helpers\GlobalFunction::numberFormate($lines->unit_price, 'amount') }}
                                            </p>
                                        </td>
                                        <td><input type="number" class="form-control respone2 qty"
                                                name="qty_{{ $lines->id }}" id="qty_{{ $lines->id }}"
                                                style="width: 100px;"
                                                value="{{ \App\Helpers\GlobalFunction::numberFormate($lines->quantity, 'quantity') }}"
                                                data-line_id={{ $lines->id }} data-document_no= {{$document_no}}></td>
                                        <td><input type="text" class="form-control respone2 des"
                                                name="des_{{ $lines->id }}" id="des_{{ $lines->id }}"
                                                style="width: 100px;" placeholder=" % or $"
                                                value="{{ \App\Helpers\GlobalFunction::numberFormate($lines->discount_amount, 'amount') }}"
                                                data-line_id={{ $lines->id }} data-document_no= {{$document_no}}> </td>
                                        <td>
                                            <p class="respon3">
                                                {{ \App\Helpers\GlobalFunction::numberFormate($lines->amount, 'amount') }}
                                            </p>
                                        </td>

                                    </tr>
                                @endforeach

                            @endif
                        @endif
                    </tbody>
                    </thead>
                </table>
            </div>
        </div>
        <div class="tab-pane fade h-100" id="orderHistoryTab">
            <div class="h-100 d-flex align-items-center justify-content-center text-center p-20">
                <div>
                    <div class="mb-3 mt-n5">
                        <svg width="6em" height="6em" viewBox="0 0 16 16" class="text-gray-300" fill="currentColor"
                            xmlns="http://www.w3.org/2000/svg">
                            <path fill-rule="evenodd"
                                d="M14 5H2v9a1 1 0 0 0 1 1h10a1 1 0 0 0 1-1V5zM1 4v10a2 2 0 0 0 2 2h10a2 2 0 0 0 2-2V4H1z" />
                            <path d="M8 1.5A2.5 2.5 0 0 0 5.5 4h-1a3.5 3.5 0 1 1 7 0h-1A2.5 2.5 0 0 0 8 1.5z" />
                        </svg>
                    </div>
                    <h4>No order history found</h4>
                </div>
            </div>
        </div>
    </div>
    <div class="pos-sidebar-footer">
       @include('pos.pos_total')
    </div>
</div>
