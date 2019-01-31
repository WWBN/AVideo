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

class Google_Service_YouTube_LiveChatMessageSnippet extends Google_Model
{
  public $authorChannelId;
  public $displayMessage;
  protected $fanFundingEventDetailsType = 'Google_Service_YouTube_LiveChatFanFundingEventDetails';
  protected $fanFundingEventDetailsDataType = '';
  public $hasDisplayContent;
  public $liveChatId;
  protected $messageDeletedDetailsType = 'Google_Service_YouTube_LiveChatMessageDeletedDetails';
  protected $messageDeletedDetailsDataType = '';
  protected $messageRetractedDetailsType = 'Google_Service_YouTube_LiveChatMessageRetractedDetails';
  protected $messageRetractedDetailsDataType = '';
  protected $pollClosedDetailsType = 'Google_Service_YouTube_LiveChatPollClosedDetails';
  protected $pollClosedDetailsDataType = '';
  protected $pollEditedDetailsType = 'Google_Service_YouTube_LiveChatPollEditedDetails';
  protected $pollEditedDetailsDataType = '';
  protected $pollOpenedDetailsType = 'Google_Service_YouTube_LiveChatPollOpenedDetails';
  protected $pollOpenedDetailsDataType = '';
  protected $pollVotedDetailsType = 'Google_Service_YouTube_LiveChatPollVotedDetails';
  protected $pollVotedDetailsDataType = '';
  public $publishedAt;
  protected $superChatDetailsType = 'Google_Service_YouTube_LiveChatSuperChatDetails';
  protected $superChatDetailsDataType = '';
  protected $textMessageDetailsType = 'Google_Service_YouTube_LiveChatTextMessageDetails';
  protected $textMessageDetailsDataType = '';
  public $type;
  protected $userBannedDetailsType = 'Google_Service_YouTube_LiveChatUserBannedMessageDetails';
  protected $userBannedDetailsDataType = '';

  public function setAuthorChannelId($authorChannelId)
  {
    $this->authorChannelId = $authorChannelId;
  }
  public function getAuthorChannelId()
  {
    return $this->authorChannelId;
  }
  public function setDisplayMessage($displayMessage)
  {
    $this->displayMessage = $displayMessage;
  }
  public function getDisplayMessage()
  {
    return $this->displayMessage;
  }
  public function setFanFundingEventDetails(Google_Service_YouTube_LiveChatFanFundingEventDetails $fanFundingEventDetails)
  {
    $this->fanFundingEventDetails = $fanFundingEventDetails;
  }
  public function getFanFundingEventDetails()
  {
    return $this->fanFundingEventDetails;
  }
  public function setHasDisplayContent($hasDisplayContent)
  {
    $this->hasDisplayContent = $hasDisplayContent;
  }
  public function getHasDisplayContent()
  {
    return $this->hasDisplayContent;
  }
  public function setLiveChatId($liveChatId)
  {
    $this->liveChatId = $liveChatId;
  }
  public function getLiveChatId()
  {
    return $this->liveChatId;
  }
  public function setMessageDeletedDetails(Google_Service_YouTube_LiveChatMessageDeletedDetails $messageDeletedDetails)
  {
    $this->messageDeletedDetails = $messageDeletedDetails;
  }
  public function getMessageDeletedDetails()
  {
    return $this->messageDeletedDetails;
  }
  public function setMessageRetractedDetails(Google_Service_YouTube_LiveChatMessageRetractedDetails $messageRetractedDetails)
  {
    $this->messageRetractedDetails = $messageRetractedDetails;
  }
  public function getMessageRetractedDetails()
  {
    return $this->messageRetractedDetails;
  }
  public function setPollClosedDetails(Google_Service_YouTube_LiveChatPollClosedDetails $pollClosedDetails)
  {
    $this->pollClosedDetails = $pollClosedDetails;
  }
  public function getPollClosedDetails()
  {
    return $this->pollClosedDetails;
  }
  public function setPollEditedDetails(Google_Service_YouTube_LiveChatPollEditedDetails $pollEditedDetails)
  {
    $this->pollEditedDetails = $pollEditedDetails;
  }
  public function getPollEditedDetails()
  {
    return $this->pollEditedDetails;
  }
  public function setPollOpenedDetails(Google_Service_YouTube_LiveChatPollOpenedDetails $pollOpenedDetails)
  {
    $this->pollOpenedDetails = $pollOpenedDetails;
  }
  public function getPollOpenedDetails()
  {
    return $this->pollOpenedDetails;
  }
  public function setPollVotedDetails(Google_Service_YouTube_LiveChatPollVotedDetails $pollVotedDetails)
  {
    $this->pollVotedDetails = $pollVotedDetails;
  }
  public function getPollVotedDetails()
  {
    return $this->pollVotedDetails;
  }
  public function setPublishedAt($publishedAt)
  {
    $this->publishedAt = $publishedAt;
  }
  public function getPublishedAt()
  {
    return $this->publishedAt;
  }
  public function setSuperChatDetails(Google_Service_YouTube_LiveChatSuperChatDetails $superChatDetails)
  {
    $this->superChatDetails = $superChatDetails;
  }
  public function getSuperChatDetails()
  {
    return $this->superChatDetails;
  }
  public function setTextMessageDetails(Google_Service_YouTube_LiveChatTextMessageDetails $textMessageDetails)
  {
    $this->textMessageDetails = $textMessageDetails;
  }
  public function getTextMessageDetails()
  {
    return $this->textMessageDetails;
  }
  public function setType($type)
  {
    $this->type = $type;
  }
  public function getType()
  {
    return $this->type;
  }
  public function setUserBannedDetails(Google_Service_YouTube_LiveChatUserBannedMessageDetails $userBannedDetails)
  {
    $this->userBannedDetails = $userBannedDetails;
  }
  public function getUserBannedDetails()
  {
    return $this->userBannedDetails;
  }
}
