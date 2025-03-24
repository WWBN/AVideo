<?php

declare(strict_types=1);

namespace OneSignal;

use OneSignal\Resolver\ResolverFactory;
use ReflectionMethod;

use function count;

class Notifications extends AbstractApi
{
    private ResolverFactory $resolverFactory;

    public function __construct(OneSignal $client, ResolverFactory $resolverFactory)
    {
        parent::__construct($client);

        $this->resolverFactory = $resolverFactory;
    }

    /**
     * Get information about notification with provided ID.
     *
     * Application authentication key and ID must be set.
     *
     * @param non-empty-string $id Notification ID
     *
     * @return array<mixed>
     */
    public function getOne(string $id): array
    {
        $request = $this->createRequest('GET', "/notifications/$id?app_id={$this->client->getConfig()->getApplicationId()}");
        $request = $request->withHeader('Authorization', "Basic {$this->client->getConfig()->getApplicationAuthKey()}");

        return $this->client->sendRequest($request);
    }

    /**
     * Get information about all notifications.
     *
     * Application authentication key and ID must be set.
     *
     * @param int<0, 50>|null $limit  How many notifications to return (max 50)
     * @param int|null        $offset Results offset (results are sorted by ID)
     *
     * @phpstan-param int $kind   Kind of notifications returned. Default (not set) is all notification types
     *
     * @return array<mixed>
     */
    public function getAll(?int $limit = null, ?int $offset = null/* , ?int $kind = null */): array
    {
        if (func_num_args() > 2 && !is_int(func_get_arg(2))) {
            trigger_deprecation('norkunas/onesignal-php-api', '2.1.0', 'Method %s() will have a third `int $kind` argument. Not defining it or passing a non integer value is deprecated.', __METHOD__);
        } elseif (__CLASS__ !== static::class) {
            $r = new ReflectionMethod($this, __FUNCTION__);

            if (count($r->getParameters()) > 2) {
                trigger_deprecation('norkunas/onesignal-php-api', '2.1.0', 'Method %s() will have a third `int $kind` argument. Not defining it or passing a non integer value is deprecated.', __METHOD__);
            }
        }

        $query = ['app_id' => $this->client->getConfig()->getApplicationId()];

        if ($limit !== null) {
            $query['limit'] = $limit;
        }

        if ($offset !== null) {
            $query['offset'] = $offset;
        }

        if (func_num_args() > 2 && is_int(func_get_arg(2))) {
            $query['kind'] = func_get_arg(2);
        }

        $request = $this->createRequest('GET', '/notifications?'.http_build_query($query));
        $request = $request->withHeader('Authorization', "Basic {$this->client->getConfig()->getApplicationAuthKey()}");

        return $this->client->sendRequest($request);
    }

    /**
     * Send new notification with provided data.
     *
     * Application authentication key and ID must be set.
     *
     * @param array<mixed> $data
     *
     * @return array<mixed>
     */
    public function add(array $data): array
    {
        $resolvedData = $this->resolverFactory->createNotificationResolver()->resolve($data);

        $request = $this->createRequest('POST', '/notifications');
        $request = $request->withHeader('Authorization', "Basic {$this->client->getConfig()->getApplicationAuthKey()}");
        $request = $request->withHeader('Content-Type', 'application/json');
        $request = $request->withBody($this->createStream($resolvedData));

        return $this->client->sendRequest($request);
    }

    /**
     * Open notification.
     *
     * Application authentication key and ID must be set.
     *
     * @param non-empty-string $id Notification ID
     *
     * @return array<mixed>
     */
    public function open(string $id): array
    {
        $request = $this->createRequest('PUT', "/notifications/$id");
        $request = $request->withHeader('Authorization', "Basic {$this->client->getConfig()->getApplicationAuthKey()}");
        $request = $request->withHeader('Content-Type', 'application/json');
        $request = $request->withBody($this->createStream([
            'app_id' => $this->client->getConfig()->getApplicationId(),
            'opened' => true,
        ]));

        return $this->client->sendRequest($request);
    }

    /**
     * Cancel notification.
     *
     * Application authentication key and ID must be set.
     *
     * @param non-empty-string $id Notification ID
     *
     * @return array<mixed>
     */
    public function cancel(string $id): array
    {
        $request = $this->createRequest('DELETE', "/notifications/$id?app_id={$this->client->getConfig()->getApplicationId()}");
        $request = $request->withHeader('Authorization', "Basic {$this->client->getConfig()->getApplicationAuthKey()}");

        return $this->client->sendRequest($request);
    }

    /**
     * View the devices sent a notification.
     *
     * Application authentication key and ID must be set.
     *
     * @param non-empty-string $id   Notification ID
     * @param array<mixed>     $data
     *
     * @return array<mixed>
     */
    public function history(string $id, array $data): array
    {
        $resolvedData = $this->resolverFactory->createNotificationHistoryResolver()->resolve($data);

        $request = $this->createRequest('POST', "/notifications/$id/history");
        $request = $request->withHeader('Authorization', "Basic {$this->client->getConfig()->getApplicationAuthKey()}");
        $request = $request->withHeader('Cache-Control', 'no-cache');
        $request = $request->withHeader('Content-Type', 'application/json');
        $request = $request->withBody($this->createStream($resolvedData));

        return $this->client->sendRequest($request);
    }
}
