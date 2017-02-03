<?php
/**
 * This set of functions will manage the weather cache and load the weather to be added to nav bar.
 */

function outputWeather($db)
{
    /**
     *  This dose the time check and if old gets new weather else just displays the weather
     */
    if (checkWeatherTime($db) == false) {
        getWeather($db);
        return $db->getOne('Weather');
    } else {
        return $db->getOne('Weather');
    }
}

function getWeather($db)
{
    /**
     * This will get the weather from two sources, openweathermaps.com for the icon code, and weather.gov for the condition and temperature.
     */
    $q = "TRUNCATE TABLE  `Weather`";
    $db->rawQuery($q, Array(10));
    $url = "http://api.openweathermap.org/data/2.5/weather?id=5423573&appid=f824f0b7e326cdcf55c281c0a995b53e";
    $content = file_get_contents($url);
    $json = json_decode($content, true);

    $icon = $json['weather'][0]['id'];

    $GovURL = "http://w1.weather.gov/xml/current_obs/KGJT.xml";

    $command = "wget -qO- {$GovURL}";
    exec($command, $govWX);

    $temp = $govWX[23];
    $temp = str_replace("	<temp_f>", '', $temp);
    $temp = str_replace("</temp_f>", '', $temp);

    $condition = $govWX[21];
    $condition = str_replace("	<weather>", '', $condition);
    $condition = str_replace("</weather>", '', $condition);

    $weather = Array("CurrentTemp" => $temp,
        "CurrentCondition" => $condition,
        "CurrentImage" => $icon
    );
    $db->insert('Weather', $weather);
}

function checkWeatherTime($db)
{
    /**
     * This dose a check for the current time to see if the weather cached in the database is older than one hour.
     */
    $q = "SELECT * FROM Weather WHERE TIMESTAMP < SUBDATE( TIMESTAMP( NOW( ) ) , INTERVAL 1 HOUR )";
    if (empty($db->rawQueryValue($q))) {
        return true;
    } else {
        return false;
    }
}
