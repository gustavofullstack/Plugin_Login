# Plugin de Tela de Login Personalizada (WordPress & WooCommerce)

Documento com as especificações funcionais, visuais e técnicas para o desenvolvimento do plugin solicitado.

## 1. Objetivo do Projeto
- Substituir integralmente todas as telas padrão de login, registro e recuperação de senha do WordPress/WooCommerce por experiências personalizadas com foco em branding, conversão e segurança.
- Garantir consistência visual com a identidade dark/neon da marca, maior usabilidade e recursos avançados (login social, captcha, limites de tentativas, etc.).
- Fornecer configurações amigáveis no painel administrativo para que a loja ajuste estilos, mensagens, fluxos e integrações sem editar código.

## 2. Diretrizes de UI/UX
- **Paleta e estilos**: aplicar estritamente as variáveis definidas (`--udi-black`, `--udi-neon`, etc.). Fundo predominante escuro, formulário em destaque (podendo ser container translúcido) e opção de imagem/gráfico de fundo discreto.
- **Tipografia**: texto principal em `--udi-text`, tipografia consistente com o site. Botões em verde neon com animações de hover usando `--udi-neon-soft` e `--udi-neon-glow`.
- **Layout**: formulário minimalista, campos essenciais, opção de login e registro lado a lado ou via abas responsivas (colunas em desktop, empilhados em mobile). Headline opcional e mensagens de boas-vindas configuráveis.
- **Componentes aprimorados**: placeholders claros, validações em tempo real, indicador de força de senha, botão “mostrar/ocultar senha”, checkbox “lembrar-me” estilizada, links evidentes de registro e recuperação.
- **Feedback visual**: estados de foco com glow neon, bordas em `--udi-danger` para erros, alertas estilizados com ícones/cores da paleta, micro animações leves (hover, loading, etc.).
- **Branding**: logo custom acima ou próximo ao formulário com opção de upload no painel. Links do logo e textos alinhados ao branding da loja.
- **Acessibilidade**: labels associados, contraste adequado, navegação por teclado, textos alternativos, alerts acessíveis (ARIA).

## 3. Funcionalidades Principais
- **Formulários unificados**: login, registro e recuperação no front-end com mesma estética; se usuário estiver logado, exibir mensagem/atalho ou redirecionar.
- **Login social**: integração nativa ou compatibilidade com plugins (ex.: Nextend Social Login). Botões estilizados, com suporte a Google, Facebook, Apple, Twitter, etc.
- **Registro simplificado**: campos mínimos (nome, e-mail, senha ou e-mail/senha), email como username opcional, validações de disponibilidade e força de senha. Após registro, opção de login automático e redirecionamento configurável.
- **Recuperação de senha**: fluxo completo dentro do layout custom, incluindo formulários de envio e redefinição; mensagens de email personalizadas opcionalmente.
- **Redirecionamentos**: configurações para pós-login, pós-registro e pós-recuperação; suporte a regras por função (cliente, admin) e retorno à página anterior quando aplicável.
- **Mensagens customizadas**: textos de sucesso/erro editáveis (i18n-ready) para login incorreto, bloqueio, cadastro finalizado, etc.
- **Compatibilidade WooCommerce**: aplicar estilos na página “Minha Conta”, formulário de checkout (se exibir login) e quaisquer shortcodes/login hooks usados pelo WooCommerce.
- **Shortcodes/Templates**: fornecer shortcode (ex.: `[udi_custom_login]`) e possibilidade de template override para inserir os formulários em páginas personalizadas.

## 4. Segurança
- Obrigatoriedade de HTTPS (verificação/redirecionamento automático).
- Integração com Google reCAPTCHA v2/v3 (configuração de Site Key/Secret no painel). Opção de ativar apenas após X falhas ou sempre.
- Limite configurável de tentativas por usuário/IP com bloqueio temporário e mensagem dedicada.
- Suporte/compatibilidade com 2FA (plugins populares) e, se viável, implementação nativa via TOTP/email/SMS.
- Opção para URL de login customizada e redirecionamento controlado das rotas `wp-login.php`.
- Saneamento/validação rigorosa (uso de funções WP, nonces, escape, prepared statements).
- Política de senha forte e indicador visual; possibilidade de exigir requisitos mínimos.
- Proteções anti-spam em registro (honeypot, confirmação de email, reCAPTCHA).
- Compatibilidade com plugins de auditoria/logs; opcionalmente registrar tentativas/bloqueios.

## 5. Desempenho
- Carregar CSS/JS somente em páginas relevantes (wp-login, shortcode, Minha Conta, checkout se necessário).
- Código modular, minimalista e minificado; evitar dependências pesadas.
- Assets otimizados (imagens comprimidas, CSS crítico inline se preciso).
- Scripts JS assíncronos/deferidos, sem bloquear thread principal; microinterações leves.
- Compatibilidade com plugins de cache/CDN; documentar exceções (não cachear página de login).
- Boas práticas gerais: nocache_headers em fluxos de autenticação, uso eficiente de APIs externas (captcha/OAuth), evitar consultas repetitivas.

## 6. Configuração/Admin
- Página de configurações no WP Admin (API Settings), preferencialmente organizada em abas: Design, Funcionalidades, Segurança, Redirecionamentos, Textos.
- Campos previstos:
  - Upload de logo, imagem de fundo, toggles para container translúcido, ajustes de cor (quando aplicável).
  - Seleção de layout (colunas vs abas), mensagens de boas-vindas/headline.
  - Ativação/ordem dos logins sociais e chaves das integrações.
  - Configurações reCAPTCHA (tipo, chaves, quando exibir) e limites de tentativas (quantidade/duração).
  - URLs de redirecionamento (com seletor de páginas ou campo livre), opção de diferenciar por função.
  - Textos customizáveis para erros e confirmações, suportando placeholders.
  - Opção de habilitar/desabilitar override de wp-login e WooCommerce.
- Todos os campos com saneamento adequado e armazenamento usando `get_option`/`update_option`.
- Incluir export/import das configurações (opcional) ou instruções para migração.

## 7. Implementação Técnica
- Arquitetura orientada a ações/filtros: `login_enqueue_scripts`, `login_head`, `login_form`, `woocommerce_before_customer_login_form`, etc.
- Prefixos únicos (`udi_login_*`) para funções, hooks, CSS e IDs.
- Templates WooCommerce sobrescritos via filtro `woocommerce_locate_template` ou pasta `templates/woocommerce/myaccount/form-login.php`.
- Shortcodes e widgets encapsulados; possibilidade de registrar rota custom (`rewrite_endpoint`) para URL amigável de login.
- Internacionalização completa: usar `__()`, `_e()`, `_x()`; fornecer arquivo `.pot`.
- Compatibilidade com WP 5.8+ e WooCommerce 7.x+. Testar também com temas padrão (Twenty Twenty-One) e populares (Astra, Flatsome).
- Documentar hooks internos (ex.: `do_action('udi_login_after_fields')`) para extensões futuras.
- Respeitar WordPress Coding Standards (PHPCS). CSS/JS seguindo convenções e com comentários apenas quando necessário para clarificar trechos complexos.

## 8. Testes e QA
- Fluxos: login válido/ inválido, registro válido/duplicado, recuperação de senha, redirecionamentos.
- Segurança: captcha ativo/inativo, bloqueios por tentativas, integração com plugin 2FA, mudança de URL.
- UX: responsividade em breakpoints principais, navegação por teclado, leitores de tela, animações suaves.
- Performance: medir com Lighthouse/Pingdom, garantir carregamento rápido (<2s em rede padrão).
- Compatibilidade: verificar com WooCommerce My Account, checkout com login, shortcodes, além de garantir que WP Admin não é afetado.
- Logs/debug: modo verbose opcional (constante ou toggle) registrando eventos relevantes durante desenvolvimento/testes.

## 9. Documentação Entregue
- Este README com especificações.
- Guia de instalação/configuração (pode ser seção adicional ou arquivo separado) com:
  - Pré-requisitos (versões WP, Woo, PHP, extensões).
  - Passo a passo para configurar logo, cores, captcha, social login, redirecionamentos.
  - Instruções para aplicar shortcode ou definir página custom de login.
  - Notas sobre compatibilidade com cache/CDN e testes recomendados.

## 10. Critérios de Aceite
- As telas de login/registro/recuperação padrão são 100% substituídas pela interface personalizada.
- WooCommerce “Minha Conta” e demais pontos de login refletem o mesmo design.
- Todos os requisitos de segurança (HTTPS obrigatório, captcha, limites, validações, compatibilidade 2FA) estão ativos/ configuráveis.
- Configurações administrativas permitem ajustar logo, mensagens, redirecionamentos e integrações sem tocar no código.
- Código segue padrões WP, está traduzível e carrega assets apenas quando necessário.
- Experiência de usuário suave, responsiva, com animações sutis e feedback claro.

Com essas diretrizes, o plugin deverá oferecer uma experiência de login “absurdamente boa”, alinhando design premium, usabilidade e segurança reforçada para suportar o crescimento da loja WooCommerce.

## 11. Implementação
O diretório agora contém o plugin `UDI Custom Login`, com:
- Arquitetura em classes (`includes/`) cobrindo configurações, assets, formulários, segurança, shortcodes e integração WooCommerce.
- Shortcode `[udi_custom_login]`, template completo (`templates/`) e override automático da página definida nas configurações.
- Assets otimizados (`assets/css/login.css`, `assets/js/login.js`) com variáveis da paleta neon/dark.
- Hooks de segurança (HTTPS obrigatório, bloqueio por tentativas, reCAPTCHA opcional) e redirecionamentos pós-login/registro/logout.
- Página de configurações em `Configurações > UDI Login` permitindo definir logo, fundo, mensagens, chaves reCAPTCHA, limites de tentativas e URLs de redirecionamento.
- Customização da página “Minha Conta” com novo endpoint de histórico, cartões de acesso rápido e menu reorganizado controlado nas configurações.
