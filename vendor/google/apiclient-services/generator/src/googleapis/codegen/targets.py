#!/usr/bin/python2.7
# Copyright 2011 Google Inc. All Rights Reserved.
#
#  Licensed under the Apache License, Version 2.0 (the "License");
#  you may not use this file except in compliance with the License.
#  You may obtain a copy of the License at
#
#      http://www.apache.org/licenses/LICENSE-2.0
#
#  Unless required by applicable law or agreed to in writing, software
#  distributed under the License is distributed on an "AS IS" BASIS,
#  WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
#  See the License for the specific language governing permissions and
#  limitations under the License.

"""Targets class describes which languages/platforms we support."""

__author__ = 'wclarkso@google.com (Will Clarkson)'

import logging
import os


from googleapis.codegen.filesys import files
from googleapis.codegen.utilities import json_expander
from googleapis.codegen.utilities import json_with_comments


class Targets(object):
  """Targets maintains the list of possible target options.

  Reads targets.json file in local directory. This file is formatted
  as:
  {
  'languages': {
    'languageA': {
      'surface_option1': {
        'path': 'stable',
        'description': 'something about language A',
        'displayName': 'SurfaceOption1',
      },
      'surface_option2': {
        'path': 'experimental',
        'description': 'something about language A',
        'displayName': 'SurfaceOption2',
        'platforms': ['cmd-line'],
      }
     },
    'languageB': {
      ...
    }, ...
    },
  'platforms': {
    'cmd-line': {
      'displayName': 'Pretty Platform Name'
    }
   }
  }
  """

  def __init__(self, targets_path=None, template_root=None, targets_dict=None):
    """Constructor.

    Loads targets file.

    Args:
      targets_path: (str) Path to targets file. Defaults to './targets.json'
      template_root: (str) Path to template root. Defaults to '.'
      targets_dict: (dict) Initial data, if not supplied from a file.

    Raises:
      ValueError: if the targets file does not contain the required sections.
    """
    self.template_root = template_root or Targets._default_template_root
    self.targets_path = targets_path or os.path.join(self.template_root,
                                                     'targets.json')
    if targets_dict:
      self._targets_dict = targets_dict
    else:
      self._targets_dict = json_with_comments.Loads(
          files.GetFileContents(self.targets_path))

    # Do some basic validation that this has the required fields
    if 'languages' not in self._targets_dict:
      raise ValueError('languages not in targets.json')

  def Dict(self):
    """The targets.json file as a dictionary."""
    return self._targets_dict

  def VariationsForLanguage(self, language):
    language_def = self._targets_dict['languages'].get(language)
    if not language_def:
      return None
    return Variations(self, language, language_def['variations'])

  def GetLanguage(self, language):
    return self._targets_dict['languages'][language]

  def Languages(self):
    return self._targets_dict['languages']

  def Platforms(self):
    return self._targets_dict.get('platforms', {})

  @staticmethod
  def SetDefaultTemplateRoot(path):
    """Sets a new default full path to the templates directory.

    Args:
      path: (str) full path to templates directory.
    """
    # This is not a classmethod because we don't want subclasses
    # to shadow this value.
    logging.info('setting default template root to %s', path)
    Targets._default_template_root = path

  @staticmethod
  def GetDefaultTemplateRoot():
    return Targets._default_template_root

  # Set the initial template root.
  _default_template_root = os.path.join(os.path.dirname(__file__),
                                        'languages')

  # Whether to use variation release versions when calculating template paths.
  use_versioned_paths = False

  @staticmethod
  def SetUseVersionedPaths(use_versioned_paths):
    """Sets whether versions are used in the template path."""
    # This is not a classmethod because we don't want subclasses
    # to shadow this value.
    Targets.use_versioned_paths = use_versioned_paths


class Variations(dict):
  """A set of variations available for a particular language."""

  def __init__(self, targets, language, variations_dict):
    super(Variations, self).__init__(variations_dict)
    self._targets = targets
    self._language = language

  def IsValid(self, variation):
    """Test is a variation exists."""
    return variation in self

  def _RelativeTemplateDir(self, variation):
    """Returns the path to template dir for the selected variation.

    By default, the path is the same as the variation name. It can be
    overridden in two ways, of descending precedence:
      1. by the 'releaseVersion' element, if use_versioned_paths is set.
      2. with an explicit 'path' statement.

    Args:
      variation: (str) A target variation name.
    Returns:
      (str) Relative path to template directory.
    """
    if self._targets.use_versioned_paths:
      path = self[variation].get('releaseVersion') or variation
    else:
      path = None
    if not path:
      path = self.get(variation, {}).get('path') or variation
    return os.path.join(self._language, path)

  def AbsoluteTemplateDir(self, variation):
    """Returns the path to template dir for the selected variation.

    Args:
      variation: (str) A target variation name.
    Returns:
      (str) Absolute path to template directory.
    """
    return os.path.join(self._targets.template_root,
                        self._RelativeTemplateDir(variation))

  def GetFeaturesForReleaseVersion(self, release_version):
    for name in self:
      features = self.GetFeatures(name)
      if release_version == features.get('releaseVersion'):
        return features
    return None

  def GetFeatures(self, variation):
    """Returns the features dictionary for a specific variation.

    This is the basic dictionary informaion plus any specific overrides in
    the per-template-tree features.json file.

    Args:
      variation: (str) A target variation name.
    Returns:
      (Features) features dictionary
    """
    if not variation:
      return None
    template_dir = self.AbsoluteTemplateDir(variation)
    features = Features(template_dir, self.get(variation), variation)
    json_path = os.path.join(template_dir, 'features.json')

    try:
      features_json = files.GetFileContents(json_path)
    except files.FileDoesNotExist:
      # for backwards compatibility, we forgive this.
      # TODO(user): be stricter about this and
      # fix/remove any tests that fail as a result.
      return features

    features.update(json_expander.ExpandJsonTemplate(
        json_with_comments.Loads(features_json)))
    # If not specified, the releaseVersion matches the variation
    if not features.get('releaseVersion'):
      features['releaseVersion'] = variation
    return features


class Features(dict):
  """A dictionary describing the features of a particular API variation."""

  # TODO(user): Do we need initial_content? The only thing we see in it is
  # path, which should be set explicitly to the dirname of the real file path.
  def __init__(self, template_dir, initial_content=None, name=None):
    super(Features, self).__init__(initial_content or {})
    self.name = name
    self.template_dir = template_dir
    if 'path' not in self:
      self['path'] = os.path.basename(template_dir)
