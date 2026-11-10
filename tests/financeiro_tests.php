<?php

use PHPUnit\Framework\TestCase;
use Slim\Psr7\Factory\ServerRequestFactory;

class financeiro_tests extends TestCase
{
    public function testPostCriaProduto(): void
    {
        $app = require __DIR__ . '/../src/app.php'; // retorna $app sem chamar ->run()
        $request = (new ServerRequestFactory())->createServerRequest('POST', '/produtos')->withHeader('Content-Type', 'application/json');
        $request->getBody()->write(json_encode(['nome' => 'Mouse']));
        $request->getBody()->rewind();
        $response = $app->handle($request);
        $this->assertEquals(201, $response->getStatusCode());
        $corpo = json_decode((string) $response->getBody(), true);
        $this->assertEquals('Mouse', $corpo['nome']);
    }
     
    public function testPostCriaProdutoComErro(): void
    {
        $app = require __DIR__ . '/../src/app.php';
        $request = (new ServerRequestFactory())->createServerRequest('POST', '/produtos')->withHeader('Content-Type', 'application/json');
        $request->getBody()->write(json_encode([]));
        $request->getBody()->rewind();
        $response = $app->handle($request);
        $this->assertEquals(400, $response->getStatusCode());
        $corpo = json_decode((string) $response->getBody(), true);
        $this->assertEquals('O nome é obrigatório', $corpo['erro']);
    }



    public function testGetProdutoPorId(): void
    {
        $app = require __DIR__ . '/../src/app.php';
        $request = (new ServerRequestFactory())->createServerRequest('GET', '/produtos/1')->withHeader('Content-Type', 'application/json');
        $response = $app->handle($request);
        $this->assertEquals(200, $response->getStatusCode());
        // $corpo = json_decode((string) $response->getBody(), true);
        // $this->assertEquals('O nome é obrigatório', $corpo['erro']);
    }

    public function testGetProdutoPorIdInexistente(): void
    {
        $app = require __DIR__ . '/../src/app.php';
        $request = (new ServerRequestFactory())->createServerRequest('GET', '/produtos/999')->withHeader('Content-Type', 'application/json');
        $response = $app->handle($request);
        $this->assertEquals(404, $response->getStatusCode());
        $corpo = json_decode((string) $response->getBody(), true);
        $this->assertEquals('Produto não encontrado', $corpo['erro']);
    }



    public function testDeleteProdutoPorId(): void
    {
        $app = require __DIR__ . '/../src/app.php';
        $request = (new ServerRequestFactory())->createServerRequest('DELETE', '/produtos/1')->withHeader('Content-Type', 'application/json');
        $response = $app->handle($request);
        $this->assertEquals(200, $response->getStatusCode());
        $corpo = json_decode((string) $response->getBody(), true);
        $this->assertEquals('Produto removido com sucesso', $corpo['mensagem']);
    }

    public function testDeleteProdutoPorIdInexistente(): void
    {
        $app = require __DIR__ . '/../src/app.php';
        $request = (new ServerRequestFactory())->createServerRequest('DELETE', '/produtos/999')->withHeader('Content-Type', 'application/json');
        $response = $app->handle($request);
        $this->assertEquals(404, $response->getStatusCode());
        $corpo = json_decode((string) $response->getBody(), true);
        $this->assertEquals('Produto não encontrado', $corpo['erro']);
    }



    public function testPutProdutoPorId(): void
    {
        $app = require __DIR__ . '/../src/app.php';
        $request = (new ServerRequestFactory())->createServerRequest('PUT', '/produtos/1')->withHeader('Content-Type', 'application/json');
        $request->getBody()->write(json_encode(['nome' => 'Teclado atualizado']));
        $request->getBody()->rewind();
        $response = $app->handle($request);
        $this->assertEquals(200, $response->getStatusCode());
        $corpo = json_decode((string) $response->getBody(), true);
        $this->assertEquals('Produto atualizado com sucesso', $corpo['mensagem']);
        $requestAtualizado = (new ServerRequestFactory())->createServerRequest('GET', '/produtos/1');
        $responseAtualizado = $app->handle($requestAtualizado);
        $produtoAtualizado = json_decode((string) $responseAtualizado->getBody(), true);
        $this->assertEquals('Teclado atualizado', $produtoAtualizado['nome']);
    }

    public function testputProdutoPorIdinexistente(): void
    {
        $app = require __DIR__ . '/../src/app.php'; // retorna $app sem chamar ->run()
        $request = (new ServerRequestFactory())->createServerRequest('PUT', '/produtos/999')->withHeader('Content-Type', 'application/json');
        $request->getBody()->write(json_encode(['nome' => 'Teclado atualizado']));
        $request->getBody()->rewind();
        $response = $app->handle($request);
        $this->assertEquals(404, $response->getStatusCode());
        $corpo = json_decode((string) $response->getBody(), true);
        $this->assertEquals('Produto não encontrado', $corpo['erro']);
    }
}
