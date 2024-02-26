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
import { LoaderInterface, Segment } from "./loader-interface";
import { EventEmitter } from "events";
export declare class HybridLoader extends EventEmitter implements LoaderInterface {
    private readonly debug;
    private readonly debugSegments;
    private readonly httpManager;
    private readonly p2pManager;
    private segmentsStorage;
    private segmentsQueue;
    private readonly bandwidthApproximator;
    private readonly settings;
    private httpRandomDownloadInterval;
    private httpDownloadInitialTimeoutTimestamp;
    private masterSwarmId?;
    static isSupported(): boolean;
    constructor(settings?: Partial<HybridLoaderSettings>);
    private createHttpManager;
    private createP2PManager;
    load(segments: Segment[], streamSwarmId: string): Promise<void>;
    getSegment(id: string): Promise<Segment | undefined>;
    getSettings(): HybridLoaderSettings;
    getDetails(): {
        peerId: string;
    };
    destroy(): Promise<void>;
    private processInitialSegmentTimeout;
    private processSegmentsQueue;
    private downloadRandomSegmentOverHttp;
    private onPieceBytesDownloaded;
    private onPieceBytesUploaded;
    private onSegmentLoaded;
    private onSegmentError;
    private getStreamSwarmId;
    private createSegmentsMap;
    private onPeerConnect;
    private onPeerClose;
    private onTrackerUpdate;
    private cleanSegmentsStorage;
    private now;
}
export interface SegmentsStorage {
    storeSegment(segment: Segment): Promise<void>;
    getSegmentsMap(masterSwarmId: string): Promise<Map<string, {
        segment: Segment;
    }>>;
    getSegment(id: string, masterSwarmId: string): Promise<Segment | undefined>;
    clean(masterSwarmId: string, lockedSementsfilter?: (id: string) => boolean): Promise<boolean>;
    destroy(): Promise<void>;
}
export declare type SegmentValidatorCallback = (segment: Segment, method: "http" | "p2p", peerId?: string) => Promise<void>;
export declare type XhrSetupCallback = (xhr: XMLHttpRequest, url: string) => void;
export declare type SegmentUrlBuilder = (segment: Segment) => string;
export interface HybridLoaderSettings {
    /**
     * Segment lifetime in cache. The segment is deleted from the cache if the last access time is greater than this value (in milliseconds).
     */
    cachedSegmentExpiration: number;
    /**
     * Max number of segments that can be stored in the cache.
     */
    cachedSegmentsCount: number;
    /**
     * Enable/Disable peers interaction.
     */
    useP2P: boolean;
    /**
     * The peer will not upload segments data to the P2P network but still download from others.
     */
    consumeOnly: boolean;
    /**
     * The maximum priority of the segments to be downloaded (if not available) as quickly as possible (i.e. via HTTP method).
     */
    requiredSegmentsPriority: number;
    /**
     * Max number of simultaneous downloads from HTTP source.
     */
    simultaneousHttpDownloads: number;
    /**
     * Probability of downloading remaining not downloaded segment in the segments queue via HTTP.
     */
    httpDownloadProbability: number;
    /**
     * Interval of the httpDownloadProbability check (in milliseconds).
     */
    httpDownloadProbabilityInterval: number;
    /**
     * Don't download segments over HTTP randomly when there is no peers.
     */
    httpDownloadProbabilitySkipIfNoPeers: boolean;
    /**
     * Timeout before trying to load segment again via HTTP after failed attempt (in milliseconds).
     */
    httpFailedSegmentTimeout: number;
    /**
     * Segments with higher priority will not be downloaded over HTTP.
     */
    httpDownloadMaxPriority: number;
    /**
     * Try to download initial segments over P2P if the value is > 0.
     * But HTTP download will be forcibly enabled if there is no peers on tracker or
     * single sequential segment P2P download is timed out (see httpDownloadInitialTimeoutPerSegment).
     */
    httpDownloadInitialTimeout: number;
    /**
     * Use HTTP ranges requests where it is possible.
     * Allows to continue (and not start over) aborted P2P downloads over HTTP.
     */
    httpUseRanges: boolean;
    /**
     * If initial HTTP download timeout is enabled (see httpDownloadInitialTimeout)
     * this parameter sets additional timeout for a single sequential segment download
     * over P2P. It will cancel initial HTTP download timeout mode if a segment download is timed out.
     */
    httpDownloadInitialTimeoutPerSegment: number;
    /**
     * Max number of simultaneous downloads from peers.
     */
    simultaneousP2PDownloads: number;
    /**
     * Segments with higher priority will not be downloaded over P2P.
     */
    p2pDownloadMaxPriority: number;
    /**
     * Timeout to download a segment from a peer. If exceeded the peer is dropped.
     */
    p2pSegmentDownloadTimeout: number;
    /**
     * Max WebRTC message size. 64KiB - 1B should work with most of recent browsers. Set it to 16KiB for older browsers support.
     */
    webRtcMaxMessageSize: number;
    /**
     * Torrent trackers (announcers) to use.
     */
    trackerAnnounce: string[];
    /**
     * Number of requested peers in each announce for each tracker. Maximum is 10.
     */
    peerRequestsPerAnnounce: number;
    /**
     * An RTCConfiguration dictionary providing options to configure WebRTC connections.
     */
    rtcConfig: any;
    /**
     * Segment validation callback - validates the data after it has been downloaded.
     */
    segmentValidator?: SegmentValidatorCallback;
    /**
     * XMLHttpRequest setup callback. Handle it when you need additional setup for requests made by the library.
     */
    xhrSetup?: XhrSetupCallback;
    /**
     * Allow to modify the segment URL before HTTP request.
     */
    segmentUrlBuilder?: SegmentUrlBuilder;
    /**
     * A storage for the downloaded segments.
     * By default the segments are stored in JavaScript memory.
     */
    segmentsStorage?: SegmentsStorage;
}
