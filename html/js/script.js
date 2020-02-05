"use strict";

$('form').submit(function(){
  var scroll_top = $(window).scrollTop();
  window.localStorage.setItem('is_post', true);
  window.localStorage.setItem('scrooll_top', scroll_top);
});

window.onload = function(){
    if (indow.localStorage.getItem('is_post')) {
        $(window).scrollTop(window.localStorage.getItem('scrooll_top'));
        window.localStorage.removeItem('is_post');
    }

}