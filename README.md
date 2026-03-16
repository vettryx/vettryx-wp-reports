# VETTRYX WP Reports

> ⚠️ **Atenção:** Este repositório agora atua exclusivamente como um **Submódulo** do ecossistema principal `VETTRYX WP Core`. Ele não deve mais ser instalado como um plugin standalone (isolado) nos clientes.

Este submódulo é responsável pela geração ágil e padronizada de relatórios mensais de manutenção de sites, permitindo a prestação de contas automatizada e o controle de SLA para contratos recorrentes da VETTRYX Tech.

## 🚀 Funcionalidades

* **Automação de Histórico:** Conecta-se nativamente ao submódulo *VETTRYX WP Audit Log* para puxar, processar e resumir em um clique todas as atualizações e edições feitas no ambiente durante o mês.
* **Controle de SLA:** Formulário dedicado para documentação de horas consumidas no período e checklist de conformidade (backups, segurança, uptime, atualizações).
* **Exportação em PDF Nativa:** Template de impressão estilizado em CSS via `@media print` que oculta o painel inteiro do WordPress e gera um documento limpo, corporativo e pronto para o cliente.

## ⚙️ Arquitetura e Deploy (CI/CD)

Este repositório não gera mais arquivos `.zip` para instalação manual. O fluxo de deploy é 100% automatizado:

1. Qualquer push na branch `main` deste repositório dispara um webhook (Repository Dispatch) para o repositório principal do Core.
2. O repositório do Core puxa este código atualizado para dentro da pasta `/modules/`.
3. O GitHub Actions do Core empacota tudo e gera uma única Release oficial.

## 🛠️ Como Usar

Uma vez que o **VETTRYX WP Core** esteja instalado e o módulo ativado no painel do cliente:

1. Acesse **VETTRYX Tech > Report Manager** no painel do WordPress.
2. Clique em **"Puxar Histórico Automático do Audit Log"** para preencher o descritivo de tarefas com os dados reais do mês vigente.
3. Preencha o SLA, ajuste o checklist e clique em **"Atualizar Dados do Relatório"**.
4. Clique em **"Salvar como PDF"** para imprimir/gerar o documento final para envio ao cliente.

---

**VETTRYX Tech**
*Transformando ideias em experiências digitais.*
