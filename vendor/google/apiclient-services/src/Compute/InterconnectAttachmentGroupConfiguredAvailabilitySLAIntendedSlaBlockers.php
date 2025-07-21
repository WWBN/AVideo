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

class InterconnectAttachmentGroupConfiguredAvailabilitySLAIntendedSlaBlockers extends \Google\Collection
{
  protected $collection_key = 'zones';
  /**
   * @var string[]
   */
  public $attachments;
  /**
   * @var string
   */
  public $blockerType;
  /**
   * @var string
   */
  public $documentationLink;
  /**
   * @var string
   */
  public $explanation;
  /**
   * @var string[]
   */
  public $metros;
  /**
   * @var string[]
   */
  public $regions;
  /**
   * @var string[]
   */
  public $zones;

  /**
   * @param string[]
   */
  public function setAttachments($attachments)
  {
    $this->attachments = $attachments;
  }
  /**
   * @return string[]
   */
  public function getAttachments()
  {
    return $this->attachments;
  }
  /**
   * @param string
   */
  public function setBlockerType($blockerType)
  {
    $this->blockerType = $blockerType;
  }
  /**
   * @return string
   */
  public function getBlockerType()
  {
    return $this->blockerType;
  }
  /**
   * @param string
   */
  public function setDocumentationLink($documentationLink)
  {
    $this->documentationLink = $documentationLink;
  }
  /**
   * @return string
   */
  public function getDocumentationLink()
  {
    return $this->documentationLink;
  }
  /**
   * @param string
   */
  public function setExplanation($explanation)
  {
    $this->explanation = $explanation;
  }
  /**
   * @return string
   */
  public function getExplanation()
  {
    return $this->explanation;
  }
  /**
   * @param string[]
   */
  public function setMetros($metros)
  {
    $this->metros = $metros;
  }
  /**
   * @return string[]
   */
  public function getMetros()
  {
    return $this->metros;
  }
  /**
   * @param string[]
   */
  public function setRegions($regions)
  {
    $this->regions = $regions;
  }
  /**
   * @return string[]
   */
  public function getRegions()
  {
    return $this->regions;
  }
  /**
   * @param string[]
   */
  public function setZones($zones)
  {
    $this->zones = $zones;
  }
  /**
   * @return string[]
   */
  public function getZones()
  {
    return $this->zones;
  }
}

// Adding a class alias for backwards compatibility with the previous class name.
class_alias(InterconnectAttachmentGroupConfiguredAvailabilitySLAIntendedSlaBlockers::class, 'Google_Service_Compute_InterconnectAttachmentGroupConfiguredAvailabilitySLAIntendedSlaBlockers');
