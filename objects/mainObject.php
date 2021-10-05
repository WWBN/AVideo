<?php
class Main {
     /**
      * receive a YYYY-MM-DD
      * @param type $brDate
      * @return String dd/mm/yyyy
      */
    static public function dateMySQLToBrString($mySqlDate) {
        $parts = explode('-', $mySqlDate);
        //switch month and day
        if (empty($parts[2])) {
            return $mySqlDate;
        } else {
            if (strlen($parts[2]) > 2) { // if there is hour
                $parts2 = explode(" ", $parts[2]);
                $day = $parts2[0];
                $hour = " " . $parts2[1];
            } else {
                $day = $parts[2];
                $hour = "";
            }
            return "{$day}/{$parts[1]}/{$parts[0]}{$hour}";
        }
    }

    /**
     * receive a DD/MM/YYYY
     * @param type $mySqlDate
     * @return String YYYY-mm-dd
     */
    static public function dateBrStringToMySQL($date) {
        $parts = explode("/", $date);
        //switch month and day
        if (empty($parts[2])) {
            return $date;
        } else {
            if (strlen($parts[2]) > 4) { // if there is hour
                $parts2 = explode(" ", $parts[2]);
                $year = $parts2[0];
                $hour = " " . $parts2[1];
            } else {
                $year = $parts[2];
                $hour = "";
            }
            return "{$year}-{$parts[1]}-{$parts[0]}{$hour}";
        }
    }
}
