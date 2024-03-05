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

namespace Google\Service\Aiplatform;

class LearningGenaiRootScoreBasedRoutingConfigRule extends \Google\Model
{
  protected $equalOrGreaterThanType = LearningGenaiRootScore::class;
  protected $equalOrGreaterThanDataType = '';
  protected $lessThanType = LearningGenaiRootScore::class;
  protected $lessThanDataType = '';
  /**
   * @var string
   */
  public $modelConfigId;

  /**
   * @param LearningGenaiRootScore
   */
  public function setEqualOrGreaterThan(LearningGenaiRootScore $equalOrGreaterThan)
  {
    $this->equalOrGreaterThan = $equalOrGreaterThan;
  }
  /**
   * @return LearningGenaiRootScore
   */
  public function getEqualOrGreaterThan()
  {
    return $this->equalOrGreaterThan;
  }
  /**
   * @param LearningGenaiRootScore
   */
  public function setLessThan(LearningGenaiRootScore $lessThan)
  {
    $this->lessThan = $lessThan;
  }
  /**
   * @return LearningGenaiRootScore
   */
  public function getLessThan()
  {
    return $this->lessThan;
  }
  /**
   * @param string
   */
  public function setModelConfigId($modelConfigId)
  {
    $this->modelConfigId = $modelConfigId;
  }
  /**
   * @return string
   */
  public function getModelConfigId()
  {
    return $this->modelConfigId;
  }
}

// Adding a class alias for backwards compatibility with the previous class name.
class_alias(LearningGenaiRootScoreBasedRoutingConfigRule::class, 'Google_Service_Aiplatform_LearningGenaiRootScoreBasedRoutingConfigRule');
