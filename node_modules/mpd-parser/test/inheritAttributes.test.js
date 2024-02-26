import {
  inheritAttributes,
  buildBaseUrls,
  parseCaptionServiceMetadata,
  getSegmentInformation,
  getPeriodStart,
  toEventStream
} from '../src/inheritAttributes';
import { stringToMpdXml } from '../src/stringToMpdXml';
import errors from '../src/errors';
import QUnit from 'qunit';
import { stub } from 'sinon';
import { toPlaylists } from '../src/toPlaylists';
import decodeB64ToUint8Array from '@videojs/vhs-utils/es/decode-b64-to-uint8-array';
import { findChildren } from '../src/utils/xml';

QUnit.module('buildBaseUrls');

QUnit.test('returns reference urls when no BaseURL nodes', function(assert) {
  const reference = [{ baseUrl: 'https://example.com/' }, { baseUrl: 'https://foo.com/' }];

  assert.deepEqual(buildBaseUrls(reference, []), reference, 'returns reference urls');
});

QUnit.test('single reference url with single BaseURL node', function(assert) {
  const reference = [{ baseUrl: 'https://example.com' }];
  const node = [{ textContent: 'bar/' }];
  const expected = [{ baseUrl: 'https://example.com/bar/' }];

  assert.deepEqual(buildBaseUrls(reference, node), expected, 'builds base url');
});

QUnit.test('multiple reference urls with single BaseURL node', function(assert) {
  const reference = [{ baseUrl: 'https://example.com/' }, { baseUrl: 'https://foo.com/' }];
  const node = [{ textContent: 'bar/' }];
  const expected = [{ baseUrl: 'https://example.com/bar/' }, { baseUrl: 'https://foo.com/bar/' }];

  assert.deepEqual(
    buildBaseUrls(reference, node), expected,
    'base url for each reference url'
  );
});

QUnit.test('multiple BaseURL nodes with single reference url', function(assert) {
  const reference = [{ baseUrl: 'https://example.com/' }];
  const nodes = [{ textContent: 'bar/' }, { textContent: 'baz/' }];
  const expected = [{ baseUrl: 'https://example.com/bar/' }, { baseUrl: 'https://example.com/baz/' }];

  assert.deepEqual(buildBaseUrls(reference, nodes), expected, 'base url for each node');
});

QUnit.test('multiple reference urls with multiple BaseURL nodes', function(assert) {
  const reference = [
    { baseUrl: 'https://example.com/' }, { baseUrl: 'https://foo.com/' }, { baseUrl: 'http://example.com' }
  ];
  const nodes =
    [{ textContent: 'bar/' }, { textContent: 'baz/' }, { textContent: 'buzz/' }];
  const expected = [
    { baseUrl: 'https://example.com/bar/' },
    { baseUrl: 'https://example.com/baz/' },
    { baseUrl: 'https://example.com/buzz/' },
    { baseUrl: 'https://foo.com/bar/' },
    { baseUrl: 'https://foo.com/baz/' },
    { baseUrl: 'https://foo.com/buzz/' },
    { baseUrl: 'http://example.com/bar/' },
    { baseUrl: 'http://example.com/baz/' },
    { baseUrl: 'http://example.com/buzz/' }
  ];

  assert.deepEqual(buildBaseUrls(reference, nodes), expected, 'creates all base urls');
});

QUnit.test('absolute BaseURL overwrites reference', function(assert) {
  const reference = [{ baseUrl: 'https://example.com' }];
  const node = [{ textContent: 'https://foo.com/bar/' }];
  const expected = [{ baseUrl: 'https://foo.com/bar/'}];

  assert.deepEqual(
    buildBaseUrls(reference, node), expected,
    'absolute url overwrites reference'
  );
});

QUnit.test('reference attributes are ignored when there is a BaseURL node', function(assert) {
  const reference = [{ baseUrl: 'https://example.com', attributes: [{ name: 'test', value: 'wow' }] }];
  const node = [{ textContent: 'https://foo.com/bar/' }];
  const expected = [{ baseUrl: 'https://foo.com/bar/' }];

  assert.deepEqual(
    buildBaseUrls(reference, node), expected,
    'baseURL attributes are not included'
  );
});

QUnit.test('BasURL attributes are still added with a reference', function(assert) {
  const reference = [{ baseUrl: 'https://example.com' }];
  const node = [{ textContent: 'https://foo.com/bar/', attributes: [{ name: 'test', value: 'wow' }] }];

  const expected = [{ baseUrl: 'https://foo.com/bar/', test: 'wow' }];

  assert.deepEqual(
    buildBaseUrls(reference, node), expected,
    'baseURL attributes are included'
  );
});

QUnit.test('attributes are replaced when both reference and BaseURL have the same attributes', function(assert) {
  const reference = [{ baseUrl: 'https://example.com', attributes: [{ name: 'test', value: 'old' }] }];
  const node = [{ textContent: 'https://foo.com/bar/', attributes: [{ name: 'test', value: 'new' }] }];
  const expected = [{ baseUrl: 'https://foo.com/bar/', test: 'new' }];

  assert.deepEqual(
    buildBaseUrls(reference, node), expected,
    'baseURL attributes are included'
  );
});

QUnit.module('getPeriodStart');

QUnit.test('gets period start when available', function(assert) {
  assert.equal(
    getPeriodStart({
      attributes: { start: 11 },
      priorPeriodAttributes: null,
      mpdType: 'static'
    }),
    11,
    'returned period start value'
  );
});

QUnit.test('gets period start when prior period and duration', function(assert) {
  assert.equal(
    getPeriodStart({
      attributes: {},
      priorPeriodAttributes: { start: 11, duration: 20 },
      mpdType: 'static'
    }),
    31,
    'returned correct period start value'
  );
});

QUnit.test('gets period start when no prior period and static', function(assert) {
  assert.equal(
    getPeriodStart({
      attributes: {},
      priorPeriodAttributes: null,
      mpdType: 'static'
    }),
    0,
    'returned correct period start value'
  );
});

QUnit.test('null when static and prior period but missing attributes', function(assert) {
  assert.equal(
    getPeriodStart({
      attributes: {},
      priorPeriodAttributes: { start: 11 },
      mpdType: 'static'
    }),
    null,
    'null when no duration in prior period'
  );

  assert.equal(
    getPeriodStart({
      attributes: {},
      priorPeriodAttributes: { duration: 20 },
      mpdType: 'static'
    }),
    null,
    'null when no start in prior period'
  );
});

QUnit.test('null when dynamic, no prior period, and no start attribute', function(assert) {
  assert.equal(
    getPeriodStart({
      attributes: {},
      priorPeriodAttributes: null,
      mpdType: 'dyanmic'
    }),
    null,
    'null when no dynamic, no start attribute, and no prior period'
  );
});

QUnit.module('getSegmentInformation');

QUnit.test('undefined Segment information when no Segment nodes', function(assert) {
  const adaptationSet = { childNodes: [] };
  const expected = {};

  assert.deepEqual(
    getSegmentInformation(adaptationSet), expected,
    'undefined segment info'
  );
});

QUnit.test('gets SegmentTemplate attributes', function(assert) {
  const adaptationSet = {
    childNodes: [{
      tagName: 'SegmentTemplate',
      attributes: [{ name: 'media', value: 'video.mp4' }],
      childNodes: []
    }]
  };
  const expected = {
    template: { media: 'video.mp4' }
  };

  assert.deepEqual(
    getSegmentInformation(adaptationSet), expected,
    'SegmentTemplate info'
  );
});

QUnit.test('gets SegmentList attributes', function(assert) {
  const adaptationSet = {
    childNodes: [{
      tagName: 'SegmentList',
      attributes: [{ name: 'duration', value: '10' }],
      childNodes: []
    }]
  };
  const expected = {
    list: {
      duration: 10,
      segmentUrls: [],
      initialization: {}
    }
  };

  assert.deepEqual(
    getSegmentInformation(adaptationSet), expected,
    'SegmentList info'
  );
});

QUnit.test('gets SegmentBase attributes', function(assert) {
  const adaptationSet = {
    childNodes: [{
      tagName: 'SegmentBase',
      attributes: [{ name: 'duration', value: '10' }],
      childNodes: []
    }]
  };
  const expected = {
    base: { duration: 10, initialization: {} }
  };

  assert.deepEqual(
    getSegmentInformation(adaptationSet), expected,
    'SegmentBase info'
  );
});

QUnit.test('gets SegmentTemplate and SegmentTimeline attributes', function(assert) {
  const adaptationSet = {
    childNodes: [{
      tagName: 'SegmentTemplate',
      attributes: [{ name: 'media', value: 'video.mp4' }],
      childNodes: [{
        tagName: 'SegmentTimeline',
        childNodes: [{
          tagName: 'S',
          attributes: [{ name: 'd', value: '10' }]
        }, {
          tagName: 'S',
          attributes: [{ name: 'd', value: '5' }]
        }, {
          tagName: 'S',
          attributes: [{ name: 'd', value: '7' }]
        }]
      }]
    }]
  };
  const expected = {
    template: { media: 'video.mp4' },
    segmentTimeline: [{ d: 10 }, { d: 5 }, { d: 7 }]
  };

  assert.deepEqual(
    getSegmentInformation(adaptationSet), expected,
    'SegmentTemplate and SegmentTimeline info'
  );
});

QUnit.module('caption service metadata');

QUnit.test('parsed 608 metadata', function(assert) {
  const getmd = (value) => ({
    schemeIdUri: 'urn:scte:dash:cc:cea-608:2015',
    value
  });

  const assertServices = (services, expected, message) => {
    if (!services) {
      assert.notOk(expected, message);
      return;
    }

    services.forEach((service, i) => {
      assert.deepEqual(service, expected[i], message);
    });
  };

  assertServices(parseCaptionServiceMetadata({
    schemeIdUri: 'random scheme',
    value: 'CC1'
  }), undefined, 'dont parse incorrect scheme ID for 608');
  assertServices(parseCaptionServiceMetadata(getmd('CC1')), [{
    channel: 'CC1',
    language: 'CC1'
  }], 'CC1');
  assertServices(parseCaptionServiceMetadata(getmd('CC2')), [{
    channel: 'CC2',
    language: 'CC2'
  }], 'CC2');
  assertServices(parseCaptionServiceMetadata(getmd('English')), [{
    channel: undefined,
    language: 'English'
  }], 'English');
  assertServices(parseCaptionServiceMetadata(getmd('CC1;CC2')), [{
    channel: 'CC1',
    language: 'CC1'
  }, {
    channel: 'CC2',
    language: 'CC2'
  }], 'CC1;CC2');
  assertServices(parseCaptionServiceMetadata(getmd('CC1=eng;CC3=swe')), [{
    channel: 'CC1',
    language: 'eng'
  }, {
    channel: 'CC3',
    language: 'swe'
  }], 'CC1=eng;CC3=swe');
  assertServices(parseCaptionServiceMetadata(getmd('CC1=Hello;CC3=World')), [{
    channel: 'CC1',
    language: 'Hello'
  }, {
    channel: 'CC3',
    language: 'World'
  }], 'CC1=Hello;CC3=World');
  assertServices(parseCaptionServiceMetadata(getmd('eng;swe')), [{
    channel: undefined,
    language: 'eng'
  }, {
    channel: undefined,
    language: 'swe'
  }], 'eng;CC3');
});

QUnit.test('parsed 708 metadata', function(assert) {
  const getmd = (value) => ({
    schemeIdUri: 'urn:scte:dash:cc:cea-708:2015',
    value
  });

  const assertServices = (services, expected, message) => {
    if (!services) {
      assert.notOk(expected, message);
      return;
    }

    services.forEach((service, i) => {
      assert.deepEqual(service, expected[i], message);
    });
  };

  assertServices(parseCaptionServiceMetadata({
    schemeIdUri: 'random scheme',
    value: 'eng'
  }), undefined, 'dont parse incorrect scheme for 708');

  assertServices(parseCaptionServiceMetadata(getmd('eng')), [{
    'channel': undefined,
    'language': 'eng',
    'aspectRatio': 1,
    'easyReader': 0,
    '3D': 0
  }], 'simple eng');

  assertServices(parseCaptionServiceMetadata(getmd('eng;swe')), [{
    'channel': undefined,
    'language': 'eng',
    'aspectRatio': 1,
    'easyReader': 0,
    '3D': 0
  }, {
    'channel': undefined,
    'language': 'swe',
    'aspectRatio': 1,
    'easyReader': 0,
    '3D': 0
  }], 'eng;swe');

  assertServices(parseCaptionServiceMetadata(getmd('1=lang:eng;2=lang:swe')), [{
    'channel': 'SERVICE1',
    'language': 'eng',
    'aspectRatio': 1,
    'easyReader': 0,
    '3D': 0
  }, {
    'channel': 'SERVICE2',
    'language': 'swe',
    'aspectRatio': 1,
    'easyReader': 0,
    '3D': 0
  }], '1=lang:eng;2=lang:swe');

  assertServices(parseCaptionServiceMetadata(getmd('1=lang:eng;swe')), [{
    'channel': 'SERVICE1',
    'language': 'eng',
    'aspectRatio': 1,
    'easyReader': 0,
    '3D': 0
  }, {
    'channel': undefined,
    'language': 'swe',
    'aspectRatio': 1,
    'easyReader': 0,
    '3D': 0
  }], 'mixed 1=lang:eng;swe');

  assertServices(parseCaptionServiceMetadata(getmd('1=lang:eng;2=lang:eng,war:1,er:1')), [{
    'channel': 'SERVICE1',
    'language': 'eng',
    'aspectRatio': 1,
    'easyReader': 0,
    '3D': 0
  }, {
    'channel': 'SERVICE2',
    'language': 'eng',
    'aspectRatio': 1,
    'easyReader': 1,
    '3D': 0
  }], '1=lang:eng;2=lang:eng,war:1,er:1');

  assertServices(parseCaptionServiceMetadata(getmd('1=lang:eng,war:0;2=lang:eng,3D:1,er:1')), [{
    'channel': 'SERVICE1',
    'language': 'eng',
    'aspectRatio': 0,
    'easyReader': 0,
    '3D': 0
  }, {
    'channel': 'SERVICE2',
    'language': 'eng',
    'aspectRatio': 1,
    'easyReader': 1,
    '3D': 1
  }], '1=lang:eng,war:0;2=lang:eng,3D:1,er:1');

  assertServices(parseCaptionServiceMetadata(getmd('eng;fre;spa;jpn;deu;swe;kor;lat;zho;heb;rus;ara;hin;por;tur')), [{
    'channel': undefined,
    'language': 'eng',
    'aspectRatio': 1,
    'easyReader': 0,
    '3D': 0
  }, {
    'channel': undefined,
    'language': 'fre',
    'aspectRatio': 1,
    'easyReader': 0,
    '3D': 0
  }, {
    'channel': undefined,
    'language': 'spa',
    'aspectRatio': 1,
    'easyReader': 0,
    '3D': 0
  }, {
    'channel': undefined,
    'language': 'jpn',
    'aspectRatio': 1,
    'easyReader': 0,
    '3D': 0
  }, {
    'channel': undefined,
    'language': 'deu',
    'aspectRatio': 1,
    'easyReader': 0,
    '3D': 0
  }, {
    'channel': undefined,
    'language': 'swe',
    'aspectRatio': 1,
    'easyReader': 0,
    '3D': 0
  }, {
    'channel': undefined,
    'language': 'kor',
    'aspectRatio': 1,
    'easyReader': 0,
    '3D': 0
  }, {
    'channel': undefined,
    'language': 'lat',
    'aspectRatio': 1,
    'easyReader': 0,
    '3D': 0
  }, {
    'channel': undefined,
    'language': 'zho',
    'aspectRatio': 1,
    'easyReader': 0,
    '3D': 0
  }, {
    'channel': undefined,
    'language': 'heb',
    'aspectRatio': 1,
    'easyReader': 0,
    '3D': 0
  }, {
    'channel': undefined,
    'language': 'rus',
    'aspectRatio': 1,
    'easyReader': 0,
    '3D': 0
  }, {
    'channel': undefined,
    'language': 'ara',
    'aspectRatio': 1,
    'easyReader': 0,
    '3D': 0
  }, {
    'channel': undefined,
    'language': 'hin',
    'aspectRatio': 1,
    'easyReader': 0,
    '3D': 0
  }, {
    'channel': undefined,
    'language': 'por',
    'aspectRatio': 1,
    'easyReader': 0,
    '3D': 0
  }, {
    'channel': undefined,
    'language': 'tur',
    'aspectRatio': 1,
    'easyReader': 0,
    '3D': 0
  }], 'make sure that parsing 15 services works');
});

QUnit.module('inheritAttributes');

QUnit.test('needs at least one Period', function(assert) {
  assert.throws(
    () => inheritAttributes(stringToMpdXml('<MPD></MPD>')),
    new RegExp(errors.INVALID_NUMBER_OF_PERIOD)
  );
});

QUnit.test('end to end - basic', function(assert) {
  const NOW = Date.now();

  const actual = inheritAttributes(stringToMpdXml(`
    <MPD mediaPresentationDuration="PT30S" >
      <BaseURL>https://www.example.com/base/</BaseURL>
      <Period>
        <AdaptationSet mimeType="video/mp4" >
          <Role value="main"></Role>
          <SegmentTemplate></SegmentTemplate>
          <Representation
            bandwidth="5000000"
            codecs="avc1.64001e"
            height="404"
            id="test"
            width="720">
          </Representation>
        </AdaptationSet>
        <AdaptationSet mimeType="text/vtt" lang="en">
          <Representation bandwidth="256" id="en">
            <BaseURL>https://example.com/en.vtt</BaseURL>
          </Representation>
        </AdaptationSet>
      </Period>
    </MPD>
  `), { NOW });

  const expected = {
    contentSteeringInfo: null,
    eventStream: [],
    locations: undefined,
    representationInfo: [{
      attributes: {
        bandwidth: 5000000,
        baseUrl: 'https://www.example.com/base/',
        codecs: 'avc1.64001e',
        height: 404,
        id: 'test',
        mediaPresentationDuration: 30,
        mimeType: 'video/mp4',
        periodStart: 0,
        role: {
          value: 'main'
        },
        sourceDuration: 30,
        type: 'static',
        width: 720,
        NOW,
        clientOffset: 0
      },
      segmentInfo: {
        template: {}
      }
    }, {
      attributes: {
        bandwidth: 256,
        baseUrl: 'https://example.com/en.vtt',
        id: 'en',
        lang: 'en',
        mediaPresentationDuration: 30,
        mimeType: 'text/vtt',
        periodStart: 0,
        role: {},
        sourceDuration: 30,
        type: 'static',
        NOW,
        clientOffset: 0
      },
      segmentInfo: {}
    }]
  };

  assert.equal(actual.representationInfo.length, 2);
  assert.deepEqual(actual, expected);
});

QUnit.test('end to end - basic using manifest uri', function(assert) {
  const NOW = Date.now();

  const actual = inheritAttributes(stringToMpdXml(`
    <MPD mediaPresentationDuration="PT30S" >
      <BaseURL>base/</BaseURL>
      <Period>
        <AdaptationSet mimeType="video/mp4" >
          <Role value="main"></Role>
          <SegmentTemplate></SegmentTemplate>
          <Representation
            bandwidth="5000000"
            codecs="avc1.64001e"
            height="404"
            id="test"
            width="720">
          </Representation>
        </AdaptationSet>
        <AdaptationSet mimeType="text/vtt" lang="en">
          <Representation bandwidth="256" id="en">
            <BaseURL>en.vtt</BaseURL>
          </Representation>
        </AdaptationSet>
      </Period>
    </MPD>
  `), { NOW, manifestUri: 'https://www.test.com' });

  const expected = {
    contentSteeringInfo: null,
    eventStream: [],
    locations: undefined,
    representationInfo: [{
      attributes: {
        bandwidth: 5000000,
        baseUrl: 'https://www.test.com/base/',
        codecs: 'avc1.64001e',
        height: 404,
        id: 'test',
        mediaPresentationDuration: 30,
        mimeType: 'video/mp4',
        periodStart: 0,
        role: {
          value: 'main'
        },
        sourceDuration: 30,
        type: 'static',
        width: 720,
        NOW,
        clientOffset: 0
      },
      segmentInfo: {
        template: {}
      }
    }, {
      attributes: {
        bandwidth: 256,
        baseUrl: 'https://www.test.com/base/en.vtt',
        id: 'en',
        lang: 'en',
        mediaPresentationDuration: 30,
        mimeType: 'text/vtt',
        periodStart: 0,
        role: {},
        sourceDuration: 30,
        type: 'static',
        NOW,
        clientOffset: 0
      },
      segmentInfo: {}
    }]
  };

  assert.equal(actual.representationInfo.length, 2);
  assert.deepEqual(actual, expected);
});

QUnit.test('end to end - basic dynamic', function(assert) {
  const NOW = Date.now();

  const actual = inheritAttributes(stringToMpdXml(`
    <MPD type="dyanmic">
      <BaseURL>https://www.example.com/base/</BaseURL>
      <Period start="PT0S">
        <AdaptationSet mimeType="video/mp4">
          <Role value="main"></Role>
          <SegmentTemplate></SegmentTemplate>
          <Representation
            bandwidth="5000000"
            codecs="avc1.64001e"
            height="404"
            id="test"
            width="720">
          </Representation>
        </AdaptationSet>
        <AdaptationSet mimeType="text/vtt" lang="en">
          <Representation bandwidth="256" id="en">
            <BaseURL>https://example.com/en.vtt</BaseURL>
          </Representation>
        </AdaptationSet>
      </Period>
    </MPD>
  `), { NOW });

  const expected = {
    contentSteeringInfo: null,
    eventStream: [],
    locations: undefined,
    representationInfo: [{
      attributes: {
        bandwidth: 5000000,
        baseUrl: 'https://www.example.com/base/',
        codecs: 'avc1.64001e',
        height: 404,
        id: 'test',
        mimeType: 'video/mp4',
        periodStart: 0,
        role: {
          value: 'main'
        },
        sourceDuration: 0,
        type: 'dyanmic',
        width: 720,
        NOW,
        clientOffset: 0
      },
      segmentInfo: {
        template: {}
      }
    }, {
      attributes: {
        bandwidth: 256,
        baseUrl: 'https://example.com/en.vtt',
        id: 'en',
        lang: 'en',
        mimeType: 'text/vtt',
        periodStart: 0,
        role: {},
        sourceDuration: 0,
        type: 'dyanmic',
        NOW,
        clientOffset: 0
      },
      segmentInfo: {}
    }]
  };

  assert.equal(actual.representationInfo.length, 2);
  assert.deepEqual(actual, expected);
});

QUnit.test('end to end - content steering - non resolvable base URLs', function(assert) {
  const NOW = Date.now();

  const actual = inheritAttributes(stringToMpdXml(`
  <MPD type="dyanmic">
    <ContentSteering defaultServiceLocation="beta" queryBeforeStart="false" proxyServerURL="http://127.0.0.1:3455/steer">https://example.com/app/url</ContentSteering>
    <BaseURL serviceLocation="alpha">https://cdn1.example.com/</BaseURL>
    <BaseURL serviceLocation="beta">https://cdn2.example.com/</BaseURL>
    <Period start="PT0S">
      <AdaptationSet mimeType="video/mp4">
        <Role value="main"></Role>
        <SegmentTemplate></SegmentTemplate>
        <Representation
          bandwidth="5000000"
          codecs="avc1.64001e"
          height="404"
          id="test"
          width="720">
        </Representation>
      </AdaptationSet>
      <AdaptationSet mimeType="text/vtt" lang="en">
        <Representation bandwidth="256" id="en">
        <BaseURL>https://example.com/en.vtt</BaseURL>
        </Representation>
      </AdaptationSet>
    </Period>
  </MPD>
`), { NOW, manifestUri: 'https://www.test.com' });

  // Note that we expect to see the `contentSteeringInfo` object set with the
  // proper values. We also expect to see the `serviceLocation` property set to
  // the correct values inside of the correct representations.
  const expected = {
    contentSteeringInfo: {
      defaultServiceLocation: 'beta',
      proxyServerURL: 'http://127.0.0.1:3455/steer',
      queryBeforeStart: false,
      serverURL: 'https://example.com/app/url'
    },
    eventStream: [],
    locations: undefined,
    representationInfo: [
      {
        attributes: {
          NOW,
          bandwidth: 5000000,
          baseUrl: 'https://cdn1.example.com/',
          clientOffset: 0,
          codecs: 'avc1.64001e',
          height: 404,
          id: 'test',
          mimeType: 'video/mp4',
          periodStart: 0,
          role: {
            value: 'main'
          },
          serviceLocation: 'alpha',
          sourceDuration: 0,
          type: 'dyanmic',
          width: 720
        },
        segmentInfo: {
          template: {}
        }
      },
      {
        attributes: {
          NOW,
          bandwidth: 5000000,
          baseUrl: 'https://cdn2.example.com/',
          clientOffset: 0,
          codecs: 'avc1.64001e',
          height: 404,
          id: 'test',
          mimeType: 'video/mp4',
          periodStart: 0,
          role: {
            value: 'main'
          },
          serviceLocation: 'beta',
          sourceDuration: 0,
          type: 'dyanmic',
          width: 720
        },
        segmentInfo: {
          template: {}
        }
      },
      {
        attributes: {
          NOW,
          bandwidth: 256,
          baseUrl: 'https://example.com/en.vtt',
          clientOffset: 0,
          id: 'en',
          lang: 'en',
          mimeType: 'text/vtt',
          periodStart: 0,
          role: {},
          sourceDuration: 0,
          type: 'dyanmic'
        },
        segmentInfo: {}
      },
      {
        attributes: {
          NOW,
          bandwidth: 256,
          baseUrl: 'https://example.com/en.vtt',
          clientOffset: 0,
          id: 'en',
          lang: 'en',
          mimeType: 'text/vtt',
          periodStart: 0,
          role: {},
          sourceDuration: 0,
          type: 'dyanmic'
        },
        segmentInfo: {}
      }
    ]
  };

  assert.equal(actual.representationInfo.length, 4);
  assert.deepEqual(actual, expected);
});

QUnit.test('end to end - content steering - resolvable base URLs', function(assert) {
  const NOW = Date.now();

  const actual = inheritAttributes(stringToMpdXml(`
  <MPD type="dyanmic">
    <ContentSteering defaultServiceLocation="beta" queryBeforeStart="false" proxyServerURL="http://127.0.0.1:3455/steer">https://example.com/app/url</ContentSteering>
    <BaseURL serviceLocation="alpha">https://cdn1.example.com/</BaseURL>
    <BaseURL serviceLocation="beta">https://cdn2.example.com/</BaseURL>
    <Period start="PT0S">
      <AdaptationSet mimeType="video/mp4">
        <Role value="main"></Role>
        <SegmentTemplate></SegmentTemplate>
        <Representation
          bandwidth="5000000"
          codecs="avc1.64001e"
          height="404"
          id="test"
          width="720">
        </Representation>
        <BaseURL>/video</BaseURL>
      </AdaptationSet>
      <AdaptationSet mimeType="text/vtt" lang="en">
        <Representation bandwidth="256" id="en">
        <BaseURL>/vtt</BaseURL>
        </Representation>
      </AdaptationSet>
    </Period>
  </MPD>
`), { NOW, manifestUri: 'https://www.test.com' });

  // Note that we expect to see the `contentSteeringInfo` object set with the
  // proper values. We also expect to see the `serviceLocation` property set to
  // the correct values inside of the correct representations.
  //
  // Also note that some of the representations have '/video' appended
  // to the end of the baseUrls
  const expected = {
    contentSteeringInfo: {
      defaultServiceLocation: 'beta',
      proxyServerURL: 'http://127.0.0.1:3455/steer',
      queryBeforeStart: false,
      serverURL: 'https://example.com/app/url'
    },
    eventStream: [],
    locations: undefined,
    representationInfo: [
      {
        attributes: {
          NOW,
          bandwidth: 5000000,
          baseUrl: 'https://cdn1.example.com/video',
          clientOffset: 0,
          codecs: 'avc1.64001e',
          height: 404,
          id: 'test',
          mimeType: 'video/mp4',
          periodStart: 0,
          role: {
            value: 'main'
          },
          serviceLocation: 'alpha',
          sourceDuration: 0,
          type: 'dyanmic',
          width: 720
        },
        segmentInfo: {
          template: {}
        }
      },
      {
        attributes: {
          NOW,
          bandwidth: 5000000,
          baseUrl: 'https://cdn2.example.com/video',
          clientOffset: 0,
          codecs: 'avc1.64001e',
          height: 404,
          id: 'test',
          mimeType: 'video/mp4',
          periodStart: 0,
          role: {
            value: 'main'
          },
          serviceLocation: 'beta',
          sourceDuration: 0,
          type: 'dyanmic',
          width: 720
        },
        segmentInfo: {
          template: {}
        }
      },
      {
        attributes: {
          NOW,
          bandwidth: 256,
          baseUrl: 'https://cdn1.example.com/vtt',
          clientOffset: 0,
          id: 'en',
          lang: 'en',
          mimeType: 'text/vtt',
          periodStart: 0,
          role: {},
          serviceLocation: 'alpha',
          sourceDuration: 0,
          type: 'dyanmic'
        },
        segmentInfo: {}
      },
      {
        attributes: {
          NOW,
          bandwidth: 256,
          baseUrl: 'https://cdn2.example.com/vtt',
          clientOffset: 0,
          id: 'en',
          lang: 'en',
          mimeType: 'text/vtt',
          periodStart: 0,
          role: {},
          serviceLocation: 'beta',
          sourceDuration: 0,
          type: 'dyanmic'
        },
        segmentInfo: {}
      }
    ]
  };

  assert.equal(actual.representationInfo.length, 4);
  assert.deepEqual(actual, expected);
});

QUnit.test('Too many content steering tags sends a warning to the eventHandler', function(assert) {
  const handlerStub = stub();
  const NOW = Date.now();

  inheritAttributes(stringToMpdXml(`
    <MPD type="dyanmic">
      <ContentSteering defaultServiceLocation="alpha" queryBeforeStart="false" proxyServerURL="http://127.0.0.1:3455/steer">https://example.com/app/url</ContentSteering>
      <ContentSteering defaultServiceLocation="beta" queryBeforeStart="false" proxyServerURL="http://127.0.0.1:3455/steer">https://example.com/app/url</ContentSteering>
      <BaseURL serviceLocation="alpha">https://cdn1.example.com/</BaseURL>
      <BaseURL serviceLocation="beta">https://cdn2.example.com/</BaseURL>
      <Period start="PT0S">
        <AdaptationSet mimeType="video/mp4">
          <Role value="main"></Role>
          <SegmentTemplate></SegmentTemplate>
          <Representation
            bandwidth="5000000"
            codecs="avc1.64001e"
            height="404"
            id="test"
            width="720">
          </Representation>
        </AdaptationSet>
        <AdaptationSet mimeType="text/vtt" lang="en">
          <Representation bandwidth="256" id="en">
          <BaseURL>/video</BaseURL>
          </Representation>
        </AdaptationSet>
      </Period>
    </MPD>
  `), { NOW, manifestUri: 'https://www.test.com', eventHandler: handlerStub });

  assert.ok(handlerStub.calledWith({
    type: 'warn',
    message: 'The MPD manifest should contain no more than one ContentSteering tag'
  }));
});

QUnit.test('end to end - basic multiperiod', function(assert) {
  const NOW = Date.now();

  // no start time attributes on either period, should be inferred
  const actual = inheritAttributes(stringToMpdXml(`
    <MPD mediaPresentationDuration="PT60S" >
      <BaseURL>https://www.example.com/base/</BaseURL>
      <Period duration="PT30S">
        <AdaptationSet mimeType="video/mp4" >
          <Role value="main"></Role>
          <SegmentTemplate></SegmentTemplate>
          <Representation
            bandwidth="5000000"
            codecs="avc1.64001e"
            height="404"
            id="test"
            width="720">
          </Representation>
        </AdaptationSet>
      </Period>
      <Period>
        <AdaptationSet mimeType="video/mp4" >
          <Role value="main"></Role>
          <SegmentTemplate></SegmentTemplate>
          <Representation
            bandwidth="5000000"
            codecs="avc1.64001e"
            height="404"
            id="test"
            width="720">
          </Representation>
        </AdaptationSet>
      </Period>
    </MPD>
  `), { NOW });

  const expected = {
    contentSteeringInfo: null,
    eventStream: [],
    locations: undefined,
    representationInfo: [{
      attributes: {
        bandwidth: 5000000,
        baseUrl: 'https://www.example.com/base/',
        codecs: 'avc1.64001e',
        height: 404,
        id: 'test',
        mediaPresentationDuration: 60,
        mimeType: 'video/mp4',
        periodDuration: 30,
        // inferred start
        periodStart: 0,
        role: {
          value: 'main'
        },
        sourceDuration: 60,
        type: 'static',
        width: 720,
        NOW,
        clientOffset: 0
      },
      segmentInfo: {
        template: {}
      }
    }, {
      attributes: {
        bandwidth: 5000000,
        baseUrl: 'https://www.example.com/base/',
        codecs: 'avc1.64001e',
        height: 404,
        id: 'test',
        mediaPresentationDuration: 60,
        mimeType: 'video/mp4',
        // inferred start
        periodStart: 30,
        role: {
          value: 'main'
        },
        sourceDuration: 60,
        type: 'static',
        width: 720,
        NOW,
        clientOffset: 0
      },
      segmentInfo: {
        template: {}
      }
    }]
  };

  assert.equal(actual.representationInfo.length, 2);
  assert.deepEqual(actual, expected);
});

QUnit.test('end to end - inherits BaseURL from all levels', function(assert) {
  const NOW = Date.now();

  const actual = inheritAttributes(stringToMpdXml(`
    <MPD mediaPresentationDuration="PT30S" >
      <BaseURL>https://www.example.com/base/</BaseURL>
      <Period>
        <BaseURL>foo/</BaseURL>
        <AdaptationSet mimeType="video/mp4" >
          <BaseURL>bar/</BaseURL>
          <Role value="main"></Role>
          <SegmentTemplate></SegmentTemplate>
          <Representation
            bandwidth="5000000"
            codecs="avc1.64001e"
            height="404"
            id="test"
            width="720">
            <BaseURL>buzz/</BaseURL>
          </Representation>
        </AdaptationSet>
        <AdaptationSet mimeType="text/vtt" lang="en">
          <Representation bandwidth="256" id="en">
            <BaseURL>https://example.com/en.vtt</BaseURL>
          </Representation>
        </AdaptationSet>
      </Period>
    </MPD>
  `), { NOW });

  const expected = {
    contentSteeringInfo: null,
    eventStream: [],
    locations: undefined,
    representationInfo: [{
      attributes: {
        bandwidth: 5000000,
        baseUrl: 'https://www.example.com/base/foo/bar/buzz/',
        clientOffset: 0,
        codecs: 'avc1.64001e',
        height: 404,
        id: 'test',
        mediaPresentationDuration: 30,
        mimeType: 'video/mp4',
        periodStart: 0,
        role: {
          value: 'main'
        },
        sourceDuration: 30,
        type: 'static',
        width: 720,
        NOW
      },
      segmentInfo: {
        template: {}
      }
    }, {
      attributes: {
        bandwidth: 256,
        baseUrl: 'https://example.com/en.vtt',
        id: 'en',
        lang: 'en',
        mediaPresentationDuration: 30,
        mimeType: 'text/vtt',
        periodStart: 0,
        role: {},
        sourceDuration: 30,
        type: 'static',
        NOW,
        clientOffset: 0
      },
      segmentInfo: { }
    }]
  };

  assert.equal(actual.representationInfo.length, 2);
  assert.deepEqual(actual, expected);
});

QUnit.test('end to end - alternate BaseURLs', function(assert) {
  const NOW = Date.now();
  const actual = inheritAttributes(stringToMpdXml(`
    <MPD mediaPresentationDuration= "PT30S"  >
      <BaseURL>https://www.example.com/base/</BaseURL>
      <BaseURL>https://www.test.com/base/</BaseURL>
      <Period>
        <AdaptationSet mimeType= "video/mp4"  >
          <BaseURL>segments/</BaseURL>
          <BaseURL>media/</BaseURL>
          <Role value= "main" ></Role>
          <SegmentTemplate></SegmentTemplate>
          <Representation
            bandwidth= "5000000"
            codecs= "avc1.64001e"
            height= "404"
            id= "test"
            width= "720" >
          </Representation>
        </AdaptationSet>
        <AdaptationSet mimeType= "text/vtt"  lang= "en" >
          <Representation bandwidth= "256"  id= "en" >
            <BaseURL>https://example.com/en.vtt</BaseURL>
          </Representation>
        </AdaptationSet>
      </Period>
    </MPD>
  `), { NOW });

  const expected = {
    contentSteeringInfo: null,
    eventStream: [],
    locations: undefined,
    representationInfo: [{
      attributes: {
        bandwidth: 5000000,
        baseUrl: 'https://www.example.com/base/segments/',
        codecs: 'avc1.64001e',
        height: 404,
        id: 'test',
        mediaPresentationDuration: 30,
        mimeType: 'video/mp4',
        periodStart: 0,
        role: {
          value: 'main'
        },
        sourceDuration: 30,
        type: 'static',
        width: 720,
        NOW,
        clientOffset: 0
      },
      segmentInfo: {
        template: {}
      }
    }, {
      attributes: {
        bandwidth: 5000000,
        baseUrl: 'https://www.example.com/base/media/',
        codecs: 'avc1.64001e',
        height: 404,
        id: 'test',
        mediaPresentationDuration: 30,
        mimeType: 'video/mp4',
        periodStart: 0,
        role: {
          value: 'main'
        },
        sourceDuration: 30,
        type: 'static',
        width: 720,
        NOW,
        clientOffset: 0
      },
      segmentInfo: {
        template: {}
      }
    }, {
      attributes: {
        bandwidth: 5000000,
        baseUrl: 'https://www.test.com/base/segments/',
        codecs: 'avc1.64001e',
        height: 404,
        id: 'test',
        mediaPresentationDuration: 30,
        mimeType: 'video/mp4',
        periodStart: 0,
        role: {
          value: 'main'
        },
        sourceDuration: 30,
        type: 'static',
        width: 720,
        NOW,
        clientOffset: 0
      },
      segmentInfo: {
        template: {}
      }
    }, {
      attributes: {
        bandwidth: 5000000,
        baseUrl: 'https://www.test.com/base/media/',
        codecs: 'avc1.64001e',
        height: 404,
        id: 'test',
        mediaPresentationDuration: 30,
        mimeType: 'video/mp4',
        periodStart: 0,
        role: {
          value: 'main'
        },
        sourceDuration: 30,
        type: 'static',
        width: 720,
        NOW,
        clientOffset: 0
      },
      segmentInfo: {
        template: {}
      }
    }, {
      attributes: {
        bandwidth: 256,
        baseUrl: 'https://example.com/en.vtt',
        id: 'en',
        lang: 'en',
        mediaPresentationDuration: 30,
        mimeType: 'text/vtt',
        periodStart: 0,
        role: {},
        sourceDuration: 30,
        type: 'static',
        NOW,
        clientOffset: 0
      },
      segmentInfo: {}
    }, {
      attributes: {
        bandwidth: 256,
        baseUrl: 'https://example.com/en.vtt',
        id: 'en',
        lang: 'en',
        mediaPresentationDuration: 30,
        mimeType: 'text/vtt',
        periodStart: 0,
        role: {},
        sourceDuration: 30,
        type: 'static',
        NOW,
        clientOffset: 0
      },
      segmentInfo: {}
    }]
  };

  assert.equal(actual.representationInfo.length, 6);
  assert.deepEqual(actual, expected);
});

QUnit.test(
  ' End to End test for checking support of segments in representation',
  function(assert) {
    const NOW = Date.now();
    const actual = inheritAttributes(stringToMpdXml(`
    <MPD mediaPresentationDuration= "PT30S"  >
      <BaseURL>https://www.example.com/base/</BaseURL>
      <Period>
        <AdaptationSet mimeType= "video/mp4"  >
          <Role value= "main" ></Role>
          <SegmentBase indexRangeExact= "true"  indexRange= "820-2087" >
              <Initialization range= "0-987" />
          </SegmentBase>

          <Representation
            mimeType= "video/mp6"
            bandwidth= "5000000"
            codecs= "avc1.64001e"
            height= "404"
            id= "test"
            width= "720" >
            <SegmentBase>
              <Initialization range= "0-567" />
            </SegmentBase>
          </Representation>
          <Representation
            height= "545" >
          </Representation>
        </AdaptationSet>
        <AdaptationSet mimeType= "text/vtt"  lang= "en" >
          <Representation bandwidth= "256"  id= "en" >
            <BaseURL>https://example.com/en.vtt</BaseURL>
          </Representation>
        </AdaptationSet>
      </Period>
    </MPD>
  `), { NOW });

    const expected = {
      contentSteeringInfo: null,
      eventStream: [],
      locations: undefined,
      representationInfo: [{
        attributes: {
          bandwidth: 5000000,
          baseUrl: 'https://www.example.com/base/',
          codecs: 'avc1.64001e',
          height: 404,
          id: 'test',
          mediaPresentationDuration: 30,
          mimeType: 'video/mp6',
          periodStart: 0,
          role: {
            value: 'main'
          },
          sourceDuration: 30,
          type: 'static',
          width: 720,
          NOW,
          clientOffset: 0
        },
        segmentInfo: {
          base: {
            indexRange: '820-2087',
            indexRangeExact: 'true',
            initialization: {
              range: '0-567'
            }
          }
        }
      }, {
        attributes: {
          baseUrl: 'https://www.example.com/base/',
          mediaPresentationDuration: 30,
          mimeType: 'video/mp4',
          periodStart: 0,
          height: 545,
          role: {
            value: 'main'
          },
          sourceDuration: 30,
          type: 'static',
          NOW,
          clientOffset: 0
        },
        segmentInfo: {
          base: {
            indexRange: '820-2087',
            indexRangeExact: 'true',
            initialization: {
              range: '0-987'
            }
          }
        }
      }, {
        attributes: {
          bandwidth: 256,
          baseUrl: 'https://example.com/en.vtt',
          id: 'en',
          lang: 'en',
          mediaPresentationDuration: 30,
          mimeType: 'text/vtt',
          periodStart: 0,
          role: {},
          sourceDuration: 30,
          type: 'static',
          NOW,
          clientOffset: 0
        },
        segmentInfo: {}
      }]
    };

    assert.equal(actual.representationInfo.length, 3);
    assert.deepEqual(actual, expected);
  }
);

QUnit.test(
  ' End to End test for checking support of segments in period ',
  function(assert) {
    const NOW = Date.now();
    const actual = inheritAttributes(stringToMpdXml(`
    <MPD mediaPresentationDuration= "PT30S"  >
      <BaseURL>https://www.example.com/base/</BaseURL>
      <Period duration= "PT0H4M40.414S" >
        <SegmentBase indexRangeExact= "false"  indexRange= "9999" >
           <Initialization range= "0-1111" />
        </SegmentBase>
        <AdaptationSet mimeType= "video/mp4"  >
          <Role value= "main" ></Role>
          <Representation
            mimeType= "video/mp6"
            bandwidth= "5000000"
            codecs= "avc1.64001e"
            height= "404"
            id= "test"
            width= "720" >
          </Representation>
          <Representation
            height= "545" >
          </Representation>
        </AdaptationSet>
        <AdaptationSet mimeType= "text/vtt"  lang= "en" >
          <Representation bandwidth= "256"  id= "en" >
            <BaseURL>https://example.com/en.vtt</BaseURL>
          </Representation>
        </AdaptationSet>
      </Period>
    </MPD>
  `), { NOW });

    const expected = {
      contentSteeringInfo: null,
      eventStream: [],
      locations: undefined,
      representationInfo: [{
        attributes: {
          bandwidth: 5000000,
          baseUrl: 'https://www.example.com/base/',
          codecs: 'avc1.64001e',
          height: 404,
          id: 'test',
          mediaPresentationDuration: 30,
          mimeType: 'video/mp6',
          periodDuration: 280.414,
          periodStart: 0,
          role: {
            value: 'main'
          },
          sourceDuration: 30,
          type: 'static',
          width: 720,
          NOW,
          clientOffset: 0
        },
        segmentInfo: {
          base: {
            indexRange: '9999',
            indexRangeExact: 'false',
            initialization: {
              range: '0-1111'
            }
          }
        }
      }, {
        attributes: {
          baseUrl: 'https://www.example.com/base/',
          mediaPresentationDuration: 30,
          mimeType: 'video/mp4',
          periodDuration: 280.414,
          periodStart: 0,
          height: 545,
          role: {
            value: 'main'
          },
          sourceDuration: 30,
          type: 'static',
          NOW,
          clientOffset: 0
        },
        segmentInfo: {
          base: {
            indexRange: '9999',
            indexRangeExact: 'false',
            initialization: {
              range: '0-1111'
            }
          }
        }
      }, {
        attributes: {
          bandwidth: 256,
          baseUrl: 'https://example.com/en.vtt',
          id: 'en',
          lang: 'en',
          mediaPresentationDuration: 30,
          mimeType: 'text/vtt',
          periodDuration: 280.414,
          periodStart: 0,
          role: {},
          sourceDuration: 30,
          type: 'static',
          NOW,
          clientOffset: 0
        },
        segmentInfo: {
          base: {
            indexRange: '9999',
            indexRangeExact: 'false',
            initialization: {
              range: '0-1111'
            }
          }
        }
      }]
    };

    assert.equal(actual.representationInfo.length, 3);
    assert.deepEqual(actual, expected);
  }
);

QUnit.test(
  ' End to End test for checking support of Segments in Adaptation set',
  function(assert) {
    const NOW = Date.now();
    const actual = inheritAttributes(stringToMpdXml(`
    <MPD mediaPresentationDuration= "PT30S"  >
      <BaseURL>https://www.example.com/base/</BaseURL>
      <Period>
        <AdaptationSet mimeType= "video/mp4"  >
          <Role value= "main" ></Role>
          <SegmentBase indexRange= "1212"  indexRangeExact= "true" >
           <Initialization range= "0-8888"  />
          </SegmentBase>
          <Representation
            mimeType= "video/mp6"
            bandwidth= "5000000"
            codecs= "avc1.64001e"
            height= "404"
            id= "test"
            width= "720" >
          </Representation>
          <Representation
            height= "545" >
          </Representation>
        </AdaptationSet>
        <AdaptationSet mimeType= "text/vtt"  lang= "en" >
          <Representation bandwidth= "256"  id= "en" >
            <BaseURL>https://example.com/en.vtt</BaseURL>
          </Representation>
        </AdaptationSet>
      </Period>
    </MPD>
  `), { NOW });

    const expected = {
      contentSteeringInfo: null,
      eventStream: [],
      locations: undefined,
      representationInfo: [{
        attributes: {
          bandwidth: 5000000,
          baseUrl: 'https://www.example.com/base/',
          codecs: 'avc1.64001e',
          height: 404,
          id: 'test',
          mediaPresentationDuration: 30,
          mimeType: 'video/mp6',
          periodStart: 0,
          role: {
            value: 'main'
          },
          sourceDuration: 30,
          type: 'static',
          width: 720,
          NOW,
          clientOffset: 0
        },
        segmentInfo: {
          base: {
            indexRange: '1212',
            indexRangeExact: 'true',
            initialization: {
              range: '0-8888'

            }
          }
        }
      }, {
        attributes: {
          baseUrl: 'https://www.example.com/base/',
          mediaPresentationDuration: 30,
          mimeType: 'video/mp4',
          periodStart: 0,
          height: 545,
          role: {
            value: 'main'
          },
          sourceDuration: 30,
          type: 'static',
          NOW,
          clientOffset: 0
        },
        segmentInfo: {
          base: {
            indexRange: '1212',
            indexRangeExact: 'true',
            initialization: {
              range: '0-8888'
            }
          }
        }
      }, {
        attributes: {
          bandwidth: 256,
          baseUrl: 'https://example.com/en.vtt',
          id: 'en',
          lang: 'en',
          mediaPresentationDuration: 30,
          mimeType: 'text/vtt',
          periodStart: 0,
          role: {},
          sourceDuration: 30,
          type: 'static',
          NOW,
          clientOffset: 0
        },
        segmentInfo: {}
      }]
    };

    assert.equal(actual.representationInfo.length, 3);
    assert.deepEqual(actual, expected);
  }
);

// Although according to the Spec, at most one set of Segment information should be
// present at each level, this test would still handle the case and prevent errors if
// multiple set of segment information are present at any particular level.

QUnit.test(
  'Test for checking use of only one set of Segment Information when multiple are present',
  function(assert) {
    const NOW = Date.now();
    const actual = toPlaylists(inheritAttributes(stringToMpdXml(`
    <MPD mediaPresentationDuration= "PT30S"  >
      <BaseURL>https://www.example.com/base</BaseURL>
      <Period>
        <AdaptationSet
          mimeType= "video/mp4"
          segmentAlignment= "true"
          startWithSAP= "1"
          lang= "es" >
          <Role value= "main" ></Role>
          <SegmentTemplate
            duration= "95232"
            initialization= "$RepresentationID$/es/init.m4f"
            media= "$RepresentationID$/es/$Number$.m4f"
            startNumber= "0"
            timescale= "48000" >
          </SegmentTemplate>
          <SegmentList timescale= "1000"  duration= "1000" >
            <RepresentationIndex sourceURL= "representation-index-low" />
            <SegmentURL media= "low/segment-1.ts" />
            <SegmentURL media= "low/segment-2.ts" />
            <SegmentURL media= "low/segment-3.ts" />
            <SegmentURL media= "low/segment-4.ts" />
            <SegmentURL media= "low/segment-5.ts" />
            <SegmentURL media= "low/segment-6.ts" />
          </SegmentList>
          <Representation
            mimeType= "video/mp6"
            bandwidth= "5000000"
            codecs= "avc1.64001e"
            height= "404"
            id= "125000"
            width= "720" >
          </Representation>
          <Representation
            height= "545"
            id="125000" >
          </Representation>
        </AdaptationSet>
      </Period>
    </MPD>
  `), { NOW }).representationInfo);

    const expected = [{
      attributes: {
        NOW,
        bandwidth: 5000000,
        baseUrl: 'https://www.example.com/base',
        duration: 1.984,
        codecs: 'avc1.64001e',
        height: 404,
        id: '125000',
        lang: 'es',
        mediaPresentationDuration: 30,
        mimeType: 'video/mp6',
        periodStart: 0,
        startNumber: 0,
        timescale: 48000,
        role: {
          value: 'main'
        },
        clientOffset: 0,
        initialization: {
          sourceURL: '$RepresentationID$/es/init.m4f'
        },
        media: '$RepresentationID$/es/$Number$.m4f',
        segmentAlignment: 'true',
        sourceDuration: 30,
        type: 'static',
        width: 720,
        startWithSAP: '1'
      },
      segments: [{
        duration: 1.984,
        map: {
          resolvedUri: 'https://www.example.com/125000/es/init.m4f',
          uri: '125000/es/init.m4f'
        },
        resolvedUri: 'https://www.example.com/125000/es/0.m4f',
        timeline: 0,
        uri: '125000/es/0.m4f',
        number: 0,
        presentationTime: 0
      }, {
        duration: 1.984,
        map: {
          resolvedUri: 'https://www.example.com/125000/es/init.m4f',
          uri: '125000/es/init.m4f'
        },
        resolvedUri: 'https://www.example.com/125000/es/1.m4f',
        timeline: 0,
        uri: '125000/es/1.m4f',
        number: 1,
        presentationTime: 1.984
      }, {
        duration: 1.984,
        map: {
          resolvedUri: 'https://www.example.com/125000/es/init.m4f',
          uri: '125000/es/init.m4f'
        },
        resolvedUri: 'https://www.example.com/125000/es/2.m4f',
        timeline: 0,
        uri: '125000/es/2.m4f',
        number: 2,
        presentationTime: 3.968
      }, {
        duration: 1.984,
        map: {
          resolvedUri: 'https://www.example.com/125000/es/init.m4f',
          uri: '125000/es/init.m4f'
        },
        resolvedUri: 'https://www.example.com/125000/es/3.m4f',
        timeline: 0,
        uri: '125000/es/3.m4f',
        number: 3,
        presentationTime: 5.952
      }, {
        duration: 1.984,
        map: {
          resolvedUri: 'https://www.example.com/125000/es/init.m4f',
          uri: '125000/es/init.m4f'
        },
        resolvedUri: 'https://www.example.com/125000/es/4.m4f',
        timeline: 0,
        uri: '125000/es/4.m4f',
        number: 4,
        presentationTime: 7.936
      }, {
        duration: 1.984,
        map: {
          resolvedUri: 'https://www.example.com/125000/es/init.m4f',
          uri: '125000/es/init.m4f'
        },
        resolvedUri: 'https://www.example.com/125000/es/5.m4f',
        timeline: 0,
        uri: '125000/es/5.m4f',
        number: 5,
        presentationTime: 9.92
      }, {
        duration: 1.984,
        map: {
          resolvedUri: 'https://www.example.com/125000/es/init.m4f',
          uri: '125000/es/init.m4f'
        },
        resolvedUri: 'https://www.example.com/125000/es/6.m4f',
        timeline: 0,
        uri: '125000/es/6.m4f',
        number: 6,
        presentationTime: 11.904
      }, {
        duration: 1.984,
        map: {
          resolvedUri: 'https://www.example.com/125000/es/init.m4f',
          uri: '125000/es/init.m4f'
        },
        resolvedUri: 'https://www.example.com/125000/es/7.m4f',
        timeline: 0,
        uri: '125000/es/7.m4f',
        number: 7,
        presentationTime: 13.888
      }, {
        duration: 1.984,
        map: {
          resolvedUri: 'https://www.example.com/125000/es/init.m4f',
          uri: '125000/es/init.m4f'
        },
        resolvedUri: 'https://www.example.com/125000/es/8.m4f',
        timeline: 0,
        uri: '125000/es/8.m4f',
        number: 8,
        presentationTime: 15.872
      }, {
        duration: 1.984,
        map: {
          resolvedUri: 'https://www.example.com/125000/es/init.m4f',
          uri: '125000/es/init.m4f'
        },
        resolvedUri: 'https://www.example.com/125000/es/9.m4f',
        timeline: 0,
        uri: '125000/es/9.m4f',
        number: 9,
        presentationTime: 17.856
      }, {
        duration: 1.984,
        map: {
          resolvedUri: 'https://www.example.com/125000/es/init.m4f',
          uri: '125000/es/init.m4f'
        },
        resolvedUri: 'https://www.example.com/125000/es/10.m4f',
        timeline: 0,
        uri: '125000/es/10.m4f',
        number: 10,
        presentationTime: 19.84
      }, {
        duration: 1.984,
        map: {
          resolvedUri: 'https://www.example.com/125000/es/init.m4f',
          uri: '125000/es/init.m4f'
        },
        resolvedUri: 'https://www.example.com/125000/es/11.m4f',
        timeline: 0,
        uri: '125000/es/11.m4f',
        number: 11,
        presentationTime: 21.824
      }, {
        duration: 1.984,
        map: {
          resolvedUri: 'https://www.example.com/125000/es/init.m4f',
          uri: '125000/es/init.m4f'
        },
        resolvedUri: 'https://www.example.com/125000/es/12.m4f',
        timeline: 0,
        uri: '125000/es/12.m4f',
        number: 12,
        presentationTime: 23.808
      }, {
        duration: 1.984,
        map: {
          resolvedUri: 'https://www.example.com/125000/es/init.m4f',
          uri: '125000/es/init.m4f'
        },
        resolvedUri: 'https://www.example.com/125000/es/13.m4f',
        timeline: 0,
        uri: '125000/es/13.m4f',
        number: 13,
        presentationTime: 25.792
      }, {
        duration: 1.984,
        map: {
          resolvedUri: 'https://www.example.com/125000/es/init.m4f',
          uri: '125000/es/init.m4f'
        },
        resolvedUri: 'https://www.example.com/125000/es/14.m4f',
        timeline: 0,
        uri: '125000/es/14.m4f',
        number: 14,
        presentationTime: 27.776
      }, {
        duration: 0.240000000000002,
        map: {
          resolvedUri: 'https://www.example.com/125000/es/init.m4f',
          uri: '125000/es/init.m4f'
        },
        resolvedUri: 'https://www.example.com/125000/es/15.m4f',
        timeline: 0,
        uri: '125000/es/15.m4f',
        number: 15,
        presentationTime: 29.76
      }]
    }, {
      attributes: {
        NOW,
        baseUrl: 'https://www.example.com/base',
        duration: 1.984,
        lang: 'es',
        height: 545,
        id: '125000',
        mediaPresentationDuration: 30,
        mimeType: 'video/mp4',
        periodStart: 0,
        role: {
          value: 'main'
        },
        segmentAlignment: 'true',
        sourceDuration: 30,
        type: 'static',
        startWithSAP: '1',
        clientOffset: 0,
        initialization: {
          sourceURL: '$RepresentationID$/es/init.m4f'
        },
        media: '$RepresentationID$/es/$Number$.m4f',
        startNumber: 0,
        timescale: 48000
      },
      segments: [{
        duration: 1.984,
        map: {
          resolvedUri: 'https://www.example.com/125000/es/init.m4f',
          uri: '125000/es/init.m4f'
        },
        resolvedUri: 'https://www.example.com/125000/es/0.m4f',
        timeline: 0,
        uri: '125000/es/0.m4f',
        number: 0,
        presentationTime: 0
      }, {
        duration: 1.984,
        map: {
          resolvedUri: 'https://www.example.com/125000/es/init.m4f',
          uri: '125000/es/init.m4f'
        },
        resolvedUri: 'https://www.example.com/125000/es/1.m4f',
        timeline: 0,
        uri: '125000/es/1.m4f',
        number: 1,
        presentationTime: 1.984
      }, {
        duration: 1.984,
        map: {
          resolvedUri: 'https://www.example.com/125000/es/init.m4f',
          uri: '125000/es/init.m4f'
        },
        resolvedUri: 'https://www.example.com/125000/es/2.m4f',
        timeline: 0,
        uri: '125000/es/2.m4f',
        number: 2,
        presentationTime: 3.968
      }, {
        duration: 1.984,
        map: {
          resolvedUri: 'https://www.example.com/125000/es/init.m4f',
          uri: '125000/es/init.m4f'
        },
        resolvedUri: 'https://www.example.com/125000/es/3.m4f',
        timeline: 0,
        uri: '125000/es/3.m4f',
        number: 3,
        presentationTime: 5.952
      }, {
        duration: 1.984,
        map: {
          resolvedUri: 'https://www.example.com/125000/es/init.m4f',
          uri: '125000/es/init.m4f'
        },
        resolvedUri: 'https://www.example.com/125000/es/4.m4f',
        timeline: 0,
        uri: '125000/es/4.m4f',
        number: 4,
        presentationTime: 7.936
      }, {
        duration: 1.984,
        map: {
          resolvedUri: 'https://www.example.com/125000/es/init.m4f',
          uri: '125000/es/init.m4f'
        },
        resolvedUri: 'https://www.example.com/125000/es/5.m4f',
        timeline: 0,
        uri: '125000/es/5.m4f',
        number: 5,
        presentationTime: 9.92
      }, {
        duration: 1.984,
        map: {
          resolvedUri: 'https://www.example.com/125000/es/init.m4f',
          uri: '125000/es/init.m4f'
        },
        resolvedUri: 'https://www.example.com/125000/es/6.m4f',
        timeline: 0,
        uri: '125000/es/6.m4f',
        number: 6,
        presentationTime: 11.904
      }, {
        duration: 1.984,
        map: {
          resolvedUri: 'https://www.example.com/125000/es/init.m4f',
          uri: '125000/es/init.m4f'
        },
        resolvedUri: 'https://www.example.com/125000/es/7.m4f',
        timeline: 0,
        uri: '125000/es/7.m4f',
        number: 7,
        presentationTime: 13.888
      }, {
        duration: 1.984,
        map: {
          resolvedUri: 'https://www.example.com/125000/es/init.m4f',
          uri: '125000/es/init.m4f'
        },
        resolvedUri: 'https://www.example.com/125000/es/8.m4f',
        timeline: 0,
        uri: '125000/es/8.m4f',
        number: 8,
        presentationTime: 15.872
      }, {
        duration: 1.984,
        map: {
          resolvedUri: 'https://www.example.com/125000/es/init.m4f',
          uri: '125000/es/init.m4f'
        },
        resolvedUri: 'https://www.example.com/125000/es/9.m4f',
        timeline: 0,
        uri: '125000/es/9.m4f',
        number: 9,
        presentationTime: 17.856
      }, {
        duration: 1.984,
        map: {
          resolvedUri: 'https://www.example.com/125000/es/init.m4f',
          uri: '125000/es/init.m4f'
        },
        resolvedUri: 'https://www.example.com/125000/es/10.m4f',
        timeline: 0,
        uri: '125000/es/10.m4f',
        number: 10,
        presentationTime: 19.84
      }, {
        duration: 1.984,
        map: {
          resolvedUri: 'https://www.example.com/125000/es/init.m4f',
          uri: '125000/es/init.m4f'
        },
        resolvedUri: 'https://www.example.com/125000/es/11.m4f',
        timeline: 0,
        uri: '125000/es/11.m4f',
        number: 11,
        presentationTime: 21.824
      }, {
        duration: 1.984,
        map: {
          resolvedUri: 'https://www.example.com/125000/es/init.m4f',
          uri: '125000/es/init.m4f'
        },
        resolvedUri: 'https://www.example.com/125000/es/12.m4f',
        timeline: 0,
        uri: '125000/es/12.m4f',
        number: 12,
        presentationTime: 23.808
      }, {
        duration: 1.984,
        map: {
          resolvedUri: 'https://www.example.com/125000/es/init.m4f',
          uri: '125000/es/init.m4f'
        },
        resolvedUri: 'https://www.example.com/125000/es/13.m4f',
        timeline: 0,
        uri: '125000/es/13.m4f',
        number: 13,
        presentationTime: 25.792
      }, {
        duration: 1.984,
        map: {
          resolvedUri: 'https://www.example.com/125000/es/init.m4f',
          uri: '125000/es/init.m4f'
        },
        resolvedUri: 'https://www.example.com/125000/es/14.m4f',
        timeline: 0,
        uri: '125000/es/14.m4f',
        number: 14,
        presentationTime: 27.776
      }, {
        duration: 0.240000000000002,
        map: {
          resolvedUri: 'https://www.example.com/125000/es/init.m4f',
          uri: '125000/es/init.m4f'
        },
        resolvedUri: 'https://www.example.com/125000/es/15.m4f',
        timeline: 0,
        uri: '125000/es/15.m4f',
        number: 15,
        presentationTime: 29.76
      }]
    }];

    assert.equal(actual.length, 2);
    assert.deepEqual(actual, expected);
  }
);

// Although the Spec states that if SegmentTemplate or SegmentList is present on one
// level of the hierarchy the other one shall not be present on any lower level, this
// test would still handle the case if both are present in the hierarchy and would
// prevent throwing errors.

QUnit.test('Test to check use of either Segment Template or Segment List when both are' +
' present in the hierarchy', function(assert) {
  const NOW = Date.now();
  const actual = toPlaylists(inheritAttributes(stringToMpdXml(`
    <MPD mediaPresentationDuration= "PT30S"  >
      <BaseURL>https://www.example.com/base</BaseURL>
      <Period>
        <AdaptationSet
          mimeType= "video/mp4"
          segmentAlignment= "true"
          startWithSAP= "1"
          lang= "es" >
          <Role value= "main" ></Role>
          <SegmentTemplate
            duration= "95232"
            initialization= "$RepresentationID$/es/init.m4f"
            media= "$RepresentationID$/es/$Number$.m4f"
            startNumber= "0"
            timescale= "48000" >
          </SegmentTemplate>
          <Representation
            mimeType= "video/mp6"
            bandwidth= "5000000"
            codecs= "avc1.64001e"
            height= "404"
            id= "125000"
            width= "720" >
            <SegmentList timescale= "1000"  duration= "1000" >
              <RepresentationIndex sourceURL= "representation-index-low" />
              <SegmentURL media= "low/segment-1.ts" />
              <SegmentURL media= "low/segment-2.ts" />
              <SegmentURL media= "low/segment-3.ts" />
              <SegmentURL media= "low/segment-4.ts" />
              <SegmentURL media= "low/segment-5.ts" />
              <SegmentURL media= "low/segment-6.ts" />
            </SegmentList>
          </Representation>
        </AdaptationSet>
      </Period>
    </MPD>
  `), { NOW }).representationInfo);

  const expected = [{
    attributes: {
      NOW,
      clientOffset: 0,
      initialization: {
        sourceURL: '$RepresentationID$/es/init.m4f'
      },
      media: '$RepresentationID$/es/$Number$.m4f',
      bandwidth: 5000000,
      baseUrl: 'https://www.example.com/base',
      duration: 1.984,
      codecs: 'avc1.64001e',
      height: 404,
      id: '125000',
      lang: 'es',
      mediaPresentationDuration: 30,
      mimeType: 'video/mp6',
      periodStart: 0,
      role: {
        value: 'main'
      },
      segmentAlignment: 'true',
      sourceDuration: 30,
      type: 'static',
      width: 720,
      startWithSAP: '1',
      startNumber: 0,
      timescale: 48000
    },
    segments: [{
      duration: 1.984,
      map: {
        resolvedUri: 'https://www.example.com/125000/es/init.m4f',
        uri: '125000/es/init.m4f'
      },
      resolvedUri: 'https://www.example.com/125000/es/0.m4f',
      timeline: 0,
      uri: '125000/es/0.m4f',
      number: 0,
      presentationTime: 0
    }, {
      duration: 1.984,
      map: {
        resolvedUri: 'https://www.example.com/125000/es/init.m4f',
        uri: '125000/es/init.m4f'
      },
      resolvedUri: 'https://www.example.com/125000/es/1.m4f',
      timeline: 0,
      uri: '125000/es/1.m4f',
      number: 1,
      presentationTime: 1.984
    }, {
      duration: 1.984,
      map: {
        resolvedUri: 'https://www.example.com/125000/es/init.m4f',
        uri: '125000/es/init.m4f'
      },
      resolvedUri: 'https://www.example.com/125000/es/2.m4f',
      timeline: 0,
      uri: '125000/es/2.m4f',
      number: 2,
      presentationTime: 3.968
    }, {
      duration: 1.984,
      map: {
        resolvedUri: 'https://www.example.com/125000/es/init.m4f',
        uri: '125000/es/init.m4f'
      },
      resolvedUri: 'https://www.example.com/125000/es/3.m4f',
      timeline: 0,
      uri: '125000/es/3.m4f',
      number: 3,
      presentationTime: 5.952
    }, {
      duration: 1.984,
      map: {
        resolvedUri: 'https://www.example.com/125000/es/init.m4f',
        uri: '125000/es/init.m4f'
      },
      resolvedUri: 'https://www.example.com/125000/es/4.m4f',
      timeline: 0,
      uri: '125000/es/4.m4f',
      number: 4,
      presentationTime: 7.936
    }, {
      duration: 1.984,
      map: {
        resolvedUri: 'https://www.example.com/125000/es/init.m4f',
        uri: '125000/es/init.m4f'
      },
      resolvedUri: 'https://www.example.com/125000/es/5.m4f',
      timeline: 0,
      uri: '125000/es/5.m4f',
      number: 5,
      presentationTime: 9.92
    }, {
      duration: 1.984,
      map: {
        resolvedUri: 'https://www.example.com/125000/es/init.m4f',
        uri: '125000/es/init.m4f'
      },
      resolvedUri: 'https://www.example.com/125000/es/6.m4f',
      timeline: 0,
      uri: '125000/es/6.m4f',
      number: 6,
      presentationTime: 11.904
    }, {
      duration: 1.984,
      map: {
        resolvedUri: 'https://www.example.com/125000/es/init.m4f',
        uri: '125000/es/init.m4f'
      },
      resolvedUri: 'https://www.example.com/125000/es/7.m4f',
      timeline: 0,
      uri: '125000/es/7.m4f',
      number: 7,
      presentationTime: 13.888
    }, {
      duration: 1.984,
      map: {
        resolvedUri: 'https://www.example.com/125000/es/init.m4f',
        uri: '125000/es/init.m4f'
      },
      resolvedUri: 'https://www.example.com/125000/es/8.m4f',
      timeline: 0,
      uri: '125000/es/8.m4f',
      number: 8,
      presentationTime: 15.872
    }, {
      duration: 1.984,
      map: {
        resolvedUri: 'https://www.example.com/125000/es/init.m4f',
        uri: '125000/es/init.m4f'
      },
      resolvedUri: 'https://www.example.com/125000/es/9.m4f',
      timeline: 0,
      uri: '125000/es/9.m4f',
      number: 9,
      presentationTime: 17.856
    }, {
      duration: 1.984,
      map: {
        resolvedUri: 'https://www.example.com/125000/es/init.m4f',
        uri: '125000/es/init.m4f'
      },
      resolvedUri: 'https://www.example.com/125000/es/10.m4f',
      timeline: 0,
      uri: '125000/es/10.m4f',
      number: 10,
      presentationTime: 19.84
    }, {
      duration: 1.984,
      map: {
        resolvedUri: 'https://www.example.com/125000/es/init.m4f',
        uri: '125000/es/init.m4f'
      },
      resolvedUri: 'https://www.example.com/125000/es/11.m4f',
      timeline: 0,
      uri: '125000/es/11.m4f',
      number: 11,
      presentationTime: 21.824
    }, {
      duration: 1.984,
      map: {
        resolvedUri: 'https://www.example.com/125000/es/init.m4f',
        uri: '125000/es/init.m4f'
      },
      resolvedUri: 'https://www.example.com/125000/es/12.m4f',
      timeline: 0,
      uri: '125000/es/12.m4f',
      number: 12,
      presentationTime: 23.808
    }, {
      duration: 1.984,
      map: {
        resolvedUri: 'https://www.example.com/125000/es/init.m4f',
        uri: '125000/es/init.m4f'
      },
      resolvedUri: 'https://www.example.com/125000/es/13.m4f',
      timeline: 0,
      uri: '125000/es/13.m4f',
      number: 13,
      presentationTime: 25.792
    }, {
      duration: 1.984,
      map: {
        resolvedUri: 'https://www.example.com/125000/es/init.m4f',
        uri: '125000/es/init.m4f'
      },
      resolvedUri: 'https://www.example.com/125000/es/14.m4f',
      timeline: 0,
      uri: '125000/es/14.m4f',
      number: 14,
      presentationTime: 27.776
    }, {
      duration: 0.240000000000002,
      map: {
        resolvedUri: 'https://www.example.com/125000/es/init.m4f',
        uri: '125000/es/init.m4f'
      },
      resolvedUri: 'https://www.example.com/125000/es/15.m4f',
      timeline: 0,
      uri: '125000/es/15.m4f',
      number: 15,
      presentationTime: 29.76
    }]
  }];

  assert.equal(actual.length, 1);
  assert.deepEqual(actual, expected);
});

QUnit.test('keySystem info for representation - lowercase UUIDs', function(assert) {
  const NOW = Date.now();

  const widevinePsshB64 = 'AAAANHBzc2gAAAAA7e+LqXnWSs6jyCfc1R0h7QAAABQIARIQCHJ4bvnnRl+jok5bDvj6RQ==';
  const playreadyPsshB64 = 'AAAB5HBzc2gAAAAAmgTweZhAQoarkuZb4IhflQAAAcTEAQAAAQABALoBPABXAFIATQBIAEUAQQBEAEUAUgAgAHgAbQBsAG4AcwA9ACIAaAB0AHQAcAA6AC8ALwBzAGMAaABlAG0AYQBzAC4AbQBpAGMAcgBvAHMAbwBmAHQALgBjAG8AbQAvAEQAUgBNAC8AMgAwADAANwAvADAAMwAvAFAAbABhAHkAUgBlAGEAZAB5AEgAZQBhAGQAZQByACIAIAB2AGUAcgBzAGkAbwBuAD0AIgA0AC4AMAAuADAALgAwACIAPgA8AEQAQQBUAEEAPgA8AFAAUgBPAFQARQBDAFQASQBOAEYATwA+ADwASwBFAFkATABFAE4APgAxADYAPAAvAEsARQBZAEwARQBOAD4APABBAEwARwBJAEQAPgBBAEUAUwBDAFQAUgA8AC8AQQBMAEcASQBEAD4APAAvAFAAUgBPAFQARQBDAFQASQBOAEYATwA+ADwASwBJAEQAPgBiAG4AaAB5AEMATwBmADUAWAAwAGEAagBvAGsANQBiAEQAdgBqADYAUgBRAD0APQA8AC8ASwBJAEQAPgA8AC8ARABBAFQAQQA+ADwALwBXAFIATQBIAEUAQQBEAEUAUgA+AA==';

  const widevinePsshBytes = decodeB64ToUint8Array(widevinePsshB64);
  const playreadyPsshBytes = decodeB64ToUint8Array(playreadyPsshB64);

  // Content protection info from dash.js demo
  const actual = inheritAttributes(stringToMpdXml(`
    <MPD mediaPresentationDuration="PT30S" xmlns:cenc="urn:mpeg:cenc:2013">
      <BaseURL>https://www.example.com/base/</BaseURL>
      <Period>
        <AdaptationSet mimeType="video/mp4" >
          <ContentProtection schemeIdUri="urn:mpeg:dash:mp4protection:2011" value="cenc" cenc:default_KID="0872786e-f9e7-465f-a3a2-4e5b0ef8fa45" />
			    <ContentProtection value="MSPR 2.0" schemeIdUri="urn:uuid:9a04f079-9840-4286-ab92-e65be0885f95">
				    <cenc:pssh>AAAB5HBzc2gAAAAAmgTweZhAQoarkuZb4IhflQAAAcTEAQAAAQABALoBPABXAFIATQBIAEUAQQBEAEUAUgAgAHgAbQBsAG4AcwA9ACIAaAB0AHQAcAA6AC8ALwBzAGMAaABlAG0AYQBzAC4AbQBpAGMAcgBvAHMAbwBmAHQALgBjAG8AbQAvAEQAUgBNAC8AMgAwADAANwAvADAAMwAvAFAAbABhAHkAUgBlAGEAZAB5AEgAZQBhAGQAZQByACIAIAB2AGUAcgBzAGkAbwBuAD0AIgA0AC4AMAAuADAALgAwACIAPgA8AEQAQQBUAEEAPgA8AFAAUgBPAFQARQBDAFQASQBOAEYATwA+ADwASwBFAFkATABFAE4APgAxADYAPAAvAEsARQBZAEwARQBOAD4APABBAEwARwBJAEQAPgBBAEUAUwBDAFQAUgA8AC8AQQBMAEcASQBEAD4APAAvAFAAUgBPAFQARQBDAFQASQBOAEYATwA+ADwASwBJAEQAPgBiAG4AaAB5AEMATwBmADUAWAAwAGEAagBvAGsANQBiAEQAdgBqADYAUgBRAD0APQA8AC8ASwBJAEQAPgA8AC8ARABBAFQAQQA+ADwALwBXAFIATQBIAEUAQQBEAEUAUgA+AA==</cenc:pssh>
				    <pro xmlns="urn:microsoft:playready">xAEAAAEAAQC6ATwAVwBSAE0ASABFAEEARABFAFIAIAB4AG0AbABuAHMAPQAiAGgAdAB0AHAAOgAvAC8AcwBjAGgAZQBtAGEAcwAuAG0AaQBjAHIAbwBzAG8AZgB0AC4AYwBvAG0ALwBEAFIATQAvADIAMAAwADcALwAwADMALwBQAGwAYQB5AFIAZQBhAGQAeQBIAGUAYQBkAGUAcgAiACAAdgBlAHIAcwBpAG8AbgA9ACIANAAuADAALgAwAC4AMAAiAD4APABEAEEAVABBAD4APABQAFIATwBUAEUAQwBUAEkATgBGAE8APgA8AEsARQBZAEwARQBOAD4AMQA2ADwALwBLAEUAWQBMAEUATgA+ADwAQQBMAEcASQBEAD4AQQBFAFMAQwBUAFIAPAAvAEEATABHAEkARAA+ADwALwBQAFIATwBUAEUAQwBUAEkATgBGAE8APgA8AEsASQBEAD4AYgBuAGgAeQBDAE8AZgA1AFgAMABhAGoAbwBrADUAYgBEAHYAagA2AFIAUQA9AD0APAAvAEsASQBEAD4APAAvAEQAQQBUAEEAPgA8AC8AVwBSAE0ASABFAEEARABFAFIAPgA=</pro>
			    </ContentProtection>
			    <ContentProtection value="Widevine" schemeIdUri="urn:uuid:edef8ba9-79d6-4ace-a3c8-27dcd51d21ed">
				    <cenc:pssh>AAAANHBzc2gAAAAA7e+LqXnWSs6jyCfc1R0h7QAAABQIARIQCHJ4bvnnRl+jok5bDvj6RQ==</cenc:pssh>
			    </ContentProtection>
          <Role value="main"></Role>
          <SegmentTemplate></SegmentTemplate>
          <Representation
            bandwidth="5000000"
            codecs="avc1.64001e"
            height="404"
            id="test"
            width="720">
          </Representation>
        </AdaptationSet>
      </Period>
    </MPD>
  `), { NOW });

  // inconsistent quoting because of quote-props
  const expected = {
    contentSteeringInfo: null,
    eventStream: [],
    locations: undefined,
    representationInfo: [{
      attributes: {
        'bandwidth': 5000000,
        'baseUrl': 'https://www.example.com/base/',
        'codecs': 'avc1.64001e',
        'contentProtection': {
          'com.microsoft.playready': {
            attributes: {
              schemeIdUri: 'urn:uuid:9a04f079-9840-4286-ab92-e65be0885f95',
              value: 'MSPR 2.0'
            },
            pssh: playreadyPsshBytes
          },
          'com.widevine.alpha': {
            attributes: {
              schemeIdUri: 'urn:uuid:edef8ba9-79d6-4ace-a3c8-27dcd51d21ed',
              value: 'Widevine'
            },
            pssh: widevinePsshBytes
          },
          'mp4protection': {
            attributes: {
              'cenc:default_KID': '0872786e-f9e7-465f-a3a2-4e5b0ef8fa45',
              'schemeIdUri': 'urn:mpeg:dash:mp4protection:2011',
              'value': 'cenc'
            }
          }
        },
        'height': 404,
        'id': 'test',
        'mediaPresentationDuration': 30,
        'mimeType': 'video/mp4',
        'periodStart': 0,
        'role': {
          value: 'main'
        },
        'sourceDuration': 30,
        'type': 'static',
        'width': 720,
        NOW,
        'clientOffset': 0,
        'xmlns:cenc': 'urn:mpeg:cenc:2013'
      },
      segmentInfo: {
        template: {}
      }
    }]
  };

  assert.equal(actual.representationInfo.length, 1);
  assert.deepEqual(actual, expected);
});

QUnit.test('keySystem info for representation - uppercase UUIDs', function(assert) {
  const NOW = Date.now();

  const widevinePsshB64 = 'AAAANHBzc2gAAAAA7e+LqXnWSs6jyCfc1R0h7QAAABQIARIQCHJ4bvnnRl+jok5bDvj6RQ==';
  const playreadyPsshB64 = 'AAAB5HBzc2gAAAAAmgTweZhAQoarkuZb4IhflQAAAcTEAQAAAQABALoBPABXAFIATQBIAEUAQQBEAEUAUgAgAHgAbQBsAG4AcwA9ACIAaAB0AHQAcAA6AC8ALwBzAGMAaABlAG0AYQBzAC4AbQBpAGMAcgBvAHMAbwBmAHQALgBjAG8AbQAvAEQAUgBNAC8AMgAwADAANwAvADAAMwAvAFAAbABhAHkAUgBlAGEAZAB5AEgAZQBhAGQAZQByACIAIAB2AGUAcgBzAGkAbwBuAD0AIgA0AC4AMAAuADAALgAwACIAPgA8AEQAQQBUAEEAPgA8AFAAUgBPAFQARQBDAFQASQBOAEYATwA+ADwASwBFAFkATABFAE4APgAxADYAPAAvAEsARQBZAEwARQBOAD4APABBAEwARwBJAEQAPgBBAEUAUwBDAFQAUgA8AC8AQQBMAEcASQBEAD4APAAvAFAAUgBPAFQARQBDAFQASQBOAEYATwA+ADwASwBJAEQAPgBiAG4AaAB5AEMATwBmADUAWAAwAGEAagBvAGsANQBiAEQAdgBqADYAUgBRAD0APQA8AC8ASwBJAEQAPgA8AC8ARABBAFQAQQA+ADwALwBXAFIATQBIAEUAQQBEAEUAUgA+AA==';

  const widevinePsshBytes = decodeB64ToUint8Array(widevinePsshB64);
  const playreadyPsshBytes = decodeB64ToUint8Array(playreadyPsshB64);

  // Content protection info from dash.js demo
  const actual = inheritAttributes(stringToMpdXml(`
    <MPD mediaPresentationDuration="PT30S" xmlns:cenc="urn:mpeg:cenc:2013">
      <BaseURL>https://www.example.com/base/</BaseURL>
      <Period>
        <AdaptationSet mimeType="video/mp4" >
          <ContentProtection schemeIdUri="urn:mpeg:dash:mp4protection:2011" value="cenc" cenc:default_KID="0872786E-F9E7-465F-A3A2-4E5B0EF8FA45" />
			    <ContentProtection value="MSPR 2.0" schemeIdUri="urn:uuid:9A04F079-9840-4286-AB92-E65BE0885F95">
				    <cenc:pssh>AAAB5HBzc2gAAAAAmgTweZhAQoarkuZb4IhflQAAAcTEAQAAAQABALoBPABXAFIATQBIAEUAQQBEAEUAUgAgAHgAbQBsAG4AcwA9ACIAaAB0AHQAcAA6AC8ALwBzAGMAaABlAG0AYQBzAC4AbQBpAGMAcgBvAHMAbwBmAHQALgBjAG8AbQAvAEQAUgBNAC8AMgAwADAANwAvADAAMwAvAFAAbABhAHkAUgBlAGEAZAB5AEgAZQBhAGQAZQByACIAIAB2AGUAcgBzAGkAbwBuAD0AIgA0AC4AMAAuADAALgAwACIAPgA8AEQAQQBUAEEAPgA8AFAAUgBPAFQARQBDAFQASQBOAEYATwA+ADwASwBFAFkATABFAE4APgAxADYAPAAvAEsARQBZAEwARQBOAD4APABBAEwARwBJAEQAPgBBAEUAUwBDAFQAUgA8AC8AQQBMAEcASQBEAD4APAAvAFAAUgBPAFQARQBDAFQASQBOAEYATwA+ADwASwBJAEQAPgBiAG4AaAB5AEMATwBmADUAWAAwAGEAagBvAGsANQBiAEQAdgBqADYAUgBRAD0APQA8AC8ASwBJAEQAPgA8AC8ARABBAFQAQQA+ADwALwBXAFIATQBIAEUAQQBEAEUAUgA+AA==</cenc:pssh>
				    <pro xmlns="urn:microsoft:playready">xAEAAAEAAQC6ATwAVwBSAE0ASABFAEEARABFAFIAIAB4AG0AbABuAHMAPQAiAGgAdAB0AHAAOgAvAC8AcwBjAGgAZQBtAGEAcwAuAG0AaQBjAHIAbwBzAG8AZgB0AC4AYwBvAG0ALwBEAFIATQAvADIAMAAwADcALwAwADMALwBQAGwAYQB5AFIAZQBhAGQAeQBIAGUAYQBkAGUAcgAiACAAdgBlAHIAcwBpAG8AbgA9ACIANAAuADAALgAwAC4AMAAiAD4APABEAEEAVABBAD4APABQAFIATwBUAEUAQwBUAEkATgBGAE8APgA8AEsARQBZAEwARQBOAD4AMQA2ADwALwBLAEUAWQBMAEUATgA+ADwAQQBMAEcASQBEAD4AQQBFAFMAQwBUAFIAPAAvAEEATABHAEkARAA+ADwALwBQAFIATwBUAEUAQwBUAEkATgBGAE8APgA8AEsASQBEAD4AYgBuAGgAeQBDAE8AZgA1AFgAMABhAGoAbwBrADUAYgBEAHYAagA2AFIAUQA9AD0APAAvAEsASQBEAD4APAAvAEQAQQBUAEEAPgA8AC8AVwBSAE0ASABFAEEARABFAFIAPgA=</pro>
			    </ContentProtection>
			    <ContentProtection value="Widevine" schemeIdUri="urn:uuid:EDEF8BA9-79D6-4ACE-A3C8-27DCD51D21ED">
				    <cenc:pssh>AAAANHBzc2gAAAAA7e+LqXnWSs6jyCfc1R0h7QAAABQIARIQCHJ4bvnnRl+jok5bDvj6RQ==</cenc:pssh>
			    </ContentProtection>
          <Role value="main"></Role>
          <SegmentTemplate></SegmentTemplate>
          <Representation
            bandwidth="5000000"
            codecs="avc1.64001e"
            height="404"
            id="test"
            width="720">
          </Representation>
        </AdaptationSet>
      </Period>
    </MPD>
  `), { NOW });

  // inconsistent quoting because of quote-props
  const expected = {
    contentSteeringInfo: null,
    eventStream: [],
    locations: undefined,
    representationInfo: [{
      attributes: {
        'bandwidth': 5000000,
        'baseUrl': 'https://www.example.com/base/',
        'codecs': 'avc1.64001e',
        'contentProtection': {
          'com.microsoft.playready': {
            attributes: {
              schemeIdUri: 'urn:uuid:9a04f079-9840-4286-ab92-e65be0885f95',
              value: 'MSPR 2.0'
            },
            pssh: playreadyPsshBytes
          },
          'com.widevine.alpha': {
            attributes: {
              schemeIdUri: 'urn:uuid:edef8ba9-79d6-4ace-a3c8-27dcd51d21ed',
              value: 'Widevine'
            },
            pssh: widevinePsshBytes
          },
          'mp4protection': {
            attributes: {
              'cenc:default_KID': '0872786E-F9E7-465F-A3A2-4E5B0EF8FA45',
              'schemeIdUri': 'urn:mpeg:dash:mp4protection:2011',
              'value': 'cenc'
            }
          }
        },
        'height': 404,
        'id': 'test',
        'mediaPresentationDuration': 30,
        'mimeType': 'video/mp4',
        'periodStart': 0,
        'role': {
          value: 'main'
        },
        'sourceDuration': 30,
        'type': 'static',
        'width': 720,
        NOW,
        'clientOffset': 0,
        'xmlns:cenc': 'urn:mpeg:cenc:2013'
      },
      segmentInfo: {
        template: {}
      }
    }]
  };

  assert.equal(actual.representationInfo.length, 1);
  assert.deepEqual(actual, expected);
});

QUnit.test('gets EventStream data from toEventStream', function(assert) {
  const mpd = stringToMpdXml(`
    <MPD mediaPresentationDuration="PT30S" xmlns:cenc="urn:mpeg:cenc:2013">
      <Period id="dai_pod-0001065804-ad-1" start="PT17738H17M14.156S" duration="PT9.977S">
        <BaseURL>https://www.example.com/base/</BaseURL>
        <SegmentTemplate media="$RepresentationID$/$Number$.mp4" initialization="$RepresentationID$/init.mp4"/>
        <EventStream schemeIdUri="urn:google:dai:2018" timescale="1000" contentEncoding="foo" presentationTimeOffset="1">
          <Event presentationTime="100" duration="0" id="0" messageData="foo"/>
          <Event presentationTime="900" duration="0" id="5" messageData="bar"/>
          <Event presentationTime="1900" duration="0" id="6" messageData="foo_bar"/>
        </EventStream>
      </Period>
    </MPD>`);
  const expected = [
    {
      end: 2.1,
      id: '0',
      messageData: 'foo',
      schemeIdUri: 'urn:google:dai:2018',
      start: 2.1,
      value: undefined,
      contentEncoding: 'foo',
      presentationTimeOffset: 1

    },
    {
      end: 2.9,
      id: '5',
      messageData: 'bar',
      schemeIdUri: 'urn:google:dai:2018',
      start: 2.9,
      value: undefined,
      contentEncoding: 'foo',
      presentationTimeOffset: 1
    },
    {
      end: 3.9,
      id: '6',
      messageData: 'foo_bar',
      schemeIdUri: 'urn:google:dai:2018',
      start: 3.9,
      value: undefined,
      contentEncoding: 'foo',
      presentationTimeOffset: 1
    }
  ];

  const firstPeriod = { node: findChildren(mpd, 'Period')[0], attributes: { start: 2 } };
  const eventStreams = toEventStream(firstPeriod);

  assert.deepEqual(eventStreams, expected, 'toEventStream returns the expected object');
});

QUnit.test('can get EventStream data from toEventStream with no schemeIdUri', function(assert) {
  const mpd = stringToMpdXml(`
    <MPD mediaPresentationDuration="PT30S" xmlns:cenc="urn:mpeg:cenc:2013">
      <Period id="dai_pod-0001065804-ad-1" start="PT17738H17M14.156S" duration="PT9.977S">
        <BaseURL>https://www.example.com/base/</BaseURL>
        <SegmentTemplate media="$RepresentationID$/$Number$.mp4" initialization="$RepresentationID$/init.mp4"/>
        <EventStream timescale="1000">
          <Event presentationTime="100" duration="0" id="0" messageData="foo"/>
          <Event presentationTime="900" duration="0" id="5" messageData="bar"/>
          <Event presentationTime="1900" duration="0" id="6" messageData="foo_bar"/>
        </EventStream>
      </Period>
    </MPD>`);

  const expected = [
    {
      end: 2.1,
      id: '0',
      messageData: 'foo',
      schemeIdUri: undefined,
      start: 2.1,
      value: undefined,
      contentEncoding: undefined,
      presentationTimeOffset: 0

    },
    {
      end: 2.9,
      id: '5',
      messageData: 'bar',
      schemeIdUri: undefined,
      start: 2.9,
      value: undefined,
      contentEncoding: undefined,
      presentationTimeOffset: 0
    },
    {
      end: 3.9,
      id: '6',
      messageData: 'foo_bar',
      schemeIdUri: undefined,
      start: 3.9,
      value: undefined,
      contentEncoding: undefined,
      presentationTimeOffset: 0
    }
  ];

  const firstPeriod = { node: findChildren(mpd, 'Period')[0], attributes: { start: 2} };
  const eventStreams = toEventStream(firstPeriod);

  assert.deepEqual(eventStreams, expected, 'toEventStream returns the expected object');
});

QUnit.test('gets eventStream from inheritAttributes', function(assert) {
  const mpd = stringToMpdXml(`
    <MPD mediaPresentationDuration="PT30S" xmlns:cenc="urn:mpeg:cenc:2013">
      <Period id="dai_pod-0001065804-ad-1" start="PT0H0M14.9S" duration="PT9.977S">
        <BaseURL>https://www.example.com/base/</BaseURL>
        <SegmentTemplate media="$RepresentationID$/$Number$.mp4" initialization="$RepresentationID$/init.mp4"/>
        <EventStream schemeIdUri="urn:google:dai:2018" timescale="1000" value="foo">
          <Event presentationTime="100" duration="0" id="0" messageData="foo"/>
          <Event presentationTime="1100" duration="0" id="5" messageData="bar"/>
          <Event presentationTime="2100" duration="0" id="6" messageData="foo_bar"/>
        </EventStream>
      </Period>
    </MPD>`);
  const expected = {
    contentSteeringInfo: null,
    eventStream: [
      {
        end: 15,
        id: '0',
        messageData: 'foo',
        schemeIdUri: 'urn:google:dai:2018',
        start: 15,
        value: 'foo',
        contentEncoding: undefined,
        presentationTimeOffset: 0
      },
      {
        end: 16,
        id: '5',
        messageData: 'bar',
        schemeIdUri: 'urn:google:dai:2018',
        start: 16,
        value: 'foo',
        contentEncoding: undefined,
        presentationTimeOffset: 0
      },
      {
        end: 17,
        id: '6',
        messageData: 'foo_bar',
        schemeIdUri: 'urn:google:dai:2018',
        start: 17,
        value: 'foo',
        contentEncoding: undefined,
        presentationTimeOffset: 0
      }
    ],
    locations: undefined,
    representationInfo: []
  };

  const eventStreams = inheritAttributes(mpd);

  assert.deepEqual(eventStreams, expected, 'inheritAttributes returns the expected object');
});

QUnit.test('can get EventStream data from toEventStream with data in Event tags', function(assert) {
  const mpd = stringToMpdXml(`
    <MPD mediaPresentationDuration="PT30S" xmlns:cenc="urn:mpeg:cenc:2013">
      <Period id="dai_pod-0001065804-ad-1" start="PT17738H17M14.156S" duration="PT9.977S">
        <BaseURL>https://www.example.com/base/</BaseURL>
        <SegmentTemplate media="$RepresentationID$/$Number$.mp4" initialization="$RepresentationID$/init.mp4"/>
        <EventStream timescale="1000">
          <Event presentationTime="100" duration="0" id="0">foo</Event>
          <Event presentationTime="900" duration="0" id="5">bar</Event>
          <Event presentationTime="1900" duration="0" id="6">foo_bar</Event>
        </EventStream>
      </Period>
    </MPD>`);

  const expected = [
    {
      end: 2.1,
      id: '0',
      messageData: 'foo',
      schemeIdUri: undefined,
      start: 2.1,
      value: undefined,
      contentEncoding: undefined,
      presentationTimeOffset: 0

    },
    {
      end: 2.9,
      id: '5',
      messageData: 'bar',
      schemeIdUri: undefined,
      start: 2.9,
      value: undefined,
      contentEncoding: undefined,
      presentationTimeOffset: 0
    },
    {
      end: 3.9,
      id: '6',
      messageData: 'foo_bar',
      schemeIdUri: undefined,
      start: 3.9,
      value: undefined,
      contentEncoding: undefined,
      presentationTimeOffset: 0
    }
  ];

  const firstPeriod = { node: findChildren(mpd, 'Period')[0], attributes: { start: 2} };
  const eventStreams = toEventStream(firstPeriod);

  assert.deepEqual(eventStreams, expected, 'toEventStream returns the expected object');
});

QUnit.test('gets eventStream from inheritAttributes with data in Event tags', function(assert) {
  const mpd = stringToMpdXml(`
    <MPD mediaPresentationDuration="PT30S" xmlns:cenc="urn:mpeg:cenc:2013">
      <Period id="dai_pod-0001065804-ad-1" start="PT0H0M14.9S" duration="PT9.977S">
        <BaseURL>https://www.example.com/base/</BaseURL>
        <SegmentTemplate media="$RepresentationID$/$Number$.mp4" initialization="$RepresentationID$/init.mp4"/>
        <EventStream schemeIdUri="urn:google:dai:2018" timescale="1000" value="foo">
          <Event presentationTime="100" duration="0" id="0">foo</Event>
          <Event presentationTime="1100" duration="0" id="5">bar</Event>
          <Event presentationTime="2100" duration="0" id="6">foo_bar</Event>
        </EventStream>
      </Period>
    </MPD>`);
  const expected = {
    contentSteeringInfo: null,
    eventStream: [
      {
        end: 15,
        id: '0',
        messageData: 'foo',
        schemeIdUri: 'urn:google:dai:2018',
        start: 15,
        value: 'foo',
        contentEncoding: undefined,
        presentationTimeOffset: 0
      },
      {
        end: 16,
        id: '5',
        messageData: 'bar',
        schemeIdUri: 'urn:google:dai:2018',
        start: 16,
        value: 'foo',
        contentEncoding: undefined,
        presentationTimeOffset: 0
      },
      {
        end: 17,
        id: '6',
        messageData: 'foo_bar',
        schemeIdUri: 'urn:google:dai:2018',
        start: 17,
        value: 'foo',
        contentEncoding: undefined,
        presentationTimeOffset: 0
      }
    ],
    locations: undefined,
    representationInfo: []
  };

  const eventStreams = inheritAttributes(mpd);

  assert.deepEqual(eventStreams, expected, 'inheritAttributes returns the expected object');
});
