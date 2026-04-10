<?php
// O nome das funções DEVE conter o nome exato da pasta do plugin (plugin_matrizpermissoes)

/**
 * Função principal de inicialização do plugin
 */
function plugin_init_matrizpermissoes() {
    global $PLUGIN_HOOKS;
    $PLUGIN_HOOKS['menu_toadd']['matrizpermissoes'] = ['tools' => 'PluginMatrizpermissoesMatriz'];
}

/**
 * Define a versão, autor e requisitos do plugin
 */
function plugin_version_matrizpermissoes() {
    return [
        'name'           => 'Matriz de Permissões',
        'version'        => '1.0.0',
        'author'         => 'andrefelipeufcg',
        'license'        => 'GPLv2+',
        'homepage'       => 'https://sti.ufcg.edu.br',
        'minGlpiVersion' => '10.0.0' // Funciona para a v10 e v11
    ];
}

/**
 * Verifica os pré-requisitos antes de deixar o usuário clicar em "Instalar"
 */
function plugin_matrizpermissoes_check_prerequisites() {
    if (version_compare(GLPI_VERSION, '10.0.0', '<')) {
        echo "Este plugin requer o GLPI 10.0.0 ou superior.";
        return false;
    }
    return true;
}

/**
 * Verifica se a configuração inicial está correta
 */
function plugin_matrizpermissoes_check_config() {
    return true;
}