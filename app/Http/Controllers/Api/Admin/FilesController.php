<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Mixins\BunnyCDN\BunnyVideoStream;
use App\Models\File;
use App\Models\Translation\FileTranslation;
use App\Models\Api\Webinar;
use App\Models\Api\WebinarChapterItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Validator;

class FilesController extends Controller
{
    public function store(Request $request)
    {
        try {
             $this->authorize('admin_webinars_edit');
            $data = $request->all();
            $s3FileInput = $request->file('s3_file');

            $data['s3_file'] = $s3FileInput;

            if (empty($data['storage'])) {
                $data['storage'] = 'upload';
            }

            if (!empty($data['file_path']) and is_array($data['file_path'])) {
                $data['file_path'] = $data['file_path'][0];
            }

            $sourceRequiredFileType = ['external_link', 's3', 'google_drive', 'upload'];
            $sourceRequiredFileVolume = ['external_link', 'google_drive'];
            $sourceDefaultFileTypeAndVolume = ['youtube', 'vimeo', 'iframe', 'secure_host'];

            if (in_array($data['storage'], $sourceDefaultFileTypeAndVolume)) {
                $data['file_type'] = 'video';
                $data['volume'] = 0;
            }

            $rules = [
                'webinar_id' => 'required',
                'chapter_id' => 'required',
                'title' => 'required|max:255',
                'accessibility' => 'required|' . Rule::in(File::$accessibility),
                'file_path' => 'required',
                'file_type' => Rule::requiredIf(in_array($data['storage'], $sourceRequiredFileType)),
                'volume' => Rule::requiredIf(in_array($data['storage'], $sourceRequiredFileVolume)),
                'description' => 'nullable',
            ];

            if ($data['storage'] == 'upload_archive') {
                $rules['interactive_type'] = 'required';
                $rules['interactive_file_name'] = Rule::requiredIf($data['interactive_type'] == 'custom');
            }

            if (in_array($data['storage'], ['s3', 'secure_host'])) {
                $rules['file_path'] = 'nullable';
                $rules['s3_file'] = 'required';
            }

            $validator = Validator::make($data, $rules);

            if ($validator->fails()) {
                return response([
                    'code' => 422,
                    'errors' => $validator->errors(),
                ], 422);
            }

            $data['downloadable'] = !empty($data['downloadable']);
            if (in_array($data['storage'], ['youtube', 'vimeo', 'iframe', 'google_drive', 'upload_archive'])) {
                $data['downloadable'] = false;
            } elseif (in_array($data['storage'], ['external_link', 's3']) and $data['file_type'] != 'video') {
                $data['downloadable'] = true;
            }

            if (!empty($data['sequence_content']) and $data['sequence_content'] == 'on') {
                $data['check_previous_parts'] = (!empty($data['check_previous_parts']) and $data['check_previous_parts'] == 'on');
                $data['access_after_day'] = !empty($data['access_after_day']) ? $data['access_after_day'] : null;
            } else {
                $data['check_previous_parts'] = false;
                $data['access_after_day'] = null;
            }

            $webinar = Webinar::find($data['webinar_id']);

            if (!empty($webinar)) {
                $user = $webinar->creator;

                $volume = 0;
                $fileInfos = null;

                if ($data['storage'] == 'upload_archive') {
                    $uploadFile = $request->file('file_path'); // هنا الملف نفسه

                    if (!$uploadFile || $uploadFile->getClientOriginalExtension() != 'zip') {
                        return response([
                            'code' => 422,
                            'errors' => [
                                'file_path' => [trans('validation.mimes', ['attribute' => 'file', 'values' => 'zip'])]
                            ],
                        ], 422);
                    }

                       $volume = convertToMB($uploadFile->getSize());
                    $fileInfos['extension'] = 'archive';

                    // احفظ الملف مؤقتًا أو عالطريق اللي عايزة
                    $path = $uploadFile->store('archives');
                    $data['file_path'] = $path;

                    $data['interactive_file_path'] = $this->handleUnZipFile($data, $user->id);
                } elseif ($data['storage'] == 'upload') {
                    $uploadFile = $request->file('file_path'); // الملف اللي جاى من الفورم

                    if ($uploadFile) {
                        // خزنه في storage/app/public/uploads
                        $path = $uploadFile->store('uploads', 'public');
                        $data['file_path'] = $path;

                        // حجم الملف بالـ MB
                        $volume = convertToMB($uploadFile->getSize());
                    }
                } elseif (in_array($data['storage'], ['s3', 'secure_host'])) {
                    $data['volume'] = $request->file('s3_file')->getSize();;

                    if ($data['storage'] == 's3') {
                        $result = $this->uploadFileToS3($data['s3_file'], $user->id);
                    } else {
                        $result = $this->uploadFileToBunny($webinar, $data['s3_file']);
                    }

                    if (!$result['status']) {
                        return $result['path'];
                    }

                    $data['file_path'] = $result['path'];
                    $fileInfos['extension'] = $data['file_type'];
                    $volume = convertToMB(($data['volume'] ?? 0));
                } else {
                    $volume = !empty($data['volume']) ? $data['volume'] : 0; // input is MB
                }

                $file = File::create([
                    'creator_id' => $user->id,
                    'webinar_id' => $data['webinar_id'],
                    'chapter_id' => $data['chapter_id'],
                    'file' => $data['file_path'],
                    'volume' => $volume,
                    'file_type' => !empty($fileInfos) ? $fileInfos['extension'] : $data['file_type'],
                    'accessibility' => $data['accessibility'],
                    'storage' => $data['storage'],
                    'interactive_type' => $data['interactive_type'] ?? null,
                    'interactive_file_name' => $data['interactive_file_name'] ?? null,
                    'interactive_file_path' => $data['interactive_file_path'] ?? null,
                    'downloadable' => $data['downloadable'],
                    'online_viewer' => (!empty($data['online_viewer']) and $data['online_viewer'] == 'on'),
                    'check_previous_parts' => $data['check_previous_parts'],
                    'access_after_day' => $data['access_after_day'],
                    'status' => (!empty($data['status']) and $data['status'] == 'on') ? File::$Active : File::$Inactive,
                    'created_at' => time()
                ]);

                if (!empty($file)) {
                    FileTranslation::updateOrCreate([
                        'file_id' => $file->id,
                        'locale' => mb_strtolower($data['locale']),
                    ], [
                        'title' => $data['title'],
                        'description' => $data['description'],
                    ]);

                    if (!empty($file->chapter_id)) {
                        WebinarChapterItem::makeItem($file->creator_id, $file->chapter_id, $file->id, WebinarChapterItem::$chapterFile);
                    }
                }

                return response()->json([
                    'code' => 200,
                    'file'=>$file,
                ], 200);
            }

            return response()->json([], 422);
        } catch (\Illuminate\Validation\ValidationException $e) {
            $formattedErrors = [];

            $currentLocale = app()->getLocale();

            foreach ($e->errors() as $field => $messages) {
                $data = $e->validator->getData();
                $rules = [$field => $e->validator->getRules()[$field]];

                app()->setLocale('ar');
                $arValidator = Validator::make($data, $rules);
                $arMessage = $arValidator->errors()->first($field);

                app()->setLocale('en');
                $enValidator = Validator::make($data, $rules);
                $enMessage = $enValidator->errors()->first($field);

                $formattedErrors[$field] = [
                    'ar' => $arMessage,
                    'en' => $enMessage
                ];
            }

            app()->setLocale($currentLocale);

            return response()->json([
                'message' => 'Validation failed',
                'errors' => $formattedErrors
            ], 422);
        }
    }

    public function update($url_name,Request $request, $id)
    {

        try {

            $data = $request->all();
            $s3FileInput = $request->file('s3_file');

            $data['s3_file'] = $s3FileInput;

            $sourceRequiredFileType = ['external_link', 's3', 'google_drive', 'upload'];
            $sourceRequiredFileVolume = ['external_link', 'google_drive'];

            if (empty($data['storage'])) {
                $data['storage'] = 'upload';
            }

            if (!empty($data['file_path']) and is_array($data['file_path'])) {
                $data['file_path'] = $data['file_path'][0];
            }

            $rules = [
                'webinar_id' => 'required',
                'chapter_id' => 'required',
                'title' => 'required|max:255',
                'accessibility' => 'required|' . Rule::in(File::$accessibility),
                'file_path' => 'required',
                'file_type' => Rule::requiredIf(in_array($data['storage'], $sourceRequiredFileType)),
                'volume' => Rule::requiredIf(in_array($data['storage'], $sourceRequiredFileVolume)),
                'description' => 'nullable',
            ];

            if ($data['storage'] == 'upload_archive') {
                $rules['interactive_type'] = 'required';
                $rules['interactive_file_name'] = Rule::requiredIf($data['interactive_type'] == 'custom');
            }

            if ($data['storage'] == 's3') {
                $rules['file_path'] = 'nullable';
                $rules['s3_file'] = 'nullable';
            }

            $validator = Validator::make($data, $rules);

            if ($validator->fails()) {
                return response([
                    'code' => 422,
                    'errors' => $validator->errors(),
                ], 422);
            }

            $data['downloadable'] = !empty($data['downloadable']);
            if (in_array($data['storage'], ['youtube', 'vimeo', 'iframe', 'google_drive', 'upload_archive'])) {
                $data['downloadable'] = false;
            } elseif (in_array($data['storage'], ['external_link', 's3']) and $data['file_type'] != 'video') {
                $data['downloadable'] = true;
            }

            if (!empty($data['sequence_content']) and $data['sequence_content'] == 'on') {
                $data['check_previous_parts'] = (!empty($data['check_previous_parts']) and $data['check_previous_parts'] == 'on');
                $data['access_after_day'] = !empty($data['access_after_day']) ? $data['access_after_day'] : null;
            } else {
                $data['check_previous_parts'] = false;
                $data['access_after_day'] = null;
            }

            $volume = 0;
            $fileInfos = null;

            $webinar = Webinar::find($data['webinar_id']);
            $file = File::where('id', $id)->first();

            if (!empty($webinar) and !empty($file)) {

                if ($data['storage'] == 'upload_archive') {
                    $fileInfos = $this->fileInfo($data['file_path']);

                    if (empty($fileInfos) or $fileInfos['extension'] != 'zip') {
                        return response([
                            'code' => 422,
                            'errors' => [
                                'file_path' => [trans('validation.mimes', ['attribute' => 'file', 'values' => 'zip'])]
                            ],
                        ], 422);
                    }

                    $volume = convertToMB($fileInfos['size'] ?? 0);
                    $fileInfos['extension'] = 'archive';
                    $data['interactive_file_path'] = $this->handleUnZipFile($data, $file->creator_id);
                } elseif ($data['storage'] == 'upload') {
                    $uploadFile = $this->fileInfo($data['file_path']);
                    $volume = convertToMB($uploadFile['size'] ?? 0);
                } elseif (in_array($data['storage'], ['s3', 'secure_host'])) {

                    if (!empty($data['s3_file'])) {
                        $data['volume'] = $request->file('s3_file')->getSize();
                        $fileInfos['real_size'] = formatSizeUnits($data['volume']);

                        if ($data['storage'] == 's3') {
                            $result = $this->uploadFileToS3($data['s3_file'], $file->creator_id);
                        } else {
                            $result = $this->uploadFileToBunny($webinar, $data['s3_file']);
                        }

                        if (!$result['status']) {
                            return $result['path'];
                        }

                        $data['file_path'] = $result['path'];
                    } else {
                        $fileInfos['real_size'] = $data['volume'];
                    }

                    $fileInfos['extension'] = $data['file_type'];
                    $volume = convertToMB(($data['volume'] ?? 0));
                } else {
                    $volume = !empty($data['volume']) ? $data['volume'] : 0; // input is MB
                }


                $changeChapter = ($data['chapter_id'] != $file->chapter_id);
                $oldChapterId = $file->chapter_id;

                $file->update([
                    'chapter_id' => $data['chapter_id'],
                    'file' => $data['file_path'],
                    'volume' => $volume,
                    'file_type' => !empty($fileInfos) ? $fileInfos['extension'] : $data['file_type'],
                    'accessibility' => $data['accessibility'],
                    'storage' => $data['storage'],
                    'interactive_type' => $data['interactive_type'] ?? null,
                    'interactive_file_name' => $data['interactive_file_name'] ?? null,
                    'interactive_file_path' => $data['interactive_file_path'] ?? null,
                    'downloadable' => $data['downloadable'],
                    'online_viewer' => (!empty($data['online_viewer']) and $data['online_viewer'] == 'on'),
                    'check_previous_parts' => $data['check_previous_parts'],
                    'access_after_day' => $data['access_after_day'],
                    'status' => (!empty($data['status']) and $data['status'] == 'on') ? File::$Active : File::$Inactive,
                    'updated_at' => time()
                ]);

                if ($changeChapter) {
                    WebinarChapterItem::changeChapter($file->creator_id, $oldChapterId, $file->chapter_id, $file->id, WebinarChapterItem::$chapterFile);
                }

                FileTranslation::updateOrCreate([
                    'file_id' => $file->id,
                    'locale' => mb_strtolower($data['locale']),
                ], [
                    'title' => $data['title'],
                    'description' => $data['description'],
                ]);

                $checkWebinarChapterItem = WebinarChapterItem::where('user_id', $file->creator_id)
                    ->where('item_id', $file->id)
                    ->where('type', WebinarChapterItem::$chapterFile)
                    ->first();

                if (!empty($file->chapter_id) and empty($checkWebinarChapterItem)) {
                    WebinarChapterItem::makeItem($file->creator_id, $file->chapter_id, $file->id, WebinarChapterItem::$chapterFile);
                }

                removeContentLocale();

                return response()->json([
                    'code' => 200,
                    'file'=>$file,
                ], 200);
            }

            removeContentLocale();

            return response()->json([], 422);
        } catch (\Illuminate\Validation\ValidationException $e) {
            $formattedErrors = [];

            $currentLocale = app()->getLocale();

            foreach ($e->errors() as $field => $messages) {
                $data = $e->validator->getData();
                $rules = [$field => $e->validator->getRules()[$field]];

                app()->setLocale('ar');
                $arValidator = Validator::make($data, $rules);
                $arMessage = $arValidator->errors()->first($field);

                app()->setLocale('en');
                $enValidator = Validator::make($data, $rules);
                $enMessage = $enValidator->errors()->first($field);

                $formattedErrors[$field] = [
                    'ar' => $arMessage,
                    'en' => $enMessage
                ];
            }

            app()->setLocale($currentLocale);

            return response()->json([
                'message' => 'Validation failed',
                'errors' => $formattedErrors
            ], 422);
        }
    }


    public function fileInfo($path)
    {
        $file = [];

        $file_path = public_path($path);

        if (!file_exists($file_path)) {
            return null;
        }

        $filePath = pathinfo($file_path);

        $file['name'] = $filePath['filename'] ?? null;
        $file['extension'] = $filePath['extension'] ?? null;
        $file['size'] = filesize($file_path);

        return $file;
    }

    private function handleUnZipFile($data, $user_id)
    {
        $path = $data['file_path']; // ده بيكون مثلاً archives/file.zip
        $interactiveType = $data['interactive_type'] ?? null;
        $interactiveFileName = $data['interactive_file_name'] ?? null;

        $storage = Storage::disk('public');

        // خلي fileInfo يشتغل على المسار الحقيقي جوه storage
        $fullPath = $storage->path($path);

        if (!file_exists($fullPath)) {
            throw new \Exception("File not found: " . $fullPath);
        }

        $filePathInfo = pathinfo($fullPath);
        $extractPath = $user_id . '/' . $filePathInfo['filename'];
        $storageExtractPath = $storage->path($extractPath);

        if (!$storage->exists($extractPath)) {
            $storage->makeDirectory($extractPath);

            $zip = new \ZipArchive();
            $res = $zip->open($fullPath);

            if ($res === true) {
                $zip->extractTo($storageExtractPath);
                $zip->close();
            } else {
                throw new \Exception("Cannot open ZIP file: " . $fullPath);
            }
        }

        $fileName = 'index.html';
        if ($interactiveType == 'i_spring') {
            $fileName = 'story.html';
        } elseif ($interactiveType == 'custom') {
            $fileName = $interactiveFileName;
        }

        // رجّع URL public مش ال path الداخلي
        return $storage->url($extractPath . '/' . $fileName);
    }
    private function uploadFileToS3($file, $user_id)
    {
        $path = 'store/' . $user_id;

        $result = [
            'path' => null,
            'status' => true
        ];

        try {
            // اسم الملف مع التايم ستامب
            $fileName = time() . '_' . $file->getClientOriginalName();

            $storage = Storage::disk('minio');

            // إنشاء المجلد لو مش موجود
            if (!$storage->exists($path)) {
                $storage->makeDirectory($path);
            }

            // رفع الملف بالاسم الصحيح
            $path = $storage->putFileAs($path, $file, $fileName);

            // رابط الملف النهائي
            $result['path'] = $storage->url($path);
        } catch (\Exception $ex) {
            $result = [
                'path' => response([
                    'code' => 500,
                    'message' => $ex->getMessage(),
                    'traces' => $ex->getTrace(),
                ], 500),
                'status' => false
            ];
        }

        return $result;
    }


    private function uploadFileToBunny($webinar, $file)
    {
        $result = [
            'path' => null,
            'status' => true
        ];

        try {
            $bunnyVideoStream = new BunnyVideoStream();

            // يعمل Collection لكل كورس
            $collectionId = $bunnyVideoStream->createCollection("course {$webinar->id}", true);

            if ($collectionId) {
                // يرفع الفيديو ويرجع الـ URL
                $videoUrl = $bunnyVideoStream->uploadVideo(
                    $file->getClientOriginalName(),
                    $collectionId,
                    $file
                );

                $result['path'] = $videoUrl;
            }
        } catch (\Exception $ex) {
            $result = [
                'path' => response([
                    'code' => 500,
                    'message' => $ex->getMessage(),
                    'traces' => $ex->getTrace(),
                ], 500),
                'status' => false
            ];
        }

        return $result;
    }


    public function destroy($url_name, Request $request, $id)
    {


        $file = File::where('id', $id)
            ->first();

        if (!empty($file)) {
            if ($file->storage == "secure_host") {
                $bunnyVideoStream = new BunnyVideoStream();
                $bunnyVideoStream->deleteVideo($file->file);
            }


            WebinarChapterItem::where('user_id', $file->creator_id)
                ->where('item_id', $file->id)
                ->where('type', WebinarChapterItem::$chapterFile)
                ->delete();

            $file->delete();
        }

        return response()->json([
            'code' => 200
        ], 200);
    }
}
