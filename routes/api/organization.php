<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\FAQController;
use App\Http\Controllers\Api\Admin\PlanController;
use App\Http\Controllers\Api\Admin\RoleController;
use App\Http\Controllers\Api\Admin\UserController;
use App\Http\Controllers\Api\Admin\CodesController;
use App\Http\Controllers\Api\Admin\FilesController;
use App\Http\Controllers\Api\Admin\GroupController;
use App\Http\Controllers\Api\Panel\SalesController;
use App\Http\Controllers\Api\Panel\UsersController;
use App\Http\Controllers\Api\Admin\BundleController;
use App\Http\Controllers\Api\Admin\QuizzesController;
use App\Http\Controllers\Api\Admin\WebinarController;
use App\Http\Controllers\Admin\PrerequisiteController;
use App\Http\Controllers\Api\Admin\CategoryController;
use App\Http\Controllers\Api\Admin\ChaptersController;
use App\Http\Controllers\Api\Admin\DiscountController;
use App\Http\Controllers\Api\Admin\ServicesController;
use App\Http\Controllers\Api\Admin\SupportsController;
use App\Http\Controllers\Api\Admin\DashboardController;
use App\Http\Controllers\Api\Admin\DocumentsController;
use App\Http\Controllers\Api\Admin\AssignmentsController;
use App\Http\Controllers\Api\Admin\EnrollmentsController;
use App\Http\Controllers\Api\Admin\TextLessonsController;
use App\Http\Controllers\Api\Admin\CertificatesController;
use App\Http\Controllers\Api\Admin\InstallmentsController;
use App\Http\Controllers\Api\Admin\OrganizationController;
use App\Http\Controllers\Api\Admin\RequirementsController;
use App\Http\Controllers\Api\Admin\StudyClassesController;
use App\Http\Controllers\Api\Admin\NotificationsController;
use App\Http\Controllers\Api\Admin\OfflinePaymentsController;
use App\Http\Controllers\Api\Admin\EmployeeProgressController;
use App\Http\Controllers\Api\Admin\SupportsQuestionController;
use App\Http\Controllers\Api\Admin\WebinarStatisticController;
use App\Http\Controllers\Api\Admin\WebinarCertificateController;
use App\Http\Controllers\Admin\WebinarExtraDescriptionController;
use App\Http\Controllers\Api\Admin\UsersNotAccessToContentController;
use App\Http\Controllers\Api\Admin\SalesController as AdminSalesController;
use App\Http\Controllers\Api\Admin\WebinarQuizController;

Route::prefix('{url_name}')->group(function () {
    Route::middleware(['auth:api'])->group(function () {

        // User Dashboard
        Route::get('/', [DashboardController::class, 'dashboard']);
        Route::post('/admin/impersonate/{user_id}', [DashboardController::class, 'impersonate']);
        Route::group(['prefix' => 'notifications'], function () {
            Route::get('/', [NotificationsController::class, 'list']);
            Route::post('/{id}/seen', [NotificationsController::class, 'seen'])->name('notifications.seen');
        });

        // Admission Requirments
        Route::group(['prefix' => 'requirements'], function () {
            Route::get('/list', [RequirementsController::class, 'index']);
            Route::get('/rejectionReasons', [RequirementsController::class, 'rejectionReasons']);
            Route::get('/{id}/approve', [RequirementsController::class, 'approve']);
            Route::post('/{id}/reject', [RequirementsController::class, 'reject'])->middleware('can:admin_requirements_reject');
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
            Route::get('/{id}', [UsersController::class, 'show']);
            Route::delete('/{id}', [UsersController::class, 'destroy']);
        });

        // Electronic Services
        Route::prefix('services')->group(function () {
            Route::get('', [ServicesController::class, 'index']);
            Route::get('{id}', [ServicesController::class, 'show']);
            Route::post('', [ServicesController::class, 'store']);
            Route::put('{service}', [ServicesController::class, 'update']);
            Route::delete('{service}', [ServicesController::class, 'destroy']);
            Route::get('{service}/requests', [ServicesController::class, 'requests']);
            Route::get('/requests/{serviceUser}/approve', [ServicesController::class, 'approveRequest']);
            Route::post('/requests/{serviceUser}/reject', [ServicesController::class, 'rejectRequest']);
            Route::get('/requests/{service}/export', [ServicesController::class, 'exportRequests']);
        });

        // Academic Classes
        Route::prefix('classes')->group(function () {
            Route::get('/', [StudyClassesController::class, 'index']);
            Route::get('/targetOptions', [StudyClassesController::class, 'getTargetOptions']);
            Route::post('/', [StudyClassesController::class, 'store']);
            Route::put('/{class}', [StudyClassesController::class, 'update']);
            Route::delete('/{class}', [StudyClassesController::class, 'destroy']);
            Route::get('/{class}/students', [StudyClassesController::class, 'students']);
            Route::get('/{class}/excelStudent', [StudyClassesController::class, 'exportExcelBatchStudents']);
            Route::get('/{class}/registered_users', [StudyClassesController::class, 'RegisteredUsers']);
            Route::get('/{class}/users', [StudyClassesController::class, 'Users']);
            Route::get('/{class}/enrollers', [StudyClassesController::class, 'Enrollers']);
            Route::get('/{class}/direct_register', [StudyClassesController::class, 'directRegister']);
            Route::get('/{class}/scholarship', [StudyClassesController::class, 'ScholarshipStudent']);
            Route::get('/{class}/requirements', [StudyClassesController::class, 'requirements']);
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
                Route::put('/{template_id}', [CertificatesController::class, 'CertificatesTemplateUpdate']);
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
            Route::get('/', [CategoryController::class, 'categories']);
            Route::get('/subCategories/{categoryId}', [CategoryController::class, 'subCategories']);
            Route::get('/{id}', [CategoryController::class, 'show']);
            Route::post('/', [CategoryController::class, 'store']);
            Route::put('/{id}', [CategoryController::class, 'update']);
            Route::delete('/{id}', [CategoryController::class, 'destroy']);
        });

        // Plans
        Route::group(['prefix' => 'plans'], function () {
            Route::get('/', [PlanController::class, 'index']);
            Route::post('/', [PlanController::class, 'store']);
            Route::put('/{id}', [PlanController::class, 'update']);
            Route::delete('/{id}', [PlanController::class, 'destroy']);
            Route::post('/{id}/active', [PlanController::class, 'makeActive']);
        });

        // User Progress
        Route::group(['prefix' => 'progress'], function () {
            Route::get('bundlesProgress', [EmployeeProgressController::class, 'getBundlesAverageProgress']);
            Route::get('bundlesProgress/{bundleId}', [EmployeeProgressController::class, 'getBundleLearningProgress']);
            Route::get('UsersBundlesProgress', [EmployeeProgressController::class, 'getAllUsersBundlesProgress']);
        });

        // Course Registration
        Route::prefix('courses')->group(function () {
            Route::get('/list', [UserController::class, 'coursesList']);
            Route::get('/{id}', [UserController::class, 'Courses']);
            Route::get('/groups/{id}/show', [UserController::class, 'groupInfo']);
            Route::get('/groups/{group}/exportExcel', [UserController::class, 'groupExportExcel']);
            Route::put('/groups/{group}/update', [UserController::class, 'groupUpdate']);
            Route::post('/groups/change', [UserController::class, 'changeGroup']);
            Route::delete('/groups/{id}/delete', [GroupController::class, 'destroy']);
        });
        Route::group(['prefix' => 'faqs'], function () {
            Route::post('/store', [FAQController::class, 'store']);
            Route::post('/{id}/description', [FAQController::class, 'descriptionFaq']);
            Route::post('/{id}/update', [FAQController::class, 'updateFaq']);
            Route::get('/{id}/delete', [FAQController::class, 'destroyFaq']);
            Route::get('/{id}/list', [FAQController::class, 'list']);
        });
        Route::group(['prefix' => 'webinar-quiz'], function () {
            Route::post('/store', [WebinarQuizController::class, 'store']);
            Route::post('/{id}/update', [WebinarQuizController::class, 'updateWebinarQuiz']);
            Route::get('/{id}/delete', [WebinarQuizController::class, 'destroyWebinarQuiz']);
            Route::get('/{id}/list', [WebinarQuizController::class, 'list']);
        });
        Route::group(['prefix' => 'prerequisites'], function () {
            Route::post('/store', [PrerequisiteController::class, 'store']);
            Route::post('/{id}/update', [PrerequisiteController::class, 'updatePrerequisite']);
            Route::get('/{id}/delete', [PrerequisiteController::class, 'destroyPrerequisite']);
            Route::get('/{id}/list', [PrerequisiteController::class, 'list']);
        });
        Route::group(['prefix' => 'webinar-extra-description'], function () {
            Route::post('/store', [WebinarExtraDescriptionController::class, 'store']);
            Route::post('/{id}/update', [WebinarExtraDescriptionController::class, 'updateWebinarExtraDescription']);
            Route::get('/{id}/delete', [WebinarExtraDescriptionController::class, 'destroyWebinarExtraDescription']);
            Route::get('/{id}/list', [WebinarExtraDescriptionController::class, 'list']);
        });

        // Courses/
        Route::prefix('webinars')->group(function () {
            Route::get('/', [WebinarController::class, 'index']);
            Route::get('/chapters/{webinarId}', [WebinarController::class, 'listChapters']);
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

        // chapters
        Route::group(['prefix' => 'chapters'], function () {
            Route::get('/{webinar_id}', [ChaptersController::class, 'listChaptersByWebinarId']);
            Route::post('/', [ChaptersController::class, 'store']);
            Route::put('/{id}', [ChaptersController::class, 'update']);
            Route::delete('/{id}', [ChaptersController::class, 'destroy']);
            Route::post('/change', [ChaptersController::class, 'change']);
            Route::post('/orderChapters/{webinarId}', [ChaptersController::class, 'orderChapters']);


            // new file and SCORM
            Route::group(['prefix' => 'files'], function () {
                Route::post('/', [FilesController::class, 'store']);
                Route::post('/{id}', [FilesController::class, 'update']);
                Route::delete('/{id}', [FilesController::class, 'destroy']);
            });

            // text lesson
            Route::group(['prefix' => 'text-lesson'], function () {
                Route::post('/', [TextLessonsController::class, 'store']);
                Route::put('/{id}', [TextLessonsController::class, 'update']);
                Route::delete('/{id}', [TextLessonsController::class, 'destroy']);
            });

            // new quiz
            Route::group(['prefix' => 'webinar-quiz'], function () {
                Route::post('/', [QuizzesController::class, 'store']);
                Route::put('/{id}', [QuizzesController::class, 'update']);
                Route::delete('/{id}', [QuizzesController::class, 'delete']);
            });

            // new assignment
            Route::group(['prefix' => 'assignments'], function () {
                Route::post('/assignment', [AssignmentsController::class, 'store']);
                Route::post('/{id}', [AssignmentsController::class, 'update']);
                Route::delete('/{id}', [AssignmentsController::class, 'destroy']);
            });
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
            Route::get('/bundles/statistics/export', [BundleController::class, 'BundleStatisticsExportExcel']);
            Route::get('/bundles/{bundle}/export-usercodes', [BundleController::class, 'exportBundleUserCodes']);
            Route::get('/webinars', [WebinarController::class, 'statistics']);
        });

        // Quizzes
        Route::prefix('quizzes')->group(function () {
            Route::get('/', [QuizzesController::class, 'index']);
            Route::get('/chapters/{quiz_id}', [QuizzesController::class, 'listChaptersByQuiz']);
            Route::get('/excel', [QuizzesController::class, 'exportExcel']);
            Route::get('/{id}/results', [QuizzesController::class, 'results']);
            Route::get('/{id}/results/excel', [QuizzesController::class, 'resultsExportExcel']);
            Route::delete('/result/{result_id}', [QuizzesController::class, 'resultDelete']);
            Route::post('/', [QuizzesController::class, 'store']);
            Route::put('/{id}', [QuizzesController::class, 'update']);
            Route::delete('/{id}', [QuizzesController::class, 'delete']);
            Route::prefix('questions')->group(function () {
                Route::post('/', [QuizzesController::class, 'storeQuestion']);
                Route::get('/{quiz_id}', [QuizzesController::class, 'getQuestionsByQuiz']);
                Route::delete('/{id}', [QuizzesController::class, 'deletequestion']);
                Route::put('/{id}', [QuizzesController::class, 'updateQuestion']);
                Route::post('/change/{id}', [QuizzesController::class, 'orderItems']);
            });
        });

        // Assignments
        Route::prefix('assignments')->group(function () {
            Route::get('/', [AssignmentsController::class, 'index']);
            Route::get('/{id}/students', [AssignmentsController::class, 'students']);
            Route::get('/{assignmentId}/history/{historyId}/conversations', [AssignmentsController::class, 'conversations']);
            Route::post('/', 'AssignmentController@store');
            Route::put('/{id}', [AssignmentsController::class, 'update']);
            Route::delete('/{id}', 'AssignmentController@destroy');
        });

        Route::prefix('financial')->group(function () {
            // Balances
            Route::prefix('documents')->group(function () {
                Route::get('/', [DocumentsController::class, 'index']);
                Route::get('/users', [DocumentsController::class, 'indexusers']);
                Route::post('/', [DocumentsController::class, 'store']);
            });

            // Sales
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
            Route::put('/{id}', [UsersController::class, 'update']);
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
                Route::group(['prefix' => 'permissions'], function () {
                    Route::get('/', [RoleController::class, 'listPermissions']);
                    Route::get('/{id}', [RoleController::class, 'showPermissions']);
                });
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
