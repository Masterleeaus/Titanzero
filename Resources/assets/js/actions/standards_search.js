(function(){
  function qs(sel){ return document.querySelector(sel); }

  async function postJSON(url, data){
    const res = await fetch(url, {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'X-Requested-With': 'XMLHttpRequest',
        'X-CSRF-TOKEN': (qs('meta[name="csrf-token"]')||{}).content || ''
      },
      body: JSON.stringify(data || {})
    });
    return res.json();
  }

  window.TitanZeroStandardsSearch = async function(query){
    return postJSON('/titan-zero/standards/search', {query});
  }
})(); 
