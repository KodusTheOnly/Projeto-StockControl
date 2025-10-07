[RF01] Gerenciar login do usuário
* Este RF também será responsável por gerenciar o login do usuário no sistema e pela *criação de nova senha. 
* Cada usuário terá um Login (email) e Senha (deve ser guardada criptografada no Banco de Dados. 
* Ao clicar no "esqueceu a senha (RF?) deve ser enviada uma mensagem para email de modo a permitir criar nova senha.
* Quando for manipulado os dados deve-se manter o nome do usuário que o manteve. (log para irrefutabilidade - não repúdio)
* Deve ser possível efetuar login por meio de autenticação federada (OAuth 2.0/OpenID Connect ), popularmente conhecido como "Entrar com Google".
* Ator: Usuário, Administrador
* Prioridade: (X) Essencial	( ) Importante	( ) Desejável
* Entradas e pré-condições: Validação do usuário, (e-mail)
* Saídas e pós-condições: Autenticação do usuário e ou autenticação da senha e(ou) recuperação da senha executadas.

# COMO SE CONECTAR
coloque a pasta no HTDOCS, e depois insira o link no browser:
http://localhost/Projeto-StockControl/RF01-Gestao-Usuario-da-Empresa/criar-conta.html
teste