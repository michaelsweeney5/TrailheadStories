<?php
/*
 * Simple Calendar using PHP from https://davidwalsh.name/php-calendar
 */

/**
 * @param $month: to show
 * @param $year: to show
 * @param $db: Databased connection
 * @return string html table of month
 */

function drawCalendar($month, $year, $db)
{
    $calendar = '<table cellpadding="0" cellspacing="0" class="table calendar">';

    $heading = array('Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday');
    $calendar .= '<tr class="calendar-row"><td class="calendar-day-head">' . implode('</td><td class="calendar-day-head">', $heading) . '</td></tr>';

    $runningDay = date('w', mktime(0, 0, 0, $month, 1, $year));
    $dayInMonth = date('t', mktime(0, 0, 0, $month, 1, $year));
    $daysInThisWeek = 1;
    $dayCounter = 0;
    $datesArray = array();

    $calendar .= '<tr class="calendar-row">';

    for ($x = 0; $x < $runningDay; $x++) {
        $calendar .= '<td class="calendar-day-np"> </td>';
        $daysInThisWeek++;
    }

    for ($listDay = 1; $listDay <= $dayInMonth; $listDay++) {
        $calendar .= '<td class="calendar-day">';
        $calendar .= '<div class="day-number">' . $listDay . '</div>';

        /** pulls current reservations if there is any and adds the html to display them in that day */
        $currentDate = $year.'-'.$month.'-'.$listDay;
        $db->where('invoiceStartDate', $currentDate);
        $db->join('Customers c', 'i.customerID = c.customerID');
        $r = $db->get('Invoices i');

        if($r != null) {
            foreach ($r as $i) {
                $calendar .= '<p><button type="button" class="btn btn-primary btn-xs" data-toggle="modal" data-target="#'.$i['invoiceID'].'">' . $i['customerFirstName'] . ' ' . $i['customerLastName'] . '</button></p>';
                $calendar .= '<div class="modal" id="'.$i['invoiceID'].'">
                                  <div class="modal-dialog">
                                    <div class="modal-content">
                                      <div class="modal-header">
                                        <button type="button" class="close" data-dismiss="modal" aria-hidden="true" >×</button>
                                        <h4 class="modal-title">'.$i['invoiceID'].' - ' . $i['customerFirstName'] . ' ' . $i['customerLastName'] . '</h4>
                                      </div>
                                      <div class="modal-body">
                                        <h4>'.$i['invoiceStartDate'].' - '.$i['invoiceEndDate'].'</h4>
                                        <p>'.$i['invoiceComments'].'</p>
                                        <a href="customers.php?cID='.$i['customerID'].'">' . $i['customerFirstName'] . ' ' . $i['customerLastName'] . '</a>
                                      </div>
                                      <div class="modal-footer">
                                        <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                                        <a class="btn btn-primary" href="invoices.php?invoiceID='.$i['invoiceID'].'" role="button">View Reservation</a>
                                      </div>
                                    </div>
                                  </div>
                                </div>
                    ';
            }
        } else {
            $calendar .= str_repeat('<p> </p>', 2);
        }

        $calendar .= '</td>';
        if ($runningDay == 6) {
            $calendar .= '</tr>';
            if (($dayCounter + 1) != $dayInMonth) {
                $calendar .= '<tr class="calendar-row">';
            }
            $runningDay = -1;
            $daysInThisWeek = 0;
        }
        $daysInThisWeek++;
        $runningDay++;
        $dayCounter++;
    }

    if ($daysInThisWeek < 8) {
        for ($x = 1; $x <= (8 - $daysInThisWeek); $x++) {
            $calendar .= '<td class="calendar-day-np"> </td>';
        }
    }

    $calendar .= '</tr>';

    $calendar .= '</table>';

    return $calendar;
}