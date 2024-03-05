<?php
/*
 * Copyright 2014 Google Inc.
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

namespace Google\Service\Aiplatform;

class LearningGenaiRecitationDocAttribution extends \Google\Model
{
  /**
   * @var string
   */
  public $amarnaId;
  /**
   * @var string
   */
  public $arxivId;
  /**
   * @var string
   */
  public $author;
  /**
   * @var string
   */
  public $bibkey;
  /**
   * @var string
   */
  public $biorxivId;
  /**
   * @var string
   */
  public $bookTitle;
  /**
   * @var string
   */
  public $bookVolumeId;
  /**
   * @var string
   */
  public $conversationId;
  /**
   * @var string
   */
  public $dataset;
  /**
   * @var string
   */
  public $filepath;
  /**
   * @var string
   */
  public $geminiId;
  /**
   * @var string
   */
  public $gnewsArticleTitle;
  /**
   * @var string
   */
  public $goodallExampleId;
  /**
   * @var bool
   */
  public $isOptOut;
  /**
   * @var bool
   */
  public $isPrompt;
  /**
   * @var string
   */
  public $lamdaExampleId;
  /**
   * @var string
   */
  public $license;
  /**
   * @var string
   */
  public $meenaConversationId;
  /**
   * @var string
   */
  public $naturalLanguageCode;
  /**
   * @var bool
   */
  public $noAttribution;
  /**
   * @var string
   */
  public $podcastUtteranceId;
  protected $publicationDateType = GoogleTypeDate::class;
  protected $publicationDateDataType = '';
  public $qualityScoreExperimentOnly;
  /**
   * @var string
   */
  public $repo;
  /**
   * @var string
   */
  public $url;
  /**
   * @var string
   */
  public $volumeId;
  /**
   * @var string
   */
  public $wikipediaArticleTitle;
  /**
   * @var string
   */
  public $youtubeVideoId;

  /**
   * @param string
   */
  public function setAmarnaId($amarnaId)
  {
    $this->amarnaId = $amarnaId;
  }
  /**
   * @return string
   */
  public function getAmarnaId()
  {
    return $this->amarnaId;
  }
  /**
   * @param string
   */
  public function setArxivId($arxivId)
  {
    $this->arxivId = $arxivId;
  }
  /**
   * @return string
   */
  public function getArxivId()
  {
    return $this->arxivId;
  }
  /**
   * @param string
   */
  public function setAuthor($author)
  {
    $this->author = $author;
  }
  /**
   * @return string
   */
  public function getAuthor()
  {
    return $this->author;
  }
  /**
   * @param string
   */
  public function setBibkey($bibkey)
  {
    $this->bibkey = $bibkey;
  }
  /**
   * @return string
   */
  public function getBibkey()
  {
    return $this->bibkey;
  }
  /**
   * @param string
   */
  public function setBiorxivId($biorxivId)
  {
    $this->biorxivId = $biorxivId;
  }
  /**
   * @return string
   */
  public function getBiorxivId()
  {
    return $this->biorxivId;
  }
  /**
   * @param string
   */
  public function setBookTitle($bookTitle)
  {
    $this->bookTitle = $bookTitle;
  }
  /**
   * @return string
   */
  public function getBookTitle()
  {
    return $this->bookTitle;
  }
  /**
   * @param string
   */
  public function setBookVolumeId($bookVolumeId)
  {
    $this->bookVolumeId = $bookVolumeId;
  }
  /**
   * @return string
   */
  public function getBookVolumeId()
  {
    return $this->bookVolumeId;
  }
  /**
   * @param string
   */
  public function setConversationId($conversationId)
  {
    $this->conversationId = $conversationId;
  }
  /**
   * @return string
   */
  public function getConversationId()
  {
    return $this->conversationId;
  }
  /**
   * @param string
   */
  public function setDataset($dataset)
  {
    $this->dataset = $dataset;
  }
  /**
   * @return string
   */
  public function getDataset()
  {
    return $this->dataset;
  }
  /**
   * @param string
   */
  public function setFilepath($filepath)
  {
    $this->filepath = $filepath;
  }
  /**
   * @return string
   */
  public function getFilepath()
  {
    return $this->filepath;
  }
  /**
   * @param string
   */
  public function setGeminiId($geminiId)
  {
    $this->geminiId = $geminiId;
  }
  /**
   * @return string
   */
  public function getGeminiId()
  {
    return $this->geminiId;
  }
  /**
   * @param string
   */
  public function setGnewsArticleTitle($gnewsArticleTitle)
  {
    $this->gnewsArticleTitle = $gnewsArticleTitle;
  }
  /**
   * @return string
   */
  public function getGnewsArticleTitle()
  {
    return $this->gnewsArticleTitle;
  }
  /**
   * @param string
   */
  public function setGoodallExampleId($goodallExampleId)
  {
    $this->goodallExampleId = $goodallExampleId;
  }
  /**
   * @return string
   */
  public function getGoodallExampleId()
  {
    return $this->goodallExampleId;
  }
  /**
   * @param bool
   */
  public function setIsOptOut($isOptOut)
  {
    $this->isOptOut = $isOptOut;
  }
  /**
   * @return bool
   */
  public function getIsOptOut()
  {
    return $this->isOptOut;
  }
  /**
   * @param bool
   */
  public function setIsPrompt($isPrompt)
  {
    $this->isPrompt = $isPrompt;
  }
  /**
   * @return bool
   */
  public function getIsPrompt()
  {
    return $this->isPrompt;
  }
  /**
   * @param string
   */
  public function setLamdaExampleId($lamdaExampleId)
  {
    $this->lamdaExampleId = $lamdaExampleId;
  }
  /**
   * @return string
   */
  public function getLamdaExampleId()
  {
    return $this->lamdaExampleId;
  }
  /**
   * @param string
   */
  public function setLicense($license)
  {
    $this->license = $license;
  }
  /**
   * @return string
   */
  public function getLicense()
  {
    return $this->license;
  }
  /**
   * @param string
   */
  public function setMeenaConversationId($meenaConversationId)
  {
    $this->meenaConversationId = $meenaConversationId;
  }
  /**
   * @return string
   */
  public function getMeenaConversationId()
  {
    return $this->meenaConversationId;
  }
  /**
   * @param string
   */
  public function setNaturalLanguageCode($naturalLanguageCode)
  {
    $this->naturalLanguageCode = $naturalLanguageCode;
  }
  /**
   * @return string
   */
  public function getNaturalLanguageCode()
  {
    return $this->naturalLanguageCode;
  }
  /**
   * @param bool
   */
  public function setNoAttribution($noAttribution)
  {
    $this->noAttribution = $noAttribution;
  }
  /**
   * @return bool
   */
  public function getNoAttribution()
  {
    return $this->noAttribution;
  }
  /**
   * @param string
   */
  public function setPodcastUtteranceId($podcastUtteranceId)
  {
    $this->podcastUtteranceId = $podcastUtteranceId;
  }
  /**
   * @return string
   */
  public function getPodcastUtteranceId()
  {
    return $this->podcastUtteranceId;
  }
  /**
   * @param GoogleTypeDate
   */
  public function setPublicationDate(GoogleTypeDate $publicationDate)
  {
    $this->publicationDate = $publicationDate;
  }
  /**
   * @return GoogleTypeDate
   */
  public function getPublicationDate()
  {
    return $this->publicationDate;
  }
  public function setQualityScoreExperimentOnly($qualityScoreExperimentOnly)
  {
    $this->qualityScoreExperimentOnly = $qualityScoreExperimentOnly;
  }
  public function getQualityScoreExperimentOnly()
  {
    return $this->qualityScoreExperimentOnly;
  }
  /**
   * @param string
   */
  public function setRepo($repo)
  {
    $this->repo = $repo;
  }
  /**
   * @return string
   */
  public function getRepo()
  {
    return $this->repo;
  }
  /**
   * @param string
   */
  public function setUrl($url)
  {
    $this->url = $url;
  }
  /**
   * @return string
   */
  public function getUrl()
  {
    return $this->url;
  }
  /**
   * @param string
   */
  public function setVolumeId($volumeId)
  {
    $this->volumeId = $volumeId;
  }
  /**
   * @return string
   */
  public function getVolumeId()
  {
    return $this->volumeId;
  }
  /**
   * @param string
   */
  public function setWikipediaArticleTitle($wikipediaArticleTitle)
  {
    $this->wikipediaArticleTitle = $wikipediaArticleTitle;
  }
  /**
   * @return string
   */
  public function getWikipediaArticleTitle()
  {
    return $this->wikipediaArticleTitle;
  }
  /**
   * @param string
   */
  public function setYoutubeVideoId($youtubeVideoId)
  {
    $this->youtubeVideoId = $youtubeVideoId;
  }
  /**
   * @return string
   */
  public function getYoutubeVideoId()
  {
    return $this->youtubeVideoId;
  }
}

// Adding a class alias for backwards compatibility with the previous class name.
class_alias(LearningGenaiRecitationDocAttribution::class, 'Google_Service_Aiplatform_LearningGenaiRecitationDocAttribution');
