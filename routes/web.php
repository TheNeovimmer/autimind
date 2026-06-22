<?php

use App\Core\Router;

/** @var Router $router */

// Auth routes
$router->get('/login', 'AuthController@loginForm');
$router->post('/login', 'AuthController@login');
$router->get('/signup', 'AuthController@signupForm');
$router->post('/signup', 'AuthController@signup');
$router->get('/logout', 'AuthController@logout');
$router->get('/forgot-password', 'AuthController@forgotPasswordForm');
$router->post('/forgot-password', 'AuthController@forgotPassword');
$router->get('/reset-password/{token}', 'AuthController@resetPasswordForm');
$router->post('/reset-password/{token}', 'AuthController@resetPassword');

// Public pages
$router->get('/', 'HomeController@index');
$router->get('/about', 'HomeController@about');
$router->get('/pricing', 'HomeController@pricing');
$router->get('/contact', 'HomeController@contact');
$router->post('/contact', 'HomeController@submitContact');
$router->get('/faq', 'HomeController@faq');
$router->get('/program', 'HomeController@program');
$router->get('/espaceenfant', 'HomeController@espaceEnfant');
$router->get('/espaceparent', 'HomeController@espaceParent');
$router->get('/specialists', 'HomeController@specialists');
$router->get('/chatbot', 'HomeController@chatbot');
$router->get('/chatbotstart', 'HomeController@chatbotStart', ['auth']);
$router->post('/chatbot/message', 'HomeController@chatbotMessage', ['auth']);

// Newsletter
$router->post('/subscribe', 'HomeController@subscribe');

// Parent dashboard routes
$router->get('/parent/dashboard', 'ParentController@dashboard', ['auth', 'role:parent']);
$router->get('/parent/children', 'ParentController@children', ['auth', 'role:parent']);
$router->get('/parent/children/add', 'ParentController@addChildForm', ['auth', 'role:parent']);
$router->post('/parent/children/add', 'ParentController@addChild', ['auth', 'role:parent']);
$router->get('/parent/children/{id}/edit', 'ParentController@editChildForm', ['auth', 'role:parent']);
$router->post('/parent/children/{id}/edit', 'ParentController@editChild', ['auth', 'role:parent']);
$router->post('/parent/children/{id}/delete', 'ParentController@deleteChild', ['auth', 'role:parent']);
$router->post('/parent/children/{id}/avatar-upload', 'ParentController@childrenAvatarUpload', ['auth', 'role:parent']);
$router->get('/parent/children/{id}', 'ParentController@childDetail', ['auth', 'role:parent']);
$router->get('/parent/quiz', 'ParentController@quizList', ['auth', 'role:parent']);
$router->get('/parent/quiz/start/{childId}', 'ParentController@quizStart', ['auth', 'role:parent']);
$router->post('/parent/quiz/submit', 'ParentController@quizSubmit', ['auth', 'role:parent']);
$router->get('/parent/quiz/results/{attemptId}', 'ParentController@quizResults', ['auth', 'role:parent']);
$router->get('/parent/progress', 'ParentController@progress', ['auth', 'role:parent']);
$router->get('/parent/progress/activities/{childId}', 'ParentController@childActivities', ['auth', 'role:parent']);
$router->get('/parent/specialists', 'ParentController@specialists', ['auth', 'role:parent']);
$router->get('/parent/appointments', 'ParentController@appointments', ['auth', 'role:parent']);
$router->get('/parent/appointments/book', 'ParentController@bookAppointmentForm', ['auth', 'role:parent']);
$router->post('/parent/appointments/book', 'ParentController@bookAppointment', ['auth', 'role:parent']);
$router->post('/parent/appointments/{id}/cancel', 'ParentController@cancelAppointment', ['auth', 'role:parent']);
$router->get('/parent/appointments/{id}/reschedule', 'ParentController@rescheduleAppointmentForm', ['auth', 'role:parent']);
$router->post('/parent/appointments/{id}/reschedule', 'ParentController@rescheduleAppointment', ['auth', 'role:parent']);
$router->get('/parent/messages', 'ParentController@messages', ['auth', 'role:parent']);
$router->get('/parent/messages/send/{receiverId}', 'ParentController@sendMessageForm', ['auth', 'role:parent']);
$router->post('/parent/messages/send', 'ParentController@sendMessage', ['auth', 'role:parent']);
$router->get('/parent/messages/thread/{partnerId}', 'ParentController@messageThread', ['auth', 'role:parent']);
$router->post('/parent/messages/reply', 'ParentController@replyMessage', ['auth', 'role:parent']);
$router->get('/parent/chatbot', 'ParentController@chatbot', ['auth', 'role:parent']);
$router->post('/parent/chatbot/message', 'ParentController@chatbotMessage', ['auth', 'role:parent']);
$router->get('/parent/settings', 'ParentController@settings', ['auth', 'role:parent']);
$router->post('/parent/settings', 'ParentController@updateSettings', ['auth', 'role:parent']);
$router->get('/parent/subscription', 'ParentController@subscription', ['auth', 'role:parent']);
$router->post('/parent/subscription/upgrade', 'ParentController@upgradeSubscription', ['auth', 'role:parent']);

// Specialist dashboard routes
$router->get('/specialist/dashboard', 'SpecialistController@dashboard', ['auth', 'role:specialist']);
$router->get('/specialist/patients', 'SpecialistController@patients', ['auth', 'role:specialist']);
$router->get('/specialist/patients/{id}', 'SpecialistController@patientDetail', ['auth', 'role:specialist']);
$router->post('/specialist/patients/{id}/notes', 'SpecialistController@addPatientNote', ['auth', 'role:specialist']);
$router->get('/specialist/patients/export', 'SpecialistController@exportPatientsCSV', ['auth', 'role:specialist']);
$router->get('/specialist/appointments', 'SpecialistController@appointments', ['auth', 'role:specialist']);
$router->post('/specialist/appointments/{id}/status', 'SpecialistController@updateAppointmentStatus', ['auth', 'role:specialist']);
$router->post('/specialist/appointments/{id}/cancel', 'SpecialistController@cancelAppointment', ['auth', 'role:specialist']);
$router->post('/specialist/appointments/{id}/complete', 'SpecialistController@completeAppointment', ['auth', 'role:specialist']);
$router->get('/specialist/messages', 'SpecialistController@messages', ['auth', 'role:specialist']);
$router->get('/specialist/messages/send/{receiverId}', 'SpecialistController@sendMessageForm', ['auth', 'role:specialist']);
$router->get('/specialist/messages/thread/{partnerId}', 'SpecialistController@messageThread', ['auth', 'role:specialist']);
$router->post('/specialist/messages/send', 'SpecialistController@sendMessage', ['auth', 'role:specialist']);
$router->get('/specialist/schedule', 'SpecialistController@schedule', ['auth', 'role:specialist']);
$router->post('/specialist/schedule', 'SpecialistController@updateSchedule', ['auth', 'role:specialist']);
$router->get('/specialist/calendar', 'SpecialistController@calendar', ['auth', 'role:specialist']);
$router->get('/specialist/settings', 'SpecialistController@settings', ['auth', 'role:specialist']);
$router->post('/specialist/settings', 'SpecialistController@updateSettings', ['auth', 'role:specialist']);

// Admin dashboard routes
$router->get('/admin/dashboard', 'AdminController@dashboard', ['auth', 'role:admin']);
$router->get('/admin/users', 'AdminController@users', ['auth', 'role:admin']);
$router->get('/admin/users/add', 'AdminController@addUserForm', ['auth', 'role:admin']);
$router->post('/admin/users/add', 'AdminController@addUser', ['auth', 'role:admin']);
$router->get('/admin/users/{id}/edit', 'AdminController@editUserForm', ['auth', 'role:admin']);
$router->post('/admin/users/{id}/edit', 'AdminController@editUser', ['auth', 'role:admin']);
$router->post('/admin/users/{id}/delete', 'AdminController@deleteUser', ['auth', 'role:admin']);
$router->post('/admin/users/{id}/toggle-status', 'AdminController@toggleUserStatus', ['auth', 'role:admin']);
$router->get('/admin/specialists', 'AdminController@manageSpecialists', ['auth', 'role:admin']);
$router->post('/admin/specialists/{id}/approve', 'AdminController@approveSpecialist', ['auth', 'role:admin']);
$router->get('/admin/specialists/{id}/edit', 'AdminController@editSpecialistDetailsForm', ['auth', 'role:admin']);
$router->post('/admin/specialists/{id}/edit', 'AdminController@editSpecialistDetails', ['auth', 'role:admin']);
$router->get('/admin/quiz', 'AdminController@quiz', ['auth', 'role:admin']);
$router->get('/admin/quiz/add', 'AdminController@addQuizForm', ['auth', 'role:admin']);
$router->post('/admin/quiz/add', 'AdminController@addQuiz', ['auth', 'role:admin']);
$router->get('/admin/quiz/{id}/edit', 'AdminController@editQuizForm', ['auth', 'role:admin']);
$router->post('/admin/quiz/{id}/edit', 'AdminController@editQuiz', ['auth', 'role:admin']);
$router->post('/admin/quiz/{id}/delete', 'AdminController@deleteQuiz', ['auth', 'role:admin']);
$router->get('/admin/quiz/{id}/options', 'AdminController@quizOptions', ['auth', 'role:admin']);
$router->get('/admin/quiz/options/add/{questionId}', 'AdminController@addQuizOptionForm', ['auth', 'role:admin']);
$router->post('/admin/quiz/options/add', 'AdminController@addQuizOption', ['auth', 'role:admin']);
$router->get('/admin/quiz/options/{id}/edit', 'AdminController@editQuizOptionForm', ['auth', 'role:admin']);
$router->post('/admin/quiz/options/{id}/edit', 'AdminController@editQuizOption', ['auth', 'role:admin']);
$router->post('/admin/quiz/options/{id}/delete', 'AdminController@deleteQuizOption', ['auth', 'role:admin']);
$router->get('/admin/activities', 'AdminController@activities', ['auth', 'role:admin']);
$router->get('/admin/activities/add', 'AdminController@addActivityForm', ['auth', 'role:admin']);
$router->post('/admin/activities/add', 'AdminController@addActivity', ['auth', 'role:admin']);
$router->get('/admin/activities/{id}/edit', 'AdminController@editActivityForm', ['auth', 'role:admin']);
$router->post('/admin/activities/{id}/edit', 'AdminController@editActivity', ['auth', 'role:admin']);
$router->post('/admin/activities/{id}/delete', 'AdminController@deleteActivity', ['auth', 'role:admin']);
$router->get('/admin/appointments', 'AdminController@appointments', ['auth', 'role:admin']);
$router->get('/admin/messages', 'AdminController@messages', ['auth', 'role:admin']);
$router->get('/admin/subscriptions', 'AdminController@subscriptions', ['auth', 'role:admin']);
$router->get('/admin/subscriptions/add', 'AdminController@addSubscriptionForm', ['auth', 'role:admin']);
$router->post('/admin/subscriptions/add', 'AdminController@addSubscription', ['auth', 'role:admin']);
$router->get('/admin/subscriptions/{id}/edit', 'AdminController@editSubscriptionForm', ['auth', 'role:admin']);
$router->post('/admin/subscriptions/{id}/edit', 'AdminController@editSubscription', ['auth', 'role:admin']);
$router->post('/admin/subscriptions/{id}/delete', 'AdminController@deleteSubscription', ['auth', 'role:admin']);
$router->get('/admin/contacts', 'AdminController@contacts', ['auth', 'role:admin']);
$router->post('/admin/contacts/{id}/read', 'AdminController@markContactRead', ['auth', 'role:admin']);
$router->post('/admin/contacts/{id}/delete', 'AdminController@deleteContact', ['auth', 'role:admin']);
$router->get('/admin/faq', 'AdminController@faq', ['auth', 'role:admin']);
$router->get('/admin/faq/add', 'AdminController@addFaqForm', ['auth', 'role:admin']);
$router->post('/admin/faq/add', 'AdminController@addFaq', ['auth', 'role:admin']);
$router->get('/admin/faq/{id}/edit', 'AdminController@editFaqForm', ['auth', 'role:admin']);
$router->post('/admin/faq/{id}/edit', 'AdminController@editFaq', ['auth', 'role:admin']);
$router->post('/admin/faq/{id}/delete', 'AdminController@deleteFaq', ['auth', 'role:admin']);
$router->get('/admin/chatbot', 'AdminController@chatbot', ['auth', 'role:admin']);
$router->get('/admin/chatbot/add', 'AdminController@addChatbotForm', ['auth', 'role:admin']);
$router->post('/admin/chatbot/add', 'AdminController@addChatbot', ['auth', 'role:admin']);
$router->get('/admin/chatbot/{id}/edit', 'AdminController@editChatbotForm', ['auth', 'role:admin']);
$router->post('/admin/chatbot/{id}/edit', 'AdminController@editChatbot', ['auth', 'role:admin']);
$router->post('/admin/chatbot/{id}/delete', 'AdminController@deleteChatbot', ['auth', 'role:admin']);
$router->post('/admin/chatbot/config', 'AdminController@updateChatbotConfig', ['auth', 'role:admin']);
$router->get('/admin/progress', 'AdminController@progress', ['auth', 'role:admin']);
$router->get('/admin/progress/child/{childId}', 'AdminController@childProgress', ['auth', 'role:admin']);
$router->get('/admin/settings', 'AdminController@settings', ['auth', 'role:admin']);
$router->post('/admin/settings', 'AdminController@updateSettings', ['auth', 'role:admin']);
