<pre>
<?php


    const TPL_DIR = '../application/views';
    const CLEAN_HTML = FALSE;
    const KNIT_VERSION = 21;

    $files = find_all_files_tpl(TPL_DIR);
    $templates = Array();
    foreach($files as $file)
    {
        $f_path = str_replace(Array(TPL_DIR.'/', '.html'), '', $file);
        $templates[$f_path] = file_get_contents($file);
    }

    $UI_CACHE_FUNCS = Array();

    foreach ($templates as $name => $html)
    {
        print "Compiling $name\n";
        $is_tpl = preg_match('/layout/Usi', $name) ? false : true;
        $html = str_replace('"','\\"', $html);

        /* identify if-elseif-else chains */
        if (preg_match_all('/\<if(.*)\<\/if\>/Usi', $html, $matches))
        {

            $count_starts = substr_count($html, '<if');
            $count_ends   = substr_count($html, '</if');

            if ($count_starts != $count_ends)
                die("IF-ELSE statements compilation failed, counts mismatch - $count_starts != $count_ends\n");

            if (preg_match_all('/\<if(.*)\>\s/Usi', $html, $matches))
            {
                foreach ($matches[1] as $k => $v)
                {
                    $v = trim($v);
                    if ($v[0] == ':')
                        $v = substr($v,1,strlen($v));

                    $v = str_replace('\\"', '"', $v);

                    $v = '"; if ('.$v.') { print "';

                    $html = str_replace($matches[0][$k], $v, $html);
                }
            }
            if (preg_match_all('/\<elseif(.*)\>\s/Usi', $html, $matches))
            {
                foreach ($matches[1] as $k => $v)
                {
                    $v = trim($v);
                    if ($v[0] == ':')
                        $v = substr($v,1,strlen($v));

                    $v = str_replace('\\"', '"', $v);

                    $v = '"; } elseif ('.$v.') { print "';

                    $html = str_replace($matches[0][$k], $v, $html);
                }
            }
            if (preg_match_all('/\<else\>/Usi', $html, $matches))
            {
                foreach ($matches[0] as $k => $v)
                {
                    $html = str_replace($matches[0][$k], '"; } else { print "', $html);
                }

            }
            if (preg_match_all('/\<\/if\>/Usi', $html, $matches))
            {
                foreach ($matches[0] as $k => $v)
                {
                    $html = str_replace($matches[0][$k], '"; } print "', $html);
                }
            }
        }


        /* Iterator */
        if (preg_match_all('/\<iterator\:(.*)\:(.*)\/\>/Usi', $html, $matches))
        {
            foreach ($matches[2] as $key => $subtemplate)
            {
                $sub_name = $name.'_'.trim($subtemplate);
                $__var = $matches[1][$key];
                $sub_name = to_function_name($sub_name, true);

                $iterator_html  = "\";
                if (is_array(@$__var) || is_object(@$__var))
                {
                    foreach (@$__var as \$_KEY => \$_VAL)
                    {
                        print radSYS_TE::$sub_name(\$THIS, \$_DATA);
                    }
                } print \"";

                $html = str_replace($matches[0][$key], $iterator_html, $html);
            }
        }

        /* identifying urls */
        if (preg_match_all('/\<url\:(.*)\/\>/Usi', $html, $matches))
        {
            //print_R($matches);
            $urls = $matches[1];
            foreach ($urls as $key => $url)
            {
                @list($url, $cnt, $mhd) = explode(':', $url);

                $url = trim($url);
                $cnt = $cnt !== NULL ? trim($cnt) : NULL;
                $mhd = $mhd !== NULL ? trim($mhd) : NULL;

                $repl_text = "{\$_SYS->http_root}";
                switch ($url)
                {
                    case 'root' : break;
                    case 'controller' : $repl_text .='/'.(($cnt !== NULL and $cnt !== '') ? $cnt.'/'.(  ($mhd !== NULL and $mhd !== '') ? $mhd : '' ) : '') ; break;
                    case 'assets' : $repl_text .='/assets'; break;
                    default :
                        if (substr($url,0,4) == 'root')
                            $repl_text = str_replace('root',"{\$_SYS->http_root}", $url);
                        else
                            $repl_text .='/assets/'.$url;
                }

                $html = str_replace($matches[0][$key], $repl_text, $html);
            }
        }

        /* identifying function calls */
        if (preg_match_all('/\<function\:(.*)\/\>/Usi', $html, $matches))
        {
            $funcs = $matches[1];
            foreach ($funcs as $key => $func)
            {
                @list($func, $arguments) = explode(':', $func);
                $arguments = trim($arguments);
                //ob_start("radSYS_ob");
                if ($arguments == NULL)
                    $__radSYS_evalfunc = '".$THIS->'.$func.'()."';
                else
                {
                    $arguments = 'Array( '.$arguments.' )';

                    $__radSYS_evalfunc = '".call_user_func_array(array($THIS, "'.$func.'"), '.$arguments.')."';
                }
                //$conetents = ob_get_contents();
                //ob_end_clean();

                $repl_text = $__radSYS_evalfunc;
                $html = str_replace($matches[0][$key], $repl_text, $html);
                $__radSYS_evalfunc = $arguments = '';
            }
        }

        /* identifying inner templates */
        if (preg_match_all('/\<render\:(.*)\/\>/Usi', $html, $matches))
        {
            $rep_templates = $matches[1];
            foreach ($rep_templates as $key => $ttemplate)
            {
                $tname = to_function_name($ttemplate, true);
                $html = str_replace($matches[0][$key], "\".radSYS_TE::$tname(\$THIS, \$_DATA).\"", $html);
            }
        }


        /* identify sub templates */
        if (preg_match_all('/\<sub\:(.*)\>(.*)\<\/sub\>/Usi', $html, $matches))
        {
            $rep_templates = $matches[1];
            foreach ($rep_templates as $key => $subtemplate)
            {
                $sub_name = trim($name).'_'.trim($matches[1][$key]);
                $sub_code = $matches[2][$key];

                $templates[$sub_name] = $sub_code;
                $sub_name = to_function_name($sub_name, true);


                #$html = str_replace($matches[0][$key], "\".radSYS_TE::$sub_name(\$THIS, \$_DATA).\"", $html);
                $html = str_replace($matches[0][$key], "", $html);
            }
        }


        /* UI cached */
        if (preg_match_all('/\<cache\:(.*)\>(.*)\<\/cache\>/Usi', $html, $matches))
        {
            //print_r($matches);

            foreach ($matches[2] as $ckey => $inner)
            {
                $block = $matches[0][$ckey];
                $cname = $matches[1][$ckey];
                //$inner - laready there
                $did = "{$name}_{$cname}";
                $UI_CACHE_FUNCS[]= "
                public static function uistorage_{$did}()
                {
					global \$_APP, \$_LANG, \$_SYS;
					//\$_SYS->http_root     = &\$GLOBALS['http_url'];
                    return @\"$inner\";
                }
                ";

                $replace =
                "<div id='{$did}_storage'>".
                    "<script>".
                        "rad_ui_check_cache('{$did}');".
                    "</script>".
                "</div>";

                $html = str_replace($block, $replace, $html);
                print "  - creating cache : $did\n";
            }
        }


        /*if (preg_match_all('/\<form(.*)data\-model\=\\\"(.*)\\\"\s/Usi', $html, $matches))
        {
            print_r($matches);

            foreach ($matches[0] as $fkey => $block)
            {
                $attrs = $matches[1][$fkey];
                $model = $matches[2][$fkey];

                //$repla = "<form{$attrs}"
            }


            die;
        }*/
        
        if(preg_match_all('/<form(.*)>\s/Usi',$html,$matches) && isset($_GET['fl_create']))
        {
           
            foreach ($matches[1] as $k => $form)
            {
                /*if (!preg_match('/id\=/USi', $form))
                {
                    print "Error - formid missing in form #".($k+1)." in template '$name'\n";
                    continue;
                }
                $form = stripslashes($form);*/
                //print "$form\n";
                //$form .= ' rad-fl-id=\\"'.$name.'_'.$k.'\\"';
                //$form = str_replace('/', '_', $form);
                //$attrs = preg_split('/\s/', trim($form));
                //print_r($attrs);
                $html = str_replace($form, $form .' data-fl-id=\\"'.str_replace(['/','.'], '_',$name).'_'.$k.'\\"', $html);
            }
            //die;
        }



        $templates [$name] = $html;

    }



    $UI_CACHE_FUNCS = implode("\n", $UI_CACHE_FUNCS);

    foreach ($templates as $name => $html)
    {
        $is_tpl = preg_match('/layout/Usi', $name) ? false : true;
        $f_name = to_function_name($name, $is_tpl);

        if (CLEAN_HTML)
            $html = preg_replace('/\s+/',' ', $html);

        $templates[$name] = "
                /* $name - ".($is_tpl?'template':'layout')." */
                case '$f_name':
                {
                    print \"$html\";
                    break;
                }
        ";
    }


    $code = "<?php
    /*
     * radSYS - [knit] Template compiler v".KNIT_VERSION."
     * NOTE : do not modify this file! As it gets overwritten automatically
     *
     * TE file generated at : ".date('F d, Y h:i:sA', time())."
     */

    class radSYS_TE
    {
        $UI_CACHE_FUNCS
        public static function __callStatic(\$method, \$args)
        {
            global \$_APP, \$_DATA, \$_LANG, \$_KEY, \$_VAL, \$_SYS;

            \$THIS  = \$args[0];
            \$_DATA = \$args[1];
            \$_LANG = &\$THIS->lang;
            \$_SYS  = &\$THIS->SYS;


            ob_start(\"radSYS_ob\");

            switch(\$method) {

    ";

    $code .= implode("\n\n", $templates);
    $code .= "
                default:
                    print '{{invalid or missing template : '.\$method.\"}}\\n\\n\";
            }

            \$conetents = ob_get_contents();
            ob_end_clean();

            return \$conetents;
        }
    }


    if (!function_exists('radSYS_ob'))
    {
        function radSYS_ob(\$buffer)
        {
            return \$buffer;
        }
    }

    /** Test of invalid template **/
    //uncomment following line to test TE alone
    //print radSYS_TE::invalid_template(NULL, NULL);

    ";

    file_put_contents(
        '../includes/_compiled_templates.php',
        $code
    );


    print "<b>radSYS - knit Tempalte compiler v".KNIT_VERSION."...</b>\nTemplates / Layouts compiled successfully in ../includes/_compiled_templates.php at ".date("F d Y, h:i:sA", microtime(true))."\n\n</pre>
    <script>localStorage.clear();</script>";





    function to_function_name($path, $is_tpl=TRUE)
    {
        $tl = $is_tpl?'template':'layout';
        return //$tl.'_'.
                str_replace('/', '_', $path);
    }

    die;




    function find_all_files_tpl($dir)
    {
        $root = scandir($dir);
        foreach($root as $value)
        {
            if($value === '.' || $value === '..') {continue;}
            if(is_file("$dir/$value")) {$result[]="$dir/$value";continue;}
            foreach(find_all_files_tpl("$dir/$value") as $value)
            {
                $result[]=$value;
            }
        }
        return $result;
    }


