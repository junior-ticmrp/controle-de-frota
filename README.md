# Controle de Frota — Câmara Municipal de Ribeirão Preto

Aplicação Laravel para controle institucional de requisições, autorizações, abastecimentos, veículos, relatórios, perfis de acesso e manuais operacionais.

> Este repositório é privado e contém somente o código-fonte saneado. Credenciais, arquivos `.env`, chaves privadas, certificados, logs, banco local e dependências vendorizadas não fazem parte da publicação.

## Escopo funcional

A aplicação organiza o fluxo desde a criação da requisição até o registro do abastecimento e a consulta administrativa. O sistema contempla os perfis **Usuário**, **Operador** e **Administração técnica**, autorizações em três vias nos formatos A4 e cupom térmico de 80 mm, filtros por situação, veículo, motorista, período e Gabinete/Setor, além da central de manuais orientada ao perfil autenticado.

O acesso de produção ocorre por HTTPS na rede interna, com certificado emitido pela autoridade certificadora institucional. A configuração de TLS, a chave privada e os arquivos de ambiente devem permanecer fora do repositório.

## Requisitos

- PHP 8.3 ou compatível com as dependências do `composer.json`.
- Composer 2.
- Banco de dados configurado para o ambiente de execução.
- Node.js e npm/pnpm somente quando for necessário recompilar os ativos frontend.
- Extensões PHP indicadas pelo Laravel e pelo `composer.json`.

## Instalação local

```bash
cp .env.example .env
composer install
php artisan key:generate
php artisan migrate
php artisan optimize:clear
php artisan serve
```

Configure o `.env` localmente antes de executar migrations. Nunca copie o `.env` do servidor para o repositório ou para chamados.

## Testes

A suíte automatizada pode ser executada em ambiente de testes isolado:

```bash
php artisan test
```

Não execute a suíte apontando para o banco de produção. Para uma validação segura, use um banco de testes e as variáveis definidas no `phpunit.xml` ou em uma configuração de teste dedicada.

## Desenvolvimento e operação

As rotas protegidas continuam submetidas ao mecanismo de autorização por perfil. A configuração do menu pode ocultar módulos para Usuário ou Operador, mas a ocultação visual não substitui a autorização das rotas.

A central de manuais está disponível em `/manual-operacao` e seleciona o conteúdo conforme o papel autenticado. A configuração administrativa do menu está disponível em `/configuracoes/menu` para Administração técnica.

Para impressão, a autorização A4 deve ser enviada à impressora institucional em papel A4. A autorização Cupom utiliza rolo de 80 mm e pode ser impressa em uma Epson T20 pela janela de impressão do navegador.

## Segurança

Não versionar:

- `.env` e arquivos de ambiente com valores reais;
- senhas, tokens, cookies, chaves privadas, arquivos `.pfx`, `.p12`, `.key`, `.pem`, `.crt` ou `.csr`;
- logs de produção, dumps de banco, backups e arquivos temporários;
- `vendor/`, `node_modules/` e artefatos compilados locais.

Antes de cada publicação, revise `git status`, `git diff --cached --stat` e a lista de arquivos staged. Em caso de suspeita de segredo publicado, interrompa a distribuição, revogue a credencial e solicite análise da TI.

## Estrutura resumida

- `app/`: domínio, modelos, serviços, middleware e controladores;
- `config/`: configurações da aplicação;
- `database/`: migrations, factories e seeders;
- `resources/views/`: telas Blade, relatórios e manuais;
- `routes/`: rotas web;
- `tests/`: testes automatizados;
- `docs/`: documentação técnica versionada quando aplicável.

## Licença e responsabilidade

Este software é destinado ao uso institucional da Câmara Municipal de Ribeirão Preto. Alterações em produção devem seguir backup, revisão, janela controlada e validação posterior.
