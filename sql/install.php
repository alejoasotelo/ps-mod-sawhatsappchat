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
$sql = [];

foreach ($sql as $query) {
    if (Db::getInstance()->execute($query) == false) {
        return false;
    }
}
