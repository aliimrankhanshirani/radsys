<?php

    /* CRUD level constants */
    const DELETE_ALL = TRUE;

    $GLOBALS['RestGlobalOffset'] = NULL;
    $GLOBALS['RestGlobalLimit']  = NULL;
    $GLOBALS['RestGlobalSelect'] = NULL;
    $GLOBALS['RestLastEntity']   = NULL;


    /**
     * ORM specific constants
     */

    const NOT_NULLABLE  = 'NOT_NULLABLE';
    const NOT_AVAILABLE = 'NOT_AVAILABLE';
    const NOT_VALID     = 'NOT_VALID'; /* Type check */
    const NOT_IN_LIMIT  = 'NOT_VALID'; /* Length check */

    /* utility constants */
    define ('NOW',      date('Y-m-d h:i:sA', time()));
    define ('TIME_NOW', date('h:i:sA', time()));
    define ('DATE_NOW', date('Y-m-d', time()));

    /**
     * type specific constants against errors
     */

    abstract class DBEngine
    {
        protected $conn  = NULL;
        protected $CACHE = NULL;

        public function start($host,$user,$pass,$db='',$port='') {}
        public function stop() {}
        public function select_db($db) {}
        public function query($q) {}
        public function add(&$obj, $record) {}
        public function insert_id() {}
    }


    class Model implements Iterator
    {
        private $stack  = array();
        private $index  = 0;
        public  $count  = 0;
        public  $table_name = 'Unknown';
        public  $table_fields = Array();
        public  $system_update_mode = false;
        public  $DB, $SYS;
        public  $selected_keys, $affected;
        public  $query_elements = Array('order' => NULL, 'limit' =>NULL, 'group' => NULL, 'select' => '*');

        public function eval_data($data=Array())
        {
            $ret = Array(
                'object' => get_class($this),
                'ok'     => Array(),
                'errors' => Array(),
            );

            if (!is_array($data) OR empty($data))
                return FALSE;

            if (isset($data[$this->table_key]))
                unset($data[$this->table_key]);

            $common = array_intersect(array_keys($data), $this->table_fields);

            foreach ($common as $data_field)
            {
                if ($this->field_types[$data_field]['nullable'] == FALSE && $data[$data_field] =='')
                {
                    $ret['errors'][$data_field]= NOT_NULLABLE;
                }
                else
                    $ret['ok'][$data_field]= $data[$data_field];
            }

            $temp_my_fields = $this->table_fields;

            if ( ($index = array_search($this->table_key, $temp_my_fields)) !== FALSE )
                unset($temp_my_fields[$index]);

            $missing = array_diff($temp_my_fields, $common);

            foreach ($missing as $data_field)
            {
                if (
                    $this->field_types[$data_field]['nullable'] == FALSE
                    and
                    trim($this->field_types[$data_field]['default']) == ''
                )
                {
                    $ret['errors'][$data_field]= NOT_AVAILABLE;
                }
            }

            if (!empty($ret['ok']))
            {
                foreach ($ret['ok'] as $field => $value)
                {
                    $type = $this->field_types[$field]['type'];
                    $length = intval($this->field_types[$field]['length']);
                    $_has_error = FALSE;
                    if (preg_match('/int|byte/Usi', $type))
                    {
                        $ret['ok'][$field] = $value = intval($value);
                        if ($value <= 0)
                        {
                            $ret['errors'][$field] = NOT_VALID;
                            unset($ret['ok'][$field]);
                        }
                    }
                    else
                    if (preg_match('/char|text|binary|blob/Usi', $type))
                    {
                        if (strlen($value) > $length)
                        {
                            $ret['errors'][$field] = NOT_IN_LIMIT;
                            unset($ret['ok'][$field]);
                        }
                    }
                    else
                    if ('enum' == $type)
                    {
                        $ret['ok'][$field] = $value = trim($value);

                        if (!in_array($value, $this->field_types[$field]['extra']))
                        {
                            $ret['errors'][$field] = NOT_VALID;
                            unset($ret['ok'][$field]);
                        }
                    }
                    else
                    if ('set' == $type)
                    {
                        $err = FALSE;
                        if (!is_array($value))
                            $ret['ok'][$field] = $value = explode(',', str_replace(Array("'",'"'),'', $value));

                        $diff = array_diff($value, $this->field_types[$field]['extra']);

                        if (!empty($diff))
                        {
                            $ret['errors'][$field] = NOT_VALID;
                            unset($ret['ok'][$field]);
                        }
                        else
                            $ret['ok'][$field] = implode(',', $ret['ok'][$field]);
                    }
                    else
                    if (preg_match('/date|time/Usi', $type) or $type == 'timestamp')
                    {
                        $ret['ok'][$field] = $value = trim($value);

                        if (trim($this->field_types[$field]['default']) != '')
                        {
                            /* check timestamp only*/
                            if ($type == 'timestamp' and $value == 'CURRENT_TIMESTAMP')
                                continue;
                        }
                        $totime = strtotime($value);

                        if ($totime == 0)
                        {
                            $ret['errors'][$field] = NOT_VALID;
                            unset($ret['ok'][$field]);
                        }
                        else
                        {
                            $format = 'Y-m-d';
                            switch($type)
                            {
                                case 'time': $format = 'h:i:sA'; break;
                                case 'date': $format = 'Y-m-d'; break;
                                case 'datetime':
                                case 'timestamp':
                                    $format = 'Y-m-d h:i:sA'; break;
                            }
                            $ret['ok'][$field] = date($format, $totime);
                        }
                    }
                }
            }

            return $ret;
        }


        public function empty_query_elements($limit)
        {
            $this->query_elements = Array('order' => NULL, 'limit' =>NULL, 'group' => NULL, 'select' => '*');
            return $this;
        }

        public function limit($limit /* [10,100] */)
        {
            if (is_array($limit))
                $limit = implode(',', $limit);

            $this->query_elements['limit'] = $limit;
            return $this;
        }

        public function select($select)
        {
            if (is_array($select))
                $select = implode(',', $select);

            $this->query_elements['select'] = $select;
            return $this;
        }

        public function order($order /* ['name ASC', 'id DESC'] */)
        {
            if (is_array($order))
                $order = implode(',', $order);

            $this->query_elements['order'] = $order;
            return $this;
        }

        public function group($group)
        {
            if (is_array($group))
                $group = implode(',', $group);

            $this->query_elements['group'] = $group;
            return $this;
        }

        public function find($query_or_array=NULL)
        {
            $where = NULL;

            if ($query_or_array !== NULL)
            {
                if (is_int($query_or_array))
                    $where = '`'.$this->table_key.'`=\''.$query_or_array.'\'';
                else
                if (is_string($query_or_array))
                    $where = $query_or_array;
                else
                if (is_array($query_or_array))
                {
                    $where = Array();
                    foreach ($query_or_array as $key => $val)
                    {
                        if (!in_array($key, $this->table_fields))
                            throw new Exception('Model:'.get_class($this).'::find() record fields mismatch!');

                        $where []= "$key='$val'";
                    }
                    $where = implode(' AND ', $where);
                }
            }

            $this->DB->query($where, $this);

            return $this;
        }


        public function __construct()
        {
            $this->DB  = &$GLOBALS['Database'];
            //$this->SYS = &$GLOBALS['System'];

            /** RESTFul interface specific globals **/
            if ($GLOBALS['RestGlobalLimit'] !== NULL && $GLOBALS['RestGlobalOffset'] !== NULL)
                $this->limit(Array($GLOBALS['RestGlobalOffset'], $GLOBALS['RestGlobalLimit']));
            else
            if ($GLOBALS['RestGlobalOffset'] !== NULL)
                $this->limit(Array($GLOBALS['RestGlobalOffset'], '18446744073709551615'));
            else
            if ($GLOBALS['RestGlobalLimit'] !== NULL)
                $this->limit($GLOBALS['RestGlobalLimit']);

            if ($GLOBALS['RestGlobalSelect'] !== NULL)
                $this->select($GLOBALS['RestGlobalSelect']);
            /** RESTFul interface specific globals **/

            if (func_num_args()==0)
                return $this;

            $query='';
            $args = func_get_args();
            $selecting = FALSE;

            if (count ($args) == 1)
            {
                if (is_numeric($args[0])) # search by KEY
                {
                    $where = '`'.$this->table_key.'`=\''.$args[0].'\'';
                    $this->DB->query($where, $this);
                    $selecting = TRUE;
                }
                else
                if (is_string($args[0])) # is where clause, search
                {
                    $where = $args[0];

                    $this->DB->query($where, $this);
                    $selecting = TRUE;
                }
                else
                if (is_array($args[0])) # new object
                {
                    $newarr = $args[0];
                    if (isset($newarr[$this->table_key]))
                        unset($newarr[$this->table_key]);

                    $rec_keys = array_keys($newarr);

                    foreach ($rec_keys as $key)
                    {
                        if (!in_array($key, $this->table_fields))
                            throw new Exception('Model:'.get_class($this).'::__construct() record fields mismatch!');
                    }

                    $this->DB->add($this, $newarr);
                }
                unset($this->stack[$this->index]['__property_modified']);
            }
            //array_unshift($args, $this);
            //die;

            //$res = __construct_query($args);
            //$this->DB->query($res, $this);

            //if($this->count)
            //    $this->adopt(0);

            return $this;
        }

        public function clear()
        {
            $this->index = 0;
            $this->stack = Array();
            $this->selected_keys = Array();
            $this->count = 0;

            return $this;
        }

        public function rewind()
        {
            $this->index=0;
        }

        public function current()
        {
            $this->adopt($this->index);
            return $this->count ? $this : NULL;//->stack[$this->index];
        }

        public function next()
        {
            //if($this->index == count($this->stack)-1) return NULL;
            $this->index++;
        }

        public function valid()
        {
            $ret = (bool)isset($this->stack[$this->index]);
            if (!$ret)
                $this->index = count($this->stack)-1;

            return  $ret;
        }

        public function key()
        {
            return $this->index;
        }

        private function adopt($idx)
        {
            foreach ($this->stack[$this->index] as $k => $v)
            {
                //echo"-";
                if($k != $this->table_key)
                    $this->$k = $v;
            }
            return $this;
        }

        function push($rec)
        {
            $this->index = 0;
            $this->stack []= $rec;
            $this->count = count($this->stack);
            $this->selected_keys []= intval(@$rec[$this->table_key]);

            return $this;
        }

        function __get($key)
        {

            if (isset($this->$key) and $this->$key != NULL)
                return $this->$key;

            if (isset($this->stack[$this->index][$key]) )
                return $this->stack[$this->index][$key];

            return NULL;//get_class($this)."::$key not found!";
        }

        function __set($key, $value)
        {
            if (in_array($key, $this->table_fields))
            {
                /* check if developer is setting primary key , DONT ALLOW */
                if ($key == $this->table_key && !$this->system_update_mode)
                    throw new Exception('--Model:'.get_class($this)."::__set() - Cannot set primary key `$key` manually ");

                if ($this->count == 0)
                {
                    /* create new array in stack */
                    $rec = array_flip($this->table_fields);

                    foreach ($rec as $k=> $v)
                    {
                        $rec[$k] = $this->field_types[$k]['default'];
                    }

                    if ($this->table_key != '')
                        unset($rec[$this->table_key]);

                    $rec ['__property_new'] = true;
                    unset($rec ['__property_modified']);
                    $rec [$key] = $value;
                    $this->stack []= $rec;
                    $this->count++;
                }
                else
                {
                    /* modify current record */

                    //print get_class($this)." : UPDATE RECORD --- $key = $value ($this->count)\n";
                    $this->stack[$this->index][$key] = $value;

                    //if (!isset($this->stack[$this->index]['__property_new']))
                    //unset($this->stack[$this->index]['__property_new']);
                    if (!isset($this->stack[$this->index]['__property_new']))
                        $this->stack[$this->index]['__property_modified'] = true;
                }
            }
            //else
            //    $this->{$key} = $value;

            return $this;
        }

        function save($where_clause='')
        {
            /* insert records from stack which has __property_new set */
            $upd = 0;
            foreach ($this->stack as $arr)
            {
               if (isset($arr['__property_new']))
               {
                   unset($arr['__property_new']);
                   unset($arr['__property_modified']);
                   $upd = $this->DB->add($this, $arr);
               }
               else
               /* or update */
               if (isset($arr['__property_modified']))
               {
                   unset($arr['__property_modified']);
                   $upd += $this->DB->update($this, $arr, $where_clause);
               }
            }

            $this->affected = $upd;
            return $this;
        }

        function add($arr)
        {
            $this->affected = $this->DB->add($this, $arr);

            return $this;
        }

        function delete_all($where_clause='')
        {
            $this->affected = $this->delete(DELETE_ALL);
            return $this;
        }

        function delete($where_clause='')
        {
            if ($this->count == 0)
            {
                $this->affected = 0;
                return $this;
            }
            if (($ret = $this->DB->delete($this, $where_clause===DELETE_ALL ? '' : $where_clause, $where_clause===DELETE_ALL ? TRUE : FALSE)) !=0)
            {
                if ($where_clause === DELETE_ALL)
                    $this->clear();
                else
                {
                    unset($this->stack[$this->index]);
                    sort($this->stack);

                    $this->index--;
                    $this->count = count($this->stack);
                }
                $this->affected = $ret;
            }
            $this->affected = 0;

            return $this;
        }

        public function __call($fname, $fargs)
        {

            if (substr($fname,0,4) == 'set_')
            {
                $property = str_replace('set_', '', $fname);

                if (in_array($property, $this->table_fields) !== FALSE)
                    $this->{$property} = $fargs[0];

                return $this;
            }

            throw new Exception("Model:".get_class($this)."::{$fname}() does not exist");
        }

        public function get_data()
        {
            return $this->stack;
        }
    }






    class mysql extends DBEngine
    {
        public $total_queries = 0;
        public $last_query = '';

        function __construct()
        {
            $GLOBALS['Database'] = &$this;
            if (func_num_args())
            {
                $args = func_get_args();

                $this->start( @$args[0], @$args[1], @$args[2], @$args[3],  @$args[4] );
                $this->query("SET NAMES 'utf8';");
            }
        }

        function select_db($db)
        {
            if (!@$this->conn->select_db($this->conn, $db))
                throw new Exception("Cannot select database : `$db` - ".$this->conn->error);

            return $this;
        }
        
        function get_connection()
        {
			return $this->conn;
		}

        function start($host, $user, $pass, $db='', $port='')
        {
			//$eng = new mysqli(@$args[0], @$args[1], @$args[2], @$args[3]);
			
			$mysqli = new mysqli($host, $user, $pass, $db);
		
            if ($mysqli -> connect_errno)
                throw new Exception("Cannot start MySQL Server : ".$this->conn->error);

            //if ($db != '')
                //$this->select_db($db);
                
            $this->conn = $mysqli;

            return $this;
        }

        function stop()
        {
            @$this->conn->close();

            return $this;
        }


        function query($query = NULL, & $intoObj = NULL, $check_error=TRUE)
        {  //$intoObj must be instanceof Model to receive data in

            if ($intoObj != NULL)
            {
                if ($intoObj->query_elements['select'] != '*')
                {
                    $fields = explode(',', preg_replace('/\s+/','', $intoObj->query_elements['select']));
                    $diff = array_intersect($fields,$intoObj->table_fields);
                    if (empty($diff))
                        $intoObj->query_elements['select'] = '*';
                    else
                        $intoObj->query_elements['select'] = implode(',', $diff);
                }

                $query = "SELECT ".$intoObj->query_elements['select']." FROM $intoObj->table_name".($query !== NULL ? " WHERE $query " : '');
                $query .= $intoObj->query_elements['group'] !== NULL ? ' GROUP BY '.$intoObj->query_elements['group'] : '';
                $query .= $intoObj->query_elements['order'] !== NULL ? ' ORDER BY '.$intoObj->query_elements['order'] : '';

                if ($GLOBALS['RestLastEntity'] !== NULL)
                {
                    if ($intoObj->table_name == $GLOBALS['RestLastEntity'])
                        $query .= $intoObj->query_elements['limit'] !== NULL ? ' LIMIT '.   $intoObj->query_elements['limit'] : '';
                }
                else
                    $query .= $intoObj->query_elements['limit'] !== NULL ? ' LIMIT '.   $intoObj->query_elements['limit'] : '';

                $intoObj->count = 0;
                $intoObj->stack = Array();
            }

            $this->last_query = $query;
            $rows =Array();

            $this->total_queries++;

            //if ( !($r = $this->conn->query($this->conn, $query)) && $check_error===TRUE)
            
            
            if (($r = $this->conn->query($query)) ===FALSE)
            {
				//print_r("Error executing SQL query : ".$this->conn->error($this->conn));
				//die;
                throw new Exception("Error executing SQL query : ".$this->conn->error($this->conn));
			}

            $total = @$r->num_rows;


            if ($total > 0)
            {
                while ($rec = $r->fetch_assoc())
                {
                    $rows []= $rec;
                }
            }
            else
                return NULL;


            if ($intoObj !== NULL && !empty($rows))
            {
                $intoObj->clear();
                $total=0;

                foreach ($rows as $rec)
                {
                    $intoObj->push($rec);
                    $intoObj->selected_keys[] = @$rec[$intoObj->table_key];
                }
                $intoObj->selected_keys = array_unique($intoObj->selected_keys);
            }

            return $rows;
        }


        function add(&$obj, $record)
        {
            foreach ($record as $k=>$v)
                if ($v == 'CURRENT_TIMESTAMP')
                    continue;
                else
                    $record [$k] = "'$v'";

            $values = implode(',', $record);
  

            $query = "INSERT INTO $obj->table_name (`".implode("`,`", array_keys($record))."`) VALUES($values);";

            @$this->conn->query($query) or die("Cannot add: ".$this->conn->error."<br />$query");

            $aff_rows = $this->conn->insert_id;

            if ($aff_rows && $obj->table_key !='')
            {
                $old = $obj->system_update_mode;
                $obj->system_update_mode  = true;
                $obj->{"$obj->table_key"} = $this->conn->insert_id;
                $obj->system_update_mode  = $old;

                foreach ($record as $k=>$v)
                {
                    $obj->$k = $v;
                }
                //$obj->clear();
                //$obj->adopt(0);
            }

            $this->last_query = $query;

            return $aff_rows;
        }

        function update($obj, $record, $where='')
        {
            if ($obj->table_key == '' && $where == '')
                throw new Exception('Update on tables without primary key requires where clause, FROM: '.get_class($obj).'::save( $where );', E_USER_ERROR);

            if ($where == '')
                $where = $obj->table_key .'='. $record[$obj->table_key];

            unset($record[$obj->table_key]);
            $values = Array();

            foreach ($record as $k => $v)
                $values []= "`$k`=".'"'.$v.'"';//htmlspecialchars($v, ENT_QUOTES, 'UTF-8').'"';

            $query = "UPDATE `$obj->table_name` SET ".implode(",", $values). " WHERE $where;";

            //print $query ."\n\n\n";

            @$this->conn->query($query) or die("Cannot update: ".$this->conn->error);

            $this->last_query = $query;

            return $this->conn->affected_rows;
        }


        function delete($obj, $where='', $all=FALSE)
        {
            if($obj->count == 0)
                return 0;

            if ($obj->table_key == '' && $where == '')
                throw new Exception('Deletion in tables without primary key requires where clause, FROM: '.get_class($obj).'::delete( $where );');

            if ($where == '')
                $where = $obj->table_key .' IN ('. ($all===TRUE ? implode(',',$obj->selected_keys) : $obj->{"$obj->table_key"}) .')';

            $query = "DELETE FROM `$obj->table_name` WHERE $where;";

            //print "------\n$query\n----------\n" . $obj->{"$obj->table_key"}."\n--------------------------------\n";

            @$this->conn->query($query) or die("Cannot delete: ".$this->conn->error);
            $this->last_query = $query;
            return $this->conn->affected_rows;
        }
    }


    /*$db = new mysql('localhost', 'root', '', 'ecommerce');
    $db->query("TRUNCATE logs");

    $L1 = new Log;

    $L1
        ->set_data('Hi there!')
        ->save() //add
        ->set_data('Bye there!')
        ->save() //update
        ->delete()
        ->set_data('Bye there 2!')
        ->save() //add
        ->set_data('Hi there 2!')
        ->save() //update
    ;


    print_r($L1);
*/

    /*$L1
        ->set_data('New data '.microtime())
        //->save()
    ;*/

    //print_r($L1);

    /*foreach ($L1 as $key => $obj)
    {
        print "$obj->id = $obj->data\n\n";

        $obj->delete();
    }
    */


    //$L1->delete_all();
    //print_r($L1);

    //die;

    //print_r($L1);



    //$l2 = new Log("id>0"); print_r($l2);


    //print "\n\n\n\n -----\n";
    //$l3 = new Log( Array('data' => 'Log data '.microtime()) );
    //print_r($l3);

    //print "\n\n\n\n";
    //$l3->data = 'Changed '.time();
    //print_r($l3);

    //print "--------------------------\n\n";
    //$l3->save();

    //print_r($l3);

    /*
        class Permission extends Model
        {
            public $table_name   = 'permissions';
            public $table_key    = 'id';
            public $table_fields = Array('id','group_id');
            public $field_types  = Array ( 'id' => Array ( 'type' => 'int', 'extra' => Array ( ), 'length' => 0, 'nullable' => false, 'key_type' => 'PRI', ), 'group_id' => Array ( 'type' => 'int', 'extra' => Array ( ), 'length' => 0, 'nullable' => false, 'key_type' => 'MUL', ), );

            public function __get($property)
            {
                switch ($property)
                {
                    default: return parent::__get($property);
                }
            }
        }

    $db = new mysql('localhost', 'root', '', 'laser');

    $p = new Permission;

    $p
        ->select('*')
        ->order('id')
        ->group('id')
        ->limit('0,100')
        ->find('id>0');

    print_r($p);

    echo '<hr>'. $p->DB->last_query;
    */
