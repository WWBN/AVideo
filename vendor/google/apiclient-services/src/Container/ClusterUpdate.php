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

namespace Google\Service\Container;

class ClusterUpdate extends \Google\Collection
{
  protected $collection_key = 'desiredLocations';
  protected $desiredAddonsConfigType = AddonsConfig::class;
  protected $desiredAddonsConfigDataType = '';
  public $desiredAddonsConfig;
  protected $desiredAuthenticatorGroupsConfigType = AuthenticatorGroupsConfig::class;
  protected $desiredAuthenticatorGroupsConfigDataType = '';
  public $desiredAuthenticatorGroupsConfig;
  protected $desiredBinaryAuthorizationType = BinaryAuthorization::class;
  protected $desiredBinaryAuthorizationDataType = '';
  public $desiredBinaryAuthorization;
  protected $desiredClusterAutoscalingType = ClusterAutoscaling::class;
  protected $desiredClusterAutoscalingDataType = '';
  public $desiredClusterAutoscaling;
  protected $desiredCostManagementConfigType = CostManagementConfig::class;
  protected $desiredCostManagementConfigDataType = '';
  public $desiredCostManagementConfig;
  protected $desiredDatabaseEncryptionType = DatabaseEncryption::class;
  protected $desiredDatabaseEncryptionDataType = '';
  public $desiredDatabaseEncryption;
  /**
   * @var string
   */
  public $desiredDatapathProvider;
  protected $desiredDefaultSnatStatusType = DefaultSnatStatus::class;
  protected $desiredDefaultSnatStatusDataType = '';
  public $desiredDefaultSnatStatus;
  protected $desiredDnsConfigType = DNSConfig::class;
  protected $desiredDnsConfigDataType = '';
  public $desiredDnsConfig;
  /**
   * @var bool
   */
  public $desiredEnablePrivateEndpoint;
  protected $desiredGatewayApiConfigType = GatewayAPIConfig::class;
  protected $desiredGatewayApiConfigDataType = '';
  public $desiredGatewayApiConfig;
  protected $desiredGcfsConfigType = GcfsConfig::class;
  protected $desiredGcfsConfigDataType = '';
  public $desiredGcfsConfig;
  protected $desiredIdentityServiceConfigType = IdentityServiceConfig::class;
  protected $desiredIdentityServiceConfigDataType = '';
  public $desiredIdentityServiceConfig;
  /**
   * @var string
   */
  public $desiredImageType;
  protected $desiredIntraNodeVisibilityConfigType = IntraNodeVisibilityConfig::class;
  protected $desiredIntraNodeVisibilityConfigDataType = '';
  public $desiredIntraNodeVisibilityConfig;
  protected $desiredL4ilbSubsettingConfigType = ILBSubsettingConfig::class;
  protected $desiredL4ilbSubsettingConfigDataType = '';
  public $desiredL4ilbSubsettingConfig;
  /**
   * @var string[]
   */
  public $desiredLocations;
  protected $desiredLoggingConfigType = LoggingConfig::class;
  protected $desiredLoggingConfigDataType = '';
  public $desiredLoggingConfig;
  /**
   * @var string
   */
  public $desiredLoggingService;
  protected $desiredMasterAuthorizedNetworksConfigType = MasterAuthorizedNetworksConfig::class;
  protected $desiredMasterAuthorizedNetworksConfigDataType = '';
  public $desiredMasterAuthorizedNetworksConfig;
  /**
   * @var string
   */
  public $desiredMasterVersion;
  protected $desiredMeshCertificatesType = MeshCertificates::class;
  protected $desiredMeshCertificatesDataType = '';
  public $desiredMeshCertificates;
  protected $desiredMonitoringConfigType = MonitoringConfig::class;
  protected $desiredMonitoringConfigDataType = '';
  public $desiredMonitoringConfig;
  /**
   * @var string
   */
  public $desiredMonitoringService;
  protected $desiredNodePoolAutoConfigNetworkTagsType = NetworkTags::class;
  protected $desiredNodePoolAutoConfigNetworkTagsDataType = '';
  public $desiredNodePoolAutoConfigNetworkTags;
  protected $desiredNodePoolAutoscalingType = NodePoolAutoscaling::class;
  protected $desiredNodePoolAutoscalingDataType = '';
  public $desiredNodePoolAutoscaling;
  /**
   * @var string
   */
  public $desiredNodePoolId;
  protected $desiredNodePoolLoggingConfigType = NodePoolLoggingConfig::class;
  protected $desiredNodePoolLoggingConfigDataType = '';
  public $desiredNodePoolLoggingConfig;
  /**
   * @var string
   */
  public $desiredNodeVersion;
  protected $desiredNotificationConfigType = NotificationConfig::class;
  protected $desiredNotificationConfigDataType = '';
  public $desiredNotificationConfig;
  protected $desiredPrivateClusterConfigType = PrivateClusterConfig::class;
  protected $desiredPrivateClusterConfigDataType = '';
  public $desiredPrivateClusterConfig;
  /**
   * @var string
   */
  public $desiredPrivateIpv6GoogleAccess;
  protected $desiredReleaseChannelType = ReleaseChannel::class;
  protected $desiredReleaseChannelDataType = '';
  public $desiredReleaseChannel;
  protected $desiredResourceUsageExportConfigType = ResourceUsageExportConfig::class;
  protected $desiredResourceUsageExportConfigDataType = '';
  public $desiredResourceUsageExportConfig;
  protected $desiredServiceExternalIpsConfigType = ServiceExternalIPsConfig::class;
  protected $desiredServiceExternalIpsConfigDataType = '';
  public $desiredServiceExternalIpsConfig;
  protected $desiredShieldedNodesType = ShieldedNodes::class;
  protected $desiredShieldedNodesDataType = '';
  public $desiredShieldedNodes;
  /**
   * @var string
   */
  public $desiredStackType;
  protected $desiredVerticalPodAutoscalingType = VerticalPodAutoscaling::class;
  protected $desiredVerticalPodAutoscalingDataType = '';
  public $desiredVerticalPodAutoscaling;
  protected $desiredWorkloadIdentityConfigType = WorkloadIdentityConfig::class;
  protected $desiredWorkloadIdentityConfigDataType = '';
  public $desiredWorkloadIdentityConfig;
  /**
   * @var string
   */
  public $etag;

  /**
   * @param AddonsConfig
   */
  public function setDesiredAddonsConfig(AddonsConfig $desiredAddonsConfig)
  {
    $this->desiredAddonsConfig = $desiredAddonsConfig;
  }
  /**
   * @return AddonsConfig
   */
  public function getDesiredAddonsConfig()
  {
    return $this->desiredAddonsConfig;
  }
  /**
   * @param AuthenticatorGroupsConfig
   */
  public function setDesiredAuthenticatorGroupsConfig(AuthenticatorGroupsConfig $desiredAuthenticatorGroupsConfig)
  {
    $this->desiredAuthenticatorGroupsConfig = $desiredAuthenticatorGroupsConfig;
  }
  /**
   * @return AuthenticatorGroupsConfig
   */
  public function getDesiredAuthenticatorGroupsConfig()
  {
    return $this->desiredAuthenticatorGroupsConfig;
  }
  /**
   * @param BinaryAuthorization
   */
  public function setDesiredBinaryAuthorization(BinaryAuthorization $desiredBinaryAuthorization)
  {
    $this->desiredBinaryAuthorization = $desiredBinaryAuthorization;
  }
  /**
   * @return BinaryAuthorization
   */
  public function getDesiredBinaryAuthorization()
  {
    return $this->desiredBinaryAuthorization;
  }
  /**
   * @param ClusterAutoscaling
   */
  public function setDesiredClusterAutoscaling(ClusterAutoscaling $desiredClusterAutoscaling)
  {
    $this->desiredClusterAutoscaling = $desiredClusterAutoscaling;
  }
  /**
   * @return ClusterAutoscaling
   */
  public function getDesiredClusterAutoscaling()
  {
    return $this->desiredClusterAutoscaling;
  }
  /**
   * @param CostManagementConfig
   */
  public function setDesiredCostManagementConfig(CostManagementConfig $desiredCostManagementConfig)
  {
    $this->desiredCostManagementConfig = $desiredCostManagementConfig;
  }
  /**
   * @return CostManagementConfig
   */
  public function getDesiredCostManagementConfig()
  {
    return $this->desiredCostManagementConfig;
  }
  /**
   * @param DatabaseEncryption
   */
  public function setDesiredDatabaseEncryption(DatabaseEncryption $desiredDatabaseEncryption)
  {
    $this->desiredDatabaseEncryption = $desiredDatabaseEncryption;
  }
  /**
   * @return DatabaseEncryption
   */
  public function getDesiredDatabaseEncryption()
  {
    return $this->desiredDatabaseEncryption;
  }
  /**
   * @param string
   */
  public function setDesiredDatapathProvider($desiredDatapathProvider)
  {
    $this->desiredDatapathProvider = $desiredDatapathProvider;
  }
  /**
   * @return string
   */
  public function getDesiredDatapathProvider()
  {
    return $this->desiredDatapathProvider;
  }
  /**
   * @param DefaultSnatStatus
   */
  public function setDesiredDefaultSnatStatus(DefaultSnatStatus $desiredDefaultSnatStatus)
  {
    $this->desiredDefaultSnatStatus = $desiredDefaultSnatStatus;
  }
  /**
   * @return DefaultSnatStatus
   */
  public function getDesiredDefaultSnatStatus()
  {
    return $this->desiredDefaultSnatStatus;
  }
  /**
   * @param DNSConfig
   */
  public function setDesiredDnsConfig(DNSConfig $desiredDnsConfig)
  {
    $this->desiredDnsConfig = $desiredDnsConfig;
  }
  /**
   * @return DNSConfig
   */
  public function getDesiredDnsConfig()
  {
    return $this->desiredDnsConfig;
  }
  /**
   * @param bool
   */
  public function setDesiredEnablePrivateEndpoint($desiredEnablePrivateEndpoint)
  {
    $this->desiredEnablePrivateEndpoint = $desiredEnablePrivateEndpoint;
  }
  /**
   * @return bool
   */
  public function getDesiredEnablePrivateEndpoint()
  {
    return $this->desiredEnablePrivateEndpoint;
  }
  /**
   * @param GatewayAPIConfig
   */
  public function setDesiredGatewayApiConfig(GatewayAPIConfig $desiredGatewayApiConfig)
  {
    $this->desiredGatewayApiConfig = $desiredGatewayApiConfig;
  }
  /**
   * @return GatewayAPIConfig
   */
  public function getDesiredGatewayApiConfig()
  {
    return $this->desiredGatewayApiConfig;
  }
  /**
   * @param GcfsConfig
   */
  public function setDesiredGcfsConfig(GcfsConfig $desiredGcfsConfig)
  {
    $this->desiredGcfsConfig = $desiredGcfsConfig;
  }
  /**
   * @return GcfsConfig
   */
  public function getDesiredGcfsConfig()
  {
    return $this->desiredGcfsConfig;
  }
  /**
   * @param IdentityServiceConfig
   */
  public function setDesiredIdentityServiceConfig(IdentityServiceConfig $desiredIdentityServiceConfig)
  {
    $this->desiredIdentityServiceConfig = $desiredIdentityServiceConfig;
  }
  /**
   * @return IdentityServiceConfig
   */
  public function getDesiredIdentityServiceConfig()
  {
    return $this->desiredIdentityServiceConfig;
  }
  /**
   * @param string
   */
  public function setDesiredImageType($desiredImageType)
  {
    $this->desiredImageType = $desiredImageType;
  }
  /**
   * @return string
   */
  public function getDesiredImageType()
  {
    return $this->desiredImageType;
  }
  /**
   * @param IntraNodeVisibilityConfig
   */
  public function setDesiredIntraNodeVisibilityConfig(IntraNodeVisibilityConfig $desiredIntraNodeVisibilityConfig)
  {
    $this->desiredIntraNodeVisibilityConfig = $desiredIntraNodeVisibilityConfig;
  }
  /**
   * @return IntraNodeVisibilityConfig
   */
  public function getDesiredIntraNodeVisibilityConfig()
  {
    return $this->desiredIntraNodeVisibilityConfig;
  }
  /**
   * @param ILBSubsettingConfig
   */
  public function setDesiredL4ilbSubsettingConfig(ILBSubsettingConfig $desiredL4ilbSubsettingConfig)
  {
    $this->desiredL4ilbSubsettingConfig = $desiredL4ilbSubsettingConfig;
  }
  /**
   * @return ILBSubsettingConfig
   */
  public function getDesiredL4ilbSubsettingConfig()
  {
    return $this->desiredL4ilbSubsettingConfig;
  }
  /**
   * @param string[]
   */
  public function setDesiredLocations($desiredLocations)
  {
    $this->desiredLocations = $desiredLocations;
  }
  /**
   * @return string[]
   */
  public function getDesiredLocations()
  {
    return $this->desiredLocations;
  }
  /**
   * @param LoggingConfig
   */
  public function setDesiredLoggingConfig(LoggingConfig $desiredLoggingConfig)
  {
    $this->desiredLoggingConfig = $desiredLoggingConfig;
  }
  /**
   * @return LoggingConfig
   */
  public function getDesiredLoggingConfig()
  {
    return $this->desiredLoggingConfig;
  }
  /**
   * @param string
   */
  public function setDesiredLoggingService($desiredLoggingService)
  {
    $this->desiredLoggingService = $desiredLoggingService;
  }
  /**
   * @return string
   */
  public function getDesiredLoggingService()
  {
    return $this->desiredLoggingService;
  }
  /**
   * @param MasterAuthorizedNetworksConfig
   */
  public function setDesiredMasterAuthorizedNetworksConfig(MasterAuthorizedNetworksConfig $desiredMasterAuthorizedNetworksConfig)
  {
    $this->desiredMasterAuthorizedNetworksConfig = $desiredMasterAuthorizedNetworksConfig;
  }
  /**
   * @return MasterAuthorizedNetworksConfig
   */
  public function getDesiredMasterAuthorizedNetworksConfig()
  {
    return $this->desiredMasterAuthorizedNetworksConfig;
  }
  /**
   * @param string
   */
  public function setDesiredMasterVersion($desiredMasterVersion)
  {
    $this->desiredMasterVersion = $desiredMasterVersion;
  }
  /**
   * @return string
   */
  public function getDesiredMasterVersion()
  {
    return $this->desiredMasterVersion;
  }
  /**
   * @param MeshCertificates
   */
  public function setDesiredMeshCertificates(MeshCertificates $desiredMeshCertificates)
  {
    $this->desiredMeshCertificates = $desiredMeshCertificates;
  }
  /**
   * @return MeshCertificates
   */
  public function getDesiredMeshCertificates()
  {
    return $this->desiredMeshCertificates;
  }
  /**
   * @param MonitoringConfig
   */
  public function setDesiredMonitoringConfig(MonitoringConfig $desiredMonitoringConfig)
  {
    $this->desiredMonitoringConfig = $desiredMonitoringConfig;
  }
  /**
   * @return MonitoringConfig
   */
  public function getDesiredMonitoringConfig()
  {
    return $this->desiredMonitoringConfig;
  }
  /**
   * @param string
   */
  public function setDesiredMonitoringService($desiredMonitoringService)
  {
    $this->desiredMonitoringService = $desiredMonitoringService;
  }
  /**
   * @return string
   */
  public function getDesiredMonitoringService()
  {
    return $this->desiredMonitoringService;
  }
  /**
   * @param NetworkTags
   */
  public function setDesiredNodePoolAutoConfigNetworkTags(NetworkTags $desiredNodePoolAutoConfigNetworkTags)
  {
    $this->desiredNodePoolAutoConfigNetworkTags = $desiredNodePoolAutoConfigNetworkTags;
  }
  /**
   * @return NetworkTags
   */
  public function getDesiredNodePoolAutoConfigNetworkTags()
  {
    return $this->desiredNodePoolAutoConfigNetworkTags;
  }
  /**
   * @param NodePoolAutoscaling
   */
  public function setDesiredNodePoolAutoscaling(NodePoolAutoscaling $desiredNodePoolAutoscaling)
  {
    $this->desiredNodePoolAutoscaling = $desiredNodePoolAutoscaling;
  }
  /**
   * @return NodePoolAutoscaling
   */
  public function getDesiredNodePoolAutoscaling()
  {
    return $this->desiredNodePoolAutoscaling;
  }
  /**
   * @param string
   */
  public function setDesiredNodePoolId($desiredNodePoolId)
  {
    $this->desiredNodePoolId = $desiredNodePoolId;
  }
  /**
   * @return string
   */
  public function getDesiredNodePoolId()
  {
    return $this->desiredNodePoolId;
  }
  /**
   * @param NodePoolLoggingConfig
   */
  public function setDesiredNodePoolLoggingConfig(NodePoolLoggingConfig $desiredNodePoolLoggingConfig)
  {
    $this->desiredNodePoolLoggingConfig = $desiredNodePoolLoggingConfig;
  }
  /**
   * @return NodePoolLoggingConfig
   */
  public function getDesiredNodePoolLoggingConfig()
  {
    return $this->desiredNodePoolLoggingConfig;
  }
  /**
   * @param string
   */
  public function setDesiredNodeVersion($desiredNodeVersion)
  {
    $this->desiredNodeVersion = $desiredNodeVersion;
  }
  /**
   * @return string
   */
  public function getDesiredNodeVersion()
  {
    return $this->desiredNodeVersion;
  }
  /**
   * @param NotificationConfig
   */
  public function setDesiredNotificationConfig(NotificationConfig $desiredNotificationConfig)
  {
    $this->desiredNotificationConfig = $desiredNotificationConfig;
  }
  /**
   * @return NotificationConfig
   */
  public function getDesiredNotificationConfig()
  {
    return $this->desiredNotificationConfig;
  }
  /**
   * @param PrivateClusterConfig
   */
  public function setDesiredPrivateClusterConfig(PrivateClusterConfig $desiredPrivateClusterConfig)
  {
    $this->desiredPrivateClusterConfig = $desiredPrivateClusterConfig;
  }
  /**
   * @return PrivateClusterConfig
   */
  public function getDesiredPrivateClusterConfig()
  {
    return $this->desiredPrivateClusterConfig;
  }
  /**
   * @param string
   */
  public function setDesiredPrivateIpv6GoogleAccess($desiredPrivateIpv6GoogleAccess)
  {
    $this->desiredPrivateIpv6GoogleAccess = $desiredPrivateIpv6GoogleAccess;
  }
  /**
   * @return string
   */
  public function getDesiredPrivateIpv6GoogleAccess()
  {
    return $this->desiredPrivateIpv6GoogleAccess;
  }
  /**
   * @param ReleaseChannel
   */
  public function setDesiredReleaseChannel(ReleaseChannel $desiredReleaseChannel)
  {
    $this->desiredReleaseChannel = $desiredReleaseChannel;
  }
  /**
   * @return ReleaseChannel
   */
  public function getDesiredReleaseChannel()
  {
    return $this->desiredReleaseChannel;
  }
  /**
   * @param ResourceUsageExportConfig
   */
  public function setDesiredResourceUsageExportConfig(ResourceUsageExportConfig $desiredResourceUsageExportConfig)
  {
    $this->desiredResourceUsageExportConfig = $desiredResourceUsageExportConfig;
  }
  /**
   * @return ResourceUsageExportConfig
   */
  public function getDesiredResourceUsageExportConfig()
  {
    return $this->desiredResourceUsageExportConfig;
  }
  /**
   * @param ServiceExternalIPsConfig
   */
  public function setDesiredServiceExternalIpsConfig(ServiceExternalIPsConfig $desiredServiceExternalIpsConfig)
  {
    $this->desiredServiceExternalIpsConfig = $desiredServiceExternalIpsConfig;
  }
  /**
   * @return ServiceExternalIPsConfig
   */
  public function getDesiredServiceExternalIpsConfig()
  {
    return $this->desiredServiceExternalIpsConfig;
  }
  /**
   * @param ShieldedNodes
   */
  public function setDesiredShieldedNodes(ShieldedNodes $desiredShieldedNodes)
  {
    $this->desiredShieldedNodes = $desiredShieldedNodes;
  }
  /**
   * @return ShieldedNodes
   */
  public function getDesiredShieldedNodes()
  {
    return $this->desiredShieldedNodes;
  }
  /**
   * @param string
   */
  public function setDesiredStackType($desiredStackType)
  {
    $this->desiredStackType = $desiredStackType;
  }
  /**
   * @return string
   */
  public function getDesiredStackType()
  {
    return $this->desiredStackType;
  }
  /**
   * @param VerticalPodAutoscaling
   */
  public function setDesiredVerticalPodAutoscaling(VerticalPodAutoscaling $desiredVerticalPodAutoscaling)
  {
    $this->desiredVerticalPodAutoscaling = $desiredVerticalPodAutoscaling;
  }
  /**
   * @return VerticalPodAutoscaling
   */
  public function getDesiredVerticalPodAutoscaling()
  {
    return $this->desiredVerticalPodAutoscaling;
  }
  /**
   * @param WorkloadIdentityConfig
   */
  public function setDesiredWorkloadIdentityConfig(WorkloadIdentityConfig $desiredWorkloadIdentityConfig)
  {
    $this->desiredWorkloadIdentityConfig = $desiredWorkloadIdentityConfig;
  }
  /**
   * @return WorkloadIdentityConfig
   */
  public function getDesiredWorkloadIdentityConfig()
  {
    return $this->desiredWorkloadIdentityConfig;
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
}

// Adding a class alias for backwards compatibility with the previous class name.
class_alias(ClusterUpdate::class, 'Google_Service_Container_ClusterUpdate');
