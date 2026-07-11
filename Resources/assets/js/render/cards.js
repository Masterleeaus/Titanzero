(function(){
  function el(tag, cls){ const e=document.createElement(tag); if(cls) e.className=cls; return e; }

  window.TitanZeroRenderCards = function(container, cards){
    if(!container) return;
    container.innerHTML = '';
    (cards||[]).forEach(card=>{
      const wrap = el('div','tz-card');
      const title = el('div','tz-card-title');
      title.textContent = card.title || 'Result';
      wrap.appendChild(title);

      if(card.type === 'text'){
        const pre = el('pre','tz-pre');
        pre.textContent = card.content || '';
        wrap.appendChild(pre);
      } else if(card.type === 'citations'){
        const ul = el('ul','tz-citation-list');
        (card.items||[]).forEach(it=>{
          const li = el('li','tz-citation');
          li.innerHTML = '<div><strong>'+ (it.document_title || ('Document #'+it.document_id)) +'</strong> — chunk '+it.chunk_index+'</div>'
                       + '<div class="text-muted small"><code>'+it.content_hash+'</code></div>'
                       + (it.preview ? ('<div class="small mt-1">'+it.preview+'</div>') : '');
          ul.appendChild(li);
        });
        wrap.appendChild(ul);
      } else {
        const pre = el('pre','tz-pre');
        pre.textContent = JSON.stringify(card, null, 2);
        wrap.appendChild(pre);
      }

      container.appendChild(wrap);
    });
  }
})(); 
