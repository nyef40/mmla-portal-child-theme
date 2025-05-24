// Fix links that are missing port number
document.addEventListener('DOMContentLoaded', function() {
    // Fix all links on the page
    var links = document.querySelectorAll('a[href^="http://localhost/"]');
    links.forEach(function(link) {
        link.href = link.href.replace('http://localhost/', 'http://localhost:8080/');
    });
    
    // Fix Elementor buttons
    var buttons = document.querySelectorAll('.elementor-button');
    buttons.forEach(function(button) {
        if (button.href && button.href.indexOf('http://localhost/') === 0) {
            button.href = button.href.replace('http://localhost/', 'http://localhost:8080/');
        }
    });
});
