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

"""Tests for template_helpers."""

__author__ = 'aiuto@google.com (Tony Aiuto)'


import hashlib
import os
import textwrap

from google.apputils import basetest
# pylint: disable=unused-import
from googleapis.codegen import django_helpers
from googleapis.codegen import template_helpers
from django import template as django_template  # pylint: disable=g-bad-import-order


class TemplateHelpersTest(basetest.TestCase):

  _TEST_DATA_DIR = os.path.join(os.path.dirname(__file__), 'testdata')

  def testExtractCommentPrefix(self):
    self.assertEquals('   *',
                      template_helpers._ExtractCommentPrefix('   * hello'))
    self.assertEquals('   *',
                      template_helpers._ExtractCommentPrefix('   *hello'))
    self.assertEquals('//',
                      template_helpers._ExtractCommentPrefix('// hello'))
    self.assertEquals('#',
                      template_helpers._ExtractCommentPrefix('# hello'))
    self.assertEquals('  #',
                      template_helpers._ExtractCommentPrefix('  # hello'))

  def testDivideIntoBlocks(self):
    test = """
      // block 1
      //
      // block 2a
      // block 2a

      // block 3
      // """
    blocks = []
    for block in template_helpers._DivideIntoBlocks(test.split('\n'),
                                                    '      //'):
      blocks.append(block)
    self.assertEquals(3, len(blocks))
    self.assertEquals(1, len(blocks[0]))
    self.assertEquals(2, len(blocks[1]))
    self.assertEquals(1, len(blocks[2]))

  def testCommentFragment(self):
    value = '123456789 ' * 15
    indent = 6
    # What we expect is that 9 of the sequences above will fit on the first
    # line, then we wrap. It's only 89 because the trailing space is trimmed.
    expected = value[:89] + '\n' + (' ' * indent) + ' * ' + value[90:-1]
    self.assertEquals(expected,
                      template_helpers.java_comment_fragment(value, indent))

  def testCommentBlockJavaDoc(self):
    alphabet = 'abcdefghijklmnopqrstuvwxyz'
    value = """
       * %s %s
       * %s %s %s
       * """ % (alphabet, alphabet, alphabet, alphabet, alphabet)
    expected = """
       * %s %s %s
       * %s %s""" % (alphabet, alphabet, alphabet, alphabet, alphabet)
    self.assertEquals(expected, template_helpers.block_comment(value))
    value = """
       // %s %s
       // %s %s %s
       // """ % (alphabet, alphabet, alphabet, alphabet, alphabet)
    expected = """
       // %s %s %s
       // %s %s""" % (alphabet, alphabet, alphabet, alphabet, alphabet)
    self.assertEquals(expected, template_helpers.block_comment(value))
    value = '// %s %s %s %s' % ((alphabet,) * 4)
    expected = '// %s %s %s\n// %s' % ((alphabet,) * 4)
    self.assertEquals(expected, template_helpers.block_comment(value))

  def testCommentBlockPerLanguage(self):
    text = ('Confectis bellis quinquiens triumphavit, post devictum '
            'Scipionem quater eodem mense, sed interiectis diebus, et '
            'rursus semel post superatos Pompei liberos.')

    tmpl_tmpl = textwrap.dedent("""
    {%% language %s %%}

    {%% filter block_comment %%}
    %s {{ text }}
    {%% endfilter %%}
    """)

    def TestLanguage(language):
      lang_defaults = template_helpers._language_defaults[language]
      comment_start = lang_defaults[template_helpers._COMMENT_START]
      line_width = lang_defaults[template_helpers._LINE_WIDTH]
      source = tmpl_tmpl % (language, comment_start)
      result = django_helpers._DjangoRenderTemplateSource(
          source, {'text': text})
      for line in result.splitlines():
        len_line = len(line)
        self.assertTrue(len_line <= line_width,
                        '%d should be less than %d for %s' % (
                            len_line, line_width, language))

    for language in sorted(template_helpers._language_defaults):
      TestLanguage(language)

  def testNoblanklines(self):
    self.assertEquals('a\nb', template_helpers.noblanklines('a\nb'))
    self.assertEquals('a\nb', template_helpers.noblanklines('a\nb\n\n'))
    self.assertEquals('a\nb', template_helpers.noblanklines('\na\n\nb\n'))

  def _GetContext(self, data=None):
    return django_template.Context(data or {})

  def testCollapseNewLines(self):
    context = self._GetContext()

    class NodesList(object):

      def __init__(self, ret):
        self._ret = ret

      def render(self, unused_context):  # pylint: disable=g-bad-name
        return self._ret

    collapse_node = template_helpers.CollapsedNewLinesNode(NodesList('ab'))
    self.assertEquals('ab', collapse_node.render(context))
    collapse_node = template_helpers.CollapsedNewLinesNode(NodesList('a\nb'))
    self.assertEquals('a\nb', collapse_node.render(context))
    collapse_node = template_helpers.CollapsedNewLinesNode(NodesList('a\n\nb'))
    self.assertEquals('a\n\nb', collapse_node.render(context))
    collapse_node = template_helpers.CollapsedNewLinesNode(
        NodesList('a\n\n\nb'))
    self.assertEquals('a\n\nb', collapse_node.render(context))
    collapse_node = template_helpers.CollapsedNewLinesNode(
        NodesList('a\n\n\n\nb'))
    self.assertEquals('a\n\nb', collapse_node.render(context))

  def testDocCommentBlocks(self):

    def Render(language, text, block):
      context = self._GetContext()
      lang_node = template_helpers.LanguageNode(language)
      lang_node.render(context)
      doc_comment_node = template_helpers.DocCommentNode(
          text=text, comment_type='doc', wrap_blocks=block)
      return doc_comment_node.render(context)

    s1 = [('We can all agree that this comment is '
           'almost certain to be too long for a '),
          'single line due to its excessive verbosity.']
    s2 = 'This is short and sweet.'
    text = '\n'.join([''.join(s1), s2])
    no_blocks = Render('cpp', text, False)
    with_blocks = Render('cpp', text, True)
    self.assertNotEqual(no_blocks, with_blocks)
    self.assertTrue((' * %s' % s2) in no_blocks)
    self.assertTrue(s1[1] + ' ' +  s2 in with_blocks)

  def testWrapInComment(self):
    text = textwrap.dedent("""\
    Line one.

    Line three.

    Line five.
    """)

    expected = textwrap.dedent("""\
    /**
     * Line one.
     *
     * Line three.
     *
     * Line five.
     */""")
    for should_wrap in (True, False):
      wrapped = template_helpers._WrapInComment(
          text, wrap_blocks=should_wrap, start_prefix='/**',
          continue_prefix=' * ',
          comment_end=' */', begin_tag='', end_tag='',
          available_width=80)
      self.assertEquals(expected, wrapped)

  def testDocCommmentsEol(self):
    source_tmpl = textwrap.dedent("""\
    {% language java %}
    {% doc_comment XXX %}
    Sets the '<code>{{ p.wireName }}</code>' attribute.
    {% if p.deprecated %}
    @deprecated
    {% endif %}
    @param[in] value {{ p.description }}
    {% enddoc_comment %}
    """)
    for should_block in ('block', 'noblock'):
      source = source_tmpl.replace('XXX', should_block)
      template = django_template.Template(source)
      context = self._GetContext({
          'p': {
              'deprecated': True,
              'wireName': 'foobar',
              'description': 'A description.',
              }})

      rendered = template.render(context)
      expected = (
          '\n'
          '/**\n'
          ' * Sets the \'<code>foobar</code>\' attribute.\n'
          ' *\n'
          ' * @deprecated\n'
          ' *\n'
          ' * @param[in] value A description.\n'
          ' */\n')
      self.assertEquals(expected, rendered, 'should block is %s' % should_block)

  def testDocComments(self):
    def TryDocComment(language, input_text, expected):
      context = self._GetContext()
      lang_node = template_helpers.LanguageNode(language)
      lang_node.render(context)
      context['_LINE_WIDTH'] = 50  # to make expected easier to read
      doc_comment_node = template_helpers.DocCommentNode(
          text=input_text, comment_type='doc')
      self.assertEquals(expected, doc_comment_node.render(context))

    alphabet = 'abcdefghijklmnopqrstuvwxyz'

    # single line java and php
    value = '%s' % alphabet
    expected = '/** %s */' % alphabet
    TryDocComment('java', value, expected)
    TryDocComment('php', value, expected)

    # single line csharp and cpp
    value = 'Hello, World!'
    TryDocComment('cpp', value, '/** %s */' % value)
    TryDocComment('csharp', value, '/// <summary>%s</summary>' % value)

    # single line but with '\n' in it
    value = '123\n456'
    expected_expansion = '123 456'
    # NOTE(user): 20130111
    # Java and PHP have their own special methods for handling comments.
    # I think this case is wrong, but am not addressing it at this time
    # since it is still syntactically correct.
    TryDocComment('java', value, '/**\n * %s\n */' % expected_expansion)
    TryDocComment('php', value, '/**\n * %s\n */' % expected_expansion)
    TryDocComment('cpp', value, '/**\n * %s\n */' % expected_expansion)
    TryDocComment('csharp', value,
                  '/// <summary>%s</summary>' % expected_expansion)

    # multi line java and php
    value = '%s %s %s' % (alphabet, alphabet, alphabet)
    expected = '/**\n * %s\n * %s\n * %s\n */' % (alphabet, alphabet, alphabet)
    TryDocComment('java', value, expected)
    TryDocComment('php', value, expected)

    # single line csharp and c++
    value = alphabet
    TryDocComment('csharp', value, '/// <summary>%s</summary>' % value)
    TryDocComment('cpp', value, '/** %s */' % value)

    # multi line csharp
    value = '%s %s %s' % (alphabet, alphabet, alphabet)
    expected_expansion = '%s\n/// %s\n/// %s' % (alphabet, alphabet, alphabet)
    TryDocComment('csharp', value,
                  '/// <summary>%s</summary>' % expected_expansion)

    expected_expansion = '%s\n * %s\n * %s' % (alphabet, alphabet, alphabet)
    TryDocComment('cpp', value, '/**\n * %s\n */' % expected_expansion)

  def testCallTemplate(self):
    source = 'abc {% call_template _call_test foo bar qux api.xxx %} def'
    template = django_template.Template(source)
    rendered = template.render(self._GetContext({
        'template_dir': self._TEST_DATA_DIR,
        'api': {
            'xxx': 'yyy'
            },
        'bar': 'baz'
        }))
    self.assertEquals('abc 1baz1 2yyy2 3yyy3 def', rendered)

  def testCallTemplateOutOfDirectory(self):
    source = 'abc {% call_template ../_out_of_dir %} def'
    template = django_template.Template(source)
    rendered = template.render(self._GetContext({
        'template_dir': os.path.join(self._TEST_DATA_DIR, 'languages'),
        }))
    self.assertEquals('abc OUT OF DIR def', rendered)

  def testCallTemplateWithEqualsSyntax(self):
    source = 'abc {% call_template _call_test foo=bar qux=api.xxx %} def'
    template = django_template.Template(source)
    rendered = template.render(self._GetContext({
        'template_dir': self._TEST_DATA_DIR,
        'api': {
            'xxx': 'yyy'
            },
        'bar': 'baz'
        }))
    self.assertEquals('abc 1baz1 2yyy2 3yyy3 def', rendered)

  def testCallTemplateRestoreVar(self):
    """Make sure variable stacking happens correctly on call_template."""
    source = 'abc {% call_template _call_test foo bar qux api.xxx %} {{foo}}'
    template = django_template.Template(source)
    rendered = template.render(self._GetContext({
        'template_dir': self._TEST_DATA_DIR,
        'api': {
            'xxx': 'yyy'
            },
        'bar': 'baz',
        'foo': 'OrigFoo'
        }))
    self.assertEquals('abc 1baz1 2yyy2 3yyy3 OrigFoo', rendered)

  def testParamList(self):
    source = """method({% parameter_list %}
          {% parameter %}int a{% end_parameter%}
          {% parameter %}
            {% if false %}
               The condition fails, so the entire parameter is empty.
            {% endif %}
          {% end_parameter %}
          {% parameter %}string b{% end_parameter %}
        {% end_parameter_list %})"""
    template = django_template.Template(source)
    rendered = template.render(self._GetContext())
    self.assertEquals('method(int a, string b)', rendered)

  def testParamEscaping(self):
    source = """method({% parameter_list %}
        {% parameter %}JsonCppArray<string> a{% end_parameter %}
        {% end_parameter_list %})"""
    template = django_template.Template(source)
    rendered = template.render(self._GetContext({}))
    self.assertEquals('method(JsonCppArray<string> a)', rendered)
    source = """method({% parameter_list %}
        {% parameter %}{{ foo }} a{% end_parameter %}
        {% end_parameter_list %})"""
    template = django_template.Template(source)
    rendered = template.render(self._GetContext(
        {'foo': 'JsonCppArray<string>'}))
    # HTML escaping has not been turned off
    self.assertEquals('method(JsonCppArray&lt;string&gt; a)', rendered)
    source = '{% language cpp %}' + source
    template = django_template.Template(source)
    rendered = template.render(self._GetContext(
        {'foo': 'JsonCppArray<string>'}))
    self.assertEquals('method(JsonCppArray<string> a)', rendered)
    source = """{% language cpp %}
        {% call_template _escape_test foo foo %}
    """
    template = django_template.Template(source)
    rendered = template.render(self._GetContext(
        {'template_dir': self._TEST_DATA_DIR,
         'foo': 'JsonCppArray<string>'})).strip()
    self.assertEquals('method(JsonCppArray<string> a)', rendered)

  def testImportWithoutManager(self):
    expected = """import hello_world
                  import abc"""
    source = '{% imports x %}\n' + expected + '\n{% endimports %}'
    template = django_template.Template(source)
    rendered = template.render(self._GetContext({'x': {}}))
    self.assertEquals(expected, rendered)

  def testNoEol(self):
    def TryIt(source, expected, ctxt=None):
      template = django_template.Template(source)
      rendered = template.render(self._GetContext(ctxt))
      self.assertEquals(expected, rendered)

    source = textwrap.dedent("""\
    {% noeol %}
    public{% sp %}
    get
    {{ name }}() {
    {% eol %}
      return
    {% sp %}
    {{ x }};
    {% if thing %}{% eol %}{% endif %}
    }
    {% endnoeol %}""")

    expected = 'public getFoo() {\n  return foo;\n}'
    TryIt(source, expected, {'name': 'Foo', 'x': 'foo', 'thing': '1'})

    source = textwrap.dedent("""\
    {% noeol %}
    First {{ name }} Later
    {% endnoeol %}""")

    expected = 'First Bob Later'
    TryIt(source, expected, {'name': 'Bob'})

  def testNoBlank(self):
    def TryIt(source, expected, ctxt=None):
      template = django_template.Template(source)
      rendered = template.render(self._GetContext(ctxt))
      self.assertEquals(expected, rendered)

    source = textwrap.dedent("""\
    {% noblank %}

    This is all going to be fine.

    Don't be alarmed.

    There are no empty lines here.

    {% endnoblank %}""")

    expected = ('This is all going to be fine.\n'
                'Don\'t be alarmed.\n'
                'There are no empty lines here.\n')

    TryIt(source, expected, {})

    source = textwrap.dedent("""\
    {% noblank %}
    This is all going to be fine.

    Don't be alarmed.

    There is one empty line here.

    {% eol %}


    {% endnoblank %}""")

    expected = ('This is all going to be fine.\n'
                'Don\'t be alarmed.\n'
                'There is one empty line here.\n\n')

    TryIt(source, expected, {})

  def testNestedNoBlank(self):
    source = textwrap.dedent("""\
    {% noblank %}
    Foo
    {% noeol %}
    Bar
    {% eol %}
    {% endnoeol %}
    {% eol %}
    {% endnoblank %}X
    """)
    expected = 'Foo\nBar\n\nX\n'
    template = django_template.Template(source)
    self.assertEquals(expected, template.render(self._GetContext({})))

  def testNoBlankRecurse(self):

    def TryIt(source, expected):
      ctxt = self._GetContext({
          'template_dir': self._TEST_DATA_DIR
          })
      template = django_template.Template(source)
      gotten = template.render(ctxt)
      self.assertEquals(expected, gotten)

    recurse_source = textwrap.dedent("""\
    {% noblank recurse %}
    {% call_template _eoltest %}
    {% endnoblank %}
    """)
    recurse_expected = '|\n|\nX\nX\n'

    TryIt(recurse_source, recurse_expected)

    norecurse_source = textwrap.dedent("""\
    {% noblank %}
    {% call_template _eoltest %}
    {% endnoblank %}
    """)

    norecurse_expected = '|\n|\n\n\nX\n\n\nX\n'

    TryIt(norecurse_source, norecurse_expected)

    recurse_source = textwrap.dedent("""\
    {% noblank recurse %}
    {% call_template _eoltest2 %}
    {% endnoblank %}
    """)
    recurse_expected = '|\n|\n\n\nX\nX\n'

    TryIt(recurse_source, recurse_expected)

    norecurse_source = textwrap.dedent("""\
    {% noblank %}
    {% call_template _eoltest2 %}
    {% endnoblank %}
    """)

    norecurse_expected = '|\n|\n\n\nX\n\nX\n'

    TryIt(norecurse_source, norecurse_expected)

  def testLiteral(self):
    def TryTestLiteral(language, input_text, expected):
      context = self._GetContext({
          'foo': 'foo\nb"a$r',
          'bar': 'baz',
          'pattern': '\\d{4}-\\d{2}-\\d{2}'})
      lang_node = template_helpers.LanguageNode(language)
      lang_node.render(context)
      context['_LINE_WIDTH'] = 50  # to make expected easier to read
      node = template_helpers.LiteralStringNode(input_text)
      self.assertEquals(expected, node.render(context))

    TryTestLiteral('dart', ['foo', 'bar'], '"foo\\nb\\"a\\$rbaz"')
    TryTestLiteral('java', ['foo'], '"foo\\nb\\"a$r"')
    TryTestLiteral('java', ['bar'], '"baz"')
    TryTestLiteral('java', ['pattern'], '"\\\\d{4}-\\\\d{2}-\\\\d{2}"')
    TryTestLiteral('objc', ['foo'], '@"foo\\nb\\"a$r"')
    TryTestLiteral('php', ['foo', 'bar'], """'foo\nb"a$rbaz'""")

  def testCopyright(self):
    copyright_text = 'MY COPYRIGHT TEXT'
    expected_license_preamble = 'Licensed under the Apache License'
    template = django_template.Template(
        '{% language java %}{% copyright_block %}')
    context = self._GetContext({
        'template_dir': self._TEST_DATA_DIR,
        'api': {},
        })
    text_without_copyright = template.render(context)
    license_pos = text_without_copyright.find(expected_license_preamble)
    self.assertLess(3, license_pos)
    self.assertEquals(-1, text_without_copyright.find(copyright_text))
    context['api']['copyright'] = copyright_text
    text_with_copyright = template.render(context)
    license_pos_with_copyright = text_with_copyright.find(
        expected_license_preamble)
    self.assertLess(license_pos, license_pos_with_copyright)
    copyright_pos = text_with_copyright.find(copyright_text)
    self.assertEquals(license_pos, copyright_pos)

  def testGetArgFromToken(self):
    # This tests indirectly by going through a few tags known to call
    # _GetArgFromToken. That expedient avoids having to create a token stream
    # at a low level.

    # try a good one
    template = django_template.Template('{% camel_case foo %}')
    context = self._GetContext({'foo': 'hello_world'})
    self.assertEquals('HelloWorld', template.render(context))

    # Missing the arg
    for tag in ['language', 'comment_if', 'doc_comment_if']:
      try:
        template = django_template.Template('{%% %s %%}' % tag)
        self.fail('TemplateSyntaxError not raised')
      except django_template.TemplateSyntaxError as e:
        self.assertEquals('tag requires a single argument: %s' % tag, str(e))

  def testCache(self):
    loader = template_helpers.CachingTemplateLoader()
    template_dir = os.path.join(self._TEST_DATA_DIR, 'languages')
    test_path = os.path.join(template_dir, 'php/1.0dev/test.tmpl')
    stable_path = os.path.join(template_dir, 'php/1.0/test.tmpl')
    loader.GetTemplate(test_path, template_dir)
    loader.GetTemplate(stable_path, template_dir)
    self.assertTrue(stable_path in loader._cache)
    self.assertFalse(test_path in loader._cache)

  def testHalt(self):
    # See that it raises the error
    template = django_template.Template('{% halt %}')
    context = self._GetContext({})
    self.assertRaises(
        template_helpers.Halt, template.render, context)
    # But make sure it raises on execution, not parsing. :-)
    template = django_template.Template('{% if false %}{% halt %}{% endif %}OK')
    context = self._GetContext({})
    self.assertEquals('OK', template.render(context))

  def testBool(self):
    source = '{% bool x %}|{% bool y %}'

    def Test(language, x):
      ctxt = self._GetContext({'_LANGUAGE': language, 'x': x, 'y': not x})
      template = django_template.Template(source)
      key = template_helpers._BOOLEAN_LITERALS
      vals = template_helpers._language_defaults[language].get(
          key, template_helpers._defaults[key])
      if x:
        # If x, true precedes false in the output.
        vals = vals[::-1]
      expected = '|'.join(vals)
      self.assertEquals(expected, template.render(ctxt))

    for language in template_helpers._language_defaults:
      for value in (True, False, 'truthy string', ''):
        Test(language, value)

  def testDivChecksum(self):
    source = '<p>This is some test text.</p>'
    context = self._GetContext()
    template = django_template.Template(
        '{% checksummed_div %}'
        'someId'
        '{% divbody %}' + source + '{% endchecksummed_div %}')
    checksum = hashlib.sha1(source).hexdigest()
    expected = ('<div id="someId" checksum="%s">' % checksum +
                source +
                '</div>')
    self.assertEquals(expected, template.render(context))

  def testWrite(self):
    self.name_to_content = {}

    def MyWriter(name, content):
      """Capture the write event."""
      self.name_to_content[name] = content

    template = django_template.Template(
        'a{% write file1 %}foo{% endwrite %}'
        'b{% write file2 %}bar{% endwrite %}')
    context = self._GetContext({
        template_helpers.FILE_WRITER: MyWriter,
        'file1': 'x',
        'file2': 'y',
        })
    self.assertEquals('ab', template.render(context))
    self.assertEquals('foo', self.name_to_content['x'])
    self.assertEquals('bar', self.name_to_content['y'])


class TemplateGlobalsTest(basetest.TestCase):

  def testSetContext(self):
    self.assertIsNone(template_helpers.GetCurrentContext())
    data = {'key': 'value'}
    with template_helpers.SetCurrentContext(data):
      ctxt = template_helpers.GetCurrentContext()
      self.assertIsNotNone(ctxt)
      self.assertEquals('value', ctxt['key'])
    self.assertIsNone(template_helpers.GetCurrentContext())

if __name__ == '__main__':
  basetest.main()
