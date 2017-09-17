<?php
namespace Ratchet\RFC6455\Messaging;

class MessageBuffer {
    /**
     * @var \Ratchet\RFC6455\Messaging\CloseFrameChecker
     */
    private $closeFrameChecker;

    /**
     * @var callable
     */
    private $exceptionFactory;

    /**
     * @var \Ratchet\RFC6455\Messaging\Message
     */
    private $messageBuffer;

    /**
     * @var \Ratchet\RFC6455\Messaging\Frame
     */
    private $frameBuffer;

    /**
     * @var callable
     */
    private $onMessage;

    /**
     * @var callable
     */
    private $onControl;

    /**
     * @var bool
     */
    private $checkForMask;

    function __construct(
        CloseFrameChecker $frameChecker,
        callable $onMessage,
        callable $onControl = null,
        $expectMask = true,
        $exceptionFactory = null
    ) {
        $this->closeFrameChecker = $frameChecker;
        $this->checkForMask = (bool)$expectMask;

        $this->exceptionFactory ?: $this->exceptionFactory = function($msg) {
            return new \UnderflowException($msg);
        };

        $this->onMessage = $onMessage;
        $this->onControl = $onControl ?: function() {};
    }

    public function onData($data) {
        while (strlen($data) > 0) {
            $data = $this->processData($data);
        }
    }

    /**
     * @param string $data
     * @return null
     */
    private function processData($data) {
        $this->messageBuffer ?: $this->messageBuffer = $this->newMessage();
        $this->frameBuffer   ?: $this->frameBuffer   = $this->newFrame();

        $this->frameBuffer->addBuffer($data);
        if (!$this->frameBuffer->isCoalesced()) {
            return '';
        }

        $onMessage = $this->onMessage;
        $onControl = $this->onControl;

        $this->frameBuffer = $this->frameCheck($this->frameBuffer);

        $overflow = $this->frameBuffer->extractOverflow();
        $this->frameBuffer->unMaskPayload();

        $opcode = $this->frameBuffer->getOpcode();

        if ($opcode > 2) {
            $onControl($this->frameBuffer);

            if (Frame::OP_CLOSE === $opcode) {
                return '';
            }
        } else {
            $this->messageBuffer->addFrame($this->frameBuffer);
        }

        $this->frameBuffer = null;

        if ($this->messageBuffer->isCoalesced()) {
            $msgCheck = $this->checkMessage($this->messageBuffer);
            if (true !== $msgCheck) {
                $onControl($this->newCloseFrame($msgCheck, 'Ratchet detected an invalid UTF-8 payload'));
            } else {
                $onMessage($this->messageBuffer);
            }

            $this->messageBuffer = null;
        }

        return $overflow;
    }

    /**
     * Check a frame to be added to the current message buffer
     * @param \Ratchet\RFC6455\Messaging\FrameInterface|FrameInterface $frame
     * @return \Ratchet\RFC6455\Messaging\FrameInterface|FrameInterface
     */
    public function frameCheck(FrameInterface $frame) {
        if (false !== $frame->getRsv1() ||
            false !== $frame->getRsv2() ||
            false !== $frame->getRsv3()
        ) {
            return $this->newCloseFrame(Frame::CLOSE_PROTOCOL, 'Ratchet detected an invalid reserve code');
        }

        if ($this->checkForMask && !$frame->isMasked()) {
            return $this->newCloseFrame(Frame::CLOSE_PROTOCOL, 'Ratchet detected an incorrect frame mask');
        }

        $opcode = $frame->getOpcode();

        if ($opcode > 2) {
            if ($frame->getPayloadLength() > 125 || !$frame->isFinal()) {
                return $this->newCloseFrame(Frame::CLOSE_PROTOCOL, 'Ratchet detected a mismatch between final bit and indicated payload length');
            }

            switch ($opcode) {
                case Frame::OP_CLOSE:
                    $closeCode = 0;

                    $bin = $frame->getPayload();

                    if (empty($bin)) {
                        return $this->newCloseFrame(Frame::CLOSE_NORMAL);
                    }

                    if (strlen($bin) === 1) {
                        return $this->newCloseFrame(Frame::CLOSE_PROTOCOL, 'Ratchet detected an invalid close code');
                    }

                    if (strlen($bin) >= 2) {
                        list($closeCode) = array_merge(unpack('n*', substr($bin, 0, 2)));
                    }

                    $checker = $this->closeFrameChecker;
                    if (!$checker($closeCode)) {
                        return $this->newCloseFrame(Frame::CLOSE_PROTOCOL, 'Ratchet detected an invalid close code');
                    }

                    if (!$this->checkUtf8(substr($bin, 2))) {
                        return $this->newCloseFrame(Frame::CLOSE_BAD_PAYLOAD, 'Ratchet detected an invalid UTF-8 payload in the close reason');
                    }

                    return $frame;
                    break;
                case Frame::OP_PING:
                case Frame::OP_PONG:
                    break;
                default:
                    return $this->newCloseFrame(Frame::CLOSE_PROTOCOL, 'Ratchet detected an invalid OP code');
                    break;
            }

            return $frame;
        }

        if (Frame::OP_CONTINUE === $frame->getOpcode() && 0 === count($this->messageBuffer)) {
            return $this->newCloseFrame(Frame::CLOSE_PROTOCOL, 'Ratchet detected the first frame of a message was a continue');
        }

        if (count($this->messageBuffer) > 0 && Frame::OP_CONTINUE !== $frame->getOpcode()) {
            return $this->newCloseFrame(Frame::CLOSE_PROTOCOL, 'Ratchet detected invalid OP code when expecting continue frame');
        }

        return $frame;
    }

    /**
     * Determine if a message is valid
     * @param \Ratchet\RFC6455\Messaging\MessageInterface
     * @return bool|int true if valid - false if incomplete - int of recommended close code
     */
    public function checkMessage(MessageInterface $message) {
        if (!$message->isBinary()) {
            if (!$this->checkUtf8($message->getPayload())) {
                return Frame::CLOSE_BAD_PAYLOAD;
            }
        }

        return true;
    }

    private function checkUtf8($string) {
        if (extension_loaded('mbstring')) {
            return mb_check_encoding($string, 'UTF-8');
        }

        return preg_match('//u', $string);
    }

    /**
     * @return \Ratchet\RFC6455\Messaging\MessageInterface
     */
    public function newMessage() {
        return new Message;
    }

    /**
     * @param string|null $payload
     * @param bool|null   $final
     * @param int|null    $opcode
     * @return \Ratchet\RFC6455\Messaging\FrameInterface
     */
    public function newFrame($payload = null, $final = null, $opcode = null) {
        return new Frame($payload, $final, $opcode, $this->exceptionFactory);
    }

    public function newCloseFrame($code, $reason = '') {
        return $this->newFrame(pack('n', $code) . $reason, true, Frame::OP_CLOSE);
    }
}
