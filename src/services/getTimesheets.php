<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
function getTimesheet($userId){
    
    $con = mysqli_connect("localhost", "phpuser", "phpuserpw", "iws_cc");
    if(!$con){
        exit('Connect Error (' . mysqli_connect_errno() . ')'
                . mysqli_connect_error() );
    }
    mysqli_set_charset($con, 'utf-8');

    $dbQuery = "SELECT * FROM timesheets WHERE userId = " . $userId ."";

    $iwsResult = mysqli_query($con, $dbQuery);

    if(mysqli_num_rows($iwsResult) < 1){

        $timesheets = array(
            0 => array(
                'created' => null,
                'hours' => null,
                'ticket' => null,
                'comments' => null,
                'billable' => null
            )
        );
    }

    else {
        
        $timesheets = mysqli_fetch_all($iwsResult, MYSQLI_ASSOC);
    }
    
    mysqli_close($con);
    mysqli_free_result($iwsResult);
    
    return $timesheets;
   
}

 