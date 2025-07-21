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

class Backend extends \Google\Collection
{
  protected $collection_key = 'managedResources';
  /**
   * @var string[]
   */
  public $annotations;
  /**
   * @var string
   */
  public $appId;
  protected $codebaseType = Codebase::class;
  protected $codebaseDataType = '';
  /**
   * @var string
   */
  public $createTime;
  /**
   * @var string
   */
  public $deleteTime;
  /**
   * @var string
   */
  public $displayName;
  /**
   * @var string
   */
  public $environment;
  /**
   * @var string
   */
  public $etag;
  /**
   * @var string[]
   */
  public $labels;
  protected $managedResourcesType = ManagedResource::class;
  protected $managedResourcesDataType = 'array';
  /**
   * @var string
   */
  public $mode;
  /**
   * @var string
   */
  public $name;
  /**
   * @var bool
   */
  public $reconciling;
  /**
   * @var string
   */
  public $serviceAccount;
  /**
   * @var string
   */
  public $servingLocality;
  /**
   * @var string
   */
  public $uid;
  /**
   * @var string
   */
  public $updateTime;
  /**
   * @var string
   */
  public $uri;

  /**
   * @param string[]
   */
  public function setAnnotations($annotations)
  {
    $this->annotations = $annotations;
  }
  /**
   * @return string[]
   */
  public function getAnnotations()
  {
    return $this->annotations;
  }
  /**
   * @param string
   */
  public function setAppId($appId)
  {
    $this->appId = $appId;
  }
  /**
   * @return string
   */
  public function getAppId()
  {
    return $this->appId;
  }
  /**
   * @param Codebase
   */
  public function setCodebase(Codebase $codebase)
  {
    $this->codebase = $codebase;
  }
  /**
   * @return Codebase
   */
  public function getCodebase()
  {
    return $this->codebase;
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
  public function setDeleteTime($deleteTime)
  {
    $this->deleteTime = $deleteTime;
  }
  /**
   * @return string
   */
  public function getDeleteTime()
  {
    return $this->deleteTime;
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
  public function setEnvironment($environment)
  {
    $this->environment = $environment;
  }
  /**
   * @return string
   */
  public function getEnvironment()
  {
    return $this->environment;
  }
  /**
   * @param string
   */
  public function setEtag($etag)
  {
    $this->etag = $etag;
  }
  /**
   * @return string
   */
  public function getEtag()
  {
    return $this->etag;
  }
  /**
   * @param string[]
   */
  public function setLabels($labels)
  {
    $this->labels = $labels;
  }
  /**
   * @return string[]
   */
  public function getLabels()
  {
    return $this->labels;
  }
  /**
   * @param ManagedResource[]
   */
  public function setManagedResources($managedResources)
  {
    $this->managedResources = $managedResources;
  }
  /**
   * @return ManagedResource[]
   */
  public function getManagedResources()
  {
    return $this->managedResources;
  }
  /**
   * @param string
   */
  public function setMode($mode)
  {
    $this->mode = $mode;
  }
  /**
   * @return string
   */
  public function getMode()
  {
    return $this->mode;
  }
  /**
   * @param string
   */
  public function setName($name)
  {
    $this->name = $name;
  }
  /**
   * @return string
   */
  public function getName()
  {
    return $this->name;
  }
  /**
   * @param bool
   */
  public function setReconciling($reconciling)
  {
    $this->reconciling = $reconciling;
  }
  /**
   * @return bool
   */
  public function getReconciling()
  {
    return $this->reconciling;
  }
  /**
   * @param string
   */
  public function setServiceAccount($serviceAccount)
  {
    $this->serviceAccount = $serviceAccount;
  }
  /**
   * @return string
   */
  public function getServiceAccount()
  {
    return $this->serviceAccount;
  }
  /**
   * @param string
   */
  public function setServingLocality($servingLocality)
  {
    $this->servingLocality = $servingLocality;
  }
  /**
   * @return string
   */
  public function getServingLocality()
  {
    return $this->servingLocality;
  }
  /**
   * @param string
   */
  public function setUid($uid)
  {
    $this->uid = $uid;
  }
  /**
   * @return string
   */
  public function getUid()
  {
    return $this->uid;
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
class_alias(Backend::class, 'Google_Service_FirebaseAppHosting_Backend');
