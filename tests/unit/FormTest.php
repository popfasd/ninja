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
        $url = 'foo';
        $id = sha1($url);
        $data = ['foo' => 'bar'];

        $form = new Form($id, $url, $data);

        $this->assertEquals($form->getId(), $id);
        $this->assertEquals($form->getUrl(), 'foo');
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
        $url = 'foo';
        $id = sha1($url);
        $form = new Form($id, $url);
        $form->setValidationKey('');
    }

    /**
     * @depends testConstruct
     */
    public function testValidate()
    {
        $url = 'foo';
        $id = sha1($url);
        $data = ['field1' => 'test'];

        $form = new Form($id, $url, $data);

        $this->assertTrue($form->validate());
        $this->assertNull($form->getValidationErrors());
    }

    /**
     * @depends testValidate
     */
    public function testValidateWithNotReceivedField()
    {
        $url = 'foo';
        $id = sha1($url);
        $data = [];
        $rules = ['field1' => true];

        $form = new Form($id, $url, $data);
        $form->setValidationRules($rules);

        $this->assertFalse($form->validate());
        $this->assertEquals($form->getValidationErrors(), ['field1'=>'not received']);
    }

    /**
     * @depends testValidate
     */
    public function testValidateWithEmptyField()
    {
        $url = 'foo';
        $id = sha1($url);
        $data = ['field1' => ''];
        $rules = ['field1' => true];

        $form = new Form($id, $url, $data);
        $form->setValidationRules($rules);

        $this->assertFalse($form->validate());
        $this->assertEquals($form->getValidationErrors(), ['field1'=>'empty']);
    }

    /**
     * @depends testValidate
     */
    public function testValidateWithInvalidField()
    {
        $url = 'foo';
        $id = sha1($url);
        $data = ['field1' => 'bar'];
        $rules = ['field1' => '/foo/'];

        $form = new Form($id, $url, $data);
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

        $url = 'foo';
        $id = sha1($url);
        $data = ['foo' => 'bar'];

        $form = new Form($id, $url, $data);
        $this->assertInstanceOf(Submission::class, $form->process());
    }
}
