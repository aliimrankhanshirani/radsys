<?php

    //WINDOWS ONLY

    require_once '../includes/settings.php';
    
    
    const EXTERNAL_EDITOR = '"C:/Program Files (x86)/Geany/bin/geany.exe" +{LINE} "{FILE}"';
    
    
    if (isset($_GET['error_file']) and isset($_GET['error_line']) and DEV_MODE === TRUE)
    {
        $rad_line_num = intval($_GET['error_line']);
        if (isset($_GET['exec']))
        {
            $command = str_replace(Array('{LINE}', '{FILE}'), Array($rad_line_num , $_GET['error_file']), EXTERNAL_EDITOR);
            file_put_contents('error_tracker.bat', $command);
            exec("c:\\windows\\system32\\cmd.exe /c error_tracker.bat");         
            
            ?>
                <script>
                    location.href='error_tracker.php?error_file=<?=urlencode($_GET['error_file'])?>&error_line=<?=$_GET['error_line']?>#error';
                    
                </script>
            <?php
            die;
        }
    
    
        $rad_file_data = trim(file_get_contents($_GET['error_file']));
        
        $rad_file_data = str_replace("\r", '', $rad_file_data);
        
        
        if ($rad_line_num > 0)
        {
            $rad_file_data = explode("\n", $rad_file_data);
            $rad_file_data[$rad_line_num-1] = 'RADRADSTART'.$rad_file_data[$rad_line_num-1].'RADRADEND';
        }
        $rad_file_data = implode("\n", $rad_file_data);
        $rad_file_data = highlight_string($rad_file_data, TRUE);
        $onclick = 'javascript:location.href=\'error_tracker.php?exec&error_file='.urlencode($_GET['error_file']).'&error_line='.$_GET['error_line'].'\';';
        $button = '<button style="background-color:white;color:green;border:2px solid gray;height:50px" onclick="'.$onclick.'">EDIT File (jump to line#'.$rad_line_num.')</button>'
            .'&nbsp;<button style="background-color:white;border:2px solid gray;height:50px;color:blue;font-weight:bold;" onclick="window.close();">Back / Close</button>';
        $rad_file_data = str_replace("RADRADSTART", '<a name="error"></a><img src="error_tracker.gif"/>'.$button.'<br/><h2 style="background-color:#cccccc;margin-top:0px;margin-bottom:0px;color:black;">Line# '.$rad_line_num, $rad_file_data);
        
        $rad_file_data = str_replace("RADRADEND", '</h2>', $rad_file_data);
        
        
        
        
        print '<h3>File : '.$_GET['error_file'].'</h3>'.$rad_file_data;

    }

