# COMO SE CONECTAR
coloque a pasta no HTDOCS, e depois insira o link no browser:
http://localhost/Projeto-StockControl/criar-conta.html

# COMO APLICAR CONTROLE DE ACESSO NAS PÁGINAS:
No topo de qualquer página que exija login:
<?php
require_once 'auth.php';
require_login();
Para restringir apenas ao Administrador:
<?php
require_once 'auth.php';
require_role('ADMIN');
-> APLICAR NAS PÁGINAS FUTURAS DEPOIS DO REQUISITO 01 E REQUISITO 02
