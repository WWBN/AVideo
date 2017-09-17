<?php

namespace React\Tests\Dns\Query;

use Clue\React\Block;
use React\Dns\Query\Executor;
use React\Dns\Query\Query;
use React\Dns\Model\Message;
use React\Dns\Model\Record;
use React\Dns\Protocol\BinaryDumper;
use React\Tests\Dns\TestCase;

class ExecutorTest extends TestCase
{
    private $loop;
    private $parser;
    private $dumper;
    private $executor;

    public function setUp()
    {
        $this->loop = $this->getMockBuilder('React\EventLoop\LoopInterface')->getMock();
        $this->parser = $this->getMockBuilder('React\Dns\Protocol\Parser')->getMock();
        $this->dumper = new BinaryDumper();

        $this->executor = new Executor($this->loop, $this->parser, $this->dumper);
    }

    /** @test */
    public function queryShouldCreateUdpRequest()
    {
        $timer = $this->getMockBuilder('React\EventLoop\Timer\TimerInterface')->getMock();
        $this->loop
            ->expects($this->any())
            ->method('addTimer')
            ->will($this->returnValue($timer));

        $this->executor = $this->createExecutorMock();
        $this->executor
            ->expects($this->once())
            ->method('createConnection')
            ->with('8.8.8.8:53', 'udp')
            ->will($this->returnNewConnectionMock(false));

        $query = new Query('igor.io', Message::TYPE_A, Message::CLASS_IN, 1345656451);
        $this->executor->query('8.8.8.8:53', $query);
    }

    /** @test */
    public function resolveShouldRejectIfRequestIsLargerThan512Bytes()
    {
        $query = new Query(str_repeat('a', 512).'.igor.io', Message::TYPE_A, Message::CLASS_IN, 1345656451);
        $promise = $this->executor->query('8.8.8.8:53', $query);

        $this->setExpectedException('RuntimeException', 'DNS query for ' . $query->name . ' failed: Requested transport "tcp" not available, only UDP is supported in this version');
        Block\await($promise, $this->loop);
    }

    /** @test */
    public function resolveShouldCloseConnectionWhenCancelled()
    {
        $conn = $this->createConnectionMock(false);
        $conn->expects($this->once())->method('close');

        $timer = $this->getMockBuilder('React\EventLoop\Timer\TimerInterface')->getMock();
        $this->loop
            ->expects($this->any())
            ->method('addTimer')
            ->will($this->returnValue($timer));

        $this->executor = $this->createExecutorMock();
        $this->executor
            ->expects($this->once())
            ->method('createConnection')
            ->with('8.8.8.8:53', 'udp')
            ->will($this->returnValue($conn));

        $query = new Query('igor.io', Message::TYPE_A, Message::CLASS_IN, 1345656451);
        $promise = $this->executor->query('8.8.8.8:53', $query);

        $promise->cancel();

        $this->setExpectedException('React\Dns\Query\CancellationException', 'DNS query for igor.io has been cancelled');
        Block\await($promise, $this->loop);
    }

    /** @test */
    public function resolveShouldNotStartOrCancelTimerWhenCancelledWithTimeoutIsNull()
    {
        $this->loop
            ->expects($this->never())
            ->method('addTimer');

        $this->executor = new Executor($this->loop, $this->parser, $this->dumper, null);

        $query = new Query('igor.io', Message::TYPE_A, Message::CLASS_IN, 1345656451);
        $promise = $this->executor->query('8.8.8.8:53', $query);

        $promise->cancel();

        $this->setExpectedException('React\Dns\Query\CancellationException', 'DNS query for igor.io has been cancelled');
        Block\await($promise, $this->loop);
    }

    /** @test */
    public function resolveShouldRejectIfResponseIsTruncated()
    {
        $timer = $this->getMockBuilder('React\EventLoop\Timer\TimerInterface')->getMock();

        $this->loop
            ->expects($this->any())
            ->method('addTimer')
            ->will($this->returnValue($timer));

        $this->parser
            ->expects($this->once())
            ->method('parseMessage')
            ->will($this->returnTruncatedResponse());

        $this->executor = $this->createExecutorMock();
        $this->executor
            ->expects($this->once())
            ->method('createConnection')
            ->with('8.8.8.8:53', 'udp')
            ->will($this->returnNewConnectionMock());

        $query = new Query('igor.io', Message::TYPE_A, Message::CLASS_IN, 1345656451);
        $this->executor->query('8.8.8.8:53', $query);
    }

    /** @test */
    public function resolveShouldFailIfUdpThrow()
    {
        $this->loop
            ->expects($this->never())
            ->method('addTimer');

        $this->parser
            ->expects($this->never())
            ->method('parseMessage');

        $this->executor = $this->createExecutorMock();
        $this->executor
            ->expects($this->once())
            ->method('createConnection')
            ->with('8.8.8.8:53', 'udp')
            ->will($this->throwException(new \Exception('Nope')));

        $query = new Query('igor.io', Message::TYPE_A, Message::CLASS_IN, 1345656451);
        $promise = $this->executor->query('8.8.8.8:53', $query);

        $this->setExpectedException('RuntimeException', 'DNS query for igor.io failed: Nope');
        Block\await($promise, $this->loop);
    }

    /** @test */
    public function resolveShouldCancelTimerWhenFullResponseIsReceived()
    {
        $conn = $this->createConnectionMock();

        $this->parser
            ->expects($this->once())
            ->method('parseMessage')
            ->will($this->returnStandardResponse());

        $this->executor = $this->createExecutorMock();
        $this->executor
            ->expects($this->at(0))
            ->method('createConnection')
            ->with('8.8.8.8:53', 'udp')
            ->will($this->returnNewConnectionMock());


        $timer = $this->getMockBuilder('React\EventLoop\Timer\TimerInterface')->getMock();

        $this->loop
            ->expects($this->once())
            ->method('addTimer')
            ->with(5, $this->isInstanceOf('Closure'))
            ->will($this->returnValue($timer));

        $this->loop
            ->expects($this->once())
            ->method('cancelTimer')
            ->with($timer);

        $query = new Query('igor.io', Message::TYPE_A, Message::CLASS_IN, 1345656451);
        $this->executor->query('8.8.8.8:53', $query);
    }

    /** @test */
    public function resolveShouldCloseConnectionOnTimeout()
    {
        $this->executor = $this->createExecutorMock();
        $this->executor
            ->expects($this->at(0))
            ->method('createConnection')
            ->with('8.8.8.8:53', 'udp')
            ->will($this->returnNewConnectionMock(false));

        $timer = $this->getMockBuilder('React\EventLoop\Timer\TimerInterface')->getMock();
        $timer
            ->expects($this->never())
            ->method('cancel');

        $this->loop
            ->expects($this->once())
            ->method('addTimer')
            ->with(5, $this->isInstanceOf('Closure'))
            ->will($this->returnCallback(function ($time, $callback) use (&$timerCallback, $timer) {
                $timerCallback = $callback;
                return $timer;
            }));

        $query = new Query('igor.io', Message::TYPE_A, Message::CLASS_IN, 1345656451);
        $promise = $this->executor->query('8.8.8.8:53', $query);

        $this->assertNotNull($timerCallback);
        $timerCallback();

        $this->setExpectedException('React\Dns\Query\TimeoutException', 'DNS query for igor.io timed out');
        Block\await($promise, $this->loop);
    }

    private function returnStandardResponse()
    {
        $that = $this;
        $callback = function ($data) use ($that) {
            $response = new Message();
            $that->convertMessageToStandardResponse($response);
            return $response;
        };

        return $this->returnCallback($callback);
    }

    private function returnTruncatedResponse()
    {
        $that = $this;
        $callback = function ($data) use ($that) {
            $response = new Message();
            $that->convertMessageToTruncatedResponse($response);
            return $response;
        };

        return $this->returnCallback($callback);
    }

    public function convertMessageToStandardResponse(Message $response)
    {
        $response->header->set('qr', 1);
        $response->questions[] = new Record('igor.io', Message::TYPE_A, Message::CLASS_IN);
        $response->answers[] = new Record('igor.io', Message::TYPE_A, Message::CLASS_IN, 3600, '178.79.169.131');
        $response->prepare();

        return $response;
    }

    public function convertMessageToTruncatedResponse(Message $response)
    {
        $this->convertMessageToStandardResponse($response);
        $response->header->set('tc', 1);
        $response->prepare();

        return $response;
    }

    private function returnNewConnectionMock($emitData = true)
    {
        $conn = $this->createConnectionMock($emitData);

        $callback = function () use ($conn) {
            return $conn;
        };

        return $this->returnCallback($callback);
    }

    private function createConnectionMock($emitData = true)
    {
        $conn = $this->getMockBuilder('React\Socket\ConnectionInterface')->getMock();
        $conn
            ->expects($this->any())
            ->method('on')
            ->with('data', $this->isInstanceOf('Closure'))
            ->will($this->returnCallback(function ($name, $callback) use ($emitData) {
                $emitData && $callback(null);
            }));

        return $conn;
    }

    private function createExecutorMock()
    {
        return $this->getMockBuilder('React\Dns\Query\Executor')
            ->setConstructorArgs(array($this->loop, $this->parser, $this->dumper))
            ->setMethods(array('createConnection'))
            ->getMock();
    }
}
