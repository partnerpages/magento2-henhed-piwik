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

namespace Partnerpages\Piwik\Test\Unit\Model;

use \Partnerpages\Piwik\Model\Tracker;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;

/**
 * Test for \Partnerpages\Piwik\Model\Tracker
 *
 */
class TrackerTest extends \PHPUnit_Framework_TestCase
{

    /**
     * Tracker instance
     *
     * @var \Partnerpages\Piwik\Model\Tracker $_tracker
     */
    protected $_tracker;

    /**
     * Action factory mock
     *
     * @var \PHPUnit_Framework_MockObject_MockObject $_actionFactory
     */
    protected $_actionFactory;

    /**
     * Setup
     *
     * @return void
     */
    public function setUp()
    {
        $className = '\Partnerpages\Piwik\Model\Tracker';
        $objectManager = new ObjectManager($this);
        $arguments = $objectManager->getConstructArguments($className, [
            'actionFactory' => $this->getMock(
                'Partnerpages\Piwik\Model\Tracker\ActionFactory',
                ['create'], [], '', false
            )
        ]);
        $this->_tracker = $objectManager->getObject($className, $arguments);
        $this->_actionFactory = $arguments['actionFactory'];
    }

    /**
     * Test tracker action push
     *
     * Covers Tracker::push and Tracker::toArray
     *
     * @param string $name
     * @param array $args
     * @dataProvider trackerActionDataProvider
     */
    public function testPush($name, $args)
    {
        $this->_tracker->push(new Tracker\Action($name, $args));
        $this->assertEquals(
            [array_merge([$name], $args)],
            $this->_tracker->toArray()
        );
    }

    /**
     * Test magic tracker action push
     *
     * Covers Tracker::__call and Tracker::toArray
     *
     * @param string $name
     * @param array $args
     * @dataProvider trackerActionDataProvider
     */
    public function testMagicPush($name, $args)
    {
        $this->_actionFactory
            ->expects($this->once())
            ->method('create')
            ->with([
                'name' => $name,
                'args' => $args
            ])
            ->will($this->returnValue(new Tracker\Action($name, $args)));

        call_user_func_array(array($this->_tracker, $name), $args);

        $this->assertEquals(
            [array_merge([$name], $args)],
            $this->_tracker->toArray()
        );
    }

    /**
     * Tracker action data provider
     *
     * @return array
     */
    public function trackerActionDataProvider()
    {
        return [
            ['trackEvent',      ['category', 'action', 'name', 1]],
            ['trackPageView',   ['customTitle']],
            ['trackSiteSearch', ['keyword', 'category', 0]],
            ['trackGoal',       [1, 1.1]],
            ['trackLink',       ['url', 'linkType']],
            ['disableCookies',  []]
        ];
    }
}
