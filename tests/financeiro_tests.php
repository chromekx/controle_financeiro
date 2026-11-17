
<?php

use PHPUnit\Framework\TestCase;
use Slim\Psr7\Factory\ServerRequestFactory;

class financeiro_tests extends TestCase
{
    public function test_post_criaCategoria(): void
    {
        $app = require __DIR__ . '/../src/app.php'; // carrega a aplicacao slim
        $request = (new ServerRequestFactory())
            ->createServerRequest('POST', '/categorias')
            ->withHeader('Content-Type', 'application/json');
        $request->getBody()->write(json_encode([ // envia os dados da nova categoria
            'nome' => 'Transporte',
            'tipo' => 'Despesa'
        ]));
        $request->getBody()->rewind();
        $response = $app->handle($request);
        $this->assertEquals(201, $response->getStatusCode()); // verifica se a categoria foi criada
        $corpo = json_decode((string) $response->getBody(), true);
        $this->assertEquals(13, $corpo['id_categoria']);
        $this->assertEquals('Transporte', $corpo['nome']);
        $this->assertEquals('Despesa', $corpo['tipo']);
    }

    public function test_post_criaCategoriaComErro(): void
    {
        $app = require __DIR__ . '/../src/app.php'; // carrega a aplicacao para testar um cadastro invalido
        $request = (new ServerRequestFactory())
            ->createServerRequest('POST', '/categorias')
            ->withHeader('Content-Type', 'application/json');
        $request->getBody()->write(json_encode([]));
        $request->getBody()->rewind();
        $response = $app->handle($request);
        $this->assertEquals(400, $response->getStatusCode()); // verifica se a api retornou erro
        $corpo = json_decode((string) $response->getBody(), true);
        $this->assertEquals('Todos os campos precisam estar preenchidos.', $corpo['erro']);
    }

    public function test_get_CategoriaPorId(): void
    {
        $app = require __DIR__ . '/../src/app.php'; // busca uma categoria pelo id
        $request = (new ServerRequestFactory())
            ->createServerRequest('GET', '/categorias/1');
        $response = $app->handle($request);
        $this->assertEquals(200, $response->getStatusCode()); // verifica se a busca foi realizada
        $corpo = json_decode((string) $response->getBody(), true);
        $this->assertEquals(1, $corpo['id_categoria']);
        $this->assertEquals('Mercado', $corpo['nome']);
    }

    public function test_get_CategoriaPorIdInexistente(): void
    {
        $app = require __DIR__ . '/../src/app.php'; // busca uma categoria com id inexistente
        $request = (new ServerRequestFactory())
            ->createServerRequest('GET', '/categorias/999');
        $response = $app->handle($request);
        $this->assertEquals(404, $response->getStatusCode()); // verifica se a api retornou nao encontrado
        $corpo = json_decode((string) $response->getBody(), true);
        $this->assertEquals('Categoria não encontrada.', $corpo['erro']);
    }

    public function test_delete_CategoriaPorId(): void
    {
        $app = require __DIR__ . '/../src/app.php'; // exclui a categoria de id 1
        $request = (new ServerRequestFactory())
            ->createServerRequest('DELETE', '/categorias/1');
        $response = $app->handle($request);
        $this->assertEquals(204, $response->getStatusCode()); // verifica se a exclusao foi realizada
        $requestVerificacao = (new ServerRequestFactory()) // busca novamente a categoria para confirmar a exclusao
            ->createServerRequest('GET', '/categorias/1');
        $responseVerificacao = $app->handle($requestVerificacao);
        $this->assertEquals(404, $responseVerificacao->getStatusCode());
    }

    public function test_delete_CategoriaPorIdInexistente(): void
    {
        $app = require __DIR__ . '/../src/app.php'; // tenta excluir uma categoria com id inexistente
        $request = (new ServerRequestFactory())
            ->createServerRequest('DELETE', '/categorias/999');
        $response = $app->handle($request);
        $this->assertEquals(404, $response->getStatusCode()); // verifica se a api retornou nao encontrado
        $corpo = json_decode((string) $response->getBody(), true);
        $this->assertEquals('Categoria não encontrada.', $corpo['erro']);
    }

    public function test_put_CategoriaPorId(): void
    {
        $app = require __DIR__ . '/../src/app.php'; // atualiza os dados da categoria de id 1
        $request = (new ServerRequestFactory())
            ->createServerRequest('PUT', '/categorias/1')
            ->withHeader('Content-Type', 'application/json');
        $request->getBody()->write(json_encode([ // envia os novos dados da categoria
            'nome' => 'Mercado atualizado',
            'tipo' => 'Despesa'
        ]));
        $request->getBody()->rewind();
        $response = $app->handle($request);
        $this->assertEquals(200, $response->getStatusCode()); // verifica se a atualizacao foi realizada
        $corpo = json_decode((string) $response->getBody(), true);
        $this->assertEquals('Mercado atualizado', $corpo['nome']);
        $this->assertEquals('Despesa', $corpo['tipo']);
        $requestAtualizado = (new ServerRequestFactory()) // consulta novamente a categoria atualizada
            ->createServerRequest('GET', '/categorias/1');
        $responseAtualizado = $app->handle($requestAtualizado);
        $categoriaAtualizada = json_decode((string) $responseAtualizado->getBody(), true);
        $this->assertEquals('Mercado atualizado', $categoriaAtualizada['nome']);
    }

    public function test_put_CategoriaPorIdInexistente(): void
    {
        $app = require __DIR__ . '/../src/app.php'; // tenta atualizar uma categoria com id inexistente
        $request = (new ServerRequestFactory())
            ->createServerRequest('PUT', '/categorias/999')
            ->withHeader('Content-Type', 'application/json');
        $request->getBody()->write(json_encode([
            'nome' => 'Categoria atualizada',
            'tipo' => 'Despesa'
        ]));
        $request->getBody()->rewind();
        $response = $app->handle($request);
        $this->assertEquals(404, $response->getStatusCode()); // verifica se a api retornou nao encontrado
        $corpo = json_decode((string) $response->getBody(), true);
        $this->assertEquals('Categoria não encontrada.', $corpo['erro']);
    }

    public function test_get_Lancamentos(): void
    {
        $app = require __DIR__ . '/../src/app.php'; // busca todos os lancamentos cadastrados
        $request = (new ServerRequestFactory())
            ->createServerRequest('GET', '/lancamentos');
        $response = $app->handle($request);
        $this->assertEquals(200, $response->getStatusCode()); // verifica se a consulta foi realizada
        $corpo = json_decode((string) $response->getBody(), true);
        $this->assertIsArray($corpo);
    }

    public function test_get_LancamentoPorId(): void
    {
        $app = require __DIR__ . '/../src/app.php'; // busca um lancamento pelo id
        $request = (new ServerRequestFactory())
            ->createServerRequest('GET', '/lancamentos/1');
        $response = $app->handle($request);
        $this->assertEquals(200, $response->getStatusCode()); // verifica se o lancamento foi encontrado
        $corpo = json_decode((string) $response->getBody(), true);
        $this->assertEquals(1, $corpo['id_lancamento']);
    }

    public function test_get_LancamentoPorIdInexistente(): void
    {
        $app = require __DIR__ . '/../src/app.php'; // busca um lancamento com id inexistente
        $request = (new ServerRequestFactory())
            ->createServerRequest('GET', '/lancamentos/999');
        $response = $app->handle($request);
        $this->assertEquals(404, $response->getStatusCode()); // verifica se a api retornou nao encontrado
    }

    public function test_get_TotalLancamentos(): void
    {
        $app = require __DIR__ . '/../src/app.php'; // consulta os totais de receitas, despesas e saldo
        $request = (new ServerRequestFactory())
            ->createServerRequest('GET', '/lancamentos/total');
        $response = $app->handle($request);
        $this->assertEquals(200, $response->getStatusCode()); // verifica se a consulta foi realizada
        $corpo = json_decode((string) $response->getBody(), true);
        $this->assertArrayHasKey('total_receitas', $corpo);
        $this->assertArrayHasKey('total_despesas', $corpo);
        $this->assertArrayHasKey('saldo', $corpo);
    }

    public function test_post_criaLancamento(): void
    {
        $app = require __DIR__ . '/../src/app.php'; // cadastra um novo lancamento
        $request = (new ServerRequestFactory())
            ->createServerRequest('POST', '/lancamentos')
            ->withHeader('Content-Type', 'application/json');
        $request->getBody()->write(json_encode([ // envia os dados do lancamento
            'descricao' => 'Passagem de ônibus',
            'valor' => 5.50,
            'data' => '2026-11-17',
            'tipo' => 'Despesa',
            'id_categoria' => 1
        ]));
        $request->getBody()->rewind();
        $response = $app->handle($request);
        $this->assertEquals(201, $response->getStatusCode()); // verifica se o lancamento foi criado
        $corpo = json_decode((string) $response->getBody(), true);
        $this->assertEquals(8, $corpo['id_lancamento']);
        $this->assertEquals('Passagem de ônibus', $corpo['descricao']);
    }

    public function test_post_criaLancamentoComErro(): void
    {
        $app = require __DIR__ . '/../src/app.php'; // tenta cadastrar um lancamento sem preencher os campos
        $request = (new ServerRequestFactory())
            ->createServerRequest('POST', '/lancamentos')
            ->withHeader('Content-Type', 'application/json');
        $request->getBody()->write(json_encode([]));
        $request->getBody()->rewind();
        $response = $app->handle($request);
        $this->assertEquals(400, $response->getStatusCode()); // verifica se a api retornou erro
    }

    public function test_put_LancamentoPorId(): void
    {
        $app = require __DIR__ . '/../src/app.php'; // atualiza os dados do lancamento de id 1
        $request = (new ServerRequestFactory())
            ->createServerRequest('PUT', '/lancamentos/1')
            ->withHeader('Content-Type', 'application/json');
        $request->getBody()->write(json_encode([ // envia os novos dados do lancamento
            'descricao' => 'Assinatura atualizada',
            'valor' => 39.90,
            'data' => '2026-11-17',
            'tipo' => 'Despesa',
            'id_categoria' => 1
        ]));
        $request->getBody()->rewind();
        $response = $app->handle($request);
        $this->assertEquals(200, $response->getStatusCode()); // verifica se a atualizacao foi realizada
        $corpo = json_decode((string) $response->getBody(), true);
        $this->assertEquals('Assinatura atualizada', $corpo['descricao']);
    }

    public function test_put_LancamentoPorIdInexistente(): void
    {
        $app = require __DIR__ . '/../src/app.php'; // tenta atualizar um lancamento com id inexistente
        $request = (new ServerRequestFactory())
            ->createServerRequest('PUT', '/lancamentos/999')
            ->withHeader('Content-Type', 'application/json');
        $request->getBody()->write(json_encode([
            'descricao' => 'Lancamento atualizado',
            'valor' => 20.00,
            'data' => '2026-11-17',
            'tipo' => 'Despesa',
            'id_categoria' => 1
        ]));
        $request->getBody()->rewind();
        $response = $app->handle($request);
        $this->assertEquals(404, $response->getStatusCode()); // verifica se a api retornou nao encontrado
    }

    public function test_delete_LancamentoPorId(): void
    {
        $app = require __DIR__ . '/../src/app.php'; // exclui o lancamento de id 1
        $request = (new ServerRequestFactory())
            ->createServerRequest('DELETE', '/lancamentos/1');
        $response = $app->handle($request);
        $this->assertEquals(204, $response->getStatusCode()); // verifica se a exclusao foi realizada
        $requestVerificacao = (new ServerRequestFactory()) // consulta novamente o lancamento para confirmar a exclusao
            ->createServerRequest('GET', '/lancamentos/1');
        $responseVerificacao = $app->handle($requestVerificacao);
        $this->assertEquals(404, $responseVerificacao->getStatusCode());
    }

    public function test_delete_LancamentoPorIdInexistente(): void
    {
        $app = require __DIR__ . '/../src/app.php'; // tenta excluir um lancamento com id inexistente
        $request = (new ServerRequestFactory())
            ->createServerRequest('DELETE', '/lancamentos/999');
        $response = $app->handle($request);
        $this->assertEquals(404, $response->getStatusCode()); // verifica se a api retornou nao encontrado
    }
}