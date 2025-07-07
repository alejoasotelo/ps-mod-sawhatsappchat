<?php
/**
 * Copyright since 2007 Alejo A. Sotelo <alejosotelo.com.ar>
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to a commercial license from Alejo A. Sotelo
 * Use, copy, modification or distribution of this source file without written
 * license agreement from Alejo A. Sotelo is strictly forbidden.
 * In order to obtain a license, please contact us: soporte@alejosotelo.com.ar
 *
 * @author    Alejo A. Sotelo <soporte@alejosotelo.com.ar>
 * @copyright Copyright (c) since 2007 Alejo Sotelo
 * @license   Commercial License
 */
if (!defined('_PS_VERSION_')) {
    exit;
}

/**
 * This function updates your module from previous versions to the version 1.1,
 * usefull when you modify your database, or register a new hook ...
 * Don't forget to create one file per version.
 */
function upgrade_module_1_1_0($module)
{
    $msgLogged = 'Hola {asesor_nombre}! Soy {cliente_nombre}. Te contacto desde {url}. Quería hacer una consulta:';
    $msgGuest = 'Hola {asesor_nombre}! Te contacto desde {url}. Quería hacer una consulta:';

    Configuration::updateValue('SAWHATSAPPCHAT_MESSAGE_GUEST', 'Hola, estoy en {url}');
    Configuration::updateValue('SAWHATSAPPCHAT_MESSAGE_LOGGED', 'Hola! Soy {cliente_nombre} estoy en {url}');
    Configuration::updateValue('SAWHATSAPPCHAT_MESSAGE_ASESOR_GUEST', $msgGuest);
    Configuration::updateValue('SAWHATSAPPCHAT_MESSAGE_ASESOR_LOGGED', $msgLogged);

    return true;
}
