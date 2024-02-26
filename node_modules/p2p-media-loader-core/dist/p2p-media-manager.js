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
const Client = require("bittorrent-tracker/client");
const stringly_typed_event_emitter_1 = require("./stringly-typed-event-emitter");
const media_peer_1 = require("./media-peer");
const buffer_1 = require("buffer");
const sha1 = require("sha.js/sha1");
const index_1 = require("./index");
const PEER_PROTOCOL_VERSION = 2;
const PEER_ID_VERSION_STRING = index_1.version.replace(/\d*./g, v => `0${parseInt(v, 10) % 100}`.slice(-2)).slice(0, 4);
const PEER_ID_VERSION_PREFIX = `-WW${PEER_ID_VERSION_STRING}-`; // Using WebTorrent client ID in order to not be banned by websocket trackers
class PeerSegmentRequest {
    constructor(peerId, segment) {
        this.peerId = peerId;
        this.segment = segment;
    }
}
function generatePeerId() {
    const PEER_ID_SYMBOLS = "ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789";
    const PEER_ID_LENGTH = 20;
    let peerId = PEER_ID_VERSION_PREFIX;
    for (let i = 0; i < PEER_ID_LENGTH - PEER_ID_VERSION_PREFIX.length; i++) {
        peerId += PEER_ID_SYMBOLS.charAt(Math.floor(Math.random() * PEER_ID_SYMBOLS.length));
    }
    return new TextEncoder().encode(peerId).buffer;
}
class P2PMediaManager extends stringly_typed_event_emitter_1.STEEmitter {
    constructor(sementsStorage, settings) {
        super();
        this.sementsStorage = sementsStorage;
        this.settings = settings;
        this.trackerClient = null;
        this.peers = new Map();
        this.peerCandidates = new Map();
        this.peerSegmentRequests = new Map();
        this.streamSwarmId = null;
        this.debug = Debug("p2pml:p2p-media-manager");
        this.pendingTrackerClient = null;
        this.onTrackerError = (error) => {
            this.debug("tracker error", error);
        };
        this.onTrackerWarning = (warning) => {
            this.debug("tracker warning", warning);
        };
        this.onTrackerUpdate = (data) => {
            this.debug("tracker update", data);
            this.emit("tracker-update", data);
        };
        this.onTrackerPeer = (trackerPeer) => {
            this.debug("tracker peer", trackerPeer.id, trackerPeer);
            if (this.peers.has(trackerPeer.id)) {
                this.debug("tracker peer already connected", trackerPeer.id, trackerPeer);
                trackerPeer.destroy();
                return;
            }
            const peer = new media_peer_1.MediaPeer(trackerPeer, this.settings);
            peer.on("connect", this.onPeerConnect);
            peer.on("close", this.onPeerClose);
            peer.on("data-updated", this.onPeerDataUpdated);
            peer.on("segment-request", this.onSegmentRequest);
            peer.on("segment-loaded", this.onSegmentLoaded);
            peer.on("segment-absent", this.onSegmentAbsent);
            peer.on("segment-error", this.onSegmentError);
            peer.on("segment-timeout", this.onSegmentTimeout);
            peer.on("bytes-downloaded", this.onPieceBytesDownloaded);
            peer.on("bytes-uploaded", this.onPieceBytesUploaded);
            let peerCandidatesById = this.peerCandidates.get(peer.id);
            if (!peerCandidatesById) {
                peerCandidatesById = [];
                this.peerCandidates.set(peer.id, peerCandidatesById);
            }
            peerCandidatesById.push(peer);
        };
        this.onPieceBytesDownloaded = (peer, bytes) => {
            this.emit("bytes-downloaded", bytes, peer.id);
        };
        this.onPieceBytesUploaded = (peer, bytes) => {
            this.emit("bytes-uploaded", bytes, peer.id);
        };
        this.onPeerConnect = (peer) => {
            const connectedPeer = this.peers.get(peer.id);
            if (connectedPeer) {
                this.debug("tracker peer already connected (in peer connect)", peer.id, peer);
                peer.destroy();
                return;
            }
            // First peer with the ID connected
            this.peers.set(peer.id, peer);
            // Destroy all other peer candidates
            const peerCandidatesById = this.peerCandidates.get(peer.id);
            if (peerCandidatesById) {
                for (const peerCandidate of peerCandidatesById) {
                    if (peerCandidate != peer) {
                        peerCandidate.destroy();
                    }
                }
                this.peerCandidates.delete(peer.id);
            }
            this.emit("peer-connected", { id: peer.id, remoteAddress: peer.remoteAddress });
        };
        this.onPeerClose = (peer) => {
            if (this.peers.get(peer.id) != peer) {
                // Try to delete the peer candidate
                const peerCandidatesById = this.peerCandidates.get(peer.id);
                if (!peerCandidatesById) {
                    return;
                }
                const index = peerCandidatesById.indexOf(peer);
                if (index != -1) {
                    peerCandidatesById.splice(index, 1);
                }
                if (peerCandidatesById.length == 0) {
                    this.peerCandidates.delete(peer.id);
                }
                return;
            }
            for (const [key, value] of this.peerSegmentRequests) {
                if (value.peerId == peer.id) {
                    this.peerSegmentRequests.delete(key);
                }
            }
            this.peers.delete(peer.id);
            this.emit("peer-data-updated");
            this.emit("peer-closed", peer.id);
        };
        this.onPeerDataUpdated = () => {
            this.emit("peer-data-updated");
        };
        this.onSegmentRequest = async (peer, segmentId) => {
            if (this.masterSwarmId === undefined) {
                return;
            }
            const segment = await this.sementsStorage.getSegment(segmentId, this.masterSwarmId);
            if (segment) {
                peer.sendSegmentData(segmentId, segment.data);
            }
            else {
                peer.sendSegmentAbsent(segmentId);
            }
        };
        this.onSegmentLoaded = async (peer, segmentId, data) => {
            const peerSegmentRequest = this.peerSegmentRequests.get(segmentId);
            if (!peerSegmentRequest) {
                return;
            }
            const segment = peerSegmentRequest.segment;
            if (this.settings.segmentValidator) {
                try {
                    await this.settings.segmentValidator(Object.assign(Object.assign({}, segment), { data: data }), "p2p", peer.id);
                }
                catch (error) {
                    this.debug("segment validator failed", error);
                    this.peerSegmentRequests.delete(segmentId);
                    this.emit("segment-error", segment, error, peer.id);
                    this.onPeerClose(peer);
                    return;
                }
            }
            this.peerSegmentRequests.delete(segmentId);
            this.emit("segment-loaded", segment, data, peer.id);
        };
        this.onSegmentAbsent = (peer, segmentId) => {
            this.peerSegmentRequests.delete(segmentId);
            this.emit("peer-data-updated");
        };
        this.onSegmentError = (peer, segmentId, description) => {
            const peerSegmentRequest = this.peerSegmentRequests.get(segmentId);
            if (peerSegmentRequest) {
                this.peerSegmentRequests.delete(segmentId);
                this.emit("segment-error", peerSegmentRequest.segment, description, peer.id);
            }
        };
        this.onSegmentTimeout = (peer, segmentId) => {
            const peerSegmentRequest = this.peerSegmentRequests.get(segmentId);
            if (peerSegmentRequest) {
                this.peerSegmentRequests.delete(segmentId);
                peer.destroy();
                if (this.peers.delete(peerSegmentRequest.peerId)) {
                    this.emit("peer-data-updated");
                }
            }
        };
        this.peerId = settings.useP2P ? generatePeerId() : new ArrayBuffer(0);
        if (this.debug.enabled) {
            this.debug("peer ID", this.getPeerId(), new TextDecoder().decode(this.peerId));
        }
    }
    getPeers() {
        return this.peers;
    }
    getPeerId() {
        return buffer_1.Buffer.from(this.peerId).toString("hex");
    }
    async setStreamSwarmId(streamSwarmId, masterSwarmId) {
        if (this.streamSwarmId === streamSwarmId) {
            return;
        }
        this.destroy(true);
        this.streamSwarmId = streamSwarmId;
        this.masterSwarmId = masterSwarmId;
        this.debug("stream swarm ID", this.streamSwarmId);
        this.pendingTrackerClient = {
            isDestroyed: false
        };
        const pendingTrackerClient = this.pendingTrackerClient;
        // TODO: native browser 'crypto.subtle' implementation doesn't work in Chrome in insecure pages
        // TODO: Edge doesn't support SHA-1. Change to SHA-256 once Edge support is required.
        // const infoHash = await crypto.subtle.digest("SHA-1", new TextEncoder().encode(PEER_PROTOCOL_VERSION + this.streamSwarmId));
        const infoHash = new sha1().update(PEER_PROTOCOL_VERSION + this.streamSwarmId).digest();
        // destroy may be called while waiting for the hash to be calculated
        if (!pendingTrackerClient.isDestroyed) {
            this.pendingTrackerClient = null;
            this.createClient(infoHash);
        }
        else if (this.trackerClient != null) {
            this.trackerClient.destroy();
            this.trackerClient = null;
        }
    }
    createClient(infoHash) {
        if (!this.settings.useP2P) {
            return;
        }
        const clientOptions = {
            infoHash: buffer_1.Buffer.from(infoHash, 0, 20),
            peerId: buffer_1.Buffer.from(this.peerId, 0, 20),
            announce: this.settings.trackerAnnounce,
            rtcConfig: this.settings.rtcConfig,
            port: 6881,
            getAnnounceOpts: () => {
                return { numwant: this.settings.peerRequestsPerAnnounce };
            }
        };
        let oldTrackerClient = this.trackerClient;
        this.trackerClient = new Client(clientOptions);
        this.trackerClient.on("error", this.onTrackerError);
        this.trackerClient.on("warning", this.onTrackerWarning);
        this.trackerClient.on("update", this.onTrackerUpdate);
        this.trackerClient.on("peer", this.onTrackerPeer);
        this.trackerClient.start();
        if (oldTrackerClient != null) {
            oldTrackerClient.destroy();
            oldTrackerClient = null;
        }
    }
    download(segment) {
        if (this.isDownloading(segment)) {
            return false;
        }
        const candidates = [];
        for (const peer of this.peers.values()) {
            if ((peer.getDownloadingSegmentId() == null) &&
                (peer.getSegmentsMap().get(segment.id) === media_peer_1.MediaPeerSegmentStatus.Loaded)) {
                candidates.push(peer);
            }
        }
        if (candidates.length === 0) {
            return false;
        }
        const peer = candidates[Math.floor(Math.random() * candidates.length)];
        peer.requestSegment(segment.id);
        this.peerSegmentRequests.set(segment.id, new PeerSegmentRequest(peer.id, segment));
        return true;
    }
    abort(segment) {
        let downloadingSegment;
        const peerSegmentRequest = this.peerSegmentRequests.get(segment.id);
        if (peerSegmentRequest) {
            const peer = this.peers.get(peerSegmentRequest.peerId);
            if (peer) {
                downloadingSegment = peer.cancelSegmentRequest();
            }
            this.peerSegmentRequests.delete(segment.id);
        }
        return downloadingSegment;
    }
    isDownloading(segment) {
        return this.peerSegmentRequests.has(segment.id);
    }
    getActiveDownloadsCount() {
        return this.peerSegmentRequests.size;
    }
    destroy(swarmChange = false) {
        this.streamSwarmId = null;
        if (this.trackerClient) {
            this.trackerClient.stop();
            if (swarmChange) {
                // Don't destroy trackerClient to reuse its WebSocket connection to the tracker server
                this.trackerClient.removeAllListeners("error");
                this.trackerClient.removeAllListeners("warning");
                this.trackerClient.removeAllListeners("update");
                this.trackerClient.removeAllListeners("peer");
            }
            else {
                this.trackerClient.destroy();
                this.trackerClient = null;
            }
        }
        if (this.pendingTrackerClient) {
            this.pendingTrackerClient.isDestroyed = true;
            this.pendingTrackerClient = null;
        }
        this.peers.forEach(peer => peer.destroy());
        this.peers.clear();
        this.peerSegmentRequests.clear();
        for (const peerCandidateById of this.peerCandidates.values()) {
            for (const peerCandidate of peerCandidateById) {
                peerCandidate.destroy();
            }
        }
        this.peerCandidates.clear();
    }
    sendSegmentsMapToAll(segmentsMap) {
        this.peers.forEach(peer => peer.sendSegmentsMap(segmentsMap));
    }
    sendSegmentsMap(peerId, segmentsMap) {
        const peer = this.peers.get(peerId);
        if (peer) {
            peer.sendSegmentsMap(segmentsMap);
        }
    }
    getOvrallSegmentsMap() {
        const overallSegmentsMap = new Map();
        for (const peer of this.peers.values()) {
            for (const [segmentId, segmentStatus] of peer.getSegmentsMap()) {
                if (segmentStatus === media_peer_1.MediaPeerSegmentStatus.Loaded) {
                    overallSegmentsMap.set(segmentId, media_peer_1.MediaPeerSegmentStatus.Loaded);
                }
                else if (!overallSegmentsMap.get(segmentId)) {
                    overallSegmentsMap.set(segmentId, media_peer_1.MediaPeerSegmentStatus.LoadingByHttp);
                }
            }
        }
        return overallSegmentsMap;
    }
}
exports.P2PMediaManager = P2PMediaManager;
