<?php

namespace Ivoz\Provider\Domain\Model\Administrator;

use Doctrine\Common\Collections\Criteria;
use Ivoz\Core\Domain\Assert\Assertion;
use Ivoz\Core\Infrastructure\Persistence\Doctrine\Model\Helper\CriteriaHelper;
use Ivoz\Provider\Domain\Model\Brand\BrandInterface;
use Ivoz\Provider\Domain\Model\Company\CompanyInterface;

trait AdministratorSecurityTrait
{
    abstract public function getId();

    /**
     * @return string
     */
    abstract public function getUsername();

    /**
     * @return string
     */
    abstract public function getEmail();

    /**
     * @return string
     */
    abstract public function getPass();

    /**
     * @return boolean
     */
    abstract public function getActive();

    /**
     * @return BrandInterface | null
     */
    abstract public function getBrand();

    /**
     * @return CompanyInterface | null
     */
    abstract public function getCompany();

    /**
     * @return boolean
     */
    abstract public function getRestricted();

    /**
     * @see AdvancedUserInterface::getRoles()
     */
    public function getRoles()
    {
        $brand = $this->getBrand();
        $company = $this->getCompany();

        if (is_null($brand) && is_null($company)) {
            return [
                'ROLE_SUPER_ADMIN'
            ];
        }

        if (!is_null($company)) {
            return [
                'ROLE_COMPANY_ADMIN'
            ];
        }

        return [
            'ROLE_BRAND_ADMIN'
        ];
    }

    public function hasAccessPrivileges(string $fqdn, string $reqMethod): bool
    {
        if (!$this->getRestricted()) {
            return true;
        }

        $accessMethods = [
            'POST' => 'create',
            'GET' => 'read',
            'PUT' => 'update',
            'PATCH' => 'update',
            'DELETE' => 'delete'
        ];

        $reqMethod = strtoupper($reqMethod);

        try {
            Assertion::choice(
                $reqMethod,
                array_keys($accessMethods),
                'Request method "%s" is not an element of the valid values: %s'
            );

            $accessMethod = $accessMethods[$reqMethod];

            $criteria = CriteriaHelper::fromArray([
                [$accessMethod, 'eq', 1],
            ]);

            $relPublicEntities = $this
                ->getRelPublicEntities($criteria);

            Assertion::notEmpty($relPublicEntities);

            foreach ($relPublicEntities as $relPublicEntity) {
                $entityFqdn = $relPublicEntity
                    ->getPublicEntity()
                    ->getFqdn();

                if ($entityFqdn === $fqdn) {
                    return true;
                }
            }
        } catch (\Exception $e) {
            return false;
        }

        return false;
    }

    /**
     * @param Criteria|null $criteria
     * @return \Ivoz\Provider\Domain\Model\AdministratorRelPublicEntity\AdministratorRelPublicEntityInterface[]
     */
    abstract public function getRelPublicEntities(Criteria $criteria = null): array;

    /**
     * @see AdvancedUserInterface::getPassword()
     */
    public function getPassword()
    {
        return $this->getPass();
    }

    /**
     * @see AdvancedUserInterface::isAccountNonExpired()
     */
    public function isAccountNonExpired()
    {
        return true;
    }

    /**
     * @see AdvancedUserInterface::isAccountNonLocked()
     */
    public function isAccountNonLocked()
    {
        return true;
    }

    /**
     * @see AdvancedUserInterface::isCredentialsNonExpired()
     */
    public function isCredentialsNonExpired()
    {
        return true;
    }

    /**
     * @see AdvancedUserInterface::isEnabled()
     */
    public function isEnabled()
    {
        $isInnerGlobalAdmin = ($this->getId() === 0);
        if ($isInnerGlobalAdmin) {
            return true;
        }

        return $this->getActive();
    }

    /**
     * @see AdvancedUserInterface::getSalt()
     */
    public function getSalt()
    {
        return substr($this->getPassword(), 0, 29);
    }

    /**
     * @see AdvancedUserInterface::eraseCredentials()
     */
    public function eraseCredentials()
    {
    }
}
