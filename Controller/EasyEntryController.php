<?php

/*
 * This file is part of the EasyEntry
 *
 * Copyright (C) 2018 StringTech Inc.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Plugin\EasyEntry\Controller;

use Eccube\Application;
use Eccube\Entity\Master\CustomerStatus;
use Eccube\Event\EccubeEvents;
use Eccube\Event\EventArgs;
use Symfony\Component\HttpFoundation\Request;

class EasyEntryController
{
    /*
     * register
     *
     * @param Application $app
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function register(Application $app, Request $request)
    {
        if ($app->isGranted('ROLE_USER')) {
            return $app->redirect($app->url('mypage'));
        }

        $builder = $app['form.factory']->createNamedBuilder('', 'easyentry_register');

        $event = new EventArgs(array('builder' => $builder,), $request);

        $app['eccube.event.dispatcher']->dispatch(EccubeEvents::FRONT_ENTRY_INDEX_INITIALIZE, $event);

        $form = $builder->getForm();

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $email = $request->get('email');
            $error = false;
            $result = $this->isValidCustomer($app, $email);
            if ($result['result'] === 2) {
                $error = true;
            } elseif ($result['result'] === 1) {
                $Customer = $result['customer'];
                $activateUrl = $app->url('entry_activate', array('secret_key' => $Customer->getSecretKey()));
                $app['eccube.service.mail']->sendCustomerConfirmMail($Customer, $activateUrl);
                return $app->redirect($app->url('entry_complete'));
            } else {
                $Customer = $app['eccube.repository.customer']->newCustomer();
                $Customer
                    ->setName01('User')
                    ->setName02($this->makeRandStr())
                    ->setEmail($email)
                    ->setSalt(
                        $app['eccube.repository.customer']->createSalt(5)
                    )
                    ->setPassword(
                        $app['eccube.repository.customer']->encryptPassword($app, $Customer)
                    )
                    ->setSecretKey(
                        $app['eccube.repository.customer']->getUniqueSecretKey($app)
                    );

                $app['orm.em']->persist($Customer);
                $app['orm.em']->flush();
                $activateUrl = $app->url('entry_activate', array('secret_key' => $Customer->getSecretKey()));
                $app['eccube.service.mail']->sendCustomerConfirmMail($Customer, $activateUrl);
                return $app->redirect($app->url('entry_complete'));
            }
        }

        return $app->render('EasyEntry/Resource/template/register.twig', array(
            'error' => $error,
            'form' => $form->createView(),
        ));
    }

    /**
     * profile
     *
     * @param Application $app
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function profile(Application $app, Request $request)
    {
        $Customer = $app->user();
        $builder = $app['form.factory']->createNamedBuilder('', 'easyentry_profile');
        if ('POST' === $request->getMethod()) {
            if ($Customer->getEmail() != $request->get('email')) {
                $CustomerOther = $app['eccube.repository.customer']->findOneBy(array('email' => $request->get('email')));
                if (!empty($CustomerOther) && $CustomerOther->getId() != $Customer->getId()) {
                    if (!strstr($Customer->getEmail(), '@wechat.com')) {
                        $builder->get('email')->setData($Customer->getEmail());
                    }
                    $builder->get('name')->get('name01')->setData($Customer->getName01());
                    $builder->get('name')->get('name02')->setData($Customer->getName02());

                    $form = $builder->getForm();

                    return $app->render('EasyEntry/Resource/template/profile.twig', array(
                        'dup'      => true,
                        'error' => $error,
                        'form' => $form->createView(),
                    ));        
                }
            }

            $Customer->setEmail($request->get('email'));
            $Customer->setName01($request->get('name')['name01']);
            $Customer->setName02($request->get('name')['name02']);
            $app['orm.em']->persist($Customer);
            $app['orm.em']->flush();
            return $app->redirect($app->url('mypage_change_complete'));
            
        } else {
            if (!strstr($Customer->getEmail(), '@wechat.com')) {
                $builder->get('email')->setData($Customer->getEmail());
            }
            $builder->get('name')->get('name01')->setData($Customer->getName01());
            $builder->get('name')->get('name02')->setData($Customer->getName02());

            $form = $builder->getForm();

            return $app->render('EasyEntry/Resource/template/profile.twig', array(
                'dup' => false,
                'error' => $error,
                'form' => $form->createView(),
            ));
            
        }
    }

    /**
     *
     * 0: create 1: resend 2: error
     */
    private function isValidCustomer(Application $app, $email)
    {
        $result = array();
        $CustomerStatus = $app['eccube.repository.customer_status']->find(CustomerStatus::ACTIVE);
        $Customers = $app['eccube.repository.customer']->findBy(array('email' => $email));
        if (0 === count($Customers)) {
            $result['result'] = 0;
            return $result;
        }

        foreach($Customers as $Customer) {
            if ($Customer) {
               $result['customer'] = $Customer;
                $active = strcmp($CustomerStatus, $Customer->getStatus());
                if (strcmp($CustomerStatus, $Customer->getStatus()) == 0) {
                    $result['result'] = 2;
                    return $result;
                }
            }
        }
        $result['result'] = 1;
        return $result;
    }

    /**
     * random
     * @param $length
     */
    private function makeRandStr($length = 6) {
        static $chars = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJLKMNOPQRSTUVWXYZ0123456789';
        $str = '';
        for ($i = 0; $i < $length; ++$i) {
            $str .= $chars[mt_rand(0, 61)];
        }
        return $str;
    }
}
