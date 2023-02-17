"use strict";
/**
 * @license Apache-2.0
 * Copyright 2018 Novage LLC.
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *     http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */
function __export(m) {
    for (var p in m) if (!exports.hasOwnProperty(p)) exports[p] = m[p];
}
Object.defineProperty(exports, "__esModule", { value: true });
exports.version = "0.6.2";
__export(require("./engine"));
__export(require("./segment-manager"));
function initHlsJsPlayer(player) {
    if (player && player.config && player.config.loader && typeof player.config.loader.getEngine === "function") {
        initHlsJsEvents(player, player.config.loader.getEngine());
    }
}
exports.initHlsJsPlayer = initHlsJsPlayer;
function initClapprPlayer(player) {
    player.on("play", () => {
        const playback = player.core.getCurrentPlayback();
        if (playback._hls && !playback._hls._p2pm_linitialized) {
            playback._hls._p2pm_linitialized = true;
            initHlsJsPlayer(player.core.getCurrentPlayback()._hls);
        }
    });
}
exports.initClapprPlayer = initClapprPlayer;
function initFlowplayerHlsJsPlayer(player) {
    player.on("ready", () => initHlsJsPlayer(player.engine.hlsjs ? player.engine.hlsjs : player.engine.hls));
}
exports.initFlowplayerHlsJsPlayer = initFlowplayerHlsJsPlayer;
function initVideoJsContribHlsJsPlayer(player) {
    player.ready(() => {
        const options = player.tech_.options_;
        if (options && options.hlsjsConfig && options.hlsjsConfig.loader && typeof options.hlsjsConfig.loader.getEngine === "function") {
            initHlsJsEvents(player.tech_, options.hlsjsConfig.loader.getEngine());
        }
    });
}
exports.initVideoJsContribHlsJsPlayer = initVideoJsContribHlsJsPlayer;
function initVideoJsHlsJsPlugin() {
    if (videojs == undefined || videojs.Html5Hlsjs == undefined) {
        return;
    }
    videojs.Html5Hlsjs.addHook("beforeinitialize", (videojsPlayer, hlsjs) => {
        if (hlsjs.config && hlsjs.config.loader && typeof hlsjs.config.loader.getEngine === "function") {
            initHlsJsEvents(hlsjs, hlsjs.config.loader.getEngine());
        }
    });
}
exports.initVideoJsHlsJsPlugin = initVideoJsHlsJsPlugin;
function initMediaElementJsPlayer(mediaElement) {
    mediaElement.addEventListener("hlsFragChanged", (event) => {
        const hls = mediaElement.hlsPlayer;
        if (hls && hls.config && hls.config.loader && typeof hls.config.loader.getEngine === "function") {
            const engine = hls.config.loader.getEngine();
            if (event.data && (event.data.length > 1)) {
                const frag = event.data[1].frag;
                const byterange = (frag.byteRange.length !== 2)
                    ? undefined
                    : { offset: frag.byteRange[0], length: frag.byteRange[1] - frag.byteRange[0] };
                engine.setPlayingSegment(frag.url, byterange, frag.start, frag.duration);
            }
        }
    });
    mediaElement.addEventListener("hlsDestroying", async () => {
        const hls = mediaElement.hlsPlayer;
        if (hls && hls.config && hls.config.loader && typeof hls.config.loader.getEngine === "function") {
            const engine = hls.config.loader.getEngine();
            await engine.destroy();
        }
    });
    mediaElement.addEventListener("hlsError", (event) => {
        const hls = mediaElement.hlsPlayer;
        if (hls && hls.config && hls.config.loader && typeof hls.config.loader.getEngine === "function") {
            if ((event.data !== undefined) && (event.data.details === "bufferStalledError")) {
                const engine = hls.config.loader.getEngine();
                engine.setPlayingSegmentByCurrentTime(hls.media.currentTime);
            }
        }
    });
}
exports.initMediaElementJsPlayer = initMediaElementJsPlayer;
function initJwPlayer(player, hlsjsConfig) {
    const iid = setInterval(() => {
        if (player.hls && player.hls.config) {
            clearInterval(iid);
            Object.assign(player.hls.config, hlsjsConfig);
            initHlsJsPlayer(player.hls);
        }
    }, 200);
}
exports.initJwPlayer = initJwPlayer;
function initHlsJsEvents(player, engine) {
    player.on("hlsFragChanged", (_event, data) => {
        const frag = data.frag;
        const byterange = (frag.byteRange.length !== 2)
            ? undefined
            : { offset: frag.byteRange[0], length: frag.byteRange[1] - frag.byteRange[0] };
        engine.setPlayingSegment(frag.url, byterange, frag.start, frag.duration);
    });
    player.on("hlsDestroying", async () => {
        await engine.destroy();
    });
    player.on("hlsError", (_event, errorData) => {
        if (errorData.details === "bufferStalledError") {
            const htmlMediaElement = player.media === undefined
                ? player.el_ // videojs-contrib-hlsjs
                : player.media; // all others
            if (htmlMediaElement === undefined) {
                return;
            }
            engine.setPlayingSegmentByCurrentTime(htmlMediaElement.currentTime);
        }
    });
}
