// Preview en tiempo real
function updatePreview() {
  const titulo = document.getElementById('titulo').value;
  const desc   = document.getElementById('descripcion').value;
  document.getElementById('previewTitle').textContent = titulo || 'Tu título aparecerá aquí...';
  document.getElementById('previewTitle').style.fontStyle = titulo ? 'normal' : 'italic';
  document.getElementById('previewTitle').style.color = titulo ? 'var(--text)' : 'var(--text-muted)';
  document.getElementById('previewDesc').textContent = desc || 'Tu descripción aparecerá aquí...';
  document.getElementById('previewDesc').style.fontStyle = desc ? 'normal' : 'italic';
  document.getElementById('descCount').textContent = desc.length;
}

// Categoría preview
document.getElementById('categoria_id')?.addEventListener('change', function() {
  const opt   = this.options[this.selectedIndex];
  const icon  = opt.dataset.icon || 'fas fa-tag';
  const color = opt.dataset.color || 'var(--verde)';
  document.getElementById('previewCat').innerHTML = `<i class="${icon}" style="color:${color}"></i> ${opt.text}`;
});

// Editor enriquecido
const toolbar = document.getElementById('editorToolbar');
const editor  = document.getElementById('richEditor');

toolbar.addEventListener('click', e => {
  const btn = e.target.closest('[data-cmd]');
  if (!btn) return;
  e.preventDefault();
  const cmd = btn.dataset.cmd;
  editor.focus();
  if (cmd === 'h1') {
    document.execCommand('formatBlock', false, '<h1>');
  } else if (cmd === 'h2') {
    document.execCommand('formatBlock', false, '<h2>');
  } else if (cmd === 'h3') {
    document.execCommand('formatBlock', false, '<h3>');
  } else if (cmd === 'blockquote') {
    document.execCommand('formatBlock', false, '<blockquote>');
  } else if (cmd === 'codeBlock') {
    const sel = window.getSelection(); const text = sel.toString() || 'código';
    document.execCommand('insertHTML', false, `<pre><code>${text}</code></pre><p><br></p>`);
  } else if (cmd === 'infoBox') {
    document.execCommand('insertHTML', false, '<div class="info-box"><strong>ℹ️ Info:</strong> Escribe aquí.</div><p><br></p>');
  } else if (cmd === 'warningBox') {
    document.execCommand('insertHTML', false, '<div class="warning-box"><strong>⚠️ Importante:</strong> Escribe aquí.</div><p><br></p>');
  } else if (cmd === 'insertImage') {
    const url = prompt('URL de la imagen:');
    if (url) document.execCommand('insertHTML', false, `<img src="${url}" class="img-center" alt=""><p><br></p>`);
  } else if (cmd === 'insertTable') {
    const r = parseInt(prompt('Filas:','3'))||3, cl = parseInt(prompt('Columnas:','3'))||3;
    let t = '<table><thead><tr>' + Array(cl).fill(0).map((_,i)=>`<th>Col ${i+1}</th>`).join('') + '</tr></thead><tbody>';
    for(let i=0;i<r;i++){ t+='<tr>'+Array(cl).fill('<td>Dato</td>').join('')+'</tr>'; }
    document.execCommand('insertHTML', false, t+'</tbody></table><p><br></p>');
  } else if (cmd === 'insertHR') {
    document.execCommand('insertHTML', false, '<hr><p><br></p>');
  } else if (cmd === 'createLink') {
    const url = prompt('Ingresa la URL:');
    if (url) document.execCommand('createLink', false, url);
  } else if (cmd === 'foreColor') {
    const colors = ['#36c0a1','#ef7e22','#e74c3c','#3498db','#9b59b6','#f39c12','#27ae60','#000'];
    const pick = document.createElement('div');
    pick.style.cssText = 'position:fixed;z-index:9999;background:var(--bg-card);border:1px solid var(--border);border-radius:12px;padding:.75rem;display:flex;gap:.4rem;box-shadow:var(--shadow-lg)';
    const rect = btn.getBoundingClientRect();
    pick.style.left = rect.left+'px'; pick.style.top = (rect.bottom+8)+'px';
    pick.innerHTML = colors.map(c=>`<button onclick="document.execCommand('foreColor',false,'${c}');this.parentElement.remove()" style="width:26px;height:26px;border-radius:50%;background:${c};border:2px solid rgba(255,255,255,.2);cursor:pointer"></button>`).join('');
    document.body.appendChild(pick);
    setTimeout(()=>document.addEventListener('click',()=>pick.remove(),{once:true}),100);
    return;
  } else {
    document.execCommand(cmd, false, null);
  }
  // Toggle active state
  document.querySelectorAll('#editorToolbar [data-cmd]').forEach(b => b.classList.remove('active'));
  btn.classList.add('active');
});

// Imagen
function previewImage(input) {
  if (!input.files || !input.files[0]) return;
  const file = input.files[0];
  if (file.size > 5 * 1024 * 1024) { alert('La imagen no puede superar 5MB'); return; }
  const reader = new FileReader();
  reader.onload = e => {
    document.getElementById('imagePreview').src = e.target.result;
    document.getElementById('imagePreviewWrap').style.display = 'block';
    document.getElementById('imageUploadContent').style.display = 'none';
    document.getElementById('imageUploadArea').classList.add('has-image');
  };
  reader.readAsDataURL(file);
}
function removeImage() {
  document.getElementById('imagenFile').value = '';
  document.getElementById('imagePreviewWrap').style.display = 'none';
  document.getElementById('imageUploadContent').style.display = 'block';
  document.getElementById('imageUploadArea').classList.remove('has-image');
}

// Drag and drop en upload area
const uploadArea = document.getElementById('imageUploadArea');
uploadArea.addEventListener('dragover', e => { e.preventDefault(); uploadArea.style.borderColor = 'var(--verde)'; });
uploadArea.addEventListener('dragleave', () => { uploadArea.style.borderColor = ''; });
uploadArea.addEventListener('drop', e => {
  e.preventDefault();
  uploadArea.style.borderColor = '';
  const file = e.dataTransfer.files[0];
  if (file && file.type.startsWith('image/')) {
    document.getElementById('imagenFile').files = e.dataTransfer.files;
    previewImage(document.getElementById('imagenFile'));
  }
});

// Submit: sincronizar contenido del editor
document.getElementById('createForm').addEventListener('submit', function(e) {
  document.getElementById('contenido').value = editor.innerHTML;
  if (!editor.textContent.trim()) {
    e.preventDefault();
    editor.style.borderColor = '#e74c3c';
    editor.focus();
    return;
  }
});

// Color de acento opcional para la tarjeta (#2)
(function(){
  const acento = document.getElementById('colorAcento');
  const limpiar = document.getElementById('limpiarAcento');
  if (acento) {
    acento.dataset.activo = '0';
    acento.addEventListener('input', function(){ acento.dataset.activo = '1'; acento.style.outline = '2px solid var(--verde)'; });
  }
  if (limpiar) {
    limpiar.addEventListener('click', function(){
      if (acento) { acento.dataset.activo = '0'; acento.style.outline = 'none'; }
    });
  }
})();
