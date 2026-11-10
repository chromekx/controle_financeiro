<?php

use PHPUnit\Framework\TestCase;
use Slim\Psr7\Factory\ServerRequestFactory;

class financeiro_tests extends TestCase
{
    public function test_post_criaCategoria(): void
    {
        $app = require __DIR__ . '/../src/app.php'; // retorna $app sem chamar ->run()
        $request = (new ServerRequestFactory())->createServerRequest('POST', '/categorias')->withHeader('Content-Type', 'application/json');
        $request->getBody()->write(json_encode(['nome' => $dados['nome'], 'tipo' => $dados['tipo']]));
        $request->getBody()->rewind();
        $response = $app->handle($request);
        $this->assertEquals(201, $response->getStatusCode());
        $corpo = json_decode((string) $response->getBody(), true);
        $this->assertEquals($categorias['id'], $corpo['nome']);
        $this->assertEquals($dados['nome'], $corpo['nome']);
        $this->assertEquals($dados['tipo'], $corpo['tipo']);
    }
     
    public function test_post_criaCategoriaComErro(): void
    {
        $app = require __DIR__ . '/../src/app.php';
        $request = (new ServerRequestFactory())->createServerRequest('POST', '/categorias')->withHeader('Content-Type', 'application/json');
        $request->getBody()->write(json_encode([]));
        $request->getBody()->rewind();
        $response = $app->handle($request);
        $this->assertEquals(400, $response->getStatusCode());
        $corpo = json_decode((string) $response->getBody(), true);
        $this->assertEquals('Todos os campos precisam estar preenchidos.', $corpo['erro']);
    }



    public function test_get_CategoriaPorId(): void
    {
        $app = require __DIR__ . '/../src/app.php';
        $request = (new ServerRequestFactory())->createServerRequest('GET', '/categorias/{id}')->withHeader('Content-Type', 'application/json');
        $response = $app->handle($request);
        $this->assertEquals(200, $response->getStatusCode());
    }

    public function test_get_CategoriaPorIdInexistente(): void
    {
        $app = require __DIR__ . '/../src/app.php';
        $request = (new ServerRequestFactory())->createServerRequest('GET', '/categorias/{id}')->withHeader('Content-Type', 'application/json');
        $response = $app->handle($request);
        $this->assertEquals(404, $response->getStatusCode());
        $corpo = json_decode((string) $response->getBody(), true);
        $this->assertEquals('Categoria não encontrada.', $corpo['erro']);
    }



    public function test_delete_CategoriaPorId(): void
    {
        $app = require __DIR__ . '/../src/app.php';
        $request = (new ServerRequestFactory())->createServerRequest('DELETE', '/categorias/{id}')->withHeader('Content-Type', 'application/json');
        $response = $app->handle($request);
        $this->assertEquals(200, $response->getStatusCode());
        $corpo = json_decode((string) $response->getBody(), true);
        $this->assertEquals('Categoria removida com sucesso.', $corpo['mensagem']);
    }

    public function test_delete_CategoriaPorIdInexistente(): void
    {
        $app = require __DIR__ . '/../src/app.php';
        $request = (new ServerRequestFactory())->createServerRequest('DELETE', '/categorias/{id}')->withHeader('Content-Type', 'application/json');
        $response = $app->handle($request);
        $this->assertEquals(404, $response->getStatusCode());
        $corpo = json_decode((string) $response->getBody(), true);
        $this->assertEquals('Categoria não encontrada.', $corpo['erro']);
    }



    public function test_put_CategoriaPorId(): void
    {
        $app = require __DIR__ . '/../src/app.php';
        $request = (new ServerRequestFactory())->createServerRequest('PUT', '/categorias/{id}')->withHeader('Content-Type', 'application/json');
        $request->getBody()->write(json_encode(['nome' => 'Teclado atualizado']));
        $request->getBody()->rewind();
        $response = $app->handle($request);
        $this->assertEquals(200, $response->getStatusCode());
        $corpo = json_decode((string) $response->getBody(), true);
        $this->assertEquals('categoria atualizado com sucesso', $corpo['mensagem']);
        $requestAtualizado = (new ServerRequestFactory())->createServerRequest('GET', '/categorias/{id}');
        $responseAtualizado = $app->handle($requestAtualizado);
        $categoriaAtualizado = json_decode((string) $responseAtualizado->getBody(), true);
        $this->assertEquals('Teclado atualizado', $categoriaAtualizado['nome']);
    }

    public function test_put_CategoriaPorIdinexistente(): void
    {
        $app = require __DIR__ . '/../src/app.php'; // retorna $app sem chamar ->run()
        $request = (new ServerRequestFactory())->createServerRequest('PUT', '/categorias/{id}')->withHeader('Content-Type', 'application/json');
        $request->getBody()->write(json_encode(['nome' => 'Teclado atualizado']));
        $request->getBody()->rewind();
        $response = $app->handle($request);
        $this->assertEquals(404, $response->getStatusCode());
        $corpo = json_decode((string) $response->getBody(), true);
        $this->assertEquals('categoria não encontrado', $corpo['erro']);
    }
}
