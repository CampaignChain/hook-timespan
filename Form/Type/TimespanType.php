<?php
/*
 * Copyright 2016 CampaignChain, Inc. <info@campaignchain.com>
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *    http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */

namespace CampaignChain\Hook\TimespanBundle\Form\Type;

use CampaignChain\CoreBundle\Form\Type\HookType;
use Symfony\Component\Form\FormBuilderInterface;
use CampaignChain\CoreBundle\Util\DateTimeUtil;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class TimespanType extends HookType
{
    protected $container;
    protected $datetime;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
        $this->datetime = $this->container->get('campaignchain.core.util.datetime');
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $this->setOptions($options);

        if(!isset($this->hooksOptions['disabled'])){
            $this->hooksOptions['disabled'] = false;
        }

        $builder
            ->add('days', 'integer', array(
                'label' => false,
                'scale' => 0,
                'disabled' => $this->hooksOptions['disabled'],
                'attr' => array(
                    'min' => 1,
                    'help_text' => 'Number of days',
                    'input_group' => array(
                        'append' => '<span class="fa fa-calendar">',
                    )
                )
            ));
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        parent::configureOptions($resolver);

        $resolver
            ->setDefaults([
                'validation_groups' => false,
            ]);
    }

    public function getBlockPrefix()
    {
        return 'campaignchain_hook_campaignchain_timespan';
    }
}