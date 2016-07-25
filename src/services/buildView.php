<?php

function buildView ($userId, $date) {
    
    $buildView = array(
        
        'tickets' => getItems($userId),
        'timesheets' => getTimesheets($userId, $date)
        
    );
    
    return $buildView;
}

function getTimesheets($userId, $date){
    
    if(!$date){
        $stamp = getdate((time() + 345600) - time()% 604800);
        $date = ''. $stamp['year'] . '-' . $stamp['mon'] . '-' . $stamp['mday'] .'';   
    }else {
        $stamp = getdate(strtotime($date));
        $date = ''. $stamp['year'] . '-' . $stamp['mon'] . '-' . $stamp['mday'] .'';
    }
  
    $stampEnd = getdate($stamp[0] + 604800);
    $dateEnd  = ''. $stampEnd['year'] . '-' . $stampEnd['mon'] . '-' . $stampEnd['mday'] .'';
    
    $con = mysqli_connect("localhost", "phpuser", "phpuserpw", "iws_cc");
    if(!$con){
        exit('Connect Error (' . mysqli_connect_errno() . ')'
                . mysqli_connect_error() );
    }
    mysqli_set_charset($con, 'utf-8');

    $dbQuery = "SELECT * FROM timesheets WHERE userId = " . $userId ." AND created >= '". $date . "' AND created < '" . $dateEnd . "'";

    $iwsResult = mysqli_query($con, $dbQuery);
        
//    $timesheets = mysqli_fetch_all($iwsResult, MYSQLI_ASSOC);
    $timesheets = array(
        "timesheets" => mysqli_fetch_all($iwsResult, MYSQLI_ASSOC),
        "date" => $date
    );

    mysqli_close($con);
    mysqli_free_result($iwsResult);

    return $timesheets;
   
}

function getItems($userId) {

    $curl = curl_init();

    $getTicketsOptions = array(

        CURLOPT_URL => BASE_URL.GET_ITEMS.$userId,
        CURLOPT_FOLLOWLOCATION => false,
        CURLOPT_HEADER => true,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_COOKIE => $_SESSION['cookie'],
        
    );

    curl_setopt_array($curl, $getTicketsOptions);

    $data = curl_exec($curl);

    $split  = preg_split('|{|', $data);
    $ticketStr = '';

    foreach($split as $key => $value){
        if($key != 0){
            $ticketStr = $ticketStr.'{'.$value;
        }
    }

    $tickets = json_decode($ticketStr, true);

    curl_close($curl);
    
    return $tickets;
}
/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

