<?php

/*
 * This file is part of the godruoyi/ocr.
 *
 * (c) godruoyi <godruoyi@gmail.com>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Godruoyi\OCR\Providers;

use Pimple\Container;
use Pimple\ServiceProviderInterface;
use Godruoyi\OCR\Service\TencentNew\OCRManager;
use Godruoyi\OCR\Service\TencentNew\Authorization;

class TencentNewProvider implements ServiceProviderInterface
{
    /**
     * {@inheritdoc}
     */
    public function register(Container $pimple)
    {
        $pimple['tencentnew.auth'] = function ($app) {
            return new Authorization(
                $app['config']->get('ocrs.tencentnew.app_id'),
                $app['config']->get('ocrs.tencentnew.app_key')
            );
        };

        $pimple['tencentnew'] = function ($app) {
            return new OCRManager($app['tencentnew.auth'], $app['config']->get('ocrs.tencentnew'));
        };
    }
}
