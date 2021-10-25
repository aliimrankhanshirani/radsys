<?php


    $GLOBALS['start_execution_titme'] = microtime(TRUE);

    session_start();

    $GLOBALS['Root'] = dirname($_SERVER['PHP_SELF']);
    $GLOBALS['SystemRoot'] = str_replace('/public_html','', $GLOBALS['Root']);
    $GLOBALS['Arguments'] = explode('/', substr($_GET['_url'],1, strlen($_GET['_url'])-1));
    if (count($GLOBALS['Arguments']) ==1 && trim($GLOBALS['Arguments'][0]) == '')
        unset($GLOBALS['Arguments'][0]);
    try
    {
        require_once '../includes/libraries/vendor/autoload.php'; // composer
        require_once '../includes/settings.php';
        $GLOBALS['http_url'] = DEFAULT_SCHEME . '://'.dirname($_SERVER['HTTP_HOST'].$_SERVER['SCRIPT_NAME']);

        require_once '../includes/_compiled_templates.php'; //template compiler

        require_once '../includes/core/orm.php';
        require_once '../includes/core/mvc.php';
        require_once '../includes/init.php';
        require_once '../includes/base_controller.php';


        /* Include language */
        require_once '../includes/languages/'.DEFAULT_LANGUAGE.'.php';
        require_once '../application/models/models.php';

        foreach ($GLOBALS['Arguments'] as $__ak => $__ab)
            if (trim($GLOBALS['Arguments'][$__ak]) == '')
                unset($GLOBALS['Arguments'][$__ak]);

        $GLOBALS['Arguments'] = array_values($GLOBALS['Arguments']);

        $controller = DEFAULT_CONTROLLER;
        $method = DEFAULT_METHOD;
        $controller_arguments = Array();



        if (count($GLOBALS['Arguments']) > 0) //has controller
        {
            $controller = trim($GLOBALS['Arguments'][0]);
        }


        if (count($GLOBALS['Arguments']) > 1) //has method
        {
            if (trim($GLOBALS['Arguments'][1]) != '')
            {
                $method = trim($GLOBALS['Arguments'][1]);
            }
            unset($GLOBALS['Arguments'][0], $GLOBALS['Arguments'][1]);
        }

        if (count($GLOBALS['Arguments']) > 0) //has arguemnts
        {
            $_ckeys = array_keys($GLOBALS['Arguments']);
            $_lkey  = $_ckeys[count($_ckeys)-1];

            if (trim($GLOBALS['Arguments'][$_lkey]) == '' or trim($GLOBALS['Arguments'][$_lkey]) == $controller)
                unset($GLOBALS['Arguments'][$_lkey]);

            $GLOBALS['Arguments'] = array_values($GLOBALS['Arguments']);

            $controller_arguments = $GLOBALS['Arguments'];
        }

        if (OUTPUT_COMPRESSION === TRUE)
            ob_start("ob_gzhandler");

        if ($controller == 'rad-ui-cache')
        {
            if ($method == 'clear')
                die("<script>localStorage.clear()</script>");

            if (!method_exists('radSYS_TE', $method))
                die("rad-UI-cahce error : $method (use knit and try again)");
            else
            {
                print radSYS_TE::$method();

                if (OUTPUT_COMPRESSION === TRUE)
                    ob_end_flush();

                die;
            }
        }


        $controller_file = '../application/controllers/'.$controller.'.php';
        if (!file_exists($controller_file))
            throw new Exception('Controller not found '.$controller_file);

        require_once $controller_file;


        if (!class_exists($controller))
        {
            throw new Exception('Malformated controller '.$controller);
        }

        $GLOBALS['System']->current_controller = $controller;

        $_controller = new $controller();


        if (!method_exists($_controller, $method) && $controller != 'api')
            throw new Exception("Method not found - controller '$controller::$method()'".htmlspecialchars($method));

        $method = htmlspecialchars($method);
        $GLOBALS['System']->current_method = $method;

        $out_put = call_user_func_array(array($_controller, "$method"), $controller_arguments);

        $out_put = isset($_GET['ajax'])||$_controller->is_service===TRUE ? $out_put : $_controller->render_layout();

        $GLOBALS['end_execution_titme'] = microtime(TRUE);

        $GLOBALS['total_execution_titme'] = $GLOBALS['end_execution_titme'] - $GLOBALS['start_execution_titme'];

        $out_put = str_replace('{SYS_EXECUTION_TIME}',
                        sprintf("%.4lf seconds", $GLOBALS['total_execution_titme']),
                        $out_put
                    );

            print ($_controller->is_service === TRUE)
                ? json_encode($out_put)
                : $out_put;
    }

    catch (Exception $e)
    {
        $___msg  = $e->getMessage();
        $___file = $e->getFile();
        $___line = $e->getLine();

        if (substr($___msg, 0, strlen(ERR_IDENTIFIER)) == ERR_IDENTIFIER)
        {
            echo 'PHP Error';
            #is PHP error
            $uns = unserialize(substr($___msg, strlen(ERR_IDENTIFIER), strlen($___msg)));

            $___msg  = $uns['error'];
            $___file  = $uns['file'];
            $___line  = $uns['line'];

        }

        $msg =  '<div style="font-family:tahoma,verdana;font-size:12px;"><b>Error/Exception</b>:'.$___msg.' LINE <b>'.$___line.'</b> FILE <a target="_blank" href="'.$GLOBALS['Root'].'/../temp_tools/error_tracker.php?error_file='.urlencode($___file).'&error_line='.$___line.'#error">'.$___file.'</a></div>';
        if (DEV_MODE === TRUE)
            print $msg;
        else
            print '<div>'.strip_tags($msg).'</div>';
    }



    if (OUTPUT_COMPRESSION === TRUE)
        ob_end_flush();
