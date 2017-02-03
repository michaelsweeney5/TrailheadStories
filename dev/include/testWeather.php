<?php
/**
 * Created by PhpStorm.
 * User: Luke
 * Date: 2/16/2016
 * Time: 11:13 AM
 */

/*require_once("common.php");

$options  = array('http' => array('user_agent' => 'Mozilla/5.0 (Windows NT 6.1) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/41.0.2228.0 Safari/537.36'));
$context  = stream_context_create($options);
$url = "http://forecast.weather.gov/MapClick.php?lat=39.0734&lon=-108.5498&unit=0&lg=english&FcstType=json";
$content = file_get_contents($url, false, $context);
$json = json_decode($content, true);

$temp = $json['currentobservation']['Temp'];
$condition =  $json['currentobservation']['Weather'];
$icon = $json['currentobservation']['Weatherimage'];


//print_r($json);

echo "TEMP: " . $temp . "<br />";
echo "CONDITION: " . $condition . "<br />";
echo "ICON: " . $icon . "<br />";

echo "DB START <br />";
$q = "SELECT * FROM Weather WHERE TIMESTAMP < SUBDATE( TIMESTAMP( NOW( ) ) , INTERVAL 1 HOUR ) ";
//$q = "SELECT * FROM Weather";
$r = $db->rawQuery($q, Array(10));
print_r($r);
echo "DB DONE <br />";*/

//$url = "http://forecast.weather.gov/MapClick.php?lat=39.0733&lon=-108.5494&unit=0&lg=english&FcstType=json";
$url = "http://w1.weather.gov/xml/current_obs/KGJT.xml";
//$ch = curl_init();
//curl_setopt($ch, CURLOPT_URL, $url);
//curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
//
//
//$headers = array();
//$headers[] = 'X-Apple-Tz: 0';
//$headers[] = 'X-Apple-Store-Front: 143444,12';
//$headers[] = 'Accept: text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8';
//$headers[] = 'Accept-Encoding: gzip, deflate';
//$headers[] = 'Accept-Language: en-US,en;q=0.5';
//$headers[] = 'Cache-Control: no-cache';
//$headers[] = 'Content-Type: application/x-www-form-urlencoded; charset=utf-8';
//$headers[] = 'Host: dev.cmuop.com';
//$headers[] = 'Referer: http://dev.cmuop.com/index.php'; //Your referrer address
//$headers[] = 'User-Agent: Mozilla/5.0 (X11; Ubuntu; Linux i686; rv:28.0) Gecko/20100101 Firefox/28.0';
//$headers[] = 'X-MicrosoftAjax: Delta=true';
//
//curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
//
//$content = curl_exec($ch);
//curl_close($ch);

$command = "wget -qO- {$url}";
exec($command, $content);

//$xml = simplexml_load_string($content);
//$json = json_decode($content, true);
print_r($content);
//echo $content;
//echo $xml;

$temp = $content[23];
$temp = str_replace("	<temp_f>",'', $temp);
$temp = str_replace("</temp_f>",'', $temp);

$condition = $content[21];
$condition = str_replace("	<weather>", '', $condition);
$condition = str_replace("</weather>", '', $condition);

echo "<hr>";
echo $temp;
echo "<br>";
echo $condition;