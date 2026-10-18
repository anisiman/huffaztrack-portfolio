<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\LoginController;
use App\Http\Controllers\ForgotPasswordController;
use App\Http\Controllers\ResetPasswordController;
use App\Http\Controllers\AdminDashboardController;
use App\Http\Controllers\StudentDashboardController;
use App\Http\Controllers\StudentNoteController;
use App\Http\Controllers\AttendanceController;
use App\Http\Controllers\RegisterController;
use App\Http\Controllers\AdminAttendanceController;
use App\Http\Controllers\AdminHafazanController;
use App\Http\Controllers\AdminLecturerController;
use App\Http\Controllers\AdminMasterlistController;
use App\Http\Controllers\AdminStudentAccountController; // iman branch
use App\Http\Controllers\AdminAdminAccountController;
use App\Http\Controllers\StudentAttendanceController;
use App\Http\Controllers\StudentHafazanController;
use App\Http\Controllers\BatchController;               // teammate
use App\Http\Controllers\LecturerHifzController;
use App\Http\Controllers\LecturerDashboardController;
use App\Http\Controllers\LecturerAnalyticsController;
use App\Http\Controllers\LecturerInterventionController;
use App\Http\Controllers\WeeklyReportController;
use App\Http\Controllers\LecturerStudentController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\AttendanceAppealController;
use App\Http\Controllers\AttendanceDocumentController;
use App\Http\Controllers\AdminAcademicPeriodController;
use App\Http\Controllers\AdminSubjectController;
use App\Http\Controllers\LecturerSubjectController;
use App\Http\Controllers\StudentSubjectController;
use App\Http\Controllers\AdminSubjectAttendanceController;
use App\Http\Controllers\LecturerSubjectAttendanceController;
use App\Http\Controllers\StudentSubjectAttendanceController;
use App\Http\Controllers\SubjectAttendanceReportController;
use App\Http\Controllers\AdminSubjectSectionController;
use App\Http\Controllers\LecturerSubjectSectionController;
use App\Http\Controllers\StudentSubjectSectionController;

Route::get('/', function () {
    return view('landing');
})->name('landing');

Route::get('/login',[LoginController::class,'showLoginForm'])->name('login');
Route::post('/login',[LoginController::class,'login']);


// ================= PASSWORD RESET =================
Route::get('/forgot-password', [ForgotPasswordController::class, 'showLinkRequestForm'])->name('password.request');
Route::post('/forgot-password', [ForgotPasswordController::class, 'sendResetLinkEmail'])->middleware('throttle:6,1')->name('password.email');
Route::get('/reset-password/{token}', [ResetPasswordController::class, 'showResetForm'])->name('password.reset');
Route::post('/reset-password', [ResetPasswordController::class, 'reset'])->middleware('throttle:6,1')->name('password.update');


// ================= REGISTER =================
Route::get('/registration', [RegisterController::class, 'show'])->name('registration');
Route::post('/registration', [RegisterController::class, 'store'])->name('registration.store');


// ================= ADMIN DASHBOARD =================
Route::middleware(['auth', 'role:admin'])->group(function () {
    Route::get('/admin/dashboard',[AdminDashboardController::class,'index'])->name('admin.dashboard');
    Route::get('/admin/analytics', [AdminDashboardController::class, 'analytics'])->name('admin.analytics');
});


// ================= ADMIN EXTRA =================
Route::middleware(['auth', 'role:admin'])->prefix('admin')->name('admin.')->group(function () {

    Route::get('/masterlist', [AdminMasterlistController::class, 'index'])->name('masterlist');
    Route::get('/masterlist/create', [AdminMasterlistController::class, 'create'])->name('masterlist.create');
    Route::post('/masterlist', [AdminMasterlistController::class, 'store'])->name('masterlist.store');
    Route::post('/masterlist/upload', [AdminMasterlistController::class, 'upload'])->name('masterlist.upload');
    Route::post('/masterlist/{student}/khatam', [AdminMasterlistController::class, 'addKhatamRecord'])->name('masterlist.khatam.store')->where('student', '.*');
    Route::delete('/masterlist/{student}/khatam/{khatam}', [AdminMasterlistController::class, 'deleteKhatamRecord'])->name('masterlist.khatam.destroy')->where('student', '.*');
    Route::get('/masterlist/{student}/edit', [AdminMasterlistController::class, 'edit'])->name('masterlist.edit')->where('student', '.*');
    Route::put('/masterlist/{student}', [AdminMasterlistController::class, 'update'])->name('masterlist.update')->where('student', '.*');
    Route::get('/masterlist/{student}', [AdminMasterlistController::class, 'show'])->name('masterlist.show')->where('student', '.*');
    Route::delete('/masterlist/{student}', [AdminMasterlistController::class, 'destroy'])->name('masterlist.destroy')->where('student', '.*');

    Route::get('/dashboard/top-performers', [AdminDashboardController::class, 'topPerformers'])->name('dashboard.topPerformers');
    Route::get('/dashboard/at-risk', [AdminDashboardController::class, 'atRiskList'])->name('dashboard.atRisk');

    // Admin account management
    Route::get('/admin-accounts', [AdminAdminAccountController::class, 'index'])->name('adminAccounts');
    Route::post('/admin-accounts', [AdminAdminAccountController::class, 'store'])->name('adminAccounts.store');
    Route::put('/admin-accounts/{user}', [AdminAdminAccountController::class, 'update'])->name('adminAccounts.update');
    Route::delete('/admin-accounts/{user}', [AdminAdminAccountController::class, 'destroy'])->name('adminAccounts.destroy');
    Route::post('/admin-accounts/{user}/reset-password', [AdminAdminAccountController::class, 'resetPassword'])->name('adminAccounts.reset-password');

    // Student account management (iman branch)
    Route::get('/students', [AdminStudentAccountController::class, 'index'])->name('students');
    Route::post('/students', [AdminStudentAccountController::class, 'store'])->name('students.store');
    Route::put('/students/{student}', [AdminStudentAccountController::class, 'update'])->name('students.update');
    Route::delete('/students/{student}', [AdminStudentAccountController::class, 'destroy'])->name('students.destroy');
    Route::post('/students/{student}/reset-password', [AdminStudentAccountController::class, 'resetPassword'])->name('students.reset-password');

    // Batch management (teammate)
    Route::resource('batches', BatchController::class)->except(['create', 'edit', 'show']);

    // Academic period management
    Route::resource('academic-periods', AdminAcademicPeriodController::class)->except(['create', 'edit', 'show']);
    Route::post('/academic-periods/{academicPeriod}/seed-from-live', [AdminAcademicPeriodController::class, 'seedFromLive'])->name('academic-periods.seedFromLive');
    Route::put('/academic-periods/{academicPeriod}/thresholds', [AdminAcademicPeriodController::class, 'updateThresholds'])->name('academic-periods.thresholds.update');
    Route::put('/academic-periods/{academicPeriod}/batch-levels', [AdminAcademicPeriodController::class, 'updateBatchLevels'])->name('academic-periods.batchLevels.update');

    // Subject management
    Route::get('/subjects', [AdminSubjectController::class, 'index'])->name('subjects');
    Route::post('/subjects', [AdminSubjectController::class, 'store'])->name('subjects.store');
    Route::put('/subjects/{subject}', [AdminSubjectController::class, 'update'])->name('subjects.update');
    Route::delete('/subjects/{subject}', [AdminSubjectController::class, 'destroy'])->name('subjects.destroy');
    Route::get('/subjects/{subject}/marks', [AdminSubjectController::class, 'marks'])->name('subjects.marks');

    // Subject Sections & enrollment management
    Route::get('/sections', [AdminSubjectSectionController::class, 'overview'])->name('sections');
    Route::put('/sections/enrollment-toggle/{academicPeriod}', [AdminSubjectSectionController::class, 'toggleEnrollment'])->name('sections.enrollmentToggle');
    Route::get('/sections/{subject}', [AdminSubjectSectionController::class, 'manage'])->name('sections.manage');
    Route::post('/sections/{subject}', [AdminSubjectSectionController::class, 'store'])->name('sections.store');
    Route::put('/sections/{subject}/{section}', [AdminSubjectSectionController::class, 'update'])->name('sections.update');
    Route::delete('/sections/{subject}/{section}', [AdminSubjectSectionController::class, 'destroy'])->name('sections.destroy');
    Route::post('/sections/{subject}/enroll', [AdminSubjectSectionController::class, 'enrollStudent'])->name('sections.enroll');
    Route::put('/sections/{subject}/enrollments/{enrollment}', [AdminSubjectSectionController::class, 'moveStudent'])->name('sections.enrollments.move');
    Route::delete('/sections/{subject}/enrollments/{enrollment}', [AdminSubjectSectionController::class, 'removeStudent'])->name('sections.enrollments.destroy');

    // Subject attendance
    Route::get('/subject-attendance', [AdminSubjectAttendanceController::class, 'index'])->name('subjectAttendance');
    Route::get('/subject-attendance/details/{id}', [AdminSubjectAttendanceController::class, 'details'])->name('subjectAttendance.details');

    // Lecturer management
    Route::get('/lecturers', [AdminLecturerController::class, 'index'])->name('lecturers');
    Route::get('/lecturer-accounts', [AdminLecturerController::class, 'accountsIndex'])->name('lecturerAccounts');
    Route::post('/lecturers', [AdminLecturerController::class, 'store'])->name('lecturers.store');
    Route::post('/lecturers/profile', [AdminLecturerController::class, 'storeProfile'])->name('lecturers.storeProfile');
    Route::post('/lecturers/assign', [AdminLecturerController::class, 'assignStudent'])->name('lecturers.assignStudent');
    Route::get('/lecturers/{lecturer_id}', [AdminLecturerController::class, 'show'])->name('lecturers.show')->whereNumber('lecturer_id');
    Route::post('/lecturers/{lecturer_id}/transfer', [AdminLecturerController::class, 'transferHalaqah'])->name('lecturers.transfer')->whereNumber('lecturer_id');
    Route::put('/lecturers/{lecturer}', [AdminLecturerController::class, 'update'])->name('lecturers.update');
    Route::post('/lecturers/{lecturer}/reset-password', [AdminLecturerController::class, 'resetPassword'])->name('lecturers.resetPassword');
    Route::delete('/lecturers/{lecturer_id}', [AdminLecturerController::class, 'destroy'])->name('lecturers.destroy');
    Route::delete('/lecturer-accounts/{user}/account', [AdminLecturerController::class, 'destroyAccount'])->name('lecturers.destroyAccount');
    Route::delete('/lecturers/remove/{student_id}', [AdminLecturerController::class, 'removeStudent'])->name('lecturers.removeStudent')->where('student_id', '.*');

    Route::get('/attendance',  [AdminAttendanceController::class, 'index'])->name('attendance');
    Route::post('/attendance', [AdminAttendanceController::class, 'store'])->name('attendance.store');
    Route::get('/attendance/details', [AdminAttendanceController::class, 'details'])->name('attendance.details');
    Route::get('/hafazan', [AdminHafazanController::class, 'index'])->name('hafazan');
    Route::get('/hafazan/details', [AdminHafazanController::class, 'details'])->name('hafazan.details');
    Route::post('/hafazan/muqarrar', [AdminHafazanController::class, 'storeMuqarrar'])->name('hafazan.muqarrar.store');
    Route::put('/hafazan/muqarrar/{id}', [AdminHafazanController::class, 'updateMuqarrar'])->name('hafazan.muqarrar.update');
    Route::delete('/hafazan/muqarrar/{id}', [AdminHafazanController::class, 'destroyMuqarrar'])->name('hafazan.muqarrar.destroy');
    Route::get('/reports', [WeeklyReportController::class, 'adminHafazanHub'])->name('reports');
    Route::get('/reports/hafazan/daily', [WeeklyReportController::class, 'adminDailyIndex'])->name('reports.hafazan.daily');
    Route::get('/reports/hafazan/daily/pdf', [WeeklyReportController::class, 'adminHafazanDailyPdf'])->name('reports.hafazan.daily.pdf');
    Route::get('/reports/hafazan/daily/{id}', [WeeklyReportController::class, 'adminHafazanDailyLecturerDetail'])->name('reports.hafazan.daily.lecturer');
    Route::get('/reports/hafazan/weekly', [WeeklyReportController::class, 'adminIndex'])->name('reports.hafazan.weekly');
    Route::get('/reports/hafazan/weekly/pdf', [WeeklyReportController::class, 'adminHafazanWeeklyPdf'])->name('reports.hafazan.weekly.pdf');
    Route::get('/reports/hafazan/monthly', [WeeklyReportController::class, 'adminMonthlyIndex'])->name('reports.hafazan.monthly');
    Route::get('/reports/hafazan/monthly/pdf', [WeeklyReportController::class, 'adminHafazanMonthlyPdf'])->name('reports.hafazan.monthly.pdf');
    Route::get('/reports/hafazan/monthly/{id}', [WeeklyReportController::class, 'adminHafazanMonthlyLecturerDetail'])->name('reports.hafazan.monthly.lecturer');
    Route::get('/reports/hafazan/lecturer/{id}', [WeeklyReportController::class, 'adminLecturerDetail'])->name('reports.hafazan.lecturer');
    Route::get('/reports/attendance/daily', [WeeklyReportController::class, 'adminAttendanceDaily'])->name('reports.attendance.daily');
    Route::get('/reports/attendance/daily/pdf', [WeeklyReportController::class, 'adminAttendanceDailyPdf'])->name('reports.attendance.daily.pdf');
    Route::get('/reports/attendance/daily/{id}', [WeeklyReportController::class, 'adminAttendanceDailyLecturerDetail'])->name('reports.attendance.daily.lecturer');
    Route::get('/reports/attendance/weekly', [WeeklyReportController::class, 'adminAttendanceWeekly'])->name('reports.attendance.weekly');
    Route::get('/reports/attendance/weekly/pdf', [WeeklyReportController::class, 'adminAttendanceWeeklyPdf'])->name('reports.attendance.weekly.pdf');
    Route::get('/reports/attendance/weekly/{id}', [WeeklyReportController::class, 'adminAttendanceWeeklyLecturerDetail'])->name('reports.attendance.weekly.lecturer');
    Route::get('/reports/attendance/monthly', [WeeklyReportController::class, 'adminAttendanceMonthly'])->name('reports.attendance.monthly');
    Route::get('/reports/attendance/monthly/pdf', [WeeklyReportController::class, 'adminAttendanceMonthlyPdf'])->name('reports.attendance.monthly.pdf');
    Route::get('/reports/attendance/monthly/daily-register/pdf', [WeeklyReportController::class, 'adminAttendanceMonthlyDailyRegisterPdf'])->name('reports.attendance.monthly.dailyRegister.pdf');
    Route::get('/reports/attendance/monthly/{id}', [WeeklyReportController::class, 'adminAttendanceMonthlyLecturerDetail'])->name('reports.attendance.monthly.lecturer');
    Route::get('/reports/hafazan/semester', [WeeklyReportController::class, 'adminHafazanSemester'])->name('reports.hafazan.semester');
    Route::get('/reports/hafazan/semester/pdf', [WeeklyReportController::class, 'adminHafazanSemesterPdf'])->name('reports.hafazan.semester.pdf');
    Route::get('/reports/hafazan/semester/{id}', [WeeklyReportController::class, 'adminHafazanSemesterLecturerDetail'])->name('reports.hafazan.semester.lecturer');
    Route::get('/reports/attendance/semester', [WeeklyReportController::class, 'adminAttendanceSemester'])->name('reports.attendance.semester');
    Route::get('/reports/attendance/semester/pdf', [WeeklyReportController::class, 'adminAttendanceSemesterPdf'])->name('reports.attendance.semester.pdf');
    Route::get('/reports/subject-attendance', [SubjectAttendanceReportController::class, 'adminIndex'])->name('reports.subjectAttendance');
    Route::get('/reports/subject-attendance/pdf', [SubjectAttendanceReportController::class, 'adminPdf'])->name('reports.subjectAttendance.pdf');
});


// ================= LECTURER =================
Route::prefix('lecturer')->middleware(['auth', 'role:lecturer'])->name('lecturer.')->group(function () {

    Route::get('/dashboard', [LecturerDashboardController::class, 'index'])->name('dashboard');

    Route::get('/attendance',[AttendanceController::class,'index'])->name('attendance');
    Route::post('/attendance',[AttendanceController::class,'store'])->name('attendance.store');
    Route::post('/attendance/qr', [AttendanceController::class, 'generateQr'])->name('attendance.qr');
    Route::get('/attendance/qr/{sessionId}', [AttendanceController::class, 'showQrPage'])->name('attendance.qr.show');
    Route::post('/attendance/qr/{sessionId}/end', [AttendanceController::class, 'endQr'])->name('attendance.qr.end');
    Route::post('/attendance/qr/{sessionId}/resume', [AttendanceController::class, 'resumeQr'])->name('attendance.qr.resume');

    Route::get('/hifz', [LecturerHifzController::class, 'index'])->name('hifz');
    Route::post('/hifz/save-all', [LecturerHifzController::class, 'saveAll'])->name('hifz.saveAll');
    Route::get('/hifz/student-history', [LecturerHifzController::class, 'studentHistory'])->name('hifz.studentHistory');
    Route::get('/analytics', [LecturerAnalyticsController::class, 'index'])->name('analytics');
    Route::get('/analytics/intervention/{studentId}', [LecturerInterventionController::class, 'show'])
        ->where('studentId', '.*')
        ->name('analytics.intervention');
    Route::get('/reports', [WeeklyReportController::class, 'lecturerHub'])->name('reports');
    Route::get('/reports/weekly', [WeeklyReportController::class, 'lecturerIndex'])->name('reports.weekly');
    Route::get('/reports/weekly/pdf', [WeeklyReportController::class, 'lecturerWeeklyPdf'])->name('reports.weekly.pdf');
    Route::get('/reports/daily', [WeeklyReportController::class, 'lecturerDaily'])->name('reports.daily');
    Route::get('/reports/daily/pdf', [WeeklyReportController::class, 'lecturerDailyPdf'])->name('reports.daily.pdf');
    Route::get('/reports/attendance', [WeeklyReportController::class, 'lecturerAttendance'])->name('reports.attendance');
    Route::get('/reports/attendance/pdf', [WeeklyReportController::class, 'lecturerAttendancePdf'])->name('reports.attendance.pdf');
    // New attendance reports
    Route::get('/reports/attendance/daily', [WeeklyReportController::class, 'lecturerAttendanceDaily'])->name('reports.attendance.daily');
    Route::get('/reports/attendance/daily/pdf', [WeeklyReportController::class, 'lecturerAttendanceDailyPdf'])->name('reports.attendance.daily.pdf');
    Route::get('/reports/attendance/weekly', [WeeklyReportController::class, 'lecturerAttendanceWeekly'])->name('reports.attendance.weekly');
    Route::get('/reports/attendance/weekly/pdf', [WeeklyReportController::class, 'lecturerAttendanceWeeklyPdf'])->name('reports.attendance.weekly.pdf');
    // New hafazan monthly report
    Route::get('/reports/hafazan/monthly', [WeeklyReportController::class, 'lecturerHafazanMonthly'])->name('reports.hafazan.monthly');
    Route::get('/reports/hafazan/monthly/pdf', [WeeklyReportController::class, 'lecturerHafazanMonthlyPdf'])->name('reports.hafazan.monthly.pdf');
    Route::get('/reports/hafazan/semester', [WeeklyReportController::class, 'lecturerHafazanSemester'])->name('reports.hafazan.semester');
    Route::get('/reports/hafazan/semester/pdf', [WeeklyReportController::class, 'lecturerHafazanSemesterPdf'])->name('reports.hafazan.semester.pdf');
    Route::get('/reports/attendance/semester', [WeeklyReportController::class, 'lecturerAttendanceSemester'])->name('reports.attendance.semester');
    Route::get('/reports/attendance/semester/pdf', [WeeklyReportController::class, 'lecturerAttendanceSemesterPdf'])->name('reports.attendance.semester.pdf');
    Route::view('/settings', 'lecturer.settings')->name('settings');

    Route::get('/students', [LecturerStudentController::class, 'index'])->name('students');
    Route::get('/students/{studentId}', [LecturerStudentController::class, 'show'])->where('studentId', '.*')->name('students.show');
    Route::post('/students/{studentId}/khatam', [LecturerStudentController::class, 'storeKhatam'])->where('studentId', '.*')->name('students.khatam.store');
    Route::delete('/students/{studentId}/khatam/{khatam}', [LecturerStudentController::class, 'destroyKhatam'])->where('studentId', '.*')->name('students.khatam.destroy');
    Route::post('/students/{studentId}/notes', [LecturerStudentController::class, 'storeNote'])->where('studentId', '.*')->name('students.notes.store');
    Route::put('/students/{studentId}/notes/{note}', [LecturerStudentController::class, 'updateNote'])->where('studentId', '.*')->name('students.notes.update');
    Route::delete('/students/{studentId}/notes/{note}', [LecturerStudentController::class, 'destroyNote'])->where('studentId', '.*')->name('students.notes.destroy');

    Route::get('/subjects', [LecturerSubjectController::class, 'index'])->name('subjects');
    Route::get('/subjects/{subject}', [LecturerSubjectController::class, 'show'])->name('subjects.show');
    Route::post('/subjects/{subject}/marks', [LecturerSubjectController::class, 'saveMarks'])->name('subjects.marks.store');

    Route::get('/sections', [LecturerSubjectSectionController::class, 'index'])->name('sections');

    Route::get('/subject-attendance', [LecturerSubjectAttendanceController::class, 'index'])->name('subjectAttendance');
    Route::post('/subject-attendance/sessions', [LecturerSubjectAttendanceController::class, 'createSession'])->name('subjectAttendance.createSession');
    Route::get('/subject-attendance/sessions/{sessionId}', [LecturerSubjectAttendanceController::class, 'show'])->name('subjectAttendance.show');
    Route::post('/subject-attendance/sessions/{sessionId}/end', [LecturerSubjectAttendanceController::class, 'end'])->name('subjectAttendance.end');
    Route::post('/subject-attendance/sessions/{sessionId}/resume', [LecturerSubjectAttendanceController::class, 'resume'])->name('subjectAttendance.resume');
    Route::post('/subject-attendance/sessions/{sessionId}/manual', [LecturerSubjectAttendanceController::class, 'manualUpdate'])->name('subjectAttendance.manualUpdate');

    Route::get('/reports/subject-attendance', [SubjectAttendanceReportController::class, 'lecturerIndex'])->name('reports.subjectAttendance');
    Route::get('/reports/subject-attendance/pdf', [SubjectAttendanceReportController::class, 'lecturerPdf'])->name('reports.subjectAttendance.pdf');

    Route::get('/attendance/appeals', [AttendanceAppealController::class, 'lecturerIndex'])->name('attendance.appeals');
    Route::post('/attendance/appeals/{id}/approve', [AttendanceAppealController::class, 'approve'])->name('attendance.appeals.approve');
    Route::post('/attendance/appeals/{id}/reject',  [AttendanceAppealController::class, 'reject'])->name('attendance.appeals.reject');
    Route::post('/attendance/appeals/{id}/undo',    [AttendanceAppealController::class, 'undo'])->name('attendance.appeals.undo');
});


// ================= STUDENT =================
Route::middleware('auth')->group(function () {
    Route::get('/student/dashboard',  [StudentDashboardController::class,  'index'])->name('student.dashboard');
    Route::get('/student/attendance', [StudentAttendanceController::class, 'index'])->name('student.attendance');
    Route::get('/student/hafazan',    [StudentHafazanController::class,    'index'])->name('student.hafazan');
    Route::get('/student/subjects',   [StudentSubjectController::class,    'index'])->name('student.subjects');
    Route::get('/student/section-enrollment', [StudentSubjectSectionController::class, 'index'])->name('student.sectionEnrollment');
    Route::post('/student/section-enrollment', [StudentSubjectSectionController::class, 'store'])->name('student.sectionEnrollment.store');
    Route::get('/student/subject-attendance', [StudentSubjectAttendanceController::class, 'index'])->name('student.subjectAttendance');
    Route::get('/student/biodata',    [ProfileController::class, 'biodata'])->name('student.biodata');
    Route::get('/student/notes',      [StudentNoteController::class, 'index'])->name('student.notes');
    Route::post('/student/attendance/appeal', [AttendanceAppealController::class, 'store'])->name('student.attendance.appeal.store');
});

Route::get('/attendance/scan/{sessionId}', [AttendanceController::class, 'scan'])->name('attendance.scan');
Route::get('/attendance/scan/{sessionId}/mark', [AttendanceController::class, 'markScan'])
    ->middleware('auth')
    ->name('attendance.scan.mark');
Route::get('/attendance/confirmation/{sessionId}', [AttendanceController::class, 'confirmation'])->name('attendance.scan.confirmation');

Route::get('/attendance/subject/scan/{sessionId}', [LecturerSubjectAttendanceController::class, 'scan'])->name('attendance.subject.scan');
Route::get('/attendance/subject/scan/{sessionId}/mark', [LecturerSubjectAttendanceController::class, 'markScan'])
    ->middleware('auth')
    ->name('attendance.subject.scan.mark');
Route::get('/attendance/subject/confirmation/{sessionId}', [LecturerSubjectAttendanceController::class, 'confirmation'])->name('attendance.subject.scan.confirmation');


// ================= NOTIFICATIONS (all roles) =================
Route::middleware('auth')->group(function () {
    Route::get('/notifications/{id}/open', [NotificationController::class, 'open'])->name('notifications.open');
    Route::post('/notifications/read-all', [NotificationController::class, 'readAll'])->name('notifications.readAll');
});


// ================= ATTENDANCE DOCUMENTS (all roles, checked per record) =================
Route::middleware('auth')->group(function () {
    Route::get('/documents/attendance/{attendanceId}', [AttendanceDocumentController::class, 'attendance'])->whereNumber('attendanceId')->name('documents.attendance');
    Route::get('/documents/appeals/{appealId}', [AttendanceDocumentController::class, 'appeal'])->whereNumber('appealId')->name('documents.appeal');
});


// ================= PROFILE (all roles) =================
Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'show'])->name('profile.show');
    Route::post('/profile/password', [ProfileController::class, 'updatePassword'])->name('profile.updatePassword');
    Route::post('/profile/avatar', [ProfileController::class, 'updateAvatar'])->name('profile.updateAvatar');
    Route::delete('/profile/avatar', [ProfileController::class, 'removeAvatar'])->name('profile.removeAvatar');
});


// ================= LOGOUT =================
Route::post('/logout', function () {
    Auth::logout();
    request()->session()->invalidate();
    request()->session()->regenerateToken();

    return redirect('/login');
})->middleware('auth')->name('logout');
