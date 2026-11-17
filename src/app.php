<?php

require __DIR__ . '/../vendor/autoload.php';

use Slim\Factory\AppFactory;

$app = AppFactory::create(); // cria a aplicacao slim
$app->addBodyParsingMiddleware(); // permite que a aplicacao interprete dados enviados em json


$app->get('/status', function ($request, $response) { // verifica se a api ta funcionando
    $response->getBody()->write(json_encode(['status' => 'ok']));
    return $response
        ->withHeader('Content-Type', 'application/json')
        ->withStatus(200);
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

$app->get('/categorias', function ($request, $response) use (&$categorias) { // lista todas as categorias ou pesquisa pelo nome
    $queryParams = $request->getQueryParams(); // recebe os parametros enviados pela URL
    $nome = $queryParams['nome'] ?? null; // verifica se foi informado um nome para pesquisa

    if ($nome) {
        $filtrados = array_values(array_filter( // filtra as categorias que possuem o nome pesquisado
            $categorias,
            fn($item) => str_contains(
                mb_strtolower($item['nome']),
                mb_strtolower($nome)
            )
        ));
        $response->getBody()->write(json_encode($filtrados));
    } else {
        if (!$categorias) { // retorna todas as categorias cadastradas
            $response->getBody()->write(json_encode(['erro' => 'Não há categorias cadastradas.']));
            return $response
                ->withHeader('Content-Type', 'application/json')
                ->withStatus(404);
        }
        $response->getBody()->write(json_encode($categorias));
    }

    return $response
        ->withHeader('Content-Type', 'application/json')
        ->withStatus(200);
});

$app->get('/categorias/{id}', function ($request, $response, $args) use (&$categorias) { // busca uma categoria pelo ID
    $id = (int) $args['id']; // recebe o id informado na url
    $categoria = current(array_filter( // procura a categoria correspondente ao id
        $categorias,
        fn($p) => $p['id_categoria'] === $id
    ));

    if (!$categoria) { // verifica se a categoria foi encontrada
        $response->getBody()->write(json_encode(['erro' => 'Categoria não encontrada.']));
        return $response
            ->withHeader('Content-Type', 'application/json')
            ->withStatus(404);
    }

    $response->getBody()->write(json_encode($categoria)); // retorna a categoria encontrada
    return $response
        ->withHeader('Content-Type', 'application/json')
        ->withStatus(200);
});

$app->post('/categorias', function ($request, $response) use (&$categorias) { // Cadastra uma nova categoria
    $dados = $request->getParsedBody(); // recebe os dados enviados em json

    if (!isset($dados['nome']) || empty(trim($dados['nome'])) || !isset($dados['tipo']) || empty(trim($dados['tipo']))) { // verifica se todos os campos foram preenchidos
        $response->getBody()->write(json_encode(['erro' => 'Todos os campos precisam estar preenchidos.']));
        return $response
            ->withHeader('Content-Type', 'application/json')
            ->withStatus(400);
    }

    if (!in_array($dados['tipo'], ['Despesa', 'Receita'])) { // verifica se o tipo informado e valido
        $response->getBody()->write(json_encode(['erro' => 'Tipo de categoria inválido.']));
        return $response
            ->withHeader('Content-Type', 'application/json')
            ->withStatus(400);
    }

    foreach ($categorias as $categoria) { // verifica se ja existe uma categoria com o mesmo nome
        if (mb_strtolower($categoria['nome']) == mb_strtolower($dados['nome'])) {
            $response->getBody()->write(json_encode(['erro' => 'Já tem uma categoria com o mesmo nome.']));
            return $response
                ->withHeader('Content-Type', 'application/json')
                ->withStatus(400);
        }
    }

    $novaCategoria = [ // cria uma nova categoria com o prox id disponivel
        'id_categoria' => max(array_column($categorias, 'id_categoria')) + 1,
        'nome' => $dados['nome'],
        'tipo' => $dados['tipo']
    ];

    $categorias[] = $novaCategoria; // adiciona a nova categoria ao array
    $response->getBody()->write(json_encode($novaCategoria)); // retorna os dados da categoria criada
    return $response
        ->withHeader('Content-Type', 'application/json')
        ->withStatus(201);
});

$app->put('/categorias/{id}', function ($request, $response, $args) use (&$categorias) { // atualiza uma categoria pelo id   
    $dados = $request->getParsedBody(); // Recebe os dados enviados para atualização

    if (!isset($dados['nome']) || empty(trim($dados['nome'])) || !isset($dados['tipo']) || empty(trim($dados['tipo']))) { // Verifica se todos os campos foram preenchidos
        $response->getBody()->write(json_encode(['erro' => 'Complete todos os campos.']));

        return $response
            ->withHeader('Content-Type', 'application/json')
            ->withStatus(400);
    }

    if (!in_array($dados['tipo'], ['Despesa', 'Receita'])) { // Verifica se o tipo informado é válido
        $response->getBody()->write(json_encode(['erro' => 'Tipo de categoria inválido.']));

        return $response
            ->withHeader('Content-Type', 'application/json')
            ->withStatus(400);
    }

    foreach ($categorias as &$categoria) { // analisa nas categorias se tem o id informado
        if ($categoria['id_categoria'] === (int) $args['id']) {
            $categoria['nome'] = $dados['nome']; // atualiza os dados da categoria encontrada
            $categoria['tipo'] = $dados['tipo'];

            $response->getBody()->write(json_encode($categoria)); // retorna a categoria atualizada
            unset($categoria);

            return $response
                ->withHeader('Content-Type', 'application/json')
                ->withStatus(200);
        }
    }

    unset($categoria);
    $response->getBody()->write(json_encode(['erro' => 'Categoria não encontrada.'])); // Caso o ID não seja encontrado, retorna erro

    return $response
        ->withHeader('Content-Type', 'application/json')
        ->withStatus(404);
});

$app->delete('/categorias/{id}', function ($request, $response, $args) use (&$categorias) { // exclui uma categoria pelo ID
    $quantidadeAntes = count($categorias); // guarda a quantidade de categorias antes da exclusão
    $categorias = array_values(array_filter( // remove a categoria que possui o ID informado
        $categorias,
        fn($p) => $p['id_categoria'] !== (int) $args['id']
    ));

    if (count($categorias) === $quantidadeAntes) { // Verifica se alguma categoria foi removida
        $response->getBody()->write(json_encode(['erro' => 'Categoria não encontrada.']));

        return $response
            ->withHeader('Content-Type', 'application/json')
            ->withStatus(404);
    }

    return $response->withStatus(204); // Retorna 204 indicando que a exclusão foi realizada
});

// -------- //

$lancamentos = [
    ['id_lancamento' => 1, 'descricao' => 'Spotify', 'valor' => 12, 'data' => '10/11', 'tipo' => 'Despesa', 'id_categoria' => 2],
    ['id_lancamento' => 2, 'descricao' => 'Academia', 'valor' => 159.99, 'data' => '10/11', 'tipo' => 'Despesa', 'id_categoria' => 3],
    ['id_lancamento' => 3, 'descricao' => 'Geladeira Electrolux', 'valor' => 3099.99, 'data' => '24/11', 'tipo' => 'Despesa', 'id_categoria' => 1],
    ['id_lancamento' => 4, 'descricao' => 'Netflix', 'valor' => 34.99, 'data' => '04/11', 'tipo' => 'Despesa', 'id_categoria' => 7],
    ['id_lancamento' => 5, 'descricao' => 'Conta de Luz', 'valor' => 316, 'data' => '05/11', 'tipo' => 'Despesa', 'id_categoria' => 5],

    ['id_lancamento' => 6, 'descricao' => 'Devolução Sofá', 'valor' => 1599.99, 'data' => '01/01', 'tipo' => 'Receita', 'id_categoria' => 10],
    ['id_lancamento' => 7, 'descricao' => 'Salário', 'valor' => 1899.99, 'data' => '03/11', 'tipo' => 'Receita', 'id_categoria' => 8]
];


// calcula o total de receitas, despesas e saldo (precisa ficar antes de /lancamentos/{id})
$app->get('/lancamentos/total', function ($request, $response) use (&$lancamentos) {
    $totalReceitas = 0; // inicializa os valores dos totais
    $totalDespesas = 0;

    foreach ($lancamentos as $lancamento) { // analisa todos os lançamentos para calcular os valores
        if ($lancamento['tipo'] === 'Receita') {
            $totalReceitas += $lancamento['valor'];
        } else {
            $totalDespesas += $lancamento['valor'];
        }
    }

    $saldo = $totalReceitas - $totalDespesas; // calcula o saldo diminuindo as despesas das receitas
    $totais = [ // Organiza os resultados
        'total_receitas' => round($totalReceitas, 2),
        'total_despesas' => round($totalDespesas, 2),
        'saldo' => round($saldo, 2)
    ];

    $response->getBody()->write(json_encode($totais));
    return $response
        ->withHeader('Content-Type', 'application/json')
        ->withStatus(200);
});

$app->get('/lancamentos', function ($request, $response) use (&$lancamentos) { // lista todos os lançamentos ou pesquisa pela descrição
    $queryParams = $request->getQueryParams(); // recebe os parametros enviados pela url
    $descricao = $queryParams['descricao'] ?? null; // verifica se foi informada uma descrição para pesquisa

    if ($descricao) {
        $filtrados = array_values(array_filter( // filtra os lançamentos pela descriçao informada
            $lancamentos,
            fn($item) => str_contains(
                mb_strtolower($item['descricao']),
                mb_strtolower($descricao)
            )
        ));
        $response->getBody()->write(json_encode($filtrados));
    } else {
        if (!$lancamentos) { // verifica se existem lançamentos cadastrados
            $response->getBody()->write(json_encode(['erro' => 'Não há lançamentos cadastrados.']));
            return $response
                ->withHeader('Content-Type', 'application/json')
                ->withStatus(404);
        }
        $response->getBody()->write(json_encode($lancamentos)); // retorna todos os lançamentos
    }

    return $response
        ->withHeader('Content-Type', 'application/json')
        ->withStatus(200);
});

$app->get('/lancamentos/{id}', function ($request, $response, $args) use (&$lancamentos) { // busca um lançamento pelo id
    $id = (int) $args['id']; // recebe o id informado na url
    $lancamento = current(array_filter( // procura o lançamento igual ao id
        $lancamentos,
        fn($p) => $p['id_lancamento'] === $id
    ));

    if (!$lancamento) { // verifica se o lançamento foi encontrado
        $response->getBody()->write(json_encode(['erro' => 'Lançamento não encontrado.']));
        return $response
            ->withHeader('Content-Type', 'application/json')
            ->withStatus(404);
    }

    $response->getBody()->write(json_encode($lancamento)); // retorna o lançamento encontrado
    return $response
        ->withHeader('Content-Type', 'application/json')
        ->withStatus(200);
});



$app->post('/lancamentos', function ($request, $response) use (&$lancamentos, &$categorias) { // cadastra um novo lançamento

    $dados = $request->getParsedBody(); // recebe os dados enviados em json

    if ( // verifica se todos os campos foram preenchidos
        !isset($dados['descricao']) || empty(trim($dados['descricao'])) ||
        !isset($dados['valor']) || $dados['valor'] === '' ||
        !isset($dados['data']) || empty($dados['data']) ||
        !isset($dados['tipo']) || empty($dados['tipo']) ||
        !isset($dados['id_categoria']) || $dados['id_categoria'] === ''
    ) {
        $response->getBody()->write(json_encode(['erro' => 'Todos os campos precisam estar preenchidos.']));
        return $response
            ->withHeader('Content-Type', 'application/json')
            ->withStatus(400);
    }

    if (!is_numeric($dados['valor']) || $dados['valor'] <= 0) { // verifica se o valor informado e numerico e positivo
        $response->getBody()->write(json_encode(['erro' => 'O valor do lançamento precisa ser maior que zero.']));
        return $response
            ->withHeader('Content-Type', 'application/json')
            ->withStatus(400);
    }

    if (!in_array($dados['tipo'], ['Despesa', 'Receita'])) { // verifica se o tipo do lançamento e valido
        $response->getBody()->write(json_encode(['erro' => 'Tipo de lançamento inválido.']));
        return $response
            ->withHeader('Content-Type', 'application/json')
            ->withStatus(400);
    }

    $categoriaEncontrada = current(array_filter($categorias, fn($c) => $c['id_categoria'] === (int) $dados['id_categoria'])); // procura a categoria informada no lançamento

    if (!$categoriaEncontrada) { // verifica se a categoria existe
        $response->getBody()->write(json_encode(['erro' => 'Categoria não encontrada.']));
        return $response
            ->withHeader('Content-Type', 'application/json')
            ->withStatus(400);
    }

    if ($dados['tipo'] !== $categoriaEncontrada['tipo']) { // verifica se o tipo do lançamento e igual ao tipo da categoria
        $response->getBody()->write(json_encode(['erro' => 'O tipo do lançamento não corresponde ao tipo da categoria.']));
        return $response
            ->withHeader('Content-Type', 'application/json')
            ->withStatus(400);
    }

    $novoLancamento = [ // cria o novo lançamento com o prox id disponivel
        'id_lancamento' => count($lancamentos) > 0
            ? max(array_column($lancamentos, 'id_lancamento')) + 1
            : 1,
        'descricao' => $dados['descricao'],
        'valor' => (float) $dados['valor'],
        'data' => $dados['data'],
        'tipo' => $dados['tipo'],
        'id_categoria' => (int) $dados['id_categoria']
    ];


    $lancamentos[] = $novoLancamento; // adiciona o lançamento ao array
    $response->getBody()->write(json_encode($novoLancamento)); // retorna os dados do lançamento criado
    return $response
        ->withHeader('Content-Type', 'application/json')
        ->withStatus(201);
});


$app->put('/lancamentos/{id}', function ($request, $response, $args) use (&$lancamentos, &$categorias) { // atualiza um lançamento pelo ID
    $dados = $request->getParsedBody(); // Recebe os dados enviados para atualização
   
    if ( // verifica se todos os campos foram preenchidos
        !isset($dados['descricao']) || empty(trim($dados['descricao'])) ||
        !isset($dados['valor']) || $dados['valor'] === '' ||
        !isset($dados['data']) || empty($dados['data']) ||
        !isset($dados['tipo']) || empty($dados['tipo']) ||
        !isset($dados['id_categoria']) || $dados['id_categoria'] === ''
    ) {
        $response->getBody()->write(json_encode(['erro' => 'Complete todos os campos.']));
        return $response
            ->withHeader('Content-Type', 'application/json')
            ->withStatus(400);
    }
   
    if (!is_numeric($dados['valor']) || $dados['valor'] <= 0) { // verifica se o valor e numerico e positivo
        $response->getBody()->write(json_encode(['erro' => 'O valor do lançamento precisa ser maior que zero.']));
        return $response
            ->withHeader('Content-Type', 'application/json')
            ->withStatus(400);
    }
   
    if (!in_array($dados['tipo'], ['Despesa', 'Receita'])) { // verifica se o tipo informado e valido
        $response->getBody()->write(json_encode(['erro' => 'Tipo de lançamento inválido.']));
        return $response
            ->withHeader('Content-Type', 'application/json')
            ->withStatus(400);
    }

   
    $categoriaEncontrada = current(array_filter($categorias, fn($c) => $c['id_categoria'] === (int) $dados['id_categoria'])); // procura a categoria informada

    // verifica se a categoria existe
    if (!$categoriaEncontrada) {
        $response->getBody()->write(json_encode(['erro' => 'Categoria não encontrada.']));
        return $response
            ->withHeader('Content-Type', 'application/json')
            ->withStatus(400);
    }

    
    if ($dados['tipo'] !== $categoriaEncontrada['tipo']) { // verifica se o tipo do lançamento corresponde ao da categoria
        $response->getBody()->write(json_encode(['erro' => 'O tipo do lançamento não corresponde ao tipo da categoria.']));
        return $response
            ->withHeader('Content-Type', 'application/json')
            ->withStatus(400);
    }
   
    foreach ($lancamentos as &$lancamento) { // analisa os lançamentos procurando o id informado
        if ($lancamento['id_lancamento'] === (int) $args['id']) {

            $lancamento['descricao'] = $dados['descricao']; // atualiza todos os dados do lançamento
            $lancamento['valor'] = (float) $dados['valor'];
            $lancamento['data'] = $dados['data'];
            $lancamento['tipo'] = $dados['tipo'];
            $lancamento['id_categoria'] = (int) $dados['id_categoria'];
           
            $response->getBody()->write(json_encode($lancamento)); // retorna os dados atualizados
            unset($lancamento);
            return $response
                ->withHeader('Content-Type', 'application/json')
                ->withStatus(200);
        }
    }

    unset($lancamento);
    $response->getBody()->write(json_encode(['erro' => 'Lançamento não encontrado.'])); // caso o id nao seja encontrado, retorna erro
    return $response
        ->withHeader('Content-Type', 'application/json')
        ->withStatus(404);
});


$app->delete('/lancamentos/{id}', function ($request, $response, $args) use (&$lancamentos) { // exclui um lançamento pelo ID
    $quantidadeAntes = count($lancamentos); // guarda a quantidade de lançamentos antes da exclusão
    $lancamentos = array_values(array_filter($lancamentos, fn($p) => $p['id_lancamento'] !== (int) $args['id'])); // remove o lançamento que possui o id informado
   
    if (count($lancamentos) === $quantidadeAntes) { // verifica se algum lançamento foi removido
        $response->getBody()->write(json_encode(['erro' => 'Lançamento não encontrado.']));
        return $response
            ->withHeader('Content-Type', 'application/json')
            ->withStatus(404);
    }
   
    return $response->withStatus(204); // Retorna 204 indicando que a exclusão foi realizada
});

return $app; // retorna a aplicacao para que ela possa ser utilizada pelo servidor ou pelos testes
