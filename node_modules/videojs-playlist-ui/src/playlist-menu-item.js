import document from 'global/document';
import videojs from 'video.js';

const Component = videojs.getComponent('Component');

const createThumbnail = function(thumbnail) {
  if (!thumbnail) {
    const placeholder = document.createElement('div');

    placeholder.className = 'vjs-playlist-thumbnail vjs-playlist-thumbnail-placeholder';
    return placeholder;
  }

  const picture = document.createElement('picture');

  picture.className = 'vjs-playlist-thumbnail';

  if (typeof thumbnail === 'string') {
    // simple thumbnails
    const img = document.createElement('img');

    img.loading = 'lazy';
    img.src = thumbnail;
    img.alt = '';
    picture.appendChild(img);
  } else {
    // responsive thumbnails

    // additional variations of a <picture> are specified as
    // <source> elements
    for (let i = 0; i < thumbnail.length - 1; i++) {
      const variant = thumbnail[i];
      const source = document.createElement('source');

      // transfer the properties of each variant onto a <source>
      for (const prop in variant) {
        source[prop] = variant[prop];
      }
      picture.appendChild(source);
    }

    // the default version of a <picture> is specified by an <img>
    const variant = thumbnail[thumbnail.length - 1];
    const img = document.createElement('img');

    img.loading = 'lazy';
    img.alt = '';
    for (const prop in variant) {
      img[prop] = variant[prop];
    }
    picture.appendChild(img);
  }
  return picture;
};

class PlaylistMenuItem extends Component {

  constructor(player, playlistItem, settings) {
    if (!playlistItem.item) {
      throw new Error('Cannot construct a PlaylistMenuItem without an item option');
    }

    playlistItem.showDescription = settings.showDescription;
    super(player, playlistItem);
    this.item = playlistItem.item;

    this.playOnSelect = settings.playOnSelect;

    this.emitTapEvents();

    this.on(['click', 'tap'], this.switchPlaylistItem_);
    this.on('keydown', this.handleKeyDown_);

  }

  handleKeyDown_(event) {
    // keycode 13 is <Enter>
    // keycode 32 is <Space>
    if (event.which === 13 || event.which === 32) {
      this.switchPlaylistItem_();
    }
  }

  switchPlaylistItem_(event) {
    this.player_.playlist.currentItem(this.player_.playlist().indexOf(this.item));
    if (this.playOnSelect) {
      this.player_.play();
    }
  }

  createEl() {
    const li = document.createElement('li');
    const item = this.options_.item;
    const showDescription = this.options_.showDescription;

    if (typeof item.data === 'object') {
      const dataKeys = Object.keys(item.data);

      dataKeys.forEach(key => {
        const value = item.data[key];

        li.dataset[key] = value;
      });
    }

    li.className = 'vjs-playlist-item';
    li.setAttribute('tabIndex', 0);

    // Thumbnail image
    this.thumbnail = createThumbnail(item.thumbnail);
    li.appendChild(this.thumbnail);

    // Duration
    if (item.duration) {
      const duration = document.createElement('time');
      const time = videojs.time.formatTime(item.duration);

      duration.className = 'vjs-playlist-duration';
      duration.setAttribute('datetime', 'PT0H0M' + item.duration + 'S');
      duration.appendChild(document.createTextNode(time));
      li.appendChild(duration);
    }

    // Now playing
    const nowPlayingEl = document.createElement('span');
    const nowPlayingText = this.localize('Now Playing');

    nowPlayingEl.className = 'vjs-playlist-now-playing-text';
    nowPlayingEl.appendChild(document.createTextNode(nowPlayingText));
    nowPlayingEl.setAttribute('title', nowPlayingText);
    this.thumbnail.appendChild(nowPlayingEl);

    // Title container contains title and "up next"
    const titleContainerEl = document.createElement('div');

    titleContainerEl.className = 'vjs-playlist-title-container';
    this.thumbnail.appendChild(titleContainerEl);

    // Up next
    const upNextEl = document.createElement('span');
    const upNextText = this.localize('Up Next');

    upNextEl.className = 'vjs-up-next-text';
    upNextEl.appendChild(document.createTextNode(upNextText));
    upNextEl.setAttribute('title', upNextText);
    titleContainerEl.appendChild(upNextEl);

    // Video title
    const titleEl = document.createElement('cite');
    const titleText = item.name || this.localize('Untitled Video');

    titleEl.className = 'vjs-playlist-name';
    titleEl.appendChild(document.createTextNode(titleText));
    titleEl.setAttribute('title', titleText);
    titleContainerEl.appendChild(titleEl);

    // Populate thumbnails alt with the video title
    this.thumbnail.getElementsByTagName('img').alt = titleText;

    // We add thumbnail video description only if specified in playlist options
    if (showDescription) {
      const descriptionEl = document.createElement('div');
      const descriptionText = item.description || '';

      descriptionEl.className = 'vjs-playlist-description';
      descriptionEl.appendChild(document.createTextNode(descriptionText));
      descriptionEl.setAttribute('title', descriptionText);
      titleContainerEl.appendChild(descriptionEl);
    }

    return li;
  }
}

videojs.registerComponent('PlaylistMenuItem', PlaylistMenuItem);

export default PlaylistMenuItem;
