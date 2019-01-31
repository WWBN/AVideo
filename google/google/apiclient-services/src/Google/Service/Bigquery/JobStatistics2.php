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

class Google_Service_Bigquery_JobStatistics2 extends Google_Collection
{
  protected $collection_key = 'undeclaredQueryParameters';
  public $billingTier;
  public $cacheHit;
  public $numDmlAffectedRows;
  protected $queryPlanType = 'Google_Service_Bigquery_ExplainQueryStage';
  protected $queryPlanDataType = 'array';
  protected $referencedTablesType = 'Google_Service_Bigquery_TableReference';
  protected $referencedTablesDataType = 'array';
  protected $schemaType = 'Google_Service_Bigquery_TableSchema';
  protected $schemaDataType = '';
  public $statementType;
  public $totalBytesBilled;
  public $totalBytesProcessed;
  protected $undeclaredQueryParametersType = 'Google_Service_Bigquery_QueryParameter';
  protected $undeclaredQueryParametersDataType = 'array';

  public function setBillingTier($billingTier)
  {
    $this->billingTier = $billingTier;
  }
  public function getBillingTier()
  {
    return $this->billingTier;
  }
  public function setCacheHit($cacheHit)
  {
    $this->cacheHit = $cacheHit;
  }
  public function getCacheHit()
  {
    return $this->cacheHit;
  }
  public function setNumDmlAffectedRows($numDmlAffectedRows)
  {
    $this->numDmlAffectedRows = $numDmlAffectedRows;
  }
  public function getNumDmlAffectedRows()
  {
    return $this->numDmlAffectedRows;
  }
  public function setQueryPlan($queryPlan)
  {
    $this->queryPlan = $queryPlan;
  }
  public function getQueryPlan()
  {
    return $this->queryPlan;
  }
  public function setReferencedTables($referencedTables)
  {
    $this->referencedTables = $referencedTables;
  }
  public function getReferencedTables()
  {
    return $this->referencedTables;
  }
  public function setSchema(Google_Service_Bigquery_TableSchema $schema)
  {
    $this->schema = $schema;
  }
  public function getSchema()
  {
    return $this->schema;
  }
  public function setStatementType($statementType)
  {
    $this->statementType = $statementType;
  }
  public function getStatementType()
  {
    return $this->statementType;
  }
  public function setTotalBytesBilled($totalBytesBilled)
  {
    $this->totalBytesBilled = $totalBytesBilled;
  }
  public function getTotalBytesBilled()
  {
    return $this->totalBytesBilled;
  }
  public function setTotalBytesProcessed($totalBytesProcessed)
  {
    $this->totalBytesProcessed = $totalBytesProcessed;
  }
  public function getTotalBytesProcessed()
  {
    return $this->totalBytesProcessed;
  }
  public function setUndeclaredQueryParameters($undeclaredQueryParameters)
  {
    $this->undeclaredQueryParameters = $undeclaredQueryParameters;
  }
  public function getUndeclaredQueryParameters()
  {
    return $this->undeclaredQueryParameters;
  }
}
