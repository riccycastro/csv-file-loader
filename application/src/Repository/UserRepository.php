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
     * @throws DbalDriverException
     * @throws  DbalException
     */
    public function insertBulk(array $userFileLoadedDtoList)
    {

        $query = "INSERT INTO `user`(`email`, `lastname`, `firstname`, `fiscal_code`, `description`, `last_access_at`) VALUES ('cruzcastro07@gmail.com') as new on duplicate key
update firstname = new.firstname";

        $em = $this->getEntityManager();
        $stmt = $em->getConnection()->prepare($query);
        $stmt->executeStatement();
    }
}
