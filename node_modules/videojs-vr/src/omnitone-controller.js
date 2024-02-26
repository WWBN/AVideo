import videojs from 'video.js';

/**
 * This class manages ambisonic decoding and binaural rendering via Omnitone library.
 */
class OmnitoneController extends videojs.EventTarget {
  /**
   * Omnitone controller class.
   *
   * @class
   * @param {AudioContext} audioContext - associated AudioContext.
   * @param {Omnitone library} omnitone - Omnitone library element.
   * @param {HTMLVideoElement} video - vidoe tag element.
   * @param {Object} options - omnitone options.
   */
  constructor(audioContext, omnitone, video, options) {
    super();

    const settings = videojs.mergeOptions({
      // Safari uses the different AAC decoder than FFMPEG. The channel order is
      // The default 4ch AAC channel layout for FFMPEG AAC channel ordering.
      channelMap: videojs.browser.IS_SAFARI ? [2, 0, 1, 3] : [0, 1, 2, 3],
      ambisonicOrder: 1
    }, options);

    this.videoElementSource = audioContext.createMediaElementSource(video);
    this.foaRenderer = omnitone.createFOARenderer(audioContext, settings);

    this.foaRenderer.initialize().then(() => {
      if (audioContext.state === 'suspended') {
        this.trigger({type: 'audiocontext-suspended'});
      }
      this.videoElementSource.connect(this.foaRenderer.input);
      this.foaRenderer.output.connect(audioContext.destination);
      this.initialized = true;
      this.trigger({type: 'omnitone-ready'});
    }, (error) => {
      videojs.log.warn(`videojs-vr: Omnitone initializes failed with the following error: ${error})`);
    });
  }

  /**
   * Updates the rotation of the Omnitone decoder based on three.js camera matrix.
   *
   * @param {Camera} camera Three.js camera object
   */
  update(camera) {
    if (!this.initialized) {
      return;
    }
    this.foaRenderer.setRotationMatrixFromCamera(camera.matrix);
  }

  /**
   * Destroys the controller and does any necessary cleanup.
   */
  dispose() {
    this.initialized = false;
    this.foaRenderer.setRenderingMode('bypass');
    this.foaRenderer = null;
  }
}

export default OmnitoneController;
