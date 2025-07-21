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

namespace Google\Service\BigtableAdmin;

class CreateMaterializedViewRequest extends \Google\Model
{
  protected $materializedViewType = MaterializedView::class;
  protected $materializedViewDataType = '';
  /**
   * @var string
   */
  public $materializedViewId;
  /**
   * @var string
   */
  public $parent;

  /**
   * @param MaterializedView
   */
  public function setMaterializedView(MaterializedView $materializedView)
  {
    $this->materializedView = $materializedView;
  }
  /**
   * @return MaterializedView
   */
  public function getMaterializedView()
  {
    return $this->materializedView;
  }
  /**
   * @param string
   */
  public function setMaterializedViewId($materializedViewId)
  {
    $this->materializedViewId = $materializedViewId;
  }
  /**
   * @return string
   */
  public function getMaterializedViewId()
  {
    return $this->materializedViewId;
  }
  /**
   * @param string
   */
  public function setParent($parent)
  {
    $this->parent = $parent;
  }
  /**
   * @return string
   */
  public function getParent()
  {
    return $this->parent;
  }
}

// Adding a class alias for backwards compatibility with the previous class name.
class_alias(CreateMaterializedViewRequest::class, 'Google_Service_BigtableAdmin_CreateMaterializedViewRequest');
