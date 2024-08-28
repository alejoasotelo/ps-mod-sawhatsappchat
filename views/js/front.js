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
