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

namespace Google\Service\Aiplatform\Resource;

use Google\Service\Aiplatform\GoogleCloudAiplatformV1PublisherModel;

/**
 * The "models" collection of methods.
 * Typical usage is:
 *  <code>
 *   $aiplatformService = new Google\Service\Aiplatform(...);
 *   $models = $aiplatformService->publishers_models;
 *  </code>
 */
class PublishersModels extends \Google\Service\Resource
{
  /**
   * Gets a Model Garden publisher model. (models.get)
   *
   * @param string $name Required. The name of the PublisherModel resource.
   * Format: `publishers/{publisher}/models/{publisher_model}`
   * @param array $optParams Optional parameters.
   *
   * @opt_param string languageCode Optional. The IETF BCP-47 language code
   * representing the language in which the publisher model's text information
   * should be written in (see go/bcp47).
   * @opt_param string view Optional. PublisherModel view specifying which fields
   * to read.
   * @return GoogleCloudAiplatformV1PublisherModel
   * @throws \Google\Service\Exception
   */
  public function get($name, $optParams = [])
  {
    $params = ['name' => $name];
    $params = array_merge($params, $optParams);
    return $this->call('get', [$params], GoogleCloudAiplatformV1PublisherModel::class);
  }
}

// Adding a class alias for backwards compatibility with the previous class name.
class_alias(PublishersModels::class, 'Google_Service_Aiplatform_Resource_PublishersModels');
