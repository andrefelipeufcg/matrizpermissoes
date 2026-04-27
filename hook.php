<?php

/**
 * Rotina de instalação
 * Aqui você cria tabelas no banco de dados quando necessário.
 */
function plugin_matrizpermissoes_install() {
    global $DB;
    
    // Busca todos os perfis existentes no GLPI
    $profiles = $DB->request(['SELECT' => 'id', 'FROM' => 'glpi_profiles']);
    
    foreach ($profiles as $profile) {
        $profile_id = $profile['id'];
        
        // Verifica se já existe algum registro (seja 1=Liberado ou 0=Bloqueado)
        $iterator = $DB->request([
            'SELECT' => 'id',
            'FROM'   => 'glpi_profilerights',
            'WHERE'  => [
                'profiles_id' => $profile_id,
                'name'        => 'plugin_matrizpermissoes'
            ]
        ]);
        
        // Se for a primeira vez instalando, insere como 1 (Acesso Liberado) para TODOS
        if (count($iterator) == 0) {
            $DB->insert('glpi_profilerights', [
                'profiles_id' => $profile_id,
                'name'        => 'plugin_matrizpermissoes',
                'rights'      => 1
            ]);
        }
    }
    
    return true; 
}

/**
 * Rotina de desinstalação
 * Aqui você limparia as tabelas criadas na instalação.
 */
function plugin_matrizpermissoes_uninstall() {
    return true;
}