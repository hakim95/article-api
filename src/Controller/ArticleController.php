<?php

namespace App\Controller;

use App\Enum\Format;
use App\Service\Article;
use JMS\Serializer\SerializerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class ArticleController extends AbstractController
{
    private Article $articleService;
    private SerializerInterface $serializer;

    private function setResponse(string $serializedData, string $responseFormat, string $responseCode): Response
    {
        $response = new Response($serializedData);
        switch ($responseFormat) {
            case Format::Xml->value:
                $response->headers->set('Content-Type', 'application/xml');
                break;
            default:
                $response->headers->set('Content-Type', 'application/json');
                break;
        }
        $response->setStatusCode($responseCode);

        return $response;
    }

    public function __construct(Article $articleService, SerializerInterface $serializer)
    {
        $this->articleService = $articleService;
        $this->serializer = $serializer;
    }

    #[Route(
        '/api/article/new',
        name: 'app_article_new',
        methods: ['POST']
    )]
    public function new(Request $request): Response
    {
        $format = $request->query->get('_format');

        $postData = $request->request->all();
        $postData['author'] = $this->getUser();

        $responseData = $this->articleService->add($postData);

        $serializedData = $this->serializer->serialize($responseData, $format);

        $response = $this->setResponse($serializedData, $format, $responseData['code']);

        return $response;
    }

    #[Route(
        '/api/article/{id}/archive',
        name: 'app_article_archive',
        methods: ['PUT']
    )]
    public function archive(Request $request, int $id): Response
    {
        $format = $request->query->get('_format');

        $responseData = $this->articleService->archive($id);

        $serializedData = $this->serializer->serialize($responseData, $format);

        $response = $this->setResponse($serializedData, $format, $responseData['code']);
        
        return $response;
    }

    #[Route(
        '/api/articles/{page?}',
        name: 'app_articles',
        methods: ['GET'],
        priority: 1
    )]
    public function articles(Request $request, ?int $page): Response
    {
        $format = $request->query->get('_format');

        $articles = $this->articleService->articles($page);

        $serializedData = $this->serializer->serialize($articles, $format);

        $response = $this->setResponse($serializedData, $format, $articles['code']);
        
        return $response;
    }

    #[Route(
        '/api/articles/{status}/{page?}',
        name: 'app_articles_status',
        requirements: ['status' => '[a-z]+'],
        condition: "params['status'] contains 'draft' || 'published' || 'archived'",
        methods: ['GET'],
        priority: 2
    )]
    public function articlesByStatus(Request $request, string $status, ?int $page): Response
    {
        $format = $request->query->get('_format');

        $articles = $this->articleService->articlesByStatus($status, $page);

        $serializedData = $this->serializer->serialize($articles, $format);

        $response = $this->setResponse($serializedData, $format, $articles['code']);
        
        return $response;
    }
}
