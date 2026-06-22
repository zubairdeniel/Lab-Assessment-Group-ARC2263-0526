'use strict';

let currentPage = 1;
const PAGE_SIZE = 6;
let filteredStudents = [];
let deleteTargetId = null;

document.addEventListener('DOMContentLoaded', () => {
  fetchAndRender();
});

function showView(name) {
  document.querySelectorAll('.view').forEach(v => v.classList.remove('active'));
  document.querySelectorAll('.nav-btn').forEach(b => {
    if (b.dataset.view) b.classList.toggle('active', b.dataset.view === name);
  });
  document.getElementById(`view-${name}`).classList.add('active');
}

async function fetchAndRender() {
  try {
const res = await fetch('client_api.php?action=list');  
  if (!res.ok) throw new Error('Failed to fetch');
    const json = await res.json();
    if (!json.success) throw new Error(json.message);
    filteredStudents = json.data || [];
    currentPage = 1;
    renderTable();
  } catch (e) {
    console.error(e);
  }
}

function renderTable() {
  const tbody = document.getElementById('studentBody');
  const empty = document.getElementById('emptyState');
  tbody.innerHTML = '';

  const total = filteredStudents.length;
  const start = (currentPage - 1) * PAGE_SIZE;
  const page = filteredStudents.slice(start, start + PAGE_SIZE);

  if (!total) {
    empty.classList.remove('hidden');
    renderPagination(0);
    return;
  }
  empty.classList.add('hidden');

  page.forEach(s => {
    const tr = document.createElement('tr');
    tr.innerHTML = `
      <td class="id-cell">${esc(s.student_number)}</td>
      <td class="name-cell">${esc(s.first_name)} ${esc(s.last_name)}</td>
      <td>${esc(s.email)}</td>
      <td>${esc(s.course)}</td>
      <td>Yr ${s.year_level}</td>
      <td>${s.gpa ? Number(s.gpa).toFixed(2) : '—'}</td>
      <td><span class="badge badge-${(s.status||'').toLowerCase()}">${esc(s.status)}</span></td>
      <td>
        <div class="action-btns">
          <button class="btn-edit" onclick="startEdit(${s.id})">Edit</button>
          <button class="btn-del" onclick="startDelete(${s.id})">Delete</button>
        </div>
      </td>`;
    tbody.appendChild(tr);
  });

  renderPagination(total);
}

function renderPagination(total) {
  const pg = document.getElementById('pagination');
  pg.innerHTML = '';
  const pages = Math.ceil(total / PAGE_SIZE);
  if (pages <= 1) return;
  for (let i = 1; i <= pages; i++) {
    const btn = document.createElement('button');
    btn.className = 'pg-btn' + (i === currentPage ? ' active' : '');
    btn.textContent = i;
    btn.onclick = () => { currentPage = i; renderTable(); };
    pg.appendChild(btn);
  }
}

function filterTable() {
  const q = document.getElementById('searchInput').value.toLowerCase().trim();
  const all = [];
  
  // Fetch from server with search
  fetch(`api.php?action=list&search=${encodeURIComponent(q)}`)
    .then(r => r.json())
    .then(json => {
      if (json.success) {
        filteredStudents = json.data || [];
        currentPage = 1;
        renderTable();
      }
    })
    .catch(e => console.error(e));
}

function startEdit(id) {
  const s = filteredStudents.find(x => x.id === id);
  if (!s) return;

  document.getElementById('studentId').value = s.id;
  document.getElementById('firstName').value = s.first_name || '';
  document.getElementById('lastName').value = s.last_name || '';
  document.getElementById('dob').value = s.dob || '';
  document.getElementById('gender').value = s.gender || '';
  document.getElementById('address').value = s.address || '';
  document.getElementById('email').value = s.email || '';
  document.getElementById('phone').value = s.phone || '';
  document.getElementById('studentNum').value = s.student_number || '';
  document.getElementById('course').value = s.course || '';
  document.getElementById('yearLevel').value = s.year_level || '';
  document.getElementById('status').value = s.status || 'Active';

  document.getElementById('formTitle').textContent = 'Edit Student';
  document.getElementById('submitLabel').textContent = 'Save Changes';
  clearErrors();
  showView('add');
}

function startDelete(id) {
  deleteTargetId = id;
  document.getElementById('deleteModal').classList.remove('hidden');
}

function closeModal() {
  deleteTargetId = null;
  document.getElementById('deleteModal').classList.add('hidden');
}

async function confirmDelete() {
  if (!deleteTargetId) return;
  try {
    const res = await fetch('api.php?action=delete', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ id: deleteTargetId })
    });
    const json = await res.json();
    if (!json.success) throw new Error(json.message);
    closeModal();
    filteredStudents = filteredStudents.filter(s => s.id !== deleteTargetId);
    currentPage = 1;
    renderTable();
    showView('list');
  } catch (e) {
    alert('Error: ' + e.message);
  }
}

async function handleSubmit(e) {
  e.preventDefault();
  if (!validateForm()) return;

  const id = document.getElementById('studentId').value;
  const payload = {
    id: parseInt(id) || null,
    first_name: v('firstName'),
    last_name: v('lastName'),
    dob: v('dob'),
    gender: v('gender'),
    address: v('address'),
    email: v('email'),
    phone: v('phone'),
    student_number: v('studentNum'),
    course: v('course'),
    year_level: parseInt(v('yearLevel')) || null,
    status: v('status'),
  };

  const btn = document.querySelector('.btn-primary');
  btn.disabled = true;

  try {
    const action = id ? 'update' : 'create';
    const res = await fetch(`api.php?action=${action}`, {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify(payload)
    });
    const json = await res.json();
    if (!json.success) throw new Error(json.message);
    showMsg('success', id ? 'Updated' : 'Created');
    setTimeout(() => { resetForm(); showView('list'); fetchAndRender(); }, 900);
  } catch (e) {
    showMsg('error', 'Error: ' + e.message);
  } finally {
    btn.disabled = false;
  }
}

const RULES = {
  firstName: { label: 'First Name', required: true, minLen: 2 },
  lastName: { label: 'Last Name', required: true, minLen: 2 },
  dob: { label: 'DOB', required: true },
  gender: { label: 'Gender', required: true },
  email: { label: 'Email', required: true, isEmail: true },
  phone: { label: 'Phone', required: true, minLen: 8 },
  studentNum: { label: 'Student Number', required: true, minLen: 3 },
  course: { label: 'Course', required: true },
  yearLevel: { label: 'Year', required: true },
  status: { label: 'Status', required: true },
};

function validateForm() {
  clearErrors();
  let valid = true;
  for (const [fieldId, rule] of Object.entries(RULES)) {
    const el = document.getElementById(fieldId);
    const val = el.value.trim();

    if (rule.required && !val) {
      setError(fieldId, `${rule.label} is required`);
      valid = false; continue;
    }
    if (val && rule.minLen && val.length < rule.minLen) {
      setError(fieldId, `${rule.label} must be at least ${rule.minLen} chars`);
      valid = false; continue;
    }
    if (val && rule.isEmail && !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(val)) {
      setError(fieldId, 'Invalid email');
      valid = false;
    }
  }
  return valid;
}

function setError(fieldId, msg) {
  const el = document.getElementById(fieldId);
  const err = document.getElementById(`err-${fieldId}`);
  el.classList.add('error');
  if (err) err.textContent = msg;
}

function clearErrors() {
  document.querySelectorAll('.field-error').forEach(e => e.textContent = '');
  document.querySelectorAll('.error').forEach(e => e.classList.remove('error'));
}

function v(id) { return document.getElementById(id).value.trim(); }
function esc(str) { return String(str || '').replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;'); }

function showMsg(type, text) {
  const box = document.getElementById('formMsg');
  box.className = `form-msg ${type}`;
  box.textContent = text;
  box.classList.remove('hidden');
  setTimeout(() => box.classList.add('hidden'), 4000);
}

function resetForm() {
  document.getElementById('studentForm').reset();
  document.getElementById('studentId').value = '';
  document.getElementById('formTitle').textContent = 'Add New Student';
  document.getElementById('submitLabel').textContent = 'Add Student';
  clearErrors();
}
