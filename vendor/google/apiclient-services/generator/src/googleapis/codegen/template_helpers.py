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

"""Custom template tags and filters for Google APIs code generator.

These are Django template filters for reformatting blocks of code.
"""

__author__ = 'aiuto@google.com (Tony Aiuto)'

import contextlib
import hashlib
import logging
import os
import re
import string
import textwrap
import threading


import django.template as django_template  # pylint: disable=g-bad-import-order

from googleapis.codegen import utilities
from googleapis.codegen.filesys import files


register = django_template.Library()

# NOTE: Do not edit this text unless you understand the ramifications.
_LICENSE_TEXT = """
Licensed under the Apache License, Version 2.0 (the "License"); you may
not use this file except in compliance with the License. You may obtain
a copy of the License at

http://www.apache.org/licenses/LICENSE-2.0

Unless required by applicable law or agreed to in writing, software
distributed under the License is distributed on an "AS IS" BASIS, WITHOUT
WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied. See the
License for the specific language governing permissions and limitations
under the License.
"""

# Names of the parameters used for formatting generated code.  These are keys
# to the actual values, which are stored in the generation context.
_LANGUAGE = '_LANGUAGE'
_LINE_BREAK_INDENT = '_LINE_BREAK_INDENT'
_LINE_WIDTH = '_LINE_WIDTH'
_PARAMETER_INDENT = '_PARAMETER_INDENT'
_LEVEL_INDENT = '_LEVEL_INDENT'
_COMMENT_START = '_COMMENT_START'
_COMMENT_CONTINUE = '_COMMENT_CONTINUE'
_COMMENT_END = '_COMMENT_END'
_DOC_COMMENT_START = '_DOC_COMMENT_START'
_DOC_COMMENT_CONTINUE = '_DOC_COMMENT_CONTINUE'
_DOC_COMMENT_END = '_DOC_COMMENT_END'
# The begin/end tags are parts of a doc comment that surround the text, but
# are not really part of the comment tags
_DOC_COMMENT_BEGIN_TAG = '_DOC_COMMENT_BEGIN_TAG'
_DOC_COMMENT_END_TAG = '_DOC_COMMENT_END_TAG'

_LITERAL_QUOTE_START = '_LITERAL_QUOTE_START'
_LITERAL_QUOTE_END = '_LITERAL_QUOTE_END'
_LITERAL_ESCAPE = '_LITERAL_ESCAPE'
_LITERAL_FLOAT_SUFFIX = '_LITERAL_FLOAT_SUFFIX'

_CURRENT_INDENT = '_CURRENT_INDENT'  # The actual indent we are at
_CURRENT_LEVEL = '_CURRENT_LEVEL'  # The current indent level we are at
_PARAMETER_DOC_INDENT = '_PARAMETER_DOC_INDENT'
_IMPORT_REGEX = '_IMPORT_REGEX'
_IMPORT_TEMPLATE = '_IMPORT_TEMPLATE'
_BOOLEAN_LITERALS = '_BOOLEAN_LITERALS'

# The name of the context variable holding a file writer for the 'write' tag to
# use. The file writer is a method with the signature func(path, content).
FILE_WRITER = '_FILE_WRITER'

_defaults = {
    _LINE_BREAK_INDENT: 2,
    _LINE_WIDTH: 40,
    _PARAMETER_INDENT: 4,
    _LEVEL_INDENT: 2,
    _COMMENT_START: '# ',
    _COMMENT_CONTINUE: '# ',
    _COMMENT_END: '',
    _DOC_COMMENT_START: '# ',
    _PARAMETER_DOC_INDENT: 6,
    _DOC_COMMENT_CONTINUE: '# ',
    _DOC_COMMENT_END: '',
    _DOC_COMMENT_BEGIN_TAG: '',
    _DOC_COMMENT_END_TAG: '',
    _IMPORT_REGEX: r'^\s*import\s+(?P<import>[a-zA-Z0-9.]+)',
    _IMPORT_TEMPLATE: 'import %s',
    _LITERAL_QUOTE_START: '"',
    _LITERAL_QUOTE_END: '"',
    _LITERAL_ESCAPE: [
        ('\\', '\\\\'),
        ('"', '\\"'),
        ('\n', '\\n'),
        ('\t', '\\t'),
        ('\r', '\\r'),
        ('\f', '\\f'),
        ],
    _LITERAL_FLOAT_SUFFIX: '',
    _BOOLEAN_LITERALS: ('false', 'true')
    }

_language_defaults = {
    'cpp': {
        _LINE_WIDTH: 80,
        _PARAMETER_INDENT: 4,
        _LEVEL_INDENT: 2,
        _COMMENT_START: '// ',
        _COMMENT_CONTINUE: '// ',
        _COMMENT_END: '',
        _DOC_COMMENT_START: '/** ',
        _DOC_COMMENT_CONTINUE: ' * ',
        _DOC_COMMENT_END: ' */',
        _IMPORT_REGEX: r'^#include\s+(?P<import>[\<\"][a-zA-Z0-9./_\-]+[\>\"])',
        _IMPORT_TEMPLATE: '#include %s'
        },
    'csharp': {
        _LINE_WIDTH: 120,
        _PARAMETER_INDENT: 4,
        _LEVEL_INDENT: 4,
        _COMMENT_START: '// ',
        _COMMENT_CONTINUE: '// ',
        _COMMENT_END: '',
        _DOC_COMMENT_START: '/// ',
        _DOC_COMMENT_CONTINUE: '/// ',
        _DOC_COMMENT_BEGIN_TAG: '<summary>',
        _DOC_COMMENT_END_TAG: '</summary>',
        },
    'dart': {
        _LEVEL_INDENT: 2,
        _LINE_WIDTH: 100,
        _COMMENT_START: '/* ',
        _COMMENT_CONTINUE: ' * ',
        _COMMENT_END: ' */',
        _DOC_COMMENT_START: '/** ',
        _PARAMETER_DOC_INDENT: 6,
        # E.g. #import('dart:json');
        _IMPORT_REGEX: r'^#\s*import\s+\(\'(?P<import>[a-zA-Z0-9:.]+)\'\);',
        _IMPORT_TEMPLATE: """#import('%s');""",
        _LITERAL_ESCAPE: _defaults[_LITERAL_ESCAPE] + [('$', '\\$')]
        },
    'go': {
        _LINE_WIDTH: 120,
        _PARAMETER_INDENT: 4,
        _LEVEL_INDENT: 8,
        _COMMENT_START: '// ',
        _COMMENT_CONTINUE: '// ',
        _COMMENT_END: '',
        _DOC_COMMENT_START: '// ',
        _DOC_COMMENT_CONTINUE: '// '
        },
    'java': {
        _LINE_WIDTH: 100,
        _COMMENT_START: '/* ',
        _COMMENT_CONTINUE: ' * ',
        _COMMENT_END: ' */',
        _DOC_COMMENT_START: '/** ',
        _PARAMETER_DOC_INDENT: 6,
        _IMPORT_REGEX: r'^\s*import\s+(?P<import>[a-zA-Z0-9.]+);',
        _IMPORT_TEMPLATE: 'import %s;'
        },
    'javascript': {
        _LINE_WIDTH: 80,
        _COMMENT_START: '/* ',
        _COMMENT_CONTINUE: ' * ',
        _COMMENT_END: ' */',
        _DOC_COMMENT_START: '/** ',
        },
    'objc': {
        _LINE_WIDTH: 80,
        _COMMENT_START: '// ',
        _COMMENT_CONTINUE: '// ',
        _COMMENT_END: '',
        _DOC_COMMENT_START: '// ',
        _DOC_COMMENT_CONTINUE: '// ',
        _DOC_COMMENT_END: '',
        _LITERAL_QUOTE_START: '@"',
        _BOOLEAN_LITERALS: ('NO', 'YES'),
        _IMPORT_TEMPLATE: '#import %s',
        },
    'php': {
        _LINE_WIDTH: 80,
        _COMMENT_START: '/* ',
        _COMMENT_CONTINUE: ' * ',
        _COMMENT_END: ' */',
        _DOC_COMMENT_START: '/** ',
        _LITERAL_QUOTE_START: '\'',
        _LITERAL_QUOTE_END: '\'',
        _LITERAL_ESCAPE: [
            ('\\', '\\\\'),  # Really is \ => \\
            ('\'', '\\\''),  # ' => \'
            ]
        },
    'python': {
        _LINE_WIDTH: 80,
        _COMMENT_START: '# ',
        _COMMENT_CONTINUE: '# ',
        _COMMENT_END: '# ',
        _DOC_COMMENT_START: '"""',
        _DOC_COMMENT_CONTINUE: '"""',
        _LITERAL_QUOTE_START: '\'',
        _LITERAL_QUOTE_END: '\'',
        _BOOLEAN_LITERALS: ('False', 'True'),
        },
    }

_TEMPLATE_GLOBALS = threading.local()
_TEMPLATE_GLOBALS.current_context = None


def GetCurrentContext():
  return _TEMPLATE_GLOBALS.current_context


@contextlib.contextmanager
def SetCurrentContext(ctxt):
  _TEMPLATE_GLOBALS.current_context = ctxt
  try:
    yield
  finally:
    _TEMPLATE_GLOBALS.current_context = None


def _GetCurrentLanguage(ctxt=None, default=None):
  if ctxt is None:
    ctxt = GetCurrentContext() or {}
  try:
    # respect the language set by the language node, if any
    return ctxt[_LANGUAGE]
  except KeyError:
    language_model = ctxt.get('language_model')
    if language_model and language_model.language:
      return language_model.language
  logging.debug('no language set in context or language model')
  return default


class CachingTemplateLoader(object):
  """A template loader that caches templates under stable directories."""

  # A pattern that variation directories will match if they are development
  # versions that should not be cached.   E.g., "java/dev/" or "java/1.0dev"
  UNSTABLE_VARIATION_PATTERN = re.compile(r'^[^/]+/[^/]*dev/')

  def __init__(self):
    self._cache = {}

  def GetTemplate(self, template_path, template_dir):
    """Get a compiled django template.

    Args:
      template_path: Full path to the template.
      template_dir: The root of the template path.
    Returns:
      A compiled django template.
    """
    relpath = os.path.relpath(template_path, template_dir)
    if (self.UNSTABLE_VARIATION_PATTERN.match(relpath) or
        os.environ.get('NOCACHE')):
      # don't cache if specifically requested (for testing) or
      # for unstable variations
      return self._LoadTemplate(template_path)

    template = self._cache.get(template_path)
    if not template:
      try:
        template = self._LoadTemplate(template_path)
        self._cache[template_path] = template
      except django_template.TemplateSyntaxError as err:
        raise django_template.TemplateSyntaxError('%s: %s' % (relpath, err))
    return template

  def _LoadTemplate(self, template_path):
    source = files.GetFileContents(template_path).decode('utf-8')
    return django_template.Template(source)


_TEMPLATE_LOADER = CachingTemplateLoader()


def _RenderToString(template_path, context):
  """Renders a template specified by a file path with a give values dict.

  NOTE: This routine is essentially a copy of what is in django_helpers.
  We duplicate it here rather than call that one to avoid a mutual recursion
  in the strange django loading process.

  Args:
    template_path: (str) Path to file.
    context: (Context) A django Context.
  Returns:
    (str) The expanded template.
  """
  # FRAGILE: this relies on template_dir being passed in to the
  # context (in generator.py)
  t = _TEMPLATE_LOADER.GetTemplate(template_path,
                                   context.get('template_dir', ''))
  return t.render(context)


def _GetFromContext(context, *variables):
  """Safely get something from the context.

  Look for a variable (or an alternate variable) in the context. If it is not in
  the context, look in language-specific or overall defaults.

  Args:
    context: (Context|None) The Django render context
    *variables: (str) varargs list of variable names
  Returns:
    The requested value from the context or the defaults.
  """
  if context is None:
    context = GetCurrentContext()
  containers = [context]
  current_language = _GetCurrentLanguage(context)
  if current_language and current_language in _language_defaults:
    containers.append(_language_defaults[current_language])
  containers.append(_defaults)
  # Use a non-reproducible default value to allow real non-truthy values.
  default = object()
  for c in containers:
    for v in variables:
      value = c.get(v, default)
      if value is not default:
        return value
  return None


def _GetArgFromToken(token):
  """Split out a single argument word from django tag token.

  When the Django parser encounters a tag of the form {% tag x %}, the tag
  processor is handed a single token containing 'tag x'. We split that apart
  and return just the 'x'.

  Args:
    token: (django.template.Token) the token holding this tag
  Returns:
    (str) The argument word contained in the token.
  Raises:
    TemplateSyntaxError: if the token has no argument.
  """
  try:
    _, arg = token.split_contents()
  except ValueError:
    raise django_template.TemplateSyntaxError(
        'tag requires a single argument: %s' % token.contents)
  return arg


#
# Basic Filters
#


def _DivideIntoBlocks(lines, prefix):
  """Dole out the input text in blocks separated by blank lines.

  A "blank line" in this case means a line that is actually zero length or
  just is the comment prefix. The common prefix, along with any spaces trailing
  the prefix are removed from each line.

  Args:
    lines: list of str
    prefix: a commmon prefix to remove from each line
  Yields:
    list of (list of str)
  """
  block = []
  prefix = prefix.rstrip()
  for line in lines:
    if line.startswith(prefix):
      line = line[len(prefix):].strip()
    if not line:
      if block:
        yield block
      block = []
      continue
    block.append(line)
  if block:
    yield block


def _ExtractCommentPrefix(line):
  """Examine a line of text and extract what would be a comment prefix.

  The pattern we are looking for is ' *[^ ::punctuation::]*'.  This covers most
  programming languages in common use.  Fortran and Basic are obviously not
  supported. :-)

  Args:
    line: (str) a sample line
  Returns:
    (str) The comment prefix
  """
  # look for spaces followed by a comment tag and break after that.
  got_tag = False
  prefix_length = 0
  # collect the prefix pattern
  for c in line:
    if c == ' ':
      if got_tag:
        break
      prefix_length += 1
    elif c in string.punctuation:
      got_tag = True
      prefix_length += 1
    else:
      break
  return line[:prefix_length]


# We disable the bad function name warning because we use Django style names
# rather than Google style names
@register.filter
def java_comment_fragment(value, indent):  # pylint: disable=g-bad-name
  """Template filter to wrap lines into Java comment style.

  Take a single long string and break it so that subsequent lines are prefixed
  by an approprate number of spaces and then a ' * '.  The filter invocation
  should begin on a line that is already indented suffciently.

  This is typically used after we have written the lead-in for a comment. E.g.

  |    // NOTE: The leading / is indented 4 spaces.
  |    /**
  |     * {{ variable|java_comment_fragment:4 }}
  |     */

  Args:
    value: (str) the string to wrap
    indent: (int) the number of spaces to indent the block.
  Returns:
    The rewrapped string.
  """
  if not indent:
    indent = 0
  prefix = '%s * ' % (' ' * indent)
  wrapper = textwrap.TextWrapper(width=_language_defaults['java'][_LINE_WIDTH],
                                 replace_whitespace=False,
                                 initial_indent=prefix,
                                 subsequent_indent=prefix)
  wrapped = wrapper.fill(value)
  if wrapped.startswith(prefix):
    wrapped = wrapped[len(prefix):]
  return wrapped


@register.filter
def java_parameter_wrap(value):  # pylint: disable=g-bad-name
  """Templatefilter to wrap lines of parameter documentation.

  Take a single long string and breaks it up so that subsequent lines are
  prefixed by an appropriate number of spaces (and preceded by a ' * '.

  Args:
   value: (str) the string to wrap

  Returns:
  the rewrapped string.
  """
  # TODO(user): add 'parameter_doc' option to the DocCommentBlock
  indent = _language_defaults['java'][_PARAMETER_DOC_INDENT]
  prefix = ' * %s ' % (' ' * indent)
  wrapper = textwrap.TextWrapper(width=_language_defaults['java'][_LINE_WIDTH],
                                 replace_whitespace=False,
                                 initial_indent='',
                                 subsequent_indent=prefix)
  wrapped = wrapper.fill(value)
  return wrapped


# We disable the bad function name warning because we use Django style names
# rather than Google style names (disable-msg=C6409)
@register.filter
def block_comment(value):  # pylint: disable=g-bad-name
  """Template filter to line wrap a typical block comment.

  Take a block of text where each line has a common comment prefix, divide it
  into multiple sections, line wrap each section and string them back together.
  Sections are defined as blank lines or lines containing only the comment
  prefix.

  Example template usage:
    /**{% filter block_comment %}
     * wwelrj wlejrwerl jrl (very long line ...) rwrwr.
     *
     * more text
     * and more
     * {% endfilter %}
     */

  Args:
    value: (str) a block of text to line wrap.
  Returns:
    (str) the wrapped text.
  """
  if not value:
    return ''
  lines = value.split('\n')
  # Ignore a leading blank line while figuring out the comment tag. This allows
  # us to put the filter tag above the content, rather than flush left before
  # it. It makes the template easier to read.
  leading_blank = False
  if not lines[0]:
    leading_blank = True
    comment_prefix = _ExtractCommentPrefix(lines[1])
  else:
    comment_prefix = _ExtractCommentPrefix(lines[0])
  # TODO(user): Default is for backwards-compatibility; remove when safe to
  # do so.
  language = _GetCurrentLanguage(default='java')
  line_width = _language_defaults[language][_LINE_WIDTH]
  wrapper = textwrap.TextWrapper(width=line_width,
                                 replace_whitespace=False,
                                 initial_indent=('%s ' % comment_prefix),
                                 subsequent_indent=('%s ' % comment_prefix))
  wrapped_blocks = []
  for block in _DivideIntoBlocks(lines, comment_prefix):
    wrapped_blocks.append(wrapper.fill(' '.join(block)))
  ret = ''
  if leading_blank:
    ret = '\n'
  return ret + ('\n%s\n' % comment_prefix).join(wrapped_blocks)


@register.filter
def noblanklines(value):  # pylint: disable=g-bad-name
  """Template filter to remove blank lines."""
  return '\n'.join([line for line in value.split('\n') if line.strip()])


@register.filter
def collapse_blanklines(value):  # pylint: disable=g-bad-name
  """Template filter to collapse successive blank lines into a single one."""
  lines = []
  previous_blank = False
  for line in value.split('\n'):
    if not line.strip():
      if not previous_blank:
        lines.append(line)
        previous_blank = True
      else:
        pass
    else:
      lines.append(line)
      previous_blank = False
  return '\n'.join(lines)


class Halt(Exception):
  """The exception raised when a 'halt' tag is encountered."""
  pass


@register.simple_tag
def halt():  # pylint: disable=g-bad-name
  """A tag which raises a Halt exception.

  Usage:
    {% if some_condition %}{% halt %}{% endif %}

  Raises:
    Halt: always
  """
  raise Halt()


#
# Tags for programming language concepts
#


class LanguageNode(django_template.Node):
  """Node for language setting."""

  def __init__(self, language):
    self._language = language

  def render(self, context):  # pylint: disable=g-bad-name
    """Render the 'language' tag.

    For the language setting we render nothing, but we take advantage of being
    passed the context to set language specific things there, so they are
    usable later.

    Args:
      context: (Context) the render context.
    Returns:
      An empty string.
    """
    context.autoescape = False
    context[_LANGUAGE] = self._language
    per_language_defaults = _language_defaults.get(self._language)
    if per_language_defaults:
      context.update(per_language_defaults)
    context[_CURRENT_INDENT] = 0
    context[_CURRENT_LEVEL] = 0
    return ''


@register.tag(name='language')
def DoLanguage(unused_parser, token):
  """Specify the language we are emitting code in.

  Usage:
    {% language java %}

  Args:
    unused parser: (parser) the Django parser context.
    token: (django.template.Token) the token holding this tag

  Returns:
    a LanguageNode
  """
  language = _GetArgFromToken(token)
  return LanguageNode(language)


class IndentNode(django_template.Node):
  """A node which indents its contents based on indent nesting levels.

  The interior text is re-indented by the existing indent + the indent nesting
      level * the LEVEL_INDENT
  """

  def __init__(self, nodelist, levels):
    self._nodelist = nodelist
    self._levels = int(levels)

  def render(self, context):  # pylint: disable=g-bad-name
    """Reindent the block inside the tag scope."""
    current_indent = context.get(_CURRENT_INDENT, 0)
    current_indent_level = context.get(_CURRENT_LEVEL, 0)
    # How much extra indent will this level add
    extra = (_GetFromContext(context, _LEVEL_INDENT) * self._levels)
    # Set the new effective indent of this block.  Tags which wrap text to
    # the line limit must use this value to determine their actual indentation.
    context[_CURRENT_INDENT] = current_indent + extra
    context[_CURRENT_LEVEL] = current_indent_level + self._levels
    lines = self._nodelist.render(context)
    context[_CURRENT_INDENT] = current_indent
    context[_CURRENT_LEVEL] = current_indent_level
    # We only have to prefix the lines in this row by the extra indent, because
    # the outer scope will be adding its own indent as well.
    prefix = ' ' * extra

    def _PrefixNonBlank(s):
      x = s.rstrip()
      if x:
        x = '%s%s' % (prefix, x)
      return x
    return '\n'.join([_PrefixNonBlank(line) for line in lines.split('\n')])


@register.tag(name='indent')
def DoIndent(parser, token):
  """Increase the indent level for indenting.

  Usage:
    {% indent [levels] %} text... {% endindent %}
    Increase the indent on all lines of text by levels * LEVEL_INDENT

  Args:
    parser: (parser) the Django parser context.
    token: (django.template.Token) the token holding this tag

  Returns:
    a IndentNode
  """
  try:
    unused_tag_name, levels = token.split_contents()
  except ValueError:
    # No level, default to 1
    levels = 1
  nodelist = parser.parse(('endindent',))
  parser.delete_first_token()
  return IndentNode(nodelist, levels)


class CollapsedNewLinesNode(django_template.Node):
  """A node which collapses 3 or more newlines into 2 newlines."""

  def __init__(self, nodelist):
    self._nodelist = nodelist

  def render(self, context):  # pylint: disable=g-bad-name
    """Collapses newline inside the tag scope."""
    lines = self._nodelist.render(context)
    ret = re.sub(r'\n(\n)+', '\n\n', lines)
    return ret


@register.tag(name='collapsenewlines')
def DoCollapseNewLines(parser, unused_token):
  """Collapses 3 or more newlines into 2 newlines.

  Usage:
    {% collapsenewlines %}
    ...
    {% end collapsenewlines %}

  Args:
    parser: (parser) the Django parser context.
    unused_token: (django.template.Token) the token holding this tag

  Returns:
    a CollapsedNewLinesNode
  """
  nodelist = parser.parse(('endcollapsenewlines',))
  parser.delete_first_token()
  return CollapsedNewLinesNode(nodelist)


EOL_MARKER = '\x00eol\x00'
SPACE_MARKER = '\x00sp\x00'
NOBLANK_STACK = '___noblank__stack___'


@register.simple_tag(takes_context=True)
def eol(context):  # pylint:disable=g-bad-name
  # Inside a noblock node, return special marker
  if  context.get(NOBLANK_STACK):
    return EOL_MARKER
  return '\n'


@register.simple_tag(takes_context=True)
def sp(context):  # pylint:disable=g-bad-name
  # Inside a noblock node, return special marker
  if context.get(NOBLANK_STACK):
    return SPACE_MARKER
  return ' '


class NoBlankNode(django_template.Node):
  """Node for remove eols from output."""

  def __init__(self, nodelist, recurse=False, noeol=False):
    self.nodelist = nodelist
    self.recurse = recurse
    self.noeol = noeol

  def _CleanText(self, text):
    lines = [line for line in text.splitlines(True)
             if line.strip()]
    if self.noeol:
      # Remove whitespace at the end of a source line, so that invisible
      # whitespace is not significant (users should use {%sp%} in that
      # situation)..  The text passed in here doesn't necessarily end with a
      # newline, so take care not to strip out whitespace unless it does.
      def Clean(s):
        if s.endswith('\n'):
          return s.rstrip()
        return s
      lines = [Clean(line) for line in lines]
    text = ''.join(lines)
    return text

  def _ReplaceMarkers(self, text):
    return text.replace(EOL_MARKER, '\n').replace(SPACE_MARKER, ' ')

  def render(self, context):  # pylint:disable=g-bad-name
    """Render the node."""
    stack = context.get(NOBLANK_STACK)
    if stack is None:
      stack = context[NOBLANK_STACK] = [self]
    else:
      stack.append(self)
    try:
      output = []
      for n in self.nodelist:
        text = n.render(context)
        if not isinstance(n, TemplateNode) or self.recurse:
          text = self._CleanText(text)
        output.append(text)
      text = ''.join(output)
      # Only replace markers if we are the last node in the stack.
      if len(stack) == 1:
        text = self._ReplaceMarkers(text)
      return text
    finally:
      stack.pop()


@register.tag(name='noblank')
def DoNoBlank(parser, token):
  """Suppress all empty lines unless explicitly added."""
  args = token.split_contents()
  if len(args) > 2:
    raise django_template.TemplateSyntaxError(
        'noblank expects at most one argument')
  if len(args) == 2:
    recursearg = args[1]
    if recursearg not in ('recurse', 'norecurse'):
      raise django_template.TemplateSyntaxError(
          'argument to noblank must be either "norecurse" '
          '(the default) or "recurse"')
    recurse = recursearg == 'recurse'
  else:
    recurse = False

  nodelist = parser.parse(('endnoblank',))
  parser.delete_first_token()
  return NoBlankNode(nodelist, recurse=recurse)


@register.tag(name='noeol')
def DoNoEol(parser, token):
  """Suppress all empty lines unless explicitly added."""
  args = token.split_contents()
  if len(args) > 2:
    raise django_template.TemplateSyntaxError(
        'noeol expects at most one argument')
  if len(args) == 2:
    recursearg = args[1]
    if recursearg not in ('recurse', 'norecurse'):
      raise django_template.TemplateSyntaxError(
          'argument to noeol must be either "norecurse" '
          '(the default) or "recurse"')
    recurse = recursearg == 'recurse'
  else:
    recurse = False

  nodelist = parser.parse(('endnoeol',))
  parser.delete_first_token()
  return NoBlankNode(nodelist, recurse=recurse, noeol=True)


class DocCommentNode(django_template.Node):
  """Node for comments which should be formatted as doc-style comments."""

  def __init__(self, text=None, nodelist=None, comment_type=None,
               wrap_blocks=True):
    self._text = text
    self._nodelist = nodelist
    self._comment_type = comment_type
    self._wrap_blocks = wrap_blocks

  def render(self, context):  # pylint: disable=g-bad-name
    """Render the node."""
    the_text = self._text
    if self._nodelist:
      the_text = self._nodelist.render(context)
    return self.RenderText(the_text, context)

  def RenderText(self, text, context):  # pylint: disable=g-bad-name
    """Format text according to the context.

    The strategy is to divide the text into blocks (on blank lines), then
    to format the blocks individually, then reassemble.

    Args:
      text: (str) The text to format.
      context: (django_template.Context) The rendering context.

    Returns:
      The rendered comment.
    """
    if self._comment_type == 'doc':
      start_prefix = _GetFromContext(context, _DOC_COMMENT_START,
                                     _COMMENT_START)
      continue_prefix = _GetFromContext(context, _DOC_COMMENT_CONTINUE,
                                        _COMMENT_CONTINUE)
      comment_end = _GetFromContext(context, _DOC_COMMENT_END, _COMMENT_END)
      begin_tag = _GetFromContext(context, _DOC_COMMENT_BEGIN_TAG)
      end_tag = _GetFromContext(context, _DOC_COMMENT_END_TAG)
    else:
      start_prefix = _GetFromContext(context, _COMMENT_START)
      continue_prefix = _GetFromContext(context, _COMMENT_CONTINUE)
      comment_end = _GetFromContext(context, _COMMENT_END)
      begin_tag = ''
      end_tag = ''

    available_width = (_GetFromContext(context, _LINE_WIDTH) -
                       context.get(_CURRENT_INDENT, 0))

    return _WrapInComment(
        text,
        wrap_blocks=self._wrap_blocks,
        start_prefix=start_prefix,
        continue_prefix=continue_prefix,
        comment_end=comment_end,
        begin_tag=begin_tag,
        end_tag=end_tag,
        available_width=available_width)


def _WrapInComment(text, wrap_blocks, start_prefix,
                   continue_prefix, comment_end, begin_tag,
                   end_tag, available_width):
  # If the text has no EOL and is short, it may be a one-liner,
  # though still not necessarily because of other comment overhead.
  if len(text) < available_width and '\n' not in text:
    one_line = '%s%s%s%s%s' % (start_prefix, begin_tag, text, end_tag,
                               comment_end)
    if len(one_line) < available_width:
      return one_line

  wrapper = textwrap.TextWrapper(width=available_width,
                                 replace_whitespace=False,
                                 initial_indent=continue_prefix,
                                 subsequent_indent=continue_prefix)
  text = '%s%s%s' % (begin_tag, text, end_tag)
  continue_rstripped = continue_prefix.rstrip()
  if wrap_blocks:
    blocks = _DivideIntoBlocks(text.split('\n'), '')
    block_joiner = '\n%s\n' % continue_rstripped
  else:
    blocks = [[l] for l in text.split('\n')]
    # Eliminate spurious blanks at beginning and end,
    # for compatibility with wrap_blocks behavior.
    for idx in (0, -1):
      if blocks and not blocks[idx][0]:
        del blocks[idx]
    block_joiner = '\n'

  wrapped_blocks = []
  for block in blocks:
    t = ' '.join(block)
    if not t.strip():
      # The text wrapper won't apply an indent to an empty string
      wrapped_blocks.append(continue_rstripped)
    else:
      wrapped_blocks.append(wrapper.fill(t))
  ret = ''
  if start_prefix != continue_prefix:
    ret += '%s\n' % start_prefix.rstrip()

  ret += block_joiner.join(wrapped_blocks)
  if comment_end:
    ret += '\n%s' % comment_end
  return ret


class CommentIfNode(DocCommentNode):
  """Node for comments which should only appear if they have text.

  A CommentIf is a pair of a comment style and a variable name.  If the variable
  has a value, then a comment will be emmited for it, otherwise nothing is
  emitted.
  """

  def __init__(self, variable_name, comment_type=None):
    super(CommentIfNode, self).__init__(comment_type=comment_type)
    self._variable_name = variable_name

  def render(self, context):  # pylint: disable=g-bad-name
    """Render the node."""
    try:
      text = django_template.resolve_variable(self._variable_name, context)
      if text:
        return self.RenderText(text, context)
    except django_template.base.VariableDoesNotExist:
      pass
    return ''


@register.tag(name='comment_if')
def DoCommentIf(unused_parser, token):
  """If a variable has content, emit it as a comment."""
  variable_name = _GetArgFromToken(token)
  return CommentIfNode(variable_name)


@register.tag(name='doc_comment_if')
def DoDocCommentIf(unused_parser, token):
  """If a variable has content, emit it as a document compatible comment."""
  variable_name = _GetArgFromToken(token)
  return CommentIfNode(variable_name, comment_type='doc')


@register.tag(name='doc_comment')
def DoDocComment(parser, token):
  """A block tag for documentation comments.

  Example usage:
    {% doc_comment noblock %}
    With the noblock parameter, line returns will be considered hard returns
    and kept in the output, although long lines will be wrapped.

    Without noblock, contiguous non-empty lines will be wrapped together as
    paragraphs.
    {% enddoc_comment %}

  Args:
    parser: (Parser): A django template parser.
    token: (str): Token passed into the parser.
  Returns:
    (DocCommentNode) A template node.
  """
  args = token.split_contents()
  if len(args) > 2:
    raise django_template.TemplateSyntaxError(
        'doc_comment expects at most one argument')
  if len(args) == 2:
    wraparg = args[1]
    if wraparg not in ('block', 'noblock'):
      raise django_template.TemplateSyntaxError(
          'argument to doc_comment (wrap_blocks) '
          'must be either "block" (the default) or "noblock"')
    wrap_blocks = wraparg == 'block'
  else:
    wrap_blocks = True

  nodelist = parser.parse(('enddoc_comment',))
  parser.delete_first_token()
  return DocCommentNode(nodelist=nodelist, comment_type='doc',
                        wrap_blocks=wrap_blocks)


class CamelCaseNode(django_template.Node):
  """Node for camel casing a variable value."""

  def __init__(self, variable_name):
    super(CamelCaseNode, self).__init__()
    self._variable_name = variable_name

  def render(self, context):  # pylint: disable=g-bad-name
    try:
      text = django_template.resolve_variable(self._variable_name, context)
      if text:
        return utilities.CamelCase(text)
    except django_template.base.VariableDoesNotExist:
      pass
    return ''


@register.tag(name='camel_case')
def DoCamelCase(unused_parser, token):
  variable_name = _GetArgFromToken(token)
  return CamelCaseNode(variable_name)


class ParameterGetterChainNode(django_template.Node):
  """Node for returning the parameter getter chain of methods.

  The parameter getter chain here refers to the sequence of getters necessary
  to return the specified parameter. For example, for parameter xyz this method
  could return: ".getParent1().getParent2().getParent1().getXyz()".
  The chain is as long as the number of ancestors of the specified parameter.
  """

  def __init__(self, variable_name):
    super(ParameterGetterChainNode, self).__init__()
    self._variable_name = variable_name

  def render(self, context):  # pylint: disable=g-bad-name
    """Render the node."""
    try:
      prop = django_template.resolve_variable(self._variable_name, context)
    except django_template.base.VariableDoesNotExist:
      return ''

    lang_model = prop.language_model
    parent_pointer = prop.data_type.parent
    getter_chain_list = []

    while parent_pointer.parent:
      # Append a getter for an ancestor of the property.
      getter_chain_list.append(
          lang_model.ToPropertyGetterMethodWithDelim(
              parent_pointer.safeClassName))
      # Move the pointer up one level
      parent_pointer = parent_pointer.parent

    # Now append a final getter for the original property.
    getter_chain_list.append(
        lang_model.ToPropertyGetterMethodWithDelim(
            str(prop.GetTemplateValue('wireName'))))

    return ''.join(getter_chain_list)


@register.tag(name='param_getter_chain')
def DoParameterGetterChain(unused_parser, token):
  variable_name = _GetArgFromToken(token)
  return ParameterGetterChainNode(variable_name)


class ImportsNode(django_template.Node):
  """Node for outputting language specific imports."""

  def __init__(self, nodelist, element):
    self._nodelist = nodelist
    self._element = element

  def render(self, context):  # pylint: disable=g-bad-name
    """Render the node."""

    explicit_import_text = self._nodelist.render(context)

    # Look for an importManager on the element.  If we find one:
    # - scan the import text for import statements
    # - add each to the manager
    # - get the complete import set
    import_lists = None
    try:
      import_manager = django_template.resolve_variable(
          '%s.importManager' % self._element, context)
      import_regex = _GetFromContext(context, _IMPORT_REGEX)
      for line in explicit_import_text.split('\n'):
        match_obj = re.match(import_regex, line)
        if match_obj:
          import_manager.AddImport(match_obj.group('import'))
      import_lists = import_manager.ImportLists()
    except django_template.base.VariableDoesNotExist:
      pass

    import_template = _GetFromContext(context, _IMPORT_TEMPLATE)
    if import_lists:
      ret_lists = []
      for import_list in import_lists:
        ret_lists.append(
            '\n'.join([import_template % x for x in import_list]))
      # Each import should be on its own line and each group of imports should
      # be separated by a new line.
      return '\n\n'.join([ret_list for ret_list in ret_lists if ret_list])
    else:
      # We could not find the import lists from an import manager, revert to
      # the original text
      return explicit_import_text.strip()


@register.tag(name='imports')
def Imports(parser, token):
  """If an element has importLists emit them, else emit existing imports."""
  element = _GetArgFromToken(token)
  nodelist = parser.parse(('endimports',))
  parser.delete_first_token()
  return ImportsNode(nodelist, element)


class ParameterListNode(django_template.Node):
  """Node for parameter_list blocks."""

  def __init__(self, nodelist, separator):
    super(ParameterListNode, self).__init__()
    self._nodelist = nodelist
    self._separator = separator

  def render(self, context):  # pylint: disable=g-bad-name
    """Render the node."""
    blocks = []
    # Split apart on paramater boundaries, getting rid of white space between
    # parameters
    for block in self._nodelist.render(context).split(ParameterNode.BEGIN):
      block = block.rstrip().replace(ParameterNode.END, '')
      if block:
        blocks.append(block)
    return self._separator.join(blocks)


class ParameterNode(django_template.Node):
  """Node for parameter tags."""

  # Makers so the parameter_list can find me.
  BEGIN = chr(1)
  END = chr(2)

  def __init__(self, nodelist):
    super(ParameterNode, self).__init__()
    self._nodelist = nodelist

  def render(self, context):  # pylint: disable=g-bad-name
    """Render the node."""
    # Attach markers so the enclosing parameter_list can find me
    return self.BEGIN + self._nodelist.render(context).strip() + self.END


@register.tag(name='parameter_list')
def DoParameterList(parser, token):
  """Gather a list of parameter declarations and join them with ','.

  Gathers all 'parameter' nodes until the 'end_parameter_list' tag and joins
  them together with a ', ' separator. Extra white space between nodes is
  removed, but other text is left intact, joined to the end of the preceeding
  parameter node. Blank parameters are omitted from the list.

  Usage:
    foo({% parameter_list separator %}{% for p in method.parameters %}
        {{ p.type }} {{ p.name }}
        {% endfor %}
        {% end_parameter_list %})

  Args:
    parser: (parser) the Django parser context.
    token: (django.template.Token) the token holding this tag

  Returns:
    a ParameterListNode
  """
  try:
    unused_tag_name, separator = token.split_contents()
  except ValueError:
    # No separator, set default.
    separator = ', '
  nodelist = parser.parse(('end_parameter_list',))
  parser.delete_first_token()
  return ParameterListNode(nodelist, separator)


@register.tag(name='parameter')
def DoParameter(parser, unused_token):
  """A single parameter in a parameter_list.

  See DoParameterList for a description.

  Args:
    parser: (parser) the Django parser context.
    unused_token: (django.template.Token) the token holding this tag

  Returns:
    a ParameterNode
  """
  nodelist = parser.parse(('end_parameter',))
  parser.delete_first_token()
  return ParameterNode(nodelist)


#
# Tags which include language specific templates
#


class TemplateNode(django_template.Node):
  """Django template Node holding data for writing a per language template.

  The TemplateNode is a variation of an include template that allows for
  per language lookup.  The node
  * Looks up the template name w.r.t. the template_dir variable of the current
    context.  The calling application must make sure template_dir is valid.
  * evaluates a variable in the current context and binds that value to a
    specific variable in the context
  * renders the template
  * restores the context.

  See individual tag definitions for usage.
  """

  def __init__(self, template_name, bindings):
    """Construct the TemplateNode.

    Args:
      template_name: (str) the name of the template file. This will be resolved
          relative to the 'template_dir' element of the context.
      bindings: (dict) maps names of variables to be bound in the invoked
          template, to the variable from the calling template containing the
          value that should be bound.
    """
    self._template_name = template_name
    self._bindings = bindings

  def render(self, context):  # pylint: disable=g-bad-name
    """Render the node."""
    template_path = os.path.join(context['template_dir'], self._template_name)
    # Collect new additions to the context
    newvars = {}
    for target, source in self._bindings.iteritems():
      try:
        newvars[target] = django_template.resolve_variable(source, context)
      except django_template.base.VariableDoesNotExist:
        raise django_template.TemplateSyntaxError(
            'can not resolve %s when calling template %s' % (
                source, self._template_name))
    # Push new variables onto the context stack
    context.update(newvars)
    # Render the result
    try:
      return _RenderToString(template_path, context).rstrip()
    except django_template.TemplateDoesNotExist:
      # replace with full path
      raise django_template.TemplateDoesNotExist(template_path)
    finally:
      # Pop the context stack
      context.pop()

  @classmethod
  def CreateTemplateNode(cls, token, template, bound_variable):
    """Helper function to create a TemplateNode by parsing a tag.

    Args:
      token: (django.template.Token) the token holding this tag
      template: (str) The template name
      bound_variable: (str) the name of a variable to set in the context when
          we invoke the template.

    Returns:
      a TemplateNode
    """
    variable_name = _GetArgFromToken(token)
    return cls(template, {bound_variable: variable_name})


@register.tag(name='call_template')
def CallTemplate(unused_parser, token):
  """Interpret a template with an additional set of variable bindings.

  Evaluates the template named 'template_name.tmpl' with the variables 'name1',
  'name2', etc., bound to the values of the variables 'val1', 'val2'.

  Usage -- either:
    {% call_template template_name name1=val1 name2=val2 %}
  or (for backwards compatibility):
    {% call_template template_name name1 val1 name2 val2 %}

  Mixing the two styles is not allowed.

  Args:
    unused_parser: (parser) the Django parser context.
    token: (django.template.Token) the token holding this tag

  Returns:
    a TemplateNode
  """
  contents = token.split_contents()
  if len(contents) < 2:
    raise django_template.TemplateSyntaxError(
        'tag requires at least 1 argument, the called template')
  unused_tag, template = contents[:2]
  template_path = '%s.tmpl' % template
  toks = contents[2:]
  if not toks:
    return TemplateNode(template_path, {})

  has_equals = set('=' in t for t in toks)
  # Either all arguments should contain a '=', or none should.
  if len(has_equals) != 1:
    raise django_template.TemplateSyntaxError(
        'use either name1=value1 name2=value2 syntax, '
        'or name1 value1 name2 value2 syntax, but not both')
  has_equals = has_equals.pop()
  if has_equals:
    # If the actual key/value pairs are malformed, let it explode later
    bindings = dict(tok.split('=', 1) for tok in toks)
  else:
    if len(toks) % 2 != 0:
      raise django_template.TemplateSyntaxError(
          'odd number of keys and values found')
    bindings = dict(zip(toks[0::2], toks[1::2]))

  return TemplateNode('%s.tmpl' % template, bindings)


@register.tag(name='emit_parameter_doc')
def DoEmitParameterDoc(unused_parser, token):
  """Emit a parameter definition through a language specific template.

  Evaluates a template named '_parameter.tmpl' with the variable 'parameter'
  bound to the specified value.

  Usage:
    {% emit_parameter_doc parameter %}

  Args:
    unused_parser: (parser) the Django parser context
    token: (django.template.Token) the token holding this tag

  Returns:
    a TemplateNode
  """
  return TemplateNode.CreateTemplateNode(token, '_parameter.tmpl', 'parameter')


@register.tag(name='copyright_block')
def DoCopyrightBlock(parser, unused_token):
  """Emit a copyright block through a language specific template.

  Emits a copyright and license block. The copyright text is pulled from the
  variable api.copyright at rendering time.

  Usage:
    {% copyright_block %}

  Args:
    parser: (parser) the Django parser context.
    unused_token: (django.template.Token) the token holding this tag

  Returns:
    a DocCommentNode
  """
  return DocCommentNode(nodelist=django_template.NodeList([
      django_template.base.VariableNode(parser.compile_filter('api.copyright')),
      django_template.base.TextNode('\n'),
      django_template.base.TextNode(_LICENSE_TEXT)
      ]))


class LiteralStringNode(django_template.Node):
  """Django template Node holding a string to be written as a literal."""

  def __init__(self, text):
    """Construct the LiteralStringNode.

    Args:
      text: (list) the variable names containing the text being represented.
    """
    self._variables = text

  def render(self, context):  # pylint: disable=g-bad-name
    """Render the node."""
    resolve = django_template.resolve_variable
    texts = []
    for v in self._variables:
      try:
        texts.append(resolve(v, context))
      except django_template.base.VariableDoesNotExist:
        pass
    text = ''.join(texts)
    for special, replacement in _GetFromContext(context, _LITERAL_ESCAPE):
      text = text.replace(special, replacement)
    start = _GetFromContext(context, _LITERAL_QUOTE_START)
    end = _GetFromContext(context, _LITERAL_QUOTE_END)
    return start + text + end


@register.tag(name='literal')
def DoLiteralString(unused_parser, token):
  """Emit a variable as a string literal, escaped for the current language.

  A variable foo containing 'ab<newline>c' would be emitted as "ab\\nc"
  (with no literal newline character). Multiple variables are concatenated.

  Usage:
    {% literal somevar anothervar %}

  Args:
    unused_parser: (parser) the Django parser context
    token: (django.template.Token) the token holding this tag and arguments

  Returns:
    a LiteralStringNode
  """
  variables = token.split_contents()[1:]
  return LiteralStringNode(variables)


class DataContextNode(django_template.Node):
  """A Django Template Node for resolving context lookup and validation."""

  def __init__(self, variable):
    self._variable = variable

  def render(self, context):  # pylint: disable=g-bad-name
    """Make sure this is actually a Node and render it."""
    resolve = django_template.resolve_variable

    data = resolve(self._variable, context)
    if hasattr(data, 'GetLanguageModel') and hasattr(data, 'value'):
      model = data.GetLanguageModel()
      # TODO(user): Fix the fact that Arrays don't know their language
      #   model.

      try:
        return model.RenderDataValue(data)
      except ValueError as e:
        raise django_template.TemplateSyntaxError(
            'Variable (%s) with value (%s) is not an accepted DataValue '
            'type (%s) as exhibited by: ValueError(%s).' %
            (self._variable, data.value, data.data_type, e))
    else:
      raise django_template.TemplateSyntaxError(
          '(%s) is not a DataValue object.' % self._variable)


@register.tag(name='value_of')
def GetValueOf(unused_parser, token):
  """Appropriately wrap DataValue objects for eventual rendering."""
  return DataContextNode(_GetArgFromToken(token))


class BoolNode(django_template.Node):
  """A node for outputting bool values."""

  def __init__(self, variable):
    self._variable = variable

  def render(self, context):  # pylint:disable=g-bad-name
    data = bool(django_template.resolve_variable(self._variable, context))
    return _GetFromContext(context, _BOOLEAN_LITERALS)[data]


@register.tag(name='bool')
def DoBoolTag(unused_parser, token):
  return BoolNode(_GetArgFromToken(token))


class DivChecksumNode(django_template.Node):
  """A node for calculating a sha-1 checksum for HTML contents."""

  def __init__(self, id_nodes, body_nodes):
    self._id_nodes = id_nodes
    self._body_nodes = body_nodes

  def render(self, context):  # pylint:disable=g-bad-name
    body = self._body_nodes.render(context)
    element_id = self._id_nodes.render(context)
    checksum = hashlib.sha1(body).hexdigest()
    return ('<div id="%s" checksum="%s">%s</div>' %
            (element_id, checksum, body))


@register.tag(name='checksummed_div')
def DoDivChecksumTag(parser, unused_token):
  """Wraps HTML in a div with its checksum as an attribute."""

  id_nodes = parser.parse(('divbody',))
  parser.delete_first_token()
  body_nodes = parser.parse(('endchecksummed_div',))
  parser.delete_first_token()
  return DivChecksumNode(id_nodes, body_nodes)


class WriteNode(django_template.Node):
  """A node which writes its contents to a file.

  A Node which evaluates its children and writes that result to a file rather
  than into the current output document. This node does not open files directly.
  Instead, it requires that a file writing method is passed to us via the
  evaluation context. It must be under the key template_objects.FILE_WRITER,
  and be a method with the signature func(path, content).
  """

  def __init__(self, nodelist, path_variable):
    self._nodelist = nodelist
    self._path_variable = path_variable

  def render(self, context):  # pylint: disable=g-bad-name
    """Render the 'write' tag.

    Evaluate the file name, evaluate the content, find the writer, ship it.

    Args:
      context: (Context) the render context.
    Returns:
      An empty string.
    Raises:
      ValueError: If the file writer method can not be found.
    """
    path = django_template.resolve_variable(self._path_variable, context)
    content = self._nodelist.render(context)
    file_writer = _GetFromContext(context, FILE_WRITER)
    if not file_writer:
      raise ValueError('"write" called in a context where "%s" is not defined.',
                       FILE_WRITER)
    file_writer(path, content)
    return ''


@register.tag(name='write')
def DoWrite(parser, token):
  """Construct a WriteNode.

  write is a block tag which diverts the rendered content to a file rather than
  into the current output document.

  Usage:
    {% write file_path_variable %} ... {% endwrite %}

  Args:
    parser: (parser) the Django parser context.
    token: (django.template.Token) the token holding this tag

  Returns:
    a WriteNode
  """
  unused_tag_name, path = token.split_contents()
  nodelist = parser.parse(('endwrite',))
  parser.delete_first_token()
  return WriteNode(nodelist, path)
