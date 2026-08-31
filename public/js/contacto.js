function updateMsgCount() {
  document.getElementById('msgCount').textContent = document.getElementById('cMensaje').value.length;
}
function resetForm() {
  document.getElementById('contactoForm').reset();
  document.getElementById('contactoForm').style.display = 'block';
  document.getElementById('contactoSuccess').style.display = 'none';
  document.getElementById('msgCount').textContent = '0';
}
document.getElementById('contactoForm').addEventListener('submit', async function(e) {
  e.preventDefault();
  const btn = document.getElementById('cSubmitBtn');
  btn.disabled = true;
  btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Enviando...';
  const data = {
    accion: 'enviar',
    nombre: document.getElementById('cNombre').value,
    email: document.getElementById('cEmail').value,
    asunto: document.getElementById('cAsunto').value,
    mensaje: document.getElementById('cMensaje').value
  };
  try {
    const r = await fetch('php/contacto.php', {
      method: 'POST',
      headers: {'Content-Type': 'application/json'},
      body: JSON.stringify(data)
    });
    const d = await r.json();
    if (d.success) {
      this.style.display = 'none';
      document.getElementById('contactoSuccess').style.display = 'block';
    } else {
      Toast.show(d.message || 'Error al enviar', 'error');
      btn.disabled = false;
      btn.innerHTML = '<i class="fas fa-paper-plane"></i> Enviar mensaje';
    }
  } catch(err) {
    Toast.show('Error de conexión', 'error');
    btn.disabled = false;
    btn.innerHTML = '<i class="fas fa-paper-plane"></i> Enviar mensaje';
  }
});
