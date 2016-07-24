<?php

session_id('iwscc');
session_start();

define(BASE_URL, "https://radiant-cove-60089.herokuapp.com/api/v1/");
define(LOGIN, "login");
define(GET_ITEMS, "assignedItems/tickets/");

require_once __DIR__.'/../vendor/autoload.php';
include dirname(__FILE__).'/../src/services/buildView.php';

$app = new Silex\Application();

$app->register(new Silex\Provider\TwigServiceProvider(), array(
    'twig.path' => __DIR__.'/../views',
));

$app->get('/', function() use($app){
    
    session_unset();
    
    return $app['twig']->render('login.twig');

});

$app->post('/edit', function() use($app){
    
    
    if(isset($_POST['id'])){
        
        
        $con = mysqli_connect("localhost", "phpuser", "phpuserpw", "iws_cc");
        if(!$con){
            exit('Connect Error (' . mysqli_connect_errno() . ')'
                    . mysqli_connect_error() );
        }
        mysqli_set_charset($con, 'utf-8');

        $dbQuery = "SELECT * FROM timesheets WHERE userId = " . $_SESSION['userId'] ." AND id = " . $_POST['id'];

        $iwsResult = mysqli_query($con, $dbQuery);
        
        $timesheet = array(
            'timesheet' => mysqli_fetch_all($iwsResult, MYSQLI_ASSOC),
        );
        
        mysqli_close($con);
        mysqli_free_result($iwsResult);
        
        if($timesheet['timesheet'][0]['billable'] == 1){
            $timesheet['timesheet'][0]['checked'] ='checked';
        } else{
            $timesheet['timesheet'][0]['checked'] = null;
        }
        
        return $app['twig']->render('editTicket.twig', $timesheet);
        
    }
    
    else {
        
           return $app['twig']->render('viewTimesheets.twig', $buildView = buildView($_SESSION['userId']));
    }

});

$app->post('/update', function() use($app){

        
        
        if(!isset($_POST['billable'])){
            $_POST['billable'] = 0;
        }
       
        $con = mysqli_connect("localhost", "phpuser", "phpuserpw", "iws_cc");
        
        if(!$con){
            exit('Connect Error (' . mysqli_connect_errno() . ')'
                    . mysqli_connect_error() );
        }
        mysqli_set_charset($con, 'utf-8');
        
        $dbQuery = "UPDATE timesheets SET hours=" . $_POST['hours'] . ", comments = '" . $_POST['comments'] . "', billable = " . $_POST['billable'] . "  WHERE id = " . $_POST['id'] . " AND userId = " . $_SESSION['userId'];
        
        if(!mysqli_query($con, $dbQuery)){
            echo 'something went wrong <br/>';
        }
        
        mysqli_close($con);

        return $app['twig']->render('viewTimesheets.twig', $buildView = buildView($_SESSION['userId']));
    
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

            if(!isset($_SESSION['userId'])){
                
                $_SESSION['userId'] = $user['userId'];
                
                preg_match_all('|Set-Cookie: (.*);|U', $data, $matches);   
                $_SESSION['cookie'] = implode('; ', $matches[1]);
                
            }
            
            return $app['twig']->render('viewTimesheets.twig', $buildView = buildView($_SESSION['userId']));
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
    
    if(!mysqli_query($con, $dbQuery)){
        
        echo 'check your form data';

    }
    
    mysqli_close($con);  
        
    return $app['twig']->render('viewTimesheets.twig', $buildView = buildView($_SESSION['userId']));
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

    }
   

    return $app['twig']->render('viewTimesheets.twig', $buildView = buildView($_SESSION['userId']));

   
});

return $app;



/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

