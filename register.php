<?php
ini_set('session.cookie_lifetime', 60 * 5);  // 5 minute cookie lifetime
session_start();



//connect to db servers (MySQL & Mongo)
require("dbConnect.php");

$reqType = $_SERVER['REQUEST_METHOD'];

//parse request body
if($reqType == "POST"){
    $payload = $_POST["dataKey"];
    //convert JSON object to PHP object
    $payloadParsed = json_decode($payload, true);

}elseif ($reqType == "PUT"){
    parse_str(file_get_contents("php://input"), $payload);
    $payloadJSON = $payload['dataKey'];
    //convert JSON object to PHP object
    $payloadParsed = json_decode($payloadJSON, true);

}elseif ($reqType == "DELETE"){
    parse_str(file_get_contents("php://input"), $payload);
    $payloadJSON = $payload['dataKey'];
    //convert JSON object to PHP object
    $payloadParsed = json_decode($payloadJSON, true);
}


//pull variables from PHP object (POST & PUT)
if($reqType == "POST" || ($reqType == "PUT")){
    $fName = $payloadParsed["fName"];
    $lName = $payloadParsed["lName"];
    $age = ($payloadParsed["age"]==="" ? 0 : (int)$payloadParsed["age"]);
    $street = $payloadParsed["street"];
    $city = $payloadParsed["city"];
    $postal = $payloadParsed["postal"];
    $emailArray = $payloadParsed["email"];
    //below vars for MySQL only....MongoDB will insert array directly
    $email1 = $emailArray[0];
    $email2 = (isset($emailArray[1]) ? $emailArray[1] : NULL);

}elseif ($reqType == "DELETE"){
    $email = $payloadParsed["email"];
}
//***************************************************************************************************************
//***************************************************************************************************************

if($reqType == "POST"){
    //IF USER IS REGISTERING FOR FIRST TIME

    //****************************
    //MYSQL CODE
    //get max ID field in customers table (then increment)....for primary key
    $q = $conn->query("SELECT MAX(id) FROM customers");
    $maxID = $q->fetchColumn();
    $newPK = $maxID+1;

    //BEGIN TRANSACTION
    $conn->beginTransaction();

        //1. Insert to customers table
        $sql1 = "INSERT INTO customers(id, first_name, last_name, age, street_address, city, postal_code) 
        VALUES(" . $newPK . ", '" . $fName . "', '" . $lName . "', " . $age . ", '" . $street . "', '" . $city . "', '" . $postal . "')";

        //2. Insert to emails table
        if($email2===NULL){
            $sql2 = "INSERT INTO customer_emails(customer_id, email_address) 
            VALUES('" . $newPK . "', '" . $email1 . "')";
        }else{
            $sql2 = "INSERT INTO customer_emails(customer_id, email_address) 
            VALUES('" . $newPK . "','" . $email1 . "') , ('" . $newPK . "','" . $email2 . "')";
        }

    try{
        $conn->exec($sql1);
        $conn->exec($sql2);
        $conn->commit(); 
        echo "<br><br>Congratulations " . $fName . ", you successfully registered with MySQL!<br><br>";
    }catch(PDOException $e){
        $conn->rollBack();
        echo "<br><br><span style='color:red'>There was a problem inserting records to the MySQL database.<br>" . $e->getMessage() . "</span><br><br>";
        die();
    }

    //****************************
    //MONGO CODE
    //link to table
    $coll = $db->customers;

    //first get next available ID
    $documentList = $coll->find( [] , [ 'sort'=>['_id'=>-1] , 'limit'=>1, 'projection'=>['_id'=>1]] );
    foreach ( $documentList as $doc )
    {
        $newPK = $doc->_id +1;
    }

    //then insert to collection
    try{
        $coll->insertOne([
        '_id'=>$newPK, 'first_name'=>$fName, 'last_name'=>$lName, 'age'=>$age, 'street_address'=>$street, 
        'city'=>$city, 'postal_code'=>$postal, 'email_addresses'=>$emailArray, 'reviews'=>[], 'orders'=>[]
        ]);
        echo "Congratulations " . $fName . ", you successfully registered with MongoDB!";

        //also create session object to recognize user is logged in
        $_SESSION["fName"] = $fName;
        $_SESSION["lName"] = $lName;
        $_SESSION["street"] = $street;
        $_SESSION["city"] = $city;
        $_SESSION["postal"] = $postal;
        if($age!=0){$_SESSION["age"] = $age;}
        $_SESSION["email1"] = $emailArray[0];
        $_SESSION["email2"] = ( isset($emailArray[1]) ? $emailArray[1] : "" );

    }catch(MongoDB\Driver\Exception\Exception $e){
        echo "<span style='color:red'>There was a problem inserting records to the Mongo database.<br>" . $e->getMessage() . "</span>";
    }
    //****************************

//*********************************************************************************************
}elseif ($reqType == "PUT"){
    //IF USER IS EDITING REGISTRATION
    
    //****************************
    //MYSQL CODE

    //First get all values from DB
    //get customerId based on email
    $sql = "SELECT customer_id FROM customer_emails WHERE email_address = '" . $email1 . "'";
    $stmt = $conn->query($sql); 
    $result = $stmt->fetchObject();
    $custIdDB = $result->customer_id;

    //use customerId to get other email (if exists)
    $sql = "SELECT email_address FROM customer_emails WHERE customer_id = " . $custIdDB . " AND email_address <> '" . $email1 . "'";
    $stmt = $conn->query($sql); 
    $result = $stmt->fetchObject();
    if($result){
        $emailDB2 = $result->email_address;
    }else{
        $emailDB2 = "";
    }
    
    
    //use customerId to get other details from DB
    $sql = "SELECT * FROM customers WHERE id = " . $custIdDB;
    $stmt = $conn->query($sql); 
    $result = $stmt->fetchObject();

    $fNameDB = $result->first_name; 
    $lNameDB = $result->last_name; 
    $ageDB = $result->age; 
    $streetDB = $result->street_address; 
    $cityDB = $result->city; 
    $postalDB = $result->postal_code; 
    //-------------------------------

    //compare DB results v submitted results & update DB if difference
    //email2 field
    if (isset($email2)){
        if ($email2 != $emailDB2){
            $sql = "UPDATE customer_emails SET email_address = '" . $email2 . "' WHERE customer_id = " . $custIdDB . 
            " AND email_address <> '" . $email1 . "'";
            try{
                $conn->exec($sql);
                echo "<br><br>Congratulations " . $fName . ", you successfully updated the email field in MySQL!<br><br>";
            }catch(PDOException $e){
                echo "<br><br><span style='color:red'>There was a problem updating the email record in MySQL database.<br>" . $e->getMessage() . "</span><br><br>";
                die();
            }
        } 
    }//end if
    //fname field
    if ($fName != $fNameDB){
        $sql = "UPDATE customers SET first_name = '" . $fName . "' WHERE id = " . $custIdDB;
        try{
            $conn->exec($sql);
            echo "<br><br>Congratulations " . $fName . ", you successfully updated the first_name field in MySQL!<br><br>";
        }catch(PDOException $e){
             echo "<br><br><span style='color:red'>There was a problem updating the first_name record in MySQL database.<br>" . $e->getMessage() . "</span><br><br>";
             die();
        }
    } //end if
    //lname field
    if ($lName != $lNameDB){
        $sql = "UPDATE customers SET last_name = '" . $lName . "' WHERE id = " . $custIdDB;
        try{
            $conn->exec($sql);
            echo "<br><br>Congratulations " . $fName . ", you successfully updated the last_name field in MySQL!<br><br>";
        }catch(PDOException $e){
             echo "<br><br><span style='color:red'>There was a problem updating the last_name record in MySQL database.<br>" . $e->getMessage() . "</span><br><br>";
             die();
        }
    } //end if
    //street field
    if ($street != $streetDB){
        $sql = "UPDATE customers SET street_address = '" . $street . "' WHERE id = " . $custIdDB;
        try{
            $conn->exec($sql);
            echo "<br><br>Congratulations " . $fName . ", you successfully updated the street_address field in MySQL!<br><br>";
        }catch(PDOException $e){
             echo "<br><br><span style='color:red'>There was a problem updating the street_address record in MySQL database.<br>" . $e->getMessage() . "</span><br><br>";
             die();
        }
    } //end if
    //city field
    if ($city != $cityDB){
        $sql = "UPDATE customers SET city = '" . $city . "' WHERE id = " . $custIdDB;
        try{
            $conn->exec($sql);
            echo "<br><br>Congratulations " . $fName . ", you successfully updated the city field in MySQL!<br><br>";
        }catch(PDOException $e){
             echo "<br><br><span style='color:red'>There was a problem updating the city record in MySQL database.<br>" . $e->getMessage() . "</span><br><br>";
             die();
        }
    } //end if
    //postal field
    if ($postal != $postalDB){
        $sql = "UPDATE customers SET postal_code = '" . $postal . "' WHERE id = " . $custIdDB;
        try{
            $conn->exec($sql);
            echo "<br><br>Congratulations " . $fName . ", you successfully updated the postal_code field in MySQL!<br><br>";
        }catch(PDOException $e){
             echo "<br><br><span style='color:red'>There was a problem updating the postal_code record in MySQL database.<br>" . $e->getMessage() . "</span><br><br>";
             die();
        }
    } //end if
    //age
    if ($age != $ageDB){
        $sql = "UPDATE customers SET age = '" . $age . "' WHERE id = " . $custIdDB;
        try{
            $conn->exec($sql);
            echo "<br><br>Congratulations " . $fName . ", you successfully updated the age field in MySQL!<br><br>";
        }catch(PDOException $e){
             echo "<br><br><span style='color:red'>There was a problem updating the age record in MySQL database.<br>" . $e->getMessage() . "</span><br><br>";
             die();
        }
    } //end if

    //****************************
    //MONGO CODE

    //First get all values from DB based on email
    //link to collection
    $coll = $db->customers;

    //query collection to pull customer document to update
    $document = $coll->findOne(['email_addresses'=>$email1]);

    //pull variables from document object returned
    $id = $document->_id; 
    $emailArray = $document->email_addresses;
    //does email2 exist?
    if (isset($emailArray[1])){
        $emailDB2 = $emailArray[1];
    }else{
        $emailDB2 = "";
    }
    $fNameDB = $document->first_name; 
    $lNameDB = $document->last_name; 
    $ageDB = $document->age; 
    $streetDB = $document->street_address; 
    $cityDB = $document->city; 
    $postalDB = $document->postal_code; 
    //-------------------------------

    //compare DB results v submitted results & update DB if difference
    //email2 field
    if (isset($email2)){
        if ($email2 != $emailDB2){
            try{
                $coll->updateOne(
                    ['_id' => $id],
                    ['$set' => ['email_addresses.1' => $email2]]
                );
                echo "Congratulations " . $fName . ", you successfully edited the email field in MongoDB!<br><br>";
            }catch(MongoDB\Driver\Exception\Exception $e){
                echo "<span style='color:red'>There was a problem editing the email field in Mongo database.<br>" . $e->getMessage() . "</span><br><br>";
                die();
            }
        } 
    }//end if
    //fname field
    if ($fName != $fNameDB){
        try{
            $coll->updateOne(
                ['_id' => $id],
                ['$set' => ['first_name' => $fName]]
            );
            echo "Congratulations " . $fName . ", you successfully edited the first_name field in MongoDB!<br><br>";
        }catch(MongoDB\Driver\Exception\Exception $e){
            echo "<span style='color:red'>There was a problem editing the first_name field in Mongo database.<br>" . $e->getMessage() . "</span><br><br>";
            die();
        }
    } 
    //lname field
    if ($lName != $lNameDB){
        try{
            $coll->updateOne(
                ['_id' => $id],
                ['$set' => ['last_name' => $lName]]
            );
            echo "Congratulations " . $fName . ", you successfully edited the last_name field in MongoDB!<br><br>";
        }catch(MongoDB\Driver\Exception\Exception $e){
            echo "<span style='color:red'>There was a problem editing the last_name field in Mongo database.<br>" . $e->getMessage() . "</span><br><br>";
            die();
        }
    }
    //street field
    if ($street != $streetDB){
        try{
            $coll->updateOne(
                ['_id' => $id],
                ['$set' => ['street_address' => $street]]
            );
            echo "Congratulations " . $fName . ", you successfully edited the street_address field in MongoDB!<br><br>";
        }catch(MongoDB\Driver\Exception\Exception $e){
            echo "<span style='color:red'>There was a problem editing the street_address field in Mongo database.<br>" . $e->getMessage() . "</span><br><br>";
            die();
        }
    }  
    //city
    if ($city != $cityDB){
        try{
            $coll->updateOne(
                ['_id' => $id],
                ['$set' => ['city' => $city]]
            );
            echo "Congratulations " . $fName . ", you successfully edited the city field in MongoDB!<br><br>";
        }catch(MongoDB\Driver\Exception\Exception $e){
            echo "<span style='color:red'>There was a problem editing the city field in Mongo database.<br>" . $e->getMessage() . "</span><br><br>";
            die();
        }
    } 
    //postal field
    if ($postal != $postalDB){
        try{
            $coll->updateOne(
                ['_id' => $id],
                ['$set' => ['postal_code' => $postal]]
            );
            echo "Congratulations " . $fName . ", you successfully edited the postal_code field in MongoDB!<br><br>";
        }catch(MongoDB\Driver\Exception\Exception $e){
            echo "<span style='color:red'>There was a problem editing the postal_code field in Mongo database.<br>" . $e->getMessage() . "</span><br><br>";
            die();
        }
    }  
    //age field
    if ($age != $ageDB){
        try{
            $coll->updateOne(
                ['_id' => $id],
                ['$set' => ['age' => $age]]
            );
            echo "Congratulations " . $fName . ", you successfully edited the age field in MongoDB!<br><br>";
        }catch(MongoDB\Driver\Exception\Exception $e){
            echo "<span style='color:red'>There was a problem editing the age field in Mongo database.<br>" . $e->getMessage() . "</span><br><br>";
            die();
        }
    } 

//*********************************************************************************************
}elseif ($reqType == "DELETE"){
    //IF USER IS DELETING REGISTRATION
    
    //****************************
    //MYSQL CODE
    //get customerId based on email
    $sql = "SELECT customer_id FROM customer_emails WHERE email_address = '" . $email . "'";
    $stmt = $conn->query($sql); 
    $result = $stmt->fetchObject();
    $custIdDB = $result->customer_id;

    //delete from 2 x tables

    //BEGIN TRANSACTION
    $conn->beginTransaction();

        //1. delete customers table
        $sql1 = "DELETE FROM customers WHERE id=" . $custIdDB;

        //2. delete from emails table
        $sql2 = "DELETE FROM customer_emails WHERE customer_id=" . $custIdDB;

    try{
        $conn->exec($sql1);
        $conn->exec($sql2);
        $conn->commit();
    }catch(PDOException $e){
        $conn->rollBack();
        echo "<br><br><span style='color:red'>There was a problem de-registering from the MySQL database.<br>" . $e->getMessage() . "</span><br><br>";
        die();
    }

    //****************************
    //MONGO CODE
    //link to collection
    $coll = $db->customers;

    try{
        $coll->deleteOne(['email_addresses.0' => $email]);
        //remove cookie and delete session
        setcookie(session_name(), '', time()-7000000, '/');
        session_unset();
        session_destroy();
        echo "success";
    }catch(MongoDB\Driver\Exception\Exception $e){
        echo "<span style='color:red'>There was a problem de-registering from the Mongo database.<br>" . $e->getMessage() . "</span><br><br>";
    die();
    }
}

?>