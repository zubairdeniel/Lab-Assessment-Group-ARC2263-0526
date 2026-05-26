 status/**
 * StudentBase — app.js
 * Handles: view switching, form validation, table render, search,
 *          pagination, edit/delete (demo mode + PHP fetch integration).
 *
 * In production, replace the DEMO DATA block and fetch() calls with your
 * PHP endpoints (api.php).  All fetch calls are already wired to the correct
 * URLs; just remove the demo override.
 */

'use strict';

/* ─── DEMO DATA (remove when PHP backend is live) ─────────────────────── */
let demoStudents = [
  { id: 1, student_number: 'S2024001', first_name: 'Ahmad',   last_name: 'Razali',   email: 'ahmad@uni.edu.my',   phone: '+60 12-345 6789', course: 'Computer Science',   year_level: 2, gpa: '3.75', status: 'Active',   gender: 'Male',   dob: '2003-04-12', address: 'No 5 Jln Maju, Kuala Lumpur', intake: 'Sept 2023', emergency_contact: 'Razali Hamid', emergency_phone: '+60 12-100 0001' },
  { id: 2, student_number: 'S2024002', first_name: 'Siti',    last_name: 'Aminah',   email: 'siti@uni.edu.my',    phone: '+60 11-234 5678', course: 'Data Science',       year_level: 1, gpa: '3.90', status: 'Active',   gender: 'Female', dob: '2004-07-22', address: 'Blk B, PJ Utama, Petaling Jaya', intake: 'Sept 2024', emergency_contact: '', emergency_phone: '' },
  { id: 3, student_number: 'S2023003', first_name: 'Raj',     last_name: 'Kumar',    email: 'raj@uni.edu.my',     phone: '+60 16-789 0123', course: 'Electrical Engineering', year_level: 3, gpa: '2.88', status: 'Active',   gender: 'Male',   dob: '2002-11-05', address: 'Apt 12, Nilai Utama', intake: 'Sept 2022', emergency_contact: 'Priya Kumar', emergency_phone: '+60 16-000 9999' },
  { id: 4, student_number: 'S2022004', first_name: 'Mei',     last_name: 'Ling',     email: 'meiling@uni.edu.my', phone: '+60 17-456 7890', course: 'Business Administration', year_level: 4, gpa: '3.50', status: 'Active',   gender: 'Female', dob: '2001-03-18', address: 'Taman Seri, Seremban', intake: 'Sept 2021', emergency_contact: 'Tan Ah Kow', emergency_phone: '+60 17-111 2222' },
  { id: 5, student_number: 'S2021005', first_name: 'Hafiz',   last_name: 'Osman',    email: 'hafiz@uni.edu.my',   phone: '+60 13-654 3210', course: 'Medicine',           year_level: 5, gpa: '3.95', status: 'Active',   gender: 'Male',   dob: '2000-08-30', address: 'Lorong 3, Kota Bharu', intake: 'Sept 2020', emergency_contact: 'Osman Jaafar', emergency_phone: '+60 13-500 0600' },
  { id: 6, student_number: 'S2023006', first_name: 'Nurul',   last_name: 'Huda',     email: 'nurul@uni.edu.my',   phone: '+60 19-321 0987', course: 'Law',                year_level: 2, gpa: '3.60', status: 'Deferred', gender: 'Female', dob: '2003-01-14', address: 'No 88, Jln Damai, Penang', intake: 'Sept 2023', emergency_contact: '', emergency_phone: '' },
  { id: 7, student_number: 'S2020007', first_name: 'Kevin',   last_name: 'Lim',      email: 'kevin@uni.edu.my',   phone: '+60 12-000 1234', course: 'Software Engineering', year_level: 4, gpa: '3.20', status: 'Graduated', gender: 'Male',  dob: '1999-06-25', address: 'Subang Jaya, Selangor', intake: 'Sept 2019', emergency_contact: 'Lim Boon Seng', emergency_phone: '+60 12-999 8888' },
  { id: 8, student_number: 'S2024008', first_name: 'Priya',   last_name: 'Nair',     email: 'priya@uni.edu.my',   phone: '+60 14-567 8901', course: 'Pharmacy',           year_level: 1, gpa: '3.85', status: 'Active',   gender: 'Female', dob: '2004-09-09', address: 'Jln Ipoh, KL', intake: 'Sept 2024', emergency_contact: 'Nair Gopal', emergency_phone: '+60 14-777 6666' },
];
let nextId = 9;
/* ─── END DEMO DATA ───────────────────────────────────────────────────── */

/* ─── STATE ───────────────────────────────────────────────────────────── */
let currentPage = 1;
const PAGE_SIZE = 6;
let deleteTargetId = null;
let filteredStudents = [];

/* ─── INIT ────────────────────────────────────────────────────────────── */
document.addEventListener('DOMContentLoaded', () => {
  fetchAndRender();
});

/* ─── VIEW SWITCHING ──────────────────────────────────────────────────── */
function showView(name) {
  document.querySelectorAll('.view').forEach(v => v.classList.remove('active'));
  document.querySelectorAll('.nav-btn').forEach(b => {
    b.classList.toggle('active', b.dataset.view === name);
  });
  document.getElementById(`view-${name}`).classList.add('active');
  if (name === 'add') {
    const isEdit = !!document.getElementById('studentId').value;
    if (!isEdit) resetForm();
  }
}

/* ─── FETCH / RENDER ──────────────────────────────────────────────────── */
async function fetchAndRender() {
  /* PRODUCTION: replace body with real fetch
  const res = await fetch('api.php?action=list');
  const students = await res.json();
  */
  const students = demoStudents; // demo override
  filteredStudents = [...students];
  currentPage = 1;
  renderTable();
}

function renderTable() {
  const tbody = document.getElementById('studentBody');
  const empty = document.getElementById('emptyState');
  tbody.innerHTML = '';

  const total = filteredStudents.length;
  const start = (currentPage - 1) * PAGE_SIZE;
  const page  = filteredStudents.slice(start, start + PAGE_SIZE);

  if (!total) {
    empty.classList.remove('hidden');
    renderPagination(0);
    return;
  }
  empty.classList.add('hidden');

  page.forEach(s => {
    const tr = document.createElement('tr');
    tr.innerHTML = `
      <td class="id-cell">${s.student_number}</td>
      <td class="name-cell">${esc(s.first_name)} ${esc(s.last_name)}</td>
      <td>${esc(s.email)}</td>
      <td>${esc(s.phone)}</td>
      <td>${esc(s.course)}</td>
      <td>Yr ${s.year_level}</td>
      <td>${s.gpa ? Number(s.gpa).toFixed(2) : '—'}</td>
      <td><span class="badge badge-${(s.status||'').toLowerCase()}">${esc(s.status)}</span></td>
      <td>
        <div class="action-btns">
          <button class="btn-edit" onclick="startEdit(${s.id})">Edit</button>
          <button class="btn-del"  onclick="startDelete(${s.id})">Delete</button>
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

/* ─── SEARCH / FILTER ─────────────────────────────────────────────────── */
function filterTable() {
  const q = document.getElementById('searchInput').value.toLowerCase().trim();
  filteredStudents = demoStudents.filter(s =>
    (`${s.first_name} ${s.last_name}`).toLowerCase().includes(q) ||
    s.student_number.toLowerCase().includes(q) ||
    (s.email || '').toLowerCase().includes(q) ||
    (s.course || '').toLowerCase().includes(q)
  );
  currentPage = 1;
  renderTable();
}

/* ─── EDIT ────────────────────────────────────────────────────────────── */
function startEdit(id) {
  const s = demoStudents.find(x => x.id === id);
  if (!s) return;

  document.getElementById('studentId').value       = s.id;
  document.getElementById('firstName').value       = s.first_name;
  document.getElementById('lastName').value        = s.last_name;
  document.getElementById('dob').value             = s.dob || '';
  document.getElementById('gender').value          = s.gender || '';
  document.getElementById('address').value         = s.address || '';
  document.getElementById('email').value           = s.email;
  document.getElementById('phone').value           = s.phone;
  document.getElementById('emergencyContact').value= s.emergency_contact || '';
  document.getElementById('emergencyPhone').value  = s.emergency_phone || '';
  document.getElementById('studentNum').value      = s.student_number;
  document.getElementById('course').value          = s.course || '';
  document.getElementById('yearLevel').value       = s.year_level || '';
  document.getElementById('intake').value          = s.intake || '';
  document.getElementById('gpa').value             = s.gpa || '';
  document.getElementById('status').value          = s.status || '';

  document.getElementById('formTitle').textContent  = 'Edit Student';
  document.getElementById('submitLabel').textContent = 'Save Changes';
  clearErrors();
  showView('add');
}

/* ─── DELETE ──────────────────────────────────────────────────────────── */
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
  /* PRODUCTION:
  await fetch('api.php?action=delete', { method:'POST', body: JSON.stringify({id: deleteTargetId}), headers:{'Content-Type':'application/json'} });
  */
  demoStudents = demoStudents.filter(s => s.id !== deleteTargetId); // demo
  closeModal();
  filteredStudents = [...demoStudents];
  currentPage = 1;
  renderTable();
  showView('list');
}

/* ─── FORM SUBMIT ─────────────────────────────────────────────────────── */
async function handleSubmit(e) {
  e.preventDefault();
  if (!validateForm()) return;

  const id = document.getElementById('studentId').value;
  const payload = {
    id:                parseInt(id) || null,
    first_name:        v('firstName'),
    last_name:         v('lastName'),
    dob:               v('dob'),
    gender:            v('gender'),
    address:           v('address'),
    email:             v('email'),
    phone:             v('phone'),
    emergency_contact: v('emergencyContact'),
    emergency_phone:   v('emergencyPhone'),
    student_number:    v('studentNum'),
    course:            v('course'),
    year_level:        parseInt(v('yearLevel')) || null,
    intake:            v('intake'),
    gpa:               v('gpa') || null,
    status:            v('status'),
  };

  const btn = document.getElementById('submitBtn');
  btn.disabled = true;

  try {
    /* PRODUCTION:
    const action = id ? 'update' : 'create';
    const res = await fetch(`api.php?action=${action}`, {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify(payload)
    });
    const json = await res.json();
    if (!json.success) throw new Error(json.message || 'Server error');
    */

    // ── DEMO save ──
    if (payload.id) {
      const idx = demoStudents.findIndex(s => s.id === payload.id);
      if (idx > -1) demoStudents[idx] = { ...demoStudents[idx], ...payload };
    } else {
      payload.id = nextId++;
      demoStudents.push(payload);
    }
    // ── end demo ──

    showMsg('success', payload.id ? 'Student updated successfully.' : 'Student added successfully.');
    filteredStudents = [...demoStudents];
    currentPage = 1;
    setTimeout(() => { resetForm(); showView('list'); renderTable(); }, 900);
  } catch (err) {
    showMsg('error', 'Error: ' + err.message);
  } finally {
    btn.disabled = false;
  }
}

/* ─── VALIDATION ──────────────────────────────────────────────────────── */
const RULES = {
  firstName:  { label: 'First Name',      required: true,  minLen: 2, pattern: /^[A-Za-z\s'-]+$/ },
  lastName:   { label: 'Last Name',       required: true,  minLen: 2, pattern: /^[A-Za-z\s'-]+$/ },
  dob:        { label: 'Date of Birth',   required: true  },
  gender:     { label: 'Gender',          required: true  },
  email:      { label: 'Email',           required: true,  isEmail: true },
  phone:      { label: 'Phone',           required: true,  minLen: 8 },
  studentNum: { label: 'Student Number',  required: true,  minLen: 3 },
  course:     { label: 'Course',          required: true  },
  yearLevel:  { label: 'Year Level',      required: true  },
  status:     { label: 'Status',          required: true  },
  gpa:        { label: 'GPA',             required: false, isGpa: true },
};

function validateForm() {
  clearErrors();
  let valid = true;
  for (const [fieldId, rule] of Object.entries(RULES)) {
    const el = document.getElementById(fieldId);
    const val = el.value.trim();

    if (rule.required && !val) {
      setError(fieldId, `${rule.label} is required.`);
      valid = false; continue;
    }
    if (val && rule.minLen && val.length < rule.minLen) {
      setError(fieldId, `${rule.label} must be at least ${rule.minLen} characters.`);
      valid = false; continue;
    }
    if (val && rule.pattern && !rule.pattern.test(val)) {
      setError(fieldId, `${rule.label} contains invalid characters.`);
      valid = false; continue;
    }
    if (val && rule.isEmail && !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(val)) {
      setError(fieldId, 'Please enter a valid email address.');
      valid = false; continue;
    }
    if (val && rule.isGpa) {
      const g = parseFloat(val);
      if (isNaN(g) || g < 0 || g > 4) {
        setError(fieldId, 'GPA must be between 0.00 and 4.00.');
        valid = false;
      }
    }
  }

  // DOB: must not be in the future, must be ≥ 13 years ago
  const dobVal = document.getElementById('dob').value;
  if (dobVal) {
    const dob  = new Date(dobVal);
    const now  = new Date();
    const age  = (now - dob) / (365.25 * 24 * 3600 * 1000);
    if (dob > now) {
      setError('dob', 'Date of birth cannot be in the future.');
      valid = false;
    } else if (age < 13) {
      setError('dob', 'Student must be at least 13 years old.');
      valid = false;
    }
  }

  return valid;
}

function setError(fieldId, msg) {
  const el  = document.getElementById(fieldId);
  const err = document.getElementById(`err-${fieldId}`);
  el.classList.add('error');
  if (err) err.textContent = msg;
}
function clearErrors() {
  document.querySelectorAll('.field-error').forEach(e => e.textContent = '');
  document.querySelectorAll('.error').forEach(e => e.classList.remove('error'));
}

/* ─── UTILS ───────────────────────────────────────────────────────────── */
function v(id) { return document.getElementById(id).value.trim(); }
function esc(str) {
  return String(str || '').replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;');
}
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
  document.getElementById('formTitle').textContent   = 'Add New Student';
  document.getElementById('submitLabel').textContent = 'Add Student';
  clearErrors();
  document.getElementById('formMsg').classList.add('hidden');
}
