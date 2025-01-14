const socket = io(WebRTC2RTMPURL); // Connect to the Socket.IO server
const peers = {}; // Store RTCPeerConnections by peerId
const remoteStreams = {}; // Store remote MediaStreams by peerId
let currentRoom = null; // Track the current room the user is in

/**
 * Join a room and set up the necessary listeners for existing/new peers.
 * @param {string} roomId - The room identifier.
 * @param {MediaStream} localStream - The local media stream (audio/video).
 */
function joinRoom(roomId, localStream) {
    currentRoom = roomId;
    socket.emit('join-room', roomId);
    console.log(`Call Events: Requesting to join room: ${roomId}`);

    // 1. Handle the current list of peers in the room
    socket.on('peer-list', (peerList) => {
        console.log(`Call Events: Peers in room ${roomId}:`, peerList);
        peerList.forEach((peerId) => {
            if (peerId !== socket.id && !peers[peerId]) {
                console.log(`Call Events: Creating RTCPeerConnection and offer for peerId: ${peerId}`);
                const peerConnection = createPeerConnection(peerId, localStream);
                peers[peerId] = peerConnection;

                // Create an offer for each existing peer
                peerConnection.createOffer()
                    .then((offer) => {
                        console.log(`Call Events: Offer created for ${peerId}:`, offer);
                        return peerConnection.setLocalDescription(offer);
                    })
                    .then(() => {
                        console.log(`Call Events: Sending offer to ${peerId}`);
                        socket.emit('signal', {
                            roomId,
                            to: peerId,
                            offer: peers[peerId].localDescription
                        });
                    })
                    .catch((error) => {
                        console.error('Call Events: Error creating/sending offer:', error);
                    });
            }
        });
    });

    // 2. Handle notification that a new peer joined the room
    socket.on('new-peer', (peerId) => {
        console.log(`Call Events: New peer joined room ${currentRoom}: ${peerId}`);

        if (!peers[peerId]) {
            console.log(`Call Events: Creating connection for new peer: ${peerId}`);
            const peerConnection = createPeerConnection(peerId, localStream);
            peers[peerId] = peerConnection;

            // Optionally create an offer for the new peer here
            peerConnection.createOffer()
                .then((offer) => {
                    console.log(`Call Events: Created offer for ${peerId}`, offer);
                    return peerConnection.setLocalDescription(offer);
                })
                .then(() => {
                    console.log(`Call Events: Sending offer to ${peerId}`);
                    socket.emit('signal', {
                        roomId: currentRoom,
                        to: peerId,
                        offer: peers[peerId].localDescription
                    });
                })
                .catch((error) => {
                    console.error('Call Events: Error creating/sending offer:', error);
                });
        } else {
            console.log(`Call Events: Peer is already in peers: ${peerId}`);
        }
    });

    // 3. Handle signaling data (offer, answer, ICE) for the room
    socket.on('signal', async ({ from, offer, answer, candidate }) => {
        console.log(`Call Events: Signal received from ${from} in room ${roomId}`);

        // If there is no existing connection for this peer, create one
        if (!peers[from]) {
            console.log(`Call Events: Creating RTCPeerConnection for peer: ${from}`);
            peers[from] = createPeerConnection(from, localStream);
        }

        const peerConnection = peers[from];

        if (offer) {
            // Process offer
            console.log(`Call Events: Received offer from ${from}`);
            try {
                await peerConnection.setRemoteDescription(new RTCSessionDescription(offer));
                console.log('Call Events: Offer set as RemoteDescription');

                const localAnswer = await peerConnection.createAnswer();
                console.log(`Call Events: Creating answer for offer from ${from}`);

                await peerConnection.setLocalDescription(localAnswer);
                console.log('Call Events: Answer set as LocalDescription');

                // Send the answer back
                socket.emit('signal', {
                    roomId,
                    to: from,
                    answer: localAnswer
                });
                console.log(`Call Events: Sending answer to ${from}`);
            } catch (error) {
                console.error(`Call Events: Error processing offer from ${from}:`, error);
            }
        } else if (answer) {
            // Process answer
            console.log(`Call Events: Received answer from ${from}`);
            try {
                await peerConnection.setRemoteDescription(new RTCSessionDescription(answer));
                console.log('Call Events: Answer set as RemoteDescription');
            } catch (error) {
                console.error(`Call Events: Error setting RemoteDescription for answer from ${from}:`, error);
            }
        } else if (candidate) {
            // Process ICE candidate
            console.log(`Call Events: Received ICE candidate from ${from}:`, candidate);
            try {
                await peerConnection.addIceCandidate(new RTCIceCandidate(candidate));
                console.log('Call Events: ICE candidate successfully added');
            } catch (error) {
                console.error(`Call Events: Error adding ICE candidate from ${from}:`, error);
            }
        }
    });

    // 4. Handle a peer that disconnected
    socket.on('peer-disconnected', (peerId) => {
        console.log(`Call Events: Peer disconnected from room ${roomId}: ${peerId}`);

        if (peers[peerId]) {
            console.log(`Call Events: Closing connection and removing peerId: ${peerId}`);
            peers[peerId].close();
            delete peers[peerId];
        }

        // Remove the corresponding video element
        removeVideo(peerId);

        // Also remove the remote stream if tracking
        delete remoteStreams[peerId];
    });
}

/**
 * Create an RTCPeerConnection for a given peerId.
 * @param {string} peerId - The identifier of the peer.
 * @param {MediaStream} localStream - The local stream to send to the peer.
 * @returns {RTCPeerConnection} The newly created RTCPeerConnection.
 */
function createPeerConnection(peerId, localStream) {
    console.log(`Call Events: Creating RTCPeerConnection for peerId: ${peerId}`);

    const configuration = {
        iceServers: [{ urls: 'stun:stun.l.google.com:19302' }]
    };
    const peerConnection = new RTCPeerConnection(configuration);

    // Add local tracks (audio/video)
    localStream.getTracks().forEach((track) => {
        console.log(`Call Events: Adding track (${track.kind}) to peerId ${peerId}`);
        peerConnection.addTrack(track, localStream);
    });

    // ICE candidate event
    peerConnection.onicecandidate = (event) => {
        if (event.candidate) {
            //console.log(`Call Events: Sending ICE candidate to ${peerId}:`, event.candidate);
            socket.emit('signal', {
                roomId: currentRoom,
                to: peerId,
                candidate: event.candidate
            });
        }
    };

    /**
     * Remote track event:
     * By default, ontrack fires once per track (audio, then video).
     * To avoid duplicating <video> elements, we collect tracks in `remoteStreams[peerId]`.
     */
    peerConnection.ontrack = (event) => {
        console.log(`Call Events: Remote track received from ${peerId}:`, event.streams);

        // If we don't have a stream object yet for this peer, create one
        if (!remoteStreams[peerId]) {
            remoteStreams[peerId] = new MediaStream();
        }

        // Add this track to the existing remote stream
        remoteStreams[peerId].addTrack(event.track);

        // If there's no video element yet for this peer, create it now
        if (!document.getElementById(peerId)) {
            console.log(`Call Events: Creating a single video for peerId: ${peerId}`);
            addVideo(peerId, remoteStreams[peerId], 'remoteVideo');
        } else {
            console.log(`Call Events: Video element for ${peerId} already exists; just adding track`);
        }
    };

    return peerConnection;
}

/**
 * Add a video element for the specified peer.
 * @param {string} peerId - The peer ID.
 * @param {MediaStream} stream - The remote stream to display.
 * @param {string} className - The CSS class for the video element.
 */
function addVideo(peerId, stream, className) {
    // 1. Criar o contêiner para o vídeo e botão
    const $videoWrapper = $('<div>', {
        id: `wrapper-${peerId}`
    }).addClass('video-wrapper'); // Adicionar classe opcional

    // 2. Criar elemento <video> usando jQuery
    const $video = $('<video>', {
        id: peerId,
        autoplay: true,
        playsinline: true,
        controls: false // desativa controles nativos
    }).addClass(className);

    // Definir o objeto de mídia (MediaStream)
    $video[0].srcObject = stream;

    // 3. Criar botão Mute/Unmute usando jQuery com FontAwesome
    const $muteButton = $('<button>', {
        id: `mute-${peerId}`
    }).addClass('btn btn-link mute-unmute-btn');

    // Adicionar ícone inicial (Unmute icon)
    const $muteIcon = $('<i>', {
        class: 'fas fa-volume-up' // Icon for unmuted state
    });
    $muteButton.append($muteIcon);

    // 4. Evento de clique para alternar mute/unmute
    $muteButton.on('click', function () {
        const videoElement = $video[0];
        // Inverte o estado de 'muted'
        videoElement.muted = !videoElement.muted;

        // Atualiza o ícone com base no estado
        $muteIcon.attr('class', videoElement.muted ? 'fas fa-volume-mute' : 'fas fa-volume-up');
    });

    // Adicionar o botão ao contêiner do vídeo
    $videoWrapper.append($muteButton);

    // 5. Adicionar <video> e botão ao contêiner
    $videoWrapper.append($video);
    $videoWrapper.append($muteButton);

    // 6. Adicionar o contêiner ao #videoContainer
    $('#videoContainer').append($videoWrapper);
}


/**
 * Remove the video element for the specified peer.
 * @param {string} peerId - The peer ID.
 */
function removeVideo(peerId) {
    console.log(`Call Events: Attempting to remove video wrapper for peerId: ${peerId}`);
    const wrapper = document.getElementById(`wrapper-${peerId}`);
    if (wrapper) {
        console.log(`Call Events: Removing video wrapper for peerId: ${peerId}`);
        wrapper.remove();
    } else {
        console.log(`Call Events: Video wrapper not found for peerId: ${peerId}`);
    }
}

// Example usage: join "example-room" and capture local video/audio
navigator.mediaDevices.getUserMedia({ video: true, audio: true })
    .then((stream) => {
        console.log('Call Events: Successfully captured local media');
        joinRoom(roomId, stream);

        // Display local video (your own camera feed)
        const video = document.createElement('video');
        video.id = socket.id;
        video.srcObject = stream;
        video.autoplay = true;
        video.playsInline = true;
        video.muted = true;
        video.classList.add('localVideo');
        document.getElementById('localVideoContainer').appendChild(video);
    })
    .catch((error) => {
        console.error('Call Events: Error accessing media devices:', error);
    });
