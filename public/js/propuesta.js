// Mostrar sección de comentarios una vez cargue el detalle
ProposalDetail.esAdmin = document.body.dataset.esAdmin === 'true';
const origInit = ProposalDetail.init.bind(ProposalDetail);
ProposalDetail.init = async function() {
  await origInit();
  document.getElementById('commentsSection').style.display = 'block';
};
