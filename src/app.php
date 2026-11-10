<?php

require __DIR__ . '/../vendor/autoload.php';

use Slim\Factory\AppFactory;

$app = AppFactory::create();
$app->addBodyParsingMiddleware();

$app->get('/status', function ($request, $response) {
    $response->getBody()->write(json_encode(['status' => 'ok']));
    return $response->withHeader('Content-Type', 'application/json')->withStatus(200);
});

$categorias = [
    ['id_categoria' => 1, 'tipo' => 'Despesa', 'nome' => 'Mercado'],
    ['id_categoria' => 2, 'tipo' => 'Despesa', 'nome' => 'Música'],
    ['id_categoria' => 3, 'tipo' => 'Despesa', 'nome' => 'Academia'],
    ['id_categoria' => 4, 'tipo' => 'Despesa', 'nome' => 'Conta de Água'],
    ['id_categoria' => 5, 'tipo' => 'Despesa', 'nome' => 'Conta de Luz'],
    ['id_categoria' => 6, 'tipo' => 'Despesa', 'nome' => 'Aluguel'],
    ['id_categoria' => 7, 'tipo' => 'Despesa', 'nome' => 'Streaming'],
    ['id_categoria' => 8, 'tipo' => 'Receita', 'nome' => 'Salário'],
    ['id_categoria' => 9, 'tipo' => 'Receita', 'nome' => 'Venda'],
    ['id_categoria' => 10, 'tipo' => 'Receita', 'nome' => 'Devolução'],
    ['id_categoria' => 12, 'tipo' => 'Receita', 'nome' => 'Empréstimo']
];

$app->get('/categorias', function ($request, $response, $args) use (&$categorias) {
    if (!$categorias) {
        $response->getBody()->write(json_encode(['erro' => 'Não há categorias cadastradas.']));
        return $response
            ->withHeader('Content-Type', 'application/json')
            ->withStatus(404);
    }

    // Missões encontradas
    $response->getBody()->write(json_encode($categorias));
    return $response
        ->withHeader('Content-Type', 'application/json')
        ->withStatus(200);
});

$app->get('/categorias/{id}', function ($request, $response, $args) use (&$categorias) {
    $id = (int) $args['id'];
    $categoria = current(array_filter($categorias, fn($p) => $p['id'] === $id));

    if (!$categoria) {
        $response->getBody()->write(json_encode(['erro' => 'Categoria não encontrada.']));
        return $response
            ->withHeader('Content-Type', 'application/json')
            ->withStatus(404);
    }

    // missão encontrada
    $response->getBody()->write(json_encode($categoria));
    return $response
        ->withHeader('Content-Type', 'application/json')
        ->withStatus(200);
});

$app->post('/categorias', function ($request, $response) use (&$categorias) {
    // Recebe os dados enviados em JSON
    $dados = $request->getParsedBody();
    // Verifica se o nome foi informado
    if (!isset($dados['nome']) || empty($dados['nome']) || !isset($dados['tipo']) || empty($dados['tipo'])) {
        $response->getBody()->write(json_encode(['erro' => 'Todos os campos precisam estar preenchidos.']));
        return $response
            ->withHeader('Content-Type', 'application/json')
            ->withStatus(400);
    }

    // Cria uma nova categoria
    $novaCategoria = [
        'id' => count($categorias) + 1,
        'nome' => $dados['nome'],
        'tipo' => $dados['tipo']
    ];
    // Adiciona a missão ao array
    $categorias[] = $novaCategoria;
    // Retorna a missão criada
    $response->getBody()->write(
        json_encode($novaCategoria)
    );

    return $response
        ->withHeader('Content-Type', 'application/json')
        ->withStatus(201);
});

$app->put('/categorias/{id}', function ($request, $response, $args) use (&$categorias) {
    $dados = $request->getParsedBody();

    foreach ($categorias as &$categoria) {
        if ($categoria['id'] === (int) $args['id']) {
            if ($dados['nome'] == null || $dados['ano'] == null || $dados['agencia'] == null || $dados['status'] == null) {
                $response
                    ->getBody()
                    ->write(json_encode(['erro' => 'Complete todos os campos.']));
            } else {
                $categoria['nome'] = $dados['nome'];
                $categoria['ano'] = $dados['ano'];
                $categoria['agencia'] = $dados['agencia'];
                $categoria['status'] = $dados['status'];
                $response
                    ->getBody()
                    ->write(json_encode($categoria));
                return $response
                    ->withHeader('Content-Type', 'application/json')
                    ->withStatus(200);
            }
        }
    }
    return $response->withStatus(404);
});

$app->get('/categorias', function ($request, $response, $args) use (&$categorias) {
    $queryParams = $request->getQueryParams();
    $nome = $queryParams['nome'] ?? null;
    if ($nome) {
        $filtrados = array_filter($categorias, fn($item) => str_contains(mb_strtolower($item['nome']), mb_strtolower($nome)));
        $response
            ->getBody()
            ->write(json_encode($filtrados));
    } else {
        $response
            ->getBody()
            ->write(json_encode($categorias));
    }
    return $response
        ->withHeader('Content-Type', 'application/json')
        ->withStatus(200);
});

$app->delete('/categorias/{id}', function ($request, $response, $args) use (&$categorias) {
    $categorias = array_values(array_filter($categorias, fn($p) => $p['id'] !== (int) $args['id']));
    return $response->withStatus(204);
});

$valorCategoria = '';

$lancamentos = [
    ['id_lancamento' => 1, 'descricao' => 'Spotify', 'valor' => 12, 'data' => '10/11', 'tipo' => '', 'categoria' => 1]
];

$app->get('/lancamentos/total', function ($request, $response, $args) use (&$lancamentos) {

});

return $app;
