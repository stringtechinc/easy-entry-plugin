<?php

/*
 * This file is part of the EasyEntry
 *
 * Copyright (C) 2018 StringTech Inc.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Plugin\EasyEntry\ServiceProvider;

use Monolog\Handler\FingersCrossed\ErrorLevelActivationStrategy;
use Monolog\Handler\FingersCrossedHandler;
use Monolog\Handler\RotatingFileHandler;
use Monolog\Logger;
use Plugin\EasyEntry\Form\Type\EasyEntryConfigType;
use Plugin\EasyEntry\Form\Type\EasyEntryProfileType;
use Plugin\EasyEntry\Form\Type\EasyEntryRegisterType;
use Silex\Application as BaseApplication;
use Silex\ServiceProviderInterface;
use Symfony\Component\Yaml\Yaml;

class EasyEntryServiceProvider implements ServiceProviderInterface
{

    public function register(BaseApplication $app)
    {

        // Front
        $app->match('/entry', 'Plugin\EasyEntry\Controller\EasyEntryController::register')->bind('entry');
        $app->match('/mypage/change', 'Plugin\EasyEntry\Controller\EasyEntryController::profile')->bind('mypage_change');

        // Buy Step
        $app->match('/cart/buystep', 'Plugin\EasyEntry\Controller\EasyEntryController::buystep')->bind('cart_buystep');

        // Form
        $app['form.types'] = $app->share($app->extend('form.types', function ($types) use ($app) {
            $types[] = new EasyEntryProfileType();
            $types[] = new EasyEntryRegisterType();
            return $types;
        }));
       
        // Repository

        // Service

        // Config

        // ログファイル設定
        $app['monolog.logger.easyentry'] = $app->share(function ($app) {

            $logger = new $app['monolog.logger.class']('easyentry');

            $filename = $app['config']['root_dir'].'/app/log/easyentry.log';
            $RotateHandler = new RotatingFileHandler($filename, $app['config']['log']['max_files'], Logger::INFO);
            $RotateHandler->setFilenameFormat(
                'easyentry_{date}',
                'Y-m-d'
            );

            $logger->pushHandler(
                new FingersCrossedHandler(
                    $RotateHandler,
                    new ErrorLevelActivationStrategy(Logger::ERROR),
                    0,
                    true,
                    true,
                    Logger::INFO
                )
            );

            return $logger;
        });

    }

    public function boot(BaseApplication $app)
    {
    }

}
