<?php
ini_set('session.cookie_lifetime', 60 * 5);  // 5 minute cookie lifetime
session_start();

if (isset($_SESSION["email1"])){
    echo "You are already logged in as " . $_SESSION["email1"];
    die();
}

//connect to db servers (MySQL & Mongo)
require("dbConnect.php");

//PULL POSTED DATA FROM REQUEST
$payload = $_GET["dataKey"];
$email = $payload["email"];


//MYSQL CODE
//query table to pull userID using email submitted
$sql1 = "SELECT customer_id FROM customer_emails WHERE email_address = '" . $email . "'";
$stmt = $conn->query($sql1); 
$result = $stmt->fetchObject();

//populate query result into variable (or tell user they are not registered)
if ($result){
    $custId = $result->customer_id;
    //query database to pull userID using email submitted
    $sql2 = "SELECT * FROM customers WHERE id = " . $custId;
    $stmt = $conn->query($sql2); 
    $result = $stmt->fetchObject();
    echo "<span class='text-primary'>Congratulations " . $result->first_name . " " . $result->last_name . 
    ", you have successfully logged in via MySql!</span><br><br>";
}else{
    echo "<span style='color:red'>Email is not registered in MySQL</span><br><br>";
}


//***************************************************************************************************************
//***************************************************************************************************************
//MONGO CODE
//link to table
$coll = $db->customers;

//query collection to pull record
$document = $coll->findOne(['email_addresses'=>$email]);
if($document){
    //if query returns result, pass back congrats text along with JSON object
    echo "<span class='text-primary'>Congratulations " . $document->first_name . " " . $document->last_name . ", you have successfully logged in via Mongo!</span>
    <script type='text/javascript' id='jsonObject'>" . json_encode($document) . "</script>
    <br><br>";

    //also create session object to recognize user is logged in
    $_SESSION["fName"] = $document->first_name;
    $_SESSION["lName"] = $document->last_name;
    $_SESSION["street"] = $document->street_address;
    $_SESSION["city"] = $document->city;
    $_SESSION["postal"] = $document->postal_code;
    $_SESSION["age"] = $document->age;
    $_SESSION["email1"] = $document->email_addresses[0];
    $_SESSION["email2"] = ( isset($document->email_addresses[1]) ? $document->email_addresses[1] : "" );

}else{
    echo "<span style='color:red'>Email is not registered in Mongo</span>";   
}


?>

