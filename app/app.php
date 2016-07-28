<?php

session_id('iwscc');
session_start();

define(BASE_URL, "https://radiant-cove-60089.herokuapp.com/api/v1/");
define(LOGIN, "login");
define(GET_ITEMS, "assignedItems/tickets/");

require_once __DIR__.'/../vendor/autoload.php';
include dirname(__FILE__).'/../src/services/buildView.php';
include dirname(__FILE__).'/../src/services/buildCSV.php';
include dirname(__FILE__).'/../src/services/setExpFlag.php';
include dirname(__FILE__).'/../src/services/dbServices.php';

$app = new Silex\Application();


        
$app->register(new Silex\Provider\TwigServiceProvider(), array(
    'twig.path' => __DIR__.'/../views',
));

$app->get('/', function() use($app){
    
    session_unset();
    
    return $app['twig']->render('login.twig');

});

$app->get('/home', function() use($app){
    
    session_start();
    
    $con = connectToDb();
    
    return $app['twig']->render('viewTimesheets.twig', $buildView = buildView($_SESSION['userId'], $date=null, $con));
    
});

$app->post('/week' , function() use($app){

    session_start();
    
    $con = connectToDb();

    switch ($_POST['source']){
        case 'view':
            return $app['twig']->render('viewTimesheets.twig', $buildView = buildView($_SESSION['userId'], $_POST['week'], $con));
            break;
        case 'admin':
            $buildView = buildView($_SESSION['userId'], $_POST['week'], $con);
            buildCSV();
            return $app['twig']->render('admin.twig', $buildView);
            break;
    }
    
});

$app->post('/setFlag', function() use($app){
    
    session_start();
    
    setFlag();
    
    $con = connectToDb();
    
    return $app['twig']->render('adminPartial.twig', $buildView = buildView($_SESSION['userId'], $_SESSION['begin'], $con));
   

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
            
            $con = connectToDb();
            
            return $app['twig']->render('viewTimesheets.twig', $buildView = buildView($_SESSION['userId'], $date=null, $con));
        }
    }
});


$app->post('/add', function() use($app){
    
    session_start();
    
    $_POST['userId'] = $_SESSION['userId'];
    
    $con = connectToDb();
    
    addTimesheet($con);
        
    return $app['twig']->render('viewTimesheets.twig', $buildView = buildView($_SESSION['userId'], $date = null, $con));
});

$app->post('/delete', function() use($app){
    
    session_start();
    
    if(isset($_POST['id'])){
        $con = connectToDb();
        deleteTimesheet($con);
    }
    
    return $app['twig']->render('viewTimesheets.twig', $buildView = buildView($_SESSION['userId'], $date=null, $con));
});

$app->post('/edit', function() use($app){
    
    $con = connectToDb();

    $timesheet = getTimesheet($con);

    if($timesheet['timesheet'][0]['billable'] == 1){
        $timesheet['timesheet'][0]['checked'] ='checked';
    } else{
        $timesheet['timesheet'][0]['checked'] = null;
    }

    return $app['twig']->render('editTimesheet.twig', $timesheet);

});

$app->post('/update', function() use($app){

        
    if(!isset($_POST['billable'])){
        $_POST['billable'] = 0;
    }

    $con = connectToDb();

    updateTimesheet($con);

    return $app['twig']->render('viewTimesheets.twig', $buildView = buildView($_SESSION['userId'], $date=null, $con));
    
});

return $app;



/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

