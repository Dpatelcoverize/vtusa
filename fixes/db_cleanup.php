<?php

// Clean up the 'testing' database entries in production
// 8/16/2022

// Turn on error reporting
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
//mysqli_report(MYSQLI_REPORT_ALL);
error_reporting(E_ALL);


// Connect to DB
require_once "../includes/dbConnect.php";



/*
Acct_ID to remove:

1254, 1255, 1256, 1257, 1258, 1259,
1260, 1261, 1262, 1263, 1264, 1265, 1266, 1267,
1271, 1274, 1277,

-- Adirondak EVG extra rows
1278, 1279, 1280, 1281, 1282, 1283, 1284,

1286

-- Leasing Specialists LLC extra rows
1287, 1288

-- Salt City Fire Equipment extra rows
1290

-- G & G Process Services, Inc extra rows
1292, 1293, 1294,

1296


-- according to Zach, they are keeping:
?AR Number: CO120 (Leasing Specialists LLC) and
?AR Number: NH103 (Adirondack EVG Inc)
?AR Number: NY110 (Salt City)

----------------

DONE

1254, 1255, 1256, 1257,
1258, 1259, 1260, 1261,
1262, 1263, 1264, 1265,
1266, 1267, 1271, 1274,



queries
SELECT a.Acct_Nm,a.Acct_ID,cd.Assign_Rtlr_Nbr,c.Cntrct_ID,cd.Cntrct_Dim_ID FROM `Acct` a, Cntrct c, Cntrct_Dim cd WHERE
a.Acct_Nm='Adirondack EVG Inc' AND a.Acct_ID=c.Mfr_Acct_ID AND c.Cntrct_Dim_ID=cd.Cntrct_Dim_ID;


*/


//$listOfBadAcctID = [1254, 1255, 1256, 1257, 1258, 1259,1260, 1261, 1262, 1263, 1264, 1265, 1266, 1267,1271, 1274, 1277];
//$listOfBadAcctID = [1260, 1261, 1262, 1263, 1264, 1265, 1266, 1267,1271, 1274, 1277];
//$listOfBadAcctID = [1286];
//$listOfBadAcctID = [1290];
//$listOfBadAcctID = [1287,1288];
//$listOfBadAcctID = [1278, 1279, 1280, 1281, 1282, 1283, 1284];
//$listOfBadAcctID = [1292, 1293, 1294];
$listOfBadAcctID = [1275,1277,1286,1296];



foreach($listOfBadAcctID as $Acct_ID){
	echo "<br /><br />Acct_ID = ".$Acct_ID;
	echo "<Br />";

	// Get all Pers associated with this dealer
	$queryString = "SELECT * FROM Usr_Loc WHERE Dlr_Acct_ID = ".$Acct_ID;
	$persResult = $link->query($queryString);
	while ($row = mysqli_fetch_assoc($persResult)) {
		$tempPersID = $row["Pers_ID"];

		// Ensure that this person only appears once in the Usr_Loc table, so that
		//  we don't delete a user who is on a valid Acct.
		$queryString = "SELECT * FROM Usr_Loc WHERE Pers_ID = ".$tempPersID;
		$result = $link->query($queryString);
echo "<br />".$queryString.";";

		if ($result->num_rows == 1) {

			// Deletions
			$queryString = "DELETE FROM Email WHERE Acct_ID = ".$Acct_ID." AND Pers_ID = ".$tempPersID;
//			$result = $link->query($queryString);
echo "<br />".$queryString.";";

			$queryString = "DELETE FROM Addr WHERE Acct_ID = ".$Acct_ID." AND Pers_ID = ".$tempPersID;
//			$result = $link->query($queryString);
echo "<br />".$queryString.";";

			$queryString = "DELETE FROM Tel WHERE Acct_ID = ".$Acct_ID." AND Pers_ID = ".$tempPersID;
//			$result = $link->query($queryString);
echo "<br />".$queryString.";";

			$queryString = "DELETE FROM Usr_Loc WHERE Dlr_Acct_ID = ".$Acct_ID." AND Pers_ID = ".$tempPersID;
//			$result = $link->query($queryString);
echo "<br />".$queryString.";";


		}

	} // Get all Pers associated with this dealer



	// Get all Cntrct associated with this dealer
	$queryString = "SELECT * FROM Cntrct c, Cntrct_Dim cd WHERE c.Mfr_Acct_ID = ".$Acct_ID." AND c.Cntrct_Dim_ID=cd.Cntrct_Dim_ID";
	$cntrctResult = $link->query($queryString);
echo "<br />".$queryString.";";
	while ($row = mysqli_fetch_assoc($cntrctResult)) {
		$tempCntrctID = $row["Cntrct_ID"];
		$tempCntrctDimID = $row["Cntrct_Dim_ID"];
		$tempVehID = $row["Veh_ID"];
		$Cntrct_Type_Cd = $row["Cntrct_Type_Cd"];

		// Deletions
		$queryString = "DELETE FROM Sml_Goods_Cvge WHERE Cntrct_ID = ".$tempCntrctID;
//		$result = $link->query($queryString);
echo "<br />".$queryString.";";

		$queryString = "DELETE FROM Cntrct_Dim WHERE Cntrct_Dim_ID = ".$tempCntrctDimID;
//		$result = $link->query($queryString);
echo "<br />".$queryString.";";

		if(is_numeric($tempVehID)){
			$queryString = "DELETE FROM Veh WHERE Veh_ID = ".$tempVehID;
	//		$result = $link->query($queryString);
	echo "<br />".$queryString.";";
		}

		$queryString = "DELETE FROM Cntrct WHERE Cntrct_ID = ".$tempCntrctID;
//		$result = $link->query($queryString);
echo "<br />".$queryString.";";


	} // Get all Cntrct associated with this dealer


	// Main Acct related deletions
	$queryString = "DELETE FROM Addr WHERE Acct_ID = ".$Acct_ID;
//	$result = $link->query($queryString);
echo "<br />".$queryString.";";

	$queryString = "DELETE FROM Tel WHERE Acct_ID = ".$Acct_ID;
//	$result = $link->query($queryString);
echo "<br />".$queryString.";";

	$queryString = "DELETE FROM Email WHERE Acct_ID = ".$Acct_ID;
//	$result = $link->query($queryString);
echo "<br />".$queryString.";";

	$queryString = "DELETE FROM Altn_Nm WHERE Acct_ID = ".$Acct_ID;
//	$result = $link->query($queryString);
echo "<br />".$queryString.";";

	$queryString = "DELETE FROM Acct WHERE Acct_ID = ".$Acct_ID;
//	$result = $link->query($queryString);
echo "<br />".$queryString.";";


}






?>