<?php

namespace Kaliop\Queueing\Plugins\KinesisBundle\Adapter\Kinesis;

use Kaliop\QueueingBundle\Adapter\DriverInterface;
use Symfony\Component\DependencyInjection\ContainerAware;

class Driver extends ContainerAware implements DriverInterface
{
    protected $debug;

    public function getConsumer($queueName)
    {
        $consumer = $this->container->get('kaliop_queueing.kinesis.message_consumer');
        $consumer->setQueueName($queueName);
        return $consumer;
    }

    public function acceptMessage($message)
    {
// @todo
    }

    /**
     * @param ??? $message
     * @return \Kaliop\QueueingBundle\Queue\MessageInterface
     */
    public function decodeMessage($message)
    {
// @todo
    }

    /**
     * @param string $queueName
     * @return \Kaliop\QueueingBundle\Queue\ProducerInterface
     */
    public function getProducer($queueName)
    {
        $producer = $this->container->get('kaliop_queueing.kinesis.message_producer');
        $producer->setQueueName($queueName);
        $producer->setDebug($this->debug);
        return $producer;
    }

    /**
     * @param string $queueName
     * @return \Kaliop\QueueingBundle\Queue\QueueManagerInterface
     */
    public function getQueueManager($queueName)
    {
        $mgr = $this->container->get('kaliop_queueing.kinesis.queue_manager');
        $mgr->setQueueName($queueName);
        return $mgr;
    }

    public function setDebug($debug)
    {
        $this->debug = $debug;
    }
}
