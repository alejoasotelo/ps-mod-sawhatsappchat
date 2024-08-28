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
document.addEventListener('DOMContentLoaded', function () {
    const currentUrl = window.location.href;
    const message = encodeURIComponent(sawhatsappchat.message.replace('%s', currentUrl));

    const whatsappLink = `https://wa.me/${sawhatsappchat.phone}?text=${message}`;

    const template = `
        <div id="sawhatsapp-button">
            <a href="${whatsappLink}" id="sawhatsapp-link" target="_blank">
                <img alt="WhatsApp" src="${sawhatsappchat.logo}" />
            </a>
        </div>
    `;

    document.body.insertAdjacentHTML('beforeend', template);
});
