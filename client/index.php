<?php
require_once __DIR__ . '/auth.php';
require_student_session();
$studentName = htmlspecialchars($_SESSION['student_name'] ?? 'Student');
$studentId = $_SESSION['student_id'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>My Profile — StudentBase</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="style.css">
</head>
<body>

  <header class="site-header">
    <div class="header-inner">
      <div class="brand">
        <span class="brand-icon">⬡</span>
        <span class="brand-name">StudentBase</span>
      </div>
      <nav class="header-nav">
        <span class="student-name">Hi, <?= $studentName ?></span>
        <a href="logout.php" class="nav-btn nav-btn-logout">Logout</a>
      </nav>
    </div>
  </header>

  <main class="main-content">
    <div class="profile-header">
      <h1 class="section-title">My Profile</h1>
      <p class="section-sub">View your academic and personal information</p>
    </div>

    <div id="loading" class="loading-state">Loading your profile…</div>
    <div id="error" class="error-state hidden"></div>
    
    <div id="profileCard" class="profile-card hidden">
      <div class="profile-section">
        <h2 class="profile-title">Personal Information</h2>
        <div class="profile-grid">
          <div class="profile-item">
            <span class="profile-label">Full Name</span>
            <span class="profile-value" id="fullName">—</span>
          </div>
          <div class="profile-item">
            <span class="profile-label">Student Number</span>
            <span class="profile-value" id="studentNumber">—</span>
          </div>
          <div class="profile-item">
            <span class="profile-label">Date of Birth</span>
            <span class="profile-value" id="dob">—</span>
          </div>
          <div class="profile-item">
            <span class="profile-label">Gender</span>
            <span class="profile-value" id="gender">—</span>
          </div>
          <div class="profile-item">
            <span class="profile-label">Email</span>
            <span class="profile-value" id="email">—</span>
          </div>
          <div class="profile-item">
            <span class="profile-label">Phone</span>
            <span class="profile-value" id="phone">—</span>
          </div>
          <div class="profile-item full-width">
            <span class="profile-label">Address</span>
            <span class="profile-value" id="address">—</span>
          </div>
        </div>
      </div>

      <div class="profile-section">
        <h2 class="profile-title">Academic Information</h2>
        <div class="profile-grid">
          <div class="profile-item">
            <span class="profile-label">Course</span>
            <span class="profile-value" id="course">—</span>
          </div>
          <div class="profile-item">
            <span class="profile-label">Year Level</span>
            <span class="profile-value" id="yearLevel">—</span>
          </div>
          <div class="profile-item">
            <span class="profile-label">Intake</span>
            <span class="profile-value" id="intake">—</span>
          </div>
          <div class="profile-item">
            <span class="profile-label">GPA</span>
            <span class="profile-value" id="gpa">—</span>
          </div>
          <div class="profile-item">
            <span class="profile-label">Status</span>
            <span class="profile-value"><span class="badge" id="status">Active</span></span>
          </div>
        </div>
      </div>

      <div class="profile-section">
        <h2 class="profile-title">Emergency Contact</h2>
        <div class="profile-grid">
          <div class="profile-item">
            <span class="profile-label">Contact Person</span>
            <span class="profile-value" id="emergencyContact">—</span>
          </div>
          <div class="profile-item">
            <span class="profile-label">Phone</span>
            <span class="profile-value" id="emergencyPhone">—</span>
          </div>
        </div>
      </div>
    </div>
  </main>

  <footer class="site-footer">
    <p>StudentBase &copy; 2025 — Student Portal</p>
  </footer>

  <script>
    const studentId = <?= $studentId ?>;
    
    document.addEventListener('DOMContentLoaded', () => {
      fetchProfile();
    });

    async function fetchProfile() {
      try {
        const res = await fetch('api.php?action=profile');
        if (!res.ok) throw new Error('Failed to fetch');
        const json = await res.json();
        if (!json.success) throw new Error(json.message);
        
        displayProfile(json.data);
      } catch (e) {
        document.getElementById('loading').classList.add('hidden');
        document.getElementById('error').textContent = e.message;
        document.getElementById('error').classList.remove('hidden');
      }
    }

    function displayProfile(data) {
      document.getElementById('fullName').textContent = (data.first_name || '') + ' ' + (data.last_name || '');
      document.getElementById('studentNumber').textContent = data.student_number || '—';
      document.getElementById('dob').textContent = formatDate(data.dob) || '—';
      document.getElementById('gender').textContent = data.gender || '—';
      document.getElementById('email').textContent = data.email || '—';
      document.getElementById('phone').textContent = data.phone || '—';
      document.getElementById('address').textContent = data.address || '—';
      document.getElementById('course').textContent = data.course || '—';
      document.getElementById('yearLevel').textContent = data.year_level ? `Year ${data.year_level}` : '—';
      document.getElementById('intake').textContent = data.intake || '—';
      document.getElementById('gpa').textContent = data.gpa ? Number(data.gpa).toFixed(2) : '—';
      document.getElementById('emergencyContact').textContent = data.emergency_contact || '—';
      document.getElementById('emergencyPhone').textContent = data.emergency_phone || '—';
      
      const statusEl = document.getElementById('status');
      statusEl.textContent = data.status;
      statusEl.className = `badge badge-${(data.status || '').toLowerCase()}`;
      
      document.getElementById('loading').classList.add('hidden');
      document.getElementById('profileCard').classList.remove('hidden');
    }

    function formatDate(dateStr) {
      if (!dateStr) return null;
      const d = new Date(dateStr);
      return d.toLocaleDateString('en-MY', { year: 'numeric', month: 'long', day: 'numeric' });
    }
  </script>
</body>
</html>
