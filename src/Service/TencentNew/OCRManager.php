<?php
/**
 * Created by PhpStorm.
 * User: leohu
 * Date: 2017/12/6
 * Time: 下午12:44
 */

namespace Godruoyi\OCR\Service\TencentNew;

use Godruoyi\OCR\Support\Arr;
use Godruoyi\OCR\Support\Http;
use Godruoyi\OCR\Support\FileConverter;
use GuzzleHttp\Exception\ClientException;

/**
 * @copyright 2017
 *
 * @see  https://github.com/godruoyi/ocr
 *
 * @method array idcard($images, $options = []) 身份证识别
 * @method array namecard($images, $options = []) 名片识别
 * @method array driverlicen($images, $options = []) 行驶证驾驶证识别
 * @method array bankcard($images, $options = []) 银行卡识别
 * @method array general($images, $options = []) 通用文字识别
 * @method array bizlicense($images, $options = []) 营业执照识别
 */
class OCRManager
{
    /**
     * TENCENT New OCR URL
     */
    const URL_OCR = 'https://ocr.tencentcloudapi.com';

    /**
     *  Request Client
     */
    const REQUEST_CLIENT = 'SDK_PHP_3.0.166';

    /**
     * Api Version
     */
    const API_VERSION = '2018-11-19';

    /**
     * Region
     */
    const REGION = 'ap-beijing';

    /**
     * ID Card OCR Action
     */
    const ID_CARD_OCR_ACTION = 'IDCardOCR';

    /**
     * Biz License OCR Action
     */
    const BIZ_LICENSE_OCR_ACTION = 'BizLicenseOCR';

    /**
     * Tencent AI Authorization
     *
     * @var Authorization
     */
    protected $authorization;

    /**
     * @var array
     */
    protected $configs;

    /**
     * OCRManager constructor.
     *
     * @param Authorization $authorization
     * @param array $configs
     */
    public function __construct(Authorization $authorization, array $configs = [])
    {

        $this->authorization = $authorization;
        $this->configs = $configs;
    }

    /**
     * 身份证OCR识别
     *
     * @param mixed $image
     * @param array $options
     *
     * @see https://cloud.tencent.com/document/api/866/33524
     *
     * @return array
     */
    public function idcard($image, array $options = [])
    {
        $params = array_merge($this->getBaseParams($image), $options);

        return $this->request(self::ID_CARD_OCR_ACTION, 'POST', $params);
    }

    /**
     * Biz License OCR Action
     *
     * @author reallyli <zlisreallyli@outlook.com>
     * @since 2020/5/8
     * @param mixed $image
     * @param array $options
     * @see https://cloud.tencent.com/document/product/866/36215
     * @return mixed
     */
    public function bizLicense($image, array $options = [])
    {
        $params = array_merge($this->getBaseParams($image), $options);

        return $this->request(self::BIZ_LICENSE_OCR_ACTION, 'POST', $params);
    }

    /**
     * Get Base Option
     *
     * @author reallyli <zlisreallyli@outlook.com>
     * @since 2020/5/8
     * @param mixed $image
     * @return array
     */
    protected function getBaseParams($image)
    {
        if (FileConverter::isUrl($image)) {
            $params['ImageUrl'] = $image;
        } else {
            $params['ImageBase64'] = FileConverter::toBase64Encode($image);
        }

        return $params;
    }

    /**
     * Get Host
     *
     * @author reallyli <zlisreallyli@outlook.com>
     * @since 2020/5/7
     * @return string
     */
    protected function getHost()
    {
        return parse_url(self::URL_OCR)['host'];
    }

    /**
     * 发起HTTP请求，并返回JSON
     *
     * @param $action
     * @param $params
     * @param $method
     * @throws \Exception
     *
     * @return array
     */
    protected function request(string $action, string $method, array $params)
    {
        try {
            $timestamp = time();
            $contentType = $method === 'GET' ? 'application/x-www-form-urlencoded' : 'application/json';
            $headers = [
                "Host" => $this->getHost(),
                "X-TC-Action" => $action,
                "X-TC-RequestClient" => Arr::get($this->configs, 'request_client') ?: self::REQUEST_CLIENT,
                "X-TC-Timestamp" => $timestamp,
                "X-TC-Version" => Arr::get($this->configs, 'api_version') ?: self::API_VERSION,
                "X-TC-Region" => Arr::get($this->configs, 'region') ?: self::REGION,
                "Content-Type" => $contentType,
                "Authorization" => $this->authorization->generateSignature($params, $method, $timestamp, $this->getHost(), $contentType)
            ];

            $http = new HTTP();
            $response = $http->setHeaders($headers)
                ->request($method, self::URL_OCR, [
                    'json' => $params
                ]);
        } catch (ClientException $ce) {
            $response = $response->getBody();
        }

        return $http->parseJson($response);
    }
}
