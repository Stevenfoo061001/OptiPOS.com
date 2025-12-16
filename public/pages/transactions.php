<h3>Transactions (History)</h3>
<div id="txTable"></div>

<script>
async function loadTx(){
  const res = await fetch('/api/transactions.php'); const data = await res.json();
  if (!data || data.length===0) { document.getElementById('txTable').innerHTML = '<div class="p-3 text-muted">No transactions yet</div>'; return; }
  const html = '<table class="table"><thead><tr><th>ID</th><th>Receipt</th><th>Total</th><th>Date</th></tr></thead><tbody>' + data.map(t=>`<tr><td>${t.id}</td><td>${t.receipt}</td><td>${t.total}</td><td>${new Date(t.created_at).toLocaleString()}</td></tr>`).join('') + '</tbody></table>';
  document.getElementById('txTable').innerHTML = html;
}
loadTx();
</script>
