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

"""Support for simple JSON templates.

A JSON template is a dictionary of JSON data in which string values
may be simple templates in string.Template format (i.e.,
$dollarSignEscaping).  By default, the template is expanded against
its own data, optionally updated with additional context.
"""

import json
from string import Template
import sys

__author__ = 'smulloni@google.com (Jacob Smullyan)'


def ExpandJsonTemplate(json_data, extra_context=None, use_self=True):
  """Recursively template-expand a json dict against itself or other context.

  The context for string expansion is the json dict itself by default, updated
  by extra_context, if supplied.

  Args:
    json_data: (dict) A JSON object where string values may be templates.
    extra_context: (dict) Additional context for template expansion.
    use_self: (bool) Whether to expand the template against itself, or only use
        extra_context.

  Returns:
    A dict where string template values have been expanded against
    the context.
  """
  if use_self:
    context = dict(json_data)
  else:
    context = {}
  if extra_context:
    context.update(extra_context)

  def RecursiveExpand(obj):
    if isinstance(obj, list):
      return [RecursiveExpand(x) for x in obj]
    elif isinstance(obj, dict):
      return dict((k, RecursiveExpand(v)) for k, v in obj.iteritems())
    elif isinstance(obj, (str, unicode)):
      return Template(obj).safe_substitute(context)
    else:
      return obj

  return RecursiveExpand(json_data)


if __name__ == '__main__':
  if len(sys.argv) > 1:
    json_in = open(sys.argv[1])
  else:
    json_in = sys.stdin
  data = json.load(json_in)
  expanded = ExpandJsonTemplate(data)
  json.dump(expanded, sys.stdout, indent=2)
