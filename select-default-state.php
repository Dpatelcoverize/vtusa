<?php
// Connect to DB
require_once "includes/dbConnect.php";

if (isset($_POST['selectState'])) {
    $query = "SELECT * FROM St_Prov WHERE Cntry_Nm = 'US' ORDER BY St_Prov_Nm";
    $stateResult = $link->query($query);
    $loopCounter = 0;
    while ($row = mysqli_fetch_assoc($stateResult)) {
        $loopCounter++;
        ?>
        <option value="<?php echo $row["St_Prov_ID"] ?>"><?php echo $row["St_Prov_Nm"]; ?></option>
    <?php 
}
}

?>