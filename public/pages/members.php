<h3>Members (Manage)</h3>
<div class="mb-2">
  <button class="btn btn-sm btn-primary" id="btnAddMember">Add Member</button>
</div>

<div id="memberForm" class="card p-3 mb-3" style="display:none">
  <input id="m_name" class="form-control mb-1" placeholder="Name">
  <input id="m_phone" class="form-control mb-1" placeholder="Phone">
  <input id="m_email" class="form-control mb-1" placeholder="Email">
  <div><button id="saveMember" class="btn btn-success btn-sm">Save</button> <button id="cancelMember" class="btn btn-sm btn-secondary">Cancel</button></div>
  <div id="memberMsg" class="mt-2"></div>
</div>

<div id="memberTable"></div>

<script>
document.getElementById('btnAddMember').addEventListener('click', ()=>{ document.getElementById('memberForm').style.display='block';});
document.getElementById('cancelMember').addEventListener('click', ()=>{ document.getElementById('memberForm').style.display='none';});

async function loadMembers(){
  const res = await fetch('/api/members.php'); const data = await res.json();
  document.getElementById('memberTable').innerHTML = '<table class="table"><thead><tr><th>Name</th><th>Phone</th><th>Email</th></tr></thead><tbody>' + data.map(m=>`<tr><td>${m.name}</td><td>${m.phone}</td><td>${m.email}</td></tr>`).join('') + '</tbody></table>';
}
document.getElementById('saveMember').addEventListener('click', async ()=>{
  const payload = { name: document.getElementById('m_name').value.trim(), phone: document.getElementById('m_phone').value.trim(), email: document.getElementById('m_email').value.trim() };
  const res = await fetch('/api/members.php', { method:'POST', headers:{'Content-Type':'application/json'}, body: JSON.stringify(payload)});
  const j = await res.json();
  if (j.id) { document.getElementById('memberMsg').innerHTML = '<div class="alert alert-success">Member added</div>'; document.getElementById('memberForm').style.display='none'; loadMembers(); }
  else document.getElementById('memberMsg').innerHTML = '<div class="alert alert-danger">Error</div>';
});
loadMembers();
</script>
