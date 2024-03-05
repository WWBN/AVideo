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

class ResearchVisionFace2cartoonAgeClassifierResults extends \Google\Model
{
  /**
   * @var string
   */
  public $age;
  /**
   * @var float
   */
  public $predictedAge;

  /**
   * @param string
   */
  public function setAge($age)
  {
    $this->age = $age;
  }
  /**
   * @return string
   */
  public function getAge()
  {
    return $this->age;
  }
  /**
   * @param float
   */
  public function setPredictedAge($predictedAge)
  {
    $this->predictedAge = $predictedAge;
  }
  /**
   * @return float
   */
  public function getPredictedAge()
  {
    return $this->predictedAge;
  }
}

// Adding a class alias for backwards compatibility with the previous class name.
class_alias(ResearchVisionFace2cartoonAgeClassifierResults::class, 'Google_Service_Contentwarehouse_ResearchVisionFace2cartoonAgeClassifierResults');
