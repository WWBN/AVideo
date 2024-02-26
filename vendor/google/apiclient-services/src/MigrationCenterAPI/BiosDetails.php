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

namespace Google\Service\MigrationCenterAPI;

class BiosDetails extends \Google\Model
{
  /**
   * @var string
   */
  public $biosManufacturer;
  /**
   * @var string
   */
  public $biosName;
  /**
   * @var string
   */
  public $biosReleaseDate;
  /**
   * @var string
   */
  public $biosVersion;
  /**
   * @var string
   */
  public $smbiosUuid;

  /**
   * @param string
   */
  public function setBiosManufacturer($biosManufacturer)
  {
    $this->biosManufacturer = $biosManufacturer;
  }
  /**
   * @return string
   */
  public function getBiosManufacturer()
  {
    return $this->biosManufacturer;
  }
  /**
   * @param string
   */
  public function setBiosName($biosName)
  {
    $this->biosName = $biosName;
  }
  /**
   * @return string
   */
  public function getBiosName()
  {
    return $this->biosName;
  }
  /**
   * @param string
   */
  public function setBiosReleaseDate($biosReleaseDate)
  {
    $this->biosReleaseDate = $biosReleaseDate;
  }
  /**
   * @return string
   */
  public function getBiosReleaseDate()
  {
    return $this->biosReleaseDate;
  }
  /**
   * @param string
   */
  public function setBiosVersion($biosVersion)
  {
    $this->biosVersion = $biosVersion;
  }
  /**
   * @return string
   */
  public function getBiosVersion()
  {
    return $this->biosVersion;
  }
  /**
   * @param string
   */
  public function setSmbiosUuid($smbiosUuid)
  {
    $this->smbiosUuid = $smbiosUuid;
  }
  /**
   * @return string
   */
  public function getSmbiosUuid()
  {
    return $this->smbiosUuid;
  }
}

// Adding a class alias for backwards compatibility with the previous class name.
class_alias(BiosDetails::class, 'Google_Service_MigrationCenterAPI_BiosDetails');
