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

namespace Google\Service\DiscoveryEngine;

class GoogleCloudDiscoveryengineV1alphaDataConnector extends \Google\Collection
{
  protected $collection_key = 'staticIpAddresses';
  /**
   * @var bool
   */
  public $aclEnabled;
  protected $actionConfigType = GoogleCloudDiscoveryengineV1alphaActionConfig::class;
  protected $actionConfigDataType = '';
  protected $alertPolicyConfigsType = GoogleCloudDiscoveryengineV1alphaAlertPolicyConfig::class;
  protected $alertPolicyConfigsDataType = 'array';
  /**
   * @var bool
   */
  public $autoRunDisabled;
  protected $bapConfigType = GoogleCloudDiscoveryengineV1alphaBAPConfig::class;
  protected $bapConfigDataType = '';
  /**
   * @var string[]
   */
  public $blockingReasons;
  /**
   * @var string[]
   */
  public $connectorModes;
  /**
   * @var string
   */
  public $connectorType;
  /**
   * @var bool
   */
  public $createEuaSaas;
  /**
   * @var string
   */
  public $createTime;
  /**
   * @var string
   */
  public $dataSource;
  protected $destinationConfigsType = GoogleCloudDiscoveryengineV1alphaDestinationConfig::class;
  protected $destinationConfigsDataType = 'array';
  protected $endUserConfigType = GoogleCloudDiscoveryengineV1alphaDataConnectorEndUserConfig::class;
  protected $endUserConfigDataType = '';
  protected $entitiesType = GoogleCloudDiscoveryengineV1alphaDataConnectorSourceEntity::class;
  protected $entitiesDataType = 'array';
  protected $errorsType = GoogleRpcStatus::class;
  protected $errorsDataType = 'array';
  /**
   * @var string
   */
  public $identityRefreshInterval;
  protected $identityScheduleConfigType = GoogleCloudDiscoveryengineV1alphaIdentityScheduleConfig::class;
  protected $identityScheduleConfigDataType = '';
  /**
   * @var string
   */
  public $incrementalRefreshInterval;
  /**
   * @var bool
   */
  public $incrementalSyncDisabled;
  /**
   * @var string
   */
  public $kmsKeyName;
  /**
   * @var string
   */
  public $lastSyncTime;
  /**
   * @var string
   */
  public $latestPauseTime;
  /**
   * @var string
   */
  public $name;
  protected $nextSyncTimeType = GoogleTypeDateTime::class;
  protected $nextSyncTimeDataType = '';
  /**
   * @var array[]
   */
  public $params;
  /**
   * @var string
   */
  public $privateConnectivityProjectId;
  /**
   * @var string
   */
  public $realtimeState;
  protected $realtimeSyncConfigType = GoogleCloudDiscoveryengineV1alphaDataConnectorRealtimeSyncConfig::class;
  protected $realtimeSyncConfigDataType = '';
  /**
   * @var string
   */
  public $refreshInterval;
  /**
   * @var string
   */
  public $state;
  /**
   * @var string[]
   */
  public $staticIpAddresses;
  /**
   * @var bool
   */
  public $staticIpEnabled;
  /**
   * @var string
   */
  public $syncMode;
  /**
   * @var string
   */
  public $updateTime;

  /**
   * @param bool
   */
  public function setAclEnabled($aclEnabled)
  {
    $this->aclEnabled = $aclEnabled;
  }
  /**
   * @return bool
   */
  public function getAclEnabled()
  {
    return $this->aclEnabled;
  }
  /**
   * @param GoogleCloudDiscoveryengineV1alphaActionConfig
   */
  public function setActionConfig(GoogleCloudDiscoveryengineV1alphaActionConfig $actionConfig)
  {
    $this->actionConfig = $actionConfig;
  }
  /**
   * @return GoogleCloudDiscoveryengineV1alphaActionConfig
   */
  public function getActionConfig()
  {
    return $this->actionConfig;
  }
  /**
   * @param GoogleCloudDiscoveryengineV1alphaAlertPolicyConfig[]
   */
  public function setAlertPolicyConfigs($alertPolicyConfigs)
  {
    $this->alertPolicyConfigs = $alertPolicyConfigs;
  }
  /**
   * @return GoogleCloudDiscoveryengineV1alphaAlertPolicyConfig[]
   */
  public function getAlertPolicyConfigs()
  {
    return $this->alertPolicyConfigs;
  }
  /**
   * @param bool
   */
  public function setAutoRunDisabled($autoRunDisabled)
  {
    $this->autoRunDisabled = $autoRunDisabled;
  }
  /**
   * @return bool
   */
  public function getAutoRunDisabled()
  {
    return $this->autoRunDisabled;
  }
  /**
   * @param GoogleCloudDiscoveryengineV1alphaBAPConfig
   */
  public function setBapConfig(GoogleCloudDiscoveryengineV1alphaBAPConfig $bapConfig)
  {
    $this->bapConfig = $bapConfig;
  }
  /**
   * @return GoogleCloudDiscoveryengineV1alphaBAPConfig
   */
  public function getBapConfig()
  {
    return $this->bapConfig;
  }
  /**
   * @param string[]
   */
  public function setBlockingReasons($blockingReasons)
  {
    $this->blockingReasons = $blockingReasons;
  }
  /**
   * @return string[]
   */
  public function getBlockingReasons()
  {
    return $this->blockingReasons;
  }
  /**
   * @param string[]
   */
  public function setConnectorModes($connectorModes)
  {
    $this->connectorModes = $connectorModes;
  }
  /**
   * @return string[]
   */
  public function getConnectorModes()
  {
    return $this->connectorModes;
  }
  /**
   * @param string
   */
  public function setConnectorType($connectorType)
  {
    $this->connectorType = $connectorType;
  }
  /**
   * @return string
   */
  public function getConnectorType()
  {
    return $this->connectorType;
  }
  /**
   * @param bool
   */
  public function setCreateEuaSaas($createEuaSaas)
  {
    $this->createEuaSaas = $createEuaSaas;
  }
  /**
   * @return bool
   */
  public function getCreateEuaSaas()
  {
    return $this->createEuaSaas;
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
  public function setDataSource($dataSource)
  {
    $this->dataSource = $dataSource;
  }
  /**
   * @return string
   */
  public function getDataSource()
  {
    return $this->dataSource;
  }
  /**
   * @param GoogleCloudDiscoveryengineV1alphaDestinationConfig[]
   */
  public function setDestinationConfigs($destinationConfigs)
  {
    $this->destinationConfigs = $destinationConfigs;
  }
  /**
   * @return GoogleCloudDiscoveryengineV1alphaDestinationConfig[]
   */
  public function getDestinationConfigs()
  {
    return $this->destinationConfigs;
  }
  /**
   * @param GoogleCloudDiscoveryengineV1alphaDataConnectorEndUserConfig
   */
  public function setEndUserConfig(GoogleCloudDiscoveryengineV1alphaDataConnectorEndUserConfig $endUserConfig)
  {
    $this->endUserConfig = $endUserConfig;
  }
  /**
   * @return GoogleCloudDiscoveryengineV1alphaDataConnectorEndUserConfig
   */
  public function getEndUserConfig()
  {
    return $this->endUserConfig;
  }
  /**
   * @param GoogleCloudDiscoveryengineV1alphaDataConnectorSourceEntity[]
   */
  public function setEntities($entities)
  {
    $this->entities = $entities;
  }
  /**
   * @return GoogleCloudDiscoveryengineV1alphaDataConnectorSourceEntity[]
   */
  public function getEntities()
  {
    return $this->entities;
  }
  /**
   * @param GoogleRpcStatus[]
   */
  public function setErrors($errors)
  {
    $this->errors = $errors;
  }
  /**
   * @return GoogleRpcStatus[]
   */
  public function getErrors()
  {
    return $this->errors;
  }
  /**
   * @param string
   */
  public function setIdentityRefreshInterval($identityRefreshInterval)
  {
    $this->identityRefreshInterval = $identityRefreshInterval;
  }
  /**
   * @return string
   */
  public function getIdentityRefreshInterval()
  {
    return $this->identityRefreshInterval;
  }
  /**
   * @param GoogleCloudDiscoveryengineV1alphaIdentityScheduleConfig
   */
  public function setIdentityScheduleConfig(GoogleCloudDiscoveryengineV1alphaIdentityScheduleConfig $identityScheduleConfig)
  {
    $this->identityScheduleConfig = $identityScheduleConfig;
  }
  /**
   * @return GoogleCloudDiscoveryengineV1alphaIdentityScheduleConfig
   */
  public function getIdentityScheduleConfig()
  {
    return $this->identityScheduleConfig;
  }
  /**
   * @param string
   */
  public function setIncrementalRefreshInterval($incrementalRefreshInterval)
  {
    $this->incrementalRefreshInterval = $incrementalRefreshInterval;
  }
  /**
   * @return string
   */
  public function getIncrementalRefreshInterval()
  {
    return $this->incrementalRefreshInterval;
  }
  /**
   * @param bool
   */
  public function setIncrementalSyncDisabled($incrementalSyncDisabled)
  {
    $this->incrementalSyncDisabled = $incrementalSyncDisabled;
  }
  /**
   * @return bool
   */
  public function getIncrementalSyncDisabled()
  {
    return $this->incrementalSyncDisabled;
  }
  /**
   * @param string
   */
  public function setKmsKeyName($kmsKeyName)
  {
    $this->kmsKeyName = $kmsKeyName;
  }
  /**
   * @return string
   */
  public function getKmsKeyName()
  {
    return $this->kmsKeyName;
  }
  /**
   * @param string
   */
  public function setLastSyncTime($lastSyncTime)
  {
    $this->lastSyncTime = $lastSyncTime;
  }
  /**
   * @return string
   */
  public function getLastSyncTime()
  {
    return $this->lastSyncTime;
  }
  /**
   * @param string
   */
  public function setLatestPauseTime($latestPauseTime)
  {
    $this->latestPauseTime = $latestPauseTime;
  }
  /**
   * @return string
   */
  public function getLatestPauseTime()
  {
    return $this->latestPauseTime;
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
   * @param GoogleTypeDateTime
   */
  public function setNextSyncTime(GoogleTypeDateTime $nextSyncTime)
  {
    $this->nextSyncTime = $nextSyncTime;
  }
  /**
   * @return GoogleTypeDateTime
   */
  public function getNextSyncTime()
  {
    return $this->nextSyncTime;
  }
  /**
   * @param array[]
   */
  public function setParams($params)
  {
    $this->params = $params;
  }
  /**
   * @return array[]
   */
  public function getParams()
  {
    return $this->params;
  }
  /**
   * @param string
   */
  public function setPrivateConnectivityProjectId($privateConnectivityProjectId)
  {
    $this->privateConnectivityProjectId = $privateConnectivityProjectId;
  }
  /**
   * @return string
   */
  public function getPrivateConnectivityProjectId()
  {
    return $this->privateConnectivityProjectId;
  }
  /**
   * @param string
   */
  public function setRealtimeState($realtimeState)
  {
    $this->realtimeState = $realtimeState;
  }
  /**
   * @return string
   */
  public function getRealtimeState()
  {
    return $this->realtimeState;
  }
  /**
   * @param GoogleCloudDiscoveryengineV1alphaDataConnectorRealtimeSyncConfig
   */
  public function setRealtimeSyncConfig(GoogleCloudDiscoveryengineV1alphaDataConnectorRealtimeSyncConfig $realtimeSyncConfig)
  {
    $this->realtimeSyncConfig = $realtimeSyncConfig;
  }
  /**
   * @return GoogleCloudDiscoveryengineV1alphaDataConnectorRealtimeSyncConfig
   */
  public function getRealtimeSyncConfig()
  {
    return $this->realtimeSyncConfig;
  }
  /**
   * @param string
   */
  public function setRefreshInterval($refreshInterval)
  {
    $this->refreshInterval = $refreshInterval;
  }
  /**
   * @return string
   */
  public function getRefreshInterval()
  {
    return $this->refreshInterval;
  }
  /**
   * @param string
   */
  public function setState($state)
  {
    $this->state = $state;
  }
  /**
   * @return string
   */
  public function getState()
  {
    return $this->state;
  }
  /**
   * @param string[]
   */
  public function setStaticIpAddresses($staticIpAddresses)
  {
    $this->staticIpAddresses = $staticIpAddresses;
  }
  /**
   * @return string[]
   */
  public function getStaticIpAddresses()
  {
    return $this->staticIpAddresses;
  }
  /**
   * @param bool
   */
  public function setStaticIpEnabled($staticIpEnabled)
  {
    $this->staticIpEnabled = $staticIpEnabled;
  }
  /**
   * @return bool
   */
  public function getStaticIpEnabled()
  {
    return $this->staticIpEnabled;
  }
  /**
   * @param string
   */
  public function setSyncMode($syncMode)
  {
    $this->syncMode = $syncMode;
  }
  /**
   * @return string
   */
  public function getSyncMode()
  {
    return $this->syncMode;
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
class_alias(GoogleCloudDiscoveryengineV1alphaDataConnector::class, 'Google_Service_DiscoveryEngine_GoogleCloudDiscoveryengineV1alphaDataConnector');
