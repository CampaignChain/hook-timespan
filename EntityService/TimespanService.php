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

namespace CampaignChain\Hook\TimespanBundle\EntityService;

use CampaignChain\CoreBundle\Entity\Hook;
use CampaignChain\CoreBundle\EntityService\HookServiceTriggerInterface;
use CampaignChain\Hook\TimespanBundle\Entity\Timespan;
use Doctrine\Common\Persistence\ManagerRegistry;
use Doctrine\Common\Inflector\Inflector;
use Symfony\Component\DependencyInjection\ContainerInterface;

class TimespanService implements HookServiceTriggerInterface
{
    protected $em;
    protected $container;

    public function __construct(ManagerRegistry $managerRegistry, ContainerInterface $container)
    {
        $this->container = $container;
        $this->em = $managerRegistry->getManager();
    }

    public function getHook($entity, $mode = Hook::MODE_DEFAULT){
        $hook = new Timespan();

        if(is_object($entity) && $entity->getId() !== null){
            $interval = $entity->getStartDate()->diff($entity->getEndDate());
            $hook->setDays($interval->format("%a"));
            $hook->setStartDate($entity->getStartDate());
            $hook->setEndDate($entity->getEndDate());
        }

        return $hook;
    }

    public function processHook($entity, $hook){
        if(!$entity->getStartDate()){
            $now = new \DateTime('now', new \DateTimeZone($hook->getTimezone()));
            $entity->setStartDate($now);
        } elseif(!$hook->getStartDate()) {
            $hook->setStartDate($entity->getStartDate());
        } else {
            $entity->setStartDate($hook->getStartDate());
        }

        // Update the dates of the entity.
        $endDate = clone $entity->getStartDate();
        $endDate->modify(
            '+'.$hook->getDays().' days'
        );
        $entity->setEndDate($endDate);

        // If the entity is an Activity and it equals the Operation, then
        // - the same dates will be set for the Operation
        // - the same trigger Hook will be set for the Operation
        $class = get_class($entity);
        if(strpos($class, 'CoreBundle\Entity\Activity') !== false && $entity->getEqualsOperation() == true){
            $operation = $entity->getOperations()[0];
            $operation->setStartDate($entity->getStartDate());
            $operation->setEndDate($entity->getEndDate());
            $operation->setTriggerHook($entity->getTriggerHook());
        }

        return $entity;
    }

    public function arrayToObject($hookData){
        if(is_array($hookData) && count($hookData)){
            $datetimeUtil = $this->container->get('campaignchain.core.util.datetime');

            // Intercept if timespan date is supposed to be "now".
            if(isset($hookData['execution_choice'])){
                if($hookData['execution_choice'] == 'now'){
                    $nowDate = new \DateTime('now');
                    $hookData['date'] = $datetimeUtil->formatLocale($nowDate);
                }
                unset($hookData['execution_choice']);
            }

            $hook = new Timespan();
            foreach($hookData as $property => $value){
                // TODO: Research whether this is a security risk, e.g. if the property name has been injected via a REST post.
                $method = 'set'.Inflector::classify($property);
                if($method == 'setDate' && !is_object($value) && !$value instanceof \DateTime){
                    // TODO: De-localize the value and change from user format to ISO8601.
                    $value = new \DateTime($value, new \DateTimeZone($hookData['timezone']));
                }
                $hook->$method($value);
            }
        }

        return $hook;
    }

    public function tplInline($entity){
        $hook = $this->getHook($entity);
        return $this->container->get('templating')->render(
            'CampaignChainHookTimespanBundle::inline.html.twig',
            array('hook' => $hook)
        );
    }

    /**
     * Returns the corresponding start date field attribute name as specified in the respective form type.
     *
     * @return string
     */
    public function getStartDateIdentifier(){
        return 'date';
    }

    /**
     * Returns the corresponding end date field attribute name as specified in the respective form type.
     *
     * @return string
     */
    public function getEndDateIdentifier(){
        return 'date';
    }
}