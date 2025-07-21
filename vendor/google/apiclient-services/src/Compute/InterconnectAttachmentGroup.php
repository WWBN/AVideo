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

namespace Google\Service\Compute;

class InterconnectAttachmentGroup extends \Google\Model
{
  protected $attachmentsType = InterconnectAttachmentGroupAttachment::class;
  protected $attachmentsDataType = 'map';
  protected $configuredType = InterconnectAttachmentGroupConfigured::class;
  protected $configuredDataType = '';
  /**
   * @var string
   */
  public $creationTimestamp;
  /**
   * @var string
   */
  public $description;
  /**
   * @var string
   */
  public $etag;
  /**
   * @var string
   */
  public $id;
  protected $intentType = InterconnectAttachmentGroupIntent::class;
  protected $intentDataType = '';
  /**
   * @var string
   */
  public $interconnectGroup;
  /**
   * @var string
   */
  public $kind;
  protected $logicalStructureType = InterconnectAttachmentGroupLogicalStructure::class;
  protected $logicalStructureDataType = '';
  /**
   * @var string
   */
  public $name;
  /**
   * @var string
   */
  public $selfLink;

  /**
   * @param InterconnectAttachmentGroupAttachment[]
   */
  public function setAttachments($attachments)
  {
    $this->attachments = $attachments;
  }
  /**
   * @return InterconnectAttachmentGroupAttachment[]
   */
  public function getAttachments()
  {
    return $this->attachments;
  }
  /**
   * @param InterconnectAttachmentGroupConfigured
   */
  public function setConfigured(InterconnectAttachmentGroupConfigured $configured)
  {
    $this->configured = $configured;
  }
  /**
   * @return InterconnectAttachmentGroupConfigured
   */
  public function getConfigured()
  {
    return $this->configured;
  }
  /**
   * @param string
   */
  public function setCreationTimestamp($creationTimestamp)
  {
    $this->creationTimestamp = $creationTimestamp;
  }
  /**
   * @return string
   */
  public function getCreationTimestamp()
  {
    return $this->creationTimestamp;
  }
  /**
   * @param string
   */
  public function setDescription($description)
  {
    $this->description = $description;
  }
  /**
   * @return string
   */
  public function getDescription()
  {
    return $this->description;
  }
  /**
   * @param string
   */
  public function setEtag($etag)
  {
    $this->etag = $etag;
  }
  /**
   * @return string
   */
  public function getEtag()
  {
    return $this->etag;
  }
  /**
   * @param string
   */
  public function setId($id)
  {
    $this->id = $id;
  }
  /**
   * @return string
   */
  public function getId()
  {
    return $this->id;
  }
  /**
   * @param InterconnectAttachmentGroupIntent
   */
  public function setIntent(InterconnectAttachmentGroupIntent $intent)
  {
    $this->intent = $intent;
  }
  /**
   * @return InterconnectAttachmentGroupIntent
   */
  public function getIntent()
  {
    return $this->intent;
  }
  /**
   * @param string
   */
  public function setInterconnectGroup($interconnectGroup)
  {
    $this->interconnectGroup = $interconnectGroup;
  }
  /**
   * @return string
   */
  public function getInterconnectGroup()
  {
    return $this->interconnectGroup;
  }
  /**
   * @param string
   */
  public function setKind($kind)
  {
    $this->kind = $kind;
  }
  /**
   * @return string
   */
  public function getKind()
  {
    return $this->kind;
  }
  /**
   * @param InterconnectAttachmentGroupLogicalStructure
   */
  public function setLogicalStructure(InterconnectAttachmentGroupLogicalStructure $logicalStructure)
  {
    $this->logicalStructure = $logicalStructure;
  }
  /**
   * @return InterconnectAttachmentGroupLogicalStructure
   */
  public function getLogicalStructure()
  {
    return $this->logicalStructure;
  }
  /**
   * @param string
   */
  public function setName($name)
  {
    $this->name = $name;
  }
  /**
   * @return string
   */
  public function getName()
  {
    return $this->name;
  }
  /**
   * @param string
   */
  public function setSelfLink($selfLink)
  {
    $this->selfLink = $selfLink;
  }
  /**
   * @return string
   */
  public function getSelfLink()
  {
    return $this->selfLink;
  }
}

// Adding a class alias for backwards compatibility with the previous class name.
class_alias(InterconnectAttachmentGroup::class, 'Google_Service_Compute_InterconnectAttachmentGroup');
