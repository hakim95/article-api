<?php

namespace App\Tests\Article;

use App\Entity\User;
use App\Enum\Status;
use App\Repository\ArticleRepository;
use App\Repository\UserRepository;
use App\Service\Article;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class ArticleTest extends KernelTestCase
{
    protected Article $articleService;
    protected UserRepository $userRepository;

    protected function setUp(): void
    {
        self::bootKernel();
        $this->articleService = static::getContainer()->get('app.article');
        $this->userRepository = static::getContainer()->get('app.user.repository');
    }

    /**
     * @dataProvider invalidArticleProvider
     */
    public function testPublicationFailure(array $data, array $expected): void
    {
        $author = $this->userRepository->findOneBy(['username' => 'test']);
        $data['author'] = $author;

        $response = $this->articleService->add($data);

        $this->assertArrayHasKey('code', $response);
        $this->assertEquals($expected['code'], $response['code']);
    }

    public function invalidArticleProvider(): array
    {
        $datetime = new \DateTime();
        $wrongDatetime = '2022-09-22 16:50:23';

        return [
            [
                // Title > 128 characters
                [
                    'title' => 'oazieoazieoiazeoazieoazeoazieoiazooooooooooaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaadkkkkkkkkkkkkkkkkkkkddddddddddddddddddandkeeizo',
                    'content' => 'a simple content',
                    'date_publication' => $datetime,
                    'status' => Status::Published->value
                ],
                [
                    'code' => Response::HTTP_BAD_REQUEST
                ]
            ],
            [
                // Draft date publication <= now
                [
                    'title' => 'test',
                    'content' => 'a simple test',
                    'date_publication' => $wrongDatetime,
                    'status' => Status::Draft->value
                ],
                [
                    'code' => Response::HTTP_BAD_REQUEST
                ]
            ],
            [
                // Empty title and content
                [
                    'title' => '',
                    'content' => '',
                    'date_publication' => $datetime,
                    'status' => Status::Published->value
                ],
                [
                    'code' => Response::HTTP_BAD_REQUEST
                ]
            ]
        ];
    }

    /**
     * @dataProvider validArticleProvider
     */
    public function testPublicationSuccess(array $data, array $expected): void
    {
        $author = $this->userRepository->findOneBy(['username' => 'test']);
        $data['author'] = $author;

        $response = $this->articleService->add($data);

        $this->assertArrayHasKey('code', $response);
        $this->assertEquals($expected['code'], $response['code']);
    }

    public function validArticleProvider(): array
    {
        $datetime = new \DateTime();

        return [
            [
                [
                    'title' => 'test',
                    'content' => 'a valid test content',
                    'date_publication' => $datetime,
                    'status' => Status::Published->value
                ],
                [
                    'code' => Response::HTTP_OK
                ]
            ]
        ];
    }

    /**
     * @dataProvider nonExistentArticleProvider
     */
    public function testArchiveFailure(array $data, array $expected): void
    {
        $response = $this->articleService->archive($data['id']);

        $this->assertSame($expected, $response);
        $this->assertEquals($expected['code'], $response['code']);
    }

    public function nonExistentArticleProvider(): array
    {
        return [
            [
                [
                    'id' => 1000
                ],
                [
                    'code' => Response::HTTP_NOT_FOUND,
                    'message' => 'Article not found'
                ]
            ]
        ];
    }

    /**
     * @dataProvider existentArticleProvider
     */
    public function testArchiveSuccess(array $data, array $expected): void
    {
        $response = $this->articleService->archive($data['id']);

        $this->assertSame($expected, $response);
        $this->assertEquals($expected['code'], $response['code']);
    }

    public function existentArticleProvider(): array
    {
        return [
            [
                [
                    'id' => 8
                ],
                [
                    'code' => Response::HTTP_OK,
                    'message' => 'Article archived'
                ]
            ]
        ];
    }

    /**
     * @dataProvider articlesByStatusProvider
     */
    public function testArticlesByStatus(array $data, array $expected): void
    {
        $response = $this->articleService->articlesByStatus($data['status'], $data['page']);

        $this->assertEquals($expected['code'], $response['code']);
    }

    public function articlesByStatusProvider(): array
    {
        return [
            [
                [
                    'status' => Status::Draft->value,
                    'page' => 1
                ],
                [
                    'code' => Response::HTTP_OK
                ]
            ],
            [
                [
                    'status' => Status::Published->value,
                    'page' => 1
                ],
                [
                    'code' => Response::HTTP_OK
                ]
            ],
            [
                [
                    'status' => Status::Archived->value,
                    'page' => 1
                ],
                [
                    'code' => Response::HTTP_OK
                ]
            ],
            [
                [
                    'status' => 'invalidstatus',
                    'page' => 1
                ],
                [
                    'code' => Response::HTTP_BAD_REQUEST
                ]
            ],
        ];
    }

    /**
     * @dataProvider articlesByStatusEmptyPageProvider
     */
    public function testArticlesByStatusEmptyPage(array $data, array $expected): void
    {
        $response = $this->articleService->articlesByStatus($data['status'], $data['page']);

        $this->assertSame($expected, $response);
        $this->assertEquals($expected['code'], $response['code']);
    }

    public function articlesByStatusEmptyPageProvider(): array
    {
        return [
            [
                [
                    'status' => Status::Draft->value,
                    'page' => 1000
                ],
                [
                    'code' => Response::HTTP_OK,
                    'data' => []
                ]
            ]
        ];
    }


    /**
     * @dataProvider articlesProvider
     */
    public function testArticles(array $data, array $expected): void
    {
        $response = $this->articleService->articles($data['page']);

        $this->assertEquals($expected['code'], $response['code']);
    }

    public function articlesProvider(): array
    {
        return [
            [
                [
                    'page' => 1
                ],
                [
                    'code' => Response::HTTP_OK
                ]
            ]
        ];
    }

    /**
     * @dataProvider articlesEmptyPageProvider
     */
    public function testArticlesEmptyPage(array $data, array $expected): void
    {
        $response = $this->articleService->articles($data['page']);

        $this->assertSame($expected, $response);
        $this->assertEquals($expected['code'], $response['code']);
    }

    public function articlesEmptyPageProvider(): array
    {
        return [
            [
                [
                    'page' => 1000
                ],
                [
                    'code' => Response::HTTP_OK,
                    'data' => []
                ]
            ]
        ];
    }
}
