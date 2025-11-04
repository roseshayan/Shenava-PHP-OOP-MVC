<?php
/**
 * Shenava - Audio Controller
 * Handles audio playback and listening history
 */

class AudioController extends ApiController
{
    private AudioModel $audioModel;
    private AuthMiddleware $authMiddleware;

    public function __construct()
    {
        parent::__construct();
        $this->audioModel = new AudioModel();
        $this->authMiddleware = new AuthMiddleware();
    }

    /**
     * Update listening progress
     */
    public function updateProgress()
    {
        try {
            $user = $this->authMiddleware->authenticate();
            $data = $this->getRequestData();

            $required = ['chapter_uuid', 'progress_seconds', 'percentage'];
            $validation = $this->validateRequired($data, $required);

            if ($validation !== true) {
                return $this->error('Validation failed', 422, $validation);
            }

            // Get chapter by UUID
            $chapter = $this->audioModel->getChapterByUuid($data['chapter_uuid']);
            if (!$chapter) {
                return $this->error('Chapter not found', 404);
            }

            // Update progress
            $success = $this->audioModel->updateProgress(
                $user->id,
                $chapter->id,
                $data['progress_seconds'],
                $data['percentage']
            );

            if ($success) {
                // Increment plays count if starting from beginning
                if ($data['progress_seconds'] <= 5) {
                    $this->audioModel->incrementPlays($chapter->id);
                }

                return $this->success(null, 'Progress updated successfully');
            }

            return $this->error('Failed to update progress');

        } catch (Exception $e) {
            return $this->error($e->getMessage(), 500);
        }
    }

    /**
     * Get listening history
     */
    public function listeningHistory()
    {
        try {
            $user = $this->authMiddleware->authenticate();
            $pagination = $this->getPaginationParams();

            $history = $this->audioModel->getListeningHistory($user->id, [
                'limit' => $pagination['limit'],
                'offset' => $pagination['offset']
            ]);

            $response = ResponseFormatter::paginate($history, [
                'page' => $pagination['page'],
                'limit' => $pagination['limit'],
                'total' => count($history)
            ]);

            return $this->success($response);

        } catch (Exception $e) {
            return $this->error($e->getMessage(), 500);
        }
    }

    /**
     * Get chapter audio URL
     */
    public function getChapterAudio($params)
    {
        try {
            $chapterUuid = $params['uuid'] ?? '';

            if (empty($chapterUuid)) {
                return $this->error('Chapter UUID is required', 422);
            }

            $chapter = $this->audioModel->getChapterByUuid($chapterUuid);

            if (!$chapter) {
                return $this->error('Chapter not found', 404);
            }

            $response = [
                'chapter' => [
                    'uuid' => $chapter->uuid,
                    'title' => $chapter->title,
                    'audio_url' => $chapter->audio_url,
                    'duration_seconds' => $chapter->duration_seconds,
                    'file_size' => $chapter->file_size
                ],
                'book' => [
                    'uuid' => $chapter->book_uuid,
                    'title' => $chapter->book_title
                ]
            ];

            return $this->success($response);

        } catch (Exception $e) {
            return $this->error($e->getMessage(), 500);
        }
    }
}