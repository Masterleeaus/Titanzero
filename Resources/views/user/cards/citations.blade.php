<div class="tz-card">
  <div class="tz-card-title">{{ $title ?? 'Citations' }}</div>
  @if(!empty($items))
    <ul class="tz-citation-list">
      @foreach($items as $it)
        <li class="tz-citation">
          <div><strong>{{ $it['document_title'] ?? ('Document #'.$it['document_id']) }}</strong> — chunk {{ $it['chunk_index'] }}</div>
          <div class="text-muted small"><code>{{ $it['content_hash'] }}</code></div>
          @if(!empty($it['preview']))<div class="small mt-1">{{ $it['preview'] }}</div>@endif
        </li>
      @endforeach
    </ul>
  @else
    <div class="text-muted">No matching snippets found.</div>
  @endif
</div>
