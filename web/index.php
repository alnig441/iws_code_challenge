<!DOCTYPE html>
<!--
To change this license header, choose License Headers in Project Properties.
To change this template file, choose Tools | Templates
and open the template in the editor.
-->

<html>
    <head>
        <meta charset="UTF-8">
        <title></title>
        <style>
            html {font-family: "Courier New", sans-serif, monospace;}
            table {border: 1px; width: 75%; text-align: center; white-space: nowrap;}
            thead, th {font-weight: normal; border: 1px solid;}
            .table_text {white-space: nowrap; overflow: ellipsis; text-align: left;}
            .comments{width: 50%;}
            #logout {display: inline; position: absolute; right: 25%;}
            #datepicker {display: inline; position: absolute; left: 25%;}
        </style>   
    </head>
    <body>
        <?php
            require_once __DIR__.'/../app/app.php';
            
            $app['debug']=true;
            
            $app->run();
        
        ?>

    </body>
</html>
