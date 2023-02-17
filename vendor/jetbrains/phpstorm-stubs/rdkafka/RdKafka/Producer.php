<?php

namespace RdKafka;

class Producer extends \RdKafka
{
    /**
     * @param Conf $conf
     */
    public function __construct(Conf $conf = null)
    {
    }

    /**
     * @param string    $topic_name
     * @param TopicConf $topic_conf
     *
     * @return ProducerTopic
     */
    public function newTopic($topic_name, TopicConf $topic_conf = null)
    {
    }
}
