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

class AssistantApiDeviceCapabilities extends \Google\Collection
{
  protected $collection_key = 'supportedLocale';
  protected $androidIntentCapabilitiesType = AssistantApiAndroidIntentCapabilities::class;
  protected $androidIntentCapabilitiesDataType = '';
  public $androidIntentCapabilities;
  protected $audioInputType = AssistantApiAudioInput::class;
  protected $audioInputDataType = '';
  public $audioInput;
  protected $audioOutputType = AssistantApiAudioOutput::class;
  protected $audioOutputDataType = '';
  public $audioOutput;
  protected $callCapabilitiesType = AssistantApiCallCapabilities::class;
  protected $callCapabilitiesDataType = '';
  public $callCapabilities;
  protected $cameraType = AssistantApiCameraCapabilities::class;
  protected $cameraDataType = '';
  public $camera;
  /**
   * @var string[]
   */
  public $carUxRestrictions;
  protected $castType = AssistantApiCastCapabilities::class;
  protected $castDataType = '';
  public $cast;
  protected $communicationUiCapabilitiesType = AssistantApiCommunicationUiCapabilities::class;
  protected $communicationUiCapabilitiesDataType = '';
  public $communicationUiCapabilities;
  protected $contactLookupCapabilitiesType = AssistantApiContactLookupCapabilities::class;
  protected $contactLookupCapabilitiesDataType = '';
  public $contactLookupCapabilities;
  protected $deviceIdType = AssistantApiCoreTypesDeviceId::class;
  protected $deviceIdDataType = '';
  public $deviceId;
  /**
   * @var string
   */
  public $deviceUxMode;
  /**
   * @var bool
   */
  public $hasVoiceTelephony;
  protected $jwnCapabilitiesType = AssistantApiJwnCapabilities::class;
  protected $jwnCapabilitiesDataType = '';
  public $jwnCapabilities;
  protected $lensPerceptionCapabilitiesType = AssistantApiLensPerceptionCapabilities::class;
  protected $lensPerceptionCapabilitiesDataType = '';
  public $lensPerceptionCapabilities;
  protected $locationType = AssistantApiLocationCapabilities::class;
  protected $locationDataType = '';
  public $location;
  protected $loggingOnlyDataType = AssistantApiLoggingOnlyData::class;
  protected $loggingOnlyDataDataType = '';
  public $loggingOnlyData;
  protected $messageCapabilitiesType = AssistantApiMessageCapabilities::class;
  protected $messageCapabilitiesDataType = '';
  public $messageCapabilities;
  protected $movementType = AssistantApiMovementCapabilities::class;
  protected $movementDataType = '';
  public $movement;
  /**
   * @var string
   */
  public $notificationCapabilities;
  protected $notificationOutputRestrictionsType = AssistantApiNotificationOutputRestrictions::class;
  protected $notificationOutputRestrictionsDataType = '';
  public $notificationOutputRestrictions;
  protected $outputRestrictionsType = AssistantApiOutputRestrictions::class;
  protected $outputRestrictionsDataType = '';
  public $outputRestrictions;
  /**
   * @var string
   */
  public $popOnLockscreenCapability;
  /**
   * @var string
   */
  public $safetyRestrictions;
  protected $screenType = AssistantApiScreenCapabilities::class;
  protected $screenDataType = '';
  public $screen;
  protected $sodaCapabilitiesType = AssistantApiSodaCapabilities::class;
  protected $sodaCapabilitiesDataType = '';
  public $sodaCapabilities;
  protected $softwareType = AssistantApiSoftwareCapabilities::class;
  protected $softwareDataType = '';
  public $software;
  protected $speechCapabilitiesType = AssistantApiSpeechCapabilities::class;
  protected $speechCapabilitiesDataType = '';
  public $speechCapabilities;
  /**
   * @var string[]
   */
  public $supportedLocale;
  protected $surfaceIdentityType = AssistantApiCoreTypesSurfaceIdentity::class;
  protected $surfaceIdentityDataType = '';
  public $surfaceIdentity;
  /**
   * @var string
   */
  public $surfaceTypeString;
  protected $systemNotificationRestrictionsType = AssistantApiSystemNotificationRestrictions::class;
  protected $systemNotificationRestrictionsDataType = '';
  public $systemNotificationRestrictions;
  protected $thirdPartyCapabilitiesType = AssistantApiThirdPartyCapabilities::class;
  protected $thirdPartyCapabilitiesDataType = '';
  public $thirdPartyCapabilities;

  /**
   * @param AssistantApiAndroidIntentCapabilities
   */
  public function setAndroidIntentCapabilities(AssistantApiAndroidIntentCapabilities $androidIntentCapabilities)
  {
    $this->androidIntentCapabilities = $androidIntentCapabilities;
  }
  /**
   * @return AssistantApiAndroidIntentCapabilities
   */
  public function getAndroidIntentCapabilities()
  {
    return $this->androidIntentCapabilities;
  }
  /**
   * @param AssistantApiAudioInput
   */
  public function setAudioInput(AssistantApiAudioInput $audioInput)
  {
    $this->audioInput = $audioInput;
  }
  /**
   * @return AssistantApiAudioInput
   */
  public function getAudioInput()
  {
    return $this->audioInput;
  }
  /**
   * @param AssistantApiAudioOutput
   */
  public function setAudioOutput(AssistantApiAudioOutput $audioOutput)
  {
    $this->audioOutput = $audioOutput;
  }
  /**
   * @return AssistantApiAudioOutput
   */
  public function getAudioOutput()
  {
    return $this->audioOutput;
  }
  /**
   * @param AssistantApiCallCapabilities
   */
  public function setCallCapabilities(AssistantApiCallCapabilities $callCapabilities)
  {
    $this->callCapabilities = $callCapabilities;
  }
  /**
   * @return AssistantApiCallCapabilities
   */
  public function getCallCapabilities()
  {
    return $this->callCapabilities;
  }
  /**
   * @param AssistantApiCameraCapabilities
   */
  public function setCamera(AssistantApiCameraCapabilities $camera)
  {
    $this->camera = $camera;
  }
  /**
   * @return AssistantApiCameraCapabilities
   */
  public function getCamera()
  {
    return $this->camera;
  }
  /**
   * @param string[]
   */
  public function setCarUxRestrictions($carUxRestrictions)
  {
    $this->carUxRestrictions = $carUxRestrictions;
  }
  /**
   * @return string[]
   */
  public function getCarUxRestrictions()
  {
    return $this->carUxRestrictions;
  }
  /**
   * @param AssistantApiCastCapabilities
   */
  public function setCast(AssistantApiCastCapabilities $cast)
  {
    $this->cast = $cast;
  }
  /**
   * @return AssistantApiCastCapabilities
   */
  public function getCast()
  {
    return $this->cast;
  }
  /**
   * @param AssistantApiCommunicationUiCapabilities
   */
  public function setCommunicationUiCapabilities(AssistantApiCommunicationUiCapabilities $communicationUiCapabilities)
  {
    $this->communicationUiCapabilities = $communicationUiCapabilities;
  }
  /**
   * @return AssistantApiCommunicationUiCapabilities
   */
  public function getCommunicationUiCapabilities()
  {
    return $this->communicationUiCapabilities;
  }
  /**
   * @param AssistantApiContactLookupCapabilities
   */
  public function setContactLookupCapabilities(AssistantApiContactLookupCapabilities $contactLookupCapabilities)
  {
    $this->contactLookupCapabilities = $contactLookupCapabilities;
  }
  /**
   * @return AssistantApiContactLookupCapabilities
   */
  public function getContactLookupCapabilities()
  {
    return $this->contactLookupCapabilities;
  }
  /**
   * @param AssistantApiCoreTypesDeviceId
   */
  public function setDeviceId(AssistantApiCoreTypesDeviceId $deviceId)
  {
    $this->deviceId = $deviceId;
  }
  /**
   * @return AssistantApiCoreTypesDeviceId
   */
  public function getDeviceId()
  {
    return $this->deviceId;
  }
  /**
   * @param string
   */
  public function setDeviceUxMode($deviceUxMode)
  {
    $this->deviceUxMode = $deviceUxMode;
  }
  /**
   * @return string
   */
  public function getDeviceUxMode()
  {
    return $this->deviceUxMode;
  }
  /**
   * @param bool
   */
  public function setHasVoiceTelephony($hasVoiceTelephony)
  {
    $this->hasVoiceTelephony = $hasVoiceTelephony;
  }
  /**
   * @return bool
   */
  public function getHasVoiceTelephony()
  {
    return $this->hasVoiceTelephony;
  }
  /**
   * @param AssistantApiJwnCapabilities
   */
  public function setJwnCapabilities(AssistantApiJwnCapabilities $jwnCapabilities)
  {
    $this->jwnCapabilities = $jwnCapabilities;
  }
  /**
   * @return AssistantApiJwnCapabilities
   */
  public function getJwnCapabilities()
  {
    return $this->jwnCapabilities;
  }
  /**
   * @param AssistantApiLensPerceptionCapabilities
   */
  public function setLensPerceptionCapabilities(AssistantApiLensPerceptionCapabilities $lensPerceptionCapabilities)
  {
    $this->lensPerceptionCapabilities = $lensPerceptionCapabilities;
  }
  /**
   * @return AssistantApiLensPerceptionCapabilities
   */
  public function getLensPerceptionCapabilities()
  {
    return $this->lensPerceptionCapabilities;
  }
  /**
   * @param AssistantApiLocationCapabilities
   */
  public function setLocation(AssistantApiLocationCapabilities $location)
  {
    $this->location = $location;
  }
  /**
   * @return AssistantApiLocationCapabilities
   */
  public function getLocation()
  {
    return $this->location;
  }
  /**
   * @param AssistantApiLoggingOnlyData
   */
  public function setLoggingOnlyData(AssistantApiLoggingOnlyData $loggingOnlyData)
  {
    $this->loggingOnlyData = $loggingOnlyData;
  }
  /**
   * @return AssistantApiLoggingOnlyData
   */
  public function getLoggingOnlyData()
  {
    return $this->loggingOnlyData;
  }
  /**
   * @param AssistantApiMessageCapabilities
   */
  public function setMessageCapabilities(AssistantApiMessageCapabilities $messageCapabilities)
  {
    $this->messageCapabilities = $messageCapabilities;
  }
  /**
   * @return AssistantApiMessageCapabilities
   */
  public function getMessageCapabilities()
  {
    return $this->messageCapabilities;
  }
  /**
   * @param AssistantApiMovementCapabilities
   */
  public function setMovement(AssistantApiMovementCapabilities $movement)
  {
    $this->movement = $movement;
  }
  /**
   * @return AssistantApiMovementCapabilities
   */
  public function getMovement()
  {
    return $this->movement;
  }
  /**
   * @param string
   */
  public function setNotificationCapabilities($notificationCapabilities)
  {
    $this->notificationCapabilities = $notificationCapabilities;
  }
  /**
   * @return string
   */
  public function getNotificationCapabilities()
  {
    return $this->notificationCapabilities;
  }
  /**
   * @param AssistantApiNotificationOutputRestrictions
   */
  public function setNotificationOutputRestrictions(AssistantApiNotificationOutputRestrictions $notificationOutputRestrictions)
  {
    $this->notificationOutputRestrictions = $notificationOutputRestrictions;
  }
  /**
   * @return AssistantApiNotificationOutputRestrictions
   */
  public function getNotificationOutputRestrictions()
  {
    return $this->notificationOutputRestrictions;
  }
  /**
   * @param AssistantApiOutputRestrictions
   */
  public function setOutputRestrictions(AssistantApiOutputRestrictions $outputRestrictions)
  {
    $this->outputRestrictions = $outputRestrictions;
  }
  /**
   * @return AssistantApiOutputRestrictions
   */
  public function getOutputRestrictions()
  {
    return $this->outputRestrictions;
  }
  /**
   * @param string
   */
  public function setPopOnLockscreenCapability($popOnLockscreenCapability)
  {
    $this->popOnLockscreenCapability = $popOnLockscreenCapability;
  }
  /**
   * @return string
   */
  public function getPopOnLockscreenCapability()
  {
    return $this->popOnLockscreenCapability;
  }
  /**
   * @param string
   */
  public function setSafetyRestrictions($safetyRestrictions)
  {
    $this->safetyRestrictions = $safetyRestrictions;
  }
  /**
   * @return string
   */
  public function getSafetyRestrictions()
  {
    return $this->safetyRestrictions;
  }
  /**
   * @param AssistantApiScreenCapabilities
   */
  public function setScreen(AssistantApiScreenCapabilities $screen)
  {
    $this->screen = $screen;
  }
  /**
   * @return AssistantApiScreenCapabilities
   */
  public function getScreen()
  {
    return $this->screen;
  }
  /**
   * @param AssistantApiSodaCapabilities
   */
  public function setSodaCapabilities(AssistantApiSodaCapabilities $sodaCapabilities)
  {
    $this->sodaCapabilities = $sodaCapabilities;
  }
  /**
   * @return AssistantApiSodaCapabilities
   */
  public function getSodaCapabilities()
  {
    return $this->sodaCapabilities;
  }
  /**
   * @param AssistantApiSoftwareCapabilities
   */
  public function setSoftware(AssistantApiSoftwareCapabilities $software)
  {
    $this->software = $software;
  }
  /**
   * @return AssistantApiSoftwareCapabilities
   */
  public function getSoftware()
  {
    return $this->software;
  }
  /**
   * @param AssistantApiSpeechCapabilities
   */
  public function setSpeechCapabilities(AssistantApiSpeechCapabilities $speechCapabilities)
  {
    $this->speechCapabilities = $speechCapabilities;
  }
  /**
   * @return AssistantApiSpeechCapabilities
   */
  public function getSpeechCapabilities()
  {
    return $this->speechCapabilities;
  }
  /**
   * @param string[]
   */
  public function setSupportedLocale($supportedLocale)
  {
    $this->supportedLocale = $supportedLocale;
  }
  /**
   * @return string[]
   */
  public function getSupportedLocale()
  {
    return $this->supportedLocale;
  }
  /**
   * @param AssistantApiCoreTypesSurfaceIdentity
   */
  public function setSurfaceIdentity(AssistantApiCoreTypesSurfaceIdentity $surfaceIdentity)
  {
    $this->surfaceIdentity = $surfaceIdentity;
  }
  /**
   * @return AssistantApiCoreTypesSurfaceIdentity
   */
  public function getSurfaceIdentity()
  {
    return $this->surfaceIdentity;
  }
  /**
   * @param string
   */
  public function setSurfaceTypeString($surfaceTypeString)
  {
    $this->surfaceTypeString = $surfaceTypeString;
  }
  /**
   * @return string
   */
  public function getSurfaceTypeString()
  {
    return $this->surfaceTypeString;
  }
  /**
   * @param AssistantApiSystemNotificationRestrictions
   */
  public function setSystemNotificationRestrictions(AssistantApiSystemNotificationRestrictions $systemNotificationRestrictions)
  {
    $this->systemNotificationRestrictions = $systemNotificationRestrictions;
  }
  /**
   * @return AssistantApiSystemNotificationRestrictions
   */
  public function getSystemNotificationRestrictions()
  {
    return $this->systemNotificationRestrictions;
  }
  /**
   * @param AssistantApiThirdPartyCapabilities
   */
  public function setThirdPartyCapabilities(AssistantApiThirdPartyCapabilities $thirdPartyCapabilities)
  {
    $this->thirdPartyCapabilities = $thirdPartyCapabilities;
  }
  /**
   * @return AssistantApiThirdPartyCapabilities
   */
  public function getThirdPartyCapabilities()
  {
    return $this->thirdPartyCapabilities;
  }
}

// Adding a class alias for backwards compatibility with the previous class name.
class_alias(AssistantApiDeviceCapabilities::class, 'Google_Service_Contentwarehouse_AssistantApiDeviceCapabilities');
