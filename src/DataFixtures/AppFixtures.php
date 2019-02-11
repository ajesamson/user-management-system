<?php

namespace App\DataFixtures;

use App\Entity\Group;
use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\Persistence\ObjectManager;

class AppFixtures extends Fixture
{
    private const USER_REFERENCE = 'user';

    public function load(ObjectManager $manager)
    {
        $this->loadUser($manager);
        $this->loadGroup($manager);
    }

    /**
     * Generates fixtures for User
     */
    private function loadUser(ObjectManager $manager): void
    {
        $user = new User();
        $user->setName('username');

        $manager->persist($user);
        $manager->flush();

        $this->addReference(self::USER_REFERENCE, $user);
    }

    /**
     * Generates fixtures for Group
     */
    private function loadGroup(ObjectManager $manager): void
    {
        $group = new Group();
        $group->setName('Technology');
        $group->addUser($this->getReference(self::USER_REFERENCE));

        $manager->persist($group);
        $manager->flush();
    }
}
