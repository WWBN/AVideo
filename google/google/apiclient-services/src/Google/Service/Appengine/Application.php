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

class Google_Service_Appengine_Application extends Google_Collection
{
  protected $collection_key = 'dispatchRules';
  public $authDomain;
  public $codeBucket;
  public $defaultBucket;
  public $defaultCookieExpiration;
  public $defaultHostname;
  protected $dispatchRulesType = 'Google_Service_Appengine_UrlDispatchRule';
  protected $dispatchRulesDataType = 'array';
  public $id;
  public $locationId;
  public $name;

  public function setAuthDomain($authDomain)
  {
    $this->authDomain = $authDomain;
  }
  public function getAuthDomain()
  {
    return $this->authDomain;
  }
  public function setCodeBucket($codeBucket)
  {
    $this->codeBucket = $codeBucket;
  }
  public function getCodeBucket()
  {
    return $this->codeBucket;
  }
  public function setDefaultBucket($defaultBucket)
  {
    $this->defaultBucket = $defaultBucket;
  }
  public function getDefaultBucket()
  {
    return $this->defaultBucket;
  }
  public function setDefaultCookieExpiration($defaultCookieExpiration)
  {
    $this->defaultCookieExpiration = $defaultCookieExpiration;
  }
  public function getDefaultCookieExpiration()
  {
    return $this->defaultCookieExpiration;
  }
  public function setDefaultHostname($defaultHostname)
  {
    $this->defaultHostname = $defaultHostname;
  }
  public function getDefaultHostname()
  {
    return $this->defaultHostname;
  }
  public function setDispatchRules($dispatchRules)
  {
    $this->dispatchRules = $dispatchRules;
  }
  public function getDispatchRules()
  {
    return $this->dispatchRules;
  }
  public function setId($id)
  {
    $this->id = $id;
  }
  public function getId()
  {
    return $this->id;
  }
  public function setLocationId($locationId)
  {
    $this->locationId = $locationId;
  }
  public function getLocationId()
  {
    return $this->locationId;
  }
  public function setName($name)
  {
    $this->name = $name;
  }
  public function getName()
  {
    return $this->name;
  }
}
