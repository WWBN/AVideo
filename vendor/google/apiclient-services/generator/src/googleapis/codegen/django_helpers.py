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

"""Wrapper methods to insulate us from Django nuances.

Provide Django setup and some utility methods.
"""

__author__ = 'aiuto@google.com (Tony Aiuto)'

import os



from django import template as django_template
from django import utils as django_utils
from django.conf import settings
# COV_NF_START
try:
  # In AppEngine, we have to call use_library() in our main. Doing that causes
  # an error, which we can safely ignore because use_library did it
  settings.configure()
  os.environ['DJANGO_SETTINGS_MODULE'] = 'settings'
except RuntimeError:
  pass

from googleapis.codegen import template_helpers
from googleapis.codegen.filesys import files


# COV_NF_END

# This is Django magic to add builtin tags and filters.  They don't really
# support that use case.  Instead you are supposed to put a package of filters
# in a specific place and the Django web server finds them for you. We are a
# standalone app, not running in their context, so we have to go under the hood
# a little.
django_template.base.add_to_builtins(
    'googleapis.codegen.template_helpers')


def DjangoRenderTemplate(template_path, context_dict):
  """Renders a template specified by a file path with a give values dict.

  Args:
    template_path: (str) Path to file.
    context_dict: (dict) The dictionary to use for template evaluation.
  Returns:
    (str) The expanded template.
  """

  source = files.GetFileContents(template_path).decode('utf-8')
  return _DjangoRenderTemplateSource(source, context_dict)


def _DjangoRenderTemplateSource(template_source, context_dict):
  """Renders the given template source with the given values dict.

  Args:
    template_source: (str) The source of a django template.
    context_dict: (dict) The dictionary to use for template evaluation.
  Returns:
    (str) The expanded template.
  """
  t = django_template.Template(template_source)
  ctxt = django_template.Context(context_dict)
  with template_helpers.SetCurrentContext(ctxt):
    return t.render(ctxt)


def MarkSafe(s):
  return django_utils.safestring.mark_safe(s)
