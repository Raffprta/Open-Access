/* Code retrieved from: http://codepen.io/Thomas-Lebeau/pen/csHqx */

(function ($) {
  $('.spinner .btn:first-of-type').on('click', function() {
    $('.spinner input').val(Math.min(20, parseInt($('.spinner input').val(), 10) + 1));
  });
  $('.spinner .btn:last-of-type').on('click', function() {
    $('.spinner input').val(Math.max(1, parseInt($('.spinner input').val(), 10) - 1));
  });
})(jQuery);