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

namespace Google\Service\Contentwarehouse;

class NlxDataSchemaScaleSet extends \Google\Collection
{
  protected $collection_key = 'token';
  protected $byteType = NlxDataSchemaByte::class;
  protected $byteDataType = 'array';
  public $byte;
  protected $byteDocumentPresenceType = MultiscaleFieldPresence::class;
  protected $byteDocumentPresenceDataType = '';
  public $byteDocumentPresence;
  protected $bytePresenceType = MultiscaleLayerPresence::class;
  protected $bytePresenceDataType = '';
  public $bytePresence;
  protected $characterType = NlxDataSchemaCharacter::class;
  protected $characterDataType = 'array';
  public $character;
  protected $characterDocumentPresenceType = MultiscaleFieldPresence::class;
  protected $characterDocumentPresenceDataType = '';
  public $characterDocumentPresence;
  protected $characterParagraphPresenceType = MultiscaleFieldPresence::class;
  protected $characterParagraphPresenceDataType = '';
  public $characterParagraphPresence;
  protected $characterPresenceType = MultiscaleLayerPresence::class;
  protected $characterPresenceDataType = '';
  public $characterPresence;
  protected $characterSentencePresenceType = MultiscaleFieldPresence::class;
  protected $characterSentencePresenceDataType = '';
  public $characterSentencePresence;
  protected $characterTextPresenceType = MultiscaleFieldPresence::class;
  protected $characterTextPresenceDataType = '';
  public $characterTextPresence;
  protected $characterTokenPresenceType = MultiscaleFieldPresence::class;
  protected $characterTokenPresenceDataType = '';
  public $characterTokenPresence;
  protected $documentType = NlxDataSchemaDocument::class;
  protected $documentDataType = 'array';
  public $document;
  protected $documentAuthorPresenceType = MultiscaleFieldPresence::class;
  protected $documentAuthorPresenceDataType = '';
  public $documentAuthorPresence;
  protected $documentBytesPresenceType = MultiscaleFieldPresence::class;
  protected $documentBytesPresenceDataType = '';
  public $documentBytesPresence;
  protected $documentCharactersPresenceType = MultiscaleFieldPresence::class;
  protected $documentCharactersPresenceDataType = '';
  public $documentCharactersPresence;
  protected $documentIdPresenceType = MultiscaleFieldPresence::class;
  protected $documentIdPresenceDataType = '';
  public $documentIdPresence;
  protected $documentLanguageCodePresenceType = MultiscaleFieldPresence::class;
  protected $documentLanguageCodePresenceDataType = '';
  public $documentLanguageCodePresence;
  protected $documentLanguageSpansPresenceType = MultiscaleFieldPresence::class;
  protected $documentLanguageSpansPresenceDataType = '';
  public $documentLanguageSpansPresence;
  protected $documentMentionsPresenceType = MultiscaleFieldPresence::class;
  protected $documentMentionsPresenceDataType = '';
  public $documentMentionsPresence;
  protected $documentParagraphsPresenceType = MultiscaleFieldPresence::class;
  protected $documentParagraphsPresenceDataType = '';
  public $documentParagraphsPresence;
  protected $documentPresenceType = MultiscaleLayerPresence::class;
  protected $documentPresenceDataType = '';
  public $documentPresence;
  protected $documentSentencesPresenceType = MultiscaleFieldPresence::class;
  protected $documentSentencesPresenceDataType = '';
  public $documentSentencesPresence;
  protected $documentTextPresenceType = MultiscaleFieldPresence::class;
  protected $documentTextPresenceDataType = '';
  public $documentTextPresence;
  protected $documentTokensPresenceType = MultiscaleFieldPresence::class;
  protected $documentTokensPresenceDataType = '';
  public $documentTokensPresence;
  protected $documentUrlPresenceType = MultiscaleFieldPresence::class;
  protected $documentUrlPresenceDataType = '';
  public $documentUrlPresence;
  protected $entityType = NlxDataSchemaEntity::class;
  protected $entityDataType = 'array';
  public $entity;
  protected $entityGenderPresenceType = MultiscaleFieldPresence::class;
  protected $entityGenderPresenceDataType = '';
  public $entityGenderPresence;
  protected $entityMidPresenceType = MultiscaleFieldPresence::class;
  protected $entityMidPresenceDataType = '';
  public $entityMidPresence;
  protected $entityNamePresenceType = MultiscaleFieldPresence::class;
  protected $entityNamePresenceDataType = '';
  public $entityNamePresence;
  protected $entityPresenceType = MultiscaleLayerPresence::class;
  protected $entityPresenceDataType = '';
  public $entityPresence;
  protected $entityTypePresenceType = MultiscaleFieldPresence::class;
  protected $entityTypePresenceDataType = '';
  public $entityTypePresence;
  protected $languageSpanType = NlxDataSchemaLanguageSpan::class;
  protected $languageSpanDataType = 'array';
  public $languageSpan;
  protected $languageSpanBytesPresenceType = MultiscaleFieldPresence::class;
  protected $languageSpanBytesPresenceDataType = '';
  public $languageSpanBytesPresence;
  protected $languageSpanCharactersPresenceType = MultiscaleFieldPresence::class;
  protected $languageSpanCharactersPresenceDataType = '';
  public $languageSpanCharactersPresence;
  protected $languageSpanDocumentPresenceType = MultiscaleFieldPresence::class;
  protected $languageSpanDocumentPresenceDataType = '';
  public $languageSpanDocumentPresence;
  protected $languageSpanLanguageCodePresenceType = MultiscaleFieldPresence::class;
  protected $languageSpanLanguageCodePresenceDataType = '';
  public $languageSpanLanguageCodePresence;
  protected $languageSpanPresenceType = MultiscaleLayerPresence::class;
  protected $languageSpanPresenceDataType = '';
  public $languageSpanPresence;
  protected $mentionType = NlxDataSchemaMention::class;
  protected $mentionDataType = 'array';
  public $mention;
  protected $mentionBytesPresenceType = MultiscaleFieldPresence::class;
  protected $mentionBytesPresenceDataType = '';
  public $mentionBytesPresence;
  protected $mentionDocumentPresenceType = MultiscaleFieldPresence::class;
  protected $mentionDocumentPresenceDataType = '';
  public $mentionDocumentPresence;
  protected $mentionEntityPresenceType = MultiscaleFieldPresence::class;
  protected $mentionEntityPresenceDataType = '';
  public $mentionEntityPresence;
  protected $mentionKindPresenceType = MultiscaleFieldPresence::class;
  protected $mentionKindPresenceDataType = '';
  public $mentionKindPresence;
  protected $mentionPresenceType = MultiscaleLayerPresence::class;
  protected $mentionPresenceDataType = '';
  public $mentionPresence;
  protected $mentionTextPresenceType = MultiscaleFieldPresence::class;
  protected $mentionTextPresenceDataType = '';
  public $mentionTextPresence;
  protected $mentionTokensPresenceType = MultiscaleFieldPresence::class;
  protected $mentionTokensPresenceDataType = '';
  public $mentionTokensPresence;
  protected $mentionTypePresenceType = MultiscaleFieldPresence::class;
  protected $mentionTypePresenceDataType = '';
  public $mentionTypePresence;
  protected $paragraphType = NlxDataSchemaParagraph::class;
  protected $paragraphDataType = 'array';
  public $paragraph;
  protected $paragraphBytesPresenceType = MultiscaleFieldPresence::class;
  protected $paragraphBytesPresenceDataType = '';
  public $paragraphBytesPresence;
  protected $paragraphCharactersPresenceType = MultiscaleFieldPresence::class;
  protected $paragraphCharactersPresenceDataType = '';
  public $paragraphCharactersPresence;
  protected $paragraphDocumentPresenceType = MultiscaleFieldPresence::class;
  protected $paragraphDocumentPresenceDataType = '';
  public $paragraphDocumentPresence;
  protected $paragraphPresenceType = MultiscaleLayerPresence::class;
  protected $paragraphPresenceDataType = '';
  public $paragraphPresence;
  protected $paragraphSentencesPresenceType = MultiscaleFieldPresence::class;
  protected $paragraphSentencesPresenceDataType = '';
  public $paragraphSentencesPresence;
  protected $paragraphTextPresenceType = MultiscaleFieldPresence::class;
  protected $paragraphTextPresenceDataType = '';
  public $paragraphTextPresence;
  protected $paragraphTokensPresenceType = MultiscaleFieldPresence::class;
  protected $paragraphTokensPresenceDataType = '';
  public $paragraphTokensPresence;
  protected $sentenceType = NlxDataSchemaSentence::class;
  protected $sentenceDataType = 'array';
  public $sentence;
  protected $sentenceBytesPresenceType = MultiscaleFieldPresence::class;
  protected $sentenceBytesPresenceDataType = '';
  public $sentenceBytesPresence;
  protected $sentenceCharactersPresenceType = MultiscaleFieldPresence::class;
  protected $sentenceCharactersPresenceDataType = '';
  public $sentenceCharactersPresence;
  protected $sentenceDocumentPresenceType = MultiscaleFieldPresence::class;
  protected $sentenceDocumentPresenceDataType = '';
  public $sentenceDocumentPresence;
  protected $sentenceParagraphPresenceType = MultiscaleFieldPresence::class;
  protected $sentenceParagraphPresenceDataType = '';
  public $sentenceParagraphPresence;
  protected $sentencePresenceType = MultiscaleLayerPresence::class;
  protected $sentencePresenceDataType = '';
  public $sentencePresence;
  protected $sentenceTextPresenceType = MultiscaleFieldPresence::class;
  protected $sentenceTextPresenceDataType = '';
  public $sentenceTextPresence;
  protected $sentenceTokensPresenceType = MultiscaleFieldPresence::class;
  protected $sentenceTokensPresenceDataType = '';
  public $sentenceTokensPresence;
  protected $tokenType = NlxDataSchemaToken::class;
  protected $tokenDataType = 'array';
  public $token;
  protected $tokenBytesPresenceType = MultiscaleFieldPresence::class;
  protected $tokenBytesPresenceDataType = '';
  public $tokenBytesPresence;
  protected $tokenCharactersPresenceType = MultiscaleFieldPresence::class;
  protected $tokenCharactersPresenceDataType = '';
  public $tokenCharactersPresence;
  protected $tokenDependencyHeadPresenceType = MultiscaleFieldPresence::class;
  protected $tokenDependencyHeadPresenceDataType = '';
  public $tokenDependencyHeadPresence;
  protected $tokenDependencyLabelPresenceType = MultiscaleFieldPresence::class;
  protected $tokenDependencyLabelPresenceDataType = '';
  public $tokenDependencyLabelPresence;
  protected $tokenDependencyPresenceType = MultiscaleFieldPresence::class;
  protected $tokenDependencyPresenceDataType = '';
  public $tokenDependencyPresence;
  protected $tokenDocumentPresenceType = MultiscaleFieldPresence::class;
  protected $tokenDocumentPresenceDataType = '';
  public $tokenDocumentPresence;
  protected $tokenParagraphPresenceType = MultiscaleFieldPresence::class;
  protected $tokenParagraphPresenceDataType = '';
  public $tokenParagraphPresence;
  protected $tokenPosPresenceType = MultiscaleFieldPresence::class;
  protected $tokenPosPresenceDataType = '';
  public $tokenPosPresence;
  protected $tokenPresenceType = MultiscaleLayerPresence::class;
  protected $tokenPresenceDataType = '';
  public $tokenPresence;
  protected $tokenSentencePresenceType = MultiscaleFieldPresence::class;
  protected $tokenSentencePresenceDataType = '';
  public $tokenSentencePresence;
  protected $tokenTextPresenceType = MultiscaleFieldPresence::class;
  protected $tokenTextPresenceDataType = '';
  public $tokenTextPresence;

  /**
   * @param NlxDataSchemaByte[]
   */
  public function setByte($byte)
  {
    $this->byte = $byte;
  }
  /**
   * @return NlxDataSchemaByte[]
   */
  public function getByte()
  {
    return $this->byte;
  }
  /**
   * @param MultiscaleFieldPresence
   */
  public function setByteDocumentPresence(MultiscaleFieldPresence $byteDocumentPresence)
  {
    $this->byteDocumentPresence = $byteDocumentPresence;
  }
  /**
   * @return MultiscaleFieldPresence
   */
  public function getByteDocumentPresence()
  {
    return $this->byteDocumentPresence;
  }
  /**
   * @param MultiscaleLayerPresence
   */
  public function setBytePresence(MultiscaleLayerPresence $bytePresence)
  {
    $this->bytePresence = $bytePresence;
  }
  /**
   * @return MultiscaleLayerPresence
   */
  public function getBytePresence()
  {
    return $this->bytePresence;
  }
  /**
   * @param NlxDataSchemaCharacter[]
   */
  public function setCharacter($character)
  {
    $this->character = $character;
  }
  /**
   * @return NlxDataSchemaCharacter[]
   */
  public function getCharacter()
  {
    return $this->character;
  }
  /**
   * @param MultiscaleFieldPresence
   */
  public function setCharacterDocumentPresence(MultiscaleFieldPresence $characterDocumentPresence)
  {
    $this->characterDocumentPresence = $characterDocumentPresence;
  }
  /**
   * @return MultiscaleFieldPresence
   */
  public function getCharacterDocumentPresence()
  {
    return $this->characterDocumentPresence;
  }
  /**
   * @param MultiscaleFieldPresence
   */
  public function setCharacterParagraphPresence(MultiscaleFieldPresence $characterParagraphPresence)
  {
    $this->characterParagraphPresence = $characterParagraphPresence;
  }
  /**
   * @return MultiscaleFieldPresence
   */
  public function getCharacterParagraphPresence()
  {
    return $this->characterParagraphPresence;
  }
  /**
   * @param MultiscaleLayerPresence
   */
  public function setCharacterPresence(MultiscaleLayerPresence $characterPresence)
  {
    $this->characterPresence = $characterPresence;
  }
  /**
   * @return MultiscaleLayerPresence
   */
  public function getCharacterPresence()
  {
    return $this->characterPresence;
  }
  /**
   * @param MultiscaleFieldPresence
   */
  public function setCharacterSentencePresence(MultiscaleFieldPresence $characterSentencePresence)
  {
    $this->characterSentencePresence = $characterSentencePresence;
  }
  /**
   * @return MultiscaleFieldPresence
   */
  public function getCharacterSentencePresence()
  {
    return $this->characterSentencePresence;
  }
  /**
   * @param MultiscaleFieldPresence
   */
  public function setCharacterTextPresence(MultiscaleFieldPresence $characterTextPresence)
  {
    $this->characterTextPresence = $characterTextPresence;
  }
  /**
   * @return MultiscaleFieldPresence
   */
  public function getCharacterTextPresence()
  {
    return $this->characterTextPresence;
  }
  /**
   * @param MultiscaleFieldPresence
   */
  public function setCharacterTokenPresence(MultiscaleFieldPresence $characterTokenPresence)
  {
    $this->characterTokenPresence = $characterTokenPresence;
  }
  /**
   * @return MultiscaleFieldPresence
   */
  public function getCharacterTokenPresence()
  {
    return $this->characterTokenPresence;
  }
  /**
   * @param NlxDataSchemaDocument[]
   */
  public function setDocument($document)
  {
    $this->document = $document;
  }
  /**
   * @return NlxDataSchemaDocument[]
   */
  public function getDocument()
  {
    return $this->document;
  }
  /**
   * @param MultiscaleFieldPresence
   */
  public function setDocumentAuthorPresence(MultiscaleFieldPresence $documentAuthorPresence)
  {
    $this->documentAuthorPresence = $documentAuthorPresence;
  }
  /**
   * @return MultiscaleFieldPresence
   */
  public function getDocumentAuthorPresence()
  {
    return $this->documentAuthorPresence;
  }
  /**
   * @param MultiscaleFieldPresence
   */
  public function setDocumentBytesPresence(MultiscaleFieldPresence $documentBytesPresence)
  {
    $this->documentBytesPresence = $documentBytesPresence;
  }
  /**
   * @return MultiscaleFieldPresence
   */
  public function getDocumentBytesPresence()
  {
    return $this->documentBytesPresence;
  }
  /**
   * @param MultiscaleFieldPresence
   */
  public function setDocumentCharactersPresence(MultiscaleFieldPresence $documentCharactersPresence)
  {
    $this->documentCharactersPresence = $documentCharactersPresence;
  }
  /**
   * @return MultiscaleFieldPresence
   */
  public function getDocumentCharactersPresence()
  {
    return $this->documentCharactersPresence;
  }
  /**
   * @param MultiscaleFieldPresence
   */
  public function setDocumentIdPresence(MultiscaleFieldPresence $documentIdPresence)
  {
    $this->documentIdPresence = $documentIdPresence;
  }
  /**
   * @return MultiscaleFieldPresence
   */
  public function getDocumentIdPresence()
  {
    return $this->documentIdPresence;
  }
  /**
   * @param MultiscaleFieldPresence
   */
  public function setDocumentLanguageCodePresence(MultiscaleFieldPresence $documentLanguageCodePresence)
  {
    $this->documentLanguageCodePresence = $documentLanguageCodePresence;
  }
  /**
   * @return MultiscaleFieldPresence
   */
  public function getDocumentLanguageCodePresence()
  {
    return $this->documentLanguageCodePresence;
  }
  /**
   * @param MultiscaleFieldPresence
   */
  public function setDocumentLanguageSpansPresence(MultiscaleFieldPresence $documentLanguageSpansPresence)
  {
    $this->documentLanguageSpansPresence = $documentLanguageSpansPresence;
  }
  /**
   * @return MultiscaleFieldPresence
   */
  public function getDocumentLanguageSpansPresence()
  {
    return $this->documentLanguageSpansPresence;
  }
  /**
   * @param MultiscaleFieldPresence
   */
  public function setDocumentMentionsPresence(MultiscaleFieldPresence $documentMentionsPresence)
  {
    $this->documentMentionsPresence = $documentMentionsPresence;
  }
  /**
   * @return MultiscaleFieldPresence
   */
  public function getDocumentMentionsPresence()
  {
    return $this->documentMentionsPresence;
  }
  /**
   * @param MultiscaleFieldPresence
   */
  public function setDocumentParagraphsPresence(MultiscaleFieldPresence $documentParagraphsPresence)
  {
    $this->documentParagraphsPresence = $documentParagraphsPresence;
  }
  /**
   * @return MultiscaleFieldPresence
   */
  public function getDocumentParagraphsPresence()
  {
    return $this->documentParagraphsPresence;
  }
  /**
   * @param MultiscaleLayerPresence
   */
  public function setDocumentPresence(MultiscaleLayerPresence $documentPresence)
  {
    $this->documentPresence = $documentPresence;
  }
  /**
   * @return MultiscaleLayerPresence
   */
  public function getDocumentPresence()
  {
    return $this->documentPresence;
  }
  /**
   * @param MultiscaleFieldPresence
   */
  public function setDocumentSentencesPresence(MultiscaleFieldPresence $documentSentencesPresence)
  {
    $this->documentSentencesPresence = $documentSentencesPresence;
  }
  /**
   * @return MultiscaleFieldPresence
   */
  public function getDocumentSentencesPresence()
  {
    return $this->documentSentencesPresence;
  }
  /**
   * @param MultiscaleFieldPresence
   */
  public function setDocumentTextPresence(MultiscaleFieldPresence $documentTextPresence)
  {
    $this->documentTextPresence = $documentTextPresence;
  }
  /**
   * @return MultiscaleFieldPresence
   */
  public function getDocumentTextPresence()
  {
    return $this->documentTextPresence;
  }
  /**
   * @param MultiscaleFieldPresence
   */
  public function setDocumentTokensPresence(MultiscaleFieldPresence $documentTokensPresence)
  {
    $this->documentTokensPresence = $documentTokensPresence;
  }
  /**
   * @return MultiscaleFieldPresence
   */
  public function getDocumentTokensPresence()
  {
    return $this->documentTokensPresence;
  }
  /**
   * @param MultiscaleFieldPresence
   */
  public function setDocumentUrlPresence(MultiscaleFieldPresence $documentUrlPresence)
  {
    $this->documentUrlPresence = $documentUrlPresence;
  }
  /**
   * @return MultiscaleFieldPresence
   */
  public function getDocumentUrlPresence()
  {
    return $this->documentUrlPresence;
  }
  /**
   * @param NlxDataSchemaEntity[]
   */
  public function setEntity($entity)
  {
    $this->entity = $entity;
  }
  /**
   * @return NlxDataSchemaEntity[]
   */
  public function getEntity()
  {
    return $this->entity;
  }
  /**
   * @param MultiscaleFieldPresence
   */
  public function setEntityGenderPresence(MultiscaleFieldPresence $entityGenderPresence)
  {
    $this->entityGenderPresence = $entityGenderPresence;
  }
  /**
   * @return MultiscaleFieldPresence
   */
  public function getEntityGenderPresence()
  {
    return $this->entityGenderPresence;
  }
  /**
   * @param MultiscaleFieldPresence
   */
  public function setEntityMidPresence(MultiscaleFieldPresence $entityMidPresence)
  {
    $this->entityMidPresence = $entityMidPresence;
  }
  /**
   * @return MultiscaleFieldPresence
   */
  public function getEntityMidPresence()
  {
    return $this->entityMidPresence;
  }
  /**
   * @param MultiscaleFieldPresence
   */
  public function setEntityNamePresence(MultiscaleFieldPresence $entityNamePresence)
  {
    $this->entityNamePresence = $entityNamePresence;
  }
  /**
   * @return MultiscaleFieldPresence
   */
  public function getEntityNamePresence()
  {
    return $this->entityNamePresence;
  }
  /**
   * @param MultiscaleLayerPresence
   */
  public function setEntityPresence(MultiscaleLayerPresence $entityPresence)
  {
    $this->entityPresence = $entityPresence;
  }
  /**
   * @return MultiscaleLayerPresence
   */
  public function getEntityPresence()
  {
    return $this->entityPresence;
  }
  /**
   * @param MultiscaleFieldPresence
   */
  public function setEntityTypePresence(MultiscaleFieldPresence $entityTypePresence)
  {
    $this->entityTypePresence = $entityTypePresence;
  }
  /**
   * @return MultiscaleFieldPresence
   */
  public function getEntityTypePresence()
  {
    return $this->entityTypePresence;
  }
  /**
   * @param NlxDataSchemaLanguageSpan[]
   */
  public function setLanguageSpan($languageSpan)
  {
    $this->languageSpan = $languageSpan;
  }
  /**
   * @return NlxDataSchemaLanguageSpan[]
   */
  public function getLanguageSpan()
  {
    return $this->languageSpan;
  }
  /**
   * @param MultiscaleFieldPresence
   */
  public function setLanguageSpanBytesPresence(MultiscaleFieldPresence $languageSpanBytesPresence)
  {
    $this->languageSpanBytesPresence = $languageSpanBytesPresence;
  }
  /**
   * @return MultiscaleFieldPresence
   */
  public function getLanguageSpanBytesPresence()
  {
    return $this->languageSpanBytesPresence;
  }
  /**
   * @param MultiscaleFieldPresence
   */
  public function setLanguageSpanCharactersPresence(MultiscaleFieldPresence $languageSpanCharactersPresence)
  {
    $this->languageSpanCharactersPresence = $languageSpanCharactersPresence;
  }
  /**
   * @return MultiscaleFieldPresence
   */
  public function getLanguageSpanCharactersPresence()
  {
    return $this->languageSpanCharactersPresence;
  }
  /**
   * @param MultiscaleFieldPresence
   */
  public function setLanguageSpanDocumentPresence(MultiscaleFieldPresence $languageSpanDocumentPresence)
  {
    $this->languageSpanDocumentPresence = $languageSpanDocumentPresence;
  }
  /**
   * @return MultiscaleFieldPresence
   */
  public function getLanguageSpanDocumentPresence()
  {
    return $this->languageSpanDocumentPresence;
  }
  /**
   * @param MultiscaleFieldPresence
   */
  public function setLanguageSpanLanguageCodePresence(MultiscaleFieldPresence $languageSpanLanguageCodePresence)
  {
    $this->languageSpanLanguageCodePresence = $languageSpanLanguageCodePresence;
  }
  /**
   * @return MultiscaleFieldPresence
   */
  public function getLanguageSpanLanguageCodePresence()
  {
    return $this->languageSpanLanguageCodePresence;
  }
  /**
   * @param MultiscaleLayerPresence
   */
  public function setLanguageSpanPresence(MultiscaleLayerPresence $languageSpanPresence)
  {
    $this->languageSpanPresence = $languageSpanPresence;
  }
  /**
   * @return MultiscaleLayerPresence
   */
  public function getLanguageSpanPresence()
  {
    return $this->languageSpanPresence;
  }
  /**
   * @param NlxDataSchemaMention[]
   */
  public function setMention($mention)
  {
    $this->mention = $mention;
  }
  /**
   * @return NlxDataSchemaMention[]
   */
  public function getMention()
  {
    return $this->mention;
  }
  /**
   * @param MultiscaleFieldPresence
   */
  public function setMentionBytesPresence(MultiscaleFieldPresence $mentionBytesPresence)
  {
    $this->mentionBytesPresence = $mentionBytesPresence;
  }
  /**
   * @return MultiscaleFieldPresence
   */
  public function getMentionBytesPresence()
  {
    return $this->mentionBytesPresence;
  }
  /**
   * @param MultiscaleFieldPresence
   */
  public function setMentionDocumentPresence(MultiscaleFieldPresence $mentionDocumentPresence)
  {
    $this->mentionDocumentPresence = $mentionDocumentPresence;
  }
  /**
   * @return MultiscaleFieldPresence
   */
  public function getMentionDocumentPresence()
  {
    return $this->mentionDocumentPresence;
  }
  /**
   * @param MultiscaleFieldPresence
   */
  public function setMentionEntityPresence(MultiscaleFieldPresence $mentionEntityPresence)
  {
    $this->mentionEntityPresence = $mentionEntityPresence;
  }
  /**
   * @return MultiscaleFieldPresence
   */
  public function getMentionEntityPresence()
  {
    return $this->mentionEntityPresence;
  }
  /**
   * @param MultiscaleFieldPresence
   */
  public function setMentionKindPresence(MultiscaleFieldPresence $mentionKindPresence)
  {
    $this->mentionKindPresence = $mentionKindPresence;
  }
  /**
   * @return MultiscaleFieldPresence
   */
  public function getMentionKindPresence()
  {
    return $this->mentionKindPresence;
  }
  /**
   * @param MultiscaleLayerPresence
   */
  public function setMentionPresence(MultiscaleLayerPresence $mentionPresence)
  {
    $this->mentionPresence = $mentionPresence;
  }
  /**
   * @return MultiscaleLayerPresence
   */
  public function getMentionPresence()
  {
    return $this->mentionPresence;
  }
  /**
   * @param MultiscaleFieldPresence
   */
  public function setMentionTextPresence(MultiscaleFieldPresence $mentionTextPresence)
  {
    $this->mentionTextPresence = $mentionTextPresence;
  }
  /**
   * @return MultiscaleFieldPresence
   */
  public function getMentionTextPresence()
  {
    return $this->mentionTextPresence;
  }
  /**
   * @param MultiscaleFieldPresence
   */
  public function setMentionTokensPresence(MultiscaleFieldPresence $mentionTokensPresence)
  {
    $this->mentionTokensPresence = $mentionTokensPresence;
  }
  /**
   * @return MultiscaleFieldPresence
   */
  public function getMentionTokensPresence()
  {
    return $this->mentionTokensPresence;
  }
  /**
   * @param MultiscaleFieldPresence
   */
  public function setMentionTypePresence(MultiscaleFieldPresence $mentionTypePresence)
  {
    $this->mentionTypePresence = $mentionTypePresence;
  }
  /**
   * @return MultiscaleFieldPresence
   */
  public function getMentionTypePresence()
  {
    return $this->mentionTypePresence;
  }
  /**
   * @param NlxDataSchemaParagraph[]
   */
  public function setParagraph($paragraph)
  {
    $this->paragraph = $paragraph;
  }
  /**
   * @return NlxDataSchemaParagraph[]
   */
  public function getParagraph()
  {
    return $this->paragraph;
  }
  /**
   * @param MultiscaleFieldPresence
   */
  public function setParagraphBytesPresence(MultiscaleFieldPresence $paragraphBytesPresence)
  {
    $this->paragraphBytesPresence = $paragraphBytesPresence;
  }
  /**
   * @return MultiscaleFieldPresence
   */
  public function getParagraphBytesPresence()
  {
    return $this->paragraphBytesPresence;
  }
  /**
   * @param MultiscaleFieldPresence
   */
  public function setParagraphCharactersPresence(MultiscaleFieldPresence $paragraphCharactersPresence)
  {
    $this->paragraphCharactersPresence = $paragraphCharactersPresence;
  }
  /**
   * @return MultiscaleFieldPresence
   */
  public function getParagraphCharactersPresence()
  {
    return $this->paragraphCharactersPresence;
  }
  /**
   * @param MultiscaleFieldPresence
   */
  public function setParagraphDocumentPresence(MultiscaleFieldPresence $paragraphDocumentPresence)
  {
    $this->paragraphDocumentPresence = $paragraphDocumentPresence;
  }
  /**
   * @return MultiscaleFieldPresence
   */
  public function getParagraphDocumentPresence()
  {
    return $this->paragraphDocumentPresence;
  }
  /**
   * @param MultiscaleLayerPresence
   */
  public function setParagraphPresence(MultiscaleLayerPresence $paragraphPresence)
  {
    $this->paragraphPresence = $paragraphPresence;
  }
  /**
   * @return MultiscaleLayerPresence
   */
  public function getParagraphPresence()
  {
    return $this->paragraphPresence;
  }
  /**
   * @param MultiscaleFieldPresence
   */
  public function setParagraphSentencesPresence(MultiscaleFieldPresence $paragraphSentencesPresence)
  {
    $this->paragraphSentencesPresence = $paragraphSentencesPresence;
  }
  /**
   * @return MultiscaleFieldPresence
   */
  public function getParagraphSentencesPresence()
  {
    return $this->paragraphSentencesPresence;
  }
  /**
   * @param MultiscaleFieldPresence
   */
  public function setParagraphTextPresence(MultiscaleFieldPresence $paragraphTextPresence)
  {
    $this->paragraphTextPresence = $paragraphTextPresence;
  }
  /**
   * @return MultiscaleFieldPresence
   */
  public function getParagraphTextPresence()
  {
    return $this->paragraphTextPresence;
  }
  /**
   * @param MultiscaleFieldPresence
   */
  public function setParagraphTokensPresence(MultiscaleFieldPresence $paragraphTokensPresence)
  {
    $this->paragraphTokensPresence = $paragraphTokensPresence;
  }
  /**
   * @return MultiscaleFieldPresence
   */
  public function getParagraphTokensPresence()
  {
    return $this->paragraphTokensPresence;
  }
  /**
   * @param NlxDataSchemaSentence[]
   */
  public function setSentence($sentence)
  {
    $this->sentence = $sentence;
  }
  /**
   * @return NlxDataSchemaSentence[]
   */
  public function getSentence()
  {
    return $this->sentence;
  }
  /**
   * @param MultiscaleFieldPresence
   */
  public function setSentenceBytesPresence(MultiscaleFieldPresence $sentenceBytesPresence)
  {
    $this->sentenceBytesPresence = $sentenceBytesPresence;
  }
  /**
   * @return MultiscaleFieldPresence
   */
  public function getSentenceBytesPresence()
  {
    return $this->sentenceBytesPresence;
  }
  /**
   * @param MultiscaleFieldPresence
   */
  public function setSentenceCharactersPresence(MultiscaleFieldPresence $sentenceCharactersPresence)
  {
    $this->sentenceCharactersPresence = $sentenceCharactersPresence;
  }
  /**
   * @return MultiscaleFieldPresence
   */
  public function getSentenceCharactersPresence()
  {
    return $this->sentenceCharactersPresence;
  }
  /**
   * @param MultiscaleFieldPresence
   */
  public function setSentenceDocumentPresence(MultiscaleFieldPresence $sentenceDocumentPresence)
  {
    $this->sentenceDocumentPresence = $sentenceDocumentPresence;
  }
  /**
   * @return MultiscaleFieldPresence
   */
  public function getSentenceDocumentPresence()
  {
    return $this->sentenceDocumentPresence;
  }
  /**
   * @param MultiscaleFieldPresence
   */
  public function setSentenceParagraphPresence(MultiscaleFieldPresence $sentenceParagraphPresence)
  {
    $this->sentenceParagraphPresence = $sentenceParagraphPresence;
  }
  /**
   * @return MultiscaleFieldPresence
   */
  public function getSentenceParagraphPresence()
  {
    return $this->sentenceParagraphPresence;
  }
  /**
   * @param MultiscaleLayerPresence
   */
  public function setSentencePresence(MultiscaleLayerPresence $sentencePresence)
  {
    $this->sentencePresence = $sentencePresence;
  }
  /**
   * @return MultiscaleLayerPresence
   */
  public function getSentencePresence()
  {
    return $this->sentencePresence;
  }
  /**
   * @param MultiscaleFieldPresence
   */
  public function setSentenceTextPresence(MultiscaleFieldPresence $sentenceTextPresence)
  {
    $this->sentenceTextPresence = $sentenceTextPresence;
  }
  /**
   * @return MultiscaleFieldPresence
   */
  public function getSentenceTextPresence()
  {
    return $this->sentenceTextPresence;
  }
  /**
   * @param MultiscaleFieldPresence
   */
  public function setSentenceTokensPresence(MultiscaleFieldPresence $sentenceTokensPresence)
  {
    $this->sentenceTokensPresence = $sentenceTokensPresence;
  }
  /**
   * @return MultiscaleFieldPresence
   */
  public function getSentenceTokensPresence()
  {
    return $this->sentenceTokensPresence;
  }
  /**
   * @param NlxDataSchemaToken[]
   */
  public function setToken($token)
  {
    $this->token = $token;
  }
  /**
   * @return NlxDataSchemaToken[]
   */
  public function getToken()
  {
    return $this->token;
  }
  /**
   * @param MultiscaleFieldPresence
   */
  public function setTokenBytesPresence(MultiscaleFieldPresence $tokenBytesPresence)
  {
    $this->tokenBytesPresence = $tokenBytesPresence;
  }
  /**
   * @return MultiscaleFieldPresence
   */
  public function getTokenBytesPresence()
  {
    return $this->tokenBytesPresence;
  }
  /**
   * @param MultiscaleFieldPresence
   */
  public function setTokenCharactersPresence(MultiscaleFieldPresence $tokenCharactersPresence)
  {
    $this->tokenCharactersPresence = $tokenCharactersPresence;
  }
  /**
   * @return MultiscaleFieldPresence
   */
  public function getTokenCharactersPresence()
  {
    return $this->tokenCharactersPresence;
  }
  /**
   * @param MultiscaleFieldPresence
   */
  public function setTokenDependencyHeadPresence(MultiscaleFieldPresence $tokenDependencyHeadPresence)
  {
    $this->tokenDependencyHeadPresence = $tokenDependencyHeadPresence;
  }
  /**
   * @return MultiscaleFieldPresence
   */
  public function getTokenDependencyHeadPresence()
  {
    return $this->tokenDependencyHeadPresence;
  }
  /**
   * @param MultiscaleFieldPresence
   */
  public function setTokenDependencyLabelPresence(MultiscaleFieldPresence $tokenDependencyLabelPresence)
  {
    $this->tokenDependencyLabelPresence = $tokenDependencyLabelPresence;
  }
  /**
   * @return MultiscaleFieldPresence
   */
  public function getTokenDependencyLabelPresence()
  {
    return $this->tokenDependencyLabelPresence;
  }
  /**
   * @param MultiscaleFieldPresence
   */
  public function setTokenDependencyPresence(MultiscaleFieldPresence $tokenDependencyPresence)
  {
    $this->tokenDependencyPresence = $tokenDependencyPresence;
  }
  /**
   * @return MultiscaleFieldPresence
   */
  public function getTokenDependencyPresence()
  {
    return $this->tokenDependencyPresence;
  }
  /**
   * @param MultiscaleFieldPresence
   */
  public function setTokenDocumentPresence(MultiscaleFieldPresence $tokenDocumentPresence)
  {
    $this->tokenDocumentPresence = $tokenDocumentPresence;
  }
  /**
   * @return MultiscaleFieldPresence
   */
  public function getTokenDocumentPresence()
  {
    return $this->tokenDocumentPresence;
  }
  /**
   * @param MultiscaleFieldPresence
   */
  public function setTokenParagraphPresence(MultiscaleFieldPresence $tokenParagraphPresence)
  {
    $this->tokenParagraphPresence = $tokenParagraphPresence;
  }
  /**
   * @return MultiscaleFieldPresence
   */
  public function getTokenParagraphPresence()
  {
    return $this->tokenParagraphPresence;
  }
  /**
   * @param MultiscaleFieldPresence
   */
  public function setTokenPosPresence(MultiscaleFieldPresence $tokenPosPresence)
  {
    $this->tokenPosPresence = $tokenPosPresence;
  }
  /**
   * @return MultiscaleFieldPresence
   */
  public function getTokenPosPresence()
  {
    return $this->tokenPosPresence;
  }
  /**
   * @param MultiscaleLayerPresence
   */
  public function setTokenPresence(MultiscaleLayerPresence $tokenPresence)
  {
    $this->tokenPresence = $tokenPresence;
  }
  /**
   * @return MultiscaleLayerPresence
   */
  public function getTokenPresence()
  {
    return $this->tokenPresence;
  }
  /**
   * @param MultiscaleFieldPresence
   */
  public function setTokenSentencePresence(MultiscaleFieldPresence $tokenSentencePresence)
  {
    $this->tokenSentencePresence = $tokenSentencePresence;
  }
  /**
   * @return MultiscaleFieldPresence
   */
  public function getTokenSentencePresence()
  {
    return $this->tokenSentencePresence;
  }
  /**
   * @param MultiscaleFieldPresence
   */
  public function setTokenTextPresence(MultiscaleFieldPresence $tokenTextPresence)
  {
    $this->tokenTextPresence = $tokenTextPresence;
  }
  /**
   * @return MultiscaleFieldPresence
   */
  public function getTokenTextPresence()
  {
    return $this->tokenTextPresence;
  }
}

// Adding a class alias for backwards compatibility with the previous class name.
class_alias(NlxDataSchemaScaleSet::class, 'Google_Service_Contentwarehouse_NlxDataSchemaScaleSet');
