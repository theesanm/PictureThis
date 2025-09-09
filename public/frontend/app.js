async function apiGet(path){
  try{
    const res = await fetch('/api/' + path);
    if(!res.ok) throw new Error('API error');
    return await res.json();
  }catch(e){
    console.warn('API failed, loading sample-data.json', e);
    try{ const r = await fetch('/sample-data.json'); return await r.json(); }catch(_){ return []; }
  }
}

function escapeHtml(s){ return String(s||'').replace(/[&<>"']/g, c => ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":"&#39;"})[c]); }

function renderGallery(items){
  const root = document.getElementById('gallery');
  root.innerHTML = '';
  if(!items || items.length === 0){
    root.innerHTML = '<div class="text-gray-400">No images yet</div>';
    return;
  }
  items.forEach(it => {
    const div = document.createElement('div');
    div.className = 'bg-gray-800 rounded overflow-hidden';
    div.innerHTML = `\n      <img src="${it.image_url || '/placeholder-image.jpg'}" alt="${escapeHtml(it.title)}" class="w-full h-48 object-cover">\n      <div class="p-3">\n        <div class="font-medium">${escapeHtml(it.title)}</div>\n      </div>\n    `;
    root.appendChild(div);
  });
}

document.addEventListener('DOMContentLoaded', async ()=>{
  const items = await apiGet('search.php');
  renderGallery(items);
});
