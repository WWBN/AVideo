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
const events_1 = require("events");
const p2p_media_loader_core_1 = require("p2p-media-loader-core");
const segment_manager_1 = require("./segment-manager");
const hlsjs_loader_1 = require("./hlsjs-loader");
const hlsjs_loader_class_1 = require("./hlsjs-loader-class");
class Engine extends events_1.EventEmitter {
    constructor(settings = {}) {
        super();
        this.loader = new p2p_media_loader_core_1.HybridLoader(settings.loader);
        this.segmentManager = new segment_manager_1.SegmentManager(this.loader, settings.segments);
        Object.keys(p2p_media_loader_core_1.Events)
            .map(eventKey => p2p_media_loader_core_1.Events[eventKey])
            .forEach(event => this.loader.on(event, (...args) => this.emit(event, ...args)));
    }
    static isSupported() {
        return p2p_media_loader_core_1.HybridLoader.isSupported();
    }
    createLoaderClass() {
        return hlsjs_loader_class_1.createHlsJsLoaderClass(hlsjs_loader_1.HlsJsLoader, this);
    }
    async destroy() {
        await this.segmentManager.destroy();
    }
    getSettings() {
        return {
            segments: this.segmentManager.getSettings(),
            loader: this.loader.getSettings()
        };
    }
    getDetails() {
        return {
            loader: this.loader.getDetails()
        };
    }
    setPlayingSegment(url, byterange, start, duration) {
        this.segmentManager.setPlayingSegment(url, byterange, start, duration);
    }
    setPlayingSegmentByCurrentTime(playheadPosition) {
        this.segmentManager.setPlayingSegmentByCurrentTime(playheadPosition);
    }
}
exports.Engine = Engine;
