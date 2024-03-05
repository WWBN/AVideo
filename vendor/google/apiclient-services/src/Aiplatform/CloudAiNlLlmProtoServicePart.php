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

class CloudAiNlLlmProtoServicePart extends \Google\Model
{
  protected $fileDataType = CloudAiNlLlmProtoServicePartFileData::class;
  protected $fileDataDataType = '';
  protected $functionCallType = CloudAiNlLlmProtoServiceFunctionCall::class;
  protected $functionCallDataType = '';
  protected $functionResponseType = CloudAiNlLlmProtoServiceFunctionResponse::class;
  protected $functionResponseDataType = '';
  protected $inlineDataType = CloudAiNlLlmProtoServicePartBlob::class;
  protected $inlineDataDataType = '';
  /**
   * @var string
   */
  public $text;
  protected $videoMetadataType = CloudAiNlLlmProtoServicePartVideoMetadata::class;
  protected $videoMetadataDataType = '';

  /**
   * @param CloudAiNlLlmProtoServicePartFileData
   */
  public function setFileData(CloudAiNlLlmProtoServicePartFileData $fileData)
  {
    $this->fileData = $fileData;
  }
  /**
   * @return CloudAiNlLlmProtoServicePartFileData
   */
  public function getFileData()
  {
    return $this->fileData;
  }
  /**
   * @param CloudAiNlLlmProtoServiceFunctionCall
   */
  public function setFunctionCall(CloudAiNlLlmProtoServiceFunctionCall $functionCall)
  {
    $this->functionCall = $functionCall;
  }
  /**
   * @return CloudAiNlLlmProtoServiceFunctionCall
   */
  public function getFunctionCall()
  {
    return $this->functionCall;
  }
  /**
   * @param CloudAiNlLlmProtoServiceFunctionResponse
   */
  public function setFunctionResponse(CloudAiNlLlmProtoServiceFunctionResponse $functionResponse)
  {
    $this->functionResponse = $functionResponse;
  }
  /**
   * @return CloudAiNlLlmProtoServiceFunctionResponse
   */
  public function getFunctionResponse()
  {
    return $this->functionResponse;
  }
  /**
   * @param CloudAiNlLlmProtoServicePartBlob
   */
  public function setInlineData(CloudAiNlLlmProtoServicePartBlob $inlineData)
  {
    $this->inlineData = $inlineData;
  }
  /**
   * @return CloudAiNlLlmProtoServicePartBlob
   */
  public function getInlineData()
  {
    return $this->inlineData;
  }
  /**
   * @param string
   */
  public function setText($text)
  {
    $this->text = $text;
  }
  /**
   * @return string
   */
  public function getText()
  {
    return $this->text;
  }
  /**
   * @param CloudAiNlLlmProtoServicePartVideoMetadata
   */
  public function setVideoMetadata(CloudAiNlLlmProtoServicePartVideoMetadata $videoMetadata)
  {
    $this->videoMetadata = $videoMetadata;
  }
  /**
   * @return CloudAiNlLlmProtoServicePartVideoMetadata
   */
  public function getVideoMetadata()
  {
    return $this->videoMetadata;
  }
}

// Adding a class alias for backwards compatibility with the previous class name.
class_alias(CloudAiNlLlmProtoServicePart::class, 'Google_Service_Aiplatform_CloudAiNlLlmProtoServicePart');
