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

class Google_Service_Dataflow_DistributionUpdate extends Google_Model
{
  protected $countType = 'Google_Service_Dataflow_SplitInt64';
  protected $countDataType = '';
  protected $maxType = 'Google_Service_Dataflow_SplitInt64';
  protected $maxDataType = '';
  protected $minType = 'Google_Service_Dataflow_SplitInt64';
  protected $minDataType = '';
  protected $sumType = 'Google_Service_Dataflow_SplitInt64';
  protected $sumDataType = '';
  public $sumOfSquares;

  public function setCount(Google_Service_Dataflow_SplitInt64 $count)
  {
    $this->count = $count;
  }
  public function getCount()
  {
    return $this->count;
  }
  public function setMax(Google_Service_Dataflow_SplitInt64 $max)
  {
    $this->max = $max;
  }
  public function getMax()
  {
    return $this->max;
  }
  public function setMin(Google_Service_Dataflow_SplitInt64 $min)
  {
    $this->min = $min;
  }
  public function getMin()
  {
    return $this->min;
  }
  public function setSum(Google_Service_Dataflow_SplitInt64 $sum)
  {
    $this->sum = $sum;
  }
  public function getSum()
  {
    return $this->sum;
  }
  public function setSumOfSquares($sumOfSquares)
  {
    $this->sumOfSquares = $sumOfSquares;
  }
  public function getSumOfSquares()
  {
    return $this->sumOfSquares;
  }
}
