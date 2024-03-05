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

namespace Google\Service\Integrations\Resource;

use Google\Service\Integrations\GoogleCloudIntegrationsV1alphaExecuteTestCaseRequest;
use Google\Service\Integrations\GoogleCloudIntegrationsV1alphaExecuteTestCaseResponse;
use Google\Service\Integrations\GoogleCloudIntegrationsV1alphaListTestCaseExecutionsResponse;
use Google\Service\Integrations\GoogleCloudIntegrationsV1alphaTestCase;
use Google\Service\Integrations\GoogleProtobufEmpty;

/**
 * The "testCases" collection of methods.
 * Typical usage is:
 *  <code>
 *   $integrationsService = new Google\Service\Integrations(...);
 *   $testCases = $integrationsService->projects_locations_integrations_versions_testCases;
 *  </code>
 */
class ProjectsLocationsIntegrationsVersionsTestCases extends \Google\Service\Resource
{
  /**
   * Creates a new test case (testCases.create)
   *
   * @param string $parent Required. The parent resource where this test case will
   * be created. Format: projects/{project}/locations/{location}/integrations/{int
   * egration}/versions/{integration_version}
   * @param GoogleCloudIntegrationsV1alphaTestCase $postBody
   * @param array $optParams Optional parameters.
   *
   * @opt_param string testCaseId Required. Required
   * @return GoogleCloudIntegrationsV1alphaTestCase
   * @throws \Google\Service\Exception
   */
  public function create($parent, GoogleCloudIntegrationsV1alphaTestCase $postBody, $optParams = [])
  {
    $params = ['parent' => $parent, 'postBody' => $postBody];
    $params = array_merge($params, $optParams);
    return $this->call('create', [$params], GoogleCloudIntegrationsV1alphaTestCase::class);
  }
  /**
   * Deletes a test case (testCases.delete)
   *
   * @param string $name Required. ID for the test case to be deleted
   * @param array $optParams Optional parameters.
   * @return GoogleProtobufEmpty
   * @throws \Google\Service\Exception
   */
  public function delete($name, $optParams = [])
  {
    $params = ['name' => $name];
    $params = array_merge($params, $optParams);
    return $this->call('delete', [$params], GoogleProtobufEmpty::class);
  }
  /**
   * Executes functional test (testCases.executeTest)
   *
   * @param string $testCaseName Required. Test case resource name
   * @param GoogleCloudIntegrationsV1alphaExecuteTestCaseRequest $postBody
   * @param array $optParams Optional parameters.
   * @return GoogleCloudIntegrationsV1alphaExecuteTestCaseResponse
   * @throws \Google\Service\Exception
   */
  public function executeTest($testCaseName, GoogleCloudIntegrationsV1alphaExecuteTestCaseRequest $postBody, $optParams = [])
  {
    $params = ['testCaseName' => $testCaseName, 'postBody' => $postBody];
    $params = array_merge($params, $optParams);
    return $this->call('executeTest', [$params], GoogleCloudIntegrationsV1alphaExecuteTestCaseResponse::class);
  }
  /**
   * Get a test case (testCases.get)
   *
   * @param string $name Required. The ID of the test case to retrieve
   * @param array $optParams Optional parameters.
   * @return GoogleCloudIntegrationsV1alphaTestCase
   * @throws \Google\Service\Exception
   */
  public function get($name, $optParams = [])
  {
    $params = ['name' => $name];
    $params = array_merge($params, $optParams);
    return $this->call('get', [$params], GoogleCloudIntegrationsV1alphaTestCase::class);
  }
  /**
   * Lists the results of all functional test executions. The response includes
   * the same information as the [execution
   * log](https://cloud.google.com/application-integration/docs/viewing-logs) in
   * the Integration UI. (testCases.listExecutions)
   *
   * @param string $parent Required. The parent resource name of the test case
   * execution.
   * @param array $optParams Optional parameters.
   *
   * @opt_param string filter Optional. Standard filter field, we support
   * filtering on following fields: test_case_id: the ID of the test case.
   * CreateTimestamp: the execution created time. event_execution_state: the state
   * of the executions. execution_id: the id of the execution. trigger_id: the id
   * of the trigger. parameter_type: the type of the parameters involved in the
   * execution. All fields support for EQUALS, in additional: CreateTimestamp
   * support for LESS_THAN, GREATER_THAN ParameterType support for HAS For
   * example: "parameter_type" HAS \"string\" Also supports operators like AND,
   * OR, NOT For example, trigger_id=\"id1\" AND test_case_id=\"testCaseId\"
   * @opt_param string orderBy Optional. The results would be returned in order
   * you specified here. Currently supporting "last_modified_time" and
   * "create_time".
   * @opt_param int pageSize Optional. The size of entries in the response.
   * @opt_param string pageToken Optional. The token returned in the previous
   * response.
   * @opt_param string readMask Optional. View mask for the response data. If set,
   * only the field specified will be returned as part of the result. If not set,
   * all fields in event execution info will be filled and returned.
   * @opt_param bool truncateParams Optional. If true, the service will truncate
   * the params to only keep the first 1000 characters of string params and empty
   * the executions in order to make response smaller. Only works for UI and when
   * the params fields are not filtered out.
   * @return GoogleCloudIntegrationsV1alphaListTestCaseExecutionsResponse
   * @throws \Google\Service\Exception
   */
  public function listExecutions($parent, $optParams = [])
  {
    $params = ['parent' => $parent];
    $params = array_merge($params, $optParams);
    return $this->call('listExecutions', [$params], GoogleCloudIntegrationsV1alphaListTestCaseExecutionsResponse::class);
  }
  /**
   * Updates a test case (testCases.patch)
   *
   * @param string $name Output only. Auto-generated primary key.
   * @param GoogleCloudIntegrationsV1alphaTestCase $postBody
   * @param array $optParams Optional parameters.
   *
   * @opt_param string updateMask Optional. Field mask specifying the fields in
   * the above integration that have been modified and need to be updated.
   * @return GoogleCloudIntegrationsV1alphaTestCase
   * @throws \Google\Service\Exception
   */
  public function patch($name, GoogleCloudIntegrationsV1alphaTestCase $postBody, $optParams = [])
  {
    $params = ['name' => $name, 'postBody' => $postBody];
    $params = array_merge($params, $optParams);
    return $this->call('patch', [$params], GoogleCloudIntegrationsV1alphaTestCase::class);
  }
}

// Adding a class alias for backwards compatibility with the previous class name.
class_alias(ProjectsLocationsIntegrationsVersionsTestCases::class, 'Google_Service_Integrations_Resource_ProjectsLocationsIntegrationsVersionsTestCases');
