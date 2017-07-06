<?php
/*
 * Copyright 2016 Google Inc.
 *
 * Licensed under the Apache License, Version 2.0 (the "License"); you may not
 * use this file except in compliance with the License. You may obtain a copy of
 * the License at
 *
 * http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS, WITHOUT
 * WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied. See the
 * License for the specific language governing permissions and limitations under
 * the License.
 */

class Google_Service_PlusDomains_Comment extends Google_Collection
{
  protected $collection_key = 'inReplyTo';
  protected $actorType = 'Google_Service_PlusDomains_CommentActor';
  protected $actorDataType = '';
  public $etag;
  public $id;
  protected $inReplyToType = 'Google_Service_PlusDomains_CommentInReplyTo';
  protected $inReplyToDataType = 'array';
  public $kind;
  protected $objectType = 'Google_Service_PlusDomains_CommentObject';
  protected $objectDataType = '';
  protected $plusonersType = 'Google_Service_PlusDomains_CommentPlusoners';
  protected $plusonersDataType = '';
  public $published;
  public $selfLink;
  public $updated;
  public $verb;

  public function setActor(Google_Service_PlusDomains_CommentActor $actor)
  {
    $this->actor = $actor;
  }
  public function getActor()
  {
    return $this->actor;
  }
  public function setEtag($etag)
  {
    $this->etag = $etag;
  }
  public function getEtag()
  {
    return $this->etag;
  }
  public function setId($id)
  {
    $this->id = $id;
  }
  public function getId()
  {
    return $this->id;
  }
  public function setInReplyTo($inReplyTo)
  {
    $this->inReplyTo = $inReplyTo;
  }
  public function getInReplyTo()
  {
    return $this->inReplyTo;
  }
  public function setKind($kind)
  {
    $this->kind = $kind;
  }
  public function getKind()
  {
    return $this->kind;
  }
  public function setObject(Google_Service_PlusDomains_CommentObject $object)
  {
    $this->object = $object;
  }
  public function getObject()
  {
    return $this->object;
  }
  public function setPlusoners(Google_Service_PlusDomains_CommentPlusoners $plusoners)
  {
    $this->plusoners = $plusoners;
  }
  public function getPlusoners()
  {
    return $this->plusoners;
  }
  public function setPublished($published)
  {
    $this->published = $published;
  }
  public function getPublished()
  {
    return $this->published;
  }
  public function setSelfLink($selfLink)
  {
    $this->selfLink = $selfLink;
  }
  public function getSelfLink()
  {
    return $this->selfLink;
  }
  public function setUpdated($updated)
  {
    $this->updated = $updated;
  }
  public function getUpdated()
  {
    return $this->updated;
  }
  public function setVerb($verb)
  {
    $this->verb = $verb;
  }
  public function getVerb()
  {
    return $this->verb;
  }
}
