<?php

/**
 * Shenava - Audio Model
 * Handles audio playback and listening history
 */

class AudioModel extends Model
{

    protected $table = 'chapters';
    protected string $primaryKey = 'id';

    /**
     * Get chapter by UUID
     * @param string $uuid
     * @return object|null
     */
    public function getChapterByUuid($uuid)
    {
        $this->db->query("SELECT c.*, b.title as book_title, b.uuid as book_uuid
                         FROM chapters c
                         JOIN books b ON c.book_id = b.id
                         WHERE c.uuid = :uuid");
        $this->db->bind(':uuid', $uuid);
        return $this->db->single();
    }

    /**
     * Update listening progress
     * @param int $userId
     * @param int $chapterId
     * @param int $progressSeconds
     * @param float $percentage
     * @return bool
     */
    public function updateProgress($userId, $chapterId, $progressSeconds, $percentage)
    {
        // Check if record exists
        $this->db->query("SELECT id FROM listening_history 
                         WHERE user_id = :user_id AND chapter_id = :chapter_id");
        $this->db->bind(':user_id', $userId);
        $this->db->bind(':chapter_id', $chapterId);
        $existing = $this->db->single();

        $bookId = $this->getBookIdByChapter($chapterId);
        $completed = $percentage >= 95; // Mark as completed if 95% or more

        if ($existing) {
            // Update existing record
            $sql = "UPDATE listening_history 
                    SET progress_seconds = :progress_seconds, 
                        percentage = :percentage,
                        completed = :completed,
                        last_listened_at = NOW()
                    WHERE user_id = :user_id AND chapter_id = :chapter_id";
        } else {
            // Create new record
            $sql = "INSERT INTO listening_history 
                    (user_id, chapter_id, book_id, progress_seconds, percentage, completed)
                    VALUES (:user_id, :chapter_id, :book_id, :progress_seconds, :percentage, :completed)";
        }

        $this->db->query($sql);
        $this->db->bind(':user_id', $userId);
        $this->db->bind(':chapter_id', $chapterId);
        $this->db->bind(':progress_seconds', $progressSeconds);
        $this->db->bind(':percentage', $percentage);
        $this->db->bind(':completed', $completed, PDO::PARAM_BOOL);

        if (!$existing) {
            $this->db->bind(':book_id', $bookId);
        }

        return $this->db->execute();
    }

    /**
     * Get listening history for user
     * @param int $userId
     * @param array $options
     * @return array
     */
    public function getListeningHistory($userId, $options = [])
    {
        $limit = $options['limit'] ?? 20;
        $offset = $options['offset'] ?? 0;

        $sql = "SELECT lh.*, 
                       c.title as chapter_title, 
                       c.uuid as chapter_uuid,
                       b.title as book_title,
                       b.uuid as book_uuid,
                       b.cover_image,
                       a.name as author_name
                FROM listening_history lh
                JOIN chapters c ON lh.chapter_id = c.id
                JOIN books b ON lh.book_id = b.id
                LEFT JOIN authors a ON b.author_id = a.id
                WHERE lh.user_id = :user_id
                ORDER BY lh.last_listened_at DESC
                LIMIT :limit OFFSET :offset";

        $this->db->query($sql);
        $this->db->bind(':user_id', $userId);
        $this->db->bind(':limit', $limit, PDO::PARAM_INT);
        $this->db->bind(':offset', $offset, PDO::PARAM_INT);

        return $this->db->resultSet();
    }

    /**
     * Get book ID by chapter ID
     * @param int $chapterId
     * @return int
     */
    private function getBookIdByChapter($chapterId)
    {
        $this->db->query("SELECT book_id FROM chapters WHERE id = :chapter_id");
        $this->db->bind(':chapter_id', $chapterId);
        $result = $this->db->single();
        return $result ? $result->book_id : 0;
    }

    /**
     * Increment chapter plays count
     * @param int $chapterId
     * @return bool
     */
    public function incrementPlays($chapterId)
    {
        $this->db->query("UPDATE chapters SET plays_count = plays_count + 1 WHERE id = :id");
        $this->db->bind(':id', $chapterId);
        return $this->db->execute();
    }
}