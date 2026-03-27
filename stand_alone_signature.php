<?php
//
// File: dealer_addendum.php
// Author: Charles Parry
// Date: 5/12/2022
//
//

// Turn on error reporting
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
//mysqli_report(MYSQLI_REPORT_ALL);
error_reporting(E_ALL);


$pageBreadcrumb = "Dealer Agent Signature";
$pageTitle = "Dealer Agent Signature";


// Connect to DB
require_once "includes/dbConnect.php";

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'PHPMailer/vendor/autoload.php';

if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }

// Variables.
$agentID = "";
$form_err = "";


// Get a dealer ID from session.
$agentID = base64_decode($_GET["agentID"]);
// $agentID = $_SESSION["id"];

//Get Agent Info
$query = $query = "SELECT * FROM Pers WHERE Pers_ID = ".$agentID;
$result = $link->query($query);
$row = mysqli_fetch_assoc($result);

$agentName  = $row["Pers_Full_Nm"];
$agentSignature = $row['w9_signature'];
// Process form data when form is submitted.
if ($_SERVER["REQUEST_METHOD"] == "POST") {

	// Save the signature
    // handle signature
    $fileName = "";
    try {

        $fields = (object)$_POST;
        $image = base64_decode($fields->signature);
        $imageData = $_POST['signature'];
        $imageDataBase30 = $_POST['base30'];
        list($type, $imageData) = explode(';', $imageData);
        list(, $extension) = explode('/', $type);
        list(, $imageData) = explode(',', $imageData);
		// $fileName = uniqid().'.'.$extension;
        $data = explode('+', $extension);
        $fileName = uniqid() . '.' . $data[0];
        $imageData = base64_decode($imageData);
        $image = 'uploads/' . $fileName;
        file_put_contents($image, $imageData);
        $my_date = date("Y-m-d H:i:s");

    } catch (Exception $exception) {
		//echo json_encode(['status'=>400,'message'=>$exception->getMessage()]);
    }

	// Update tracker for dealer forms, to indicate the addendum is signed
    $stmt = mysqli_prepare($link, "UPDATE Pers SET w9_signature=?, W9_Signature_Base30=? WHERE Pers_ID=?");

	/* Bind variables to parameters */
    $val1 = $fileName;
    $val2 = $imageDataBase30;
    $val3 = $agentID;

    mysqli_stmt_bind_param($stmt, "ssi", $val1, $val2, $val3);

	/* Execute the statement */
    $result = mysqli_stmt_execute($stmt);

    if($result)
    {
        // Redirect to next form
        header("location: index.php");
        exit;
    }

    die();

 }

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title>Vital Trends - Portal - Dealer Agreement</title>
    <!-- Favicon icon -->
    <link rel="icon" type="image/png" sizes="16x16" href="./images/favicon.ico">
    <link href="./vendor/jqvmap/css/jqvmap.min.css" rel="stylesheet">
	<link rel="stylesheet" href="./vendor/chartist/css/chartist.min.css">
    <link href="./vendor/bootstrap-select/dist/css/bootstrap-select.min.css" rel="stylesheet">
	<link href="./vendor/owl-carousel/owl.carousel.css" rel="stylesheet">
    <link href="./css/style.css" rel="stylesheet">
	<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@100;200;300;400;500;600;700;800;900&family=Roboto:wght@100;300;400;500;700;900&display=swap" rel="stylesheet">
    <style>
        .logo{
            width: 500px;
            height: 154px;
            margin: 0 auto;
            display: flex;
            align-items: center;
        }
        .logo img{
            max-width: 100%;
            height: auto;
        }

        .dealer-form{
            position: relative;
            background-position: center;
            background-repeat: no-repeat;
            background-size: contain;
            z-index: 1;
        }
        .dealer-form .form-control{
            background: transparent;
            color: #3d4465;
            border:1px solid rgb(199, 200, 201);
        }
        .dealer-form .form-group label{
            color: #3d4465;
        }

        .watermark{
            top: 60%;
            left: 60%;
            transform: translate(-50%, -50%);
            opacity: 0.1;
            z-index: -1;
            position: fixed;
            max-width: 450px;
        }
        .watermark img{
            max-width: 100%;
            height: auto;
        }

        .terms-text{
            border: 1px solid rgb(199, 200, 201);
            border-radius: 4px;
            padding: 10px 20px !important;
            margin: 5px 5px 15px;
        }
        .brand-logo img{
            max-width: 100%;
            height: auto;
        }
        .terms-text ol li{
            list-style: auto;
            padding: 0 10px;
            margin: 0 0 0 15px;
        }
        .terms-text ol li ol li{
            list-style: lower-alpha;
        }
        .terms-text ol li ol li ol li{
            list-style: lower-roman;
        }
		button:disabled {
            cursor: not-allowed;
             pointer-events: all !important;
        }
    </style>
</head>
<body>
    <!--*******************
        Preloader start
    ********************-->
    <div id="preloader">
        <div class="sk-three-bounce">
            <div class="sk-child sk-bounce1"></div>
            <div class="sk-child sk-bounce2"></div>
            <div class="sk-child sk-bounce3"></div>
        </div>
    </div>
    <!--*******************
        Preloader end
    ********************-->

    <!--**********************************
        Main wrapper start
    ***********************************-->
    <div id="main-wrapper">

		
		<!--**********************************
            Content body start
        ***********************************-->
        <div class="content-body" style="padding-top:0; margin:auto; width:80%;">
            <!-- row -->
			<div class="container-fluid">
                <?php require_once("includes/common_page_content.php"); ?>
                <div class="row">
                    <div class="col-md-12">
                    <?php if(isset($_SESSION["success"])){?>
                    <div class="alert alert-success" role="alert">
                        <?php  echo $_SESSION["success"];?>
                    </div>
                    <?php
                    unset($_SESSION['success']);
                     } ?>
                        <div class="card">
                            <div class="card-header text-center" style="justify-content: normal;">
                                <h4 class="card-title">Stand Alone Signature</h4>
                                <p style="margin: 0; padding: 0 0 0 10px;"> <?php echo '('.$agentName.')';   ?></p>
                            </div>
                            <div class="card-body">
                                <div class="basic-form dealer-form">
                                    <div class="watermark">
                                        <img src="images/logo_large_bg.png" alt="">
                                    </div>
                                    <form name="dealerAgentForm" id="SignatureForm" method="POST" action="">
                                        <div class="form-row">
                                            <div class="form-group col-md-12">
                                            <?php if($agentSignature == ""){ ?>
                                            <div class="form-group col-md-12">
                                            </div>
                                            <div class="form-group col-md-12 row">
                                                <div class="form-group col-md-6">
                                                    <h5 class="font-weight-normal">Sign here</h5>
                                                    <div class="signature"></div>
                                                    <span style="color: red;display: none;" id="signatureE">Please Enter Signature Data..!</span>
                                                </div>
                                            </div>
                                        </div>
                                        <button type="button" id="SignatureFormSubmit" class="btn btn-primary">Submit</button>
                                        <?php } else {
                                        ?>
                                            <div class="form-group col-md-6">
                                                <h5 class="text-primary d-inline">Signature</h5>
                                                <img class="text-muted mb-0" src="uploads/<?php echo $agentSignature; ?>" alt="">
                                            </div>

                                        <?php } ?>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!--**********************************
                dealer_agreement form
            ***********************************-->
        </div>
        <!--**********************************
            Content body end
        ***********************************-->
    </div>

<!--**********************************
Footer start
***********************************-->
<div class="footer" style="padding-left:0;">
<div class="copyright">
    <p>Copyright &copy; 2022 Vital Trends</p>
</div>
</div>
<!--**********************************
Footer end
***********************************-->

<?php if(false){ ?>

<!-- Plugin scripts -->
<script src="vendors/bundle.js"></script>

<!-- Chartjs -->
<script src="vendors/charts/chartjs/chart.min.js"></script>

<!-- Circle progress -->
<script src="vendors/circle-progress/circle-progress.min.js"></script>

<!-- Peity -->
<script src="vendors/charts/peity/jquery.peity.min.js"></script>
<script src="assets/js/examples/charts/peity.js"></script>

<!-- Datepicker -->
<script src="vendors/datepicker/daterangepicker.js"></script>

<!-- Slick -->
<script src="vendors/slick/slick.min.js"></script>

<!-- Vamp -->
<script src="vendors/vmap/jquery.vmap.min.js"></script>
<script src="vendors/vmap/maps/jquery.vmap.usa.js"></script>
<script src="assets/js/examples/vmap.js"></script>

<!-- Dashboard scripts -->
<script src="assets/js/examples/dashboard.js"></script>
<div class="colors"> <!-- To use theme colors with Javascript -->
<div class="bg-primary"></div>
<div class="bg-primary-bright"></div>
<div class="bg-secondary"></div>
<div class="bg-secondary-bright"></div>
<div class="bg-info"></div>
<div class="bg-info-bright"></div>
<div class="bg-success"></div>
<div class="bg-success-bright"></div>
<div class="bg-danger"></div>
<div class="bg-danger-bright"></div>
<div class="bg-warning"></div>
<div class="bg-warning-bright"></div>
</div>

<!-- App scripts -->
<script src="assets/js/app.min.js"></script>
</body>
</html>

<?php
}
?>

		<!--**********************************
           Support ticket button start
        ***********************************-->

        <!--**********************************
           Support ticket button end
        ***********************************-->


    </div>
    <!--**********************************
        Main wrapper end
    ***********************************-->

    <!--**********************************
        Scripts
    ***********************************-->
    <!-- Required vendors -->
    <script src="./vendor/global/global.min.js"></script>
	<script src="./vendor/bootstrap-select/dist/js/bootstrap-select.min.js"></script>

	<!-- Chart piety plugin files -->
    <script src="./vendor/peity/jquery.peity.min.js"></script>

	<!-- Dashboard 1 -->
	<script src="./js/dashboard/dashboard-1.js"></script>
    <script src="./js/custom.min.js"></script>
	<script src="./js/deznav-init.js"></script>
    <script src="./js/jSignature/jSignature.min.js"></script>
    <script src="./js/jSignature/jSignInit.js"></script>
    <script src="./js/custom-validation.js"></script>
<script>
      jQuery('input').on('keypress', function(e) {
        if(e.which === 13)
		{
            var flag1 = 0;

            if ($(".jSignature").jSignature("getData", "native").length == 0) {
            $("#signatureE").css("display", "block");
            } else {
            $("#signatureE").css("display", "none");
            flag1 = 1;
            }
        }
      });
</script>
	<script>
		function carouselReview(){
			/*  testimonial one function by = owl.carousel.js */
			function checkDirection() {
				var htmlClassName = document.getElementsByTagName('html')[0].getAttribute('class');
				if(htmlClassName == 'rtl') {
					return true;
				} else {
					return false;

				}
			}

			jQuery('.testimonial-one').owlCarousel({
				loop:true,
				autoplay:true,
				margin:30,
				nav:false,
				dots: false,
				rtl: checkDirection(),
				left:true,
				navText: ['', ''],
				responsive:{
					0:{
						items:1
					},
					1200:{
						items:2
					},
					1600:{
						items:3
					}
				}
			})
		}
		jQuery(window).on('load',function(){
			setTimeout(function(){
				carouselReview();
			}, 1000);
		});
	</script>
    <script>
        function printpart () {
            var printwin = window.open("");
            printwin.document.write(document.getElementById("toprint").innerHTML);
            printwin.stop();
            printwin.print();
            printwin.close();
        }
    </script>

    <script>
        //sketch lib
        (function () {
            var __slice = [].slice;

            (function ($) {
                var Sketch;
                $.fn.sketch = function () {
                    var args, key, sketch;
                    key = arguments[0], args = 2 <= arguments.length ? __slice.call(arguments, 1) : [];
                    if (this.length > 1) {
                        $.error('Sketch.js can only be called on one element at a time.');
                    }
                    sketch = this.data('sketch');
                    if (typeof key === 'string' && sketch) {
                        if (sketch[key]) {
                            if (typeof sketch[key] === 'function') {
                                return sketch[key].apply(sketch, args);
                            } else if (args.length === 0) {
                                return sketch[key];
                            } else if (args.length === 1) {
                                return sketch[key] = args[0];
                            }
                        } else {
                            return $.error('Sketch.js did not recognize the given command.');
                        }
                    } else if (sketch) {
                        return sketch;
                    } else {
                        this.data('sketch', new Sketch(this.get(0), key));
                        return this;
                    }
                };
                Sketch = (function () {

                    function Sketch(el, opts) {
                        this.el = el;
                        this.canvas = $(el);
                        this.context = el.getContext('2d');
                        this.options = $.extend({
                            toolLinks: true,
                            defaultTool: 'marker',
                            defaultColor: '#000000',
                            defaultSize: 2
                        }, opts);
                        this.painting = false;
                        this.color = this.options.defaultColor;
                        this.size = this.options.defaultSize;
                        this.tool = this.options.defaultTool;
                        this.actions = [];
                        this.action = [];
                        this.canvas.bind('click mousedown mouseup mousemove mouseleave mouseout touchstart touchmove touchend touchcancel', this.onEvent);
                        if (this.options.toolLinks) {
                            $('body').delegate("a[href=\"#" + (this.canvas.attr('id')) + "\"]", 'click', function (e) {
                                var $canvas, $this, key, sketch, _i, _len, _ref;
                                $this = $(this);
                                $canvas = $($this.attr('href'));
                                sketch = $canvas.data('sketch');
                                _ref = ['color', 'size', 'tool'];
                                for (_i = 0, _len = _ref.length; _i < _len; _i++) {
                                    key = _ref[_i];
                                    if ($this.attr("data-" + key)) {
                                        sketch.set(key, $(this).attr("data-" + key));
                                    }
                                }
                                if ($(this).attr('data-download')) {
                                    sketch.download($(this).attr('data-download'));
                                }
                                return false;
                            });
                        }
                    }

                    Sketch.prototype.download = function (format) {
                        var mime;
                        format || (format = "png");
                        if (format === "jpg") {
                            format = "jpeg";
                        }
                        mime = "image/" + format;
                        return window.open(this.el.toDataURL(mime));
                    };

                    Sketch.prototype.set = function (key, value) {
                        this[key] = value;
                        return this.canvas.trigger("sketch.change" + key, value);
                    };

                    Sketch.prototype.startPainting = function () {
                        this.painting = true;
                        return this.action = {
                            tool: this.tool,
                            color: this.color,
                            size: parseFloat(this.size),
                            events: []
                        };
                    };


                    Sketch.prototype.stopPainting = function () {
                        if (this.action) {
                            this.actions.push(this.action);
                        }
                        this.painting = false;
                        this.action = null;
                        return this.redraw();
                    };

                    Sketch.prototype.onEvent = function (e) {
                        if (e.originalEvent && e.originalEvent.targetTouches) {
                            e.pageX = e.originalEvent.targetTouches[0].pageX;
                            e.pageY = e.originalEvent.targetTouches[0].pageY;
                        }
                        $.sketch.tools[$(this).data('sketch').tool].onEvent.call($(this).data('sketch'), e);
                        e.preventDefault();
                        return false;
                    };

                    Sketch.prototype.redraw = function () {
                        var sketch;
                        //this.el.width = this.canvas.width();
                        this.context = this.el.getContext('2d');
                        sketch = this;
                        $.each(this.actions, function () {
                            if (this.tool) {
                                return $.sketch.tools[this.tool].draw.call(sketch, this);
                            }
                        });
                        if (this.painting && this.action) {
                            return $.sketch.tools[this.action.tool].draw.call(sketch, this.action);
                        }
                    };

                    return Sketch;

                })();
                $.sketch = {
                    tools: {}
                };
                $.sketch.tools.marker = {
                    onEvent: function (e) {
                        switch (e.type) {
                            case 'mousedown':
                            case 'touchstart':
                                if (this.painting) {
                                    this.stopPainting();
                                }
                                this.startPainting();
                                break;
                            case 'mouseup':
                            //return this.context.globalCompositeOperation = oldcomposite;
                            case 'mouseout':
                            case 'mouseleave':
                            case 'touchend':
                            //this.stopPainting();
                            case 'touchcancel':
                                this.stopPainting();
                        }
                        if (this.painting) {
                            this.action.events.push({
                                x: e.pageX - this.canvas.offset().left,
                                y: e.pageY - this.canvas.offset().top,
                                event: e.type
                            });
                            return this.redraw();
                        }
                    },
                    draw: function (action) {
                        var event, previous, _i, _len, _ref;
                        this.context.lineJoin = "round";
                        this.context.lineCap = "round";
                        this.context.beginPath();
                        this.context.moveTo(action.events[0].x, action.events[0].y);
                        _ref = action.events;
                        for (_i = 0, _len = _ref.length; _i < _len; _i++) {
                            event = _ref[_i];
                            this.context.lineTo(event.x, event.y);
                            previous = event;
                        }
                        this.context.strokeStyle = action.color;
                        this.context.lineWidth = action.size;
                        return this.context.stroke();
                    }
                };
                return $.sketch.tools.eraser = {
                    onEvent: function (e) {
                        return $.sketch.tools.marker.onEvent.call(this, e);
                    },
                    draw: function (action) {
                        var oldcomposite;
                        oldcomposite = this.context.globalCompositeOperation;
                        this.context.globalCompositeOperation = "destination-out";
                        action.color = "rgba(0,0,0,1)";
                        $.sketch.tools.marker.draw.call(this, action);
                        return this.context.globalCompositeOperation = oldcomposite;
                    }
                };
            })(jQuery);

        }).call(this);


        (function ($) {
            $.fn.SignaturePad = function (options) {

                //update the settings
                var settings = $.extend({
                    allowToSign: true,
                    img64: 'data:image/gif;base64,R0lGODlhAQABAIAAAAAAAP///yH5BAEAAAAALAAAAAABAAEAAAIBRAA7',
                    border: '1px solid #c7c8c9',
                    width: '300px',
                    height: '150px',
                    callback: function () {
                        return true;
                    }
                }, options);

                //control should be a textbox
                //loop all the controls
                var id = 0;

                //add a very big pad
                var big_pad = $('#signPadBig');
                var back_drop = $('#signPadBigBackDrop');
                var canvas = undefined;
                if (big_pad.length == 0) {

                    back_drop = $('<div>')
                    back_drop.css('position', 'fixed');
                    back_drop.css('top', '0');
                    back_drop.css('right', '0');
                    back_drop.css('bottom', '0');
                    back_drop.css('left', '0');
                    back_drop.css('z-index', '1040 !important');
                    back_drop.css('background-color', '#000');
                    back_drop.css('display', 'none');
                    back_drop.css('filter', 'alpha(opacity=50)');
                    back_drop.css('opacity', '0.5');
                    $('body').append(back_drop);

                    big_pad = $('<div>');
                    big_pad.css('display', 'none');
                    big_pad.css('position', 'fixed');
                    big_pad.css('margin', 'auto');
                    big_pad.css('top', '0');
                    big_pad.css('bottom', '0');
                    big_pad.css('right', '0');
                    big_pad.css('left', '0');
                    big_pad.css('z-index', '1000002 !important');
                    big_pad.css('overflow', 'hidden');
                    big_pad.css('outline', '0');
                    big_pad.css('-webkit-overflow-scrolling', 'touch');

                    big_pad.css('right', '0');
                    big_pad.css('border', '1px solid #c8c8c8');
                    big_pad.css('padding', '15px');
                    big_pad.css('background-color', 'white');
                    big_pad.css('margin-top', 'auto');
                    big_pad.css('width', '60%');
                    big_pad.css('height', '40%');
                    big_pad.css('z-index', '999999999');
                    big_pad.css('border-radius', '10px');
                    big_pad.attr('id', 'signPadBig');
                    $('body').append(big_pad);

                    var update_canvas_size = function () {
                        var w = big_pad.width() //* 0.95;
                        var h = big_pad.height() - 55;

                        canvas.attr('width', w);
                        // canvas.attr('height', h);
                    }


                    canvas = $('<canvas>');
                    canvas.css('display', 'block');
                    canvas.css('margin', '0 auto');
                    canvas.css('border', '1px solid #c8c8c8');
                    canvas.css('border-radius', '10px');
                    //canvas.css('width', '90%');
                    canvas.css('height', 'auto');
                    big_pad.append(canvas);

                    update_canvas_size();
                    $(window).on('resize', function () {
                        update_canvas_size();
                    });

                    var clearCanvas = function () {
                        canvas.sketch().action = null;
                        canvas.sketch().actions = [];       // this line empties the actions.
                        var ctx = canvas[0].getContext("2d");
                        ctx.clearRect(0, 0, canvas[0].width, canvas[0].height);
                        return true
                    }

                    var _get_base64_value = function () {
                        var text_control = $.data(big_pad[0], 'control');  //settings.control; // $('#' + big_pad.attr('id'));
                        return $(text_control).val();
                    }

                    var copyCanvas = function () {
                        //get data from bigger pad
                        var sigData = canvas[0].toDataURL("image/png");

                        var _img = new Image;
                        _img.onload = resizeImage;
                        _img.src = sigData;

                        var targetWidth = canvas.width();
                        var targetHeight = canvas.height();

                        function resizeImage() {
                            var imageToDataUri = function (img, width, height) {

                                // create an off-screen canvas
                                var canvas = document.createElement('canvas'),
                                    ctx = canvas.getContext('2d');

                                // set its dimension to target size
                                canvas.width = width;
                                canvas.height = height;

                                // draw source image into the off-screen canvas:
                                ctx.drawImage(img, 0, 0, width, height);

                                // encode image to data-uri with base64 version of compressed image
                                return canvas.toDataURL();
                            }

                            var newDataUri = imageToDataUri(this, targetWidth, targetHeight);
                            var control_img = $.data(big_pad[0], 'img');
                            if (control_img)
                                $(control_img).attr("src", newDataUri);

                            var text_control = $.data(big_pad[0], 'control');  //settings.control; // $('#' + big_pad.attr('id'));
                            if (text_control)
                                $(text_control).val(newDataUri);
                        }
                    }

                    var buttons = [
                        {
                            title: 'Close',
                            callback: function () {
                                clearCanvas();
                                big_pad.slideToggle(function () {
                                    back_drop.hide('fade');
                                });

                            }
                        },
                        {
                            title: 'Clear',
                            callback: function () {
                                clearCanvas();
                                if (settings.callback)
                                    settings.callback(_get_base64_value(), 'clear');
                            }
                        },
                        {
                            title: 'Accept',
                            callback: function () {
                                copyCanvas();
                                clearCanvas();
                                big_pad.slideToggle(function () {
                                    back_drop.hide('fade', function () {
                                        if (settings.callback)
                                            settings.callback(_get_base64_value(), 'accept');
                                    });
                                });
                            }
                        }].forEach(function (e) {
                        var btn = $('<button>');
                        btn.attr('type', 'button');
                        btn.css('border', '1px solid #c8c8c8');
                        btn.css('background-color', 'white');
                        btn.css('padding', '10px');
                        btn.css('display', 'block');
                        btn.css('margin-top', '15px');
                        btn.css('margin-right', '5px');
                        btn.css('cursor', 'pointer');
                        btn.css('border-radius', '5px');
                        btn.css('float', 'right');
                        btn.css('height', '40px');
                        btn.text(e.title);
                        btn.on('click', function () {
                            e.callback(e.title);
                        })
                        big_pad.append(btn);

                    });

                }
                else {
                    canvas = big_pad.find('canvas')[0];
                }

                //init the signpad
                if (canvas) {
                    var sign1big = $(canvas).sketch({ defaultColor: "#000", defaultSize: 5 });
                }

                //for each control
                return this.each(function () {

                    var control = $(this);
                    control.hide();

                    //get the control parent
                    var wrapper = control.parent();
                    var img = $('<img>');

                    //style it
                    img.css("cursor", "pointer");
                    img.css("border", settings.border);
                    img.css("height", settings.height);
                    img.css("width", settings.width);
                    img.css('border-radius', '5px')
                    img.attr("src", settings.img64);

                    if (typeof (wrapper) == 'object') {
                        wrapper.append(img);
                    }




                    //init the big sign pad
                    if (settings.allowToSign == true) {
                        //click to the pad bigger
                        img.on('click', function () {
                            //show the pad
                            back_drop.show();
                            big_pad.slideToggle();

                            //save control to use later
                            $.data(big_pad[0], 'img', img);
                            $.data(big_pad[0], 'control', control);

                            //settings.control = control;
                            //settings.img = img;
                        });
                    }
                });
            };


        })(jQuery);

        $(document).ready(function () {
            var sign = $('#txt').SignaturePad({
                allowToSign: true,
                img64: 'data:image/gif;base64,R0lGODlhAQABAIAAAAAAAP///yH5BAEAAAAALAAAAAABAAEAAAIBRAA7',
                border: '1px solid #c7c8c9',
                width: '300px',
                height: '150px',
                callback: function (data, action) {
                    console.log(data);
                }
            });
        })
    </script>
    <script>
	$(document).ready(function(){
		$(".moveToW9").click(function(){
			window.location.href='dealer_w9.php';
		});
	});
    </script>
    <script>
// Validation for dealer addendum form submission
$(document).ready(function () {
  $("#SignatureFormSubmit").click(function () {
    var flag1 = 0;

    if ($(".jSignature").jSignature("getData", "native").length == 0) {
      $("#signatureE").css("display", "block");
    } else {
      $("#signatureE").css("display", "none");
      flag1 = 1;
    }

    if (flag1 == 1) {
      let form = $("#SignatureForm");
      let signature = $(".signature").jSignature("getData", "svgbase64");
      $(".sign_hash").val(signature);
      let signatureBase30 = $(".signature").jSignature("getData", "base30");
      $(".base30").val(signatureBase30);
      $("#SignatureFormSubmit").prop("disabled", true);
      $("#SignatureFormSubmit").text("Dealer Loading......");
      form.submit();
    }
  });
});
    </script>
</body>
</html>