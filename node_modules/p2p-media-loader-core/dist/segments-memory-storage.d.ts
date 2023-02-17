/**
 * Copyright 2019 Novage LLC.
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
import { Segment } from "./loader-interface";
import { SegmentsStorage } from "./hybrid-loader";
export declare class SegmentsMemoryStorage implements SegmentsStorage {
    private settings;
    private cache;
    constructor(settings: {
        cachedSegmentExpiration: number;
        cachedSegmentsCount: number;
    });
    storeSegment(segment: Segment): Promise<void>;
    getSegmentsMap(masterSwarmId: string): Promise<Map<string, {
        segment: Segment;
        lastAccessed: number;
    }>>;
    getSegment(id: string, masterSwarmId: string): Promise<Segment | undefined>;
    hasSegment(id: string, masterSwarmId: string): Promise<boolean>;
    clean(masterSwarmId: string, lockedSementsfilter?: (id: string) => boolean): Promise<boolean>;
    destroy(): Promise<void>;
}
