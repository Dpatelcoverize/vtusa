<?php
//
// File: warranty_uploads.php
// Author: Charles Parry
// Date: 8/04/2022
//
//

// Turn on error reporting
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
//mysqli_report(MYSQLI_REPORT_ALL);
error_reporting(E_ALL);


$pageBreadcrumb = "Warranty Uploads";
$pageTitle = "Warranty Uploads";


// Connect to DB
require_once "includes/dbConnect.php";

// DB Library
require_once "lib/dblib.php";

/**For encryption of the data */
require_once 'encrypt.php';


// Variables.
$dealerID = "";
$warrantyID = "";
$errorMessage = "";
$fileType = "";
$filename = "";
$ext = "";

$loopCounter=0;

$isQuote = "";

if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }


// Make sure a dealer is currently logged in, or go back to the Agreement
if(!(isset($_SESSION["userType"])) || !($_SESSION["userType"] == "dealer")){
    header("location: index.php");
    exit;
}

// Get a dealer ID from session.
if(!(isset($_SESSION["id"]))){
    header("location: index.php");
    exit;
}else{
	$dealerID = $_SESSION["id"];
}

if (isset($_SESSION["admin_id"])) {
	$adminID = $_SESSION["admin_id"];
}


if (isset($_GET["isQuote"])) {
	$isQuote = $_GET["isQuote"];
}


// Process form data when form is submitted.
if ($_SERVER["REQUEST_METHOD"] == "POST") {

	$_SESSION["errorMessage"]="";

	// Get the file details being uploaded
    if(isset($_POST["warrantyID"]) && ($_POST["warrantyID"]!="")){
		$warrantyID = $_POST["warrantyID"];

		// SECURITY make sure this dealer may edit this warranty
		$securityCheck = dealerOwnsWarranty($link,$dealerID,$warrantyID);
		if(!$securityCheck){
			if($isQuote=="Y"){
				header("location: warranty_pending.php?isQuote=Y");
			}else{
				header("location: warranty_pending.php");
			}
			exit;
		}

    }else{
        Header("Location: warranty_pending.php");
	    exit;
    }

	// Get the file type details
    if(isset($_POST["fileType"]) && ($_POST["fileType"]!="")){
		$fileType = $_POST["fileType"];
    }else{
		if($isQuote=="Y"){
			header("location: warranty_pending.php?isQuote=Y");
		}else{
			header("location: warranty_pending.php");
		}
		exit;
    }

    if(isset($_FILES['uploadedFile']['name']) && ($_FILES['uploadedFile']['name']!="")){
		$filename = $_FILES['uploadedFile']['name'];

	    $ext = pathinfo($filename,PATHINFO_EXTENSION);

		// Strip out bad characters which are upsetting the system (except for space)
		$filename = preg_replace('/[^A-Za-z0-9 ]/', '', $filename);

		// Now convert space to underscore
		$filename = str_replace(' ', '_', $filename);

		// Randomize the name a bit, in case the customer uploads a bunch of
		//  files with the same name.
		$filename = substr(md5(rand()), 0, 7).$filename;

    }else{
		if($isQuote=="Y"){
			header("location: warranty_pending.php?isQuote=Y");
		}else{
			header("location: warranty_pending.php");
		}
		exit;
    }


    $allowed = array("pdf" => "application/pdf","jpg" => "image/jpg","png" => "image/png");

    if(!array_key_exists(strtolower($ext),$allowed)){
        $_SESSION['status'] = "The file format is not acceptable";
		if($isQuote=="Y"){
			header("location: warranty_pending.php?isQuote=Y");
		}else{
			header("location: warranty_pending.php");
		}
		exit;
    }


	// Save the file

	// To stay organized, we will gather our files into a folder named for the contractID
	if (!file_exists("uploads/warranty_pdf/".$warrantyID)) {
		mkdir("uploads/warranty_pdf/".$warrantyID, 0777, true);
	}
	$filename = str_replace(" ", "_", $filename) ;

    move_uploaded_file($_FILES['uploadedFile']['tmp_name'],"uploads/warranty_pdf/".$warrantyID."/".$filename);



	// Save the path to the file in File_Assets for this warrantyID

	$query = "SELECT ul.Pers_ID FROM Usr_Loc ul, Pers p WHERE ul.Dlr_Acct_ID=" . $dealerID . " AND
			  ul.Pers_ID = p.Pers_ID AND p.Cntct_Prsn_For_Acct_Flg='Y';";
	$result = $link->query($query);
	$row = $result->fetch_assoc();

	$primary_Contact_Person_id = $row['Pers_ID'];


	// Insert file in our tracking table
	$stmt = mysqli_prepare($link, "INSERT INTO File_Assets (Acct_ID,Dealer_Pers_ID,VT_Pers_ID,Dealer_Cntrct_ID,
									Path_to_File,File_Asset_Type_ID,createdDate) VALUES (?,?,?,?,?,?,NOW())");




/* Bind variables to parameters */
	$val1 = $dealerID;
	$val2 = $primary_Contact_Person_id;
	$val3 = $adminID;
	$val4 = $warrantyID;
	$val5 = "/uploads/warranty_pdf/".$warrantyID."/".$filename;
	$val6 = $fileType;

	mysqli_stmt_bind_param($stmt, "iiiisi", $val1, $val2, $val3, $val4, $val5, $val6);


/* Execute the statement */
	$result = mysqli_stmt_execute($stmt);

	if($isQuote=="Y"){
		header("location: warranty_print.php?warrantyID=".encryptData($warrantyID)."&isQuote=Y");
	}else{
		if($result && $fileType == 17){
			$stmt = mysqli_prepare($link, "UPDATE Cntrct SET Finalized_Warranty_Flg=? WHERE Cntrct_ID=?");

			$val1 = "Y";
			$val2 = $warrantyID;

			mysqli_stmt_bind_param($stmt, "si", $val1, $val2);

			mysqli_stmt_execute($stmt);
		}
		header("location: warranty_print.php?warrantyID=".encryptData($warrantyID));
	}
	exit;


}else{
	// See if we are specifying a warrantyID in the URL request.
	if(isset($_GET["warrantyID"])){
		$warrantyID = $_GET["warrantyID"];

		// SECURITY make sure this dealer may edit this warranty
		$securityCheck = dealerOwnsWarranty($link,$dealerID,$warrantyID);
		if(!$securityCheck){
			if($isQuote=="Y"){
				header("location: warranty_pending.php?isQuote=Y");
			}else{
				header("location: warranty_pending.php");
			}
			exit;
		}

	}else{
		if($isQuote=="Y"){
			header("location: warranty_pending.php?isQuote=Y");
		}else{
			header("location: warranty_pending.php");
		}
		exit;
	}


}



require_once("includes/header.php");


if(isset($_SESSION["errorMessage"]) && ($_SESSION["errorMessage"]!="")){
	$errorMessage = $_SESSION["errorMessage"];
	$_SESSION["errorMessage"]="";
}else{
	$errorMessage = "";
}

?>

		<!--**********************************
            Content body start
        ***********************************-->
        <div class="content-body">
            <!-- row -->
			<div class="container-fluid">
                <div class="row">
                    <div class="col-md-6">
                        <div>
                            <img src="images/VTPoweredbyTNG.png" alt="Vital Trends Powered by TruNorth">
                        </div>
                    </div>
                    <div class="col-md-6">
						&nbsp;
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-12">
                        <div class="card">
                            <div class="card-header text-center">
                                <h4 class="card-title">Upload Files for <?php if($isQuote=="Y"){echo " Quote ";}else{ echo " Warranty ";}?></h4>
                            </div>
                            <div class="card-header text-center">
								<?php
								if($isQuote=="Y"){
								?>
                                <h5>(<a href="warranty_pending.php?showQuotes=Y">Return to Quote List</a>)</h5>
								<?php }else{ ?>
                                <h5>(<a href="warranty_pending.php">Return to Warranty List</a>)</h5>
								<?php } ?>
							</div>
							<?php
							if($errorMessage!=""){
							?>
								<div class="card-header text-center">
									<span style="color:red;">ERROR: <?php echo $errorMessage;?></span>
								</div>
							<?php
							}
							?>
                            <div class="card-body">
                                <div class="basic-form dealer-form">
                                    <div class="watermark">
                                        <img src="images/logo_large_bg.png" alt="">
                                    </div>

									<div class="form-row">

										<div class="form-group col-md-6">
											<label>Ink Signed Warranty<span class="text-danger"></span></label>
											<?php
												if($isQuote=="Y"){
													$fileTypeID = 16;
												}else{
													$fileTypeID = 17;
												}
												$filePathResult = getFileAssetForWarranty($link,$warrantyID,$fileTypeID);
												if($filePathResult!=0){
												?>
													<br />
													<img src="images/green_check.png" height="20" width="20" alt="File Uploaded" />
													<span style="color:green;">File Uploaded! <a href="<?php echo $filePathResult;?>" target="_blank">(view)</a></span>
													<br /><br />
												<?php
												}
											?>
											<form action="warranty_uploads.php" method="POST" id="warrantyUploadForm" enctype="multipart/form-data">
												<input type="hidden" name="dealerID" id="dealerID" value="<?php echo $dealerID;?>">
												<input type="hidden" name="warrantyID" id="warrantyID" value="<?php echo $warrantyID;?>">
												<input type="hidden" name="fileType" id="fileType" value="<?php echo $fileTypeID;?>">
												<div class="form-group">
													<input name="uploadedFile" id="warrantyPDF" type="file"><br>
													<button type="submit" name="uploadFile" id="upload" class="btn btn-md btn-primary float-left">Upload</button>
												</div>
											</form>

										</div>

										<div class="form-group col-md-6">
											&nbsp;
										</div>

										<div class="form-group col-md-6">
											<label>Inspection Report<span class="text-danger"></span></label>
											<?php
												$fileTypeID = 8;
												$filePathResult = getFileAssetForWarranty($link,$warrantyID,$fileTypeID);
												if($filePathResult!=0){
												?>
													<br />
													<img src="images/green_check.png" height="20" width="20" alt="File Uploaded" />
													<span style="color:green;">File Uploaded! <a href="<?php echo $filePathResult;?>" target="_blank">(view)</a></span>
													<br /><br />
												<?php
												}
											?>
											<form action="warranty_uploads.php" method="POST" id="warrantyUploadForm" enctype="multipart/form-data">
												<input type="hidden" name="dealerID" id="dealerID" value="<?php echo $dealerID;?>">
												<input type="hidden" name="warrantyID" id="warrantyID" value="<?php echo $warrantyID;?>">
												<input type="hidden" name="fileType" id="fileType" value="<?php echo $fileTypeID;?>">
												<div class="form-group">
													<input name="uploadedFile" id="warrantyPDF" type="file"><br>
													<button type="submit" name="uploadFile" id="upload" class="btn btn-md btn-primary float-left">Upload</button>
												</div>
											</form>

										</div>

										<div class="form-group col-md-6">
											<label>ECA Report<span class="text-danger"></span></label>
											<?php
												$fileTypeID = 9;
												$filePathResult = getFileAssetForWarranty($link,$warrantyID,$fileTypeID);
												if($filePathResult!=0){
												?>
													<br />
													<img src="images/green_check.png" height="20" width="20" alt="File Uploaded" />
													<span style="color:green;">File Uploaded! <a href="<?php echo $filePathResult;?>" target="_blank">(view)</a></span>
													<br /><br />
												<?php
												}
											?>
											<form action="warranty_uploads.php" method="POST" id="warrantyUploadForm" enctype="multipart/form-data">
												<input type="hidden" name="dealerID" id="dealerID" value="<?php echo $dealerID;?>">
												<input type="hidden" name="warrantyID" id="warrantyID" value="<?php echo $warrantyID;?>">
												<input type="hidden" name="fileType" id="fileType" value="<?php echo $fileTypeID;?>">
												<div class="form-group">
													<input name="uploadedFile" id="warrantyPDF" type="file"><br>
													<button type="submit" name="uploadFile" id="upload" class="btn btn-md btn-primary float-left">Upload</button>
												</div>
											</form>

										</div>

										<div class="form-group col-md-6">
											<label>VIN Placard<span class="text-danger"></span></label>
											<?php
												$fileTypeID = 10;
												$filePathResult = getFileAssetForWarranty($link,$warrantyID,$fileTypeID);
												if($filePathResult!=0){
												?>
													<br />
													<img src="images/green_check.png" height="20" width="20" alt="File Uploaded" />
													<span style="color:green;">File Uploaded! <a href="<?php echo $filePathResult;?>" target="_blank">(view)</a></span>
													<br /><br />
												<?php
												}
											?>
											<form action="warranty_uploads.php" method="POST" id="warrantyUploadForm" enctype="multipart/form-data">
												<input type="hidden" name="dealerID" id="dealerID" value="<?php echo $dealerID;?>">
												<input type="hidden" name="warrantyID" id="warrantyID" value="<?php echo $warrantyID;?>">
												<input type="hidden" name="fileType" id="fileType" value="<?php echo $fileTypeID;?>">
												<div class="form-group">
													<input name="uploadedFile" id="warrantyPDF" type="file"><br>
													<button type="submit" name="uploadFile" id="upload" class="btn btn-md btn-primary float-left">Upload</button>
												</div>
											</form>

										</div>

										<div class="form-group col-md-6">
											<label>Dashboard Photo<span class="text-danger"></span></label>
											<?php
												$fileTypeID = 11;
												$filePathResult = getFileAssetForWarranty($link,$warrantyID,$fileTypeID);
												if($filePathResult!=0){
												?>
													<br />
													<img src="images/green_check.png" height="20" width="20" alt="File Uploaded" />
													<span style="color:green;">File Uploaded! <a href="<?php echo $filePathResult;?>" target="_blank">(view)</a></span>
													<br /><br />
												<?php
												}
											?>
											<form action="warranty_uploads.php" method="POST" id="warrantyUploadForm" enctype="multipart/form-data">
												<input type="hidden" name="dealerID" id="dealerID" value="<?php echo $dealerID;?>">
												<input type="hidden" name="warrantyID" id="warrantyID" value="<?php echo $warrantyID;?>">
												<input type="hidden" name="fileType" id="fileType" value="<?php echo $fileTypeID;?>">
												<div class="form-group">
													<input name="uploadedFile" id="warrantyPDF" type="file"><br>
													<button type="submit" name="uploadFile" id="upload" class="btn btn-md btn-primary float-left">Upload</button>
												</div>
											</form>

										</div>

										<div class="form-group col-md-6">
											<label>Engine Placard<span class="text-danger"></span></label>
											<?php
												$fileTypeID = 12;
												$filePathResult = getFileAssetForWarranty($link,$warrantyID,$fileTypeID);
												if($filePathResult!=0){
												?>
													<br />
													<img src="images/green_check.png" height="20" width="20" alt="File Uploaded" />
													<span style="color:green;">File Uploaded! <a href="<?php echo $filePathResult;?>" target="_blank">(view)</a></span>
													<br /><br />
												<?php
												}
											?>
											<form action="warranty_uploads.php" method="POST" id="warrantyUploadForm" enctype="multipart/form-data">
												<input type="hidden" name="dealerID" id="dealerID" value="<?php echo $dealerID;?>">
												<input type="hidden" name="warrantyID" id="warrantyID" value="<?php echo $warrantyID;?>">
												<input type="hidden" name="fileType" id="fileType" value="<?php echo $fileTypeID;?>">
												<div class="form-group">
													<input name="uploadedFile" id="warrantyPDF" type="file"><br>
													<button type="submit" name="uploadFile" id="upload" class="btn btn-md btn-primary float-left">Upload</button>
												</div>
											</form>

										</div>

										<div class="form-group col-md-6">
											<label>Maintenance and Wear Form<span class="text-danger"></span></label>
											<?php
												$fileTypeID = 15;
												$filePathResult = getFileAssetForWarranty($link,$warrantyID,$fileTypeID);
												if($filePathResult!=0){
												?>
													<br />
													<img src="images/green_check.png" height="20" width="20" alt="File Uploaded" />
													<span style="color:green;">File Uploaded! <a href="<?php echo $filePathResult;?>" target="_blank">(view)</a></span>
													<br /><br />
												<?php
												}
											?>
											<form action="warranty_uploads.php" method="POST" id="warrantyUploadForm" enctype="multipart/form-data">
												<input type="hidden" name="dealerID" id="dealerID" value="<?php echo $dealerID;?>">
												<input type="hidden" name="warrantyID" id="warrantyID" value="<?php echo $warrantyID;?>">
												<input type="hidden" name="fileType" id="fileType" value="<?php echo $fileTypeID;?>">
												<div class="form-group">
													<input name="uploadedFile" id="warrantyPDF" type="file"><br>
													<button type="submit" name="uploadFile" id="upload" class="btn btn-md btn-primary float-left">Upload</button>
												</div>
											</form>

										</div>


									</div>
                                </div>
                            </div>
                        </div>
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
                <p>Copyright Developed by <a href="http://vitaltrendsusa.com/" target="_blank">Vital Trends</a> 2022</p>
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

<!-- Dashboard 1 -->
<script src="./js/custom.min.js"></script>
<script src="./js/deznav-init.js"></script>

<script src="./js/jSignature/jSignature.min.js"></script>
<script src="./js/jSignature/jSignInit.js"></script>
<script src="./js/common.js"></script>

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

</body>
</html>