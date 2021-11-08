<?php

namespace BackblazeB2;

class File implements \JsonSerializable
{
    protected $id;
    protected $name;
    protected $hash;
    protected $size;
    protected $type;
    protected $info;
    protected $bucketId;
    protected $action;
    protected $uploadTimestamp;

    /**
     * File constructor.
     *
     * @param $id
     * @param $name
     * @param $hash
     * @param $size
     * @param $type
     * @param $info
     * @param $bucketId
     * @param $action
     * @param $uploadTimestamp
     */
    public function __construct($id, $name, $hash = null, $size = null, $type = null, $info = null, $bucketId = null, $action = null, $uploadTimestamp = null)
    {
        $this->id = $id;
        $this->name = $name;
        $this->hash = $hash;
        $this->size = $size;
        $this->type = $type;
        $this->info = $info;
        $this->bucketId = $bucketId;
        $this->action = $action;
        $this->uploadTimestamp = $uploadTimestamp;
    }

    /**
     * @return array
     * */
    public function jsonSerialize()
    {
        return [
            'id'              => $this->getId(),
            'name'            => $this->getName(),
            'hash'            => $this->getHash(),
            'size'            => $this->getSize(),
            'type'            => $this->getType(),
            'info'            => $this->getInfo(),
            'bucketId'        => $this->getBucketId(),
            'action'          => $this->getAction(),
            'uploadTimestamp' => $this->getUploadTimestamp(),
        ];
    }

    /**
     * @return string
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @return string
     */
    public function getHash()
    {
        return $this->hash;
    }

    /**
     * @return int
     */
    public function getSize()
    {
        return $this->size;
    }

    /**
     * @return string
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * @return array
     */
    public function getInfo()
    {
        return $this->info;
    }

    /**
     * @return string
     */
    public function getBucketId()
    {
        return $this->bucketId;
    }

    /**
     * @return string
     */
    public function getAction()
    {
        return $this->action;
    }

    /**
     * @return string
     */
    public function getUploadTimestamp()
    {
        return $this->uploadTimestamp;
    }
}
