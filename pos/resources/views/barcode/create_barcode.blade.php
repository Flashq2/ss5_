
<div class="row">
    <div class="col-12"> 
        <div class="row">
        @for ($i = 0; $i < $barcode->quantity; $i++)
        <div class="col-1">
        </div>
            <div class="col-4">
                <img  class="{{$barcode->item_no}}" >
                        <script>
                            $(document).ready(function () {
                                JsBarcode(".{{$barcode->item_no}}",'{{$barcode->item_no}}', {
                                    height: 100,
                                    displayValue: true,
                                    textPosition:'bottom',
                                    fontSize:20,
                                });
                            });
                        </script>
            </div>
            <div class="col-1">
            
            </div>
        @endfor
    </div>
    </div>
</div>