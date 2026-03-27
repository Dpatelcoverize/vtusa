<?php
// Connect to DB
require_once "includes/dbConnect.php";

if(isset($_POST['changeSState'])){
    $bState = $_POST['bState'];
    $query = "SELECT * FROM St_Prov WHERE Cntry_Nm = 'US' ORDER BY St_Prov_Nm";
    $stateResult = $link->query($query);
    $loopCounter = 0;
    while($row = mysqli_fetch_assoc($stateResult)) {
        $loopCounter++;
    ?>
        <option value="<?php echo $row["St_Prov_ID"]?>" <?php if($row['St_Prov_ID'] == $bState){ ?> selected <?php } ?>><?php echo $row["St_Prov_Nm"];?></option>
    <?php } 
}

// Change The final total of Dealer Markup, MSRP Start...
if(isset($_POST['changeDealerMarkupTotalCost'])){
    $warrantyID = $_POST['warrantyID'];
    $Change_Tot_Dlr_Mrkp_Act_Amt = $_POST['Tot_Dlr_Mrkp_Act_Amt'];
    
    $query = "SELECT * FROM Cntrct c WHERE Cntrct_ID='$warrantyID'";
	$cntrctResult = $link->query($query);

	$numRows = mysqli_num_rows($cntrctResult);
	if ($numRows > 0) {
		$row = mysqli_fetch_assoc($cntrctResult);
        $Minus_Dlr_Mrkp_Act_Amt = $row["Tot_Dlr_Mrkp_Act_Amt"]-$Change_Tot_Dlr_Mrkp_Act_Amt;
        $Final_Tot_Dlr_Mrkp_Act_Amt = $row["Tot_Dlr_Mrkp_Act_Amt"]-$Minus_Dlr_Mrkp_Act_Amt;
		$Final_Tot_MSRP_Amt = $row["Tot_MSRP_Amt"]-$Minus_Dlr_Mrkp_Act_Amt;

        // Now update the 'Dealer Markup Total Cost, MSRP Total Cost' columns in the Cntrct table
        $query = "UPDATE Cntrct SET Tot_Dlr_Mrkp_Act_Amt=".$Final_Tot_Dlr_Mrkp_Act_Amt.",Tot_MSRP_Amt=".$Final_Tot_MSRP_Amt."  WHERE Cntrct_ID=".$warrantyID.";";
        $result = $link->query($query); 
        
        //Send data to reflect total columns of table...
        $data = array(
                        "Tot_Dlr_Mrkp_Act_Amt"=>$Final_Tot_Dlr_Mrkp_Act_Amt,
                        "Tot_MSRP_Amt"=>$Final_Tot_MSRP_Amt
        );
        echo json_encode($data);
    }
}
// Change The final total of Dealer Markup, MSRP End...

// Change The total of tier type Dealer Markup, MSRP Start...
if(isset($_POST['changeTierTypeDealerMarkup'])){
    $warrantyID = $_POST['warrantyID'];
    $Change_Tier_Type_Dlr_Mrkp_Actl_Amt = $_POST['Tier_Type_Dlr_Mrkp_Actl_Amt'];
    
    $query = "SELECT * FROM Cntrct c WHERE Cntrct_ID='$warrantyID'";
	$cntrctResult = $link->query($query);

	$numRows = mysqli_num_rows($cntrctResult);
	if ($numRows > 0) {
		$row = mysqli_fetch_assoc($cntrctResult);
        $Minus_Tier_type_Dlr_Mrkp_Actl_Amt = $row["Dlr_Mrkp_Actl_Amt"]-$Change_Tier_Type_Dlr_Mrkp_Actl_Amt;
		$Final_MSRP_Amt = $row["MSRP_Amt"]-$Minus_Tier_type_Dlr_Mrkp_Actl_Amt;
        $Final_Tot_Dlr_Mrkp_Act_Amt = $row["Tot_Dlr_Mrkp_Act_Amt"]-$Minus_Tier_type_Dlr_Mrkp_Actl_Amt;
		$Final_Tot_MSRP_Amt = $row["Tot_MSRP_Amt"]-$Minus_Tier_type_Dlr_Mrkp_Actl_Amt;
        
        //echo $Minus_Tier_type_Dlr_Mrkp_Actl_Amt." - ".$Final_MSRP_Amt." - ".$Final_Tot_Dlr_Mrkp_Act_Amt." - ".$Final_Tot_MSRP_Amt;
        // Now update the 'Dealer Markup Total Cost, MSRP Total Cost' columns in the Cntrct table
        $query = "UPDATE Cntrct SET Dlr_Mrkp_Actl_Amt=".$Change_Tier_Type_Dlr_Mrkp_Actl_Amt.",MSRP_Amt=".$Final_MSRP_Amt.",Tot_Dlr_Mrkp_Act_Amt=".$Final_Tot_Dlr_Mrkp_Act_Amt.",Tot_MSRP_Amt=".$Final_Tot_MSRP_Amt."  WHERE Cntrct_ID=".$warrantyID.";";
        $result = $link->query($query);
        
        //Send data to reflect total columns of table...
        $data = array(
                        "Dlr_Mrkp_Actl_Amt"=>$Change_Tier_Type_Dlr_Mrkp_Actl_Amt,
                        "MSRP_Amt"=>$Final_MSRP_Amt,
                        "Tot_Dlr_Mrkp_Act_Amt"=>$Final_Tot_Dlr_Mrkp_Act_Amt,
                        "Tot_MSRP_Amt"=>$Final_Tot_MSRP_Amt
        );
        echo json_encode($data);
    }
}
//Change The total of tier type Dealer Markup, MSRP End...
?>