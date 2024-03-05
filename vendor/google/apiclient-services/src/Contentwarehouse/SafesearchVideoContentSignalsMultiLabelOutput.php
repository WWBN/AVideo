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

class SafesearchVideoContentSignalsMultiLabelOutput extends \Google\Model
{
  /**
   * @var float
   */
  public $ageIndeterminate;
  /**
   * @var float
   */
  public $csam;
  /**
   * @var float
   */
  public $porn;
  /**
   * @var float
   */
  public $racy;
  /**
   * @var float
   */
  public $violence;

  /**
   * @param float
   */
  public function setAgeIndeterminate($ageIndeterminate)
  {
    $this->ageIndeterminate = $ageIndeterminate;
  }
  /**
   * @return float
   */
  public function getAgeIndeterminate()
  {
    return $this->ageIndeterminate;
  }
  /**
   * @param float
   */
  public function setCsam($csam)
  {
    $this->csam = $csam;
  }
  /**
   * @return float
   */
  public function getCsam()
  {
    return $this->csam;
  }
  /**
   * @param float
   */
  public function setPorn($porn)
  {
    $this->porn = $porn;
  }
  /**
   * @return float
   */
  public function getPorn()
  {
    return $this->porn;
  }
  /**
   * @param float
   */
  public function setRacy($racy)
  {
    $this->racy = $racy;
  }
  /**
   * @return float
   */
  public function getRacy()
  {
    return $this->racy;
  }
  /**
   * @param float
   */
  public function setViolence($violence)
  {
    $this->violence = $violence;
  }
  /**
   * @return float
   */
  public function getViolence()
  {
    return $this->violence;
  }
}

// Adding a class alias for backwards compatibility with the previous class name.
class_alias(SafesearchVideoContentSignalsMultiLabelOutput::class, 'Google_Service_Contentwarehouse_SafesearchVideoContentSignalsMultiLabelOutput');
