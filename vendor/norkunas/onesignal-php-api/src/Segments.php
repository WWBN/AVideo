<?php

declare(strict_types=1);

namespace OneSignal;

use OneSignal\Dto\Segment\CreateSegment;
use OneSignal\Dto\Segment\ListSegments;
use OneSignal\Response\Segment\CreateSegmentResponse;
use OneSignal\Response\Segment\DeleteSegmentResponse;
use OneSignal\Response\Segment\ListSegmentsResponse;

class Segments extends AbstractApi
{
    public function __construct(OneSignal $client)
    {
        parent::__construct($client);
    }

    /**
     * Get information about all segments.
     *
     * Application authentication key and ID must be set.
     */
    public function list(ListSegments $listSegmentsDto): ListSegmentsResponse
    {
        $appId = $this->client->getConfig()->getApplicationId();

        $request = $this->createRequest('GET', '/apps/'.$appId.'/segments?'.http_build_query($listSegmentsDto->toArray()));
        $request = $request->withHeader('Authorization', "Basic {$this->client->getConfig()->getApplicationAuthKey()}");

        return ListSegmentsResponse::makeFromResponse($this->client->makeRequest($request));
    }

    /**
     * Create new segment with provided data.
     *
     * Application authentication key and ID must be set.
     */
    public function create(CreateSegment $createSegmentDto): CreateSegmentResponse
    {
        $appId = $this->client->getConfig()->getApplicationId();

        $request = $this->createRequest('POST', '/apps/'.$appId.'/segments');
        $request = $request->withHeader('Authorization', "Basic {$this->client->getConfig()->getApplicationAuthKey()}");
        $request = $request->withHeader('Content-Type', 'application/json');
        $request = $request->withBody($this->createStream($createSegmentDto->toArray()));

        return CreateSegmentResponse::makeFromResponse($this->client->makeRequest($request));
    }

    /**
     * Delete segment.
     *
     * Application authentication key and ID must be set.
     *
     * @param non-empty-string $id Segment ID
     */
    public function delete(string $id): DeleteSegmentResponse
    {
        $appId = $this->client->getConfig()->getApplicationId();

        $request = $this->createRequest('DELETE', '/apps/'.$appId.'/segments/'.$id);
        $request = $request->withHeader('Authorization', "Basic {$this->client->getConfig()->getApplicationAuthKey()}");

        return DeleteSegmentResponse::makeFromResponse($this->client->makeRequest($request));
    }
}
