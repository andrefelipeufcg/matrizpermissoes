<?php

/**
 * Rotina de instalação
 * Aqui você criaria tabelas no banco de dados se fosse necessário.
 */
function plugin_matrizpermissoes_install() {
    // Como é um plugin de leitura, não precisamos alterar o banco de dados.
    return true;
}

/**
 * Rotina de desinstalação
 * Aqui você limparia as tabelas criadas na instalação.
 */
function plugin_matrizpermissoes_uninstall() {
    return true;
}