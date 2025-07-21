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

class InterconnectGroup extends \Google\Model
{
  protected $configuredType = InterconnectGroupConfigured::class;
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
  protected $intentType = InterconnectGroupIntent::class;
  protected $intentDataType = '';
  protected $interconnectsType = InterconnectGroupInterconnect::class;
  protected $interconnectsDataType = 'map';
  /**
   * @var string
   */
  public $kind;
  /**
   * @var string
   */
  public $name;
  protected $physicalStructureType = InterconnectGroupPhysicalStructure::class;
  protected $physicalStructureDataType = '';
  /**
   * @var string
   */
  public $selfLink;

  /**
   * @param InterconnectGroupConfigured
   */
  public function setConfigured(InterconnectGroupConfigured $configured)
  {
    $this->configured = $configured;
  }
  /**
   * @return InterconnectGroupConfigured
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
   * @param InterconnectGroupIntent
   */
  public function setIntent(InterconnectGroupIntent $intent)
  {
    $this->intent = $intent;
  }
  /**
   * @return InterconnectGroupIntent
   */
  public function getIntent()
  {
    return $this->intent;
  }
  /**
   * @param InterconnectGroupInterconnect[]
   */
  public function setInterconnects($interconnects)
  {
    $this->interconnects = $interconnects;
  }
  /**
   * @return InterconnectGroupInterconnect[]
   */
  public function getInterconnects()
  {
    return $this->interconnects;
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
   * @param InterconnectGroupPhysicalStructure
   */
  public function setPhysicalStructure(InterconnectGroupPhysicalStructure $physicalStructure)
  {
    $this->physicalStructure = $physicalStructure;
  }
  /**
   * @return InterconnectGroupPhysicalStructure
   */
  public function getPhysicalStructure()
  {
    return $this->physicalStructure;
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
class_alias(InterconnectGroup::class, 'Google_Service_Compute_InterconnectGroup');
