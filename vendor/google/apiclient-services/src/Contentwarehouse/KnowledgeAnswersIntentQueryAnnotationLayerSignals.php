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

class KnowledgeAnswersIntentQueryAnnotationLayerSignals extends \Google\Model
{
  protected $customVehicleActionArgumentAnnotatorSignalsType = KnowledgeAnswersIntentQueryCustomVehicleActionArgumentAnnotatorSignals::class;
  protected $customVehicleActionArgumentAnnotatorSignalsDataType = '';
  public $customVehicleActionArgumentAnnotatorSignals;
  protected $freetextAnnotationSignalsType = KnowledgeAnswersIntentQueryFreetextAnnotationSignals::class;
  protected $freetextAnnotationSignalsDataType = '';
  public $freetextAnnotationSignals;
  protected $nimbleAnnotationSignalsType = KnowledgeAnswersIntentQueryNimbleAnnotationSignals::class;
  protected $nimbleAnnotationSignalsDataType = '';
  public $nimbleAnnotationSignals;
  protected $ntprAnnotationSignalsType = KnowledgeAnswersIntentQueryNTPRAnnotationSignals::class;
  protected $ntprAnnotationSignalsDataType = '';
  public $ntprAnnotationSignals;
  protected $qrefAnnotationSignalsType = KnowledgeAnswersIntentQueryQrefAnnotationSignals::class;
  protected $qrefAnnotationSignalsDataType = '';
  public $qrefAnnotationSignals;
  protected $semanticAnnotationSignalsType = KnowledgeAnswersIntentQuerySemanticAnnotationSignals::class;
  protected $semanticAnnotationSignalsDataType = '';
  public $semanticAnnotationSignals;
  protected $teleportArgumentAnnotatorSignalsType = KnowledgeAnswersIntentQueryTeleportArgumentAnnotatorSignals::class;
  protected $teleportArgumentAnnotatorSignalsDataType = '';
  public $teleportArgumentAnnotatorSignals;

  /**
   * @param KnowledgeAnswersIntentQueryCustomVehicleActionArgumentAnnotatorSignals
   */
  public function setCustomVehicleActionArgumentAnnotatorSignals(KnowledgeAnswersIntentQueryCustomVehicleActionArgumentAnnotatorSignals $customVehicleActionArgumentAnnotatorSignals)
  {
    $this->customVehicleActionArgumentAnnotatorSignals = $customVehicleActionArgumentAnnotatorSignals;
  }
  /**
   * @return KnowledgeAnswersIntentQueryCustomVehicleActionArgumentAnnotatorSignals
   */
  public function getCustomVehicleActionArgumentAnnotatorSignals()
  {
    return $this->customVehicleActionArgumentAnnotatorSignals;
  }
  /**
   * @param KnowledgeAnswersIntentQueryFreetextAnnotationSignals
   */
  public function setFreetextAnnotationSignals(KnowledgeAnswersIntentQueryFreetextAnnotationSignals $freetextAnnotationSignals)
  {
    $this->freetextAnnotationSignals = $freetextAnnotationSignals;
  }
  /**
   * @return KnowledgeAnswersIntentQueryFreetextAnnotationSignals
   */
  public function getFreetextAnnotationSignals()
  {
    return $this->freetextAnnotationSignals;
  }
  /**
   * @param KnowledgeAnswersIntentQueryNimbleAnnotationSignals
   */
  public function setNimbleAnnotationSignals(KnowledgeAnswersIntentQueryNimbleAnnotationSignals $nimbleAnnotationSignals)
  {
    $this->nimbleAnnotationSignals = $nimbleAnnotationSignals;
  }
  /**
   * @return KnowledgeAnswersIntentQueryNimbleAnnotationSignals
   */
  public function getNimbleAnnotationSignals()
  {
    return $this->nimbleAnnotationSignals;
  }
  /**
   * @param KnowledgeAnswersIntentQueryNTPRAnnotationSignals
   */
  public function setNtprAnnotationSignals(KnowledgeAnswersIntentQueryNTPRAnnotationSignals $ntprAnnotationSignals)
  {
    $this->ntprAnnotationSignals = $ntprAnnotationSignals;
  }
  /**
   * @return KnowledgeAnswersIntentQueryNTPRAnnotationSignals
   */
  public function getNtprAnnotationSignals()
  {
    return $this->ntprAnnotationSignals;
  }
  /**
   * @param KnowledgeAnswersIntentQueryQrefAnnotationSignals
   */
  public function setQrefAnnotationSignals(KnowledgeAnswersIntentQueryQrefAnnotationSignals $qrefAnnotationSignals)
  {
    $this->qrefAnnotationSignals = $qrefAnnotationSignals;
  }
  /**
   * @return KnowledgeAnswersIntentQueryQrefAnnotationSignals
   */
  public function getQrefAnnotationSignals()
  {
    return $this->qrefAnnotationSignals;
  }
  /**
   * @param KnowledgeAnswersIntentQuerySemanticAnnotationSignals
   */
  public function setSemanticAnnotationSignals(KnowledgeAnswersIntentQuerySemanticAnnotationSignals $semanticAnnotationSignals)
  {
    $this->semanticAnnotationSignals = $semanticAnnotationSignals;
  }
  /**
   * @return KnowledgeAnswersIntentQuerySemanticAnnotationSignals
   */
  public function getSemanticAnnotationSignals()
  {
    return $this->semanticAnnotationSignals;
  }
  /**
   * @param KnowledgeAnswersIntentQueryTeleportArgumentAnnotatorSignals
   */
  public function setTeleportArgumentAnnotatorSignals(KnowledgeAnswersIntentQueryTeleportArgumentAnnotatorSignals $teleportArgumentAnnotatorSignals)
  {
    $this->teleportArgumentAnnotatorSignals = $teleportArgumentAnnotatorSignals;
  }
  /**
   * @return KnowledgeAnswersIntentQueryTeleportArgumentAnnotatorSignals
   */
  public function getTeleportArgumentAnnotatorSignals()
  {
    return $this->teleportArgumentAnnotatorSignals;
  }
}

// Adding a class alias for backwards compatibility with the previous class name.
class_alias(KnowledgeAnswersIntentQueryAnnotationLayerSignals::class, 'Google_Service_Contentwarehouse_KnowledgeAnswersIntentQueryAnnotationLayerSignals');
