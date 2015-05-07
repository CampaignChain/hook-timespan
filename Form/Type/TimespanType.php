<?php
/*
 * This file is part of the CampaignChain package.
 *
 * (c) Sandro Groganz <sandro@campaignchain.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace CampaignChain\Hook\TimespanBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use CampaignChain\CoreBundle\Util\DateTimeUtil;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class TimespanType extends AbstractType
{
    private $campaign;

    protected $container;
    protected $datetime;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
        $this->datetime = $this->container->get('campaignchain.core.util.datetime');
    }

    public function setCampaign($campaign){
        $this->campaign = $campaign;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('months', 'choice', array(
            'choices'   => array(
                0 => '0 months',
                1 => '1 month',
                2 => '2 months',
                3 => '3 months',
                4 => '4 months',
                5 => '5 months',
                6 => '6 months',
                7 => '7 months',
                8 => '8 months',
                9 => '9 months',
                10 => '10 months',
                11 => '11 months',
                12 => '12 months',
            ),
            'required'  => false,
            'label' => false,
            'attr' => array(
                'placeholder' => 'Months',
            ),
        ))
            ->add('days', 'choice', array(
                'choices'   => array(
                    0 => '0 days',
                    1 => '1 day',
                    2 => '2 days',
                    3 => '3 days',
                    4 => '4 days',
                    5 => '5 days',
                    6 => '6 days',
                    7 => '7 days',
                    8 => '8 days',
                    9 => '9 days',
                    10 => '10 days',
                    11 => '11 days',
                    12 => '12 days',
                    13 => '13 days',
                    14 => '14 days',
                    15 => '15 days',
                    16 => '16 days',
                    17 => '17 days',
                    18 => '18 days',
                    19 => '19 days',
                    20 => '20 days',
                    21 => '21 days',
                    22 => '22 days',
                    23 => '23 days',
                    24 => '24 days',
                    25 => '25 days',
                    26 => '26 days',
                    27 => '27 days',
                    28 => '28 days',
                    29 => '29 days',
                    30 => '30 days',

                ),
                'required'  => false,
                'label' => false,
                'attr' => array(
                    'placeholder' => 'Days',
                ),
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