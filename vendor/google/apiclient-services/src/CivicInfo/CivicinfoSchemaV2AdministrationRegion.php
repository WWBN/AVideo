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

namespace Google\Service\CivicInfo;

class CivicinfoSchemaV2AdministrationRegion extends \Google\Collection
{
  protected $collection_key = 'sources';
  protected $internal_gapi_mappings = [
        "localJurisdiction" => "local_jurisdiction",
  ];
  protected $electionAdministrationBodyType = CivicinfoSchemaV2AdministrativeBody::class;
  protected $electionAdministrationBodyDataType = '';
  protected $localJurisdictionType = CivicinfoSchemaV2AdministrationRegion::class;
  protected $localJurisdictionDataType = '';
  /**
   * @var string
   */
  public $name;
  protected $sourcesType = CivicinfoSchemaV2Source::class;
  protected $sourcesDataType = 'array';

  /**
   * @param CivicinfoSchemaV2AdministrativeBody
   */
  public function setElectionAdministrationBody(CivicinfoSchemaV2AdministrativeBody $electionAdministrationBody)
  {
    $this->electionAdministrationBody = $electionAdministrationBody;
  }
  /**
   * @return CivicinfoSchemaV2AdministrativeBody
   */
  public function getElectionAdministrationBody()
  {
    return $this->electionAdministrationBody;
  }
  /**
   * @param CivicinfoSchemaV2AdministrationRegion
   */
  public function setLocalJurisdiction(CivicinfoSchemaV2AdministrationRegion $localJurisdiction)
  {
    $this->localJurisdiction = $localJurisdiction;
  }
  /**
   * @return CivicinfoSchemaV2AdministrationRegion
   */
  public function getLocalJurisdiction()
  {
    return $this->localJurisdiction;
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
   * @param CivicinfoSchemaV2Source[]
   */
  public function setSources($sources)
  {
    $this->sources = $sources;
  }
  /**
   * @return CivicinfoSchemaV2Source[]
   */
  public function getSources()
  {
    return $this->sources;
  }
}

// Adding a class alias for backwards compatibility with the previous class name.
class_alias(CivicinfoSchemaV2AdministrationRegion::class, 'Google_Service_CivicInfo_CivicinfoSchemaV2AdministrationRegion');
