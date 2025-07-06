<?php

use App\Http\Controllers\Api\Admin\GroupController;
use App\Http\Controllers\Api\Admin\AssignmentsController;
use App\Http\Controllers\Api\Admin\BundleController;
use App\Http\Controllers\Api\Admin\WebinarStatisticController;
use App\Http\Controllers\Api\Admin\WebinarController;
use App\Http\Controllers\Api\Admin\UserController;
use App\Http\Controllers\Api\Admin\CategoryController;
use App\Http\Controllers\Api\Admin\EnrollmentsController;
use App\Http\Controllers\Api\Admin\CodesController;
use App\Http\Controllers\Api\Admin\DashboardController;
use App\Http\Controllers\Api\Admin\RequirementsController;
use App\Http\Controllers\Api\Panel\SalesController;
use App\Http\Controllers\Api\Panel\UsersController;
use App\Http\Controllers\Api\Admin\ServicesController;
use App\Http\Controllers\Api\Admin\StudyClassesController;
use App\Http\Controllers\Api\Admin\CertificatesController;
use App\Http\Controllers\Api\Admin\DiscountController;
use App\Http\Controllers\Api\Admin\DocumentsController;
use App\Http\Controllers\Api\Admin\InstallmentsController;
use App\Http\Controllers\Api\Admin\OfflinePaymentsController;
use App\Http\Controllers\Api\Admin\OrganizationController;
use App\Http\Controllers\Api\Admin\PlanController;
use App\Http\Controllers\Api\Admin\QuizzesController;
use App\Http\Controllers\Api\Admin\RoleController;
use App\Http\Controllers\Api\Admin\SalesController as AdminSalesController;
use App\Http\Controllers\Api\Admin\SupportsController;
use App\Http\Controllers\Api\Admin\SupportsQuestionController;
use App\Http\Controllers\Api\Admin\UsersNotAccessToContentController;
use App\Http\Controllers\Api\Admin\WebinarCertificateController;

use App\Http\Controllers\Api\Instructor\EmployeeProgressController;

use App\Http\Controllers\Api\Panel\NotificationsController;

use Illuminate\Support\Facades\Route;

Route::prefix('{url_name}')->group(function () {
    Route::middleware(['auth:api'])->group(function () {

        // User Dashboard
        Route::get('/', [DashboardController::class, 'dashboard']);
        Route::group(['prefix' => 'notifications'], function () {
            Route::get('/', [NotificationsController::class, 'list']);
            Route::post('/{id}/seen', [NotificationsController::class, 'seen'])->name('notifications.seen');
        });

        // Admission Requirments
        Route::group(['prefix' => 'requirements'], function () {
            Route::get('/list', [RequirementsController::class, 'index']);
            Route::get('/{id}/approve', [RequirementsController::class, 'approve']);
            Route::get('/{id}/reject', [RequirementsController::class, 'reject'])->middleware('can:admin_requirements_reject');
            Route::get('/excel', [RequirementsController::class, 'exportExcelRequirements']);
        });

        // Students Permissions
        Route::prefix('permission')->group(function () {
            Route::get('/user_access', [SalesController::class, 'index2']);
            Route::post('/toggle_access/{id}', [SalesController::class, 'toggleAccess']);
            Route::get('/export', [SalesController::class, 'exportExcel']);
        });

        // Students Records
        Route::prefix('students')->group(function () {
            Route::get('/all', [UsersController::class, 'students']);
            Route::get('/excelAll', [UsersController::class, 'exportExcelAll']);
            Route::get('/registered_users', [UsersController::class, 'RegisteredUsers']);
            Route::get('/excelRegisteredUsers',  [UsersController::class, 'exportExcelRegisteredUsers']);
            Route::get('/reserve_seat', [UsersController::class, 'reserveSeat']);
            Route::get('/excelReserveSeat', [UsersController::class, 'exportExcelReserveSeat']);
            Route::get('/enrollers', [UsersController::class, 'Enrollers']);
            Route::get('/excelEnroller', [UsersController::class, 'exportExcelEnrollers']);
            Route::get('/direct_register', [UsersController::class, 'directRegister']);
            Route::get('/excelDirectRegister', [UsersController::class, 'exportExcelDirectRegister']);
            Route::get('/scholarship', [UsersController::class, 'ScholarshipStudent']);
            Route::get('/excelScholarship',  [UsersController::class, 'exportExcelScholarship']);
            Route::put('/{id}', [UsersController::class, 'update']);
            Route::delete('/{id}', [UsersController::class, 'destroy']);
        });

        // Electronic Services
        Route::prefix('services')->group(function () {
            Route::get('', [ServicesController::class, 'index']);
            Route::get('{service}', [ServicesController::class, 'show']);
            Route::post('', [ServicesController::class, 'store']);
            Route::put('{service}', [ServicesController::class, 'update']);
            Route::delete('{service}', [ServicesController::class, 'destroy']);
            Route::get('{service}/requests', [ServicesController::class, 'requests']);
            Route::get('/requests/{service}/export', [ServicesController::class, 'exportRequests']);
        });

        // Academic Classes
        Route::prefix('classes')->group(function () {
            Route::get('/', [StudyClassesController::class, 'index']);
            Route::post('/', [StudyClassesController::class, 'store']);
            Route::put('/{class}', [StudyClassesController::class, 'update']);
            Route::delete('/{class}', [StudyClassesController::class, 'destroy']);
            Route::get('/{class}/students', [StudyClassesController::class, 'students']);
            Route::get('/{class}/excelStudent', [StudyClassesController::class, 'exportExcelBatchStudents']);
            Route::get('/{class}/registered_users', [StudyClassesController::class, 'RegisteredUsers']);
            Route::get('/{class}/users', [StudyClassesController::class, 'Users']);
            Route::get('/{class}/enrollers', [StudyClassesController::class, 'Enrollers']);
            Route::get('/{class}/direct_register', [StudyClassesController::class, 'directRegister']);
        });

        // Codes
        Route::prefix('codes')->group(function () {
            Route::get('/', [CodesController::class, 'index']);
            Route::post('/', [CodesController::class, 'store']);
            Route::get('/instructor', [CodesController::class, 'index_instructor']);
            Route::post('/instructor_store', [CodesController::class, 'store_instructor']);
        });

        // Certificates
        Route::prefix('certificates')->group(function () {
            Route::get('/', [CertificatesController::class, 'index']);
            Route::get('/{id}/download', [CertificatesController::class, 'CertificatesDownload']);
            Route::get('/excel', [CertificatesController::class, 'exportExcel']);
            Route::get('/course-competition', [WebinarCertificateController::class, 'index']);
            Route::prefix('templates')->group(function () {
                Route::get('/', [CertificatesController::class, 'CertificatesTemplatesList']);
                Route::post('/', [CertificatesController::class, 'CertificatesTemplateStore']);
                Route::put('/{template_id}', [CertificatesController::class, 'CertificatesTemplateStore']);
                Route::delete('/{template_id}', [CertificatesController::class, 'CertificatesTemplatesDelete']);
            });
        });

        // Registrations (enrollments)
        Route::prefix('enrollments')->group(function () {
            Route::get('/history', [EnrollmentsController::class, 'history']);
            Route::get('/{sale_id}/block-access', [EnrollmentsController::class, 'blockAccess']);
            Route::get('/{sale_id}/enable-access', [EnrollmentsController::class, 'enableAccess']);
            Route::get('/export', [EnrollmentsController::class, 'exportExcel']);
            Route::post('/store', [EnrollmentsController::class, 'store']);
        });

        // Categories
        Route::prefix('categories')->group(function () {
            Route::get('/', [CategoryController::class, 'index']);
            Route::post('/', [CategoryController::class, 'store']);
            Route::put('/{id}/update', [CategoryController::class, 'update']);
            Route::delete('/{id}/delete', [CategoryController::class, 'destroy']);
        });

        // Course Registration
        Route::prefix('courses')->group(function () {
            Route::get('/list', [UserController::class, 'coursesList']);
        });

        // Courses
        Route::prefix('webinars')->group(function () {
            Route::get('/', [WebinarController::class, 'index']);
            Route::get('/excel', [WebinarController::class, 'exportExcel']);
            Route::get('/{id}/approve', [WebinarController::class, 'approve']);
            Route::get('/{id}/reject', [WebinarController::class, 'reject']);
            Route::get('/{id}/unpublish', [WebinarController::class, 'unpublish']);
            Route::post('/{id}/sendNotification', [WebinarController::class, 'sendNotificationToStudents']);
            Route::get('/{id}/students', [WebinarController::class, 'studentsLists']);
            Route::get('/{id}/students/export', [WebinarController::class, 'exportStudents']);
            Route::get('/{id}/statistics', [WebinarStatisticController::class, 'index']);
            Route::post('/', [WebinarController::class, 'store']);
            Route::put('/{id}', [WebinarController::class, 'update']);
            Route::delete('/{id}', [WebinarController::class, 'destroy']);
        });

        // Bundles
        Route::prefix('bundles')->group(function () {
            Route::get('/', [BundleController::class, 'index']);
            Route::post('/{id}/sendNotification', [BundleController::class, 'sendNotificationToStudents']);
            Route::get('/{id}/students', [BundleController::class, 'studentsLists']);
            Route::post('/', [BundleController::class, 'store']);
            Route::put('/{id}', [BundleController::class, 'update']);
            Route::delete('/{id}', [BundleController::class, 'destroy']);
        });

        // Programs Statistics
        Route::prefix('programs_statistics')->group(function () {
            Route::get('/bundles', [BundleController::class, 'statistics']);
            Route::get('/webinars', [WebinarController::class, 'statistics']);
        });

        // Quizzes
        Route::prefix('quizzes')->group(function () {
            Route::get('/', [QuizzesController::class, 'index']);
            Route::get('/excel', [QuizzesController::class, 'exportExcel']);
            Route::get('/{id}/results', [QuizzesController::class, 'results']);
            Route::get('/{id}/results/excel', [QuizzesController::class, 'resultsExportExcel']);
            Route::delete('/result/{result_id}', [QuizzesController::class, 'resultDelete']);
            Route::post('/', [QuizzesController::class, 'store']);
            Route::put('/{id}', [QuizzesController::class, 'update']);
            Route::delete('/{id}', [QuizzesController::class, 'delete']);
        });

        // Assignments
        Route::prefix('assignments')->group(function () {
            Route::get('/', [AssignmentsController::class, 'index']);
            Route::get('/{id}/students', [AssignmentsController::class, 'students']);
            Route::get('/{assignmentId}/history/{historyId}/conversations', [AssignmentsController::class, 'conversations']);
            Route::put('/{id}', [AssignmentsController::class, 'update']);
        });

        Route::prefix('financial')->group(function () {
            // Balances
            Route::prefix('documents')->group(function () {
                Route::get('/', [DocumentsController::class, 'index']);
                Route::post('/', [DocumentsController::class, 'store']);
            });
            // Sales list
            Route::prefix('sales')->group(function () {
                Route::get('/', [AdminSalesController::class, 'index']);
                Route::get('/export', [AdminSalesController::class, 'exportExcel']);
                Route::get('/{sale}/toggle-access', [AdminSalesController::class, 'toggleAccess']);
                Route::post('/{id}/refund', [AdminSalesController::class, 'refund']);
                Route::get('/{id}/invoice', [AdminSalesController::class, 'invoice']);
            });

            // Offline Payments
            Route::prefix('offline_payments')->group(function () {
                Route::get('/', [OfflinePaymentsController::class, 'index']);
                Route::get('/excel', [OfflinePaymentsController::class, 'exportExcel']);
                Route::get('/{offlinePayment}/reject', [OfflinePaymentsController::class, 'reject']);
                Route::get('/{id}/approved', [OfflinePaymentsController::class, 'approved']);
            });

            // Installments
            Route::group(['prefix' => 'installments'], function () {
                Route::get('/', [InstallmentsController::class, 'index']);
                Route::post('/', [InstallmentsController::class, 'store']);
                Route::put('/{id}', [InstallmentsController::class, 'update']);
                Route::delete('/{id}', [InstallmentsController::class, 'delete']);

                // Purchases
                Route::get('/purchases', [InstallmentsController::class, 'purchases']);
                Route::get('/purchases/export', [InstallmentsController::class, 'purchasesExportExcel']);
                Route::get('/orders/{id}/details', [InstallmentsController::class, 'details']);
                Route::put('users/{id}', [UserController::class, 'update']);
                Route::post('/support', [SupportsController::class, 'store']);
                Route::get('/cancel/{id}', [InstallmentsController::class, 'cancel']);

                // Overdue
                Route::group(['prefix' => 'overdue'], function () {
                    Route::get('/', [InstallmentsController::class, 'overdueLists']);
                    Route::get('/export', [InstallmentsController::class, 'overdueListsExportExcel']);
                });

                // Overdue History
                Route::group(['prefix' => 'overdue_history'], function () {
                    Route::get('/', [InstallmentsController::class, 'overdueHistories']);
                    Route::get('/export', [InstallmentsController::class, 'overdueHistoriesExportExcel']);
                });

                // Setting
                Route::group(['prefix' => 'settings'], function () {
                    Route::get('/', [InstallmentsController::class, 'settings']);
                    Route::post('/', [InstallmentsController::class, 'storeSettings']);
                });
            });

            // Discount Code
            Route::group(['prefix' => 'discounts'], function () {
                Route::get('/', [DiscountController::class, 'index']);
                Route::get('/{discount}/students', [DiscountController::class, 'students']);
                Route::post('/', [DiscountController::class, 'store']);
                Route::put('/{id}', [DiscountController::class, 'update']);
                Route::delete('/{id}', [DiscountController::class, 'destroy']);
            });
        });

        // Staffs
        Route::group(['prefix' => 'staffs'], function () {
            Route::get('/', [UserController::class, 'staffs']);
        });

        // Students
        Route::prefix('students')->group(function () {
            Route::get('/', [UsersController::class, 'students']);
        });

        // Instructors
        Route::group(['prefix' => 'instructors'], function () {
            Route::get('/', [UserController::class, 'instructors']);
            Route::get('/excel', 'UserController@exportExcelInstructors');
        });

        Route::group(['prefix' => 'users'], function () {
            Route::post('/', [UserController::class, 'store']);
            Route::delete('/{id}', [UserController::class, 'destroy']);

            // Users Who do Not Have Access To Content
            Route::group(['prefix' => 'not-access-to-content'], function () {
                Route::get('/', [UsersNotAccessToContentController::class, 'index']);
                Route::post('/', [UsersNotAccessToContentController::class, 'store']);
                Route::get('/{id}/active', [UsersNotAccessToContentController::class, 'active']);
            });

            // Roles
            Route::group(['prefix' => 'roles'], function () {
                Route::get('/', [RoleController::class, 'index']);
                Route::post('/', [RoleController::class, 'store']);
                Route::put('/{id}', [RoleController::class, 'update']);
                Route::delete('/{id}', [RoleController::class, 'destroy']);
            });

            // Groups
            Route::group(['prefix' => 'groups'], function () {
                Route::get('/', [GroupController::class, 'index']);
                Route::post('/', [GroupController::class, 'store']);
                Route::put('/{id}', [GroupController::class, 'update']);
                Route::delete('/{id}', [GroupController::class, 'destroy']);
                Route::post('/{id}/groupRegistrationPackage', [GroupController::class, 'groupRegistrationPackage']);
            });
        });


        Route::group(['prefix' => 'plans'], function () {

            Route::get('/', [PlanController::class, 'index']);
            Route::post('/', [PlanController::class, 'store']);
            Route::put('/{id}', [PlanController::class, 'update']);
            Route::delete('/{id}', [PlanController::class, 'destroy']);
            Route::post('/{id}/active', [PlanController::class, 'makeActive']);
        });

        Route::get('employee_progress', [EmployeeProgressController::class, 'index']);
        Route::delete('/{bundle_id}/{student_id}/remove', [EmployeeProgressController::class, 'destroy']);
        Route::post('/add_employee', [EmployeeProgressController::class, 'store']);
        

        // Support
        Route::group(['prefix' => 'supports'], function () {
            Route::get('/', [SupportsController::class, 'index']);
            Route::post('/', [SupportsController::class, 'store']);
            Route::put('/{id}', [SupportsController::class, 'update']);
            Route::delete('/{id}', [SupportsController::class, 'delete']);
        });

        // Supports Questions
        Route::group(['prefix' => 'supports-questions'], function () {
            Route::get('/', [supportsQuestionController::class, 'index']);
            Route::get('/{id}', [supportsQuestionController::class, 'show']);
            Route::post('/', [supportsQuestionController::class, 'store']);
            Route::put('/{id}', [supportsQuestionController::class, 'update']);
            Route::delete('/{id}', [supportsQuestionController::class, 'destroy']);
        });

        Route::get('/profile', [OrganizationController::class, 'index']);
    });
});
