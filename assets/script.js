jQuery(document).ready(function($) {
    $('.area-card').on('click', function(e) {
        // Don't animate if it's a link (it will navigate)
        if ($(this).is('a')) {
            return true;
        }
        
        // Animate non-link cards
        $(this).css('animation', 'pulse 0.5s ease');
        setTimeout(() => {
            $(this).css('animation', '');
        }, 500);
    });
});
