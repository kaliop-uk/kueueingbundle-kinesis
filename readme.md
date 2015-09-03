# Kaliop Queueing Bundle - Kinesis plugin

Adds support for AWS Kinesis to the Kaliop Queueing Bundle

See: http://aws.amazon.com/kinesis/ and https://github.com/kaliop-uk/kueueingbundle respectively.

It has been given its own bundle because it has higher requirements than the base Queueing Bundle

## Installation

1. Install the bundle.

3. Enable the KaliopQueueingPluginsKinesisBundle bundle in your kernel class registerBundles().    

4. Clear all caches if not on a dev environment

5. If you do not have an AWS account, sign up for one at http://aws.amazon.com/
    NB: note that there is no free tier for Kinesis. Pricing is described at: http://aws.amazon.com/kinesis/pricing/

## Usage

* Set up configuration according to your AWS account - see parameters.yml in this bundle

* To create a kinesis stream, use the web interface: https://console.aws.amazon.com/kinesis/home 

* When running the kaliop_queueing:queuemessage, usage of the -k option to specify a shard partition key is mandatory.
  NB: the value used is *not* the id of the shard, rather it is hashed and a shard is picked according to the hash
  value

* When running the kaliop_queueing:messageconsumer, usage of the -r option to specify a shard Id is mandatory.