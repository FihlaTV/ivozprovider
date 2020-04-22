<?php

namespace Ivoz\Provider\Infrastructure\Persistence\Doctrine;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Ivoz\Provider\Domain\Model\Administrator\AdministratorInterface;
use Ivoz\Provider\Domain\Model\Brand\BrandInterface;
use Ivoz\Provider\Domain\Model\Carrier\Carrier;
use Ivoz\Provider\Domain\Model\Carrier\CarrierInterface;
use Ivoz\Provider\Domain\Model\Carrier\CarrierRepository;
use Ivoz\Provider\Domain\Model\ProxyTrunk\ProxyTrunkInterface;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * CarrierDoctrineRepository
 *
 * This class was generated by the Doctrine ORM. Add your own custom
 * repository methods below.
 */
class CarrierDoctrineRepository extends ServiceEntityRepository implements CarrierRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, Carrier::class);
    }

    /**
     * @return array
     */
    public function getCarrierIdsGroupByBrand()
    {
        /** @var CarrierInterface[] $carriers */
        $carriers = $this->findAll();
        $response = [];

        foreach ($carriers as $carrier) {
            $brandId = $carrier->getBrand()->getId();
            if (!array_key_exists($brandId, $response)) {
                $response[$brandId] = [];
            }
            $response[$brandId][] = $carrier->getId();
        }

        return $response;
    }

    public function getCarrierIdsByBrandAdmin(AdministratorInterface $admin): array
    {
        if (!$admin->isBrandAdmin()) {
            throw new \DomainException('User must be brand admin');
        }

        $qb = $this->createQueryBuilder('self');
        $expression = $qb->expr();

        $qb
            ->select('self.id')
            ->where(
                $expression->eq(
                    'self.brand',
                    $admin->getBrand()->getId()
                )
            );

        $result = $qb
            ->getQuery()
            ->getScalarResult();

        return array_column(
            $result,
            'id'
        );
    }

    /**
     * @param BrandInterface $brand
     * @param ProxyTrunkInterface $proxyTrunks
     * @return array|CarrierInterface[]
     */
    public function findByBrandAndProxyTrunks(BrandInterface $brand, ProxyTrunkInterface $proxyTrunks)
    {
        /** @var CarrierInterface[] $response */
        $response = $this->findBy([
            'brand' => $brand,
            'proxyTrunk' => $proxyTrunks
        ]);

        return $response;
    }

    /**
     * @param ProxyTrunkInterface $proxyTrunks
     * @return array|mixed
     */
    public function findByProxyTrunks(ProxyTrunkInterface $proxyTrunks)
    {
        /** @var CarrierInterface[] $response */
        $response = $this->findBy([
            'proxyTrunk' => $proxyTrunks
        ]);

        return $response;
    }

    /**
     * @param int $brandId | null
     * @return string[]
     */
    public function getNames($brandId = null)
    {
        $qb = $this->createQueryBuilder('self');

        $qb->select('self.id, self.name');

        if ($brandId) {
            $qb->where(
                $qb->expr()->eq('self.brand', $brandId)
            );
        }

        $result = $qb
            ->select('self.id, self.name')
            ->getQuery()
            ->getScalarResult();

        $response = [];
        foreach ($result as $row) {
            $response[$row['id']] = $row['name'];
        }

        return $response;
    }
}
