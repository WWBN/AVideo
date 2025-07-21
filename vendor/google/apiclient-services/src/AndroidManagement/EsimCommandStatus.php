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

namespace Google\Service\AndroidManagement;

class EsimCommandStatus extends \Google\Model
{
  protected $esimInfoType = EsimInfo::class;
  protected $esimInfoDataType = '';
  protected $internalErrorDetailsType = InternalErrorDetails::class;
  protected $internalErrorDetailsDataType = '';
  /**
   * @var string
   */
  public $status;

  /**
   * @param EsimInfo
   */
  public function setEsimInfo(EsimInfo $esimInfo)
  {
    $this->esimInfo = $esimInfo;
  }
  /**
   * @return EsimInfo
   */
  public function getEsimInfo()
  {
    return $this->esimInfo;
  }
  /**
   * @param InternalErrorDetails
   */
  public function setInternalErrorDetails(InternalErrorDetails $internalErrorDetails)
  {
    $this->internalErrorDetails = $internalErrorDetails;
  }
  /**
   * @return InternalErrorDetails
   */
  public function getInternalErrorDetails()
  {
    return $this->internalErrorDetails;
  }
  /**
   * @param string
   */
  public function setStatus($status)
  {
    $this->status = $status;
  }
  /**
   * @return string
   */
  public function getStatus()
  {
    return $this->status;
  }
}

// Adding a class alias for backwards compatibility with the previous class name.
class_alias(EsimCommandStatus::class, 'Google_Service_AndroidManagement_EsimCommandStatus');
