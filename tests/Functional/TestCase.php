<?php
declare(strict_types=1);

namespace App\Tests\Functional;

use Doctrine\Common\DataFixtures\Purger\ORMPurger;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class TestCase extends WebTestCase
{
    protected EntityManagerInterface $entityManager;

    protected function setUp(): void
    {
        parent::setUp();

        $this->entityManager = self::getContainer()->get(EntityManagerInterface::class);

        $this->truncateEntities();
    }

    private function truncateEntities()
    {
        $purger = new ORMPurger($this->entityManager);
        $purger->purge();
    }
}
