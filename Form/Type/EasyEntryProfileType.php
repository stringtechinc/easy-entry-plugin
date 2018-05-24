<?php

/*
 * This file is part of the EasyEntry
 *
 * Copyright (C) 2018 StringTech Inc.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Plugin\EasyEntry\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints as Assert;

class EasyEntryProfileType extends AbstractType
{

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('name', 'name', array(
                'required' => true,
            ))
            ->add('email', 'email', array(
                'attr' => array(
                    'max_length' => 320,
                ),
                'constraints' => array(
                    new Assert\NotBlank(),
                    new Assert\Email(array('strict' => true)),
                ),
            ));
    }

    public function getName()
    {
        return 'easyentry_profile';
    }

}
