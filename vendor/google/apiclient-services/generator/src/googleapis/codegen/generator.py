#!/usr/bin/python2.7
# Copyright 2010 Google Inc. All Rights Reserved.
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

"""Base library generator.

This module holds the base classes used for all code generators.
"""

__author__ = 'aiuto@google.com (Tony Aiuto)'

import datetime
import os
import re
import StringIO
import time
import zipfile


from googleapis.codegen.django_helpers import DjangoRenderTemplate
from googleapis.codegen.language_model import LanguageModel
from googleapis.codegen.template_objects import UseableInTemplates
# Has to be after django_helpers pylint: disable=g-bad-import-order
from googleapis.codegen import template_helpers
from googleapis.codegen.filesys import files

# This block is static information about the generator which will get passed
# into templates.
_GENERATOR_INFORMATION = {
    'name': 'google-apis-code-generator',
    'version': '1.5.1',
    'buildDate': '2015-03-24',
    }

# app.yaml and other names that app engine refuses to open.
# TODO(user) Remove once templates are stored in BlobStore.
_SPECIAL_FILENAMES = ['app_yaml']


class TemplateGenerator(object):
  """Base class for walking a template tree to generate output files.

  This class provides methods for processing template trees to produce output
  trees.
  * Provides a common base dictionary of variables for use in templates.
  * Callers can augment that with their own dictionary of variables.
  * Callers can provide a set of replacements to be made to file paths in
    the template tree
  """

  def __init__(self, language_model=None, options=None):
    self._tool_info = ToolInformation()
    self._options = options or dict()
    self._template_dir = os.path.dirname(__file__)
    self._surface_features = {}
    self._language_model = language_model or LanguageModel()

  @property
  def language_model(self):
    return self._language_model

  def IncludeFileTree(self, path_to_tree, package):
    """Walk a file tree and copy files directly into an output package.

    Walks a file tree relative to the target language, copying all files
    found into an output package.

    Args:
      path_to_tree: (str) path relative to the language template directory
      package: (LibraryPackage) output package.
    """
    top_of_tree = os.path.join(self._template_dir, path_to_tree)
    # Walk tree for jar files to directly include
    for path in files.IterFiles(top_of_tree):
      relative_path = path[len(top_of_tree) + 1:]
      package.IncludeFile(path, relative_path)

  def PathToTemplate(self, template_name):
    """Returns the full path to a template."""
    return os.path.join(self._template_dir, template_name)

  def RenderTemplate(self, template_path, context_dict=None):
    """Render a template.

    Renders a template with the standard dictionary of bindings.

    Args:
      template_path: (str) Full path to a template.
      context_dict: (dict) A dictionary to augment the standard template
        dictionary.
    Returns:
      (str) The fully rendered template string.
    """
    variables_dict = {
        'tool': self._tool_info,  # Information about the build tool
        'options': self._options,  # Options for this invocation
        'template_dir': self._template_dir,  # path to the template tree
        'features': self._surface_features,  # sub language options
        'language_model': self._language_model
        }
    if context_dict:
      variables_dict.update(context_dict)
    return DjangoRenderTemplate(template_path, variables_dict)

  def RenderTemplateToFile(self, template_path, context_dict, package,
                           output_path):
    """Render a template as a file in the output package.

    Args:
      template_path: (str) Full path to a template.
      context_dict: (dict) A dictionary to augment the standard template
        dictionary.
      package: (LibraryPackage) output package.
      output_path: (str) file path in the package.

    Returns:
      None
    """
    output_dir, file_name = os.path.split(output_path)

    def WriteFileInPackage(path, content):
      """Writes content to a path in our current package writer."""
      if isinstance(content, unicode):
        content = content.encode('utf-8', errors='ignore')
      out = package.StartFile(os.path.join(output_dir, path))
      out.write(content)
      package.EndFile()

    try:
      context_dict[template_helpers.FILE_WRITER] = WriteFileInPackage
      content = self.RenderTemplate(template_path, context_dict)
      WriteFileInPackage(file_name, content)
    except template_helpers.Halt:
      pass
    del context_dict[template_helpers.FILE_WRITER]

  def WalkTemplateTree(self, path_to_tree, path_replacements, list_replacements,
                       variables, package, file_filter=None):
    """Walk a file tree and copy files or process templates.

    Walks a file tree to write on output package, running all files ending in
    ".tmpl" through the template renderer, and directly copying all the other
    files. While doing so, the caller may provide for some transformations of
    the file path:
    1. They can pass in a dictionary of string literals to replacements which
       are applied directly to the file path. E.g. '___package___' might be
       replaced by a path snippet of the form 'com/google/tasks'
    2. They can pass a dictionary of strings literals which, if found in the
       file path trigger the evaluation of thats template against a list of
       CodeObjects, with a distinct output file being generated for each object.
       E.g. The ApiLibraryGenerator uses the pattern  '___models_className___'
       to generate a set of files, each named by its className member.

    Args:
      path_to_tree: (str) path relative to the language template directory.
      path_replacements: (dict) dict holding elements which should be replaced
        if found in a path.
      list_replacements: (dict) dict holding elements which should be replaced
        by many files when found in a path. The keys of the dict are strings
        to be found in a path. The values are a tuple of
           (name_to_bind, [list of code objects])
        where name_to_bind is a variable name which will be bound to each
        successive code object during template evaluation. See the
        GenerateListOfFiles method for more details about name expansion.
      variables: (dict) The dictionary of variable replacements to pass to the
         templates.
      package: (LibraryPackage) output package.
      file_filter: (func) method to allow the caller to filter files included
         by name. The method is called with 2 arguments, the template file path
         and the path after all path replacements are done.
    """

    def ExpandZipFile(path):
      """Expand a zip file found in the template tree.

      Args:
        path: Path to file, relative to top of template tree.
      """
      full_path = os.path.join(self._template_dir, path)
      zip_slurp = files.GetFileContents(full_path)
      archive = zipfile.ZipFile(StringIO.StringIO(zip_slurp), 'r')
      for info in archive.infolist():
        package.WriteDataAsFile(
            archive.read(info.filename),
            os.path.join(relative_path, info.filename))

    top_of_tree = os.path.normpath(
        os.path.join(self._template_dir, path_to_tree))
    # Walk tree for jar files to directly include
    variables.update({'template_dir': top_of_tree})
    for path in files.IterFiles(top_of_tree):
      root, file_name = os.path.split(path)
      template_path = file_name
      relative_path = root[len(top_of_tree) + 1:]

      # Perform the replacements on the path and file name
      for path_item, replacement in path_replacements.iteritems():
        relative_path = relative_path.replace(path_item, replacement)
      for path_item, replacement in path_replacements.iteritems():
        file_name = file_name.replace(path_item, replacement)
      full_template_path = os.path.join(relative_path, template_path)

      for path_item, call_info in list_replacements.iteritems():
        if file_name.find(path_item) >= 0:
          self.GenerateListOfFiles(path_item, call_info, path, relative_path,
                                   file_name, variables, package,
                                   file_filter=file_filter)

      if file_name.startswith('___unzip___'):
        # TODO(user) Doesn't account for changes to relative_path above.
        ExpandZipFile(path)
        continue
      if file_name.startswith('_'):
        continue
      if file_name.endswith('.tmpl'):
        name_in_zip = file_name[:-5]  # strip '.tmpl'
        if name_in_zip in _SPECIAL_FILENAMES:
          name_in_zip = name_in_zip.replace('_', '.')
        full_output_path = os.path.join(relative_path, name_in_zip)
        if file_filter and not file_filter(full_template_path,
                                           full_output_path):
          continue
        self.RenderTemplateToFile(path, variables, package, full_output_path)
      else:
        full_output_path = os.path.join(relative_path, file_name)
        if file_filter and not file_filter(full_template_path,
                                           full_output_path):
          continue
        package.IncludeFile(path, full_output_path)

  def GeneratePackage(self, package_writer):
    """Generate the package.

    Args:
      package_writer: (LibraryPackage) output package
    """
    # COV_NF_START
    raise NotImplementedError(
        'GeneratePackage must be implemented by all subclasses')
    # COV_NF_END

  def DefaultGeneratePackage(self, package_writer, path_replacements,
                             variables):
    """Default operations to generate the package.

    Do all the default operations for generating a package.
    1. Walk the template tree to generate the source.
    2. Optionally copy in dependencies

    This is a utility method intended for subclasses of TemplateGenerator, so
    that they may implement the bulk of GeneratePackage by calling this.

    Args:
      package_writer: (LibraryPackage) output package.
      path_replacements: (dict) dict holding elements which should be replaced
         if found in a path.
      variables: (dict) The dictionary of variable replacements to pass to the
         templates.
    """
    self.WalkTemplateTree('templates', path_replacements, {}, variables,
                          package_writer)

  def SetFeatures(self, surface_features):
    """Sets the dict to be used for the 'features' variable in templates."""
    self._surface_features = surface_features

  @property
  def features(self):
    return self._surface_features

  @features.setter
  def features(self, surface_features):
    self._surface_features = surface_features

  @property
  def language_version(self):
    if self._surface_features:
      return self._surface_features.get('releaseVersion')

  def SetTemplateDir(self, template_dir):
    """Sets the template directory tree to use for WalkTemplateTree.

    Args:
      template_dir: (str) Path to template tree. If it is an absolute path it
        will be used directly. If relative, it is taken relative to the source
        tree.
    """
    if template_dir.startswith('/'):
      self._template_dir = template_dir
    else:
      self._template_dir = os.path.join(self._template_dir, template_dir)

  def GenerateListOfFiles(self, path_prefix, call_info, template_path,
                          relative_path, template_file_name, variables,
                          package, file_filter=None):
    """Generate many output files from a template.

    This method blends together a list of CodeObjects (from call_info) with
    the template_file_name to produce an output file for each of the elements
    in the list. The names for each file are derived from a template variable
    of each element.

    Args:
      path_prefix: (str) The piece of path which triggers the replacement.
      call_info: (list) ['name to bind', [list of CodeObjects]]
      template_path: (str) The path of the template file.
      relative_path: (str) The relative path of the output file in the package.
      template_file_name: (str) the file name of the template for this list.
        The file name must contain the form '{path_prefix}{variable_name}___'
        (without the braces). The pair is replaced by the value of variable_name
        from each successive element of the call list.
      variables: (dict) The dictionary of variable replacements to pass to the
         templates.
      package: (LibraryWriter) The output package stream to write to.
      file_filter: (func) See WalkTemplateTree for a description.

    Raises:
      ValueError: If the template_file_name does not match the call_info data.
    """
    path_and_var_regex = r'%s([a-z][A-Za-z]*)___' % path_prefix
    match_obj = re.compile(path_and_var_regex).match(template_file_name)
    if not match_obj:
      raise ValueError(
          'file names which match path item for GenerateListOfFiles must'
          ' contain a variable for substitution. E.g. "___models_codeName___"')
    variable_name = match_obj.group(1)
    file_name_piece_to_replace = path_prefix + variable_name + '___'
    for element in call_info[1]:
      file_name = template_file_name.replace(
          file_name_piece_to_replace, element.values[variable_name])
      name_in_zip = file_name[:-5]  # strip '.tmpl'
      if file_filter and not file_filter(None, name_in_zip):
        continue
      d = dict(variables)
      d[call_info[0]] = element
      self.RenderTemplateToFile(
          template_path, d, package, os.path.join(relative_path, name_in_zip))


class ToolInformation(UseableInTemplates):
  """Defines information about this generator tool itself."""

  def __init__(self):
    super(ToolInformation, self).__init__(_GENERATOR_INFORMATION)
    now = datetime.datetime.utcnow()
    self.SetTemplateValue('runDate',
                          '%4d-%02d-%02d' % (now.year, now.month, now.day))
    self.SetTemplateValue(
        'runTime',
        '%02d:%02d:%02d UTC' % (now.hour, now.minute, now.second))
