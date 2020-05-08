<?php
/**
 * Created by PhpStorm.
 * User: smile
 * Date: 2020/5/7
 * Time: 6:31 PM
 */

namespace Godruoyi\OCR\Support;

class Sign
{
    /**
     * @param string $skey
     * @param string $date
     * @param string $service
     * @param string $str2sign
     * @return string
     */
    public static function signTC3(string $skey, string $date, string $service, string $str2sign)
    {
        $dateKey = hash_hmac("SHA256", $date, "TC3" . $skey, true);
        $serviceKey = hash_hmac("SHA256", $service, $dateKey, true);
        $reqKey = hash_hmac("SHA256", "tc3_request", $serviceKey, true);

        return hash_hmac("SHA256", $str2sign, $reqKey);
    }
}