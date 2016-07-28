<?php

include dirname(__FILE__).'/../src/services/dbServices.php';

function buildView ($userId, $date, $con) {
    
    $buildView = array(
        
        'tickets' => getItems($userId),
        'timesheets' => getTimesheets($userId, $date, $con)
        
    );
    
    return $buildView;
}

function getTimesheets($userId, $date, $con){

    if(!$date){
        $stamp = getdate((time() - 259200) - time()% 604800);
        $dateBegin = ''. $stamp['year'] . '-' . $stamp['mon'] . '-' . $stamp['mday'] .'';   
    }else {
        $stamp = getdate(strtotime($date));
        $dateBegin = ''. $stamp['year'] . '-' . $stamp['mon'] . '-' . $stamp['mday'] .'';
    }
  
    $stampEnd = getdate($stamp[0] + 604800);
    $dateEnd  = ''. $stampEnd['year'] . '-' . $stampEnd['mon'] . '-' . $stampEnd['mday'] .'';
    
    $date = ''. $stamp['mon'] . '-' . $stamp['mday'] .'-' . $stamp['year'] .'';

    $timesheets = array(
        "timesheets" => getAllTimesheets($con, $userId, $dateBegin, $dateEnd),
        "date" => $date
    );
    
    $_SESSION['begin'] = $dateBegin;
    $_SESSION['end'] = $dateEnd;
    
    return $timesheets;
   
}

function getItems($userId) {
    
    if(!$_SESSION['items']){
        
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

        $_SESSION['items'] = json_decode($ticketStr, true);
        
        curl_close($curl);
        
    }

    return $_SESSION['items'];
}
/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

