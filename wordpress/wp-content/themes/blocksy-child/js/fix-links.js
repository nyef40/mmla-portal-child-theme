/**
 * Fix dev URLs in front-end links (localhost in DB exports; missing :8080 on local Docker).
 * Uses URL parsing so we never turn http://localhost:8080 into http://localhost:8080:8080.
 */
document.addEventListener('DOMContentLoaded', function () {
    var home = (typeof mmlaFixLinks !== 'undefined' && mmlaFixLinks.homeUrl)
        ? mmlaFixLinks.homeUrl.replace(/\/$/, '')
        : '';

    function rewriteDevLocalhostHref(href) {
        if (!href || !home) {
            return href;
        }
        if (href.charAt(0) === '#' || href.indexOf('mailto:') === 0 || href.indexOf('tel:') === 0) {
            return href;
        }

        try {
            var parsed = new URL(href, window.location.origin);
            if (parsed.hostname !== 'localhost' && parsed.hostname !== '127.0.0.1') {
                return href;
            }
            var homeUrl = new URL(home + '/');
            parsed.protocol = homeUrl.protocol;
            parsed.hostname = homeUrl.hostname;
            parsed.port = homeUrl.port;
            return parsed.href;
        } catch (e) {
            return href;
        }
    }

    document.querySelectorAll('a[href]').forEach(function (link) {
        var href = link.getAttribute('href');
        if (!href) {
            return;
        }
        var fixed = rewriteDevLocalhostHref(href);
        if (fixed !== href) {
            link.setAttribute('href', fixed);
        }
    });

    document.querySelectorAll('.elementor-button[href]').forEach(function (button) {
        var href = button.getAttribute('href');
        if (!href) {
            return;
        }
        var fixed = rewriteDevLocalhostHref(href);
        if (fixed !== href) {
            button.setAttribute('href', fixed);
        }
    });
});
