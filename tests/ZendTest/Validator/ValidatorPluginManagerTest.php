<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2014 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace ZendTest\Validator;

use Zend\Validator\ValidatorPluginManager;

/**
 * @group      Zend_Validator
 */
class ValidatorPluginManagerTest extends \PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        $this->validators = new ValidatorPluginManager();
    }

    public function testAllowsInjectingTranslator()
    {
        $translator = $this->getMock('ZendTest\Validator\TestAsset\Translator');

        $slContents = array(array('MvcTranslator', $translator));
        $serviceLocator = $this->getMock('Zend\ServiceManager\ServiceLocatorInterface');
        $serviceLocator->expects($this->once())
            ->method('get')
            ->will($this->returnValueMap($slContents));
        $serviceLocator->expects($this->once())
            ->method('has')
            ->with($this->equalTo('MvcTranslator'))
            ->will($this->returnValue(true));

        $this->validators->setServiceLocator($serviceLocator);
        $this->assertSame($serviceLocator, $this->validators->getServiceLocator());

        $validator = $this->validators->get('notempty');
        $this->assertSame($translator, $validator->getTranslator());
    }

    public function testNoTranslatorInjectedWhenTranslatorIsNotPresent()
    {
        $serviceLocator = $this->getMock('Zend\ServiceManager\ServiceLocatorInterface');
        $serviceLocator->expects($this->once())
            ->method('has')
            ->with($this->equalTo('MvcTranslator'))
            ->will($this->returnValue(false));

        $this->validators->setServiceLocator($serviceLocator);
        $this->assertSame($serviceLocator, $this->validators->getServiceLocator());

        $validator = $this->validators->get('notempty');
        $this->assertNull($validator->getTranslator());
    }

    public function testRegisteringInvalidValidatorRaisesException()
    {
        $this->setExpectedException('Zend\Validator\Exception\RuntimeException');
        $this->validators->setService('test', $this);
    }

    public function testLoadingInvalidValidatorRaisesException()
    {
        $this->validators->setInvokableClass('test', get_class($this));
        $this->setExpectedException('Zend\Validator\Exception\RuntimeException');
        $this->validators->get('test');
    }

    public function testInjectedValidatorPluginManager()
    {
        $validator = $this->validators->get('explode');
        $this->assertSame($this->validators, $validator->getValidatorPluginManager());
    }
}
