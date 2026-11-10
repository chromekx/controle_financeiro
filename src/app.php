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

    if ($dados['nome'] == $categorias['nome']) {
        $response->getBody()->write(json_encode(['erro' => 'Já tem uma categoria com o mesmo nome.']));
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
            if ($dados['nome'] == null || $dados['tipo'] == null) {
                return $response
                    ->getBody()
                    ->write(json_encode(['erro' => 'Complete todos os campos.']));
            } else {
                $categoria['nome'] = $dados['nome'];
                $categoria['tipo'] = $dados['tipo'];
                $response->getBody()->write(json_encode($categoria/* [$args['id']] */)); // n sei se aquele args é necessário pra identificar o id da categoria
                return $response
                    ->withHeader('Content-Type', 'application/json')
                    ->withStatus(200);
            }
        } else {
            $response->getBody()->write(json_encode(['erro' => 'Não há categoria com esse id.']));
            return $response
                ->withHeader('Content-Type', 'application/json')
                ->withStatus(404);
        }
    }
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
    return $response
        ->withHeader('Content-Type', 'application/json')
        ->withStatus(204);
});


// lançamentos

$lancamentos = [
    ['id_lancamento' => 1, 'descricao' => 'Spotify', 'valor' => 12, 'data' => '10/11', 'tipo' => 'Despesa', 'id_categoria' => 2],
    ['id_lancamento' => 2, 'descricao' => 'Academia', 'valor' => 159.99, 'data' => '10/11', 'tipo' => 'Despesa', 'id_categoria' => 3],
    ['id_lancamento' => 3, 'descricao' => 'Geladeira Electrolux', 'valor' => 3099.99, 'data' => '24/11', 'tipo' => 'Despesa', 'id_categoria' => 1],
    ['id_lancamento' => 4, 'descricao' => 'Netflix', 'valor' => 34.99, 'data' => '04/11', 'tipo' => 'Despesa', 'id_categoria' => 7],
    ['id_lancamento' => 5, 'descricao' => 'Conta de Luz', 'valor' => 316, 'data' => '05/11', 'tipo' => 'Despesa', 'id_categoria' => 5],

    ['id_lancamento' => 6, 'descricao' => 'Devolução Sofá', 'valor' => 1599.99, 'data' => '01/01', 'tipo' => 'Receita', 'id_categoria' => 10],
    ['id_lancamento' => 7, 'descricao' => 'salário', 'valor' => 1899.99, 'data' => '03/11', 'tipo' => 'Receita', 'id_categoria' => 8]
];

$app->get('/lancamentos/total', function ($request, $response, $args) use (&$lancamentos) {});

$app->get('/lancamentos', function ($request, $response, $args) use (&$lancamentos) {
    if (!$lancamentos) {
        $response->getBody()->write(json_encode(['erro' => 'Não há lancamentos cadastradas.']));
        return $response
            ->withHeader('Content-Type', 'application/json')
            ->withStatus(404);
    }

    // Missões encontradas
    $response->getBody()->write(json_encode($lancamentos));
    return $response
        ->withHeader('Content-Type', 'application/json')
        ->withStatus(200);
});

$app->get('/lancamentos/{id}', function ($request, $response, $args) use (&$lancamentos) {
    $id = (int) $args['id'];
    $lancamento = current(array_filter($lancamentos, fn($p) => $p['id'] === $id));

    if (!$lancamento) {
        $response->getBody()->write(json_encode(['erro' => 'Lançamento não encontrado.']));
        return $response
            ->withHeader('Content-Type', 'application/json')
            ->withStatus(404);
    }

    // missão encontrada
    $response->getBody()->write(json_encode($lancamento));
    return $response
        ->withHeader('Content-Type', 'application/json')
        ->withStatus(200);
});

$app->post('/lancamentos', function ($request, $response) use (&$lancamentos) {
    // Recebe os dados enviados em JSON
    $dados = $request->getParsedBody();
    // Verifica se o nome foi informado
    if (!isset($dados['descricao']) || empty($dados['descricao']) || 
        !isset($dados['valor']) || empty($dados['valor']) ||
        !isset($dados['data']) || empty($dados['data']) ||
        !isset($dados['tipo']) || empty($dados['tipo']) ||
        !isset($dados['id_categoria']) || empty($dados['id_categoria'])) {
        $response->getBody()->write(json_encode(['erro' => 'Todos os campos precisam estar preenchidos.']));
        return $response
            ->withHeader('Content-Type', 'application/json')
            ->withStatus(400);
    }

    if ($dados['valor'] <= 0) {
        $response->getBody()->write(json_encode(['erro' => 'Um lançamento não pode ter valor 0.']));
        return $response
            ->withHeader('Content-Type', 'application/json')
            ->withStatus(400);
    }

    if ($dados['categoria'] != $categorias['id']) {
        $response->getBody()->write(json_encode(['erro' => 'Um lançamento não pode ter valor 0.']));
        return $response
            ->withHeader('Content-Type', 'application/json')
            ->withStatus(400);
    }

    // Cria uma nova lancamento
    $novalancamento = [
        'id' => count($lancamentos) + 1,
        'nome' => $dados['nome'],
        'tipo' => $dados['tipo']
    ];
    // Adiciona a missão ao array
    $lancamentos[] = $novalancamento;
    // Retorna a missão criada
    $response->getBody()->write(
        json_encode($novalancamento)
    );

    return $response
        ->withHeader('Content-Type', 'application/json')
        ->withStatus(201);
});

$app->put('/lancamentos/{id}', function ($request, $response, $args) use (&$lancamentos) {
    $dados = $request->getParsedBody();

    foreach ($lancamentos as &$lancamento) {
        if ($lancamento['id'] === (int) $args['id']) {
            if ($dados['nome'] == null || $dados['ano'] == null || $dados['agencia'] == null || $dados['status'] == null) {
                return $response
                    ->getBody()
                    ->write(json_encode(['erro' => 'Complete todos os campos.']));
            } else {
                $lancamento['nome'] = $dados['nome'];
                $lancamento['tipo'] = $dados['tipo'];
                $response->getBody()->write(json_encode($lancamento/* [$args['id']] */)); // n sei se aquele args é necessário pra identificar o id da categoria
                return $response
                    ->withHeader('Content-Type', 'application/json')
                    ->withStatus(200);
            }
        } else {
            $response->getBody()->write(json_encode(['erro' => 'Não há categoria com esse id.']));
            return $response
                ->withHeader('Content-Type', 'application/json')
                ->withStatus(404);
        }
    }
});

$app->get('/lancamentos', function ($request, $response, $args) use (&$lancamentos) {
    $queryParams = $request->getQueryParams();
    $nome = $queryParams['nome'] ?? null;
    if ($nome) {
        $filtrados = array_filter($lancamentos, fn($item) => str_contains(mb_strtolower($item['nome']), mb_strtolower($nome)));
        $response
            ->getBody()
            ->write(json_encode($filtrados));
    } else {
        $response
            ->getBody()
            ->write(json_encode($lancamentos));
    }
    return $response
        ->withHeader('Content-Type', 'application/json')
        ->withStatus(200);
});

$app->delete('/lancamentos/{id}', function ($request, $response, $args) use (&$lancamentos) {
    $lancamentos = array_values(array_filter($lancamentos, fn($p) => $p['id'] !== (int) $args['id']));
    return $response
        ->withHeader('Content-Type', 'application/json')
        ->withStatus(204);
});

return $app;
