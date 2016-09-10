<?php

namespace Kaliop\Queueing\Plugins\KinesisBundle\Adapter\Kinesis;

use Kaliop\QueueingBundle\Queue\MessageConsumerInterface;
use Kaliop\QueueingBundle\Queue\ConsumerInterface;
use Kaliop\Queueing\Plugins\KinesisBundle\Service\SequenceNumberStoreInterface;
use \Aws\Kinesis\KinesisClient;

class Consumer implements ConsumerInterface
{
    /** @var  \Aws\Kinesis\KinesisClient */
    protected $client;
    protected $shardId;
    protected $streamName;
    protected $callback;
    /** @var  \Kaliop\Queueing\Plugins\KinesisBundle\Service\SequenceNumberStoreInterface */
    protected $sequenceNumberStore;
    // allowed values: TRIM_HORIZON and LATEST
    protected $defaultShardIteratorType = 'TRIM_HORIZON';
    protected $requestBatchSize = 1;

    public function __construct(array $config)
    {
        $this->client = new KinesisClient($config);
    }

    /**
     * Does nothing
     * @param int $limit
     * @return Consumer
     */
    public function setMemoryLimit($limit)
    {
        return $this;
    }

    /**
     * @param string $key
     * @return Consumer
     * @todo if null and there is only 1 shard in the stream -> get it! Or allow asking for shard 1, 2, 3, ... instead of using the Id
     */
    public function setRoutingKey($key)
    {
        $this->shardId = $key;

        return $this;
    }

    /**
     * @param MessageConsumerInterface $callback
     * @return Consumer
     */
    public function setCallback($callback)
    {
        if (! $callback instanceof \Kaliop\QueueingBundle\Queue\MessageConsumerInterface) {
            throw new \RuntimeException('Can not set callback to SQS Consumer, as it is not a MessageConsumerInterface');
        }
        $this->callback = $callback;

        return $this;
    }

    /**
     * @param SequenceNumberStoreInterface $store
     * @return Consumer
     */
    public function setSequenceNumberStore(SequenceNumberStoreInterface $store)
    {
        $this->sequenceNumberStore = $store;

        return $this;
    }

    /**
     * The number of messages to download in every request to the Kinesis API.
     * Bigger numbers are better for performances, but there is a limit on the size of the response which Kinesis will send.
     * @param int $amount
     * @return Consumer
     */
    public function setRequestBatchSize($amount)
    {
        $this->requestBatchSize = $amount;

        return $this;
    }

    /**
     * Use this to decide what happens when the Consumer starts getting messages from a shard, and it does not
     * have stored a pointer to the last consumed message.
     *
     * @param string $type either LATEST (discard messages already in the shard) or TRIM_HORIZON (get all messages in the shard)
     * @return Consumer
     */
    public function setDefaultShardIteratorType($type)
    {
        $this->defaultShardIteratorType = $type;

        return $this;
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
        $iterator = $this->getInitialMessageIterator();

        $limit = ($amount > 0) ? $amount : $this->requestBatchSize;

        while(true) {
            $reqTime = microtime(true);
            $result = $this->client->getRecords(array(
                'ShardIterator' => $iterator,
                'Limit' => $limit,
            ));

            $records = $result->get('Records');

            if (count($records) && $this->sequenceNumberStore) {
                $last = end($records);
                $this->sequenceNumberStore->save($this->streamName, $this->shardId, $last['SequenceNumber']);
            }

            foreach($records as $record) {
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
     * Builds an iterator to start getting messages from the shard based on both injected config and the fact that
     * the store has a value for the last Sequence Number previously read
     */
    protected function getInitialMessageIterator()
    {
        $start = null;
        if ($this->sequenceNumberStore) {
            $start = $this->sequenceNumberStore->fetch($this->streamName, $this->shardId);
        }

        $iteratorOptions = array(
            'StreamName' => $this->streamName,
            'ShardId' => $this->shardId
        );

        if ($start == null) {
            $iteratorOptions['ShardIteratorType'] = $this->defaultShardIteratorType;
        } else {
            $iteratorOptions['ShardIteratorType'] = 'AFTER_SEQUENCE_NUMBER';
            $iteratorOptions['StartingSequenceNumber'] = $start;
        }

        return $this->client->getShardIterator($iteratorOptions)->get('ShardIterator');
    }

    /**
     * @param string $queueName
     * @return Consumer
     */
    public function setQueueName($queueName)
    {
        $this->streamName = $queueName;

        return $this;
    }
}