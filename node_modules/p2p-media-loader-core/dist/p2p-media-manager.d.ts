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
import { STEEmitter } from "./stringly-typed-event-emitter";
import { Segment } from "./loader-interface";
import { MediaPeer, MediaPeerSegmentStatus } from "./media-peer";
import { SegmentsStorage, SegmentValidatorCallback } from "./hybrid-loader";
export declare class P2PMediaManager extends STEEmitter<"peer-connected" | "peer-closed" | "peer-data-updated" | "segment-loaded" | "segment-error" | "bytes-downloaded" | "bytes-uploaded" | "tracker-update"> {
    private sementsStorage;
    private settings;
    private trackerClient;
    private peers;
    private peerCandidates;
    private peerSegmentRequests;
    private streamSwarmId;
    private readonly peerId;
    private debug;
    private pendingTrackerClient;
    private masterSwarmId?;
    constructor(sementsStorage: SegmentsStorage, settings: {
        useP2P: boolean;
        trackerAnnounce: string[];
        p2pSegmentDownloadTimeout: number;
        segmentValidator?: SegmentValidatorCallback;
        webRtcMaxMessageSize: number;
        rtcConfig?: RTCConfiguration;
        peerRequestsPerAnnounce: number;
    });
    getPeers(): Map<string, MediaPeer>;
    getPeerId(): string;
    setStreamSwarmId(streamSwarmId: string, masterSwarmId: string): Promise<void>;
    private createClient;
    private onTrackerError;
    private onTrackerWarning;
    private onTrackerUpdate;
    private onTrackerPeer;
    download(segment: Segment): boolean;
    abort(segment: Segment): ArrayBuffer[] | undefined;
    isDownloading(segment: Segment): boolean;
    getActiveDownloadsCount(): number;
    destroy(swarmChange?: boolean): void;
    sendSegmentsMapToAll(segmentsMap: {
        [key: string]: [string, number[]];
    }): void;
    sendSegmentsMap(peerId: string, segmentsMap: {
        [key: string]: [string, number[]];
    }): void;
    getOvrallSegmentsMap(): Map<string, MediaPeerSegmentStatus>;
    private onPieceBytesDownloaded;
    private onPieceBytesUploaded;
    private onPeerConnect;
    private onPeerClose;
    private onPeerDataUpdated;
    private onSegmentRequest;
    private onSegmentLoaded;
    private onSegmentAbsent;
    private onSegmentError;
    private onSegmentTimeout;
}
