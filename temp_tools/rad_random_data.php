<pre>
<?php

    set_time_limit(0);

    /**
     * Random Data Generator
     * @author Sanan Saleem <sanan.fui@gmail.com>
     *
     * WARNING : It emptries all the tables first!
     */

    const DD_VERSION = 2.0;
    const DD_NUM_ENTRIES = 10;

    require_once '../includes/core/mvc.php';
    require_once '../includes/core/orm.php';
    require_once '../includes/settings.php';

    require_once '../includes/init.php';


    $schema = DB_NAME;//@$argv[1];

    require_once 'rad_retinfo.php';

    $tables = db_get_tables($schema);
    
    


    foreach ($tables as $table => $info)
    {
        $model_name = table_to_model_name($table);

		//print "$model_name\n";

        $info = table_get_fields_types($schema, $table);
        $fields = Array();
		//_r($info);

        foreach ($info as $field => $detail)
        {
            $value = '';

            if ($detail['key_type'] == 'PRI')
            {
                unset($info[$field]);
                continue;
            }
            if ($detail['key_type'] == 'MUL')
            {
                $value = 1;
            }
            else
            {
                if (preg_match('/int/Usi', $detail['type']))
                {
					if (strtolower($detail['type']) == 'tinyint')
						$value=rand()%2;
					else
						$value = rand() % 256;
                }
                else
                if (preg_match('/char|text/Usi', $detail['type']))
                {
				    if (preg_match('/email|mail/Usi', $field))
						$value = preg_replace('/\s/','', "$table$field".abs(rand(999, 9999999))."@".abs(rand(999,9999999)).".com");
					else
					if (preg_match('/pass/Usi', $field))
						$value = md5('123123');
                    else
						$value = random_string ($detail['length']);

                }
                else
                if (preg_match('/date|time|timestamp/Usi', $detail['type']))
                {
                    $value = date('Y-m-d', time());//NOW;
                }
                else
                if (preg_match('/enum|set/Usi', $detail['type']))
                {
                    shuffle($detail['extra']);
                    $value = $detail['extra'][0];

                }


            }

            //if (preg_match('/pass/', $field))
                //$value = substr(md5($value . microtime()),0,$detail['length']);

            $fields [$field] = "'".$value."'";
//			print "$field - done\n";
        }

        $tables [$table] = $fields;
		//print "$table done\n";

    }
    

    $GLOBALS['Database']->get_connection()->query('SET @@FOREIGN_KEY_CHECKS=0;') or die(mysql_error());

    $trunc = $tables;
    

	foreach ($trunc as $table => $data)
	{
		$Q = "TRUNCATE $table;";

		if ($GLOBALS['Database']->get_connection()->query($Q))
		{
			print "Successful : $Q\n";

		}
	}

	

    $trunc = &$tables;


	foreach ($trunc as $table => $data)
	{
		$Q = "INSERT INTO `$table` (`".implode('`,`',array_keys($data))."`) VALUES \n".

				implode(", ", array_fill(0,DD_NUM_ENTRIES, "(".implode(',', $data).")"));
				
		

		$successful = FALSE;
		if ($GLOBALS['Database']->get_connection()->query($Q))
			$successful = TRUE;

		if ($successful)
		{
			print DD_NUM_ENTRIES." records inserted to $table\n";
		} else 
			print "Error : $Q\n";
	}
	die;

    $GLOBALS['Database']->get_connection()->query('SET @@FOREIGN_KEY_CHECKS=1;') or die(mysql_error());


    print '</pre>';


    function random_string($length)
    {
        //$__words = explode(' ', 'Lorem ipsum dolor sit amet, consectetur adipisicing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat Sed ut perspiciatis unde omnis iste natus error sit voluptatem accusantium doloremque laudantium, totam rem aperiam');
        $__words = explode(' ', 'a quick brown fox jumps over the lazy dog');

        shuffle($__words);

        return substr(implode(' ', $__words), 0, $length);
    }
