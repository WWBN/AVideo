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
var Events;
(function (Events) {
    /**
     * Emitted when segment has been downloaded.
     * Args: segment
     */
    Events["SegmentLoaded"] = "segment_loaded";
    /**
     * Emitted when an error occurred while loading the segment.
     * Args: segment, error
     */
    Events["SegmentError"] = "segment_error";
    /**
     * Emitted for each segment that does not hit into a new segments queue when the load() method is called.
     * Args: segment
     */
    Events["SegmentAbort"] = "segment_abort";
    /**
     * Emitted when a peer is connected.
     * Args: peer
     */
    Events["PeerConnect"] = "peer_connect";
    /**
     * Emitted when a peer is disconnected.
     * Args: peerId
     */
    Events["PeerClose"] = "peer_close";
    /**
     * Emitted when a segment piece has been downloaded.
     * Args: method (can be "http" or "p2p" only), bytes
     */
    Events["PieceBytesDownloaded"] = "piece_bytes_downloaded";
    /**
     * Emitted when a segment piece has been uploaded.
     * Args: method (can be "p2p" only), bytes
     */
    Events["PieceBytesUploaded"] = "piece_bytes_uploaded";
})(Events = exports.Events || (exports.Events = {}));
