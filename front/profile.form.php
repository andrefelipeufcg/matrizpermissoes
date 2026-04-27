<?php
include("../../../inc/includes.php");

// Verifica se quem está salvando tem permissão nativa para editar Perfis
Session::checkRight("profile", UPDATE);

// Validação de segurança do GLPI 10/11
Session::checkCSRF($_POST);

global $DB;

if (isset($_POST['update_matriz_right'])) {
    $profile_id = intval($_POST['profiles_id']);
    $right = isset($_POST['matriz_read']) ? 1 : 0;
    
    // Remove a permissão antiga
    $DB->delete('glpi_profilerights', [
        'profiles_id' => $profile_id,
        'name'        => 'plugin_matrizpermissoes'
    ]);
    
    // Insere o status atualizado (Garante que o 0 fique salvo se desmarcado)
    $DB->insert('glpi_profilerights', [
        'profiles_id' => $profile_id,
        'name'        => 'plugin_matrizpermissoes',
        'rights'      => $right
    ]);
    
    Html::back();
}