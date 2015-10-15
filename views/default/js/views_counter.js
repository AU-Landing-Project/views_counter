define(['require', 'jquery'], function(require, $) {

    $('.views-counter-container').each(function(index, item) {
        var target = $(item).attr('data-target');
        console.log(target);
        if (target) {
            // if a target is set lets move the counter there
            $(this).removeClass('hidden');
            $(target).append($(this));
        }
    });
    
});