<?php

namespace Kaliop\Queueing\Plugins\KinesisBundle\Adapter\Kinesis;


class Consumer
{
    protected $shard;
    protected $streamName;

    /**
     * Does nothing
     */
    public function setMemoryLimit()
    {
    }

    public function setRoutingKey($key)
    {
        $this->shard = $key;
    }

    public function consume($amount)
    {
// @todo
    }

    /**
     * @param string $queueName
     */
    public function setQueueName($queueName)
    {
        $this->streamName = $queueName;
    }
}