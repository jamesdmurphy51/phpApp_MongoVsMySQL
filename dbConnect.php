<?php

//MYSQL
$user = "root";
$pass = "";

try {
    $conn = new PDO("mysql:host=localhost;dbname=shoestore", $user, $pass);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    echo "There was a problem connecting to the SQL database. " . $e->getMessage() . "<br><br>";
}

//MONGO
require "vendor/autoload.php";

try{
    $client = new MongoDB\Client("mongodb://localhost:27017");
}catch(MongoDB\Driver\Exception\Exception $e){
    echo "There was a problem connecting to the Mongo database.<br>" . $e->getMessage() . "<br><br>";
}

$db = $client->shoeStore;


?>