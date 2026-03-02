<?php

namespace App\Helpers;

class ThaiBahtTextHelper
{
    public static function convert($number)
    {
        $txtnum1 = ["ศูนย์","หนึ่ง","สอง","สาม","สี่","ห้า","หก","เจ็ด","แปด","เก้า"];
        $txtnum2 = ["","สิบ","ร้อย","พัน","หมื่น","แสน","ล้าน"];
        $number = str_replace([",", " "], "", $number);
        $number = explode(".", $number);
        $integerPart = $number[0];
        $decimalPart = isset($number[1]) ? substr(str_pad($number[1], 2, '0'), 0, 2) : '00';

        $convert = '';
        $len = strlen($integerPart);
        for ($i = 0; $i < $len; $i++) {
            $n = (int)$integerPart[$i];
            if ($n != 0) {
                if ($i == $len - 1 && $n == 1) {
                    $convert .= "เอ็ด";
                } elseif ($i == $len - 2 && $n == 2) {
                    $convert .= "ยี่";
                } elseif ($i == $len - 2 && $n == 1) {
                    $convert .= "";
                } else {
                    $convert .= $txtnum1[$n];
                }
                $convert .= $txtnum2[$len - $i - 1];
            }
        }

        $convert .= "บาท";
        if ($decimalPart == "00") {
            $convert .= "ถ้วน";
        } else {
            $len = strlen($decimalPart);
            for ($i = 0; $i < $len; $i++) {
                $n = (int)$decimalPart[$i];
                if ($n != 0) {
                    if ($i == $len - 1 && $n == 1) {
                        $convert .= "เอ็ด";
                    } elseif ($i == $len - 2 && $n == 2) {
                        $convert .= "ยี่";
                    } elseif ($i == $len - 2 && $n == 1) {
                        $convert .= "";
                    } else {
                        $convert .= $txtnum1[$n];
                    }
                    $convert .= $txtnum2[$len - $i - 1];
                }
            }
            $convert .= "สตางค์";
        }

        return $convert;
    }
}
