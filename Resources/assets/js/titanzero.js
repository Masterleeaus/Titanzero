(function () {
  function qs(sel){ return document.querySelector(sel); }

  function getContext(){
    const ctx = {
      url: window.location.href,
      title: document.title,
      headings: Array.from(document.querySelectorAll('h1,h2,h3')).slice(0,10).map(h=>h.innerText.trim()).filter(Boolean),
      forms: []
    };

    const forms = Array.from(document.querySelectorAll('form')).slice(0,3);
    forms.forEach((form)=>{
      const fields = Array.from(form.querySelectorAll('input,textarea,select')).slice(0,40).map(el=>{
        const type = (el.getAttribute('type')||'').toLowerCase();
        const name = el.getAttribute('name') || el.id || '';
        if(!name) return null;
        if(['password','hidden'].includes(type)) return null;
        if(name.toLowerCase().includes('token') || name.toLowerCase().includes('password')) return null;

        let value = '';
        if(el.tagName === 'SELECT'){
          value = el.options[el.selectedIndex]?.text || '';
        } else {
          value = (el.value || '').toString();
        }
        // light redaction
        if(value.length > 200) value = value.slice(0,200) + '…';
        return {name, type: el.tagName.toLowerCase(), value};
      }).filter(Boolean);
      if(fields.length) ctx.forms.push({fields});
    });

    return ctx;
  }


  function renderResults(cards){
    const host = qs('#tz-results');
    if(!host) return;
    host.innerHTML = '';
    (cards||[]).forEach(card=>{
      const wrap = document.createElement('div');
      wrap.className = 'tz-result-card';
      const title = document.createElement('div');
      title.className = 'tz-result-title';
      title.textContent = card.title || 'Result';
      wrap.appendChild(title);
      if(card.type === 'list'){
        const ul = document.createElement('ul');
        ul.className = 'tz-result-list';
        (card.items||[]).forEach(it=>{
          const li=document.createElement('li'); li.textContent = it; ul.appendChild(li);
        });
        wrap.appendChild(ul);
      } else if(card.type === 'suggestions'){
        const ul = document.createElement('ul');
        ul.className = 'tz-result-list';
        (card.suggestions||[]).forEach(s=>{
          const li=document.createElement('li');
          li.textContent = (s.field||'') + ': ' + (s.value||'') + (s.reason?(' — '+s.reason):'');
          ul.appendChild(li);
        });
        wrap.appendChild(ul);
      } else {
        const body = document.createElement('div');
        body.className = 'tz-result-body';
        body.textContent = card.body || '';
        wrap.appendChild(body);
      }
      host.appendChild(wrap);
    });
  }

  async function callAction(action){
    try{
      const res = await fetch('/titan-zero/action', {
        method: 'POST',
        headers: {'Content-Type':'application/json','X-Requested-With':'XMLHttpRequest','X-CSRF-TOKEN': (document.querySelector('meta[name="csrf-token"]')||{}).content || ''},
        body: JSON.stringify({action: action, context: getContext()})
      });
      const data = await res.json();
      if(data && data.cards){ renderResults(data.cards); }
      else { renderResults([{type:'text',title:'Error',body:'No response cards returned.'}]); }
    }catch(e){
      renderResults([{type:'text',title:'Error',body:String(e)}]);
    }
  }

  function renderContext(){
    const pre = qs('#tz-context-preview');
    if(!pre) return;
    pre.textContent = JSON.stringify(getContext(), null, 2);
  }

  function toggle(open){
    const panel = qs('#tz-panel');
    if(!panel) return;
    panel.setAttribute('aria-hidden', open ? 'false' : 'true');
    panel.classList.toggle('is-open', !!open);
    if(open) renderContext();
  }

  document.addEventListener('click', function(e){
    if(e.target && (e.target.id === 'tz-bubble-btn' || e.target.closest('#tz-bubble-btn'))){
      const panel = qs('#tz-panel');
      const open = panel && panel.classList.contains('is-open');
      toggle(!open);
    }
    if(e.target && (e.target.id === 'tz-panel-close' || e.target.closest('#tz-panel-close'))){
      toggle(false);
    }
    if(e.target && (e.target.id === 'tz-refresh-context' || e.target.closest('#tz-refresh-context'))){
      renderContext();
    }

    if(e.target && (e.target.id === 'tz-action-explain' || e.target.closest('#tz-action-explain'))){
      callAction('explain_page');
    }
    if(e.target && (e.target.id === 'tz-action-missing' || e.target.closest('#tz-action-missing'))){
      callAction('check_missing');
    }
    if(e.target && (e.target.id === 'tz-action-fill' || e.target.closest('#tz-action-fill'))){
      callAction('fill_form');
    }
    if(e.target && (e.target.id === 'tz-action-notes' || e.target.closest('#tz-action-notes'))){
      callAction('generate_notes');
    }

    if(e.target && (e.target.id === 'tz-ping' || e.target.closest('#tz-ping'))){
      fetch('/titan-zero/ping', {credentials:'same-origin'})
        .then(r=>r.json()).then(j=>alert('Titan Zero: ' + (j.status||'ok')))
        .catch(()=>alert('Titan Zero: ping failed'));
    }
  });

})();