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

namespace Google\Service\CloudDataplex;

class GoogleCloudDataplexV1DataQualityRule extends \Google\Model
{
  /**
   * @var string
   */
  public $column;
  /**
   * @var string
   */
  public $dimension;
  /**
   * @var bool
   */
  public $ignoreNull;
  protected $nonNullExpectationType = GoogleCloudDataplexV1DataQualityRuleNonNullExpectation::class;
  protected $nonNullExpectationDataType = '';
  protected $rangeExpectationType = GoogleCloudDataplexV1DataQualityRuleRangeExpectation::class;
  protected $rangeExpectationDataType = '';
  protected $regexExpectationType = GoogleCloudDataplexV1DataQualityRuleRegexExpectation::class;
  protected $regexExpectationDataType = '';
  protected $rowConditionExpectationType = GoogleCloudDataplexV1DataQualityRuleRowConditionExpectation::class;
  protected $rowConditionExpectationDataType = '';
  protected $setExpectationType = GoogleCloudDataplexV1DataQualityRuleSetExpectation::class;
  protected $setExpectationDataType = '';
  protected $statisticRangeExpectationType = GoogleCloudDataplexV1DataQualityRuleStatisticRangeExpectation::class;
  protected $statisticRangeExpectationDataType = '';
  protected $tableConditionExpectationType = GoogleCloudDataplexV1DataQualityRuleTableConditionExpectation::class;
  protected $tableConditionExpectationDataType = '';
  public $threshold;
  protected $uniquenessExpectationType = GoogleCloudDataplexV1DataQualityRuleUniquenessExpectation::class;
  protected $uniquenessExpectationDataType = '';

  /**
   * @param string
   */
  public function setColumn($column)
  {
    $this->column = $column;
  }
  /**
   * @return string
   */
  public function getColumn()
  {
    return $this->column;
  }
  /**
   * @param string
   */
  public function setDimension($dimension)
  {
    $this->dimension = $dimension;
  }
  /**
   * @return string
   */
  public function getDimension()
  {
    return $this->dimension;
  }
  /**
   * @param bool
   */
  public function setIgnoreNull($ignoreNull)
  {
    $this->ignoreNull = $ignoreNull;
  }
  /**
   * @return bool
   */
  public function getIgnoreNull()
  {
    return $this->ignoreNull;
  }
  /**
   * @param GoogleCloudDataplexV1DataQualityRuleNonNullExpectation
   */
  public function setNonNullExpectation(GoogleCloudDataplexV1DataQualityRuleNonNullExpectation $nonNullExpectation)
  {
    $this->nonNullExpectation = $nonNullExpectation;
  }
  /**
   * @return GoogleCloudDataplexV1DataQualityRuleNonNullExpectation
   */
  public function getNonNullExpectation()
  {
    return $this->nonNullExpectation;
  }
  /**
   * @param GoogleCloudDataplexV1DataQualityRuleRangeExpectation
   */
  public function setRangeExpectation(GoogleCloudDataplexV1DataQualityRuleRangeExpectation $rangeExpectation)
  {
    $this->rangeExpectation = $rangeExpectation;
  }
  /**
   * @return GoogleCloudDataplexV1DataQualityRuleRangeExpectation
   */
  public function getRangeExpectation()
  {
    return $this->rangeExpectation;
  }
  /**
   * @param GoogleCloudDataplexV1DataQualityRuleRegexExpectation
   */
  public function setRegexExpectation(GoogleCloudDataplexV1DataQualityRuleRegexExpectation $regexExpectation)
  {
    $this->regexExpectation = $regexExpectation;
  }
  /**
   * @return GoogleCloudDataplexV1DataQualityRuleRegexExpectation
   */
  public function getRegexExpectation()
  {
    return $this->regexExpectation;
  }
  /**
   * @param GoogleCloudDataplexV1DataQualityRuleRowConditionExpectation
   */
  public function setRowConditionExpectation(GoogleCloudDataplexV1DataQualityRuleRowConditionExpectation $rowConditionExpectation)
  {
    $this->rowConditionExpectation = $rowConditionExpectation;
  }
  /**
   * @return GoogleCloudDataplexV1DataQualityRuleRowConditionExpectation
   */
  public function getRowConditionExpectation()
  {
    return $this->rowConditionExpectation;
  }
  /**
   * @param GoogleCloudDataplexV1DataQualityRuleSetExpectation
   */
  public function setSetExpectation(GoogleCloudDataplexV1DataQualityRuleSetExpectation $setExpectation)
  {
    $this->setExpectation = $setExpectation;
  }
  /**
   * @return GoogleCloudDataplexV1DataQualityRuleSetExpectation
   */
  public function getSetExpectation()
  {
    return $this->setExpectation;
  }
  /**
   * @param GoogleCloudDataplexV1DataQualityRuleStatisticRangeExpectation
   */
  public function setStatisticRangeExpectation(GoogleCloudDataplexV1DataQualityRuleStatisticRangeExpectation $statisticRangeExpectation)
  {
    $this->statisticRangeExpectation = $statisticRangeExpectation;
  }
  /**
   * @return GoogleCloudDataplexV1DataQualityRuleStatisticRangeExpectation
   */
  public function getStatisticRangeExpectation()
  {
    return $this->statisticRangeExpectation;
  }
  /**
   * @param GoogleCloudDataplexV1DataQualityRuleTableConditionExpectation
   */
  public function setTableConditionExpectation(GoogleCloudDataplexV1DataQualityRuleTableConditionExpectation $tableConditionExpectation)
  {
    $this->tableConditionExpectation = $tableConditionExpectation;
  }
  /**
   * @return GoogleCloudDataplexV1DataQualityRuleTableConditionExpectation
   */
  public function getTableConditionExpectation()
  {
    return $this->tableConditionExpectation;
  }
  public function setThreshold($threshold)
  {
    $this->threshold = $threshold;
  }
  public function getThreshold()
  {
    return $this->threshold;
  }
  /**
   * @param GoogleCloudDataplexV1DataQualityRuleUniquenessExpectation
   */
  public function setUniquenessExpectation(GoogleCloudDataplexV1DataQualityRuleUniquenessExpectation $uniquenessExpectation)
  {
    $this->uniquenessExpectation = $uniquenessExpectation;
  }
  /**
   * @return GoogleCloudDataplexV1DataQualityRuleUniquenessExpectation
   */
  public function getUniquenessExpectation()
  {
    return $this->uniquenessExpectation;
  }
}

// Adding a class alias for backwards compatibility with the previous class name.
class_alias(GoogleCloudDataplexV1DataQualityRule::class, 'Google_Service_CloudDataplex_GoogleCloudDataplexV1DataQualityRule');
