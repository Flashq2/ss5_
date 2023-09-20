<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Document</title>
    <link crossorigin="anonymous" media="all" rel="stylesheet"
        href="https://github.githubassets.com/assets/primer-15214e5f9104.css" />
    <link crossorigin="anonymous" media="all" rel="stylesheet"
        href="https://github.githubassets.com/assets/global-38f5d4710d3e.css" />
    <link crossorigin="anonymous" media="all" rel="stylesheet"
        href="https://github.githubassets.com/assets/light-983b05c0927a.css" />
        <script src="http://cdnjs.cloudflare.com/ajax/libs/gsap/1.18.0/TweenMax.min.js"></script>
    <style>
        .mt-3 {
            width: 400px;
            border-radius: 5px !important;
            margin: 0 auto;
            height: 250px;

        }

        .control-img {
            margin: 0 auto;
            width: 500px;
            display: flex;
            justify-content: center;
            align-items: unset;
        }

        .control-img img {
            width: 200px;
            height: 100px;
            object-fit: contain;
            margin: 0 auto;
        }

        /* Header */
        .large-header {
            position: relative;
            width: 100%;
            background: rgba(0, 0, 0, 0.76);
            overflow: hidden;
            background-size: cover;
            background-position: center center;
            z-index: 1;
        }

        #large-header {
            /* background-image: url('https://www.marcoguglie.it/Codepen/AnimatedHeaderBg/demo-1/img/demo-1-bg.jpg'); */
        }

        .main-title {
            position: absolute;
            margin: 0;
            padding: 0;
            color: #f9f1e9;
            text-align: center;
            top: 50%;
            left: 50%;
            -webkit-transform: translate3d(-50%, -50%, 0);
            transform: translate3d(-50%, -50%, 0);
        }

        .demo-1 .main-title {
            text-transform: uppercase;
            font-size: 4.2em;
            letter-spacing: 0.1em;
        }

        .main-title .thin {
            font-weight: 200;
        }

        @media only screen and (max-width : 768px) {
            .demo-1 .main-title {
                font-size: 3em;
            }
        }
        .control-login{
            position: absolute;
            top: 50%;
            z-index: 100;
            left: 50%;
            transform: translate(-50%,-50%)
        }
    </style>
</head>

<body>
    <div id="large-header" class="large-header">
        <canvas id="demo-canvas"></canvas>
        <h1 class="main-title">Connect <span class="thin">Three</span></h1>
    </div>
    <br>
    <div class="control-login">
        <div class="control-img">
            <img src="https://www.setecu.com/images/sampledata/icetheme/logo.png" alt="">
        </div>
        <div class="auth-form-body mt-3">
            </option>
            </form>
            <form form method="POST" action="{{ route('login') }}">
                @csrf
                <label for="login_field">
                    Username or email address
                </label>
                <input id="email" class="form-control form-control input-block js-password-field" type="email"
                    name="email" :value="old('email')" required autofocus autocomplete="username">
                <div class="position-relative">
                    <label for="password">
                        Password
                    </label>
                    <input id="password" class="form-control form-control input-block js-password-field" type="password"
                        name="password" required autocomplete="current-password" />
                    <input type="submit" name="commit" value="Sign in" class="btn btn-primary btn-block js-sign-in-button"
                        data-disable-with="Signing in…" data-signin-label="Sign in"
                        data-sso-label="Sign in with your identity provider" development="false" />
    
                    <a class="label-link position-absolute top-0 right-0" id="forgot-password" tabindex="0"
                        href="/password_reset">Forgot password?</a>
                </div>
            </form>
        </div>
    </div>
    
    </div>

</body>
<script>
    (function() {

        var width, height, largeHeader, canvas, ctx, points, target, animateHeader = true;

        // Main
        initHeader();
        initAnimation();
        addListeners();

        function initHeader() {
            width = window.innerWidth;
            height = window.innerHeight;
            target = {
                x: width / 2,
                y: height / 2
            };

            largeHeader = document.getElementById('large-header');
            largeHeader.style.height = height + 'px';

            canvas = document.getElementById('demo-canvas');
            canvas.width = width;
            canvas.height = height;
            ctx = canvas.getContext('2d');

            // create points
            points = [];
            for (var x = 0; x < width; x = x + width / 20) {
                for (var y = 0; y < height; y = y + height / 20) {
                    var px = x + Math.random() * width / 20;
                    var py = y + Math.random() * height / 20;
                    var p = {
                        x: px,
                        originX: px,
                        y: py,
                        originY: py
                    };
                    points.push(p);
                }
            }

            // for each point find the 5 closest points
            for (var i = 0; i < points.length; i++) {
                var closest = [];
                var p1 = points[i];
                for (var j = 0; j < points.length; j++) {
                    var p2 = points[j]
                    if (!(p1 == p2)) {
                        var placed = false;
                        for (var k = 0; k < 5; k++) {
                            if (!placed) {
                                if (closest[k] == undefined) {
                                    closest[k] = p2;
                                    placed = true;
                                }
                            }
                        }

                        for (var k = 0; k < 5; k++) {
                            if (!placed) {
                                if (getDistance(p1, p2) < getDistance(p1, closest[k])) {
                                    closest[k] = p2;
                                    placed = true;
                                }
                            }
                        }
                    }
                }
                p1.closest = closest;
            }

            // assign a circle to each point
            for (var i in points) {
                var c = new Circle(points[i], 2 + Math.random() * 2, 'rgba(64, 80, 227)');
                points[i].circle = c;
            }
        }

        // Event handling
        function addListeners() {
            if (!('ontouchstart' in window)) {
                window.addEventListener('mousemove', mouseMove);
            }
            window.addEventListener('scroll', scrollCheck);
            window.addEventListener('resize', resize);
        }

        function mouseMove(e) {
            var posx = posy = 0;
            if (e.pageX || e.pageY) {
                posx = e.pageX;
                posy = e.pageY;
            } else if (e.clientX || e.clientY) {
                posx = e.clientX + document.body.scrollLeft + document.documentElement.scrollLeft;
                posy = e.clientY + document.body.scrollTop + document.documentElement.scrollTop;
            }
            target.x = posx;
            target.y = posy;
        }

        function scrollCheck() {
            if (document.body.scrollTop > height) animateHeader = false;
            else animateHeader = true;
        }

        function resize() {
            width = window.innerWidth;
            height = window.innerHeight;
            largeHeader.style.height = height + 'px';
            canvas.width = width;
            canvas.height = height;
        }

        // animation
        function initAnimation() {
            animate();
            for (var i in points) {
                shiftPoint(points[i]);
            }
        }

        function animate() {
            if (animateHeader) {
                ctx.clearRect(0, 0, width, height);
                for (var i in points) {
                    // detect points in range
                    if (Math.abs(getDistance(target, points[i])) < 4000) {
                        points[i].active = 0.3;
                        points[i].circle.active = 0.6;
                    } else if (Math.abs(getDistance(target, points[i])) < 20000) {
                        points[i].active = 0.1;
                        points[i].circle.active = 0.3;
                    } else if (Math.abs(getDistance(target, points[i])) < 40000) {
                        points[i].active = 0.02;
                        points[i].circle.active = 0.1;
                    } else {
                        points[i].active = 0;
                        points[i].circle.active = 0;
                    }

                    drawLines(points[i]);
                    points[i].circle.draw();
                }
            }
            requestAnimationFrame(animate);
        }

        function shiftPoint(p) {
            TweenLite.to(p, 1 + 1 * Math.random(), {
                x: p.originX - 50 + Math.random() * 100,
                y: p.originY - 50 + Math.random() * 100,
                ease: Circ.easeInOut,
                onComplete: function() {
                    shiftPoint(p);
                }
            });
        }

        // Canvas manipulation
        function drawLines(p) {
            if (!p.active) return;
            for (var i in p.closest) {
                ctx.beginPath();
                ctx.moveTo(p.x, p.y);
                ctx.lineTo(p.closest[i].x, p.closest[i].y);
                ctx.strokeStyle = 'rgba(224, 63, 224,' + p.active + ')';
                ctx.stroke();
            }
        }

        function Circle(pos, rad, color) {
            var _this = this;

            // constructor
            (function() {
                _this.pos = pos || null;
                _this.radius = rad || null;
                _this.color = color || null;
            })();

            this.draw = function() {
                if (!_this.active) return;
                ctx.beginPath();
                ctx.arc(_this.pos.x, _this.pos.y, _this.radius, 0, 2 * Math.PI, false);
                ctx.fillStyle = 'rgba(64, 80, 227,' + _this.active + ')';
                ctx.fill();
            };
        }

        // Util
        function getDistance(p1, p2) {
            return Math.pow(p1.x - p2.x, 2) + Math.pow(p1.y - p2.y, 2);
        }

    })();
</script>

</html>
