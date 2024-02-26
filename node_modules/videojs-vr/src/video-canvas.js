import videojs from 'video.js';

const ClickableComponent = videojs.getComponent('ClickableComponent');

/**
 * This class reacts to interactions with the canvas and
 * triggers appropriate functionality on the player. Right now
 * it does two things:
 *
 * 1. A `mousedown`/`touchstart` followed by `touchend`/`mouseup` without any
 *    `touchmove` or `mousemove` toggles play/pause on the player
 * 2. Only moving on/clicking the control bar or toggling play/pause should
 *    show the control bar. Moving around the scene in the canvas should not.
 */
class VideoCanvas extends ClickableComponent {
  constructor(player, options) {
    super(player, options);

    this.player_.controlBar.on([
      'mousedown',
      'mousemove',
      'mouseup',
      'touchstart',
      'touchmove',
      'touchend'
    ], this.onControlBarMove);

    // we have to override these here because
    // video.js listens for user activity on the video element
    // and makes the user active when the mouse moves.
    // We don't want that for 3d videos
    this.oldReportUserActivity = this.player_.reportUserActivity;
    this.player_.reportUserActivity = () => {};

    // canvas movements
    this.on('mousemove', this.onMove);
    // this.on('tap', this.togglePlay);
    this.shouldTogglePlay = false;
  }

  onTap(e) {
  }

  handleClick(e) {
    if (e.type !== 'tap' && !this.shouldTogglePlay) {
      this.shouldTogglePlay = true;
      return;
    }

    this.togglePlay();
  }

  togglePlay() {
    if (this.player_.paused()) {
      this.player_.play();
    } else {
      this.player_.pause();
    }
  }

  onMove(e) {
    this.shouldTogglePlay = false;
  }

  onControlBarMove(e) {
    this.player_.userActive(true);
  }

  dispose() {
    super.dispose();

    this.player_.controlBar.off([
      'mousedown',
      'mousemove',
      'mouseup',
      'touchstart',
      'touchmove',
      'touchend'
    ], this.onControlBarMove);

    this.player_.reportUserActivity = this.oldReportUserActivity;
  }
}

VideoCanvas.prototype.options_ = {
  reportTouchActivity: false
};

videojs.registerComponent('VideoCanvas', VideoCanvas);

export default VideoCanvas;
