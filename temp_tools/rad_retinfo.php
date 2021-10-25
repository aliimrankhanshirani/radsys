<?php


    const MODELS_DIR = '../application/models';

	function db_get_tables($schema)
	{
		$R = $GLOBALS['Database']->get_connection()->query("SHOW TABLES FROM $schema") or die($GLOBALS['Database']->conn->error);
		$tables = Array();
		
		while ($t = $R->fetch_assoc())
			$tables [$t["Tables_in_$schema"]]= $t["Tables_in_$schema"];

		return $tables;
	}

	function table_get_fields($schema, $table)
	{
		if (!($R = $GLOBALS['Database']->get_connection()->query("SHOW COLUMNS FROM $schema.$table;")) ) 
            return Array();
        
        $fields = Array();
        $key = '';
        while ($row = $R->fetch_assoc()) 
        {
			if ($row['Key'] == 'PRI')
				$key = $row['Field'];
				
            $fields []= $row['Field'];
		}
		return Array(
			'key' => $key,
			'fields' => $fields,
		);
	}
	
	function table_get_fields_types($schema, $table)
	{
		$field_types =  Array();
        
        $q = "SELECT * FROM information_schema.columns WHERE table_name='$table' AND table_schema='$schema'";
        $r = $GLOBALS['Database']->get_connection()->query($q) or die(mysql_error());
        while ($row = $r->fetch_assoc()) 
        {
            $type_extra = Array();
            if (preg_match('/enum|set/Usi', $row['COLUMN_TYPE']))
                $type_extra = explode(',', str_replace(Array('enum','set', '(', ')', '\'', '"'),'', $row['COLUMN_TYPE']));
            
            $field_types[$row['COLUMN_NAME']] = Array(
                'type' =>$row['DATA_TYPE'],
                'extra' =>$type_extra,
                'length' => intval($row['CHARACTER_MAXIMUM_LENGTH']),
                'nullable' => strtolower($row['IS_NULLABLE']) == 'yes' ? TRUE : FALSE,
                'key_type' => $row['COLUMN_KEY'],
                'default' => $row['COLUMN_DEFAULT'],
            );
        }		
        return $field_types;
	}
	
	function table_get_children($schema, $table)
	{
		$Q = "
			SELECT
			  ke.referenced_table_name parent_table,
			  ke.table_name child_table,
			  ke.constraint_name,
			  ke.REFERENCED_COLUMN_NAME parent_col,
			  ke.COLUMN_NAME child_col
			FROM
			  information_schema.KEY_COLUMN_USAGE ke
			WHERE
			  ke.CONSTRAINT_SCHEMA ='$schema'
			  AND ke.referenced_table_name='$table'
			  AND ke.table_name IS NOT NULL
			ORDER BY
			  ke.referenced_table_name ASC, ke.table_name ASC, ke.COLUMN_NAME ASC
		";
		$R = $GLOBALS['Database']->get_connection()->query($Q) or die(mysql_error());
		$children = Array();
		while ($ref = $R->fetch_assoc())
		{
			$children []= $ref;
		}
		return $children;
	}	
	
	function table_get_parents($schema, $table)
	{
		$Q = "
			SELECT
			  ke.referenced_table_name parent_table,
			  ke.table_name child_table,
			  ke.constraint_name,
			  ke.REFERENCED_COLUMN_NAME parent_col,
			  ke.COLUMN_NAME child_col
			FROM
			  information_schema.KEY_COLUMN_USAGE ke
			WHERE
			  ke.CONSTRAINT_SCHEMA ='$schema'
			  AND ke.table_name='$table'
			  AND ke.referenced_table_name IS NOT NULL
			ORDER BY
			  ke.referenced_table_name ASC, ke.table_name ASC, ke.COLUMN_NAME ASC
		";
		$R = $GLOBALS['Database']->get_connection()->query($Q) or die(mysql_error());
		$children = Array();
		while ($ref = $R->fetch_assoc())
		{
			$children []= $ref;
		}
		return $children;
	}	
	
	
    function table_to_model_name($table)
    {
        if (preg_match('/\_/', $table))
        {
            $table = explode('_', $table);
            foreach ($table as $k => $v)
                $table[$k] = table_to_model_name($v);
            
            return implode('_', $table);
        }
        $table = strtolower($table);
        if (substr($table, -3) == 'ees')
            $table = substr($table, 0, strlen($table)-1);
        else
        if (substr($table, -3) == 'ies')
            $table = substr($table, 0, strlen($table)-3).'y';
        else
        if (substr($table, -2) == 'es' AND !(substr($table, -3) == 'ges'))
            $table = substr($table, 0, strlen($table)-2);
        else
        if (substr($table, -1) == 's')
            $table = substr($table, 0, strlen($table)-1);

        return ucfirst($table);

    }
    
    
    function to_singular($table)
    {
        return strtolower(table_to_model_name($table));
    }

    function to_plural($table)
    {
        return strtolower($table);
    }

