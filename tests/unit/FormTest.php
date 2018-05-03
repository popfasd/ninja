<?php

use Popfasd\Ninja\Form;
use Popfasd\Ninja\Submission;
use Popfasd\Ninja\SubmissionProcessedEvent;
use Popfasd\Ninja\DomainEvents;
use MattFerris\Events\DispatcherInterface;

class FormTest extends PHPUnit_Framework_TestCase
{

    public function testConstruct()
    {
        $host = 'foo';
        $name = 'bar';
        $id = sha1($host.'$'.$name);
        $data = ['foo' => 'bar'];

        $form = new Form($id, $host, $name, $data);

        $this->assertEquals($form->getId(), $id);
        $this->assertEquals($form->getHost(), 'foo');
        $this->assertEquals($form->getName(), 'bar');
        $this->assertNull($form->getFields());
        $this->assertNull($form->getValidationErrors());

    }

    /**
     * @depends testConstruct
     * @expectedException InvalidArgumentException
     * @expectedExceptionMessage $key expects non-empty string
     */
    public function testSetEmptyValidationKey()
    {
        $host = 'foo';
        $name = 'bar';
        $id = sha1($host.'$'.$name);
        $form = new Form($id, $host, $name);
        $form->setValidationKey('');
    }

    /**
     * @depends testConstruct
     */
    public function testValidate()
    {
        $host = 'foo';
        $name = 'bar';
        $id = sha1($host.'$'.$name);
        $data = ['field1' => 'test'];

        $form = new Form($id, $host, $name, $data);

        $this->assertTrue($form->validate());
        $this->assertNull($form->getValidationErrors());
    }

    /**
     * @depends testValidate
     */
    public function testValidateWithNotReceivedField()
    {
        $host = 'foo';
        $name = 'bar';
        $id = sha1($host.'$'.$name);
        $data = [];
        $rules = ['field1' => true];

        $form = new Form($id, $host, $name, $data);
        $form->setValidationRules($rules);

        $this->assertFalse($form->validate());
        $this->assertEquals($form->getValidationErrors(), ['field1'=>'not received']);
    }

    /**
     * @depends testValidate
     */
    public function testValidateWithEmptyField()
    {
        $host = 'foo';
        $name = 'bar';
        $id = sha1($host.'$'.$name);
        $data = ['field1' => ''];
        $rules = ['field1' => true];

        $form = new Form($id, $host, $name, $data);
        $form->setValidationRules($rules);

        $this->assertFalse($form->validate());
        $this->assertEquals($form->getValidationErrors(), ['field1'=>'empty']);
    }

    /**
     * @depends testValidate
     */
    public function testValidateWithInvalidField()
    {
        $host = 'foo';
        $name = 'bar';
        $id = sha1($host.'$'.$name);
        $data = ['field1' => 'bar'];
        $rules = ['field1' => '/foo/'];

        $form = new Form($id, $host, $name, $data);
        $form->setValidationRules($rules);

        $this->assertFalse($form->validate());
        $this->assertEquals($form->getValidationErrors(), ['field1'=>'failed']);
    }

    /**
     * @depends testConstruct
     */
    public function testProcess()
    {
        $dispatcher = $this->createMock(DispatcherInterface::class);
        $dispatcher->expects($this->once())
            ->method('dispatch')
            ->with($this->isInstanceOf(SubmissionProcessedEvent::class));

        DomainEvents::setDispatcher($dispatcher);

        $host = 'foo';
        $name = 'bar';
        $id = sha1($host.'$'.$name);
        $data = ['foo' => 'bar'];

        $form = new Form($id, $host, $name, $data);
        $this->assertInstanceOf(Submission::class, $form->process());
    }
}
