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
 * The "translations" collection of methods.
 * Typical usage is:
 *  <code>
 *   $translateService = new Google_Service_Translate(...);
 *   $translations = $translateService->translations;
 *  </code>
 */
class Google_Service_Translate_Resource_Translations extends Google_Service_Resource
{
  /**
   * Returns text translations from one language to another.
   * (translations.listTranslations)
   *
   * @param string|array $q The text to translate
   * @param string $target The target language into which the text should be
   * translated
   * @param array $optParams Optional parameters.
   *
   * @opt_param string cid The customization id for translate
   * @opt_param string format The format of the text
   * @opt_param string source The source language of the text
   * @return Google_Service_Translate_TranslationsListResponse
   */
  public function listTranslations($q, $target, $optParams = array())
  {
    $params = array('q' => $q, 'target' => $target);
    $params = array_merge($params, $optParams);
    return $this->call('list', array($params), "Google_Service_Translate_TranslationsListResponse");
  }
}
