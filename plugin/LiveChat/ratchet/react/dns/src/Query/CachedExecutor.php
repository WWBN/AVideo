<?php

namespace React\Dns\Query;

use React\Dns\Model\Message;

class CachedExecutor implements ExecutorInterface
{
    private $executor;
    private $cache;

    public function __construct(ExecutorInterface $executor, RecordCache $cache)
    {
        $this->executor = $executor;
        $this->cache = $cache;
    }

    public function query($nameserver, Query $query)
    {
        $executor = $this->executor;
        $cache = $this->cache;

        return $this->cache
            ->lookup($query)
            ->then(
                function ($cachedRecords) use ($query) {
                    return Message::createResponseWithAnswersForQuery($query, $cachedRecords);
                },
                function () use ($executor, $cache, $nameserver, $query) {
                    return $executor
                        ->query($nameserver, $query)
                        ->then(function ($response) use ($cache, $query) {
                            $cache->storeResponseMessage($query->currentTime, $response);
                            return $response;
                        });
                }
            );
    }

    /**
     * @deprecated unused, exists for BC only
     */
    public function buildResponse(Query $query, array $cachedRecords)
    {
        return Message::createResponseWithAnswersForQuery($query, $cachedRecords);
    }

    /**
     * @deprecated unused, exists for BC only
     */
    protected function generateId()
    {
        return mt_rand(0, 0xffff);
    }
}
