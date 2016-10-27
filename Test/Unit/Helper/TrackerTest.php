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

namespace Partnerpages\Piwik\Test\Unit\Helper;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;

/**
 * Test for \Partnerpages\Piwik\Helper\Tracker
 *
 */
class TrackerTest extends \PHPUnit_Framework_TestCase
{

    /**
     * Piwik tracker helper (test subject) instance
     *
     * @var \Partnerpages\Piwik\Helper\Tracker $_helper
     */
    protected $_helper;

    /**
     * Tracker instance
     *
     * @var \Partnerpages\Piwik\Model\Tracker $_tracker
     */
    protected $_tracker;

    /**
     * Setup
     *
     * @return void
     */
    public function setUp()
    {
        $objectManager = new ObjectManager($this);

        // Create test subject
        $this->_helper = $objectManager->getObject(
            '\Partnerpages\Piwik\Helper\Tracker'
        );

        // Create tracker instance
        $class = '\Partnerpages\Piwik\Model\Tracker';
        $arguments = $objectManager->getConstructArguments($class, [
            'actionFactory' => $this->getMock(
                'Partnerpages\Piwik\Model\Tracker\ActionFactory',
                ['create'], [], '', false
            )
        ]);
        $arguments['actionFactory']
            ->expects($this->any())
            ->method('create')
            ->willReturnCallback(function ($data) {
                return new \Partnerpages\Piwik\Model\Tracker\Action(
                    $data['name'],
                    $data['args']
                );
            });
        $this->_tracker = $objectManager->getObject($class, $arguments);
    }

    /**
     * Quote item data provider
     *
     * @return array
     */
    public function addQuoteDataProvider()
    {
        return [
            [
                [
                    ['SKUA', 'First product',  123.45, 1],
                    ['SKUB', 'Second product', 6780,   2]
                ],
                123456.78
            ],
            [
                [
                    ['',    '',    '123.45', '0'],
                    [null,  true,  0,        false],
                    [false, 0,     -123,     -2]
                ],
                null
            ]
        ];
    }

    /**
     * Test for \Partnerpages\Piwik\Helper\Tracker::addQuote
     *
     * Also covers `addQuoteItem' and `addQuoteTotal'
     *
     * @param array $items
     * @param float $total
     * @dataProvider addQuoteDataProvider
     */
    public function testAddQuote($items, $total)
    {
        // Build expected tracker result from $items and $total
        $expectedResult = [];
        foreach ($items as $item) {
            list($sku, $name, $price, $qty) = $item;
            $expectedResult[] = [
                'addEcommerceItem',
                $sku,
                $name,
                false,
                (float) $price,
                (float) $qty
            ];
        }
        $expectedResult[] = ['trackEcommerceCartUpdate', (float) $total];

        // Test `addQuote' with mock quote created from $items and $total
        $this->_helper->addQuote(
            $this->_getQuoteMock($items, $total),
            $this->_tracker
        );
        $this->assertSame($expectedResult, $this->_tracker->toArray());
    }

    /**
     * Create a mock quote object with given data
     *
     * @param array $items
     * @param float $total
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    protected function _getQuoteMock($items, $total)
    {
        $quoteItems = [];
        foreach ($items as $itemData) {
            list($sku, $name, $price, $qty) = $itemData;
            $itemClass = 'Magento\Quote\Model\Quote\Item';
            $item = $this->getMock($itemClass, ['getData'], [], '', false);
            $item
                ->expects($this->any())
                ->method('getData')
                ->willReturnMap([
                    ['sku',            null, $sku],
                    ['name',           null, $name],
                    ['price_incl_tax', null, $price],
                    ['qty',            null, $qty]
                ]);
            $quoteItems[] = $item;
        }

        $quote = $this->getMock(
            'Magento\Quote\Model\Quote',
            ['getAllVisibleItems', 'getData'],
            [], '', false
        );
        $quote
            ->expects($this->any())
            ->method('getAllVisibleItems')
            ->willReturn($quoteItems);
        $quote
            ->expects($this->any())
            ->method('getData')
            ->with('base_grand_total')
            ->willReturn($total);

        return $quote;
    }
}
