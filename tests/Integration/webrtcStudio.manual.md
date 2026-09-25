# Webcam studio verification

Run automated state/transport regressions with `node tests/Integration/webrtcStudio.js`.
The fixtures run the actual client scripts with simulated media devices, recorder, timers and Socket.IO events; they do not send a broadcast.

Before merging, verify with a real live server and a test channel:

1. Open Live with WebRTC enabled and use the quick Go Live entry as well. Both should open a preview, without an extra setup confirmation. Opening the preview must not make the channel live.
2. Choose camera/microphone, update the preview, and start. Confirm received audio/video in a separate viewer. Verify Starting changes to Live only after the server reports the stream running.
3. End the broadcast. Verify the viewer stops receiving it and the studio returns to its local preview. Start again, then close the studio during startup and during a broadcast; verify forwarding and capture stop.
4. Deny access, disconnect the camera or microphone, and make a device busy in another app. Check guidance and recovery through Try again or Update preview. Check saved devices, front/rear cameras, and rotation without reopening a stopped camera.
5. Interrupt the network during startup, live streaming and stopping. The studio must retain an unconfirmed broadcast state, stop local forwarding, reconcile with the server after reconnecting and require an explicit Start to publish again. A lost stop acknowledgement must leave End broadcast available to retry.
6. Test screen sharing with and without audio and cancel the screen picker. End sharing through the browser control. Check that missing screen audio is disclosed and that the previous preview survives a cancelled picker.
7. Check 320/390 px phones, tablet and desktop; light/dark themes; keyboard focus and screen-reader status announcements; Portuguese labels; and expanded chat/settings without overlap. Verify Android Chrome and iOS Safari on real devices (capture, audio and backgrounding differ from desktop emulation).

Observed locally: Chrome preview, camera off/reopen, device selectors, chat expansion, keyboard focus and responsive widths 320/390/768/1280 px. PHP/JavaScript syntax checks and simulated lifecycle regressions pass. End-to-end publication, mobile hardware behavior and screen-reader announcements still require the real-device/live-server checks above.
