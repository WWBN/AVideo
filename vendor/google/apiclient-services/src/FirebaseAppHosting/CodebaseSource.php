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

namespace Google\Service\FirebaseAppHosting;

class CodebaseSource extends \Google\Model
{
  protected $authorType = UserMetadata::class;
  protected $authorDataType = '';
  /**
   * @var string
   */
  public $branch;
  /**
   * @var string
   */
  public $commit;
  /**
   * @var string
   */
  public $commitMessage;
  /**
   * @var string
   */
  public $commitTime;
  /**
   * @var string
   */
  public $displayName;
  /**
   * @var string
   */
  public $hash;
  /**
   * @var string
   */
  public $uri;

  /**
   * @param UserMetadata
   */
  public function setAuthor(UserMetadata $author)
  {
    $this->author = $author;
  }
  /**
   * @return UserMetadata
   */
  public function getAuthor()
  {
    return $this->author;
  }
  /**
   * @param string
   */
  public function setBranch($branch)
  {
    $this->branch = $branch;
  }
  /**
   * @return string
   */
  public function getBranch()
  {
    return $this->branch;
  }
  /**
   * @param string
   */
  public function setCommit($commit)
  {
    $this->commit = $commit;
  }
  /**
   * @return string
   */
  public function getCommit()
  {
    return $this->commit;
  }
  /**
   * @param string
   */
  public function setCommitMessage($commitMessage)
  {
    $this->commitMessage = $commitMessage;
  }
  /**
   * @return string
   */
  public function getCommitMessage()
  {
    return $this->commitMessage;
  }
  /**
   * @param string
   */
  public function setCommitTime($commitTime)
  {
    $this->commitTime = $commitTime;
  }
  /**
   * @return string
   */
  public function getCommitTime()
  {
    return $this->commitTime;
  }
  /**
   * @param string
   */
  public function setDisplayName($displayName)
  {
    $this->displayName = $displayName;
  }
  /**
   * @return string
   */
  public function getDisplayName()
  {
    return $this->displayName;
  }
  /**
   * @param string
   */
  public function setHash($hash)
  {
    $this->hash = $hash;
  }
  /**
   * @return string
   */
  public function getHash()
  {
    return $this->hash;
  }
  /**
   * @param string
   */
  public function setUri($uri)
  {
    $this->uri = $uri;
  }
  /**
   * @return string
   */
  public function getUri()
  {
    return $this->uri;
  }
}

// Adding a class alias for backwards compatibility with the previous class name.
class_alias(CodebaseSource::class, 'Google_Service_FirebaseAppHosting_CodebaseSource');
