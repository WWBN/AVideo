import window from 'global/window';
import videojs from 'video.js';
// Add Cardboard button
const Button = videojs.getComponent('Button');

class CardboardButton extends Button {
  constructor(player, options) {
    super(player, options);

    this.handleVrDisplayActivate_ = videojs.bind(this, this.handleVrDisplayActivate_);
    this.handleVrDisplayDeactivate_ = videojs.bind(this, this.handleVrDisplayDeactivate_);
    this.handleVrDisplayPresentChange_ = videojs.bind(this, this.handleVrDisplayPresentChange_);
    this.handleOrientationChange_ = videojs.bind(this, this.handleOrientationChange_);
    window.addEventListener('orientationchange', this.handleOrientationChange_);
    window.addEventListener('vrdisplayactivate', this.handleVrDisplayActivate_);
    window.addEventListener('vrdisplaydeactivate', this.handleVrDisplayDeactivate_);

    // vrdisplaypresentchange does not fire activate or deactivate
    // and happens when hitting the back button during cardboard mode
    // so we need to make sure we stay in the correct state by
    // listening to it and checking if we are presenting it or not
    window.addEventListener('vrdisplaypresentchange', this.handleVrDisplayPresentChange_);

    // we cannot show the cardboard button in fullscreen on
    // android as it breaks the controls, and makes it impossible
    // to exit cardboard mode
    if (videojs.browser.IS_ANDROID) {
      this.on(player, 'fullscreenchange', () => {
        if (player.isFullscreen()) {
          this.hide();
        } else {
          this.show();
        }
      });
    }
  }

  buildCSSClass() {
    return `vjs-button-vr ${super.buildCSSClass()}`;
  }

  handleVrDisplayPresentChange_() {
    if (!this.player_.vr().vrDisplay.isPresenting && this.active_) {
      this.handleVrDisplayDeactivate_();
    }
    if (this.player_.vr().vrDisplay.isPresenting && !this.active_) {
      this.handleVrDisplayActivate_();
    }
  }

  handleOrientationChange_() {
    if (this.active_ && videojs.browser.IS_IOS) {
      this.changeSize_();
    }
  }

  changeSize_() {
    this.player_.width(window.innerWidth);
    this.player_.height(window.innerHeight);
    window.dispatchEvent(new window.Event('resize'));
  }

  handleVrDisplayActivate_() {
    // we mimic fullscreen on IOS
    if (videojs.browser.IS_IOS) {
      this.oldWidth_ = this.player_.currentWidth();
      this.oldHeight_ = this.player_.currentHeight();
      this.player_.enterFullWindow();
      this.changeSize_();
    }

    this.active_ = true;
  }

  handleVrDisplayDeactivate_() {
    // un-mimic fullscreen on iOS
    if (videojs.browser.IS_IOS) {
      if (this.oldWidth_) {
        this.player_.width(this.oldWidth_);
      }
      if (this.oldHeight_) {
        this.player_.height(this.oldHeight_);
      }
      this.player_.exitFullWindow();
    }

    this.active_ = false;
  }

  handleClick(event) {
    // if cardboard mode display is not active, activate it
    // otherwise deactivate it
    if (!this.active_) {
      // This starts playback mode when the cardboard button
      // is clicked on Android. We need to do this as the controls
      // disappear
      if (!this.player_.hasStarted() && videojs.browser.IS_ANDROID) {
        this.player_.play();
      }
      window.dispatchEvent(new window.Event('vrdisplayactivate'));
    } else {
      window.dispatchEvent(new window.Event('vrdisplaydeactivate'));
    }
  }

  dispose() {
    super.dispose();
    window.removeEventListener('vrdisplayactivate', this.handleVrDisplayActivate_);
    window.removeEventListener('vrdisplaydeactivate', this.handleVrDisplayDeactivate_);
    window.removeEventListener('vrdisplaypresentchange', this.handleVrDisplayPresentChange_);
  }

}

videojs.registerComponent('CardboardButton', CardboardButton);

export default CardboardButton;
