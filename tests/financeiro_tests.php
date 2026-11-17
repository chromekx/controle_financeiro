
<?php

use PHPUnit\Framework\TestCase;
use Slim\Psr7\Factory\ServerRequestFactory;

class financeiro_tests extends TestCase
{
    // Testa se uma categoria é cadastrada corretamente (POST)
    public function test_post_criaCategoria(): void
    {
        // Carrega a aplicação Slim sem iniciar o servidor
        $app = require __DIR__ . '/../src/app.php';

        // Cria uma requisição POST para cadastrar uma categoria
        $request = (new ServerRequestFactory())
            ->createServerRequest('POST', '/categorias')
            ->withHeader('Content-Type', 'application/json');

        // Envia os dados da nova categoria em formato JSON
        $request->getBody()->write(json_encode([
            'nome' => 'Transporte',
            'tipo' => 'Despesa'
        ]));

        // Retorna o cursor do corpo da requisição para o início
        $request->getBody()->rewind();

        // Executa a requisição na aplicação
        $response = $app->handle($request);

        // Verifica se o cadastro retornou o status 201 (criado)
        $this->assertEquals(201, $response->getStatusCode());

        // Converte a resposta JSON em um array PHP
        $corpo = json_decode((string) $response->getBody(), true);

        // Verifica se os dados retornados são os esperados
        $this->assertEquals(13, $corpo['id_categoria']);
        $this->assertEquals('Transporte', $corpo['nome']);
        $this->assertEquals('Despesa', $corpo['tipo']);
    }


    public function test_post_criaCategoriaComErro(): void
    {
        $app = require __DIR__ . '/../src/app.php';

        // Cria uma requisição POST sem enviar nenhum campo
        $request = (new ServerRequestFactory())
            ->createServerRequest('POST', '/categorias')
            ->withHeader('Content-Type', 'application/json');

        $request->getBody()->write(json_encode([]));
        $request->getBody()->rewind();

        $response = $app->handle($request);

        // Verifica se a API retornou 400 (requisição inválida)
        $this->assertEquals(400, $response->getStatusCode());

        // Converte a resposta para verificar a mensagem de erro
        $corpo = json_decode((string) $response->getBody(), true);

        $this->assertEquals(
            'Todos os campos precisam estar preenchidos.',
            $corpo['erro']
        );
    }



    public function test_get_CategoriaPorId(): void
    {
        $app = require __DIR__ . '/../src/app.php';

        // Cria uma requisição GET para buscar a categoria de ID 1
        $request = (new ServerRequestFactory())
            ->createServerRequest('GET', '/categorias/1');

        $response = $app->handle($request);

        // Verifica se a busca foi realizada com sucesso
        $this->assertEquals(200, $response->getStatusCode());

        // Converte a resposta JSON em um array PHP
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

        // Verifica se a API retornou 404 (não encontrado)
        $this->assertEquals(404, $response->getStatusCode());

        // Verifica se a mensagem de erro está correta
        $corpo = json_decode((string) $response->getBody(), true);

        $this->assertEquals(
            'Categoria não encontrada.',
            $corpo['erro']
        );
    }



    public function test_delete_CategoriaPorId(): void
    {
        $app = require __DIR__ . '/../src/app.php';

        // Cria uma requisição DELETE para excluir a categoria de ID 1
        $request = (new ServerRequestFactory())
            ->createServerRequest('DELETE', '/categorias/1');

        $response = $app->handle($request);

        // Verifica se a exclusão retornou 204 (sem conteúdo)
        $this->assertEquals(204, $response->getStatusCode());

        // Realiza outra requisição GET para verificar se a categoria foi excluída
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

        // Verifica se a API retornou 404 (não encontrado)
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

        // Cria uma requisição PUT para atualizar a categoria de ID 1
        $request = (new ServerRequestFactory())
            ->createServerRequest('PUT', '/categorias/1')
            ->withHeader('Content-Type', 'application/json');

        // Envia os novos dados da categoria em formato JSON
        $request->getBody()->write(json_encode([
            'nome' => 'Mercado atualizado',
            'tipo' => 'Despesa'
        ]));

        $request->getBody()->rewind();

        $response = $app->handle($request);

        // Verifica se a atualização foi realizada com sucesso
        $this->assertEquals(200, $response->getStatusCode());

        // Converte a resposta para um array PHP
        $corpo = json_decode((string) $response->getBody(), true);

        // Verifica se os dados retornados correspondem aos novos valores
        $this->assertEquals('Mercado atualizado', $corpo['nome']);
        $this->assertEquals('Despesa', $corpo['tipo']);

        // Faz uma nova requisição GET para conferir se a atualização foi salva
        $requestAtualizado = (new ServerRequestFactory())
            ->createServerRequest('GET', '/categorias/1');

        $responseAtualizado = $app->handle($requestAtualizado);

        // Converte a resposta da nova consulta em um array PHP
        $categoriaAtualizada = json_decode(
            (string) $responseAtualizado->getBody(),
            true
        );

        // Confirma se o nome atualizado foi retornado
        $this->assertEquals(
            'Mercado atualizado',
            $categoriaAtualizada['nome']
        );
    }


    public function test_put_CategoriaPorIdInexistente(): void
    {
        $app = require __DIR__ . '/../src/app.php';

        // Cria uma requisição PUT para um ID que não existe
        $request = (new ServerRequestFactory())
            ->createServerRequest('PUT', '/categorias/999')
            ->withHeader('Content-Type', 'application/json');

        // Envia os dados que seriam utilizados na atualização
        $request->getBody()->write(json_encode([
            'nome' => 'Categoria atualizada',
            'tipo' => 'Despesa'
        ]));

        $request->getBody()->rewind();

        $response = $app->handle($request);

        // Verifica se a API retornou 404 (não encontrado)
        $this->assertEquals(404, $response->getStatusCode());

        // Verifica se a mensagem de erro está correta
        $corpo = json_decode((string) $response->getBody(), true);

        $this->assertEquals(
            'Categoria não encontrada.',
            $corpo['erro']
        );
    }
}