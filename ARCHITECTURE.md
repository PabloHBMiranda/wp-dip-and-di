# Arquitetura de Temas WordPress com DIP + DI

> Documento de referência para discussão técnica sobre escalabilidade e manutenibilidade de temas WordPress usando Dependency Inversion Principle e Dependency Injection.

---

## O problema que esse modelo resolve

### Cenário atual — N forks

```
core-theme/          ← mantém
client-a-theme/      ← fork, mantém separado
client-b-theme/      ← fork, mantém separado
client-c-theme/      ← fork, mantém separado
```

- Bug corrigido no core → precisa replicar em N temas manualmente
- WordPress atualiza → precisa testar N temas
- Feature nova → decide quais forks merecem receber, replica manualmente
- Cada fork carrega **o peso do tema inteiro** para personalizar **5% do comportamento**

### Cenário proposto — Core + Plugins

```
core-theme/          ← mantém (1 lugar)
client-a-plugin/     ← 5 a 10 arquivos
client-b-plugin/     ← 5 a 10 arquivos
client-c-plugin/     ← 5 a 10 arquivos
```

- Bug corrigido no core → todos os clientes recebem na próxima atualização
- Feature nova → entra no core, cada cliente adota no ritmo dele
- Cada plugin carrega **5% do código** para personalizar **5% do comportamento**

---

## Como funciona

### Contratos (Interfaces)

O tema core define **o que** pode ser implementado. Nunca **como**:

| Interface | Responsabilidade |
|---|---|
| `NewsRepositoryInterface` | De onde vêm os dados (DB, RSS, REST API…) |
| `NewsServiceInterface` | Como os dados são orquestrados |
| `NewsRendererInterface` | Como os dados viram HTML |
| `NewsQueryBuilderInterface` | Como montar queries no WordPress |
| `ServiceProviderInterface` | Como um plugin se registra no sistema |

### Estratégias

Cada plugin registra uma estratégia com um alias único:

| Alias | Fonte | Renderer |
|---|---|---|
| `default` | WordPress DB (`WP_Query`) | Grid de cards |
| `g1` | RSS G1/Globo | Hero editorial + grade |
| `bbc` | RSS BBC News | Header escuro + top story |
| `portal-go` | REST API Portal GO | Featured com overlay |

### Resolução sem if/else

```php
// NewsListBlock::render() — o bloco não conhece nenhuma estratégia concreta
$alias    = $attributes['strategy']; // ex: "g1"
$service  = $registry->resolve($alias, NewsServiceInterface::class);
$renderer = $registry->resolve($alias, NewsRendererInterface::class);

return $renderer->render($service->getNews($attributes), $attributes);
```

O registry + container resolvem a classe concreta certa. Nenhum condicional no tema.

### Como um plugin se registra

```php
// client-a-plugin/client-a.php — único ponto de entrada
add_action('core_theme_register_providers', function (Application $app): void {
    $app->registerProvider(new ClientAServiceProvider());
});

// ClientAServiceProvider::boot()
$strategy = new StrategyStack('client-a', 'Client A News');
$strategy
    ->bind(NewsRepositoryInterface::class, ClientANewsRepository::class)
    ->bind(NewsRendererInterface::class,   ClientANewsRenderer::class);

$registry->register($strategy);
```

---

## DIP+DI vs Filtros de Bloco

### Quando filtros ganham

| Critério | Filtros |
|---|---|
| Curva de aprendizado | Qualquer dev WordPress já sabe |
| Adoção incremental | Funciona em qualquer tema existente hoje |
| Ecossistema | Plugins de terceiros se integram naturalmente |
| Tooling | Query Monitor, WP-CLI entendem nativamente |

### Quando DIP+DI ganha

| Critério | DIP+DI |
|---|---|
| Manutenção de N clientes | 1 base + N plugins vs N forks |
| Atualização | Automática para todos os clientes |
| Isolamento | Cada cliente não afeta os outros |
| Testabilidade | Classes puras, sem bootstrap do WordPress |
| Contrato explícito | IDE, análise estática, erro em tempo de boot |
| Rastreabilidade | Registry sabe exatamente o que está ativo |

### O argumento definitivo para o seu cenário

Filtros dizem **"você pode se pendurar aqui"**.
Contratos dizem **"você pode substituir isso aqui"**.

Para um desenvolvedor que mantém um produto com N personalizações, **substituição controlada é mais poderosa que gancho aberto**.

---

## 20 vantagens do modelo

1. **Atualização sem regressão** — core muda internamente, plugins não quebram
2. **Extensão sem acesso ao código-fonte** — o contrato é a documentação completa
3. **Múltiplos fornecedores para o mesmo contrato** — sem conflito entre plugins
4. **Falha explícita e antecipada** — erro em boot, não em produção com usuário
5. **Testabilidade real por camada** — cada classe testável sem WordPress
6. **Onboarding com escopo cirúrgico** — lê os contratos, sabe onde contribuir
7. **Precificação por capacidade** — cada contrato é um eixo de personalização vendável
8. **Composição de estratégias parciais** — sobrescreve só o que precisa mudar
9. **Rastreabilidade completa** — registry sabe qual classe resolve o quê
10. **Autodocumentação por tipo** — assinatura do método é o contrato
11. **Refatoração interna sem cerimônia** — implementação muda, contrato não
12. **Análise estática funciona** — PHPStan/Psalm entendem interfaces
13. **Fallback previsível** — hierarquia explícita, sem guerra de prioridades
14. **Proteção contra dependência de implementação** — plugins dependem do contrato
15. **Evolução por adição** — novas features entram como novos arquivos
16. **Contrato como SLA técnico** — estabilidade da interface é uma promessa formal
17. **Injeção de dependências em profundidade** — container resolve a árvore inteira
18. **Portabilidade da lógica de negócio** — classes PHP puras, sem acoplamento ao WP
19. **Experiência de IDE completa** — autocompletar, navegação, detecção de erros
20. **O produto cresce sem crescer em complexidade** — N plugins, mesma complexidade do core

---

## Desvantagens reais

Das 20 desvantagens possíveis desse modelo, apenas **3 se aplicam** quando o desenvolvedor é o mesmo dos dois lados:

1. **Cold start por requisição** — o container bootstrapa a cada requisição PHP. Custo fixo e pequeno, mas real em alta concorrência.

2. **Acertar os contratos cedo** — definir granularidade errada no início custa refatoração futura. Muitos contratos = complexidade desnecessária. Poucos = pouco flexível.

3. **Deploy do autoloader** — atualizar um plugin requer `composer dump-autoload` na raiz do tema. Dependendo do pipeline de entrega, isso pode ser um atrito operacional.

---

## Conclusão

O modelo de contratos transforma o relacionamento entre tema base e personalização:

| | Fork (hoje) | Plugin (proposto) |
|---|---|---|
| Tamanho da personalização | Tema inteiro | 5–10 arquivos |
| Bug fix no core | Manual em N lugares | Automático |
| Feature nova | Decisão por fork | Disponível para todos |
| Teste | N ambientes | 1 core + mocks |
| Rastreabilidade | Diff entre forks | Registry em runtime |

A arquitetura certa não é a mais elegante — é a que resolve o problema real sem custo desnecessário. Nesse cenário, DIP+DI resolve o problema central: **manter um produto com múltiplas variações sem multiplicar a superfície de manutenção**.
