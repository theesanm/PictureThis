// public/js/main.js
async function apiGet(path, params = {}){
  const url = '/api/' + path + (Object.keys(params).length ? ('?' + new URLSearchParams(params)) : '');
  const res = await fetch(url);
  if(!res.ok) throw new Error('API error');
  return res.json();
}

function renderGallery(items){
  const root = document.getElementById('gallery');
  root.innerHTML = '';
  items.forEach(it => {
    const div = document.createElement('div');
    div.className = 'card';
    div.innerHTML = `\n      <img src="${it.image_url || '/placeholder-image.jpg'}" alt="${escapeHtml(it.title || '')}">\n      <div>${escapeHtml(it.title || '')}</div>\n    `;
    root.appendChild(div);
  });
}

function escapeHtml(s){ return String(s).replace(/[&<>"']/g, c => ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":"&#39;"})[c]); }

document.addEventListener('DOMContentLoaded', async ()=>{
  try{
    const data = await apiGet('search.php');
    renderGallery(data);
  }catch(e){
    console.error(e);
  }
});
