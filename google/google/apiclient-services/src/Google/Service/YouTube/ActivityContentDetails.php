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

class Google_Service_YouTube_ActivityContentDetails extends Google_Model
{
  protected $bulletinType = 'Google_Service_YouTube_ActivityContentDetailsBulletin';
  protected $bulletinDataType = '';
  protected $channelItemType = 'Google_Service_YouTube_ActivityContentDetailsChannelItem';
  protected $channelItemDataType = '';
  protected $commentType = 'Google_Service_YouTube_ActivityContentDetailsComment';
  protected $commentDataType = '';
  protected $favoriteType = 'Google_Service_YouTube_ActivityContentDetailsFavorite';
  protected $favoriteDataType = '';
  protected $likeType = 'Google_Service_YouTube_ActivityContentDetailsLike';
  protected $likeDataType = '';
  protected $playlistItemType = 'Google_Service_YouTube_ActivityContentDetailsPlaylistItem';
  protected $playlistItemDataType = '';
  protected $promotedItemType = 'Google_Service_YouTube_ActivityContentDetailsPromotedItem';
  protected $promotedItemDataType = '';
  protected $recommendationType = 'Google_Service_YouTube_ActivityContentDetailsRecommendation';
  protected $recommendationDataType = '';
  protected $socialType = 'Google_Service_YouTube_ActivityContentDetailsSocial';
  protected $socialDataType = '';
  protected $subscriptionType = 'Google_Service_YouTube_ActivityContentDetailsSubscription';
  protected $subscriptionDataType = '';
  protected $uploadType = 'Google_Service_YouTube_ActivityContentDetailsUpload';
  protected $uploadDataType = '';

  public function setBulletin(Google_Service_YouTube_ActivityContentDetailsBulletin $bulletin)
  {
    $this->bulletin = $bulletin;
  }
  public function getBulletin()
  {
    return $this->bulletin;
  }
  public function setChannelItem(Google_Service_YouTube_ActivityContentDetailsChannelItem $channelItem)
  {
    $this->channelItem = $channelItem;
  }
  public function getChannelItem()
  {
    return $this->channelItem;
  }
  public function setComment(Google_Service_YouTube_ActivityContentDetailsComment $comment)
  {
    $this->comment = $comment;
  }
  public function getComment()
  {
    return $this->comment;
  }
  public function setFavorite(Google_Service_YouTube_ActivityContentDetailsFavorite $favorite)
  {
    $this->favorite = $favorite;
  }
  public function getFavorite()
  {
    return $this->favorite;
  }
  public function setLike(Google_Service_YouTube_ActivityContentDetailsLike $like)
  {
    $this->like = $like;
  }
  public function getLike()
  {
    return $this->like;
  }
  public function setPlaylistItem(Google_Service_YouTube_ActivityContentDetailsPlaylistItem $playlistItem)
  {
    $this->playlistItem = $playlistItem;
  }
  public function getPlaylistItem()
  {
    return $this->playlistItem;
  }
  public function setPromotedItem(Google_Service_YouTube_ActivityContentDetailsPromotedItem $promotedItem)
  {
    $this->promotedItem = $promotedItem;
  }
  public function getPromotedItem()
  {
    return $this->promotedItem;
  }
  public function setRecommendation(Google_Service_YouTube_ActivityContentDetailsRecommendation $recommendation)
  {
    $this->recommendation = $recommendation;
  }
  public function getRecommendation()
  {
    return $this->recommendation;
  }
  public function setSocial(Google_Service_YouTube_ActivityContentDetailsSocial $social)
  {
    $this->social = $social;
  }
  public function getSocial()
  {
    return $this->social;
  }
  public function setSubscription(Google_Service_YouTube_ActivityContentDetailsSubscription $subscription)
  {
    $this->subscription = $subscription;
  }
  public function getSubscription()
  {
    return $this->subscription;
  }
  public function setUpload(Google_Service_YouTube_ActivityContentDetailsUpload $upload)
  {
    $this->upload = $upload;
  }
  public function getUpload()
  {
    return $this->upload;
  }
}
