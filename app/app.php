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
include dirname(__FILE__).'/../src/services/extAPI.php';

$app = new Silex\Application();

$con = connectToDb();
     
$app->register(new Silex\Provider\TwigServiceProvider(), array(
    'twig.path' => __DIR__.'/../views',
));

$app->register(new Silex\Provider\SessionServiceProvider);

$app->register(new Silex\Provider\AssetServiceProvider(), array(
    'assets.named_pacakages' => array(
        'css' => array(
            'version' => 'css3', 
            'base_path' => '../src/css/'
            )
    )
));

$app->get('/', function() use($app, $con){
    
    if($_SESSION['userId'] != undefined){
        
        mysqli_close($con);
        session_unset();
        
    }

    return $app['twig']->render('login.twig');

});

$app->get('/home', function() use($app, $con){
    
    session_start();
    
    return $app['twig']->render('viewTimesheets.twig', $buildView = buildView($date=null, $con));
    
});

$app->post('/week' , function() use($app, $con){

    session_start();

    switch ($_POST['source']){
        case 'view':
            return $app['twig']->render('viewTimesheets.twig', $buildView = buildView($_POST['week'], $con));
            break;
        case 'admin':
            $buildView = buildView($_POST['week'], $con);
            buildCSV();
            return $app['twig']->render('admin.twig', $buildView);
            break;
    }
    
});

$app->post('/setFlag', function() use($app, $con){
    
    session_start();
    
    setFlag();
    
    return $app['twig']->render('adminPartial.twig', $buildView = buildView($_SESSION['begin'], $con));
});

$app->post('/', function() use($app, $con){
    
    session_start();
    
    if($_POST['username'] == "" ||  $_POST['password'] == ""){
        
        $app['session']->getFlashBag()->add('auth_err', 'please supply username/password');
        
        return $app['twig']->render('login.twig');
        
    }
    
    else{
        
        $user = getUser();
        
        if(!$user["success"]){
            
            $app['session']->getFlashBag()->add('auth_err', $user['error']);
            
            return $app['twig']->render('login.twig');
        
        }
        
        else {
            
            $tickets = getTickets();
            
            if($tickets == null){
                
                $app['session']->getFlashBag()->add('add_view_err', 'No tickets retrieved! '.$tickets['error']);
                
            }

            return $app['twig']->render('viewTimesheets.twig', $buildView = buildView($date=null, $con));

        }
    }
});


$app->post('/add', function() use($app, $con){
    
    session_start();
    
    if ($_POST['created'] == "" || $_POST['hours'] == "" || $_POST['comments'] == ""){
        
        $app['session']->getFlashBag()->add('add_view_err', 'Empty text field submitted!');
        
    }
        
    else{
        
        $resp = addTimesheet($con);
        
        if(!$resp){
            
            $app['session']->getFlashBag()->add('add_view_err', 'Record insertion unsuccesful!');
            
        }
        
    }
    
    return $app['twig']->render('viewTimesheets.twig', $buildView = buildView($date = null, $con));
});

$app->post('/delete', function() use($app, $con){
    
    session_start();
    
    if(isset($_POST['id'])){
        deleteTimesheet($con);
    }
    
    return $app['twig']->render('viewTimesheets.twig', $buildView = buildView($date = null, $con));
});

$app->post('/edit', function() use($app, $con){
    
    session_start();

    $timesheet = getTimesheet($con);

    if($timesheet['timesheet'][0]['billable'] == 1){
        $timesheet['timesheet'][0]['checked'] ='checked';
    } else{
        $timesheet['timesheet'][0]['checked'] = null;
    }

    return $app['twig']->render('editTimesheet.twig', $timesheet);

});

$app->post('/update', function() use($app, $con){
    
    session_start();
    
    if(!isset($_POST['billable'])){
        $_POST['billable'] = 0;
    }

    $resp = updateTimesheet($con);
    
    if(!$resp){
        
        $app['session']->getFlashBag()->add('update_view_err', 'Record update unsuccesful!');
        
    }
    
    else{
        
        return $app['twig']->render('viewTimesheets.twig', $buildView = buildView($date=null, $con));
        
    }

    
    
});

return $app;

