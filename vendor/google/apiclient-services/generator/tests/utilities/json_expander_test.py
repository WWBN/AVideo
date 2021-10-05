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


__author__ = "smulloni@google.com (Jacob Smullyan)"

from google.apputils import basetest
from googleapis.codegen.utilities import json_expander


class JsonExpanderTest(basetest.TestCase):

  def testExpand(self):
    x_val = "foo"
    y_val = "bar"
    d = dict(x=x_val, y=y_val, t1="$x", t2="${y}",
             r={"t": "$x$y", "l": [3, "$x"]})
    expanded = json_expander.ExpandJsonTemplate(d)
    self.assertEquals(x_val, expanded["t1"])
    self.assertEquals(y_val, expanded["t2"])
    self.assertEquals(x_val + y_val, expanded["r"]["t"])
    self.assertEquals(x_val, expanded["r"]["l"][1])

  def testExpandWithAdditionalContext(self):
    y_val = "bar"
    extra = dict(y=y_val)
    d = dict(t="${y}")
    expanded = json_expander.ExpandJsonTemplate(d)
    self.assertNotEquals(y_val, expanded["t"])

    expanded = json_expander.ExpandJsonTemplate(d, extra)
    self.assertEquals(y_val, expanded["t"])

  def testExpandNoSelf(self):
    d = dict(x="aha", t1="$x")
    extra = dict(x="no-no")
    expanded = json_expander.ExpandJsonTemplate(d, extra, use_self=False)
    self.assertEquals("no-no", expanded["t1"])


if __name__ == "__main__":
  basetest.main()
