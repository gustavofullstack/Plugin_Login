# Estudo de Customização da Página “Minha Conta” (WooCommerce)

## 1. Visão Geral
- A página padrão é gerada pelo shortcode `[woocommerce_my_account]`, exibindo formulário de login quando o usuário não está autenticado e o dashboard com menu lateral ao logar.
- Endpoints nativos: `dashboard`, `orders`, `downloads`, `edit-address`, `edit-account`, `customer-logout`. Plugins extras podem adicionar abas (subscriptions, memberships, etc.).
- Templates localizados em `woocommerce/templates/myaccount/` controlam cada seção; podem ser copiados para o tema filho para customização visual.

## 2. Tipos de Customização
- **Visuais**: alterar layout/cores via CSS ou sobrescrita de templates; ideal para apenas ajustar aparência mantendo lógica padrão.
- **Funcionais**: adicionar/remover/renomear endpoints, integrar shortcodes externos, unificar login/registro, etc. Preferir hooks/filtros para manter compatibilidade com updates.

## 3. Endpoints Personalizados
1. Registrar endpoint: `add_rewrite_endpoint( 'wishlist', EP_ROOT | EP_PAGES );` executado em `init`.
2. Atualizar menu: filtro `woocommerce_account_menu_items` para inserir, renomear ou remover itens.
3. Renderizar conteúdo: `add_action( 'woocommerce_account_wishlist_endpoint', 'callback' );` com HTML/shortcodes desejados.
4. Atualizar permalinks após registrar novos endpoints.
5. Condicionar exibição por role/capacidade quando necessário.

Exemplo:
```php
add_action( 'init', fn() => add_rewrite_endpoint( 'history', EP_ROOT | EP_PAGES ) );
add_filter( 'woocommerce_account_menu_items', function( $items ) {
    $items['history'] = __( 'Histórico', 'udi-custom-login' );
    return $items;
} );
add_action( 'woocommerce_account_history_endpoint', function() {
    echo '<h3>Produtos vistos recentemente</h3>';
    // render custom content...
} );
```

## 4. Menu da Conta
- **Remover**: `unset( $items['downloads'] );`
- **Renomear**: `$items['orders'] = 'Meus Pedidos';`
- **Reordenar**: construir novo array, reposicionando `dashboard`, `orders`, etc., e recolocando `customer-logout` ao final.
- Evitar esconder apenas via CSS para não deixar endpoints acessíveis por URL.

## 5. Login + Registro
- Habilitar “Allow customers to create an account on the My Account page” em WooCommerce → Configurações → Contas e privacidade.
- Se necessário, sobrescrever `myaccount/form-login.php` para formato custom (tabs, colunas, etc.) ou substituir por shortcode do plugin existente.
- Adicionar campos extras no registro por meio de hooks (`woocommerce_register_form`, `woocommerce_created_customer`) se for coletar mais dados.

## 6. Integrações Extras
- Avaliar plugins ativos que adicionam itens (Subscriptions, Memberships, LMS, Afiliados, Wishlist).
- Para unificar experiência, criar endpoints que exibam shortcodes/dashboards desses plugins, respeitando permissões.
- Garantir que reordenações não escondam informações críticas desses add-ons.

## 7. Boas Práticas
- Testar sempre em ambiente de staging antes de atualizar produção.
- Após criar endpoints ou alterar templates, salvar links permanentes.
- Documentar endpoints custom, inclusive redirecionamentos necessários ao remover abas.
- Manter templates sobrescritos atualizados com novas versões do WooCommerce.

## 8. Referências
- WooCommerce Docs (My Account customization)
- Tutorials Codeable, CSSIgniter, WP Beaches
- Exemplos Stack Overflow / blogs especializados citados no briefing original.
