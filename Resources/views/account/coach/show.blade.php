@php($pageTitle = 'Titan Zero • Coach')
<div class="container-fluid">
  <div class="d-flex align-items-center justify-content-between mb-3">
    <div>
      <h3 class="mb-0">{{ $coach['name'] ?? 'Coach' }}</h3>
      <div class="text-muted small"><code>{{ $coach['key'] ?? '' }}</code></div>
    </div>
    <a class="btn btn-sm btn-outline-secondary" href="{{ route('titan.zero.coaches.index') }}">All Coaches</a>
  </div>

  <div class="card mb-3">
    <div class="card-body">
      <div class="mb-2">{{ $coach['description'] ?? '' }}</div>
      <div class="input-group">
        <input id="tzCoachQuestion" class="form-control" placeholder="Ask a question..." />
        <button class="btn btn-primary" id="tzCoachAskBtn">Ask</button>
      </div>
      <div class="text-muted small mt-2">
        Uses your document library and returns citations when available.
      </div>
    </div>
  </div>

  <div id="tzCoachResults"></div>
</div>

<script>
(function(){
  const btn = document.getElementById('tzCoachAskBtn');
  const input = document.getElementById('tzCoachQuestion');
  const out = document.getElementById('tzCoachResults');
  async function ask(){
    const q = (input.value||'').trim();
    if(!q) return;
    const res = await fetch("{{ route('titan.zero.coaches.ask', $coach['key']) }}", {
      method: "POST",
      headers: {
        "Content-Type":"application/json",
        "X-Requested-With":"XMLHttpRequest",
        "X-CSRF-TOKEN": (document.querySelector('meta[name="csrf-token"]')||{}).content || ""
      },
      body: JSON.stringify({question: q, page_context: {title: document.title, url: location.pathname}})
    });
    const data = await res.json();
    if(window.TitanZeroRenderCards){ window.TitanZeroRenderCards(out, data.cards || []); }
    else { out.innerHTML = "<pre>"+JSON.stringify(data,null,2)+"</pre>"; }
  }
  btn.addEventListener('click', ask);
  input.addEventListener('keydown', (e)=>{ if(e.key==='Enter') ask(); });
})();
</script>
