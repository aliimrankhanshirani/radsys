<?php


    /**
     * @author Sanan Saleem <sanan.fui@gmail.com>
     * 
     * @desc Cache whole project
     * 
     * Updated to ZendOpcache for PHP 8 by <Ali Shirani>
     * thanks to //https://github.com/rlerdorf/opcache-status
     */


	include '../includes/utilities.php';

    if (!function_exists('opcache_invalidate'))
        die("Zend OpCache extension doesn't seem be loaded");
    
   
    const SCRIPTS_DIR = '..';

    $files = find_all_files(SCRIPTS_DIR);
    
    print '<pre>';
        
    $templates = Array();
    $compiled = 0;
    opcache_reset();
    foreach($files as $file)
    {
		if (in_array(basename($file),
				[ 
					'rad_cache_scripts.php',
					'rad_extras.php',
				]
			)
		)
			continue;
        //opcache_invalidate($file,true);
        print "Caching $file - " .( ($status=opcache_compile_file($file)) ? 'successful' : '<span style="color:red">FAILED</span>')."\n";
        $compiled += (int)$status;
    }
    
    print "Total files compiled - $compiled\n";
    //print "Satistics powered by : https://github.com/rlerdorf/opcache-status";
    
    print '</pre>';

    
