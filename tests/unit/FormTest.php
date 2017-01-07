<?php

use Popfasd\Ninja\Form;
use Popfasd\Ninja\Submission;
use Popfasd\Ninja\SubmissionProcessedEvent;
use Popfasd\Ninja\DomainEvents;
use Psr\Http\Message\ServerRequestInterface;
use org\bovigo\vfs\vfsStream;
use MattFerris\Events\DispatcherInterface;

class FormTest extends PHPUnit_Framework_TestCase
{
    protected function makeRequest($referer = 'foo', $count = 1)
    {
        $request = $this->getMock(ServerRequestInterface::class);
        $request->expects($this->exactly($count))
            ->method('getHeaderLine')
            ->with('Referer')
            ->willReturn($referer);
        return $request;
    }

    protected function makeRequestWithBody($body, $count = 1, $request = null)
    {
        if (is_null($request)) {
            $request = $this->makeRequest();
        }
        $request->expects($this->exactly($count))
            ->method('getParsedBody')
            ->willReturn($body);
        return $request;
    }

    public function testConstructWithNewForm()
    {
        $vfs = vfsStream::setup('testConstruct');

        $request = $this->makeRequest('foo', 2);

        $formId = sha1('foo');
        $cacheDir = vfsStream::url('testConstruct');
        $formDir = $cacheDir.'/'.$formId;

        $form = new Form($request, $cacheDir);

        $this->assertEquals($form->getId(), $formId);
        $this->assertEquals($form->getRequest(), $request);
        $this->assertEquals($form->getUrl(), 'foo');
        $this->assertNull($form->getFields());
        $this->assertEquals($form->getCacheDir(), $formDir);
        $this->assertNull($form->getNextUrl());
        $this->assertTrue($vfs->hasChild($formId));
        $this->assertTrue($vfs->getChild($formId)->hasChild('settings.php'));
    }

    /**
     * @depends testConstructWithNewForm
     */
    public function testConstructWithExistingForm()
    {
        $request = $this->makeRequest();

        $formId = sha1('foo');
        $cacheDir = vfsStream::url('testConstruct');
        $formDir = $cacheDir.'/'.$formId;

        file_put_contents($formDir.'/settings.php', "<?php
\$nexturl = 'bar';
\$fields = ['field1'=>'Field One'];
");

        $form = new Form($request, $cacheDir);

        $this->assertEquals($form->getFields(), ['field1'=>'Field One']);
        $this->assertEquals($form->getFieldTitle('field1'), 'Field One');
        $this->assertTrue($form->hasField('field1'));
        $this->assertEquals($form->getNextUrl(), 'bar');
    }

    /**
     * @depends testConstructWithNewForm
     * @expectedException InvalidArgumentException
     * @expectedExceptionMessage $key expects non-empty string
     */
    public function testSetEmptyValidationKey()
    {
        $vfs = vfsStream::setup('testSetValidationKey');
        $request = $this->makeRequest();
        $form = new Form($request, vfsStream::url('testSetValidationKey'));
        $form->setValidationKey('');
    }

    /**
     * @depends testConstructWithNewForm
     */
    public function testValidateWithNewForm()
    {
        vfsStream::setup('testValidate');

        $request = $this->makeRequest();
        $request->expects($this->once())
            ->method('getParsedBody')
            ->willReturn(['field1'=>'test']);

        $form = new Form($request, vfsStream::url('testValidate'));

        $this->assertTrue($form->validate());
        $this->assertNull($form->getValidationErrors());
    }

    /**
     * @depends testValidateWithNewForm
     */
    public function testValidateWithExistingForm()
    {
        $request = $this->makeRequestWithBody([
                'field1' => 'test',
                'field2' => ''
        ]);

        file_put_contents(vfsStream::url('testValidate').'/'.sha1('foo').'/settings.php', "<?php
");

        $form = new Form($request, vfsStream::url('testValidate'));
        $this->assertTrue($form->validate());
        $this->assertNull($form->getValidationErrors());
    }

    /**
     * @depends testValidateWithExistingForm
     */
    public function testValidateWithNotReceivedField()
    {
        $request = $this->makeRequestWithBody([]);

        file_put_contents(vfsStream::url('testValidate').'/'.sha1('foo').'/settings.php', "<?php
\$validationRules = ['field1'=>true];
");

        $form = new Form($request, vfsStream::url('testValidate'));
        $this->assertFalse($form->validate());
        $this->assertEquals($form->getValidationErrors(), ['field1'=>'not received']);
    }

    /**
     * @depends testValidateWithExistingForm
     */
    public function testValidateWithEmptyField()
    {
        $request = $this->makeRequestWithBody(['field1'=>'']);

        file_put_contents(vfsStream::url('testValidate').'/'.sha1('foo').'/settings.php', "<?php
\$validationRules = ['field1'=>true];
");

        $form = new Form($request, vfsStream::url('testValidate'));
        $this->assertFalse($form->validate());
        $this->assertEquals($form->getValidationErrors(), ['field1'=>'empty']);
    }

    /**
     * @depends testValidateWithExistingForm
     */
    public function testValidateWithInvalidField()
    {
        $request = $this->makeRequestWithBody(['field1'=>'bar']);

        file_put_contents(vfsStream::url('testValidate').'/'.sha1('foo').'/settings.php', "<?php
\$validationRules = ['field1'=>'/foo/'];
");

        $form = new Form($request, vfsStream::url('testValidate'));
        $this->assertFalse($form->validate());
        $this->assertEquals($form->getValidationErrors(), ['field1'=>'failed']);
    }

    /**
     * @depends testConstructWithNewForm
     */
    public function testProcessWithNewForm()
    {
        vfsStream::setup('testProcess');

        $dispatcher = $this->getMock(DispatcherInterface::class);
        $dispatcher->expects($this->once())
            ->method('dispatch')
            ->with($this->isInstanceOf(SubmissionProcessedEvent::class));

        DomainEvents::setDispatcher($dispatcher);

        $request = $this->makeRequestWithBody([]);

        $form = new Form($request, vfsStream::url('testProcess'));
        $this->assertInstanceOf(Submission::class, $form->process());
    }
}
