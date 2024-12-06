import videojs from 'video.js';
import initOverlayComponent from './overlay-component';
import OverlayPlugin from './plugin';
import {version as VERSION} from '../package.json';

initOverlayComponent(videojs);

OverlayPlugin.VERSION = VERSION;

videojs.registerPlugin('overlay', OverlayPlugin);

export default OverlayPlugin;
