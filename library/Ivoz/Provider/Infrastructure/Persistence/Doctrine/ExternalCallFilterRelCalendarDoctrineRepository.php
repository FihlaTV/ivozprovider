<?php

namespace Ivoz\Provider\Infrastructure\Persistence\Doctrine;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Ivoz\Provider\Domain\Model\ExternalCallFilterRelCalendar\ExternalCallFilterRelCalendar;
use Ivoz\Provider\Domain\Model\ExternalCallFilterRelCalendar\ExternalCallFilterRelCalendarRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * ExternalCallFilterRelCalendarDoctrineRepository
 *
 * This class was generated by the Doctrine ORM. Add your own custom
 * repository methods below.
 */
class ExternalCallFilterRelCalendarDoctrineRepository extends ServiceEntityRepository implements ExternalCallFilterRelCalendarRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, ExternalCallFilterRelCalendar::class);
    }
}
