<?php
if (!defined('GLPI_ROOT')) { die("Acesso negado"); }

class PluginMatrizpermissoesProfile extends CommonGLPI {

    static function getTypeName($nb = 0) {
        return 'Matriz de Permissões';
    }

    static function getIcon() {
        return "fas fa-table";
    }

    function getTabNameForItem(CommonGLPI $item, $withtemplate = 0) {
        if ($item->getType() == 'Profile') {
            // Usa o construtor oficial do GLPI para alinhar o ícone e o texto perfeitamente
            return self::createTabEntry(self::getTypeName(), 0, self::getIcon());
        }
        return '';
    }

    static function displayTabContentForItem(CommonGLPI $item, $tabnum = 1, $withtemplate = 0) {
        global $DB;
        $profile_id = $item->getID();

        // Busca o status atual no banco
        $current_right = 0;
        $res = $DB->request('glpi_profilerights', ['profiles_id' => $profile_id, 'name' => 'plugin_matrizpermissoes']);
        if ($row = $res->current()) {
            $current_right = $row['rights'];
        }

        // Desenha o Formulário
        $url_form = Plugin::getWebDir('matrizpermissoes') . '/front/profile.form.php';
        
        echo "<form method='post' action='$url_form'>";
        echo Html::hidden('_glpi_csrf_token', ['value' => Session::getNewCSRFToken()]);
        echo Html::hidden('profiles_id', ['value' => $profile_id]);
        echo Html::hidden('update_matriz_right', ['value' => 1]);
        
        echo "<div class='center'><table class='tab_cadre_fixehov'>";
        echo "<tr class='tab_bg_1'><th colspan='2'>Configuração de Acesso ao Plugin</th></tr>";
        echo "<tr class='tab_bg_2'><td class='center'>Pode visualizar a matriz de permissões?</td>";
        echo "<td class='center'><input type='checkbox' name='matriz_read' value='1' ".($current_right ? "checked" : "")."></td></tr>";
        
        echo "<tr class='tab_bg_2'><td colspan='2' class='center'>";
        echo Html::submit('Salvar', ['name' => 'update', 'class' => 'submit']);
        echo "</td></tr>";
        echo "</table></div>";
        Html::closeForm();

        return true;
    }
}