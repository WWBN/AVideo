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

namespace Google\Service\MapsPlaces;

class GoogleMapsPlacesV1PlaceReviewSummary extends \Google\Model
{
  protected $disclosureTextType = GoogleTypeLocalizedText::class;
  protected $disclosureTextDataType = '';
  /**
   * @var string
   */
  public $flagContentUri;
  /**
   * @var string
   */
  public $reviewsUri;
  protected $textType = GoogleTypeLocalizedText::class;
  protected $textDataType = '';

  /**
   * @param GoogleTypeLocalizedText
   */
  public function setDisclosureText(GoogleTypeLocalizedText $disclosureText)
  {
    $this->disclosureText = $disclosureText;
  }
  /**
   * @return GoogleTypeLocalizedText
   */
  public function getDisclosureText()
  {
    return $this->disclosureText;
  }
  /**
   * @param string
   */
  public function setFlagContentUri($flagContentUri)
  {
    $this->flagContentUri = $flagContentUri;
  }
  /**
   * @return string
   */
  public function getFlagContentUri()
  {
    return $this->flagContentUri;
  }
  /**
   * @param string
   */
  public function setReviewsUri($reviewsUri)
  {
    $this->reviewsUri = $reviewsUri;
  }
  /**
   * @return string
   */
  public function getReviewsUri()
  {
    return $this->reviewsUri;
  }
  /**
   * @param GoogleTypeLocalizedText
   */
  public function setText(GoogleTypeLocalizedText $text)
  {
    $this->text = $text;
  }
  /**
   * @return GoogleTypeLocalizedText
   */
  public function getText()
  {
    return $this->text;
  }
}

// Adding a class alias for backwards compatibility with the previous class name.
class_alias(GoogleMapsPlacesV1PlaceReviewSummary::class, 'Google_Service_MapsPlaces_GoogleMapsPlacesV1PlaceReviewSummary');
