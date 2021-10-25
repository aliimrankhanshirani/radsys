<?php

    const MODELIZER_VERSION = 2.1;

    require_once '../includes/core/mvc.php';
    require_once '../includes/core/orm.php';
    require_once '../includes/settings.php';
    require_once '../includes/init.php';


    $schema = DB_NAME;//@$argv[1];

    require_once 'rad_retinfo.php';

    $tables = db_get_tables($schema);

    $infos = Array();

    foreach ($tables as $table)
    {
        $parents  = table_get_parents($schema, $table);
        $children = table_get_children($schema, $table);

        /* for resetful api */
        $parents_and_children = Array();
        foreach ($parents as $p)
            $parents_and_children []= $p['parent_table'];
        foreach ($children as $c)
            $parents_and_children []= $c['child_table'];

        $infos [$table] = Array(
            'fields'       => table_get_fields($schema, $table),
            'fileds_types' => table_get_fields_types($schema, $table),
            'children'     => $children,
            'parents'      => $parents,
            'mapable'      => $parents_and_children,
        );
    }

    ksort($infos);
    //echo '<pre>'; print_r($infos);die;

    $lengths = array_map('strlen', array_keys($infos));

    $php = Array();

    foreach ($infos as $table => $info)
    {
        $model_name = table_to_model_name($table);

        //print_r($field_types);

        $str = "
        class $model_name extends Model
        {
            public \$table_name   = '$table';
            public \$table_key    = '{$info['fields']['key']}';
            public \$table_fields = Array ('".implode("','", $info['fields']['fields'])    ."');
            public \$field_types  = ".str_replace('array', 'Array', preg_replace('/\s+/', ' ', var_export($info['fileds_types'], TRUE))).";

            /* for restful interface */
            public \$mapables     = Array ('".implode("','",$info['mapable'])."');
            public function is_mapable(\$property)
            {
                return in_array(\$property, \$this->mapables);
            }

            public static function new()
            {
                return new $model_name;
            }


            public function __get(\$property)
            {
                switch (\$property)
                {";

        if (!empty($info['parents']))
        {
            $str .= "\n
                    /* Parent entities */\n";
            /* Link to parent properties */
            foreach ($info['parents'] as $parent)
            {
                $parent_property = to_singular($parent['parent_table']);
                $parent_model = table_to_model_name($parent['parent_table']);
                $child_column = $parent['child_col'];
                $parent_column = $parent['parent_col'];

                $spaces = str_repeat(' ', max($lengths) - strlen($parent_property));

                $str .= "\n                    ".
                            "case '$parent_property' : {$spaces}return new $parent_model(\"`$parent_column`='\$this->$child_column'\");";
            }
        }

        if (!empty($info['children']))
        {
            $str .= "\n
                    /* Child entities */\n";

            /* Link to child properties */
            foreach ($info['children'] as $parent)
            {
                $child_property = to_plural($parent['child_table']);
                $child_model = table_to_model_name($parent['child_table']);
                $child_column = $parent['child_col'];

                $spaces = str_repeat(' ', max($lengths) - strlen($child_property));

                $str .= "\n                    ".
                            "case '$child_property' : {$spaces}return new $child_model(\"`$child_column`='\$this->{$info['fields']['key']}'\");";
            }
        }
        $str .= "


                    default: return parent::__get(\$property);
                }
            }
        }
        ";

        $php [$table]= $str;

    }

    if (@$get)
        echo '<pre>';
    $code =
    "<?php
    /*
     * radSYS - Modelizer v".MODELIZER_VERSION." generated models from database '$schema'
     * NOTE : do not modify this file! As it gets overwritten automatically
     */


".implode("\n\n", $php) ."\n\n\n";

    file_put_contents(MODELS_DIR.'/models.php', $code);

    print "<pre><b>radSYS - Modelizer v".MODELIZER_VERSION."...</b><br/>Models generated successfully in ".MODELS_DIR."/models.php at ".date("F d Y, h:i:sA", time())."\n\n";
    print "Models definition written to ".MODELS_DIR."/models.json</pre>\n\n";

    //print $code;

    foreach ($tables as $t=>$v)
        $tables[$t] = table_to_model_name($v);

    $tables = json_encode($tables, JSON_PRETTY_PRINT);
    $infos = json_encode($infos, JSON_PRETTY_PRINT);
    file_put_contents(MODELS_DIR.'/models.json', $tables);
    file_put_contents(MODELS_DIR.'/models_def.json', $infos);

