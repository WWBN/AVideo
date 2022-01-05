export const parsedManifest = {
  allowCache: true,
  uri: '',
  duration: 30,
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
                'CODECS': 'mp4a.40.2',
                'PROGRAM-ID': 1
              },
              uri: '',
              endList: true,
              timeline: 1,
              resolvedUri: '',
              targetDuration: 5,
              segments: [
                {
                  uri: 'audio/segment_0.m4f',
                  timeline: 1,
                  duration: 5,
                  resolvedUri: 'https://www.example.com/audio/segment_0.m4f',
                  map: {
                    uri: 'audio/init.m4f',
                    resolvedUri: 'https://www.example.com/audio/init.m4f'
                  },
                  number: 0,
                  presentationTime: 0
                },
                {
                  uri: 'audio/segment_1.m4f',
                  timeline: 1,
                  duration: 5,
                  resolvedUri: 'https://www.example.com/audio/segment_1.m4f',
                  map: {
                    uri: 'audio/init.m4f',
                    resolvedUri: 'https://www.example.com/audio/init.m4f'
                  },
                  number: 1,
                  presentationTime: 5
                },
                {
                  uri: 'audio/segment_2.m4f',
                  timeline: 1,
                  duration: 5,
                  resolvedUri: 'https://www.example.com/audio/segment_2.m4f',
                  map: {
                    uri: 'audio/init.m4f',
                    resolvedUri: 'https://www.example.com/audio/init.m4f'
                  },
                  number: 2,
                  presentationTime: 10
                },
                {
                  discontinuity: true,
                  uri: 'audio/segment_0.m4f',
                  timeline: 2,
                  duration: 5,
                  resolvedUri: 'https://www.example.com/audio/segment_0.m4f',
                  map: {
                    uri: 'audio/init.m4f',
                    resolvedUri: 'https://www.example.com/audio/init.m4f'
                  },
                  number: 0,
                  presentationTime: 15
                },
                {
                  uri: 'audio/segment_1.m4f',
                  timeline: 2,
                  duration: 5,
                  resolvedUri: 'https://www.example.com/audio/segment_1.m4f',
                  map: {
                    uri: 'audio/init.m4f',
                    resolvedUri: 'https://www.example.com/audio/init.m4f'
                  },
                  number: 1,
                  presentationTime: 20
                },
                {
                  uri: 'audio/segment_2.m4f',
                  timeline: 2,
                  duration: 5,
                  resolvedUri: 'https://www.example.com/audio/segment_2.m4f',
                  map: {
                    uri: 'audio/init.m4f',
                    resolvedUri: 'https://www.example.com/audio/init.m4f'
                  },
                  number: 2,
                  presentationTime: 25
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
        'CODECS': 'avc1.4d001f',
        'BANDWIDTH': 100000,
        'PROGRAM-ID': 1
      },
      uri: '',
      endList: true,
      timeline: 1,
      resolvedUri: '',
      targetDuration: 5,
      segments: [
        {
          uri: 'video/segment_0.m4f',
          timeline: 1,
          duration: 5,
          resolvedUri: 'https://www.example.com/video/segment_0.m4f',
          map: {
            uri: 'video/init.m4f',
            resolvedUri: 'https://www.example.com/video/init.m4f'
          },
          number: 0,
          presentationTime: 0
        },
        {
          uri: 'video/segment_1.m4f',
          timeline: 1,
          duration: 5,
          resolvedUri: 'https://www.example.com/video/segment_1.m4f',
          map: {
            uri: 'video/init.m4f',
            resolvedUri: 'https://www.example.com/video/init.m4f'
          },
          number: 1,
          presentationTime: 5
        },
        {
          uri: 'video/segment_2.m4f',
          timeline: 1,
          duration: 5,
          resolvedUri: 'https://www.example.com/video/segment_2.m4f',
          map: {
            uri: 'video/init.m4f',
            resolvedUri: 'https://www.example.com/video/init.m4f'
          },
          number: 2,
          presentationTime: 10
        },
        {
          discontinuity: true,
          uri: 'video/segment_0.m4f',
          timeline: 2,
          duration: 5,
          resolvedUri: 'https://www.example.com/video/segment_0.m4f',
          map: {
            uri: 'video/init.m4f',
            resolvedUri: 'https://www.example.com/video/init.m4f'
          },
          number: 0,
          presentationTime: 15
        },
        {
          uri: 'video/segment_1.m4f',
          timeline: 2,
          duration: 5,
          resolvedUri: 'https://www.example.com/video/segment_1.m4f',
          map: {
            uri: 'video/init.m4f',
            resolvedUri: 'https://www.example.com/video/init.m4f'
          },
          number: 1,
          presentationTime: 20
        },
        {
          uri: 'video/segment_2.m4f',
          timeline: 2,
          duration: 5,
          resolvedUri: 'https://www.example.com/video/segment_2.m4f',
          map: {
            uri: 'video/init.m4f',
            resolvedUri: 'https://www.example.com/video/init.m4f'
          },
          number: 2,
          presentationTime: 25
        }
      ],
      mediaSequence: 0
    }
  ]
};
