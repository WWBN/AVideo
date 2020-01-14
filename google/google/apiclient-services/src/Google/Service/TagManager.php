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
 * Service definition for TagManager (v1).
 *
 * <p>
 * Accesses Tag Manager accounts and containers.</p>
 *
 * <p>
 * For more information about this service, see the API
 * <a href="https://developers.google.com/tag-manager/api/v1/" target="_blank">Documentation</a>
 * </p>
 *
 * @author Google, Inc.
 */
class Google_Service_TagManager extends Google_Service
{
  /** Delete your Google Tag Manager containers. */
  const TAGMANAGER_DELETE_CONTAINERS =
      "https://www.googleapis.com/auth/tagmanager.delete.containers";
  /** Manage your Google Tag Manager container and its subcomponents, excluding versioning and publishing. */
  const TAGMANAGER_EDIT_CONTAINERS =
      "https://www.googleapis.com/auth/tagmanager.edit.containers";
  /** Manage your Google Tag Manager container versions. */
  const TAGMANAGER_EDIT_CONTAINERVERSIONS =
      "https://www.googleapis.com/auth/tagmanager.edit.containerversions";
  /** View and manage your Google Tag Manager accounts. */
  const TAGMANAGER_MANAGE_ACCOUNTS =
      "https://www.googleapis.com/auth/tagmanager.manage.accounts";
  /** Manage user permissions of your Google Tag Manager account and container. */
  const TAGMANAGER_MANAGE_USERS =
      "https://www.googleapis.com/auth/tagmanager.manage.users";
  /** Publish your Google Tag Manager container versions. */
  const TAGMANAGER_PUBLISH =
      "https://www.googleapis.com/auth/tagmanager.publish";
  /** View your Google Tag Manager container and its subcomponents. */
  const TAGMANAGER_READONLY =
      "https://www.googleapis.com/auth/tagmanager.readonly";

  public $accounts;
  public $accounts_containers;
  public $accounts_containers_environments;
  public $accounts_containers_folders;
  public $accounts_containers_folders_entities;
  public $accounts_containers_move_folders;
  public $accounts_containers_reauthorize_environments;
  public $accounts_containers_tags;
  public $accounts_containers_triggers;
  public $accounts_containers_variables;
  public $accounts_containers_versions;
  public $accounts_permissions;
  
  /**
   * Constructs the internal representation of the TagManager service.
   *
   * @param Google_Client $client
   */
  public function __construct(Google_Client $client)
  {
    parent::__construct($client);
    $this->rootUrl = 'https://www.googleapis.com/';
    $this->servicePath = 'tagmanager/v1/';
    $this->version = 'v1';
    $this->serviceName = 'tagmanager';

    $this->accounts = new Google_Service_TagManager_Resource_Accounts(
        $this,
        $this->serviceName,
        'accounts',
        array(
          'methods' => array(
            'get' => array(
              'path' => 'accounts/{accountId}',
              'httpMethod' => 'GET',
              'parameters' => array(
                'accountId' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
              ),
            ),'list' => array(
              'path' => 'accounts',
              'httpMethod' => 'GET',
              'parameters' => array(),
            ),'update' => array(
              'path' => 'accounts/{accountId}',
              'httpMethod' => 'PUT',
              'parameters' => array(
                'accountId' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
                'fingerprint' => array(
                  'location' => 'query',
                  'type' => 'string',
                ),
              ),
            ),
          )
        )
    );
    $this->accounts_containers = new Google_Service_TagManager_Resource_AccountsContainers(
        $this,
        $this->serviceName,
        'containers',
        array(
          'methods' => array(
            'create' => array(
              'path' => 'accounts/{accountId}/containers',
              'httpMethod' => 'POST',
              'parameters' => array(
                'accountId' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
              ),
            ),'delete' => array(
              'path' => 'accounts/{accountId}/containers/{containerId}',
              'httpMethod' => 'DELETE',
              'parameters' => array(
                'accountId' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
                'containerId' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
              ),
            ),'get' => array(
              'path' => 'accounts/{accountId}/containers/{containerId}',
              'httpMethod' => 'GET',
              'parameters' => array(
                'accountId' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
                'containerId' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
              ),
            ),'list' => array(
              'path' => 'accounts/{accountId}/containers',
              'httpMethod' => 'GET',
              'parameters' => array(
                'accountId' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
              ),
            ),'update' => array(
              'path' => 'accounts/{accountId}/containers/{containerId}',
              'httpMethod' => 'PUT',
              'parameters' => array(
                'accountId' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
                'containerId' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
                'fingerprint' => array(
                  'location' => 'query',
                  'type' => 'string',
                ),
              ),
            ),
          )
        )
    );
    $this->accounts_containers_environments = new Google_Service_TagManager_Resource_AccountsContainersEnvironments(
        $this,
        $this->serviceName,
        'environments',
        array(
          'methods' => array(
            'create' => array(
              'path' => 'accounts/{accountId}/containers/{containerId}/environments',
              'httpMethod' => 'POST',
              'parameters' => array(
                'accountId' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
                'containerId' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
              ),
            ),'delete' => array(
              'path' => 'accounts/{accountId}/containers/{containerId}/environments/{environmentId}',
              'httpMethod' => 'DELETE',
              'parameters' => array(
                'accountId' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
                'containerId' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
                'environmentId' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
              ),
            ),'get' => array(
              'path' => 'accounts/{accountId}/containers/{containerId}/environments/{environmentId}',
              'httpMethod' => 'GET',
              'parameters' => array(
                'accountId' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
                'containerId' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
                'environmentId' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
              ),
            ),'list' => array(
              'path' => 'accounts/{accountId}/containers/{containerId}/environments',
              'httpMethod' => 'GET',
              'parameters' => array(
                'accountId' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
                'containerId' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
              ),
            ),'patch' => array(
              'path' => 'accounts/{accountId}/containers/{containerId}/environments/{environmentId}',
              'httpMethod' => 'PATCH',
              'parameters' => array(
                'accountId' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
                'containerId' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
                'environmentId' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
                'fingerprint' => array(
                  'location' => 'query',
                  'type' => 'string',
                ),
              ),
            ),'update' => array(
              'path' => 'accounts/{accountId}/containers/{containerId}/environments/{environmentId}',
              'httpMethod' => 'PUT',
              'parameters' => array(
                'accountId' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
                'containerId' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
                'environmentId' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
                'fingerprint' => array(
                  'location' => 'query',
                  'type' => 'string',
                ),
              ),
            ),
          )
        )
    );
    $this->accounts_containers_folders = new Google_Service_TagManager_Resource_AccountsContainersFolders(
        $this,
        $this->serviceName,
        'folders',
        array(
          'methods' => array(
            'create' => array(
              'path' => 'accounts/{accountId}/containers/{containerId}/folders',
              'httpMethod' => 'POST',
              'parameters' => array(
                'accountId' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
                'containerId' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
              ),
            ),'delete' => array(
              'path' => 'accounts/{accountId}/containers/{containerId}/folders/{folderId}',
              'httpMethod' => 'DELETE',
              'parameters' => array(
                'accountId' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
                'containerId' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
                'folderId' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
              ),
            ),'get' => array(
              'path' => 'accounts/{accountId}/containers/{containerId}/folders/{folderId}',
              'httpMethod' => 'GET',
              'parameters' => array(
                'accountId' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
                'containerId' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
                'folderId' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
              ),
            ),'list' => array(
              'path' => 'accounts/{accountId}/containers/{containerId}/folders',
              'httpMethod' => 'GET',
              'parameters' => array(
                'accountId' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
                'containerId' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
              ),
            ),'update' => array(
              'path' => 'accounts/{accountId}/containers/{containerId}/folders/{folderId}',
              'httpMethod' => 'PUT',
              'parameters' => array(
                'accountId' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
                'containerId' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
                'folderId' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
                'fingerprint' => array(
                  'location' => 'query',
                  'type' => 'string',
                ),
              ),
            ),
          )
        )
    );
    $this->accounts_containers_folders_entities = new Google_Service_TagManager_Resource_AccountsContainersFoldersEntities(
        $this,
        $this->serviceName,
        'entities',
        array(
          'methods' => array(
            'list' => array(
              'path' => 'accounts/{accountId}/containers/{containerId}/folders/{folderId}/entities',
              'httpMethod' => 'GET',
              'parameters' => array(
                'accountId' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
                'containerId' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
                'folderId' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
              ),
            ),
          )
        )
    );
    $this->accounts_containers_move_folders = new Google_Service_TagManager_Resource_AccountsContainersMoveFolders(
        $this,
        $this->serviceName,
        'move_folders',
        array(
          'methods' => array(
            'update' => array(
              'path' => 'accounts/{accountId}/containers/{containerId}/move_folders/{folderId}',
              'httpMethod' => 'PUT',
              'parameters' => array(
                'accountId' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
                'containerId' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
                'folderId' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
                'tagId' => array(
                  'location' => 'query',
                  'type' => 'string',
                  'repeated' => true,
                ),
                'triggerId' => array(
                  'location' => 'query',
                  'type' => 'string',
                  'repeated' => true,
                ),
                'variableId' => array(
                  'location' => 'query',
                  'type' => 'string',
                  'repeated' => true,
                ),
              ),
            ),
          )
        )
    );
    $this->accounts_containers_reauthorize_environments = new Google_Service_TagManager_Resource_AccountsContainersReauthorizeEnvironments(
        $this,
        $this->serviceName,
        'reauthorize_environments',
        array(
          'methods' => array(
            'update' => array(
              'path' => 'accounts/{accountId}/containers/{containerId}/reauthorize_environments/{environmentId}',
              'httpMethod' => 'PUT',
              'parameters' => array(
                'accountId' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
                'containerId' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
                'environmentId' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
              ),
            ),
          )
        )
    );
    $this->accounts_containers_tags = new Google_Service_TagManager_Resource_AccountsContainersTags(
        $this,
        $this->serviceName,
        'tags',
        array(
          'methods' => array(
            'create' => array(
              'path' => 'accounts/{accountId}/containers/{containerId}/tags',
              'httpMethod' => 'POST',
              'parameters' => array(
                'accountId' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
                'containerId' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
              ),
            ),'delete' => array(
              'path' => 'accounts/{accountId}/containers/{containerId}/tags/{tagId}',
              'httpMethod' => 'DELETE',
              'parameters' => array(
                'accountId' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
                'containerId' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
                'tagId' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
              ),
            ),'get' => array(
              'path' => 'accounts/{accountId}/containers/{containerId}/tags/{tagId}',
              'httpMethod' => 'GET',
              'parameters' => array(
                'accountId' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
                'containerId' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
                'tagId' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
              ),
            ),'list' => array(
              'path' => 'accounts/{accountId}/containers/{containerId}/tags',
              'httpMethod' => 'GET',
              'parameters' => array(
                'accountId' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
                'containerId' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
              ),
            ),'update' => array(
              'path' => 'accounts/{accountId}/containers/{containerId}/tags/{tagId}',
              'httpMethod' => 'PUT',
              'parameters' => array(
                'accountId' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
                'containerId' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
                'tagId' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
                'fingerprint' => array(
                  'location' => 'query',
                  'type' => 'string',
                ),
              ),
            ),
          )
        )
    );
    $this->accounts_containers_triggers = new Google_Service_TagManager_Resource_AccountsContainersTriggers(
        $this,
        $this->serviceName,
        'triggers',
        array(
          'methods' => array(
            'create' => array(
              'path' => 'accounts/{accountId}/containers/{containerId}/triggers',
              'httpMethod' => 'POST',
              'parameters' => array(
                'accountId' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
                'containerId' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
              ),
            ),'delete' => array(
              'path' => 'accounts/{accountId}/containers/{containerId}/triggers/{triggerId}',
              'httpMethod' => 'DELETE',
              'parameters' => array(
                'accountId' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
                'containerId' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
                'triggerId' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
              ),
            ),'get' => array(
              'path' => 'accounts/{accountId}/containers/{containerId}/triggers/{triggerId}',
              'httpMethod' => 'GET',
              'parameters' => array(
                'accountId' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
                'containerId' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
                'triggerId' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
              ),
            ),'list' => array(
              'path' => 'accounts/{accountId}/containers/{containerId}/triggers',
              'httpMethod' => 'GET',
              'parameters' => array(
                'accountId' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
                'containerId' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
              ),
            ),'update' => array(
              'path' => 'accounts/{accountId}/containers/{containerId}/triggers/{triggerId}',
              'httpMethod' => 'PUT',
              'parameters' => array(
                'accountId' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
                'containerId' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
                'triggerId' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
                'fingerprint' => array(
                  'location' => 'query',
                  'type' => 'string',
                ),
              ),
            ),
          )
        )
    );
    $this->accounts_containers_variables = new Google_Service_TagManager_Resource_AccountsContainersVariables(
        $this,
        $this->serviceName,
        'variables',
        array(
          'methods' => array(
            'create' => array(
              'path' => 'accounts/{accountId}/containers/{containerId}/variables',
              'httpMethod' => 'POST',
              'parameters' => array(
                'accountId' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
                'containerId' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
              ),
            ),'delete' => array(
              'path' => 'accounts/{accountId}/containers/{containerId}/variables/{variableId}',
              'httpMethod' => 'DELETE',
              'parameters' => array(
                'accountId' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
                'containerId' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
                'variableId' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
              ),
            ),'get' => array(
              'path' => 'accounts/{accountId}/containers/{containerId}/variables/{variableId}',
              'httpMethod' => 'GET',
              'parameters' => array(
                'accountId' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
                'containerId' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
                'variableId' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
              ),
            ),'list' => array(
              'path' => 'accounts/{accountId}/containers/{containerId}/variables',
              'httpMethod' => 'GET',
              'parameters' => array(
                'accountId' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
                'containerId' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
              ),
            ),'update' => array(
              'path' => 'accounts/{accountId}/containers/{containerId}/variables/{variableId}',
              'httpMethod' => 'PUT',
              'parameters' => array(
                'accountId' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
                'containerId' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
                'variableId' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
                'fingerprint' => array(
                  'location' => 'query',
                  'type' => 'string',
                ),
              ),
            ),
          )
        )
    );
    $this->accounts_containers_versions = new Google_Service_TagManager_Resource_AccountsContainersVersions(
        $this,
        $this->serviceName,
        'versions',
        array(
          'methods' => array(
            'create' => array(
              'path' => 'accounts/{accountId}/containers/{containerId}/versions',
              'httpMethod' => 'POST',
              'parameters' => array(
                'accountId' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
                'containerId' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
              ),
            ),'delete' => array(
              'path' => 'accounts/{accountId}/containers/{containerId}/versions/{containerVersionId}',
              'httpMethod' => 'DELETE',
              'parameters' => array(
                'accountId' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
                'containerId' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
                'containerVersionId' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
              ),
            ),'get' => array(
              'path' => 'accounts/{accountId}/containers/{containerId}/versions/{containerVersionId}',
              'httpMethod' => 'GET',
              'parameters' => array(
                'accountId' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
                'containerId' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
                'containerVersionId' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
              ),
            ),'list' => array(
              'path' => 'accounts/{accountId}/containers/{containerId}/versions',
              'httpMethod' => 'GET',
              'parameters' => array(
                'accountId' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
                'containerId' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
                'headers' => array(
                  'location' => 'query',
                  'type' => 'boolean',
                ),
                'includeDeleted' => array(
                  'location' => 'query',
                  'type' => 'boolean',
                ),
              ),
            ),'publish' => array(
              'path' => 'accounts/{accountId}/containers/{containerId}/versions/{containerVersionId}/publish',
              'httpMethod' => 'POST',
              'parameters' => array(
                'accountId' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
                'containerId' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
                'containerVersionId' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
                'fingerprint' => array(
                  'location' => 'query',
                  'type' => 'string',
                ),
              ),
            ),'restore' => array(
              'path' => 'accounts/{accountId}/containers/{containerId}/versions/{containerVersionId}/restore',
              'httpMethod' => 'POST',
              'parameters' => array(
                'accountId' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
                'containerId' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
                'containerVersionId' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
              ),
            ),'undelete' => array(
              'path' => 'accounts/{accountId}/containers/{containerId}/versions/{containerVersionId}/undelete',
              'httpMethod' => 'POST',
              'parameters' => array(
                'accountId' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
                'containerId' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
                'containerVersionId' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
              ),
            ),'update' => array(
              'path' => 'accounts/{accountId}/containers/{containerId}/versions/{containerVersionId}',
              'httpMethod' => 'PUT',
              'parameters' => array(
                'accountId' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
                'containerId' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
                'containerVersionId' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
                'fingerprint' => array(
                  'location' => 'query',
                  'type' => 'string',
                ),
              ),
            ),
          )
        )
    );
    $this->accounts_permissions = new Google_Service_TagManager_Resource_AccountsPermissions(
        $this,
        $this->serviceName,
        'permissions',
        array(
          'methods' => array(
            'create' => array(
              'path' => 'accounts/{accountId}/permissions',
              'httpMethod' => 'POST',
              'parameters' => array(
                'accountId' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
              ),
            ),'delete' => array(
              'path' => 'accounts/{accountId}/permissions/{permissionId}',
              'httpMethod' => 'DELETE',
              'parameters' => array(
                'accountId' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
                'permissionId' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
              ),
            ),'get' => array(
              'path' => 'accounts/{accountId}/permissions/{permissionId}',
              'httpMethod' => 'GET',
              'parameters' => array(
                'accountId' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
                'permissionId' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
              ),
            ),'list' => array(
              'path' => 'accounts/{accountId}/permissions',
              'httpMethod' => 'GET',
              'parameters' => array(
                'accountId' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
              ),
            ),'update' => array(
              'path' => 'accounts/{accountId}/permissions/{permissionId}',
              'httpMethod' => 'PUT',
              'parameters' => array(
                'accountId' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
                'permissionId' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
              ),
            ),
          )
        )
    );
  }
}
