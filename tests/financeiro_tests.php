
<?php

use PHPUnit\Framework\TestCase;
use Slim\Psr7\Factory\ServerRequestFactory;

class financeiro_tests extends TestCase
{
    // testa se uma categoria foi cadastrada corretamente
    public function test_post_criaCategoria(): void
    {
        // carrega a aplicacao slim sem iniciar o servidor
        $app = require __DIR__ . '/../src/app.php';

        // cria uma requisiçao post para cadastrar uma categoria
        $request = (new ServerRequestFactory())
            ->createServerRequest('POST', '/categorias')
            ->withHeader('Content-Type', 'application/json');

        // Envia os dados da nova categoria em formato json
        $request->getBody()->write(json_encode([
            'nome' => 'Transporte',
            'tipo' => 'Despesa'
        ]));

        // retorna o cursor do corpo da requisicao para o inicio
        $request->getBody()->rewind();

        // executa a requisicao na aplicacao
        $response = $app->handle($request);

        // verifica se o cadastro retornou o status 201 (criado)
        $this->assertEquals(201, $response->getStatusCode());

        // converte a resposta json em um array php
        $corpo = json_decode((string) $response->getBody(), true);

        // verifica se os dados retornados sao os esperados
        $this->assertEquals(13, $corpo['id_categoria']);
        $this->assertEquals('Transporte', $corpo['nome']);
        $this->assertEquals('Despesa', $corpo['tipo']);
    }


    public function test_post_criaCategoriaComErro(): void
    {
        $app = require __DIR__ . '/../src/app.php';

        // cria uma requisicao post sem enviar nenhum campo
        $request = (new ServerRequestFactory())
            ->createServerRequest('POST', '/categorias')
            ->withHeader('Content-Type', 'application/json');

        $request->getBody()->write(json_encode([]));
        $request->getBody()->rewind();

        $response = $app->handle($request);

        // verifica se a api retornou 400 (requisicao invalida)
        $this->assertEquals(400, $response->getStatusCode());

        // converte a resposta para verificar a mensagem de erro
        $corpo = json_decode((string) $response->getBody(), true);

        $this->assertEquals(
            'Todos os campos precisam estar preenchidos.',
            $corpo['erro']
        );
    }



    public function test_get_CategoriaPorId(): void
    {
        $app = require __DIR__ . '/../src/app.php';

        // cria uma requisicao get para buscar a categoria de id 1
        $request = (new ServerRequestFactory())
            ->createServerRequest('GET', '/categorias/1');

        $response = $app->handle($request);

        // verifica se a busca foi realizada com sucesso
        $this->assertEquals(200, $response->getStatusCode());

        // converte a resposta json em um array PHP
        $corpo = json_decode((string) $response->getBody(), true);

        // Verifica se os dados da categoria estão corretos
        $this->assertEquals(1, $corpo['id_categoria']);
        $this->assertEquals('Mercado', $corpo['nome']);
    }


    public function test_get_CategoriaPorIdInexistente(): void
    {
        $app = require __DIR__ . '/../src/app.php';

        // Busca uma categoria utilizando um ID que não existe
        $request = (new ServerRequestFactory())
            ->createServerRequest('GET', '/categorias/999');

        $response = $app->handle($request);

        // Verifica se a api retornou 404 (não encontrado)
        $this->assertEquals(404, $response->getStatusCode());

        // Verifica se a mensagem de erro ta correta
        $corpo = json_decode((string) $response->getBody(), true);

        $this->assertEquals(
            'Categoria não encontrada.',
            $corpo['erro']
        );
    }



    public function test_delete_CategoriaPorId(): void
    {
        $app = require __DIR__ . '/../src/app.php';

        // Cria uma requisicao DELETE para excluir a categoria de ID 1
        $request = (new ServerRequestFactory())
            ->createServerRequest('DELETE', '/categorias/1');

        $response = $app->handle($request);

        // Verifica se a exclusão retornou 204 (sem conteúdo)
        $this->assertEquals(204, $response->getStatusCode());

        // Realiza outra requisicao GET para verificar se a categoria foi excluída
        $requestVerificacao = (new ServerRequestFactory())
            ->createServerRequest('GET', '/categorias/1');

        $responseVerificacao = $app->handle($requestVerificacao);

        // Verifica se a categoria não foi mais encontrada
        $this->assertEquals(404, $responseVerificacao->getStatusCode());
    }


    public function test_delete_CategoriaPorIdInexistente(): void
    {
        $app = require __DIR__ . '/../src/app.php';

        // Tenta excluir uma categoria utilizando um ID inexistente
        $request = (new ServerRequestFactory())
            ->createServerRequest('DELETE', '/categorias/999');

        $response = $app->handle($request);

        // Verifica se a api retornou 404 (não encontrado)
        $this->assertEquals(404, $response->getStatusCode());

        // Verifica a mensagem de erro retornada
        $corpo = json_decode((string) $response->getBody(), true);

        $this->assertEquals(
            'Categoria não encontrada.',
            $corpo['erro']
        );
    }



    public function test_put_CategoriaPorId(): void
    {
        $app = require __DIR__ . '/../src/app.php';

        // cria uma requisiçao put para atualizar a categoria de id 1
        $request = (new ServerRequestFactory())
            ->createServerRequest('PUT', '/categorias/1')
            ->withHeader('Content-Type', 'application/json');

        // envia os novos dados da categoria em formato json
        $request->getBody()->write(json_encode([
            'nome' => 'Mercado atualizado',
            'tipo' => 'Despesa'
        ]));

        $request->getBody()->rewind();

        $response = $app->handle($request);

        // verifica se a atualização foi realizada com sucesso
        $this->assertEquals(200, $response->getStatusCode());

        // converte a resposta para um array php
        $corpo = json_decode((string) $response->getBody(), true);

        // verifica se os dados retornados correspondem aos novos valores
        $this->assertEquals('Mercado atualizado', $corpo['nome']);
        $this->assertEquals('Despesa', $corpo['tipo']);

        // Faz uma nova requisicao get para conferir se a atualizaçao foi salva
        $requestAtualizado = (new ServerRequestFactory())
            ->createServerRequest('GET', '/categorias/1');

        $responseAtualizado = $app->handle($requestAtualizado);

        // converte a resposta da nova consulta em um array php
        $categoriaAtualizada = json_decode(
            (string) $responseAtualizado->getBody(),
            true
        );

        // confirma se o nome atualizado foi retornado
        $this->assertEquals(
            'Mercado atualizado',
            $categoriaAtualizada['nome']
        );
    }


    public function test_put_CategoriaPorIdInexistente(): void
    {
        $app = require __DIR__ . '/../src/app.php';

        // cria uma requisicao put para um id que nao existe
        $request = (new ServerRequestFactory())
            ->createServerRequest('PUT', '/categorias/999')
            ->withHeader('Content-Type', 'application/json');

        // envia os dados que seriam utilizados na atualizaçao
        $request->getBody()->write(json_encode([
            'nome' => 'Categoria atualizada',
            'tipo' => 'Despesa'
        ]));

        $request->getBody()->rewind();

        $response = $app->handle($request);

        // verifica se a api retornou 404 (nao encontrado)
        $this->assertEquals(404, $response->getStatusCode());

        // verifica se a mensagem de erro ta correta
        $corpo = json_decode((string) $response->getBody(), true);

        $this->assertEquals(
            'Categoria não encontrada.',
            $corpo['erro']
        );
    }
}