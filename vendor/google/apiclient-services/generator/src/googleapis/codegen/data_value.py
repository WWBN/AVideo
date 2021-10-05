#!/usr/bin/python2.7
# Copyright 2012 Google Inc. All Rights Reserved.
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

"""Objects for holding data values which can be expanded in templates.

This module contains the base classes for objects which can hold data values
(scaler constants, arrays, ...) that might be expanded in a template. It is
primarily used for CSP.
"""

from googleapis.codegen import template_objects


class DataValue(template_objects.CodeObject):
  """Provide a reasonable value wrapper for converting types to strings."""

  def __init__(self, value, val_type):
    # Because val_type could be a schema and DataObject tries to deepcopy
    # the definition dictionary when schemas store CodeObjects in their
    # _def_dicts. Sidestep this part.
    super(DataValue, self).__init__(
        {}, api=val_type.api, parent=val_type.parent)
    self._def_dict = val_type.values

    # Type may be passed in wrapped in a Property/Parameter object...
    if hasattr(val_type, 'data_type'):
      data_type = val_type.data_type
    else:
      data_type = val_type

    # Type may be a reference, we want the real thing...
    # TODO(user): 20130227
    # If you need this trickery here, we are doing something wrong in the
    # overall structure.
    data_type = getattr(data_type, 'referenced_schema', data_type)

    self._value = value
    self._data_type = data_type
    self._metadata = {}

  def SetValue(self, value):
    self._value = value

  def SetLanguageModel(self, language_model):
    super(DataValue, self).SetLanguageModel(language_model)
    self.data_type.SetLanguageModel(language_model)

  def GetLanguageModel(self):
    # pylint: disable=protected-access
    return self.data_type.language_model

  @property
  def value(self):
    return self._value

  @property
  def data_type(self):
    return self._data_type

  @property
  def code_type(self):
    # Certain data types return a code_type dependent on language model...
    # so we assure that our data_type uses the correct one for safety.
    if self._language_model:
      self._data_type.SetLanguageModel(self._language_model)
    return self._data_type.code_type

  @property
  def metadata(self):
    return self._metadata
