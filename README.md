# Plugin GLPI - Matriz de Permissões

Um plugin nativo para o GLPI (compatível com as versões 10 e 11) que gera uma visualização rápida e exportável das permissões dos usuários, cruzando Perfis e Grupos de acordo com as Entidades selecionadas.

Desenvolvido para facilitar a auditoria de acessos e a extração de relatórios estruturados.

## ✨ Funcionalidades

* **Geração de Matriz Visual:** Tabela dinâmica que exibe os usuários ativos/inativos e marca com um "X" os seus respectivos perfis e grupos.
* **Filtro Inteligente por Entidade:** Campos de seleção integrados com a API do Select2 nativa do GLPI. Ao selecionar a entidade do perfil, a entidade do grupo é sincronizada automaticamente.
* **UX Avançada (Sticky Columns):** Congelamento dinâmico da linha de cabeçalho e das colunas de identificação do usuário (Ativo, Usuário, Nome, Sobrenome), permitindo rolar matrizes extensas sem perder a referência.
* **Exportação para CSV:** Download da matriz gerada em formato `.csv` (codificação UTF-8) com um único clique, pronta para ser aberta no Excel ou planilhas.
* **Compatibilidade e Segurança:** Totalmente adaptado para o motor do GLPI 11, utilizando tipagem estrita do PHP 8 (`: bool`) e o novo sistema de tokens de sessão (`_glpi_csrf_token`).

## 📋 Pré-requisitos

* **GLPI:** Versão 10.0.0 ou superior (Testado e homologado no GLPI 11).
* **PHP:** Versão 8.0 ou superior.
* Acesso ao servidor web (terminal SSH) para ajuste de permissões.

## 🚀 Como Instalar

1. **Faça o download do plugin** e extraia os arquivos.
2. **Renomeie a pasta** obrigatoriamente para `matrizpermissoes` (sem caracteres especiais ou sublinhados, exigência do GLPI).
3. Envie a pasta para o diretório de plugins do seu servidor GLPI:
   ```bash
   /var/www/seu_glpi/plugins/matrizpermissoes

4. **Ajuste as permissões no servidor (Importante):**
   O servidor web precisa ter permissão de leitura para compilar o Autoloader. Acesse seu terminal e execute:
   ```bash
   sudo chown -R www-data:www-data /var/www/seu_glpi/plugins/matrizpermissoes
   sudo chmod -R 755 /var/www/seu_glpi/plugins/matrizpermissoes
   ```
   *(Nota: Se o seu servidor utilizar CentOS/RedHat, o usuário pode ser o `apache` em vez de `www-data`).*

5. **Limpe o Cache (Para garantir a leitura da classe no GLPI 11):**
   ```bash
   sudo -u www-data php /var/www/seu_glpi/bin/console cache:clear
   sudo systemctl restart apache2
   ```

6. **Ative no GLPI:**
   * Acesse o sistema com o perfil de Super-Admin.
   * Navegue até **Configurar > Plugins**.
   * Localize o "Matriz de Permissões", clique em **Instalar** e, em seguida, em **Habilitar**.

## 📖 Como Usar

1. No menu superior escuro do GLPI, vá em **Ferramentas > Matriz de Permissões**.
2. Selecione a entidade desejada nos campos disponíveis.
3. Clique em **Gerar Matriz Completa**.
4. Visualize os dados em tela ou clique em **Exportar para CSV** para fazer o download.

## 🛠️ Estrutura de Diretórios

* `setup.php` e `hook.php`: Inicialização e registros de hooks no ecossistema do GLPI.
* `inc/matriz.class.php`: Classe de controle principal e renderização de menu.
* `front/matriz.php`: Interface visual do gerador (seleção de entidades).
* `front/processa_matriz.php`: Motor de busca no banco de dados, geração da tabela HTML com UX avançada e exportação CSV.

## 📄 Licença

Este projeto está licenciado sob a licença GPLv2+, seguindo o padrão do framework GLPI.
