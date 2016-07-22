<?php

session_id('iwscc');
session_start();

define(BASE_URL, "https://radiant-cove-60089.herokuapp.com/api/v1/");
define(LOGIN, "login");
define(GET_ITEMS, "assignedItems/tickets/");

require_once __DIR__.'/../vendor/autoload.php';
include dirname(__FILE__).'/../src/services/getTimesheets.php';
include dirname(__FILE__).'/../src/services/getItems.php';

$app = new Silex\Application();

$app->register(new Silex\Provider\TwigServiceProvider(), array(
    'twig.path' => __DIR__.'/../views',
));

$app->get('/', function() use($app){
    
    session_unset();
    
    return $app['twig']->render('login.twig');

});

$app->post('/edit', function() use($app){
    
    echo 'editing ticket id: '.$_POST['id'].'<br/>';
    
    return $app['twig']->render('editTicket.twig');
});

$app->post('/', function() use($app){
    
    session_start();
    
    if(empty($_POST['username']) && !empty($_POST['password'])){
        
        return $app['twig']->render('login.twig');
        
    }
    
    else{

        $curl = curl_init();

        $postOptions = array(

            CURLOPT_URL => BASE_URL.LOGIN,
            CURLOPT_HEADER => true,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_FOLLOWLOCATION => false,
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => http_build_query($_POST),
        );

        curl_setopt_array($curl, $postOptions);

        $data = curl_exec($curl);

        $split = preg_split('|{|', $data);
        $user = json_decode('{'.$split[1], true);
      
        curl_close($curl);
        
        if(!$user["success"]){
            
            return $app['twig']->render('login.twig').'wrong user/pw combo';
        
        }
        
        else {
//            
            if(!isset($_SESSION['userId'])){
                
                $_SESSION['userId'] = $user['userId'];
                
                preg_match_all('|Set-Cookie: (.*);|U', $data, $matches);   
                $_SESSION['cookie'] = implode('; ', $matches[1]);
                
            }
            
            $tickets = getItems($_SESSION['userId']);
            
            $timesheets = getTimesheet($_SESSION['userId']);

            $buildView = array(
                'tickets' => $tickets,
                'timesheets' => $timesheets,
             );
            
            return $app['twig']->render('viewTimesheets.twig', $buildView);
        }
    }
});


$app->post('/addTimesheet', function() use($app){
    
    session_start();
    
    $userId = $_SESSION['userId'];

    $_POST['userId'] = $_SESSION['userId'];
   
    $con = mysqli_connect("localhost", "phpuser", "phpuserpw", "iws_cc");
            if(!$con){
                exit('Connect Error (' . mysqli_connect_errno() . ')'
                        . mysqli_connect_error() );
            }
    mysqli_set_charset($con, 'utf-8');

    $values = "'". $_POST['created'] . "', " . $_POST['userId'] . ", " . $_POST['hours'] . ", '" .$_POST['ticket'] . "', '" . $_POST['comments'] . "', '" . $_POST['billable'] . "' ";
    
    $dbQuery = "INSERT INTO timesheets (created, userId, hours, ticket, comments, billable) VALUES (" . $values . ")";
            
    if($iwsResult = mysqli_query($con, $dbQuery)){
        
//            BUILD ARRAY TO SEND TO TWIG TEMPLATE (EXISTING TIMESHEETS FROM TIMESHEET DB + ASSIGNED ITEMS FROM TICKET DB
        
        $timesheets = getTimesheet($_SESSION['userId']);

        $tickets = getItems($_SESSION['userId']);

        $buildView = array(
            'tickets' => $tickets,
            'timesheets' => $timesheets,
         );

        return $app['twig']->render('viewTimesheets.twig', $buildView);
    }
    
    else {
        
        echo 'check your form data';
        
    }
    
    mysqli_close($con);
    mysqli_free_result($iwsResult);
    
    return $app['twig']->render('viewTimesheets.twig', $tickets);
});

$app->post('/delete', function() use($app){
    
    session_start();
    
    if(isset($_POST['id'])){
        
        $con = mysqli_connect("localhost", "phpuser", "phpuserpw", "iws_cc");

        if(!$con){
            exit('Connect Error (' . mysqli_connect_errno() . ')'
                    . mysqli_connect_error() );
        }

        mysqli_set_charset($con, 'utf-8');

        $dbQuery = "DELETE FROM timesheets WHERE id = ". $_POST['id'] . " AND userId = " . $_SESSION['userId'];

        mysqli_query($con, $dbQuery);
        
        mysqli_close($con);

        $tickets = getItems($_SESSION['userId']);

        $timesheets = getTimesheet($_SESSION['userId']);
        
        $buildView = array(
            'tickets' => $tickets,
            'timesheets' => $timesheets,
        );
        
        return $app['twig']->render('viewTimesheets.twig', $buildView);

    }
    
    else {
        
        $tickets = getItems($_SESSION['userId']);

        $timesheets = getTimesheet($_SESSION['userId']);
        
        $buildView = array(
            'tickets' => $tickets,
            'timesheets' => $timesheets,
        );
        
        return $app['twig']->render('viewTimesheets.twig', $buildView);
    }
   
   
    
    
    
});

return $app;



/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

