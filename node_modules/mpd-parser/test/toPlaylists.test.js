import {
  toPlaylists
} from '../src/toPlaylists';
import QUnit from 'qunit';

QUnit.module('toPlaylists');

QUnit.test('no representations', function(assert) {
  assert.deepEqual(toPlaylists([]), []);
});

QUnit.test('pretty simple', function(assert) {
  const representations = [{
    attributes: {
      baseUrl: 'http://example.com/',
      sourceDuration: 2,
      type: 'static',
      periodStart: 0
    },
    segmentInfo: {
      template: { }
    }
  }];

  const playlists = [{
    attributes: {
      baseUrl: 'http://example.com/',
      periodStart: 0,
      sourceDuration: 2,
      duration: 2,
      type: 'static'
    },
    segments: [{
      uri: '',
      timeline: 0,
      duration: 2,
      resolvedUri: 'http://example.com/',
      map: {
        uri: '',
        resolvedUri: 'http://example.com/'
      },
      number: 1,
      presentationTime: 0
    }]
  }];

  assert.deepEqual(toPlaylists(representations), playlists);
});

QUnit.test('segment base', function(assert) {
  const representations = [{
    attributes: {
      baseUrl: 'http://example.com/',
      periodStart: 0,
      sourceDuration: 2,
      type: 'static'
    },
    segmentInfo: {
      base: true
    }
  }];

  const playlists = [{
    attributes: {
      baseUrl: 'http://example.com/',
      periodStart: 0,
      sourceDuration: 2,
      duration: 2,
      type: 'static'
    },
    segments: [{
      map: {
        resolvedUri: 'http://example.com/',
        uri: ''
      },
      resolvedUri: 'http://example.com/',
      uri: 'http://example.com/',
      timeline: 0,
      duration: 2,
      presentationTime: 0,
      number: 0
    }]
  }];

  assert.deepEqual(toPlaylists(representations), playlists);
});

QUnit.test('playlist with content steering BaseURLs', function(assert) {
  const representations = [
    {
      attributes: {
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
  ];

  const playlists = [{
    attributes: {
      bandwidth: 5000000,
      baseUrl: 'https://cdn1.example.com/',
      clientOffset: 0,
      codecs: 'avc1.64001e',
      duration: 0,
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
    segments: [
      {
        duration: 0,
        map: {
          resolvedUri: 'https://cdn1.example.com/',
          uri: ''
        },
        number: 1,
        presentationTime: 0,
        resolvedUri: 'https://cdn1.example.com/',
        timeline: 0,
        uri: ''
      }
    ]
  }, {
    attributes: {
      bandwidth: 5000000,
      baseUrl: 'https://cdn2.example.com/',
      clientOffset: 0,
      codecs: 'avc1.64001e',
      duration: 0,
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
    segments: [
      {
        duration: 0,
        map: {
          resolvedUri: 'https://cdn2.example.com/',
          uri: ''
        },
        number: 1,
        presentationTime: 0,
        resolvedUri: 'https://cdn2.example.com/',
        timeline: 0,
        uri: ''
      }
    ]
  }, {
    attributes: {
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
    }
  }, {
    attributes: {
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
    }
  }];

  assert.deepEqual(toPlaylists(representations), playlists);
});

QUnit.test('segment base with sidx', function(assert) {
  const representations = [{
    attributes: {
      baseUrl: 'http://example.com/',
      periodStart: 0,
      sourceDuration: 2,
      indexRange: '10-19',
      type: 'static'
    },
    segmentInfo: {
      base: true
    }
  }];

  const playlists = [{
    attributes: {
      baseUrl: 'http://example.com/',
      periodStart: 0,
      sourceDuration: 2,
      duration: 2,
      indexRange: '10-19',
      type: 'static'
    },
    segments: [],
    sidx: {
      map: {
        resolvedUri: 'http://example.com/',
        uri: ''
      },
      resolvedUri: 'http://example.com/',
      uri: 'http://example.com/',
      byterange: {
        offset: 10,
        length: 10
      },
      timeline: 0,
      presentationTime: 0,
      duration: 2,
      number: 0
    }
  }];

  assert.deepEqual(toPlaylists(representations), playlists);
});

QUnit.test('segment list', function(assert) {
  const representations = [{
    attributes: {
      baseUrl: 'http://example.com/',
      duration: 10,
      sourceDuration: 11,
      periodStart: 0,
      type: 'static'
    },
    segmentInfo: {
      list: {
        segmentUrls: [{
          media: '1.fmp4'
        }, {
          media: '2.fmp4'
        }]
      }
    }
  }];

  const playlists = [{
    attributes: {
      baseUrl: 'http://example.com/',
      duration: 10,
      sourceDuration: 11,
      segmentUrls: [{
        media: '1.fmp4'
      }, {
        media: '2.fmp4'
      }],
      periodStart: 0,
      type: 'static'
    },
    segments: [{
      duration: 10,
      map: {
        resolvedUri: 'http://example.com/',
        uri: ''
      },
      resolvedUri: 'http://example.com/1.fmp4',
      timeline: 0,
      presentationTime: 0,
      uri: '1.fmp4',
      number: 1
    }, {
      duration: 1,
      map: {
        resolvedUri: 'http://example.com/',
        uri: ''
      },
      resolvedUri: 'http://example.com/2.fmp4',
      timeline: 0,
      presentationTime: 10,
      uri: '2.fmp4',
      number: 2
    }]
  }];

  assert.deepEqual(toPlaylists(representations), playlists);
});

QUnit.test('presentationTime accounts for presentationTimeOffset', function(assert) {
  const representations = [{
    attributes: {
      baseUrl: 'http://example.com/',
      sourceDuration: 2,
      type: 'static',
      periodStart: 25
    },
    segmentInfo: {
      template: {
        presentationTimeOffset: 100,
        timescale: 4
      }
    }
  }];

  const playlists = [{
    attributes: {
      baseUrl: 'http://example.com/',
      periodStart: 25,
      presentationTimeOffset: 100,
      sourceDuration: 2,
      duration: 2,
      timescale: 4,
      type: 'static'
    },
    segments: [{
      uri: '',
      timeline: 25,
      duration: 2,
      // The presentationTime value should be adjusted based on the presentationTimeOffset
      // and its timescale.
      presentationTime: 0,
      resolvedUri: 'http://example.com/',
      map: {
        uri: '',
        resolvedUri: 'http://example.com/'
      },
      number: 1
    }]
  }];

  assert.deepEqual(toPlaylists(representations), playlists);
});

QUnit.test('playlist with content steering and resolvable BaseURLs', function(assert) {
  const representations = [
    {
      attributes: {
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
      segmentInfo: {
        template: {}
      }
    },
    {
      attributes: {
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
  ];

  const playlists = [
    {
      attributes: {
        bandwidth: 5000000,
        baseUrl: 'https://cdn1.example.com/video',
        clientOffset: 0,
        codecs: 'avc1.64001e',
        duration: 0,
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
      segments: [
        {
          duration: 0,
          map: {
            resolvedUri: 'https://cdn1.example.com/video',
            uri: ''
          },
          number: 1,
          presentationTime: 0,
          resolvedUri: 'https://cdn1.example.com/video',
          timeline: 0,
          uri: ''
        }
      ]
    },
    {
      attributes: {
        bandwidth: 5000000,
        baseUrl: 'https://cdn2.example.com/video',
        clientOffset: 0,
        codecs: 'avc1.64001e',
        duration: 0,
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
      segments: [
        {
          duration: 0,
          map: {
            resolvedUri: 'https://cdn2.example.com/video',
            uri: ''
          },
          number: 1,
          presentationTime: 0,
          resolvedUri: 'https://cdn2.example.com/video',
          timeline: 0,
          uri: ''
        }
      ]
    },
    {
      attributes: {
        bandwidth: 256,
        baseUrl: 'https://cdn1.example.com/vtt',
        clientOffset: 0,
        duration: 0,
        id: 'en',
        lang: 'en',
        mimeType: 'text/vtt',
        periodStart: 0,
        role: {},
        serviceLocation: 'alpha',
        sourceDuration: 0,
        type: 'dyanmic'
      },
      segments: [
        {
          duration: 0,
          map: {
            resolvedUri: 'https://cdn1.example.com/vtt',
            uri: ''
          },
          number: 1,
          presentationTime: 0,
          resolvedUri: 'https://cdn1.example.com/vtt',
          timeline: 0,
          uri: ''
        }
      ]
    },
    {
      attributes: {
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
      }
    }
  ];

  assert.deepEqual(toPlaylists(representations), playlists);
});
