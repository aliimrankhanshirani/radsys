<?php

	if(isset($_GET['local_ip']))
		die(getHostByName(getHostName()));
	else
	if(isset($_GET['opcache_info']))
	{
		if (!function_exists('opcache_get_status'))
			die('Zend Opcache extension not found!');

		$info1 = opcache_get_status(true);
		$info2 = opcache_get_configuration();
		$total = $info1['memory_usage']['used_memory'] + $info1['memory_usage']['free_memory'];	
		$used = ($info1['memory_usage']['used_memory']/$total)*100.;
		
		printf("<h1>Zend Opcache vaersion %s</h1>", $info2['version']['version']);
		
		printf("<h3>Usage: %.2lf%% of total %d MB</h3>", ($used), $total/1048576);
		$stats = $info1['opcache_statistics'];
		printf("<h4>Start: %s</h4>", date('F d, Y h:i:sA', intval($stats['start_time']+3600*3)));
		printf("<h4>Restarts: %s</h4>", date('F d, Y h:i:sA', intval($stats['last_restart_time']+3600*3)));
		print '<hr>';
		
		print '<h3>Cached scripts - out of total used '.sprintf("<u>%d</u>",round($info1['memory_usage']['used_memory']/1048576)).'MB memory:</h3>';
		$files = [];
		foreach ($info1['scripts'] as $scr => $arr)
		{
			$files[basename($arr['full_path'])] = $arr['memory_consumption'];
			//print "<li>{$arr['full_path']} - consumed {$arr['memory_consumption']} bytes</li>"; 
			
		}
		//print '</ul>';	
		print create_bar($files);
		die;	
	}
	


	function create_bar($arr, $width=1024)
	{
		asort($arr);
		$arr = array_reverse($arr, true);
		
		$colors = [
		  '#FF6633', '#FFB399', '#FF33FF', '#FFFF99', '#00B3E6', 
		  '#E6B333', '#3366E6', '#999966', '#99FF99', '#B34D4D',
		  '#80B300', '#809900', '#E6B3B3', '#6680B3', '#66991A', 
		  '#FF99E6', '#CCFF1A', '#FF1A66', '#E6331A', '#33FFCC',
		  '#66994D', '#B366CC', '#4D8000', '#B33300', '#CC80CC', 
		  '#66664D', '#991AFF', '#E666FF', '#4DB3FF', '#1AB399',
		  '#E666B3', '#33991A', '#CC9999', '#B3B31A', '#00E680', 
		  '#4D8066', '#809980', '#E6FF80', '#1AFF33', '#999933',
		  '#FF3380', '#CCCC00', '#66E64D', '#4D80CC', '#9900B3', 
		  '#E64D66', '#4DB380', '#FF4D4D', '#99E6E6', '#6666FF'];
		$total = array_sum($arr);
		$ret = '<div>';//'<table border="1" width="500px"><tr>';
		//shuffle($colors);
		$x = 0;
		foreach ($arr as $label => $value)
		{
			

			$p = sprintf("%.1lf", ($value/$total)*100.);
			//$ret .= '<td align="center" width="'.$p.'%" bgcolor="'.$colors[$x++].'">'.trim($label).'-'.$p.'%</td>';
			//$ret .= '<td align="center" width="'.$p.'%" bgcolor="'.$colors[$x++].'">'.trim($label).'</td>';
			$pp = ($p/100) * $width;
			//$ret .= "<div style='width:{$pp}px;min-width:{$pp}px;max-width:{$pp}px;background-color:".$colors[$x++]."'>$label"."[$p%]</div>";
			$ret .= '<div class="progress">
  <div class="progress-bar" role="progressbar" style="width:'.$p.'%;background-color:'.$colors[$x++].'" aria-valuenow="25" aria-valuemin="0" aria-valuemax="100"></div>
  &nbsp;&nbsp;'.$label.' ['.$p.'%]
</div>';
			
		}
		$ret .= '</div>';
		
		return $ret;
	}
	

