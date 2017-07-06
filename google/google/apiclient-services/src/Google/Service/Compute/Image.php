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

class Google_Service_Compute_Image extends Google_Collection
{
  protected $collection_key = 'licenses';
  public $archiveSizeBytes;
  public $creationTimestamp;
  protected $deprecatedType = 'Google_Service_Compute_DeprecationStatus';
  protected $deprecatedDataType = '';
  public $description;
  public $diskSizeGb;
  public $family;
  protected $guestOsFeaturesType = 'Google_Service_Compute_GuestOsFeature';
  protected $guestOsFeaturesDataType = 'array';
  public $id;
  protected $imageEncryptionKeyType = 'Google_Service_Compute_CustomerEncryptionKey';
  protected $imageEncryptionKeyDataType = '';
  public $kind;
  public $licenses;
  public $name;
  protected $rawDiskType = 'Google_Service_Compute_ImageRawDisk';
  protected $rawDiskDataType = '';
  public $selfLink;
  public $sourceDisk;
  protected $sourceDiskEncryptionKeyType = 'Google_Service_Compute_CustomerEncryptionKey';
  protected $sourceDiskEncryptionKeyDataType = '';
  public $sourceDiskId;
  public $sourceType;
  public $status;

  public function setArchiveSizeBytes($archiveSizeBytes)
  {
    $this->archiveSizeBytes = $archiveSizeBytes;
  }
  public function getArchiveSizeBytes()
  {
    return $this->archiveSizeBytes;
  }
  public function setCreationTimestamp($creationTimestamp)
  {
    $this->creationTimestamp = $creationTimestamp;
  }
  public function getCreationTimestamp()
  {
    return $this->creationTimestamp;
  }
  public function setDeprecated(Google_Service_Compute_DeprecationStatus $deprecated)
  {
    $this->deprecated = $deprecated;
  }
  public function getDeprecated()
  {
    return $this->deprecated;
  }
  public function setDescription($description)
  {
    $this->description = $description;
  }
  public function getDescription()
  {
    return $this->description;
  }
  public function setDiskSizeGb($diskSizeGb)
  {
    $this->diskSizeGb = $diskSizeGb;
  }
  public function getDiskSizeGb()
  {
    return $this->diskSizeGb;
  }
  public function setFamily($family)
  {
    $this->family = $family;
  }
  public function getFamily()
  {
    return $this->family;
  }
  public function setGuestOsFeatures($guestOsFeatures)
  {
    $this->guestOsFeatures = $guestOsFeatures;
  }
  public function getGuestOsFeatures()
  {
    return $this->guestOsFeatures;
  }
  public function setId($id)
  {
    $this->id = $id;
  }
  public function getId()
  {
    return $this->id;
  }
  public function setImageEncryptionKey(Google_Service_Compute_CustomerEncryptionKey $imageEncryptionKey)
  {
    $this->imageEncryptionKey = $imageEncryptionKey;
  }
  public function getImageEncryptionKey()
  {
    return $this->imageEncryptionKey;
  }
  public function setKind($kind)
  {
    $this->kind = $kind;
  }
  public function getKind()
  {
    return $this->kind;
  }
  public function setLicenses($licenses)
  {
    $this->licenses = $licenses;
  }
  public function getLicenses()
  {
    return $this->licenses;
  }
  public function setName($name)
  {
    $this->name = $name;
  }
  public function getName()
  {
    return $this->name;
  }
  public function setRawDisk(Google_Service_Compute_ImageRawDisk $rawDisk)
  {
    $this->rawDisk = $rawDisk;
  }
  public function getRawDisk()
  {
    return $this->rawDisk;
  }
  public function setSelfLink($selfLink)
  {
    $this->selfLink = $selfLink;
  }
  public function getSelfLink()
  {
    return $this->selfLink;
  }
  public function setSourceDisk($sourceDisk)
  {
    $this->sourceDisk = $sourceDisk;
  }
  public function getSourceDisk()
  {
    return $this->sourceDisk;
  }
  public function setSourceDiskEncryptionKey(Google_Service_Compute_CustomerEncryptionKey $sourceDiskEncryptionKey)
  {
    $this->sourceDiskEncryptionKey = $sourceDiskEncryptionKey;
  }
  public function getSourceDiskEncryptionKey()
  {
    return $this->sourceDiskEncryptionKey;
  }
  public function setSourceDiskId($sourceDiskId)
  {
    $this->sourceDiskId = $sourceDiskId;
  }
  public function getSourceDiskId()
  {
    return $this->sourceDiskId;
  }
  public function setSourceType($sourceType)
  {
    $this->sourceType = $sourceType;
  }
  public function getSourceType()
  {
    return $this->sourceType;
  }
  public function setStatus($status)
  {
    $this->status = $status;
  }
  public function getStatus()
  {
    return $this->status;
  }
}
