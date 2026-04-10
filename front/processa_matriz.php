<?php
include ("../../../inc/includes.php");
Session::checkLoginUser();

// Verifica se o formulário original ou o botão de exportar foram acionados
if (!isset($_POST['gerar_matriz']) && !isset($_POST['exportar_csv'])) {
    Html::redirect("matriz.php");
    exit;
}

$is_export = isset($_POST['exportar_csv']);
$entidade_perfis = $_POST['entities_id_profiles'];
$entidade_grupos = $_POST['entities_id_groups'];

global $DB;

// =========================================================
// 1. BUSCA DE GRUPOS VÁLIDOS
// =========================================================
$iterator_grupos = $DB->request([
    'SELECT' => ['id', 'name'],
    'FROM'   => 'glpi_groups',
    'WHERE'  => ['entities_id' => $entidade_grupos]
]);

$dicionario_grupos = [];
$nomes_grupos = [];
foreach ($iterator_grupos as $linha) {
    $dicionario_grupos[$linha['id']] = $linha['name'];
    $nomes_grupos[] = $linha['name'];
}
sort($nomes_grupos);

// =========================================================
// 2. BUSCA DE PERFIS
// =========================================================
$iterator_perfis = $DB->request([
    'SELECT'     => ['pu.users_id', 'p.name AS profile_name'],
    'FROM'       => 'glpi_profiles_users AS pu',
    'INNER JOIN' => [
        'glpi_profiles AS p' => ['ON' => ['pu' => 'profiles_id', 'p' => 'id']]
    ],
    'WHERE'      => ['pu.entities_id' => $entidade_perfis]
]);

$mapa_usuarios = [];
$nomes_perfis = [];
foreach ($iterator_perfis as $linha) {
    $uid = $linha['users_id'];
    $nome_perfil = $linha['profile_name'];
    
    if (!isset($mapa_usuarios[$uid])) {
        $mapa_usuarios[$uid] = ['perfis' => [], 'grupos' => []];
    }
    $mapa_usuarios[$uid]['perfis'][$nome_perfil] = true;
    
    if (!in_array($nome_perfil, $nomes_perfis)) {
        $nomes_perfis[] = $nome_perfil;
    }
}
sort($nomes_perfis);

// =========================================================
// 3. BUSCA DE VÍNCULOS DE GRUPOS
// =========================================================
if (!empty($mapa_usuarios) && !empty($dicionario_grupos)) {
    $iterator_vinculos_grupos = $DB->request([
        'SELECT' => ['users_id', 'groups_id'],
        'FROM'   => 'glpi_groups_users',
        'WHERE'  => [
            'users_id'  => array_keys($mapa_usuarios),
            'groups_id' => array_keys($dicionario_grupos)
        ]
    ]);
    foreach ($iterator_vinculos_grupos as $linha) {
        $nome_curto_grupo = $dicionario_grupos[$linha['groups_id']];
        $mapa_usuarios[$linha['users_id']]['grupos'][$nome_curto_grupo] = true;
    }
}

// =========================================================
// 4. BUSCA DE DADOS CADASTRAIS
// =========================================================
if (!empty($mapa_usuarios)) {
    $iterator_users = $DB->request([
        'SELECT' => ['id', 'name AS login', 'firstname', 'realname', 'is_active'],
        'FROM'   => 'glpi_users',
        'WHERE'  => ['id' => array_keys($mapa_usuarios)]
    ]);
    foreach ($iterator_users as $linha) {
        $uid = $linha['id'];
        $mapa_usuarios[$uid]['login']     = $linha['login'];
        $mapa_usuarios[$uid]['firstname'] = $linha['firstname'];
        $mapa_usuarios[$uid]['realname']  = $linha['realname'];
        $mapa_usuarios[$uid]['ativo']     = $linha['is_active'] ? 'Sim' : 'Não';
    }
}

// Ordenar os usuários em ordem alfabética (Nome + Sobrenome)
uasort($mapa_usuarios, function($a, $b) {
    $nomeA = strtolower(trim(($a['firstname'] ?? '') . ' ' . ($a['realname'] ?? '')));
    $nomeB = strtolower(trim(($b['firstname'] ?? '') . ' ' . ($b['realname'] ?? '')));
    return strcmp($nomeA, $nomeB);
});

// =========================================================
// 5. MODO EXPORTAÇÃO (Se o botão de Download foi clicado)
// =========================================================
if ($is_export) {
    $nome_arquivo = "matriz_permissoes_" . date("Ymd_His") . ".csv";
    header('Content-Type: text/csv; charset=UTF-8');
    header('Content-Disposition: attachment; filename="' . $nome_arquivo . '"');
    header('Pragma: no-cache');
    header('Expires: 0');

    $output = fopen('php://output', 'w');
    fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF)); 

    $cabecalho = array_merge(['Ativo', 'Usuário', 'Nome', 'Sobrenome'], $nomes_perfis, $nomes_grupos);
    fputcsv($output, $cabecalho, ';'); 

    foreach ($mapa_usuarios as $uid => $dados) {
        $linha = [$dados['ativo'] ?? 'Não', $dados['login'] ?? '', $dados['firstname'] ?? '', $dados['realname'] ?? ''];
        foreach ($nomes_perfis as $p) $linha[] = isset($dados['perfis'][$p]) ? 'X' : '';
        foreach ($nomes_grupos as $g) $linha[] = isset($dados['grupos'][$g]) ? 'X' : '';
        fputcsv($output, $linha, ';');
    }
    fclose($output);
    exit;
}

// =========================================================
// 6. MODO VISUALIZAÇÃO (Tela HTML do GLPI)
// =========================================================
Html::header('Matriz de Permissões', $_SERVER['PHP_SELF'], "tools", "PluginMatrizpermissoesMatriz");

// --- ESTILOS CSS PARA TRAVAR AS COLUNAS E A LINHA DO TOPO ---
echo "<style>
    /* 1. Colunas fixas na esquerda */
    .freeze-col {
        position: -webkit-sticky;
        position: sticky;
        z-index: 2; /* Acima dos dados comuns da tabela */
        background-color: #f4f4f4;
    }
    
    /* 2. NOVA MÁGICA: Linha de cabeçalhos travada no topo */
    .headerRow th {
        position: -webkit-sticky;
        position: sticky;
        top: 0; /* O segredo que trava no teto */
        z-index: 3; /* Acima dos dados rolando para cima */
        box-shadow: 0px 2px 4px -1px rgba(0,0,0,0.2); /* Sombrinha embaixo da linha */
    }
    
    /* 3. O Cruzamento Exato (Canto superior esquerdo) precisa ser o maior de todos */
    .headerRow th.freeze-col {
        z-index: 4; /* Fica acima das colunas (z:2) e da linha de cabeçalho (z:3) */
        background-color: #e0e0e0; 
        color: #333;
    }
    
    /* 4. Sombra lateral */
    .freeze-shadow {
        border-right: 1px solid #999;
        box-shadow: 3px 0px 5px -1px rgba(0,0,0,0.2);
    }
</style>";

echo "<div class='center' style='margin-top: 20px; width: 95%; margin-left: auto; margin-right: auto;'>";

$total_usuarios = count($mapa_usuarios);

// Painel Superior: Totalizador e Botões
echo "<div style='display: flex; justify-content: space-between; align-items: center; margin-bottom: 15px; padding: 10px; background: #f8f9fa; border: 1px solid #ddd; border-radius: 4px;'>";
    echo "<div style='font-size: 15px; font-weight: bold; color: #333;'>";
        echo "<i class='fas fa-users' style='margin-right: 5px; color: #1d5ea3;'></i> Total de usuários encontrados: <span style='color: #1d5ea3; font-size: 16px;'>" . $total_usuarios . "</span>";
    echo "</div>";

    echo "<div style='display: flex; gap: 10px;'>";
        echo "<a href='matriz.php' class='vsubmit' style='background-color: #555555; text-decoration: none; padding: 5px 15px; display: inline-flex; align-items: center;' title='Voltar para a seleção de entidades'>⬅️ Voltar</a>";

        echo "<form method='post' action='processa_matriz.php' style='margin: 0;'>";
            // CORREÇÃO: Usando a forma correta para o GLPI 11
            echo "<input type='hidden' name='_glpi_csrf_token' value='" . Session::getNewCSRFToken() . "'>";
            echo "<input type='hidden' name='entities_id_profiles' value='$entidade_perfis'>";
            echo "<input type='hidden' name='entities_id_groups' value='$entidade_grupos'>";
            echo "<button type='submit' name='exportar_csv' value='1' class='vsubmit' style='background-color: #2e7d32;' title='Fazer o download da tabela em formato CSV'>📥 Exportar para CSV</button>";
        echo "</form>";
    echo "</div>";
echo "</div>";

// --- PAINEL DE FILTROS DINÂMICOS ---
echo "<div style='margin-bottom: 15px; padding: 15px; background: #f8f9fa; border: 1px solid #ddd; border-radius: 4px;'>";

// O Botão de Toggle
echo "<div id='btn-toggle-filtro' style='cursor: pointer; text-align: center; color: #1d5ea3; font-weight: bold; font-size: 14px; padding: 5px;'>";
echo "<i class='fas fa-filter'></i> Ocultar/Mostrar Colunas (Filtro Visual) <i class='fas fa-caret-down'></i>";
echo "</div>";

// O Conteúdo do Filtro (oculto por padrão)
echo "<div id='conteudo-filtro' style='display: none; border-top: 1px solid #ccc; margin-top: 10px; padding-top: 15px;'>";
echo "<div style='display: flex; gap: 20px; flex-wrap: wrap;'>";

$col_index = 4; // As 4 primeiras colunas são fixas (0 a 3)

// Caixa de Filtros de Perfis
echo "<div style='flex: 1; min-width: 250px; text-align: left;'>";
echo "<div style='display: flex; justify-content: space-between; align-items: center; margin-bottom: 5px;'>";
echo "<strong style='color: #555;'>Perfis:</strong>";
echo "<div style='font-size: 12px;'>";
echo "<a href='#' class='acao-massa-perfil' data-acao='marcar' style='color: #1d5ea3; text-decoration: none;'>Marcar Todos</a> | ";
echo "<a href='#' class='acao-massa-perfil' data-acao='desmarcar' style='color: #990000; text-decoration: none;'>Desmarcar Todos</a>";
echo "</div></div>";

echo "<div id='caixa-perfis' style='max-height: 120px; overflow-y: auto; border: 1px solid #ccc; padding: 8px; background: #fff; border-radius: 3px;'>";
foreach ($nomes_perfis as $p) {
    echo "<label style='display: block; margin-bottom: 4px; cursor: pointer; font-size: 13px; text-align: left;'>";
    echo "<input type='checkbox' class='col-filter' data-colindex='$col_index' checked style='margin-right: 5px;'> $p";
    echo "</label>";
    $col_index++;
}
echo "</div></div>";

// Caixa de Filtros de Grupos
echo "<div style='flex: 1; min-width: 250px; text-align: left;'>";
echo "<div style='display: flex; justify-content: space-between; align-items: center; margin-bottom: 5px;'>";
echo "<strong style='color: #555;'>Grupos:</strong>";
echo "<div style='font-size: 12px;'>";
echo "<a href='#' class='acao-massa-grupo' data-acao='marcar' style='color: #1d5ea3; text-decoration: none;'>Marcar Todos</a> | ";
echo "<a href='#' class='acao-massa-grupo' data-acao='desmarcar' style='color: #990000; text-decoration: none;'>Desmarcar Todos</a>";
echo "</div></div>";

echo "<div id='caixa-grupos' style='max-height: 120px; overflow-y: auto; border: 1px solid #ccc; padding: 8px; background: #fff; border-radius: 3px;'>";
foreach ($nomes_grupos as $g) {
    echo "<label style='display: block; margin-bottom: 4px; cursor: pointer; font-size: 13px; text-align: left;'>";
    echo "<input type='checkbox' class='col-filter' data-colindex='$col_index' checked style='margin-right: 5px;'> $g";
    echo "</label>";
    $col_index++;
}
echo "</div></div>";

echo "</div>"; // Fim do flex container
echo "</div>"; // Fim do conteudo-filtro
echo "</div>"; // Fim do painel principal

// A Tabela
// DICA DE OURO: border-collapse: separate garante que as colunas sticky não percam as bordas
echo "<div style='overflow-x: auto; max-height: 70vh; box-shadow: 0 0 5px rgba(0,0,0,0.1);'>";
echo "<table class='tab_cadre_fixehov' style='margin: 0; width: 100%; border-collapse: separate; border-spacing: 0;'>";

// Cabeçalhos
echo "<tr class='headerRow'>";
// Aplicando a classe freeze e marcando o índice da coluna
echo "<th class='freeze-col' data-colindex='0'>Ativo</th>";
echo "<th class='freeze-col' data-colindex='1'>Usuário</th>";
echo "<th class='freeze-col' data-colindex='2'>Nome</th>";
echo "<th class='freeze-col freeze-shadow' data-colindex='3'>Sobrenome</th>";

foreach ($nomes_perfis as $p) echo "<th style='background-color: #999999; color: white; white-space: nowrap;'>$p</th>";
foreach ($nomes_grupos as $g) echo "<th style='background-color: #0b5394; color: white; white-space: nowrap;'>$g</th>";
echo "</tr>";

// Linhas de Dados
foreach ($mapa_usuarios as $uid => $dados) {
    echo "<tr class='tab_bg_1'>";
    
    $cor_ativo = ($dados['ativo'] === 'Sim') ? 'color: #274e13; font-weight: bold;' : 'color: #990000;';

    // Travando as 4 primeiras colunas com os mesmos índices dos cabeçalhos
    echo "<td class='center freeze-col' data-colindex='0' style='$cor_ativo'>" . ($dados['ativo'] ?? 'Não') . "</td>";
    echo "<td class='freeze-col' data-colindex='1' style='white-space: nowrap;'>" . ($dados['login'] ?? '') . "</td>";
    echo "<td class='freeze-col' data-colindex='2' style='white-space: nowrap;'>" . ($dados['firstname'] ?? '') . "</td>";
    echo "<td class='freeze-col freeze-shadow' data-colindex='3' style='white-space: nowrap;'>" . ($dados['realname'] ?? '') . "</td>";
    
    foreach ($nomes_perfis as $p) {
        $marca = isset($dados['perfis'][$p]) ? "<b style='color: #333;'>X</b>" : "";
        echo "<td class='center'>$marca</td>";
    }
    
    foreach ($nomes_grupos as $g) {
        $marca = isset($dados['grupos'][$g]) ? "<b style='color: #0b5394;'>X</b>" : "";
        echo "<td class='center'>$marca</td>";
    }
    
    echo "</tr>";
}

echo "</table>";
echo "</div>"; 
echo "</div>";

// --- SCRIPT DE CÁLCULO DINÂMICO E FILTROS ---
echo "<script type='text/javascript'>
$(document).ready(function() {
    var leftPositions = [];
    var currentLeft = 0;
    
    // 1. Lê a largura exata de cada cabeçalho fixado
    $('.headerRow th.freeze-col').each(function() {
        leftPositions.push(currentLeft);
        currentLeft += $(this).outerWidth();
    });

    // 2. Aplica a distância 'left' correta para cada célula
    $('.freeze-col').each(function() {
        var index = $(this).data('colindex');
        $(this).css('left', leftPositions[index] + 'px');
    });

    // 3. FILTRO INSTANTÂNEO (COLUNAS E LINHAS)
    $('.col-filter').on('change', function() {
        // A. Esconde ou mostra a coluna inteira
        var colIndex = $(this).data('colindex');
        var isVisible = $(this).is(':checked');
        var nth = colIndex + 1; 
        
        if (isVisible) {
            $('.tab_cadre_fixehov tr').find('th:nth-child(' + nth + '), td:nth-child(' + nth + ')').show();
        } else {
            $('.tab_cadre_fixehov tr').find('th:nth-child(' + nth + '), td:nth-child(' + nth + ')').hide();
        }

        // B. Descobre quais colunas de perfil/grupo ainda estão ativadas no filtro
        var colunasVisiveis = [];
        $('.col-filter:checked').each(function() {
            colunasVisiveis.push($(this).data('colindex') + 1);
        });

        // C. Varre todos os usuários. Se não tiver um 'X' visível, esconde a pessoa.
        $('.tab_cadre_fixehov tr.tab_bg_1').each(function() {
            var $linha = $(this);
            var linhaTemX = false;

            if (colunasVisiveis.length > 0) {
                for (var i = 0; i < colunasVisiveis.length; i++) {
                    var textoCelula = $linha.find('td:nth-child(' + colunasVisiveis[i] + ')').text().trim();
                    if (textoCelula === 'X') {
                        linhaTemX = true;
                        break; 
                    }
                }
            }

            if (linhaTemX) {
                $linha.show();
            } else {
                $linha.hide();
            }
        });
    });

    // Efeito de abrir e fechar a caixa de filtros
    $('#btn-toggle-filtro').on('click', function() {
        $('#conteudo-filtro').slideToggle('fast');
    });

    // Marcar/Desmarcar todos os Perfis
    $('.acao-massa-perfil').on('click', function(e) {
        e.preventDefault(); // Impede a tela de pular pro topo
        var marcar = $(this).data('acao') === 'marcar';
        $('#caixa-perfis .col-filter').prop('checked', marcar).trigger('change');
    });

    // Marcar/Desmarcar todos os Grupos
    $('.acao-massa-grupo').on('click', function(e) {
        e.preventDefault();
        var marcar = $(this).data('acao') === 'marcar';
        $('#caixa-grupos .col-filter').prop('checked', marcar).trigger('change');
    });

});
</script>";

Html::footer();