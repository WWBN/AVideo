<?php

namespace React\Tests\Dns\Resolver;

use React\Tests\Dns\TestCase;
use React\Dns\Resolver\Resolver;
use React\Dns\Model\Message;
use React\Dns\Model\Record;

class ResolveAliasesTest extends TestCase
{
    /**
     * @dataProvider provideAliasedAnswers
     */
    public function testResolveAliases(array $expectedAnswers, array $answers, $name)
    {
        $message = new Message();
        foreach ($answers as $answer) {
            $message->answers[] = $answer;
        }

        $executor = $this->createExecutorMock();
        $executor->expects($this->once())->method('query')->willReturn(\React\Promise\resolve($message));

        $resolver = new Resolver($executor);

        $answers = $resolver->resolveAll($name, Message::TYPE_A);

        $answers->then($this->expectCallableOnceWith($expectedAnswers), null);
    }

    public function provideAliasedAnswers()
    {
        return array(
            array(
                array('178.79.169.131'),
                array(
                    new Record('igor.io', Message::TYPE_A, Message::CLASS_IN, 3600, '178.79.169.131'),
                ),
                'igor.io',
            ),
            array(
                array('178.79.169.131', '178.79.169.132', '178.79.169.133'),
                array(
                    new Record('igor.io', Message::TYPE_A, Message::CLASS_IN, 3600, '178.79.169.131'),
                    new Record('igor.io', Message::TYPE_A, Message::CLASS_IN, 3600, '178.79.169.132'),
                    new Record('igor.io', Message::TYPE_A, Message::CLASS_IN, 3600, '178.79.169.133'),
                ),
                'igor.io',
            ),
            array(
                array('178.79.169.131'),
                array(
                    new Record('igor.io', Message::TYPE_A, Message::CLASS_IN, 3600, '178.79.169.131'),
                    new Record('foo.igor.io', Message::TYPE_A, Message::CLASS_IN, 3600, '178.79.169.131'),
                    new Record('bar.igor.io', Message::TYPE_A, Message::CLASS_IN, 3600, '178.79.169.131'),
                ),
                'igor.io',
            ),
            array(
                array('178.79.169.131'),
                array(
                    new Record('igor.io', Message::TYPE_CNAME, Message::CLASS_IN, 3600, 'foo.igor.io'),
                    new Record('foo.igor.io', Message::TYPE_A, Message::CLASS_IN, 3600, '178.79.169.131'),
                ),
                'igor.io',
            ),
            array(
                array('178.79.169.131'),
                array(
                    new Record('igor.io', Message::TYPE_CNAME, Message::CLASS_IN, 3600, 'foo.igor.io'),
                    new Record('foo.igor.io', Message::TYPE_CNAME, Message::CLASS_IN, 3600, 'bar.igor.io'),
                    new Record('bar.igor.io', Message::TYPE_A, Message::CLASS_IN, 3600, '178.79.169.131'),
                ),
                'igor.io',
            ),
            array(
                array('178.79.169.131', '178.79.169.132', '178.79.169.133'),
                array(
                    new Record('igor.io', Message::TYPE_CNAME, Message::CLASS_IN, 3600, 'foo.igor.io'),
                    new Record('foo.igor.io', Message::TYPE_CNAME, Message::CLASS_IN, 3600, 'bar.igor.io'),
                    new Record('bar.igor.io', Message::TYPE_CNAME, Message::CLASS_IN, 3600, 'baz.igor.io'),
                    new Record('bar.igor.io', Message::TYPE_CNAME, Message::CLASS_IN, 3600, 'qux.igor.io'),
                    new Record('baz.igor.io', Message::TYPE_A, Message::CLASS_IN, 3600, '178.79.169.131'),
                    new Record('baz.igor.io', Message::TYPE_A, Message::CLASS_IN, 3600, '178.79.169.132'),
                    new Record('qux.igor.io', Message::TYPE_A, Message::CLASS_IN, 3600, '178.79.169.133'),
                ),
                'igor.io',
            ),
        );
    }

    private function createExecutorMock()
    {
        return $this->getMockBuilder('React\Dns\Query\ExecutorInterface')->getMock();
    }
}
