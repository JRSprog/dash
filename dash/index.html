<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>School Management Dashboard</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --primary: #6366f1;
            --primary-dark: #4f46e5;
            --secondary: #f43f5e;
            --dark: #1e293b;
            --light: #f8fafc;
            --gray: #94a3b8;
            --gray-light: #e2e8f0;
            --success: #10b981;
            --warning: #f59e0b;
            --danger: #ef4444;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        body {
            background-color: #f1f5f9;
            color: var(--dark);
            min-height: 100vh;
            display: grid;
            grid-template-rows: auto 1fr auto;
            grid-template-columns: 280px 1fr;
            grid-template-areas:
                "sidebar header"
                "sidebar main"
                "sidebar footer";
        }

        /* Header Styles */
        header {
            grid-area: header;
            background-color: white;
            padding: 1rem 2rem;
            display: flex;
            align-items: center;
            justify-content: space-between;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
            z-index: 10;
        }

        .search-bar {
            display: flex;
            align-items: center;
            background-color: var(--gray-light);
            border-radius: 0.5rem;
            padding: 0.5rem 1rem;
            width: 400px;
            max-width: 50%;
            transition: all 0.3s ease;
        }

        .search-bar.focused {
            box-shadow: 0 0 0 2px var(--primary);
        }

        .search-bar input {
            border: none;
            background: transparent;
            width: 100%;
            padding: 0.5rem;
            outline: none;
            font-size: 0.9rem;
        }

        .search-bar i {
            color: var(--gray);
            margin-right: 0.5rem;
        }

        .user-actions {
            display: flex;
            align-items: center;
            gap: 1.5rem;
        }

        .notification {
            position: relative;
            cursor: pointer;
        }

        .notification-badge {
            position: absolute;
            top: -5px;
            right: -5px;
            background-color: var(--secondary);
            color: white;
            border-radius: 50%;
            width: 18px;
            height: 18px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 0.7rem;
        }

        .notification-dropdown {
            position: absolute;
            right: 0;
            top: 40px;
            background-color: white;
            border-radius: 0.5rem;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            width: 300px;
            padding: 1rem;
            z-index: 100;
            display: none;
        }

        .notification-dropdown.show {
            display: block;
        }

        .notification-header {
            display: flex;
            justify-content: space-between;
            margin-bottom: 1rem;
        }

        .notification-item {
            padding: 0.75rem 0;
            border-bottom: 1px solid var(--gray-light);
        }

        .notification-item:last-child {
            border-bottom: none;
        }

        .user-profile {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            cursor: pointer;
            position: relative;
        }

        .user-dropdown {
            position: absolute;
            right: 0;
            top: 40px;
            background-color: white;
            border-radius: 0.5rem;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            width: 200px;
            padding: 0.5rem 0;
            z-index: 100;
            display: none;
        }

        .user-dropdown.show {
            display: block;
        }

        .user-dropdown-item {
            padding: 0.75rem 1rem;
            transition: background-color 0.2s;
        }

        .user-dropdown-item:hover {
            background-color: var(--gray-light);
        }

        .user-avatar {
            width: 36px;
            height: 36px;
            border-radius: 50%;
            background-color: var(--primary);
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: bold;
        }

        /* Sidebar Styles */
        aside {
            grid-area: sidebar;
            background-color: white;
            padding: 1.5rem 0;
            box-shadow: 2px 0 5px rgba(0, 0, 0, 0.05);
            height: 100vh;
            position: sticky;
            top: 0;
            overflow-y: auto;
        }

        .logo {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            padding: 0 1.5rem 1.5rem;
            border-bottom: 1px solid var(--gray-light);
            margin-bottom: 1.5rem;
        }

        .logo i {
            color: var(--primary);
            font-size: 1.5rem;
        }

        .logo h1 {
            font-size: 1.25rem;
            font-weight: 600;
        }

        .nav-menu {
            display: flex;
            flex-direction: column;
            gap: 0.5rem;
            padding: 0 1rem;
        }

        .nav-item {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            padding: 0.75rem 1rem;
            border-radius: 0.5rem;
            color: var(--dark);
            text-decoration: none;
            transition: all 0.2s;
            cursor: pointer;
        }

        .nav-item:hover {
            background-color: var(--gray-light);
        }

        .nav-item.active {
            background-color: var(--primary);
            color: white;
        }

        .nav-item i {
            width: 24px;
            text-align: center;
        }

        .nav-item span {
            font-size: 0.9rem;
            font-weight: 500;
        }

        .menu-title {
            padding: 1rem 1rem 0.5rem;
            font-size: 0.75rem;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            color: var(--gray);
            font-weight: 600;
        }

        /* Main Content Styles */
        main {
            grid-area: main;
            padding: 2rem;
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            gap: 1.5rem;
            align-content: start;
        }

        .card {
            background-color: white;
            border-radius: 0.75rem;
            padding: 1.5rem;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
            transition: transform 0.2s, box-shadow 0.2s;
        }

        .card:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }

        .card-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 1rem;
        }

        .card-title {
            font-size: 1rem;
            font-weight: 600;
        }

        .card-value {
            font-size: 2rem;
            font-weight: 700;
            margin-bottom: 0.5rem;
        }

        .card-change {
            display: flex;
            align-items: center;
            gap: 0.25rem;
            font-size: 0.85rem;
        }

        .card-change.positive {
            color: var(--success);
        }

        .card-change.negative {
            color: var(--danger);
        }

        .chart-container {
            height: 150px;
            margin-top: 1rem;
        }

        /* Footer Styles */
        footer {
            grid-area: footer;
            background-color: white;
            padding: 1rem 2rem;
            text-align: center;
            font-size: 0.85rem;
            color: var(--gray);
            border-top: 1px solid var(--gray-light);
        }

        /* Dark Mode Styles */
        body.dark-mode {
            background-color: #0f172a;
            color: #f8fafc;
        }

        body.dark-mode header,
        body.dark-mode aside,
        body.dark-mode footer,
        body.dark-mode .card {
            background-color: #1e293b;
            color: #f8fafc;
        }

        body.dark-mode .search-bar {
            background-color: #334155;
        }

        body.dark-mode .search-bar input {
            color: #f8fafc;
        }

        body.dark-mode .nav-item:not(.active):hover {
            background-color: #334155;
        }

        body.dark-mode .menu-title {
            color: #94a3b8;
        }

        /* Fixed Sidebar Icons in Dark Mode */
        body.dark-mode .nav-item:not(.active) i {
            color: #94a3b8;
        }

        body.dark-mode .nav-item.active i {
            color: white;
        }

        /* Fixed Dropdown Styles for Dark Mode */
        body.dark-mode .user-dropdown {
            background-color: #1e293b;
            border: 1px solid #334155;
        }

        body.dark-mode .user-dropdown-item {
            color: #f8fafc;
        }

        body.dark-mode .user-dropdown-item:hover {
            background-color: #334155;
        }

        body.dark-mode .notification-dropdown {
            background-color: #1e293b;
            border: 1px solid #334155;
        }

        body.dark-mode .notification-item {
            border-bottom-color: #334155;
            color: #f8fafc;
        }

        /* Responsive Design */
        @media (max-width: 992px) {
            body {
                grid-template-columns: 80px 1fr;
            }

            .logo h1, .nav-item span, .menu-title {
                display: none;
            }

            .logo {
                justify-content: center;
                padding: 0 0 1.5rem;
            }

            .nav-item {
                justify-content: center;
                padding: 0.75rem;
            }

            .search-bar {
                width: 200px;
            }
        }

        @media (max-width: 768px) {
            body {
                grid-template-areas:
                    "header header"
                    "main main"
                    "footer footer";
                grid-template-columns: 1fr;
                grid-template-rows: auto 1fr auto;
            }

            aside {
                position: fixed;
                bottom: 0;
                left: 0;
                right: 0;
                height: auto;
                width: 100%;
                padding: 0.5rem;
                z-index: 100;
                box-shadow: 0 -1px 5px rgba(0, 0, 0, 0.1);
                height: 60px;
            }

            .logo {
                display: none;
            }

            .nav-menu {
                flex-direction: row;
                justify-content: space-around;
                padding: 0;
            }

            .nav-item {
                flex-direction: column;
                gap: 0.25rem;
                font-size: 0.7rem;
                padding: 0.5rem;
            }

            .nav-item i {
                font-size: 1.1rem;
            }

            .nav-item span {
                font-size: 0.7rem;
            }

            .menu-title {
                display: none;
            }

            main {
                padding: 1rem;
                margin-bottom: 60px;
            }

            .search-bar {
                display: none;
            }
        }

        @media (max-width: 480px) {
            .card {
                grid-column: 1 / -1;
            }

            .user-profile span {
                display: none;
            }
        }
    </style>
</head>
<body>
    <header>
        <div class="search-bar">
            <i class="fas fa-search"></i>
            <input type="text" placeholder="Search students, teachers...">
        </div>
        <div class="user-actions">
            <div class="notification">
                <i class="fas fa-bell"></i>
                <span class="notification-badge">3</span>
                <div class="notification-dropdown">
                    <div class="notification-header">
                        <h4>Notifications</h4>
                        <button class="mark-all-read">Mark all as read</button>
                    </div>
                    <div class="notification-item">
                        <p>New student registration</p>
                        <small>2 minutes ago</small>
                    </div>
                    <div class="notification-item">
                        <p>Parent-teacher meeting scheduled</p>
                        <small>1 hour ago</small>
                    </div>
                    <div class="notification-item">
                        <p>School maintenance scheduled</p>
                        <small>3 hours ago</small>
                    </div>
                </div>
            </div>
            <div class="user-profile">
                <div class="user-avatar">AD</div>
                <span>Admin</span>
                <i class="fas fa-chevron-down"></i>
                <div class="user-dropdown">
                    <div class="user-dropdown-item">
                        <i class="fas fa-user"></i> Profile
                    </div>
                    <div class="user-dropdown-item">
                        <i class="fas fa-cog"></i> Settings
                    </div>
                    <div class="user-dropdown-item" id="dark-mode-toggle">
                        <i class="fas fa-moon"></i> Dark Mode
                    </div>
                    <div class="user-dropdown-item">
                        <i class="fas fa-sign-out-alt"></i> Logout
                    </div>
                </div>
            </div>
        </div>
    </header>

    <aside>
        <div class="logo">
            <i class="fas fa-graduation-cap"></i>
            <h1>SchoolSys</h1>
        </div>
        
        <div class="menu-title">Main</div>
        <nav class="nav-menu">
            <a href="#" class="nav-item active">
                <i class="fas fa-home"></i>
                <span>Dashboard</span>
            </a>
            <a href="#" class="nav-item">
                <i class="fas fa-user-graduate"></i>
                <span>Students</span>
            </a>
            <a href="#" class="nav-item">
                <i class="fas fa-chalkboard-teacher"></i>
                <span>Teachers</span>
            </a>
            <a href="#" class="nav-item">
                <i class="fas fa-book"></i>
                <span>Classes</span>
            </a>
        </nav>
        
        <div class="menu-title">Management</div>
        <nav class="nav-menu">
            <a href="#" class="nav-item">
                <i class="fas fa-calendar-alt"></i>
                <span>Schedule</span>
            </a>
            <a href="#" class="nav-item">
                <i class="fas fa-file-invoice-dollar"></i>
                <span>Finance</span>
            </a>
            <a href="#" class="nav-item">
                <i class="fas fa-clipboard-list"></i>
                <span>Reports</span>
            </a>
        </nav>
        
        <div class="menu-title">Settings</div>
        <nav class="nav-menu">
            <a href="#" class="nav-item">
                <i class="fas fa-cog"></i>
                <span>Settings</span>
            </a>
            <a href="#" class="nav-item">
                <i class="fas fa-question-circle"></i>
                <span>Help</span>
            </a>
        </nav>
    </aside>

    <main>
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Total Students</h3>
                <i class="fas fa-user-graduate"></i>
            </div>
            <div class="card-value">1,247</div>
            <div class="card-change positive">
                <i class="fas fa-arrow-up"></i>
                <span>5.2% from last term</span>
            </div>
            <div class="chart-container" id="students-chart">
                <!-- Chart will be rendered by Chart.js -->
            </div>
        </div>

        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Teachers</h3>
                <i class="fas fa-chalkboard-teacher"></i>
            </div>
            <div class="card-value">78</div>
            <div class="card-change positive">
                <i class="fas fa-arrow-up"></i>
                <span>2 new hires</span>
            </div>
            <div class="chart-container" id="teachers-chart">
                <!-- Chart will be rendered by Chart.js -->
            </div>
        </div>

        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Attendance Rate</h3>
                <i class="fas fa-calendar-check"></i>
            </div>
            <div class="card-value">94.6%</div>
            <div class="card-change negative">
                <i class="fas fa-arrow-down"></i>
                <span>1.2% from last week</span>
            </div>
            <div class="chart-container" id="attendance-chart">
                <!-- Chart will be rendered by Chart.js -->
            </div>
        </div>

        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Active Classes</h3>
                <i class="fas fa-door-open"></i>
            </div>
            <div class="card-value">42</div>
            <div class="card-change positive">
                <i class="fas fa-arrow-up"></i>
                <span>3 new classes</span>
            </div>
            <div class="chart-container" id="classes-chart">
                <!-- Chart will be rendered by Chart.js -->
            </div>
        </div>

        <div class="card" style="grid-column: span 2;">
            <div class="card-header">
                <h3 class="card-title">Recent School Activities</h3>
                <i class="fas fa-calendar-day"></i>
            </div>
            <div id="activity-feed">
                <!-- Activity feed will be populated by JavaScript -->
            </div>
        </div>
    </main>

    <footer>
        <p>© 2023 SchoolSys Management Dashboard. All rights reserved.</p>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Toggle notification dropdown
            const notificationIcon = document.querySelector('.notification');
            const notificationDropdown = document.querySelector('.notification-dropdown');
            
            notificationIcon.addEventListener('click', function(e) {
                e.stopPropagation();
                notificationDropdown.classList.toggle('show');
            });

            // Toggle user dropdown
            const userProfile = document.querySelector('.user-profile');
            const userDropdown = document.querySelector('.user-dropdown');
            
            userProfile.addEventListener('click', function(e) {
                e.stopPropagation();
                userDropdown.classList.toggle('show');
                // Close notification dropdown if open
                notificationDropdown.classList.remove('show');
            });

            // Close dropdowns when clicking outside
            document.addEventListener('click', function() {
                notificationDropdown.classList.remove('show');
                userDropdown.classList.remove('show');
            });

            // Mark all notifications as read
            const markAllReadBtn = document.querySelector('.mark-all-read');
            const notificationBadge = document.querySelector('.notification-badge');
            
            markAllReadBtn.addEventListener('click', function() {
                notificationBadge.textContent = '0';
                notificationBadge.style.display = 'none';
            });

            // Dark mode toggle
            const darkModeToggle = document.getElementById('dark-mode-toggle');
            
            darkModeToggle.addEventListener('click', function() {
                document.body.classList.toggle('dark-mode');
                const icon = darkModeToggle.querySelector('i');
                if (document.body.classList.contains('dark-mode')) {
                    icon.classList.remove('fa-moon');
                    icon.classList.add('fa-sun');
                    localStorage.setItem('darkMode', 'enabled');
                } else {
                    icon.classList.remove('fa-sun');
                    icon.classList.add('fa-moon');
                    localStorage.setItem('darkMode', 'disabled');
                }
            });

            // Check for saved dark mode preference
            if (localStorage.getItem('darkMode') === 'enabled') {
                document.body.classList.add('dark-mode');
                const icon = darkModeToggle.querySelector('i');
                icon.classList.remove('fa-moon');
                icon.classList.add('fa-sun');
            }

            // Search bar focus effect
            const searchBar = document.querySelector('.search-bar');
            const searchInput = document.querySelector('.search-bar input');
            
            searchInput.addEventListener('focus', function() {
                searchBar.classList.add('focused');
            });
            
            searchInput.addEventListener('blur', function() {
                searchBar.classList.remove('focused');
            });

            // Navigation menu active state
            const navItems = document.querySelectorAll('.nav-item');
            
            navItems.forEach(item => {
                item.addEventListener('click', function(e) {
                    e.preventDefault();
                    navItems.forEach(nav => nav.classList.remove('active'));
                    this.classList.add('active');
                });
            });

            // Create charts
            createCharts();

            // Populate activity feed
            populateActivityFeed();
        });

        function createCharts() {
            // Students Chart
            const studentsCtx = document.getElementById('students-chart').getContext('2d');
            new Chart(studentsCtx, {
                type: 'line',
                data: {
                    labels: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun'],
                    datasets: [{
                        label: 'Students',
                        data: [1000, 1050, 1100, 1150, 1200, 1247],
                        borderColor: '#6366f1',
                        backgroundColor: 'rgba(99, 102, 241, 0.1)',
                        borderWidth: 2,
                        tension: 0.4,
                        fill: true
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            display: false
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            grid: {
                                display: false
                            }
                        },
                        x: {
                            grid: {
                                display: false
                            }
                        }
                    }
                }
            });

            // Teachers Chart
            const teachersCtx = document.getElementById('teachers-chart').getContext('2d');
            new Chart(teachersCtx, {
                type: 'bar',
                data: {
                    labels: ['2020', '2021', '2022', '2023'],
                    datasets: [{
                        label: 'Teachers',
                        data: [65, 70, 75, 78],
                        backgroundColor: 'rgba(244, 63, 94, 0.7)',
                        borderColor: 'rgba(244, 63, 94, 1)',
                        borderWidth: 1
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            display: false
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            grid: {
                                display: false
                            }
                        },
                        x: {
                            grid: {
                                display: false
                            }
                        }
                    }
                }
            });

            // Attendance Chart
            const attendanceCtx = document.getElementById('attendance-chart').getContext('2d');
            new Chart(attendanceCtx, {
                type: 'doughnut',
                data: {
                    labels: ['Present', 'Absent'],
                    datasets: [{
                        data: [94.6, 5.4],
                        backgroundColor: [
                            'rgba(16, 185, 129, 0.7)',
                            'rgba(239, 68, 68, 0.1)'
                        ],
                        borderColor: [
                            'rgba(16, 185, 129, 1)',
                            'rgba(239, 68, 68, 0.3)'
                        ],
                        borderWidth: 1
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    cutout: '70%',
                    plugins: {
                        legend: {
                            display: false
                        }
                    }
                }
            });

            // Classes Chart
            const classesCtx = document.getElementById('classes-chart').getContext('2d');
            new Chart(classesCtx, {
                type: 'line',
                data: {
                    labels: ['Grade 1', 'Grade 2', 'Grade 3', 'Grade 4', 'Grade 5', 'Grade 6'],
                    datasets: [{
                        label: 'Classes',
                        data: [8, 7, 7, 6, 7, 7],
                        borderColor: '#10b981',
                        backgroundColor: 'rgba(16, 185, 129, 0.1)',
                        borderWidth: 2,
                        tension: 0.4,
                        fill: true
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            display: false
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            grid: {
                                display: false
                            },
                            ticks: {
                                stepSize: 1
                            }
                        },
                        x: {
                            grid: {
                                display: false
                            }
                        }
                    }
                }
            });
        }

        function populateActivityFeed() {
            const activities = [
                { action: 'Science fair registration opened', time: 'Today, 9:00 AM', user: 'Ms. Johnson' },
                { action: 'Parent-teacher meetings scheduled', time: 'Yesterday, 3:45 PM', user: 'Admin' },
                { action: 'New student enrolled in Grade 3', time: 'Yesterday, 11:20 AM', user: 'Registration Office' },
                { action: 'School basketball team won regional finals', time: '2 days ago', user: 'Sports Dept' },
                { action: 'Library new books arrived', time: '3 days ago', user: 'Librarian' }
            ];

            const activityFeed = document.getElementById('activity-feed');
            
            activities.forEach(activity => {
                const activityItem = document.createElement('div');
                activityItem.className = 'notification-item';
                activityItem.innerHTML = `
                    <p><strong>${activity.action}</strong></p>
                    <small>${activity.time} • ${activity.user}</small>
                `;
                activityFeed.appendChild(activityItem);
            });
        }
    </script>
</body>
</html>
