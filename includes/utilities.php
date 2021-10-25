<?php


    function find_all_files($dir, $pattern='.php')
    {
        $result = Array();
        
        if (! ($root = @scandir($dir)))
            return $result;
        
        foreach ($root as $value)
        {
            if ($value === '.' || $value === '..'  || $value==='.git') {continue;}
            
            
            if (is_file("$dir/$value") AND preg_match('/'.$pattern.'/Usi', $value)) 
            {
                $result[]="$dir/$value";
                continue;
            }
            foreach (find_all_files("$dir/$value", $pattern) as $value)
            {
                $result[]=$value;
            }
        }
        return $result;
    } 


