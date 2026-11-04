/* Static, human-authored English/Hindi content; no external translation service. */
(function(){
 'use strict';
 const storageKey='smf-preferred-language';
 let currentLanguage='en';
 try { if(localStorage.getItem(storageKey)==='hi') currentLanguage='hi'; }catch(e){}
 const en=document.getElementById('smfEnglishMain');
 const hi=document.getElementById('smfHindiMain');
 function translate(language,persist){
   const next=language==='hi'?'hi':'en';
   currentLanguage=next;
   document.documentElement.lang=next;
   if(en) en.hidden=(next==='hi');
   if(hi) hi.hidden=(next!=='hi');
   document.querySelectorAll('.smf-lang-btn').forEach(btn=>{
     const on=btn.dataset.lang===next;
     btn.setAttribute('aria-pressed',String(on));
   });
   document.querySelectorAll('[data-en][data-hi]').forEach(el=>{
     // Keep markup with decorative symbols and the official brand unmodified.
     const label=el.getAttribute('data-'+next);
     if(el.classList.contains('smf-nav-item') || el.classList.contains('smf-mobile-item') || el.classList.contains('smf-more-item') || el.classList.contains('smf-contact-btn') || el.tagName==='A'){
       if(label) el.textContent=label;
     } else if(el.tagName==='SUMMARY'){
       if(label)el.textContent=label+' ▾';
     } else if(label){el.textContent=label;}
   });
   const toggle=document.querySelector('.smf-mobile-menu-toggle');
   if(toggle) toggle.setAttribute('aria-label',next==='hi'?'नेविगेशन मेन्यू खोलें':'Open navigation menu');
   if(persist){try{localStorage.setItem(storageKey,next)}catch(e){}}
   // Set the page title to an appropriate localized title without rewriting the URL.
   const h1=hi?.querySelector('h1');
   if(next==='hi' && h1){document.title=h1.textContent+' | SMF'}
   else if(document.body.dataset.originalTitle){document.title=document.body.dataset.originalTitle}
 }
 document.body.dataset.originalTitle=document.title;
 document.querySelectorAll('.smf-lang-btn').forEach(b=>b.addEventListener('click',()=>translate(b.dataset.lang,true)));
 translate(currentLanguage,false);
 const result=document.getElementById('smfEmiResult');
 if(result){
   const update=()=>{
     const amount=Number(document.getElementById('smfEmiAmount')?.value||0), rate=Number(document.getElementById('smfEmiRate')?.value||0)/1200, months=Number(document.getElementById('smfEmiMonths')?.value||0);
     if(amount<=0||months<=0||months>360||rate<0||rate>1){result.textContent='कृपया सही संख्या भरें';return;}
     const emi=rate===0?amount/months:amount*rate*Math.pow(1+rate,months)/(Math.pow(1+rate,months)-1);
     result.textContent='अनुमानित EMI: '+new Intl.NumberFormat('hi-IN',{style:'currency',currency:'INR',maximumFractionDigits:0}).format(emi)+' / माह';
   };
   ['smfEmiAmount','smfEmiRate','smfEmiMonths'].forEach(id=>document.getElementById(id)?.addEventListener('input',update));update();
 }
})();
