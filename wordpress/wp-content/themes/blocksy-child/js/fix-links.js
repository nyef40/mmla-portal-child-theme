/**
 * Fix dev URLs in front-end links (localhost in DB exports; missing :8080 on local Docker).
 */
document.addEventListener('DOMContentLoaded', function () {
    var home = (typeof mmlaFixLinks !== 'undefined' && mmlaFixLinks.homeUrl)
        ? mmlaFixLinks.homeUrl.replace(/\/$/, '')
        : '';

    var devHostPatterns = [
        /^https?:\/\/localhost:8080/i,
        /^https?:\/\/localhost/i,
    ];

    function rewriteHref(href) {
        if (!href || !home) {
            return href;
        }
        var out = href;
        devHostPatterns.forEach(function (re) {
            if (re.test(out)) {
                out = out.replace(re, home);
            }
        });
        return out;
    }

    document.querySelectorAll('a[href]').forEach(function (link) {
        var href = link.getAttribute('href');
        if (!href) {
            return;
        }
        var fixed = rewriteHref(href);
        if (fixed !== href) {
            link.setAttribute('href', fixed);
        }
    });

    document.querySelectorAll('.elementor-button[href]').forEach(function (button) {
        var href = button.getAttribute('href');
        if (!href) {
            return;
        }
        var fixed = rewriteHref(href);
        if (fixed !== href) {
            button.setAttribute('href', fixed);
        }
    });

    // Local Docker: menu/export sometimes uses http://localhost/ without port.
    if (window.location.hostname === 'localhost' && window.location.port === '8080') {
        document.querySelectorAll('a[href^="http://localhost/"]').forEach(function (link) {
            link.href = link.href.replace('http://localhost/', 'http://localhost:8080/');
        });
    }
});
