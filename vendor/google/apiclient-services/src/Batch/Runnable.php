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

namespace Google\Service\Batch;

class Runnable extends \Google\Model
{
  /**
   * @var bool
   */
  public $alwaysRun;
  /**
   * @var bool
   */
  public $background;
  protected $barrierType = Barrier::class;
  protected $barrierDataType = '';
  public $barrier;
  protected $containerType = Container::class;
  protected $containerDataType = '';
  public $container;
  protected $environmentType = Environment::class;
  protected $environmentDataType = '';
  public $environment;
  /**
   * @var bool
   */
  public $ignoreExitStatus;
  protected $scriptType = Script::class;
  protected $scriptDataType = '';
  public $script;
  /**
   * @var string
   */
  public $timeout;

  /**
   * @param bool
   */
  public function setAlwaysRun($alwaysRun)
  {
    $this->alwaysRun = $alwaysRun;
  }
  /**
   * @return bool
   */
  public function getAlwaysRun()
  {
    return $this->alwaysRun;
  }
  /**
   * @param bool
   */
  public function setBackground($background)
  {
    $this->background = $background;
  }
  /**
   * @return bool
   */
  public function getBackground()
  {
    return $this->background;
  }
  /**
   * @param Barrier
   */
  public function setBarrier(Barrier $barrier)
  {
    $this->barrier = $barrier;
  }
  /**
   * @return Barrier
   */
  public function getBarrier()
  {
    return $this->barrier;
  }
  /**
   * @param Container
   */
  public function setContainer(Container $container)
  {
    $this->container = $container;
  }
  /**
   * @return Container
   */
  public function getContainer()
  {
    return $this->container;
  }
  /**
   * @param Environment
   */
  public function setEnvironment(Environment $environment)
  {
    $this->environment = $environment;
  }
  /**
   * @return Environment
   */
  public function getEnvironment()
  {
    return $this->environment;
  }
  /**
   * @param bool
   */
  public function setIgnoreExitStatus($ignoreExitStatus)
  {
    $this->ignoreExitStatus = $ignoreExitStatus;
  }
  /**
   * @return bool
   */
  public function getIgnoreExitStatus()
  {
    return $this->ignoreExitStatus;
  }
  /**
   * @param Script
   */
  public function setScript(Script $script)
  {
    $this->script = $script;
  }
  /**
   * @return Script
   */
  public function getScript()
  {
    return $this->script;
  }
  /**
   * @param string
   */
  public function setTimeout($timeout)
  {
    $this->timeout = $timeout;
  }
  /**
   * @return string
   */
  public function getTimeout()
  {
    return $this->timeout;
  }
}

// Adding a class alias for backwards compatibility with the previous class name.
class_alias(Runnable::class, 'Google_Service_Batch_Runnable');
