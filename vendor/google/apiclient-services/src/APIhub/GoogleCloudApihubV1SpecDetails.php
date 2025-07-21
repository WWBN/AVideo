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

namespace Google\Service\APIhub;

class GoogleCloudApihubV1SpecDetails extends \Google\Model
{
  /**
   * @var string
   */
  public $description;
  protected $openApiSpecDetailsType = GoogleCloudApihubV1OpenApiSpecDetails::class;
  protected $openApiSpecDetailsDataType = '';

  /**
   * @param string
   */
  public function setDescription($description)
  {
    $this->description = $description;
  }
  /**
   * @return string
   */
  public function getDescription()
  {
    return $this->description;
  }
  /**
   * @param GoogleCloudApihubV1OpenApiSpecDetails
   */
  public function setOpenApiSpecDetails(GoogleCloudApihubV1OpenApiSpecDetails $openApiSpecDetails)
  {
    $this->openApiSpecDetails = $openApiSpecDetails;
  }
  /**
   * @return GoogleCloudApihubV1OpenApiSpecDetails
   */
  public function getOpenApiSpecDetails()
  {
    return $this->openApiSpecDetails;
  }
}

// Adding a class alias for backwards compatibility with the previous class name.
class_alias(GoogleCloudApihubV1SpecDetails::class, 'Google_Service_APIhub_GoogleCloudApihubV1SpecDetails');
