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
# Copyright 2011 Google Inc. All Rights Reserved.

"""Assorted utility methods for the code generator."""

__author__ = 'aiuto@google.com (Tony Aiuto)'


import re


_WORD_SPLIT_PATTERN = re.compile(r'[\._/-]+')


def CamelCase(s):
  """CamelCase a string so that it is more readable as a variable name.

  Camelcases a string, begining new words after any instances of '.', '_',
      '/', or '-'.

  Args:
    s: (str) A string.
  Returns:
    s, with the first letter of each word capitalized.
  """
  title = lambda x: x[0].upper() + x[1:] if x else x
  return ''.join([title(x) for x in _WORD_SPLIT_PATTERN.split(s)])


def UnCamelCase(phrase, separator='_'):
  """Convert CamelCased phrase into lower-case delimited words.

  Args:
    phrase: CamelCased phrase.
    separator: The word separator to inject between lowercased words.
  Returns:
    lower case phrase with separators between case changes from lower
    to upper or acronyms (all upper) to lower case.
  """
  phrase_len = len(phrase)
  if not phrase_len:
    return ''

  ch = phrase[0]
  text_run = ch.isalnum()
  last_was_separator = ch.isupper() or not text_run
  caps_run = False
  result = ch.lower()

  # already did first index
  for i in range(phrase_len - 1):
    ch = phrase[i + 1]
    if ch.isupper():
      caps_run = text_run and last_was_separator
      text_run = True
      if not last_was_separator:
        result += separator
        last_was_separator = True
    elif not ch.isalnum():
      caps_run = False
      text_run = False
      last_was_separator = True
    else:
      text_run = True
      last_was_separator = False
      if caps_run:
        result += separator
        last_was_separator = True
        caps_run = False

    result += ch.lower()
  return result


def SanitizeDomain(s):
  """Sanitize a domain name to ch aracters suitable for use in code.

  We only want text characters, digits, and '.'. For now, we only allow ASCII,
  characters but we may revisit that in the future if there is demand from
  Endpoints customers.

  Since the pattern 'my-custom-app.appspot.com' is a popular one, preserve the
  '-' in a useful way.

  Args:
    s: (str) A domain name
  Returns:
    (str) A version of the domain name suitable for use in code structures
        like Java package names. None if s is None.
  """
  if s is None:
    return None
  s = s.lower().replace('-', '_')
  return ''.join([c for c in s if c.isalnum() or c in ['.', '_']])


def ReversedDomainComponents(s):
  """Returns a list of domain components in reverse order.

  Args:
    s: (str) A string of the form "a.b.c"
  Returns:
    list(s) E.g. ['c', 'b', 'a']
  """
  if not s:
    return []
  parts = s.split('.')
  parts.reverse()
  return parts


def NoSpaces(s):
  """Remove spaces from a string, but preserves None-ness."""
  if s:
    return s.replace(' ', '')
  return s
