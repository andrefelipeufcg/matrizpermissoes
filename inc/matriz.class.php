<?php

// Trava de segurança padrão do GLPI para impedir acesso direto ao arquivo
if (!defined('GLPI_ROOT')) {
    die("Desculpe. Você não pode acessar este arquivo diretamente");
}


/**
 * Classe principal do plugin.
 * A nomenclatura DEVE ser Plugin + NomeDoPlugin + NomeDaClasse
 */
class PluginMatrizpermissoesMatriz extends CommonGLPI {
    
    /**
     * Define o nome que vai aparecer no menu do GLPI
     */
    static function getMenuName() {
        return "Matriz de Permissões";
    }

    /**
     * Define o nome interno do tipo (padrão do framework)
     */
    static function getTypeName($nb = 0) {
        return "Matriz de Permissões";
    }

    /**
     * Define o ícone que vai aparecer ao lado do nome no menu (opcional na v10+)
     */
    static function getIcon() {
        return "fas fa-table"; // Ícone de tabela do FontAwesome
    }

    /**
     * Constrói o conteúdo do menu, indicando para qual página ele aponta
     */
    static function getMenuContent() {
        return [
            'title' => self::getMenuName(),
            'page'  => '/plugins/matrizpermissoes/front/matriz.php',
            'icon'  => self::getIcon()
        ];
    }

    /**
     * Controle de Acesso (Segurança)
     * Define quem pode ver este botão no menu.
     * CORREÇÃO: Adicionado o ": bool" exigido pelo PHP 8 / GLPI 11
     */
    public static function canView(): bool {
        return true; 
    }
}