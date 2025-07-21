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

namespace Google\Service\StorageBatchOperations;

class DeleteObject extends \Google\Model
{
  /**
   * @var bool
   */
  public $permanentObjectDeletionEnabled;

  /**
   * @param bool
   */
  public function setPermanentObjectDeletionEnabled($permanentObjectDeletionEnabled)
  {
    $this->permanentObjectDeletionEnabled = $permanentObjectDeletionEnabled;
  }
  /**
   * @return bool
   */
  public function getPermanentObjectDeletionEnabled()
  {
    return $this->permanentObjectDeletionEnabled;
  }
}

// Adding a class alias for backwards compatibility with the previous class name.
class_alias(DeleteObject::class, 'Google_Service_StorageBatchOperations_DeleteObject');
