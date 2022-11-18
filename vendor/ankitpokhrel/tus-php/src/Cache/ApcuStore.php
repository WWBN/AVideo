<?php

namespace TusPhp\Cache;

use APCUIterator;
use Carbon\Carbon;

class ApcuStore extends AbstractCache
{
    /**
     * {@inheritDoc}
     */
    public function get(string $key, bool $withExpired = false)
    {
        $contents = apcu_fetch($this->getActualCacheKey($key));

        if ( ! $contents) {
            return null;
        }

        if ($withExpired) {
            return $contents ?: null;
        }

        $isExpired = Carbon::parse($contents['expires_at'])->lt(Carbon::now());

        return $isExpired ? null : $contents;
    }

    /**
     * {@inheritDoc}
     */
    public function set(string $key, $value)
    {
        $contents = $this->get($key) ?? [];

        if (\is_array($value)) {
            $contents = $value + $contents;
        } else {
            $contents[] = $value;
        }

        return apcu_store($this->getActualCacheKey($key), $contents, $this->getTtl());
    }

    /**
     * {@inheritDoc}
     */
    public function delete(string $key): bool
    {
        return true === apcu_delete($this->getActualCacheKey($key));
    }

    /**
     * {@inheritDoc}
     */
    public function keys(): array
    {
        $iterator = new APCUIterator('/^' . preg_quote($this->getPrefix()) . '.*$/', APC_ITER_KEY);

        return array_column(iterator_to_array($iterator, false), 'key');
    }

    /**
     * Get actual cache key with prefix.
     *
     * @param string $key
     *
     * @return string
     */
    protected function getActualCacheKey(string $key): string
    {
        $prefix = $this->getPrefix();

        if (false === strpos($key, $prefix)) {
            $key = $prefix . $key;
        }

        return $key;
    }
}
