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

"""Common generator index utilities."""

__author__ = 'akesling@google.com (Alex Kesling)'

from googleapis.codegen import php_generator

# Multiple generators per language are possible, as is the case with
# Java below. Template trees can specify a specific generator in their
# features.json file (with the "generator" attribute); this will refer
# to a key in these dictionaries.  If a template tree does not
# include this specification, the language name is used as a key.
_GENERATORS_BY_LANGUAGE = {
  'php': php_generator.PHPGenerator,
}


def GetGeneratorByLanguage(language_or_generator):
  """Return the appropriate generator for this language.

  Args:
    language_or_generator: (str) the language for which to return a generator,
        or the name of a specific generator.

  Raises:
    ValueError: If provided language isn't supported.

  Returns:
    The appropriate code generator object (which may be None).
  """

  try:
    return _GENERATORS_BY_LANGUAGE[language_or_generator]
  except KeyError:
    raise ValueError('Unsupported language: %s' % language_or_generator)


def SupportedLanguages():
  """Return the list of languages we support.

  Returns:
    list(str)
  """
  return sorted(_GENERATORS_BY_LANGUAGE)
