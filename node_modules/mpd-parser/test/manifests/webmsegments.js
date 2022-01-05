export const parsedManifest = {
  allowCache: true,
  discontinuityStarts: [],
  segments: [],
  endList: true,
  mediaGroups: {
    'AUDIO': {
      audio: {
        en: {
          language: 'en',
          autoselect: true,
          default: true,
          playlists: [
            {
              attributes: {
                'NAME': '2',
                'BANDWIDTH': 32000,
                'CODECS': 'opus',
                'PROGRAM-ID': 1
              },
              uri: '',
              endList: true,
              timeline: 1,
              resolvedUri: '',
              targetDuration: 4,
              segments: [
                {
                  uri: 'audio/segment_0.chk',
                  timeline: 1,
                  duration: 4,
                  resolvedUri: 'https://www.example.com/audio/segment_0.chk',
                  map: {
                    uri: 'audio/init.hdr',
                    resolvedUri: 'https://www.example.com/audio/init.hdr'
                  },
                  number: 0,
                  presentationTime: 0
                },
                {
                  uri: 'audio/segment_1.chk',
                  timeline: 1,
                  duration: 4,
                  resolvedUri: 'https://www.example.com/audio/segment_1.chk',
                  map: {
                    uri: 'audio/init.hdr',
                    resolvedUri: 'https://www.example.com/audio/init.hdr'
                  },
                  number: 1,
                  presentationTime: 4
                },
                {
                  uri: 'audio/segment_2.chk',
                  timeline: 1,
                  duration: 4,
                  resolvedUri: 'https://www.example.com/audio/segment_2.chk',
                  map: {
                    uri: 'audio/init.hdr',
                    resolvedUri: 'https://www.example.com/audio/init.hdr'
                  },
                  number: 2,
                  presentationTime: 8
                },
                {
                  uri: 'audio/segment_3.chk',
                  timeline: 1,
                  duration: 4,
                  resolvedUri: 'https://www.example.com/audio/segment_3.chk',
                  map: {
                    uri: 'audio/init.hdr',
                    resolvedUri: 'https://www.example.com/audio/init.hdr'
                  },
                  number: 3,
                  presentationTime: 12
                }
              ],
              mediaSequence: 0
            }
          ],
          uri: ''
        }
      }
    },
    'VIDEO': {},
    'CLOSED-CAPTIONS': {},
    'SUBTITLES': {}
  },
  uri: '',
  duration: 16,
  playlists: [
    {
      attributes: {
        'NAME': '1',
        'AUDIO': 'audio',
        'SUBTITLES': 'subs',
        'RESOLUTION': {
          width: 480,
          height: 200
        },
        'CODECS': 'av1',
        'BANDWIDTH': 100000,
        'PROGRAM-ID': 1
      },
      uri: '',
      endList: true,
      timeline: 1,
      resolvedUri: '',
      targetDuration: 4,
      segments: [
        {
          uri: 'video/segment_0.chk',
          timeline: 1,
          duration: 4,
          resolvedUri: 'https://www.example.com/video/segment_0.chk',
          map: {
            uri: 'video/init.hdr',
            resolvedUri: 'https://www.example.com/video/init.hdr'
          },
          number: 0,
          presentationTime: 0
        },
        {
          uri: 'video/segment_1.chk',
          timeline: 1,
          duration: 4,
          resolvedUri: 'https://www.example.com/video/segment_1.chk',
          map: {
            uri: 'video/init.hdr',
            resolvedUri: 'https://www.example.com/video/init.hdr'
          },
          number: 1,
          presentationTime: 4
        },
        {
          uri: 'video/segment_2.chk',
          timeline: 1,
          duration: 4,
          resolvedUri: 'https://www.example.com/video/segment_2.chk',
          map: {
            uri: 'video/init.hdr',
            resolvedUri: 'https://www.example.com/video/init.hdr'
          },
          number: 2,
          presentationTime: 8
        },
        {
          uri: 'video/segment_3.chk',
          timeline: 1,
          duration: 4,
          resolvedUri: 'https://www.example.com/video/segment_3.chk',
          map: {
            uri: 'video/init.hdr',
            resolvedUri: 'https://www.example.com/video/init.hdr'
          },
          number: 3,
          presentationTime: 12
        }
      ],
      mediaSequence: 0
    }
  ]
};
