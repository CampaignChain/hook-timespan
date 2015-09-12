<?php
/*
 * This file is part of the CampaignChain package.
 *
 * (c) CampaignChain Inc. <info@campaignchain.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace CampaignChain\Hook\TimespanBundle\Form\Type;

use CampaignChain\CoreBundle\Form\Type\HookType;
use Symfony\Component\Form\FormBuilderInterface;
use CampaignChain\CoreBundle\Util\DateTimeUtil;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

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
        if(!isset($this->hooksOptions['disabled'])){
            $this->hooksOptions['disabled'] = false;
        }

        $builder
            ->add('days', 'integer', array(
                'label' => false,
                'precision' => 0,
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

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver
            ->setDefaults([
                'validation_groups' => false,
            ]);
    }

    public function getName()
    {
        return 'campaignchain_hook_campaignchain_timespan';
    }
}