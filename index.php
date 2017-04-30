<?php 
    ini_set('session.cookie_lifetime', 60 * 5);  // 5 minute cookie lifetime
    session_start() 
?>

<!doctype html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Shoe Store App</title>

    <!-- Bootstrap css -->
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css" integrity="sha384-BVYiiSIFeK1dGmJRAkycuHAHRg32OmUcww7on3RYdg4Va+PmSTsz/K68vbdEjh4u" crossorigin="anonymous">
    <!--custom css-->
    <link rel="stylesheet" href="./css/main.css">
</head>

<body>  
    <!-- nav bar -->
    <nav class="navbar navbar-inverse" id="navBar">
        <div class="container-fluid">
            <div class="navbar-header">
                <span class="navbar-brand">Shoe Store App</span>
            </div>
            <ul class="nav navbar-nav">
                <li id="li_home" class="navLi"><a href="#home">Home</a></li>
                <li id="li_register" class="navLi"><a href="#register">Register</a></li>
                <li id="li_about" class="navLi"><a href="#about">About Us</a></li>
            </ul>
        </div>
    </nav>
    <!-- end nav bar -->



    <!-- main -->
    <main class="container">


        <!--*************BEGIN HOME PAGE************************************************************************************ -->
        <!--*************BEGIN HOME PAGE************************************************************************************ -->
        <div class="page" id=homePage>

            <!--MAIN (ONLY) ROW -->
            <div class="row">

                <!-- header -->
                <header class="col-sm-9">
                    <h3 class="text-muted">Welcome to the Shoe Store Application</h3>
                    <h4 class="text-muted"><span id="span_login"> <?php echo (isset($_SESSION["email1"]) ? "You are logged in as " . $_SESSION["email1"] : "Please login") ?> </span></h4>
                </header>
                <!-- end header -->

               <!-- section for user login -->
                <section id="login" class="col-sm-3">
                    <div class="row">    
                        <div class="input-group">   
                            <input type="email" class="form-control" placeholder="Enter email address" id="input_email">
                            <span class="input-group-btn"><button class="btn btn-primary btn_log" type="button" id="btn_login">Login</button></span>
                        </div>
                    </div>
                    <div class="row">    
                        <div class="input-group">   
                            <input type="email" class="form-control" style="display:none"> <!--purely for aesthetic purposes!!!-->
                            <span class="input-group-btn"><button class="btn btn-danger pull-right btn_log" style= <?php echo (isset($_SESSION["email1"]) ? 'display:inline' : 'display:none') ?> type="button" id="btn_logout">Logout</button></span>
                        </div>
                    </div>
                </section>
                <!-- end section -->

            </div>
            <!-- END MAIN (ONLY) ROW -->



        </div>
        <!--*************END HOME PAGE************************************************************************************ -->
        <!--*************END HOME PAGE************************************************************************************ -->


        <!--*************BEGIN REGISTER PAGE************************************************************************************ -->
        <!--*************BEGIN REGISTER PAGE************************************************************************************ -->
        <div class="page" id=regPage>
            <!-- header -->
            <header>
                <h3 class="text-muted" id="regHeader">Hello and Welcome to the Registration Page!</h3>
            </header>
            <!-- end header -->

            <form id="inputForm" class="row">
                <div class="col-sm-3">
                    <div class="form-group">
                        <label for="fName">First name:</label>
                        <input type="text" class="form-control" id="fName" value=<?php echo (isset($_SESSION["fName"]) ? $_SESSION["fName"] : "") ?>>
                    </div>
                    <div class="form-group">
                        <label for="lName">Last name:</label>
                        <input type="text" class="form-control" id="lName" value=<?php echo (isset($_SESSION["lName"]) ? $_SESSION["lName"] : "") ?>>
                    </div>
                    <div class="form-group">
                        <label for="email1">Email address 1:</label>
                        <input type="email" class="form-control" id="email1" value=<?php echo (isset($_SESSION["email1"]) ? $_SESSION["email1"] : "") . " " . (isset($_SESSION["email1"]) ? "disabled" : "") ?>>
                    </div>
                    <div class="form-group">
                        <label for="email2">Email address 2:</label>
                        <input type="email" class="form-control" id="email2" value=<?php echo (isset($_SESSION["email2"]) ? $_SESSION["email2"] : "") ?>>
                    </div>
                </div>
                <div class="col-sm-3">
                    <div class="form-group">
                        <label for="street">Street:</label>
                        <input type="text" class="form-control" id="street" value=<?php echo (isset($_SESSION["street"]) ? "'" . $_SESSION["street"] . "'" : "") ?>>
                    </div>
                    <div class="form-group">
                        <label for="city">City:</label>
                        <input type="text" class="form-control" id="city" value=<?php echo (isset($_SESSION["city"]) ? $_SESSION["city"] : "") ?>>
                    </div>
                    <div class="form-group">
                        <label for="postal">Postal Code:</label>
                        <input type="text" class="form-control" id="postal" value=<?php echo (isset($_SESSION["postal"]) ? $_SESSION["postal"] : "") ?>>
                    </div>
                    <div class="form-group">
                        <label for="age">Age:</label>
                        <input type="text" class="form-control" id="age" value=<?php echo (isset($_SESSION["age"]) ? $_SESSION["age"] : "") ?>>
                    </div>
                </div>
            </form> 
            <button type="button" id = "btn_register" class="btn btn-primary"><?php echo (isset($_SESSION["email1"]) ? "Edit Registration Details" : "Submit")?></button>
            <button type="button" id = "btn_delist" class="btn btn-danger" style= <?php echo (isset($_SESSION["email1"]) ? 'display:inline' : 'display:none') ?> >Delist</button>
            <span id="span_register" class="text-primary"></span>
        
        </div>
        <!--*************END REGISTER PAGE************************************************************************************ -->
        <!--*************END REGISTER PAGE************************************************************************************ -->


        <!--*************BEGIN ABOUT PAGE************************************************************************************ -->
        <!--*************BEGIN ABOUT PAGE************************************************************************************ -->
        <div class="page" id=aboutPage>
            <!-- header -->
            <header>
                <h3 class="text-muted" id="aboutHeader">Hello and Welcome to the About Us Page!</h3>
            </header>
            <!-- end header -->

            <div class="text-muted">
                <p>One day we are going to have the best shoe store on the web!!<br><br>
                This app uses php for it's' server side language, along with the MongoDB NoSQL database.<br>
                The PHP driver for MongoDB is provided by 'pecl.php.net', and PHP library is installed using the 'Composer' dependency manager (getcomposer.org).<br><br> 
                The site is published to an Ubuntu VPS on Digital Ocean.<br><br>
                Code is available for download from <a href='#' target='_blank'>GitHub</a><br>
                Video tutorial showing the app interacting with MongoDB can be viewed on <a href='#' target='_blank'>YouTube</a><br><br>
                Feel free to reach out to me via email, at <a href='mailto:jamesdmurphy51@gmail.com?Subject=Enquiry%20about%20your%20website' target='_top'>jamesdmurphy51@gmail.com</a><br><br>   
                </p>
            </div>

        </div>
        <!--*************END ABOUT PAGE************************************************************************************ -->
        <!--*************END ABOUT PAGE************************************************************************************ -->



    </main>
    <!-- end main -->


    <footer class="navbar-fixed-bottom navbar-inverse">
        <div class="container">
            <p class="text-muted">&copy;jamesdavidmurphy.com</p>
        </div>
    </footer>






    <!-- Jquery -->
    <script src="https://code.jquery.com/jquery-1.12.0.min.js"></script>
    <!-- Bootstrap js -->
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/js/bootstrap.min.js" integrity="sha384-Tc5IQib027qvyjSMfHjOMaLkfuWVxZxUPnCJA7l2mCWNIpG9mGCD8wGNIcPD7Txa" crossorigin="anonymous"></script>


    <!-- custom js -->
    <script src="./js/main.js"></script>
    <script>
        document.addEventListener("DOMContentLoaded", onDOMLoaded);
    </script>

</body>
</html>
