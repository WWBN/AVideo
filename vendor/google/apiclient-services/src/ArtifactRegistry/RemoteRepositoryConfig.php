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

namespace Google\Service\ArtifactRegistry;

class RemoteRepositoryConfig extends \Google\Model
{
  /**
   * @var string
   */
  public $description;
  protected $dockerRepositoryType = DockerRepository::class;
  protected $dockerRepositoryDataType = '';
  protected $mavenRepositoryType = MavenRepository::class;
  protected $mavenRepositoryDataType = '';
  protected $npmRepositoryType = NpmRepository::class;
  protected $npmRepositoryDataType = '';
  protected $pythonRepositoryType = PythonRepository::class;
  protected $pythonRepositoryDataType = '';

  /**
   * @param string
   */
  public function setDescription($description)
  {
    $this->description = $description;
  }
  /**
   * @return string
   */
  public function getDescription()
  {
    return $this->description;
  }
  /**
   * @param DockerRepository
   */
  public function setDockerRepository(DockerRepository $dockerRepository)
  {
    $this->dockerRepository = $dockerRepository;
  }
  /**
   * @return DockerRepository
   */
  public function getDockerRepository()
  {
    return $this->dockerRepository;
  }
  /**
   * @param MavenRepository
   */
  public function setMavenRepository(MavenRepository $mavenRepository)
  {
    $this->mavenRepository = $mavenRepository;
  }
  /**
   * @return MavenRepository
   */
  public function getMavenRepository()
  {
    return $this->mavenRepository;
  }
  /**
   * @param NpmRepository
   */
  public function setNpmRepository(NpmRepository $npmRepository)
  {
    $this->npmRepository = $npmRepository;
  }
  /**
   * @return NpmRepository
   */
  public function getNpmRepository()
  {
    return $this->npmRepository;
  }
  /**
   * @param PythonRepository
   */
  public function setPythonRepository(PythonRepository $pythonRepository)
  {
    $this->pythonRepository = $pythonRepository;
  }
  /**
   * @return PythonRepository
   */
  public function getPythonRepository()
  {
    return $this->pythonRepository;
  }
}

// Adding a class alias for backwards compatibility with the previous class name.
class_alias(RemoteRepositoryConfig::class, 'Google_Service_ArtifactRegistry_RemoteRepositoryConfig');
