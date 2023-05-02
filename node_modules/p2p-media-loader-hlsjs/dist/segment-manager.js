"use strict";
/**
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
Object.defineProperty(exports, "__esModule", { value: true });
const p2p_media_loader_core_1 = require("p2p-media-loader-core");
const m3u8_parser_1 = require("m3u8-parser");
const defaultSettings = {
    forwardSegmentCount: 20,
    swarmId: undefined,
    assetsStorage: undefined,
};
class SegmentManager {
    constructor(loader, settings = {}) {
        this.masterPlaylist = null;
        this.variantPlaylists = new Map();
        this.segmentRequest = null;
        this.playQueue = [];
        this.onSegmentLoaded = (segment) => {
            if (this.segmentRequest && (this.segmentRequest.segmentUrl === segment.url) &&
                (byterangeToString(this.segmentRequest.segmentByterange) === segment.range)) {
                this.segmentRequest.onSuccess(segment.data.slice(0), segment.downloadBandwidth);
                this.segmentRequest = null;
            }
        };
        this.onSegmentError = (segment, error) => {
            if (this.segmentRequest && (this.segmentRequest.segmentUrl === segment.url) &&
                (byterangeToString(this.segmentRequest.segmentByterange) === segment.range)) {
                this.segmentRequest.onError(error);
                this.segmentRequest = null;
            }
        };
        this.onSegmentAbort = (segment) => {
            if (this.segmentRequest && (this.segmentRequest.segmentUrl === segment.url) &&
                (byterangeToString(this.segmentRequest.segmentByterange) === segment.range)) {
                this.segmentRequest.onError("Loading aborted: internal abort");
                this.segmentRequest = null;
            }
        };
        this.settings = Object.assign(Object.assign({}, defaultSettings), settings);
        this.loader = loader;
        this.loader.on(p2p_media_loader_core_1.Events.SegmentLoaded, this.onSegmentLoaded);
        this.loader.on(p2p_media_loader_core_1.Events.SegmentError, this.onSegmentError);
        this.loader.on(p2p_media_loader_core_1.Events.SegmentAbort, this.onSegmentAbort);
    }
    getSettings() {
        return this.settings;
    }
    processPlaylist(requestUrl, content, responseUrl) {
        const parser = new m3u8_parser_1.Parser();
        parser.push(content);
        parser.end();
        const playlist = new Playlist(requestUrl, responseUrl, parser.manifest);
        if (playlist.manifest.playlists) {
            this.masterPlaylist = playlist;
            for (const [key, variantPlaylist] of this.variantPlaylists) {
                const { streamSwarmId, found, index } = this.getStreamSwarmId(variantPlaylist.requestUrl);
                if (!found) {
                    this.variantPlaylists.delete(key);
                }
                else {
                    variantPlaylist.streamSwarmId = streamSwarmId;
                    variantPlaylist.streamId = "V" + index.toString();
                }
            }
        }
        else {
            const { streamSwarmId, found, index } = this.getStreamSwarmId(requestUrl);
            if (found || (this.masterPlaylist === null)) { // do not add audio and subtitles to variants
                playlist.streamSwarmId = streamSwarmId;
                playlist.streamId = (this.masterPlaylist === null ? undefined : "V" + index.toString());
                this.variantPlaylists.set(requestUrl, playlist);
                this.updateSegments();
            }
        }
    }
    async loadPlaylist(url) {
        const assetsStorage = this.settings.assetsStorage;
        let xhr;
        if (assetsStorage !== undefined) {
            let masterSwarmId;
            masterSwarmId = this.getMasterSwarmId();
            if (masterSwarmId === undefined) {
                masterSwarmId = url.split("?")[0];
            }
            const asset = await assetsStorage.getAsset(url, undefined, masterSwarmId);
            if (asset !== undefined) {
                xhr = {
                    responseURL: asset.responseUri,
                    response: asset.data,
                };
            }
            else {
                xhr = await this.loadContent(url, "text");
                assetsStorage.storeAsset({
                    masterManifestUri: this.masterPlaylist !== null ? this.masterPlaylist.requestUrl : url,
                    masterSwarmId: masterSwarmId,
                    requestUri: url,
                    responseUri: xhr.responseURL,
                    data: xhr.response,
                });
            }
        }
        else {
            xhr = await this.loadContent(url, "text");
        }
        this.processPlaylist(url, xhr.response, xhr.responseURL);
        return xhr;
    }
    async loadSegment(url, byterange) {
        const segmentLocation = this.getSegmentLocation(url, byterange);
        const byteRangeString = byterangeToString(byterange);
        if (!segmentLocation) {
            let content;
            // Not a segment from variants; usually can be: init, audio or subtitles segment, encription key etc.
            const assetsStorage = this.settings.assetsStorage;
            if (assetsStorage !== undefined) {
                let masterManifestUri = this.masterPlaylist !== null ? this.masterPlaylist.requestUrl : undefined;
                let masterSwarmId;
                masterSwarmId = this.getMasterSwarmId();
                if (masterSwarmId === undefined && this.variantPlaylists.size === 1) {
                    masterSwarmId = this.variantPlaylists.values().next().value.requestUrl.split("?")[0];
                }
                if (masterManifestUri === undefined && this.variantPlaylists.size === 1) {
                    masterManifestUri = this.variantPlaylists.values().next().value.requestUrl;
                }
                if (masterSwarmId !== undefined && masterManifestUri !== undefined) {
                    const asset = await assetsStorage.getAsset(url, byteRangeString, masterSwarmId);
                    if (asset !== undefined) {
                        content = asset.data;
                    }
                    else {
                        const xhr = await this.loadContent(url, "arraybuffer", byteRangeString);
                        content = xhr.response;
                        assetsStorage.storeAsset({
                            masterManifestUri: masterManifestUri,
                            masterSwarmId: masterSwarmId,
                            requestUri: url,
                            requestRange: byteRangeString,
                            responseUri: xhr.responseURL,
                            data: content,
                        });
                    }
                }
            }
            if (content === undefined) {
                const xhr = await this.loadContent(url, "arraybuffer", byteRangeString);
                content = xhr.response;
            }
            return { content, downloadBandwidth: 0 };
        }
        const segmentSequence = (segmentLocation.playlist.manifest.mediaSequence ? segmentLocation.playlist.manifest.mediaSequence : 0)
            + segmentLocation.segmentIndex;
        if (this.playQueue.length > 0) {
            const previousSegment = this.playQueue[this.playQueue.length - 1];
            if (previousSegment.segmentSequence !== segmentSequence - 1) {
                // Reset play queue in case of segment loading out of sequence
                this.playQueue = [];
            }
        }
        if (this.segmentRequest) {
            this.segmentRequest.onError("Cancel segment request: simultaneous segment requests are not supported");
        }
        const promise = new Promise((resolve, reject) => {
            this.segmentRequest = new SegmentRequest(url, byterange, segmentSequence, segmentLocation.playlist.requestUrl, (content, downloadBandwidth) => resolve({ content, downloadBandwidth }), error => reject(error));
        });
        this.playQueue.push({ segmentUrl: url, segmentByterange: byterange, segmentSequence: segmentSequence });
        this.loadSegments(segmentLocation.playlist, segmentLocation.segmentIndex, true);
        return promise;
    }
    setPlayingSegment(url, byterange, start, duration) {
        const urlIndex = this.playQueue.findIndex(segment => (segment.segmentUrl == url) && compareByterange(segment.segmentByterange, byterange));
        if (urlIndex >= 0) {
            this.playQueue = this.playQueue.slice(urlIndex);
            this.playQueue[0].playPosition = { start, duration };
            this.updateSegments();
        }
    }
    setPlayingSegmentByCurrentTime(playheadPosition) {
        if (this.playQueue.length === 0 || !this.playQueue[0].playPosition) {
            return;
        }
        const currentSegmentPosition = this.playQueue[0].playPosition;
        const segmentEndTime = currentSegmentPosition.start + currentSegmentPosition.duration;
        if (segmentEndTime - playheadPosition < 0.2) {
            // means that current segment is (almost) finished playing
            // remove it from queue
            this.playQueue = this.playQueue.slice(1);
            this.updateSegments();
        }
    }
    abortSegment(url, byterange) {
        if (this.segmentRequest && (this.segmentRequest.segmentUrl === url) &&
            compareByterange(this.segmentRequest.segmentByterange, byterange)) {
            this.segmentRequest.onSuccess(undefined, 0);
            this.segmentRequest = null;
        }
    }
    async destroy() {
        if (this.segmentRequest) {
            this.segmentRequest.onError("Loading aborted: object destroyed");
            this.segmentRequest = null;
        }
        this.masterPlaylist = null;
        this.variantPlaylists.clear();
        this.playQueue = [];
        if (this.settings.assetsStorage !== undefined) {
            await this.settings.assetsStorage.destroy();
        }
        await this.loader.destroy();
    }
    updateSegments() {
        if (!this.segmentRequest) {
            return;
        }
        const segmentLocation = this.getSegmentLocation(this.segmentRequest.segmentUrl, this.segmentRequest.segmentByterange);
        if (segmentLocation) {
            this.loadSegments(segmentLocation.playlist, segmentLocation.segmentIndex, false);
        }
    }
    getSegmentLocation(url, byterange) {
        for (const playlist of this.variantPlaylists.values()) {
            const segmentIndex = playlist.getSegmentIndex(url, byterange);
            if (segmentIndex >= 0) {
                return { playlist: playlist, segmentIndex: segmentIndex };
            }
        }
        return undefined;
    }
    async loadSegments(playlist, segmentIndex, requestFirstSegment) {
        const segments = [];
        const playlistSegments = playlist.manifest.segments;
        const initialSequence = playlist.manifest.mediaSequence ? playlist.manifest.mediaSequence : 0;
        let loadSegmentId = null;
        let priority = Math.max(0, this.playQueue.length - 1);
        const masterSwarmId = this.getMasterSwarmId();
        for (let i = segmentIndex; i < playlistSegments.length && segments.length < this.settings.forwardSegmentCount; ++i) {
            const segment = playlist.manifest.segments[i];
            const url = playlist.getSegmentAbsoluteUrl(segment.uri);
            const byterange = segment.byterange;
            const id = this.getSegmentId(playlist, initialSequence + i);
            segments.push({
                id: id,
                url: url,
                masterSwarmId: masterSwarmId !== undefined ? masterSwarmId : playlist.streamSwarmId,
                masterManifestUri: this.masterPlaylist !== null ? this.masterPlaylist.requestUrl : playlist.requestUrl,
                streamId: playlist.streamId,
                sequence: (initialSequence + i).toString(),
                range: byterangeToString(byterange),
                priority: priority++,
            });
            if (requestFirstSegment && !loadSegmentId) {
                loadSegmentId = id;
            }
        }
        this.loader.load(segments, playlist.streamSwarmId);
        if (loadSegmentId) {
            const segment = await this.loader.getSegment(loadSegmentId);
            if (segment) { // Segment already loaded by loader
                this.onSegmentLoaded(segment);
            }
        }
    }
    getSegmentId(playlist, segmentSequence) {
        return `${playlist.streamSwarmId}+${segmentSequence}`;
    }
    getMasterSwarmId() {
        const settingsSwarmId = (this.settings.swarmId && (this.settings.swarmId.length !== 0)) ? this.settings.swarmId : undefined;
        if (settingsSwarmId !== undefined) {
            return settingsSwarmId;
        }
        return (this.masterPlaylist !== null)
            ? this.masterPlaylist.requestUrl.split("?")[0]
            : undefined;
    }
    getStreamSwarmId(playlistUrl) {
        const masterSwarmId = this.getMasterSwarmId();
        if (this.masterPlaylist !== null) {
            for (let i = 0; i < this.masterPlaylist.manifest.playlists.length; ++i) {
                const url = new URL(this.masterPlaylist.manifest.playlists[i].uri, this.masterPlaylist.responseUrl).toString();
                if (url === playlistUrl) {
                    return { streamSwarmId: `${masterSwarmId}+V${i}`, found: true, index: i };
                }
            }
        }
        return {
            streamSwarmId: masterSwarmId !== undefined ? masterSwarmId : playlistUrl.split("?")[0],
            found: false,
            index: -1
        };
    }
    async loadContent(url, responseType, range) {
        return new Promise((resolve, reject) => {
            const xhr = new XMLHttpRequest();
            xhr.open("GET", url, true);
            xhr.responseType = responseType;
            if (range) {
                xhr.setRequestHeader("Range", range);
            }
            xhr.addEventListener("readystatechange", () => {
                if (xhr.readyState !== 4) {
                    return;
                }
                if (xhr.status >= 200 && xhr.status < 300) {
                    resolve(xhr);
                }
                else {
                    reject(xhr.statusText);
                }
            });
            const xhrSetup = this.loader.getSettings().xhrSetup;
            if (xhrSetup) {
                xhrSetup(xhr, url);
            }
            xhr.send();
        });
    }
}
exports.SegmentManager = SegmentManager;
class Playlist {
    constructor(requestUrl, responseUrl, manifest) {
        this.requestUrl = requestUrl;
        this.responseUrl = responseUrl;
        this.manifest = manifest;
        this.streamSwarmId = "";
    }
    getSegmentIndex(url, byterange) {
        for (let i = 0; i < this.manifest.segments.length; ++i) {
            const segment = this.manifest.segments[i];
            const segmentUrl = this.getSegmentAbsoluteUrl(segment.uri);
            if ((url === segmentUrl) && compareByterange(segment.byterange, byterange)) {
                return i;
            }
        }
        return -1;
    }
    getSegmentAbsoluteUrl(segmentUrl) {
        return new URL(segmentUrl, this.responseUrl).toString();
    }
}
class SegmentRequest {
    constructor(segmentUrl, segmentByterange, segmentSequence, playlistRequestUrl, onSuccess, onError) {
        this.segmentUrl = segmentUrl;
        this.segmentByterange = segmentByterange;
        this.segmentSequence = segmentSequence;
        this.playlistRequestUrl = playlistRequestUrl;
        this.onSuccess = onSuccess;
        this.onError = onError;
    }
}
function compareByterange(b1, b2) {
    return (b1 === undefined)
        ? (b2 === undefined)
        : ((b2 !== undefined) && (b1.length === b2.length) && (b1.offset === b2.offset));
}
function byterangeToString(byterange) {
    if (byterange === undefined) {
        return undefined;
    }
    const end = byterange.offset + byterange.length - 1;
    return `bytes=${byterange.offset}-${end}`;
}
