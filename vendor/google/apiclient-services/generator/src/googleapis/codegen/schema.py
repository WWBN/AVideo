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

"""API data models - schemas and their properties.

This module handles the objects created for the "schema" section of an API.
"""

__author__ = 'aiuto@google.com (Tony Aiuto)'

import collections
import logging


from googleapis.codegen import data_types
from googleapis.codegen import template_objects
from googleapis.codegen.api_exception import ApiException


_ADDITIONAL_PROPERTIES = 'additionalProperties'

_LOGGER = logging.getLogger('codegen')


class Schema(data_types.ComplexDataType):
  """The definition of a schema."""

  def __init__(self, api, default_name, def_dict, parent=None):
    """Construct a Schema object from a discovery dictionary.

    Schemas represent data models in the API.

    Args:
      api: (Api) the Api instance owning the Schema
      default_name: (str) the default name of the Schema. If there is an 'id'
        member in the definition, that is used for the name instead.
      def_dict: (dict) a discovery dictionary
      parent: (Schema) The containing schema. To be used to establish unique
        names for anonymous sub-schemas.
    """
    super(Schema, self).__init__(default_name, def_dict, api, parent=parent)

    name = def_dict.get('id', default_name)
    _LOGGER.debug('Schema(%s)', name)

    # Protect against malicious discovery
    template_objects.CodeObject.ValidateName(name)
    self.SetTemplateValue('wireName', name)
    class_name = api.ToClassName(name, self, element_type='schema')
    self.SetTemplateValue('className', class_name)
    self.SetTemplateValue('isSchema', True)
    self.SetTemplateValue('properties', [])
    self._module = (template_objects.Module.ModuleFromDictionary(self.values)
                    or api.model_module)

  @classmethod
  def Create(cls, api, default_name, def_dict, wire_name, parent=None):
    """Construct a Schema or DataType from a discovery dictionary.

    Schemas contain either object declarations, simple type declarations, or
    references to other Schemas.  Object declarations conceptually map to real
    classes.  Simple types will map to a target language built-in type.
    References should effectively be replaced by the referenced Schema.

    Args:
      api: (Api) the Api instance owning the Schema
      default_name: (str) the default name of the Schema. If there is an 'id'
        member in the definition, that is used for the name instead.
      def_dict: (dict) a discovery dictionary
      wire_name: The name which will identify objects of this type in data on
        the wire. The path of wire_names can trace an item back through
        discovery.
      parent: (Schema) The containing schema. To be used to establish nesting
        for anonymous sub-schemas.

    Returns:
      A Schema or DataType.

    Raises:
      ApiException: If the definition dict is not correct.
    """

    schema_id = def_dict.get('id')
    if schema_id:
      name = schema_id
    else:
      name = default_name
    class_name = api.ToClassName(name, None, element_type='schema')

    _LOGGER.debug('Create: %s, parent=%s', name,
                  parent.values.get('wireName', '<anon>') if parent else 'None')

    # Schema objects come in several patterns.
    #
    # 1. Simple objects
    #    { type: object, properties: { "foo": {schema} ... }}
    #
    # 2. Maps of objects
    #    { type: object, additionalProperties: { "foo": {inner_schema} ... }}
    #
    #    What we want is a data type which is Map<string, {inner_schema}>
    #    The schema we create here is essentially a built in type which we
    #    don't want to generate a class for.
    #
    # 3. Arrays of objects
    #    { type: array, items: { inner_schema }}
    #
    #    Same kind of issue as the map, but with List<{inner_schema}>
    #
    # 4. Primitive data types, described by type and format.
    #    { type: string, format: int32 }
    #    { type: string, enum: ["value", ...], enumDescriptions: ["desc", ...]}
    #
    # 5. Refs to another schema.
    #    { $ref: name }
    #
    # 6. Variant schemas
    #    { type: object, variant: { discriminant: "prop", map:
    #             [ { 'type_value': value, '$ref': wireName }, ... ] } }
    #
    #    What we do is map the variant schema to a schema with a single
    #    property for the discriminant. To that property, we attach
    #    the variant map which specifies which discriminator values map
    #    to which schema references. We also collect variant information
    #    in the api so we can later associate discriminator value and
    #    base type with the generated variant subtypes.

    if 'type' in def_dict:
      # The 'type' field of the schema can either be 'array', 'object', or a
      # base json type.
      json_type = def_dict['type']
      if json_type == 'object':

        # Look for variants
        variant = def_dict.get('variant')
        if variant:
          return cls._CreateVariantType(variant, api, name,
                                        def_dict, wire_name, parent)

        # Look for full object definition.  You can have properties or
        # additionalProperties, but it does not  do anything useful to have
        # both.

        # Replace properties dict with Property's
        props = def_dict.get('properties')
        if props:
          # This case 1 from above
          return cls._CreateObjectWithProperties(props, api, name,
                                                 def_dict, wire_name, parent)

        # Look for case 2
        additional_props = def_dict.get(_ADDITIONAL_PROPERTIES)
        if additional_props:
          return cls._CreateMapType(additional_props, api, name, wire_name,
                                    class_name, parent)

        # no properties
        return cls._CreateSchemaWithoutProperties(api, name, def_dict,
                                                  wire_name, parent)

      elif json_type == 'array':
        # Case 3: Look for array definition
        return cls._CreateArrayType(api, def_dict, wire_name, class_name,
                                    schema_id, parent)
      else:
        # Case 4: This must be a basic type.  Create a DataType for it.
        return data_types.CreatePrimitiveDataType(def_dict, api, wire_name,
                                                  parent=parent)

    referenced_schema = def_dict.get('$ref')
    if referenced_schema:
      # Case 5: Reference to another Schema.
      #
      # There are 4 ways you can see '$ref' in discovery.
      # 1. In a property of a schema or a method request/response, pointing
      #    back to a previously defined schema
      # 2. As above, pointing to something not defined yet.
      # 3. In a method request or response or property of a schema pointing to
      #    something undefined.
      #
      # For case 1, the schema will be in the API name to schema map.
      #
      # For case 2, just creating this placeholder here is fine.  When the
      # actual schema is hit in the loop in _BuildSchemaDefinitions, we will
      # replace the entry and DataTypeFromJson will resolve the to the new def.
      #
      # For case 3, we will end up with a dangling reference and fail later.
      schema = api.SchemaByName(referenced_schema)
      # The stored "schema" may not be an instance of Schema, but rather a
      # data_types.PrimitiveDataType, which has no 'wireName' value.
      if schema:
        _LOGGER.debug('Schema.Create: %s => %s',
                      default_name, schema.values.get('wireName', '<unknown>'))
        return schema
      return data_types.SchemaReference(referenced_schema, api)

    raise ApiException('Cannot decode JSON Schema for: %s' % def_dict)

  @classmethod
  def _CreateObjectWithProperties(cls, props, api, name, def_dict,
                                  wire_name, parent):
    properties = []
    schema = cls(api, name, def_dict, parent=parent)
    if wire_name:
      schema.SetTemplateValue('wireName', wire_name)
    for prop_name in sorted(props):
      prop_dict = props[prop_name]
      _LOGGER.debug('  adding prop: %s to %s', prop_name, name)
      properties.append(Property(api, schema, prop_name, prop_dict))
      # Some APIs express etag directly in the response, others don't.
      # Knowing that we have it explicitly makes special case code generation
      # easier
      if prop_name == 'etag':
        schema.SetTemplateValue('hasEtagProperty', True)
    schema.SetTemplateValue('properties', properties)

    # check for @ clashing. E.g. No 'foo' and '@foo' in the same object.
    names = set()
    for p in properties:
      wire_name = p.GetTemplateValue('wireName')
      no_at_sign = wire_name.replace('@', '')
      if no_at_sign in names:
        raise ApiException(
            'Property name clash in schema %s:'
            ' %s conflicts with another property' % (name, wire_name))
      names.add(no_at_sign)

    return schema

  @classmethod
  def _CreateVariantType(cls, variant, api, name, def_dict,
                         wire_name, parent):
    """Creates a variant type."""
    variants = collections.OrderedDict()
    schema = cls(api, name, def_dict, parent=parent)
    if wire_name:
      schema.SetTemplateValue('wireName', wire_name)
    discriminant = variant['discriminant']

    # Walk over variants building the variant map and register
    # variant info on the api.
    for variant_entry in variant['map']:
      discriminant_value = variant_entry['type_value']
      variant_schema = api.DataTypeFromJson(variant_entry, name, parent=parent)
      variants[discriminant_value] = variant_schema
      # Set variant info. We get the original wire name from the JSON properties
      # via '$ref' it is not currently accessible via variant_schema.
      api.SetVariantInfo(variant_entry.get('$ref'), discriminant,
                         discriminant_value, schema)

    prop = Property(api, schema, discriminant, {'type': 'string'},
                    key_for_variants=variants)
    schema.SetTemplateValue('is_variant_base', True)
    schema.SetTemplateValue('discriminant', prop)
    schema.SetTemplateValue('properties', [prop])
    return schema

  @classmethod
  def _CreateMapType(cls, additional_props, api, name, wire_name,
                     class_name, parent):
    _LOGGER.debug('Have only additionalProps for %s, dict=%s',
                  name, additional_props)
    # TODO(user): Remove this hack at the next large breaking change
    # The "Items" added to the end is unneeded and ugly. This is for
    # temporary backwards compatibility.  Same for _CreateArrayType().
    if additional_props.get('type') == 'array':
      name = '%sItem' % name
    subtype_name = additional_props.get('id', name + 'Element')
    # Note, since this is an interim, non class just to hold the map
    # make the parent schema the parent passed in, not myself.
    _LOGGER.debug('name:%s, wire_name:%s, subtype name %s', name, wire_name,
                  subtype_name)
    # When there is a parent, we synthesize a wirename when none exists.
    # Purpose is to avoid generating an extremely long class name, since we
    # don't do so for other nested classes.
    if parent and wire_name:
      base_wire_name = wire_name + 'Element'
    else:
      base_wire_name = None

    base_type = api.DataTypeFromJson(
        additional_props, subtype_name, parent=parent,
        wire_name=base_wire_name)
    map_type = data_types.MapDataType(name, base_type, parent=parent,
                                      wire_name=wire_name)
    map_type.SetTemplateValue('className', class_name)
    _LOGGER.debug('  %s is MapOf<string, %s>',
                  class_name, base_type.class_name)
    return map_type

  @classmethod
  def _CreateSchemaWithoutProperties(cls, api, name, def_dict, wire_name,
                                     parent):
    if parent:
      try:
        pname = parent['id']
      except KeyError:
        pname = '<unknown>'
      name_to_log = '%s.%s' % (pname, name)
    else:
      name_to_log = name
    # logging.warning('object without properties %s: %s',
    #                 name_to_log, def_dict)
    schema = cls(api, name, def_dict, parent=parent)
    if wire_name:
      schema.SetTemplateValue('wireName', wire_name)
    return schema

  @classmethod
  def _CreateArrayType(cls, api, def_dict, wire_name,
                       class_name, schema_id, parent):
    items = def_dict.get('items')
    if not items:
      raise ApiException('array without items in: %s' % def_dict)
    tentative_class_name = class_name
    # TODO(user): We should not rename things items.
    # if we have an anonymous type within a map or array, it should be
    # called 'Item', and let the namespacing sort it out.
    if schema_id:
      _LOGGER.debug('Top level schema %s is an array', class_name)
      tentative_class_name += 'Items'
    base_type = api.DataTypeFromJson(items, tentative_class_name,
                                     parent=parent, wire_name=wire_name)
    _LOGGER.debug('  %s is ArrayOf<%s>', class_name, base_type.class_name)
    array_type = data_types.ArrayDataType(tentative_class_name, base_type,
                                          wire_name=wire_name,
                                          parent=parent)
    if schema_id:
      array_type.SetTemplateValue('className', schema_id)
    return array_type

  @property
  def class_name(self):
    return self.values['className']

  @property
  def anonymous(self):
    return 'id' not in self.raw

  @property
  def properties(self):
    return self.values['properties']

  @property
  def isContainerWrapper(self):
    """Is this schema just a simple wrapper around another container.

    A schema is just a wrapper for another datatype if it is an object that
    contains just a single container datatype and (optionally) a kind and
    etag field. This may be used by language generators to create iterators
    directly on the schema. E.g. You could have
        SeriesList ret = api.GetSomeSeriesMethod(args).Execute();
        for (series in ret) { ... }
    rather than
        for (series in ret->items) { ... }

    Returns:
      None or ContainerDataType
    """
    return self._GetPropertyWhichWeWrap() is not None

  @property
  def containerProperty(self):
    """If isContainerWrapper, returns the propery which holds the container."""
    return self._GetPropertyWhichWeWrap()

  def _GetPropertyWhichWeWrap(self):
    """Returns the property which is the type we are wrapping."""
    container_property = None
    for p in self.values['properties']:
      if p.values['wireName'] == 'kind' or p.values['wireName'] == 'etag':
        continue
      if p.data_type.GetTemplateValue('isContainer'):
        if container_property:
          return None
        container_property = p
      else:
        return None
    return container_property

  def __str__(self):
    return '<%s Schema {%s}>' % (self.values['wireName'], self.values)


class Property(template_objects.CodeObject):
  """The definition of a schema property.

  Example property in the discovery schema:
      "id": {"type": "string"}
  """

  def __init__(self, api, schema, name, def_dict, key_for_variants=None):
    """Construct a Property.

    A Property requires several elements in its template value dictionary which
    are set here:
      wireName: the string which labels this Property in the JSON serialization.
      dataType: the DataType of this property.

    Args:
      api: (Api) The Api which owns this Property
      schema: (Schema) the schema this Property is part of
      name: (string) the name for this Property
      def_dict: (dict) the JSON schema dictionary
      key_for_variants: (dict) if given, maps discriminator values to
                        variant schemas.

    Raises:
      ApiException: If we have an array type without object definitions.
    """
    super(Property, self).__init__(def_dict, api, wire_name=name)
    self.ValidateName(name)
    self.schema = schema
    self._key_for_variants = key_for_variants

    # TODO(user): find a better way to mark a schema as an array type
    # so we can display schemas like BlogList in method responses
    try:
      if self.values['wireName'] == 'items' and self.values['type'] == 'array':
        self.schema.values['isList'] = True
    except KeyError:
      pass
    # If the schema value for this property defines a new object directly,
    # rather than refering to another schema, we will have to create a class
    # name for it.   We create a unique name by prepending the schema we are
    # in to the object name.
    tentative_class_name = api.NestedClassNameForProperty(name, schema)
    self._data_type = api.DataTypeFromJson(def_dict, tentative_class_name,
                                           parent=schema, wire_name=name)

  @property
  def code_type(self):
    if self._language_model:
      self._data_type.SetLanguageModel(self._language_model)
    return self._data_type.code_type

  @property
  def safe_code_type(self):
    if self._language_model:
      self._data_type.SetLanguageModel(self._language_model)
    return self._data_type.safe_code_type

  @property
  def primitive_data_type(self):
    if self._language_model:
      self._data_type.SetLanguageModel(self._language_model)
    return self._data_type.primitive_data_type

  @property
  def data_type(self):
    return self._data_type

  @property
  def member_name_is_json_name(self):
    return self.memberName == self.values['wireName']

  @property
  def is_variant_key(self):
    return self._key_for_variants

  @property
  def variant_map(self):
    return self._key_for_variants
