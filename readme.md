# Kaliop Queueing Bundle - Kinesis plugin

Adds support for AWS Kinesis to the Kaliop Queueing Bundle

See: http://aws.amazon.com/kinesis/ and https://github.com/kaliop-uk/kueueingbundle respectively.

It has been given its own bundle because it has higher requirements than the base Queueing Bundle


## Installation

1. Install the bundle via Composer.

2. Enable the KaliopQueueingPluginsKinesisBundle bundle in your kernel class registerBundles().
    Also enable the DoctrineCacheBundle.

3. Clear all caches if not on a dev environment


## Usage

4. If you do not have an AWS account, sign up for one at http://aws.amazon.com/
    NB: note that there is no free tier for Kinesis. Pricing is described at: http://aws.amazon.com/kinesis/pricing/

5. Create a Kinesis stream, using the web interface: https://console.aws.amazon.com/kinesis/home

6. Set up configuration according to your AWS account

    - edit parameters.yml in this bundle
    - copy/include kinesis_sample.yml in your app config, and edit it if needed
 
7. check that you can list the stream, and the shards in it:
 
        php app/console kaliop_queueing:managequeue list -bkinesis
        
        php app/console kaliop_queueing:managequeue info -bkinesis <stream>

8. push a message to the stream 

        php app/console kaliop_queueing:queuemessage -bkinesis -r<shard-partition> <stream> <jsonpayload>
        
9. receive messages from the stream

        php app/console kaliop_queueing:consumer -bkinesis -r<shard-id> <stream>


## Notes

* Kinesis groups messages in streams, which are divided into one or more shards each.
    Each message is pushed onto a shard, and has to be pulled from it. 
    In kaliop-queueing, the following mapping applies:
    - stream => queue
    - shard => routing key

* Kinesis by default does not remove messages from its shards when they are consumed. This means that the consumer has
    to keep an internal pointer to the last consumed message. A service is used to handle the local storage of this
    value - the default one provided in this bundle is based on the Doctrine-Cache bundle, and has to be configured
    to be enabled (see point 7 above). Note that since it uses the cache directory to store the data, on any cache
    clear the pointer will be reset, and old messages downloaded. To change this behaviour, see the option
        kaliop_queueing_kinesis.default.missing_sequence_number_strategy
    in parameters.yml

* When running the kaliop_queueing:queuemessage, usage of the -r option to specify a shard partition key is mandatory.
    NB: the value used is *not* the id of the shard, rather it is hashed and a shard is picked according to the hash
    value

* When running the kaliop_queueing:messageconsumer, usage of the -r option to specify a shard Id is mandatory.
    If you do not know the Shard Id, use the `kaliop_queueing:managequeue info <stream>` command to see it


[![License](https://poser.pugx.org/kaliop/queueingbundle-kinesis/license)](https://packagist.org/packages/kaliop/queueingbundle-kinesis)
[![Latest Stable Version](https://poser.pugx.org/kaliop/queueingbundle-kinesis/v/stable)](https://packagist.org/packages/kaliop/queueingbundle-kinesis)
[![Total Downloads](https://poser.pugx.org/kaliop/queueingbundle-kinesis/downloads)](https://packagist.org/packages/kaliop/queueingbundle-kinesis)

[![Build Status](https://travis-ci.org/kaliop-uk/queueingbundle-kinesis.svg?branch=master)](https://travis-ci.org/kaliop-uk/queueingbundle-kinesis)
[![SensioLabsInsight](https://insight.sensiolabs.com/projects/cbaefe33-51f4-4b7e-a423-c08797f7359b/mini.png)](https://insight.sensiolabs.com/projects/cbaefe33-51f4-4b7e-a423-c08797f7359b)
