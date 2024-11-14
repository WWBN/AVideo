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

namespace Google\Service\Contactcenterinsights\Resource;

use Google\Service\Contactcenterinsights\GoogleCloudContactcenterinsightsV1Conversation;
use Google\Service\Contactcenterinsights\GoogleCloudContactcenterinsightsV1ListConversationsResponse;
use Google\Service\Contactcenterinsights\GoogleLongrunningOperation;
use Google\Service\Contactcenterinsights\GoogleProtobufEmpty;

/**
 * The "conversations" collection of methods.
 * Typical usage is:
 *  <code>
 *   $contactcenterinsightsService = new Google\Service\Contactcenterinsights(...);
 *   $conversations = $contactcenterinsightsService->projects_locations_authorizedViewSets_authorizedViews_conversations;
 *  </code>
 */
class ProjectsLocationsAuthorizedViewSetsAuthorizedViewsConversations extends \Google\Service\Resource
{
  /**
   * Creates a conversation. Note that this method does not support audio
   * transcription or redaction. Use `conversations.upload` instead.
   * (conversations.create)
   *
   * @param string $parent Required. The parent resource of the conversation.
   * @param GoogleCloudContactcenterinsightsV1Conversation $postBody
   * @param array $optParams Optional parameters.
   *
   * @opt_param string conversationId A unique ID for the new conversation. This
   * ID will become the final component of the conversation's resource name. If no
   * ID is specified, a server-generated ID will be used. This value should be
   * 4-64 characters and must match the regular expression `^[a-z0-9-]{4,64}$`.
   * Valid characters are `a-z-`
   * @return GoogleCloudContactcenterinsightsV1Conversation
   * @throws \Google\Service\Exception
   */
  public function create($parent, GoogleCloudContactcenterinsightsV1Conversation $postBody, $optParams = [])
  {
    $params = ['parent' => $parent, 'postBody' => $postBody];
    $params = array_merge($params, $optParams);
    return $this->call('create', [$params], GoogleCloudContactcenterinsightsV1Conversation::class);
  }
  /**
   * Deletes a conversation. (conversations.delete)
   *
   * @param string $name Required. The name of the conversation to delete.
   * @param array $optParams Optional parameters.
   *
   * @opt_param bool force If set to true, all of this conversation's analyses
   * will also be deleted. Otherwise, the request will only succeed if the
   * conversation has no analyses.
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
   * Gets a conversation. (conversations.get)
   *
   * @param string $name Required. The name of the conversation to get.
   * @param array $optParams Optional parameters.
   *
   * @opt_param string view The level of details of the conversation. Default is
   * `FULL`.
   * @return GoogleCloudContactcenterinsightsV1Conversation
   * @throws \Google\Service\Exception
   */
  public function get($name, $optParams = [])
  {
    $params = ['name' => $name];
    $params = array_merge($params, $optParams);
    return $this->call('get', [$params], GoogleCloudContactcenterinsightsV1Conversation::class);
  }
  /**
   * Lists conversations. (conversations.listProjectsLocationsAuthorizedViewSetsAu
   * thorizedViewsConversations)
   *
   * @param string $parent Required. The parent resource of the conversation.
   * @param array $optParams Optional parameters.
   *
   * @opt_param string filter A filter to reduce results to a specific subset.
   * Useful for querying conversations with specific properties.
   * @opt_param string orderBy Optional. The attribute by which to order
   * conversations in the response. If empty, conversations will be ordered by
   * descending creation time. Supported values are one of the following: *
   * create_time * customer_satisfaction_rating * duration * latest_analysis *
   * start_time * turn_count The default sort order is ascending. To specify
   * order, append `asc` or `desc` (`create_time desc`). For more details, see
   * [Google AIPs Ordering](https://google.aip.dev/132#ordering).
   * @opt_param int pageSize The maximum number of conversations to return in the
   * response. A valid page size ranges from 0 to 100,000 inclusive. If the page
   * size is zero or unspecified, a default page size of 100 will be chosen. Note
   * that a call might return fewer results than the requested page size.
   * @opt_param string pageToken The value returned by the last
   * `ListConversationsResponse`. This value indicates that this is a continuation
   * of a prior `ListConversations` call and that the system should return the
   * next page of data.
   * @opt_param string view The level of details of the conversation. Default is
   * `BASIC`.
   * @return GoogleCloudContactcenterinsightsV1ListConversationsResponse
   * @throws \Google\Service\Exception
   */
  public function listProjectsLocationsAuthorizedViewSetsAuthorizedViewsConversations($parent, $optParams = [])
  {
    $params = ['parent' => $parent];
    $params = array_merge($params, $optParams);
    return $this->call('list', [$params], GoogleCloudContactcenterinsightsV1ListConversationsResponse::class);
  }
  /**
   * Updates a conversation. (conversations.patch)
   *
   * @param string $name Immutable. The resource name of the conversation. Format:
   * projects/{project}/locations/{location}/conversations/{conversation}
   * @param GoogleCloudContactcenterinsightsV1Conversation $postBody
   * @param array $optParams Optional parameters.
   *
   * @opt_param string updateMask The list of fields to be updated. All possible
   * fields can be updated by passing `*`, or a subset of the following updateable
   * fields can be provided: * `agent_id` * `language_code` * `labels` *
   * `metadata` * `quality_metadata` * `call_metadata` * `start_time` *
   * `expire_time` or `ttl` * `data_source.gcs_source.audio_uri` or
   * `data_source.dialogflow_source.audio_uri`
   * @return GoogleCloudContactcenterinsightsV1Conversation
   * @throws \Google\Service\Exception
   */
  public function patch($name, GoogleCloudContactcenterinsightsV1Conversation $postBody, $optParams = [])
  {
    $params = ['name' => $name, 'postBody' => $postBody];
    $params = array_merge($params, $optParams);
    return $this->call('patch', [$params], GoogleCloudContactcenterinsightsV1Conversation::class);
  }
  /**
   * Create a long-running conversation upload operation. This method differs from
   * `CreateConversation` by allowing audio transcription and optional DLP
   * redaction. (conversations.upload)
   *
   * @param string $parent Required. The parent resource of the conversation.
   * @param GoogleCloudContactcenterinsightsV1Conversation $postBody
   * @param array $optParams Optional parameters.
   *
   * @opt_param string conversationId Optional. A unique ID for the new
   * conversation. This ID will become the final component of the conversation's
   * resource name. If no ID is specified, a server-generated ID will be used.
   * This value should be 4-64 characters and must match the regular expression
   * `^[a-z0-9-]{4,64}$`. Valid characters are `a-z-`
   * @opt_param string redactionConfig.deidentifyTemplate The fully-qualified DLP
   * deidentify template resource name. Format:
   * `projects/{project}/deidentifyTemplates/{template}`
   * @opt_param string redactionConfig.inspectTemplate The fully-qualified DLP
   * inspect template resource name. Format:
   * `projects/{project}/locations/{location}/inspectTemplates/{template}`
   * @opt_param string speechConfig.speechRecognizer The fully-qualified Speech
   * Recognizer resource name. Format:
   * `projects/{project_id}/locations/{location}/recognizer/{recognizer}`
   * @return GoogleLongrunningOperation
   * @throws \Google\Service\Exception
   */
  public function upload($parent, GoogleCloudContactcenterinsightsV1Conversation $postBody, $optParams = [])
  {
    $params = ['parent' => $parent, 'postBody' => $postBody];
    $params = array_merge($params, $optParams);
    return $this->call('upload', [$params], GoogleLongrunningOperation::class);
  }
}

// Adding a class alias for backwards compatibility with the previous class name.
class_alias(ProjectsLocationsAuthorizedViewSetsAuthorizedViewsConversations::class, 'Google_Service_Contactcenterinsights_Resource_ProjectsLocationsAuthorizedViewSetsAuthorizedViewsConversations');
