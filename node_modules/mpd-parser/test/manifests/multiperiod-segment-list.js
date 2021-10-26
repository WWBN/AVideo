export const parsedManifest = {
  allowCache: true,
  discontinuityStarts: [],
  duration: 12,
  endList: true,
  mediaGroups: {
    'AUDIO': {},
    'CLOSED-CAPTIONS': {},
    'SUBTITLES': {},
    'VIDEO': {}
  },
  playlists: [
    {
      attributes: {
        'AUDIO': 'audio',
        'BANDWIDTH': 449000,
        'CODECS': 'avc1.420015',
        'NAME': '482',
        'PROGRAM-ID': 1,
        'RESOLUTION': {
          height: 270,
          width: 482
        },
        'SUBTITLES': 'subs'
      },
      endList: true,
      mediaSequence: 1,
      targetDuration: 3,
      resolvedUri: '',
      segments: [
        {
          duration: 3,
          map: {
            uri: '',
            resolvedUri: 'https://www.example.com/base'
          },
          resolvedUri: 'https://www.example.com/low/segment-1.ts',
          timeline: 0,
          presentationTime: 0,
          uri: 'low/segment-1.ts',
          number: 1
        },
        {
          duration: 3,
          map: {
            uri: '',
            resolvedUri: 'https://www.example.com/base'
          },
          resolvedUri: 'https://www.example.com/low/segment-2.ts',
          timeline: 0,
          presentationTime: 3,
          uri: 'low/segment-2.ts',
          number: 2
        },
        {
          discontinuity: true,
          duration: 3,
          map: {
            uri: '',
            resolvedUri: 'https://www.example.com/base'
          },
          resolvedUri: 'https://www.example.com/low/segment-1.ts',
          timeline: 1,
          presentationTime: 6,
          uri: 'low/segment-1.ts',
          number: 1
        },
        {
          duration: 3,
          map: {
            uri: '',
            resolvedUri: 'https://www.example.com/base'
          },
          resolvedUri: 'https://www.example.com/low/segment-2.ts',
          timeline: 1,
          presentationTime: 9,
          uri: 'low/segment-2.ts',
          number: 2
        }
      ],
      timeline: 0,
      uri: ''
    }
  ],
  segments: [],
  uri: ''
};
