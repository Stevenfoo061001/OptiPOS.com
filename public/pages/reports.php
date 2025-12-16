<h3>Reports</h3>
<p>Sales (last transactions)</p>
<canvas id="salesChart" height="120"></canvas>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
async function renderReport(){
  const res = await fetch('/api/transactions.php'); const tx = await res.json();
  // group by date (YYYY-MM-DD)
  const map = {};
  tx.forEach(t=>{
    const d = new Date(t.created_at); const day = d.toISOString().slice(0,10);
    map[day] = (map[day] || 0) + (parseFloat(t.total) || 0);
  });
  const labels = Object.keys(map).sort();
  const data = labels.map(l => map[l]);
  new Chart(document.getElementById('salesChart'), { type: 'bar', data: { labels, datasets:[{ label:'Revenue', data }] }, options:{ responsive:true }});
}
renderReport();
</script>
