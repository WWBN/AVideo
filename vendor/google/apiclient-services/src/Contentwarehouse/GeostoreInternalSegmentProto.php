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

class GeostoreInternalSegmentProto extends \Google\Collection
{
  protected $collection_key = 'travelAllowance';
  protected $disallowedConnectionsType = GeostoreInternalSegmentProtoLaneConnectionReference::class;
  protected $disallowedConnectionsDataType = 'array';
  protected $disallowedPrimaryConnectionType = GeostoreInternalSegmentProtoLaneConnectionReference::class;
  protected $disallowedPrimaryConnectionDataType = 'array';
  protected $travelAllowanceType = GeostoreRestrictionProto::class;
  protected $travelAllowanceDataType = 'array';

  /**
   * @param GeostoreInternalSegmentProtoLaneConnectionReference[]
   */
  public function setDisallowedConnections($disallowedConnections)
  {
    $this->disallowedConnections = $disallowedConnections;
  }
  /**
   * @return GeostoreInternalSegmentProtoLaneConnectionReference[]
   */
  public function getDisallowedConnections()
  {
    return $this->disallowedConnections;
  }
  /**
   * @param GeostoreInternalSegmentProtoLaneConnectionReference[]
   */
  public function setDisallowedPrimaryConnection($disallowedPrimaryConnection)
  {
    $this->disallowedPrimaryConnection = $disallowedPrimaryConnection;
  }
  /**
   * @return GeostoreInternalSegmentProtoLaneConnectionReference[]
   */
  public function getDisallowedPrimaryConnection()
  {
    return $this->disallowedPrimaryConnection;
  }
  /**
   * @param GeostoreRestrictionProto[]
   */
  public function setTravelAllowance($travelAllowance)
  {
    $this->travelAllowance = $travelAllowance;
  }
  /**
   * @return GeostoreRestrictionProto[]
   */
  public function getTravelAllowance()
  {
    return $this->travelAllowance;
  }
}

// Adding a class alias for backwards compatibility with the previous class name.
class_alias(GeostoreInternalSegmentProto::class, 'Google_Service_Contentwarehouse_GeostoreInternalSegmentProto');
