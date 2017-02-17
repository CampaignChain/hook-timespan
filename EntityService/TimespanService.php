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
use CampaignChain\CoreBundle\EntityService\CampaignService;
use CampaignChain\CoreBundle\EntityService\HookServiceTriggerInterface;
use CampaignChain\CoreBundle\Util\DateTimeUtil;
use CampaignChain\Hook\TimespanBundle\Entity\Timespan;
use CampaignChain\CoreBundle\Exception\ErrorCode;
use Doctrine\Common\Persistence\ManagerRegistry;
use Doctrine\Common\Inflector\Inflector;
use Symfony\Bundle\FrameworkBundle\Templating\EngineInterface;

class TimespanService extends HookServiceTriggerInterface
{
    protected $em;
    protected $dateTimeUtil;
    protected $templating;
    protected $campaignService;

    public function __construct(
        ManagerRegistry $managerRegistry,
        DateTimeUtil $dateTimeUtil,
        EngineInterface $templating,
        CampaignService $campaignService
    )
    {
        $this->em = $managerRegistry->getManager();
        $this->dateTimeUtil = $dateTimeUtil;
        $this->templating = $templating;
        $this->campaignService = $campaignService;
    }

    public function getHook($entity, $mode = Hook::MODE_DEFAULT){
        $hook = new Timespan();

        if(is_object($entity) && $entity->getId() !== null){
            $interval = $entity->getStartDate()->diff($entity->getEndDate());
            $hook->setDays($interval->format("%a"));
            $hook->setStartDate($entity->getStartDate());
            $hook->setEndDate($entity->getEndDate());

            /**
             * If this is a campaign, we define the time limits for the timespan.
             */
            $class = get_class($entity);
            if(strpos($class, 'CoreBundle\Entity\Campaign') !== false) {
                $entity = $this->setPostStartDateLimit($entity);
                $entity = $this->setPreEndDateLimit($entity);
            }
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

        $class = get_class($entity);
        if(strpos($class, 'CoreBundle\Entity\Campaign') !== false) {
            if(!$this->campaignService->isValidTimespan($entity, new \DateInterval("P".$hook->getDays()."D"))){
                $this->addErrorCode(ErrorCode::CAMPAIGN_TIMESPAN_INSUFFICIENT);
            }
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

        $this->setEntity($entity);

        return true;
    }

    public function arrayToObject($hookData){
        if(is_array($hookData) && count($hookData)){
            // Intercept if timespan date is supposed to be "now".
            if(isset($hookData['execution_choice'])){
                if($hookData['execution_choice'] == 'now'){
                    $nowDate = new \DateTime('now');
                    $hookData['date'] = $this->datetimeUtil->formatLocale($nowDate);
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
        return $this->templating->render(
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