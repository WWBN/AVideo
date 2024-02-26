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
const Debug = require("debug");
const loader_interface_1 = require("./loader-interface");
const events_1 = require("events");
const http_media_manager_1 = require("./http-media-manager");
const p2p_media_manager_1 = require("./p2p-media-manager");
const media_peer_1 = require("./media-peer");
const bandwidth_approximator_1 = require("./bandwidth-approximator");
const segments_memory_storage_1 = require("./segments-memory-storage");
const getBrowserRTC = require("get-browser-rtc");
const Peer = require("simple-peer");
const defaultSettings = {
    cachedSegmentExpiration: 5 * 60 * 1000,
    cachedSegmentsCount: 30,
    useP2P: true,
    consumeOnly: false,
    requiredSegmentsPriority: 1,
    simultaneousHttpDownloads: 2,
    httpDownloadProbability: 0.1,
    httpDownloadProbabilityInterval: 1000,
    httpDownloadProbabilitySkipIfNoPeers: false,
    httpFailedSegmentTimeout: 10000,
    httpDownloadMaxPriority: 20,
    httpDownloadInitialTimeout: 0,
    httpDownloadInitialTimeoutPerSegment: 4000,
    httpUseRanges: false,
    simultaneousP2PDownloads: 3,
    p2pDownloadMaxPriority: 20,
    p2pSegmentDownloadTimeout: 60000,
    webRtcMaxMessageSize: 64 * 1024 - 1,
    trackerAnnounce: ["wss://tracker.novage.com.ua", "wss://tracker.openwebtorrent.com"],
    peerRequestsPerAnnounce: 10,
    rtcConfig: Peer.config
};
class HybridLoader extends events_1.EventEmitter {
    constructor(settings = {}) {
        super();
        this.debug = Debug("p2pml:hybrid-loader");
        this.debugSegments = Debug("p2pml:hybrid-loader-segments");
        this.segmentsQueue = [];
        this.bandwidthApproximator = new bandwidth_approximator_1.BandwidthApproximator();
        this.httpDownloadInitialTimeoutTimestamp = -Infinity;
        this.processInitialSegmentTimeout = async () => {
            if (this.httpRandomDownloadInterval === undefined) {
                return; // Instance destroyed
            }
            if (this.masterSwarmId !== undefined) {
                const storageSegments = await this.segmentsStorage.getSegmentsMap(this.masterSwarmId);
                if (this.processSegmentsQueue(storageSegments) && !this.settings.consumeOnly) {
                    this.p2pManager.sendSegmentsMapToAll(this.createSegmentsMap(storageSegments));
                }
            }
            if (this.httpDownloadInitialTimeoutTimestamp !== -Infinity) {
                // Set one more timeout for a next segment
                setTimeout(this.processInitialSegmentTimeout, this.settings.httpDownloadInitialTimeoutPerSegment);
            }
        };
        this.downloadRandomSegmentOverHttp = async () => {
            if (this.masterSwarmId === undefined ||
                this.httpRandomDownloadInterval === undefined ||
                this.httpDownloadInitialTimeoutTimestamp !== -Infinity ||
                this.httpManager.getActiveDownloadsCount() >= this.settings.simultaneousHttpDownloads ||
                (this.settings.httpDownloadProbabilitySkipIfNoPeers && this.p2pManager.getPeers().size === 0) ||
                this.settings.consumeOnly) {
                return;
            }
            const storageSegments = await this.segmentsStorage.getSegmentsMap(this.masterSwarmId);
            const segmentsMap = this.p2pManager.getOvrallSegmentsMap();
            const pendingQueue = this.segmentsQueue.filter(s => !this.p2pManager.isDownloading(s) &&
                !this.httpManager.isDownloading(s) &&
                !segmentsMap.has(s.id) &&
                !this.httpManager.isFailed(s) &&
                (s.priority <= this.settings.httpDownloadMaxPriority) &&
                !storageSegments.has(s.id));
            if (pendingQueue.length == 0) {
                return;
            }
            if (Math.random() > this.settings.httpDownloadProbability * pendingQueue.length) {
                return;
            }
            const segment = pendingQueue[Math.floor(Math.random() * pendingQueue.length)];
            this.debugSegments("HTTP download (random)", segment.priority, segment.url);
            this.httpManager.download(segment);
            this.p2pManager.sendSegmentsMapToAll(this.createSegmentsMap(storageSegments));
        };
        this.onPieceBytesDownloaded = (method, bytes, peerId) => {
            this.bandwidthApproximator.addBytes(bytes, this.now());
            this.emit(loader_interface_1.Events.PieceBytesDownloaded, method, bytes, peerId);
        };
        this.onPieceBytesUploaded = (method, bytes, peerId) => {
            this.emit(loader_interface_1.Events.PieceBytesUploaded, method, bytes, peerId);
        };
        this.onSegmentLoaded = async (segment, data, peerId) => {
            this.debugSegments("segment loaded", segment.id, segment.url);
            if (this.masterSwarmId === undefined) {
                return;
            }
            segment.data = data;
            segment.downloadBandwidth = this.bandwidthApproximator.getBandwidth(this.now());
            await this.segmentsStorage.storeSegment(segment);
            this.emit(loader_interface_1.Events.SegmentLoaded, segment, peerId);
            let storageSegments;
            storageSegments = (storageSegments === undefined ? await this.segmentsStorage.getSegmentsMap(this.masterSwarmId) : storageSegments);
            this.processSegmentsQueue(storageSegments);
            if (!this.settings.consumeOnly) {
                this.p2pManager.sendSegmentsMapToAll(this.createSegmentsMap(storageSegments));
            }
        };
        this.onSegmentError = async (segment, details, peerId) => {
            this.debugSegments("segment error", segment.id, segment.url, peerId, details);
            this.emit(loader_interface_1.Events.SegmentError, segment, details, peerId);
            if (this.masterSwarmId !== undefined) {
                const storageSegments = await this.segmentsStorage.getSegmentsMap(this.masterSwarmId);
                if (this.processSegmentsQueue(storageSegments) && !this.settings.consumeOnly) {
                    this.p2pManager.sendSegmentsMapToAll(this.createSegmentsMap(storageSegments));
                }
            }
        };
        this.onPeerConnect = async (peer) => {
            this.emit(loader_interface_1.Events.PeerConnect, peer);
            if (!this.settings.consumeOnly && this.masterSwarmId !== undefined) {
                this.p2pManager.sendSegmentsMap(peer.id, this.createSegmentsMap(await this.segmentsStorage.getSegmentsMap(this.masterSwarmId)));
            }
        };
        this.onPeerClose = (peerId) => {
            this.emit(loader_interface_1.Events.PeerClose, peerId);
        };
        this.onTrackerUpdate = async (data) => {
            if (this.httpDownloadInitialTimeoutTimestamp !== -Infinity &&
                data.incomplete !== undefined && data.incomplete <= 1) {
                this.debugSegments("cancel initial HTTP download timeout - no peers");
                this.httpDownloadInitialTimeoutTimestamp = -Infinity;
                if (this.masterSwarmId !== undefined) {
                    const storageSegments = await this.segmentsStorage.getSegmentsMap(this.masterSwarmId);
                    if (this.processSegmentsQueue(storageSegments) && !this.settings.consumeOnly) {
                        this.p2pManager.sendSegmentsMapToAll(this.createSegmentsMap(storageSegments));
                    }
                }
            }
        };
        this.settings = Object.assign(Object.assign({}, defaultSettings), settings);
        if (settings.bufferedSegmentsCount) {
            if (settings.p2pDownloadMaxPriority === undefined) {
                this.settings.p2pDownloadMaxPriority = settings.bufferedSegmentsCount;
            }
            if (settings.httpDownloadMaxPriority === undefined) {
                this.settings.p2pDownloadMaxPriority = settings.bufferedSegmentsCount;
            }
            delete this.settings.bufferedSegmentsCount;
        }
        this.segmentsStorage = (this.settings.segmentsStorage === undefined
            ? new segments_memory_storage_1.SegmentsMemoryStorage(this.settings)
            : this.settings.segmentsStorage);
        this.debug("loader settings", this.settings);
        this.httpManager = this.createHttpManager();
        this.httpManager.on("segment-loaded", this.onSegmentLoaded);
        this.httpManager.on("segment-error", this.onSegmentError);
        this.httpManager.on("bytes-downloaded", (bytes) => this.onPieceBytesDownloaded("http", bytes));
        this.p2pManager = this.createP2PManager();
        this.p2pManager.on("segment-loaded", this.onSegmentLoaded);
        this.p2pManager.on("segment-error", this.onSegmentError);
        this.p2pManager.on("peer-data-updated", async () => {
            if (this.masterSwarmId === undefined) {
                return;
            }
            const storageSegments = await this.segmentsStorage.getSegmentsMap(this.masterSwarmId);
            if (this.processSegmentsQueue(storageSegments) && !this.settings.consumeOnly) {
                this.p2pManager.sendSegmentsMapToAll(this.createSegmentsMap(storageSegments));
            }
        });
        this.p2pManager.on("bytes-downloaded", (bytes, peerId) => this.onPieceBytesDownloaded("p2p", bytes, peerId));
        this.p2pManager.on("bytes-uploaded", (bytes, peerId) => this.onPieceBytesUploaded("p2p", bytes, peerId));
        this.p2pManager.on("peer-connected", this.onPeerConnect);
        this.p2pManager.on("peer-closed", this.onPeerClose);
        this.p2pManager.on("tracker-update", this.onTrackerUpdate);
    }
    static isSupported() {
        const browserRtc = getBrowserRTC();
        return (browserRtc && (browserRtc.RTCPeerConnection.prototype.createDataChannel !== undefined));
    }
    createHttpManager() {
        return new http_media_manager_1.HttpMediaManager(this.settings);
    }
    createP2PManager() {
        return new p2p_media_manager_1.P2PMediaManager(this.segmentsStorage, this.settings);
    }
    async load(segments, streamSwarmId) {
        if (this.httpRandomDownloadInterval === undefined) { // Do once on first call
            this.httpRandomDownloadInterval = setInterval(this.downloadRandomSegmentOverHttp, this.settings.httpDownloadProbabilityInterval);
            if (this.settings.httpDownloadInitialTimeout > 0 && this.settings.httpDownloadInitialTimeoutPerSegment > 0) {
                // Initialize initial HTTP download timeout (i.e. download initial segments over P2P)
                this.debugSegments("enable initial HTTP download timeout", this.settings.httpDownloadInitialTimeout, "per segment", this.settings.httpDownloadInitialTimeoutPerSegment);
                this.httpDownloadInitialTimeoutTimestamp = this.now();
                setTimeout(this.processInitialSegmentTimeout, this.settings.httpDownloadInitialTimeoutPerSegment + 100);
            }
        }
        if (segments.length > 0) {
            this.masterSwarmId = segments[0].masterSwarmId;
        }
        if (this.masterSwarmId !== undefined) {
            this.p2pManager.setStreamSwarmId(streamSwarmId, this.masterSwarmId);
        }
        this.debug("load segments");
        let updateSegmentsMap = false;
        // stop all http requests and p2p downloads for segments that are not in the new load
        for (const segment of this.segmentsQueue) {
            if (!segments.find(f => f.url == segment.url)) {
                this.debug("remove segment", segment.url);
                if (this.httpManager.isDownloading(segment)) {
                    updateSegmentsMap = true;
                    this.httpManager.abort(segment);
                }
                else {
                    this.p2pManager.abort(segment);
                }
                this.emit(loader_interface_1.Events.SegmentAbort, segment);
            }
        }
        if (this.debug.enabled) {
            for (const segment of segments) {
                if (!this.segmentsQueue.find(f => f.url == segment.url)) {
                    this.debug("add segment", segment.url);
                }
            }
        }
        this.segmentsQueue = segments;
        if (this.masterSwarmId === undefined) {
            return;
        }
        let storageSegments = await this.segmentsStorage.getSegmentsMap(this.masterSwarmId);
        updateSegmentsMap = (this.processSegmentsQueue(storageSegments) || updateSegmentsMap);
        if (await this.cleanSegmentsStorage()) {
            storageSegments = await this.segmentsStorage.getSegmentsMap(this.masterSwarmId);
            updateSegmentsMap = true;
        }
        if (updateSegmentsMap && !this.settings.consumeOnly) {
            this.p2pManager.sendSegmentsMapToAll(this.createSegmentsMap(storageSegments));
        }
    }
    async getSegment(id) {
        return this.masterSwarmId === undefined
            ? undefined
            : this.segmentsStorage.getSegment(id, this.masterSwarmId);
    }
    getSettings() {
        return this.settings;
    }
    getDetails() {
        return {
            peerId: this.p2pManager.getPeerId()
        };
    }
    async destroy() {
        if (this.httpRandomDownloadInterval !== undefined) {
            clearInterval(this.httpRandomDownloadInterval);
            this.httpRandomDownloadInterval = undefined;
        }
        this.httpDownloadInitialTimeoutTimestamp = -Infinity;
        this.segmentsQueue = [];
        this.httpManager.destroy();
        this.p2pManager.destroy();
        this.masterSwarmId = undefined;
        await this.segmentsStorage.destroy();
    }
    processSegmentsQueue(storageSegments) {
        this.debugSegments("process segments queue. priority", this.segmentsQueue.length > 0 ? this.segmentsQueue[0].priority : 0);
        if (this.masterSwarmId === undefined || this.segmentsQueue.length === 0) {
            return false;
        }
        let updateSegmentsMap = false;
        let segmentsMap;
        let httpAllowed = true;
        if (this.httpDownloadInitialTimeoutTimestamp !== -Infinity) {
            let firstNotDownloadePriority;
            for (const segment of this.segmentsQueue) {
                if (!storageSegments.has(segment.id)) {
                    firstNotDownloadePriority = segment.priority;
                    break;
                }
            }
            const httpTimeout = this.now() - this.httpDownloadInitialTimeoutTimestamp;
            httpAllowed = (httpTimeout >= this.settings.httpDownloadInitialTimeout)
                || ((firstNotDownloadePriority !== undefined) && (httpTimeout > this.settings.httpDownloadInitialTimeoutPerSegment) && (firstNotDownloadePriority <= 0));
            if (httpAllowed) {
                this.debugSegments("cancel initial HTTP download timeout - timed out");
                this.httpDownloadInitialTimeoutTimestamp = -Infinity;
            }
        }
        for (let index = 0; index < this.segmentsQueue.length; index++) {
            const segment = this.segmentsQueue[index];
            if (storageSegments.has(segment.id) || this.httpManager.isDownloading(segment)) {
                continue;
            }
            if (segment.priority <= this.settings.requiredSegmentsPriority && httpAllowed && !this.httpManager.isFailed(segment)) {
                // Download required segments over HTTP
                if (this.httpManager.getActiveDownloadsCount() >= this.settings.simultaneousHttpDownloads) {
                    // Not enough HTTP download resources. Abort one of the HTTP downloads.
                    for (let i = this.segmentsQueue.length - 1; i > index; i--) {
                        const segmentToAbort = this.segmentsQueue[i];
                        if (this.httpManager.isDownloading(segmentToAbort)) {
                            this.debugSegments("cancel HTTP download", segmentToAbort.priority, segmentToAbort.url);
                            this.httpManager.abort(segmentToAbort);
                            break;
                        }
                    }
                }
                if (this.httpManager.getActiveDownloadsCount() < this.settings.simultaneousHttpDownloads) {
                    // Abort P2P download of the required segment if any and force HTTP download
                    const downloadedPieces = this.p2pManager.abort(segment);
                    this.httpManager.download(segment, downloadedPieces);
                    this.debugSegments("HTTP download (priority)", segment.priority, segment.url);
                    updateSegmentsMap = true;
                    continue;
                }
            }
            if (this.p2pManager.isDownloading(segment)) {
                continue;
            }
            if (segment.priority <= this.settings.requiredSegmentsPriority) { // Download required segments over P2P
                segmentsMap = segmentsMap ? segmentsMap : this.p2pManager.getOvrallSegmentsMap();
                if (segmentsMap.get(segment.id) !== media_peer_1.MediaPeerSegmentStatus.Loaded) {
                    continue;
                }
                if (this.p2pManager.getActiveDownloadsCount() >= this.settings.simultaneousP2PDownloads) {
                    // Not enough P2P download resources. Abort one of the P2P downloads.
                    for (let i = this.segmentsQueue.length - 1; i > index; i--) {
                        const segmentToAbort = this.segmentsQueue[i];
                        if (this.p2pManager.isDownloading(segmentToAbort)) {
                            this.debugSegments("cancel P2P download", segmentToAbort.priority, segmentToAbort.url);
                            this.p2pManager.abort(segmentToAbort);
                            break;
                        }
                    }
                }
                if (this.p2pManager.getActiveDownloadsCount() < this.settings.simultaneousP2PDownloads) {
                    if (this.p2pManager.download(segment)) {
                        this.debugSegments("P2P download (priority)", segment.priority, segment.url);
                        continue;
                    }
                }
                continue;
            }
            if (this.p2pManager.getActiveDownloadsCount() < this.settings.simultaneousP2PDownloads &&
                segment.priority <= this.settings.p2pDownloadMaxPriority) {
                if (this.p2pManager.download(segment)) {
                    this.debugSegments("P2P download", segment.priority, segment.url);
                }
            }
        }
        return updateSegmentsMap;
    }
    getStreamSwarmId(segment) {
        return segment.streamId === undefined ? segment.masterSwarmId : `${segment.masterSwarmId}+${segment.streamId}`;
    }
    createSegmentsMap(storageSegments) {
        const segmentsMap = {};
        const addSegmentToMap = (segment, status) => {
            const streamSwarmId = this.getStreamSwarmId(segment);
            const segmentId = segment.sequence;
            let segmentsIdsAndStatuses = segmentsMap[streamSwarmId];
            if (segmentsIdsAndStatuses === undefined) {
                segmentsIdsAndStatuses = ["", []];
                segmentsMap[streamSwarmId] = segmentsIdsAndStatuses;
            }
            const segmentsStatuses = segmentsIdsAndStatuses[1];
            segmentsIdsAndStatuses[0] += ((segmentsStatuses.length == 0) ? segmentId : `|${segmentId}`);
            segmentsStatuses.push(status);
        };
        for (const storageSegment of storageSegments.values()) {
            addSegmentToMap(storageSegment.segment, media_peer_1.MediaPeerSegmentStatus.Loaded);
        }
        for (const download of this.httpManager.getActiveDownloads().values()) {
            addSegmentToMap(download.segment, media_peer_1.MediaPeerSegmentStatus.LoadingByHttp);
        }
        return segmentsMap;
    }
    async cleanSegmentsStorage() {
        if (this.masterSwarmId === undefined) {
            return false;
        }
        return this.segmentsStorage.clean(this.masterSwarmId, (id) => this.segmentsQueue.find(queueSegment => queueSegment.id === id) !== undefined);
    }
    now() {
        return performance.now();
    }
}
exports.HybridLoader = HybridLoader;
