<style>
	pre
	{
		font-family:"Courier New";
		font-size:13px;
	}
    .identifier
    {
        color:black;
    }
    .keyword
    {
        color:blue;
    }
    .type
    {
        color:#008000;
        
    }
    .digit
    {
        color:#008080;
		font-weight:bold;
    }
</style>
<?php

    require_once '../includes/core/mvc.php';
    require_once '../includes/core/orm.php';
    require_once '../includes/settings.php';
    
    require_once '../includes/init.php';


    $tables = mysqli_query("SHOW TABLES FROM ".DB_NAME) or die(mysqli_error());
    
    $existing_tables = Array();
    
    while ($table = mysqli_fetch_assoc($tables))
        $existing_tables []= $table['Tables_in_'.DB_NAME];
        
    $add_columns='';
    
    $QS = Array();
    
    foreach ($existing_tables as $table)
    {
        $QS []= "
	ALTER TABLE `".$table."`
		DROP COLUMN `creation_date`,
		DROP COLUMN `modification_date`;";
        
        $QS []= "
	ALTER TABLE `".$table."`
		ADD  COLUMN `creation_date` TIMESTAMP DEFAULT CURRENT_TIMESTAMP(),
		ADD  COLUMN `modification_date` TIMESTAMP NULL;";
        
    }
   
    foreach ($QS as $Q)
    {
        if (!mysql_query($Q) && !preg_match('/DROP/', $Q))
            ;//print(mysql_error()."<br/>");
        //else
         //   print "Success : $Q<br/>";
    }
    //file_put_contents("add_field_to_all_tables.sql",$add_columns);
    
    echo "<pre><b>#--Executed SQL queries to save:<br/></b>".hl_SQL(implode("\n", $QS))."</pre>";








//--------------------------------------------------------------------------------------//

    function hl_SQL($sql) {
    
    	global $SQLKeywords;
        $sql = preg_replace('/"(?:\.|(\\\")|[^\""\n])*"/', '<span style="quoted">$0</span>', $sql);
        $sql = preg_replace("/'(?:\.|(\\\')|[^\''\n])*'/", '<span class="quoted">$0</span>', $sql);    
        $sql = preg_replace("/`(?:\.|[^\``\n])*`/", '<span class="identifier">$0</span>', $sql);    

        $keywords = Array(
        	
        	'ALTER',
        	'COLUMN',
        	'TABLE',
        	'ADD',
        	'DROP',
            'AND',
            'IS',
                        //'\&\&',
            'LOG',
                        'NOT',
            'NOW',
            'MIN',
                       //'\!',
                       // '\|\|',
                       // 'OR',
            'OCT',
            'TAN',
            'STD',
            'SHA',
            'ORD',
                        'XOR',


                        'SELECT',
                        'UPDATE',
                        'INSERT',
                        'DELETE',
            'USING',
            'LIMIT',
            'OFFSET',
                        'SET',


            'DATE',
                        'INTO',
                        'FROM',
                        'THEN',
                        'WHEN',
            'WHERE',
            'JOIN',
                        'ELSE',


            'ABS',
            'ACOS',
            'ADDDATE',
            'ADDTIME',
            'AES_DECRYPT',
            'AES_ENCRYPT',
            'ASCII',
            'ASIN',
            'ATAN2',
            'ATAN',
            'AVG',
            'BETWEEN',
            'BIN',
            'BINARY',
            'BIT_AND',
            'BIT_LENGTH',
            'BIT_OR',
            'BIT_XOR',
            'CASE',
            'CAST',
            'CEIL',
            'CEILING',
            'CHAR_LENGTH',
            'CHAR',
            'CHARACTER_LENGTH',
            'CHARSET',
            'COALESCE',
            'COERCIBILITY',
                        'COLLATION',
                        'COMPRESS',
                        'CONCAT_WS',
                        'CONCAT',
                        'CONNECTION_ID',
                        'CONV',
                        'CONVERT_TZ',
                        'Convert',
                        'COS',
                        'COT',
                        'COUNT',
                        'COUNT',
                        'COUNT(DISTINCT)',
                        'CRC32',
                        'CURDATE',
                        'CURRENT_DATE',
                        'CURRENT_TIME',
                        'CURRENT_TIMESTAMP',
                        'CURRENT_USER',
                        'CURTIME',
                        'DATABASE',
                        'DATE_ADD',
                        'DATE_FORMAT',
                        'DATE_SUB',
                        'DATEDIFF',
                        'DAY ',
                        'DAYNAME',
                        'DAYOFMONTH',
                        'DAYOFWEEK',
                        'DAYOFYEAR',
                        'DECODE',
                        'DEFAULT',
                        'DEGREES',
                        'DES_DECRYPT',
                        'DES_ENCRYPT',
                        'DIV',
                        'ELT',
                        'ENCODE',
                        'ENCRYPT',
                        'EXP()',
                        'EXPORT_SET',
                        'EXTRACT',
                        'FIELD',
                        'FIND_IN_SET',
                        'FLOOR',
                        'FORMAT',
                        'FOUND_ROWS',
                        'FROM_DAYS',
                        'FROM_UNIXTIME',
                        'GET_FORMAT',
                        'GET_LOCK',
                        'GREATEST',
                        'GROUP_CONCAT',
                        'HEX ',
                        'HOUR',
                        'IF',
                        'IFNULL',
                        'IN',
                        'INET_ATON',
                        'INET_NTOA',
                        'INSTR',
                        'IS_FREE_LOCK',
                        'IS NOT NULL',
                        'IS NOT',
                        'IS NULL',
                        'IS_USED_LOCK',
                        'ISNULL',
                        'LAST_DAY',
                        'LAST_INSERT_ID',
                        'LCASE',
                        'LEAST',
                        'LEFT',
                        'LENGTH',
                        'LIKE',
                        'LN',
                        'LOAD_FILE',
                        'LOCALTIME',
                        'LOCALTIMESTAMP',
                        'LOCATE',
                        'LOG10',
                        'LOG2',
                        'LOWER',
                        'LPAD',
                        'LTRIM',
                        'MAKE_SET',
                        'MAKEDATE',
                        'MAKETIME',
                        'MASTER_POS_WAIT',
                        'MATCH',
                        'MAX',
                        'MD\5',
                        'MICROSECOND',
                        'MID',
                        'MINUTE',
                        'MOD',
                        'MONTH',
                        'MONTHNAME',
                        'NOT BETWEEN',
                        'NOT IN',
                        'NOT LIKE',
                        'NOT REGEXP',
                        'NULLIF',
                        'OCTET_LENGTH',
                        'OLD_PASSWORD',
                        'ORD',
                        'PASSWORD',
                        'PERIOD_ADD',
                        'PERIOD_DIFF',
                        'PI',
                        'POSITION',
                        'POW',
                        'POWER',
                        'PROCEDURE ANALYSE',
                        'QUARTER',
                        'QUOTE',
                        'RADIANS',
                        'RAND',
                        'REGEXP',
                        'RELEASE_LOCK',
                        'REPEAT',
                        'REPLACE',
                        'REVERSE',
                        'RIGHT',
                        'RLIKE',
                        'ROUND',
                        'ROW_COUN',
                        'RPAD',
                        'RTRIM',
                        'SCHEMA',
                        'SEC_TO_TIME',
                        'SECOND',
                        'SESSION_USER',
                        'SHA\1',
                        'SIGN',
            'SLEEP',
            'SOUNDEX',
            'SOUNDS LIKE',
            'SPACE',
            'SQRT',
            'STDDEV_POP',
            'STDDEV_SAMP',
            'STDDEV',
            'STR_TO_DATE',
            'SUBDATE',
            'SUBSTR',
            'SUBSTRING_INDEX',
            'SUBSTRING',
            'SUBTIME',
            'SUM',
            'SYSDATE',
            'SYSTEM_USER',
            'TIME_FORMAT',
            'TIME_TO_SEC',
            'TIME',
            'TIMEDIFF',
                        'TIMESTAMPADD',
                        'TIMESTAMPDIFF',
                        'TO_DAYS',
                        'TRIM',
                        'TRUNCATE',
                        'UCASE',
                        'UNCOMPRESS',
                        'UNCOMPRESSED_LENGTH',
                        'UNHEX',
                        'UNIX_TIMESTAMP',
            'UPPER',
                        'USER',
                        'UTC_DATE',
                        'UTC_TIME',
                        'UTC_TIMESTAMP',
                        'UUID',
                        'VALUES',
                        'VAR_POP',
                        'VAR_SAMP',
                        'VARIANCE',
                        'VERSION',
                        'WEEK',
            'WEEKDAY',
            'WEEKOFYEAR',
            'YEAR',
            'YEARWEE',
            'KEY',
            'CREATE',
            'SELECT',
            'UPDATE',
            'INSERT',
            'DELETE',
            'LIMIT',
            'SET',
            'INTO',
            'FROM',
            'ALTER',
            'CHANGE',
            'TABLE',
            'DEFAULT',
            'NOT',
            'NULL',
            'CHARACTER',
            'COLLATE',
            'DROP',
            'INDEX',
            'CONSTRAINT',
            'PRIMARY',
            'FOREIGN',
            'UNIQUE',
            'ENGINE',
            'CHARSET',
            'IGNORE',
            'VALUES',
            'AUTO_INCREMENT',
            'WHERE',
            );

       //$keywords = unserialize('a:242:{i:0;s:3:"AND";i:1;s:2:"IS";i:2;s:4:"\&\&";i:3;s:3:"LOG";i:4;s:3:"NOT";i:5;s:3:"NOW";i:6;s:3:"MIN";i:7;s:2:"\!";i:8;s:4:"\|\|";i:9;s:2:"OR";i:10;s:3:"OCT";i:11;s:3:"TAN";i:12;s:3:"STD";i:13;s:3:"SHA";i:14;s:3:"ORD";i:15;s:3:"XOR";i:16;s:6:"SELECT";i:17;s:6:"UPDATE";i:18;s:6:"INSERT";i:19;s:6:"DELETE";i:20;s:5:"USING";i:21;s:5:"LIMIT";i:22;s:6:"OFFSET";i:23;s:3:"SET";i:24;s:4:"DATE";i:25;s:4:"INTO";i:26;s:4:"FROM";i:27;s:4:"THEN";i:28;s:4:"WHEN";i:29;s:5:"WHERE";i:30;s:4:"JOIN";i:31;s:4:"ELSE";i:32;s:3:"ABS";i:33;s:4:"ACOS";i:34;s:7:"ADDDATE";i:35;s:7:"ADDTIME";i:36;s:11:"AES_DECRYPT";i:37;s:11:"AES_ENCRYPT";i:38;s:5:"ASCII";i:39;s:4:"ASIN";i:40;s:5:"ATAN2";i:41;s:4:"ATAN";i:42;s:3:"AVG";i:43;s:7:"BETWEEN";i:44;s:3:"BIN";i:45;s:6:"BINARY";i:46;s:7:"BIT_AND";i:47;s:10:"BIT_LENGTH";i:48;s:6:"BIT_OR";i:49;s:7:"BIT_XOR";i:50;s:4:"CASE";i:51;s:4:"CAST";i:52;s:4:"CEIL";i:53;s:7:"CEILING";i:54;s:11:"CHAR_LENGTH";i:55;s:4:"CHAR";i:56;s:16:"CHARACTER_LENGTH";i:57;s:7:"CHARSET";i:58;s:8:"COALESCE";i:59;s:12:"COERCIBILITY";i:60;s:9:"COLLATION";i:61;s:8:"COMPRESS";i:62;s:9:"CONCAT_WS";i:63;s:6:"CONCAT";i:64;s:13:"CONNECTION_ID";i:65;s:4:"CONV";i:66;s:10:"CONVERT_TZ";i:67;s:7:"Convert";i:68;s:3:"COS";i:69;s:3:"COT";i:70;s:5:"COUNT";i:71;s:5:"COUNT";i:72;s:15:"COUNT(DISTINCT)";i:73;s:5:"CRC32";i:74;s:7:"CURDATE";i:75;s:12:"CURRENT_DATE";i:76;s:12:"CURRENT_TIME";i:77;s:17:"CURRENT_TIMESTAMP";i:78;s:12:"CURRENT_USER";i:79;s:7:"CURTIME";i:80;s:8:"DATABASE";i:81;s:8:"DATE_ADD";i:82;s:11:"DATE_FORMAT";i:83;s:8:"DATE_SUB";i:84;s:8:"DATEDIFF";i:85;s:4:"DAY ";i:86;s:7:"DAYNAME";i:87;s:10:"DAYOFMONTH";i:88;s:9:"DAYOFWEEK";i:89;s:9:"DAYOFYEAR";i:90;s:6:"DECODE";i:91;s:7:"DEFAULT";i:92;s:7:"DEGREES";i:93;s:11:"DES_DECRYPT";i:94;s:11:"DES_ENCRYPT";i:95;s:3:"DIV";i:96;s:3:"ELT";i:97;s:6:"ENCODE";i:98;s:7:"ENCRYPT";i:99;s:5:"EXP()";i:100;s:10:"EXPORT_SET";i:101;s:7:"EXTRACT";i:102;s:5:"FIELD";i:103;s:11:"FIND_IN_SET";i:104;s:5:"FLOOR";i:105;s:6:"FORMAT";i:106;s:10:"FOUND_ROWS";i:107;s:9:"FROM_DAYS";i:108;s:13:"FROM_UNIXTIME";i:109;s:10:"GET_FORMAT";i:110;s:8:"GET_LOCK";i:111;s:8:"GREATEST";i:112;s:12:"GROUP_CONCAT";i:113;s:4:"HEX ";i:114;s:4:"HOUR";i:115;s:2:"IF";i:116;s:6:"IFNULL";i:117;s:2:"IN";i:118;s:9:"INET_ATON";i:119;s:9:"INET_NTOA";i:120;s:5:"INSTR";i:121;s:12:"IS_FREE_LOCK";i:122;s:11:"IS NOT NULL";i:123;s:6:"IS NOT";i:124;s:7:"IS NULL";i:125;s:12:"IS_USED_LOCK";i:126;s:6:"ISNULL";i:127;s:8:"LAST_DAY";i:128;s:14:"LAST_INSERT_ID";i:129;s:5:"LCASE";i:130;s:5:"LEAST";i:131;s:4:"LEFT";i:132;s:6:"LENGTH";i:133;s:4:"LIKE";i:134;s:2:"LN";i:135;s:9:"LOAD_FILE";i:136;s:9:"LOCALTIME";i:137;s:14:"LOCALTIMESTAMP";i:138;s:6:"LOCATE";i:139;s:5:"LOG10";i:140;s:4:"LOG2";i:141;s:5:"LOWER";i:142;s:4:"LPAD";i:143;s:5:"LTRIM";i:144;s:8:"MAKE_SET";i:145;s:8:"MAKEDATE";i:146;s:8:"MAKETIME";i:147;s:15:"MASTER_POS_WAIT";i:148;s:5:"MATCH";i:149;s:3:"MAX";i:150;s:4:"MD\5";i:151;s:11:"MICROSECOND";i:152;s:3:"MID";i:153;s:6:"MINUTE";i:154;s:3:"MOD";i:155;s:5:"MONTH";i:156;s:9:"MONTHNAME";i:157;s:11:"NOT BETWEEN";i:158;s:6:"NOT IN";i:159;s:8:"NOT LIKE";i:160;s:10:"NOT REGEXP";i:161;s:6:"NULLIF";i:162;s:12:"OCTET_LENGTH";i:163;s:12:"OLD_PASSWORD";i:164;s:3:"ORD";i:165;s:8:"PASSWORD";i:166;s:10:"PERIOD_ADD";i:167;s:11:"PERIOD_DIFF";i:168;s:2:"PI";i:169;s:8:"POSITION";i:170;s:3:"POW";i:171;s:5:"POWER";i:172;s:17:"PROCEDURE ANALYSE";i:173;s:7:"QUARTER";i:174;s:5:"QUOTE";i:175;s:7:"RADIANS";i:176;s:4:"RAND";i:177;s:6:"REGEXP";i:178;s:12:"RELEASE_LOCK";i:179;s:6:"REPEAT";i:180;s:7:"REPLACE";i:181;s:7:"REVERSE";i:182;s:5:"RIGHT";i:183;s:5:"RLIKE";i:184;s:5:"ROUND";i:185;s:8:"ROW_COUN";i:186;s:4:"RPAD";i:187;s:5:"RTRIM";i:188;s:6:"SCHEMA";i:189;s:11:"SEC_TO_TIME";i:190;s:6:"SECOND";i:191;s:12:"SESSION_USER";i:192;s:4:"SHA1";i:193;s:4:"SIGN";i:194;s:5:"SLEEP";i:195;s:7:"SOUNDEX";i:196;s:11:"SOUNDS LIKE";i:197;s:5:"SPACE";i:198;s:4:"SQRT";i:199;s:10:"STDDEV_POP";i:200;s:11:"STDDEV_SAMP";i:201;s:6:"STDDEV";i:202;s:11:"STR_TO_DATE";i:203;s:7:"SUBDATE";i:204;s:6:"SUBSTR";i:205;s:15:"SUBSTRING_INDEX";i:206;s:9:"SUBSTRING";i:207;s:7:"SUBTIME";i:208;s:3:"SUM";i:209;s:7:"SYSDATE";i:210;s:11:"SYSTEM_USER";i:211;s:11:"TIME_FORMAT";i:212;s:11:"TIME_TO_SEC";i:213;s:4:"TIME";i:214;s:8:"TIMEDIFF";i:215;s:9:"TIMESTAMP";i:216;s:12:"TIMESTAMPADD";i:217;s:13:"TIMESTAMPDIFF";i:218;s:7:"TO_DAYS";i:219;s:4:"TRIM";i:220;s:8:"TRUNCATE";i:221;s:5:"UCASE";i:222;s:10:"UNCOMPRESS";i:223;s:19:"UNCOMPRESSED_LENGTH";i:224;s:5:"UNHEX";i:225;s:14:"UNIX_TIMESTAMP";i:226;s:5:"UPPER";i:227;s:4:"USER";i:228;s:8:"UTC_DATE";i:229;s:8:"UTC_TIME";i:230;s:13:"UTC_TIMESTAMP";i:231;s:4:"UUID";i:232;s:6:"VALUES";i:233;s:7:"VAR_POP";i:234;s:8:"VAR_SAMP";i:235;s:8:"VARIANCE";i:236;s:7:"VERSION";i:237;s:4:"WEEK";i:238;s:7:"WEEKDAY";i:239;s:10:"WEEKOFYEAR";i:240;s:4:"YEAR";i:241;s:7:"YEARWEE";}');
            
        foreach ($keywords as $word)  
        {
			$word = addslashes($word);
        	$sql = preg_replace('/\b('.$word.')\b/i', '<span class="keyword">\1</span>', $sql);
		}
        
        //foreach ($keywords as $word)  
        //	$sql = preg_replace("/\w*?".preg_quote($word)."\w*/i", '<span class="keyword">$0</span>', $sql);

        $types = Array(
        	'INT', 'TINYINT', 'MEDIUMINT', 'BIGINT',
        	'FLOAT', 'DOUBLE','DECIMAL',
        	'CHAR', 
        	'BLOB', 'TINYBLOB', 'MEDIUMBLOB', 'LONGBLOB',
        	'TEXT', 'TINYTEXT','MEDIUMTEXT', 'LONGTEXT',
        	'VARCHAR', 
        	'VARBINARY',
        	'UNSIGNED', 'TIMESTAMP',
        	
        );

        foreach ($types as $word)  
        {
			$word = addslashes($word);
        	$sql = preg_replace('/\b('.$word.')\b/i', '<span class="type">\1</span>', $sql);
		}


        //$punctuations = Array('(',')');
        //foreach ($punctuations as $word) $sql = str_replace($word, '<span class="punctuation">'.$word.'</span>', $sql);

        $sql = preg_replace("/\b(\d+)\b/i", '<span class="digit">\1</span>', $sql);

        return str_replace("\n", "<br />", $sql);
        
    }        







