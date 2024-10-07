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

namespace Google\Service\Cloudchannel\Resource;

use Google\Service\Cloudchannel\GoogleCloudChannelV1ListSubscribersResponse;
use Google\Service\Cloudchannel\GoogleCloudChannelV1RegisterSubscriberResponse;
use Google\Service\Cloudchannel\GoogleCloudChannelV1UnregisterSubscriberResponse;

/**
 * The "integrators" collection of methods.
 * Typical usage is:
 *  <code>
 *   $cloudchannelService = new Google\Service\Cloudchannel(...);
 *   $integrators = $cloudchannelService->integrators;
 *  </code>
 */
class Integrators extends \Google\Service\Resource
{
  /**
   * Lists service accounts with subscriber privileges on the Cloud Pub/Sub topic
   * created for this Channel Services account. Possible error codes: *
   * PERMISSION_DENIED: The reseller account making the request and the provided
   * reseller account are different, or the impersonated user is not a super
   * admin. * INVALID_ARGUMENT: Required request parameters are missing or
   * invalid. * NOT_FOUND: The topic resource doesn't exist. * INTERNAL: Any non-
   * user error related to a technical issue in the backend. Contact Cloud Channel
   * support. * UNKNOWN: Any non-user error related to a technical issue in the
   * backend. Contact Cloud Channel support. Return value: A list of service email
   * addresses. (integrators.listSubscribers)
   *
   * @param string $integrator Optional. Resource name of the integrator.
   * @param array $optParams Optional parameters.
   *
   * @opt_param string account Optional. Resource name of the account.
   * @opt_param int pageSize Optional. The maximum number of service accounts to
   * return. The service may return fewer than this value. If unspecified, returns
   * at most 100 service accounts. The maximum value is 1000; the server will
   * coerce values above 1000.
   * @opt_param string pageToken Optional. A page token, received from a previous
   * `ListSubscribers` call. Provide this to retrieve the subsequent page. When
   * paginating, all other parameters provided to `ListSubscribers` must match the
   * call that provided the page token.
   * @return GoogleCloudChannelV1ListSubscribersResponse
   * @throws \Google\Service\Exception
   */
  public function listSubscribers($integrator, $optParams = [])
  {
    $params = ['integrator' => $integrator];
    $params = array_merge($params, $optParams);
    return $this->call('listSubscribers', [$params], GoogleCloudChannelV1ListSubscribersResponse::class);
  }
  /**
   * Registers a service account with subscriber privileges on the Cloud Pub/Sub
   * topic for this Channel Services account. After you create a subscriber, you
   * get the events through SubscriberEvent Possible error codes: *
   * PERMISSION_DENIED: The reseller account making the request and the provided
   * reseller account are different, or the impersonated user is not a super
   * admin. * INVALID_ARGUMENT: Required request parameters are missing or
   * invalid. * INTERNAL: Any non-user error related to a technical issue in the
   * backend. Contact Cloud Channel support. * UNKNOWN: Any non-user error related
   * to a technical issue in the backend. Contact Cloud Channel support. Return
   * value: The topic name with the registered service email address.
   * (integrators.registerSubscriber)
   *
   * @param string $integrator Optional. Resource name of the integrator.
   * @param array $optParams Optional parameters.
   *
   * @opt_param string account Optional. Resource name of the account.
   * @opt_param string serviceAccount Required. Service account that provides
   * subscriber access to the registered topic.
   * @return GoogleCloudChannelV1RegisterSubscriberResponse
   * @throws \Google\Service\Exception
   */
  public function registerSubscriber($integrator, $optParams = [])
  {
    $params = ['integrator' => $integrator];
    $params = array_merge($params, $optParams);
    return $this->call('registerSubscriber', [$params], GoogleCloudChannelV1RegisterSubscriberResponse::class);
  }
  /**
   * Unregisters a service account with subscriber privileges on the Cloud Pub/Sub
   * topic created for this Channel Services account. If there are no service
   * accounts left with subscriber privileges, this deletes the topic. You can
   * call ListSubscribers to check for these accounts. Possible error codes: *
   * PERMISSION_DENIED: The reseller account making the request and the provided
   * reseller account are different, or the impersonated user is not a super
   * admin. * INVALID_ARGUMENT: Required request parameters are missing or
   * invalid. * NOT_FOUND: The topic resource doesn't exist. * INTERNAL: Any non-
   * user error related to a technical issue in the backend. Contact Cloud Channel
   * support. * UNKNOWN: Any non-user error related to a technical issue in the
   * backend. Contact Cloud Channel support. Return value: The topic name that
   * unregistered the service email address. Returns a success response if the
   * service email address wasn't registered with the topic.
   * (integrators.unregisterSubscriber)
   *
   * @param string $integrator Optional. Resource name of the integrator.
   * @param array $optParams Optional parameters.
   *
   * @opt_param string account Optional. Resource name of the account.
   * @opt_param string serviceAccount Required. Service account to unregister from
   * subscriber access to the topic.
   * @return GoogleCloudChannelV1UnregisterSubscriberResponse
   * @throws \Google\Service\Exception
   */
  public function unregisterSubscriber($integrator, $optParams = [])
  {
    $params = ['integrator' => $integrator];
    $params = array_merge($params, $optParams);
    return $this->call('unregisterSubscriber', [$params], GoogleCloudChannelV1UnregisterSubscriberResponse::class);
  }
}

// Adding a class alias for backwards compatibility with the previous class name.
class_alias(Integrators::class, 'Google_Service_Cloudchannel_Resource_Integrators');
