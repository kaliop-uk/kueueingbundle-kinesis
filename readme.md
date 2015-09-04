# Kaliop Queueing Bundle - Kinesis plugin

Adds support for AWS Kinesis to the Kaliop Queueing Bundle

See: http://aws.amazon.com/kinesis/ and https://github.com/kaliop-uk/kueueingbundle respectively.

It has been given its own bundle because it has higher requirements than the base Queueing Bundle


## Installation

1. Install the bundle.

3. Enable the KaliopQueueingPluginsKinesisBundle bundle in your kernel class registerBundles().    

4. Clear all caches if not on a dev environment


## Usage

5. If you do not have an AWS account, sign up for one at http://aws.amazon.com/
    NB: note that there is no free tier for Kinesis. Pricing is described at: http://aws.amazon.com/kinesis/pricing/

6. Create a Kinesis stream, using the web interface: https://console.aws.amazon.com/kinesis/home

7. Set up configuration according to your AWS account - see parameters.yml in this bundle
 
8. check that you can list the stream, and the shards in it:
 
        php app/console kaliop_queueing:managequeue list -bkinesis

9. push a message to the stream 

        php app/console kaliop_queueing:queuemessage -bkinesis -r<shard-partition> <stream> <jsonpayload>
        
10. receive messages from the stream

        php app/console kaliop_queueing:consumer -bkinesis -r<shard-id> <stream>


## Notes

* Kinesis groups messages in streams, which are divided into one or more shards each.
    Each message is pushed onto a shard, and has to be pulled from it. 
    In kaliop-messaging, the following mapping applies:
    - stream => queue
    - shard => routing key

* Kinesis by default does not remove messages from its shards when they are consumed. This means that the consumer has
    to keep an internal pointer to the last consumed message. A service is used to handle the local storage of this
    value

* When running the kaliop_queueing:queuemessage, usage of the -k option to specify a shard partition key is mandatory.
    NB: the value used is *not* the id of the shard, rather it is hashed and a shard is picked according to the hash
    value

* When running the kaliop_queueing:messageconsumer, usage of the -r option to specify a shard Id is mandatory.
    If you do not know the Shard Id, use the `kaliop_queueing:managequeue info <stream>` command to see it
