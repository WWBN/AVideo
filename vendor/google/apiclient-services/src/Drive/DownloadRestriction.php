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

namespace Google\Service\Drive;

class DownloadRestriction extends \Google\Model
{
  /**
   * @var bool
   */
  public $restrictedForReaders;
  /**
   * @var bool
   */
  public $restrictedForWriters;

  /**
   * @param bool
   */
  public function setRestrictedForReaders($restrictedForReaders)
  {
    $this->restrictedForReaders = $restrictedForReaders;
  }
  /**
   * @return bool
   */
  public function getRestrictedForReaders()
  {
    return $this->restrictedForReaders;
  }
  /**
   * @param bool
   */
  public function setRestrictedForWriters($restrictedForWriters)
  {
    $this->restrictedForWriters = $restrictedForWriters;
  }
  /**
   * @return bool
   */
  public function getRestrictedForWriters()
  {
    return $this->restrictedForWriters;
  }
}

// Adding a class alias for backwards compatibility with the previous class name.
class_alias(DownloadRestriction::class, 'Google_Service_Drive_DownloadRestriction');
