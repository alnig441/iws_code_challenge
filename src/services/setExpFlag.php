<?php

include dirname(__FILE__).'/../src/services/buildView.php';

function setFlag(){
    
    $con = mysqli_connect("localhost", "phpuser", "phpuserpw", "iws_cc");
    
    if(!$con){
        exit('Connect Error (' . mysqli_connect_errno() . ')'
                . mysqli_connect_error() );
    }
    
    mysqli_set_charset($con, 'utf-8');

    $dbQuery = "UPDATE timesheets SET exported = true WHERE userId = " . $_SESSION['userId'] . " AND created >= '" . $_SESSION['begin'] . "' AND created < '" . $_SESSION['end'] . "'";
    
    if(!$result = mysqli_query($con, $dbQuery)){
        return 'something went wrong <br/>';
    }
    
    mysqli_free_result($result);
    mysql_close();
    
    return;
    
//    return buildView($_SESSION['userId']);

}


/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

