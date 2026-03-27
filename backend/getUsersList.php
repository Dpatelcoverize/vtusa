<?php
header('Content-Type: application/json');
require '../config.php';
/* Attempt to connect to MySQL database */
$link = mysqli_connect(DB_SERVER, DB_USERNAME, DB_PASSWORD, DB_NAME);
try {
    $userInfo=[];
    if(isset($_POST['type']) && $_POST['type']==='getUsersList'){
        
        $role=$_POST['role'];
        $query = "SELECT `username` FROM Users WHERE Role_ID=" . $role . ";";
        $result = $link->query($query);
      
     
        if (mysqli_num_rows($result) > 0) {
            while($row = mysqli_fetch_assoc($result)) {
                $userInfo[]=$row['username'];
            }

            echo json_encode(['status' => 200,  'data' => $userInfo,'message' => 'Record Found']);
        }else{
            echo json_encode(['status' => 400, 'message' => 'Record Not Found ']);
        }
    
    }
    
} catch (Exception $exception) {
    echo json_encode(['status' => 400, 'message' => $exception->getMessage()]);
}
