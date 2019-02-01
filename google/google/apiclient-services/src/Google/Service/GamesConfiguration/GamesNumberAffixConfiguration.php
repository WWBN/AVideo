<?php
/*
 * Copyright 2016 Google Inc.
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

class Google_Service_GamesConfiguration_GamesNumberAffixConfiguration extends Google_Model
{
  protected $fewType = 'Google_Service_GamesConfiguration_LocalizedStringBundle';
  protected $fewDataType = '';
  protected $manyType = 'Google_Service_GamesConfiguration_LocalizedStringBundle';
  protected $manyDataType = '';
  protected $oneType = 'Google_Service_GamesConfiguration_LocalizedStringBundle';
  protected $oneDataType = '';
  protected $otherType = 'Google_Service_GamesConfiguration_LocalizedStringBundle';
  protected $otherDataType = '';
  protected $twoType = 'Google_Service_GamesConfiguration_LocalizedStringBundle';
  protected $twoDataType = '';
  protected $zeroType = 'Google_Service_GamesConfiguration_LocalizedStringBundle';
  protected $zeroDataType = '';

  public function setFew(Google_Service_GamesConfiguration_LocalizedStringBundle $few)
  {
    $this->few = $few;
  }
  public function getFew()
  {
    return $this->few;
  }
  public function setMany(Google_Service_GamesConfiguration_LocalizedStringBundle $many)
  {
    $this->many = $many;
  }
  public function getMany()
  {
    return $this->many;
  }
  public function setOne(Google_Service_GamesConfiguration_LocalizedStringBundle $one)
  {
    $this->one = $one;
  }
  public function getOne()
  {
    return $this->one;
  }
  public function setOther(Google_Service_GamesConfiguration_LocalizedStringBundle $other)
  {
    $this->other = $other;
  }
  public function getOther()
  {
    return $this->other;
  }
  public function setTwo(Google_Service_GamesConfiguration_LocalizedStringBundle $two)
  {
    $this->two = $two;
  }
  public function getTwo()
  {
    return $this->two;
  }
  public function setZero(Google_Service_GamesConfiguration_LocalizedStringBundle $zero)
  {
    $this->zero = $zero;
  }
  public function getZero()
  {
    return $this->zero;
  }
}
