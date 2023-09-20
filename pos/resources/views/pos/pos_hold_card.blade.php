<div class="modal fade" id="hold" tabindex="-1" aria-labelledby="hold" aria-hidden="true">
    <div class="modal-dialog">
    <div class="modal-content">
        <div class="modal-header">
        <h5 class="modal-title" id="hold">Suspend Sale</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
           <form action="">
            <label for="">Reference Note</label>
            <input type="text" name="refer" id="refer" class="form-control">
           </form>
        </div>
        <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
        <button type="button" class="btn btn-primary on_hold" data-document_no = {{$document_no}}>Yes</button>
        </div>
    </div>
    </div>
</div>