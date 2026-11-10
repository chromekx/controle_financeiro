Como executar a API:

Copie o link do repositório e clone ele com o GitBash:
git clone (link do repositório)

Abra o repositório clonado no VSCode, e baixe os seguintes programas pelo terminal, um de cada vez:
composer require slim/slim 
composer require slim/psr7
composer require --dev phpunit/phpunit
sudo apt install php-xml php-dom

Como executar os testes automatizados:

Abra o terminal do VSCode e digite os seguintes comandos, um de cada vez:
./vendor/bin/phpunit tests
./vendor/bin/phpunit tests/ProdutoTest.php
./vendor/bin/phpunit --filter testDeleteProdutoPorId tests/ProdutoTest.php

Digite na URL do navegador o link do servidor local e o endpoint desejado:
localhost:8080/categorias/2