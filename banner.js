(function () {
    const banner = document.getElementById('cookie-banner');
    if (!banner) return;

    const box = banner.querySelector('#cookie-box');
    const settings = banner.querySelector('[data-settings]');
    const error = box.querySelector('[role="status"]');
    const types = Array.from(box.querySelectorAll('[data-category]'));
    const cookie = 'cookie_banner_choices=';
    const choices = readChoices();

    function readChoices() {
        const entry = document.cookie.split(/;\s*/).find(part => part.startsWith(cookie));
        if (!entry) return null;

        try {
            const saved = JSON.parse(decodeURIComponent(entry.slice(cookie.length)));
            // Если состав категорий изменился, спрашиваем заново.
            if (!saved || Object.keys(saved).length !== types.length) return null;

            for (const input of types) {
                if (typeof saved[input.value] !== 'boolean') return null;
            }

            return saved;
        } catch {
            return null;
        }
    }

    function show(open) {
        box.hidden = !open;
        settings.hidden = open;
        settings.setAttribute('aria-expanded', String(open));
    }

    function save(action) {
        const picked = {};
        for (const input of types) {
            picked[input.value] = action === 'accept' || (action === 'save' && input.checked);
        }

        const entry = cookie + encodeURIComponent(JSON.stringify(picked));
        const secure = location.protocol === 'https:' ? '; Secure' : '';
        try {
            document.cookie = entry + '; Path=/; Max-Age=31536000; SameSite=Lax' + secure;
            if (document.cookie.split(/;\s*/).includes(entry)) {
                // Скрипты сайта прочитают новый выбор при загрузке.
                location.reload();
                return;
            }
        } catch {
            // Браузер может запретить запись.
        }

        error.hidden = false;
    }

    settings.onclick = function () {
        for (const input of types) {
            input.checked = choices?.[input.value] === true;
        }
        show(true);
        box.focus();
    };

    box.onkeydown = function (event) {
        if (event.key === 'Escape' && choices) {
            show(false);
            settings.focus();
        }
    };

    for (const button of box.querySelectorAll('[data-action]')) {
        button.onclick = function () {
            save(button.dataset.action);
        };
    }

    // Эту проверку вызывают перед загрузкой сторонних скриптов.
    window.cookieBanner = Object.freeze({
        allows(category) {
            return category === 'essential' || choices?.[category] === true;
        }
    });

    function loadScripts() {
        const data = document.getElementById('cookie-banner-scripts');
        if (!data) return;

        for (const [category, {url, code}] of Object.entries(JSON.parse(data.textContent))) {
            if (!window.cookieBanner.allows(category)) continue;

            function runCode() {
                if (!code) return;
                const script = document.createElement('script');
                script.textContent = code;
                document.head.append(script);
            }

            if (url) {
                const script = document.createElement('script');
                script.src = url;
                script.addEventListener('load', runCode, {once: true});
                document.head.append(script);
            } else {
                runCode();
            }
        }
    }

    show(!choices);
    document.dispatchEvent(new Event('cookie-banner-ready'));
    loadScripts();
})();
