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

class YoutubeCommentsApiCommentModeratedRestriction extends \Google\Collection
{
  protected $collection_key = 'autoModEnforcements';
  protected $autoModEnforcementsType = YoutubeCommentsApiCommentModeratedRestrictionAutoModDecisionEnforcement::class;
  protected $autoModEnforcementsDataType = 'array';
  /**
   * @var string
   */
  public $createTime;
  /**
   * @var string
   */
  public $externalCommentId;
  protected $issuerType = YoutubeCommentsApiCommentRestrictionIssuer::class;
  protected $issuerDataType = '';
  protected $reasonType = YoutubeCommentsApiCommentRestrictionReason::class;
  protected $reasonDataType = '';
  /**
   * @var bool
   */
  public $reviewable;
  /**
   * @var string
   */
  public $status;
  /**
   * @var string
   */
  public $updateTime;

  /**
   * @param YoutubeCommentsApiCommentModeratedRestrictionAutoModDecisionEnforcement[]
   */
  public function setAutoModEnforcements($autoModEnforcements)
  {
    $this->autoModEnforcements = $autoModEnforcements;
  }
  /**
   * @return YoutubeCommentsApiCommentModeratedRestrictionAutoModDecisionEnforcement[]
   */
  public function getAutoModEnforcements()
  {
    return $this->autoModEnforcements;
  }
  /**
   * @param string
   */
  public function setCreateTime($createTime)
  {
    $this->createTime = $createTime;
  }
  /**
   * @return string
   */
  public function getCreateTime()
  {
    return $this->createTime;
  }
  /**
   * @param string
   */
  public function setExternalCommentId($externalCommentId)
  {
    $this->externalCommentId = $externalCommentId;
  }
  /**
   * @return string
   */
  public function getExternalCommentId()
  {
    return $this->externalCommentId;
  }
  /**
   * @param YoutubeCommentsApiCommentRestrictionIssuer
   */
  public function setIssuer(YoutubeCommentsApiCommentRestrictionIssuer $issuer)
  {
    $this->issuer = $issuer;
  }
  /**
   * @return YoutubeCommentsApiCommentRestrictionIssuer
   */
  public function getIssuer()
  {
    return $this->issuer;
  }
  /**
   * @param YoutubeCommentsApiCommentRestrictionReason
   */
  public function setReason(YoutubeCommentsApiCommentRestrictionReason $reason)
  {
    $this->reason = $reason;
  }
  /**
   * @return YoutubeCommentsApiCommentRestrictionReason
   */
  public function getReason()
  {
    return $this->reason;
  }
  /**
   * @param bool
   */
  public function setReviewable($reviewable)
  {
    $this->reviewable = $reviewable;
  }
  /**
   * @return bool
   */
  public function getReviewable()
  {
    return $this->reviewable;
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
  /**
   * @param string
   */
  public function setUpdateTime($updateTime)
  {
    $this->updateTime = $updateTime;
  }
  /**
   * @return string
   */
  public function getUpdateTime()
  {
    return $this->updateTime;
  }
}

// Adding a class alias for backwards compatibility with the previous class name.
class_alias(YoutubeCommentsApiCommentModeratedRestriction::class, 'Google_Service_Contentwarehouse_YoutubeCommentsApiCommentModeratedRestriction');
