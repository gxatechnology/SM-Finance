/* SMF responsive navigation v5 — desktop hover dropdown, smooth touch navigation. */
(function(){
  'use strict';
  var button=document.querySelector('.smf-mobile-menu-toggle');
  var nav=document.getElementById('smfMobileNav');
  if(button && nav){
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
    var lastDesktop=window.matchMedia('(min-width:1180px)');
    if(lastDesktop.addEventListener)lastDesktop.addEventListener('change',function(){setOpen(false)});
  }

  // Desktop Hover Behavior for "More" Dropdown with 200ms debounced close
  var desktopQuery=window.matchMedia('(min-width:1180px)');
  document.querySelectorAll('.smf-site-header .smf-more').forEach(function(details){
    var closeTimer=null;
    function isDesktop(){ return desktopQuery.matches; }

    details.addEventListener('mouseenter',function(){
      if(!isDesktop())return;
      if(closeTimer){clearTimeout(closeTimer);closeTimer=null;}
      details.setAttribute('open','');
    });

    details.addEventListener('mouseleave',function(){
      if(!isDesktop())return;
      if(closeTimer){clearTimeout(closeTimer);}
      closeTimer=setTimeout(function(){
        details.removeAttribute('open');
      },200);
    });

    document.addEventListener('click',function(e){
      if(!details.contains(e.target)){
        if(closeTimer){clearTimeout(closeTimer);closeTimer=null;}
        details.removeAttribute('open');
      }
    });

    details.querySelectorAll('.smf-more-item').forEach(function(link){
      link.addEventListener('click',function(){
        if(closeTimer){clearTimeout(closeTimer);closeTimer=null;}
        details.removeAttribute('open');
      });
    });
  });
})();
