<?php
/*
 * Copyright 2014 Google Inc.
 *
 * Licensed under the Apache License, Version 2.0 (the "License"); you may not
 * use this file except in compliance with the License. You may obtain a copy of
 * the License at
 *
 * http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS, WITHOUT
 * WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied. See the
 * License for the specific language governing permissions and limitations under
 * the License.
 */

namespace Google\Service\Contentwarehouse;

class SocialGraphApiProtoPrompt extends \Google\Collection
{
  protected $collection_key = 'notificationTriggers';
  /**
   * @var string
   */
  public $activeState;
  protected $contentType = SocialGraphApiProtoPromptContent::class;
  protected $contentDataType = '';
  protected $lastDismissDateType = GoogleTypeDate::class;
  protected $lastDismissDateDataType = '';
  protected $notificationTriggersType = SocialGraphApiProtoNotificationTrigger::class;
  protected $notificationTriggersDataType = 'array';
  /**
   * @var string
   */
  public $purpose;
  protected $recurrenceType = SocialGraphApiProtoRecurrence::class;
  protected $recurrenceDataType = '';
  /**
   * @var string
   */
  public $uniquePromptId;

  /**
   * @param string
   */
  public function setActiveState($activeState)
  {
    $this->activeState = $activeState;
  }
  /**
   * @return string
   */
  public function getActiveState()
  {
    return $this->activeState;
  }
  /**
   * @param SocialGraphApiProtoPromptContent
   */
  public function setContent(SocialGraphApiProtoPromptContent $content)
  {
    $this->content = $content;
  }
  /**
   * @return SocialGraphApiProtoPromptContent
   */
  public function getContent()
  {
    return $this->content;
  }
  /**
   * @param GoogleTypeDate
   */
  public function setLastDismissDate(GoogleTypeDate $lastDismissDate)
  {
    $this->lastDismissDate = $lastDismissDate;
  }
  /**
   * @return GoogleTypeDate
   */
  public function getLastDismissDate()
  {
    return $this->lastDismissDate;
  }
  /**
   * @param SocialGraphApiProtoNotificationTrigger[]
   */
  public function setNotificationTriggers($notificationTriggers)
  {
    $this->notificationTriggers = $notificationTriggers;
  }
  /**
   * @return SocialGraphApiProtoNotificationTrigger[]
   */
  public function getNotificationTriggers()
  {
    return $this->notificationTriggers;
  }
  /**
   * @param string
   */
  public function setPurpose($purpose)
  {
    $this->purpose = $purpose;
  }
  /**
   * @return string
   */
  public function getPurpose()
  {
    return $this->purpose;
  }
  /**
   * @param SocialGraphApiProtoRecurrence
   */
  public function setRecurrence(SocialGraphApiProtoRecurrence $recurrence)
  {
    $this->recurrence = $recurrence;
  }
  /**
   * @return SocialGraphApiProtoRecurrence
   */
  public function getRecurrence()
  {
    return $this->recurrence;
  }
  /**
   * @param string
   */
  public function setUniquePromptId($uniquePromptId)
  {
    $this->uniquePromptId = $uniquePromptId;
  }
  /**
   * @return string
   */
  public function getUniquePromptId()
  {
    return $this->uniquePromptId;
  }
}

// Adding a class alias for backwards compatibility with the previous class name.
class_alias(SocialGraphApiProtoPrompt::class, 'Google_Service_Contentwarehouse_SocialGraphApiProtoPrompt');
