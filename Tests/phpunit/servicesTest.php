<?php

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class servicesTest extends WebTestCase
{
    protected function getContainer()
    {
        if (null !== static::$kernel) {
            static::$kernel->shutdown();
        }
        $options = array();
        static::$kernel = static::createKernel($options);
        static::$kernel->boot();
        return static::$kernel->getContainer();
    }

    /**
     * Minimalistic test: check that all known services can be loaded
     */
    public function testKnownServices()
    {
        $container = $this->getContainer();
        $service = $container->get('kaliop_queueing.driver.kinesis');
        $service = $container->get('test_alias.kaliop_queueing.kinesis.queue_manager');
        $service = $container->get('test_alias.kaliop_queueing.kinesis.sequence_number_store');
        $service = $container->get('test_alias.kaliop_queueing.kinesis.producer');
        $service = $container->get('test_alias.kaliop_queueing.kinesis.consumer');
	
        // useless assertion used to silence a warning that this test is risky
        $this->assertEquals(1, 1); 
    }
}
