# WordPress DIP + DI — POC

> **Este projeto é uma Prova de Conceito (POC).** O objetivo não é ter um tema pronto para produção, mas demonstrar como aplicar **Dependency Inversion Principle (DIP)** e **Dependency Injection (DI)** no ecossistema WordPress usando o container do Laravel (`illuminate/container`).

---

## O que está sendo demonstrado

| Princípio / Padrão | Como aparece no projeto |
|---|---|
| **DIP** | O tema depende apenas de contratos (interfaces), nunca de classes concretas |
| **DI Container** | `illuminate/container` resolve as dependências automaticamente via autowiring |
| **Strategy + Registry** | Cada fonte de notícias é uma estratégia; o bloco não sabe qual está ativa |
| **Service Provider** | Plugins registram suas implementações sem tocar no código do tema |
| **Sem if/else de dispatch** | A escolha da estratégia é feita pelo registry + container, nunca por condicionais |

---

## Arquitetura

```
wp-dip-and-di/
├── composer.json                  ← autoloader único para toda a solução
├── vendor/                        ← illuminate/container + dependências
├── public/                        ← WordPress (webroot)
│   └── wp-content/
│       ├── themes/
│       │   └── core-theme/        ← Tema principal — define contratos e o bloco
│       │       └── src/
│       │           ├── Contracts/         ← Interfaces (o "D" do DIP)
│       │           ├── Bootstrap/         ← Application singleton + container
│       │           ├── Registry/          ← NewsBlockRegistry (mapa alias → estratégia)
│       │           ├── Support/           ← StrategyStack (value object)
│       │           ├── Block/             ← NewsListBlock (render callback)
│       │           ├── Providers/         ← CoreNewsServiceProvider
│       │           └── Implementations/   ← Implementações padrão (WordPress DB)
│       └── plugins/
│           ├── g1-news/           ← Estratégia "g1": RSS do G1/Globo
│           ├── bbc-news/          ← Estratégia "bbc": RSS da BBC News
│           └── portal-go-news/    ← Estratégia "portal-go": REST API do Portal GO
```

### Fluxo de inicialização

```
functions.php
  │
  ├─ require vendor/autoload.php          (1) carrega o autoloader único
  ├─ Application::getInstance()           (2) cria container + registry
  ├─ registerProvider(CoreProvider)       (3) tema registra estratégia "default"
  │
  ├─ after_setup_theme [prio 5]
  │   └─ do_action('core_theme_register_providers', $app)
  │         │
  │         ├─ g1-news/g1-news.php        (4) plugin registra estratégia "g1"
  │         ├─ bbc-news/bbc-news.php      (4) plugin registra estratégia "bbc"
  │         └─ portal-go-news/...php      (4) plugin registra estratégia "portal-go"
  │
  ├─ after_setup_theme [prio 10]
  │   └─ $app->boot()                     (5) executa boot() de todos os providers
  │
  └─ init
      └─ NewsListBlock::register()        (6) registra o bloco no WordPress
```

### Como uma estratégia é resolvida (sem if/else)

Quando o bloco renderiza com `strategy = "g1"`:

```
NewsListBlock::render(['strategy' => 'g1'])
  │
  ├─ registry->resolve('g1', NewsServiceInterface::class)
  │     └─ container->make(G1NewsService::class)     ← autowiring resolve G1NewsRepository
  │
  └─ registry->resolve('g1', NewsRendererInterface::class)
        └─ container->make(G1NewsRenderer::class)
```

Nenhum `if ($strategy === 'g1')` em lugar nenhum.

### Contratos (Interfaces)

Todos definidos no tema, implementados nos plugins:

| Interface | Responsabilidade |
|---|---|
| `NewsRepositoryInterface` | Busca dados (DB, RSS, REST API…) |
| `NewsServiceInterface` | Orquestra a busca e aplica regras |
| `NewsRendererInterface` | Gera o HTML final |
| `NewsQueryBuilderInterface` | Monta os args do `WP_Query` (estratégia default) |
| `ServiceProviderInterface` | Registra implementações no container e no registry |

### Estratégias disponíveis

| Alias | Plugin | Fonte | Renderer |
|---|---|---|---|
| `default` | Core Theme | Posts do WordPress (`WP_Query`) | Cards com imagem em grid |
| `g1` | g1-news | RSS do G1 Globo | Hero editorial + grade de cards |
| `bbc` | bbc-news | RSS da BBC News | Header escuro + top story horizontal + lista de texto |
| `portal-go` | portal-go-news | REST API do Portal GO (`wp-json/wp/v2/posts`) | Featured com overlay + lista com thumbnail à direita |

---

## Como rodar

### Pré-requisitos

- Docker e Docker Compose
- PHP 8.1+ e Composer (para gerar o autoloader localmente)

### 1. Instalar dependências

```bash
composer install
```

### 2. Subir os containers

```bash
docker compose up -d
```

Aguarde o MariaDB ficar saudável (o serviço WordPress depende disso via `healthcheck`).

### 3. Instalar o WordPress

Acesse `http://localhost:8080` e siga o assistente de instalação.

### 4. Ativar tema e plugins

No painel WordPress:

1. **Aparência → Temas** → ativar **Core Theme**
2. **Plugins → Instalados** → ativar:
   - **G1 News**
   - **BBC News**
   - **Portal GO News**

### 5. Adicionar o bloco

Em qualquer página ou post:

1. Abra o editor de blocos (Gutenberg)
2. Adicione o bloco **News List**
3. No painel lateral, escolha a estratégia no campo **Strategy**
4. O bloco renderiza ao vivo via Server-Side Render

---

## Volumes Docker

```yaml
wordpress:
  volumes:
    - ./vendor:/var/www/vendor:ro    # autoloader do Composer (somente leitura)
    - ./public:/var/www/public:ro    # resolve os paths PSR-4 do classloader
    - ./public:/var/www/html         # webroot servido pelo nginx + PHP-FPM
```

O `composer.json` fica na raiz do projeto com um único `vendor/`. Todos os namespaces (`CoreTheme\`, `G1News\`, `BbcNews\`, `PortalGo\`) são mapeados nele — nenhum plugin tem seu próprio `composer.json`.

---

## Adicionando uma nova estratégia

1. Crie um plugin WordPress normal
2. Implemente as interfaces que desejar sobrescrever (`NewsRepositoryInterface`, `NewsServiceInterface`, `NewsRendererInterface`)
3. Crie um `ServiceProviderInterface` que registre um `StrategyStack` com um alias único
4. No arquivo principal do plugin, registre o provider no hook:

```php
add_action('core_theme_register_providers', function (Application $app): void {
    $app->registerProvider(new MinhaEstrategiaServiceProvider());
});
```

5. Ative o plugin — a estratégia aparece automaticamente no SelectControl do editor

Nenhum arquivo do tema ou dos outros plugins precisa ser tocado.
