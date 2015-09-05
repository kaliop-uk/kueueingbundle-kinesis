<?php

namespace Kaliop\Queueing\Plugins\KinesisBundle\Service;

use Doctrine\Common\Cache\Cache;

/**
 * Uses the Doctrine Cache to store the Sequence Number of stream shards
 */
class DoctrineCacheSequenceNumberStore implements SequenceNumberStoreInterface
{
    /** @var  \Doctrine\Common\Cache\Cache */
    protected $cache;

    public function __construct(Cache $cacheService)
    {
        $this->cache =  $cacheService;
    }

    /**
     * @param $stream
     * @param $shard
     * @param $sequenceNumber
     */
    public function save($stream, $shard, $sequenceNumber)
    {
        $this->cache->save($this->getCacheId($stream, $shard), $sequenceNumber);
    }

    /**
     * @param $stream
     * @param $shard
     * @return string|null
     */
    public function fetch($stream, $shard)
    {
        return $this->cache->fetch($this->getCacheId($stream, $shard));
    }

    protected function getCacheId($stream, $shard)
    {
        return md5(md5($stream).md5($shard));
    }
}