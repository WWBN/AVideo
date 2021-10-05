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


from google.apputils import basetest
from googleapis.codegen.utilities import convert_size


class ConvertSizeTest(basetest.TestCase):
  """Test for unit conversion."""

  def testConvertSize(self):
    self.assertEquals(None, convert_size.ConvertSize(None))
    self.assertEquals(None, convert_size.ConvertSize('4'))
    self.assertEquals(None, convert_size.ConvertSize('4C'))
    self.assertEquals(4, convert_size.ConvertSize('4B'))
    self.assertEquals(4 * 2 ** 10, convert_size.ConvertSize('4KB'))
    self.assertEquals(12 * 2 ** 20, convert_size.ConvertSize('12MB'))
    self.assertEquals(10 * 2 ** 30, convert_size.ConvertSize('10GB'))


if __name__ == '__main__':
  basetest.main()
