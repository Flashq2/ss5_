<style>
    .control_image{
        width: 100%;
        height: 300px;
        border-radius: 20px;
        border: none;
        border: 1px solid rgba(0, 0, 0, 0.226);

    }
    .control_image img{
        width: 100%;
        height: 100%;
        object-fit: contain;
    }
    #file{
      position: absolute;
      width: 99%;
      opacity: 0;
      height: 300px;
    }
</style>
<div class="modal fade" id="exampleModal" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
      <div class="modal-content ">
        <div class="modal-header">
          <h5 class="modal-title fs-5" id="exampleModalLabel">Preview Image</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
          <form method="post" enctype="multipart/form-data" id="imgForm">
                    <input type="file" name="file" id="file">
          </form>
          
          <div class="control_image">
            <img src="{{asset('/img/loadi')}}" class="pre_img" alt="">
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary resetImage" data-code="">Delete</button>
          <button type="button" class="btn btn-primary uploadImage" style="background-color:rgb(0, 162, 255)" data-code="">Upload</button>
        </div>
      </div>
    </div>
  </div>