import document from 'global/document';

// check if the browser supports cors
export const corsSupport = (function() {
  const video = document.createElement('video');

  video.crossOrigin = 'anonymous';

  return video.hasAttribute('crossorigin');
})();

export const isHLS = function(currentType) {
  // hls video types
  const hlsTypes = [
    // Apple santioned
    'application/vnd.apple.mpegurl',
    // Very common
    'application/x-mpegurl',
    // Included for completeness
    'video/x-mpegurl',
    'video/mpegurl',
    'application/mpegurl'
  ];

  // if the current type has a case insensitivie match from the list above
  // this is hls
  return hlsTypes.some((type) => (RegExp(`^${type}$`, 'i')).test(currentType));
};

export const validProjections = [
  '360',
  '360_LR',
  '360_TB',
  '360_CUBE',
  'EAC',
  'EAC_LR',
  'NONE',
  'AUTO',
  'Sphere',
  'Cube',
  'equirectangular',
  '180',
  '180_LR',
  '180_MONO'
];

export const getInternalProjectionName = function(projection) {
  if (!projection) {
    return;
  }

  projection = projection.toString().trim();

  if ((/sphere/i).test(projection)) {
    return '360';
  }

  if ((/cube/i).test(projection)) {
    return '360_CUBE';
  }

  if ((/equirectangular/i).test(projection)) {
    return '360';
  }

  for (let i = 0; i < validProjections.length; i++) {
    if (new RegExp('^' + validProjections[i] + '$', 'i').test(projection)) {
      return validProjections[i];
    }
  }

};
