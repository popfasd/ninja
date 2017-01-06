<?php

use Popfasd\Ninja\Form;
use Psr\Http\Message\RequestInterface;
use org\bovigo\vfs\vfsStream;

class FormTest extends PHPUnit_Framework_TestCase
{
    public function testConstruct()
    {
        $vfs = vfsStream::setup('root');

        $request = $this->getMock(RequestInterface::class);

        $request->expects($this->exactly(2))
            ->method('getHeaderLine')
            ->with('Referer')
            ->willReturn('foo');

        $form = new Form($request, vfsStream::url('root'));

        $this->assertEquals($form->getId(), sha1('foo'));
        $this->assertEquals($form->getRequest(), $request);
        $this->assertEquals($form->getUrl(), 'foo');
        $this->assertNull($form->getFields());
        $this->assertEquals($form->getCacheDir(), vfsStream::url('root').'/'.$form->getId());
        $this->assertNull($form->getNextUrl());
    }
}
