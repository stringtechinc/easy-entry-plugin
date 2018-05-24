<?php
/*
 * This file is part of the EasyEntry
 *
 * Copyright (C) 2018 StringTech Inc.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Plugin\EasyEntry;
use Eccube\Application;
use Eccube\Event\EventArgs;
use Symfony\Component\DomCrawler\Crawler;
use Symfony\Component\HttpKernel\Event\FilterControllerEvent;
use Symfony\Component\HttpKernel\Event\FilterResponseEvent;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\HttpKernel\Event\GetResponseForExceptionEvent;
use Symfony\Component\HttpKernel\Event\PostResponseEvent;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
class EasyEntryEvent
{
    /** @var  \Eccube\Application $app */
    private $app;
    /**
     * EasyEntryEvent constructor.
     *
     * @param Application $app
     */
    public function __construct(Application $app)
    {
        $this->app = $app;
    }

    /**
     * @param EventArgs $event
     */
    public function onAdminCustomerEditIndexInitialize(EventArgs $event)
    {
        $builder = $event->getArgument('builder');
        $builder
        ->add('kana', 'name', array(
            'required' => false
        ));
    }
}
