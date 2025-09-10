<template>
  <div class="homepage">
    <!-- Navigation Header -->
    <nav class="navbar">
      <div class="nav-container">
        <div class="nav-left">
          <div class="logo">
            <img src="@/assets/posu_logo.png" alt="POSU Echague" class="logo-img" />
            <span class="logo-text">POSU Echague</span>
          </div>
        </div>
        <div class="nav-right">
          <router-link to="/login" class="btn btn-outline">Login</router-link>
          <router-link to="/register" class="btn btn-primary">Sign Up</router-link>
        </div>
      </div>
    </nav>

    <!-- Hero Section -->
    <section class="hero">
      <div class="hero-container">
        <div class="hero-content">
          <h1 class="hero-title">
            Professional Traffic Violation Management System
          </h1>
          <p class="hero-subtitle">
            Streamline traffic enforcement, manage violations efficiently, and ensure transparent operations with POSU Echague's comprehensive digital platform.
          </p>
          <div class="hero-buttons">
            <router-link to="/register" class="btn btn-primary btn-large">
              Get Started
            </router-link>
            <router-link to="/login" class="btn btn-outline2 btn-large">
              Sign In
            </router-link>
          </div>
        </div>
        <div class="hero-image">
          <div class="hero-graphic">
            <div class="graphic-card">
              <div class="graphic-icon">🚔</div>
              <h3>Traffic Enforcement</h3>
            </div>
            <div class="graphic-card">
              <div class="graphic-icon">📋</div>
              <h3>Violation Management</h3>
            </div>
            <div class="graphic-card">
              <div class="graphic-icon">📊</div>
              <h3>Analytics & Reports</h3>
            </div>
          </div>
        </div>
      </div>
    </section>

    <!-- Features Section -->
    <section class="features">
      <div class="container">
        <h2 class="section-title">Key Features</h2>
        <div class="features-grid">
          <div class="feature-card">
            <div class="feature-icon admin-icon">👨‍💼</div>
            <h3>Admin Dashboard</h3>
            <p>Comprehensive system management, user oversight, and detailed reporting capabilities.</p>
            <ul>
              <li>User Management</li>
              <li>Violation Types</li>
              <li>System Reports</li>
              <li>Notifications</li>
            </ul>
          </div>
          <div class="feature-card">
            <div class="feature-icon enforcer-icon">👮‍♂️</div>
            <h3>Enforcer Tools</h3>
            <p>Efficient violation recording, transaction management, and performance tracking.</p>
            <ul>
              <li>Record Violations</li>
              <li>Manage Transactions</li>
              <li>Performance Stats</li>
              <li>Violator Database</li>
            </ul>
          </div>
          <div class="feature-card">
            <div class="feature-icon violator-icon">🚗</div>
            <h3>Violator Portal</h3>
            <p>Easy access to violation history, payment options, and profile management.</p>
            <ul>
              <li>Violation History</li>
              <li>Payment Tracking</li>
              <li>Profile Updates</li>
              <li>Notifications</li>
            </ul>
          </div>
        </div>
      </div>
    </section>

    <!-- Stats Section -->
    <section class="stats">
      <div class="container">
        <div class="stats-grid">
          <div class="stat-item">
            <div class="stat-number">24/7</div>
            <div class="stat-label">System Availability</div>
          </div>
          <div class="stat-item">
            <div class="stat-number">100%</div>
            <div class="stat-label">Digital Processing</div>
          </div>
          <div class="stat-item">
            <div class="stat-number">Real-time</div>
            <div class="stat-label">Data Updates</div>
          </div>
          <div class="stat-item">
            <div class="stat-number">Secure</div>
            <div class="stat-label">Data Protection</div>
          </div>
        </div>
      </div>
    </section>

    <!-- Footer -->
    <footer class="footer">
      <div class="container">
        <div class="footer-content">
          <div class="footer-left">
            <div class="logo">
              <span class="logo-text">POSU Echague</span>
            </div>
            <p>Professional traffic violation management system for efficient law enforcement operations.</p>
          </div>
          <div class="footer-right">
            <div class="footer-links">
              <router-link to="/login">Login</router-link>
              <router-link to="/register">Register</router-link>
            </div>
          </div>
        </div>
        <div class="footer-bottom">
          <p>&copy; 2025 POSU Echague. All rights reserved.</p>
        </div>
      </div>
    </footer>
  </div>
</template>

<script>
export default {
  name: 'HomePage'
}
</script>

<style scoped>
.homepage {
  min-height: 100vh;
}

/* Navigation */
.navbar {
  background: white;
  box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
  position: sticky;
  top: 0;
  z-index: 100;
}

.nav-container {
  max-width: 1200px;
  margin: 0 auto;
  padding: 0 20px;
  display: flex;
  justify-content: space-between;
  align-items: center;
  height: 70px;
}

.nav-left {
  display: flex;
  align-items: center;
}

.logo {
  display: flex;
  align-items: center;
  gap: 12px;
}

.logo-img {
  width: 56px;
  height: 60px;
  border-radius: 8px;
}

.logo-text {
  font-size: 24px;
  font-weight: 700;
  background: linear-gradient(135deg, #1e3a8a, #3b82f6);
  -webkit-background-clip: text;
  -webkit-text-fill-color: transparent;
  background-clip: text;
}

.nav-right {
  display: flex;
  gap: 16px;
  align-items: center;
}

.btn-outline {
  background: transparent;
  color: #1e3a8a;
  border: 2px solid #1e3a8a;
}

.btn-outline:hover {
  background: #1e3a8a;
  color: white;
}
.btn-outline2 {
  background: linear-gradient(135deg, #1e3a8a, #3b82f6);
  color: white;
  border: 2px solid #1e3a8a;
}

.btn-outline2:hover {
  background: #1e3a8a;
  color: white;
}
/* Hero Section */
.hero {
  background: linear-gradient(135deg, #1e3a8a 0%, #3b82f6 100%);
  color: white;
  padding: 100px 0;
  position: relative;
  overflow: hidden;
}

.hero::before {
  content: '';
  position: absolute;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100"><defs><pattern id="grain" width="100" height="100" patternUnits="userSpaceOnUse"><circle cx="50" cy="50" r="1" fill="white" opacity="0.1"/></pattern></defs><rect width="100" height="100" fill="url(%23grain)"/></svg>');
  opacity: 0.1;
}

.hero-container {
  max-width: 1200px;
  margin: 0 auto;
  padding: 0 20px;
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 60px;
  align-items: center;
  position: relative;
  z-index: 1;
}

.hero-title {
  font-size: 3.5rem;
  font-weight: 800;
  line-height: 1.1;
  margin-bottom: 24px;
}

.hero-subtitle {
  font-size: 1.25rem;
  line-height: 1.6;
  margin-bottom: 40px;
  opacity: 0.9;
}

.hero-buttons {
  display: flex;
  gap: 20px;
}

.btn-large {
  padding: 16px 32px;
  font-size: 1.1rem;
}

.hero-graphic {
  display: grid;
  grid-template-columns: repeat(2, 1fr);
  gap: 20px;
}

.graphic-card {
  background: rgba(255, 255, 255, 0.1);
  backdrop-filter: blur(10px);
  border-radius: 16px;
  padding: 30px 20px;
  text-align: center;
  border: 1px solid rgba(255, 255, 255, 0.2);
}

.graphic-card:first-child {
  grid-column: 1 / -1;
}

.graphic-icon {
  font-size: 3rem;
  margin-bottom: 16px;
}

.graphic-card h3 {
  font-size: 1.1rem;
  font-weight: 600;
}

/* Features Section */
.features {
  padding: 100px 0;
  background: #f8fafc;
}

.container {
  max-width: 1200px;
  margin: 0 auto;
  padding: 0 20px;
}

.section-title {
  text-align: center;
  font-size: 2.5rem;
  font-weight: 700;
  color: #1e3a8a;
  margin-bottom: 60px;
}

.features-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(350px, 1fr));
  gap: 40px;
}

.feature-card {
  background: white;
  border-radius: 16px;
  padding: 40px;
  box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
  transition: transform 0.3s ease, box-shadow 0.3s ease;
}

.feature-card:hover {
  transform: translateY(-10px);
  box-shadow: 0 20px 40px rgba(0, 0, 0, 0.15);
}

.feature-icon {
  font-size: 4rem;
  margin-bottom: 24px;
  display: block;
}

.admin-icon { color: #dc2626; }
.enforcer-icon { color: #1e3a8a; }
.violator-icon { color: #059669; }

.feature-card h3 {
  font-size: 1.5rem;
  font-weight: 700;
  color: #1e3a8a;
  margin-bottom: 16px;
}

.feature-card p {
  color: #6b7280;
  line-height: 1.6;
  margin-bottom: 24px;
}

.feature-card ul {
  list-style: none;
  padding: 0;
}

.feature-card li {
  padding: 8px 0;
  color: #4b5563;
  position: relative;
  padding-left: 24px;
}

.feature-card li::before {
  content: '✓';
  position: absolute;
  left: 0;
  color: #10b981;
  font-weight: bold;
}

/* Stats Section */
.stats {
  background: #1e3a8a;
  color: white;
  padding: 80px 0;
}

.stats-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
  gap: 40px;
  text-align: center;
}

.stat-number {
  font-size: 3rem;
  font-weight: 800;
  margin-bottom: 8px;
}

.stat-label {
  font-size: 1.1rem;
  opacity: 0.9;
}

/* Footer */
.footer {
  background: #111827;
  color: white;
  padding: 60px 0 20px;
}

.footer-content {
  display: grid;
  grid-template-columns: 2fr 1fr;
  gap: 60px;
  margin-bottom: 40px;
}

.footer-left p {
  margin-top: 16px;
  opacity: 0.8;
  line-height: 1.6;
}

.footer-links {
  display: flex;
  gap: 24px;
}

.footer-links a {
  color: white;
  text-decoration: none;
  transition: color 0.3s ease;
}

.footer-links a:hover {
  color: #3b82f6;
}

.footer-bottom {
  border-top: 1px solid #374151;
  padding-top: 20px;
  text-align: center;
  opacity: 0.6;
}

/* Responsive Design */
@media (max-width: 768px) {
  .hero-container {
    grid-template-columns: 1fr;
    text-align: center;
  }
  
  .hero-title {
    font-size: 2.5rem;
  }
  
  .features-grid {
    grid-template-columns: 1fr;
  }
  
  .stats-grid {
    grid-template-columns: repeat(2, 1fr);
  }
  
  .footer-content {
    grid-template-columns: 1fr;
    text-align: center;
  }
  
  .nav-container {
    padding: 0 16px;
  }
  
  .hero-buttons {
    flex-direction: column;
    align-items: center;
  }
}
</style>
