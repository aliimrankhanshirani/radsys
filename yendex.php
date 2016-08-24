<?php

$query = "The holidays have come and gone and everyone ";

$query = "%22".urlencode($query)."%22";

$url = 'https://yandex.com/search/xml?l10n=en&user=mwaqas2024&key=03.377075725:aad57ad610f8ff81b43cad1be7e34342&query='.$query.'&l10n=en&noreask=1&filter=strict';
$body = file_get_contents($url);
$xml=simplexml_load_string($body) or die("Error: Cannot create object");
$json = json_encode($xml);


$ch = curl_init ();
$useragent = "Opera/9.80 (J2ME/MIDP; Opera Mini/4.2.14912/870; U; id) Presto/2.4.15";

curl_setopt ($ch, CURLOPT_URL, "https://www.yandex.com/search/?msid=1471339499.47949.22879.21472&text=%22Installing%20a%20home%20solar%20power%20system%20is%20expensive%20but%22&lr=114008");
curl_setopt ($ch, CURLOPT_USERAGENT, $useragent); // set user agent
curl_setopt ($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_FOLLOWLOCATION, TRUE);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
//curl_setopt($ch, CURLOPT_REFERER, 'http://www.google.com/');

if ($proxy !== NULL)
    //curl_setopt($ch, CURLOPT_PROXY, "http://104.250.98.41:80");
    curl_setopt($ch, CURLOPT_PROXY, $sock5."://".$proxy);

$result = curl_exec ($ch);
curl_close($ch);

print($result);

preg_match_all('/<ul class="b-results"><div style="clear:both"><a\s+href="([^"]*)"/i', $result, $matches);

<!DOCTYPE html PUBLIC "-//WAPFORUM//DTD XHTML Mobile 1.0//EN" "http://www.wapforum.org/DTD/xhtml-mobile10.dtd">
<html id="nojs" xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-type" content="text/html; charset=UTF-8" />
<title>
&quot;Installing a home solar power system is expensive but&quot; -
Yandex:
1 answer found
</title>
<link rel="shortcut icon" href="//yastatic.net/lego/_/rBTjd6UOPk5913OSn5ZQVYMTQWQ.ico" />
<link type="application/opensearchdescription+xml" title="Yandex" href="http://yandex.com/opensearch.xml" rel="search">
<meta name="MobileOptimized" content="176" />
<meta name="PalmComputingPlatform" content="true" />
<meta name="format-detection" content="telephone=no" />

</head>
<body  onload="setTimeout(function() {scrollTo(0, 1)}, 1);">
<table class="b-head" cellspacing="0" cellpadding="0">
<tr>
<td class="b-head__logo">
<a href="http://www.yandex.com" class="b-head__logo-link">
<div class="b-head__logo-image b-head__logo-image_lang_en"></div>
</a>
</td>
<td class="b-head__user">
<a href='http://pda-passport.yandex.com/passport?mode=auth&amp;retpath=http%3A%2F%2Fm.yandex.ru%2Fsearch%2Fsmart%2F%3Ftext%3D%2522Installing%2520a%2520home%2520solar%2520power%2520system%2520is%2520expensive%2520but%2522%26lr%3D114008'>log in</a>
</td>
</tr>
</table>
<table class="b-subhead">
<tr>
<td class="b-subhead__region"><a href='http://m.tune.yandex.com/region/?retpath=http%3A%2F%2Fwww.yandex.com%2Fsearch%2Fsmart%2F%3Fmsid%3D1471339499.47949.22879.21472%26text%3D%2522Installing%2520a%2520home%2520solar%2520power%2520system%2520is%2520expensive%2520but%2522'>Region</a>: Sialkot</td>
<td class="b-subhead__quit"></td>
</tr>
</table>
<form class="h-search" name="web" action="/search">
<input name="lr" type="hidden" value="114008"/>
<div class="b-search b-search_theme_arrow b-search_suggest_yes">
<table class="b-search__layout" cellpadding="0" cellspacing="0">
<tr>
<td class="b-search__layout-l">
<div class="b-form-input b-form-input_suggest_yes">
<div class="b-form-input__box">
<input id="search" class="b-form-input__input" type="search" name="query" value="&quot;Installing a home solar power system is expensive but&quot;" alt="запрос" autocomplete="off" autocorrect="off"/>
</div>
</div>
</td>
<td class="b-search__layout-r">
<div class="b-button">
<input type="submit" class="b-search__button" value="Search" />
</div>
</td>
</tr>
</table>
</div>
<div class="b-tabs b-tabs_mini">
<a id="images" class="tab" href="http://m.images.yandex.com" onclick="tab(this)">Images</a>
<a id="news" class="tab" href="http://pda.news.yandex.com" onclick="tab(this)">News</a>
</div>
<script type="text/javascript" charset="utf-8">
var links = {'images': 'http://m.images.yandex.com/search?rpt=image&text=','news': 'http://pda.news.yandex.com/yandsearch?rpt=nnews2&grhow=clutop&text='},
searchInput = document.getElementById("search");
function tab(link){ var text = searchInput.value, url = links[ link.id ]; if(text != ""){ link.href = url + text; } };
</script>
<input name="search_top" type="hidden" value="1" />
</form>
<div class="b-wizard b-wizard_txt">
Webpages containing the exact quote have been found.
<a href="http://www.yandex.com/search/smart/?text=&amp;lr=114008">Search without quotes</a>
</div>
<ul class="b-results">
<li >
<a href="http://ezinearticles.com/expert/Lyndsay_Whittaker/182739" >Lyndsay Whittaker - EzineArticles.com Expert Author</a><br/>
<span class="b-results__text">
<b>Installing</b> <b>a</b> <b>home</b> <b>solar</b> <b>power</b> <b>system</b> <b>is</b> <b>expensive</b> <b>but</b> you can get <i class="b-wbr"></i>some help with the costs. Many states and local governments are <i class="b-wbr"></i>offering very generous incentives for people to go green and install <i class="b-wbr"></i>their own home energy systems.
</span>
<div class="www">ezinearticles.com/expert…</div>
</li>
</ul>
<div class="b-searchengines">
Try your search on:<br>
<a href="http://www.google.com/m/search?q=%22Installing%20a%20home%20solar%20power%20system%20is%20expensive%20but%22" >Google</a>  
<a href="http://m.bing.com/search?q=%22Installing%20a%20home%20solar%20power%20system%20is%20expensive%20but%22" >Bing</a>  
<a href="http://m.yahoo.com/w/search?p=%22Installing%20a%20home%20solar%20power%20system%20is%20expensive%20but%22">Yahoo!</a>
</div>
<div class="b-wizard b-wizard_txt">
</div>
<div class="b-foot">
<p>© <a href="http://www.yandex.com">Yandex</a></p>
<p class="b-foot__info"><a href="http://tune.yandex.com/api/my/v1.0/my.xml?param=1&amp;block=44&amp;sk=y5eae17ec616c1a44f1b9e1670d0f68f0&amp;retpath=http%3A%2F%2Fyandex.com%2Fyandsearch%3Ftext%3D%22Installing%20a%20home%20solar%20power%20system%20is%20expensive%20but%22">Full version</a></p>
<img width="1" height="1" src="http://yandex.com/clck/redir/dtype=stred/pid=93/cid=2304/path=show/*http://img.yandex.net/i/x.gif" alt="" />


