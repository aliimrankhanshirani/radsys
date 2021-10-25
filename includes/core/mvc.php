<?php

    interface ICache
    {
        public function set($key, $value);
        public function get($key);
        public function remove($key);

        public function __set($key, $value);
        Public function __get($key);
    }

    class Session implements ICache
    {
        public function set($key, $value)
        {
            $_SESSION[$key] = $value;
        }

        public function get($key)
        {
            return isset($_SESSION[$key]) ? $_SESSION[$key] : NULL;
        }

        public function remove($key)
        {
            unset($_SESSION[$key]);
        }

        public function __set($key, $value)
        { $this->set($key, $value); }

        public function __get($key)
        { return $this->get($key); }
    }


    class FlashMessage extends Session
    {
        public function set($key, $message)
        {
            parent::set($key, $message);
        }

        public function get($key)
        {
            $msg = parent::get($key);
            parent::remove($key);

            return $msg;
        }
    }


    class Cookie implements ICache
    {
        public function set($key, $value, $time=3600)
        {
            return setcookie($key, serialize($value), time() + $time, COOKIE_PATH, COOKIE_DOMAIN);
        }

        public function get($key)
        {
            return isset($_COOKIE[$key])?unserialize($_COOKIE[$key]):NULL;
        }

        public function remove($key)
        {
            return @setcookie($key, '', time(), COOKIE_PATH, COOKIE_DOMAIN);
        }

        public function __set($key, $value)
        { self::set($key, $value); }

        public function __get($key)
        { return self::get($key); }
    }

    class LFS implements ICache
    {
        public function set($key, $value, $serialization=true)
        {
            @file_put_contents(CACHE_DIR.'/'.crc32($key), $serialization ? serialize($value) : $value);
        }

        public function get($key,$serialization=true,$DIR=CACHE_DIR)
        {
            $ret = @file_get_contents($DIR.'/'.crc32($key), $serialization=true);
            return $ret? ($serialization ? @unserialize($ret) : $ret) :NULL;
        }

        public function remove($key)
        {
            @unlink(CACHE_DIR.'/'.crc32($key));
        }

        public function __set($key, $value)
        { self::set($key, $value); }

        public function __get($key)
        { return self::get($key); }
    }

    if (class_exists('Memcache'))
    {
        class Cache implements ICache
        {
            public $memcache=NULL;

            public function __construct()
            {
                if(!isset($GLOBALS['Memcache']))
                {
                    $GLOBALS['Memcache'] = new Memcache;
                    $GLOBALS['Memcache']->connect(MEMCACHE_HOST, MEMCACHE_PORT);
                }

                $this->memcache = &$GLOBALS['Memcache'];
            }

            public function set($key, $value)
            {
                return $this->memcache->set(md5($key), $value, MEMCACHE_COMPRESSED, MEMCACHE_TIMEOUT);
            }

            public function get($key)
            {
                $v = $this->memcache->get(md5($key));
                return  NULL;//!$v ? NULL : $v;
            }

            public function __set($key, $value)
            {
                return $this->set($key, $value);
            }

            public function __get($key)
            {
                return $this->get($key);
            }

            public function remove($key)
            {
                return $this->memcache->delete(md5($key));
            }

            public function flush()
            {
                $this->memcache->flush();
            }

            public function all()
            {
                return NULL;//$this->memcache->fetchAll();
            }
        }
    }


    class Security
    {
        public static function secure_for_sql($data)
        {
            return mysql_real_escape_string(htmlentities($data, ENT_QUOTES, 'utf-8'));
        }

        public static function valid_email($email)
        {
            return filter_var($email, FILTER_VALIDATE_EMAIL) === TRUE;
        }

        public static function encrypt1($data)
        {
            return md5($data);
        }
    }


    class System
    {
        public  $DB,
                $SYS,
                $ARGS,
                $root,
                $http_root,
                $LANG;

        public  $post,
                $get,
                $cookies,
                $session,
                $lfs,
                $raw_post,
                $raw_post_data, #parseed
                $files,
                $cache = NULL,
                $flashmessage,
                $current_controller,
                $current_method,
                $request_method,
                $url,
                $accept_content_type=NULL,
                $request_headers;

        public function __construct()
        {
            $GLOBALS['Session'] = new Session;
            $GLOBALS['Cookie'] = new Cookie;
            $GLOBALS['LFS'] = new LFS;
            $GLOBALS['Flashmessage'] = new FlashMessage;
            if (class_exists('Memcache'))
                $GLOBALS['Cache'] = new Cache;

            $this->raw_post_data =
            $this->raw_post = file_get_contents('php://input');

            $this->url      = &$_SERVER['REDIRECT_URL'];
            $this->post     = &$_POST;
            $this->get      = &$_GET;
            $this->files    = &$_FILES;
            $this->lang     = &$GLOBALS['Language'];
            $this->DB       = &$GLOBALS['Database'];
            $this->ARGS     = &$GLOBALS['Arguments'];
            $this->root     = &$GLOBALS['Root'];
            $this->http_root     = &$GLOBALS['http_url'];

            //ICache implementations
            $this->session  = &$GLOBALS['Session'];
            $this->cookies  = &$GLOBALS['Cookie'];
            $this->lfs      = &$GLOBALS['LFS'];
            $this->flashmessage      = &$GLOBALS['Flashmessage'];
            if (class_exists('Memcache'))
                $this->cache      = &$GLOBALS['Cache'];
            $this->request_method = strtoupper(@$_SERVER['REQUEST_METHOD']);

            $this->request_headers = $this->get_request_headers();
            if (isset($this->request_headers['x-http-method']))
                $this->request_method  = strtoupper($this->request_headers['x-http-method']);

            if ($this->request_method == 'UPDATE')
                $this->request_method = 'PUT';

            if (isset($this->request_headers['content-type']) && $this->raw_post != '')
            {
                if (
                    preg_match('/application\/json/Usi', $this->request_headers['content-type'])
                    OR
                    'application\/json' == $this->request_headers['content-type']
                )
                {
                    if ($temp = json_decode($this->raw_post, TRUE))
                    {
                        $_POST = $this->raw_post_data = $temp;
                    }
                    else
                    {
                        $temp = NULL;
                        parse_str($this->raw_post, $temp);

                        if (is_array($temp))
                            $_POST = $this->raw_post_data = $temp;
                    }
                }
            }

            $GLOBALS['System']  = $this;
        }

        public function redirect($url, $method=NULL)
        {
            if (is_string($url) && $mthod==NULL)
                header('Location: '.$url);
            else
            if (is_string($url))
                print('Location: '.$this->http_root."/$url/$method");
            else
            if (is_object($url))
                header('Location: '.$this->get_controller_url($url, $method));

            die;
        }

        public function get_controller_url($controller, $method=NULL)
        {
            return $this->root . '/' .
                (is_object($controller) ? $controller->class : $controller).
                (is_string($method) ? '/'.$method : '')
            ;
        }

        public function get_request_headers()
        {

            $ret = Array();
            if (function_exists('getallheders'))
                $ret = getallheders();
            else
            if (function_exists('apache_request_headers'))
                $ret = apache_request_headers();
            else
            {
                $arh = array();
                $rx_http = '/\AHTTP_/';
                foreach($_SERVER as $key => $val) {
                        if( preg_match($rx_http, $key) ) {
                                $arh_key = preg_replace($rx_http, '', $key);
                                $rx_matches = array();
                                // do some nasty string manipulations to restore the original letter case
                                // this should work in most cases
                                $rx_matches = explode('_', strtolower($arh_key));
                                if( count($rx_matches) > 0 and strlen($arh_key) > 2 ) {
                                        foreach($rx_matches as $ak_key => $ak_val) $rx_matches[$ak_key] = ucfirst($ak_val);
                                        $arh_key = implode('-', $rx_matches);
                                }
                                $arh[$arh_key] = $val;
                        }
                }
                if(isset($_SERVER['CONTENT_TYPE'])) $arh['Content-Type'] = $_SERVER['CONTENT_TYPE'];
                if(isset($_SERVER['CONTENT_LENGTH'])) $arh['Content-Length'] = $_SERVER['CONTENT_LENGTH'];
                $ret = $arh;
            }

            $return = Array();

            foreach ($ret as $k => $v)
                $return[strtolower($k)] = $v;

            return $return;
        }


    }

    class BaseController
    {
        public $layout = 'NONE';

        public $is_service = FALSE;

        protected $RENDERS = Array(), $SUBTEMPLATES = Array(),  $LAYOUT_DATA = Array();
        public $post, $get, $cookies, $session, $raw_post, $files, $lfs, $cache = NULL, $flashmessage;
        public $lang, $class, $error_report = Array('errors' => Array());
        public $DB, $SYS, $ARGS;

        public function __construct()
        {
            $this->raw_post = file_get_contents('php://input');
            $this->post     = &$_POST;
            $this->get      = &$_GET;
            $this->files    = &$_FILES;
            $this->lang     = &$GLOBALS['Language'];
            $this->DB       = &$GLOBALS['Database'];
            /* todo */
            $this->SYS      = &$GLOBALS['System'];
            $this->ARGS     = &$GLOBALS['Arguments'];
            $this->class   = get_class($this);

            //ICache implementations
            $this->session  = &$GLOBALS['Session'];
            $this->cookies  = &$GLOBALS['Cookie'];
            $this->lfs      = &$GLOBALS['LFS'];
            $this->flashmessage      = &$GLOBALS['Flashmessage'];
            if (class_exists('Memcache'))
                $this->cache      = &$GLOBALS['Cache'];            
            

            $this->set_view_start();

            return $this;
        }
        const NOT_AVAILABLE = 'a';
        
        
        private function clean_arr(Array $arr, $func = 'htmlspecialchars')
        {
            if (!is_array($arr))
                return $arr;
            
            foreach ($arr as $k => $v)
            {
                if (is_array($v))
                    $arr[$k] = $this->clean_arr($v);
                else
                    $arr[$k] = $func($v);
            }
            return $arr;
        }
        
        public function clean_post()
        {
            return $this->clean_arr($this->post);
        }
        
        public function report_errors($e_data = Array(), $custom_msgs = Array())
        {
            if (isset($e_data['errors']))
            {
                if (!empty($e_data['errors']))
                {
                    $this->reported_errors = $e_data['errors'];
                    if (!empty($custom_msg))
                        $this->reported_error_msgs = $custom_msg;
                }
            }
        }
        public function render_str($str, $area=NULL, $template=NULL)
        {
            static $tpl_counter=0;

            $area = trim($area);
            //if ($area =='')
                //$area = $this->get_view_data()[0];

            if (!isset($this->RENDERS [$area]))
                $this->RENDERS [$area] = Array();
  

            $key = md5($template).'_'.$template;

            if ($template === NULL)
                $key = md5($str).'_GENERALSTR' .(++$tpl_counter);

            $this->RENDERS [$area] [$key]= $str;

            return $this;
        }

        public function render($template, $area, $data=Array())
        {
            $template_data = $this->eval_template($template, $data); ////replace with evaluated template
            $this->render_str($template_data, trim($area), $template);

            return $this;
        }

        public function eval_template($template, $data=Array(), $is_sub=FALSE, $main_template=NULL)
        {
            global $_APP, $_DATA, $_LANG, $_KEY, $_VAL;

            $_DATA = $data;
            $_LANG = &$this->lang;

            if (DEV_MODE === FALSE)
            {
                $tpl_function = str_replace('/','_', $template);
                return radSYS_TE::$tpl_function($this, $data);
            }

            $template = trim($template);
            $tpl_data = '<!-- NO TEMPLATE READ -->';

            if ($is_sub===FALSE)
            {
                $template_file = '../application/views/'.$template.'.html';

                if (!file_exists($template_file))
                    throw new Exception("Template missing : {$template}");

                $tpl_data = file_get_contents($template_file);
            }
            else
            {
                $index = 's_'.md5($main_template).'_'.$template;
                $tpl_data = $this->SUBTEMPLATES[ $index ];
            }


            if (preg_match_all('/\<if(.*)\<\/if\>/Usi', $tpl_data, $matches))
            {
                $matches = $matches[0];

                foreach ($matches as $ikey => $chain_code)
                {
                    $tpl_data = str_replace($chain_code, '{{cannot run following statement without knit:<br/>'.htmlspecialchars($chain_code).'}}', $tpl_data);
                }
            }


            /* identifying SUB templates - extract and maintain stack */
            if (preg_match_all('/\<sub\:(.*)\>(.*)\<\/sub\>/Usi', $tpl_data, $matches))
            {
                $rep_templates = $matches[1];
                foreach ($rep_templates as $key => $subtemplate)
                {
                    $this->SUBTEMPLATES['s_'.md5($template) .'_'. $matches[1][$key] ] = $matches[2][$key];
                    $tpl_data = str_replace($matches[0][$key], "<!-- sub template ".$matches[1][$key]." discarded -->\n", $tpl_data);
                }
            }


            /* evaluate and render subtemplates */
            if (preg_match_all('/\<rendersub\:(.*)\/\>/Usi', $tpl_data, $matches))
            {
                $rep_templates = $matches[1];
                foreach ($rep_templates as $key => $subtemplate)
                {
                    $subhtml = $this->eval_template($matches[1][$key], $data, TRUE, $template);
                    $tpl_data = str_replace($matches[0][$key], "<!-- sub-start:".$matches[1][$key]." -->\n".$subhtml."<!-- sub-end:".$matches[1][$key]." -->\n", $tpl_data);
                }
            }

            /* Iterator */
            if (preg_match_all('/\<iterator\:(.*)\:(.*)\/\>/Usi', $tpl_data, $matches))
            {
                foreach ($matches[2] as $key => $subtemplate)
                {
                    $subtemplate = trim($subtemplate);
                    @eval("\$__var = ".$matches[1][$key].";");

                    $iterator_html = '';
                    foreach ($__var as $_KEY => $_VAL)
                    {
                        $iterator_html .= $this->eval_template($subtemplate, $data, TRUE, $template);
                    }

                    $tpl_data = str_replace($matches[0][$key], $iterator_html, $tpl_data);
                }
            }


            /* identifying urls */
            if (preg_match_all('/\<url\:(.*)\/\>/Usi', $tpl_data, $matches))
            {
                //print_R($matches);
                $urls = $matches[1];
                foreach ($urls as $key => $url)
                {
                    @list($url, $cnt, $mhd) = explode(':', $url);

                    $url = trim($url);
                    $cnt = $cnt !== NULL ? trim($cnt) : NULL;
                    $mhd = $mhd !== NULL ? trim($mhd) : NULL;

                    $repl_text = $this->SYS->http_root;
                    switch ($url)
                    {
                        case 'root' : break;
                        case 'controller' : $repl_text .='/'.(($cnt !== NULL and $cnt !== '') ? $cnt.'/'.(  ($mhd !== NULL and $mhd !== '') ? $mhd : '' ) : '') ; break;
                        case 'assets' : $repl_text .='/assets'; break;
                        default :
                            if (substr($url,0,4) == 'root')
                                $repl_text = str_replace('root',$this->SYS->http_root, $url);
                            else
                                $repl_text .='/assets/'.$url;
                    }

                    //echo $repl_text ."\n\n";

                    $tpl_data = str_replace($matches[0][$key], $repl_text, $tpl_data);
                    //$__radSYS_evalfunc = $__radSYS_evalargs = '';
                }
            }



            /* identifying function calls */
            if (preg_match_all('/\<function\:(.*)\/\>/Usi', $tpl_data, $matches))
            {
                $funcs = $matches[1];
                foreach ($funcs as $key => $func)
                {
                    @list($func, $arguments) = explode(':', $func);

                    ob_start("radSYS_ob");
                    if ($arguments == NULL)
                        $__radSYS_evalfunc = $this->$func();
                    else
                    {
                        $arguments = 'Array( '.$arguments.' )';
                        $arguments = str_replace("\\'", "'", $arguments);
                        $ev_code = "\$__radSYS_evalargs = $arguments;";
                        @eval($ev_code);

                        $__radSYS_evalfunc = call_user_func_array(array($this, $func), $__radSYS_evalargs);
                    }
                    //$conetents = ob_get_contents();
                    ob_end_clean();

                    $repl_text = $__radSYS_evalfunc;
                    $tpl_data = str_replace($matches[0][$key], $repl_text, $tpl_data);
                    $__radSYS_evalfunc = $__radSYS_evalargs = '';
                }
            }

            /* identifying inner templates */
            if (preg_match_all('/\<render\:(.*)\/\>/Usi', $tpl_data, $matches))
            {
                $rep_templates = $matches[1];
                foreach ($rep_templates as $key => $template)
                {
                    $new_data = $this->eval_template($template, $data);
                    $tpl_data = str_replace($matches[0][$key], $new_data, $tpl_data);
                }
            }

            if (preg_match_all('/\<cache\:(.*)\>(.*)\<\/cache\>/Usi', $tpl_data, $matches))
            {
                foreach ($matches[2] as $ckey => $inner)
                {
                    $block = $matches[0][$ckey];
                    $cname = $matches[1][$ckey];
                    //$inner - laready there
                    $did = "{$template}_{$cname}";
                    $replace =
                    "<div id='{$did}_storage'>".
                        "<script>".
                            //"localStorage.removeItem('{$did}');".
                            "rad_ui_check_cache('{$did}');".
                        "</script>".
                    "</div>";

                    $tpl_data = str_replace($block, $replace, $tpl_data);

                }


            }

            if (preg_match_all ( '/\$(\w+)/ ', $tpl_data, $matches))
            {
                foreach ($matches[1] as $var)
                {
                    /*
                    if (preg_match('/\$\_LANG/Usi', $var))
                        continue;

                    global ${$var};
                    */
                }

                $tpl_data = addslashes($tpl_data);
                $tpl_data = str_replace("\\'", "'", $tpl_data);
                @eval ( "\$__radSYS_evaltext = \"$tpl_data\";" );
                $tpl_data = $__radSYS_evaltext;
            }


            return $tpl_data;
        }

        public function clear_renders()
        {
            $this->RENDERS = Array();

            return $this;
        }

        public function get_first_area_name()
        {
            $area_keys = array_keys($this->RENDERS);

            return empty($area_keys) ? 0 : $area_keys[0];
        }

        public function set_layout($layout)
        {
            $this->layout = $layout;

            return $this;
        }

        public function set_layout_data($data=Array())
        {
            $this->LAYOUT_DATA = $data;

            return $this;
        }

        public function set_view_start()
        {
            $this->render_str('<!-- radSYS no header set, use controller::set_view_start -->', $this->get_first_area_name());

            return $this;
        }

        public function set_view_end()
        {
            $this->render_str('<!-- radSYS no footer set, use controller::set_view_end -->', $this->get_first_area_name());

            return $this;
        }

        /**
         * render in buffer and return layout, replace all areas with rendered cotnent
         */
        public function render_layout()
        {
            $RENDERS_FINAL = $this->get_view_data();
        
            $layout_data = $this->eval_template('layouts/'.$this->layout, $this->LAYOUT_DATA);

            foreach ($RENDERS_FINAL as $area => $array)
                $RENDERS_FINAL[$area] = implode('', $array);

            /** identifying areas **/
            if (preg_match_all('/\<area\:(.*)\/\>/Usi', $layout_data, $matches))
            {
                $rep_templates = $matches[1];
                foreach ($rep_templates as $key => $area)
                {
                    $new_data = @$RENDERS_FINAL[trim($matches[1][$key])];
                    $layout_data = str_replace($matches[0][$key], $new_data, $layout_data);
                }
            }
            return $layout_data;

        }

        public function get_view_data()
        {
            $this->set_view_end();

            return $this->RENDERS;
        }

    }

    class ApiBaseController extends Basecontroller
    {

        public $output_format='json';
        public $return ='normal'; //hierarchical

        public function __construct()
        {
            parent::__construct();

            $this->output_format =
                    isset($_GET['output'])
                    ? htmlspecialchars($_GET['output'])
                    : 'json';

            $this->return  =
                    isset($_GET['return'])
                    ? strtolower(htmlspecialchars($_GET['return']))
                    : 'normal';

            if (isset($_GET['offset']))
            {
                $offset = intval($_GET['offset']);
                $offset = $offset < 0 ? 0 : $offset;

                $GLOBALS['RestGlobalOffset'] = $offset;
            }

            if (isset($_GET['limit']))
            {
                $limit = intval($_GET['limit']);
                $limit = $limit <= 0 ? RESTFUL_DEFAULT_LIMIT : $limit;

                $GLOBALS['RestGlobalLimit'] = $limit;
            }

            if (isset($_GET['fields']))
            {
                $GLOBALS['RestGlobalSelect'] = htmlspecialchars($_GET['fields']);
            }

            header('Content-Type: application/'.$this->output_format.';odata=verbose');
            header('X-Powered-By: '.ApiController::API_ABOUT);
            header('X-Download-Link: '.ApiController::API_LINK);
            header('X-Api-Version: '.ApiController::API_VERSION_MAJ);

        }


        public function main()
        {
            $this->rest_format_data(
                TRUE,
                Array(
                    'Version' =>
                        ApiController::API_VERSION_MAJ
                        .'.'.
                        ApiController::API_VERSION_MIN
                        ,

                    'API_VERSION_MAJ' => ApiController::API_VERSION_MAJ,
                    'API_VERSION_MIN' => ApiController::API_VERSION_MIN,
                    'API_ABOUT' => ApiController::API_ABOUT,
                    'API_LINK' => ApiController::API_LINK,
                )
            );
        }

        public function rest_to_xml($data, &$xml_data = NULL)
        {
            if ($xml_data === NULL)
                $xml_data = new SimpleXMLElement('<?xml version="1.0"?><rest></rest>');

            foreach( $data as $key => $value )
            {
                if( is_array($value) )
                {
                    if( is_numeric($key))
                    {
                        $key = 'item';//.$key;
                    }
                    $subnode = $xml_data->addChild($key);
                    $this->rest_to_xml($value, $subnode);
                }
                else
                {
                    if( intval($key) > 0 || $key=='0')
                        $key = 'item';

                    $xml_data->addChild("$key", htmlentities($value));
                }
            }

            $dom = new DOMDocument('1.0');
            $dom->preserveWhiteSpace = false;
            $dom->formatOutput = true;
            $dom->loadXML($xml_data->asXML());
            return $dom->saveXML();
        }

        public function rest_format_data($success=FALSE, $data=Array(), $message=NULL, $output_format='json')
        {
            $out_array = Array(
                //'content-type' => 'application/'.$this->output_format,
                //'accept' => 'application/'.$this->output_format,
            );
            if ($success === TRUE)
            {
                $out_array['data'] = $data;
                $G = $_GET;
                unset($G['_url']);

                $link =
                    $this->SYS->http_root.'/'.
                    $this->SYS->current_controller.'/'.
                    $this->SYS->current_method.'/'.
                    implode('/', $this->SYS->ARGS);

                ;
                if (substr($link,-1) != '/')
                   $link .= '/';

                $link .= (!empty($G) ? '?'.http_build_query($G) : '');

                $out_array['link'] = $link;
                $out_array['next'] = 'NOT_IMPLEMENTED';
                $out_array['previous'] = 'NOT_IMPLEMENTED';
            }
            else
                $out_array['error'] = Array(
                    'code' => $data,
                    'message' => $message,
                );

            $output =
                strtolower($this->output_format) == 'json'
                ? json_encode ( $out_array , JSON_PRETTY_PRINT)
                : (
                    strtolower($this->output_format) == 'xml'
                    ? $this->rest_to_xml ($out_array)
                    : var_export ($out_array)
                  )
            ;

            $this->render_str(
                $output,
                'REST'
            );
        }

        protected function entity_to_model($table)
        {
            if (preg_match('/\_/', $table))
            {
                $table = explode('_', $table);
                foreach ($table as $k => $v)
                    $table[$k] = table_to_model_name($v);

                return implode('_', $table);
            }
            $table = strtolower($table);
            if (substr($table, -3) == 'ies')
                $table = substr($table, 0, strlen($table)-3).'y';
            else
            if (substr($table, -2) == 'es')
                $table = substr($table, 0, strlen($table)-1);
            else
            if (substr($table, -1) == 's')
                $table = substr($table, 0, strlen($table)-1);

            return ucfirst($table);
        }
    }





