<?php
//
// File: index.php
// Author: Charles Parry
// Date: 5/7/2022
//
//

$pageBreadcrumb = "Contracts Home";
$pageTitle = "Contracts";


if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
require_once("includes/header.php");

?>
		<!--**********************************
            Content body start
        ***********************************-->
        <div class="content-body">
            <!-- row -->
			<div class="container-fluid">
				<div class="row">
					<div class="col-xl-9 col-xxl-8">
						<div class="row">
							Please select your form from the left hand nav.
						</div>
					</div>
				</div>
            </div>
        </div>
        <!--**********************************
            Content body end
        ***********************************-->

        <!--**********************************
            Footer start
        ***********************************-->
        <div class="footer">
            <div class="copyright">
                <p>Copyright &copy. 2022 Vital Trends</p>
            </div>
        </div>
        <!--**********************************
            Footer end
        ***********************************-->

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
	<script src="./vendor/chart.js/Chart.bundle.min.js"></script>
	<script src="./vendor/owl-carousel/owl.carousel.js"></script>

	<!-- Chart piety plugin files -->
    <script src="./vendor/peity/jquery.peity.min.js"></script>

	<!-- Apex Chart -->
	<!-- <script src="./vendor/apexchart/apexchart.js"></script> -->

	<!-- Dashboard 1 -->
	<!-- <script src="./js/dashboard/dashboard-1.js"></script> -->
    <script src="./js/custom.min.js"></script>
	<script src="./js/deznav-init.js"></script>


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
	<script src="js/demo.js"></script>
<script src="js/styleSwitcher.js"></script>
</body>
</html>