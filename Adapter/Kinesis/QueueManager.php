<?php
/**
 * User: gaetano.giunta
 * Date: 19/05/14
 * Time: 19.08
 */

namespace Kaliop\Queueing\Plugins\KinesisBundle\Adapter\Kinesis;

use Kaliop\QueueingBundle\Service\MessageProducer as BaseMessageProducer;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\Exception\ServiceNotFoundException;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\XmlFileLoader;
use Symfony\Component\Config\FileLocator;
use InvalidArgumentException;
use Kaliop\QueueingBundle\Queue\Queue;
use Kaliop\QueueingBundle\Queue\QueueManagerInterface;

/**
 * A class dedicated not really to sending messages to a queue, bur rather to sending control commands
 */
class QueueManager /*extends BaseMessageProducer*/ implements ContainerAwareInterface, QueueManagerInterface
{
    protected $queue;
    protected $container;

    public function setContainer(ContainerInterface $container = null)
    {
        $this->container = $container;
    }

    /**
     * @param string $queue
     */
    public function setQueueName($queue)
    {
        $this->queue = $queue;
    }

    public function listActions()
    {
        return array(/*'purge', 'delete', 'info', 'list'*/);
    }

    public function executeAction($action)
    {
        /*switch ($action) {
            case 'purge':
                return $this->purgeQueue();

            case 'delete':
                return $this->deleteQueue();

            case 'info':
                return $this->queueInfo();

            case 'list':
                return $this->listQueues();

            default:
                throw new InvalidArgumentException("Action $action not supported");
        }*/
    }
}