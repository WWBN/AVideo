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
"""Tests for json_with_comments.py."""

from google.apputils import basetest

from googleapis.codegen.utilities import json_with_comments


class JsonWithCommentsTest(basetest.TestCase):

  SOME_JSON_WITH_COMMENTS = """
    # Garlic and sapphires in the mud
    # Clot the bedded axle-tree.
    {"author": "Timmy",
     "books": [
       # Diet classic
       "The Waist Band"
     ]
    }
    # The End
    """

  JSON_CONTENT = {'author': 'Timmy',
                  'books': ['The Waist Band']}

  def testLineNumbering(self):
    stripped = json_with_comments._StripComments(self.SOME_JSON_WITH_COMMENTS)
    # The stripped version should have the same number of line breaks as the
    # original.
    num_lines = self.SOME_JSON_WITH_COMMENTS.count('\n')
    self.assertEquals(num_lines, stripped.count('\n'))

  def testLoads(self):
    data = json_with_comments.Loads(self.SOME_JSON_WITH_COMMENTS)
    self.assertEquals(self.JSON_CONTENT, data)


if __name__ == '__main__':
  basetest.main()
