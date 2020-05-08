<?php

namespace Godruoyi\OCR\Service\TencentNew;
use Godruoyi\OCR\Support\Sign;

/**
 * @see https://cloud.tencent.com/document/api/866
 * @author leohu <alpha1130@gmail.com>
 *
 */
class Authorization
{

    /**
     * Tencent AI appid
     *
     * @var string
     */
    protected $appId = '';

    /**
     * Tencent AI appkey
     *
     * @var string
     */
    protected $appKey = '';

    /**
     * Authorization constructor.
     *
     * @param $appId
     * @param $appKey
     */
    public function __construct($appId, $appKey)
    {
        $this->appId = $appId;
        $this->appKey = $appKey;
    }

    /**
     * Generate Signature
     *
     * @author reallyli <zlisreallyli@outlook.com>
     * @since 2020/5/7
     * @param array $params
     * @param int $timestamp
     * @param string $method
     * @param string $host
     * @param string $contentType
     * @return mixed
     */
    public function generateSignature(array $params, string $method, int $timestamp, string $host, string $contentType)
    {
        $service = explode(".", $host)[0];
        $sid = $this->appId;
        $skey = $this->appKey;
        $algo = "TC3-HMAC-SHA256";
        $date = gmdate("Y-m-d", $timestamp);
        $credentialScope = $date . "/ocr/tc3_request";
        $reqmethod = $method;
        $canonicalUri = '/';
        $canonicalQueryString = "";
        $canonicalHeaders = "content-type:" . $contentType . "\n".
            "host:" . $host . "\n";
        $signedHeaders = "content-type;host";
        $payload = json_encode($params);
        $payloadHash = hash("SHA256", $payload);
        $canonicalRequest = $reqmethod . "\n" .
            $canonicalUri . "\n" .
            $canonicalQueryString . "\n".
            $canonicalHeaders . "\n" .
            $signedHeaders . "\n" .
            $payloadHash;
        $hashedCanonicalRequest = hash("SHA256", $canonicalRequest);
        $str2sign = $algo . "\n" .
            $timestamp . "\n".
            $credentialScope . "\n" .
            $hashedCanonicalRequest;
        $signature = Sign::signTC3($skey, $date, $service, $str2sign);
        $auth = $algo .
            " Credential=" . $sid . "/" . $credentialScope .
            ", SignedHeaders=content-type;host, Signature=" . $signature;

        return $auth;
    }
}
