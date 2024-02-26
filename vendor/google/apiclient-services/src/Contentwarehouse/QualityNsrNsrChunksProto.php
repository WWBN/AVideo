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

class QualityNsrNsrChunksProto extends \Google\Collection
{
  protected $collection_key = 'secondaryChunks';
  /**
   * @var string
   */
  public $primaryChunk;
  /**
   * @var string[]
   */
  public $secondaryChunks;

  /**
   * @param string
   */
  public function setPrimaryChunk($primaryChunk)
  {
    $this->primaryChunk = $primaryChunk;
  }
  /**
   * @return string
   */
  public function getPrimaryChunk()
  {
    return $this->primaryChunk;
  }
  /**
   * @param string[]
   */
  public function setSecondaryChunks($secondaryChunks)
  {
    $this->secondaryChunks = $secondaryChunks;
  }
  /**
   * @return string[]
   */
  public function getSecondaryChunks()
  {
    return $this->secondaryChunks;
  }
}

// Adding a class alias for backwards compatibility with the previous class name.
class_alias(QualityNsrNsrChunksProto::class, 'Google_Service_Contentwarehouse_QualityNsrNsrChunksProto');
