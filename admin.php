<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>BloxCash Admin Panel</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">
    <style>
        .sidebar {
            position: fixed;
            top: 0;
            bottom: 0;
            left: 0;
            z-index: 100;
            padding: 48px 0 0;
            box-shadow: inset -1px 0 0 rgba(0, 0, 0, .1);
        }
        .sidebar-sticky {
            position: relative;
            top: 0;
            height: calc(100vh - 48px);
            padding-top: .5rem;
            overflow-x: hidden;
            overflow-y: auto;
        }
        .navbar-brand {
            padding-top: .75rem;
            padding-bottom: .75rem;
            font-size: 1rem;
            background-color: rgba(0, 0, 0, .25);
            box-shadow: inset -1px 0 0 rgba(0, 0, 0, .25);
        }
    </style>
</head>
<body>
    <nav class="navbar navbar-dark sticky-top bg-dark flex-md-nowrap p-0 shadow">
        <a class="navbar-brand col-md-3 col-lg-2 me-0 px-3" href="#">BloxCash Admin</a>
        <button class="navbar-toggler position-absolute d-md-none collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#sidebarMenu" aria-controls="sidebarMenu" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="w-100"></div>
        <ul class="navbar-nav px-3">
            <li class="nav-item text-nowrap">
                <a class="nav-link" href="#" id="logoutButton">Sign out</a>
            </li>
        </ul>
    </nav>

    <div class="container-fluid">
        <div class="row">
            <nav id="sidebarMenu" class="col-md-3 col-lg-2 d-md-block bg-light sidebar collapse">
                <div class="position-sticky pt-3">
                    <ul class="nav flex-column">
                        <li class="nav-item">
                            <a class="nav-link active" href="#" data-page="dashboard">
                                <i class="fas fa-tachometer-alt"></i> Dashboard
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="#" data-page="users">
                                <i class="fas fa-users"></i> Users
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="#" data-page="competitions">
                                <i class="fas fa-trophy"></i> Competitions
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="#" data-page="financials">
                                <i class="fas fa-chart-line"></i> Financial Reports
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="#" data-page="promocodes">
                                <i class="fas fa-tags"></i> Promocodes
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="#" data-page="giveaways">
                                <i class="fas fa-gift"></i> Giveaways
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="#" data-page="offerwall">
                                <i class="fas fa-ad"></i> Offer Wall
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="#" data-page="referrals">
                                <i class="fas fa-network-wired"></i> Referrals
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="#" data-page="database">
                                <i class="fas fa-database"></i> Database Management
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="#" data-page="settings">
                                <i class="fas fa-cog"></i> System Settings
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="#" data-page="logs">
                                <i class="fas fa-clipboard-list"></i> Audit Logs
                            </a>
                        </li>
                    </ul>
                </div>
            </nav>

            <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
                <div id="content">
                    <!-- Content will be loaded here -->
                </div>
            </main>
        </div>
    </div>

    <!-- Login Modal -->
    <div class="modal fade" id="loginModal" tabindex="-1" aria-labelledby="loginModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="loginModalLabel">Admin Login</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="loginForm">
                        <div class="mb-3">
                            <label for="password" class="form-label">Password</label>
                            <input type="password" class="form-control" id="password" required>
                        </div>
                        <button type="submit" class="btn btn-primary">Login</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        // Check if user is logged in
        function checkAuth() {
            const isLoggedIn = localStorage.getItem('adminLoggedIn');
            if (!isLoggedIn) {
                showLoginModal();
            }
        }

        // Show login modal
        function showLoginModal() {
            const loginModal = new bootstrap.Modal(document.getElementById('loginModal'));
            loginModal.show();
        }

        // Handle login form submission
        document.getElementById('loginForm').addEventListener('submit', function(e) {
            e.preventDefault();
            const password = document.getElementById('password').value;
            // In a real application, you would send this password to the server for verification
            if (password === 'admin123') { // This should be replaced with proper server-side authentication
                localStorage.setItem('adminLoggedIn', 'true');
                bootstrap.Modal.getInstance(document.getElementById('loginModal')).hide();
                loadContent('dashboard');
            } else {
                alert('Invalid password');
            }
        });

        // Handle logout
        document.getElementById('logoutButton').addEventListener('click', function(e) {
            e.preventDefault();
            localStorage.removeItem('adminLoggedIn');
            showLoginModal();
        });

        // Load content based on sidebar selection
        document.querySelectorAll('.nav-link').forEach(link => {
            link.addEventListener('click', function(e) {
                e.preventDefault();
                const page = this.getAttribute('data-page');
                loadContent(page);
            });
        });

        // Function to load content
        function loadContent(page) {
            const contentDiv = document.getElementById('content');
            // In a real application, you would fetch this content from the server
            switch(page) {
                case 'dashboard':
                    contentDiv.innerHTML = '<h2>Dashboard</h2><p>Welcome to the BloxCash Admin Panel</p>';
                    break;
                    case 'users':
    loadUserManagement();
    break;
    case 'competitions':
    loadCompetitionManagement();
    break;
    case 'financials':
    loadFinancialReports();
    break;
    case 'promocodes':
    loadPromocodeManagement();
    break;
                // Add cases for other pages
                default:
                    contentDiv.innerHTML = '<h2>404</h2><p>Page not found</p>';
            }
        }

        // Initial auth check and content load
        checkAuth();
        loadContent('dashboard');
        function loadUserManagement() {
    const contentDiv = document.getElementById('content');
    contentDiv.innerHTML = `
        <h2>User Management</h2>
        <button class="btn btn-primary mb-3" onclick="showAddUserModal()">Add New User</button>
        <table class="table table-striped" id="userTable">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Email</th>
                    <th>Unique ID</th>
                    <th>Roblox Username</th>
                    <th>Rank</th>
                    <th>Balance</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <!-- User data will be inserted here -->
            </tbody>
        </table>
    `;
    
    // In a real application, you would fetch this data from the server
    const users = [
        { id: 1, email: 'user1@example.com', unique_id: 'abc123', roblox_username: 'RobloxUser1', Rank: 'Coin Collector', balance: 100 },
        { id: 2, email: 'user2@example.com', unique_id: 'def456', roblox_username: 'RobloxUser2', Rank: 'Robux Renegade', balance: 250 },
        // Add more mock data as needed
    ];
    
    const tbody = document.querySelector('#userTable tbody');
    users.forEach(user => {
        const row = `
            <tr>
                <td>${user.id}</td>
                <td>${user.email}</td>
                <td>${user.unique_id}</td>
                <td>${user.roblox_username}</td>
                <td>${user.Rank}</td>
                <td>${user.balance}</td>
                <td>
                    <button class="btn btn-sm btn-primary" onclick="editUser(${user.id})">Edit</button>
                    <button class="btn btn-sm btn-danger" onclick="deleteUser(${user.id})">Delete</button>
                </td>
            </tr>
        `;
        tbody.insertAdjacentHTML('beforeend', row);
    });
}

function showAddUserModal() {
    const modal = `
        <div class="modal fade" id="addUserModal" tabindex="-1" aria-labelledby="addUserModalLabel" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="addUserModalLabel">Add New User</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <form id="addUserForm">
                            <div class="mb-3">
                                <label for="email" class="form-label">Email</label>
                                <input type="email" class="form-control" id="email" required>
                            </div>
                            <div class="mb-3">
                                <label for="roblox_username" class="form-label">Roblox Username</label>
                                <input type="text" class="form-control" id="roblox_username" required>
                            </div>
                            <div class="mb-3">
                                <label for="rank" class="form-label">Rank</label>
                                <select class="form-control" id="rank">
                                    <option>Coin Collector</option>
                                    <option>Robux Renegade</option>
                                    <option>Robux Royalty</option>
                                </select>
                            </div>
                            <button type="submit" class="btn btn-primary">Add User</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    `;
    document.body.insertAdjacentHTML('beforeend', modal);
    const addUserModal = new bootstrap.Modal(document.getElementById('addUserModal'));
    addUserModal.show();
    
    document.getElementById('addUserForm').addEventListener('submit', function(e) {
        e.preventDefault();
        // In a real application, you would send this data to the server
        console.log('Adding user:', {
            email: document.getElementById('email').value,
            roblox_username: document.getElementById('roblox_username').value,
            rank: document.getElementById('rank').value
        });
        addUserModal.hide();
        loadUserManagement(); // Reload the user list
    });
}

function editUser(userId) {
    // In a real application, you would fetch the user's current data from the server
    console.log('Editing user:', userId);
    // Implement edit user functionality here
}

function deleteUser(userId) {
    if (confirm('Are you sure you want to delete this user?')) {
        // In a real application, you would send a delete request to the server
        console.log('Deleting user:', userId);
        loadUserManagement(); // Reload the user list
    }
}
function loadCompetitionManagement() {
    const contentDiv = document.getElementById('content');
    contentDiv.innerHTML = `
        <h2>Competition Management</h2>
        <ul class="nav nav-tabs" id="competitionTabs" role="tablist">
            <li class="nav-item" role="presentation">
                <button class="nav-link active" id="youtube-tab" data-bs-toggle="tab" data-bs-target="#youtube" type="button" role="tab" aria-controls="youtube" aria-selected="true">YouTube</button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="tiktok-tab" data-bs-toggle="tab" data-bs-target="#tiktok" type="button" role="tab" aria-controls="tiktok" aria-selected="false">TikTok</button>
            </li>
        </ul>
        <div class="tab-content" id="competitionTabsContent">
            <div class="tab-pane fade show active" id="youtube" role="tabpanel" aria-labelledby="youtube-tab">
                <h3 class="mt-3">YouTube Competitions</h3>
                <button class="btn btn-primary mb-3" onclick="showAddCompetitionModal('youtube')">Add New YouTube Competition</button>
                <div id="youtubeCompetitions"></div>
            </div>
            <div class="tab-pane fade" id="tiktok" role="tabpanel" aria-labelledby="tiktok-tab">
                <h3 class="mt-3">TikTok Competitions</h3>
                <button class="btn btn-primary mb-3" onclick="showAddCompetitionModal('tiktok')">Add New TikTok Competition</button>
                <div id="tiktokCompetitions"></div>
            </div>
        </div>
    `;
    
    loadYouTubeCompetitions();
    loadTikTokCompetitions();
}

function loadYouTubeCompetitions() {
    // In a real application, you would fetch this data from the server
    const competitions = [
        { id: 1, start_date: '2024-06-01', end_date: '2024-06-30', winner_id: null, amount: 5000 },
        { id: 2, start_date: '2024-05-01', end_date: '2024-05-31', winner_id: 'T8WTIRG0ECcaYAwI', amount: 4000 },
    ];
    
    const competitionsDiv = document.getElementById('youtubeCompetitions');
    competitionsDiv.innerHTML = competitions.map(comp => `
        <div class="card mb-3">
            <div class="card-body">
                <h5 class="card-title">Competition #${comp.id}</h5>
                <p class="card-text">
                    Start Date: ${comp.start_date}<br>
                    End Date: ${comp.end_date}<br>
                    Prize: $${comp.amount}
                </p>
                ${comp.winner_id 
                    ? `<p class="card-text">Winner: ${comp.winner_id}</p>` 
                    : `<button class="btn btn-primary" onclick="viewSubmissions('youtube', ${comp.id})">View Submissions</button>`
                }
            </div>
        </div>
    `).join('');
}

function loadTikTokCompetitions() {
    // Similar to loadYouTubeCompetitions, but for TikTok
    // You would fetch TikTok competition data from the server
    const competitions = [
        { id: 1, start_date: '2024-06-15', end_date: '2024-07-15', winner_id: null, amount: 7000 },
    ];
    
    const competitionsDiv = document.getElementById('tiktokCompetitions');
    competitionsDiv.innerHTML = competitions.map(comp => `
        <div class="card mb-3">
            <div class="card-body">
                <h5 class="card-title">Competition #${comp.id}</h5>
                <p class="card-text">
                    Start Date: ${comp.start_date}<br>
                    End Date: ${comp.end_date}<br>
                    Prize: $${comp.amount}
                </p>
                ${comp.winner_id 
                    ? `<p class="card-text">Winner: ${comp.winner_id}</p>` 
                    : `<button class="btn btn-primary" onclick="viewSubmissions('tiktok', ${comp.id})">View Submissions</button>`
                }
            </div>
        </div>
    `).join('');
}

function showAddCompetitionModal(platform) {
    const modal = `
        <div class="modal fade" id="addCompetitionModal" tabindex="-1" aria-labelledby="addCompetitionModalLabel" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="addCompetitionModalLabel">Add New ${platform.charAt(0).toUpperCase() + platform.slice(1)} Competition</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <form id="addCompetitionForm">
                            <div class="mb-3">
                                <label for="start_date" class="form-label">Start Date</label>
                                <input type="date" class="form-control" id="start_date" required>
                            </div>
                            <div class="mb-3">
                                <label for="end_date" class="form-label">End Date</label>
                                <input type="date" class="form-control" id="end_date" required>
                            </div>
                            <div class="mb-3">
                                <label for="amount" class="form-label">Prize Amount</label>
                                <input type="number" class="form-control" id="amount" required>
                            </div>
                            <button type="submit" class="btn btn-primary">Add Competition</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    `;
    document.body.insertAdjacentHTML('beforeend', modal);
    const addCompetitionModal = new bootstrap.Modal(document.getElementById('addCompetitionModal'));
    addCompetitionModal.show();
    
    document.getElementById('addCompetitionForm').addEventListener('submit', function(e) {
        e.preventDefault();
        // In a real application, you would send this data to the server
        console.log('Adding competition:', {
            platform: platform,
            start_date: document.getElementById('start_date').value,
            end_date: document.getElementById('end_date').value,
            amount: document.getElementById('amount').value
        });
        addCompetitionModal.hide();
        loadCompetitionManagement(); // Reload the competition list
    });
}

function viewSubmissions(platform, competitionId) {
    // In a real application, you would fetch submissions from the server
    const submissions = [
        { id: 1, unique_id: 'T8WTIRG0ECcaYAwI', video_url: 'https://www.youtube.com/embed/dQw4w9WgXcQ' },
        { id: 2, unique_id: 'Og9qM0j8', video_url: 'https://www.youtube.com/embed/dQw4w9WgXcQ' },
    ];
    
    const modal = `
        <div class="modal fade" id="submissionsModal" tabindex="-1" aria-labelledby="submissionsModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-xl">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="submissionsModalLabel">Submissions for ${platform.charAt(0).toUpperCase() + platform.slice(1)} Competition #${competitionId}</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        ${submissions.map(sub => `
                            <div class="card mb-3">
                                <div class="card-body">
                                    <h5 class="card-title">Submission by ${sub.unique_id}</h5>
                                    <div class="embed-responsive embed-responsive-16by9">
                                        <iframe class="embed-responsive-item" src="${sub.video_url}" allowfullscreen></iframe>
                                    </div>
                                    <button class="btn btn-success mt-2" onclick="chooseWinner('${platform}', ${competitionId}, '${sub.unique_id}')">Choose as Winner</button>
                                </div>
                            </div>
                        `).join('')}
                    </div>
                </div>
            </div>
        </div>
    `;
    document.body.insertAdjacentHTML('beforeend', modal);
    const submissionsModal = new bootstrap.Modal(document.getElementById('submissionsModal'));
    submissionsModal.show();
}

function chooseWinner(platform, competitionId, winnerId) {
    if (confirm(`Are you sure you want to choose ${winnerId} as the winner for this competition?`)) {
        // In a real application, you would send this data to the server
        console.log('Choosing winner:', { platform, competitionId, winnerId });
        // Close the modal and reload the competition list
        bootstrap.Modal.getInstance(document.getElementById('submissionsModal')).hide();
        loadCompetitionManagement();
    }
}
function loadFinancialReports() {
    const contentDiv = document.getElementById('content');
    contentDiv.innerHTML = `
        <h2>Financial Reports</h2>
        <div class="row mb-4">
            <div class="col-md-4">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title">Total Earnings</h5>
                        <p class="card-text" id="totalEarnings">Loading...</p>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title">Total Withdrawals</h5>
                        <p class="card-text" id="totalWithdrawals">Loading...</p>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title">Current Balance</h5>
                        <p class="card-text" id="currentBalance">Loading...</p>
                    </div>
                </div>
            </div>
        </div>
        <h3>Detailed Report</h3>
        <div class="mb-3">
            <label for="reportType" class="form-label">Report Type</label>
            <select class="form-select" id="reportType" onchange="updateReport()">
                <option value="earnings">Earnings</option>
                <option value="withdrawals">Withdrawals</option>
            </select>
        </div>
        <div class="mb-3">
            <label for="dateRange" class="form-label">Date Range</label>
            <select class="form-select" id="dateRange" onchange="updateReport()">
                <option value="7">Last 7 days</option>
                <option value="30">Last 30 days</option>
                <option value="90">Last 90 days</option>
                <option value="365">Last 365 days</option>
            </select>
        </div>
        <canvas id="financialChart"></canvas>
        <table class="table table-striped mt-4" id="financialTable">
            <thead>
                <tr>
                    <th>Date</th>
                    <th>User</th>
                    <th>Type</th>
                    <th>Amount</th>
                </tr>
            </thead>
            <tbody>
                <!-- Data will be inserted here -->
            </tbody>
        </table>
    `;
    
    loadFinancialOverview();
    updateReport();
}

function loadFinancialOverview() {
    // In a real application, you would fetch this data from the server
    const totalEarnings = 50000;
    const totalWithdrawals = 30000;
    const currentBalance = totalEarnings - totalWithdrawals;
    
    document.getElementById('totalEarnings').textContent = `$${totalEarnings.toFixed(2)}`;
    document.getElementById('totalWithdrawals').textContent = `$${totalWithdrawals.toFixed(2)}`;
    document.getElementById('currentBalance').textContent = `$${currentBalance.toFixed(2)}`;
}

function updateReport() {
    const reportType = document.getElementById('reportType').value;
    const dateRange = document.getElementById('dateRange').value;
    
    // In a real application, you would fetch this data from the server based on the selected options
    const data = generateMockData(reportType, dateRange);
    
    updateChart(data);
    updateTable(data);
}

function generateMockData(reportType, dateRange) {
    const data = [];
    const startDate = new Date();
    startDate.setDate(startDate.getDate() - parseInt(dateRange));
    
    for (let i = 0; i < parseInt(dateRange); i++) {
        const date = new Date(startDate.getTime() + i * 24 * 60 * 60 * 1000);
        data.push({
            date: date.toISOString().split('T')[0],
            user: `User${Math.floor(Math.random() * 1000)}`,
            type: reportType === 'earnings' ? ['promocode', 'referral', 'survey'][Math.floor(Math.random() * 3)] : 'withdrawal',
            amount: Math.random() * 100
        });
    }
    
    return data;
}

function updateChart(data) {
    const ctx = document.getElementById('financialChart').getContext('2d');
    const chartData = data.reduce((acc, item) => {
        if (!acc[item.date]) {
            acc[item.date] = 0;
        }
        acc[item.date] += item.amount;
        return acc;
    }, {});
    
    new Chart(ctx, {
        type: 'line',
        data: {
            labels: Object.keys(chartData),
            datasets: [{
                label: document.getElementById('reportType').value.charAt(0).toUpperCase() + document.getElementById('reportType').value.slice(1),
                data: Object.values(chartData),
                borderColor: 'rgb(75, 192, 192)',
                tension: 0.1
            }]
        },
        options: {
            responsive: true,
            scales: {
                y: {
                    beginAtZero: true
                }
            }
        }
    });
}

function updateTable(data) {
    const tbody = document.querySelector('#financialTable tbody');
    tbody.innerHTML = data.map(item => `
        <tr>
            <td>${item.date}</td>
            <td>${item.user}</td>
            <td>${item.type}</td>
            <td>$${item.amount.toFixed(2)}</td>
        </tr>
    `).join('');
}
function loadPromocodeManagement() {
    const contentDiv = document.getElementById('content');
    contentDiv.innerHTML = `
        <h2>Promocode Management</h2>
        <button class="btn btn-primary mb-3" onclick="showAddPromocodeModal()">Add New Promocode</button>
        <table class="table table-striped" id="promocodeTable">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Code</th>
                    <th>Points</th>
                    <th>Expiry Date</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <!-- Promocode data will be inserted here -->
            </tbody>
        </table>
    `;
    
    loadPromocodes();
}

function loadPromocodes() {
    // In a real application, you would fetch this data from the server
    const promocodes = [
        { id: 1, code: 'TRIAL120', points: 120, expiry_date: '2025-05-29' },
        { id: 2, code: 'EXP150', points: 150, expiry_date: '2024-05-01' },
    ];
    
    const tbody = document.querySelector('#promocodeTable tbody');
    tbody.innerHTML = promocodes.map(promo => `
        <tr>
            <td>${promo.id}</td>
            <td>${promo.code}</td>
            <td>${promo.points}</td>
            <td>${promo.expiry_date}</td>
            <td>
                <button class="btn btn-sm btn-primary" onclick="editPromocode(${promo.id})">Edit</button>
                <button class="btn btn-sm btn-danger" onclick="deletePromocode(${promo.id})">Delete</button>
            </td>
        </tr>
    `).join('');
}

function showAddPromocodeModal() {
    const modal = `
        <div class="modal fade" id="addPromocodeModal" tabindex="-1" aria-labelledby="addPromocodeModalLabel" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="addPromocodeModalLabel">Add New Promocode</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <form id="addPromocodeForm">
                            <div class="mb-3">
                                <label for="code" class="form-label">Code</label>
                                <input type="text" class="form-control" id="code" required>
                            </div>
                            <div class="mb-3">
                                <label for="points" class="form-label">Points</label>
                                <input type="number" class="form-control" id="points" required>
                            </div>
                            <div class="mb-3">
                                <label for="expiry_date" class="form-label">Expiry Date</label>
                                <input type="date" class="form-control" id="expiry_date" required>
                            </div>
                            <button type="submit" class="btn btn-primary">Add Promocode</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    `;
    document.body.insertAdjacentHTML('beforeend', modal);
    const addPromocodeModal = new bootstrap.Modal(document.getElementById('addPromocodeModal'));
    addPromocodeModal.show();
    
    document.getElementById('addPromocodeForm').addEventListener('submit', function(e) {
        e.preventDefault();
        // In a real application, you would send this data to the server
        console.log('Adding promocode:', {
            code: document.getElementById('code').value,
            points: document.getElementById('points').value,
            expiry_date: document.getElementById('expiry_date').value
        });
        addPromocodeModal.hide();
        loadPromocodes(); // Reload the promocode list
    });
}

function editPromocode(promocodeId) {
    // In a real application, you would fetch the promocode's current data from the server
    const promocode = { id: promocodeId, code: 'TRIAL120', points: 120, expiry_date: '2025-05-29' };
    
    const modal = `
        <div class="modal fade" id="editPromocodeModal" tabindex="-1" aria-labelledby="editPromocodeModalLabel" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="editPromocodeModalLabel">Edit Promocode</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <form id="editPromocodeForm">
                            <div class="mb-3">
                                <label for="edit_code" class="form-label">Code</label>
                                <input type="text" class="form-control" id="edit_code" value="${promocode.code}" required>
                            </div>
                            <div class="mb-3">
                                <label for="edit_points" class="form-label">Points</label>
                                <input type="number" class="form-control" id="edit_points" value="${promocode.points}" required>
                            </div>
                            <div class="mb-3">
                                <label for="edit_expiry_date" class="form-label">Expiry Date</label>
                                <input type="date" class="form-control" id="edit_expiry_date" value="${promocode.expiry_date}" required>
                            </div>
                            <button type="submit" class="btn btn-primary">Update Promocode</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    `;
    document.body.insertAdjacentHTML('beforeend', modal);
    const editPromocodeModal = new bootstrap.Modal(document.getElementById('editPromocodeModal'));
    editPromocodeModal.show();
    
    document.getElementById('editPromocodeForm').addEventListener('submit', function(e) {
        e.preventDefault();
        // In a real application, you would send this data to the server
        console.log('Updating promocode:', {
            id: promocodeId,
            code: document.getElementById('edit_code').value,
            points: document.getElementById('edit_points').value,
            expiry_date: document.getElementById('edit_expiry_date').value
        });
        editPromocodeModal.hide();
        loadPromocodes(); // Reload the promocode list
    });
}

function deletePromocode(promocodeId) {
    if (confirm('Are you sure you want to delete this promocode?')) {
        // In a real application, you would send a delete request to the server
        console.log('Deleting promocode:', promocodeId);
        loadPromocodes(); // Reload the promocode list
    }
}
    </script>
</body>
</html> 