<?php

namespace App\Service;

use App\Entity\Article as ArticleEntity;
use App\Enum\Status;
use App\Repository\ArticleRepository;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class Article
{
    private ArticleRepository $articleRepository;
    private ManagerRegistry $entityManager;
    private ValidatorInterface $validator;

    public function __construct(ArticleRepository $articleRepository, ManagerRegistry $entityManager, ValidatorInterface $validator)
    {
        $this->articleRepository = $articleRepository;
        $this->entityManager = $entityManager;
        $this->validator = $validator;
    }

    public function add(array $data): array
    {
        $dateTime = new \DateTime();
        $title = htmlentities($data['title']);
        $content = htmlentities($data['content']);
        $status = htmlentities($data['status']);

        $article = new ArticleEntity();
        $article->setTitle($title);
        $article->setContent($content);
        $article->setAuthor($data['author']);
        $article->setStatus($status);
        $article->setPublicationDate($dateTime);
        if ($data['status'] === Status::Draft->value) {
            $draftCreatedAt = new \DateTime($data['date_publication']);
            if ($draftCreatedAt <= $dateTime) {
                return [
                    'code' => Response::HTTP_BAD_REQUEST,
                    'message' => 'The date of publication has to be greater than today'
                ];
            }

            $article->setPublicationDate($draftCreatedAt);
        }

        $errors = $this->validator->validate($article);

        if (count($errors) > 0) {
            $errorsMessages = (string) $errors;

            return [
                'code' => Response::HTTP_BAD_REQUEST,
                'message' => 'Article could not be created because of some invalid values : ' . $errorsMessages
            ];
        }

        $this->articleRepository->add($article, true);

        return [
            'code' => Response::HTTP_OK,
            'message' => 'Article created'
        ];
    }

    public function archive(int $id): array
    {
        $article = $this->articleRepository->findOneBy(['id' => $id]);

        if (!$article) {
            return [
                'code' => Response::HTTP_NOT_FOUND,
                'message' => 'Article not found'
            ];
        }

        $article->setStatus(Status::Archived->value);
        $this->entityManager->getManager()->persist($article);
        $this->entityManager->getManager()->flush();

        return [
            'code' => Response::HTTP_OK,
            'message' => 'Article archived'
        ];
    }

    public function articles(?int $page): array
    {
        $articles = $this->articleRepository->findArticles($page);

        return [
            'code' => Response::HTTP_OK,
            'data' => $articles
        ];
    }

    public function articlesByStatus(string $status, ?int $page): array
    {
        $statusList = Status::cases();
        $matchingStatus = array_search($status, array_column($statusList, 'value'));

        if ($matchingStatus === false) {
            return [
                'code' => Response::HTTP_BAD_REQUEST,
                'message' => 'The entered status is not valid. Should be one of these : ' . implode(', ', array_column($statusList, 'value'))
            ];
        }

        $articles = $this->articleRepository->findArticlesByStatus($status, $page);
        
        return [
            'code' => Response::HTTP_OK,
            'data' => $articles
        ];
    }
}