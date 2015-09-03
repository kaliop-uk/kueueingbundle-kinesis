<?php

namespace Kaliop\Queueing\Plugins\KinesisBundle\Adapter\Kinesis;

use Kaliop\QueueingBundle\Queue\MessageConsumerInterface;
use Kaliop\QueueingBundle\Queue\ConsumerInterface;

class Consumer implements ConsumerInterface
{
    /** @var  \Aws\Kinesis\KinesisClient */
    protected $client;
    protected $shard;
    protected $streamName;
    protected $callback;

    public function __construct(array $config)
    {
        $this->client = new KinesisClient($config);
    }

    /**
     * Does nothing
     */
    public function setMemoryLimit($limit)
    {
    }

    public function setRoutingKey($key)
    {
        $this->shard = $key;
    }

    public function setCallback(MessageConsumerInterface $callback)
    {
        $this->callback = $callback;
    }

    /**
     * @see http://docs.aws.amazon.com/aws-sdk-php/v2/api/class-Aws.Kinesis.KinesisClient.html#_getRecords
     * @param int $amount
     * @return nothing
     */
    public function consume($amount)
    {
        $iterator = $this->client->getShardIterator(array(
            'StreamName' => $this->streamName,
            'ShardId' => $this->shard,
            // ShardIteratorType is required
            'ShardIteratorType' => 'TRIM_HORIZON'
            //'StartingSequenceNumber' => 'string',  //...
        ));

        while(true) {
            $result = $this->client->getRecords(array(
                'ShardIterator' => $iterator,
                'Limit' => $amount,
            ));

            foreach($result->get('NextShardIterator') as $record) {
                $data = $record['data'];
                unset($record['data']);
                $this->callback->receive(new Message($data, $record));
            }

            if ($amount > 0) {
                return;
            }

            $iterator = $result->get('NextShardIterator');
            if ($iterator == null) {
                // shard is closed
                return;
            }
        }
    }

    /**
     * @param string $queueName
     */
    public function setQueueName($queueName)
    {
        $this->streamName = $queueName;
    }
}