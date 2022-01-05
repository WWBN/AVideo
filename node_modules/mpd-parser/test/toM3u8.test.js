import { toM3u8, generateSidxKey } from '../src/toM3u8';
import QUnit from 'qunit';

QUnit.module('toM3u8');

QUnit.test('playlists', function(assert) {
  const input = [{
    attributes: {
      id: '1',
      codecs: 'foo;bar',
      sourceDuration: 100,
      duration: 0,
      bandwidth: 20000,
      periodIndex: 1,
      mimeType: 'audio/mp4',
      type: 'static'
    },
    segments: []
  }, {
    attributes: {
      id: '2',
      codecs: 'foo;bar',
      sourceDuration: 100,
      duration: 0,
      bandwidth: 10000,
      periodIndex: 1,
      mimeType: 'audio/mp4',
      type: 'static'
    },
    segments: []
  }, {
    attributes: {
      sourceDuration: 100,
      id: '1',
      width: 800,
      height: 600,
      codecs: 'foo;bar',
      duration: 0,
      bandwidth: 10000,
      periodIndex: 1,
      mimeType: 'video/mp4',
      type: 'static'
    },
    segments: []
  }, {
    attributes: {
      sourceDuration: 100,
      id: '1',
      bandwidth: 20000,
      periodIndex: 1,
      mimeType: 'text/vtt',
      type: 'static',
      baseUrl: 'https://www.example.com/vtt'
    }
  }, {
    attributes: {
      sourceDuration: 100,
      id: '1',
      bandwidth: 10000,
      periodIndex: 1,
      mimeType: 'text/vtt',
      type: 'static',
      baseUrl: 'https://www.example.com/vtt'
    }
  }];

  const expected = {
    allowCache: true,
    discontinuityStarts: [],
    duration: 100,
    endList: true,
    mediaGroups: {
      AUDIO: {
        audio: {
          main: {
            autoselect: true,
            default: true,
            language: '',
            playlists: [{
              attributes: {
                BANDWIDTH: 20000,
                CODECS: 'foo;bar',
                NAME: '1',
                ['PROGRAM-ID']: 1
              },
              mediaSequence: 1,
              endList: true,
              resolvedUri: '',
              segments: [],
              timeline: 1,
              uri: '',
              targetDuration: 0
            }, {
              attributes: {
                BANDWIDTH: 10000,
                CODECS: 'foo;bar',
                NAME: '2',
                ['PROGRAM-ID']: 1
              },
              mediaSequence: 1,
              endList: true,
              resolvedUri: '',
              segments: [],
              timeline: 1,
              uri: '',
              targetDuration: 0

            }],
            uri: ''
          }
        }
      },
      ['CLOSED-CAPTIONS']: {},
      SUBTITLES: {
        subs: {
          text: {
            autoselect: false,
            default: false,
            language: 'text',
            playlists: [{
              attributes: {
                BANDWIDTH: 20000,
                NAME: '1',
                ['PROGRAM-ID']: 1
              },
              mediaSequence: 0,
              targetDuration: 100,
              endList: true,
              resolvedUri: 'https://www.example.com/vtt',
              segments: [{
                duration: 100,
                resolvedUri: 'https://www.example.com/vtt',
                timeline: 1,
                uri: 'https://www.example.com/vtt',
                number: 0
              }],
              timeline: 1,
              uri: ''
            }, {
              attributes: {
                BANDWIDTH: 10000,
                NAME: '1',
                ['PROGRAM-ID']: 1
              },
              mediaSequence: 0,
              targetDuration: 100,
              endList: true,
              resolvedUri: 'https://www.example.com/vtt',
              segments: [{
                duration: 100,
                resolvedUri: 'https://www.example.com/vtt',
                timeline: 1,
                uri: 'https://www.example.com/vtt',
                number: 0
              }],
              timeline: 1,
              uri: ''
            }],
            uri: ''
          }
        }
      },
      VIDEO: {}
    },
    playlists: [{
      attributes: {
        AUDIO: 'audio',
        SUBTITLES: 'subs',
        BANDWIDTH: 10000,
        CODECS: 'foo;bar',
        NAME: '1',
        ['PROGRAM-ID']: 1,
        RESOLUTION: {
          height: 600,
          width: 800
        }
      },
      endList: true,
      mediaSequence: 1,
      targetDuration: 0,
      resolvedUri: '',
      segments: [],
      timeline: 1,
      uri: ''
    }],
    segments: [],
    uri: ''
  };

  assert.deepEqual(toM3u8(input), expected);
});

QUnit.test('playlists with segments', function(assert) {
  const input = [{
    attributes: {
      id: '1',
      codecs: 'foo;bar',
      duration: 2,
      sourceDuration: 100,
      bandwidth: 20000,
      periodIndex: 1,
      mimeType: 'audio/mp4',
      type: 'static'
    },
    segments: [{
      uri: '',
      timeline: 1,
      duration: 2,
      resolvedUri: '',
      map: {
        uri: '',
        resolvedUri: ''
      },
      number: 1
    }, {
      uri: '',
      timeline: 1,
      duration: 2,
      resolvedUri: '',
      map: {
        uri: '',
        resolvedUri: ''
      },
      number: 2
    }]
  }, {
    attributes: {
      id: '2',
      codecs: 'foo;bar',
      sourceDuration: 100,
      duration: 2,
      bandwidth: 10000,
      periodIndex: 1,
      mimeType: 'audio/mp4',
      type: 'static'
    },
    segments: [{
      uri: '',
      timeline: 1,
      duration: 2,
      resolvedUri: '',
      map: {
        uri: '',
        resolvedUri: ''
      },
      number: 1
    }, {
      uri: '',
      timeline: 1,
      duration: 2,
      resolvedUri: '',
      map: {
        uri: '',
        resolvedUri: ''
      },
      number: 2
    }]
  }, {
    attributes: {
      sourceDuration: 100,
      id: '1',
      width: 800,
      duration: 2,
      height: 600,
      codecs: 'foo;bar',
      bandwidth: 10000,
      periodIndex: 1,
      mimeType: 'video/mp4',
      type: 'static'
    },
    segments: [{
      uri: '',
      timeline: 1,
      duration: 2,
      resolvedUri: '',
      map: {
        uri: '',
        resolvedUri: ''
      },
      number: 1
    }, {
      uri: '',
      timeline: 1,
      duration: 2,
      resolvedUri: '',
      map: {
        uri: '',
        resolvedUri: ''
      },
      number: 2
    }]
  }, {
    attributes: {
      sourceDuration: 100,
      id: '1',
      duration: 2,
      bandwidth: 20000,
      periodIndex: 1,
      mimeType: 'text/vtt',
      type: 'static',
      baseUrl: 'https://www.example.com/vtt'
    },
    segments: [{
      uri: '',
      timeline: 1,
      duration: 2,
      resolvedUri: '',
      map: {
        uri: '',
        resolvedUri: ''
      },
      number: 1
    }, {
      uri: '',
      timeline: 1,
      duration: 2,
      resolvedUri: '',
      map: {
        uri: '',
        resolvedUri: ''
      },
      number: 2
    }]
  }, {
    attributes: {
      sourceDuration: 100,
      duration: 2,
      id: '2',
      bandwidth: 10000,
      periodIndex: 1,
      mimeType: 'text/vtt',
      type: 'static',
      baseUrl: 'https://www.example.com/vtt'
    },
    segments: [{
      uri: '',
      timeline: 1,
      duration: 2,
      resolvedUri: '',
      map: {
        uri: '',
        resolvedUri: ''
      },
      number: 1
    }, {
      uri: '',
      timeline: 1,
      duration: 2,
      resolvedUri: '',
      map: {
        uri: '',
        resolvedUri: ''
      },
      number: 2
    }]
  }];

  const expected = {
    allowCache: true,
    discontinuityStarts: [],
    duration: 100,
    endList: true,
    mediaGroups: {
      AUDIO: {
        audio: {
          main: {
            autoselect: true,
            default: true,
            language: '',
            playlists: [{
              attributes: {
                BANDWIDTH: 20000,
                CODECS: 'foo;bar',
                NAME: '1',
                ['PROGRAM-ID']: 1
              },
              targetDuration: 2,
              mediaSequence: 1,
              endList: true,
              resolvedUri: '',
              segments: [{
                uri: '',
                timeline: 1,
                duration: 2,
                resolvedUri: '',
                map: {
                  uri: '',
                  resolvedUri: ''
                },
                number: 1
              }, {
                uri: '',
                timeline: 1,
                duration: 2,
                resolvedUri: '',
                map: {
                  uri: '',
                  resolvedUri: ''
                },
                number: 2
              }],
              timeline: 1,
              uri: ''
            }, {
              attributes: {
                BANDWIDTH: 10000,
                CODECS: 'foo;bar',
                NAME: '2',
                ['PROGRAM-ID']: 1
              },
              targetDuration: 2,
              mediaSequence: 1,
              endList: true,
              resolvedUri: '',
              segments: [{
                uri: '',
                timeline: 1,
                duration: 2,
                resolvedUri: '',
                map: {
                  uri: '',
                  resolvedUri: ''
                },
                number: 1
              }, {
                uri: '',
                timeline: 1,
                duration: 2,
                resolvedUri: '',
                map: {
                  uri: '',
                  resolvedUri: ''
                },
                number: 2
              }],
              timeline: 1,
              uri: ''
            }],
            uri: ''
          }
        }
      },
      ['CLOSED-CAPTIONS']: {},
      SUBTITLES: {
        subs: {
          text: {
            autoselect: false,
            default: false,
            language: 'text',
            playlists: [{
              attributes: {
                BANDWIDTH: 20000,
                NAME: '1',
                ['PROGRAM-ID']: 1
              },
              endList: true,
              targetDuration: 2,
              mediaSequence: 1,
              resolvedUri: 'https://www.example.com/vtt',
              segments: [{
                uri: '',
                timeline: 1,
                duration: 2,
                resolvedUri: '',
                map: {
                  uri: '',
                  resolvedUri: ''
                },
                number: 1
              }, {
                uri: '',
                timeline: 1,
                duration: 2,
                resolvedUri: '',
                map: {
                  uri: '',
                  resolvedUri: ''
                },
                number: 2
              }],
              timeline: 1,
              uri: ''
            }, {
              attributes: {
                BANDWIDTH: 10000,
                NAME: '2',
                ['PROGRAM-ID']: 1
              },
              endList: true,
              targetDuration: 2,
              mediaSequence: 1,
              resolvedUri: 'https://www.example.com/vtt',
              segments: [{
                uri: '',
                timeline: 1,
                duration: 2,
                resolvedUri: '',
                map: {
                  uri: '',
                  resolvedUri: ''
                },
                number: 1
              }, {
                uri: '',
                timeline: 1,
                duration: 2,
                resolvedUri: '',
                map: {
                  uri: '',
                  resolvedUri: ''
                },
                number: 2
              }],
              timeline: 1,
              uri: ''
            }],
            uri: ''
          }
        }
      },
      VIDEO: {}
    },
    playlists: [{
      attributes: {
        AUDIO: 'audio',
        SUBTITLES: 'subs',
        BANDWIDTH: 10000,
        CODECS: 'foo;bar',
        NAME: '1',
        ['PROGRAM-ID']: 1,
        RESOLUTION: {
          height: 600,
          width: 800
        }
      },
      endList: true,
      resolvedUri: '',
      mediaSequence: 1,
      targetDuration: 2,
      segments: [{
        uri: '',
        timeline: 1,
        duration: 2,
        resolvedUri: '',
        map: {
          uri: '',
          resolvedUri: ''
        },
        number: 1
      }, {
        uri: '',
        timeline: 1,
        duration: 2,
        resolvedUri: '',
        map: {
          uri: '',
          resolvedUri: ''
        },
        number: 2
      }],
      timeline: 1,
      uri: ''
    }],
    segments: [],
    uri: ''
  };

  assert.deepEqual(toM3u8(input), expected);
});

QUnit.test('playlists with sidx and sidxMapping', function(assert) {
  const input = [{
    attributes: {
      sourceDuration: 100,
      id: '1',
      width: 800,
      height: 600,
      codecs: 'foo;bar',
      duration: 0,
      bandwidth: 10000,
      periodIndex: 1,
      mimeType: 'video/mp4',
      type: 'static'
    },
    segments: [],
    sidx: {
      byterange: {
        offset: 10,
        length: 10
      },
      uri: 'sidx.mp4',
      resolvedUri: 'http://example.com/sidx.mp4',
      duration: 10
    },
    uri: 'http://example.com/fmp4.mp4'
  }];

  const mapping = {
    'sidx.mp4-10-19': {
      sidx: {
        timescale: 1,
        firstOffset: 0,
        references: [{
          referenceType: 0,
          referencedSize: 5,
          subsegmentDuration: 2
        }]
      }
    }
  };

  const expected = [{
    attributes: {
      AUDIO: 'audio',
      SUBTITLES: 'subs',
      BANDWIDTH: 10000,
      CODECS: 'foo;bar',
      NAME: '1',
      ['PROGRAM-ID']: 1,
      RESOLUTION: {
        height: 600,
        width: 800
      }
    },
    sidx: {
      byterange: {
        offset: 10,
        length: 10
      },
      uri: 'sidx.mp4',
      resolvedUri: 'http://example.com/sidx.mp4',
      duration: 10
    },
    targetDuration: 0,
    timeline: 1,
    uri: '',
    segments: [{
      map: {
        resolvedUri: 'http://example.com/sidx.mp4',
        uri: ''
      },
      byterange: {
        offset: 20,
        length: 5
      },
      uri: 'http://example.com/sidx.mp4',
      resolvedUri: 'http://example.com/sidx.mp4',
      duration: 2,
      number: 0,
      timeline: 1
    }],
    endList: true,
    mediaSequence: 1,
    resolvedUri: ''
  }];

  assert.deepEqual(toM3u8(input, null, mapping).playlists, expected);
});

QUnit.test('playlists without minimumUpdatePeriod dont assign default value', function(assert) {
  const input = [{
    attributes: {
      sourceDuration: 100,
      id: '1',
      width: 800,
      height: 600,
      codecs: 'foo;bar',
      duration: 0,
      bandwidth: 10000,
      periodIndex: 1,
      mimeType: 'video/mp4',
      type: 'static'
    },
    segments: [],
    sidx: {
      byterange: {
        offset: 10,
        length: 10
      },
      uri: 'sidx.mp4',
      resolvedUri: 'http://example.com/sidx.mp4',
      duration: 10
    },
    uri: 'http://example.com/fmp4.mp4'
  }];

  assert.equal(toM3u8(input).minimumUpdatePeriod, undefined);
});

QUnit.test('playlists with minimumUpdatePeriod = 0', function(assert) {
  const input = [{
    attributes: {
      sourceDuration: 100,
      id: '1',
      width: 800,
      height: 600,
      codecs: 'foo;bar',
      duration: 0,
      bandwidth: 10000,
      periodIndex: 1,
      mimeType: 'video/mp4',
      type: 'static',
      minimumUpdatePeriod: 0
    },
    segments: [],
    sidx: {
      byterange: {
        offset: 10,
        length: 10
      },
      uri: 'sidx.mp4',
      resolvedUri: 'http://example.com/sidx.mp4',
      duration: 10
    },
    uri: 'http://example.com/fmp4.mp4'
  }];

  assert.equal(toM3u8(input).minimumUpdatePeriod, 0);
});

QUnit.test('playlists with integer value for minimumUpdatePeriod', function(assert) {
  const input = [{
    attributes: {
      sourceDuration: 100,
      id: '1',
      width: 800,
      height: 600,
      codecs: 'foo;bar',
      duration: 0,
      bandwidth: 10000,
      periodIndex: 1,
      mimeType: 'video/mp4',
      type: 'static',
      minimumUpdatePeriod: 2
    },
    segments: [],
    sidx: {
      byterange: {
        offset: 10,
        length: 10
      },
      uri: 'sidx.mp4',
      resolvedUri: 'http://example.com/sidx.mp4',
      duration: 10
    },
    uri: 'http://example.com/fmp4.mp4'
  }];

  assert.equal(toM3u8(input).minimumUpdatePeriod, 2000, 'converts update period to ms');
});

QUnit.test('no playlists', function(assert) {
  assert.deepEqual(toM3u8([]), {});
});

QUnit.test('dynamic playlists with suggestedPresentationDelay', function(assert) {
  const input = [{
    attributes: {
      id: '1',
      codecs: 'foo;bar',
      sourceDuration: 100,
      duration: 0,
      bandwidth: 20000,
      periodIndex: 1,
      mimeType: 'audio/mp4',
      type: 'dynamic',
      suggestedPresentationDelay: 18
    },
    segments: []
  }, {
    attributes: {
      id: '2',
      codecs: 'foo;bar',
      sourceDuration: 100,
      duration: 0,
      bandwidth: 10000,
      periodIndex: 1,
      mimeType: 'audio/mp4',
      type: 'static'
    },
    segments: []
  }, {
    attributes: {
      sourceDuration: 100,
      id: '1',
      width: 800,
      height: 600,
      codecs: 'foo;bar',
      duration: 0,
      bandwidth: 10000,
      periodIndex: 1,
      mimeType: 'video/mp4',
      type: 'static'
    },
    segments: []
  }, {
    attributes: {
      sourceDuration: 100,
      id: '1',
      bandwidth: 20000,
      periodIndex: 1,
      mimeType: 'text/vtt',
      type: 'static',
      baseUrl: 'https://www.example.com/vtt'
    }
  }, {
    attributes: {
      sourceDuration: 100,
      id: '1',
      bandwidth: 10000,
      periodIndex: 1,
      mimeType: 'text/vtt',
      type: 'static',
      baseUrl: 'https://www.example.com/vtt'
    }
  }];

  const output = toM3u8(input);

  assert.ok('suggestedPresentationDelay' in output);
  assert.deepEqual(output.suggestedPresentationDelay, 18);
});

QUnit.test('playlists with label', function(assert) {
  const label = 'English with commentary';
  const input = [{
    attributes: {
      id: '1',
      codecs: 'foo;bar',
      sourceDuration: 100,
      duration: 0,
      bandwidth: 20000,
      periodIndex: 1,
      mimeType: 'audio/mp4',
      type: 'dynamic',
      label
    },
    segments: []
  }, {
    attributes: {
      id: '2',
      codecs: 'foo;bar',
      sourceDuration: 100,
      duration: 0,
      bandwidth: 10000,
      periodIndex: 1,
      mimeType: 'audio/mp4',
      type: 'static'
    },
    segments: []
  }, {
    attributes: {
      sourceDuration: 100,
      id: '1',
      width: 800,
      height: 600,
      codecs: 'foo;bar',
      duration: 0,
      bandwidth: 10000,
      periodIndex: 1,
      mimeType: 'video/mp4',
      type: 'static'
    },
    segments: []
  }];
  const output = toM3u8(input);

  assert.ok(label in output.mediaGroups.AUDIO.audio, 'label exists');
});

QUnit.test('608 captions', function(assert) {
  const input = [{
    attributes: {
      captionServices: [{
        channel: 'CC1',
        language: 'CC1'
      }, {
        channel: 'CC2',
        language: 'CC2'
      }, {
        channel: undefined,
        language: 'English'
      }, {
        channel: 'CC4',
        language: 'eng'
      }],
      id: '1',
      codecs: 'foo;bar',
      sourceDuration: 100,
      duration: 0,
      bandwidth: 20000,
      periodIndex: 1,
      mimeType: 'audio/mp4',
      type: 'dynamic'
    },
    segments: []
  }, {
    attributes: {
      id: '2',
      codecs: 'foo;bar',
      sourceDuration: 100,
      duration: 0,
      bandwidth: 10000,
      periodIndex: 1,
      mimeType: 'audio/mp4',
      type: 'static'
    },
    segments: []
  }, {
    attributes: {
      sourceDuration: 100,
      id: '1',
      width: 800,
      height: 600,
      codecs: 'foo;bar',
      duration: 0,
      bandwidth: 10000,
      periodIndex: 1,
      mimeType: 'video/mp4',
      type: 'static'
    },
    segments: []
  }];
  const output = toM3u8(input);

  const cc = output.mediaGroups['CLOSED-CAPTIONS'].cc;

  Object.keys(cc).forEach((key) => {
    assert.notOk(cc[key].autoselect, 'no autoselect');
    assert.notOk(cc[key].default, 'no default');
  });

  assert.deepEqual(Object.keys(cc), ['CC1', 'CC2', 'English', 'eng'], 'we have 4 channels');
  assert.equal(cc.CC1.instreamId, 'CC1', 'CC1 has an instreamId of CC1');
  assert.equal(cc.CC2.instreamId, 'CC2', 'CC2 has an instreamId of CC1');
  assert.equal(cc.English.instreamId, undefined, 'English captions dont have an instreamId');
  assert.equal(cc.eng.instreamId, 'CC4', 'eng captions have an instreamId of CC4');
});

QUnit.module('generateSidxKey');

QUnit.test('generates correct key', function(assert) {
  const sidxInfo = {
    byterange: {
      offset: 1,
      length: 5
    },
    uri: 'uri'
  };

  assert.strictEqual(
    generateSidxKey(sidxInfo),
    'uri-1-5',
    'the key byterange should have a inclusive end'
  );
});

