<?php

declare(strict_types=1);

namespace OneSignal;

use OneSignal\Resolver\ResolverFactory;

class Devices extends AbstractApi
{
    public const IOS = 0;
    public const ANDROID = 1;
    public const AMAZON = 2;
    public const WINDOWS_PHONE = 3;
    public const WINDOWS_PHONE_MPNS = 3;
    public const CHROME_APP = 4;
    public const CHROME_WEB = 5;
    public const WINDOWS_PHONE_WNS = 6;
    public const SAFARI = 7;
    public const FIREFOX = 8;
    public const MACOS = 9;
    public const ALEXA = 10;
    public const EMAIL = 11;
    public const HUAWEI = 13;
    public const SMS = 14;

    private ResolverFactory $resolverFactory;

    public function __construct(OneSignal $client, ResolverFactory $resolverFactory)
    {
        parent::__construct($client);

        $this->resolverFactory = $resolverFactory;
    }

    /**
     * Get information about device with provided ID.
     *
     * @param non-empty-string $id Device ID
     *
     * @return array<mixed>
     */
    public function getOne(string $id): array
    {
        $request = $this->createRequest('GET', "/players/$id?app_id={$this->client->getConfig()->getApplicationId()}");

        return $this->client->sendRequest($request);
    }

    /**
     * Get information about all registered devices for your application.
     *
     * Application auth key must be set.
     *
     * @param int<0, 300>|null $limit  How many devices to return. Max is 300. Default is 300
     * @param int|null         $offset Result offset. Default is 0. Results are sorted by id
     *
     * @return array<mixed>
     */
    public function getAll(?int $limit = null, ?int $offset = null): array
    {
        $query = ['app_id' => $this->client->getConfig()->getApplicationId()];

        if ($limit !== null) {
            $query['limit'] = $limit;
        }

        if ($offset !== null) {
            $query['offset'] = $offset;
        }

        $request = $this->createRequest('GET', '/players?'.http_build_query($query));
        $request = $request->withHeader('Authorization', "Basic {$this->client->getConfig()->getApplicationAuthKey()}");

        return $this->client->sendRequest($request);
    }

    /**
     * Register a device for your application.
     *
     * @param array<mixed> $data Device data
     *
     * @return array<mixed>
     */
    public function add(array $data): array
    {
        $resolvedData = $this->resolverFactory->createNewDeviceResolver()->resolve($data);

        $request = $this->createRequest('POST', '/players');
        $request = $request->withHeader('Content-Type', 'application/json');
        $request = $request->withBody($this->createStream($resolvedData));

        return $this->client->sendRequest($request);
    }

    /**
     * Update existing registered device for your application with provided data.
     *
     * @param non-empty-string $id   Device ID
     * @param array<mixed>     $data New device data
     *
     * @return array<mixed>
     */
    public function update(string $id, array $data): array
    {
        $resolvedData = $this->resolverFactory->createExistingDeviceResolver()->resolve($data);

        $request = $this->createRequest('PUT', "/players/$id");
        $request = $request->withHeader('Content-Type', 'application/json');
        $request = $request->withBody($this->createStream($resolvedData));

        return $this->client->sendRequest($request);
    }

    /**
     * Delete existing registered device from your application.
     *
     * OneSignal supports DELETE on the players API endpoint which is not documented in their official documentation
     * Reference: https://documentation.onesignal.com/docs/handling-personal-data#section-deleting-users-or-other-data-from-onesignal
     *
     * Application auth key must be set.
     *
     * @param non-empty-string $id Device ID
     *
     * @return array<mixed>
     */
    public function delete(string $id): array
    {
        $request = $this->createRequest('DELETE', "/players/$id?app_id={$this->client->getConfig()->getApplicationId()}");
        $request = $request->withHeader('Authorization', "Basic {$this->client->getConfig()->getApplicationAuthKey()}");

        return $this->client->sendRequest($request);
    }

    /**
     * Call on new device session in your app.
     *
     * @param non-empty-string $id   Device ID
     * @param array<mixed>     $data Device data
     *
     * @return array<mixed>
     */
    public function onSession(string $id, array $data): array
    {
        $resolvedData = $this->resolverFactory->createDeviceSessionResolver()->resolve($data);

        $request = $this->createRequest('POST', "/players/$id/on_session");
        $request = $request->withHeader('Content-Type', 'application/json');
        $request = $request->withBody($this->createStream($resolvedData));

        return $this->client->sendRequest($request);
    }

    /**
     * Track a new purchase.
     *
     * @param non-empty-string $id   Device ID
     * @param array<mixed>     $data Device data
     *
     * @return array<mixed>
     */
    public function onPurchase(string $id, array $data): array
    {
        $resolvedData = $this->resolverFactory->createDevicePurchaseResolver()->resolve($data);

        $request = $this->createRequest('POST', "/players/$id/on_purchase");
        $request = $request->withHeader('Content-Type', 'application/json');
        $request = $request->withBody($this->createStream($resolvedData));

        return $this->client->sendRequest($request);
    }

    /**
     * Increment the device's total session length.
     *
     * @param non-empty-string $id   Device ID
     * @param array<mixed>     $data Device data
     *
     * @return array<mixed>
     */
    public function onFocus(string $id, array $data): array
    {
        $resolvedData = $this->resolverFactory->createDeviceFocusResolver()->resolve($data);

        $request = $this->createRequest('POST', "/players/$id/on_focus");
        $request = $request->withHeader('Content-Type', 'application/json');
        $request = $request->withBody($this->createStream($resolvedData));

        return $this->client->sendRequest($request);
    }

    /**
     * Export all information about devices in a CSV format for your application.
     *
     * Application auth key must be set.
     *
     * @param array<mixed>          $extraFields     Additional fields that you wish to include.
     *                                               Currently supports: "location", "country", "rooted"
     * @param non-empty-string|null $segmentName     A segment name to filter the scv export by.
     *                                               Only devices from that segment will make it into the export
     * @param int|null              $lastActiveSince An epoch to filter results to users active after this time
     *
     * @return array<mixed>
     */
    public function csvExport(array $extraFields = [], ?string $segmentName = null, ?int $lastActiveSince = null): array
    {
        $request = $this->createRequest('POST', "/players/csv_export?app_id={$this->client->getConfig()->getApplicationId()}");
        $request = $request->withHeader('Authorization', "Basic {$this->client->getConfig()->getApplicationAuthKey()}");
        $request = $request->withHeader('Content-Type', 'application/json');

        $body = ['extra_fields' => $extraFields];

        if ($segmentName !== null) {
            $body['segment_name'] = $segmentName;
        }

        if ($lastActiveSince !== null) {
            $body['last_active_since'] = (string) $lastActiveSince;
        }

        $request = $request->withBody($this->createStream($body));

        return $this->client->sendRequest($request);
    }

    /**
     * Update an existing device's tags using the External User ID.
     *
     * @param non-empty-string $externalUserId External User ID
     * @param array<mixed>     $data           Tags data
     *
     * @return array<mixed>
     */
    public function editTags(string $externalUserId, array $data): array
    {
        $resolvedData = $this->resolverFactory->createDeviceTagsResolver()->resolve($data);

        $request = $this->createRequest('PUT', "/apps/{$this->client->getConfig()->getApplicationId()}/users/$externalUserId");
        $request = $request->withHeader('Content-Type', 'application/json');
        $request = $request->withBody($this->createStream($resolvedData));

        return $this->client->sendRequest($request);
    }
}
