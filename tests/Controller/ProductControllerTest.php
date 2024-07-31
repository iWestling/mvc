<?php

namespace App\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use App\Controller\ProductController;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\ORM\EntityManagerInterface;
use App\Repository\ProductRepository;
use App\Entity\Product;
use Symfony\Component\HttpFoundation\Response;
use PHPUnit\Framework\MockObject\MockObject;
use Psr\Container\ContainerInterface;

class ProductControllerTest extends WebTestCase
{
    /** @var MockObject&ManagerRegistry */
    private $managerRegistry;

    /** @var MockObject&ProductRepository */
    private $productRepository;

    /** @var MockObject&EntityManagerInterface */
    private $entityManager;

    /** @var MockObject&ContainerInterface */
    private $container;

    protected function setUp(): void
    {
        $this->managerRegistry = $this->createMock(ManagerRegistry::class);
        $this->productRepository = $this->createMock(ProductRepository::class);
        $this->entityManager = $this->createMock(EntityManagerInterface::class);
        $this->container = $this->createMock(ContainerInterface::class);
    }

    public function testCreateProduct(): void
    {
        $product = new Product();
        $product->setName('Keyboard_num_1')->setValue(150);

        $this->entityManager->expects($this->once())
            ->method('persist')
            ->with($this->isInstanceOf(Product::class));
        $this->entityManager->expects($this->once())
            ->method('flush');

        $this->managerRegistry->method('getManager')->willReturn($this->entityManager);

        $controller = new ProductController();
        $controller->setContainer($this->container);
        $response = $controller->createProduct($this->managerRegistry);

        $this->assertInstanceOf(Response::class, $response);
        $content = $response->getContent();
        $this->assertIsString($content);
        $this->assertStringContainsString('Saved new product with id', $content);
    }

    public function testShowAllProducts(): void
    {
        $product = new Product();
        $product->setName('Test Product')->setValue(123);

        $this->productRepository->method('findAll')->willReturn([$product]);

        $controller = new ProductController();
        $controller->setContainer($this->container);
        $response = $controller->showAllProduct($this->productRepository);

        $this->assertInstanceOf(Response::class, $response);
        $content = $response->getContent();
        $this->assertIsString($content);
        $this->assertJson($content);
    }

    public function testShowProductById(): void
    {
        $product = new Product();
        $product->setName('Test Product')->setValue(123);

        $this->productRepository->method('find')->with(1)->willReturn($product);

        $controller = new ProductController();
        $controller->setContainer($this->container);
        $response = $controller->showProductById($this->productRepository, 1);

        $this->assertInstanceOf(Response::class, $response);
        $content = $response->getContent();
        $this->assertIsString($content);
        $this->assertJson($content);
    }

}
