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

"""Create an API definition by interpreting a discovery document.

This module interprets a discovery document to create a tree of classes which
represent the API structure in a way that is useful for generating a library.
For each discovery element (e.g. schemas, resources, methods, ...) there is
a class to represent it which is directly usable in the templates. The
instances of those classes are annotated with extra variables for use
in the template which are language specific.

The current way to make use of this class is to create a programming language
specific subclass of Api, which adds annotations and template variables
appropriate for that language.
TODO(user): Refactor this so that the API can be loaded first, then annotated.
"""

__author__ = 'aiuto@google.com (Tony Aiuto)'

import json
import logging
import operator
import urlparse


from googleapis.codegen import data_types
from googleapis.codegen import template_objects
from googleapis.codegen import utilities
from googleapis.codegen.api_exception import ApiException
from googleapis.codegen.schema import Schema
from googleapis.codegen.utilities import convert_size

_DEFAULT_SERVICE_HOST = 'www.googleapis.com'
_DEFAULT_OWNER_DOMAIN = 'google.com'
_DEFAULT_OWNER_NAME = 'Google'

_RECOGNIZED_GOOGLE_DOMAINS = (
    'google.com',
    'googleapis.com',
    'googleplex.com'
)

# Recognized names of request and response fields used for paging.
_PAGE_TOKEN_NAMES = ('pageToken', 'nextPageToken')

_LOGGER = logging.getLogger('codegen')


class Api(template_objects.CodeObject):
  """An API definition.

  This class holds a discovery centric definition of an API. It contains
  members such as "resources" and "schemas" which relate directly to discovery
  concepts. It defines several properties that can be used in code generation
  templates:
    name: The API name.
    version: The API version.
    versionNoDots: The API version with all '.' characters replaced with '_'.
        This is typically used in class names.
    versionNoDash: The API version with all '-' characters replaced with '_'.
        This is typically used in file names where '-' has meaning.

    authScopes: The list of the OAuth scopes used by this API.
    dataWrapper: True if the API definition contains the 'dataWrapper' feature.
    methods: The list of top level API methods.
    models: The list of API data models, both from the schema section of
      discovery and from anonymous objects defined in method definitions.
    parameters: The list of global method parameters (applicable to all methods)
    resources: The list of API resources
  """

  def __init__(self, discovery_doc, language=None):
    super(Api, self).__init__(discovery_doc, self,
                              wire_name=discovery_doc['name'])
    name = self.values['name']
    self._validator.ValidateApiName(name)
    if name != 'freebase':
      self._validator.ValidateApiVersion(self.values['version'])
    canonical_name = self.values.get('canonicalName') or name
    if not self.values.get('canonicalName'):
      self.values['canonicalName'] = canonical_name
    self._class_name = self.ToClassName(canonical_name, self)
    # Guard against language implementor not taking care of spaces
    self._class_name = self._class_name.replace(' ', '')
    self._NormalizeOwnerInformation()
    self._language = language
    self._template_dir = None
    self._surface_features = {}
    self._schemas = {}
    self._methods_by_name = {}
    self._all_methods = []

    self.SetTemplateValue('className', self._class_name)
    self.SetTemplateValue('versionNoDots',
                          self.values['version'].replace('.', '_'))
    self.SetTemplateValue('versionNoDash',
                          self.values['version'].replace('-', '_'))
    self.SetTemplateValue('dataWrapper',
                          'dataWrapper' in discovery_doc.get('features', []))
    self.values.setdefault('title', name)
    self.values.setdefault('exponentialBackoffDefault', False)
    if not self.values.get('revision'):
      self.values['revision'] = 'snapshot'

    self._NormalizeUrlComponents()

    # Information for variant subtypes, a dictionary of the format:
    #
    #  { 'wireName': {'discriminant': discriminant, 'value': value,
    #                 'schema': schema},
    #    ... }
    #
    # ... where wireName is the name of variant subtypes, discriminant
    # the field name of the discriminant, value the discriminant value
    # for this variant, and schema the base schema.
    #
    # This information cannot be stored in the referred schema at
    # reading time because at the time we read it from the base
    # schema, the referenced variant schemas may not yet be loaded. So
    # we first store it here, and after all schemas have been loaded,
    # update the schema template properties.
    self._variant_info = {}

    # Build data types and methods
    self._SetupModules()
    self.void_type = data_types.Void(self)
    self._BuildSchemaDefinitions()
    self._BuildResourceDefinitions()
    self.SetTemplateValue('resources', self._resources)
    resource_names = []
    for resource in self._resources:
      resource_names.append(resource.GetTemplateValue('className'))
    self.SetTemplateValue('resourceNames', resource_names)

    # Make data models part of the api dictionary
    self.SetTemplateValue('models', self.ModelClasses())

    # Replace methods dict with Methods
    self._top_level_methods = []
    method_dict = self.values.get('methods') or {}
    for name in sorted(method_dict):
      self._top_level_methods.append(Method(self, name, method_dict[name]))
    self.SetTemplateValue('methods', self._top_level_methods)

    # Global parameters
    self._parameters = []
    param_dict = self.values.get('parameters') or {}
    for name in sorted(param_dict):
      parameter = Parameter(self, name, param_dict[name], self)
      self._parameters.append(parameter)
      if name == 'alt':
        self.SetTemplateValue('alt', parameter)
    self.SetTemplateValue('parameters', self._parameters)

    # Auth scopes
    self._authscopes = []
    if (self.values.get('auth') and
        self.values['auth'].get('oauth2') and
        self.values['auth']['oauth2'].get('scopes')):
      for value, auth_dict in sorted(
          self.values['auth']['oauth2']['scopes'].iteritems()):
        self._authscopes.append(AuthScope(self, value, auth_dict))
      self.SetTemplateValue('authscopes', self._authscopes)

  @property
  def all_schemas(self):
    """The dictionary of all the schema objects found in the API."""
    return self._schemas

  def _SetupModules(self):
    """Compute and set the module(s) which this API belongs under."""
    # The containing module is based on the owner information.
    path = self.values.get('modulePath') or self.values.get('packagePath')
    self._containing_module = template_objects.Module(
        package_path=path,
        owner_name=self.values.get('owner'),
        owner_domain=self.values.get('ownerDomain'))
    self.SetTemplateValue('containingModule', self._containing_module)

    # The API is a child of the containing_module
    base = self.values['name']
    # TODO(user): Introduce a breaking change where we always prefer
    # canonicalName.
    if self.values.get('packagePath'):
      # Lowercase the canonical name only for non-cloud-endpoints Google APIs.
      # This is to avoid breaking changes to existing Google-owned Cloud
      # Endpoints APIs.
      if self.values.get('rootUrl').find('.googleapis.com') > 0:
        base = self.values.get('canonicalName').lower() or base
      else:
        base = self.values.get('canonicalName') or base
    if self.values.get('version_module'):
      base = '%s/%s' % (base, self.values['versionNoDots'])
    self._module = template_objects.Module(package_path=base,
                                           parent=self._containing_module)
    self.SetTemplateValue('module', self._module)

    # The default module for data models defined by this API.
    self._model_module = template_objects.Module(package_path=None,
                                                 parent=self._module)

  def _BuildResourceDefinitions(self):
    """Loop over the resources in the discovery doc and build definitions."""
    self._resources = []
    def_dict = self.values.get('resources') or {}
    for name in sorted(def_dict):
      resource = Resource(self, name, def_dict[name], parent=self)
      self._resources.append(resource)

  def _BuildSchemaDefinitions(self):
    """Loop over the schemas in the discovery doc and build definitions."""
    schemas = self.values.get('schemas')
    if schemas:
      for name in sorted(schemas):
        def_dict = schemas[name]
        # Upgrade the string format schema to a dict.
        if isinstance(def_dict, unicode):
          def_dict = json.loads(def_dict)
        self._schemas[name] = self.DataTypeFromJson(def_dict, name)

      # Late bind info for variant types, and mark the discriminant
      # field and value.
      for name, info in self._variant_info.iteritems():
        if name not in self._schemas:
          # The error will be reported elsewhere
          continue
        schema = self._schemas[name]
        for prop in schema.values.get('properties'):
          if prop.values['wireName'] == info['discriminant']:
            # Filter out the discriminant property as it is already
            # contained in the base type.
            schema.SetTemplateValue(
                'properties',
                [p for p in schema.values.get('properties') if p != prop])
            break
        else:
          logging.warn("Variant schema '%s' for base schema '%s' "
                       "has not the expected discriminant property '%s'.",
                       name, info['schema'].values['wireName'],
                       info['discriminant'])
        schema.SetTemplateValue('superClass', info['schema'].class_name)
        # TODO(user): baseType is for backwards compatability only. It should
        # have always been a different name. When the old Java generators roll
        # off, remove it.
        schema.SetTemplateValue('baseType', info['schema'].class_name)
        schema.SetTemplateValue('discriminantValue', info['value'])

  def _NormalizeOwnerInformation(self):
    """Ensure that owner and ownerDomain are set to sane values."""
    owner_domain = self.get('ownerDomain', '')
    if not owner_domain:
      root_url = self.get('rootUrl')
      if root_url:
        owner_domain = urlparse.urlparse(root_url).hostname
        # Normalize google domains.
        if any(owner_domain.endswith(d) for d in _RECOGNIZED_GOOGLE_DOMAINS):
          owner_domain = 'google.com'
    if owner_domain:
      owner_domain = utilities.SanitizeDomain(owner_domain)
    else:
      owner_domain = _DEFAULT_OWNER_DOMAIN

    self.SetTemplateValue('ownerDomain', owner_domain)
    if not self.get('ownerName'):
      if owner_domain == _DEFAULT_OWNER_DOMAIN:
        owner_name = _DEFAULT_OWNER_NAME
      else:
        owner_name = owner_domain.replace('.', '_')
      self.SetTemplateValue('ownerName', owner_name)
    if not self.get('owner'):
      self.SetTemplateValue('owner', self['ownerName'].lower())

  def _NormalizeUrlComponents(self):
    """Sets template values concerning the path to the service.

    Sets rootUrl and servicePath from the values given or defaults based on what
    is available. Verifies them for safeness.  The hierarchy of the possible
    inputs is:
      use rootUrl + servicePath as the best choice if it exists (v1new)
      or rpcPath
      or use baseUrl (v1)
      or use basePath (v1)
      or restBasePath (v0.3)
      or default to 'api/version'

    Raises:
      ValueError: if the values available are inconsistent or disallowed.
    """
    # If both rootUrl and servicePath exist, they equal what is in baseUrl.
    root_url = self.values.get('rootUrl')
    service_path = self.values.get('servicePath')
    rpc_path = self.values.get('rpcPath')
    if root_url:
      # oauth2 has a servicePath of "". This is wierd but OK for that API, but
      # it means we must explicitly check against None.
      if service_path is not None:
        base_url = root_url + service_path
      elif rpc_path:
        base_url = rpc_path
      else:
        raise ValueError('Neither servicePath nor rpcPath is defined.')
    else:
      base_url = self.values.get('baseUrl')

    # If we have a full path ('https://superman.appspot.com/kryptonite/hurts'),
    # then go with that, otherwise just use the various things which might
    # hint at the servicePath.
    best_path = (base_url
                 or self.values.get('basePath')
                 or self.values.get('restBasePath')
                 or '/%s/%s/' % (self.values['name'], self.values['version']))
    if best_path.find('..') >= 0:
      raise ValueError('api path must not contain ".." (%s)' % best_path)
    # And let urlparse to the grunt work of normalizing and parsing.
    url_parts = urlparse.urlparse(best_path)

    scheme = url_parts.scheme or 'https'
    service_host = url_parts.netloc or _DEFAULT_SERVICE_HOST
    base_path = url_parts.path
    if not root_url:
      self._api.SetTemplateValue('rootUrl', '%s://%s/' % (scheme, service_host))
    if service_path is None:
      self._api.SetTemplateValue('servicePath', base_path[1:])

    # Make sure template writers do not revert
    self._api.DeleteTemplateValue('baseUrl')
    self._api.DeleteTemplateValue('basePath')
    self._api.DeleteTemplateValue('serviceHost')

  def ModelClasses(self):
    """Return all the model classes."""
    ret = set(
        s for s in self._schemas.itervalues()
        if isinstance(s, Schema) or isinstance(s, data_types.MapDataType))
    return sorted(ret, key=operator.attrgetter('class_name'))

  def TopLevelModelClasses(self):
    """Return the models which are not children of another model."""
    return [m for m in self.ModelClasses() if not m.parent]

  def DataTypeFromJson(self, type_dict, default_name, parent=None,
                       wire_name=None):
    """Returns a schema object represented by a JSON Schema dictionary.

    Evaluate a JSON schema dictionary and return an appropriate schema object.
    If a data type is defined in-line, then create the schema dynamically. If
    the schema is a $ref to another, return the previously created schema or
    a lazy reference.

    If the type_dict is None, a blank schema will be created.

    Args:
      type_dict: A dict of the form expected of a request or response member
        of a method description.   See the Discovery specification for more.
      default_name: The unique name to give the schema if we have to create it.
      parent: The schema where I was referenced. If we cannot determine that
        this is a top level schema, set the parent to this.
      wire_name: The name which will identify objects of this type in data on
        the wire.
    Returns:
      A Schema object.
    """

    # new or not initialized, create a fresh one
    schema = Schema.Create(self, default_name, type_dict or {}, wire_name,
                           parent)
    # Only put it in our by-name list if it is a real object
    if isinstance(schema, Schema) or isinstance(schema, data_types.MapDataType):
      # Use the path to the schema as a key. This means that an anonymous class
      # for the 'person' property under the schema 'Activity' will have the
      # unique name 'Activity.person', rather than 'ActivityPerson'.
      path = '.'.join(
          [a.values.get('wireName', '<anon>') for a in schema.full_path])
      _LOGGER.debug('DataTypeFromJson: add %s to cache', path)
      self._schemas[path] = schema
    return schema

  def AddMethod(self, method):
    """Add a new method to the set of all methods."""
    self._all_methods.append(method)
    self._methods_by_name[method.values['rpcMethod']] = method

  def MethodByName(self, method_name):
    """Find a method by name.

    Args:
      method_name: (str) the full RPC name of a method defined by this API.

    Returns:
      Method object or None if not found.
    """
    return self._methods_by_name.get(method_name)

  def SchemaByName(self, schema_name):
    """Find a schema by name.

    Args:
      schema_name: (str) name of a schema defined by this API.

    Returns:
      Schema object or None if not found.
    """
    return self._schemas.get(schema_name, None)

  def SetVariantInfo(self, ref, discriminant, value, schema):
    """Sets variant info for the given reference."""
    if ref in self._variant_info:
      logging.warning("Base type of '%s' changed from '%s' to '%s'. "
                      "This is an indication that a variant schema is used "
                      "from multiple base schemas and may result in an "
                      "inconsistent model.",
                      ref, self._base_type[ref].wireName, schema.wireName)
    self._variant_info[ref] = {'discriminant': discriminant, 'value': value,
                               'schema': schema}

  def VisitAll(self, func):
    """Visit all nodes of an API tree and apply a function to each.

    Walks a tree and calls a function on each element of it. This should be
    called after the API is fully loaded.

    Args:
      func: (function) Method to call on each object.
    """
    _LOGGER.debug('Applying function to all nodes')
    func(self._containing_module)
    func(self._module)
    func(self._model_module)
    for resource in self.values['resources']:
      self._VisitResource(resource, func)
    # Top level methods
    for method in self.values['methods']:
      self._VisitMethod(method, func)
    for parameter in self.values['parameters']:
      func(parameter)
      func(parameter.data_type)
    for schema in self._schemas.values():
      self._VisitSchema(schema, func)
    for scope in self.GetTemplateValue('authscopes') or []:
      func(scope)

  def _VisitMethod(self, method, func):
    """Visit a method, calling a function on every child.

    Args:
      method: (Method) The Method to visit.
      func: (function) Method to call on each object.
    """
    func(method)
    for parameter in method.parameters:
      func(parameter)

  def _VisitResource(self, resource, func):
    """Visit a resource tree, calling a function on every child.

    Calls down recursively to sub resources.

    Args:
      resource: (Resource) The Resource to visit.
      func: (function) Method to call on each object.
    """
    func(resource)
    for method in resource.values['methods']:
      self._VisitMethod(method, func)
    for r in resource.values['resources']:
      self._VisitResource(r, func)

  def _VisitSchema(self, schema, func):
    """Visit a schema tree, calling a function on every child.

    Args:
      schema: (Schema) The Schema to visit.
      func: (function) Method to call on each object.
    """
    func(schema)
    func(schema.module)
    for prop in schema.values.get('properties', []):
      func(prop)
    for child in self.children:
      func(child)

  # Do not warn about unused arguments, pylint: disable=unused-argument
  def ToClassName(self, s, element, element_type=None):
    """Convert a name to a suitable class name in the target language.

    This default implementation camel cases the string, which is appropriate
    for some languages.  Subclasses are encouraged to override this.

    Args:
      s: (str) A rosy name of data element.
      element: (object) The object we are making a class name for.
      element_type: (str) Deprecated. The kind of object we are making a class
        name for.  E.g. resource, method, schema.
        TODO(user): replace type in favor of class of element, but that will
        require changing the place where we call ToClassName with no element.
    Returns:
      A name suitable for use as a class in the generator's target language.
    """
    return utilities.CamelCase(s).replace(' ', '')

  def NestedClassNameForProperty(self, name, schema):
    """Returns the class name of an object nested in a property."""
    # TODO(user): This functionality belongs in the language model, but
    # because of the way the api is bootstrapped, that isn't available when we
    # need it.  When language model is available from the start, this should be
    # moved.
    return '%s%s' % (schema.class_name, utilities.CamelCase(name))

  @property
  def class_name(self):
    return self.values['className']

  @property
  def model_module(self):
    return self._model_module

  @property
  def containing_module(self):
    return self._containing_module

  @property
  def all_methods(self):
    """All the methods in the entire API."""
    return self._all_methods

  @property
  def top_level_methods(self):
    """All the methods at the API top level (not in a resource)."""
    return self._top_level_methods


class Resource(template_objects.CodeObject):

  def __init__(self, api, name, def_dict, parent=None):
    """Creates a Resource.

    Args:
      api: (Api) The Api which owns this Resource.
      name: (string) The discovery name of the Resource.
      def_dict: (dict) The discovery dictionary for this Resource.
      parent: (CodeObject) The resource containing this method, if any. Top
         level resources have the API as a parent.
    """
    super(Resource, self).__init__(def_dict, api, parent=parent, wire_name=name)
    self.ValidateName(name)
    class_name = api.ToClassName(name, self, element_type='resource')
    self.SetTemplateValue('className', class_name)
    # Replace methods dict with Methods
    self._methods = []
    self._method_classes = []
    method_dict = self.values.get('methods') or {}
    for name in sorted(method_dict):
      method = Method(api, name, method_dict[name], parent=self)
      requestType = method.values.get('requestType')
      if requestType and requestType.GetTemplateValue('className') and \
         requestType.GetTemplateValue('className') not in self._method_classes:
        self._method_classes.append(requestType.GetTemplateValue('className'))
      response = method.values.get('response')
      responseType = method.values.get('responseType')
      if response and responseType.GetTemplateValue('className') not in self._method_classes:
        self._method_classes.append(responseType.GetTemplateValue('className'))
      self._methods.append(method)
    self._method_classes.sort()
    self.SetTemplateValue('methods', self._methods)
    # Get sub resources
    self._resources = []
    r_def_dict = self.values.get('resources') or {}
    for name in sorted(r_def_dict):
      r = Resource(api, name, r_def_dict[name], parent=self)
      self._resources.append(r)
    self.SetTemplateValue('resources', self._resources)

  @property
  def methods(self):
    return self._methods

  @property
  def methods_dict(self):
    return {method['wireName']: method for method in self._methods}

  @property
  def methodClasses(self):
    return self._method_classes

class AuthScope(template_objects.CodeObject):
  """The definition of an auth scope.

  An AuthScope defines these template values
    value: The scope url
    name: a sanitized version of the value, transformed so it generally can
          be used as an indentifier in code. Deprecated, use constantName
    description: the description of the scope.
  It also provides a template property which can be used after a language
  binding is set.
    constantName: A transformation of the value so it is suitable as a constant
                  name in the specific language.
  """

  GOOGLE_PREFIX = 'https://www.googleapis.com/auth/'
  HTTPS_PREFIX = 'https://'

  def __init__(self, api, value, def_dict):
    """Construct an auth scope.

    Args:
      api: (Api) The Api which owns this Property
      value: (string) The unique identifier of this scope, often a URL
      def_dict: (dict) The discovery dictionary for this auth scope.
    """
    super(AuthScope, self).__init__(def_dict, api, wire_name=value)
    self._module = api.module
    self.SetTemplateValue('value', value)
    while value.endswith('/'):
      value = value[:-1]
    if 'description' not in self.values:
      self.SetTemplateValue('description', value)

    # Strip the common prefix to get a unique identifying name
    if value.startswith(AuthScope.GOOGLE_PREFIX):
      scope_id = value[len(AuthScope.GOOGLE_PREFIX):]
    elif value.startswith(AuthScope.HTTPS_PREFIX):
      # some comon scopes are are just a URL
      scope_id = value[len(AuthScope.HTTPS_PREFIX):]
    else:
      scope_id = value
    # We preserve the value stripped of the most common prefixes so we can
    # use it for building constantName in templates.
    self.SetTemplateValue('lastPart', scope_id)

    # replace all non alphanumeric with '_' to form 'name'
    name = ''.join([(c if c.isalnum() else '_') for c in scope_id.upper()])
    self.SetTemplateValue('name', name)

  @property
  def constantName(self):  # pylint: disable=g-bad-name
    """Overrides default behavior of constantName."""
    return self._language_model.ApplyPolicy('constant', self,
                                            self.values['lastPart'])


class Method(template_objects.CodeObject):
  """The definition of a method."""

  def __init__(self, api, name, def_dict, parent=None):
    """Construct a method.

    Methods in REST discovery are inside of a resource. Note that the method
    name and id are calculable from each other. id will always be equal to
    api_name.resource_name[.sub_resource...].method_name.  At least it should
    be, as that is the transformation Discovery makes from the API definition,
    which is essentially a flat list of methods, into a hierarchy of resources.

    Args:
      api: (Api) The Api which owns this Method.
      name: (string) The discovery name of the Method.
      def_dict: (dict) The discovery dictionary for this Method.
      parent: (CodeObject) The resource containing this Method, if any.

    Raises:
      ApiException: If the httpMethod type is not one we know how to
          handle.
    """
    super(Method, self).__init__(def_dict, api, parent=(parent or api))
    # TODO(user): Fix java templates to name vs. wireName correctly. Then
    # change the __init__ to have wire_name=def_dict.get('id') or name
    # then eliminate this line.
    self.SetTemplateValue('wireName', name)
    self.ValidateName(name)
    class_name = api.ToClassName(name, self, element_type='method')
    if parent and class_name == parent.values['className']:
      # Some languages complain when the collection name is the same as the
      # method name.
      class_name = '%sRequest' % class_name
    # The name is the key of the dict defining use. The id field is what you
    # have to use to call the method via RPC. That is unique, name might not be.
    self.SetTemplateValue('name', name)
    # Fix up very old discovery, which does not have an id.
    if 'id' not in self.values:
      self.values['id'] = name
    self.SetTemplateValue('className', class_name)
    http_method = def_dict.get('httpMethod', 'POST').upper()
    self.SetTemplateValue('httpMethod', http_method)
    self.SetTemplateValue('rpcMethod',
                          def_dict.get('rpcMethod') or def_dict['id'])
    rest_path = def_dict.get('path') or def_dict.get('restPath')
    # TODO(user): if rest_path is not set, raise a good error and fail fast.
    self.SetTemplateValue('restPath', rest_path)

    # Figure out the input and output types and schemas for this method.
    expected_request = self.values.get('request')
    if expected_request:
      # TODO(user): RequestBody is only used if the schema is anonymous.
      # When we go to nested models, this could be a nested class off the
      # Method, making it unique without the silly name.  Same for ResponseBody.
      request_schema = api.DataTypeFromJson(expected_request,
                                            '%sRequestContent' % name,
                                            parent=self)
      self.SetTemplateValue('requestType', request_schema)

    expected_response = def_dict.get('response') or def_dict.get('returns')
    if expected_response:
      response_schema = api.DataTypeFromJson(expected_response,
                                             '%sResponse' % name,
                                             parent=self)
      if self.values['wireName'] == 'get':
        response_schema.values['associatedResource'] = parent
      self.SetTemplateValue('responseType', response_schema)
    else:
      self.SetTemplateValue('responseType', api.void_type)
    # Make sure we can handle this method type and do any fixups.
    if http_method not in ['DELETE', 'GET', 'OPTIONS', 'PATCH', 'POST', 'PUT',
                           'PROPFIND', 'PROPPATCH', 'REPORT']:
      raise ApiException('Unknown HTTP method: %s' % http_method, def_dict)
    if http_method == 'GET':
      self.SetTemplateValue('requestType', None)

    # Replace parameters dict with Parameters.  We try to order them by their
    # position in the request path so that the generated code can track the
    # more human readable definition, rather than the order of the parameters
    # in the discovery doc.
    order = self.values.get('parameterOrder', [])
    req_parameters = []
    opt_parameters = []
    for name, def_dict in self.values.get('parameters', {}).iteritems():
      param = Parameter(api, name, def_dict, self)
      if name == 'alt':
        # Treat the alt parameter differently
        self.SetTemplateValue('alt', param)
        continue

      # Standard params are part of the generic request class
      # We want to push all parameters that aren't declared inside
      # parameterOrder after those that are.
      if param.values['wireName'] in order:
        req_parameters.append(param)
      else:
        # optional parameters are appended in the order they're declared.
        opt_parameters.append(param)
    # pylint: disable=g-long-lambda
    req_parameters.sort(lambda x, y: cmp(order.index(x.values['wireName']),
                                         order.index(y.values['wireName'])))
    # sort optional parameters by name to avoid code churn
    opt_parameters.sort(lambda x, y: cmp(x.values['wireName'], y.values['wireName']))
    req_parameters.extend(opt_parameters)
    self.SetTemplateValue('parameters', req_parameters)

    self._InitMediaUpload(parent)
    self._InitPageable(api)
    api.AddMethod(self)

  def _InitMediaUpload(self, parent):
    media_upload = self.values.get('mediaUpload')
    if media_upload:
      if parent:
        parent.SetTemplateValue('isMedia', True)
      # Get which MIME Media Ranges are accepted for media uploads to this
      # method.
      accepted_mime_ranges = media_upload.get('accept')
      self.SetTemplateValue('accepted_mime_ranges', accepted_mime_ranges)
      max_size = media_upload.get('maxSize')
      self.SetTemplateValue('max_size', max_size)
      self.SetTemplateValue('max_size_bytes',
                            convert_size.ConvertSize(max_size))
      # Find which upload protocols are supported.
      upload_protocols = media_upload['protocols']
      for upload_protocol in upload_protocols:
        self._SetUploadTemplateValues(
            upload_protocol, upload_protocols[upload_protocol])

  def _InitPageable(self, api):
    response_type = self.values.get('responseType')
    if response_type == api.void_type:
      return
    next_page_token_name = self.FindPageToken(
        response_type.values.get('properties'))
    if not next_page_token_name:
      return
    is_page_token_parameter = True
    page_token_name = self.FindPageToken(self.optional_parameters)
    if not page_token_name:
      # page token may be field of request body instead of query parameter
      is_page_token_parameter = False
      request_type = self.values.get('requestType')
      if request_type:
        page_token_name = self.FindPageToken(
            request_type.values.get('properties'))
    if not page_token_name:
      return
    self.SetTemplateValue('isPageable', True)
    self.SetTemplateValue('isPagingStyleStandard',
                          (is_page_token_parameter and
                           page_token_name == 'pageToken' and
                           next_page_token_name == 'nextPageToken'))

  def _SetUploadTemplateValues(self, upload_protocol, protocol_dict):
    """Sets upload specific template values.

    Args:
      upload_protocol: (str) The name of the upload protocol. Eg: 'simple' or
        'resumable'.
      protocol_dict: (dict) The dictionary that corresponds to this upload
        protocol. It typically contains keys like 'path', 'multipart' etc.
    """
    self.SetTemplateValue('%s_upload_supported' % upload_protocol, True)
    upload_path = protocol_dict.get('path')
    if upload_path:
      self.SetTemplateValue('%s_upload_path' % upload_protocol, upload_path)
      self.SetTemplateValue('%s_upload_multipart' % upload_protocol,
                            protocol_dict.get('multipart', False))

  @property
  def media_upload_parameters(self):
    return self.values.get('mediaUpload')

  @property
  def parameters(self):
    return self.values['parameters']

  @property
  def optional_parameters(self):
    return [p for p in self.values['parameters'] if not p.required]

  @property
  def required_parameters(self):
    return [p for p in self.values['parameters'] if p.required]

  @property
  def path_parameters(self):
    return [p for p in self.values['parameters'] if p.location == 'path']

  @property
  def query_parameters(self):
    return [p for p in self.values['parameters'] if p.location == 'query']

  @staticmethod
  def FindCodeObjectWithWireName(things, wire_name):
    """Looks for an element having the given wire_name.

    Args:
      things: (array of DataType) List of parameters or properties to search.
      wire_name: (str) The wireName we are looking to find.

    Returns:
      None or element with the given wire_name.
    """
    if not things: return None
    for e in things:
      if e.values['wireName'] == wire_name: return e
    return None

  @staticmethod
  def FindPageToken(things):
    """Looks for an element with a wireName like a page token.

    Args:
      things: (array of DataType) List of parameters or properties to search.

    Returns:
      None or page token name found.
    """
    for token_name in _PAGE_TOKEN_NAMES:
      if Method.FindCodeObjectWithWireName(things, token_name):
        return token_name
    return None

  #
  # Expose some properties with the naming convention we use in templates
  #
  def optionalParameters(self):  # pylint: disable=g-bad-name
    return self.optional_parameters

  def requiredParameters(self):  # pylint: disable=g-bad-name
    return self.required_parameters

  def pathParameters(self):  # pylint: disable=g-bad-name
    return self.path_parameters

  def queryParameters(self):  # pylint: disable=g-bad-name
    return self.query_parameters


class Parameter(template_objects.CodeObject):
  """The definition of a method parameter."""

  def __init__(self, api, name, def_dict, method):
    super(Parameter, self).__init__(def_dict, api, parent=method,
                                    wire_name=name)
    self.ValidateName(name)
    self.schema = api

    # TODO(user): Deal with dots in names better. What we should do is:
    # For x.y, x.z create a little class X, with members y and z. Then
    # have the constructor method take an X.

    self._repeated = self.values.get('repeated', False)
    self._required = self.values.get('required', False)
    self._location = (self.values.get('location')
                      or self.values.get('restParameterType')
                      or 'query')
    # TODO(user): Why not just use Schema.Create here?
    referenced_schema = self.values.get('$ref')
    if referenced_schema:
      self._data_type = (api.SchemaByName(referenced_schema) or
                         data_types.SchemaReference(referenced_schema, api))
    elif def_dict.get('type') == 'array':
      self._data_type = Schema.Create(api, name, def_dict, name, method)
    elif self.values.get('enum'):
      self._data_type = data_types.Enum(def_dict,
                                        api,
                                        name,
                                        self.values.get('enum'),
                                        self.values.get('enumDescriptions'),
                                        parent=method)
      self.SetTemplateValue('enumType', self._data_type)
    else:
      self._data_type = data_types.PrimitiveDataType(def_dict, api, parent=self)
    if self._repeated:
      self._data_type = data_types.ArrayDataType(name, self._data_type,
                                                 parent=self)

  @property
  def repeated(self):
    return self._repeated

  @property
  def required(self):
    return self._required

  @property
  def location(self):
    return self._location

  @property
  def code_type(self):
    return self._data_type.code_type

  @property
  def data_type(self):
    return self._data_type
