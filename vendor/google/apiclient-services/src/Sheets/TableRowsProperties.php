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

namespace Google\Service\Sheets;

class TableRowsProperties extends \Google\Model
{
  protected $firstBandColorStyleType = ColorStyle::class;
  protected $firstBandColorStyleDataType = '';
  protected $footerColorStyleType = ColorStyle::class;
  protected $footerColorStyleDataType = '';
  protected $headerColorStyleType = ColorStyle::class;
  protected $headerColorStyleDataType = '';
  protected $secondBandColorStyleType = ColorStyle::class;
  protected $secondBandColorStyleDataType = '';

  /**
   * @param ColorStyle
   */
  public function setFirstBandColorStyle(ColorStyle $firstBandColorStyle)
  {
    $this->firstBandColorStyle = $firstBandColorStyle;
  }
  /**
   * @return ColorStyle
   */
  public function getFirstBandColorStyle()
  {
    return $this->firstBandColorStyle;
  }
  /**
   * @param ColorStyle
   */
  public function setFooterColorStyle(ColorStyle $footerColorStyle)
  {
    $this->footerColorStyle = $footerColorStyle;
  }
  /**
   * @return ColorStyle
   */
  public function getFooterColorStyle()
  {
    return $this->footerColorStyle;
  }
  /**
   * @param ColorStyle
   */
  public function setHeaderColorStyle(ColorStyle $headerColorStyle)
  {
    $this->headerColorStyle = $headerColorStyle;
  }
  /**
   * @return ColorStyle
   */
  public function getHeaderColorStyle()
  {
    return $this->headerColorStyle;
  }
  /**
   * @param ColorStyle
   */
  public function setSecondBandColorStyle(ColorStyle $secondBandColorStyle)
  {
    $this->secondBandColorStyle = $secondBandColorStyle;
  }
  /**
   * @return ColorStyle
   */
  public function getSecondBandColorStyle()
  {
    return $this->secondBandColorStyle;
  }
}

// Adding a class alias for backwards compatibility with the previous class name.
class_alias(TableRowsProperties::class, 'Google_Service_Sheets_TableRowsProperties');
