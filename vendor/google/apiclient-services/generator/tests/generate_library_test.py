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


import os

from google.apputils import app
import gflags as flags
from google.apputils import basetest
from googleapis.codegen import generate_library

FLAGS = flags.FLAGS


def CallGeneratorMain():
  generate_library.main([])


class GenerateLibraryTest(basetest.TestCase):

  def AssertRaisesContainingText(self, expected_exception, function,
                                 expected_text):
    expected_exception_name = expected_exception.__class__.__name__
    try:
      function()
    except expected_exception as got:
      if str(got).find(expected_text) < 0:
        self.fail('Expected %s in exception. Got: %s' % (expected_text, got))
    except Exception as got:
      self.fail(
          'Unexpected exception thrown (expected %s), Got: %s' % (
              expected_exception_name, got))
    else:
      self.fail('ExpectedException (%s) not thrown' % expected_exception_name)

  def testApiNameOrInputTest(self):
    FLAGS.api_name = None
    FLAGS.input = None
    self.AssertRaisesContainingText(app.UsageError, CallGeneratorMain,
                                    'specify one of --api_name')
    FLAGS.api_name = 'foo'
    FLAGS.input = 'bar'
    self.AssertRaisesContainingText(app.UsageError, CallGeneratorMain,
                                    'specify one of --api_name')

  def testOutputDirOrFile(self):
    FLAGS.api_name = None
    FLAGS.input = 'dummy'

    FLAGS.output_dir = None
    FLAGS.output_file = None
    self.AssertRaisesContainingText(app.UsageError, CallGeneratorMain,
                                    'specify one of --output_dir')
    FLAGS.output_dir = 'foo'
    FLAGS.output_file = 'bar'
    self.AssertRaisesContainingText(app.UsageError, CallGeneratorMain,
                                    'specify one of --output_dir')

  def testApiNameNeedsVersion(self):
    FLAGS.api_name = 'name'
    FLAGS.input = None
    FLAGS.output_dir = None
    FLAGS.output_file = '/dev/null'
    self.AssertRaisesContainingText(app.UsageError, CallGeneratorMain,
                                    'You must specify --api_version')



if __name__ == '__main__':
  basetest.main()
