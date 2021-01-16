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

/**
 * The "languages" collection of methods.
 * Typical usage is:
 *  <code>
 *   $translateService = new Google_Service_Translate(...);
 *   $languages = $translateService->languages;
 *  </code>
 */
class Google_Service_Translate_Resource_Languages extends Google_Service_Resource
{
  /**
   * List the source/target languages supported by the API
   * (languages.listLanguages)
   *
   * @param array $optParams Optional parameters.
   *
   * @opt_param string target the language and collation in which the localized
   * results should be returned
   * @return Google_Service_Translate_LanguagesListResponse
   */
  public function listLanguages($optParams = array())
  {
    $params = array();
    $params = array_merge($params, $optParams);
    return $this->call('list', array($params), "Google_Service_Translate_LanguagesListResponse");
  }
}
