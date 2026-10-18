<?php

namespace App\Services;

use App\Models\Notification;
use App\Models\Lecturer;
use App\Models\Student;
use App\Models\User;

class NotificationService
{
    public static function send(int $userId, string $type, string $title, string $message, ?string $link = null): void
    {
        Notification::create([
            'user_id' => $userId,
            'type'    => $type,
            'title'   => $title,
            'message' => $message,
            'link'    => $link,
            'is_read' => false,
        ]);
    }

    public static function notifyAdmins(string $type, string $title, string $message, ?string $link = null): void
    {
        User::where('role', 'admin')
            ->pluck('id')
            ->each(fn ($id) => static::send($id, $type, $title, $message, $link));
    }

    public static function notifyStudent(string $studentId, string $type, string $title, string $message, ?string $link = null): void
    {
        $userId = Student::where('student_id', $studentId)->value('user_id');
        if ($userId) {
            static::send($userId, $type, $title, $message, $link);
        }
    }

    public static function notifyLecturerByStudentId(string $studentId, string $type, string $title, string $message, ?string $link = null): void
    {
        $lecturerId = Student::where('student_id', $studentId)->value('lecturer_id');
        if ($lecturerId) {
            $userId = Lecturer::where('lecturer_id', $lecturerId)->value('user_id');
            if ($userId) {
                static::send($userId, $type, $title, $message, $link);
            }
        }
    }
}
