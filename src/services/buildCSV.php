<?php

function buildCSV () {
    
    $con = mysqli_connect("localhost", "phpuser", "phpuserpw", "iws_cc");
    
    if(!$con){
            exit('Connect Error (' . mysqli_connect_errno() . ')'
                    . mysqli_connect_error() );
    }
    
    $file = fopen("timesheets.csv", "w");
    fputcsv($file, array('DATE', 'HOURS', 'TICKET', 'COMMENTS', 'BILLABLE', 'USERID'));
    
    mysqli_set_charset($con, 'utf-8');
    
    $dbQuery = "SELECT created, hours, ticket, comments, billable, userId FROM timesheets WHERE userId = '" . $_SESSION['userId'] . "' AND created >= '" . $_SESSION['begin'] . "' AND created < '" . $_SESSION['end'] . "' ORDER BY created ASC";
    
    if(!$result = mysqli_query($con, $dbQuery)){
        echo 'something went wrong';
    }
    
    while ($timesheets = mysqli_fetch_assoc($result)){
        fputcsv($file, $timesheets);
    }
    
    mysqli_free_result($result);
    mysql_close($con);
    
    return;
    
}
 