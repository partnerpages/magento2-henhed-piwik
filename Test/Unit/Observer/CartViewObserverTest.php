<?php
/**
 * Copyright 2016 Henrik Hedelund
 *
 * This file is part of Partnerpages_Piwik.
 *
 * Partnerpages_Piwik is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Affero General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * Partnerpages_Piwik is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU Affero General Public License for more details.
 *
 * You should have received a copy of the GNU Affero General Public License
 * along with Partnerpages_Piwik.  If not, see <http://www.gnu.org/licenses/>.
 */

namespace Partnerpages\Piwik\Test\Unit\Observer;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;

/**
 * Test for \Partnerpages\Piwik\Observer\CartViewObserver
 *
 */
class CartViewObserverTest extends \PHPUnit_Framework_TestCase
{

    /**
     * Cart view observer (test subject) instance
     *
     * @var \Partnerpages\Piwik\Observer\CartViewObserver
     */
    protected $_observer;

    /**
     * Event observer mock object
     *
     * @var \PHPUnit_Framework_MockObject_MockObject $_eventObserverMock
     */
    protected $_eventObserverMock;

    /**
     * Tracker mock object
     *
     * @var \PHPUnit_Framework_MockObject_MockObject $_eventObserverMock
     */
    protected $_trackerMock;

    /**
     * Tracker helper mock object
     *
     * @var \PHPUnit_Framework_MockObject_MockObject $_eventObserverMock
     */
    protected $_trackerHelperMock;

    /**
     * Piwik data helper mock object
     *
     * @var \PHPUnit_Framework_MockObject_MockObject $_dataHelperMock
     */
    protected $_dataHelperMock;

    /**
     * Checkout session mock object
     *
     * @var \PHPUnit_Framework_MockObject_MockObject $_eventObserverMock
     */
    protected $_checkoutSessionMock;

    /**
     * Quote mock object
     *
     * @var \PHPUnit_Framework_MockObject_MockObject $_eventObserverMock
     */
    protected $_quoteMock;

    /**
     * Set up
     *
     * @return void
     */
    public function setUp()
    {
        $className = 'Partnerpages\Piwik\Observer\CartViewObserver';
        $objectManager = new ObjectManager($this);
        $arguments = $objectManager->getConstructArguments($className);
        $this->_observer = $objectManager->getObject($className, $arguments);
        $this->_trackerMock = $arguments['piwikTracker'];
        $this->_trackerHelperMock = $arguments['trackerHelper'];
        $this->_dataHelperMock = $arguments['dataHelper'];
        $this->_checkoutSessionMock = $arguments['checkoutSession'];
        $this->_eventObserverMock = $this->getMock(
            'Magento\Framework\Event\Observer', [], [], '', false
        );
        $this->_quoteMock = $this->getMock(
            'Magento\Quote\Model\Quote', [], [], '', false
        );
    }

    /**
     * Test for \Partnerpages\Piwik\Observer\CartViewObserver::execute where
     * tracking is enabled.
     *
     * @return void
     */
    public function testExecuteWithTrackingEnabled()
    {
        // Enable tracking
        $this->_dataHelperMock
            ->expects($this->once())
            ->method('isTrackingEnabled')
            ->willReturn(true);

        // Provide quote mock access from checkout session mock
        $this->_checkoutSessionMock
            ->expects($this->any())
            ->method('getQuote')
            ->willReturn($this->_quoteMock);

        // Make sure the tracker helpers `addQuote' is called exactly once with
        // provided quote and tracker. Actual behavior of `addQuote' is covered
        // by \Partnerpages\Piwik\Test\Unit\Helper\TrackerTest.
        $this->_trackerHelperMock
            ->expects($this->once())
            ->method('addQuote')
            ->with($this->_quoteMock, $this->_trackerMock)
            ->willReturn($this->_trackerHelperMock);

        // Assert that `execute' returns $this
        $this->assertSame(
            $this->_observer,
            $this->_observer->execute($this->_eventObserverMock)
        );
    }

    /**
     * Test for \Partnerpages\Piwik\Observer\CartViewObserver::execute where
     * tracking is disabled.
     *
     * @return void
     */
    public function testExecuteWithTrackingDisabled()
    {
        // Disable tracking
        $this->_dataHelperMock
            ->expects($this->once())
            ->method('isTrackingEnabled')
            ->willReturn(false);

        // Provide quote mock access from checkout session mock
        $this->_checkoutSessionMock
            ->expects($this->any())
            ->method('getQuote')
            ->willReturn($this->_quoteMock);

        // Make sure the tracker helpers `addQuote' is never called
        $this->_trackerHelperMock
            ->expects($this->never())
            ->method('addQuote');

        // Assert that `execute' returns $this
        $this->assertSame(
            $this->_observer,
            $this->_observer->execute($this->_eventObserverMock)
        );
    }
}
