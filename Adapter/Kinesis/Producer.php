<?php

namespace Kaliop\Queueing\Plugins\KinesisBundle\Adapter\Kinesis;

use Kaliop\QueueingBundle\Queue\ProducerInterface;
use Aws\Kinesis\KinesisClient;

class Producer implements ProducerInterface
{
    /** @var  \Aws\Kinesis\KinesisClient */
    protected $client;
    protected $streamName;
    protected $debug;

    /**
     * @param array $config - minimum seems to be: 'credentials', 'region', 'version'
     * @see \Aws\AwsClient::__construct for the full list
     * @see http://docs.aws.amazon.com/aws-sdk-php/v3/guide/guide/configuration.html
     */
    public function __construct(array $config)
    {
        $this->client = new KinesisClient($config);
    }

    /**
     * @param string $queueName
     * @return Producer
     * @todo test that we can successfully send messages to 2 queues using the same KinesisClient
     */
    public function setQueueName($queueName)
    {
        $this->streamName = $queueName;

        return $this;
    }

    /**
     * Note that this has less effect than passing a 'debug' option in constructor, as it will be
     * only used by publish() from now on
     *
     * @param bool $debug use null for 'undefined'
     * @return Producer
     */
    public function setDebug($debug)
    {
        $this->debug = $debug;

        return $this;
    }

    /**
     * Publishes the message and does nothing with the properties
     *
     * @param string $msgBody
     * @param string $routingKey
     * @param array $additionalProperties
     */
    public function publish($msgBody, $routingKey = '', $additionalProperties = array())
    {
        $result = $this->client->putRecord(array_merge(
            array(
                'StreamName' => $this->streamName,
                'Data' => $msgBody,
                'PartitionKey' => $routingKey
            ),
            $this->getClientParams()
        ));
    }

    /**
     * Allows callers to do whatever they want with the client - useful to the Queue Mgr
     *
     * @param string $method
     * @param array $args
     * @return mixed
     */
    public function call($method, array $args = array())
    {
        return $this->client->$method(array_merge($args, $this->getClientParams()));
    }

    /**
     * Prepares the extra parameters to be injected into calls made via the Kinesis Client
     * @return array
     */
    protected function getClientParams()
    {
        if ($this->debug !== null) {
            return array('@http' => array('debug' => $this->debug));
        }

        return array();
    }

    /**
     * @param string $contentType
     * @return Producer
     * @throws \Exception if unsupported contentType is used
     *
     * @todo allow different serializations - but the Kinesis protocol does not allow to store the content type in the
     *       message natively, so we should have to 'invent' an encapsulation format...
     */
    public function setContentType($contentType)
    {
        if($contentType != 'application/json') {
            throw new \Exception("Unsupported content-type for message serialization: $contentType. Only 'application/json' is supported");
        }

        return $this;
    }
}