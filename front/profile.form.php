<?php
include("../../../inc/includes.php");

// Verifica se está logado e se tem permissão de gerenciar perfis
Session::checkLoginUser();
Session::checkRight("profile", UPDATE);

global $DB;

if (isset($_POST['update_matriz_right'])) {
    $profile_id = intval($_POST['profiles_id']);
    $right = (isset($_POST['matriz_read']) && $_POST['matriz_read'] == '1') ? 1 : 0;
    
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