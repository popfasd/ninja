<?php

use Popfasd\Ninja\Submission;
use Popfasd\Ninja\Form;

class SubmissionTest extends PHPUnit_Framework_TestCase
{
    public function testConstruct()
    {
        $form = $this->getMockBuilder(Form::class)
            ->disableOriginalConstructor()
            ->getMock();

        $ts = time();
        $submission = new Submission('foo', $form, ['bar'=>'baz']);

        $this->assertEquals($submission->getId(), 'foo');
        $this->assertEquals($submission->getForm(), $form);
        $this->assertGreaterThanOrEqual($submission->getTimestamp(), $ts);

        $data = $submission->getData();
        $this->assertEquals($data['bar'], 'baz');
        $this->assertEquals($data['__id'], 'foo');
        $this->assertGreaterThanOrEqual($data['__ts'], $ts);
    }

    /**
     * @depends testConstruct
     */
    public function testConstructWithTimestamp()
    {
        $form = $this->getMockBuilder(Form::class)
            ->disableOriginalConstructor()
            ->getMock();

        $ts = time();
        $submission = new Submission('foo', $form, [], $ts);
        $data = $submission->getData();
        $this->assertEquals($data['__ts'], $ts);
    }
}
