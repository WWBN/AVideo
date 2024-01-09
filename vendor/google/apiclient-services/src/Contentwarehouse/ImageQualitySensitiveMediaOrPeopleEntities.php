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

class ImageQualitySensitiveMediaOrPeopleEntities extends \Google\Collection
{
  protected $collection_key = 'peopleEntitiesId';
  /**
   * @var string[]
   */
  public $mediaEntitiesId;
  /**
   * @var string[]
   */
  public $peopleEntitiesId;

  /**
   * @param string[]
   */
  public function setMediaEntitiesId($mediaEntitiesId)
  {
    $this->mediaEntitiesId = $mediaEntitiesId;
  }
  /**
   * @return string[]
   */
  public function getMediaEntitiesId()
  {
    return $this->mediaEntitiesId;
  }
  /**
   * @param string[]
   */
  public function setPeopleEntitiesId($peopleEntitiesId)
  {
    $this->peopleEntitiesId = $peopleEntitiesId;
  }
  /**
   * @return string[]
   */
  public function getPeopleEntitiesId()
  {
    return $this->peopleEntitiesId;
  }
}

// Adding a class alias for backwards compatibility with the previous class name.
class_alias(ImageQualitySensitiveMediaOrPeopleEntities::class, 'Google_Service_Contentwarehouse_ImageQualitySensitiveMediaOrPeopleEntities');
