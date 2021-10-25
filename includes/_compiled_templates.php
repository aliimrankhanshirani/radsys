<?php
    /*
     * radSYS - [knit] Template compiler v21
     * NOTE : do not modify this file! As it gets overwritten automatically
     *
     * TE file generated at : October 25, 2021 06:55:28AM
     */

    class radSYS_TE
    {
        
        public static function __callStatic($method, $args)
        {
            global $_APP, $_DATA, $_LANG, $_KEY, $_VAL, $_SYS;

            $THIS  = $args[0];
            $_DATA = $args[1];
            $_LANG = &$THIS->lang;
            $_SYS  = &$THIS->SYS;


            ob_start("radSYS_ob");

            switch($method) {

    
                /* footer - template */
                case 'footer':
                {
                    print "<div class=\"navbar navbar-default navbar-fixed-bottom\" style=\"height:50px;\">
    <div class=\"container\">
		<p class=\"navbar-text pull-left\">&copy; 2015 - No rights reserved - the dinner is served !
			<a href=\"#\" target=\"_blank\" >My Link</a>
		</p>
		<!-- a href=\"http://aborder.org/files/radSYS/?MA\" class=\"navbar-btn btn-primary btn pull-right\">
			<span class=\"glyphicon glyphicon-object-align-bottom\"></span>  Download
		</a -->
    </div>
</div>
";
                    break;
                }
        


                /* header - template */
                case 'header':
                {
                    print "<img src=\"{$_SYS->http_root}/assets/images/radsys-header.png\"/>
<style>
	.navbar-brand img {
		margin-left: 5px;
		margin-top: 0px;
		margin-right: 5px;
	}
</style>

<nav class=\"navbar navbar-inverse\">
	<div class=\"container-fluid\">
		<div class=\"navbar-header\">
			<a class=\"navbar-brand\" href=\"{$_SYS->http_root}/../\">
				<img class=\"pull-left\" src=\"{$_SYS->http_root}/assets/images/logo.png\" width=\"24\" />
				<b>&copy; RADSYS 4</b>
			</a>
		</div>
		<div >
			<ul class=\"nav navbar-nav\">
				<li class=\"active\"><a href=\"{$_SYS->http_root}/home\">Home</a></li>
				<li><a href=\"{$_SYS->http_root}/auth/\">Log In</a></li>
			</ul>
		</div>
	</div>
</nav>
";
                    break;
                }
        


                /* layouts/NONE - layout */
                case 'layouts_NONE':
                {
                    print "
";
                    break;
                }
        


                /* layouts/default - layout */
                case 'layouts_default':
                {
                    print "<!DOCTYPE html>
<html>
	<head>
		<title>{$_APP['website_name']}</title>
		<meta charset=\"utf-8\" />
		<meta name=\"viewport\" content=\"width=device-width, initial-scale=1\" />
		<link rel=\"icon\" type=\"image/ico\" href=\"{$_SYS->http_root}/assets/icons/radsys-icon.ico\" />
		
		<link rel=\"stylesheet\" href=\"{$_SYS->http_root}/assets/css/bootstrap.min.css\" />

		<script src=\"{$_SYS->http_root}/assets/js/jquery.min.js\"></script>
		<script src=\"{$_SYS->http_root}/assets/js/bootstrap.min.js\"></script>
		<script src=\"{$_SYS->http_root}/assets/js/rad-ui-cache.js\"></script>
		<script src=\"{$_SYS->http_root}/assets/js/rad-routines.js\"></script>
	</head>
	<body>
		<script language=\"javascript\">var root_path = '{$_SYS->http_root}';</script>
		".radSYS_TE::header ($THIS, $_DATA)."
			
		<div class=\"container\">
			<area:CONTENT />
		</div>
		
		".radSYS_TE::footer ($THIS, $_DATA)."
	</body>
</html>
";
                    break;
                }
        


                /* layouts/empty - layout */
                case 'layouts_empty':
                {
                    print "<area:CONTENT />
";
                    break;
                }
        


                /* main - template */
                case 'main':
                {
                    print "<h1>MAIN TEMPLATE</h1>

";
                if (is_array(@$_DATA) || is_object(@$_DATA))
                {
                    foreach (@$_DATA as $_KEY => $_VAL)
                    {
                        print radSYS_TE::main_s_user($THIS, $_DATA);
                    }
                } print "


";
                    break;
                }
        


                /* main_s_user - template */
                case 'main_s_user':
                {
                    print "
    <b>{$_VAL->name}</b><br/>
    <b>{$_VAL->email}</b><br/>
    <b>{$_VAL->id}</b><br/>
    <b>{$_VAL->role}</b><br/>
    <hr />
";
                    break;
                }
        
                default:
                    print '{{invalid or missing template : '.$method."}}\n\n";
            }

            $conetents = ob_get_contents();
            ob_end_clean();

            return $conetents;
        }
    }


    if (!function_exists('radSYS_ob'))
    {
        function radSYS_ob($buffer)
        {
            return $buffer;
        }
    }

    /** Test of invalid template **/
    //uncomment following line to test TE alone
    //print radSYS_TE::invalid_template(NULL, NULL);

    