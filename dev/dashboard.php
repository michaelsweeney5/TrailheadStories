<?php
/**
 * Logged in users start at the dashboard with common links to tasks they can perform.
 * Also shows a calendar of current reservations for a given month and can be paged through.
 */
session_start();
if(empty($_SESSION['user']))
{
    header("Location: index.php");
    die("Redirecting to Log In");
}
require_once('include/common.php');

require_once('include/drawCalendar.php');

$month = date('m');
$year = date('Y');

if(!empty($_GET['month']) && !empty($_GET['year'])) {
    $month = $_GET['month'];
    $year =  $_GET['year'];
}

$data['calendar'] = drawCalendar($month, $year, $db);
$data['calendarMonth'] = date('F', mktime(0, 0, 0, $month, 10));
$data['calendarYear'] = $year;

if ($month == 1) {
    $data['calendarPreYear'] = $year-1;
    $data['calendarPreMonth'] = 12;
} else {
    $data['calendarPreYear'] = $year;
    $data['calendarPreMonth'] = $month-1;
}
if ($month == 12) {
    $data['calendarNextYear'] = $year+1;
    $data['calendarNextMonth'] = 1;
} else {
    $data['calendarNextYear'] = $year;
    $data['calendarNextMonth'] = $month+1;
}

$tpl = $m->loadTemplate('dashboard');
echo $tpl->render($data);