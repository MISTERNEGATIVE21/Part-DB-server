<?php
/**
 * This file is part of Part-DB (https://github.com/Part-DB/Part-DB-symfony).
 *
 * Copyright (C) 2019 Jan Böhmer (https://github.com/jbtronics)
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License
 * as published by the Free Software Foundation; either version 2
 * of the License, or (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA
 */

namespace App\Tests\Services;

use App\Entity\UserSystem\Group;
use App\Entity\UserSystem\PermissionsEmbed;
use App\Entity\UserSystem\User;
use App\Services\PermissionResolver;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class PermissionResolverTest extends WebTestCase
{
    /**
     * @var PermissionResolver
     */
    protected $service;

    protected $user;
    protected $user_withoutGroup;
    protected $group;

    public function setUp() : void
    {
        parent::setUp(); // TODO: Change the autogenerated stub

        //Get an service instance.
        self::bootKernel();
        $this->service = self::$container->get(PermissionResolver::class);

        //Set up a mocked user
        $user_embed = new PermissionsEmbed();
        $user_embed->setPermissionValue('parts', 0, true) //read
            ->setPermissionValue('parts', 2, false) //edit
            ->setPermissionValue('parts', 4, null) //create
            ->setPermissionValue('parts', 30, null) //move
            ->setPermissionValue('parts', 8, null); //delete

        $this->user = $this->createMock(User::class);
        $this->user->method('getPermissions')->willReturn($user_embed);

        $this->user_withoutGroup = $this->createMock(User::class);
        $this->user_withoutGroup->method('getPermissions')->willReturn($user_embed);
        $this->user_withoutGroup->method('getGroup')->willReturn(null);

        //Set up a faked group
        $group1_embed = new PermissionsEmbed();
        $group1_embed->setPermissionValue('parts', 6, true)
            ->setPermissionValue('parts', 8, false)
            ->setPermissionValue('parts', 10, null)
            ->setPermissionValue('parts', 0, false)
            ->setPermissionValue('parts', 30, true)
            ->setPermissionValue('parts', 2, true);

        $this->group = $this->createMock(Group::class);
        $this->group->method('getPermissions')->willReturn($group1_embed);

        //Set this group for the user
        $this->user->method('getGroup')->willReturn($this->group);

        //parent group
        $parent_group_embed = new PermissionsEmbed();
        $parent_group_embed->setPermissionValue('parts', 12, true)
            ->setPermissionValue('parts', 14, false)
            ->setPermissionValue('parts', 16, null);
        $parent_group = $this->createMock(Group::class);
        $parent_group->method('getPermissions')->willReturn($parent_group_embed);

        $this->group->method('getParent')->willReturn($parent_group);
    }

    public function getPermissionNames()
    {
        //List all possible operation names.
        return [
            [PermissionsEmbed::PARTS],
            [PermissionsEmbed::USERS],
            [PermissionsEmbed::PARTS_ORDERDETAILS],
            [PermissionsEmbed::PARTS_NAME],
            [PermissionsEmbed::PARTS_ORDER],
            [PermissionsEmbed::PARTS_MINAMOUNT],
            [PermissionsEmbed::PARTS_MANUFACTURER],
            [PermissionsEmbed::DEVICES],
            [PermissionsEmbed::PARTS_FOOTPRINT],
            [PermissionsEmbed::PARTS_DESCRIPTION],
            [PermissionsEmbed::PARTS_COMMENT],
            [PermissionsEmbed::PARTS_ATTACHMENTS],
            [PermissionsEmbed::MANUFACTURERS],
            [PermissionsEmbed::LABELS],
            [PermissionsEmbed::DATABASE],
            [PermissionsEmbed::GROUPS],
            [PermissionsEmbed::FOOTRPINTS],
            [PermissionsEmbed::DEVICE_PARTS],
            [PermissionsEmbed::CATEGORIES],
            [PermissionsEmbed::PARTS_PRICES],
            [PermissionsEmbed::ATTACHMENT_TYPES],
            [PermissionsEmbed::CONFIG],
        ];
    }

    /**
     * @dataProvider getPermissionNames
     */
    public function testListOperationsForPermission($perm_name)
    {
        $arr = $this->service->listOperationsForPermission($perm_name);

        //Every entry should not be empty.
        $this->assertNotEmpty($arr);
    }

    public function testInvalidListOperationsForPermission()
    {
        $this->expectException(\InvalidArgumentException::class);
        //Must throw an exception
        $this->service->listOperationsForPermission('invalid');
    }

    public function testisValidPermission()
    {
        $this->assertTrue($this->service->isValidPermission('parts'));
        $this->assertFalse($this->service->isValidPermission('invalid'));
    }

    public function testIsValidOperation()
    {
        $this->assertTrue($this->service->isValidOperation('parts', 'read'));

        //Must return false if either the permission or the operation is not existing
        $this->assertFalse($this->service->isValidOperation('parts', 'invalid'));
        $this->assertFalse($this->service->isValidOperation('invalid', 'read'));
        $this->assertFalse($this->service->isValidOperation('invalid', 'invalid'));
    }

    public function testDontInherit()
    {
        //Check with faked object
        $this->assertTrue($this->service->dontInherit($this->user, 'parts', 'read'));
        $this->assertFalse($this->service->dontInherit($this->user, 'parts', 'edit'));
        $this->assertNull($this->service->dontInherit($this->user, 'parts', 'create'));
        $this->assertNull($this->service->dontInherit($this->user, 'parts', 'show_history'));
        $this->assertNull($this->service->dontInherit($this->user, 'parts', 'delete'));

        //Test for user without group
        $this->assertTrue($this->service->dontInherit($this->user_withoutGroup, 'parts', 'read'));
        $this->assertFalse($this->service->dontInherit($this->user_withoutGroup, 'parts', 'edit'));
        $this->assertNull($this->service->dontInherit($this->user_withoutGroup, 'parts', 'create'));
        $this->assertNull($this->service->dontInherit($this->user_withoutGroup, 'parts', 'show_history'));
        $this->assertNull($this->service->dontInherit($this->user_withoutGroup, 'parts', 'delete'));
    }

    public function testInherit()
    {
        //Not inherited values should be same as dont inherit:
        $this->assertTrue($this->service->inherit($this->user, 'parts', 'read'));
        $this->assertFalse($this->service->inherit($this->user, 'parts', 'edit'));
        //When thing can not be resolved null should be returned
        $this->assertNull($this->service->inherit($this->user, 'parts', 'create'));

        //Check for inherit from group
        $this->assertTrue($this->service->inherit($this->user, 'parts', 'show_history'));
        $this->assertFalse($this->service->inherit($this->user, 'parts', 'delete'));
        $this->assertNull($this->service->inherit($this->user, 'parts', 'search'));

        //Check for inherit from group and parent group
        $this->assertTrue($this->service->inherit($this->user, 'parts', 'all_parts'));
        $this->assertFalse($this->service->inherit($this->user, 'parts', 'no_price_parts'));
        $this->assertNull($this->service->inherit($this->user, 'parts', 'obsolete_parts'));

        //Test for user without group
        $this->assertTrue($this->service->inherit($this->user_withoutGroup, 'parts', 'read'));
        $this->assertFalse($this->service->inherit($this->user_withoutGroup, 'parts', 'edit'));
        $this->assertNull($this->service->inherit($this->user_withoutGroup, 'parts', 'create'));
        $this->assertNull($this->service->inherit($this->user_withoutGroup, 'parts', 'show_history'));
        $this->assertNull($this->service->inherit($this->user_withoutGroup, 'parts', 'delete'));
    }
}
