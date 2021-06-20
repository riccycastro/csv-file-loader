<?php

namespace App\Repository;

use App\Dto\UserFileLoadedDto;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\DBAL\Driver\Exception as DbalDriverException;
use Doctrine\DBAL\Exception as DbalException;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method User|null find($id, $lockMode = null, $lockVersion = null)
 * @method User|null findOneBy(array $criteria, array $orderBy = null)
 * @method User[]    findAll()
 * @method User[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class UserRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, User::class);
    }

    /**
     * @param UserFileLoadedDto[] $userFileLoadedDtoList
     * @return int
     * @throws DbalDriverException
     * @throws DbalException
     */
    public function insertBulk(array $userFileLoadedDtoList): int
    {
        if (!$userFileLoadedDtoList) {
            return 0;
        }

        $values = [];
        $data = [];

        foreach ($userFileLoadedDtoList as $userFileLoadedDto) {
            $values[] = "(?, ?, ?, ?, ?, ?)";
            $data[] = $userFileLoadedDto->email;
            $data[] = $userFileLoadedDto->lastName;
            $data[] = $userFileLoadedDto->firstName;
            $data[] = $userFileLoadedDto->fiscalCode;
            $data[] = $userFileLoadedDto->description;
            $data[] = $userFileLoadedDto->lastAccessDate;
        }

        $query = "INSERT INTO `user`(`email`, `lastname`, `firstname`, `fiscal_code`, `description`, `last_access_at`) VALUES " . implode(', ', $values) . " as userData on duplicate key
update `lastname` = userData.lastname, `firstname` = userData.firstname, `fiscal_code` = userData.fiscal_code, `description` = userData.description, `last_access_at` = userData.last_access_at";

        $em = $this->getEntityManager();
        $stmt = $em->getConnection()->prepare($query);

        return $stmt->executeStatement($data);
    }
}
