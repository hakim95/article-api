<?php

namespace App\DataFixtures;

use App\DataFixtures\UserFixtures;
use App\Entity\Article;
use App\Enum\Status;
use App\Repository\UserRepository;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;

class ArticleFixtures extends Fixture implements DependentFixtureInterface
{
    private UserRepository $userRepository;

    public function __construct(UserRepository $userRepository)
    {
        $this->userRepository = $userRepository;
    }

    public function load(ObjectManager $manager): void
    {
        $user = $this->userRepository->findOneBy(['username' => 'test']);
        $now = new \DateTime();

        for ($i = 0; $i < 20; $i++) {
            $article = new Article();
            $article->setTitle("title$i");
            $article->setContent('test content');
            $article->setAuthor($user);
            $article->setPublicationDate($now);
            $article->setStatus(Status::Published->value);
            $manager->persist($article);
        }

        $manager->flush();
    }

    public function getDependencies()
    {
        return [
            UserFixtures::class,
        ];
    }
}
