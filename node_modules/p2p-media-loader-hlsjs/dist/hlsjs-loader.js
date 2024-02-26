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
const DEFAULT_DOWNLOAD_LATENCY = 1;
const DEFAULT_DOWNLOAD_BANDWIDTH = 12500; // bytes per millisecond
class HlsJsLoader {
    constructor(segmentManager) {
        this.stats = {}; // required for older versions of hls.js
        this.segmentManager = segmentManager;
    }
    async load(context, _config, callbacks) {
        if (context.type) {
            try {
                const result = await this.segmentManager.loadPlaylist(context.url);
                this.successPlaylist(result, context, callbacks);
            }
            catch (e) {
                this.error(e, context, callbacks);
            }
        }
        else if (context.frag) {
            try {
                const result = await this.segmentManager.loadSegment(context.url, (context.rangeStart == undefined) || (context.rangeEnd == undefined)
                    ? undefined
                    : { offset: context.rangeStart, length: context.rangeEnd - context.rangeStart });
                if (result.content !== undefined) {
                    setTimeout(() => this.successSegment(result.content, result.downloadBandwidth, context, callbacks), 0);
                }
            }
            catch (e) {
                setTimeout(() => this.error(e, context, callbacks), 0);
            }
        }
        else {
            console.warn("Unknown load request", context);
        }
    }
    abort(context) {
        this.segmentManager.abortSegment(context.url, (context.rangeStart == undefined) || (context.rangeEnd == undefined)
            ? undefined
            : { offset: context.rangeStart, length: context.rangeEnd - context.rangeStart });
    }
    successPlaylist(xhr, context, callbacks) {
        const now = performance.now();
        this.stats.trequest = now - 300;
        this.stats.tfirst = now - 200;
        this.stats.tload = now;
        this.stats.loaded = xhr.response.length;
        callbacks.onSuccess({
            url: xhr.responseURL,
            data: xhr.response
        }, this.stats, context);
    }
    successSegment(content, downloadBandwidth, context, callbacks) {
        const now = performance.now();
        const downloadTime = content.byteLength / (((downloadBandwidth === undefined) || (downloadBandwidth <= 0)) ? DEFAULT_DOWNLOAD_BANDWIDTH : downloadBandwidth);
        this.stats.trequest = now - DEFAULT_DOWNLOAD_LATENCY - downloadTime;
        this.stats.tfirst = now - downloadTime;
        this.stats.tload = now;
        this.stats.loaded = content.byteLength;
        callbacks.onSuccess({
            url: context.url,
            data: content
        }, this.stats, context);
    }
    error(error, context, callbacks) {
        callbacks.onError(error, context);
    }
}
exports.HlsJsLoader = HlsJsLoader;
