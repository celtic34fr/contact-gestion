<?php

namespace Bolt\Extension\Celtic34fr\ContactCore\Tests;

use Bolt\Extension\Celtic34fr\ContactGestion\Extension;
use PHPUnit\Framework\TestCase;

/**
 * Menueditor testing class.
 *
 * @author Your Name <you@example.com>
 */
;
class ExtensionTest extends TestCase
{
    /**
     * Ensure that the Menueditor extension loads correctly.
     */
    public function testExtensionBasics()
    {
        $extension = new Extension();

        $name = $extension->getName();
        $this->assertSame($name, 'Bolt Celtic34fr Contact Formular and Managment Extension');
        $this->assertInstanceOf('\Bolt\Extension\ExtensionInterface', $extension);
    }}