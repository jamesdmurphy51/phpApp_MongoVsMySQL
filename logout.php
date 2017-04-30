<?php
    session_start();
    
    //remove cookie and delete session
    setcookie(session_name(), '', time()-7000000, '/');
    session_unset();
    session_destroy();

?>