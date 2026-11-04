/* SMF responsive navigation v4. Contact form logic stays in site.js. */
(function(){
  'use strict';
  var button=document.querySelector('.smf-mobile-menu-toggle');
  var nav=document.getElementById('smfMobileNav');
  if(!button||!nav)return;
  function setOpen(open){
    nav.classList.toggle('is-open',open);
    nav.hidden=!open;
    button.setAttribute('aria-expanded',String(open));
    button.setAttribute('aria-label',open?'Close navigation menu':'Open navigation menu');
    button.textContent=open?'✕':'☰';
  }
  setOpen(false);
  button.addEventListener('click',function(e){e.stopPropagation();setOpen(button.getAttribute('aria-expanded')!=='true')});
  nav.addEventListener('click',function(e){if(e.target.closest('a'))setOpen(false)});
  document.addEventListener('click',function(e){if(!e.target.closest('.smf-site-header'))setOpen(false)});
  document.addEventListener('keydown',function(e){if(e.key==='Escape'){setOpen(false);var opened=document.querySelector('.smf-more[open]');if(opened)opened.removeAttribute('open')}});
  document.querySelectorAll('.smf-site-header .smf-more').forEach(function(details){
    document.addEventListener('click',function(e){if(!details.contains(e.target))details.removeAttribute('open')});
  });
  var lastDesktop=window.matchMedia('(min-width:1180px)');
  if(lastDesktop.addEventListener)lastDesktop.addEventListener('change',function(){setOpen(false)});
})();
