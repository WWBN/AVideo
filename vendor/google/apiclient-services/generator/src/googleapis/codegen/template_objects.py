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

"""Base classes for objects which are usable in templates.

This module contains the base classes for objects which can be used directly
in template expansion.
"""

__author__ = 'aiuto@google.com (Tony Aiuto)'

import copy

from googleapis.codegen import utilities
from googleapis.codegen.django_helpers import MarkSafe
from googleapis.codegen.utilities import html_stripper
from googleapis.codegen.utilities import name_validator


class UseableInTemplates(object):
  """Base class for any object usable in templates.

  The important feature is that they function as dicts, so that their properties
  can be referenced from django templates.
  """

  def __init__(self, def_dict):
    """Construct a UseableInTemplates object.

    Args:
      def_dict: The discovery dictionary for this element. All the values in it
          are exposed to the template expander.
    """
    # TODO(user): Do we really need both of these?  Can def_dict
    # simply be a deep copy? Or can we store mutations separately and
    # thus not change the underlying dictionary?
    self._def_dict = dict(def_dict)
    self._raw_def_dict = dict(copy.deepcopy(def_dict))

  def __getitem__(self, key):
    """Overrides default __getitem__ to return values from the original dict."""
    return self._def_dict[key]

  def GetTemplateValue(self, name):
    """Get the value for a name which might appear in a template.

    Args:
      name: (str) name of the value.
    Returns:
      object or None if not found.
    """
    return self._def_dict.get(name, None)

  # pylint: disable=unused-argument
  def SetTemplateValue(self, name, value, meaning=None):
    """Adds a name/value pair to the template."""
    self._def_dict[name] = value
    # TODO(user): call something like docmaker.add(
    #    self.__class__.__name__, name, meaning)

  def DeleteTemplateValue(self, name):
    """Delete a value from the object."""
    if name in self._def_dict:
      del self._def_dict[name]

  @property
  def values(self):
    """Return the underlying name/value pair dictionary."""
    return self._def_dict

  @property
  def raw(self):
    return self._raw_def_dict

  def get(self, key, default=None):  # pylint:disable=g-bad-name
    return self._def_dict.get(key, default)


class CodeObject(UseableInTemplates):
  """Template objects which represents an element that might be in code.

  This is the base class for things which might be code elements, such as
  classes, variables and methods.
  """

  _validator = name_validator

  def __init__(self, def_dict, api, parent=None, wire_name=None,
               language_model=None):
    """Construct a CodeObject.

    Args:
      def_dict: (dict) The discovery dictionary for this element.
      api: (Api) The Api instance which owns this element.
      parent: (CodeObject) The parent of this element.
      wire_name: (str) The name of this object as it appears in the protocol.
      language_model: (LanguageModel) The language we are targetting.
        Dynamically defaults to the parent's language model.
    """
    super(CodeObject, self).__init__(def_dict)
    self._api = api
    self._children = []
    self._parent = None
    self._language_model = language_model
    self._module = None
    if wire_name:
      self.SetTemplateValue(
          'wireName',
          wire_name,
          meaning='The name of this object as it appears in the data stream.')
    self.SetParent(parent)
    # Sanitize the 'description'. It is a block of user written text we want to
    # emit whenever possible.
    d = def_dict.get('description')
    if d:
      self.SetTemplateValue('description',
                            self.ValidateAndSanitizeComment(self.StripHTML(d)))

  @classmethod
  def ValidateName(cls, name):
    """Validate that the name is safe to use in generated code."""
    cls._validator.Validate(name)

  @classmethod
  def ValidateAndSanitizeComment(cls, comment):
    """Remove unsafe constructions from a string and make it safe in templates.

    Make sure a string intended as a comment has only safe constructs in it and
    then make it as safe to expand directly in a template. If it fails the test,
    return an empty string.

    Args:
      comment: (str) A string which is expected to be a documentation comment.

    Returns:
      (str) The comment with HTML-unsafe constructions removed.
    """
    return MarkSafe(cls._validator.ValidateAndSanitizeComment(comment))

  @staticmethod
  def StripHTML(input_string):
    """Strip HTML from a string."""
    stripper = html_stripper.HTMLStripper()
    stripper.feed(input_string)
    return stripper.GetFedData()

  @property
  def api(self):
    return self._api

  @property
  def children(self):
    return self._children

  @property
  def parent(self):
    return self._parent

  @property
  def module(self):
    """Returns the module this object would belong in.

    Walks up the ancesters _module is undefined for self. If a module can not
    be found, raises an error, since this indicates either a problem building
    the API model or in writing a template. Failing silently does not help
    debug that.

    Returns:
      Module

    Raises:
      ValueError: If the module can not be determined.
    """
    if self._module:
      return self._module
    if self.parent:
      return self.parent.module
    if self.api:
      return self.api.module
    raise ValueError('Asked for module of CodeObject without any: %s, %s' %
                     (self.values.get('wireName', '<unnamed>'), self))

  @property
  def codeName(self):  # pylint: disable=g-bad-name
    """Returns a language appropriate name for this object.

    This property should only be used during template expansion. It is computed
    once, using the LanguageModel in play, and then that value is cached.

    Returns:
      (str) a name for an instance of this object.
    """
    # Note that if code name is in self._def_dict (and hence GetTemplateValue
    # returns something) then this property won't be called during template
    # expansion at all.  Therefore, the change this makes -- marking codeName
    # safe -- may never take place at all.
    code_name = self.GetTemplateValue('codeName')
    if not code_name:
      code_name = self.values['wireName']
      if self.language_model:
        code_name = self.language_model.ToMemberName(code_name, self._api)
    code_name = MarkSafe(code_name)
    self.SetTemplateValue('codeName', code_name)
    return code_name

  @property
  def fullClassName(self):  # pylint: disable=g-bad-name
    """Returns the fully qualified class name for this object.

    This property can only be used during template expansion.  Walks up the
    parent chain building a fully qualified class name. If the object is in a
    module, include the module name.

    Returns:
      (str) The class name of this object.
    """
    module = self.module
    if module:
      class_name_delimiter = self.language_model.class_name_delimiter
      return module.name + class_name_delimiter + self.RelativeClassName(None)
    return MarkSafe(self.RelativeClassName(None))

  @property
  def packageRelativeClassName(self):  # pylint: disable=g-bad-name
    """Returns the class name for this object relative to its package.

    Walks up the parent chain building a fully qualified class name.

    Returns:
      (str) The class name of this object.
    """
    return MarkSafe(self.RelativeClassName(None))

  def RelativeClassName(self, other):
    """Returns the class name for this object relative to another.

    This property can only be used during template expansion.

    Args:
      other: (CodeObject) Another code object which might be a parent.
    Returns:
      (str) The class name of this object relative to another.
    """
    if self == other:
      return ''
    full_name = ''
    if self.parent:
      full_name = self.parent.RelativeClassName(other)
    if full_name:
      language_model = self.language_model
      if language_model:
        class_name_delimiter = language_model.class_name_delimiter
      full_name += class_name_delimiter
    full_name += (self.values.get('className')
                  or self.values.get('codeName')
                  or self.values.get('name', ''))
    return full_name

  @property
  def parentPath(self):  # pylint: disable=g-bad-name
    """Returns the classNames from my ultimate parent to my immediate parent.

    Walks up the parent chain building a list of ancestors.

    TODO(user): Eliminate this routine by adding template tags which
    let me process the ancestor list directly.

    Returns:
      (list) The class name of this object.
    """
    parent_list = self.ancestors
    return [p.values.get('className') for p in parent_list]

  @property
  def ancestors(self):
    """Return the objects from my ultimate parent down to my immediate parent.

    Returns:
      (list) list of CodeObjects.
    """
    if self.parent:
      return self.parent.ancestors + [self.parent]
    return []

  @property
  def full_path(self):
    """Return the objects from my ultimate parent down to me.

    Returns:
      (list) list of CodeObjects.
    """
    return (self.ancestors or []) + [self]

  def FindTopParent(self):
    if self.parent:
      return self.parent.FindTopParent()
    return self

  def SetLanguageModel(self, language_model):
    """Changes the language model of this code object."""
    self._language_model = language_model

  def SetParent(self, parent):
    """Changes the parent of this code object.

    Args:
      parent: (CodeObject) the new parent.
    """
    if self._parent:
      self._parent.children.remove(self)
    self._parent = parent
    if self._parent:
      self._parent.children.append(self)

  @property
  def language_model(self):
    """Returns the nearest LanguageModel by walking my parents.

    Memoizes the computation, by setting self._language_model after the first
    parent lookup.

    Returns:
      (LanguageModel) A LanguageModel
    """
    if self._language_model:
      return self._language_model
    if self._parent:
      self._language_model = self._parent.language_model
    return self._language_model

  @property
  def codeType(self):  # pylint: disable=g-bad-name
    """Accessor for codeType for use in templates.

    If the template value for codeType was explicitly set, return that,
    otherwise use the code_type member. This is only safe to call for code
    objects which implement code_type.

    Returns:
      (str) the value for codeType
    """
    return MarkSafe(self.GetTemplateValue('codeType') or self.code_type)

  @property
  def safeCodeType(self):  # pylint: disable=g-bad-name
    """Expose this in template using the template naming convention.

    Just redirect to safe_code_type.

    Returns:
      (str) The evaluated code type.
    """
    return MarkSafe(self.safe_code_type)

  @property
  def constantName(self):  # pylint: disable=g-bad-name
    """Returns a name for this object when used as an constant."""
    return self.language_model.ApplyPolicy('constant', self,
                                           self.values['wireName'])

  @property
  def memberName(self):  # pylint: disable=g-bad-name
    """Returns a name for this object when used as an class member."""
    return self.language_model.ApplyPolicy('member', self,
                                           self.values['wireName'])

  @property
  def getterName(self):  # pylint: disable=g-bad-name
    """Returns a name for the getter of memberName."""
    return self.language_model.ApplyPolicy('getter', self,
                                           self.values['wireName'])

  @property
  def setterName(self):  # pylint: disable=g-bad-name
    """Returns a name for the setter of memberName."""
    return self.language_model.ApplyPolicy('setter', self,
                                           self.values['wireName'])

  @property
  def hasName(self):  # pylint: disable=g-bad-name
    """Returns a name for the has check of memberName."""
    return self.language_model.ApplyPolicy('has', self, self.values['wireName'])

  @property
  def unsetName(self):  # pylint: disable=g-bad-name
    """Returns a name for the unset method of memberName."""
    return self.language_model.ApplyPolicy('unset', self,
                                           self.values['wireName'])

  @property
  def parameterName(self):  # pylint: disable=g-bad-name
    """Returns a name for this object when used as the parameter to a method."""
    return self.language_model.ApplyPolicy('parameter_name', self,
                                           self.values['wireName'])


class Module(CodeObject):
  """A code object which represents the concept of a module.

  A Module has two properties available for use in templates:
    name: The full name of this module, including the parent of this Module.
    path: The file path where this module would be stored in a full generated
          code layout. Since the templates can not open files for writing, this
          is intended for use inside documentation.

  These values are derived from elements defining the owner of the API or
  shared data type as described in http://goto/apiarylibrarynamespacing

  Typically, a code generator will create a model (e.g. an Api) and assign a
  a Module to the top node. Other nodes in the model might be in different
  modules, which can be created as children of the top Module. E.g.
    api = LoadApi(....)
    top_module = Module(... api owner information ...)
    api_module = Module(api.name, parent=top_module)
    api._module = api_module
    model_module = Module('model', parent=api_module)
    for s in api.schemas:
      s._module = model_module

  Shared data types contain information that specify the module they belong to,
  which may be different from the module for the API itself.

  Modules are mutable up until the first time the name or path properties
  are evaluated.
  """

  def __init__(self, package_path=None, owner_name=None, owner_domain=None,
               parent=None, language_model=None):
    """Construct a Module.

    Args:
      package_path: (str) A '/' delimited path to this module.
      owner_name: (str) The name of the owner of the API, as they would like it
        to appear in library code. E.g "Best Buy"
      owner_domain: (str) The domain of the owner of the API, as they would like
        it to appear in library code.
      parent: (CodeObject) The parent of this element.
      language_model: (LanguageModel) The language we are targetting.
        Dynamically defaults to the parent's language model.
    """
    super(Module, self).__init__({}, None,
                                 parent=parent,
                                 language_model=language_model)
    self._package_path = utilities.NoSpaces(package_path)
    self._owner_name = utilities.NoSpaces(owner_name)
    self._owner_domain = utilities.SanitizeDomain(owner_domain)
    self._name = None  # will be memoized on first call to name property

  @classmethod
  def ModuleFromDictionary(cls, def_dict):
    """Returns a Module corresponding the library_definition of an object.

    If there is a 'library_definition' section in the given dictionary, use it
    to construct a Module from that information. Return None if there is no
    definition.

    Args:
      def_dict: (dict) Discovery style object definition.
    Returns:
      Module or None.
    """
    lib_def = def_dict.get('library_definition')
    if not lib_def:
      return None
    # Newer style uses modulePath, but some paths through Discovery may use
    # packagePath instead.
    return Module(package_path=(lib_def.get('modulePath')
                                or lib_def.get('packagePath')
                                or ''),
                  owner_name=lib_def.get('owner'),
                  owner_domain=lib_def.get('domain'))

  def SetPath(self, path):
    """Changes the path for this module.

    May be called up until the first time we ask for the module name. This
    restriction is to detect a class of coding errors which could occur if we
    incorrectly share the Module across types of different parentage.

    Args:
      path: (str) Path for this module ('/' delimited).

    Raises:
      ValueError: if called after the name or path properties have been evaled.
    """
    if self._name:
      raise ValueError('SetPath called after first use of name property')
    self._package_path = path

  @property
  def owner_domain(self):
    return self._owner_domain

  @property
  def owner_name(self):
    return self._owner_name

  @property
  def package_path(self):
    return self._package_path

  @property
  def name(self):
    """Returns the language appropriate name for a module."""
    if not self._name:
      self._name = self.path.replace(
          '/', self.language_model.module_name_delimiter)
    return self._name

  @property
  def path(self):
    """Returns the full / delimited file path for this package."""
    if self.parent:
      base_path = self.parent.path
    else:
      base_path = self.language_model.DefaultContainerPathForOwner(self)
    return '/'.join(x for x in (base_path, self._package_path) if x)


class Constant(CodeObject):
  """A code object which represents a constant value.

  Constants have a value and, optionally, a name and description.  The name of
  a constant is the identifier we would use in a program..  We typically use
  constants to represent the possible values of an Enum data type.
  """

  def __init__(self, value, name=None, description=None,
               parent=None, language_model=None):
    """Construct a Module.

    Args:
      value: (str|int) The string value of the constant.
      name: (str) The name for this value. If not specified, the value will be
        used as the base for the name, but numbers will be prefixed with the
        string "value_" to turn them into a valid identifier.
      description: (str) A description of the meaning of this constant.
      parent: (CodeObject) The parent of this element.
      language_model: (LanguageModel) The language we are targetting.
        Dynamically defaults to the parent's language model.
    """
    super(Constant, self).__init__({}, None, parent=parent,
                                   language_model=language_model)
    self._value = str(value)
    self.SetTemplateValue('wireName', self._value)
    if description:
      self._description = self.ValidateAndSanitizeComment(
          self.StripHTML(description))
    else:
      self._description = None
    self._name = name

  @property
  def description(self):
    return self._description

  @property
  def name(self):
    if not self._name:
      # No name, we have to make one.
      self._name = self._NameFromValue(self.value)
    return self._name

  @property
  def value(self):
    return self._value

  @property
  def constantName(self):  # pylint: disable=g-bad-name
    """Override."""
    return self.language_model.ApplyPolicy('constant', self, self.name)

  @classmethod
  def _NameFromValue(cls, value):
    """Construct a safe name for a constant from a value.

    Constants might be numbers or strings with symbols that cannot be used in
    identifiers. We want to do the minimal transform we can to make a name
    that could be turned into an identifier.

    Args:
      value: (str) The value to derive a name from.

    Returns:
      (str): A name which could
    """
    # Many string constants begin with punctuation or symbols like '@'. Remove
    # those.
    name = value
    while not name[0].isalnum():
      name = name[1:]
      if not name:
        # we ran out? Just revert
        name = value
        break
    # If we are left with a number, we have to turn it into an alphanumeric.
    if not name[0].isalpha():
      name = 'value_' + name
    return name
