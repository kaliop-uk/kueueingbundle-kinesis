<?php

namespace Kaliop\Queueing\Plugins\KinesisBundle\Service;

interface SequenceNumberStoreInterface
{
    /**
     * @param $stream
     * @param $shard
     * @param $sequenceNumber
     */
    public function save($stream, $shard, $sequenceNumber);

    /**
     * @param $stream
     * @param $shard
     * @return string|null
     */
    public function fetch($stream, $shard);
}