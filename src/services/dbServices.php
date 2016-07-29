<?php

function connectToDb(){
    
    $con = mysqli_connect("localhost", "phpuser", "phpuserpw", "iws_cc");

    if(!$con){
        exit('Connect Error (' . mysqli_connect_errno() . ')'
                . mysqli_connect_error() );
    }

    mysqli_set_charset($con, 'utf-8');
        
    return $con;
}

function deleteTimesheet ($con){

    $dbQuery = "DELETE FROM timesheets WHERE id = ". $_POST['id'] . " AND userId = " . $_SESSION['userId'];

    mysqli_query($con, $dbQuery);

    return;
}

function updateTimesheet ($con) {
    
    $dbQuery = "UPDATE timesheets SET hours=" . $_POST['hours'] . ", comments = '" . $_POST['comments'] . "', billable = " . $_POST['billable'] . "  WHERE id = " . $_POST['id'] . " AND userId = " . $_SESSION['userId'];
        
    if(!mysqli_query($con, $dbQuery)){
        echo 'something went wrong <br/>';
    }
    
    return;
}

function getTimesheet ($con){
    
    $dbQuery = "SELECT * FROM timesheets WHERE userId = " . $_SESSION['userId'] ." AND id = " . $_POST['id'];

    $iwsResult = mysqli_query($con, $dbQuery);

    $timesheet = array(
        'timesheet' => mysqli_fetch_all($iwsResult, MYSQLI_ASSOC),
    );
    
    mysqli_free_result($iwsResult);

    return $timesheet;
    
};

function addTimesheet ($con) {
  
 $values = "'". $_POST['created'] . "', " . $_SESSION['userId'] . ", " . $_POST['hours'] . ", '" . $_POST['ticket'] . "', '" . $_POST['comments'] . "', '" . $_POST['billable'] . "' ";
    
    $dbQuery = "INSERT INTO timesheets (created, userId, hours, ticket, comments, billable) VALUES (" . $values . ")";
    
    if(!mysqli_query($con, $dbQuery)){
        
        echo 'check your form data';

    }
    
    return;
}

function getAllTimesheets ($con, $dateBegin, $dateEnd) {
    
    $dbQuery = "SELECT * FROM timesheets WHERE userId = " . $_SESSION['userId'] ." AND created >= '". $dateBegin . "' AND created < '" . $dateEnd . "' ORDER BY created";
    
    $iwsResult = mysqli_query($con, $dbQuery);

    $timesheets = mysqli_fetch_all($iwsResult, MYSQLI_ASSOC);

    mysqli_free_result($iwsResult);
    
    return $timesheets;
}


