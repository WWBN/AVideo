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

class BuildSource extends \Google\Model
{
  protected $codebaseType = CodebaseSource::class;
  protected $codebaseDataType = '';
  protected $containerType = ContainerSource::class;
  protected $containerDataType = '';

  /**
   * @param CodebaseSource
   */
  public function setCodebase(CodebaseSource $codebase)
  {
    $this->codebase = $codebase;
  }
  /**
   * @return CodebaseSource
   */
  public function getCodebase()
  {
    return $this->codebase;
  }
  /**
   * @param ContainerSource
   */
  public function setContainer(ContainerSource $container)
  {
    $this->container = $container;
  }
  /**
   * @return ContainerSource
   */
  public function getContainer()
  {
    return $this->container;
  }
}

// Adding a class alias for backwards compatibility with the previous class name.
class_alias(BuildSource::class, 'Google_Service_FirebaseAppHosting_BuildSource');
