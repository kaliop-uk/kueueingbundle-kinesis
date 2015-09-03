<?php

namespace Kaliop\Queueing\Plugins\KinesisBundle\Adapter\Kinesis;

use Kaliop\QueueingBundle\Queue\MessageConsumerInterface;
use Kaliop\QueueingBundle\Queue\ConsumerInterface;
use \Aws\Kinesis\KinesisClient;

class Consumer implements ConsumerInterface
{
    /** @var  \Aws\Kinesis\KinesisClient */
    protected $client;
    protected $shardId;
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

    /**
     * @param string $key
     * @todo if null and there is only 1 shard in the stream -> get it! Or allow asking for shard 1, 2, 3, ... instead of using the Id
     */
    public function setRoutingKey($key)
    {
        $this->shardId = $key;
    }

    public function setCallback(MessageConsumerInterface $callback)
    {
        $this->callback = $callback;
    }

    /**
     * @see http://docs.aws.amazon.com/aws-sdk-php/v2/api/class-Aws.Kinesis.KinesisClient.html#_getRecords
     * Will throw an exception if $amount is > 10.000
     *
     * @param int $amount
     * @return nothing
     */
    public function consume($amount)
    {
        $iterator = $this->client->getShardIterator(array(
            'StreamName' => $this->streamName,
            'ShardId' => $this->shardId,
            // ShardIteratorType is required
/// @TODO this downloads all messages in the shard. How to get only the new ones?
            'ShardIteratorType' => 'TRIM_HORIZON'
            //'StartingSequenceNumber' => 'string',  //...
        ))->get('ShardIterator');

        /// @todo allow a parameter to decide the batch size for reading in continuous loop mode
        $limit = ($amount > 0) ? $amount : 1;

        while(true) {
            $reqTime = microtime(true);
            $result = $this->client->getRecords(array(
                'ShardIterator' => $iterator,
                'Limit' => $limit,
            ));

            foreach($result->get('Records') as $record) {
                $data = $record['Data'];
                unset($record['Data']);
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

            // observe MAX 5 requests per sec per shard: sleep for 0.2 secs in between requests
            $passedMs = (microtime(true) - $reqTime) * 1000000;
            if ($passedMs < 200000) {
                usleep(200000 - $passedMs);
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