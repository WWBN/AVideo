<?php

namespace Ratchet\RFC6455\Test\Unit\Messaging;

use Ratchet\RFC6455\Messaging\CloseFrameChecker;
use Ratchet\RFC6455\Messaging\Frame;
use Ratchet\RFC6455\Messaging\Message;
use Ratchet\RFC6455\Messaging\MessageBuffer;

class MessageBufferTest extends \PHPUnit_Framework_TestCase
{
    /**
     * This is to test that MessageBuffer can handle a large receive
     * buffer with many many frames without blowing the stack (pre-v0.4 issue)
     */
    public function testProcessingLotsOfFramesInASingleChunk() {
        $frame = new Frame('a', true, Frame::OP_TEXT);

        $frameRaw = $frame->getContents();

        $data = str_repeat($frameRaw, 1000);

        $messageCount = 0;

        $messageBuffer = new MessageBuffer(
            new CloseFrameChecker(),
            function (Message $message) use (&$messageCount) {
                $messageCount++;
                $this->assertEquals('a', $message->getPayload());
            },
            null,
            false
        );

        $messageBuffer->onData($data);

        $this->assertEquals(1000, $messageCount);
    }
}