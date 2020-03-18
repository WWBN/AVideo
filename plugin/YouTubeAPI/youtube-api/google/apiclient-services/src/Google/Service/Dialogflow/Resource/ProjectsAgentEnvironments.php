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

/**
 * The "environments" collection of methods.
 * Typical usage is:
 *  <code>
 *   $dialogflowService = new Google_Service_Dialogflow(...);
 *   $environments = $dialogflowService->environments;
 *  </code>
 */
class Google_Service_Dialogflow_Resource_ProjectsAgentEnvironments extends Google_Service_Resource
{
  /**
   * Creates an agent environment. (environments.create)
   *
   * @param string $parent Required. The agent to create a environment for.
   * Format: `projects//agent`.
   * @param Google_Service_Dialogflow_GoogleCloudDialogflowV2Environment $postBody
   * @param array $optParams Optional parameters.
   *
   * @opt_param string environmentId Required. The unique id of the new
   * environment.
   * @return Google_Service_Dialogflow_GoogleCloudDialogflowV2Environment
   */
  public function create($parent, Google_Service_Dialogflow_GoogleCloudDialogflowV2Environment $postBody, $optParams = array())
  {
    $params = array('parent' => $parent, 'postBody' => $postBody);
    $params = array_merge($params, $optParams);
    return $this->call('create', array($params), "Google_Service_Dialogflow_GoogleCloudDialogflowV2Environment");
  }
  /**
   * Deletes the specified agent environment. (environments.delete)
   *
   * @param string $name Required. The name of the environment to delete. Format:
   * `projects//agent/environments/`.
   * @param array $optParams Optional parameters.
   * @return Google_Service_Dialogflow_GoogleProtobufEmpty
   */
  public function delete($name, $optParams = array())
  {
    $params = array('name' => $name);
    $params = array_merge($params, $optParams);
    return $this->call('delete', array($params), "Google_Service_Dialogflow_GoogleProtobufEmpty");
  }
  /**
   * Retrieves the specified agent environment. (environments.get)
   *
   * @param string $name Required. The name of the environment. Format:
   * `projects//agent/environments/`.
   * @param array $optParams Optional parameters.
   * @return Google_Service_Dialogflow_GoogleCloudDialogflowV2Environment
   */
  public function get($name, $optParams = array())
  {
    $params = array('name' => $name);
    $params = array_merge($params, $optParams);
    return $this->call('get', array($params), "Google_Service_Dialogflow_GoogleCloudDialogflowV2Environment");
  }
  /**
   * Gets the history of the specified environment. (environments.getHistory)
   *
   * @param string $parent Required. The name of the environment to retrieve
   * history for. Format: `projects//agent/environments/`.
   * @param array $optParams Optional parameters.
   *
   * @opt_param string pageToken Optional. The next_page_token value returned from
   * a previous list request.
   * @opt_param int pageSize Optional. The maximum number of items to return in a
   * single page. By default 100 and at most 1000.
   * @return Google_Service_Dialogflow_GoogleCloudDialogflowV2EnvironmentHistory
   */
  public function getHistory($parent, $optParams = array())
  {
    $params = array('parent' => $parent);
    $params = array_merge($params, $optParams);
    return $this->call('getHistory', array($params), "Google_Service_Dialogflow_GoogleCloudDialogflowV2EnvironmentHistory");
  }
  /**
   * Returns the list of all environments of the specified agent.
   * (environments.listProjectsAgentEnvironments)
   *
   * @param string $parent Required. The agent to list all environments from.
   * Format: `projects//agent`.
   * @param array $optParams Optional parameters.
   *
   * @opt_param string pageToken Optional. The next_page_token value returned from
   * a previous list request.
   * @opt_param int pageSize Optional. The maximum number of items to return in a
   * single page. By default 100 and at most 1000.
   * @return Google_Service_Dialogflow_GoogleCloudDialogflowV2ListEnvironmentsResponse
   */
  public function listProjectsAgentEnvironments($parent, $optParams = array())
  {
    $params = array('parent' => $parent);
    $params = array_merge($params, $optParams);
    return $this->call('list', array($params), "Google_Service_Dialogflow_GoogleCloudDialogflowV2ListEnvironmentsResponse");
  }
  /**
   * Updates the specified agent environment.
   *
   * This method allows you to deploy new agent versions into the environment.
   * When a environment is pointed to a new agent version by setting
   * `environment.agent_version`, the environment is temporarily set to the
   * `LOADING` state. During that time, the environment keeps on serving the
   * previous version of the agent. After the new agent version is done loading,
   * the environment is set back to the `RUNNING` state. (environments.patch)
   *
   * @param string $name Output only. The unique identifier of this agent
   * environment. Format: `projects//agent/environments/`.
   * @param Google_Service_Dialogflow_GoogleCloudDialogflowV2Environment $postBody
   * @param array $optParams Optional parameters.
   *
   * @opt_param string updateMask Optional. The mask to control which fields get
   * updated.
   * @return Google_Service_Dialogflow_GoogleCloudDialogflowV2Environment
   */
  public function patch($name, Google_Service_Dialogflow_GoogleCloudDialogflowV2Environment $postBody, $optParams = array())
  {
    $params = array('name' => $name, 'postBody' => $postBody);
    $params = array_merge($params, $optParams);
    return $this->call('patch', array($params), "Google_Service_Dialogflow_GoogleCloudDialogflowV2Environment");
  }
}
