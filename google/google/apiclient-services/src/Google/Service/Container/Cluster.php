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

class Google_Service_Container_Cluster extends Google_Collection
{
  protected $collection_key = 'nodePools';
  protected $addonsConfigType = 'Google_Service_Container_AddonsConfig';
  protected $addonsConfigDataType = '';
  public $clusterIpv4Cidr;
  public $createTime;
  public $currentMasterVersion;
  public $currentNodeCount;
  public $currentNodeVersion;
  public $description;
  public $enableKubernetesAlpha;
  public $endpoint;
  public $expireTime;
  public $initialClusterVersion;
  public $initialNodeCount;
  public $instanceGroupUrls;
  public $locations;
  public $loggingService;
  protected $masterAuthType = 'Google_Service_Container_MasterAuth';
  protected $masterAuthDataType = '';
  public $monitoringService;
  public $name;
  public $network;
  protected $nodeConfigType = 'Google_Service_Container_NodeConfig';
  protected $nodeConfigDataType = '';
  public $nodeIpv4CidrSize;
  protected $nodePoolsType = 'Google_Service_Container_NodePool';
  protected $nodePoolsDataType = 'array';
  public $selfLink;
  public $servicesIpv4Cidr;
  public $status;
  public $statusMessage;
  public $subnetwork;
  public $zone;

  public function setAddonsConfig(Google_Service_Container_AddonsConfig $addonsConfig)
  {
    $this->addonsConfig = $addonsConfig;
  }
  public function getAddonsConfig()
  {
    return $this->addonsConfig;
  }
  public function setClusterIpv4Cidr($clusterIpv4Cidr)
  {
    $this->clusterIpv4Cidr = $clusterIpv4Cidr;
  }
  public function getClusterIpv4Cidr()
  {
    return $this->clusterIpv4Cidr;
  }
  public function setCreateTime($createTime)
  {
    $this->createTime = $createTime;
  }
  public function getCreateTime()
  {
    return $this->createTime;
  }
  public function setCurrentMasterVersion($currentMasterVersion)
  {
    $this->currentMasterVersion = $currentMasterVersion;
  }
  public function getCurrentMasterVersion()
  {
    return $this->currentMasterVersion;
  }
  public function setCurrentNodeCount($currentNodeCount)
  {
    $this->currentNodeCount = $currentNodeCount;
  }
  public function getCurrentNodeCount()
  {
    return $this->currentNodeCount;
  }
  public function setCurrentNodeVersion($currentNodeVersion)
  {
    $this->currentNodeVersion = $currentNodeVersion;
  }
  public function getCurrentNodeVersion()
  {
    return $this->currentNodeVersion;
  }
  public function setDescription($description)
  {
    $this->description = $description;
  }
  public function getDescription()
  {
    return $this->description;
  }
  public function setEnableKubernetesAlpha($enableKubernetesAlpha)
  {
    $this->enableKubernetesAlpha = $enableKubernetesAlpha;
  }
  public function getEnableKubernetesAlpha()
  {
    return $this->enableKubernetesAlpha;
  }
  public function setEndpoint($endpoint)
  {
    $this->endpoint = $endpoint;
  }
  public function getEndpoint()
  {
    return $this->endpoint;
  }
  public function setExpireTime($expireTime)
  {
    $this->expireTime = $expireTime;
  }
  public function getExpireTime()
  {
    return $this->expireTime;
  }
  public function setInitialClusterVersion($initialClusterVersion)
  {
    $this->initialClusterVersion = $initialClusterVersion;
  }
  public function getInitialClusterVersion()
  {
    return $this->initialClusterVersion;
  }
  public function setInitialNodeCount($initialNodeCount)
  {
    $this->initialNodeCount = $initialNodeCount;
  }
  public function getInitialNodeCount()
  {
    return $this->initialNodeCount;
  }
  public function setInstanceGroupUrls($instanceGroupUrls)
  {
    $this->instanceGroupUrls = $instanceGroupUrls;
  }
  public function getInstanceGroupUrls()
  {
    return $this->instanceGroupUrls;
  }
  public function setLocations($locations)
  {
    $this->locations = $locations;
  }
  public function getLocations()
  {
    return $this->locations;
  }
  public function setLoggingService($loggingService)
  {
    $this->loggingService = $loggingService;
  }
  public function getLoggingService()
  {
    return $this->loggingService;
  }
  public function setMasterAuth(Google_Service_Container_MasterAuth $masterAuth)
  {
    $this->masterAuth = $masterAuth;
  }
  public function getMasterAuth()
  {
    return $this->masterAuth;
  }
  public function setMonitoringService($monitoringService)
  {
    $this->monitoringService = $monitoringService;
  }
  public function getMonitoringService()
  {
    return $this->monitoringService;
  }
  public function setName($name)
  {
    $this->name = $name;
  }
  public function getName()
  {
    return $this->name;
  }
  public function setNetwork($network)
  {
    $this->network = $network;
  }
  public function getNetwork()
  {
    return $this->network;
  }
  public function setNodeConfig(Google_Service_Container_NodeConfig $nodeConfig)
  {
    $this->nodeConfig = $nodeConfig;
  }
  public function getNodeConfig()
  {
    return $this->nodeConfig;
  }
  public function setNodeIpv4CidrSize($nodeIpv4CidrSize)
  {
    $this->nodeIpv4CidrSize = $nodeIpv4CidrSize;
  }
  public function getNodeIpv4CidrSize()
  {
    return $this->nodeIpv4CidrSize;
  }
  public function setNodePools($nodePools)
  {
    $this->nodePools = $nodePools;
  }
  public function getNodePools()
  {
    return $this->nodePools;
  }
  public function setSelfLink($selfLink)
  {
    $this->selfLink = $selfLink;
  }
  public function getSelfLink()
  {
    return $this->selfLink;
  }
  public function setServicesIpv4Cidr($servicesIpv4Cidr)
  {
    $this->servicesIpv4Cidr = $servicesIpv4Cidr;
  }
  public function getServicesIpv4Cidr()
  {
    return $this->servicesIpv4Cidr;
  }
  public function setStatus($status)
  {
    $this->status = $status;
  }
  public function getStatus()
  {
    return $this->status;
  }
  public function setStatusMessage($statusMessage)
  {
    $this->statusMessage = $statusMessage;
  }
  public function getStatusMessage()
  {
    return $this->statusMessage;
  }
  public function setSubnetwork($subnetwork)
  {
    $this->subnetwork = $subnetwork;
  }
  public function getSubnetwork()
  {
    return $this->subnetwork;
  }
  public function setZone($zone)
  {
    $this->zone = $zone;
  }
  public function getZone()
  {
    return $this->zone;
  }
}
