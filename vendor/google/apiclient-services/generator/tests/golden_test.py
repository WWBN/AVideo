"""Run golden output tests.

The golden tests are a convenient way to make sure that a "small" change
does not break anyone else.
"""

from __future__ import absolute_import
from __future__ import division
from __future__ import print_function

from collections import namedtuple
from google.apputils import basetest
import os
import subprocess
import sys

test_dir = os.path.dirname(os.path.realpath(__file__))
GOLDEN_CASES_DIR = test_dir + '/testdata/golden'
GOLDEN_DISCOVERY_DIR = test_dir + '/testdata/golden_discovery'
CODEGEN_DIR = test_dir + '/../src/googleapis/codegen'
VERBOSE = False

Test = namedtuple('Test', [
    'language',
    'variant',
    'input',
    'options',
    'golden_file'])

class GoldenTest(basetest.TestCase):
  def FindTests(self):
    """Finds golden files and returns Test cases for each."""
    for root, _, files in os.walk(GOLDEN_CASES_DIR):
      print(root)
      print(files)
      path_parts = root.split('/')
      if path_parts[-3] == 'golden':
        language = path_parts[-2]
        variant = path_parts[-1]
        for golden_file in files:
          input, _ = golden_file.split('.')
          options = None
          if input.endswith('_monolithic'):
            input = input[0:-11]
            options = ['--monolithic_source_name=sink']  # pure hackery
          yield Test(
              language = language,
              variant = variant,
              input = input,
              options = options,
              golden_file = os.path.join(root, golden_file))


  def Generate(self, language, variant, input, options, out_file):
    cmd = [
        'python',
        CODEGEN_DIR + '/generate_library.py',
        '--input=%s/%s.json' % (GOLDEN_DISCOVERY_DIR, input),
        '--language=%s' % language,
        '--language_variant=%s' % variant,
        '--output_format=txt',
        '--output_file=%s' % out_file,
    ]
    if options:
      cmd.extend(options)
    try:
      if VERBOSE:
        print('generate cmd: %s' % ' '.join(cmd))
      subprocess.check_call(cmd, stdout=sys.stdout, stderr=sys.stderr)
    except subprocess.CalledProcessError as e:
      msg = '(%s, %s, %s, %s)' % (language, variant, input, options)
      print('FAIL: generate(%s), cmd=[%s]' % (msg, ' '.join(cmd)))
      return False
    return True


  def RunTest(self, test):
    # Fix this
    out_file = '/tmp/%s.new' % test.golden_file.split('/')[-1]
    if self.Generate(test.language, test.variant, test.input, test.options, out_file):
      cmd = ['diff', '--brief', test.golden_file, out_file]
      try:
        subprocess.check_call(cmd, stderr=sys.stderr)
        print('PASS: %s, %s, %s, %s' % (test.language, test.variant, test.input, test.options))
        return True
      except subprocess.CalledProcessError as e:
        print('FAIL: %s' % str(test))
        return False


  def testGolden(self):
    src_path = os.path.join(os.getcwd(), 'src')
    python_path = os.environ.get('PYTHONPATH')
    if python_path:
      os.environ['PYTHONPATH'] = '%s:%s' % (src_path, python_path)
    else:
      os.environ['PYTHONPATH'] = src_path

    allpassed = True
    for test in self.FindTests():
      allpassed = allpassed and self.RunTest(test)

    if not allpassed:
      self.fail('One or more golden tests failed');


if __name__ == '__main__':
  basetest.main()
