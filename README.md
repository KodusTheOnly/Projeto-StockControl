# Projeto-StockControl
### Integrantes:
* André
* Arlete
* David Kauan
* Filipe Peres
* Gabriel Costa Republicano
* Miguel Pedro
## Escopo do Produto
* RF01 - Gestão de Usuário da Empresa
    * RF01.1 – Cadastrar e autenticar usuário
    * RF01.2 – Redefinir senha
* RF02 -  Manter Cadastro de Produtos 
    * RF02.1 – Consultar produto por nome, código ou categoria
    * RF02.2 – Cadastrar novo produto (nome, categoria, fornecedor, preço, quantidade mínima)
    * RF02.3 – Editar informações de produto existente
    * RF02.4 – Excluir produto do cadastro
* RF03 - Página: Gerenciar Movimentações de Estoque - ANDRÉ
    * RF03.1 – Registrar entrada de mercadorias
    * RF03.2 – Registrar saída de mercadorias (vendas, perdas, transferências)
    * RF03.3 – Consultar histórico de movimentações
* RF04 - Página: Controle de Validade e Perdas - ARLETE
    * RF04.1 – Emitir alertas de produtos próximos do vencimento
    * RF04.2 – Registrar produtos descartados (perda por validade ou avaria)
    * RF04.3 – Gerar relatórios de perdas por período
* RF05 - Página: Relatórios Gerenciais - FILIPE
    * RF05.1 – Relatório de estoque atual por categoria
    * RF05.2 – Relatório de movimentações mensais
    * RF05.3 – Relatório de perdas e desperdícios
* RF06 - Gestão de Fornecedores - David
    * RF06.1 – Cadastrar fornecedores (nome, CNPJ/CPF, endereço, telefone, e-mail)
    * RF06.2 – Consultar fornecedores cadastrados
    * RF06.3 – Editar dados de fornecedores
    * RF06.4 – Excluir fornecedores
    * RF06.5 – Relacionar fornecedores aos produtos para rastrear origem e facilitar reposição
* RF07 - Etiquetagem e Rastreamento por QR Code - Gabriel 
    * RF07.1 - Gerar QR Code único por item ou lote no cadastro.
    * RF07.2 - Imprimir e associar etiqueta ao produto.
    * RF07.3 - Ler QR pela câmera para abrir o item e registrar entrada, saída ou descarte.
    * RF07.4 - Registrar trilha de auditoria por leitura com data, usuário e ação.
* RF08 - Área do Administrador - Miguel
    * RF08.1 - Usuários e papéis, com redefinição de senha
    * RF08.2 - Permissões por módulo e ação
    * RF08.3 - Parâmetros globais: estoque mínimo, validade, motivos de ajuste
    * RF08.4 - Cadastros mestres: fornecedores, locais, categorias, unidades
    * RF08.5 - Auditoria e logs com exportação
    * RF08.6 -Integrações API/webhooks e backup/importação CSV/Excel
