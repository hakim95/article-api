<?php

namespace App\Repository;

use App\Entity\Article;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Article>
 *
 * @method Article|null find($id, $lockMode = null, $lockVersion = null)
 * @method Article|null findOneBy(array $criteria, array $orderBy = null)
 * @method Article[]    findAll()
 * @method Article[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ArticleRepository extends ServiceEntityRepository
{
    const PAGE_SIZE = 10;

    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Article::class);
    }

    public function add(Article $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(Article $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function findArticles(?int $page): array
    {
        $qb = $this->createQueryBuilder('a');
        $qb->select('a.id, a.title, a.content, a.publication_date, a.status, u.username as author')
        ->join('a.author', 'u');

        if (!empty($page)) {
            $firstResult = ($page - 1) * self::PAGE_SIZE;

            $qb->setFirstResult($firstResult)
                ->setMaxResults(self::PAGE_SIZE);

            $paginator = new Paginator($qb, false);

            return $paginator->getQuery()->getResult();
        }

        return $qb->getQuery()->getResult();
    }

    public function findArticlesByStatus(string $status, ?int $page): array
    {
        $qb = $this->createQueryBuilder('a');
        $qb->select('a.id, a.title, a.content, a.publication_date, a.status, u.username as author')
        ->join('a.author', 'u')
        ->andWhere('a.status = :status')
        ->setParameter('status', $status);
        
        if (!empty($page)) {
            $firstResult = ($page - 1) * self::PAGE_SIZE;

            $qb->setFirstResult($firstResult)
                ->setMaxResults(self::PAGE_SIZE);

            $paginator = new Paginator($qb, false);
    
            return $paginator->getQuery()->getResult();
        }

        return $qb->getQuery()->getResult();
    }
}
