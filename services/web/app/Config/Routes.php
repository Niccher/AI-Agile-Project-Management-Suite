<?php

use CodeIgniter\Router\RouteCollection;

/** @var RouteCollection $routes */

$routes->get('/', 'Home::index');
$routes->get('/features', 'Home::features');
$routes->get('/pricing', 'Home::pricing');
$routes->get('/setup', 'Home::setup');
$routes->get('/faqs', 'Home::faqs');
$routes->get('/compare', 'Home::compare');
$routes->get('/community', 'Home::community');

// Health Check
$routes->get('/health', 'HealthController::index');

// Public Client Portal (Token Gated - No Login Required)
$routes->get('/portal/(:segment)', 'PortalController::view/$1');

$routes->group('', ['filter' => 'session'], function($routes) {
    $routes->get('/home', 'User\MyDashboardController::index');
    $routes->get('/dashboard', 'User\MyDashboardController::index');
    $routes->get('/user/dashboard', 'User\MyDashboardController::index');
    $routes->get('/user/home', 'User\MyDashboardController::index');
    
    // Projects
    $routes->get('/projects', 'User\ProjectController::index');
    $routes->get('/projects/create', 'User\ProjectController::create');
    $routes->get('/projects/health', 'User\ProjectController::health');
    $routes->get('/projects/telemetry-live', 'User\ProjectController::telemetryLive');
    $routes->post('/projects/store', 'User\ProjectController::store');
    $routes->post('/projects/portal/generate', 'PortalController::generate');
    $routes->post('/projects/portal/revoke/(:num)', 'PortalController::revoke/$1');
    $routes->get('/projects/view/(:segment)', 'User\ProjectController::view/$1');
    $routes->get('/projects/edit/(:segment)', 'User\ProjectController::edit/$1');
    $routes->post('/projects/update/(:segment)', 'User\ProjectController::update/$1');
    $routes->post('/projects/delete/(:segment)', 'User\ProjectController::delete/$1');
    $routes->get('/projects/archive/(:segment)', 'User\ProjectController::archive/$1');
    $routes->get('/projects/analytics/(:segment)', 'User\AnalyticsController::index/$1');
    $routes->get('/projects/time/(:segment)', 'User\TimeTrackerController::index/$1');
    
    // Kanban
    $routes->get('/projects/kanban/(:segment)', 'User\KanbanController::index/$1');
    $routes->get('/projects/kanban', 'User\KanbanController::index');
    $routes->get('/kanban', 'User\KanbanController::index');
    
    // Sprints & Backlog
    $routes->get('/projects/sprints/(:segment)', 'User\SprintController::index/$1');
    $routes->post('/projects/sprints/store/(:segment)', 'User\SprintController::store/$1');
    $routes->post('/projects/sprints/start/(:segment)', 'User\SprintController::start/$1');
    $routes->post('/projects/sprints/complete/(:segment)', 'User\SprintController::complete/$1');
    $routes->get('/projects/sprints/burndown/(:segment)', 'User\SprintController::burndown/$1');
    
    // Project Wiki & Documentation
    $routes->get('/projects/wiki/(:segment)', 'User\ProjectWikiController::index/$1');
    $routes->get('/projects/wiki/(:segment)/page/(:segment)', 'User\ProjectWikiController::index/$1/$2');
    $routes->get('/projects/wiki/(:segment)/create', 'User\ProjectWikiController::create/$1');
    $routes->post('/projects/wiki/(:segment)/store', 'User\ProjectWikiController::store/$1');
    $routes->get('/projects/wiki/page/(:num)/edit', 'User\ProjectWikiController::edit/$1');
    $routes->post('/projects/wiki/page/(:num)/update', 'User\ProjectWikiController::update/$1');
    $routes->post('/projects/wiki/page/(:num)/delete', 'User\ProjectWikiController::delete/$1');
    $routes->get('/projects/wiki/page/(:num)/history', 'User\ProjectWikiController::history/$1');
    $routes->post('/projects/wiki/page/(:num)/rollback/(:num)', 'User\ProjectWikiController::rollback/$1/$2');
    
    // My Tasks
    $routes->get('/my-tasks', 'User\MyTasksController::index');
    
    // Calendar
    $routes->get('/calendar', 'User\CalendarController::index');
    $routes->get('/calendar/events', 'Api\CalendarApiController::index');
    $routes->post('/calendar/event/store', 'User\CalendarController::storeEvent');
    $routes->post('/calendar/event/update/(:num)', 'User\CalendarController::updateEvent/$1');
    $routes->post('/calendar/event/delete/(:num)', 'User\CalendarController::deleteEvent/$1');
    
    // Time & Invoicing Reports
    $routes->get('/time', 'User\TimeTrackerController::index');
    $routes->post('/time/manual', 'User\TimeTrackerController::logManual');
    $routes->post('/time/start', 'Api\TimeApiController::start');
    $routes->post('/time/stop/(:num)', 'Api\TimeApiController::stop/$1');
    $routes->get('/time/report', 'User\TimeReportController::index');
    $routes->get('/time/report/pdf', 'User\TimeReportController::pdf');
    $routes->get('/time/report/csv', 'User\TimeReportController::csv');
    
    // Notes
    $routes->get('/notes', 'User\NoteController::index');
    $routes->post('/notes/store', 'User\NoteController::store');
    $routes->post('/notes/update/(:num)', 'User\NoteController::update/$1');
    $routes->post('/notes/delete/(:num)', 'User\NoteController::delete/$1');
    $routes->post('/notes/star/(:num)', 'Api\NoteApiController::toggleStar/$1');
    $routes->post('/notes/complete/(:num)', 'Api\NoteApiController::toggleComplete/$1');
    
    // Direct Task AJAX aliases for Kanban & Projects
    $routes->post('/projects/task/move', 'Api\TaskApiController::move');
    $routes->post('/projects/task/store', 'Api\TaskApiController::store');
    $routes->post('/projects/task/update/(:num)', 'Api\TaskApiController::update/$1');
    
    // Analytics & Settings
    $routes->get('/analytics', 'User\AnalyticsController::index');
    $routes->get('/settings', 'User\SettingsController::index');
    $routes->post('/settings/update', 'User\SettingsController::update');
    $routes->post('/settings/change-password', 'User\SettingsController::changePassword');
    
    // Secure Avatars
    $routes->get('/avatar/(:segment)', 'User\AvatarController::show/$1');
});

// JSON API zone
$routes->group('api', ['filter' => 'session'], function($routes) {
    // Time
    $routes->post('time/start', 'Api\TimeApiController::start');
    $routes->post('time/stop/(:num)', 'Api\TimeApiController::stop/$1');
    
    // Calendar
    $routes->get('calendar/events', 'Api\CalendarApiController::index');
    
    // Tasks
    $routes->post('tasks/move', 'Api\TaskApiController::move');
    $routes->post('tasks/store', 'Api\TaskApiController::store');
    $routes->post('tasks/(:num)/update', 'Api\TaskApiController::update/$1');
    
    // Notes
    $routes->post('notes/(:num)/star', 'Api\NoteApiController::toggleStar/$1');
    $routes->post('notes/(:num)/complete', 'Api\NoteApiController::toggleComplete/$1');
    
    // Sprints API
    $routes->post('sprints/assign-task', 'Api\SprintApiController::assignTask');
    $routes->post('sprints/task-points', 'Api\SprintApiController::updatePoints');
    
    // Notifications API
    $routes->get('notifications/unread-count', 'Api\NotificationApiController::unreadCount');
    $routes->post('notifications/mark-read/(:num)', 'Api\NotificationApiController::markRead/$1');
    $routes->post('notifications/mark-all-read', 'Api\NotificationApiController::markAllRead');
    
    // Global Unified Search & Command Palette
    $routes->get('search', 'Api\SearchApiController::index');
});

// Manager zone — requires login + manager role (support both /manage and /manager)
$managerRouteHandler = function($routes) {
    $routes->get('/', 'Manager\TeamDashboardController::index');
    $routes->get('team', 'Manager\TeamDashboardController::index');
    $routes->get('tasks/assign', 'Manager\TaskAssignmentController::index');
    $routes->post('tasks/assign', 'Manager\TaskAssignmentController::assign');
    $routes->get('approvals', 'Manager\WorkApprovalController::index');
    $routes->post('approvals/(:num)/approve', 'Manager\WorkApprovalController::approve/$1');
    $routes->post('approvals/(:num)/reject', 'Manager\WorkApprovalController::reject/$1');
    $routes->get('reports', 'Manager\ReportController::index');
    $routes->post('reports/generate', 'Manager\ReportController::generate');
    $routes->get('reports/download/(:num)', 'Manager\ReportController::download/$1');
};

$routes->group('manage', ['filter' => ['session', 'manager']], $managerRouteHandler);
$routes->group('manager', ['filter' => ['session', 'manager']], $managerRouteHandler);

// Admin zone — requires login + admin role
$routes->group('admin', ['filter' => ['session', 'admin']], function($routes) {
    $routes->get('/', 'Admin\UserManagementController::index');
    $routes->post('users/provision', 'Admin\UserManagementController::provision');
    $routes->post('users/(:num)/role', 'Admin\UserManagementController::assignRole/$1');
    $routes->post('users/(:num)/deactivate', 'Admin\UserManagementController::deactivate/$1');
    $routes->get('audit-log', 'Admin\AuditLogController::index');
    $routes->get('telemetry', 'Admin\TelemetryController::index');
    $routes->get('settings', 'Admin\SystemSettingsController::index');
    $routes->post('settings/update', 'Admin\SystemSettingsController::update');
    $routes->post('settings/send-test-email', 'Admin\SystemSettingsController::sendTestEmail');
    $routes->post('settings/backup/create', 'Admin\SystemSettingsController::createBackup');
    $routes->get('settings/backup/download/(:segment)', 'Admin\SystemSettingsController::downloadBackup/$1');
    
    // AI Engine Admin routes
    $routes->get('ai/telemetry', 'Admin\AiController::telemetry');
    $routes->get('ai/settings', 'Admin\AiController::settings');
    $routes->get('ai/models-json', 'Admin\AiController::modelsJson');
    $routes->post('ai/settings/update', 'Admin\AiController::updateSettings');
    $routes->post('ai/test-connection', 'Admin\AiController::testConnection');
    $routes->post('ai/cache-action', 'Admin\AiController::cacheAction');
    
    // AI Reports routes
    $routes->get('ai/reports', 'Admin\AiReportsController::index');
    $routes->post('ai/reports/generate', 'Admin\AiReportsController::generate');
    $routes->get('ai/reports/status/(:segment)', 'Admin\AiReportsController::status/$1');
});

// Load default Shield routes, excluding those we'll customize
service('auth')->routes($routes, ['except' => ['login', 'register', 'forgot', 'reset', 'verify-email', 'locked', 'logout']]);

// Direct Logout routes (root /logout and /auth/logout)
$routes->get('logout', [\App\Controllers\Auth\LoginController::class, 'logoutAction'], ['as' => 'logout']);
$routes->post('logout', [\App\Controllers\Auth\LoginController::class, 'logoutAction']);

// Custom Authentication Routes
$routes->group('auth', static function ($routes) {
    // Login & Logout
    $routes->get('login', [\App\Controllers\Auth\LoginController::class, 'loginView'], ['as' => 'login']);
    $routes->post('login', [\App\Controllers\Auth\LoginController::class, 'loginAction']);
    $routes->get('logout', [\App\Controllers\Auth\LoginController::class, 'logoutAction']);
    $routes->post('logout', [\App\Controllers\Auth\LoginController::class, 'logoutAction']);

    // Register
    $routes->get('register', [\App\Controllers\Auth\RegisterController::class, 'registerView'], ['as' => 'register']);
    $routes->post('register', [\App\Controllers\Auth\RegisterController::class, 'registerAction']);

    // Forgot Password
    $routes->get('forgot-password', [\App\Controllers\Auth\ForgotPasswordController::class, 'forgotPasswordView']);
    $routes->post('forgot-password', [\App\Controllers\Auth\ForgotPasswordController::class, 'forgotPasswordAction']);

    // Reset Password
    $routes->get('reset-password', [\App\Controllers\Auth\ResetPasswordController::class, 'resetPasswordView']);
    $routes->post('reset-password', [\App\Controllers\Auth\ResetPasswordController::class, 'resetPasswordAction']);

    // Email Verification - Using Shield's built-in routes
    $routes->get('verify-email', [\App\Controllers\Auth\AccountController::class, 'verifyEmailAction'], ['as' => 'verify-email']);
    
    // Resend verification (support both -verification and -activation)
    $routes->post('resend-verification', [\App\Controllers\Auth\AccountController::class, 'resendShieldActivation']);
    $routes->post('resend-activation', [\App\Controllers\Auth\AccountController::class, 'resendShieldActivation']);

    // Email Verification Success
    $routes->get('verify-email-success', [\App\Controllers\Auth\AccountController::class, 'verifyEmailSuccess']);

    // Locked Account
    $routes->get('locked', [\App\Controllers\Auth\AccountController::class, 'lockedView']);
    $routes->post('unlock-account', [\App\Controllers\Auth\AccountController::class, 'resendActivation']);
});