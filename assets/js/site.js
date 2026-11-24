/* SMF site enhancements — safe navigation and honest enquiry responses */
(function () {
  'use strict';
  document.querySelectorAll('form[data-smf-enquiry]').forEach(function(form){
    form.addEventListener('submit',async function(event){
      event.preventDefault();
      var status=form.querySelector('.smf-form-status');var btn=form.querySelector('[type=submit]');
      if(!form.reportValidity())return;
      if(btn)btn.disabled=true;
      if(status){status.className='smf-form-status';status.textContent='Submitting your enquiry…';}
      try{
        var response=await fetch('api/contact.php',{method:'POST',body:new FormData(form),headers:{'Accept':'application/json'}});
        var data=await response.json();
        if(!response.ok || !data.success)throw new Error(data.message||'Unable to submit.');
        if(status){status.className='smf-form-status is-success';status.textContent='Your enquiry was received. Reference: '+data.reference;}
        form.reset();
      } catch(e){if(status){status.className='smf-form-status is-error';status.textContent='Enquiry was not saved: '+(e.message||'Network error')+' Please try again or contact the company.';}}
      finally{if(btn)btn.disabled=false;}
    });
  });
  // Existing Stitch calculator and FAQ scripts are retained where they illustrate general education only.
  // They do not quote actual company lending terms.

  // Graceful fallback for unapproved or withheld field photography
  window.addEventListener('error', function(event) {
    var target = event.target;
    if (target && target.tagName === 'IMG' && target.src && target.src.indexOf('company-activities-') !== -1) {
      target.onerror = null;
      target.src = 'assets/images/client/smf-og-image.webp';
    }
  }, true);
})();
