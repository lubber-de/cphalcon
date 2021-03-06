<?php
declare(strict_types=1);

/**
 * This file is part of the Phalcon Framework.
 *
 * (c) Phalcon Team <team@phalconphp.com>
 *
 * For the full copyright and license information, please view the LICENSE.txt
 * file that was distributed with this source code.
 */

namespace Phalcon\Test\Unit\Acl\Adapter\Memory;

use Exception;
use Phalcon\Acl;
use Phalcon\Acl\Adapter\Memory;
use Phalcon\Test\Fixtures\Acl\TestOperationAware;
use Phalcon\Test\Fixtures\Acl\TestSubjectAware;
use UnitTester;

/**
 * Class AllowCest
 */
class AllowCest
{
    /**
     * Tests Phalcon\Acl\Adapter\Memory :: allow()
     *
     * @param UnitTester $I
     *
     * @author Phalcon Team <team@phalconphp.com>
     * @since  2018-11-13
     */
    public function aclAdapterMemoryAllow(UnitTester $I)
    {
        $I->wantToTest('Acl\Adapter\Memory - allow()');
        $acl = new Memory();
        $acl->setDefaultAction(Acl::DENY);
        $acl->addOperation('Guests');
        $acl->addOperation('Member');
        $acl->addSubject('Post', ['update']);

        $acl->allow('Member', 'Post', 'update');

        $actual = $acl->isAllowed('Guest', 'Post', 'update');
        $I->assertFalse($actual);
        $actual = $acl->isAllowed('Member', 'Post', 'update');
        $I->assertTrue($actual);
    }

    /**
     * Tests Phalcon\Acl\Adapter\Memory :: allow() - function
     *
     * @param UnitTester $I
     *
     * @issue   https://github.com/phalcon/cphalcon/issues/11235
     *
     * @author  Wojciech Slawski <jurigag@gmail.com>
     * @since   2015-12-16
     */
    public function aclAdapterMemoryAllowFunction(UnitTester $I)
    {
        $I->wantToTest('Acl\Adapter\Memory - allow() - function');
        $acl = new Memory();
        $acl->setDefaultAction(Acl::DENY);
        $acl->addOperation('Guests');
        $acl->addOperation('Members', 'Guests');
        $acl->addOperation('Admins', 'Members');
        $acl->addSubject('Post', ['update']);

        $guest         = new TestOperationAware(1, 'Guests');
        $member        = new TestOperationAware(2, 'Members');
        $anotherMember = new TestOperationAware(3, 'Members');
        $admin         = new TestOperationAware(4, 'Admins');
        $model         = new TestSubjectAware(2, 'Post');

        $acl->deny('Guests', 'Post', 'update');
        $acl->allow(
            'Members',
            'Post',
            'update',
            function (TestOperationAware $user, TestSubjectAware $model) {
                return $user->getId() == $model->getUser();
            }
        );
        $acl->allow('Admins', 'Post', 'update');

        $actual = $acl->isAllowed($guest, $model, 'update');
        $I->assertFalse($actual);

        $actual = $acl->isAllowed($member, $model, 'update');
        $I->assertTrue($actual);

        $actual = $acl->isAllowed($anotherMember, $model, 'update');
        $I->assertFalse($actual);

        $actual = $acl->isAllowed($admin, $model, 'update');
        $I->assertTrue($actual);
    }

    /**
     * Tests Phalcon\Acl\Adapter\Memory :: allow() - function exception
     *
     * @issue   https://github.com/phalcon/cphalcon/issues/11235
     *
     * @author  Wojciech Slawski <jurigag@gmail.com>
     * @since   2016-06-05
     */
    public function aclAdapterMemoryAllowFunctionException(UnitTester $I)
    {
        $I->expectThrowable(
            new Exception(
                "You didn't provide any parameters when 'Guests' can " .
                "'update' 'Post'. We will use default action when no arguments.",
                1024
            ),
            function () {
                $acl = new Memory;
                $acl->setDefaultAction(Acl::ALLOW);
                $acl->setNoArgumentsDefaultAction(Acl::DENY);
                $acl->addOperation('Guests');
                $acl->addOperation('Members', 'Guests');
                $acl->addOperation('Admins', 'Members');
                $acl->addSubject('Post', ['update']);

                $guest         = new TestOperationAware(1, 'Guests');
                $member        = new TestOperationAware(2, 'Members');
                $anotherMember = new TestOperationAware(3, 'Members');
                $admin         = new TestOperationAware(4, 'Admins');
                $model         = new TestSubjectAware(2, 'Post');

                $acl->allow('Guests', 'Post', 'update', function ($parameter) {
                    return $parameter % 2 == 0;
                });
                $acl->allow('Members', 'Post', 'update', function ($parameter) {
                    return $parameter % 2 == 0;
                });
                $acl->allow('Admins', 'Post', 'update');

                $actual = $acl->isAllowed($guest, $model, 'update');
                $I->assertFalse($actual);
                $actual = $acl->isAllowed($member, $model, 'update');
                $I->assertFalse($actual);
                $actual = $acl->isAllowed($anotherMember, $model, 'update');
                $I->assertFalse($actual);
                $actual = $acl->isAllowed($admin, $model, 'update');
                $I->assertTrue($actual);
            }
        );
    }
}
