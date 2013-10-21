<?php

namespace Lighthouse\CoreBundle\Tests\Validator\Constraints\Compare;

use DateTime;
use Lighthouse\CoreBundle\Test\TestCase;
use Lighthouse\CoreBundle\Tests\Validator\Constraints\CompareObjectFixture;
use Lighthouse\CoreBundle\Validator\Constraints\Compare\DatesCompare;
use Lighthouse\CoreBundle\Validator\Constraints\Compare\DatesCompareValidator;
use Symfony\Component\Validator\ExecutionContext;

class DatesCompareValidatorTest extends TestCase
{
    /**
     * @var ExecutionContext
     */
    protected $context;

    /**
     * @var DatesCompareValidator
     */
    protected $validator;

    public function setUp()
    {
        $this->context = $this->getMock('Symfony\Component\Validator\ExecutionContext', array(), array(), '', false);
        $this->validator = new DatesCompareValidator();
        $this->validator->initialize($this->context);
    }

    public function tearDown()
    {
        $this->context = null;
        $this->validator = null;
    }

    /**
     * @dataProvider validValuesProvider
     */
    public function testValidValues(DateTime $orderDate = null, DateTime $createdDate = null)
    {
        $this
            ->context
            ->expects($this->never())
            ->method('addViolationAt');

        $this->doValidate($orderDate, $createdDate);
    }

    /**
     * @return array
     */
    public function validValuesProvider()
    {
        return array(
            array(
                new \DateTime('2013-01-04'),
                new \DateTime('2013-01-03'),
            ),
            array(
                new \DateTime('2013-01-03 15:50:03'),
                new \DateTime('2013-01-03 15:50:02'),
            ),
            array(
                new \DateTime('2013-01-03 15:50:02'),
                new \DateTime('2013-01-03 15:50:02'),
            ),
            array(
                null,
                new \DateTime('2013-01-03 15:50:02'),
            ),
            array(
                new \DateTime('2013-01-03 15:50:02'),
                null,
            ),
        );
    }

    /**
     * @dataProvider invalidValuesProvider
     */
    public function testInvalidValues(DateTime $orderDate, DateTime $createdDate)
    {
        $this
            ->context
            ->expects($this->once())
            ->method('addViolationAt');

        $this->doValidate($orderDate, $createdDate);
    }

    /**
     * @return array
     */
    public function invalidValuesProvider()
    {
        return array(
            array(
                new \DateTime('2013-01-03'),
                new \DateTime('2013-01-04'),
            ),
            array(
                new \DateTime('2013-01-03 15:50:02'),
                new \DateTime('2013-01-03 15:50:03'),
            ),
        );
    }

    /**
     * @expectedException \Symfony\Component\Validator\Exception\UnexpectedTypeException
     * @expectedExceptionMessage Expected argument of type Lighthouse\CoreBundle\Document\AbstractDocument
     * @dataProvider unexpectedValueTypeProvider
     */
    public function testValueUnexpectedType($value)
    {
        $options = array(
            'minField' => 'orderDate',
            'maxField' => 'createdDate',
            'message' => 'message'
        );
        $constraint = new DatesCompare($options);

        $this->validator->validate($value, $constraint);
    }

    public function unexpectedValueTypeProvider()
    {
        return array(
            array(null),
            array(new \stdClass),
            array(array()),
            array(1),
            array(false),
            array("test")
        );
    }

    /**
     * @expectedException \Symfony\Component\Validator\Exception\UnexpectedTypeException
     * @expectedExceptionMessage Expected argument of type \DateTime
     * @dataProvider fieldUnexpectedTypesProvider
     */
    public function testFieldUnexpectedType($orderDate, $createdDate)
    {
        $this->doValidate($orderDate, $createdDate);
    }

    /**
     * @return array
     */
    public function fieldUnexpectedTypesProvider()
    {
        return array(
            array(
                123,
                new \DateTime('2013-01-04'),
            ),
            array(
                new \DateTime('2013-01-03 15:50:02'),
                564564,
            ),
            array(
                "test",
                new \DateTime('2013-01-04'),
            ),
            array(
                new \DateTime('2013-01-03 15:50:02'),
                "test",
            ),
            array(
                "2013-01-04",
                new \DateTime('2013-01-04'),
            ),
            array(
                new \DateTime('2013-01-03 15:50:02'),
                "2013-01-03 15:50:02",
            ),
        );
    }

    /**
     * @param \DateTime $orderDate
     * @param \DateTime $createdDate
     */
    protected function doValidate($orderDate = null, $createdDate = null)
    {
        $options = array(
            'minField' => 'createdDate',
            'maxField' => 'orderDate',
            'message' => 'message'
        );
        $constraint = new DatesCompare($options);

        $value = new CompareObjectFixture();
        $value->orderDate = $orderDate;
        $value->createdDate = $createdDate;

        $this->validator->validate($value, $constraint);
    }
}
