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

"""Generator for an API library.

This module specializes TemplateGenerator for building API libraries.
"""

__author__ = 'aiuto@google.com (Tony Aiuto)'

from googleapis.codegen.generator import TemplateGenerator


class ApiLibraryGenerator(TemplateGenerator):
  """TemplateGenerator specialization which produces an API library."""

  def __init__(self, api_loader, discovery, language, language_model=None,
               options=None):
    """Construct an ApiLibraryGenerator.

    Args:
      api_loader: (Api) Method which can construct an Api from discovery.
      discovery: (dict) A discovery definition.
      language: (str) The target language name. This has no semantic meaning
          other than to specify the template set to use.
      language_model: (LanguageModel) The target language data model.
      options: (dict) Code generator options.
    """
    super(ApiLibraryGenerator, self).__init__(language_model=language_model,
                                              options=options)
    # Load the API definition and an prepare it for generating code.
    # TODO(user): package_path and version_package are really changes to the
    # API data model, rather than generator options. Move them to a distinct
    # parameter, and pass them down to the api_loader.
    options = options or {}
    module_path = options.get('package_path')
    if module_path:
      discovery['modulePath'] = module_path
    if options.get('version_package'):
      discovery['version_module'] = True
    self._api = api_loader(discovery)
    self._language = language

  @property
  def api(self):
    return self._api

  @property
  def model_module(self):
    return self._api.model_module

  def GeneratePackage(self, package_writer, path_replacements=None,
                      extra_defines=None):
    """Generate the entire package of an API library.

    Overrides superclass.

    Args:
      package_writer: (LibraryPackage) output package
      path_replacements: (dict) dict holding elements which should be replaced
        if found in a path. (See generator.WalkTemplateTree for details.)
      extra_defines: (dict) Dictionary of extra definitions to provide to the
        templates
    """
    api = self._api
    self.AnnotateApiForLanguage(api)

    self._BuildPathReplacements(path_replacements)
    self._top_level_defines = {'api': api.values}
    self._top_level_defines.update(extra_defines or {})

    self._GenerateLibrarySource(api, package_writer)
    if self._options.get('include_dependencies'):
      self.WalkTemplateTree('dependencies', self._path_replacements, {},
                            self._top_level_defines, package_writer)

  def _BuildPathReplacements(self, path_replacements):
    """Build the set of path replacements used for template tree walking.

    Augments a default list of path replacements with a caller provided set.
    Side effects: Sets self._path_replacements.

    Args:
      path_replacements: (dict) caller provided dict holding elements which
        should be replaced if found in a path.
    """
    self._path_replacements = {
        '___package___': self._api.module.path,
        '___package_name___': self._api.module.name,
        '___language___': self._language,
        # language version will in practice be set, because it
        # comes from features, but may not be in some unit tests.
        '___language_version___': self.language_version or '',
        }

    for key, value in self.features.iteritems():
      if isinstance(value, (unicode, str)):
        self._path_replacements['___features_%s___' % key] = value

    if not isinstance(self._api.values['revision'], (unicode, str)):
        # Make sure revision is a string
      self._path_replacements['___api_revision___'] = str(
          self._api.values['revision'])

    for key, value in self._api.values.iteritems():
      if isinstance(value, (unicode, str)):
        self._path_replacements['___api_%s___' % key] = value

    self._path_replacements.update(path_replacements or {})

  def _GenerateLibrarySource(self, api, source_package_writer):
    """Default operations to generate the package.

    Do all the default operations for generating a package.
    1. Walk the template tree to generate the source.
    2. Add in per-language additions to the source
    3. Optionally copy in dependencies
    4. (Side effect) Closes the source_package_writer.

    Args:
      api: (Api) The Api instance we are writing a libary for.
      source_package_writer: (LibraryPackage) source output package.
    """
    list_replacements = {
        '___models_': ['model', api.ModelClasses()],
        '___topLevelModels_': ['model', api.TopLevelModelClasses()],
        }
    self.WalkTemplateTree('templates', self._path_replacements,
                          list_replacements,
                          self._top_level_defines, source_package_writer)
    # Call back to the language specific generator to give it a chance to emit
    # special case elements.
    self.GenerateExtraSourceOutput(source_package_writer)

  def GenerateExtraSourceOutput(self, source_package_writer):
    """Extension point for subclasses to add extra data to the output.

    A language generator may provide an implementation of this to emit elements
    which cannot be handled by GenerateLibraryPackage.

    Args:
      source_package_writer: (LibraryPackage) An output package writer.
    """
    pass

  def AnnotateApiForLanguage(self, the_api):
    """Add the language specific annotations to an api.

    Performs all the language specific annotations on an API so it is ready to
    use for generating a library surface. This is essentially an impedance match
    between what is expressed in the API definition and how a language specific
    binding can be expressed using only templates.

    Args:
      the_api: (Api) The API to annotate.
    """
    the_api.VisitAll(lambda o: o.SetLanguageModel(self.language_model))
    the_api.void_type.SetLanguageModel(self.language_model)
    self._AnnotateTree(the_api)

  def _AnnotateTree(self, api):
    """Decorate the API tree with language model specific elements.

    Walks the tree and calls annotators on the Methods and Properties.  This
    may be used to supply language specific transforms to an API between the
    time the API is loaded and before we generate code through the templates.

    Should be called after the API is constructed and before we generate
    any code.

    Args:
      api: (Api) The Api.
    """
    self.AnnotateApi(api)
    for schema in api.all_schemas.values():
      # TODO(user): remove this after completing the transition away from
      # package in all the templates
      schema.SetTemplateValue('package', self.model_module)
      self.AnnotateSchema(api, schema)
      for prop in schema.values.get('properties', []):
        self.AnnotateProperty(api, prop, schema)
    for resource in api.values['resources']:
      self.AnnotateResource(api, resource)
    for method in api.values['methods']:
      self.AnnotateMethod(api, method, None)

  def AnnotateApi(self, api):
    """Extension point for subclasses to annotate the API node itself.

    A language generator may provide an implementation for this.

    Args:
      api: (Api) The Api.
    """
    api.SetTemplateValue('topLevelModels', api.TopLevelModelClasses())
    for parameter in api.values.get('parameters') or []:
      self.AnnotateParameter(api, parameter)

  def AnnotateMethod(self, unused_api, method, unused_resource):
    """Extension point for subclasses to annotate Resources.

    A language generator may provide an implementation for this.

    Args:
      unused_api: (Api) The Api.
      method: (Method) The Method to annotate.
      unused_resource: (Resource) The Resource which owns this Method.
    """
    for parameter in method.parameters:
      self.AnnotateParameter(method, parameter)

  def AnnotateParameter(self, method, parameter):
    """Extension point for subclasses to annotate method Parameters.

    A language generator may provide an implementation for this.

    Args:
      method: (Method) The Method this parameter belongs to.
      parameter: (Parameter) The Parameter to annotate.
    """
    pass

  def AnnotateProperty(self, api, prop, schema=None):
    """Extension point for subclasses to annotate Properties.

    A language generator may provide an implementation for this.

    Args:
      api: (Api) The Api.
      prop: (Property) The Property to annotate.
      schema: (Schema) The Schema this Property belongs to.
    """
    pass

  def AnnotateResource(self, api, resource):
    """Extension point for subclasses to annotate Resources.

    A language generator may provide an implementation for this. The default
    walks the Resources methods and sub-resources to annotate those.

    Args:
      api: (Api) The Api which owns this resource.
      resource: (Resource) The Resource to annotate.
    """
    for method in resource.values['methods']:
      self.AnnotateMethod(api, method, resource)
    for r in resource.values['resources']:
      self.AnnotateResource(api, r)

  def AnnotateSchema(self, api, schema):
    """Extension point for subclasses to annotate Schemas.

    A language generator may provide an implementation for this.

    Args:
      api: (Api) The Api.
      schema: (Schema) The Schema to annotate
    """
    pass


class NullLibraryGenerator(ApiLibraryGenerator):
  """Used to flag a language that doesn't do library generation."""
  pass
