<?php

    
    /* INIIALIZATIONS */
    
    set_error_handler('radSYS_error_handler', E_ERROR /*| E_WARNING | E_NOTICE */);
       
    new mysql(DB_HOST, DB_USER, DB_PASS, DB_NAME);

    new System;






    /* REGISTRATIONS */
    function radSYS_error_handler($num, $str, $file, $line)
    {
        throw new Exception (
            'radSYS_error~'.serialize(
                Array(
                    'line'  => $line,
                    'file'  => $file, 
                    'error' => $str,
                )
            )
        );
        //"<i>radSYS encountered error</i> <i>\"$str\"</i><br/>LINE #<b>$line</b> FILE xxx<a href='$file'>$file</a>");
    }
    



    /* OUTPUT BUFFERING */
    if (!function_exists('radSYS_ob'))
    {
        function radSYS_ob($buffer)
        {
            return $buffer;
        }    
    }
