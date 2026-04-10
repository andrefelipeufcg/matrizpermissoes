<?php
// 1. Inicializa o ambiente do GLPI (obrigatório em todos os arquivos 'front')
include ("../../../inc/includes.php");

// 2. Trava de segurança: Garante que só quem está logado pode acessar
Session::checkLoginUser();

// 3. Renderiza o cabeçalho nativo do GLPI
// Parâmetros: Título da aba, Caminho atual, Menu principal (Ferramentas), Nome do Plugin
Html::header('Matriz de Permissões', $_SERVER['PHP_SELF'], "tools", "PluginMatrizpermissoesMatriz");

// 4. Início da construção da Interface (Usando as classes CSS nativas do GLPI)
echo "<div class='center' style='margin-top: 20px;'>";
echo "<table class='tab_cadre_fixe'>";

// Título do quadro
echo "<tr><th colspan='2'>Gerador de Matriz de Permissões</th></tr>";

// Formulário que vai enviar os dados para o motor de processamento
echo "<form method='post' action='processa_matriz.php'>";

// Proteção CSRF nativa do GLPI (Segurança)
echo "<input type='hidden' name='_glpi_csrf_token' value='" . Session::getNewCSRFToken() . "'>";

// --- CAMPO 1: Entidade dos Perfis ---
echo "<tr class='tab_bg_1'>";
echo "<td width='30%'><strong>1. Entidade dos Perfis:</strong></td>";
echo "<td>";
// A mágica acontece aqui: Essa linha cria aquele Select com pesquisa (Select2)
// que já puxa todas as entidades do banco de dados automaticamente!
Entity::dropdown([
    'name' => 'entities_id_profiles', 
    'id'   => 'dropdown_entities_profiles'
]);
echo "</td>";
echo "</tr>";

// --- CAMPO 2: Entidade dos Grupos ---
echo "<tr class='tab_bg_1'>";
echo "<td><strong>2. Entidade dos Grupos:</strong></td>";
echo "<td>";
Entity::dropdown([
    'name' => 'entities_id_groups', 
    'id'   => 'dropdown_entities_groups'
]);
echo "</td>";
echo "</tr>";

// --- BOTÃO DE SUBMIT ---
echo "<tr class='tab_bg_2'>";
echo "<td colspan='2' class='center'>";
echo "<button type='submit' name='gerar_matriz' class='vsubmit'>Gerar Matriz Completa</button>";
echo "</td>";
echo "</tr>";

echo "</form>";
echo "</table>";
echo "</div>";

// 5. O Script de Sincronização de UX (Corrigido para a API do Select2 no GLPI)
echo "<script type='text/javascript'>
$(document).ready(function() {
    var \$selectPerfil = $('select[name=\"entities_id_profiles\"]');
    var \$selectGrupo = $('select[name=\"entities_id_groups\"]');

    \$selectPerfil.on('change', function() {
        var valorSelecionado = $(this).val();
        
        // A mágica: Em vez de buscar no HTML, buscamos o texto direto na memória do Select2
        var dadosSelect2 = $(this).select2('data');
        var textoSelecionado = (dadosSelect2 && dadosSelect2.length > 0) ? dadosSelect2[0].text : '';
        
        if (valorSelecionado && textoSelecionado != '') {
            // Verifica se a opção já existe no segundo campo
            if (\$selectGrupo.find(\"option[value='\" + valorSelecionado + \"']\").length === 0) {
                // Cria a nova opção com o texto correto
                var novaOpcao = new Option(textoSelecionado, valorSelecionado, true, true);
                \$selectGrupo.append(novaOpcao);
            }
            
            // Define o valor e atualiza o componente visual (trigger 'change' puro)
            \$selectGrupo.val(valorSelecionado).trigger('change');
        }
    });
});
</script>";

// 6. Renderiza o rodapé padrão do GLPI
Html::footer();
