<?php

include dirname(__FILE__).'/../src/services/dbServices.php';


function buildView ($date, $con) {
    
    $buildView = array(
        
        'tickets' => $_SESSION['items'],
        'timesheets' => getTimesheets($date, $con)
        
    );
    
    return $buildView;
}

function getTimesheets($date, $con){

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
        "timesheets" => getAllTimesheets($con, $dateBegin, $dateEnd),
        "date" => $date
    );
    
    $_SESSION['begin'] = $dateBegin;
    $_SESSION['end'] = $dateEnd;
    
    return $timesheets;
   
}
