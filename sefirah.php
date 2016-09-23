<?php

/*
    Converts a given number to its Hebrew numeral representation
    To be used on numbers < 1000

    convertNumberToHebrew is based on http://www.mediawiki.org/wiki/Extension:Hebrew_Numbers
*/

class sefirah
{
    public static function get($timestamp = null)
    {
        $number = self::getNumber($timestamp);
        $number = self::convertNumberToHebrew($number);

        return $number ? $number . ' בעומר' : null;
    }

    public static function getNumber($timestamp = null)
    {
        if (! $timestamp) {
            $timestamp = time();
        }

        // Jewish year
        $year = self::jewishYear(date('Y', $timestamp));
        // Pesach (since 7 is Adar2, regardless of whether this is a leap year)
        $month = 8;
        // 2nd day of pesach
        $day = 16;

        $pesachTimestamp = jdtounix(jewishtojd($month, $day, $year));

        $daysSince2ndDayPesach = self::daysBetween($pesachTimestamp, $timestamp);

        return $daysSince2ndDayPesach < 0 || $daysSince2ndDayPesach > 49 ?
                null :
                $daysSince2ndDayPesach;
    }

    // This is inaccurate, since the Jewish new year is usually
    // before the gregorian calendar, but it serves our purposes here.
    private static function jewishYear($gregorianYear)
    {
        return $gregorianYear + 3760;
    }

    public static function convertNumberToHebrew($num)
    {
        mb_language('uni');
        mb_internal_encoding('UTF-8');

        $output = "";

        // Do thousands
        $thousands = self::calcOnes(intval($num / 1000) % 10);

        if ($thousands != null) {
            $output .= $thousands . self::unichr(hexdec("5F3"));
        }

        // Do hundreds
        $hundreds = self::calcHundreds(intval($num / 100) % 10);
        $output .= $hundreds;

        // fix exceptions for 15 & 16
        if ($num % 100 == 15) {
            $ones = self::calcOnes(9);
            $output .= $ones;
            $ones = self::calcOnes(6);
            $output .= $ones;
        } elseif ($num % 100 == 16) {
            $ones = self::calcOnes(9);
            $output .= $ones;

            $ones = self::calcOnes(7);
            $output .= $ones;
        } else {
            // Do tens
            $tens = self::calcTens(intval($num / 10) % 10);
            $output .= $tens;

            // Do ones
            $ones = self::calcOnes(intval($num) % 10);
            $output .= $ones;
        }

        // Add quote or apostrophe
        if (mb_strlen($output) > 1) {
            $output = mb_substr($output, 0, - 1) . self::unichr(hexdec("5F4")) . mb_substr($output, - 1, mb_strlen($output) + 1);
        } elseif (mb_strlen($output) == 1) {
            $output .= self::unichr(hexdec("5F3"));
        }

        return $output;
    }

    private static function calcHundreds($digit)
    {
        if ($digit != null) {
            $output = "";

            while ($digit >= 4) {
                $output .= self::unichr(hexdec("5EA"));
                $digit -= 4;
            }

            // add number to numerical value for unicode character before "kuf"
            if ($digit > 0) {
                $output .= self::unichr($digit + 1510);
            }

            return $output;
        }
    }

    private static function calcTens($digit)
    {
        if ($digit != null) {
            // store the unicode value in decimal of hebrew representation for tens
            $tensUnicodes = ['5D9', '5DB', '5DC', '5DE', '5E0', '5E1', '5E2', '5E4', '5E6'];
            return self::unichr(hexdec($tensUnicodes[$digit - 1]));
        }
    }

    private static function calcOnes($digit)
    {
        if ($digit != null) {
            // add number to numerical value for unicode character before "aleph"
            return self::unichr($digit + 1487);
        }
    }

    // returns a one character string containing the unicode character specified by unicode
    // from: http://www.php.net/manual/en/function.chr.php#88611
    private static function unichr($unicode)
    {
        return mb_convert_encoding('&#' . $unicode . ';', 'UTF-8', 'HTML-ENTITIES');
    }

    // returns the diffrence in days between two timestamps
    // from: http://stackoverflow.com/questions/1363920
    private static function daysBetween($start, $end)
    {
        $diff = $end - $start;

        return ceil($diff / 86400);
    }
}
