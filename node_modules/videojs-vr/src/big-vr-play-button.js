import videojs from 'video.js';

const BigPlayButton = videojs.getComponent('BigPlayButton');

class BigVrPlayButton extends BigPlayButton {
  buildCSSClass() {
    return `vjs-big-vr-play-button ${super.buildCSSClass()}`;
  }
}

videojs.registerComponent('BigVrPlayButton', BigVrPlayButton);

export default BigVrPlayButton;
