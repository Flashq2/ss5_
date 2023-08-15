
<style>
    @keyframes animate8345 {
  0%,100% {
    filter: hue-rotate(0deg);
  }

  50% {
    filter: hue-rotate(360deg);
  }
}

.loader {
  color: rgb(0, 0, 0);
  background: linear-gradient(to right, #2d60ec, #3ccfda);
  font-size: 30px;
  -webkit-text-fill-color: transparent;
  -webkit-background-clip: text;
  animation: animate8345 9s linear infinite;
  font-weight: bold;
  position: fixed;
  top: 50%;
  left: 50%;
  transform: translate(-50%, -50%);
  text-align: center;
}
.sty_loader{
    width: 100%;
    height: 100%;
    position: fixed;
    background-color: rgba(144, 136, 219, 0.253);
    top: 0;
    z-index: 100;
    left: 0;
    z-index: 99999999999;
}
.control_imga{
    width: 200px;
    height: 200px;
    background-color: aliceblue;
    overflow: hidden;
    border-radius: 5px;

}
.control_imga img{
    width: 100%;
    height: 100%;
    object-fit: contain;
}
</style>
<div class="sty_loader">
    <div class="loader">
        <div class="control_imga">
            <img src="{{asset('/img/loading-wtf.gif')}}" alt="">
        </div>
        loading...
    </div>
</div>
